<?php

namespace Tests\Feature;

use App\Jobs\TaskAssignment\SendESignatureRequestJob;
use App\Jobs\UserMailerJob;
use App\Mail\ApplicationReceivedMail;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignatureEvent;
use App\Models\ApplicantUser;
use App\Models\TaskAssignmentRule;
use App\Services\ESignature\ESignatureEmailCapture;
use App\Services\ESignature\ESignatureRequestSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * "Apply now" sends one email - the reference number plus, when the task
 * engine raised an e-signature request, the link to sign - instead of a
 * confirmation email followed by a separate e-signature email. Uses the
 * user's test applicant (3559 / user 3427); everything rolls back.
 */
class ApplicationReceivedMailTest extends TestCase
{
    use DatabaseTransactions;

    private const APPLICANT = 3559;

    protected function tearDown(): void
    {
        ESignatureEmailCapture::stop(); /* never leave a capture open for the next test */
        parent::tearDown();
    }

    private function render(ApplicationReceivedMail $mail): string
    {
        config(['mail.mailers.array' => ['transport' => 'array']]);
        Mail::mailer('array')->to('nobody@example.test')->send($mail);
        $messages = Mail::mailer('array')->getSymfonyTransport()->messages();

        return (string) $messages[count($messages) - 1]->getOriginalMessage()->getHtmlBody();
    }

    public function test_the_email_carries_the_reference_and_the_sign_link_when_there_is_one(): void
    {
        $html = $this->render(new ApplicationReceivedMail('Sara FERDOUSH', '203561', 'London Churchill College', 'https://lcc.test/sign/abc', 'https://lcc.test/track/1'));

        $this->assertStringContainsString('Dear Sara FERDOUSH', $html);
        $this->assertStringContainsString('>203561<', $html);
        $this->assertStringContainsString('Action required: E-signature', $html);
        $this->assertSame(3, substr_count($html, 'https://lcc.test/sign/abc'), 'button href, link href, link text');
        $this->assertStringContainsString('src="https://lcc.test/track/1"', $html, 'the open-tracking pixel');
        $this->assertStringContainsString('Reference: 203561', $html, 'inbox preview line');
        $this->assertStringContainsString('cid:', $html, 'the logo is embedded');
    }

    public function test_the_email_has_no_signature_block_without_a_request(): void
    {
        $html = $this->render(new ApplicationReceivedMail('Sara FERDOUSH', '203561', 'London Churchill College'));

        $this->assertStringContainsString('>203561<', $html);
        $this->assertStringNotContainsString('E-signature', $html);
        $this->assertStringNotContainsString('Complete E-Signature', $html);
    }

    public function test_the_sender_hands_the_link_back_while_a_capture_is_open(): void
    {
        Bus::fake([UserMailerJob::class]);
        $applicant = Applicant::findOrFail(self::APPLICANT);
        $before = AdminESignature::where('applicant_id', $applicant->id)->count();

        ESignatureEmailCapture::start();
        $result = app(ESignatureRequestSender::class)->send($applicant, true, false, ['user_id' => 1, 'device' => 'Task Automation']);
        $caught = ESignatureEmailCapture::stop();

        $this->assertTrue($result['email'], 'the email channel still counts as used');
        $this->assertNotNull($caught);
        $this->assertStringContainsString('/e-signature', $caught['url']);
        $this->assertStringContainsString('/email/open/', $caught['tracking_url']);
        Bus::assertNotDispatched(UserMailerJob::class);
        $this->assertSame($before + 1, AdminESignature::where('applicant_id', $applicant->id)->count(), 'the audit row is still written');
        $this->assertNotNull(ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', 'email_sent')->latest('id')->first());
    }

    public function test_the_sender_still_emails_on_its_own_outside_a_capture(): void
    {
        Bus::fake([UserMailerJob::class]);
        $applicant = Applicant::findOrFail(self::APPLICANT);

        app(ESignatureRequestSender::class)->send($applicant, true, false, ['user_id' => 1, 'device' => 'Task Automation']);

        Bus::assertDispatched(UserMailerJob::class, fn(UserMailerJob $job) => $job->mailable->subject === 'E-Signature Form');
    }

    public function test_apply_now_sends_one_email_with_the_sign_link(): void
    {
        config(['tasks.jobs_run' => 'sync', 'tasks.esignature_user_id' => 1]);
        Bus::fake([UserMailerJob::class]);
        $applicant = Applicant::findOrFail(self::APPLICANT);
        /* make sure rule 1's follow-up is the e-signature job and the task is not already there */
        TaskAssignmentRule::where('task_list_id', 1)->update(['after_assign_job' => SendESignatureRequestJob::class, 'active' => 1]);
        /* a soft-deleted task reads as "removed by staff" and is never re-added: clear it outright (rolled back with the rest) */
        \App\Models\ApplicantTask::withTrashed()->where('applicant_id', $applicant->id)->where('task_list_id', 1)->forceDelete();
        \App\Models\ApplicantESignature::where('applicant_id', $applicant->id)->delete();

        $this->actingAs(ApplicantUser::findOrFail($applicant->applicant_user_id), 'applicant');
        $res = $this->postJson(route('applicant.application.store.submission'), ['applicant_id' => $applicant->id]);

        $res->assertOk();
        $this->assertSame(2, (int) $applicant->fresh()->status_id);
        Bus::assertDispatchedTimes(UserMailerJob::class, 1);
        Bus::assertDispatched(UserMailerJob::class, function (UserMailerJob $job) use ($applicant) {
            $mail = $job->mailable;
            return $mail instanceof ApplicationReceivedMail
                && $mail->reference === (string) $applicant->application_no
                && $mail->signUrl !== null && str_contains($mail->signUrl, '/e-signature')
                && $mail->trackingUrl !== null
                && $job->to === [$applicant->users->email];
        });
    }
}
