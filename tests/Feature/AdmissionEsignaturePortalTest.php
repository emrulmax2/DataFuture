<?php

namespace Tests\Feature;

use App\Enums\EsignEventType;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The E-signature tab on the admission portal shell: the overview (empty state
 * or request summary) and the sign request page (signers, details, audit trail).
 */
class AdmissionEsignaturePortalTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    /** A submitted applicant with no e-signature request at all. */
    private function freshApplicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')
            ->whereNotIn('id', $requested)
            ->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant, 'An applicant without an e-signature request is needed.');

        return $applicant;
    }

    public function test_the_overview_shows_the_empty_state_with_send_request_when_nothing_was_sent(): void
    {
        $applicant = $this->freshApplicant();

        $response = $this->actingAs($this->staff())->get(route('admission.show.e.signature', $applicant->id));

        $response->assertOk()
            ->assertSee('data-adp-esign-overview', false)
            ->assertSee('No request sent.')
            ->assertSee('id="sendEsignBtn"', false)
            ->assertSee('id="sendOfferAcceptanceModal"', false)
            ->assertSee('id="changeStatusModal"', false)
            ->assertDontSee('View request')
            ->assertDontSee('Simulate signing')
            ->assertDontSee('adm-sign-summary');

        // Without a request the sign request page sends you back to the overview.
        $this->actingAs($this->staff())->get(route('admission.show.e.signature.request', $applicant->id))
            ->assertRedirect(route('admission.show.e.signature', $applicant->id));
    }

    public function test_a_sent_request_shows_the_summary_with_view_and_resend_and_its_detail_page(): void
    {
        $applicant = $this->freshApplicant();
        $staff = $this->staff();
        $admin = AdminESignature::create(['user_id' => $staff->id, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'ip_address' => '79.171.153.100', 'device' => 'Mozilla/5.0', 'browser' => 'Chrome', 'os' => 'Mac', 'via_email' => 1, 'via_sms' => 1]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'user', 'event_type' => EsignEventType::SIGN_REQUEST_CREATED->value, 'event_description' => 'London Churchill College (info@lcc.ac.uk) initialized a sign request with the document', 'ip_address' => '79.171.153.100', 'browser' => 'Chrome', 'os' => 'Mac']);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::EMAIL_SENT->value, 'event_description' => $applicant->users->email.' was notified by email', 'extra_field' => ['opened' => false]]);

        $overview = $this->actingAs($staff)->get(route('admission.show.e.signature', $applicant->id));
        $overview->assertOk()
            ->assertSee('Awaiting signature')
            ->assertSee('View request')
            ->assertSee('Resend')
            ->assertSee('via email + SMS')
            ->assertSee(route('admission.show.e.signature.request', $applicant->id), false)
            ->assertDontSee('No request sent.')
            ->assertDontSee('downloadEsignBtn');

        $detail = $this->actingAs($staff)->get(route('admission.show.e.signature.request', $applicant->id));
        $detail->assertOk()
            ->assertSee('E-signature overview')
            ->assertSee('Sign request')
            ->assertSee('Requested')
            ->assertSee('Not signed yet.')
            ->assertSee('Awaiting this signer.')
            ->assertSee('Signer #1')
            ->assertSee('Signer #2')
            ->assertSee('IP 79.171.153.100')
            ->assertSee('Sign request created')
            ->assertSee('Email sent')
            ->assertSee('ESIG-'.$applicant->id)
            ->assertSee('Pending')
            ->assertDontSee('Tamper-proof')
            ->assertDontSee('adm-sign-summary');

        // Opening the request is a staff view, recorded once.
        $this->assertSame(1, ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('user_type', 'user')->where('event_type', EsignEventType::VIEWED->value)->count());
        $this->actingAs($staff)->get(route('admission.show.e.signature.request', $applicant->id))->assertOk();
        $this->assertSame(1, ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('user_type', 'user')->where('event_type', EsignEventType::VIEWED->value)->count());
        // The two seeded events plus the single staff view.
        $this->assertSame(3, ApplicantESignatureEvent::where('applicant_id', $applicant->id)->count());
    }

    public function test_a_signed_request_shows_finalised_with_download_and_tamper_proof(): void
    {
        $applicant = $this->freshApplicant();
        $staff = $this->staff();
        AdminESignature::create(['user_id' => $staff->id, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'via_email' => 1, 'via_sms' => 0]);
        $esign = ApplicantESignature::create(['applicant_id' => $applicant->id, 'ip_address' => '104.28.40.91', 'device' => 'Mozilla/5.0', 'browser' => 'Safari', 'os' => 'Mac', 'latitude' => '51.542305', 'longitude' => '0.032443', 'viewed_via' => 'email', 'status' => 'accepted', 'video_consent' => 1, 'declaration' => 1, 'signature' => 'storage/applicants/'.$applicant->id.'/signature_test.png', 'signed_date' => '2026-10-21']);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::FINALIZED->value, 'event_description' => $applicant->users->email.' finalized the sign request for the document']);

        $overview = $this->actingAs($staff)->get(route('admission.show.e.signature', $applicant->id));
        $overview->assertOk()
            ->assertSee('Finalised')
            ->assertSee('id="downloadEsignBtn"', false)
            ->assertSee('ESIG-'.$applicant->id.'-'.$esign->id)
            ->assertDontSee('Resend');

        $detail = $this->actingAs($staff)->get(route('admission.show.e.signature.request', $applicant->id));
        $detail->assertOk()
            ->assertSee('View signature')
            ->assertSee('Tamper-proof')
            ->assertSee('All signers complete')
            ->assertSee('Download signed PDF')
            ->assertSee('signature_test.png', false)
            ->assertSee('IP 104.28.40.91')
            ->assertDontSee('Awaiting this signer.');
    }
}
