<?php

namespace Tests\Feature;

use App\Enums\EsignEventType;
use App\Jobs\StoreSignedApplicationDocuments;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\DocumentSettings;
use App\Models\User;
use App\Services\Admission\SignedApplicationDocuments;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * When the applicant submits the e-signature, the signed application form and the
 * audit trail PDFs land in the applicant's Uploads under "Other" (user, 27 Oct 2026).
 */
class SignedApplicationDocumentsTest extends TestCase
{
    use DatabaseTransactions;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('s3');
        Storage::fake('public');
    }

    /** A submitted applicant with no e-signature yet, given a pending request. */
    private function applicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereNull('deleted_at')->whereHas('users')->whereHas('course')
            ->whereNotIn('id', $requested)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        AdminESignature::create(['user_id' => $staff->id, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'via_email' => 1, 'via_sms' => 0]);
        // no coordinates on the request, so the audit uses the default map (no Google call)
        ApplicantESignature::create(['applicant_id' => $applicant->id, 'ip_address' => '127.0.0.1', 'device' => 'test', 'browser' => 'Chrome', 'os' => 'Mac', 'viewed_via' => 'email', 'status' => 'pending']);

        return $applicant;
    }

    private function sign(Applicant $applicant): void
    {
        ApplicantESignature::where('applicant_id', $applicant->id)->update(['status' => 'accepted', 'signature' => 'storage/signatures/test.png', 'signed_date' => now()]);
        ApplicantESignatureEvent::create(['applicant_id' => $applicant->id, 'user_type' => 'applicant', 'event_type' => EsignEventType::FINALIZED->value, 'event_description' => 'test finalized']);
    }

    private function stored(Applicant $applicant)
    {
        return ApplicantDocument::where('applicant_id', $applicant->id)->where('created_by', (int) config('tasks.system_user_id', 1))
            ->where('display_file_name', 'like', 'Other - Signed Application Form%')->orderBy('id')->get();
    }

    public function test_submitting_the_e_signature_queues_the_documents(): void
    {
        Bus::fake();
        $applicant = $this->applicant();

        $this->postJson(route('applicant.e.signature.store', base64_encode($applicant->id)), [
            'applicant_id' => base64_encode($applicant->id),
            'video_consent' => '1',
            'declaration' => '1',
            'signature' => self::PNG,
            'latitude' => '51.542305',
            'longitude' => '0.032443',
        ])->assertOk();

        Bus::assertDispatched(StoreSignedApplicationDocuments::class, fn ($job) => $job->applicantId === (int) $applicant->id);
    }

    public function test_the_form_and_the_audit_trail_are_stored_under_other(): void
    {
        $applicant = $this->applicant();
        $this->sign($applicant);

        app()->call([new StoreSignedApplicationDocuments((int) $applicant->id), 'handle']);

        $docs = $this->stored($applicant);
        $this->assertSame(['Other - Signed Application Form', 'Other - Signed Application Form Audit Trail'], $docs->pluck('display_file_name')->all());
        $other = DocumentSettings::where('name', 'Other')->value('id');
        foreach ($docs as $doc) {
            $this->assertSame((int) $other, (int) $doc->document_setting_id);
            $this->assertSame('local', $doc->disk_type); // S3 on the servers
            $this->assertSame('pdf', $doc->doc_type);
            $path = 'public/applicants/'.$applicant->id.'/'.$doc->current_file_name;
            Storage::disk('local')->assertExists($path);
            $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($path));
        }
        $this->assertStringContainsString('Application_of_', $docs[0]->current_file_name);
        $this->assertStringContainsString('audit-', $docs[1]->current_file_name);
    }

    public function test_a_second_run_for_the_same_signature_stores_nothing_more(): void
    {
        $applicant = $this->applicant();
        $this->sign($applicant);
        $service = app(SignedApplicationDocuments::class);

        $service->store((int) $applicant->id);
        $again = $service->store((int) $applicant->id);

        $this->assertSame([], $again['stored']);
        $this->assertCount(2, $this->stored($applicant));
    }

    public function test_nothing_is_stored_before_the_applicant_signs(): void
    {
        $applicant = $this->applicant();

        $result = app(SignedApplicationDocuments::class)->store((int) $applicant->id);

        $this->assertSame([], $result['stored']);
        $this->assertCount(0, $this->stored($applicant));
    }
}
