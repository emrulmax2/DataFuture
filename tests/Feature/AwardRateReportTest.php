<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\IntakePerformance\AwardRateReportController;
use App\Models\Semester;
use App\Models\User;
use App\Services\Reports\AwardRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Reports › Intake Performance › Award Rate, new version (23 Nov 2026): a
 * "New version" tab beside the old report, on the user's web and PDF designs.
 * Registered and completed students per course must stay those of the old
 * report.
 */
class AwardRateReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_new_version_counts_as_the_old_report(): void
    {
        $old = app(AwardRateReportController::class);
        $new = app(AwardRate::class);
        $checked = 0;
        foreach (Semester::orderBy('id', 'desc')->limit(20)->pluck('id') as $semesterId) {
            $result = $old->refineResult([$semesterId])['result'][$semesterId];
            $before = array_values(array_map(function ($c) { return [$c['registered_students'], $c['awarded_students']]; }, $result['course'] ?? []));
            $report = $new->build((int) $semesterId);
            $after = array_map(function ($c) { return [$c['registered'], $c['completed']]; }, $report['courses']);
            sort($before);
            sort($after);
            $this->assertSame($before, $after, 'Semester '.$semesterId);
            $this->assertSame([$result['registered_students'], $result['awarded_students']], [$report['totals']['registered'], $report['totals']['completed']], 'Totals, semester '.$semesterId);
            $checked++;
        }
        if ($checked === 0) {
            $this->markTestSkipped('No semesters in this database.');
        }
    }

    public function test_january_2023_reads_as_in_the_design(): void
    {
        $semesterId = (int) Semester::where('name', '2023 January')->value('id');
        if ($semesterId < 1) {
            $this->markTestSkipped('2023 January is not in this database.');
        }
        $report = app(AwardRate::class)->build($semesterId);
        $t = $report['totals'];

        $this->assertSame([218, 164, 54, 75.2, 2], [$t['registered'], $t['completed'], $t['not_completed'], $t['rate'], $t['courses']]);
        $this->assertSame(['HND Hospitality Management', 'HND Business'], array_column($report['courses'], 'title'));
        $this->assertSame([63.6, 76.5], array_column($report['courses'], 'rate'));
        $this->assertSame([true, false], array_column($report['courses'], 'low'));
        $this->assertSame([true, false], array_column($report['courses'], 'small'));
        $this->assertSame('Small cohort = fewer than 30 registered students; with 22 registered, one student changes the rate by 4.5 percentage points.', $report['small_note']);
    }

    public function test_the_page_and_the_pdf(): void
    {
        $staff = User::find(1);
        $page = $this->actingAs($staff)->get(route('reports.intake.performance'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-controls="awdPaneNew" data-version="new"', $page);
        $this->assertStringContainsString('id="awardRateSearchForm"', $page); // the old form is still there
        $this->assertStringContainsString('data-generate="reports.intake.performance.award.rate.generate"', $page);

        $this->actingAs($staff)->postJson(route('reports.intake.performance.award.rate.generate'), [])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids');

        $semesterId = (int) Semester::where('name', '2023 January')->value('id');
        if ($semesterId < 1) {
            return;
        }
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.award.rate.generate'), ['semester_ids' => [$semesterId]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('164 of 218 registered students', $html);
        $this->assertStringContainsString('Across 1 intake', $html);
        $this->assertStringContainsString('75.2% · 164 of 218 completed', $html);
        $this->assertStringContainsString('aria-label="22 students: 14 completed, 8 did not complete"', $html);
        $this->assertSame(218, substr_count($html, '<span class="is-done"></span>') + substr_count($html, '<span class="is-not"></span>'));

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.award.rate.pdf', $semesterId))->assertOk();
        $this->assertStringContainsString('Award_Rate_2023_January.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf->getContent()));

        // Two intakes: totals add up and each intake keeps its own rate.
        $may = (int) Semester::where('name', '2023 May')->value('id');
        if ($may < 1) {
            return;
        }
        $report = app(AwardRate::class)->buildMany([$may, $semesterId]);
        $this->assertSame('January 2023 and May 2023 intakes', $report['intake']);
        $this->assertSame([75.2], [$report['intakes'][0]['totals']['rate']]);
        $this->assertSame($report['intakes'][0]['totals']['registered'] + $report['intakes'][1]['totals']['registered'], $report['totals']['registered']);
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.award.rate.generate'), ['semester_ids' => [$semesterId, $may]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('Across 2 intakes', $html);
        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.award.rate.pdf', $semesterId.'_'.$may))->assertOk();
        $this->assertStringContainsString('Award_Rate_2023_January_2023_May.pdf', (string) $pdf->headers->get('content-disposition'));
    }
}
