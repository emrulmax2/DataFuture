<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\HourReportNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\HourReport as R;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Employee Hour Report, new version (user's employee-hour-report design, 26 Nov 2026).
 * The old version (HolidayHourReportController) is unchanged and sits beside it on the same page.
 */
class HolidayHourReportNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    public function data(Request $request)
    {
        $r = R::range($request->all());
        if ($r['from'] === '') return response()->json(['message' => 'Choose a From date.'], 422);

        return response()->json([
            'from' => $r['from'], 'to' => $r['to'], 'weeks' => R::weeks($r['from'], $r['to']), 'today' => date('Y-m-d'),
            'staff' => array_map(fn ($x) => array_diff_key($x, ['ni' => 1, 'full' => 1]), $this->report->rows($r['from'], $r['to'])),
        ]);
    }

    /** The old screen's result for the same dates, so a New search fills the Old pane too. */
    public function old(Request $request, HolidayHourReportController $old)
    {
        $r = R::range($request->all());
        if ($r['from'] === '') return response()->json(['message' => 'Choose a From date.'], 422);

        return response()->json([
            'html' => $old->getSearchResult($r['from'], $r['toGiven'] ? $r['to'] : ''),
            'export' => route('hr.portal.reports.holiday.hour.export', [strtotime($r['from']), $r['toGiven'] ? strtotime($r['to']) : '']),
        ]);
    }

    public function excel(Request $request)
    {
        set_time_limit(300);
        $r = R::range($request->all());
        abort_if($r['from'] === '', 422);
        $weeks = R::weeks($r['from'], $r['to']);
        $rows = R::shown($this->report->rows($r['from'], $r['to']), $request->all(), $weeks);

        return Excel::download(new HourReportNewExport($rows, $weeks),
            date('d_M_Y', strtotime($r['from'])).'_'.date('d_M_Y', strtotime($r['to'])).'_Hour_Report.xlsx');
    }
}
