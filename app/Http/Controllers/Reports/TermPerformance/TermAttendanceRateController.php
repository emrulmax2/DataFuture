<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Http\Controllers\Controller;
use App\Services\Reports\TermAttendanceChart;
use App\Services\Reports\TermAttendanceTree;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;

/**
 * Reports › Term Performance › Attendance Rates, "New version" tab (user, 24 Nov 2026). The old
 * version (TermAttendancePerformanceReportController) stays on its own tab until it is retired.
 */
class TermAttendanceRateController extends Controller
{
    /** The whole term as a tree (JSON); the screen drills down without asking again. */
    public function generate(int $term)
    {
        $data = TermAttendanceTree::build($term);
        if ($data === null) {
            return response()->json(['message' => 'That attendance semester was not found.'], 404);
        }

        return response()->json($data);
    }

    /**
     * PDF of one level (user's attendance-term-report-pdf design): the term, or a course / group
     * named by ?path=courseId/groupId (node ids), with the chart lines named by ?show (the ones
     * switched on on screen; else the screen's default).
     */
    public function pdf(Request $request, int $term)
    {
        $data = TermAttendanceTree::build($term);
        abort_if($data === null, 404);

        // Follow the path down the tree; stop where it no longer matches.
        $trail = [$data['tree']];
        foreach (array_filter(explode('/', (string) $request->query('path', ''))) as $id) {
            $next = collect(end($trail)['children'])->firstWhere('id', $id);
            if (!$next || count($trail) > 2) {
                break;
            }
            $trail[] = $next;
        }
        $node = end($trail);
        $depth = count($trail) - 1;

        $show = array_filter(explode(',', (string) $request->query('show', '')));
        $chart = TermAttendanceChart::svg($node, $data['weeks'], $show);

        $view = [
            'term' => $data['term'],
            'weeks' => $data['weeks'],
            'node' => $node,
            'depth' => $depth,
            'trail' => $trail,
            'chart' => $chart,
            'logo' => ReportPdfLogo::dataUri(),
            'generated' => now()->format('j M Y'),
        ];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.reports.term-performance.attendance.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        $name = 'Attendance_'.preg_replace('/[^A-Za-z0-9]+/', '_', $data['term']['name'].($depth > 0 ? ' '.$node['name'] : ''));

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download(trim($name, '_').'.pdf');
    }
}
