<?php

namespace App\Services;

use App\Models\StudentCourseRelation;
use Illuminate\Support\Facades\DB;

/**
 * A student's module results counted the way the Results tab of their profile
 * counts them: how many modules carry a result, and how many are completed.
 *
 * The profile builds its table in `StudentResultController::index()` and counts
 * it in `students/live/result/index.blade.php`. This runs the same rules for a
 * whole cohort in one query, so a figure quoted on another screen reads the
 * same as the Completed / Total pills on the student's own Results tab.
 *
 *  - Scope is the student's active course: plans from the active course
 *    creation up to, but not including, the next one the student enrolled on.
 *    A student who moved on to a later course does not carry its modules back.
 *  - A module is a course module name, and counts once however many plans,
 *    attempts or re-marks carry it.
 *  - The grade that stands is the newest: the latest result on the newest plan
 *    of that module.
 *  - Completed means that grade is in COMPLETED_GRADES: Pass, Merit,
 *    Distinction or Withhold. Anything else — Referred, Unclassified, Absent, no
 *    grade — is outstanding.
 */
class StudentResultSummary
{
    /** Grades that complete a module. The Results tab reads this too. */
    public const COMPLETED_GRADES = ['P', 'M', 'D', 'W'];

    /**
     * Keyed by student id, each `['completed' => int, 'total' => int]`. A
     * student with no active course, or no results on it, is left out.
     */
    public static function forStudents(array $studentIds): array
    {
        $windows = self::courseWindows($studentIds);
        if (empty($windows)) {
            return [];
        }

        // Newest plan first, newest result first within it — the order the
        // profile walks them in, so the first row seen per module is the grade
        // that stands. A soft-deleted grade reads as no grade, as it does
        // through the model.
        $rows = DB::table('results as r')
            ->join('plans as p', 'p.id', 'r.plan_id')
            ->join('module_creations as mc', 'mc.id', 'p.module_creation_id')
            ->join('course_modules as cm', 'cm.id', 'mc.course_module_id')
            ->leftJoin('grades as g', function ($join) {
                $join->on('g.id', 'r.grade_id')->whereNull('g.deleted_at');
            })
            ->whereNull('r.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('mc.deleted_at')
            ->whereNull('cm.deleted_at')
            ->whereIn('r.student_id', array_keys($windows))
            ->orderBy('p.id', 'DESC')->orderBy('r.id', 'DESC')
            ->get(['r.student_id', 'p.course_creation_id', 'cm.name as module', 'g.code']);

        $seen = [];
        $out = [];
        foreach ($rows as $row) {
            $studentId = (int) $row->student_id;
            $window = $windows[$studentId];

            if ($row->course_creation_id === null) {
                continue;
            }

            $creationId = (int) $row->course_creation_id;
            if ($creationId < $window['from'] || ($window['to'] !== null && $creationId >= $window['to'])) {
                continue;
            }

            $module = (string) $row->module;
            if (isset($seen[$studentId][$module])) {
                continue;
            }
            $seen[$studentId][$module] = true;

            $out[$studentId] = $out[$studentId] ?? ['completed' => 0, 'total' => 0];
            $out[$studentId]['total']++;
            if (in_array(trim((string) $row->code), self::COMPLETED_GRADES, true)) {
                $out[$studentId]['completed']++;
            }
        }

        return $out;
    }

    /**
     * The course creation range each student's results are read from.
     *
     * `from` is the active relation's course creation. `to` is the next course
     * creation the student was enrolled on, when there is one after it — the
     * same bound the profile applies.
     */
    private static function courseWindows(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));
        if (empty($studentIds)) {
            return [];
        }

        $relations = StudentCourseRelation::whereIn('student_id', $studentIds)
            ->get(['student_id', 'course_creation_id', 'active'])
            ->groupBy('student_id');

        $windows = [];
        foreach ($relations as $studentId => $rows) {
            $active = $rows->firstWhere('active', 1);
            if (empty($active) || empty($active->course_creation_id)) {
                continue;
            }

            $from = (int) $active->course_creation_id;
            $creationIds = $rows->pluck('course_creation_id')->map(fn ($id) => (int) $id)->toArray();
            sort($creationIds);

            $to = null;
            if ($from < max($creationIds) && $from >= min($creationIds)) {
                $to = $creationIds[array_search($from, $creationIds) + 1];
            }

            $windows[(int) $studentId] = ['from' => $from, 'to' => $to];
        }

        return $windows;
    }
}
