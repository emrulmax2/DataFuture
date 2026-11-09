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
 * Every opening of the applicant's e-signature link is recorded (refreshes
 * from the same device within ten minutes collapse into one), and the staff
 * pages report the count.
 */
class EsignatureViewHistoryTest extends TestCase
{
    use DatabaseTransactions;

    private function applicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereNotIn('id', $requested)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);
        AdminESignature::create(['user_id' => 1, 'applicant_id' => $applicant->id, 'smtp_email' => 'info@lcc.ac.uk', 'via_email' => 1, 'via_sms' => 1]);

        return $applicant;
    }

    private function views(Applicant $applicant)
    {
        return ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('user_type', 'applicant')->where('event_type', EsignEventType::VIEWED->value)->orderBy('id')->get();
    }

    public function test_each_distinct_visit_is_recorded_and_refreshes_collapse(): void
    {
        $applicant = $this->applicant();
        $emailLink = route('applicant.e.signature', ['hashedId' => urlencode(base64_encode($applicant->id)).'e']);
        $smsLink = route('applicant.e.signature', ['hashedId' => urlencode(base64_encode($applicant->id)).'s']);
        // Laravel's get() takes no server variables; the IP and browser go in this way.
        $visit = fn (string $url, string $ip, string $agent) => $this->withServerVariables(['REMOTE_ADDR' => $ip])->withHeaders(['User-Agent' => $agent])->get($url);
        $chromeIp = '86.12.34.56';
        $chromeUa = 'Mozilla/5.0 (Windows NT 10.0) Chrome/155.0 Safari/537.36';

        $visit($emailLink, $chromeIp, $chromeUa)->assertOk();
        $visit($emailLink, $chromeIp, $chromeUa)->assertOk();
        $this->assertCount(1, $this->views($applicant), 'A refresh within ten minutes is the same visit.');
        $first = $this->views($applicant)->first();
        $this->assertStringContainsString('viewed the document via the email link', $first->event_description);
        $this->assertSame('email', $first->extra_field['via']);
        $this->assertFalse($first->extra_field['repeat']);

        // Later, from the same device: a new visit.
        ApplicantESignatureEvent::where('id', $first->id)->update(['created_at' => now()->subMinutes(30)]);
        $visit($emailLink, $chromeIp, $chromeUa)->assertOk();
        $this->assertCount(2, $this->views($applicant));
        $this->assertTrue($this->views($applicant)->last()->extra_field['repeat']);

        // Straight away from another device, through the SMS link: a new visit too.
        $visit($smsLink, '104.28.40.91', 'Mozilla/5.0 (iPhone) Safari/605.1')->assertOk();
        $views = $this->views($applicant);
        $this->assertCount(3, $views);
        $this->assertSame('sms', $views->last()->extra_field['via']);
        $this->assertSame('104.28.40.91', $views->last()->ip_address);

        // The first opening stays on the signature row.
        $row = ApplicantESignature::where('applicant_id', $applicant->id)->first();
        $this->assertSame('email', $row->viewed_via);
        $this->assertSame('86.12.34.56', $row->ip_address);

        // Staff pages report the count.
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->actingAs($staff)->get(route('admission.show.e.signature', $applicant->id))->assertOk()->assertSee('opened 3 times');
        $this->actingAs($staff)->get(route('admission.show.e.signature.request', $applicant->id))->assertOk()->assertSee('Opened 3 times')->assertSee('via the sms link');
    }
}
