<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\SlcAgreement;
use App\Models\Student;
use App\Services\Reports\SubmissionPerformance;
use App\Support\ReportPdfLogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Reports › Term Performance › Submission Performance, "New version" tab (user's
 * submission-performance-report design, 25 Nov 2026): JSON for the screen, the PDF and Excel.
 * The old version (TermSubmissionPerformanceReportController) stays on its own tab.
 */
class SubmissionPerformanceController extends Controller
{
    public function generate(int $term)
    {
        $data = SubmissionPerformance::build($term);
        if ($data === null) {
            return response()->json(['message' => 'That attendance term was not found.'], 404);
        }

        return response()->json($data);
    }

    /** A4 landscape: the summary, then the full figures from page 2 (the design's print layout). */
    public function pdf(int $term)
    {
        $data = SubmissionPerformance::build($term);
        abort_if($data === null, 404);

        $view = $data + ['logo' => ReportPdfLogo::dataUri(), 'hatch' => self::hatch(), 'generated' => now()->format('j M Y')];
        $make = fn (int $pages) => app('dompdf.wrapper')
            ->loadView('pages.reports.term-performance.submission.pdf', $view + ['pages' => $pages])
            ->setPaper('a4', 'landscape')
            ->setWarnings(false);
        // Rendered twice so the footer can say "Page X of Y".
        $first = $make(0);
        $first->render();

        return $make((int) $first->getDomPDF()->getCanvas()->get_page_count())->download($this->fileName($data, 'pdf'));
    }

    /** One row per course and the total, first submission and final result side by side. */
    public function excel(int $term)
    {
        $data = SubmissionPerformance::build($term);
        abort_if($data === null, 404);

        $pct = fn ($a, $b) => $b > 0 ? round($a * 100 / $b, 2) : '';
        $heads = ['Course', 'Students'];
        foreach (['Expected', 'Submitted', 'Submission rate %', 'Distinction', 'Merit', 'Pass', 'Total passed', 'Pass rate %', 'Referred', 'Plagiarised', 'Not submitted', 'Other grades'] as $h) {
            $heads[] = $h.' (first)';
            $heads[] = $h.' (final)';
        }
        $line = function (string $name, array $row) use ($pct) {
            $out = [$name, $row['students']];
            foreach ([
                fn ($f) => $f['expected'], fn ($f) => $f['sub'], fn ($f) => $pct($f['sub'], $f['expected']),
                fn ($f) => $f['D'], fn ($f) => $f['M'], fn ($f) => $f['P'], fn ($f) => SubmissionPerformance::passed($f),
                fn ($f) => $pct(SubmissionPerformance::passed($f), $f['sub']), fn ($f) => $f['R'], fn ($f) => $f['C'], fn ($f) => $f['A'],
                fn ($f) => array_sum($f['other']),
            ] as $get) {
                $out[] = $get($row['first']);
                $out[] = $get($row['final']);
            }

            return $out;
        };
        $sheet = [1 => $heads];
        foreach ($data['courses'] as $course) {
            $sheet[] = $line($course['name'], $course);
        }
        $sheet[] = $line('All courses', $data['totals']);

        return Excel::download(new ArrayCollectionExport($sheet, 'Submission Performance'), $this->fileName($data, 'xlsx'));
    }

    /**
     * The students behind one count of the Full figures table (user, 25 Nov 2026; the old version
     * links its per-course counts to a student list too). Worked out again here from the term,
     * course, side and figure, so no list of ids travels in the URL. 50 a page.
     */
    public function students(Request $request, int $term)
    {
        $in = $request->validate([
            'course' => ['required', 'regex:/^(all|\d+)$/'],
            'which' => ['required', 'in:first,final'],
            'key' => ['required', Rule::in(SubmissionPerformance::FIGURES)],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort' => ['nullable', 'in:count,name,registration_no'],
            'dir' => ['nullable', 'in:asc,desc'],
        ]);
        $hits = SubmissionPerformance::students($term, $in['course'], $in['which'], $in['key']);
        if ($hits === null) {
            return response()->json(['message' => 'That attendance term was not found.'], 404);
        }

        // Sort every student of the figure, then load only the page.
        $sort = $in['sort'] ?? 'count';
        $dir = ($in['dir'] ?? ($sort === 'count' ? 'desc' : 'asc')) === 'desc' ? -1 : 1;
        $people = [];
        foreach (array_chunk(array_keys($hits), 2000) as $chunk) {
            foreach (Student::whereIn('id', $chunk)->get(['id', 'registration_no', 'application_no', 'first_name', 'last_name']) as $s) {
                $people[$s->id] = ['reg' => (string) ($s->registration_no ?: $s->application_no), 'name' => trim($s->first_name.' '.$s->last_name)];
            }
        }
        $ids = array_keys($people);
        $byName = fn ($a, $b) => strnatcasecmp($people[$a]['name'], $people[$b]['name']) ?: $a <=> $b;
        usort($ids, match ($sort) {
            'name' => fn ($a, $b) => $dir * $byName($a, $b),
            'registration_no' => fn ($a, $b) => $dir * (strnatcasecmp($people[$a]['reg'], $people[$b]['reg']) ?: $a <=> $b),
            default => fn ($a, $b) => $dir * ($hits[$a]['count'] <=> $hits[$b]['count']) ?: $byName($a, $b),
        });

        $perPage = 50;
        $total = count($ids);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min((int) ($in['page'] ?? 1), $lastPage);
        $pageIds = array_slice($ids, ($page - 1) * $perPage, $perPage);

        $students = Student::with(['status', 'other', 'activeCR.creation.course', 'activeCR.creation.semester', 'activeCR.propose'])
            ->whereIn('id', $pageIds)->get()->keyBy('id');
        $flags = DB::table('student_notes as sn')->join('student_flags as sf', 'sf.id', '=', 'sn.student_flag_id')
            ->whereIn('sn.student_id', $pageIds)->where('sn.is_flaged', 'Yes')->where('sn.flaged_status', 'Active')->whereNull('sn.deleted_at')
            ->groupBy('sn.student_id', 'sf.id', 'sf.name', 'sf.color')
            ->get(['sn.student_id', 'sf.name', 'sf.color'])->groupBy('student_id');
        $gradeNames = DB::table('grades')->pluck('name', 'id');
        $due = self::dueOf($students);

        $rows = [];
        foreach ($pageIds as $id) {
            $s = $students[$id] ?? null;
            if (!$s) {
                continue;
            }
            $creation = $s->activeCR->creation ?? null;
            $grades = $hits[$id]['grades'];
            arsort($grades);
            $rows[] = [
                'id' => $s->id,
                'registration_no' => $people[$id]['reg'],
                'name' => $people[$id]['name'],
                'photo_url' => $s->photo_url,
                'url' => route('student.show', $s->id),
                'course' => (string) ($creation->course->name ?? ''),
                'semester' => (string) ($creation->semester->name ?? ''),
                'status' => (string) ($s->status->name ?? ''),
                'full_time' => (int) ($s->activeCR->propose->full_time ?? 0) === 1,
                'disability' => (int) ($s->other->disability_status ?? 0) > 0,
                'due' => $due[$s->id] ?? 1,
                'flags' => ($flags[$id] ?? collect())->map(fn ($f) => ['name' => (string) $f->name, 'color' => strtolower((string) $f->color)])->values(),
                'count' => $hits[$id]['count'],
                'grades' => collect($grades)->map(fn ($n, $g) => ['name' => (string) ($gradeNames[$g] ?? 'No grade name'), 'count' => $n])->values(),
            ];
        }

        return response()->json([
            'total' => $total,
            'assignments' => array_sum(array_column($hits, 'count')),
            'page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'rows' => $rows,
        ]);
    }

    /**
     * Student::$due for a page of students in three queries (the accessor asks per student and took
     * ~70 ms each): 1 no SLC agreement on the active course, 2 nothing owed, 3 owed (instalment
     * under 30 days old or undated), 4 owed for over 30 days. As the accessor, the last owing
     * agreement decides.
     */
    private static function dueOf($students): array
    {
        $relation = [];
        foreach ($students as $s) {
            $relation[$s->id] = (int) ($s->activeCR->id ?? 0);
        }
        // claim_amount / received_amount sum these two relations; eager-loaded, they cost one query each.
        $agreements = SlcAgreement::with(['installments', 'payments'])->whereIn('student_id', array_keys($relation))->orderBy('id')->get()
            ->filter(fn ($a) => (int) $a->student_course_relation_id === ($relation[$a->student_id] ?? -1));

        $out = array_fill_keys(array_keys($relation), 1);
        foreach ($agreements->groupBy('student_id') as $studentId => $list) {
            $status = 2;
            foreach ($list as $agr) {
                if ((float) ($agr->claim_amount ?? 0) > (float) ($agr->received_amount ?? 0)) {
                    $date = $agr->installments->sortByDesc('id')->first()->installment_date ?? null;
                    $status = (!empty($date) && date('Y-m-d', strtotime('+30 Days', strtotime($date))) < date('Y-m-d')) ? 4 : 3;
                }
            }
            $out[$studentId] = $status;
        }

        return $out;
    }

    private function fileName(array $data, string $ext): string
    {
        return 'Submission_Performance_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $data['term']['name']), '_').'.'.$ext;
    }

    /** The design's hatched "not accounted for" fill as a small PNG tile (DomPDF has no gradients). */
    private static function hatch(): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }
        $img = imagecreatetruecolor(6, 6);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        $line = imagecolorallocate($img, 154, 163, 171);
        for ($i = 0; $i < 6; $i++) {
            imagesetpixel($img, $i, 5 - $i, $line);
        }
        ob_start();
        imagepng($img);

        return 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
    }
}
