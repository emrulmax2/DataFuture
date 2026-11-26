<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentPerformanceReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Result Grade Performance, "New version" tab (user's design, 25 Nov 2026). The page is
 * still StudentPerformanceReportController::index, which keeps serving the old version beside it.
 */
class StudentPerformanceReportNewController extends Controller
{
    /** Every found student with expected and achieved points per semester; the screen does the rest. */
    public function data(Request $request, StudentPerformanceReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }

        return response()->json($report->build($filters));
    }

    /** The old export's sheet (same columns and order) for the same search. */
    public function excel(Request $request, StudentPerformanceReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }

        return Excel::download(new ArrayCollectionExport($report->sheet($filters)), 'student_performance_report.xlsx');
    }
}
