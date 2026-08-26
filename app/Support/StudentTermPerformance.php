<?php

namespace App\Support;

use App\Models\AcademicCriteria;
use App\Models\Assign;
use App\Models\Attendance;
use App\Models\AttendanceCriteria;
use App\Models\Plan;
use App\Models\Result;
use App\Models\TermPerformanceCriteria;
use Carbon\Carbon;

/**
 * Headline numbers for one attendance term, using the same criteria tables and
 * the same attendance + academic weighting the Performance screen reports.
 *
 * Only the dashboard stat row needs this; the Performance screen still does
 * its own per-term pass because it renders every term at once.
 */
class StudentTermPerformance
{
    /**
     * @param  \App\Models\Student  $student
     * @return array{term_id: int|null, percent: float, label: string, tone: string, achieved: int, expected: int, results_published: int, results_total: int}
     */
    public static function latest($student)
    {
        $empty = [
            'term_id' => null,
            'percent' => 0.0,
            'label' => 'Not available',
            'tone' => 'grey',
            'achieved' => 0,
            'expected' => 0,
            'results_published' => 0,
            'results_total' => 0,
        ];

        if (!isset($student->id)) {
            return $empty;
        }

        $planIds = Assign::where('student_id', $student->id)->whereHas('plan')->pluck('plan_id')->unique()->toArray();

        if (empty($planIds)) {
            return $empty;
        }

        $termId = Plan::whereIn('id', $planIds)->max('term_declaration_id');

        if (!$termId) {
            return $empty;
        }

        $termPlanIds = Plan::whereIn('id', $planIds)->where('term_declaration_id', $termId)->pluck('id')->toArray();

        /* ── Attendance side ─────────────────────────────────────────── */

        $topAttendance = (float) (optional(AttendanceCriteria::orderBy('point', 'desc')->first())->point ?? 0);

        $attendance = Attendance::with('feed')
            ->whereHas('planDateList', function ($query) use ($termPlanIds) {
                $query->whereIn('plan_id', $termPlanIds);
            })
            ->where('student_id', $student->id)
            ->get();

        $present = $attendance->filter(function ($row) {
            return isset($row->feed->attendance_count) && $row->feed->attendance_count == 1;
        })->count();

        $attendancePercent = $attendance->count() > 0 ? round(($present / $attendance->count()) * 100) : 0;

        $attendanceCriteria = AttendanceCriteria::where('range_from', '<=', $attendancePercent)
            ->where('range_to', '>=', $attendancePercent)
            ->first();

        $attendancePoint = (float) (optional($attendanceCriteria)->point ?? 0);

        /* ── Academic side ───────────────────────────────────────────── */

        $academicCriteria = AcademicCriteria::orderBy('point', 'desc')->get();
        $topAcademic = (float) (optional($academicCriteria->first())->point ?? 0);
        $countableGrades = $academicCriteria->pluck('code')->toArray();

        $results = Result::with('grade')
            ->whereIn('plan_id', $termPlanIds)
            ->where('student_id', $student->id)
            ->where('published_at', '<', Carbon::now())
            ->get();

        $achievedAcademic = 0;
        $expectedAcademic = 0;

        foreach ($results as $result) {
            $code = isset($result->grade->code) ? trim($result->grade->code) : '';

            if (!in_array($code, $countableGrades, true)) {
                continue;
            }

            $achievedAcademic += (float) (optional($academicCriteria->firstWhere('code', $code))->point ?? 0);
            $expectedAcademic += $topAcademic;
        }

        $achieved = $attendancePoint + $achievedAcademic;
        $expected = $topAttendance + $expectedAcademic;
        $percent = $expected > 0 ? round(($achieved / $expected) * 100, 2) : 0;

        $criteria = TermPerformanceCriteria::where('range_from', '<=', $percent)
            ->where('range_to', '>=', $percent)
            ->first();

        return [
            'term_id' => $termId,
            'percent' => (float) $percent,
            'label' => optional($criteria)->label ?: 'Not available',
            'tone' => static::tone($percent),
            'achieved' => (int) round($achieved),
            'expected' => (int) round($expected),
            'results_published' => $results->count(),
            'results_total' => count($termPlanIds),
        ];
    }

    /**
     * Map a percentage onto the portal's three chip tones.
     */
    public static function tone($percent)
    {
        if ($percent >= 70) {
            return 'green';
        }

        if ($percent >= 50) {
            return 'cream';
        }

        return 'rust';
    }
}
