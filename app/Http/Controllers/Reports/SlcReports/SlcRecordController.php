<?php

namespace App\Http\Controllers\Reports\SlcReports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Services\Reports\SlcRecord;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › SLC Reports › SLC Record Report, "New version" tab (user's web and PDF designs, 25 Nov
 * 2026): the figures of the chosen intakes, the students behind a count, the Excel summary, each
 * intake's student lists, and the PDF. The old version (SlcRecordReportController) stays on its
 * own tab. {semesters} = semester ids joined by "_" (as the old report's links).
 */
class SlcRecordController extends Controller
{
    public function data(string $semesters)
    {
        $data = SlcRecord::build($this->ids($semesters));
        if (empty($data['intakes'])) {
            return response()->json(['message' => 'Those intake semesters were not found.'], 404);
        }

        return response()->json($data);
    }

    /** GET ?set=lcc|franchise&course=all|creation id&key=lcc|awb|…|gap: the students behind one count. */
    public function students(Request $request, int $semester)
    {
        $in = $request->validate([
            'set' => ['required', 'in:'.implode(',', array_keys(SlcRecord::SETS))],
            'course' => ['required', 'regex:/^(all|\d+)$/'],
            'key' => ['required', 'in:'.implode(',', array_keys(SlcRecord::KEYS))],
        ]);
        $sets = SlcRecord::sets([$semester]);
        abort_if(empty($sets), 404);
        $set = $sets[$semester]['sets'][$in['set']];
        if ($in['course'] === 'all') {
            $ids = $set['all'][$in['key']];
            $course = null;
        } else {
            abort_unless(isset($set['courses'][(int) $in['course']]), 404);
            $ids = $set['courses'][(int) $in['course']]['ids'][$in['key']];
            $course = $set['courses'][(int) $in['course']]['name'];
        }

        return response()->json([
            'intake' => SlcRecord::label($sets[$semester]['name']),
            'course' => $course,
            'set' => SlcRecord::SETS[$in['set']],
            'title' => SlcRecord::KEYS[$in['key']],
            'rows' => SlcRecord::students($ids),
        ]);
    }

    /** GET ?set: one row per intake and course, and the total. */
    public function excel(Request $request, string $semesters)
    {
        $set = $this->set($request);
        $data = SlcRecord::build($this->ids($semesters));
        abort_if(empty($data['intakes']), 404);

        $sheet = [1 => ['Intake', 'Course', 'Registered at LCC', 'Registered with awarding body', 'SLC registered', '% of LCC', 'Not yet SLC registered', 'SLC attendance confirmed', 'SLC withdrawn', 'Withdrawn or intermittent', 'Self funded']];
        $line = fn ($intake, $course, array $v) => [$intake, $course, $v[0], $v[1], $v[2], $v[0] ? round($v[2] * 100 / $v[0], 1) : '', $v[7], $v[3], $v[4], $v[5], $v[6]];
        $total = array_fill(0, 8, 0);
        foreach ($data['intakes'] as $intake) {
            $s = $intake['sets'][$set];
            $sheet[] = $line(SlcRecord::label($intake['name']), 'All courses', $s['v']);
            foreach ($s['courses'] as $c) {
                $sheet[] = $line(SlcRecord::label($intake['name']), $c['name'], $c['v']);
            }
            foreach ($s['v'] as $k => $x) {
                $total[$k] += $x;
            }
        }
        if (count($data['intakes']) > 1) {
            $sheet[] = $line('All selected intakes', '', $total);
        }

        return Excel::download(new ArrayCollectionExport($sheet, SlcRecord::SETS[$set]), $this->fileName($data, $set, 'xlsx'));
    }

    /** GET ?set: one intake's students under each figure (as the old per-intake Export). */
    public function details(Request $request, int $semester)
    {
        $set = $this->set($request);
        $sets = SlcRecord::sets([$semester]);
        abort_if(empty($sets), 404);

        $sheet = [1 => ['Registration No', 'Name', 'Intake', 'Course', 'Status']];
        foreach (SlcRecord::KEYS as $key => $title) {
            $rows = SlcRecord::students($sets[$semester]['sets'][$set]['all'][$key]);
            $sheet[] = [$title.' ('.count($rows).')'];
            foreach ($rows as $r) {
                $sheet[] = [$r[1], $r[2], $r[3], $r[4], $r[5]];
            }
            if (empty($rows)) {
                $sheet[] = ['No students.'];
            }
            $sheet[] = [''];
        }
        $name = 'SLC_Record_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $sets[$semester]['name']), '_').'_'.($set === 'franchise' ? 'franchise_' : '').'students.xlsx';

        return Excel::download(new ArrayCollectionExport($sheet, 'Students'), $name);
    }

    /** GET ?set: A4 landscape, as the design's print layout. */
    public function pdf(Request $request, string $semesters)
    {
        $set = $this->set($request);
        $data = SlcRecord::build($this->ids($semesters));
        abort_if(empty($data['intakes']), 404);

        $view = $data + ['set' => $set, 'logo' => ReportPdfLogo::dataUri(), 'generated' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.reports.slc.record.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->fileName($data, $set, 'pdf'));
    }

    private function ids(string $semesters): array
    {
        return array_values(array_unique(array_filter(array_map('intval', explode('_', $semesters)))));
    }

    private function set(Request $request): string
    {
        return $request->validate(['set' => ['nullable', 'in:'.implode(',', array_keys(SlcRecord::SETS))]])['set'] ?? 'lcc';
    }

    private function fileName(array $data, string $set, string $ext): string
    {
        $names = implode('_', array_map(fn ($i) => $i['name'], array_slice($data['intakes'], 0, 3))).(count($data['intakes']) > 3 ? '_and_more' : '');

        return 'SLC_Record_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $names), '_').($set === 'franchise' ? '_franchise' : '').'.'.$ext;
    }
}
