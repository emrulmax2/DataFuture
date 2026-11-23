<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\IntakePerformance\RetentionRateReportController;
use App\Models\Semester;
use App\Models\User;
use App\Services\Reports\RetentionRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Reports › Intake Performance › Retention Rate, new version (23 Nov 2026):
 * a "New version" tab beside the old report, on the user's web and PDF
 * designs, with an Excel version. The numbers must stay those of the old report.
 */
class RetentionRateReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_new_version_counts_exactly_as_the_old_report(): void
    {
        $old = app(RetentionRateReportController::class);
        $new = app(RetentionRate::class);
        $checked = 0;
        foreach (Semester::orderBy('id', 'desc')->limit(14)->pluck('id') as $semesterId) {
            $before = $old->refineResult([$semesterId])['result'][$semesterId] ?? [];
            $after = $new->build((int) $semesterId);

            $this->assertSame(
                [(int) ($before['admissions'] ?? 0), (int) ($before['registered'] ?? 0), (int) ($before['droppedout'] ?? 0)],
                [$after['totals']['intake'], $after['totals']['registered'], $after['totals']['dropped_out']],
                'Semester '.$semesterId
            );
            $checked++;
        }
        if ($checked === 0) {
            $this->markTestSkipped('No semesters in this database.');
        }
    }

    public function test_january_2025_reads_as_in_the_design(): void
    {
        $semesterId = (int) Semester::where('name', '2025 January')->value('id');
        if ($semesterId < 1) {
            $this->markTestSkipped('2025 January is not in this database.');
        }
        $report = app(RetentionRate::class)->build($semesterId);
        $students = app(RetentionRate::class)->students($semesterId);

        $this->assertSame(['intake' => 248, 'registered' => 238, 'kept' => 205, 'dropped_out' => 33, 'never_registered' => 10, 'rate' => 86.1], $report['totals']);
        $this->assertSame([71.4, 85.3, 88.6], array_column($report['courses'], 'rate'));
        $this->assertSame('Fewer than 30 registered students. With 7 registered, one student changes the rate by 14 percentage points, so read this rate with care.', $report['small_note']);

        $outcomes = array_count_values(array_column($students, 'outcome'));
        ksort($outcomes);
        $this->assertSame(['Dropped out' => 33, 'Never registered' => 10, 'Retained' => 205], $outcomes);
    }

    public function test_the_page_the_pdf_and_the_excel(): void
    {
        $staff = User::find(1);
        $page = $this->actingAs($staff)->get(route('reports.intake.performance'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-controls="rtnPaneNew" data-version="new"', $page);
        $this->assertStringContainsString('id="retentionRateSearchForm"', $page); // the old form is still there
        $this->assertStringContainsString('data-excel="reports.intake.performance.retention.rate.excel"', $page);

        $this->actingAs($staff)->postJson(route('reports.intake.performance.retention.rate.generate'), [])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids');

        $semesterId = (int) Semester::where('name', '2025 January')->value('id');
        if ($semesterId < 1) {
            return;
        }
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.retention.rate.generate'), ['semester_ids' => [$semesterId]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('Retention rate', $html);
        $this->assertStringContainsString('Still enrolled at the report date', $html);
        $this->assertStringContainsString('86.1%', $html);

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.retention.rate.pdf', $semesterId))->assertOk();
        $this->assertStringContainsString('Retention_Rate_2025_January.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $excel = $this->actingAs($staff)->get(route('reports.intake.performance.retention.rate.excel', $semesterId))->assertOk();
        $this->assertStringContainsString('Retention_Rate_2025_January.xlsx', (string) $excel->headers->get('content-disposition'));
        $book = IOFactory::load($excel->baseResponse->getFile()->getPathname());

        $this->assertSame(['Report', 'Students'], $book->getSheetNames());
        $report = $book->getSheetByName('Report');
        $this->assertSame('Retention Rate Report', $report->getCell('A2')->getValue());
        $this->assertSame('January 2025 intake', $report->getCell('E1')->getValue());
        $this->assertEqualsWithDelta(0.861, $report->getCell('A5')->getValue(), 0.0001);
        $this->assertSame('0.0%', $report->getStyle('A5')->getNumberFormat()->getFormatCode());

        $students = $book->getSheetByName('Students');
        $this->assertSame(['Intake', 'Registration no'], [$students->getCell('A1')->getValue(), $students->getCell('B1')->getValue()]);
        $this->assertSame('January 2025', $students->getCell('A2')->getValue());
        $this->assertSame(249, $students->getHighestRow()); // header + 248 students

        // Two intakes: one Excel with both, each student row naming its intake.
        $may = (int) Semester::where('name', '2025 May')->value('id');
        if ($may > 0) {
            $excel = $this->actingAs($staff)->get(route('reports.intake.performance.retention.rate.excel', $semesterId.'_'.$may))->assertOk();
            $this->assertStringContainsString('Retention_Rate_2025_January_2025_May.xlsx', (string) $excel->headers->get('content-disposition'));
            $book = IOFactory::load($excel->baseResponse->getFile()->getPathname());
            $this->assertSame('January 2025 and May 2025 intakes', $book->getSheetByName('Report')->getCell('E1')->getValue());
            $intakes = array_count_values(array_column($book->getSheetByName('Students')->rangeToArray('A2:A'.$book->getSheetByName('Students')->getHighestRow()), 0));
            $this->assertSame(248, $intakes['January 2025']);
            $this->assertGreaterThan(0, $intakes['May 2025'] ?? 0);
        }
    }
}
