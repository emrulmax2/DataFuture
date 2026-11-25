<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Models\User;
use App\Services\Reports\SlcRegistrationHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * SLC Reports › SLC Registration History "New version" (user, 25 Nov 2026): the screen's data for a
 * date range, the browser-side filters mirrored for Excel, and the page's two tabs. Read-only
 * against the dev database.
 */
class SlcRegistrationHistoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    public function test_the_default_range_is_the_last_12_months_of_registrations(): void
    {
        $from = now()->subYear()->addDay()->toDateString();
        $to = now()->toDateString();
        $res = $this->getJson(route('reports.slc.registration.history.data'))->assertOk()
            ->assertJsonPath('from', $from)->assertJsonPath('to', $to)
            ->assertJsonStructure(['from', 'to', 'today', 'statuses' => [['id', 'code', 'name', 'colour']], 'years', 'courses', 'intakes', 'studentStatuses', 'students', 'rows', 'checks' => ['left', 'enrolled', 'registered']]);

        $expected = DB::table('slc_registrations')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to])->count();
        $rows = $res->json('rows');
        $this->assertCount($expected, $rows);
        $dates = array_column($rows, 1);
        $sorted = $dates;
        rsort($sorted);
        $this->assertSame($sorted, $dates, 'newest first');
        $this->assertSame(['Yes', 'No', 'N/R'], array_slice(array_column($res->json('statuses'), 'code'), 0, 3));
    }

    public function test_excel_has_the_filtered_selection(): void
    {
        $from = now()->subYears(3)->toDateString();
        $to = now()->toDateString();
        $base = DB::table('slc_registrations')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to]);
        $status = (int) (clone $base)->groupBy('slc_registration_status_id')->orderByRaw('COUNT(*) ASC')->value('slc_registration_status_id');
        if (!$status) {
            $this->markTestSkipped('No registrations in the last three years.');
        }
        $expected = (clone $base)->where('slc_registration_status_id', $status)->count();
        $this->assertCount($expected, SlcRegistrationHistory::filter(SlcRegistrationHistory::rows($from, $to), ['statuses' => [$status]]));
        $year = (int) (clone $base)->whereNotNull('academic_year_id')->value('academic_year_id');
        $this->assertCount((clone $base)->where('academic_year_id', $year)->count(), SlcRegistrationHistory::filter(SlcRegistrationHistory::rows($from, $to), ['academic' => [$year]]));

        Excel::fake();
        $this->get(route('reports.slc.registration.history.excel', ['from' => $from, 'to' => $to, 'statuses' => [$status]]))->assertOk();
        Excel::assertDownloaded('SLC_Registration_History_'.$from.'_to_'.$to.'.xlsx', function (ArrayCollectionExport $export) use ($expected) {
            $rows = $export->array();

            return count($rows) === $expected + 1 && $rows[1][0] === 'Registration No' && $rows[1][15] === 'Confirmation Date';
        });
    }

    public function test_bad_ranges_are_refused(): void
    {
        $this->getJson(route('reports.slc.registration.history.data', ['from' => '2026-02-01', 'to' => '2026-01-01']))->assertStatus(422);
        $this->getJson(route('reports.slc.registration.history.data', ['to' => '2026-01-01']))->assertStatus(422);
    }

    public function test_the_page_has_both_versions(): void
    {
        $this->get(route('reports.slc.index'))->assertOk()
            ->assertSee('data-srh', false)
            ->assertSee('id="srhPaneNew"', false)
            ->assertSee('id="registrationSLCForm"', false)
            ->assertSee('data-statuses=', false);
    }
}
