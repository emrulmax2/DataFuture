<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\StudentPerformanceReportController;
use App\Models\User;
use App\Services\Reports\StudentPerformanceReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Result Grade Performance "New version" (user, 25 Nov 2026): the old export's expected
 * and achieved points, the attendance-semester narrowing, the screen data, the Excel and the
 * page's two tabs. Read-only against the dev database.
 */
class StudentPerformanceReportNewTest extends TestCase
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

    public function test_the_sheet_matches_the_old_export(): void
    {
        $intake = $this->intake();
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $filters = StudentResultReport::filters(['intake' => [$intake]]);
        $ids = array_slice(array_keys(app(StudentResultReport::class)->students($filters)), 0, 60);

        Excel::fake();
        app(StudentPerformanceReportController::class)->excelDownload(new Request(['studentIds' => implode(',', $ids)]));
        $old = null;
        Excel::assertDownloaded('student_performance_report.xlsx', function (ArrayCollectionExport $export) use (&$old) {
            $old = array_values($export->array());

            return true;
        });

        $mine = array_flip($ids);
        $students = array_filter(app(StudentPerformanceReport::class)->students($filters), fn ($s) => isset($mine[$s['id']]));
        $regs = array_flip(array_column($students, 'reg'));
        $new = [];
        foreach (array_slice(app(StudentPerformanceReport::class)->sheet($filters), 1) as $row) {
            if (isset($regs[$row[2] !== '' ? $row[2] : $row[6]])) $new[] = $row;
        }

        $old = array_slice($old, 1);
        $this->assertCount(count($old), $new, 'same rows');
        foreach ($old as $i => $o) {
            foreach ([0, 2, 3, 4, 5, 6, 7, 8, 9, 10] as $col) {
                $this->assertSame((string) $o[$col], (string) $new[$i][$col], "row $i column $col");
            }
            // Term Status: the old code compares 0 to NULL loosely, so it never writes "No".
            $this->assertContains($new[$i][1], ['', 'No']);
        }
    }

    public function test_attendance_semesters_narrow_the_semesters_counted(): void
    {
        $intake = $this->intake();
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $service = app(StudentPerformanceReport::class);
        $all = $service->students(StudentResultReport::filters(['intake' => [$intake]]));
        $term = null;
        foreach ($all as $s) {
            if ($s['terms']) {
                $term = array_key_first($s['terms']);
                break;
            }
        }
        if (! $term) {
            $this->markTestSkipped('No semesters.');
        }
        $narrow = $service->students(StudentResultReport::filters(['intake' => [$intake], 'attendance' => [$term]]));
        $this->assertNotEmpty($narrow);
        foreach ($narrow as $id => $s) {
            $this->assertSame([$term], array_keys($s['terms']), 'only the chosen semester');
            $this->assertSame($all[$id]['terms'][$term], $s['terms'][$term], 'same figures as without the filter');
        }
    }

    public function test_the_screen_data_adds_up(): void
    {
        $intake = $this->intake();
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $filters = StudentResultReport::filters(['intake' => [$intake]]);
        $data = app(StudentPerformanceReport::class)->build($filters);
        $students = app(StudentPerformanceReport::class)->students($filters);
        $this->assertCount(count($students), $data['rows']);
        $this->assertSame(4, $data['points']['attendance']);
        foreach ($data['rows'] as $r) {
            $s = $students[$r[0]];
            $this->assertEquals(array_sum(array_column($s['terms'], 0)), array_sum(array_column($r[5], 1)), "{$r[1]} expected");
            $this->assertEquals(array_sum(array_column($s['terms'], 1)), array_sum(array_column($r[5], 2)), "{$r[1]} achieved");
            foreach ($r[5] as $cell) {
                // Expected = 4 attendance points + 3 per counted result; achieved never exceeds it.
                $this->assertSame(0, ($cell[1] - 4) % 3);
                $this->assertLessThanOrEqual($cell[1], $cell[2]);
            }
        }
    }

    public function test_endpoints_and_page(): void
    {
        $this->getJson(route('report.student.performance.new.data'))->assertStatus(422);
        $intake = $this->intake();
        $this->getJson(route('report.student.performance.new.data', ['intake' => [$intake]]))
            ->assertOk()->assertJsonStructure(['points', 'terms', 'courses', 'statuses', 'intakes', 'rows']);

        Excel::fake();
        $this->get(route('report.student.performance.new.excel', ['intake' => [$intake]]))->assertOk();
        Excel::assertDownloaded('student_performance_report.xlsx', fn (ArrayCollectionExport $export) => $export->array()[1][0] === 'Term Name');

        $this->get(route('report.student.performance.view'))->assertOk()
            ->assertSee('data-spr', false)
            ->assertSee('data-srp-pane="new"', false)
            ->assertSee('data-srp-pane="old"', false)
            ->assertSee('id="studentGroupSearchForm"', false);
    }
}
