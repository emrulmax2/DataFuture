<?php

namespace Tests\Feature;

use App\Enums\EsignEventType;
use App\Jobs\TaskAssignment\SendESignatureRequestJob;
use App\Jobs\UserMailerJob;
use App\Models\AdminESignature;
use App\Models\Applicant;
use App\Models\ApplicantESignature;
use App\Models\ApplicantESignatureEvent;
use App\Models\ApplicantTask;
use App\Services\ESignature\ESignatureRequestContext;
use App\Services\ESignature\ESignatureRequestSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * An automatic e-signature request raised on "Apply now" records who
 * submitted the application and from where, instead of "Task Automation".
 */
class EsignatureSubmissionSenderTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        ESignatureRequestContext::clear();
        parent::tearDown();
    }

    private function applicant(): Applicant
    {
        $requested = AdminESignature::select('applicant_id')->union(ApplicantESignature::select('applicant_id'));
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereNotIn('id', $requested)->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        return $applicant;
    }

    private function taskId(Applicant $applicant): int
    {
        return (int) (ApplicantTask::where('applicant_id', $applicant->id)->value('id') ?? ApplicantTask::value('id'));
    }

    public function test_the_submitters_details_are_recorded_on_the_request(): void
    {
        Bus::fake();
        $applicant = $this->applicant();
        $request = Request::create('/applicant/application/submit', 'POST', ['applicant_id' => $applicant->id], [], [], [
            'REMOTE_ADDR' => '86.12.34.56',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/155.0 Safari/537.36',
        ]);
        ESignatureRequestContext::set(ESignatureRequestContext::fromRequest($request, 'agent_submission', ['type' => 'agent', 'id' => 42, 'name' => 'Global Study Agency', 'email' => 'agent@example.com']));

        // Constructed the way TaskAssigner does it, while the context is open.
        $job = new SendESignatureRequestJob($applicant->id, $this->taskId($applicant), null);
        ESignatureRequestContext::clear();
        $job->handle(app(ESignatureRequestSender::class));

        $admin = AdminESignature::where('applicant_id', $applicant->id)->latest('id')->first();
        $this->assertNotNull($admin);
        $this->assertSame('86.12.34.56', $admin->ip_address);
        $this->assertSame('Chrome', $admin->browser);
        $this->assertSame('Windows', $admin->os);
        $this->assertStringContainsString('Mozilla/5.0', $admin->device);
        $this->assertSame((int) config('tasks.esignature_user_id', 1), (int) $admin->user_id);

        $event = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::SIGN_REQUEST_CREATED->value)->latest('id')->first();
        $this->assertNotNull($event);
        $this->assertSame('86.12.34.56', $event->ip_address);
        $this->assertStringContainsString('on the application submission by agent Global Study Agency (agent@example.com)', $event->event_description);
        $this->assertSame('agent_submission', $event->extra_field['trigger']);
        $this->assertSame('agent@example.com', $event->extra_field['by_email']);
        Bus::assertDispatched(UserMailerJob::class);
    }

    public function test_without_a_submission_context_the_request_is_still_recorded_as_task_automation(): void
    {
        Bus::fake();
        $applicant = $this->applicant();
        ESignatureRequestContext::clear();

        (new SendESignatureRequestJob($applicant->id, $this->taskId($applicant), null))->handle(app(ESignatureRequestSender::class));

        $admin = AdminESignature::where('applicant_id', $applicant->id)->latest('id')->first();
        $this->assertSame('Task Automation', $admin->device);
        $this->assertSame('System', $admin->browser);
        $event = ApplicantESignatureEvent::where('applicant_id', $applicant->id)->where('event_type', EsignEventType::SIGN_REQUEST_CREATED->value)->latest('id')->first();
        $this->assertNull($event->extra_field);
        $this->assertStringEndsWith('with the document', $event->event_description);
    }
}
