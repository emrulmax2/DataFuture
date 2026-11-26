<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\StudentProgressMonitoringReportController;
use App\Models\User;
use App\Services\Reports\StudentProgressReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Progress Monitoring "New version" (user, 25 Nov 2026): the old export's modules and
 * totals per student, the Certificate claimed filter, the screen data, the Excel and the page's two
 * tabs. Read-only against the dev database.
 */
class StudentProgressReportNewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    private function intake(int $skip = 2): ?int
    {
        $id = DB::table('student_course_relations as scr')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->where('scr.active', 1)->whereNull('scr.deleted_at')
            ->groupBy('cc.semester_id')->havingRaw('count(*) between 20 and 400')
            ->orderByDesc('cc.semester_id')->skip($skip)->value('cc.semester_id');

        return $id ? (int) $id : null;
    }

    /** Student blocks of a sheet from the header row on: [summary row, module rows]. */
    private function blocks(array $rows): array
    {
        $out = [];
        $cur = [];
        foreach (array_slice(array_values($rows), 5) as $r) {
            if (($r[0] ?? '') !== '') {
                $out[] = [$r, $cur];
                $cur = [];
            } else {
                $cur[] = $r;
            }
        }

        return $out;
    }

    public function test_the_sheet_matches_the_old_export(): void
    {
        $intake = $this->intake();
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $filters = StudentResultReport::filters(['intake' => [$intake]]);
        $ids = array_keys(app(StudentResultReport::class)->students($filters));

        Excel::fake();
        app(StudentProgressMonitoringReportController::class)->excelDownload(new Request(['studentIds' => implode(',', $ids), 'searchedCriteria' => json_encode('x'), 'term' => json_encode('')]));
        $old = null;
        Excel::assertDownloaded('student_progress_monitor_report.xlsx', function (ArrayCollectionExport $export) use (&$old) {
            $old = $export->array();

            return true;
        });
        $new = app(StudentProgressReport::class)->sheet($filters, 'x');

        $this->assertSame(array_values($old)[4], array_values($new)[4], 'the 29 headings');
        $ob = $this->blocks($old);
        $nb = $this->blocks($new);
        $this->assertCount(count($ob), $nb, 'one block per student');
        $key = fn ($rows) => collect($rows)->filter(fn ($r) => $r[7] !== '')->map(fn ($r) => implode('|', [$r[7], $r[8], $r[9], $r[16], $r[17]]))->sort()->values()->all();
        foreach ($ob as $i => [$summary, $modules]) {
            $this->assertSame(array_map('strval', $summary), array_map('strval', $nb[$i][0]), "summary of {$summary[0]}");
            // Results published at the same moment come back in MySQL's own order in the old query.
            $this->assertSame($key($modules), $key($nb[$i][1]), "modules of {$summary[0]}");
        }
    }

    public function test_certificate_claimed_filter(): void
    {
        $service = app(StudentResultReport::class);
        $intake = $this->intake(8) ?? $this->intake();
        $all = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake]])));
        $yes = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake], 'certificate' => 'Yes'])));
        $no = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake], 'certificate' => 'No'])));
        $this->assertEqualsCanonicalizing($all, array_merge($yes, $no), 'Yes and No cover everyone (No includes students with no award record)');
        $this->assertEmpty(array_intersect($yes, $no));
        foreach (array_slice($yes, 0, 20) as $id) {
            $latest = DB::table('student_awards')->where('student_id', $id)->whereNull('deleted_at')->orderByDesc('id')->value('certificate_requested');
            $this->assertSame('Yes', $latest);
        }
    }

    public function test_the_screen_data_adds_up(): void
    {
        $intake = $this->intake();
        $filters = StudentResultReport::filters(['intake' => [$intake]]);
        $data = app(StudentProgressReport::class)->build($filters);
        $sheet = $this->blocks(app(StudentProgressReport::class)->sheet($filters, 'x'));
        $this->assertCount(count($sheet), $data['rows']);
        foreach ($data['rows'] as $i => $r) {
            $summary = $sheet[$i][0];
            $this->assertSame($summary[0], $r[1]);
            $withResult = array_filter($r[7], fn ($m) => $m[8] > 0);
            $passed = array_filter($withResult, fn ($m) => $m[6] !== '');
            $this->assertSame((int) $summary[7], count($withResult), "{$r[1]} modules");
            $this->assertSame((int) $summary[18], count($passed), "{$r[1]} complete");
            $this->assertEquals((float) $summary[9], array_sum(array_column($passed, 3)), "{$r[1]} credits");
            // Modules with no result yet carry credit (tutorials are left out).
            foreach (array_filter($r[7], fn ($m) => $m[8] === 0) as $m) $this->assertGreaterThan(0, $m[3]);
        }
    }

    public function test_endpoints_and_page(): void
    {
        $this->getJson(route('report.student.progress.new.data'))->assertStatus(422);
        $intake = $this->intake();
        $this->getJson(route('report.student.progress.new.data', ['intake' => [$intake]]))
            ->assertOk()->assertJsonStructure(['terms', 'grades', 'passing', 'units', 'staff', 'groups', 'courses', 'statuses', 'intakes', 'rows']);

        Excel::fake();
        $this->get(route('report.student.progress.new.excel', ['intake' => [$intake], 'criteria' => 'Intake semester: x']))->assertOk();
        Excel::assertDownloaded('student_progress_monitor_report.xlsx', fn (ArrayCollectionExport $export) => $export->array()[1] === ['Search Criteria', 'Intake semester: x']
            && $export->array()[5][0] === 'LCC ID');

        $this->get(route('report.student.progress.view'))->assertOk()
            ->assertSee('data-spm', false)
            ->assertSee('data-srp-pane="new"', false)
            ->assertSee('data-srp-pane="old"', false)
            ->assertSee('id="studentGroupSearchForm"', false);
    }
}
