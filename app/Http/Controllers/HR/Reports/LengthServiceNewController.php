<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\ServiceLengthsNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\ServiceLengthsReport as R;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Service Lengths, new version (user's design, 26 Nov 2026).
 * The old version (LengthServiceController) is unchanged and sits beside it on the same page.
 */
class LengthServiceNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    /** Every active and inactive staff member with their service worked out; the screen filters itself. */
    public function data()
    {
        return response()->json(['today' => Carbon::today()->format('Y-m-d'), 'staff' => $this->report->staff()]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $today = Carbon::today();
        $f = R::filters($request->all());
        $d = R::build(R::rows($this->report->staff($today), $f), $f, $today);
        $view = ['d' => $d, 'f' => $f, 'today' => $today];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.service.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download('Employee_Service_Lengths_'.$today->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        $today = Carbon::today();
        $f = R::filters($request->all());
        $d = R::build(R::rows($this->report->staff($today), $f), $f, $today);

        return Excel::download(new ServiceLengthsNewExport($d['shown'], $d['noStart']), 'Employee_Service_Lengths_'.$today->format('Y-m-d').'.xlsx');
    }
}
