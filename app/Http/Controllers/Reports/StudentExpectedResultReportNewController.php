<?php

namespace App\Http\Controllers\Reports;

use App\Exports\CustomExpectedResultCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentExpectedResultReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Student Expected Result, "New version" tab (user's design, 25 Nov 2026). The page is
 * still StudentExpectedResultReportController::index, which keeps serving the old version beside it.
 */
class StudentExpectedResultReportNewController extends Controller
{
    /** Every found student per chosen attendance semester with their Yes / No per unit. */
    public function data(Request $request, StudentExpectedResultReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! $filters['attendance']) {
            return response()->json(['message' => 'Select at least one attendance semester.'], 422);
        }

        return response()->json($report->build($filters));
    }

    /** The old export's sheet (same columns and rows) for the same search. */
    public function excel(Request $request, StudentExpectedResultReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! $filters['attendance']) {
            return response()->json(['message' => 'Select at least one attendance semester.'], 422);
        }
        [$rows, $headers, $units] = $report->sheet($filters);

        return Excel::download(new CustomExpectedResultCollectionExport($rows, $headers, $units), 'board_expected_result_report.xlsx');
    }
}
