<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\RecordCardNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\RecordCardReport as R;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Record Card, new version (user's design, 26 Nov 2026).
 * The old version (RecordCardController) is unchanged and sits beside it on the same page.
 */
class RecordCardNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    /** Every active and inactive staff member's card; the screen filters itself. */
    public function data()
    {
        // photoPath is the server file (for the PDF); the browser only gets the photo URL.
        return response()->json(['staff' => array_map(function ($r) { unset($r['photoPath']); return $r; }, $this->report->staff())]);
    }

    /** One A4 page per card ("Record X of N" in the footer, so one pass is enough). */
    public function pdf(Request $request)
    {
        set_time_limit(600);
        $today = Carbon::today();
        $list = R::rows($this->report->staff($today), R::filters($request->all()));
        $name = count($list) === 1 ? 'Record_Card_'.preg_replace('/[^A-Za-z0-9]+/', '_', trim($list[0]['forename'].' '.$list[0]['surname'])) : 'Employee_Record_Cards';

        return app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.record.pdf', ['list' => $list, 'printed' => $today->format('j F Y')])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->download(trim($name, '_').'_'.$today->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        return Excel::download(new RecordCardNewExport(R::rows($this->report->staff(), R::filters($request->all()))), 'Employee_Record_Cards_'.now()->format('Y-m-d').'.xlsx');
    }
}
