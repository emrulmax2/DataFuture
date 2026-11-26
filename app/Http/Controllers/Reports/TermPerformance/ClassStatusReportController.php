<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\ClassStatusSummary;
use App\Support\ReportPdfLogo;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Term Performance › Class Status, "New version" tab (user's class-status-report
 * design, 25 Nov 2026): JSON for the screen, the PDF (new) and Excel. The old version
 * (Reports\ClassStatusByTermController) stays on its own tab until it is retired.
 */
class ClassStatusReportController extends Controller
{
    public function generate(int $term)
    {
        $data = ClassStatusSummary::build($term);
        if ($data === null) {
            return response()->json(['message' => 'That attendance term was not found.'], 404);
        }

        return response()->json($data);
    }

    /** A4 landscape, every course with all its groups (the design's print layout). */
    public function pdf(int $term)
    {
        $data = ClassStatusSummary::build($term);
        abort_if($data === null, 404);

        $view = $data + ['logo' => ReportPdfLogo::dataUri(), 'generated' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.reports.term-performance.class-status.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->fileName($data, 'pdf'));
    }

    /** One row per course, then its groups, with every figure. */
    public function excel(int $term)
    {
        $data = ClassStatusSummary::build($term);
        abort_if($data === null, 404);

        $pct = fn ($row) => ClassStatusSummary::rate($row) === null ? '' : round(ClassStatusSummary::rate($row), 1);
        $line = fn ($course, $group, $row) => [
            $course, $group, $row['scheduled'], $row['held'], $pct($row), $row['not_held'], $row['proxy'], $row['held'] - $row['proxy'],
            $row['cancelled'], $row['unknown'], $row['future'],
        ];
        $sheet = [1 => ['Course', 'Group', 'Scheduled (due so far)', 'Held', 'Held %', 'Not held', 'Held by proxy', 'Held by scheduled tutor', 'Cancelled', 'Unknown', 'Still to come']];
        foreach ($data['courses'] as $course) {
            $sheet[] = $line($course['name'], 'All groups', $course);
            foreach ($course['groups'] as $group) {
                $sheet[] = $line($course['name'], $group['name'], $group);
            }
        }
        $sheet[] = $line('All courses', '', $data['totals']);

        return Excel::download(new ArrayCollectionExport($sheet, 'Class Status'), $this->fileName($data, 'xlsx'));
    }

    private function fileName(array $data, string $ext): string
    {
        return 'Class_Status_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $data['term']['name']), '_').'.'.$ext;
    }
}
