<?php

namespace Tests\Feature;

use App\Http\Controllers\Reports\IntakePerformance\ContinuationReportController;
use App\Models\Semester;
use App\Models\User;
use App\Services\Reports\ContinuationRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Reports › Intake Performance › Continuation Rate, new version (23 Nov 2026):
 * a "New version" tab beside the old report, on the user's web and PDF designs.
 * The numbers must stay those of the old report.
 */
class ContinuationRateReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_new_version_counts_exactly_as_the_old_report(): void
    {
        $old = app(ContinuationReportController::class);
        $new = app(ContinuationRate::class);
        $checked = 0;
        foreach (Semester::orderBy('id', 'desc')->limit(14)->pluck('id') as $semesterId) {
            $before = $old->refineResult([$semesterId])['result'][$semesterId] ?? [];
            $after = $new->build((int) $semesterId);

            $this->assertSame(
                [(int) ($before['admissions'] ?? 0), (int) ($before['registered'] ?? 0), (int) ($before['droppedout'] ?? 0)],
                [$after['totals']['intake'], $after['totals']['registered'], $after['totals']['dropped_out']],
                'Semester '.$semesterId
            );
            foreach ($after['courses'] as $row) {
                if ($row['due']) {
                    $this->assertSame($row['registered'] - $row['dropped_out'], $row['kept']);
                    $this->assertSame($row['intake'] - $row['registered'], $row['never_registered']);
                }
            }
            $checked++;
        }
        if ($checked === 0) {
            $this->markTestSkipped('No semesters in this database.');
        }
    }

    public function test_rows_are_lowest_rate_first_and_names_are_tidied(): void
    {
        $semesterId = (int) Semester::where('name', '2025 September')->value('id');
        if ($semesterId < 1) {
            $this->markTestSkipped('2025 September is not in this database.');
        }
        $report = app(ContinuationRate::class)->build($semesterId);

        $rates = array_values(array_filter(array_column($report['courses'], 'rate'), fn ($rate) => $rate !== null));
        $sorted = $rates;
        sort($sorted);
        $this->assertSame($sorted, $rates);
        $this->assertSame('September 2025 intake', $report['intake']);

        $business = collect($report['courses'])->firstWhere('title', 'HND Business');
        if ($business) {
            $this->assertSame('Entrepreneurship and Small Business Management', $business['pathway']);
            $this->assertSame('HND Business (Entrepreneurship and Small Business Management)', $business['name']);
        }
    }

    public function test_an_intake_inside_its_380_days_is_not_due(): void
    {
        $now = now();
        $semesterId = DB::table('semesters')->insertGetId(['name' => '2099 January', 'created_by' => 1, 'created_at' => $now]);
        $courseId = DB::table('courses')->insertGetId([
            'name' => 'HND in Testing (Quality And Assurance)', 'degree_offered' => 'HND', 'pre_qualification' => 'None',
            'awarding_body_id' => (int) DB::table('awarding_bodies')->value('id'), 'source_tuition_fee_id' => (int) DB::table('source_tuition_fees')->value('id'),
            'created_by' => 1, 'created_at' => $now,
        ]);
        $creationId = DB::table('course_creations')->insertGetId([
            'semester_id' => $semesterId, 'course_id' => $courseId, 'duration' => 2, 'unit_length' => 'Years', 'created_by' => 1, 'created_at' => $now,
        ]);
        $start = now()->subDays(100);
        DB::table('course_creation_availabilities')->insert([
            'course_creation_id' => $creationId, 'admission_date' => $start->copy()->subMonth()->toDateString(), 'admission_end_date' => $start->toDateString(),
            'course_start_date' => $start->toDateString(), 'course_end_date' => $start->copy()->addYears(2)->toDateString(),
            'last_joinning_date' => $start->toDateString(), 'type' => 'UK', 'created_by' => 1, 'created_at' => $now,
        ]);

        $report = app(ContinuationRate::class)->build($semesterId);
        $this->assertFalse($report['has_due']);
        $this->assertSame($start->copy()->addDays(380)->toDateString(), $report['next_due']);
        $this->assertSame('HND Testing', $report['courses'][0]['title']);
        $this->assertSame('Quality and Assurance', $report['courses'][0]['pathway']);

        $html = $this->actingAs(User::find(1))->postJson(route('reports.intake.performance.continuation.rate.generate'), ['semester_id' => $semesterId])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('Not due yet.', $html);
        $this->assertStringContainsString('ends on '.$start->copy()->addDays(380)->format('j M Y'), $html);
    }

    public function test_the_page_has_both_versions_and_the_new_one_answers(): void
    {
        $staff = User::find(1);
        $page = $this->actingAs($staff)->get(route('reports.intake.performance'))->assertOk()->getContent();
        $this->assertStringContainsString('aria-controls="crnPaneNew" data-version="new"', $page);
        $this->assertStringContainsString('aria-controls="crnPaneOld" data-version="old"', $page);
        $this->assertStringContainsString('id="continuationRateSearchForm"', $page); // the old form is still there
        $this->assertStringNotContainsString('role="tab"', substr($page, strpos($page, 'crn-tabs'), 600)); // the theme's tab script grabs [role=tab]

        $this->actingAs($staff)->postJson(route('reports.intake.performance.continuation.rate.generate'), [])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids');
        $this->actingAs($staff)->postJson(route('reports.intake.performance.continuation.rate.generate'), ['semester_ids' => [999999]])
            ->assertStatus(422)->assertJsonValidationErrors('semester_ids.0');

        $semesterId = (int) Semester::where('name', '2025 September')->value('id');
        if ($semesterId < 1) {
            return;
        }
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.continuation.rate.generate'), ['semester_ids' => [$semesterId]])
            ->assertOk()->json('htm');
        $this->assertSame(4, preg_match_all('/class="crn-kpi( crn-kpi--main)?"/', $html));
        $this->assertStringContainsString('By intake and course', $html);
        $this->assertStringContainsString('data-crn-toggle="continuation-intake-0"', $html);
        $this->assertStringNotContainsString('All intakes', $html); // one intake: its own row is the total

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.continuation.rate.pdf', $semesterId))->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        $this->assertStringContainsString('Continuation_Rate_2025_September.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->actingAs($staff)->get(route('reports.intake.performance.continuation.rate.pdf', $semesterId.'_999999'))->assertNotFound();
    }

    /** The 23 Nov 2026 design: January 2025 and May 2025 intakes together. */
    public function test_two_intakes_read_as_in_the_design(): void
    {
        $january = (int) Semester::where('name', '2025 January')->value('id');
        $may = (int) Semester::where('name', '2025 May')->value('id');
        if ($january < 1 || $may < 1) {
            $this->markTestSkipped('2025 January or 2025 May is not in this database.');
        }
        $report = app(ContinuationRate::class)->buildMany([$may, $january]); // put back in intake order
        $t = $report['totals'];

        $this->assertSame('January 2025 and May 2025 intakes', $report['intake']);
        $this->assertSame(['2025 January', '2025 May'], array_column($report['intakes'], 'semester'));
        $this->assertSame([513, 488, 394, 94, 25, 80.7], [$t['intake'], $t['registered'], $t['kept'], $t['dropped_out'], $t['never_registered'], $t['rate']]);
        $this->assertSame([84.5, 77.2], array_map(function ($i) { return $i['totals']['rate']; }, $report['intakes']));
        // 71.4% is orange in the design, 73.4% is not.
        $this->assertSame([[71.4, true], [73.4, false]], [
            [$report['intakes'][0]['courses'][0]['rate'], $report['intakes'][0]['courses'][0]['low']],
            [$report['intakes'][1]['courses'][0]['rate'], $report['intakes'][1]['courses'][0]['low']],
        ]);

        $staff = User::find(1);
        $html = $this->actingAs($staff)->postJson(route('reports.intake.performance.continuation.rate.generate'), ['semester_ids' => [$january, $may]])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('All intakes', $html);
        $this->assertStringContainsString('394 of 488 registered students', $html);
        $this->assertStringContainsString('Of 513 students · not in the rate', $html);
        $this->assertStringContainsString('· small cohort', $html);

        $pdf = $this->actingAs($staff)->get(route('reports.intake.performance.continuation.rate.pdf', $january.'_'.$may))->assertOk();
        $this->assertStringContainsString('Continuation_Rate_2025_January_2025_May.pdf', (string) $pdf->headers->get('content-disposition'));
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page[^s]/', $pdf->getContent())); // one page, as the design
    }
}
