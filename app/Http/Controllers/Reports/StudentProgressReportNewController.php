<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\StudentProgressReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Progress Monitoring, "New version" tab (user's design, 25 Nov 2026). The page is still
 * StudentProgressMonitoringReportController::index, which keeps serving the old version beside it.
 */
class StudentProgressReportNewController extends Controller
{
    /** Every found student with their modules and certificate; the screen does the rest. */
    public function data(Request $request, StudentProgressReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }

        return response()->json($report->build($filters));
    }

    /** The old export's sheet (same columns and rows) for the same search; `criteria` is the screen's search line. */
    public function excel(Request $request, StudentProgressReport $report)
    {
        $filters = StudentResultReport::filters($request->all());
        if (! StudentResultReport::chosen($filters)) {
            return response()->json(['message' => 'Choose at least one filter.'], 422);
        }
        $criteria = mb_substr(trim((string) $request->query('criteria', '')), 0, 1000);

        return Excel::download(new ArrayCollectionExport($report->sheet($filters, $criteria)), 'student_progress_monitor_report.xlsx');
    }
}
