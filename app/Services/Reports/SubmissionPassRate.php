<?php

namespace App\Services\Reports;

use App\Models\Assign;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use Illuminate\Support\Facades\DB;

/**
 * Submission & Pass Rate for one intake (Reports › Intake Performance, new version).
 *
 * Same rules as the old report (SubmissionPassRateReportController::refineResult):
 * per course of the intake (latest creation per course), the registered
 * students' graded results on every class plan they are assigned to, by term.
 *   expected      = results with a grade
 *   not submitted = grade A (Absent)
 *   submitted     = expected - not submitted
 *   passed        = grades D, M, P
 * Both rates are out of expected. As in the old report, the course's students
 * are not limited to active course relations, and results are not filtered on
 * deleted_at.
 */
class SubmissionPassRate
{
    use Concerns\NamesIntakeCourses;

    const ABSENT = 2;
    const PASSING = [4, 5, 6];

    public function build(int $semesterId): array
    {
        $semester = Semester::find($semesterId);
        $courses = [];
        $creations = CourseCreation::with('course')->where('semester_id', $semesterId)->orderBy('id', 'DESC')->get()->unique('course_id');
        foreach ($creations as $creation) {
            $students = StudentCourseRelation::where('course_creation_id', $creation->id)->pluck('student_id')->unique()->values()->all();
            if (empty($students)) {
                continue; // as the old report: a course nobody started is left out
            }
            $registered = StudentAwardingBodyDetails::whereIn('student_id', $students)->whereNotNull('reference')
                ->whereHas('studentcrel', function ($q) use ($creation) {
                    $q->where('course_creation_id', $creation->id);
                })->pluck('student_id')->unique()->values()->all();

            $terms = $this->terms($registered);
            $course = $this->courseNames((string) optional($creation->course)->name) + ['terms' => $terms]
                + $this->figures(array_sum(array_column($terms, 'expected')), array_sum(array_column($terms, 'not_submitted')), array_sum(array_column($terms, 'passed')));
            $courses[] = $course;
        }

        // Lowest pass rate first; courses with nothing due yet at the end.
        usort($courses, function ($a, $b) {
            return [$a['pass_rate'] === null ? 1 : 0, $a['pass_exact'] ?? 0, $a['title']] <=> [$b['pass_rate'] === null ? 1 : 0, $b['pass_exact'] ?? 0, $b['title']];
        });

        $totals = $this->figures(
            array_sum(array_column($courses, 'expected')),
            array_sum(array_column($courses, 'not_submitted')),
            array_sum(array_column($courses, 'passed'))
        );

        return [
            'semester' => (string) optional($semester)->name,
            'intake' => $this->intakeLabel((string) optional($semester)->name),
            'courses' => $courses,
            'totals' => $totals,
            'waffle' => $this->waffle([$totals['passed'], $totals['submitted_not_passed'], $totals['not_submitted']]),
            'has_results' => ($totals['expected'] > 0),
        ];
    }

    /**
     * Several intakes, each worked out on its own as build() does, in intake
     * order, plus totals and the waffle over all of them.
     */
    public function buildMany(array $semesterIds): array
    {
        $intakes = [];
        foreach ($this->orderSemesters($semesterIds) as $id) {
            $intakes[] = ['id' => $id] + $this->build($id);
        }
        $sum = function ($key) use ($intakes) {
            return array_sum(array_map(function ($intake) use ($key) { return $intake['totals'][$key]; }, $intakes));
        };
        $totals = $this->figures($sum('expected'), $sum('not_submitted'), $sum('passed'));
        $names = array_column($intakes, 'semester');

        return [
            'intakes' => $intakes,
            'many' => (count($intakes) > 1),
            'semester' => implode(', ', $names),
            'intake' => $this->intakesLabel($names),
            'file' => $this->intakesFileStem($names),
            'totals' => $totals,
            'waffle' => $this->waffle([$totals['passed'], $totals['submitted_not_passed'], $totals['not_submitted']]),
            'has_courses' => (bool) array_filter($intakes, function ($intake) { return !empty($intake['courses']); }),
            'has_results' => ($totals['expected'] > 0),
        ];
    }

    /** The registered students' results per term, oldest term first. */
    protected function terms(array $registered): array
    {
        if (empty($registered)) {
            return [];
        }
        $plans = Assign::whereIn('student_id', $registered)->pluck('plan_id')->unique()->values()->all();
        if (empty($plans)) {
            return [];
        }
        $rows = DB::table('results as rs')
            ->leftJoin('students as std', 'rs.student_id', 'std.id')
            ->leftJoin('plans as pln', 'rs.plan_id', 'pln.id')
            ->leftJoin('term_declarations as td', 'pln.term_declaration_id', 'td.id')
            ->whereIn('rs.plan_id', $plans)
            ->whereIn('rs.student_id', $registered)
            ->whereIn('std.id', $registered)
            ->groupBy('pln.term_declaration_id', 'td.id', 'td.name')
            ->orderBy('pln.term_declaration_id', 'ASC')
            ->select(
                'td.id as term_id', 'td.name as term_name',
                DB::raw('COUNT(DISTINCT std.id) as students'),
                DB::raw('COUNT(rs.grade_id) as expected'),
                DB::raw('SUM(CASE WHEN rs.grade_id = '.self::ABSENT.' THEN 1 ELSE 0 END) as not_submitted'),
                DB::raw('SUM(CASE WHEN rs.grade_id IN ('.implode(',', self::PASSING).') THEN 1 ELSE 0 END) as passed')
            )->get();

        return $rows->map(function ($row) {
            return [
                'id' => (int) $row->term_id,
                'name' => preg_replace('/\s*\(/', ' (', trim((string) $row->term_name)),
                'students' => (int) $row->students,
            ] + $this->figures((int) $row->expected, (int) $row->not_submitted, (int) $row->passed);
        })->all();
    }

    /** Counts and rates from expected, not submitted and passed. */
    protected function figures(int $expected, int $notSubmitted, int $passed): array
    {
        $submitted = $expected - $notSubmitted;

        return [
            'expected' => $expected,
            'not_submitted' => $notSubmitted,
            'submitted' => $submitted,
            'passed' => $passed,
            'submitted_not_passed' => max(0, $submitted - $passed),
            'submission_rate' => ($expected > 0 ? round($submitted / $expected * 100, 1) : null),
            'pass_rate' => ($expected > 0 ? round($passed / $expected * 100, 1) : null),
            'pass_exact' => ($expected > 0 ? $passed / $expected * 100 : null),
            'pass_of_submitted' => ($submitted > 0 ? round($passed / $submitted * 100, 1) : null),
        ];
    }

    /** Out of 100: whole squares per part, largest remainders first, so they add up to 100. */
    protected function waffle(array $parts): array
    {
        $total = array_sum($parts);
        if ($total < 1) {
            return array_fill(0, count($parts), 0);
        }
        $exact = array_map(function ($n) use ($total) { return $n / $total * 100; }, $parts);
        $whole = array_map('floor', $exact);
        $left = 100 - array_sum($whole);
        $order = array_keys($exact);
        usort($order, function ($a, $b) use ($exact, $whole) {
            return ($exact[$b] - $whole[$b]) <=> ($exact[$a] - $whole[$a]);
        });
        foreach (array_slice($order, 0, (int) $left) as $i) {
            $whole[$i]++;
        }

        return array_map('intval', $whole);
    }
}
