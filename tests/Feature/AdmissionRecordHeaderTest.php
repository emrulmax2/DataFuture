<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantDocusealSubmission;
use App\Models\Student;
use App\Models\StudentConversionLog;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The record header around the DocuSeal offer: no Change status at Offer Placed (the
 * signing moves it), Offer Placed again from Offer Rejected, and View Student at
 * Offer Accepted. Rendered on the uploads page; every record page shares the header.
 */
class AdmissionRecordHeaderTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.docuseal.auto_convert' => true]);
    }

    private function staff(): User
    {
        return User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
    }

    private function applicantAt(int $status): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->orderByDesc('id')->firstOrFail();
        $applicant->status_id = $status;
        $applicant->save();

        return $applicant;
    }

    private function page(Applicant $applicant): string
    {
        return $this->actingAs($this->staff())->get(route('admission.uploads', $applicant->id))->assertOk()->getContent();
    }

    public function test_offer_placed_has_no_change_status_while_docuseal_decides(): void
    {
        $applicant = $this->applicantAt(6);
        app(\App\Services\DocuSeal\OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 888001], 1);

        $html = $this->page($applicant);

        $this->assertStringNotContainsString('data-adp-status-open', $html);
        $this->assertStringNotContainsString('data-adp-status-anchor=', $html);
        $this->assertStringContainsString('Waiting for the applicant to sign', $html);
        $this->assertStringNotContainsString('data-adp-view-student', $html);
    }

    public function test_offer_placed_offers_offer_accepted_when_the_signed_form_was_held(): void
    {
        $applicant = $this->applicantAt(6);
        $row = app(\App\Services\DocuSeal\OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 888002], 1);
        $row->update(['state' => 'stored', 'docuseal_status' => 'completed', 'outcome' => ApplicantDocusealSubmission::OUTCOME_HELD, 'completed_at' => now()]);

        $html = $this->page($applicant);

        $this->assertStringContainsString('data-adp-status-open', $html);
        $this->assertStringContainsString('data-adp-status-anchor="7"', $html);
        $this->assertStringNotContainsString('data-adp-status-anchor="9"', $html);
        $this->assertStringContainsString('Signed — proof of ID needed', $html);
    }

    public function test_offer_placed_keeps_manual_accept_and_reject_when_the_automatic_move_is_off(): void
    {
        config(['services.docuseal.auto_convert' => false]);
        $html = $this->page($this->applicantAt(6));

        $this->assertStringContainsString('data-adp-status-anchor="7"', $html);
        $this->assertStringContainsString('data-adp-status-anchor="9"', $html);
    }

    public function test_offer_rejected_can_be_placed_again_which_sends_a_new_form(): void
    {
        $applicant = $this->applicantAt(9);
        $row = app(\App\Services\DocuSeal\OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 888003], 1);
        $row->update(['state' => 'declined', 'docuseal_status' => 'declined', 'outcome' => 'rejected', 'decline_reason' => 'Next semester']);

        $html = $this->page($applicant);
        $this->assertStringContainsString('data-adp-status-open', $html);
        $this->assertStringContainsString('data-adp-status-anchor="6"', $html);
        $this->assertSame(1, substr_count($html, 'data-adp-status-anchor='));
        $this->assertStringContainsString('Offer rejected', $html);
        $this->assertStringContainsString('Next semester', $html);

        // Change status › Offer Placed: the status moves and a new DocuSeal form goes out
        // (it needs the proof of ID + fee eligibility on record, AdmissionStatusFlow::missingForOffer)
        \App\Models\ApplicantProofOfId::create(['applicant_id' => $applicant->id, 'proof_type' => 'passport', 'proof_id' => 'X1234567', 'proof_expiredate' => '01-01-2031', 'created_by' => 7]);
        \App\Models\ApplicantFeeEligibility::create(['applicant_id' => $applicant->id, 'fee_eligibility_id' => 2, 'created_by' => 7]);
        config(['services.docuseal.api_key' => 'test-key', 'services.docuseal.offer_acceptance_template_id' => 999, 'services.docuseal.api_url' => 'https://api.docuseal.co']);
        Http::preventStrayRequests();
        Http::fake(['https://api.docuseal.co/submissions' => Http::response([
            ['id' => 1, 'submission_id' => 888004, 'role' => 'London Churchill College', 'email' => 'admission@lcc.ac.uk', 'status' => 'completed'],
            ['id' => 2, 'submission_id' => 888004, 'role' => 'Applicant', 'email' => 'test@example.com', 'status' => 'sent', 'embed_src' => 'https://docuseal.com/s/new'],
        ], 200)]);
        $this->actingAs($this->staff())->postJson(route('admission.student.update.status'), ['applicantID' => $applicant->id, 'statusidID' => 6])->assertOk();
        $this->assertSame(6, (int) $applicant->fresh()->status_id);
        $this->assertTrue(ApplicantDocusealSubmission::where('applicant_id', $applicant->id)->where('submission_id', 888004)->exists());
    }

    public function test_offer_accepted_shows_view_student_linked_to_the_student_record(): void
    {
        $log = StudentConversionLog::whereNotNull('student_id')->orderByDesc('id')->firstOrFail();
        $applicant = Applicant::findOrFail($log->applicant_id);
        $applicant->status_id = 7;
        $applicant->save();
        $student = Student::findOrFail($log->student_id);

        $html = $this->page($applicant);

        $this->assertStringContainsString('data-adp-view-student', $html);
        $this->assertStringContainsString('href="'.route('student.show', $student->id).'"', $html);
        $this->assertStringContainsString('View Student', $html);
        $this->assertStringContainsString((string) $student->registration_no, $html);
        $this->assertStringNotContainsString('data-adp-status-open', $html);
    }

    public function test_view_student_needs_the_live_student_dashboard_privilege(): void
    {
        config(['privileges.source' => 'new']);
        $log = StudentConversionLog::whereNotNull('student_id')->orderByDesc('id')->firstOrFail();
        $applicant = Applicant::findOrFail($log->applicant_id);
        $applicant->status_id = 7;
        $applicant->save();
        $link = 'href="'.route('student.show', $log->student_id).'"';

        // an ordinary member of staff (not a super admin), with the toggle set each way
        $userId = \App\Models\EmployeePermission::query()->distinct()->pluck('user_id')
            ->first(fn ($id) => ($u = User::find($id)) && !$u->isSuperAdmin());
        $this->assertNotNull($userId, 'needs a non-super-admin with employee permissions');
        $set = function (string $value) use ($userId) {
            \App\Models\EmployeePermission::updateOrCreate(['user_id' => $userId, 'key' => 'dashboard_live_student'], ['value' => $value]);
            return User::find($userId); // fresh instance: priv() is cached per object
        };
        $view = fn (User $u) => $this->actingAs($u)->get(route('admission.uploads', $applicant->id))->assertOk()->getContent();

        $html = $view($set('1'));
        $this->assertStringContainsString($link, $html);

        $html = $view($set('0'));
        $this->assertStringContainsString('data-adp-view-student', $html, 'the button stays');
        $this->assertStringNotContainsString($link, $html);
        $this->assertMatchesRegularExpression('/<a href="#" class="adp-btn adp-btn--primary" data-adp-view-student aria-disabled="true"/', $html);
    }

    public function test_offer_accepted_without_a_student_points_to_the_conversion_log(): void
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')
            ->whereNotIn('id', StudentConversionLog::select('applicant_id'))->orderByDesc('id')->get()
            ->first(fn ($a) => \App\Support\AdmissionStatusFlow::studentFor($a) === null);
        $this->assertNotNull($applicant);
        $applicant->status_id = 7;
        $applicant->save();

        $html = $this->page($applicant);

        $this->assertStringNotContainsString('data-adp-view-student', $html);
        $this->assertStringContainsString('Student record not created yet', $html);
        $this->assertStringContainsString(route('admission.conversion.log', $applicant->id), $html);
    }
}
