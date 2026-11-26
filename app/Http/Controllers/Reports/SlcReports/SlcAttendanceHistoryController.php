<?php

namespace App\Http\Controllers\Reports\SlcReports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\SlcAttendance;
use App\Services\Reports\SlcAttendanceHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › SLC Reports › SLC Attendance History, "New version" tab (user's design, 25 Nov 2026):
 * the screen's data for a date range and the Excel of the current selection. The old version
 * (SlcDataReportController::SLCAttendanceExcelDownload, Excel only) stays on its own tab.
 */
class SlcAttendanceHistoryController extends Controller
{
    /** GET ?from=Y-m-d&to=Y-m-d: every confirmation of the range (the browser filters the rest). */
    public function data(Request $request)
    {
        [$from, $to] = $this->range($request);

        return response()->json(SlcAttendanceHistory::build($from, $to));
    }

    /**
     * GET ?from&to&codes[]&years[]&terms[]&course: the selection on screen, with the old
     * export's columns (registration, SSN, date of birth, course dates, awarding body…).
     */
    public function excel(Request $request)
    {
        [$from, $to] = $this->range($request);
        $request->validate([
            'codes' => ['nullable', 'array'], 'codes.*' => ['integer'],
            'years' => ['nullable', 'array'], 'years.*' => ['integer'],
            'terms' => ['nullable', 'array'], 'terms.*' => ['integer'],
            'course' => ['nullable', 'integer'],
        ]);
        $ids = SlcAttendanceHistory::filter(SlcAttendanceHistory::rows($from, $to), $request->only('codes', 'years', 'terms', 'course'))->pluck('id')->all();

        $sheet = [1 => ['Registration No', 'SSN', 'Name', 'Date Of Birth', 'Course', 'Semester', 'Class Start Date', 'Class End Date', 'Status', 'Status Change Date', 'Awarding Body', 'SLC Course Code', 'Attendance Year', 'Code', 'Term', 'Claim Amount', 'Confirmation Date']];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $byId = SlcAttendance::with('student', 'student.termStatus', 'student.status', 'currentClaimAmount', 'code', 'crel', 'crel.abody', 'crel.creation.course', 'crel.creation.semester', 'crel.propose')
                ->whereIn('id', $chunk)->get()->keyBy('id');
            foreach ($chunk as $id) {
                $slc = $byId[$id] ?? null;
                if (!$slc) {
                    continue;
                }
                $student = $slc->student;
                $crel = $slc->crel;
                $sheet[] = [
                    $student->registration_no ?? '',
                    $student->ssn_no ?? '',
                    $student->full_name ?? '',
                    $student->date_of_birth ?? '',
                    $crel->creation->course->name ?? '',
                    $crel->creation->semester->name ?? '',
                    $crel->course_start_date ?? '',
                    $crel->course_end_date ?? '',
                    $student->status->name ?? '',
                    $student->termStatus->status_change_date ?? '',
                    $crel->abody->reference ?? '',
                    $crel->propose->slc_code ?? '',
                    $slc->attendance_year ? $slc->attendance_year.' Year' : '',
                    $slc->code->code ?? '',
                    $slc->session_term ? 'Term '.$slc->session_term : '',
                    $slc->currentClaimAmount->amount ?? '',
                    $slc->confirmation_date ?? '',
                ];
            }
        }

        return Excel::download(new ArrayCollectionExport($sheet, 'SLC Attendance'), 'SLC_Attendance_History_'.$from.'_to_'.$to.'.xlsx');
    }

    /** The date range: from ≤ to, both Y-m-d; neither given = the last 12 months (the screen's first load). */
    private function range(Request $request): array
    {
        if (!$request->filled('from') && !$request->filled('to')) {
            return [now()->subYear()->addDay()->toDateString(), now()->toDateString()];
        }
        $in = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return [$in['from'], $in['to']];
    }
}
