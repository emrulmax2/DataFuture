<?php

namespace App\Services;

/**
 * What share of a cohort passed.
 *
 * The same denominator as {@see SubmissionRate} — the whole cohort, not just
 * the students who submitted — so the two read as a pair: how many handed
 * work in, and how many of the class got through.
 */
class PassRate extends GradeRate
{
    /**
     * Grades that count as a pass: Pass, Merit, Distinction, and
     * Unclassified/Compensated.
     *
     * Referred and Submitted are submissions but not yet passes.
     *
     * Unlike the submission rate, this scores every attempt rather than only
     * the latest: nobody resubmits a module they have already passed, so a
     * pass anywhere in the history is a pass, and looking only at the newest
     * row would drop a student whose last entry is an administrative one.
     */
    public const GRADE_CODES = ['P', 'M', 'D', 'U'];
}
