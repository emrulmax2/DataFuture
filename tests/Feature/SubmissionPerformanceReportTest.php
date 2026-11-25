<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\TermPerformance\TermSubmissionPerformanceReportController;
use App\Models\User;
use App\Services\Reports\SubmissionPerformance;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Term Performance › Submission Performance "New version" (user, 25 Nov 2026): the old report's
 * figures (refineResult) for first submission and final result, the grades behind "not accounted
 * for", the JSON, the PDF and the Excel. Read-only against the dev database.
 */
class SubmissionPerformanceReportTest extends TestCase
{
    use DatabaseTransactions;

    private int $term;

    protected function setUp(): void
    {
        parent::setUp();
        // The term with the most results on its plans.
        $term = DB::table('results as r')->join('plans as p', 'p.id', '=', 'r.plan_id')->whereNull('p.deleted_at')
            ->groupBy('p.term_declaration_id')->orderByRaw('COUNT(*) DESC')->value('p.term_declaration_id');
        if (!$term) {
            $this->markTestSkipped('No results in this database.');
        }
        $this->term = (int) $term;
        $this->actingAs(User::find(7));
    }

    public function test_figures_match_the_old_report(): void
    {
        $new = SubmissionPerformance::build($this->term);
        $old = app(TermSubmissionPerformanceReportController::class)->refineResult($this->term)['result'][$this->term];
        $map = ['exp_submission' => 'expected', 'no_of_submission' => 'sub', 'grade_pass' => 'P', 'grade_merit' => 'M', 'grade_distinction' => 'D', 'grade_reffered' => 'R', 'grade_plagiarised' => 'C', 'grade_absent' => 'A'];

        $courses = collect($new['courses'])->keyBy('id');
        foreach ($old['course'] as $courseId => $o) {
            $n = $courses[$courseId] ?? null;
            if ((int) $o['f_sub']['student_status'] === 0 && (int) $o['f_sub']['exp_submission'] === 0) {
                $this->assertNull($n, 'a course with nothing to show is left out');
                continue;
            }
            $this->assertSame((int) $o['f_sub']['student_status'], $n['students'], $o['name']);
            foreach (['f_sub' => 'first', 'all_sub' => 'final'] as $ok => $nk) {
                foreach ($map as $okey => $nkey) {
                    $this->assertSame((int) $o[$ok][$okey], $n[$nk][$nkey], "{$o['name']} $ok $okey");
                }
            }
        }
        foreach (['f_sub' => 'first', 'all_sub' => 'final'] as $ok => $nk) {
            $this->assertSame((int) $old[$ok]['no_of_submission'], $new['totals'][$nk]['sub']);
            $this->assertSame((int) $old[$ok]['total_pass'], SubmissionPerformance::passed($new['totals'][$nk]));
        }
    }

    public function test_other_grades_explain_the_gap(): void
    {
        $T = SubmissionPerformance::build($this->term)['totals'];
        foreach (['first', 'final'] as $which) {
            $f = $T[$which];
            $categorised = $f['D'] + $f['M'] + $f['P'] + $f['R'] + $f['C'] + $f['A'];
            $this->assertSame($f['expected'] - $categorised, array_sum($f['other']), $which);
            // Grade 8 (Submitted) is the only one outside the categories that counts as submitted.
            $this->assertSame($f['sub'], $f['D'] + $f['M'] + $f['P'] + $f['R'] + $f['C'] + ($f['other'][8] ?? 0));
        }
    }

    public function test_the_screen_json(): void
    {
        $this->getJson(route('reports.term.performance.submission.new.generate', $this->term))
            ->assertOk()
            ->assertJsonPath('term.id', $this->term)
            ->assertJsonStructure(['term' => ['id', 'name'], 'grades', 'totals' => ['students', 'first' => ['expected', 'sub', 'D', 'M', 'P', 'R', 'C', 'A', 'other'], 'final'], 'courses' => [['id', 'name', 'students', 'first', 'final']]]);

        $this->getJson(route('reports.term.performance.submission.new.generate', 99999999))->assertNotFound();
    }

    /** The Full figures counts open their students (user, 25 Nov 2026): each list adds up to its count. */
    public function test_student_lists_add_up_to_the_counts(): void
    {
        $data = SubmissionPerformance::build($this->term);
        $cols = array_merge([['id' => 'all', 'students' => $data['totals']['students'], 'first' => $data['totals']['first'], 'final' => $data['totals']['final']]], array_slice($data['courses'], 0, 2));
        $count = fn ($f, $key) => match ($key) {
            'passed' => SubmissionPerformance::passed($f),
            'other' => array_sum($f['other']),
            default => $f[$key],
        };
        foreach ($cols as $col) {
            foreach (['first', 'final'] as $which) {
                foreach (array_diff(SubmissionPerformance::FIGURES, ['students']) as $key) {
                    $hits = SubmissionPerformance::students($this->term, (string) $col['id'], $which, $key);
                    $this->assertSame($count($col[$which], $key), array_sum(array_column($hits, 'count')), "{$col['id']} $which $key");
                }
            }
            $students = SubmissionPerformance::students($this->term, (string) $col['id'], 'final', 'students');
            $this->assertLessThanOrEqual($col['students'], count($students), 'a student on two courses is listed once');
            $this->assertSame($col['final']['expected'], array_sum(array_column($students, 'count')));
        }

        $page = $this->getJson(route('reports.term.performance.submission.new.students', ['term' => $this->term, 'course' => 'all', 'which' => 'final', 'key' => 'M', 'sort' => 'name']))
            ->assertOk()->assertJsonStructure(['total', 'assignments', 'page', 'last_page', 'per_page', 'rows' => [['id', 'registration_no', 'name', 'url', 'course', 'status', 'due', 'flags', 'count', 'grades']]]);
        $this->assertSame($data['totals']['final']['M'], $page->json('assignments'));
        $names = array_column($page->json('rows'), 'name');
        $sorted = $names;
        usort($sorted, 'strnatcasecmp');
        $this->assertSame($sorted, $names, 'sorted by name');

        $this->getJson(route('reports.term.performance.submission.new.students', ['term' => $this->term, 'course' => 'all', 'which' => 'final', 'key' => 'X']))->assertStatus(422);
        $this->getJson(route('reports.term.performance.submission.new.students', ['term' => 99999999, 'course' => 'all', 'which' => 'final', 'key' => 'M']))->assertNotFound();
    }

    public function test_pdf_and_excel(): void
    {
        $pdf = $this->get(route('reports.term.performance.submission.new.pdf', $this->term));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        Excel::fake();
        $data = SubmissionPerformance::build($this->term);
        $this->get(route('reports.term.performance.submission.new.excel', $this->term))->assertOk();
        $name = 'Submission_Performance_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $data['term']['name']), '_').'.xlsx';
        Excel::assertDownloaded($name, function (ArrayCollectionExport $export) use ($data) {
            $rows = $export->array();
            $last = end($rows);

            return count($rows) === count($data['courses']) + 2 && $last[0] === 'All courses' && $last[4] === $data['totals']['first']['sub'];
        });
    }
}
