<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\ClassStatusByTermController;
use App\Models\User;
use App\Services\Reports\ClassStatusSummary;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Term Performance › Class Status "New version" (user, 25 Nov 2026): the old report's classes and
 * counts, the design's meanings (scheduled = due so far, held by proxy ⊂ held), the JSON, the PDF
 * and the Excel. Read-only against the dev database.
 */
class ClassStatusReportTest extends TestCase
{
    use DatabaseTransactions;

    private int $term;

    protected function setUp(): void
    {
        parent::setUp();
        // The latest finished term with class dates (no future classes, so the old Schedule = scheduled).
        $term = DB::table('term_declarations as td')
            ->whereNotNull('td.end_date')->where('td.end_date', '<', now()->subDay()->toDateString())
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('plans as p')->join('plans_date_lists as d', 'd.plan_id', '=', 'p.id')
                ->whereColumn('p.term_declaration_id', 'td.id')->whereNull('p.deleted_at')->whereNull('d.deleted_at'))
            ->orderByDesc('td.id')->value('td.id');
        if (!$term) {
            $this->markTestSkipped('No finished term with classes in this database.');
        }
        $this->term = (int) $term;
        $this->actingAs(User::find(7));
    }

    private function old(int $term): array
    {
        $rows = app(ClassStatusByTermController::class)->list(new Request(['attendance_semester' => [$term]]))->getData(true);

        return collect($rows)->keyBy('course_name')->all();
    }

    /** The finished term with the most class dates (held, not held and proxies to compare). */
    private function busiestTerm(): int
    {
        return (int) DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->join('term_declarations as td', 'td.id', '=', 'p.term_declaration_id')
            ->where('td.end_date', '<', now()->subDay()->toDateString())->whereNull('p.deleted_at')->whereNull('d.deleted_at')
            ->groupBy('p.term_declaration_id')->orderByRaw('COUNT(*) DESC')->value('p.term_declaration_id');
    }

    private static function num($v): int
    {
        return (int) ($v === '' || $v === null ? 0 : $v);
    }

    public function test_courses_and_groups_match_the_old_report(): void
    {
        foreach (array_unique([$this->term, $this->busiestTerm()]) as $term) {
            $this->assertTermMatchesTheOldReport($term);
        }
    }

    private function assertTermMatchesTheOldReport(int $term): void
    {
        $data = ClassStatusSummary::build($term);
        $old = $this->old($term);
        $this->assertSameSize($old, $data['courses']);

        foreach ($data['courses'] as $course) {
            $o = $old[$course['name']];
            $this->assertSame(self::num($o['schedule']), $course['scheduled'] + $course['future'], $course['name']);
            $this->assertSame(self::num($o['held']), $course['held']);
            $this->assertSame(self::num($o['unheld']), $course['not_held'] + $course['future']);
            $this->assertSame(self::num($o['cancelled']), $course['cancelled']);
            $this->assertSame(self::num($o['unknown']), $course['unknown']);
            // Held by proxy is the held part of the old Proxy count.
            $this->assertLessThanOrEqual(self::num($o['proxy']), $course['proxy']);

            $groups = collect($o['_children'])->keyBy('group_id');
            $this->assertSameSize($groups, $course['groups']);
            foreach ($course['groups'] as $group) {
                $g = $groups[$group['id']];
                $this->assertSame(self::num($g['schedule']), $group['scheduled'] + $group['future'], $group['name']);
                $this->assertSame(self::num($g['held']), $group['held']);
            }
        }

        $T = $data['totals'];
        $this->assertSame($T['held'] + $T['not_held'], $T['scheduled']);
        $this->assertSame(array_sum(array_column($data['courses'], 'scheduled')), $T['scheduled']);
    }

    public function test_held_by_proxy_counts_only_held_classes_with_a_proxy(): void
    {
        $expected = DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.term_declaration_id', $this->term)->whereNull('p.deleted_at')->whereNull('d.deleted_at')
            ->where('d.status', 'Completed')->whereNotNull('d.proxy_tutor_id')->count();

        $this->assertSame($expected, ClassStatusSummary::build($this->term)['totals']['proxy']);
    }

    public function test_classes_still_to_come_are_not_counted_as_scheduled_or_not_held(): void
    {
        // As if it were the term's first day: what is not held yet is still to come.
        $now = date('Y-m-d 00:00:00', strtotime(DB::table('term_declarations')->where('id', $this->term)->value('start_date')));
        $early = ClassStatusSummary::build($this->term, $now);
        $late = ClassStatusSummary::build($this->term);

        // Still to come = not yet held and starting after "now" (a class already marked held never is).
        $expected = DB::table('plans_date_lists as d')->join('plans as p', 'p.id', '=', 'd.plan_id')
            ->where('p.term_declaration_id', $this->term)->whereNull('p.deleted_at')->whereNull('d.deleted_at')
            ->whereRaw("TIMESTAMP(d.date, COALESCE(p.start_time, '00:00:00')) > ?", [$now])
            ->where(fn ($q) => $q->whereNull('d.status')->orWhere('d.status', '<>', 'Completed'))->count();
        $this->assertSame($expected, $early['totals']['future']);
        $this->assertSame($late['totals']['scheduled'] + $late['totals']['future'], $early['totals']['scheduled'] + $early['totals']['future']);
        $this->assertSame($late['totals']['held'], $early['totals']['held']);
        $this->assertSame($early['totals']['held'] + $early['totals']['not_held'], $early['totals']['scheduled']);
    }

    public function test_the_screen_json(): void
    {
        $this->getJson(route('reports.term.performance.class.status.generate', $this->term))
            ->assertOk()
            ->assertJsonPath('term.id', $this->term)
            ->assertJsonStructure(['term' => ['id', 'name'], 'totals' => ClassStatusSummary::KEYS, 'courses' => [['id', 'name', 'groups' => [['id', 'name', 'scheduled', 'held']]]]]);

        $this->getJson(route('reports.term.performance.class.status.generate', 99999999))->assertNotFound();
    }

    public function test_pdf_and_excel(): void
    {
        $pdf = $this->get(route('reports.term.performance.class.status.pdf', $this->term));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        Excel::fake();
        $data = ClassStatusSummary::build($this->term);
        $this->get(route('reports.term.performance.class.status.excel', $this->term))->assertOk();
        $rows = 1 + count($data['courses']) + array_sum(array_map(fn ($c) => count($c['groups']), $data['courses'])) + 1;
        $name = 'Class_Status_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $data['term']['name']), '_').'.xlsx';
        Excel::assertDownloaded($name, fn (ArrayCollectionExport $export) => count($export->array()) === $rows);
    }
}
