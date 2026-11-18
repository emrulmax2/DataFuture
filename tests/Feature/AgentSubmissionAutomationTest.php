<?php

namespace Tests\Feature;

use App\Models\AgentUser;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * An application an agent submits runs Task Automation like one the applicant submits (user, 18 Nov 2026). The agent
 * branch of storeApplicantSubmission called an un-imported FacadesDebugbar (added 9 Nov 2026), so every agent
 * submission threw after it was saved and before the "Application submitted" rules, the e-signature request and the
 * acknowledgement could run — applicant 3907 got no tasks.
 */
class AgentSubmissionAutomationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_an_agent_submission_runs_the_application_submitted_rules(): void
    {
        Bus::fake();
        Mail::fake();
        Http::fake();
        Notification::fake();
        Http::preventStrayRequests();

        $applicant = Applicant::whereNotNull('agent_user_id')->whereNotNull('applicant_user_id')->whereNull('deleted_at')
            ->whereHas('contact')->orderByDesc('id')->first();
        $agent = $applicant ? AgentUser::find($applicant->agent_user_id) : null;
        if (!$applicant || !$agent || !DB::table('task_assignment_rules')->where('trigger', 'application_submitted')->where('active', 1)->whereNull('deleted_at')->exists()) {
            $this->markTestSkipped('No agent applicant or no active "Application submitted" rules.');
        }
        $runs = DB::table('task_assignment_runs')->where('applicant_id', $applicant->id)->count();

        $this->actingAs($agent, 'agent')
            ->postJson(route('agent.application.store.submission'), ['applicant_id' => $applicant->id])
            ->assertOk();

        $run = DB::table('task_assignment_runs')->where('applicant_id', $applicant->id)->orderByDesc('id')->first();
        $this->assertSame($runs + 1, DB::table('task_assignment_runs')->where('applicant_id', $applicant->id)->count());
        $this->assertSame('application_submitted', $run->trigger);
        $this->assertTrue((bool) (json_decode($run->facts, true)['is_agent_application'] ?? false));
        $this->assertSame(2, (int) Applicant::find($applicant->id)->status_id);
    }
}
