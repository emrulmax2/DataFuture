<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\TelephoneDirectoryNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\TelephoneDirectory as R;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Telephone Directory, new version (user's design, 26 Nov 2026).
 * The old version (TelephoneDirectoryController) is unchanged and sits beside it on the same page.
 */
class TelephoneDirectoryNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    public function data()
    {
        return response()->json(['staff' => $this->report->staff()]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $f = R::filters($request->all());
        $list = R::shown(R::rows($this->report->staff(), $f), $f);
        $view = ['groups' => R::byLetter($list), 'n' => count($list), 'printed' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.directory.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download('Telephone_Directory_'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        $f = R::filters($request->all());
        $groups = R::byLetter(R::shown(R::rows($this->report->staff(), $f), $f));

        return Excel::download(new TelephoneDirectoryNewExport(array_merge(...array_values($groups ?: [[]]))), 'Telephone_Directory_'.now()->format('Y-m-d').'.xlsx');
    }
}
