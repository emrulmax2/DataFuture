<?php

namespace App\Services\Reports;

use App\Models\StudentAttendanceTermStatus;

/**
 * Continuation Rate for one intake (Reports › Intake Performance, new version).
 *
 * Same rules as the old report (ContinuationReportController::refineResult):
 * dropped out = registered students given a drop-out status between day 28
 * and day 380 after the course start, and a course is only counted once its
 * 380 days are over; before that it is listed as not due.
 */
class ContinuationRate extends IntakeRateReport
{
    const WINDOW_DAYS = 380;
    const KEPT_LABEL = 'Continued';

    protected function isDue(?string $start, ?string $end, string $today): bool
    {
        return ($start !== null && $this->dueDate($start, $end) < $today);
    }

    protected function dueDate(?string $start, ?string $end): ?string
    {
        return ($start !== null ? date('Y-m-d', strtotime($start.' + '.self::WINDOW_DAYS.' days')) : null);
    }

    protected function droppedOut(array $registered, ?string $start, ?string $end): array
    {
        return $this->firstDates(StudentAttendanceTermStatus::whereIn('student_id', $registered)
            ->whereIn('status_id', self::DROPPED_OUT_STATUSES)->whereNotNull('status_change_date')
            ->whereDate('status_change_date', '>=', date('Y-m-d', strtotime($start.' + '.self::REFUND_DAYS.' days')))
            ->whereDate('status_change_date', '<=', $this->dueDate($start, $end))
            ->get(['student_id', 'status_change_date']));
    }
}
