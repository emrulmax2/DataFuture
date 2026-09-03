<?php

namespace App\Services;

/**
 * What share of a cohort handed work in.
 *
 * Everything but the grade set lives in {@see GradeRate}, so this figure and
 * the pass rate beside it are computed the same way and can be read against
 * each other on one row.
 */
class SubmissionRate extends GradeRate
{
    /**
     * Grades that count as a submission.
     *
     * Absent and ungraded are the only states that mean nothing was handed in.
     * Fail, Recoverable Fail and Satisfactory are deliberately excluded.
     */
    public const GRADE_CODES = ['P', 'M', 'D', 'U', 'R', 'S', 'C', 'W'];

    /**
     * Only the latest attempt counts.
     *
     * A module can be attempted more than once, and it is the attempt that
     * stands that says whether work was handed in. A student marked Absent on
     * their resit has not submitted, however an earlier attempt was graded.
     */
    protected const LATEST_ATTEMPT_ONLY = true;
}
