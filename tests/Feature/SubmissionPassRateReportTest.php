<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\IntakePerformance\SubmissionPassRateReportController;
use App\Models\Semester;
use App\Models\User;
use App\Services\Reports\SubmissionPassRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reports › Intake Performance › Submission & Pass Rate, new version (23 Nov
 * 2026): a "New version" tab beside the old report, on the user's web and PDF
 * designs. Expected, not submitted and passed must stay those of the old
 * report. Students per term may be higher: the old report counted them from a
 * GROUP_CONCAT that MySQL cuts at 1,024 characters (about 171 ids).
 */
class SubmissionPassRateReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_new_version_counts_as_the_old_report(): void
    {
        $old = app(SubmissionPassRateReportController::class);
        $new = app(SubmissionPassRate::class);
        $checked = 0;
        foreach (Semester::orderBy('id', 'desc')->limit(10)->pluck('id') as $semesterId) {
            $before = [];
            foreach (($old->refineResult([$semesterId])['result'][$semesterId]['course'] ?? []) as $course) {
                foreach (($course['term_result'] ?? []) as $term) {
                    $before[] = [(int) $term->term_declaration_id, (int) $term->TOTAL, (int) $term->A, (int) $term->TOTALPASS, count(explode(',', (string) $term->student_data_ids))];
                }
            }
            $after = [];
            foreach ($new->build((int) $semesterId)['courses'] as $course) {
                foreach ($course['terms'] as $term) {
                    $after[] = [$term['id'], $term['expected'], $term['not_submitted'], $term['passed'], $term['students']];
                }
            }
            sort($before);
            sort($after);
            $this->assertSame(array_map(fn ($r) => array_slice($r, 0, 4), $before), array_map(fn ($r) => array_slice($r, 0, 4), $after), 'Semester '.$semesterId);
            foreach ($after as $i => $row) {
                $this->assertGreaterThanOrEqual($before[$i][4], $row[4], 'Students, semester '.$semesterId);
            }
            $checked++;
        }
        if ($checked === 0) {
            $this->markTestSkipped('No semesters in this database.');
        }
    }

    public function test_september_2025_reads_as_in_the_design(): void
    {
        $semesterId = (int) Semester::where('name', '2025 September')->value('id');
        if ($semesterId < 1) {
            $this->markTestSkipped('2025 September is not in this database.');
        }
        $report = app(SubmissionPassRate::class)->build($semesterId);
        $t = $report['totals'];

        $this->assertSame([2142, 1875, 1694, 267, 181], [$t['expected'], $t['submitted'], $t['passed'], $t['not_submitted'], $t['submitted_not_passed']]);
        $this->assertSame([87.5, 79.1, 90.3], [$t['submission_rate'], $t['pass_rate'], $t['pass_of_submitted']]);
        $this->assertSame([79, 8, 13], $report['waffle']);
        $this->assertSame([61.4, 79.1, 88.0, null], array_column($report['courses'], 'pass_rate'));

        $business = collect($report['courses'])->firstWhere('title', 'HND Business');
        $this->assertSame(209, collect($business['terms'])->firstWhere('name', '2025 September')['students']); // the old report showed 171
    }

    public function test_the_page_and_the_pdf(): void
    {
        $staff = User::find(1);
        $page = $this->actingAs($staff)->get(route('reports.intake.performance'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-controls="sprPaneNew" data-version="new"', $page);
        $this->assertStringContainsString('id="submissionPassRateSearchForm"', $page); // the old form is still there
        $this->assertStringContainsString('data-generate="reports.intake.performance.submission.rate.generate"', $page);

        $this->actingAs($staff)->postJson(route('reports.intake.performance.submission.rate.generate'), [])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids');

        $semesterId = (int) Semester::where('name', '2025 September')->value('id');
        if ($semesterId < 1) {
            return;
        }
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.submission.rate.generate'), ['semester_ids' => [$semesterId]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('Out of every 100 assignments expected', $html);
        $this->assertStringContainsString('Nothing due yet', $html);
        $this->assertStringContainsString('Of the 1,875 assignments actually submitted, 90.3% passed.', $html);
        $this->assertSame(3, substr_count($html, 'class="crn-att-toggle" aria-expanded="false"')); // courses with terms fold

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.submission.rate.pdf', $semesterId))->assertOk();
        $this->assertStringContainsString('Submission_Pass_Rate_2025_September.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf->getContent()));

        // Two intakes: totals add up, the waffle is over both, and an All intakes row closes each table.
        $january = (int) Semester::where('name', '2026 January')->value('id');
        if ($january < 1) {
            return;
        }
        $report = app(SubmissionPassRate::class)->buildMany([$semesterId, $january]);
        $this->assertSame($report['intakes'][0]['totals']['expected'] + $report['intakes'][1]['totals']['expected'], $report['totals']['expected']);
        $this->assertSame(100, array_sum($report['waffle']));
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.submission.rate.generate'), ['semester_ids' => [$semesterId, $january]])
            ->assertOk()->json('htm');
        $this->assertSame(2, substr_count($html, 'All intakes'));
        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.submission.rate.pdf', $semesterId.'_'.$january))->assertOk();
        $this->assertStringContainsString('Submission_Pass_Rate_2025_September_2026_January.pdf', (string) $pdf->headers->get('content-disposition'));
    }
}
