<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\AttendanceReportController;
use App\Models\User;
use App\Services\Reports\StudentAttendanceReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Attendance Reports "New version" (user, 24 Nov 2026): the same students and figures
 * as the old list, plus bands, sorting, paging and Excel. Read-only against the dev database.
 */
class AttendanceReportNewVersionTest extends TestCase
{
    use DatabaseTransactions;

    private int $term;

    protected function setUp(): void
    {
        parent::setUp();
        // The latest attendance semester that has attendance (small enough to run quickly).
        $term = DB::table('term_declarations as td')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('plans as p')->join('attendances as a', 'a.plan_id', '=', 'p.id')
                ->whereColumn('p.term_declaration_id', 'td.id')->whereNull('a.deleted_at'))
            ->orderByDesc('td.id')->value('td.id');
        if (!$term) {
            $this->markTestSkipped('No attendance in this database.');
        }
        $this->term = (int) $term;
        $this->actingAs(User::find(7));
    }

    private function list(array $params)
    {
        return $this->getJson(route('report.attendance.reports.new.list', $params + ['attendance_semester' => [$this->term], 'fresh' => 1]));
    }

    /** The old endpoint's rows (every page), keyed by student id. */
    private function oldRows(array $params): array
    {
        $request = Request::create('/x', 'GET', ['form_data' => http_build_query(['params' => $params]), 'size' => 'true']);
        $data = app(AttendanceReportController::class)->list($request)->getData(true)['data'];

        return collect($data)->keyBy('id')->all();
    }

    public function test_a_semester_is_required(): void
    {
        $this->getJson(route('report.attendance.reports.new.list', ['course' => [1]]))->assertStatus(422);
    }

    public function test_students_and_figures_match_the_old_version(): void
    {
        $old = $this->oldRows(['attendance_semester' => [$this->term]]);
        $this->assertNotEmpty($old);

        $response = $this->list(['size' => 100]);
        $response->assertOk();
        $this->assertSame(count($old), $response->json('bands.all'));

        $rows = StudentAttendanceReport::fromRequest(new Request(['attendance_semester' => [$this->term]]))->rows(true);
        $this->assertEqualsCanonicalizing(array_keys($old), array_column($rows, 'id'));
        foreach ($rows as $row) {
            $o = $old[$row['id']];
            $this->assertSame(
                [(int) $o['P'], (int) $o['EVENING_P'], (int) $o['WEEKEND_P'], (int) $o['O'], (int) $o['A'], (int) $o['E'], (int) $o['M'], (int) $o['H'], (int) $o['L'], (int) $o['LE'], (int) $o['TC']],
                $row['n'],
                'counts of student '.$row['id']
            );
            $this->assertEqualsWithDelta((float) rtrim($o['w_excuse'], '%'), $row['pct'], 0.001);
            $this->assertEqualsWithDelta((float) rtrim($o['wo_excuse'], '%'), $row['wo'], 0.001);
        }
    }

    public function test_the_percentage_filter_matches_the_old_version(): void
    {
        $old = $this->oldRows(['attendance_semester' => [$this->term], 'attendance_percentage' => '60.00']);
        $this->list(['attendance_percentage' => '60.00'])->assertOk()->assertJsonPath('bands.all', count($old));
    }

    public function test_bands_add_up_and_filter(): void
    {
        $bands = $this->list([])->assertOk()->json('bands');
        $this->assertSame($bands['all'], $bands['low'] + $bands['warn'] + $bands['mid'] + $bands['high']);

        foreach (StudentAttendanceReport::BANDS as $band) {
            $response = $this->list(['band' => $band, 'size' => 100])->assertOk();
            $this->assertSame($bands[$band], $response->json('total'));
            foreach ($response->json('rows') as $row) {
                $this->assertSame($band, StudentAttendanceReport::bandOf($row['pct']));
            }
        }
    }

    public function test_sorting_and_paging(): void
    {
        $first = $this->list(['sort' => 'pct', 'dir' => 'desc', 'size' => 25])->assertOk();
        $pcts = array_column($first->json('rows'), 'pct');
        $sorted = $pcts;
        rsort($sorted);
        $this->assertSame($sorted, $pcts);

        if ($first->json('total') > 25) {
            $second = $this->list(['sort' => 'pct', 'dir' => 'desc', 'size' => 25, 'page' => 2])->assertOk();
            $this->assertSame(2, $second->json('page'));
            $this->assertLessThanOrEqual(end($pcts), $second->json('rows.0.pct'));
            $this->assertEmpty(array_intersect(array_column($first->json('rows'), 'id'), array_column($second->json('rows'), 'id')));
        }
        // A page past the end comes back as the last page.
        $this->list(['size' => 25, 'page' => 9999])->assertOk()->assertJsonPath('page', $first->json('last_page'));
    }

    public function test_an_intake_with_no_students_lists_nobody(): void
    {
        // The old list dropped the student filter when it matched nobody and listed everyone.
        $this->list(['intake_semester' => [999999]])->assertOk()->assertJsonPath('bands.all', 0)->assertJsonPath('rows', []);
    }

    public function test_excel_of_the_search_and_of_the_ticked_students(): void
    {
        Excel::fake();
        $all = $this->list(['size' => 100])->json('bands.all');
        $ids = array_slice(array_column($this->list(['size' => 25])->json('rows'), 'id'), 0, 2);

        $this->post(route('report.attendance.reports.new.export'), ['attendance_semester' => [$this->term]])->assertOk();
        $this->post(route('report.attendance.reports.new.export'), ['attendance_semester' => [$this->term], 'student_ids' => $ids])->assertOk();

        $label = StudentAttendanceReport::fromRequest(new Request(['attendance_semester' => [$this->term]]))->label();
        $base = 'Attendance_Report_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $label), '_');
        Excel::assertDownloaded($base.'.xlsx', fn (ArrayCollectionExport $export) => count($export->array()) === $all + 1);
        Excel::assertDownloaded($base.'_Selected.xlsx', function (ArrayCollectionExport $export) use ($ids) {
            $rows = array_slice($export->array(), 1);

            return count($rows) === count($ids) && empty(array_diff(array_column($rows, 0), $ids));
        });
    }
}
