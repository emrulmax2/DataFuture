<?php

namespace Tests\Feature;

use App\Exports\StudentWorkplacementReportExport;
use App\Http\Controllers\Reports\StudentWorkplacementReportController;
use App\Models\User;
use App\Services\Reports\StudentResultReport;
use App\Services\Reports\StudentWorkPlacementReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Student Work Placement "New version" (user, 25 Nov 2026): the old report's students and
 * export, the students it left out (on a placement course with no records), the screen data, the
 * Excel and the page's two tabs. Read-only against the dev database.
 */
class StudentWorkPlacementReportNewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    private function courses(): array
    {
        return DB::table('workplacement_details')->whereNull('deleted_at')->pluck('course_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    public function test_the_old_students_and_sheet(): void
    {
        $courses = $this->courses();
        if (! $courses) {
            $this->markTestSkipped('No work placement set-up.');
        }
        $oldIds = app(StudentWorkplacementReportController::class)->totalCount(new Request(['group' => ['course' => $courses]]))->getData(true)['student_ids'];
        $filters = StudentResultReport::filters(['course' => $courses]);
        $students = app(StudentWorkPlacementReport::class)->students($filters);
        $this->assertSame($oldIds, array_keys(array_filter($students, fn ($s) => $s['records'])), 'students with records = the old list, same order');

        Excel::fake();
        app(StudentWorkplacementReportController::class)->excelDownload(new Request(['studentIds' => implode(',', $oldIds)]));
        $old = null;
        Excel::assertDownloaded('student_workplacement_report.xlsx', function (StudentWorkplacementReportExport $export) use (&$old) {
            $old = [array_values($export->collection()->all()), $export->headings()];

            return true;
        });
        [$rows, $headers] = app(StudentWorkPlacementReport::class)->sheet($filters);
        $regs = array_flip(array_map(fn ($id) => $students[$id]['reg'], $oldIds));
        $rows = array_values(array_filter($rows, fn ($r) => isset($regs[$r[0]])));
        $hours = fn ($v) => is_string($v) && preg_match('/^([\d.]+) Hours$/', $v, $m) ? round((float) $m[1], 2) : $v;

        $this->assertSame($old[1][1], $headers[1], 'second header row (columns)');
        $this->assertCount(count($old[0]), $rows);
        foreach ($old[0] as $i => $o) {
            foreach ($o as $c => $v) {
                $this->assertEquals($hours($v), $hours($rows[$i][$c] ?? null), "row $i column $c ({$old[1][1][$c]})");
            }
        }
    }

    public function test_students_without_records_are_on_a_placement_course(): void
    {
        $courses = $this->courses();
        $students = app(StudentWorkPlacementReport::class)->students(StudentResultReport::filters(['course' => $courses]));
        $none = array_filter($students, fn ($s) => ! $s['records']);
        foreach ($none as $s) {
            $this->assertCount(1, $s['setups'], "{$s['reg']} has the course's set-up");
            $this->assertSame(0.0, $s['total']);
        }
        // A course without a set-up adds nobody without records.
        $other = DB::table('courses')->whereNotIn('id', $courses ?: [0])->value('id');
        $more = app(StudentWorkPlacementReport::class)->students(StudentResultReport::filters(['course' => [(int) $other]]));
        $this->assertSame([], array_keys(array_filter($more, fn ($s) => ! $s['records'])));
    }

    public function test_the_screen_data_adds_up(): void
    {
        $courses = $this->courses();
        $filters = StudentResultReport::filters(['course' => $courses]);
        $data = app(StudentWorkPlacementReport::class)->build($filters);
        $students = app(StudentWorkPlacementReport::class)->students($filters);
        $this->assertCount(count($students), $data['rows']);
        foreach ($data['rows'] as $r) {
            $s = $students[$r[0]];
            $this->assertEqualsWithDelta($s['total'], $r[9], 0.01, "{$r[1]} confirmed total");
            $this->assertEqualsWithDelta($s['total'], array_sum(array_column($r[11], 1)), 0.01, "{$r[1]} module hours add up to the total");
            foreach ($r[6] as $l => [$need, $parts]) {
                $this->assertEqualsWithDelta($need, array_sum($parts), 0.01, "{$r[1]} level $l = its learning hours");
            }
        }
    }

    public function test_endpoints_and_page(): void
    {
        $this->getJson(route('report.student.workplacement.new.data'))->assertStatus(422);
        $courses = $this->courses();
        $this->getJson(route('report.student.workplacement.new.data', ['course' => $courses]))
            ->assertOk()->assertJsonStructure(['levels', 'courses', 'statuses', 'intakes', 'modules', 'rows']);

        Excel::fake();
        $this->get(route('report.student.workplacement.new.excel', ['course' => $courses]))->assertOk();
        Excel::assertDownloaded('student_workplacement_report.xlsx', fn (StudentWorkplacementReportExport $export) => $export->headings()[0][0] === 'Student ID'
            && end($export->headings()[0]) === 'Total Hours Completed');

        $this->get(route('report.student.workplacement.view'))->assertOk()
            ->assertSee('data-swp', false)
            ->assertSee('data-srp-pane="new"', false)
            ->assertSee('data-srp-pane="old"', false)
            ->assertSee('id="studentGroupSearchForm"', false);
    }
}
