<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\IntakePerformance\AttendanceRateReportController;
use App\Models\Semester;
use App\Models\User;
use App\Services\Reports\AttendanceRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reports › Intake Performance › Attendance Rate, new version (23 Nov 2026):
 * a "New version" tab beside the old report, on the user's web and PDF
 * designs. The numbers must stay those of the old report.
 */
class AttendanceRateReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_new_version_counts_exactly_as_the_old_report(): void
    {
        $old = app(AttendanceRateReportController::class);
        $new = app(AttendanceRate::class);
        $checked = 0;
        foreach (Semester::orderBy('id', 'desc')->limit(8)->pluck('id') as $semesterId) {
            $before = [];
            foreach (($old->refineResult([$semesterId])['result'][$semesterId]['course'] ?? []) as $courseId => $course) {
                $rows = [];
                $left = 0;
                $previous = $course['start_date'];
                foreach (($course['term_result'] ?? []) as $i => $term) {
                    if ($i > 0) {
                        $left += $old->get_last_term_dropped_out_students($semesterId, $courseId, $previous, $term->start_date);
                    }
                    $rows[] = [(int) $term->term_id, $course['registered_students'] - $left, (int) $term->TOTAL, (int) $term->A, (int) $term->P, (int) $term->O];
                    $previous = $term->start_date;
                }
                $before[] = [(int) $course['registered_students'], $rows];
            }
            $after = [];
            foreach ($new->build((int) $semesterId)['courses'] as $course) {
                $after[] = [$course['registered'], array_map(function ($term) {
                    return [$term['id'], $term['registered'], $term['total'], $term['codes']['A'], $term['codes']['P'], $term['codes']['O']];
                }, $course['terms'])];
            }
            sort($before);
            sort($after);
            $this->assertSame($before, $after, 'Semester '.$semesterId);
            $checked++;
        }
        if ($checked === 0) {
            $this->markTestSkipped('No semesters in this database.');
        }
    }

    public function test_january_2026_reads_as_in_the_design(): void
    {
        $semesterId = (int) Semester::where('name', '2026 January')->value('id');
        if ($semesterId < 1) {
            $this->markTestSkipped('2026 January is not in this database.');
        }
        $report = app(AttendanceRate::class)->build($semesterId);
        $t = $report['totals'];

        $this->assertSame(['Jan 2026 term', 'May 2026 term', 'Sep 2026 term'], array_column(array_values($report['slots']), 'label'));
        $this->assertSame(['Jan 2026', 'May / June 2026', 'Sep 2026'], array_column(array_values($report['slots']), 'heading'));
        $this->assertSame([277, 56773, 19272, 37501, 66.1, -14.4], [$t['registered'], $t['total'], $t['codes']['A'], $t['present'], $t['rate'], $report['trend']['change']]);
        $this->assertSame([74.3, 61.6, 59.8], array_column(array_values($t['cells']), 'rate'));
        $this->assertSame([0.0, -12.7, -14.4], array_column(array_values($t['cells']), 'vs_first'));
        $this->assertSame([62.1, 66.4, 66.4, 67.3], array_column($report['courses'], 'rate'));
        $this->assertSame([true, false, false, false], array_column($report['courses'], 'low'));
        $this->assertSame(['marks' => 615, 'terms' => 6, 'years' => [2024, 2025]], $report['early']);

        $bsc = collect($report['courses'])->first(fn ($course) => str_starts_with($course['title'], 'BSc'));
        $this->assertSame([76.3, 58.2], array_column(array_values($bsc['cells']), 'rate'));
    }

    public function test_the_page_and_the_pdf(): void
    {
        $staff = User::find(1);
        $page = $this->actingAs($staff)->get(route('reports.intake.performance'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-controls="atnPaneNew" data-version="new"', $page);
        $this->assertStringContainsString('id="attendanceRateSearchForm"', $page); // the old form is still there
        $this->assertStringContainsString('data-generate="reports.intake.performance.attendance.rate.generate"', $page);

        $this->actingAs($staff)->postJson(route('reports.intake.performance.attendance.rate.generate'), [])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids');

        $semesterId = (int) Semester::where('name', '2026 January')->value('id');
        if ($semesterId < 1) {
            return;
        }
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.attendance.rate.generate'), ['semester_ids' => [$semesterId]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('First term', $html);
        $this->assertStringContainsString('Down 12.7 points on the first term', $html);
        $this->assertStringContainsString('May / June 2026 term', $html);
        $this->assertStringContainsString('data-crn-toggle="atn-intake-0"', $html);
        $this->assertStringContainsString('May / June 2026', $html);
        $this->assertStringContainsString('Show sessions by attendance code and term', $html);
        $this->assertSame(4, substr_count($html, 'class="crn-att-toggle" aria-expanded="false"')); // each course folds its terms
        $this->assertStringContainsString('Terms dated before the intake started hold 615 sessions; they are included in the totals.', $html);

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.attendance.rate.pdf', $semesterId))->assertOk();
        $this->assertStringContainsString('Attendance_Rate_2026_January.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf->getContent()));

        // Two intakes: the term columns of both, and an All intakes row.
        $may = (int) Semester::where('name', '2026 May')->value('id');
        if ($may < 1) {
            return;
        }
        $report = app(AttendanceRate::class)->buildMany([$may, $semesterId]);
        $this->assertSame(['2026 January', '2026 May'], array_column($report['intakes'], 'semester'));
        $this->assertSame($report['intakes'][0]['totals']['total'] + $report['intakes'][1]['totals']['total'], $report['totals']['total']);
        $this->assertSame(66.1, $report['intakes'][0]['totals']['rate']); // each intake as on its own
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.attendance.rate.generate'), ['semester_ids' => [$semesterId, $may]])
            ->assertOk()->json('htm');
        $this->assertSame(2, substr_count($html, 'All intakes')); // summary and detail
        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.attendance.rate.pdf', $semesterId.'_'.$may))->assertOk();
        $this->assertStringContainsString('Attendance_Rate_2026_January_2026_May.pdf', (string) $pdf->headers->get('content-disposition'));
    }
}
