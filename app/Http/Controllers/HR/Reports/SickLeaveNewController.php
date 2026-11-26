<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\SickLeaveNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\SickLeave as R;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Sick Leave, new version (user's design, 26 Nov 2026).
 * The old version (SickLeaveController) is unchanged and sits beside it on the same page.
 */
class SickLeaveNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    public function data(Request $request)
    {
        $f = R::filters($request->all());
        $rows = $this->report->rows($f);
        [$from, $to] = R::span($f, $rows);

        return response()->json(['filters' => $f, 'from' => $from, 'to' => $to, 'staff' => $rows]);
    }

    public function excel(Request $request)
    {
        $f = R::filters($request->all());
        $rows = R::shown($this->report->rows($f), $request->all());
        [$from, $to] = R::span($f, $rows);

        return Excel::download(new SickLeaveNewExport($rows),
            'Sick_Leave_'.date('d_M_Y', strtotime($from)).'_'.date('d_M_Y', strtotime($to)).'.xlsx');
    }
}
