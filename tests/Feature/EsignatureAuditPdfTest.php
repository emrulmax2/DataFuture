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
 * The signature audit certificate PDF (applicant.e.signature.download).
 */
class EsignatureAuditPdfTest extends TestCase
{
    use DatabaseTransactions;

    private function applicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereNotIn('id', $requested)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        return $applicant;
    }

    public function test_the_certificate_renders_as_a_pdf_for_a_signed_request(): void
    {
        $applicant = $this->applicant();
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        AdminESignature::create(['user_id' => $staff->id, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'ip_address' => '79.171.153.100', 'device' => 'Mozilla/5.0', 'browser' => 'Chrome', 'os' => 'Windows', 'latitude' => '51.541841', 'longitude' => '0.032823', 'via_email' => 1, 'via_sms' => 1]);
        ApplicantESignature::create(['applicant_id' => $applicant->id, 'ip_address' => '31.94.64.59', 'device' => 'Mozilla/5.0', 'browser' => 'Safari', 'os' => 'Mac', 'latitude' => '51.542305', 'longitude' => '0.032443', 'viewed_via' => 'email', 'status' => 'accepted', 'video_consent' => 1, 'declaration' => 1, 'signature' => 'storage/signatures/missing_for_test.png', 'signed_date' => now()]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'user', 'event_type' => EsignEventType::SIGN_REQUEST_CREATED->value, 'event_description' => 'London Churchill College (info@lcc.ac.uk) initialized a sign request with the document', 'ip_address' => '79.171.153.100', 'browser' => 'Chrome', 'os' => 'Windows', 'extra_field' => ['trigger' => 'applicant_submission', 'by_type' => 'applicant', 'by_email' => $applicant->users->email]]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::EMAIL_SENT->value, 'event_description' => $applicant->users->email.' was notified by email', 'extra_field' => ['opened' => true]]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::EMAIL_READ->value, 'event_description' => $applicant->users->email.' opened the notificaiton email']);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::LOCATION_VERIFIED->value, 'event_description' => $applicant->users->email."'s location was verified", 'ip_address' => '31.94.64.59', 'browser' => 'Safari', 'os' => 'Mac', 'latitude' => '51.542305', 'longitude' => '0.032443']);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::FINALIZED->value, 'event_description' => $applicant->users->email.' finalized the sign request for the document']);

        $response = $this->get(route('applicant.e.signature.download', $applicant->id));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('audit-'.$applicant->application_no.'.pdf', (string) $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
        // Two pages: the certificate and the audit trail.
        $this->assertGreaterThanOrEqual(2, preg_match_all('/\/Type\s*\/Page[^s]/', $response->getContent()));
    }

    public function test_the_certificate_also_renders_while_the_signature_is_awaited(): void
    {
        $applicant = $this->applicant();
        AdminESignature::create(['user_id' => 1, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'device' => 'Task Automation', 'browser' => 'System', 'os' => 'System', 'via_email' => 1, 'via_sms' => 0]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'user', 'event_type' => EsignEventType::SIGN_REQUEST_CREATED->value, 'event_description' => 'London Churchill College (info@lcc.ac.uk) initialized a sign request with the document', 'browser' => 'System', 'os' => 'System']);

        $response = $this->get(route('applicant.e.signature.download', $applicant->id));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
