<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\EligibilityExpiryNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\EligibilityExpiry as R;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Eligibility Expiry, new version (user's design, 26 Nov 2026).
 * The old version (EligibilityReportController) is unchanged and sits beside it on the same page.
 */
class EligibilityReportNewController extends Controller
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
            ->loadView('pages.hr.portal.reports.eligibility.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->name($f, 'pdf', $today));
    }

    public function excel(Request $request)
    {
        $today = Carbon::today();
        $f = R::filters($request->all());
        $d = R::build(R::rows($this->report->staff($today), $f), $f, $today);

        return Excel::download(new EligibilityExpiryNewExport($d['rows'], $f['kind']), $this->name($f, 'xlsx', $today));
    }

    private function name(array $f, string $ext, Carbon $today): string
    {
        return ($f['kind'] === 'doc' ? 'Passport_ID_Expiry_' : 'Visa_Permit_Expiry_').$today->format('Y-m-d').'.'.$ext;
    }
}
