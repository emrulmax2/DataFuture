<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\ContactDetailsNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\ContactDetailsReport as R;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Employee Contact Details, new version (user's design, 26 Nov 2026).
 * The old version (EmployeeContactDetailController) is unchanged and sits beside it on the same page.
 */
class EmployeeContactDetailNewController extends Controller
{
    public function __construct(private R $report)
    {
    }

    /** Every active and inactive staff member; the screen filters itself. */
    public function data()
    {
        return response()->json(['staff' => $this->report->staff()]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $f = R::filters($request->all());
        $list = R::shown(R::base($this->report->staff(), $f), $f);
        $view = ['list' => $list, 'f' => $f, 'printed' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.contact.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download('Employee_Contact_Details_'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        $f = R::filters($request->all());

        return Excel::download(new ContactDetailsNewExport(R::shown(R::base($this->report->staff(), $f), $f)), 'Employee_Contact_Details_'.now()->format('Y-m-d').'.xlsx');
    }
}
