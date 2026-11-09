<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\TaskAssignmentRule;
use App\Models\TaskAssignmentRun;
use App\Services\TaskAssignment\ApplicationFacts;
use App\Services\TaskAssignment\TaskAssigner;
use App\Jobs\TaskAssignment\SendESignatureRequestJob;
use App\Jobs\UserMailerJob;
use App\Models\AdminESignature;
use App\Models\ApplicantTaskLog;
use Illuminate\Support\Facades\Bus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Runs against the configured database inside a transaction that is rolled
 * back - phpunit.xml points at the real dev schema, so RefreshDatabase must
 * never be used here.
 */
class TaskAssignmentEngineTest extends TestCase
{
    use DatabaseTransactions;

    private Applicant $applicant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->applicant = Applicant::whereNotNull('submission_date')->orderByDesc('id')->firstOrFail();
        /* a clean slate: only this test's rules apply */
        TaskAssignmentRule::query()->forceDelete();
        ApplicantTask::withTrashed()->where('applicant_id', $this->applicant->id)->forceDelete();
    }

    private function rule(int $task, array $conds = [], ?string $group = null, int $priority = 100): TaskAssignmentRule
    {
        $r = TaskAssignmentRule::create(['task_list_id' => $task, 'trigger' => TaskAssigner::TRIGGER_SUBMITTED, 'exclusive_group' => $group, 'priority' => $priority, 'active' => 1]);
        foreach ($conds as $i => [$f, $o, $v]) {
            $r->conditions()->create(['field' => $f, 'operator' => $o, 'value' => $v, 'sort_order' => $i]);
        }
        return $r;
    }

    public function test_operators_against_facts(): void
    {
        $facts = ['has_qualification' => true, 'age' => 30, 'course_id' => 27, 'student_loan' => 'Student Loan'];
        $this->assertTrue(ApplicationFacts::test($facts, 'has_qualification', 'is', '1'));
        $this->assertFalse(ApplicationFacts::test($facts, 'has_qualification', 'is', '0'));
        $this->assertTrue(ApplicationFacts::test($facts, 'age', 'gte', '21'));
        $this->assertFalse(ApplicationFacts::test($facts, 'age', 'lte', '20'));
        $this->assertTrue(ApplicationFacts::test($facts, 'course_id', 'in', ['27', '32']));
        $this->assertTrue(ApplicationFacts::test($facts, 'course_id', 'not_in', ['5']));
        $this->assertTrue(ApplicationFacts::test($facts, 'student_loan', 'is_not', 'Sponsor'));
        $this->assertFalse(ApplicationFacts::test(['age' => null], 'age', 'gte', '1'));
    }

    public function test_unconditional_rule_assigns_and_is_idempotent(): void
    {
        $this->rule(1);
        $assigner = app(TaskAssigner::class);

        $run = $assigner->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, true);
        $this->assertSame(1, $run->tasks_created);
        $task = ApplicantTask::where('applicant_id', $this->applicant->id)->where('task_list_id', 1)->first();
        $this->assertNotNull($task);
        $this->assertSame('auto', $task->assigned_via);
        $this->assertSame('Pending', $task->status);

        $again = $assigner->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, true);
        $this->assertSame(0, $again->tasks_created);
        $this->assertSame(TaskAssigner::EXISTS, $again->evaluations[0]['outcome']);
        $this->assertSame(1, ApplicantTask::where('applicant_id', $this->applicant->id)->count());
    }

    public function test_exclusive_group_assigns_one_task_by_priority(): void
    {
        /* both match; the lower priority number wins */
        $this->rule(5, [], 'entry', 20);
        $this->rule(6, [], 'entry', 10);
        $plan = app(TaskAssigner::class)->plan($this->applicant);
        $this->assertSame([6], array_keys($plan['assign']));
        $outcomes = collect($plan['evaluations'])->pluck('outcome', 'task_list_id');
        $this->assertSame(TaskAssigner::ASSIGN, $outcomes[6]);
        $this->assertSame(TaskAssigner::LOST_GROUP, $outcomes[5]);
    }

    public function test_conditions_route_between_academic_and_mature(): void
    {
        $this->rule(5, [['has_qualification', 'is', '1']], 'entry', 10);
        $this->rule(6, [['has_qualification', 'is', '0']], 'entry', 20);
        $plan = app(TaskAssigner::class)->plan($this->applicant);
        $expected = $plan['facts']['has_qualification'] ? 5 : 6;
        $this->assertSame([$expected], array_keys($plan['assign']));
    }

    public function test_a_task_staff_removed_is_not_put_back(): void
    {
        $t = ApplicantTask::create(['applicant_id' => $this->applicant->id, 'task_list_id' => 2, 'status' => 'Pending', 'created_by' => 1]);
        $t->delete();
        $this->rule(2);
        $run = app(TaskAssigner::class)->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, true);
        $this->assertSame(0, $run->tasks_created);
        $this->assertSame(TaskAssigner::REMOVED_BY_STAFF, $run->evaluations[0]['outcome']);
    }

    public function test_dry_run_records_but_creates_nothing(): void
    {
        $this->rule(3);
        $run = app(TaskAssigner::class)->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, false);
        $this->assertSame(TaskAssignmentRun::MODE_DRY, $run->mode);
        $this->assertSame(TaskAssigner::ASSIGN, $run->evaluations[0]['outcome']);
        $this->assertSame(0, ApplicantTask::where('applicant_id', $this->applicant->id)->count());
    }

    public function test_follow_up_job_sends_the_esignature_request_as_the_responsible_user(): void
    {
        config(['tasks.jobs_run' => 'sync', 'tasks.esignature_user_id' => 1]);
        Bus::fake([UserMailerJob::class]); /* the mail job is queued, never sent here */
        /* a prior signature would make the job skip; rolled back with the rest */
        \App\Models\ApplicantESignature::where('applicant_id', $this->applicant->id)->delete();
        $rule = $this->rule(1);
        $rule->update(['after_assign_job' => SendESignatureRequestJob::class]);

        $run = app(TaskAssigner::class)->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, true);

        $this->assertSame(1, $run->tasks_created);
        $this->assertSame('ran', $run->evaluations[0]['job_status']);

        $sent = AdminESignature::where('applicant_id', $this->applicant->id)->where('device', 'Task Automation')->latest('id')->first();
        $this->assertNotNull($sent, 'the sender record is written');
        $this->assertSame(1, (int) $sent->user_id);
        $this->assertSame('Task Automation', $sent->device);
        $hasEmail = !empty(optional($this->applicant->users)->email);
        $hasMobile = !empty(optional($this->applicant->contact)->mobile);
        $this->assertSame($hasEmail ? 1 : 0, (int) $sent->via_email);
        $this->assertSame($hasMobile ? 1 : 0, (int) $sent->via_sms);
        if ($hasEmail) {
            Bus::assertDispatched(UserMailerJob::class);
        }

        $log = ApplicantTaskLog::where('applicant_tasks_id', $run->created_task_ids[0])->where('actions', 'E-signature request sent')->first();
        $this->assertNotNull($log, 'the task log says the request was sent');
    }

    public function test_follow_up_job_does_not_run_when_the_task_already_exists(): void
    {
        config(['tasks.jobs_run' => 'sync']);
        Bus::fake([UserMailerJob::class]);
        ApplicantTask::create(['applicant_id' => $this->applicant->id, 'task_list_id' => 1, 'status' => 'Pending', 'created_by' => 1]);
        $rule = $this->rule(1);
        $rule->update(['after_assign_job' => SendESignatureRequestJob::class]);
        /* earlier live submissions may have left sender records: only a new one counts */
        $before = AdminESignature::where('applicant_id', $this->applicant->id)->where('device', 'Task Automation')->count();

        $run = app(TaskAssigner::class)->apply($this->applicant, TaskAssigner::TRIGGER_SUBMITTED, true);

        $this->assertSame(0, $run->tasks_created);
        $this->assertNull($run->evaluations[0]['job_status']);
        $this->assertSame($before, AdminESignature::where('applicant_id', $this->applicant->id)->where('device', 'Task Automation')->count());
    }

    public function test_inactive_rules_are_ignored(): void
    {
        $r = $this->rule(4);
        $r->update(['active' => 0]);
        $plan = app(TaskAssigner::class)->plan($this->applicant);
        $this->assertSame([], $plan['evaluations']);
    }
}
