<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * /applicant/application/show/{id} (user, 29 Oct 2026): the applicant's own application read back
 * like the form's review step, and nobody else's.
 */
class ApplicantApplicationShowTest extends TestCase
{
    use DatabaseTransactions;

    private function submitted(): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereNotNull('applicant_user_id')->whereNull('deleted_at')->orderByDesc('id')->first();
        if (!$applicant) {
            $this->markTestSkipped('No submitted application in this database.');
        }

        return $applicant;
    }

    public function test_the_owner_sees_the_review_sections_on_the_portal_shell(): void
    {
        $applicant = $this->submitted();

        $this->actingAs(ApplicantUser::find($applicant->applicant_user_id), 'applicant')
            ->get(route('applicant.application.show', $applicant->id))
            ->assertOk()
            ->assertSee('apd-shell', false)
            ->assertSee('Your application')
            ->assertSee('Ref '.($applicant->application_no ?: $applicant->id))
            ->assertSee('Personal details')
            ->assertSee('Course &amp; funding', false)
            ->assertSee('Residency &amp; convictions', false)
            ->assertSee('You agreed to the declaration when you applied on');
    }

    public function test_another_applicants_application_is_not_found(): void
    {
        $applicant = $this->submitted();
        $other = ApplicantUser::where('id', '!=', $applicant->applicant_user_id)->orderByDesc('id')->firstOrFail();

        $this->actingAs($other, 'applicant')
            ->get(route('applicant.application.show', $applicant->id))
            ->assertNotFound();
    }

    public function test_a_guest_is_sent_to_sign_in(): void
    {
        $applicant = $this->submitted();

        $this->get(route('applicant.application.show', $applicant->id))->assertRedirect();
    }
}
