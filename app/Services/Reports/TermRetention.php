<?php

namespace App\Services\Reports;

use App\Models\Assign;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\Plan;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Term Performance › Retention, new version (user's term-retention-report design,
 * 25 Nov 2026): for each attendance term, the intakes studying in it and how many of their
 * registered students stayed.
 *
 * Who counts is the old report's (TermRetentionReportController::refineResult), done with a few
 * grouped queries per term:
 * - the intakes are the semesters of the course creations that have an instance in the term;
 *   every creation of those semesters is looked at;
 * - Total students: a creation's students with an active course relation who are assigned to one
 *   of the term's plans; when two share a registration number, only the newest record;
 * - Registered: those with an awarding body reference on a course relation of that creation.
 * Fixed (old figures were wrong): the old "Registered" was registered minus dropped out for
 * creations with dropouts and the previous creation's list again for those without (so it could
 * exceed the total), and its rate took the dropouts off twice. Here Registered is every
 * registered student, Stayed = Registered − Dropped out, rate = Stayed ÷ Registered.
 *
 * Dropped out, changed after analysing the old rule (25 Nov 2026; the user asked for the call to
 * be made on the evidence). The old report counted anyone whose leaving status was set at any
 * time in the ~2-year course window, so a past term kept growing and one student was a dropout
 * in every term they studied (70 of 2026 January's 88 were also 2025 September's). Now:
 * - a student has left when their current status is one of DROPPED; their latest term status
 *   record of it (not deleted) places it: the term named on it (a term overlapping this one =
 *   this term), or its date when it names none;
 * - left in this term = Dropped out (once, in one term); left in a later term = Stayed here;
 * - left in an earlier term, or within 28 days of the course start (never really started) = not
 *   counted in this term at all ("left before");
 * - the date does not decide when the record names a term: leaving statuses are set a median
 *   10 days after the term ends (final-term students suspended after their course end date and
 *   recorded against that term are that term's dropouts, which the old course-end cut missed);
 * - SLC Suspended counts (only while it is still the status: returners are not counted);
 *   SLC HNC COMPLETED (an exit award) does not; status 50 (no such status) is gone.
 */
class TermRetention
{
    /**
     * Statuses that mean the student has left: Discarded, Left, SLC Discarded, SLC Left,
     * SLC Suspended, SLC Terminated, Terminated, Suspended, SLC Suspended(non-progression),
     * SLC Discarded(non-progression), Suspended(non-progression). The old list also had 42
     * SLC HNC COMPLETED (an exit award) and 50 (no such status).
     */
    const DROPPED = [14, 17, 22, 27, 30, 31, 33, 36, 43, 45, 47];

    /** One colour per term, newest first (the design's teal, blue, purple, then more). */
    const COLOURS = ['#0e5a54', '#1668a8', '#7a4fb5', '#a3456b', '#6b7a1f', '#4a5b6b', '#8a5a2b', '#2f8f84'];

    /** List keys: what a count of the table lists. */
    const KEYS = ['total', 'registered', 'stayed', 'dropped'];

    /**
     * @param  int[]  $termIds
     * @return array ['terms' => [[id, name, colour, total, registered, stayed, dropped, rate,
     *               intakes => [[id, name, study, total, registered, stayed, dropped, rate], ...]], ...]]
     *               newest term first; rate is null with nobody registered; study (1 = the
     *               intake's first term) is null when the intake's name is not "YYYY Month"
     */
    public static function build(array $termIds): array
    {
        $terms = TermDeclaration::whereIn('id', array_map('intval', $termIds))->get()
            ->sortByDesc(fn ($t) => ($t->getRawOriginal('start_date') ?: '0000-00-00').sprintf('%010d', $t->id))->values();

        $out = [];
        foreach ($terms as $i => $term) {
            $row = ['id' => (int) $term->id, 'name' => (string) $term->name, 'colour' => self::COLOURS[$i % count(self::COLOURS)], 'intakes' => []];
            foreach (self::outcomes($term) as $semesterId => $intake) {
                $figures = self::figures($intake['students']);
                if ($figures['total'] > 0) {
                    $row['intakes'][] = ['id' => $semesterId, 'name' => $intake['name'], 'study' => $intake['study']] + $figures;
                }
            }
            foreach (['total', 'registered', 'stayed', 'dropped', 'left_before'] as $k) {
                $row[$k] = array_sum(array_column($row['intakes'], $k));
            }
            $row['rate'] = self::rate($row['stayed'], $row['registered']);
            $out[] = $row;
        }

        return ['terms' => $out];
    }

    /**
     * One term's intakes, newest first, with each student's outcome:
     * [semester id => ['name', 'study', 'students' => [student id => ['outcome' => none|stayed|dropped|gone,
     * 'creation' => course creation id, 'left' => date the leaving status changed or null,
     * 'left_status' => that status id or null]]]]. "gone" = had left before the term: not counted.
     */
    public static function outcomes(TermDeclaration $term): array
    {
        $planIds = Plan::where('term_declaration_id', $term->id)->pluck('id')->all();
        $assigned = empty($planIds) ? [] : array_flip(Assign::whereIn('plan_id', $planIds)->distinct()->pluck('student_id')->all());

        $instanceIds = InstanceTerm::where('term_declaration_id', $term->id)->pluck('course_creation_instance_id')->unique()->all();
        $inTerm = CourseCreationInstance::whereIn('id', $instanceIds)->pluck('course_creation_id')->unique()->all();
        $semesterIds = CourseCreation::whereIn('id', $inTerm)->pluck('semester_id')->unique()->filter()->all();
        if (empty($semesterIds)) {
            return [];
        }
        $semesterNames = DB::table('semesters')->whereIn('id', $semesterIds)->pluck('name', 'id')->all();
        $creations = CourseCreation::with('available')->whereIn('semester_id', $semesterIds)->get(['id', 'semester_id', 'course_id']);

        // Students per creation: active course relation and assigned in the term.
        $byCreation = [];
        StudentCourseRelation::whereIn('course_creation_id', $creations->pluck('id'))->where('active', 1)
            ->get(['student_id', 'course_creation_id'])
            ->each(function ($r) use (&$byCreation, $assigned) {
                if (isset($assigned[$r->student_id])) {
                    $byCreation[$r->course_creation_id][$r->student_id] = true;
                }
            });
        $candidates = [];
        foreach ($byCreation as $ids) {
            $candidates += $ids;
        }
        $regNo = [];
        foreach (array_chunk(array_keys($candidates), 5000) as $chunk) {
            $regNo += DB::table('students')->whereIn('id', $chunk)->pluck('registration_no', 'id')->all();
        }

        // Registered: an awarding body reference on a course relation of the creation.
        $registered = [];
        foreach (array_chunk(array_keys($candidates), 5000) as $chunk) {
            StudentAwardingBodyDetails::query()
                ->join('student_course_relations as scr', 'scr.id', '=', 'student_awarding_body_details.student_course_relation_id')
                ->whereNull('scr.deleted_at')
                ->whereIn('scr.course_creation_id', array_keys($byCreation))
                ->whereIn('student_awarding_body_details.student_id', $chunk)
                ->whereNotNull('student_awarding_body_details.reference')
                ->get(['student_awarding_body_details.student_id', 'scr.course_creation_id'])
                ->each(function ($r) use (&$registered) {
                    $registered[$r->course_creation_id][$r->student_id] = true;
                });
        }

        // Who has left: the current status is a leaving one; its latest term status record (the
        // highest id, not deleted) says when.
        $leaving = [];
        foreach (array_chunk(array_keys($candidates), 5000) as $chunk) {
            DB::table('students as std')
                ->join('student_attendance_term_statuses as sats', function ($j) {
                    $j->on('std.id', 'sats.student_id');
                    $j->on('std.status_id', 'sats.status_id');
                })
                ->whereIn('std.id', $chunk)->whereIn('sats.status_id', self::DROPPED)->whereNull('sats.deleted_at')
                ->orderBy('sats.id')
                ->get(['std.id', 'sats.status_id', 'sats.status_change_date', 'sats.term_declaration_id'])
                ->each(function ($r) use (&$leaving) {
                    $date = $r->status_change_date ? substr((string) $r->status_change_date, 0, 10) : null;
                    $leaving[$r->id] = [
                        'date' => $date !== null && $date > '1971-01-01' ? $date : null,
                        'status' => (int) $r->status_id,
                        'term' => $r->term_declaration_id ? (int) $r->term_declaration_id : null,
                    ];
                });
        }
        $termDates = self::termDates();
        $span = [$term->getRawOriginal('start_date'), $term->getRawOriginal('end_date')];

        $intakes = [];
        foreach ($creations as $creation) {
            $ids = $byCreation[$creation->id] ?? [];
            if (empty($ids)) {
                continue;
            }
            // Two records with one registration number: only the newest (highest id) counts. As
            // the old report, an id with no students row is left out.
            krsort($ids);
            $seen = [];
            foreach (array_keys($ids) as $id) {
                if (!array_key_exists($id, $regNo)) {
                    unset($ids[$id]);
                    continue;
                }
                $reg = trim((string) ($regNo[$id] ?? ''));
                if ($reg !== '' && isset($seen[$reg])) {
                    unset($ids[$id]);
                } elseif ($reg !== '') {
                    $seen[$reg] = true;
                }
            }

            // Leaving before this date (28 days after the course start; the d-m-Y accessor to
            // Y-m-d) = never really started.
            $start = (string) ($creation->available->course_start_date ?? '');
            $from = $start !== '' ? date('Y-m-d', strtotime(date('Y-m-d', strtotime($start)).' + 28 days')) : '';

            $semesterId = (int) $creation->semester_id;
            if (!isset($intakes[$semesterId])) {
                $name = (string) ($semesterNames[$semesterId] ?? 'Unknown intake');
                $intakes[$semesterId] = ['name' => $name, 'study' => self::study($name, $term->getRawOriginal('start_date')), 'students' => []];
            }
            foreach (array_keys($ids) as $id) {
                $record = $leaving[$id] ?? null;
                $place = self::place($record, $span, $from, $termDates);
                if ($place === 'before') {
                    $outcome = 'gone';
                } elseif (isset($registered[$creation->id][$id])) {
                    $outcome = $place === 'here' ? 'dropped' : 'stayed';
                } else {
                    $outcome = 'none';
                }
                $left = $place === 'here' ? $record['date'] : null;
                $leftStatus = $place === 'here' ? $record['status'] : null;
                $current = $intakes[$semesterId]['students'][$id] ?? null;
                // Seen on another creation of the intake: the stronger outcome wins.
                if ($current === null || self::weight($outcome) > self::weight($current['outcome'])) {
                    $intakes[$semesterId]['students'][$id] = ['outcome' => $outcome, 'creation' => (int) $creation->id, 'left' => $left, 'left_status' => $leftStatus];
                }
            }
        }

        // Newest intake first: by the month in the name, then by id.
        uksort($intakes, function ($a, $b) use ($intakes) {
            $ka = self::month($intakes[$a]['name']) ?? -1;
            $kb = self::month($intakes[$b]['name']) ?? -1;

            return $kb <=> $ka ?: $b <=> $a;
        });

        return $intakes;
    }

    /**
     * Where a student's leaving falls against the term ($span = [start, end]): 'here' (dropped
     * out in it), 'before' (had left before it, or within 28 days of the course start: not
     * counted in it) or null (studying in it: never left, or left in a later term). The term
     * named on the record decides; its date only when it names none.
     */
    public static function place(?array $record, array $span, string $from, array $termDates): ?string
    {
        if ($record === null) {
            return null;
        }
        $date = $record['date'];
        if ($date !== null && $from !== '' && $date < $from) {
            return 'before';
        }
        $named = $record['term'] !== null ? ($termDates[$record['term']] ?? null) : null;
        if ($named && $named[0] && $named[1] && $span[0] && $span[1]) {
            if ($named[0] <= $span[1] && $named[1] >= $span[0]) {
                return 'here';
            }

            return $named[1] < $span[0] ? 'before' : null;
        }
        if ($date === null || !$span[0] || !$span[1]) {
            return null;
        }

        return $date < $span[0] ? 'before' : ($date <= $span[1] ? 'here' : null);
    }

    /** Every term's [start, end] (Y-m-d), deleted ones too: a status record may name one. */
    private static function termDates(): array
    {
        static $dates = null;

        return $dates ??= DB::table('term_declarations')->get(['id', 'start_date', 'end_date'])
            ->mapWithKeys(fn ($t) => [(int) $t->id => [$t->start_date ? substr((string) $t->start_date, 0, 10) : null, $t->end_date ? substr((string) $t->end_date, 0, 10) : null]])->all();
    }

    /**
     * Total, registered, stayed and dropped out from an intake's outcomes; rate = stayed ÷
     * registered; left_before = students not counted because they had already left.
     */
    public static function figures(array $students): array
    {
        $count = array_count_values(array_column($students, 'outcome'));
        $stayed = $count['stayed'] ?? 0;
        $dropped = $count['dropped'] ?? 0;

        return [
            'total' => count($students) - ($count['gone'] ?? 0),
            'left_before' => $count['gone'] ?? 0,
            'registered' => $stayed + $dropped,
            'stayed' => $stayed,
            'dropped' => $dropped,
            'rate' => self::rate($stayed, $stayed + $dropped),
        ];
    }

    /** Whether a student's outcome is in a list (total = everyone). */
    public static function inList(string $key, string $outcome): bool
    {
        return match ($key) {
            'registered' => $outcome !== 'none',
            'stayed' => $outcome === 'stayed',
            'dropped' => $outcome === 'dropped',
            default => $outcome !== 'gone',
        } && $outcome !== 'gone';
    }

    public static function rate(int $stayed, int $registered): ?float
    {
        return $registered > 0 ? round($stayed * 100 / $registered, 4) : null;
    }

    /**
     * Term of study: 1 for an intake in its first term. Terms start every four months (January,
     * May, September), so it is the months from the intake's month to the term's start, in fours.
     */
    public static function study(string $intake, $termStart): ?int
    {
        $from = self::month($intake);
        if ($from === null || empty($termStart)) {
            return null;
        }
        $to = (int) date('Y', strtotime($termStart)) * 12 + (int) date('n', strtotime($termStart)) - 1;
        $months = $to - $from;

        return $months < 0 ? null : (int) round($months / 4) + 1;
    }

    /** "2025 September" → months since year 0 (year × 12 + month − 1); null when not "YYYY Month". */
    private static function month(string $name): ?int
    {
        if (!preg_match('/^\s*(\d{4})\s+([A-Za-z]+)/', $name, $m)) {
            return null;
        }
        $time = strtotime('1 '.$m[2].' '.$m[1]);

        return $time === false ? null : (int) $m[1] * 12 + (int) date('n', $time) - 1;
    }

    private static function weight(string $outcome): int
    {
        return ['none' => 0, 'stayed' => 1, 'dropped' => 2, 'gone' => 3][$outcome];
    }

    /**
     * The chart's lines: each term with intakes, its retention by term of study (intakes in the
     * same term of study together). [['name', 'colour', 'points' => [study => rate]], ...]
     */
    public static function series(array $terms): array
    {
        $lines = [];
        foreach ($terms as $t) {
            $sum = [];
            foreach ($t['intakes'] as $in) {
                if ($in['study'] !== null && $in['registered'] > 0) {
                    $sum[$in['study']]['s'] = ($sum[$in['study']]['s'] ?? 0) + $in['stayed'];
                    $sum[$in['study']]['r'] = ($sum[$in['study']]['r'] ?? 0) + $in['registered'];
                }
            }
            if (!empty($sum)) {
                ksort($sum);
                $lines[] = ['name' => $t['name'], 'colour' => $t['colour'], 'points' => array_map(fn ($p) => self::rate($p['s'], $p['r']), $sum)];
            }
        }

        return $lines;
    }

    /**
     * The sentence under the chart: intakes in their first three terms against the rest, with the
     * bold lead only when one group is wholly below the other. Null without both groups.
     *
     * @return array|null ['lead' => string|null, 'text' => string]
     */
    public static function takeaway(array $series): ?array
    {
        $early = $late = [];
        foreach ($series as $s) {
            foreach ($s['points'] as $study => $rate) {
                if ($study <= 3) {
                    $early[] = $rate;
                } else {
                    $late[] = $rate;
                }
            }
        }
        if (empty($early) || empty($late)) {
            return null;
        }
        $f = fn ($v) => number_format($v, 1).'%';
        $range = fn ($a, $b) => $f($a) === $f($b) ? $f($a) : $f($a).' to '.$f($b);
        [$e0, $e1, $l0, $l1] = [min($early), max($early), min($late), max($late)];
        if ($e1 < $l0) {
            return ['lead' => 'Newer intakes lose more students.', 'text' => 'Intakes in their first three terms kept '.$range($e0, $e1).' of students; intakes in their 4th term or later kept '.$f($l0).' or more.'];
        }
        if ($e0 > $l1) {
            return ['lead' => 'Older intakes lose more students.', 'text' => 'Intakes in their first three terms kept '.$f($e0).' or more of students; intakes in their 4th term or later kept '.$range($l0, $l1).'.'];
        }

        return ['lead' => null, 'text' => 'Intakes in their first three terms kept '.$range($e0, $e1).' of students; intakes in their 4th term or later kept '.$range($l0, $l1).'.'];
    }

    /** "1st", "2nd", "3rd", "4th" … */
    public static function ordinal(int $n): string
    {
        $s = ($n % 100 >= 11 && $n % 100 <= 13) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');

        return $n.$s;
    }
}
