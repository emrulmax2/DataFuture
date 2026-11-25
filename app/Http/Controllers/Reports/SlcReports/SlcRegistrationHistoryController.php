<?php

namespace App\Http\Controllers\Reports\SlcReports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\SlcRegistration;
use App\Services\Reports\SlcRegistrationHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › SLC Reports › SLC Registration History, "New version" tab (user's design, 25 Nov
 * 2026): the screen's data for a date range and the Excel of the current selection. The old
 * version (SlcDataReportController::SlcRegistrationHistoryExcelDownload, Excel only) stays on its
 * own tab.
 */
class SlcRegistrationHistoryController extends Controller
{
    /** GET ?from=Y-m-d&to=Y-m-d: every registration confirmation of the range. */
    public function data(Request $request)
    {
        [$from, $to] = $this->range($request);

        return response()->json(SlcRegistrationHistory::build($from, $to));
    }

    /** GET ?from&to&statuses[]&years[]&academic[]&course: the selection on screen, the old export's columns. */
    public function excel(Request $request)
    {
        [$from, $to] = $this->range($request);
        $request->validate([
            'statuses' => ['nullable', 'array'], 'statuses.*' => ['integer'],
            'years' => ['nullable', 'array'], 'years.*' => ['integer'],
            'academic' => ['nullable', 'array'], 'academic.*' => ['integer'],
            'course' => ['nullable', 'integer'],
        ]);
        $ids = SlcRegistrationHistory::filter(SlcRegistrationHistory::rows($from, $to), $request->only('statuses', 'years', 'academic', 'course'))->pluck('id')->all();

        $sheet = [1 => ['Registration No', 'SSN', 'Name', 'Date Of Birth', 'Course', 'Semester', 'Class Start Date', 'Class End Date', 'Status', 'Status Change Date', 'Awarding Body', 'SLC Course Code', 'Registration Status', 'Academic Year', 'Registration Year', 'Confirmation Date']];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $byId = SlcRegistration::with('student', 'student.termStatus', 'student.status', 'regStatus', 'year', 'crel', 'crel.abody', 'crel.creation.course', 'crel.creation.semester', 'crel.propose')
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
                    $slc->regStatus->name ?? '',
                    $slc->year->name ?? '',
                    $slc->registration_year ? 'Year '.$slc->registration_year : '',
                    $slc->confirmation_date ?? '',
                ];
            }
        }

        return Excel::download(new ArrayCollectionExport($sheet, 'SLC Registrations'), 'SLC_Registration_History_'.$from.'_to_'.$to.'.xlsx');
    }

    /** The date range: from ≤ to, both Y-m-d; neither given = the last 12 months. */
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
