<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The admission list hero has intake tabs. The chosen intake travels to the
 * list and export endpoints as `intake` (a semester id) and narrows the rows
 * on top of the search form's own filters.
 */
class AdmissionListIntakeTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    /** A semester that has submitted applications, with its count. */
    private function busiestIntake(): object
    {
        $row = DB::table('applicants')
            ->join('applicant_proposed_courses as apc', 'apc.applicant_id', '=', 'applicants.id')
            ->join('semesters', 'semesters.id', '=', 'apc.semester_id')
            ->whereNull('applicants.deleted_at')->whereNull('apc.deleted_at')->whereNull('semesters.deleted_at')
            ->whereNotNull('applicants.submission_date')
            ->where('applicants.status_id', '>', 1)
            ->groupBy('semesters.id', 'semesters.name')
            ->orderByDesc('n')
            ->first(['semesters.id', 'semesters.name', DB::raw('COUNT(*) AS n')]);
        $this->assertNotNull($row, 'A semester with submitted applications is needed for this test.');

        return $row;
    }

    public function test_the_page_renders_the_hero_with_intake_tabs_and_stage_tiles(): void
    {
        $response = $this->actingAs($this->staff())->get(route('admission'));

        $response->assertOk()
            ->assertSee('data-adp-hero', false)
            ->assertSee('data-adp-intake=""', false)
            ->assertSee('data-adp-quick="2"', false)
            ->assertSee('data-adp-quick="7"', false)
            ->assertSee('data-adp-hero-cells', false);
    }

    public function test_the_list_narrows_to_the_intake(): void
    {
        $intake = $this->busiestIntake();

        $response = $this->actingAs($this->staff())->getJson(route('admission.list', ['intake' => $intake->id, 'size' => 20, 'page' => 1]));

        $response->assertOk();
        $this->assertSame((int) $intake->n, (int) $response->json('total_rows'));
        foreach ($response->json('data') as $row) {
            $this->assertSame($intake->name, $row['semester']);
        }
    }

    public function test_the_intake_combines_with_the_status_filter(): void
    {
        $intake = $this->busiestIntake();
        $expected = Applicant::whereNotNull('submission_date')
            ->whereIn('status_id', [3])
            ->whereHas('course', fn ($q) => $q->where('semester_id', $intake->id))
            ->count();

        $response = $this->actingAs($this->staff())->getJson(route('admission.list', ['intake' => $intake->id, 'statuses' => [3], 'size' => 5, 'page' => 1]));

        $response->assertOk();
        $this->assertSame($expected, (int) $response->json('total_rows'));
    }
}
