<?php

namespace App\Services\Reports;

use App\Models\Assign;
use App\Models\CourseCreation;
use App\Models\Group;
use App\Models\Plan;
use App\Models\Semester;
use App\Models\TermDeclaration;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Attendance Reports, new version (user, 24 Nov 2026): one row per student with their
 * attendance counts and percentages for the chosen intake / attendance semesters.
 *
 * The counting is the old report's (Reports\AttendanceReportController::list, kept for the "Old
 * version" tab): the same plans, assigned students, joins and percentages. New here:
 * - attendance bands (0%, below 50, 50–59, 60–79, 80 and above, on the "with excuse"
 *   percentage; 0% is also inside "below 50");
 * - sorting (the old list ignored its sorters);
 * - when the filters leave no assigned student, nothing is listed (the old list then dropped
 *   the student filter and listed everyone who attended the plans);
 * - an optional list of student ids, for "Export selected".
 *
 * The grouped query is slow on big terms (about 4 s for 1,500 students), so a search sends every
 * row once and the screen does the bands, sorting and paging itself (user: no request per band).
 * arrange() is the same order for Excel.
 */
class StudentAttendanceReport
{
    /** Band limits on the "with excuse" percentage, as in the design: <50 red, 50–59 orange, 60–79 amber, 80+ green. */
    const LOW = 50;
    const MID = 60;
    const HIGH = 80;

    /** Band filters; "zero" (exactly 0%) is a part of "low". */
    const BANDS = ['zero', 'low', 'warn', 'mid', 'high'];

    /** Attendance counts in column order: [key, select alias, header, full name]. */
    const COUNTS = [
        ['P', 'P', 'Total P', 'Present (all days)'],
        ['WD', 'EVENING_P', 'WD.P', 'Present on weekdays'],
        ['WE', 'WEEKEND_P', 'WE.P', 'Present at weekends'],
        ['O', 'O', 'O', 'Online present'],
        ['A', 'A', 'A', 'Absence'],
        ['E', 'E', 'E', 'Excuse'],
        ['M', 'M', 'M', 'Medical'],
        ['H', 'H', 'H', 'Exceptional'],
        ['L', 'L', 'L', 'Late'],
        ['LE', 'LE', 'L.E', 'Left early'],
        ['TC', 'TOTAL', 'TC', 'Total classes'],
    ];

    /** Sort keys the screen may ask for, and the columns behind them. */
    const SORTS = ['name', 'group', 'pct', 'wo', 'P', 'WD', 'WE', 'O', 'A', 'E', 'M', 'H', 'L', 'LE', 'TC'];

    public array $intakeSemesters = [];
    public array $attendanceSemesters = [];
    public array $courses = [];
    public array $groups = [];
    public array $statuses = [];
    /** "Percentage" filter: students below this (or at 0%); null = any. */
    public ?float $below = null;
    /** Only these students (Export selected); empty = everyone the filters give. */
    public array $studentIds = [];

    public static function fromRequest(Request $request): self
    {
        $ids = fn ($key) => array_values(array_unique(array_filter(array_map('intval', (array) $request->input($key, [])))));

        $report = new self();
        $report->intakeSemesters = $ids('intake_semester');
        $report->attendanceSemesters = $ids('attendance_semester');
        $report->courses = $ids('course');
        // Master groups go by name (the same name can belong to several terms and courses).
        $report->groups = array_values(array_unique(array_filter(array_map('strval', (array) $request->input('group', [])), fn ($g) => trim($g) !== '')));
        $report->statuses = $ids('group_student_status');
        $percentage = $request->input('attendance_percentage');
        $report->below = ($percentage !== null && $percentage !== '' && is_numeric($percentage)) ? (float) $percentage : null;
        $report->studentIds = $ids('student_ids');

        return $report;
    }

    /** The old report's rule: an intake or an attendance semester is required. */
    public function hasSemester(): bool
    {
        return !empty($this->intakeSemesters) || !empty($this->attendanceSemesters);
    }

    /** "2026 September" — the attendance semesters searched, else the intakes. */
    public function label(): string
    {
        $names = !empty($this->attendanceSemesters)
            ? TermDeclaration::whereIn('id', $this->attendanceSemesters)->orderBy('name', 'DESC')->pluck('name')->all()
            : Semester::whereIn('id', $this->intakeSemesters)->orderBy('name', 'DESC')->pluck('name')->all();
        if (count($names) > 2) {
            return $names[0].', '.$names[1].' + '.(count($names) - 2).' more';
        }

        return implode(', ', $names);
    }

    /** Every student the filters give, formatted for the screen. */
    public function rows(): array
    {
        return $this->base()->get()->map(fn ($row) => self::format($row))->all();
    }

    /** The raw rows (Excel), keyed by student id. */
    public function rawRows(): Collection
    {
        return $this->base()->get()->keyBy('id');
    }

    public static function bandOf(float $pct): string
    {
        return $pct < self::LOW ? 'low' : ($pct < self::MID ? 'warn' : ($pct < self::HIGH ? 'mid' : 'high'));
    }

    public static function inBand(array $row, string $band): bool
    {
        return $band === 'zero' ? (float) $row['pct'] === 0.0 : self::bandOf($row['pct']) === $band;
    }

    /** Students per band, and in all ("zero" is counted inside "low" too). */
    public static function summarise(array $rows): array
    {
        $bands = ['all' => count($rows), 'zero' => 0, 'low' => 0, 'warn' => 0, 'mid' => 0, 'high' => 0];
        foreach ($rows as $row) {
            $bands[self::bandOf($row['pct'])]++;
            if ((float) $row['pct'] === 0.0) {
                $bands['zero']++;
            }
        }

        return $bands;
    }

    /** The rows in one band (or all), sorted. Ties keep student id order. */
    public static function arrange(array $rows, string $band, string $sort, string $dir): array
    {
        if (in_array($band, self::BANDS, true)) {
            $rows = array_values(array_filter($rows, fn ($row) => self::inBand($row, $band)));
        }
        $sort = in_array($sort, self::SORTS, true) ? $sort : 'name';
        $countIndex = array_search($sort, array_column(self::COUNTS, 0), true);
        $value = match (true) {
            $sort === 'name' => fn ($row) => mb_strtolower($row['name']),
            $sort === 'group' => fn ($row) => mb_strtolower($row['group']),
            $sort === 'pct', $sort === 'wo' => fn ($row) => $row[$sort],
            default => fn ($row) => $row['n'][$countIndex],
        };
        $sign = $dir === 'desc' ? -1 : 1;
        usort($rows, function ($a, $b) use ($value, $sign) {
            $order = $value($a) <=> $value($b);

            return $order !== 0 ? $order * $sign : $a['id'] <=> $b['id'];
        });

        return $rows;
    }

    /** Plans the filters cover (old report: attendance semesters, courses, master groups). */
    protected function planIds(): array
    {
        $groupIds = [];
        if (!empty($this->groups)) {
            $groupQuery = Group::select('id')->whereIn('name', $this->groups);
            if (!empty($this->attendanceSemesters)) {
                $groupQuery->whereIn('term_declaration_id', $this->attendanceSemesters);
            }
            if (!empty($this->courses)) {
                $groupQuery->whereIn('course_id', $this->courses);
            }
            $groupIds = $groupQuery->pluck('id')->unique()->all();
        }

        $plans = Plan::query();
        if (!empty($this->attendanceSemesters)) {
            $plans->whereIn('term_declaration_id', $this->attendanceSemesters);
        }
        if (!empty($this->courses)) {
            $plans->whereIn('course_id', $this->courses);
        }
        if (!empty($groupIds)) {
            $plans->whereIn('group_id', $groupIds);
        }

        return $plans->pluck('id')->unique()->all();
    }

    /** Students assigned to those plans with the chosen status and intake (old report). */
    protected function studentIdsFor(array $planIds): array
    {
        if (empty($planIds)) {
            return [];
        }
        $statuses = $this->statuses;
        $creations = !empty($this->intakeSemesters)
            ? CourseCreation::whereIn('semester_id', $this->intakeSemesters)->pluck('id')->unique()->all()
            : [];
        if (!empty($this->intakeSemesters) && empty($creations)) {
            return [];
        }

        $ids = Assign::whereIn('plan_id', $planIds)->whereHas('student', function ($q) use ($statuses, $creations) {
            if (!empty($statuses)) {
                $q->whereIn('status_id', $statuses);
            }
            if (!empty($creations)) {
                $q->whereHas('activeCR', fn ($sq) => $sq->whereIn('course_creation_id', $creations));
            }
        })->pluck('student_id')->unique()->all();

        if (!empty($this->studentIds)) {
            $ids = array_values(array_intersect($ids, $this->studentIds));
        }

        return $ids;
    }

    /** The old report's grouped query (one row per student) with the "Percentage" filter. */
    protected function base(): Builder
    {
        $planIds = $this->planIds();
        $studentIds = $this->studentIdsFor($planIds);

        $query = DB::table('attendances as atn')
            ->select(
                'std.id', 'std.first_name', 'std.last_name', 'std.date_of_birth', 'std.registration_no',
                'std.ssn_no', 'sts.name as status', 'stc.institutional_email', 'stc.mobile', 'sabd.reference',
                'scr.id as course_relation_id', 'grp.name as group_name', 'cc.semester_id', 'sm.name as semester_name',
                'cc.course_id', 'cr.name as course_name', 'ccv.slc_code',
                DB::raw("CASE WHEN EXISTS (
                    SELECT 1 FROM student_notes AS sn
                    WHERE sn.student_id = std.id AND sn.is_flaged = 'Yes' AND sn.flaged_status = 'Active' AND sn.deleted_at IS NULL
                ) THEN 1 ELSE 0 END AS note_flag"),
                DB::raw('COUNT(DISTINCT atn.id) AS TOTAL'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 1 THEN atn.id END) AS P'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 1 AND DAYOFWEEK(atn.attendance_date) BETWEEN 2 AND 6 THEN atn.id END) AS EVENING_P'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 1 AND DAYOFWEEK(atn.attendance_date) IN (1,7) THEN atn.id END) AS WEEKEND_P'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 2 THEN atn.id END) AS O'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 3 THEN atn.id END) AS LE'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 4 THEN atn.id END) AS A'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 5 THEN atn.id END) AS L'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 6 THEN atn.id END) AS E'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 7 THEN atn.id END) AS M'),
                DB::raw('COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 8 THEN atn.id END) AS H'),
                DB::raw('ROUND((
                    COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 1 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 2 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 5 THEN atn.id END)
                ) * 100 / NULLIF(COUNT(DISTINCT atn.id), 0), 2) AS percentage_withoutexcuse'),
                DB::raw('ROUND((
                    COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 1 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 2 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 5 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 6 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 7 THEN atn.id END)
                    + COUNT(DISTINCT CASE WHEN atn.attendance_feed_status_id = 8 THEN atn.id END)
                ) * 100 / NULLIF(COUNT(DISTINCT atn.id), 0), 2) AS percentage_withexcuse')
            )
            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
            ->leftJoin('groups as grp', 'pln.group_id', 'grp.id')
            ->leftJoin('students as std', 'atn.student_id', 'std.id')
            ->leftJoin('student_course_relations as scr', function ($j) {
                $j->on('scr.student_id', '=', 'std.id');
                $j->on('scr.active', DB::raw(1));
                $j->whereNull('scr.deleted_at');
                // The row Student::activeCR() gives: the lowest active, undeleted relation (as the old report).
                $j->on('scr.id', DB::raw('(SELECT MIN(scrr.id) FROM student_course_relations as scrr WHERE scrr.student_id = std.id AND scrr.active = 1 AND scrr.deleted_at IS NULL)'));
            })
            ->leftJoin('student_proposed_courses as spc', 'scr.id', 'spc.student_course_relation_id')
            ->leftJoin('statuses as sts', 'std.status_id', 'sts.id')
            ->leftJoin('student_contacts as stc', function ($sc) {
                $sc->on('stc.student_id', '=', 'std.id');
                $sc->whereNull('stc.deleted_at');
                $sc->whereRaw("stc.id = (
                    SELECT stc2.id FROM student_contacts AS stc2
                    WHERE stc2.student_id = std.id AND stc2.deleted_at IS NULL AND NULLIF(TRIM(stc2.institutional_email), '') IS NOT NULL
                    ORDER BY stc2.updated_at DESC, stc2.id DESC LIMIT 1
                )");
            })
            ->leftJoin('student_awarding_body_details as sabd', function ($j) {
                $j->on('sabd.student_id', '=', 'std.id');
                $j->on('sabd.student_course_relation_id', '=', 'scr.id');
            })
            ->leftJoin('course_creations as cc', 'scr.course_creation_id', 'cc.id')
            ->leftJoin('semesters as sm', 'cc.semester_id', 'sm.id')
            ->leftJoin('courses as cr', 'cc.course_id', 'cr.id')
            ->leftJoin('course_creation_venue as ccv', function ($ccv) {
                $ccv->on('ccv.course_creation_id', '=', 'scr.course_creation_id');
                $ccv->on('ccv.venue_id', '=', 'spc.venue_id');
            })
            ->whereIn('atn.plan_id', $planIds)
            ->whereNull('atn.deleted_at')
            ->where('pln.course_id', DB::raw('cc.course_id'));

        if (empty($studentIds)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('atn.student_id', $studentIds);
        }
        $query->groupBy('atn.student_id');
        if ($this->below !== null) {
            $query->havingRaw('(percentage_withexcuse < '.$this->below.' OR ROUND(percentage_withexcuse) = 0)');
        }

        return $query;
    }

    /** A student row for the screen. */
    public static function format(object $row): array
    {
        $counts = [];
        foreach (self::COUNTS as [$key, $alias]) {
            $counts[] = (int) ($row->{$alias} ?? 0);
        }

        return [
            'id' => (int) $row->id,
            'reg' => (string) ($row->registration_no ?? ''),
            'name' => trim(($row->first_name ?? '').' '.($row->last_name ?? '')),
            'flag' => (int) ($row->note_flag ?? 0) === 1,
            'status' => (string) ($row->status ?? ''),
            'mobile' => (string) ($row->mobile ?? ''),
            'intake' => (string) ($row->semester_name ?? ''),
            'course' => (string) ($row->course_name ?? ''),
            'group' => (string) ($row->group_name ?? ''),
            'code' => (string) ($row->slc_code ?? ''),
            'ab_ref' => (string) ($row->reference ?? ''),
            'ssn' => (string) ($row->ssn_no ?? ''),
            'n' => $counts,
            'pct' => round((float) ($row->percentage_withexcuse ?? 0), 2),
            'wo' => round((float) ($row->percentage_withoutexcuse ?? 0), 2),
        ];
    }
}
