<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * How much of a cohort reached a given set of grades.
 *
 *     rate = students whose grade is in the set / students expected to have one
 *
 * Submission and pass rate are the same measurement over different grade sets,
 * so they share everything here and differ only in `GRADE_CODES`. Keeping them
 * as one machine is the point: the denominator, the handling of resits and the
 * treatment of legacy rows have to stay identical, or the two figures stop
 * being comparable on the same row.
 *
 * "Expected" is the cohort actually attached to each plan, summed over the
 * plans in scope. For one module that is simply its enrolment; across a term
 * it is the same as `students x modules` when every module shares a cohort,
 * and stays right when they do not — which a flat multiplication would not.
 */
abstract class GradeRate
{
    /** The grades this rate counts. Set by each subclass. */
    public const GRADE_CODES = [];

    /**
     * Whether only a student's most recent attempt is scored.
     *
     * It matters for submission — a student who submitted, then was marked
     * absent on the resit, has not submitted the work that stands — and not
     * for a pass, because nobody resubmits a module they have already passed,
     * so a pass anywhere in the history is a pass.
     */
    protected const LATEST_ATTEMPT_ONLY = false;

    /**
     * The only class type that carries assessed work.
     *
     * Matched case-insensitively, and stated as an allow list rather than as
     * "not Tutorial, not Seminar". The two pick out the same 941 plans today,
     * but they stop agreeing the moment a Practical or a Workshop is added:
     * the exclusion would let it into the denominator silently, the allow list
     * makes adding it a decision.
     *
     * Held here rather than in each caller so the term figure, the tutor
     * figure and the page headline cannot disagree about what counts.
     */
    public const SUBMITTING_CLASS_TYPES = ['theory'];

    /** Constrain a `plans` query to the class types that can be assessed. */
    public static function scopeSubmitting($query, string $column = 'class_type')
    {
        return $query->whereIn(DB::raw('LOWER('.$column.')'), self::SUBMITTING_CLASS_TYPES);
    }

    /** The same test for a plan already in memory. */
    public static function isSubmitting($classType): bool
    {
        return in_array(strtolower((string) $classType), self::SUBMITTING_CLASS_TYPES, true);
    }

    /**
     * Resolved once per request, per subclass — the codes are stable, the ids
     * are not. Keyed by class: a single shared slot would hand the pass-rate
     * grade ids to whichever rate asked first.
     */
    private static array $gradeIds = [];

    protected function gradeIds(): array
    {
        if (!isset(self::$gradeIds[static::class])):
            self::$gradeIds[static::class] = Grade::whereIn(DB::raw('TRIM(code)'), static::GRADE_CODES)
                ->pluck('id')
                ->all();
        endif;

        return self::$gradeIds[static::class];
    }

    /**
     * Counted / expected / rate for each plan, keyed by plan id.
     *
     * Two grouped queries whatever the size of the list, so a table of forty
     * modules costs the same as one.
     *
     * Results are filtered on their own `term_declaration_id`. A row with no
     * term stamped is legacy — those trace back to plans from 2016-2024 — and
     * is skipped whether or not a term is passed, so `null` here means "any
     * current term", never "including the unstamped ones".
     */
    public function perPlan(array $planIds, ?int $termDeclarationId = null): array
    {
        $planIds = array_values(array_unique(array_filter($planIds)));

        if (empty($planIds)):
            return [];
        endif;

        /* The cohort: distinct students, because an assign row is per student
           per plan and a duplicate would inflate what we expect. */
        $expected = DB::table('assigns')
            ->select('plan_id', DB::raw('COUNT(DISTINCT student_id) AS n'))
            ->whereIn('plan_id', $planIds)
            ->whereNull('deleted_at')
            ->groupBy('plan_id')
            ->pluck('n', 'plan_id')
            ->all();

        $counted = $this->countedPerPlan($planIds, $termDeclarationId);

        $out = [];
        foreach ($planIds as $id):
            $out[$id] = self::figures((int) ($counted[$id] ?? 0), (int) ($expected[$id] ?? 0));
        endforeach;

        return $out;
    }

    /** The same figures totalled across every plan in the list. */
    public function forPlans(array $planIds, ?int $termDeclarationId = null): array
    {
        return self::total($this->perPlan($planIds, $termDeclarationId));
    }

    public function forPlan($planId, ?int $termDeclarationId = null): array
    {
        return $this->forPlans([(int) $planId], $termDeclarationId);
    }

    /**
     * A whole term, optionally narrowed to one tutor and one course.
     *
     * Leave `$tutorId` null for the term as a whole — every tutor's modules
     * scored together. Pass one to get just that tutor's share of it. A single
     * module needs no tutor at all: `forPlan()` is already specific enough,
     * because a plan belongs to exactly one tutor.
     *
     * `$tutorField` is the column the tutor sits in — `personal_tutor_id` on
     * the personal-tutor screens, `tutor_id` on the teaching ones.
     *
     * Theory plans only by default. Work is assessed on a theory class, so
     * counting any other cohort would divide real results by students who were
     * never expected to produce one. Pass `$theoryOnly = false` only to score a
     * set of plans exactly as given.
     */
    public function forTerm(
        $termDeclarationId,
        $tutorId = null,
        string $tutorField = 'personal_tutor_id',
        $courseId = 0,
        bool $theoryOnly = true
    ): array {
        return $this->forPlans(
            $this->termPlanIds($termDeclarationId, $tutorId, $tutorField, $courseId, $theoryOnly),
            (int) $termDeclarationId
        );
    }

    /**
     * The same scope as `forTerm()` but per plan, for a screen that lists the
     * modules as well as totalling them.
     */
    public function perTermPlan(
        $termDeclarationId,
        $tutorId = null,
        string $tutorField = 'personal_tutor_id',
        $courseId = 0,
        bool $theoryOnly = true
    ): array {
        return $this->perPlan(
            $this->termPlanIds($termDeclarationId, $tutorId, $tutorField, $courseId, $theoryOnly),
            (int) $termDeclarationId
        );
    }

    private function termPlanIds(
        $termDeclarationId,
        $tutorId,
        string $tutorField,
        $courseId,
        bool $theoryOnly
    ): array {
        return DB::table('plans')
            ->where('term_declaration_id', $termDeclarationId)
            ->whereNull('deleted_at')
            /* A plan with nobody in the tutor column is legacy and never
               counts, whether the call is for one tutor or the whole term. It
               is also what keeps the two consistent: without this, per-tutor
               figures could never add up to the term they sit in. */
            ->whereNotNull($tutorField)
            ->where($tutorField, '>', 0)
            /* Null tutor means the whole term; `when` skips the clause rather
               than matching on null, which would return nothing. */
            ->when($tutorId, fn ($q) => $q->where($tutorField, $tutorId))
            ->when($courseId > 0, fn ($q) => $q->where('course_id', $courseId))
            ->when($theoryOnly, fn ($q) => self::scopeSubmitting($q))
            ->pluck('id')
            ->all();
    }

    /**
     * Students counted in the numerator, per plan.
     *
     * Distinct students, not rows: a student can hold several result rows for
     * one module, and counting rows takes real modules past 100%.
     */
    private function countedPerPlan(array $planIds, ?int $termDeclarationId): array
    {
        /* Every row the student has on the module, narrowed the same way in
           both branches so the two rates score the same population.

           Legacy rows carry no term, and are excluded unconditionally so the
           unfiltered call cannot quietly score a different population from the
           filtered one. */
        $scope = fn ($q) => $q
            ->whereIn('r.plan_id', $planIds)
            ->whereNull('r.deleted_at')
            ->whereNotNull('r.term_declaration_id')
            ->when($termDeclarationId !== null, fn ($q2) => $q2->where('r.term_declaration_id', $termDeclarationId));

        /* Enrolment, not history: a grade belonging to a student who has since
           left the plan stays out of the numerator, which would otherwise take
           a module past its own cohort. */
        $enrolled = function ($join, string $alias) {
            $join->on('a.plan_id', '=', $alias.'.plan_id')
                ->on('a.student_id', '=', $alias.'.student_id')
                ->whereNull('a.deleted_at');
        };

        if (!static::LATEST_ATTEMPT_ONLY):
            return DB::table('results as r')
                ->join('assigns as a', fn ($join) => $enrolled($join, 'r'))
                ->tap($scope)
                ->whereIn('r.grade_id', $this->gradeIds())
                ->select('r.plan_id', DB::raw('COUNT(DISTINCT r.student_id) AS n'))
                ->groupBy('r.plan_id')
                ->pluck('n', 'r.plan_id')
                ->all();
        endif;

        /* Rank first, filter second. Filtering by grade before ranking would
           make "latest" mean "latest row that already counts", so a student
           whose newest attempt is Absent would still be scored on the older
           submission it replaced.

           Ordered by id, not by `published_at`: the id is the order the
           attempts were actually recorded in, and 142 rows carry no
           publication date at all. */
        $ranked = DB::table('results as r')
            ->tap($scope)
            ->select('r.plan_id', 'r.student_id', 'r.grade_id', DB::raw(
                'ROW_NUMBER() OVER (PARTITION BY r.plan_id, r.student_id'
                .' ORDER BY r.id DESC) AS attempt'
            ));

        return DB::query()->fromSub($ranked, 'latest')
            ->join('assigns as a', fn ($join) => $enrolled($join, 'latest'))
            ->where('latest.attempt', 1)
            ->whereIn('latest.grade_id', $this->gradeIds())
            ->select('latest.plan_id', DB::raw('COUNT(DISTINCT latest.student_id) AS n'))
            ->groupBy('latest.plan_id')
            ->pluck('n', 'plan_id')
            ->all();
    }

    /** Sum a set of per-plan figures back into one. */
    public static function total(array $rows): array
    {
        return self::figures(
            array_sum(array_column($rows, 'counted')),
            array_sum(array_column($rows, 'expected'))
        );
    }

    /**
     * Rate is null, not 0, when nothing is expected — a module with no one
     * enrolled has no rate at all, and showing it as 0% reads as a failure
     * rather than as an empty set.
     */
    private static function figures(int $counted, int $expected): array
    {
        return [
            'counted' => $counted,
            'expected' => $expected,
            'rate' => $expected > 0 ? $counted / $expected * 100 : null,
        ];
    }
}
