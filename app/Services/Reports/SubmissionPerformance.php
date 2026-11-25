<?php

namespace App\Services\Reports;

use App\Models\Plan;
use App\Models\TermDeclaration;
use Illuminate\Support\Facades\DB;

/**
 * Reports › Term Performance › Submission Performance, new version (user's
 * submission-performance-report design, 25 Nov 2026): one term's assignments, first submission
 * against final result, by course.
 *
 * Counting is the old report's (TermSubmissionPerformanceReportController::refineResult), done
 * with a few grouped queries instead of one per student and plan:
 * - per course, the students with a result on the term's plans (deleted results included, as the
 *   old student list) who are assigned to one of those plans with attendance blank or 1;
 * - for each of their plans, the first and the latest result that is not deleted; one with a
 *   grade is an expected assignment;
 * - submitted = grades 3, 4, 5, 6, 7, 8; Distinction 4, Merit 5, Pass 6, Referred 7,
 *   Plagiarised 3, Absent 2 ("not submitted").
 * New: the grades outside those (8 Submitted, 9–13, 1) are counted by name, so the design's
 * "not accounted for" gap can say what it is.
 */
class SubmissionPerformance
{
    const SUBMITTED = [3, 4, 5, 6, 7, 8];

    /** Outcome keys and their grade ids, in the design's order. */
    const OUTCOMES = ['D' => 4, 'M' => 5, 'P' => 6, 'R' => 7, 'C' => 3, 'A' => 2];

    /** Figures a student list can be asked for. */
    const FIGURES = ['students', 'expected', 'sub', 'passed', 'D', 'M', 'P', 'R', 'C', 'A', 'other'];

    /**
     * @return array|null ['term' => [id, name], 'grades' => [id => name], 'totals' => course figures,
     *                    'courses' => [[id, name, students, first => figures, final => figures], ...]]
     *                    figures = [expected, sub, D, M, P, R, C, A, other => [grade id => count]];
     *                    null when the term does not exist
     */
    public static function build(int $termId): ?array
    {
        $loaded = self::load($termId);
        if ($loaded === null) {
            return null;
        }

        $courses = [];
        foreach ($loaded['courseIds'] as $courseId) {
            $row = ['id' => $courseId, 'name' => (string) ($loaded['courseNames'][$courseId] ?? 'Unknown course'), 'students' => 0, 'first' => self::zero(), 'final' => self::zero()];
            foreach ($loaded['counted'][$courseId] ?? [] as $grades) {
                $row['students']++;
                foreach ($grades as [$first, $final]) {
                    self::tally($row['first'], $first);
                    self::tally($row['final'], $final);
                }
            }
            $courses[] = $row;
        }

        // Courses with work to show, largest first (the design's order).
        $courses = array_values(array_filter($courses, fn ($c) => $c['students'] > 0 || $c['first']['expected'] > 0 || $c['final']['expected'] > 0));
        usort($courses, fn ($a, $b) => $b['first']['expected'] <=> $a['first']['expected'] ?: strnatcasecmp($a['name'], $b['name']));

        $totals = ['students' => 0, 'first' => self::zero(), 'final' => self::zero()];
        foreach ($courses as $c) {
            $totals['students'] += $c['students'];
            foreach (['first', 'final'] as $which) {
                foreach ($c[$which] as $key => $value) {
                    if ($key === 'other') {
                        foreach ($value as $g => $n) {
                            $totals[$which]['other'][$g] = ($totals[$which]['other'][$g] ?? 0) + $n;
                        }
                    } else {
                        $totals[$which][$key] += $value;
                    }
                }
            }
        }

        return [
            'term' => ['id' => (int) $loaded['term']->id, 'name' => (string) $loaded['term']->name],
            'grades' => DB::table('grades')->pluck('name', 'id')->map(fn ($n) => (string) ($n ?? ''))->all(),
            'totals' => $totals,
            'courses' => $courses,
        ];
    }

    /**
     * The students behind one figure of the report (the Full figures links, 25 Nov 2026):
     * [student id => ['count' => assignments in the figure, 'grades' => [grade id => count]]].
     *
     * @param string $course a course id, or 'all'
     * @param string $which  'first' or 'final'
     * @param string $key    one of FIGURES; 'students' is every counted student of the course,
     *                       with their expected assignments
     * @return array|null null when the term does not exist
     */
    public static function students(int $termId, string $course, string $which, string $key): ?array
    {
        $loaded = self::load($termId);
        if ($loaded === null) {
            return null;
        }

        $side = $which === 'first' ? 0 : 1;
        $match = $key === 'students' ? 'expected' : $key;
        $out = [];
        foreach ($loaded['counted'] as $courseId => $students) {
            if ($course !== 'all' && (int) $course !== $courseId) {
                continue;
            }
            foreach ($students as $studentId => $grades) {
                $hit = ['count' => 0, 'grades' => []];
                foreach ($grades as $pair) {
                    $grade = $pair[$side];
                    if (self::inFigure($match, $grade)) {
                        $hit['count']++;
                        $hit['grades'][$grade] = ($hit['grades'][$grade] ?? 0) + 1;
                    }
                }
                if ($hit['count'] === 0 && $key !== 'students') {
                    continue;
                }
                // A student counted on two courses appears once under "All courses".
                if (isset($out[$studentId])) {
                    $out[$studentId]['count'] += $hit['count'];
                    foreach ($hit['grades'] as $g => $c) {
                        $out[$studentId]['grades'][$g] = ($out[$studentId]['grades'][$g] ?? 0) + $c;
                    }
                } else {
                    $out[$studentId] = $hit;
                }
            }
        }

        return $out;
    }

    /** Whether a result's grade counts in a figure (the same rules as tally()). */
    private static function inFigure(string $key, int $grade): bool
    {
        if ($grade <= 0) {
            return false;
        }

        return match ($key) {
            'expected' => true,
            'sub' => in_array($grade, self::SUBMITTED, true),
            'passed' => in_array($grade, [self::OUTCOMES['P'], self::OUTCOMES['M'], self::OUTCOMES['D']], true),
            'other' => !in_array($grade, self::OUTCOMES, true),
            default => isset(self::OUTCOMES[$key]) && self::OUTCOMES[$key] === $grade,
        };
    }

    /**
     * The term's results as the old report counts them: ['term', 'courseIds', 'courseNames',
     * 'counted' => [course id => [student id => [[first grade, final grade], ... one per plan]]]].
     * Grade 0 = no non-deleted result. Null when the term does not exist.
     */
    private static function load(int $termId): ?array
    {
        $term = TermDeclaration::find($termId);
        if (!$term) {
            return null;
        }

        $plans = Plan::where('term_declaration_id', $termId)->get(['id', 'course_id']);
        $courseOfPlan = $plans->pluck('course_id', 'id')->all();
        $courseIds = array_values(array_unique(array_map('intval', $courseOfPlan)));
        $courseNames = DB::table('courses')->whereIn('id', $courseIds)->pluck('name', 'id')->all();

        // Every (student, plan) with a result; the first and latest result not deleted.
        $pairs = empty($courseOfPlan) ? collect() : DB::table('results')
            ->whereIn('plan_id', array_keys($courseOfPlan))
            ->selectRaw('student_id, plan_id, MIN(CASE WHEN deleted_at IS NULL THEN id END) AS first_id, MAX(CASE WHEN deleted_at IS NULL THEN id END) AS last_id')
            ->groupBy('student_id', 'plan_id')
            ->get();
        $ids = $pairs->pluck('first_id')->merge($pairs->pluck('last_id'))->filter()->unique()->values()->all();
        $grade = [];
        foreach (array_chunk($ids, 5000) as $chunk) {
            $grade += DB::table('results')->whereIn('id', $chunk)->pluck('grade_id', 'id')->map(fn ($g) => (int) $g)->all();
        }

        // Students assigned (attendance blank or 1) to a plan they have a result on.
        $assigned = [];
        foreach ($pairs->pluck('student_id')->unique()->chunk(2000) as $students) {
            DB::table('assigns')->whereIn('student_id', $students->all())->whereIn('plan_id', array_keys($courseOfPlan))
                ->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('attendance')->orWhere('attendance', 1))
                ->select('student_id', 'plan_id')->get()
                ->each(function ($a) use (&$assigned) {
                    $assigned[$a->student_id.':'.$a->plan_id] = true;
                });
        }

        // Per course: the student counts when any of their result plans in the course is assigned.
        $byCourse = [];
        foreach ($pairs as $pair) {
            $byCourse[(int) $courseOfPlan[$pair->plan_id]][(int) $pair->student_id][] = $pair;
        }
        $counted = [];
        foreach ($byCourse as $courseId => $students) {
            foreach ($students as $studentId => $studentPairs) {
                $counts = false;
                foreach ($studentPairs as $pair) {
                    if (isset($assigned[$studentId.':'.$pair->plan_id])) {
                        $counts = true;
                        break;
                    }
                }
                if ($counts) {
                    $counted[$courseId][$studentId] = array_map(fn ($p) => [
                        $p->first_id ? ($grade[$p->first_id] ?? 0) : 0,
                        $p->last_id ? ($grade[$p->last_id] ?? 0) : 0,
                    ], $studentPairs);
                }
            }
        }

        return ['term' => $term, 'courseIds' => $courseIds, 'courseNames' => $courseNames, 'counted' => $counted];
    }

    /** One result's grade into the figures (no grade: not expected, as the old report). */
    private static function tally(array &$figures, int $grade): void
    {
        if ($grade <= 0) {
            return;
        }
        $figures['expected']++;
        if (in_array($grade, self::SUBMITTED, true)) {
            $figures['sub']++;
        }
        $key = array_search($grade, self::OUTCOMES, true);
        if ($key !== false) {
            $figures[$key]++;
        } else {
            $figures['other'][$grade] = ($figures['other'][$grade] ?? 0) + 1;
        }
    }

    private static function zero(): array
    {
        return ['expected' => 0, 'sub' => 0, 'D' => 0, 'M' => 0, 'P' => 0, 'R' => 0, 'C' => 0, 'A' => 0, 'other' => []];
    }

    /** Passed = Pass, Merit and Distinction. */
    public static function passed(array $f): int
    {
        return $f['P'] + $f['M'] + $f['D'];
    }
}
