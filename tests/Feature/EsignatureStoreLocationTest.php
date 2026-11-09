<?php

namespace Tests\Feature;

use App\Enums\EsignEventType;
use App\Jobs\UserMailerJob;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Signing needs the device's position: the server refuses a signature
 * without coordinates and only logs "location was verified" for a real fix.
 */
class EsignatureStoreLocationTest extends TestCase
{
    use DatabaseTransactions;

    private const PNG = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private function applicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereNotIn('id', $requested)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        AdminESignature::create(['user_id' => $staff->id, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'via_email' => 1, 'via_sms' => 0]);
        ApplicantESignature::create(['applicant_id' => $applicant->id, 'ip_address' => '127.0.0.1', 'device' => 'test', 'browser' => 'Chrome', 'os' => 'Mac', 'viewed_via' => 'email', 'status' => 'pending']);

        return $applicant;
    }

    private function payload(Applicant $applicant, array $extra = []): array
    {
        return array_merge([
            'applicant_id' => base64_encode($applicant->id),
            'video_consent' => '1',
            'declaration' => '1',
            'signature' => self::PNG,
        ], $extra);
    }

    public function test_a_signature_without_coordinates_is_refused(): void
    {
        Storage::fake('public');
        Bus::fake();
        $applicant = $this->applicant();

        $response = $this->postJson(route('applicant.e.signature.store', base64_encode($applicant->id)), $this->payload($applicant));

        $response->assertStatus(422)->assertJsonValidationErrors(['latitude', 'longitude']);
        $this->assertSame('pending', ApplicantESignature::where('applicant_id', $applicant->id)->value('status'));
        $this->assertSame(0, ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::LOCATION_VERIFIED->value)->count());
        Bus::assertNotDispatched(UserMailerJob::class);
    }

    public function test_a_signature_with_coordinates_is_accepted_and_the_location_logged(): void
    {
        Storage::fake('public');
        Bus::fake();
        $applicant = $this->applicant();

        $response = $this->postJson(route('applicant.e.signature.store', base64_encode($applicant->id)), $this->payload($applicant, ['latitude' => '51.542305', 'longitude' => '0.032443']));

        $response->assertOk();
        $row = ApplicantESignature::where('applicant_id', $applicant->id)->first();
        $this->assertSame('accepted', $row->status);
        $this->assertNotEmpty($row->signature);
        $location = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::LOCATION_VERIFIED->value)->first();
        $this->assertNotNull($location);
        $this->assertEqualsWithDelta(51.542305, (float) $location->latitude, 0.00001);
        $this->assertSame(1, ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::FINALIZED->value)->count());
        Bus::assertDispatched(UserMailerJob::class);
    }
}
