<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\StarterReportNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\StarterReport as R;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Starter Information, new version (user's design, 26 Nov 2026).
 * The old version (StarterReportController) is unchanged and sits beside it on the same page.
 */
class StarterReportNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    public function data()
    {
        return response()->json(['today' => Carbon::today()->format('Y-m-d'), 'staff' => $this->report->staff()]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $today = Carbon::today();
        $f = R::filters($request->all());
        $view = ['d' => R::build(R::rows($this->report->staff($today), $f), $f, $today), 'f' => $f, 'today' => $today];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.starter.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download('Employee_Starters_'.$today->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        $today = Carbon::today();
        $f = R::filters($request->all());
        $d = R::build(R::rows($this->report->staff($today), $f), $f, $today);

        return Excel::download(new StarterReportNewExport($d['shown'], $d['noStart']), 'Employee_Starters_'.$today->format('Y-m-d').'.xlsx');
    }
}
