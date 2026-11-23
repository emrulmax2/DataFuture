<?php

namespace App\Services\Reports;

use Illuminate\Support\Facades\DB;

/**
 * Retention Rate for one intake (Reports › Intake Performance, new version).
 *
 * Same rules as the old report (RetentionRateReportController::refineResult):
 * every course with students counts straight away (a running course gives the
 * retention so far), and dropped out = registered students whose current
 * status is a drop-out status, set between day 28 and the course end date.
 */
class RetentionRate extends IntakeRateReport
{
    const KEPT_LABEL = 'Retained';

    protected function isDue(?string $start, ?string $end, string $today): bool
    {
        return true;
    }

    protected function droppedOut(array $registered, ?string $start, ?string $end): array
    {
        // A missing date compares as 1 Jan 1970, as it did in the old report.
        $from = date('Y-m-d', strtotime(($start ?? '1970-01-01').' + '.self::REFUND_DAYS.' days'));
        $to = ($end ?? '1970-01-01');

        return $this->firstDates(DB::table('students as std')
            ->join('student_attendance_term_statuses as sats', function ($join) {
                $join->on('std.id', '=', 'sats.student_id')->on('std.status_id', '=', 'sats.status_id');
            })
            ->whereIn('std.id', $registered)
            ->whereIn('sats.status_id', self::DROPPED_OUT_STATUSES)
            ->whereDate('sats.status_change_date', '>=', $from)
            ->whereDate('sats.status_change_date', '<=', $to)
            ->get(['sats.student_id', 'sats.status_change_date']));
    }
}
