<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\OutstandingHolidayNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\OutstandingHoliday as R;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Outstanding Holiday, new version (user's design, 26 Nov 2026).
 * The old version (OutstandingHolidayReportController) is unchanged and sits beside it on the same page.
 */
class OutstandingHolidayReportNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    public function data(Request $request)
    {
        set_time_limit(300);
        $f = R::filters($request->all());
        if (!$f) return response()->json(['message' => 'Choose a holiday year.'], 422);

        return response()->json([
            'filters' => $f, 'today' => date('Y-m-d'),
            'staff' => array_map(fn ($r) => array_diff_key($r, ['full' => 1]), $this->report->rows($f)),
        ]);
    }

    public function excel(Request $request)
    {
        set_time_limit(300);
        $f = R::filters($request->all());
        abort_if(!$f, 422);
        $rows = R::shown($this->report->rows($f), $request->all());

        return Excel::download(new OutstandingHolidayNewExport($rows),
            'Outstanding_Holiday_'.date('d_M_Y', strtotime($f['from'])).'_'.date('d_M_Y', strtotime($f['to'])).'.xlsx');
    }
}
