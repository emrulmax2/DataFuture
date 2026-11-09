<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantProofOfId;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The applicant details page saves proof of ID through its own dialog
 * (admission.update.proof.id) instead of the personal-details form.
 */
class AdmissionProofOfIdUpdateTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    private function applicant(): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant, 'A submitted applicant is needed for this test.');

        return $applicant;
    }

    public function test_it_saves_the_proof_of_id_row(): void
    {
        $applicant = $this->applicant();
        $existing = ApplicantProofOfId::where('applicant_id', $applicant->id)->first();

        $response = $this->actingAs($this->staff())->postJson(route('admission.update.proof.id'), [
            'applicant_id' => $applicant->id,
            'applicant_proof_of_id' => $existing ? $existing->id : 0,
            'proof_type' => 'passport',
            'proof_id' => 'TEST-512345678',
            'proof_expiredate' => '01-01-2031',
        ]);

        $response->assertOk()->assertJsonStructure(['message']);

        $row = ApplicantProofOfId::where('applicant_id', $applicant->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($row);
        $this->assertSame('passport', $row->proof_type);
        $this->assertSame('TEST-512345678', $row->proof_id);
    }

    public function test_it_rejects_a_badly_formatted_expiry_date(): void
    {
        $applicant = $this->applicant();

        $this->actingAs($this->staff())->postJson(route('admission.update.proof.id'), [
            'applicant_id' => $applicant->id,
            'proof_type' => 'passport',
            'proof_id' => '1',
            'proof_expiredate' => '2031-01-01',
        ])->assertStatus(422)->assertJsonValidationErrors(['proof_expiredate']);
    }

    public function test_it_needs_a_signed_in_user(): void
    {
        // The admission routes sit behind the web auth middleware, which sends guests to the login page.
        $this->postJson(route('admission.update.proof.id'), ['applicant_id' => 1])->assertStatus(302);
    }
}
