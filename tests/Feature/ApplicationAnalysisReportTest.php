<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reports\ApplicationAnalysis;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Admission Report (/reports/applicant-analysis), redesigned 23 Nov 2026 on
 * the user's two-page landscape design. Builds a throwaway intake inside the
 * test transaction and checks the counting rules the review asked for:
 * statuses grouped once, entry route adding up, age bands without gaps.
 */
class ApplicationAnalysisReportTest extends TestCase
{
    use DatabaseTransactions;

    private int $semesterId;
    private array $applicants = [];

    protected function setUp(): void
    {
        parent::setUp();

        $ref = fn (string $table) => (int) DB::table($table)->orderBy('id')->value('id');
        foreach (['applicant_users', 'titles', 'countries', 'awarding_bodies', 'source_tuition_fees', 'venues'] as $table) {
            if ($ref($table) < 1) {
                $this->markTestSkipped("No {$table} rows in this database.");
            }
        }
        $now = now();
        $this->semesterId = DB::table('semesters')->insertGetId(['name' => '2099 September', 'created_by' => 1, 'created_at' => $now]);
        $courseId = DB::table('courses')->insertGetId([
            'name' => 'HND in Testing and Quality (Assurance Pathway)', 'degree_offered' => 'HND', 'pre_qualification' => 'None',
            'awarding_body_id' => $ref('awarding_bodies'), 'source_tuition_fee_id' => $ref('source_tuition_fees'), 'created_by' => 1, 'created_at' => $now,
        ]);
        $creationId = DB::table('course_creations')->insertGetId([
            'semester_id' => $this->semesterId, 'course_id' => $courseId, 'duration' => 2, 'unit_length' => 'Years', 'created_by' => 1, 'created_at' => $now,
        ]);
        DB::table('course_creation_venue')->insert([
            'course_creation_id' => $creationId, 'venue_id' => $ref('venues'), 'weekdays' => 10, 'weekends' => 5, 'created_at' => $now,
        ]);

        $country = $ref('countries');
        // [status, full time?, sex, age in months, qualification, employment]
        $people = [
            'both' => [7, 0, 2, 25 * 12, true, true],            // qualification and a job: Academic, once
            'mature' => [7, 1, 1, 29 * 12 + 6, false, true],     // 29½: the old bands skipped this age
            'unknown' => [7, 0, 3, 21 * 12, false, false],       // exactly 21: in 21–29 only
            'new' => [2, 0, 2, 30 * 12, false, false],
            'rejected' => [8, 0, 2, 30 * 12, false, false],
            'declined' => [9, 0, 2, 30 * 12, false, false],
            'canceled' => [46, 0, 2, 30 * 12, false, false],
            'submitting' => [1, 0, 2, 30 * 12, false, false],    // not an application yet
            'deleted' => [7, 0, 2, 30 * 12, true, false],        // proposed course soft-deleted
        ];
        foreach ($people as $key => [$status, $fullTime, $sex, $months, $qualification, $employment]) {
            $id = DB::table('applicants')->insertGetId([
                'applicant_user_id' => $ref('applicant_users'), 'title_id' => $ref('titles'), 'first_name' => 'Report', 'last_name' => ucfirst($key),
                'date_of_birth' => now()->subMonths($months)->toDateString(), 'status_id' => $status, 'sex_identifier_id' => $sex,
                'nationality_id' => $country, 'country_id' => $country, 'created_by' => 1, 'created_at' => $now,
            ]);
            DB::table('applicant_proposed_courses')->insert([
                'applicant_id' => $id, 'course_creation_id' => $creationId, 'semester_id' => $this->semesterId, 'full_time' => $fullTime,
                'created_by' => 1, 'created_at' => $now, 'deleted_at' => ($key === 'deleted' ? $now : null),
            ]);
            DB::table('applicant_other_details')->insert([
                'applicant_id' => $id, 'is_edication_qualification' => $qualification ? 1 : 0,
                'employment_status' => $employment ? 'Full Time' : null, 'created_by' => 1, 'created_at' => $now,
            ]);
            if ($employment) {
                DB::table('applicant_employments')->insert([
                    'applicant_id' => $id, 'company_name' => 'Test Ltd', 'company_phone' => '0', 'position' => 'Clerk', 'start_date' => '01-2020',
                    'address_line_1' => '1 Street', 'post_code' => 'E1 1AA', 'city' => 'London', 'country' => 'UK', 'created_by' => 1, 'created_at' => $now,
                ]);
            }
            $this->applicants[$key] = $id;
        }
    }

    public function test_statuses_are_grouped_once_against_the_target(): void
    {
        $report = app(ApplicationAnalysis::class)->build($this->semesterId);
        $t = $report['totals'];

        $this->assertSame('September 2099 intake', $report['intake']);
        $this->assertSame(['applied' => 7, 'accepted' => 3, 'pending' => 1, 'rejected' => 1, 'declined' => 1, 'canceled' => 1], array_intersect_key($t, array_flip(['applied', 'accepted', 'pending', 'rejected', 'declined', 'canceled'])));
        $this->assertSame(15, $t['target']);
        $this->assertSame(-12, $t['gap']);
        $this->assertSame(20, $t['of_target']);
        $this->assertSame(['1 new'], $report['pending_parts']);

        $course = $report['courses'][0];
        $this->assertSame('HND Testing & Quality', $course['name']);
        $this->assertSame('Testing & Quality', $course['subject']);
        $this->assertSame('12 short. 1 still pending, not enough to close the gap.', $course['note']);
        $this->assertTrue($course['note_warn']);

        $rows = collect($report['delivery'])->keyBy('label');
        $this->assertSame([10, 6, 2], [$rows['Testing & Quality · Weekdays']['target'], $rows['Testing & Quality · Weekdays']['applied'], $rows['Testing & Quality · Weekdays']['accepted']]);
        $this->assertSame([5, 1, 1], [$rows['Testing & Quality · Evening / weekend']['target'], $rows['Testing & Quality · Evening / weekend']['applied'], $rows['Testing & Quality · Evening / weekend']['accepted']]);
    }

    public function test_who_accepted_adds_up(): void
    {
        $cohort = app(ApplicationAnalysis::class)->build($this->semesterId)['cohort'];

        $this->assertSame(3, $cohort['total']);
        $entry = $cohort['entry'][0];
        $this->assertSame([1, 1, 1], [$entry['academic'], $entry['mature'], $entry['unknown']]);
        $this->assertSame([$this->applicants['unknown']], $entry['unknown_ids']);

        $ages = collect($cohort['age']['rows'])->pluck('count', 'label')->all();
        $this->assertSame(['18–20' => 0, '21–29' => 3, '30–39' => 0, '40–49' => 0, '50–59' => 0], $ages);
        $this->assertSame(25, $cohort['age']['mean']);

        $this->assertSame([1, 1, 1], [$cohort['gender']['male'], $cohort['gender']['female'], $cohort['gender']['other']]);
        $this->assertSame('33% male · 33% female · 33% other', $cohort['gender']['caption']);
    }

    public function test_the_screen_report_and_the_pdf_render(): void
    {
        $staff = User::find(1);

        $html = $this->actingAs($staff)->postJson(route('reports.applicant.analysis.generate.report'), ['ap_an_semester_id' => $this->semesterId])
            ->assertOk()->json('htm');
        $this->assertStringContainsString('Who accepted', $html);
        $this->assertStringContainsString('<span class="aar-hero__big">3</span>', $html);
        $this->assertStringContainsString('−12', $html);
        $this->assertStringContainsString('data-ids="'.$this->applicants['unknown'].'"', $html);
        $this->assertStringNotContainsString('Offered Students Analysis', $html);

        $response = $this->actingAs($staff)->get(route('reports.applicant.analysis.print.report', $this->semesterId))->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('Admission_Report_2099_September.pdf', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_the_dark_logo_heads_the_pdf_only(): void
    {
        $data = ['report' => app(ApplicationAnalysis::class)->build($this->semesterId), 'author' => 'Test', 'date' => '1 Jan 2099'];

        $pdf = view('pages.reports.application-analysis.pdf', $data + ['logo' => 'data:image/png;base64,AAAA', 'pages' => 2])->render();
        $this->assertStringContainsString('<img class="aar-head__logo" src="data:image/png;base64,AAAA"', $pdf);
        $this->assertStringContainsString('content: "Page " counter(page) " of 2"', $pdf);

        $screen = view('pages.reports.application-analysis.report', $data + ['medium' => 'screen'])->render();
        $this->assertStringNotContainsString('aar-head__logo', $screen);
        $this->assertStringContainsString('<div class="aar-head__title">Admission Report</div>', $screen);
    }

    public function test_an_intake_without_targets_shows_no_gap(): void
    {
        DB::table('course_creation_venue')->whereIn('course_creation_id', DB::table('course_creations')->where('semester_id', $this->semesterId)->pluck('id'))->update(['weekdays' => 0, 'weekends' => 0]);
        $report = app(ApplicationAnalysis::class)->build($this->semesterId);

        $this->assertSame('No targets are set for this intake.', $report['summary']);
        $this->assertSame('No target set. 1 still pending.', $report['courses'][0]['note']);

        $html = view('pages.reports.application-analysis.report', ['report' => $report, 'medium' => 'screen', 'author' => 'Test', 'date' => '1 Jan 2099'])->render();
        $this->assertStringContainsString('accepted · no target set', $html);
        $this->assertStringNotContainsString('+3', $html);
    }
}
