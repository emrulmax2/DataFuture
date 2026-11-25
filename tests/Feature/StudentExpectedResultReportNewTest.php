<?php

namespace Tests\Feature;

use App\Exports\CustomExpectedResultCollectionExport;
use App\Http\Controllers\Reports\StudentExpectedResultReportController;
use App\Models\User;
use App\Services\Reports\StudentExpectedResultReport;
use App\Services\Reports\StudentResultReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Student Expected Result "New version" (user, 25 Nov 2026): the old export's Yes / No per
 * unit, the required attendance semester, the screen data, the Excel and the page's two tabs.
 * Read-only against the dev database.
 */
class StudentExpectedResultReportNewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    /** A recent attendance semester and an intake with classes in it. */
    private function search(): ?array
    {
        $row = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->join('student_course_relations as scr', 'scr.student_id', '=', 'a.student_id')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->where('scr.active', 1)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->groupBy('p.term_declaration_id', 'cc.semester_id')->havingRaw('count(distinct a.student_id) between 30 and 300')
            ->orderByDesc('p.term_declaration_id')->select('p.term_declaration_id as term', 'cc.semester_id as intake')->first();

        return $row ? ['intake' => [(int) $row->intake], 'attendance' => [(int) $row->term]] : null;
    }

    public function test_the_sheet_matches_the_old_export(): void
    {
        $checked = 0;
        foreach (DB::table('term_declarations')->whereNull('deleted_at')->orderByDesc('id')->limit(20)->pluck('id') as $term) {
            $in = ['attendance' => [(int) $term]];
            $old = app(StudentExpectedResultReportController::class)->totalCount(new Request(['group' => ['attendance_semester' => $in['attendance']]]))->getData(true);
            if (($old['all_rows'] ?? 0) < 30 || $old['all_rows'] > 2000) continue;
            Excel::fake();
            try {
                app(StudentExpectedResultReportController::class)->excelDownload(new Request(['studentIds' => implode(',', $old['student_ids']), 'term' => json_encode(array_map('strval', $in['attendance']))]));
            } catch (\Throwable $e) {
                continue; // the old export fails on a class without a course
            }
            $sheet = null;
            Excel::assertDownloaded('board_expected_result_report.xlsx', function (CustomExpectedResultCollectionExport $export) use (&$sheet) {
                $sheet = [array_values($export->collection()->all()), $export->headings()];

                return true;
            });
            [$rows, $headers] = app(StudentExpectedResultReport::class)->sheet(StudentResultReport::filters($in));
            $rows = array_values($rows);
            $this->assertSame($sheet[1][2], $headers[2], "term $term unit columns");
            $this->assertCount(count($sheet[0]), $rows, "term $term rows");
            foreach ($sheet[0] as $i => $o) {
                $o[1] = trim($o[1]); // the old name kept a trailing space
                $this->assertSame(array_map('strval', $o), array_map('strval', $rows[$i]), "term $term row $i");
            }
            if (++$checked === 2) break;
        }
        if (! $checked) {
            $this->markTestSkipped('No semester the old export can run.');
        }
    }

    public function test_attendance_semester_is_required(): void
    {
        $this->getJson(route('report.student.expected.result.new.data', ['intake' => [1]]))->assertStatus(422)->assertJson(['message' => 'Select at least one attendance semester.']);
        $this->getJson(route('report.student.expected.result.new.excel', ['intake' => [1]]))->assertStatus(422);
    }

    public function test_the_screen_data_matches_the_sheet(): void
    {
        $in = $this->search();
        if (! $in) {
            $this->markTestSkipped('No semester.');
        }
        $f = StudentResultReport::filters($in);
        $data = app(StudentExpectedResultReport::class)->build($f);
        [$rows, $headers, $units] = app(StudentExpectedResultReport::class)->sheet($f);
        $byReg = [];
        foreach ($rows as $r) $byReg[$r[0]] = $r;
        foreach ($data['rows'] as $r) {
            if (! isset($byReg[$r[1]])) {
                $this->assertSame([], $r[7], "{$r[1]} is left out of the export only when it has no unit");
                continue;
            }
            foreach ($r[7] as [$unit, $answer]) {
                $col = 8 + array_search($data['units'][$unit], $units, true);
                $this->assertSame($answer === 'Y' ? 'Yes' : 'No', $byReg[$r[1]][$col], "{$r[1]} {$data['units'][$unit]}");
            }
        }
    }

    public function test_endpoints_and_page(): void
    {
        $in = $this->search();
        $this->getJson(route('report.student.expected.result.new.data', $in))
            ->assertOk()->assertJsonStructure(['terms', 'units', 'courses', 'groups', 'statuses', 'intakes', 'rows', 'unplaced']);
        Excel::fake();
        $this->get(route('report.student.expected.result.new.excel', $in))->assertOk();
        Excel::assertDownloaded('board_expected_result_report.xlsx', fn (CustomExpectedResultCollectionExport $export) => $export->headings()[1][0] === 'Student ID'
            && $export->headings()[1][6] === 'Attendance Term');

        $this->get(route('report.student.expected.result.view'))->assertOk()
            ->assertSee('data-ser', false)
            ->assertSee('data-srp-pane="new"', false)
            ->assertSee('data-srp-pane="old"', false)
            ->assertSee('id="studentGroupSearchForm"', false);
    }
}
