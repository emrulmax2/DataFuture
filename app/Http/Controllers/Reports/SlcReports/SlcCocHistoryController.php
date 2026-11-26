<?php

namespace App\Http\Controllers\Reports\SlcReports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\SlcCoc;
use App\Services\Reports\SlcCocHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › SLC Reports › SLC COC History, "New version" tab (user's design, 25 Nov 2026): the
 * screen's data and the Excel of the current selection. The old version
 * (SlcDataReportController::SlcCocHistoryExcelDownload, Excel only) stays on its own tab.
 */
class SlcCocHistoryController extends Controller
{
    /** GET ?from=Y-m-d&to=Y-m-d, or neither for every date (the design's default). */
    public function data(Request $request)
    {
        [$from, $to] = $this->range($request);

        return response()->json(SlcCocHistory::build($from, $to));
    }

    /** GET ?from&to&types[]&actioned=all|yes|no&course: the selection on screen, the old export's columns. */
    public function excel(Request $request)
    {
        [$from, $to] = $this->range($request);
        $request->validate([
            'types' => ['nullable', 'array'], 'types.*' => ['string', Rule::in(array_keys(SlcCocHistory::TYPES))],
            'actioned' => ['nullable', 'in:all,yes,no'],
            'course' => ['nullable', 'integer'],
        ]);
        $ids = SlcCocHistory::filter(SlcCocHistory::rows($from, $to), $request->only('types', 'actioned', 'course'))->pluck('id')->all();

        $sheet = [1 => ['Registration No', 'SSN', 'Name', 'Date Of Birth', 'Course', 'Semester', 'Class Start Date', 'Class End Date', 'Status', 'Status Change Date', 'Awarding Body', 'SLC Course Code', 'Type Of COC', 'Actioned', 'Confirmation Date']];
        foreach (array_chunk($ids, 1000) as $chunk) {
            $byId = SlcCoc::with('student', 'student.termStatus', 'student.status', 'crel', 'crel.abody', 'crel.creation.course', 'crel.creation.semester', 'crel.propose')
                ->whereIn('id', $chunk)->get()->keyBy('id');
            foreach ($chunk as $id) {
                $coc = $byId[$id] ?? null;
                if (!$coc) {
                    continue;
                }
                $student = $coc->student;
                $crel = $coc->crel;
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
                    $coc->coc_type ?? '',
                    $coc->actioned ?? '',
                    $coc->confirmation_date ?? '',
                ];
            }
        }
        $name = $from ? 'SLC_COC_History_'.$from.'_to_'.$to.'.xlsx' : 'SLC_COC_History_all_dates.xlsx';

        return Excel::download(new ArrayCollectionExport($sheet, 'SLC COC'), $name);
    }

    /** [from, to] (Y-m-d, from ≤ to) or [null, null] for every date. */
    private function range(Request $request): array
    {
        if (!$request->filled('from') && !$request->filled('to')) {
            return [null, null];
        }
        $in = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        return [$in['from'], $in['to']];
    }
}
