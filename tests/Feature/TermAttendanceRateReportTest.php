<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\TermPerformance\TermAttendancePerformanceReportController;
use App\Models\User;
use App\Services\Reports\TermAttendanceChart;
use App\Services\Reports\TermAttendanceTree;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Term Performance › Attendance Rates "New version" (user, 24 Nov 2026): the same figures as the
 * old report's pages (course list, course view, group view, and their trend pages), the JSON the
 * screen draws from, and the PDF. Read-only against the dev database.
 */
class TermAttendanceRateReportTest extends TestCase
{
    use DatabaseTransactions;

    private int $term;

    private array $data;

    private TermAttendancePerformanceReportController $old;

    protected function setUp(): void
    {
        parent::setUp();
        $term = DB::table('term_declarations as td')
            ->whereNotNull('td.start_date')->whereNotNull('td.end_date')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('plans as p')->join('attendances as a', 'a.plan_id', '=', 'p.id')
                ->whereColumn('p.term_declaration_id', 'td.id')->whereNull('a.deleted_at'))
            ->orderByDesc('td.id')->value('td.id');
        if (!$term) {
            $this->markTestSkipped('No term with attendance in this database.');
        }
        $this->term = (int) $term;
        $this->data = TermAttendanceTree::build($this->term);
        $this->old = app(TermAttendancePerformanceReportController::class);
        $this->actingAs(User::find(7));
    }

    private static function oldWeek(?object $row): ?float
    {
        return $row && $row->TOTAL > 0 ? (float) $row->percentage_withexcuse : null;
    }

    public function test_term_course_group_and_module_rates_match_the_old_report(): void
    {
        $tree = $this->data['tree'];
        $courses = $this->old->getTermAttendance($this->term)->keyBy('course_id');
        $this->assertSameSize($courses, $tree['children']);

        $attended = $courses->sum('P') + $courses->sum('O') + $courses->sum('E') + $courses->sum('M') + $courses->sum('H') + $courses->sum('L');
        $this->assertEqualsWithDelta(round($attended * 100 / $courses->sum('TOTAL'), 2), $tree['rate'], 0.011);

        foreach ($tree['children'] as $course) {
            $courseId = (int) substr($course['id'], 1);
            $this->assertEqualsWithDelta((float) $courses[$courseId]->percentage_withexcuse, $course['rate'], 0.011, $course['name']);
            $this->assertSame((int) $courses[$courseId]->TOTAL, $course['classes']);

            $groups = $this->old->getTermCourseAttendance($this->term, $courseId)->keyBy('group_name');
            $this->assertSameSize($groups, $course['children']);
            foreach ($course['children'] as $group) {
                $this->assertEqualsWithDelta((float) $groups[$group['name']]->percentage_withexcuse, $group['rate'], 0.011, $group['name']);
            }

            $group = $course['children'][0];
            $groupId = DB::table('groups')->where('name', $group['name'])->where('course_id', $courseId)->where('term_declaration_id', $this->term)->value('id');
            $modules = $this->old->getTermCourseGroupAttendance($this->term, $courseId, $groupId)->keyBy('module_creations_id');
            foreach ($group['children'] as $module) {
                $this->assertEqualsWithDelta((float) $modules[(int) substr($module['id'], 1)]->percentage_withexcuse, $module['rate'], 0.011, $module['name']);
            }
        }
    }

    public function test_weekly_figures_match_the_old_trend_pages(): void
    {
        $tree = $this->data['tree'];
        $this->assertCount(count($this->old->getTermTrendAttendance($this->term)), $this->data['weeks']);

        foreach (array_values($this->old->getTermTrendAttendance($this->term)) as $i => $week) {
            $this->assertEqualsWithDelta((float) $week['overall'], (float) ($tree['weekly'][$i] ?? 0), 0.011, "overall week $i");
            foreach ($tree['children'] as $course) {
                $old = self::oldWeek($week['rows'][(int) substr($course['id'], 1)] ?? null);
                $this->assertSame($old === null, $course['weekly'][$i] === null, "{$course['name']} week $i");
                if ($old !== null) {
                    $this->assertEqualsWithDelta($old, $course['weekly'][$i], 0.011);
                }
            }
        }

        // Course trend (one line per group) and group trend (one per module), first course.
        $course = $tree['children'][0];
        $courseId = (int) substr($course['id'], 1);
        foreach (array_values($this->old->getTermCourseTrendAttendance($this->term, $courseId)) as $i => $week) {
            foreach ($course['children'] as $group) {
                $old = self::oldWeek($week['rows'][str_replace(' ', '_', $group['name'])] ?? null);
                $this->assertSame($old === null, $group['weekly'][$i] === null, "{$group['name']} week $i");
                if ($old !== null) {
                    $this->assertEqualsWithDelta($old, $group['weekly'][$i], 0.011);
                }
            }
        }
        $group = $course['children'][0];
        $groupId = DB::table('groups')->where('name', $group['name'])->where('course_id', $courseId)->where('term_declaration_id', $this->term)->value('id');
        foreach (array_values($this->old->getTermCourseGroupTrendAttendance($this->term, $courseId, $groupId)) as $i => $week) {
            foreach ($group['children'] as $module) {
                $old = self::oldWeek($week['rows'][(int) substr($module['id'], 1)] ?? null);
                $this->assertSame($old === null, $module['weekly'][$i] === null, "{$module['name']} week $i");
                if ($old !== null) {
                    $this->assertEqualsWithDelta($old, $module['weekly'][$i], 0.011);
                }
            }
        }
    }

    public function test_the_screen_gets_the_whole_term_in_one_request(): void
    {
        $this->getJson(route('reports.term.performance.attendance.rate.generate', $this->term))
            ->assertOk()
            ->assertJsonPath('term.id', $this->term)
            ->assertJsonStructure(['term' => ['id', 'name', 'start', 'end'], 'weeks', 'tree' => ['id', 'name', 'rate', 'weekly', 'children' => [['id', 'name', 'rate', 'weekly', 'children' => [['children']]]]]]);

        $this->getJson(route('reports.term.performance.attendance.rate.generate', 99999999))->assertNotFound();
    }

    public function test_pdf_of_the_term_and_of_a_course(): void
    {
        $response = $this->get(route('reports.term.performance.attendance.rate.pdf', $this->term));
        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $course = $this->data['tree']['children'][0];
        $this->get(route('reports.term.performance.attendance.rate.pdf', ['term' => $this->term, 'path' => $course['id'], 'show' => '_all']))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="Attendance_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $this->data['term']['name'].' '.$course['name']), '_').'.pdf"');
    }

    public function test_chart_lines_default_and_follow_the_screen(): void
    {
        $node = $this->data['tree'];
        $all = TermAttendanceChart::series($node);
        $withWeeks = count(array_filter($node['children'], fn ($c) => TermAttendanceChart::has($c['weekly'])));
        $this->assertSame($withWeeks + 1 <= TermAttendanceChart::MAX_DEFAULT ? $withWeeks + 1 : 1, count($all));
        $this->assertSame('_all', $all[0]['key']);

        $one = TermAttendanceChart::series($node, [$node['children'][0]['id']]);
        $this->assertSame([$node['children'][0]['id']], array_column($one, 'key'));
    }
}
