<?php

namespace App\Http\Controllers\Reports;

use App\Exports\CustomArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentResultReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Student Result Report, "New version" tab (user's design, 25 Nov 2026). The page is
 * still StudentResultReportController::index, which keeps serving the old version beside it.
 */
class StudentResultReportNewController extends Controller
{
    /** Every found student with their unit grades; the screen counts, filters and pages them itself. */
    public function data(Request $request, StudentResultReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }

        return response()->json($report->build($filters));
    }

    /** The old board result export (same columns and order) for the same search. */
    public function excel(Request $request, StudentResultReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }
        [$rows, $headers, $units] = $report->sheet($filters);

        return Excel::download(new CustomArrayCollectionExport($rows, $headers, $units), 'board_result_report.xlsx');
    }
}
