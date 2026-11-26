<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Reports › SLC Reports › SLC Record Report, new version (user's slc-record-report web and PDF
 * designs, 25 Nov 2026): for each intake, how far its students have got from registering at the
 * college to SLC confirming attendance, for LCC's own courses and for franchise courses (the old
 * screen's two tables; the design's "Set 1" and "Set 2").
 *
 * The counting is the old report's (SlcRecordReportController::refineResult), done with a few
 * grouped queries; every figure is a set of student ids so its students can be listed:
 * - a group is the course creations of the intake whose course is franchise_course No (LCC) or
 *   Yes (franchise), or one creation; its students have an active course relation in it;
 * - lcc: their current status is one of LCC_STATUSES ("initial LCC SMS registration, excluding
 *   discarded"); withdrawn, intermittent and self: their status is in those lists;
 * - awb: an awarding body reference (not blank) on a course relation of the group;
 * - slc: a year 1 SLC registration, status Yes or RCNR, on a course relation of the group;
 * - att: a year 1 SLC attendance confirmation coded A on a course relation of the group;
 * - gap (new, the design's "not yet SLC registered"): lcc students who are not slc.
 * Soft-deleted rows are left out everywhere, as the old models do.
 */
class SlcRecord
{
    const LCC_STATUSES = [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 15];
    const WITHDRAWN = [30, 31, 43];
    const INTERMITTENT = [27, 42];
    const SELF_FUNDED = [15];

    /** The figures in order, with the design's names. */
    const KEYS = [
        'lcc' => 'Registered at LCC',
        'awb' => 'Registered with awarding body',
        'slc' => 'SLC registered',
        'att' => 'SLC attendance confirmed',
        'withdrawn' => 'SLC withdrawn',
        'intermittent' => 'Withdrawn or intermittent',
        'self' => 'Self funded',
        'gap' => 'Not yet SLC registered',
    ];

    const SETS = ['lcc' => 'LCC courses', 'franchise' => 'Franchise courses'];

    /**
     * Student ids behind every figure:
     * [semester id => ['name', 'sets' => [lcc|franchise => ['all' => [key => ids],
     *  'courses' => [creation id => ['name', 'ids' => [key => ids]]]]]]], newest intake first.
     */
    public static function sets(array $semesterIds): array
    {
        $semesterIds = array_values(array_unique(array_map('intval', $semesterIds)));
        $semesters = DB::table('semesters')->whereIn('id', $semesterIds)->whereNull('deleted_at')->pluck('name', 'id')->all();
        if (empty($semesters)) {
            return [];
        }

        // The intakes' course creations, LCC or franchise by their course.
        $creations = DB::table('course_creations as cc')
            ->join('courses as c', 'c.id', '=', 'cc.course_id')
            ->whereIn('cc.semester_id', array_keys($semesters))->whereNull('cc.deleted_at')->whereNull('c.deleted_at')
            ->whereIn('c.franchise_course', ['No', 'Yes'])
            ->orderBy('cc.id')
            ->get(['cc.id', 'cc.semester_id', 'c.name', 'c.franchise_course']);
        $creationIds = $creations->pluck('id')->all();

        $active = [];
        $students = [];
        if (!empty($creationIds)) {
            DB::table('student_course_relations')->whereIn('course_creation_id', $creationIds)->where('active', 1)->whereNull('deleted_at')
                ->get(['student_id', 'course_creation_id'])
                ->each(function ($r) use (&$active, &$students) {
                    $active[$r->course_creation_id][$r->student_id] = true;
                    $students[$r->student_id] = true;
                });
        }
        $ids = array_keys($students);

        // Current status of the students who are not deleted.
        $status = [];
        // The creations each student has an awarding body reference, SLC registration or attendance on.
        $awb = $slc = $att = [];
        foreach (array_chunk($ids, 5000) as $chunk) {
            DB::table('students')->whereIn('id', $chunk)->whereNull('deleted_at')->pluck('status_id', 'id')
                ->each(function ($s, $id) use (&$status) {
                    $status[$id] = (int) $s;
                });
            $on = fn ($table, $alias) => DB::table($table.' as '.$alias)
                ->join('student_course_relations as scr', 'scr.id', '=', $alias.'.student_course_relation_id')
                ->whereNull($alias.'.deleted_at')->whereNull('scr.deleted_at')
                ->whereIn($alias.'.student_id', $chunk)->whereIn('scr.course_creation_id', $creationIds);
            $on('student_awarding_body_details', 'b')->whereNotNull('b.reference')->where('b.reference', '!=', '')
                ->get(['b.student_id', 'scr.course_creation_id'])
                ->each(function ($r) use (&$awb) {
                    $awb[$r->student_id][$r->course_creation_id] = true;
                });
            $on('slc_registrations', 'g')->where('g.registration_year', 1)->whereIn('g.slc_registration_status_id', [1, 3])
                ->get(['g.student_id', 'scr.course_creation_id'])
                ->each(function ($r) use (&$slc) {
                    $slc[$r->student_id][$r->course_creation_id] = true;
                });
            $on('slc_attendances', 'a')->where('a.attendance_year', 1)->where('a.attendance_code_id', 1)
                ->get(['a.student_id', 'scr.course_creation_id'])
                ->each(function ($r) use (&$att) {
                    $att[$r->student_id][$r->course_creation_id] = true;
                });
        }

        // The figures of a group of creations.
        $figures = function (array $group) use ($active, $status, $awb, $slc, $att) {
            $in = array_flip($group);
            $members = [];
            foreach ($group as $c) {
                $members += $active[$c] ?? [];
            }
            $out = array_fill_keys(array_keys(self::KEYS), []);
            $hits = fn ($map, $s) => !empty(array_intersect_key($map[$s] ?? [], $in));
            foreach (array_keys($members) as $s) {
                $st = $status[$s] ?? null;
                if ($st !== null && in_array($st, self::LCC_STATUSES, true)) {
                    $out['lcc'][] = $s;
                }
                if ($hits($awb, $s)) {
                    $out['awb'][] = $s;
                }
                if ($hits($slc, $s)) {
                    $out['slc'][] = $s;
                }
                if ($hits($att, $s)) {
                    $out['att'][] = $s;
                }
                if ($st !== null && in_array($st, self::WITHDRAWN, true)) {
                    $out['withdrawn'][] = $s;
                }
                if ($st !== null && in_array($st, self::INTERMITTENT, true)) {
                    $out['intermittent'][] = $s;
                }
                if ($st !== null && in_array($st, self::SELF_FUNDED, true)) {
                    $out['self'][] = $s;
                }
            }
            $out['gap'] = array_values(array_diff($out['lcc'], $out['slc']));

            return $out;
        };

        $out = [];
        foreach (self::order($semesters) as $semesterId => $name) {
            $row = ['name' => $name, 'sets' => []];
            foreach (['lcc' => 'No', 'franchise' => 'Yes'] as $set => $flag) {
                $group = $creations->where('semester_id', $semesterId)->where('franchise_course', $flag);
                $courses = [];
                foreach ($group as $c) {
                    $courses[(int) $c->id] = ['name' => (string) $c->name, 'ids' => $figures([(int) $c->id])];
                }
                $row['sets'][$set] = ['all' => $figures($group->pluck('id')->map(fn ($id) => (int) $id)->all()), 'courses' => $courses];
            }
            $out[$semesterId] = $row;
        }

        return $out;
    }

    /** The screen's figures: counts instead of ids. */
    public static function build(array $semesterIds): array
    {
        $count = fn (array $ids) => array_map(fn ($key) => count($ids[$key]), array_keys(self::KEYS));
        $intakes = [];
        foreach (self::sets($semesterIds) as $id => $row) {
            $sets = [];
            foreach ($row['sets'] as $set => $data) {
                $sets[$set] = [
                    'v' => $count($data['all']),
                    'courses' => array_values(array_map(fn ($cid, $c) => ['id' => $cid, 'name' => $c['name'], 'v' => $count($c['ids'])], array_keys($data['courses']), $data['courses'])),
                ];
            }
            $intakes[] = ['id' => $id, 'name' => $row['name'], 'sets' => $sets];
        }

        return ['keys' => array_keys(self::KEYS), 'names' => array_values(self::KEYS), 'sets' => self::SETS, 'intakes' => $intakes];
    }

    /** The design writes "2026 September" as "September 2026". */
    public static function label(string $name): string
    {
        return preg_match('/^(\d{4})\s+([A-Za-z]+)(.*)$/', trim($name), $m) ? $m[2].' '.$m[1].$m[3] : $name;
    }

    /** Newest intake first, by the month in the name ("2026 May"), then id. */
    public static function order(array $semesters): array
    {
        $month = function ($name) {
            if (!preg_match('/^\s*(\d{4})\s+([A-Za-z]+)/', (string) $name, $m) || ($t = strtotime('1 '.$m[2].' '.$m[1])) === false) {
                return -1;
            }

            return (int) $m[1] * 12 + (int) date('n', $t);
        };
        uksort($semesters, fn ($a, $b) => $month($semesters[$b]) <=> $month($semesters[$a]) ?: $b <=> $a);

        return $semesters;
    }

    /** Students as list rows: [[id, registration no, name, semester, course, status], ...], A–Z. */
    public static function students(array $ids): array
    {
        $rows = [];
        foreach (array_chunk(array_values(array_unique($ids)), 2000) as $chunk) {
            DB::table('students as s')
                ->leftJoin('statuses as st', 'st.id', '=', 's.status_id')
                ->leftJoin('student_course_relations as scr', function ($j) {
                    $j->on('scr.student_id', '=', 's.id')->where('scr.active', 1)->whereNull('scr.deleted_at');
                })
                ->leftJoin('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
                ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')
                ->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
                ->whereIn('s.id', $chunk)
                ->get(['s.id', 's.registration_no', 's.application_no', 's.first_name', 's.last_name', 'sm.name as semester', 'c.name as course', 'st.name as status'])
                ->each(function ($r) use (&$rows) {
                    $rows[$r->id] ??= [(int) $r->id, (string) ($r->registration_no ?: $r->application_no), trim($r->first_name.' '.$r->last_name), (string) $r->semester, (string) $r->course, (string) $r->status];
                });
        }
        $rows = array_values($rows);
        usort($rows, fn ($a, $b) => strnatcasecmp($a[2], $b[2]) ?: $a[0] <=> $b[0]);

        return $rows;
    }
}
