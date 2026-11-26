<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\HR\DiversityReportNewExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\EmployeeWorkType;
use App\Services\HR\DiversityReport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * HR Portal › Reports › Diversity Information, new version (user's design, 26 Nov 2026).
 * The old version (DiversityReportController) is unchanged and sits beside it on the same page.
 */
class DiversityReportNewController extends Controller
{
    public function __construct(private DiversityReport $report)
    {
    }

    /** Every active and inactive staff member; the screen filters and counts itself. */
    public function data()
    {
        return response()->json(['staff' => $this->report->staff()]);
    }

    public function pdf(Request $request)
    {
        set_time_limit(300);
        $f = DiversityReport::filters($request->all());
        $list = $this->report->rows($f);
        $view = ['list' => $list, 'f' => $f, 'scope' => $this->scope($f), 'printed' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.hr.portal.reports.diversity.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download('Diversity_Information_'.now()->format('Y-m-d').'.pdf');
    }

    public function excel(Request $request)
    {
        $f = DiversityReport::filters($request->all());
        $list = $this->report->rows($f);
        if ($f['pick'] !== '') {
            $dim = DiversityReport::dimension($f['dim'], $list);
            $list = array_values(array_filter($list, fn ($p) => ($dim['of'])($p) === $f['pick']));
        }

        return Excel::download(new DiversityReportNewExport($list), 'Diversity_Information_'.now()->format('Y-m-d').'.xlsx');
    }

    /** "Active staff · Academic · Employee" for the PDF head. */
    private function scope(array $f): string
    {
        $parts = [['1' => 'Active staff', '0' => 'Inactive staff', '2' => 'Active and inactive staff'][$f['status']]];
        if ($f['department'] !== '') $parts[] = (string) Department::whereKey($f['department'])->value('name');
        if ($f['type'] !== '') $parts[] = (string) EmployeeWorkType::whereKey($f['type'])->value('name');
        if ($f['from'] !== '') $parts[] = 'started by '.date('j M Y', strtotime($f['from']));
        if ($f['to'] !== '') $parts[] = 'left on or after '.date('j M Y', strtotime($f['to']));

        return implode(' · ', array_filter($parts));
    }
}
