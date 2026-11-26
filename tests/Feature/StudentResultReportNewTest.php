<?php

namespace Tests\Feature;

use App\Exports\CustomArrayCollectionExport;
use App\Http\Controllers\Reports\StudentDataReportController;
use App\Http\Controllers\Reports\StudentResultReportController;
use App\Models\User;
use App\Services\Reports\StudentResultReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Reports › Student Result Report "New version" (user, 25 Nov 2026): the old search's students,
 * the old board export's grades, the fixed filters, the screen data, the Excel and the page's two
 * tabs. Read-only against the dev database.
 */
class StudentResultReportNewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
    }

    /** Intakes with students on an active course relation, biggest first. */
    private function intakes(int $min = 20): array
    {
        return DB::table('student_course_relations as scr')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->where('scr.active', 1)->whereNull('scr.deleted_at')
            ->groupBy('cc.semester_id')->havingRaw('count(*) >= ?', [$min])
            ->orderByDesc('cc.semester_id')->limit(12)
            ->pluck('cc.semester_id')->map(fn ($id) => (int) $id)->all();
    }

    private function oldSearch(array $group): array
    {
        $ids = app(StudentDataReportController::class)->totalCount(new Request(['group' => $group]))->getData(true)['student_ids'];
        $live = DB::table('students')->whereIn('id', $ids ?: [0])->whereNull('deleted_at')->pluck('id')->map(fn ($id) => (int) $id)->all();
        sort($live);

        return $live;
    }

    public function test_students_match_the_old_search(): void
    {
        $service = new StudentResultReport;
        [$a, $b] = array_slice($this->intakes(), 2, 2) + [null, null];
        if (! $b) {
            $this->markTestSkipped('Not enough intakes.');
        }
        $term = (int) DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->join('student_course_relations as scr', 'scr.student_id', '=', 'a.student_id')
            ->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->where('cc.semester_id', $a)->where('scr.active', 1)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->max('p.term_declaration_id');
        $course = (int) DB::table('student_course_relations as scr')->join('course_creations as cc', 'cc.id', '=', 'scr.course_creation_id')
            ->where('cc.semester_id', $a)->where('scr.active', 1)->value('cc.course_id');

        $cases = [
            [['intake_semester' => [$a]], ['intake' => [$a]]],
            [['intake_semester' => [$a, $b], 'course' => [$course]], ['intake' => [$a, $b], 'course' => [$course]]],
            [['intake_semester' => [$a], 'attendance_semester' => [$term]], ['intake' => [$a], 'attendance' => [$term]]],
            [['group_student_status' => [23]], ['status' => [23]]],
            [['intake_semester' => [$a], 'group_student_status' => [23, 30]], ['intake' => [$a], 'status' => [23, 30]]],
            [['student_type' => ['UK']], ['type' => ['UK']]],
        ];
        foreach ($cases as [$old, $new]) {
            $this->assertSame($this->oldSearch($old), array_keys($service->students(StudentResultReport::filters($new))), json_encode($new));
        }
    }

    public function test_the_sheet_matches_the_old_board_export(): void
    {
        $service = new StudentResultReport;
        $checked = 0;
        foreach (array_reverse($this->intakes(20)) as $intake) {
            $filters = StudentResultReport::filters(['intake' => [$intake]]);
            $ids = array_keys($service->students($filters));
            if (count($ids) > 200) continue; // the old export takes ~5 s per 250 students
            Excel::fake();
            try {
                app(StudentResultReportController::class)->excelDownload(new Request(['studentIds' => implode(',', $ids)]));
            } catch (\Throwable $e) {
                continue; // the old export fails for a student with no group
            }
            $old = null;
            Excel::assertDownloaded('board_result_report.xlsx', function (CustomArrayCollectionExport $export) use (&$old) {
                $old = [array_values($export->collection()->all()), $export->headings()];

                return true;
            });
            if (! $old[0]) continue;

            [$rows, $headers, $units] = $service->sheet($filters);
            $this->assertSame(array_slice($old[1][2], 7), array_slice($headers[2], 7), "$intake unit columns");
            $byReg = [];
            foreach ($old[0] as $row) $byReg[$row[0]] = $row;
            $withResults = array_values(array_filter($rows, fn ($r) => isset($byReg[$r[0]])));
            $this->assertSame(array_column($old[0], 0), array_column($withResults, 0), "$intake row order");
            foreach ($withResults as $row) {
                $o = $byReg[$row[0]];
                $this->assertSame(array_map('strval', array_slice($o, 7)), array_map('strval', array_slice($row, 7)), "$intake {$row[0]} grades and completed units");
                $this->assertSame(trim($o[1]), $row[1], "$intake {$row[0]} name");
                foreach ([2, 3, 5, 6] as $col) $this->assertSame((string) $o[$col], (string) $row[$col], "$intake {$row[0]} column $col");
            }
            // The rest have no results at all.
            foreach (array_filter($rows, fn ($r) => ! isset($byReg[$r[0]])) as $row) {
                $this->assertSame(array_fill(0, count($units), ''), array_slice($row, 7, count($units)), "$intake {$row[0]} has no results");
            }
            if (++$checked === 2) break;
        }
        if (! $checked) {
            $this->markTestSkipped('No intake the old export can run.');
        }
    }

    public function test_the_screen_data_is_consistent(): void
    {
        $intake = $this->intakes(50)[2] ?? null;
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $service = new StudentResultReport;
        $filters = StudentResultReport::filters(['intake' => [$intake]]);
        $data = $service->build($filters);
        [$rows] = $service->sheet($filters);
        $this->assertCount(count($service->students($filters)), $data['rows']);

        $sheet = [];
        foreach ($rows as $r) $sheet[$r[0]] = $r;
        foreach ($data['rows'] as $r) {
            $course = $data['courses'][$r[4]];
            $this->assertCount(count($course['units']), $r[6], 'one cell per course unit');
            $grades = array_merge(array_filter($r[6], 'strlen'), array_column($r[7], 1));
            $done = count(array_filter($grades, fn ($g) => in_array($g, StudentResultReport::PASSED, true)));
            $this->assertSame($sheet[$r[1]][7 + count($data['units'])], $done, "{$r[1]} completed units as the export");
            // Course units and other units never overlap.
            $this->assertEmpty(array_intersect(array_column($r[7], 0), $course['units']));
        }
        // A course unit is held by at least 1 in 10 of the course's students.
        foreach ($data['courses'] as $ci => $course) {
            $students = array_values(array_filter($data['rows'], fn ($r) => $r[4] === $ci));
            foreach ($course['units'] as $k => $unit) {
                $had = count(array_filter($students, fn ($r) => $r[6][$k] !== ''));
                $this->assertGreaterThanOrEqual(max(1, (int) ceil(count($students) * StudentResultReport::UNIT_SHARE)), $had, $data['units'][$unit]);
            }
        }
    }

    public function test_fixed_filters(): void
    {
        $service = new StudentResultReport;
        $intake = $this->intakes(50)[2] ?? null;
        if (! $intake) {
            $this->markTestSkipped('No intake.');
        }
        $all = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake]])));
        $yes = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake], 'evening' => 'Yes'])));
        $no = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake], 'evening' => 'No'])));
        $this->assertEqualsCanonicalizing($all, array_values(array_unique(array_merge($yes, $no))), 'Evening / Weekend Yes and No cover the intake');
        $this->assertEmpty(array_intersect($yes, $no));

        // Master group without an attendance semester narrows (the old search ignored it).
        $group = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')->join('groups as g', 'g.id', '=', 'p.group_id')
            ->whereIn('a.student_id', $all ?: [0])->whereNull('a.deleted_at')->whereNull('p.deleted_at')->value('g.name');
        if ($group !== null) {
            $inGroup = array_keys($service->students(StudentResultReport::filters(['intake' => [$intake], 'group' => [$group]])));
            $this->assertNotEmpty($inGroup);
            $this->assertEmpty(array_diff($inGroup, $all));
        }

        // Filters that match nobody stay nobody (the old search then listed everyone of the status).
        $this->assertSame([], $service->students(StudentResultReport::filters(['intake' => [-1], 'status' => [23]])));
    }

    public function test_endpoints_and_page(): void
    {
        $this->getJson(route('report.student.result.new.data'))->assertStatus(422);
        $intake = $this->intakes(20)[0];
        $this->getJson(route('report.student.result.new.data', ['intake' => [$intake]]))
            ->assertOk()->assertJsonStructure(['units', 'courses', 'statuses', 'groups', 'intakes', 'rows']);

        Excel::fake();
        $this->get(route('report.student.result.new.excel', ['intake' => [$intake]]))->assertOk();
        Excel::assertDownloaded('board_result_report.xlsx', fn (CustomArrayCollectionExport $export) => $export->headings()[1][0] === 'Student ID'
            && end($export->headings()[1]) === 'Assessment Board Outcome');

        $this->get(route('report.student.result.view'))->assertOk()
            ->assertSee('data-srp-pane="new"', false)
            ->assertSee('data-srp-pane="old"', false)
            ->assertSee('id="studentGroupSearchForm"', false);
    }
}
