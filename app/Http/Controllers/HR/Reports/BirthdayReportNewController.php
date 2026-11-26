<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\BirthdayListNewExport;
use App\Http\Controllers\Controller;
use App\Services\HR\BirthdayList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Birthday List, new version (user's birthday-list-report design, 26 Nov 2026).
 * The old version (BirthdayReportController) is unchanged and sits beside it on the same page.
 */
class BirthdayReportNewController extends Controller
{
    public function __construct(private BirthdayList $report)
    {
    }

    /** Every active and inactive staff member; the screen filters and works out dates itself. */
    public function data()
    {
        return response()->json([
            'today' => Carbon::today()->format('Y-m-d'),
            'staff' => $this->report->staff(),
        ]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $data = $this->report->build(BirthdayList::filters($request->all()));
        $view = ['data' => $data, 'scope' => BirthdayList::scope($data)];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.birthday.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->fileName($data, 'pdf'));
    }

    public function excel(Request $request)
    {
        $data = $this->report->build(BirthdayList::filters($request->all()));

        return Excel::download(new BirthdayListNewExport($data), $this->fileName($data, 'xlsx'));
    }

    private function fileName(array $data, string $ext): string
    {
        $month = $data['filters']['month'] ? '_'.BirthdayList::MONTHS[$data['filters']['month'] - 1] : '';

        return 'Birthday_List'.$month.'_'.$data['today']->format('Y-m-d').'.'.$ext;
    }
}
