<?php

namespace App\Http\Controllers\Reports;

use App\Exports\StudentWorkplacementReportExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentResultReport;
use App\Services\Reports\StudentWorkPlacementReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Student Work Placement, "New version" tab (user's design, 25 Nov 2026). The page is
 * still StudentWorkplacementReportController::index, which keeps serving the old version beside it.
 */
class StudentWorkPlacementReportNewController extends Controller
{
    /** Every found student with required and completed hours; the screen does the rest. */
    public function data(Request $request, StudentWorkPlacementReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }

        return response()->json($report->build($filters));
    }

    /** The old export's sheet (same columns, merges and order) for the same search. */
    public function excel(Request $request, StudentWorkPlacementReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }
        [$rows, $headers, $modules, $merges] = $report->sheet($filters);

        return Excel::download(new StudentWorkplacementReportExport($rows, $headers, $modules, [], $merges), 'student_workplacement_report.xlsx');
    }
}
