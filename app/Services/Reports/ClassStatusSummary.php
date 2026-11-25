<?php

namespace App\Services\Reports;

use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Term Performance › Class Status, new version (user's class-status-report design,
 * 25 Nov 2026): what happened to one term's scheduled classes, by course and group.
 *
 * The classes are the old report's (ClassStatusByTermController::list): every class date of the
 * term's plans (soft-deleted plans and dates left out), courses and groups by id. What the
 * figures mean follows the design:
 * - scheduled = classes due so far (start passed, or already held); later ones are "still to come"
 *   (the old Schedule counted both);
 * - held = Completed; not held = scheduled − held (cancelled, unknown and never-marked classes);
 *   for a finished term these equal the old Held and Unheld;
 * - held by proxy = held classes with a proxy tutor (the old Proxy also counted the few with a proxy
 *   assigned that were not held, so the scheduled tutor's share stays right).
 */
class ClassStatusSummary
{
    /** Figures every row carries. */
    const KEYS = ['scheduled', 'held', 'proxy', 'not_held', 'cancelled', 'unknown', 'future'];

    /**
     * @return array|null ['term' => [id, name], 'totals' => figures, 'courses' => [figures + id, name,
     *                    groups => [figures + id, name]]], courses and groups lowest held % first; null
     *                    when the term does not exist
     */
    public static function build(int $termId, ?string $now = null): ?array
    {
        $term = TermDeclaration::find($termId);
        if (!$term) {
            return null;
        }
        $now = $now ?? now()->format('Y-m-d H:i:s');

        // "Still to come": starts after now and not already held.
        $future = "(TIMESTAMP(d.date, COALESCE(p.start_time, '00:00:00')) > ? AND COALESCE(d.status, '') <> 'Completed')";
        $rows = DB::table('plans_date_lists as d')
            ->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->leftJoin('courses as c', 'c.id', '=', 'p.course_id')
            ->leftJoin('groups as g', 'g.id', '=', 'p.group_id')
            ->where('p.term_declaration_id', $termId)
            ->whereNull('p.deleted_at')
            ->whereNull('d.deleted_at')
            ->selectRaw("p.course_id, c.name AS course_name, p.group_id, g.name AS group_name,
                COUNT(*) AS total,
                SUM(CASE WHEN {$future} THEN 1 ELSE 0 END) AS future,
                SUM(CASE WHEN d.status = 'Completed' THEN 1 ELSE 0 END) AS held,
                SUM(CASE WHEN d.status = 'Completed' AND d.proxy_tutor_id IS NOT NULL THEN 1 ELSE 0 END) AS proxy,
                SUM(CASE WHEN d.status = 'Canceled' AND NOT {$future} THEN 1 ELSE 0 END) AS cancelled,
                SUM(CASE WHEN d.status = 'Unknown' AND NOT {$future} THEN 1 ELSE 0 END) AS unknown", [$now, $now, $now])
            ->groupBy('p.course_id', 'p.group_id')
            ->get();

        $courses = [];
        foreach ($rows as $row) {
            $figures = [
                'scheduled' => (int) $row->total - (int) $row->future,
                'held' => (int) $row->held,
                'proxy' => (int) $row->proxy,
                'cancelled' => (int) $row->cancelled,
                'unknown' => (int) $row->unknown,
                'future' => (int) $row->future,
            ];
            $figures['not_held'] = max(0, $figures['scheduled'] - $figures['held']);

            $courseId = (int) $row->course_id;
            $courses[$courseId] ??= ['id' => $courseId, 'name' => $row->course_name ?: 'Unknown course', 'groups' => []] + self::zero();
            foreach (self::KEYS as $key) {
                $courses[$courseId][$key] += $figures[$key];
            }
            $courses[$courseId]['groups'][] = ['id' => (int) $row->group_id, 'name' => $row->group_name ?: 'No group'] + $figures;
        }

        $totals = self::zero();
        foreach ($courses as &$course) {
            foreach (self::KEYS as $key) {
                $totals[$key] += $course[$key];
            }
            usort($course['groups'], [self::class, 'lowestFirst']);
        }
        unset($course);
        $courses = array_values($courses);
        usort($courses, [self::class, 'lowestFirst']);

        return [
            'term' => ['id' => (int) $term->id, 'name' => (string) $term->name],
            'totals' => $totals,
            'courses' => $courses,
        ];
    }

    /** Held % of a row; null when nothing was due. */
    public static function rate(array $row): ?float
    {
        return $row['scheduled'] > 0 ? $row['held'] * 100 / $row['scheduled'] : null;
    }

    /** Lowest held % first (nothing due last), then by name. */
    private static function lowestFirst(array $a, array $b): int
    {
        $ra = self::rate($a);
        $rb = self::rate($b);
        if ($ra === null || $rb === null) {
            return ($ra === null) <=> ($rb === null) ?: strnatcasecmp($a['name'], $b['name']);
        }

        return $ra <=> $rb ?: strnatcasecmp($a['name'], $b['name']);
    }

    private static function zero(): array
    {
        return array_fill_keys(self::KEYS, 0);
    }
}
