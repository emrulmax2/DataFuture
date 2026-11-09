<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Accepting an application (status 5) places the offer straight away: Accepted
 * keeps its own history row and offer letter, then the status moves on to Offer
 * Placed (6) and the DocuSeal offer acceptance form is sent.
 */
class AdmissionAcceptPlacesOfferTest extends TestCase
{
    use DatabaseTransactions;

    private const API = 'https://docuseal.test/api';

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.docuseal.api_key' => 'test-key',
            'services.docuseal.offer_acceptance_template_id' => 6568085,
            'services.docuseal.api_url' => self::API,
        ]);
        Queue::fake();           // the offer letter email (UserMailerJob)
        Storage::fake('local');  // the generated letter PDF
        Storage::fake('s3');
    }

    private function docuseal(bool $up = true): void
    {
        Http::fake([self::API.'/submissions' => $up
            ? Http::response([
                ['role' => 'London Churchill College', 'submission_id' => 990001, 'email' => 'admission@lcc.ac.uk'],
                ['role' => 'Applicant', 'submission_id' => 990001, 'email' => 'applicant@example.com', 'embed_src' => 'https://docuseal.test/s/abc'],
            ], 200)
            : Http::response(['error' => 'down'], 500)]);
    }

    private function awaitingDecision(bool $withProof = true): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereHas('contact')->orderByDesc('id')->firstOrFail();
        DB::table('applicants')->where('id', $applicant->id)->update(['status_id' => 4, 'application_rejected_reason_id' => null]);
        DB::table('applicant_proof_of_ids')->where('applicant_id', $applicant->id)->delete();       // rolled back after the test
        DB::table('applicant_fee_eligibilities')->where('applicant_id', $applicant->id)->delete();
        if ($withProof) {
            DB::table('applicant_proof_of_ids')->insert(['applicant_id' => $applicant->id, 'proof_type' => 'passport', 'proof_id' => 'X1234567', 'proof_expiredate' => '01-01-2031', 'created_by' => 7, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('applicant_fee_eligibilities')->insert(['applicant_id' => $applicant->id, 'fee_eligibility_id' => 2, 'created_by' => 7, 'created_at' => now(), 'updated_at' => now()]);
        }

        return $applicant->fresh();
    }

    private function change(Applicant $a, int $to, array $extra = [])
    {
        return $this->actingAs(User::find(7))->postJson(route('admission.student.update.status'), ['applicantID' => $a->id, 'statusidID' => $to] + $extra);
    }

    private function history(Applicant $a, int $sinceId): array
    {
        return DB::table('applicant_archives')->where('applicant_id', $a->id)->where('field_name', 'status_id')->where('id', '>', $sinceId)
            ->orderBy('id')->get()->map(fn ($r) => $r->field_value.'→'.$r->field_new_value)->all();
    }

    public function test_accepting_records_accepted_then_places_the_offer_and_sends_the_docuseal_form(): void
    {
        $this->docuseal();
        $a = $this->awaitingDecision();
        $since = (int) DB::table('applicant_archives')->max('id');
        $letters = DB::table('applicant_letters')->where('applicant_id', $a->id)->where('letter_set_id', 153)->count();

        $this->change($a, 5)->assertOk()->assertJson(['status' => 'Offer Placed']);

        $this->assertSame(6, (int) $a->fresh()->status_id);
        $this->assertSame(['4→5', '5→6'], $this->history($a, $since), 'Accepted is kept in the history, then Offer Placed');
        $this->assertSame($letters + 1, DB::table('applicant_letters')->where('applicant_id', $a->id)->where('letter_set_id', 153)->count(), 'the Accepted offer letter still goes out');
        $this->assertDatabaseHas('applicant_docuseal_submissions', ['applicant_id' => $a->id, 'submission_id' => 990001]);
        Http::assertSent(fn ($req) => $req->url() === self::API.'/submissions' && $req['template_id'] == 6568085);
    }

    public function test_the_offer_is_still_placed_when_docuseal_is_down(): void
    {
        $this->docuseal(false);
        $a = $this->awaitingDecision();
        $since = (int) DB::table('applicant_archives')->max('id');

        $this->change($a, 5)->assertOk();

        $this->assertSame(6, (int) $a->fresh()->status_id);
        $this->assertSame(['4→5', '5→6'], $this->history($a, $since));
        $this->assertDatabaseMissing('applicant_docuseal_submissions', ['applicant_id' => $a->id, 'submission_id' => 990001]);
    }

    public function test_accepting_is_refused_until_proof_of_id_and_fee_eligibility_are_recorded(): void
    {
        $this->docuseal();
        $a = $this->awaitingDecision(false);
        $since = (int) DB::table('applicant_archives')->max('id');
        $letters = DB::table('applicant_letters')->where('applicant_id', $a->id)->count();

        $this->change($a, 5)->assertStatus(422)
            ->assertJson(['missing' => ['Proof of ID type', 'ID number', 'ID expiry date', 'Fee eligibility']]);

        $this->assertSame(4, (int) $a->fresh()->status_id, 'nothing changes');
        $this->assertSame([], $this->history($a, $since));
        $this->assertSame($letters, DB::table('applicant_letters')->where('applicant_id', $a->id)->count(), 'no offer letter');
        Http::assertNothingSent();

        // the dialog says so and keeps Continue disabled for Accepted
        $html = $this->actingAs(User::find(7))->get(route('admission.show', $a->id))->assertOk()->getContent();
        $this->assertStringContainsString('data-adp-offer-block', $html);
        $this->assertStringContainsString('Missing: <b>Proof of ID type, ID number, ID expiry date, Fee eligibility</b>', $html);
        $this->assertMatchesRegularExpression('/<option value="5"[^>]*data-needs-proof="1"/', $html);

        // filled in through the Proof of ID editor, fee eligibility included → accepted
        $this->actingAs(User::find(7))->postJson(route('admission.update.proof.id'), [
            'applicant_id' => $a->id, 'proof_type' => 'passport', 'proof_id' => 'X1234567', 'proof_expiredate' => '01-01-2031', 'fee_eligibility_id' => 2,
        ])->assertOk();
        $this->assertSame(2, (int) DB::table('applicant_fee_eligibilities')->where('applicant_id', $a->id)->value('fee_eligibility_id'));
        $this->assertStringNotContainsString('data-adp-offer-block', $this->actingAs(User::find(7))->get(route('admission.show', $a->id))->getContent());
        $this->change($a, 5)->assertOk();
        $this->assertSame(6, (int) $a->fresh()->status_id);
    }

    public function test_offer_placed_from_accepted_is_refused_without_proof_too(): void
    {
        $this->docuseal();
        $a = $this->awaitingDecision(false);
        DB::table('applicants')->where('id', $a->id)->update(['status_id' => 5]);

        $this->change($a, 6)->assertStatus(422);
        $this->assertSame(5, (int) $a->fresh()->status_id);
        Http::assertNothingSent();
    }

    public function test_other_changes_record_the_real_previous_status(): void
    {
        $this->docuseal();
        $a = $this->awaitingDecision();
        $since = (int) DB::table('applicant_archives')->max('id');
        $reason = DB::table('application_rejected_reasons')->value('id');

        $this->change($a, 8, ['rejectedReason' => $reason])->assertOk();

        $this->assertSame(8, (int) $a->fresh()->status_id);
        $this->assertSame(['4→8'], $this->history($a, $since), 'was "8 → 8" before: the old value was read after saving');
        $this->assertSame([''.'→'.$reason], DB::table('applicant_archives')->where('applicant_id', $a->id)->where('field_name', 'application_rejected_reason_id')->where('id', '>', $since)->get()->map(fn ($r) => $r->field_value.'→'.$r->field_new_value)->all());
    }
}
