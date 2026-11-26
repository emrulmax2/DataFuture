<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\TermDeclaration;
use App\Services\Reports\TermRetention;
use App\Services\Reports\TermRetentionChart;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Term Performance › Retention, "New version" tab (user's term-retention-report design,
 * 25 Nov 2026): JSON for the screen, the students behind a count, the PDF and Excel. The old
 * version (TermRetentionReportController) stays on its own tab.
 */
class RetentionByIntakeController extends Controller
{
    /** {terms} = term ids joined by "_" (as the old report's links). */
    public function generate(string $terms)
    {
        $data = $this->data($terms);
        if ($data === null) {
            return response()->json(['message' => 'Those attendance terms were not found.'], 404);
        }

        return response()->json($data);
    }

    /**
     * The students behind a count: one intake of a term ($intake = semester id) or the whole
     * term ("all"); $key total, registered, stayed or dropped. Every row at once (an intake is a
     * few hundred students): the dialog searches and pages them.
     */
    public function students(Request $request, int $term)
    {
        $in = $request->validate([
            'intake' => ['required', 'regex:/^(all|\d+)$/'],
            'key' => ['required', 'in:'.implode(',', TermRetention::KEYS)],
        ]);
        $declaration = TermDeclaration::find($term);
        if (!$declaration) {
            return response()->json(['message' => 'That attendance term was not found.'], 404);
        }

        $intakes = TermRetention::outcomes($declaration);
        if ($in['intake'] !== 'all') {
            $intakes = array_intersect_key($intakes, [(int) $in['intake'] => true]);
        }
        $picked = [];
        foreach ($intakes as $semesterId => $intake) {
            foreach ($intake['students'] as $id => $s) {
                if (TermRetention::inList($in['key'], $s['outcome'])) {
                    $picked[$id] = $s + ['intake' => $intake['name']];
                }
            }
        }

        $people = [];
        foreach (array_chunk(array_keys($picked), 2000) as $chunk) {
            DB::table('students as s')->leftJoin('statuses as st', 'st.id', '=', 's.status_id')->whereIn('s.id', $chunk)
                ->get(['s.id', 's.registration_no', 's.application_no', 's.first_name', 's.last_name', 'st.name as status'])
                ->each(function ($p) use (&$people) {
                    $people[$p->id] = $p;
                });
        }
        $courses = DB::table('course_creations as cc')->join('courses as c', 'c.id', '=', 'cc.course_id')
            ->whereIn('cc.id', array_unique(array_column($picked, 'creation')))->pluck('c.name', 'cc.id');
        $statuses = DB::table('statuses')->whereIn('id', array_filter(array_column($picked, 'left_status')))->pluck('name', 'id');

        $order = ['dropped' => 0, 'stayed' => 1, 'none' => 2];
        $rows = [];
        foreach ($picked as $id => $s) {
            $p = $people[$id] ?? null;
            $rows[] = [
                'id' => $id,
                'registration_no' => (string) ($p ? ($p->registration_no ?: $p->application_no) : ''),
                'name' => $p ? trim($p->first_name.' '.$p->last_name) : 'Student #'.$id,
                'intake' => self::nice($s['intake']),
                'course' => (string) ($courses[$s['creation']] ?? ''),
                'outcome' => $s['outcome'],
                'status' => (string) ($p->status ?? ''),
                'left' => $s['left'] ? date('j M Y', strtotime($s['left'])) : null,
                'left_status' => $s['left_status'] ? (string) ($statuses[$s['left_status']] ?? '') : null,
                'url' => route('student.show', $id),
            ];
        }
        usort($rows, fn ($a, $b) => $order[$a['outcome']] <=> $order[$b['outcome']] ?: strnatcasecmp($a['registration_no'], $b['registration_no']) ?: $a['id'] <=> $b['id']);

        return response()->json([
            'term' => ['id' => (int) $declaration->id, 'name' => self::nice($declaration->name)],
            'intake' => $in['intake'] === 'all' ? null : self::nice((string) (reset($intakes)['name'] ?? '')),
            'key' => $in['key'],
            'rows' => $rows,
        ]);
    }

    /** A4 landscape, as the design's print layout. */
    public function pdf(string $terms)
    {
        $data = $this->data($terms);
        abort_if($data === null, 404);

        $view = $data + [
            'chart' => TermRetentionChart::svg($data['series']),
            'logo' => ReportPdfLogo::dataUri(),
            'generated' => now()->format('j M Y'),
        ];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.reports.term-performance.retention.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->fileName($data, 'pdf'));
    }

    /** Each term, then its intakes. */
    public function excel(string $terms)
    {
        $data = $this->data($terms);
        abort_if($data === null, 404);

        $pct = fn ($rate) => $rate === null ? '' : round($rate, 2);
        $sheet = [1 => ['Term', 'Intake', 'Term of study', 'Total students', 'Registered', 'Stayed', 'Dropped out', 'Retention rate %', 'Not counted: had already left']];
        foreach ($data['terms'] as $t) {
            $sheet[] = [$t['label'], 'All intakes', '', $t['total'], $t['registered'], $t['stayed'], $t['dropped'], $pct($t['rate']), $t['left_before']];
            foreach ($t['intakes'] as $in) {
                $sheet[] = [$t['label'], $in['label'], $in['study'] ? TermRetention::ordinal($in['study']).' term' : '', $in['total'], $in['registered'], $in['stayed'], $in['dropped'], $pct($in['rate']), $in['left_before']];
            }
        }

        return Excel::download(new ArrayCollectionExport($sheet, 'Retention'), $this->fileName($data, 'xlsx'));
    }

    /** The report for "53_49": terms (newest first) with display names, the chart lines and the takeaway. */
    private function data(string $terms): ?array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', explode('_', $terms)))));
        $built = TermRetention::build($ids);
        if (empty($built['terms'])) {
            return null;
        }
        foreach ($built['terms'] as &$t) {
            $t['label'] = self::nice($t['name']);
            foreach ($t['intakes'] as &$in) {
                $in['label'] = self::nice($in['name']);
            }
            unset($in);
        }
        unset($t);
        $series = TermRetention::series($built['terms']);
        foreach ($series as &$s) {
            $s['label'] = self::nice($s['name']);
        }
        unset($s);

        return $built + ['series' => $series, 'takeaway' => TermRetention::takeaway($series)];
    }

    /** The design writes "2026 January" as "January 2026" ("2026 January(ULaw)" → "January 2026(ULaw)"). */
    public static function nice(string $name): string
    {
        return preg_match('/^(\d{4})\s+([A-Za-z]+)(.*)$/', trim($name), $m) ? $m[2].' '.$m[1].$m[3] : $name;
    }

    private function fileName(array $data, string $ext): string
    {
        $names = implode('_', array_map(fn ($t) => $t['name'], array_slice($data['terms'], 0, 4))).(count($data['terms']) > 4 ? '_and_more' : '');

        return 'Retention_Report_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $names), '_').'.'.$ext;
    }
}
