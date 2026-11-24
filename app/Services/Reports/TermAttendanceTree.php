<?php

namespace App\Services\Reports;

use App\Models\Assign;
use App\Models\Plan;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Term Performance › Attendance Rates, new version (user, 24 Nov 2026): one attendance
 * semester as a tree — term → course → group → module — each with its term rate and a rate per
 * week, for the drill-down screen (term-performance/attendance/new.blade.php).
 *
 * Counting is the old report's (TermPerformance\TermAttendancePerformanceReportController):
 * - attendance in the term's plans, by students assigned to those plans, not deleted, with a
 *   current student status in STATUSES;
 * - rate = (Present + Online + Late + Excuse + Medical + Exceptional) × 100 / classes;
 * - groups go by name within a course (the old course view groups by name), modules by module
 *   creation within a group;
 * - weeks are 7-day windows from the term's start date, the last one cut at its end date;
 * - the old trend pages narrow the students per level, and so does this: a group's weeks count
 *   only students assigned to that course in the term (old course trend), a module's weeks only
 *   students assigned to that group (old group trend). Term and course weeks, and every term
 *   rate, count everyone assigned in the term.
 * One grouped query (per student, so the narrowing can be applied here) replaces the old
 * per-course, per-group and per-week queries.
 */
class TermAttendanceTree
{
    /** Student statuses the old report counts. */
    const STATUSES = [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50];

    /** Feed statuses counted as attended: P, O, L, E, M, H. */
    const ATTENDED = [1, 2, 5, 6, 7, 8];

    /**
     * @return array|null  ['term' => [...], 'weeks' => ['Y-m-d', ...], 'tree' => node] or null
     *                     when the term does not exist. A node is
     *                     ['id', 'name', 'rate', 'classes', 'weekly' => [rate|null per week], 'children' => [...]]
     */
    public static function build(int $termId): ?array
    {
        $term = TermDeclaration::find($termId);
        if (!$term) {
            return null;
        }
        $start = !empty($term->start_date) ? date('Y-m-d', strtotime($term->start_date)) : null;
        $end = !empty($term->end_date) ? date('Y-m-d', strtotime($term->end_date)) : null;
        $weeks = [];
        if ($start && $end) {
            for ($day = $start; $day <= $end; $day = date('Y-m-d', strtotime('+7 day', strtotime($day)))) {
                $weeks[] = $day;
            }
        }

        $root = self::node('term', $term->name, count($weeks));
        $planIds = Plan::where('term_declaration_id', $termId)->pluck('id')->unique()->all();
        $studentIds = !empty($planIds) ? Assign::whereIn('plan_id', $planIds)->pluck('student_id')->unique()->all() : [];

        if (!empty($planIds) && !empty($studentIds)) {
            $attended = implode(',', self::ATTENDED);
            [$weekSql, $bindings] = $weeks
                ? ['CASE WHEN DATE(atn.attendance_date) BETWEEN ? AND ? THEN FLOOR(DATEDIFF(DATE(atn.attendance_date), ?) / 7) ELSE -1 END', [$start, $end, $start]]
                : ['-1', []];

            // Who is assigned where in the term: [student:course] and [student:course:group name].
            $inCourse = $inGroup = [];
            DB::table('assigns as a')
                ->join('plans as p', 'p.id', '=', 'a.plan_id')
                ->leftJoin('groups as g', 'g.id', '=', 'p.group_id')
                ->where('p.term_declaration_id', $termId)
                ->select('a.student_id', 'p.course_id', 'g.name as group_name')->distinct()
                ->get()->each(function ($a) use (&$inCourse, &$inGroup) {
                    $inCourse[$a->student_id.':'.$a->course_id] = true;
                    $inGroup[$a->student_id.':'.$a->course_id.':'.$a->group_name] = true;
                });

            $rows = DB::table('attendances as atn')
                ->selectRaw("atn.student_id, pln.course_id, cr.name AS course_name, gr.name AS group_name, pln.module_creation_id, mc.module_name,
                    {$weekSql} AS wk, COUNT(*) AS classes,
                    SUM(CASE WHEN atn.attendance_feed_status_id IN ({$attended}) THEN 1 ELSE 0 END) AS attended", $bindings)
                ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                ->leftJoin('courses as cr', 'pln.course_id', 'cr.id')
                ->leftJoin('groups as gr', 'pln.group_id', 'gr.id')
                ->leftJoin('module_creations as mc', 'pln.module_creation_id', 'mc.id')
                ->leftJoin('students as std', 'atn.student_id', 'std.id')
                ->whereIn('atn.plan_id', $planIds)
                ->whereIn('atn.student_id', $studentIds)
                ->whereNull('atn.deleted_at')
                ->whereIn('std.status_id', self::STATUSES)
                ->groupBy('atn.student_id', 'pln.course_id', 'gr.name', 'pln.module_creation_id', 'wk')
                ->get();

            $n = count($weeks);
            foreach ($rows as $row) {
                $courseKey = 'c'.(int) $row->course_id;
                $groupName = (string) ($row->group_name ?? '');
                $groupKey = 'g'.md5($groupName);
                $moduleKey = 'm'.(int) $row->module_creation_id;

                $course = &$root['children'][$courseKey];
                $course ??= self::node($courseKey, $row->course_name ?: 'Unknown course', $n);
                $group = &$course['children'][$groupKey];
                $group ??= self::node($groupKey, $groupName !== '' ? $groupName : 'No group', $n);
                $module = &$group['children'][$moduleKey];
                $module ??= self::node($moduleKey, $row->module_name ?: 'Unknown module', $n);

                $wk = (int) $row->wk;
                $memberOfCourse = isset($inCourse[$row->student_id.':'.$row->course_id]);
                $memberOfGroup = isset($inGroup[$row->student_id.':'.$row->course_id.':'.$row->group_name]);
                self::add($root, $row, $wk, $n);
                self::add($course, $row, $wk, $n);
                self::add($group, $row, $memberOfCourse ? $wk : -1, $n);
                self::add($module, $row, $memberOfGroup ? $wk : -1, $n);
                unset($course, $group, $module);
            }
        }

        return [
            'term' => ['id' => (int) $term->id, 'name' => (string) $term->name, 'start' => $start, 'end' => $end],
            'weeks' => $weeks,
            'tree' => self::finish($root),
        ];
    }

    private static function node(string $id, string $name, int $weeks): array
    {
        return [
            'id' => $id, 'name' => $name, 'classes' => 0, 'attended' => 0,
            'wc' => array_fill(0, $weeks, 0), 'wa' => array_fill(0, $weeks, 0), 'children' => [],
        ];
    }

    /** Adds one grouped row's counts to a node (and to its week, when the row falls in one). */
    private static function add(array &$node, object $row, int $wk, int $weeks): void
    {
        $node['classes'] += (int) $row->classes;
        $node['attended'] += (int) $row->attended;
        if ($wk >= 0 && $wk < $weeks) {
            $node['wc'][$wk] += (int) $row->classes;
            $node['wa'][$wk] += (int) $row->attended;
        }
    }

    /**
     * Rates from the counts; the working counts dropped. Order as the old report: courses by
     * course id (its course list), groups and modules A–Z (its course and group views).
     */
    private static function finish(array $node): array
    {
        $children = array_map(fn ($child) => self::finish($child), array_values($node['children']));
        usort($children, $node['id'] === 'term'
            ? fn ($a, $b) => (int) substr($a['id'], 1) <=> (int) substr($b['id'], 1)
            : fn ($a, $b) => strnatcasecmp($a['name'], $b['name']));

        return [
            'id' => $node['id'],
            'name' => $node['name'],
            'rate' => $node['classes'] > 0 ? round($node['attended'] * 100 / $node['classes'], 2) : null,
            'classes' => $node['classes'],
            'attended' => $node['attended'],
            'weekly' => array_map(fn ($c, $a) => $c > 0 ? round($a * 100 / $c, 2) : null, $node['wc'], $node['wa']),
            'children' => $children,
        ];
    }
}
