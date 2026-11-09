<?php

namespace Tests\Feature;

use App\Mail\OfferAcceptanceUpdate;
use App\Mail\OfferDeclinedNotice;
use App\Models\AdmissionNotification;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocusealSubmission;
use App\Models\ApplicantProofOfId;
use App\Models\User;
use App\Services\DocuSeal\OfferAcceptanceDocuments;
use App\Services\DocuSeal\OfferAcceptanceWorkflow;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The Decline button in the DocuSeal offer acceptance email (/offer/decline/{{submitter.slug}}):
 * opening it changes nothing; confirming rejects the offer, withdraws the DocuSeal form and
 * emails admissions + the sender, the applicant and their agent.
 */
class OfferDeclineTest extends TestCase
{
    use DatabaseTransactions;

    private const API = 'https://api.docuseal.co';
    private const PDF = "%PDF-1.7\n% fake signed pdf\n";

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.docuseal.api_key' => 'test-key',
            'services.docuseal.api_url' => self::API,
            'services.docuseal.notify_email' => 'admission@lcc.ac.uk',
            'services.docuseal.auto_convert' => false,
        ]);
        Storage::fake('local');
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
    }

    /** An applicant at Offer Placed with an agent, and its recorded form sent by staff user 7. */
    private function offer(int $submissionId, string $slug, ?Applicant $applicant = null): array
    {
        if (!$applicant) {
            $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereHas('course')
                ->whereNotExists(fn ($q) => $q->from('applicant_docuseal_submissions')->whereColumn('applicant_docuseal_submissions.applicant_id', 'applicants.id'))
                ->orderBy('id', 'desc')->first();
            $this->assertNotNull($applicant);
            $applicant->status_id = OfferAcceptanceWorkflow::OFFER_PLACED;
            $applicant->agent_user_id = AgentUser::whereNotNull('email')->value('id');
            $applicant->save();
            ApplicantProofOfId::create(['applicant_id' => $applicant->id, 'proof_type' => 'passport', 'proof_id' => 'P1234567', 'proof_expiredate' => '31-12-2030', 'created_by' => 1]);
        }
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, [
            'submission_id' => $submissionId, 'template_id' => 6568085, 'applicant_signing_url' => 'https://docuseal.com/s/'.$slug,
        ], 7);

        return [$applicant->fresh(), $row];
    }

    /** DocuSeal: GET answers with the submission in $status, DELETE archives it. */
    private function fakeDocuseal(int $id, string $status): void
    {
        $done = $status === 'completed';
        $submission = [
            'id' => $id, 'status' => $status, 'created_at' => '2026-10-27T10:00:00Z', 'completed_at' => $done ? '2026-10-27T10:30:00Z' : null,
            'submitters' => [
                ['role' => 'London Churchill College', 'status' => 'completed'],
                ['role' => 'Applicant', 'status' => $done ? 'completed' : 'opened'],
            ],
            'documents' => $done ? [['name' => 'Offer acceptance form', 'url' => 'https://docuseal.com/file/signed-'.$id]] : [],
            'audit_log_url' => $done ? 'https://docuseal.com/file/audit-'.$id : null,
        ];
        Http::fake(function ($request) use ($id, $submission) {
            if (str_starts_with($request->url(), 'https://docuseal.com/file/')) {
                return Http::response(self::PDF, 200, ['Content-Type' => 'application/pdf']);
            }
            if ($request->url() === self::API.'/submissions/'.$id) {
                return $request->method() === 'DELETE' ? Http::response(['id' => $id, 'archived_at' => now()->toIso8601String()], 200) : Http::response($submission, 200);
            }

            return Http::response(['error' => 'unexpected'], 500);
        });
    }

    public function test_opening_the_link_only_shows_the_confirmation_page(): void
    {
        [$applicant] = $this->offer(880001, 'dEcLiNeSlUg01');
        Http::fake();

        $this->get(route('offer.decline', 'dEcLiNeSlUg01'))
            ->assertOk()
            ->assertSee('Decline your offer?')
            ->assertSee($applicant->application_no)
            ->assertSee('https://docuseal.com/s/dEcLiNeSlUg01', false)
            // the confirmation modal, opened on load; the decline form lives inside it
            ->assertSee('id="declineModal"', false)
            ->assertSee('Are you sure you want to decline?')
            ->assertSee('No, keep my offer')
            ->assertSee('open(); // on load', false);

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_PLACED, (int) $applicant->fresh()->status_id);
        Http::assertNothingSent();
        Mail::assertNothingSent();
    }

    public function test_confirming_rejects_the_offer_withdraws_the_form_and_emails_every_party(): void
    {
        [$applicant, $row] = $this->offer(880002, 'dEcLiNeSlUg02');
        $this->fakeDocuseal(880002, 'pending');
        $staff = User::find(7)->email;
        $agent = AgentUser::find($applicant->agent_user_id)->email;

        $this->followingRedirects()
            ->post(route('offer.decline.store', 'dEcLiNeSlUg02'), ['reason' => 'I accepted another offer'])
            ->assertOk()
            ->assertSee('Your offer has been declined')
            ->assertSee('We have emailed you a confirmation');

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_REJECTED, (int) $applicant->fresh()->status_id);
        $this->assertTrue(ApplicantArchive::where('applicant_id', $applicant->id)->where('field_name', 'status_id')->where('field_value', 6)->where('field_new_value', 9)->exists());
        $row->refresh();
        $this->assertSame(ApplicantDocusealSubmission::STATE_DECLINED, $row->state);
        $this->assertSame(ApplicantDocusealSubmission::OUTCOME_REJECTED, $row->outcome);
        $this->assertSame('archived', $row->docuseal_status);
        $this->assertSame('I accepted another offer', $row->decline_reason);
        Http::assertSent(fn ($r) => $r->method() === 'DELETE' && $r->url() === self::API.'/submissions/880002');

        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'rejected' && $m->hasTo('admission@lcc.ac.uk') && $m->hasTo($staff)
            && $m->details['via'] === 'email_link' && $m->details['withdrawn'] === true && $m->details['reason'] === 'I accepted another offer');
        Mail::assertSent(OfferDeclinedNotice::class, fn ($m) => $m->audience === 'applicant' && $m->hasTo($applicant->users->email));
        Mail::assertSent(OfferDeclinedNotice::class, fn ($m) => $m->audience === 'agent' && $m->hasTo($agent));
        $this->assertSame(3, AdmissionNotification::where('applicant_id', $applicant->id)->where('type', AdmissionNotification::TYPE_OFFER_REJECTED)->where('status', AdmissionNotification::STATUS_SENT)->count());

        // a second confirm (or a refresh) changes and sends nothing more
        $this->post(route('offer.decline.store', 'dEcLiNeSlUg02'))->assertRedirect(route('offer.decline', 'dEcLiNeSlUg02'));
        Mail::assertSent(OfferDeclinedNotice::class, 2);
        Http::assertSentCount(2); // one check, one archive
    }

    public function test_agreeing_in_the_modal_declines_and_answers_with_the_success_message(): void
    {
        [$applicant, $row] = $this->offer(880007, 'dEcLiNeSlUg07');
        $this->fakeDocuseal(880007, 'pending');

        $this->postJson(route('offer.decline.store', 'dEcLiNeSlUg07'), ['reason' => 'Moving abroad'])
            ->assertOk()
            ->assertJson(['state' => 'declined', 'message' => 'We have emailed you a confirmation. If you change your mind, please contact our Admissions Team.']);

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_REJECTED, (int) $applicant->fresh()->status_id);
        $this->assertSame('Moving abroad', $row->fresh()->decline_reason);
        Mail::assertSent(OfferDeclinedNotice::class, fn ($m) => $m->audience === 'applicant');

        // the page, opened again, shows the declined state and no modal
        $this->get(route('offer.decline', 'dEcLiNeSlUg07'))->assertOk()->assertSee('Your offer has been declined')->assertDontSee('id="declineModal"', false);
    }

    public function test_the_modal_is_told_when_the_form_was_signed_meanwhile_or_docuseal_is_down(): void
    {
        [, $signed] = $this->offer(880008, 'dEcLiNeSlUg08');
        $this->fakeDocuseal(880008, 'completed');
        $this->postJson(route('offer.decline.store', 'dEcLiNeSlUg08'))->assertOk()->assertJson(['state' => 'signed']);

        [$applicant] = $this->offer(880009, 'dEcLiNeSlUg09');
        Http::fake([self::API.'/*' => Http::response(['error' => 'down'], 500)]);
        $this->postJson(route('offer.decline.store', 'dEcLiNeSlUg09'))->assertStatus(503)->assertJson(['state' => 'error']);
        $this->assertSame(OfferAcceptanceWorkflow::OFFER_PLACED, (int) $applicant->fresh()->status_id);
    }

    public function test_a_form_signed_a_moment_ago_is_never_rejected(): void
    {
        [$applicant, $row] = $this->offer(880003, 'dEcLiNeSlUg03');
        $this->fakeDocuseal(880003, 'completed');

        $this->followingRedirects()
            ->post(route('offer.decline.store', 'dEcLiNeSlUg03'), ['reason' => 'too late'])
            ->assertOk()
            ->assertSee('This offer has already been accepted');

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_PLACED, (int) $applicant->fresh()->status_id);
        $this->assertSame(ApplicantDocusealSubmission::STATE_STORED, $row->fresh()->state);
        Http::assertNotSent(fn ($r) => $r->method() === 'DELETE');
        Mail::assertNotSent(OfferDeclinedNotice::class);
    }

    public function test_an_older_link_after_a_new_form_was_sent_is_closed(): void
    {
        [$applicant, $old] = $this->offer(880004, 'dEcLiNeSlUg04');
        $old->forceFill(['sent_at' => now()->subDay()])->save();
        $this->offer(880005, 'dEcLiNeSlUg05', $applicant);
        Http::fake();

        $this->get(route('offer.decline', 'dEcLiNeSlUg04'))->assertOk()->assertSee('This link is no longer active')->assertDontSee('Yes, decline my offer');
        $this->post(route('offer.decline.store', 'dEcLiNeSlUg04'));

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_PLACED, (int) $applicant->fresh()->status_id);
        Http::assertNotSent(fn ($r) => $r->method() === 'DELETE');
    }

    public function test_an_unknown_link_is_not_found(): void
    {
        $this->get(route('offer.decline', 'nOtArEaLsLuG'))->assertNotFound()->assertSee('We could not find this offer');
        $this->post(route('offer.decline.store', 'nOtArEaLsLuG'))->assertNotFound();
        $this->get('/offer/decline/'.rawurlencode("x' or 1=1"))->assertNotFound();
    }

    public function test_declining_on_the_docuseal_form_also_tells_the_applicant_and_agent(): void
    {
        [$applicant, $row] = $this->offer(880006, 'dEcLiNeSlUg06');
        $this->fakeDocuseal(880006, 'declined');

        app(OfferAcceptanceWorkflow::class)->process($row, ['decline_reason' => 'Changed plans']);

        $this->assertSame(OfferAcceptanceWorkflow::OFFER_REJECTED, (int) $applicant->fresh()->status_id);
        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'rejected' && $m->details['via'] === 'docuseal');
        Mail::assertSent(OfferDeclinedNotice::class, fn ($m) => $m->audience === 'applicant');
        Mail::assertSent(OfferDeclinedNotice::class, fn ($m) => $m->audience === 'agent');
    }
}
