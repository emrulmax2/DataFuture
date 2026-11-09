<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Change application status dialog on the admission portal: Offer Accepted
 * collects proof of ID + fee eligibility in the dialog (pre-filled), the
 * confirm step summarises them, and the legacy field area is gone.
 */
class AdmissionStatusDialogTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        return User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
    }

    private function offerPlaced(): Applicant
    {
        $applicant = Applicant::where('status_id', 6)->whereNotNull('submission_date')->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant, 'needs an applicant at Offer Placed');

        return $applicant;
    }

    public function test_offer_accepted_fields_sit_in_the_change_status_dialog_prefilled(): void
    {
        // the manual Accept / Reject from Offer Placed exists while DocuSeal does not move it (AdmissionRecordHeaderTest)
        config(['services.docuseal.auto_convert' => false]);
        $applicant = $this->offerPlaced();

        $html = $this->actingAs($this->staff())->get(route('admission.show', $applicant->id))->assertOk()->getContent();

        // Offer Accepted / Offer Rejected are offered from Offer Placed.
        $this->assertStringContainsString('data-adp-status-anchor="7"', $html);
        $this->assertStringContainsString('data-adp-status-anchor="9"', $html);

        // One set of the four controls admission-global.js posts, inside #changeStatusModal (before the confirm modal).
        foreach (['sts_proof_type', 'sts_proof_id', 'sts_proof_expiredate', 'sts_fee_eligibility_id'] as $id) {
            $this->assertSame(1, substr_count($html, 'id="'.$id.'"'), $id);
            $this->assertGreaterThan(strpos($html, 'id="changeStatusModal"'), strpos($html, 'id="'.$id.'"'), $id);
            $this->assertLessThan(strpos($html, 'id="statusConfirmModal"'), strpos($html, 'id="'.$id.'"'), $id);
        }
        $this->assertStringContainsString('data-adp-proof hidden', $html);
        $this->assertStringContainsString('data-adp-proof-summary hidden', $html);
        $this->assertStringNotContainsString('offerAcceptedErrorArea', $html);

        // Pre-filled from the record (or the legacy "not assessed" default for the fee).
        if ($applicant->proof) {
            $this->assertMatchesRegularExpression('/<option selected value="'.preg_quote($applicant->proof->proof_type, '/').'">/', $html);
            $this->assertStringContainsString('value="'.e($applicant->proof->proof_id).'" placeholder="e.g. 512345678" id="sts_proof_id"', $html);
            $this->assertStringContainsString('value="'.$applicant->proof->proof_expiredate.'" placeholder="DD-MM-YYYY" id="sts_proof_expiredate"', $html);
        }
        $feeId = $applicant->feeeligibility->fee_eligibility_id ?? 3;
        $this->assertMatchesRegularExpression('/<option selected value="'.$feeId.'">/', $html);
    }

    public function test_every_record_page_renders_the_dialog_fields_once(): void
    {
        $applicant = $this->offerPlaced();

        foreach (['admission.show.e.signature', 'admission.notes', 'admission.process'] as $route) {
            $html = $this->actingAs($this->staff())->get(route($route, $applicant->id))->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, 'id="sts_proof_type"'), $route);
            $this->assertSame(1, substr_count($html, 'data-adp-proof-summary'), $route);
        }
    }
}
