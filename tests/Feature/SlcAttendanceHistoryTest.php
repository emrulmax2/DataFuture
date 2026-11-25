<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Models\User;
use App\Services\Reports\SlcAttendanceHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * SLC Reports › SLC Attendance History "New version" (user, 25 Nov 2026): the screen's data for a
 * date range, the browser-side filters mirrored for Excel, and the page's two tabs. Read-only
 * against the dev database.
 */
class SlcAttendanceHistoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    public function test_the_default_range_is_the_last_12_months_of_confirmations(): void
    {
        $from = now()->subYear()->addDay()->toDateString();
        $to = now()->toDateString();
        $res = $this->getJson(route('reports.slc.attendance.history.data'))->assertOk()
            ->assertJsonPath('from', $from)->assertJsonPath('to', $to)
            ->assertJsonStructure(['from', 'to', 'today', 'codes' => [['id', 'code', 'name', 'colour']], 'courses', 'intakes', 'statuses', 'students', 'rows', 'checks' => ['suspended', 'enrolled', 'left']]);

        $expected = DB::table('slc_attendances')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to])->count();
        $rows = $res->json('rows');
        $this->assertCount($expected, $rows);
        $dates = array_column($rows, 1);
        $sorted = $dates;
        rsort($sorted);
        $this->assertSame($sorted, $dates, 'newest first');
        $this->assertSame(['A', 'S', 'X'], array_slice(array_column($res->json('codes'), 'code'), 0, 3));
    }

    public function test_the_claim_amount_is_the_first_instalment(): void
    {
        $row = DB::table('slc_installments as i')->join('slc_attendances as a', 'a.id', '=', 'i.slc_attendance_id')
            ->whereNull('i.deleted_at')->whereNull('a.deleted_at')->orderByDesc('a.confirmation_date')->first(['a.id', 'a.confirmation_date']);
        if (!$row) {
            $this->markTestSkipped('No instalments linked to confirmations.');
        }
        $first = DB::table('slc_installments')->whereNull('deleted_at')->where('slc_attendance_id', $row->id)->orderBy('id')->value('amount');
        $mine = SlcAttendanceHistory::rows($row->confirmation_date, $row->confirmation_date)->firstWhere('id', $row->id);
        $this->assertEqualsWithDelta((float) $first, $mine->amount, 0.001);
    }

    public function test_excel_has_the_filtered_selection(): void
    {
        $from = now()->subYears(3)->toDateString();
        $to = now()->toDateString();
        $code = (int) DB::table('slc_attendances')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to])
            ->groupBy('attendance_code_id')->orderByRaw('COUNT(*) ASC')->value('attendance_code_id');
        if (!$code) {
            $this->markTestSkipped('No confirmations in the last three years.');
        }
        $expected = DB::table('slc_attendances')->whereNull('deleted_at')->whereBetween('confirmation_date', [$from, $to])->where('attendance_code_id', $code)->count();
        $this->assertCount($expected, SlcAttendanceHistory::filter(SlcAttendanceHistory::rows($from, $to), ['codes' => [$code]]));

        Excel::fake();
        $this->get(route('reports.slc.attendance.history.excel', ['from' => $from, 'to' => $to, 'codes' => [$code]]))->assertOk();
        Excel::assertDownloaded('SLC_Attendance_History_'.$from.'_to_'.$to.'.xlsx', function (ArrayCollectionExport $export) use ($expected) {
            $rows = $export->array();

            return count($rows) === $expected + 1 && $rows[1][0] === 'Registration No' && $rows[1][16] === 'Confirmation Date';
        });
    }

    public function test_bad_ranges_are_refused(): void
    {
        $this->getJson(route('reports.slc.attendance.history.data', ['from' => '2026-02-01', 'to' => '2026-01-01']))->assertStatus(422);
        $this->getJson(route('reports.slc.attendance.history.data', ['from' => '01-01-2026', 'to' => '2026-02-01']))->assertStatus(422);
        $this->getJson(route('reports.slc.attendance.history.data', ['from' => '2026-01-01']))->assertStatus(422);
    }

    public function test_the_page_has_both_versions(): void
    {
        $this->get(route('reports.slc.index'))->assertOk()
            ->assertSee('data-sah', false)
            ->assertSee('id="sahPaneNew"', false)
            ->assertSee('id="attendanceSLCReportForm"', false);
    }
}
