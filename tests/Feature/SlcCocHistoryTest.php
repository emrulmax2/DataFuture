<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Models\User;
use App\Services\Reports\SlcCocHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * SLC Reports › SLC COC History "New version" (user, 25 Nov 2026): every change of circumstances
 * (the design's "All dates"), the actioned rule, the filters mirrored for Excel, and the page's two
 * tabs. Read-only against the dev database.
 */
class SlcCocHistoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    public function test_all_dates_is_every_change_of_circumstances(): void
    {
        $res = $this->getJson(route('reports.slc.coc.history.data'))->assertOk()
            ->assertJsonPath('from', null)
            ->assertJsonStructure(['from', 'to', 'today', 'first', 'types', 'courses', 'intakes', 'statuses', 'students', 'rows']);
        $rows = $res->json('rows');
        $this->assertCount(DB::table('slc_cocs')->whereNull('deleted_at')->count(), $rows);
        $this->assertSame(DB::table('slc_cocs')->whereNull('deleted_at')->where('actioned', 'Yes')->count(), count(array_filter($rows, fn ($r) => $r[3] === 1)), 'only "Yes" is actioned');
        $this->assertSame(['Withdrawal', 'Suspension', 'Repetition', 'Resumption', 'Transfer', 'Fee'], array_slice(array_column($res->json('types'), 0), 0, 6));
    }

    public function test_a_date_range_and_the_excel_selection(): void
    {
        $from = now()->subYears(3)->toDateString();
        $to = now()->toDateString();
        $base = DB::table('slc_cocs')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to]);
        $this->assertCount((clone $base)->count(), $this->getJson(route('reports.slc.coc.history.data', ['from' => $from, 'to' => $to]))->json('rows'));

        $type = (string) (clone $base)->groupBy('coc_type')->orderByRaw('COUNT(*) ASC')->value('coc_type');
        if ($type === '') {
            $this->markTestSkipped('No changes of circumstances in the last three years.');
        }
        $expected = (clone $base)->where('coc_type', $type)->where(fn ($q) => $q->where('actioned', '<>', 'Yes')->orWhereNull('actioned'))->count();
        $this->assertCount($expected, SlcCocHistory::filter(SlcCocHistory::rows($from, $to), ['types' => [$type], 'actioned' => 'no']));

        Excel::fake();
        $this->get(route('reports.slc.coc.history.excel', ['from' => $from, 'to' => $to, 'types' => [$type], 'actioned' => 'no']))->assertOk();
        Excel::assertDownloaded('SLC_COC_History_'.$from.'_to_'.$to.'.xlsx', function (ArrayCollectionExport $export) use ($expected) {
            $rows = $export->array();

            return count($rows) === $expected + 1 && $rows[1][12] === 'Type Of COC' && $rows[1][14] === 'Confirmation Date';
        });
    }

    public function test_bad_input_is_refused(): void
    {
        $this->getJson(route('reports.slc.coc.history.data', ['from' => '2026-02-01', 'to' => '2026-01-01']))->assertStatus(422);
        $this->getJson(route('reports.slc.coc.history.excel', ['types' => ['Holiday']]))->assertStatus(422);
        $this->getJson(route('reports.slc.coc.history.excel', ['actioned' => 'maybe']))->assertStatus(422);
    }

    public function test_the_page_has_both_versions(): void
    {
        $this->get(route('reports.slc.index'))->assertOk()
            ->assertSee('data-sch', false)
            ->assertSee('id="schPaneNew"', false)
            ->assertSee('id="SLCcocReportForm"', false);
    }
}
