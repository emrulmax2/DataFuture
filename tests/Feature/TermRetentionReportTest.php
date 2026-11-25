<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\TermPerformance\TermRetentionReportController;
use App\Models\User;
use App\Services\Reports\TermRetention;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Term Performance › Retention "New version" (user, 25 Nov 2026): the population is the old
 * report's (refineResult) less students who had already left; Registered is counted properly (the
 * old column was registered minus dropped out, or another course's list again); a dropout counts
 * once, in the term its leaving is recorded against (the old rule counted anyone who left at any
 * time in the course); the student lists, the JSON, the PDF and the Excel. Read-only against the
 * dev database.
 */
class TermRetentionReportTest extends TestCase
{
    use DatabaseTransactions;

    /** @var int[] the latest three terms that have started and have plans */
    private array $terms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->terms = DB::table('term_declarations as t')->whereNull('t.deleted_at')->where('t.start_date', '<=', now()->toDateString())
            ->whereExists(fn ($q) => $q->from('plans as p')->whereColumn('p.term_declaration_id', 't.id')->whereNull('p.deleted_at'))
            ->orderByDesc('t.start_date')->limit(3)->pluck('t.id')->map(fn ($id) => (int) $id)->all();
        if (empty($this->terms)) {
            $this->markTestSkipped('No terms with plans in this database.');
        }
        $this->actingAs(User::find(7));
    }

    /** The population is the old report's, less the students who had already left. */
    public function test_total_is_the_old_reports_less_earlier_leavers(): void
    {
        $old = app(TermRetentionReportController::class)->refineResult($this->terms)['result'];
        foreach (TermRetention::build($this->terms)['terms'] as $term) {
            $this->assertSame((int) $old[$term['id']]['admissions'], $term['total'] + $term['left_before'], $term['name']);
            $intakes = collect($term['intakes'])->keyBy('id');
            foreach ($old[$term['id']]['semester'] as $semesterId => $s) {
                $n = $intakes[$semesterId] ?? ['total' => 0, 'left_before' => 0];
                $this->assertSame((int) $s['admissions'], $n['total'] + $n['left_before'], "{$term['name']} / {$s['name']}");
            }
        }
    }

    /** New dropout rule (25 Nov 2026): once, in the term the leaving is recorded against. */
    public function test_a_dropout_counts_once_in_the_term_it_is_recorded_against(): void
    {
        $termOf = [];
        foreach ($this->terms as $tid) {
            foreach (TermRetention::outcomes(\App\Models\TermDeclaration::find($tid)) as $intake) {
                foreach ($intake['students'] as $id => $s) {
                    if ($s['outcome'] === 'dropped') {
                        $this->assertArrayNotHasKey($id, $termOf, "student $id is a dropout of one term only");
                        $termOf[$id] = $tid;
                    }
                }
            }
        }
        if (empty($termOf)) {
            $this->markTestSkipped('No dropouts in these terms.');
        }
        $status = DB::table('students')->whereIn('id', array_keys($termOf))->pluck('status_id', 'id');
        $dates = DB::table('term_declarations')->get(['id', 'start_date', 'end_date'])->keyBy('id');
        foreach ($termOf as $id => $tid) {
            $this->assertContains((int) $status[$id], TermRetention::DROPPED, "student $id still has a leaving status");
            $record = DB::table('student_attendance_term_statuses')->where('student_id', $id)->where('status_id', $status[$id])->whereNull('deleted_at')->orderByDesc('id')->first();
            $named = $record->term_declaration_id ? $dates[$record->term_declaration_id] : null;
            $term = $dates[$tid];
            if ($named) {
                $this->assertTrue($named->start_date <= $term->end_date && $named->end_date >= $term->start_date, "student $id: the record names an overlapping term");
            } else {
                $this->assertTrue(substr($record->status_change_date, 0, 10) >= $term->start_date && substr($record->status_change_date, 0, 10) <= $term->end_date, "student $id: dated in the term");
            }
        }
        $this->assertNotContains(42, TermRetention::DROPPED, 'leaving with the HNC is not a dropout');
    }

    public function test_where_a_leaving_falls(): void
    {
        $terms = [1 => ['2025-09-08', '2025-11-21'], 2 => ['2026-01-12', '2026-03-28'], 3 => ['2026-05-18', '2026-07-18'], 4 => ['2026-01-26', '2026-05-08']];
        $span = $terms[2];
        $rec = fn ($term, $date = '2026-04-08') => ['term' => $term, 'date' => $date, 'status' => 30];
        $this->assertNull(TermRetention::place(null, $span, '2024-06-17', $terms), 'has not left');
        $this->assertSame('here', TermRetention::place($rec(2), $span, '2024-06-17', $terms), 'recorded against the term, set after it ended');
        $this->assertSame('here', TermRetention::place($rec(4), $span, '2024-06-17', $terms), 'an overlapping term (ULaw) counts as this one');
        $this->assertSame('before', TermRetention::place($rec(1), $span, '2024-06-17', $terms), 'left in an earlier term');
        $this->assertNull(TermRetention::place($rec(3), $span, '2024-06-17', $terms), 'left in a later term');
        $this->assertSame('before', TermRetention::place($rec(2, '2026-01-30'), $span, '2026-02-09', $terms), 'within 28 days of the course start');
        $this->assertSame('here', TermRetention::place($rec(null, '2026-02-20'), $span, '2024-06-17', $terms), 'no term: by its date');
        $this->assertNull(TermRetention::place($rec(null, null), $span, '2024-06-17', $terms), 'no term and no date: cannot be placed');
    }

    public function test_figures_hold_together(): void
    {
        $built = TermRetention::build($this->terms)['terms'];
        $starts = DB::table('term_declarations')->whereIn('id', $this->terms)->pluck('start_date', 'id')->sortDesc();
        $this->assertSame($starts->keys()->map(fn ($id) => (int) $id)->all(), array_column($built, 'id'), 'newest term first');
        foreach ($built as $term) {
            foreach ($term['intakes'] as $in) {
                $label = "{$term['name']} / {$in['name']}";
                $this->assertGreaterThan(0, $in['total'], $label.': empty intakes are left out');
                $this->assertLessThanOrEqual($in['total'], $in['registered'], $label.': registered within the total');
                $this->assertSame($in['registered'] - $in['dropped'], $in['stayed'], $label);
                if ($in['registered'] > 0) {
                    $this->assertEqualsWithDelta($in['stayed'] * 100 / $in['registered'], $in['rate'], 0.0001, $label);
                }
            }
            $this->assertSame(array_sum(array_column($term['intakes'], 'registered')), $term['registered']);
            $this->assertSame(array_sum(array_column($term['intakes'], 'left_before')), $term['left_before']);
        }
    }

    public function test_term_of_study(): void
    {
        $this->assertSame(1, TermRetention::study('2026 January', '2026-01-12'));
        $this->assertSame(2, TermRetention::study('2025 September', '2026-01-12'));
        $this->assertSame(3, TermRetention::study('2025 May', '2026-01-12'));
        $this->assertSame(6, TermRetention::study('2024 May', '2026-01-12'));
        $this->assertNull(TermRetention::study('Unknown intake', '2026-01-12'));
        $this->assertNull(TermRetention::study('2026 May', '2026-01-12'), 'an intake after the term');
    }

    public function test_student_lists_add_up_to_the_counts(): void
    {
        $term = TermRetention::build([$this->terms[0]])['terms'][0];
        $intake = collect($term['intakes'])->sortByDesc('dropped')->first();
        if (!$intake) {
            $this->markTestSkipped('The term has no intakes.');
        }
        $url = fn ($intakeId, $key) => route('reports.term.performance.retention.new.students', ['term' => $term['id'], 'intake' => $intakeId, 'key' => $key]);
        foreach (TermRetention::KEYS as $key) {
            $rows = $this->getJson($url($intake['id'], $key))->assertOk()->json('rows');
            $this->assertCount($intake[$key], $rows, $key);
            $this->assertSame(count($rows), count(array_unique(array_column($rows, 'id'))), "$key: each student once");
        }
        $rows = $this->getJson($url($intake['id'], 'total'))->json('rows');
        $this->assertSame($intake['dropped'], count(array_filter($rows, fn ($r) => $r['outcome'] === 'dropped')));
        $this->assertSame($intake['total'] - $intake['registered'], count(array_filter($rows, fn ($r) => $r['outcome'] === 'none')));
        // The whole term: every intake's dropouts.
        $this->assertCount($term['dropped'], $this->getJson($url('all', 'dropped'))->json('rows'));

        $this->getJson($url($intake['id'], 'everyone'))->assertStatus(422);
        $this->getJson(route('reports.term.performance.retention.new.students', ['term' => 99999999, 'intake' => 'all', 'key' => 'total']))->assertNotFound();
    }

    public function test_the_screen_json(): void
    {
        $ids = implode('_', $this->terms);
        $this->getJson(route('reports.term.performance.retention.new.generate', $ids))
            ->assertOk()
            ->assertJsonCount(count($this->terms), 'terms')
            ->assertJsonStructure(['terms' => [['id', 'name', 'label', 'colour', 'total', 'registered', 'stayed', 'dropped', 'rate', 'left_before', 'intakes']], 'series', 'takeaway']);

        $this->getJson(route('reports.term.performance.retention.new.generate', '99999999'))->assertNotFound();
    }

    public function test_pdf_and_excel(): void
    {
        $ids = implode('_', $this->terms);
        $pdf = $this->get(route('reports.term.performance.retention.new.pdf', $ids));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        Excel::fake();
        $built = TermRetention::build($this->terms)['terms'];
        $this->get(route('reports.term.performance.retention.new.excel', $ids))->assertOk();
        $name = 'Retention_Report_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', implode('_', array_column($built, 'name'))), '_').'.xlsx';
        Excel::assertDownloaded($name, function (ArrayCollectionExport $export) use ($built) {
            $rows = array_values($export->array());
            $intakes = array_sum(array_map(fn ($t) => count($t['intakes']), $built));

            return count($rows) === 1 + count($built) + $intakes && $rows[1][1] === 'All intakes' && $rows[1][6] === $built[0]['dropped'] && $rows[1][8] === $built[0]['left_before'];
        });
    }
}
