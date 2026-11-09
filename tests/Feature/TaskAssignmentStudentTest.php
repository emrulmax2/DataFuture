<?php

namespace Tests\Feature;

use App\Events\StudentStatusChanged;
use App\Jobs\ProcessStudentTaskRules;
use App\Models\Applicant;
use App\Models\ProcessList;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentConversionLog;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskAssignmentRule;
use App\Models\TaskAssignmentRun;
use App\Models\TaskList;
use App\Models\User;
use App\Services\TaskAssignment\StudentFacts;
use App\Services\TaskAssignment\TaskAssigner;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Task Automation for students: "Student created" (last conversion step) and
 * "Student status changed" rules, the settings screen's Student half, and the
 * rules that replaced the hard-wired Auto feed / status process assignments.
 * Everything runs inside a rolled-back transaction.
 */
class TaskAssignmentStudentTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;
    private TaskAssigner $assigner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = Student::whereNotNull('applicant_id')->whereHas('activeCR')->orderByDesc('id')->firstOrFail();
        $this->assigner = app(TaskAssigner::class);
        config(['tasks.student_mode' => 'live']);
    }

    /** Only this test's student rules apply (the seeded ones come back with the rollback). */
    private function clearStudentRules(): void
    {
        TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_STUDENT)->withTrashed()->get()->each(fn ($r) => $r->conditions()->delete());
        TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_STUDENT)->withTrashed()->forceDelete();
    }

    /** A Live task this student has never had (not even removed). */
    private function freshLiveTask(array $skip = []): TaskList
    {
        $had = StudentTask::withTrashed()->where('student_id', $this->student->id)->pluck('task_list_id')->all();
        $task = TaskList::whereIn('process_list_id', ProcessList::where('phase', 'Live')->pluck('id'))->whereNotIn('id', array_merge($had, $skip))->orderBy('id')->first();
        $this->assertNotNull($task, 'needs a Live task the student does not have');

        return $task;
    }

    private function rule(int $task, string $trigger, array $conds = [], ?string $group = null, int $priority = 100): TaskAssignmentRule
    {
        $r = TaskAssignmentRule::create(['task_list_id' => $task, 'trigger' => $trigger, 'exclusive_group' => $group, 'priority' => $priority, 'active' => 1]);
        foreach ($conds as $i => [$f, $o, $v]) {
            $r->conditions()->create(['field' => $f, 'operator' => $o, 'value' => $v, 'sort_order' => $i]);
        }

        return $r;
    }

    /* ---------------- engine ---------------- */

    public function test_student_facts_read_the_active_course(): void
    {
        $facts = StudentFacts::build($this->student, ['previous_status_id' => 18]);
        $this->assertSame((int) $this->student->status_id, $facts['status_id']);
        $this->assertSame(18, $facts['previous_status_id']);
        $this->assertSame((int) $this->student->activeCR->creation->course_id, (int) $facts['course_id']);
        $this->assertSame((int) $this->student->activeCR->creation->semester_id, (int) $facts['semester_id']);
        $this->assertArrayHasKey('student_loan', $facts);
        $this->assertSame(array_keys(StudentFacts::fields()), array_keys($facts));
    }

    public function test_a_student_created_rule_assigns_an_auto_task_once(): void
    {
        $this->clearStudentRules();
        $task = $this->freshLiveTask();
        $rule = $this->rule($task->id, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $run = $this->assigner->applyStudent($this->student, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $this->assertSame(1, $run->tasks_created);
        $this->assertSame($this->student->id, (int) $run->student_id);
        $this->assertNull($run->applicant_id);
        $created = StudentTask::where('student_id', $this->student->id)->where('task_list_id', $task->id)->sole();
        $this->assertSame('auto', $created->assigned_via);
        $this->assertSame($rule->id, (int) $created->rule_id);
        $this->assertSame('Pending', $created->status);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $created->id)->where('actions', 'Assigned')->where('current_field_value', (string) $rule->id)->exists());

        // idempotent: a second run finds it there
        $again = $this->assigner->applyStudent($this->student, TaskAssigner::TRIGGER_STUDENT_CREATED);
        $this->assertSame(0, $again->tasks_created);
        $this->assertSame(TaskAssigner::EXISTS, $again->evaluations[0]['outcome']);
    }

    public function test_a_status_rule_fires_only_for_its_status_and_reads_the_previous_one(): void
    {
        $this->clearStudentRules();
        $task = $this->freshLiveTask();
        $other = Status::where('type', 'Student')->where('id', '!=', $this->student->status_id)->orderBy('id')->firstOrFail();
        $this->rule($task->id, TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, [['status_id', 'is', (string) $other->id], ['previous_status_id', 'is', (string) $this->student->status_id]]);

        $none = $this->assigner->applyStudent($this->student, TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, ['previous_status_id' => $this->student->status_id]);
        $this->assertSame(0, $none->tasks_created);
        $this->assertSame(TaskAssigner::NO_MATCH, $none->evaluations[0]['outcome']);

        $previous = (int) $this->student->status_id;
        $this->student->update(['status_id' => $other->id]);
        $hit = $this->assigner->applyStudent($this->student->fresh(), TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, ['previous_status_id' => $previous]);
        $this->assertSame(1, $hit->tasks_created);

        // a "Student created" run does not use status rules
        $this->assertSame(0, count($this->assigner->planStudent($this->student->fresh(), TaskAssigner::TRIGGER_STUDENT_CREATED)['evaluations']));
    }

    public function test_a_student_task_staff_removed_is_not_put_back_and_groups_pick_one(): void
    {
        $this->clearStudentRules();
        $a = $this->freshLiveTask();
        $b = $this->freshLiveTask([$a->id]);
        $c = $this->freshLiveTask([$a->id, $b->id]);
        $this->rule($a->id, TaskAssigner::TRIGGER_STUDENT_CREATED);
        $this->rule($b->id, TaskAssigner::TRIGGER_STUDENT_CREATED, [], 'pick_one', 1);
        $this->rule($c->id, TaskAssigner::TRIGGER_STUDENT_CREATED, [], 'pick_one', 2);
        StudentTask::create(['student_id' => $this->student->id, 'task_list_id' => $a->id, 'status' => 'Pending', 'created_by' => 1])->delete();

        $plan = $this->assigner->planStudent($this->student, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $outcomes = collect($plan['evaluations'])->pluck('outcome', 'task_list_id');
        $this->assertSame(TaskAssigner::REMOVED_BY_STAFF, $outcomes[$a->id]);
        $this->assertSame(TaskAssigner::ASSIGN, $outcomes[$b->id]);
        $this->assertSame(TaskAssigner::LOST_GROUP, $outcomes[$c->id]);
        $this->assertSame([$b->id], array_keys($plan['assign']));
    }

    public function test_shadow_mode_records_the_run_but_creates_nothing(): void
    {
        $this->clearStudentRules();
        config(['tasks.student_mode' => 'shadow']);
        $task = $this->freshLiveTask();
        $this->rule($task->id, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $run = $this->assigner->applyStudent($this->student, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $this->assertSame(TaskAssignmentRun::MODE_DRY, $run->mode);
        $this->assertSame(0, $run->tasks_created);
        $this->assertFalse(StudentTask::where('student_id', $this->student->id)->where('task_list_id', $task->id)->exists());
    }

    /* ---------------- hook points ---------------- */

    public function test_the_conversion_ends_with_the_student_rules(): void
    {
        $this->assertSame(ProcessStudentTaskRules::class, last(StudentConversionLog::JOB_SEQUENCE));
        $this->assertSame('Assign Student Tasks', StudentConversionLog::jobLabel(ProcessStudentTaskRules::class));

        $this->clearStudentRules();
        $task = $this->freshLiveTask();
        $this->rule($task->id, TaskAssigner::TRIGGER_STUDENT_CREATED);
        $applicant = Applicant::findOrFail($this->student->applicant_id);
        // the step fills the student the conversion created (StudentConversion::studentFor)
        $login = \App\Models\StudentUser::where('email', \App\Models\ApplicantUser::find($applicant->applicant_user_id)->email)->first();
        $target = \App\Services\Admission\StudentConversion::studentFor($applicant, $login) ?? $this->student;

        (new ProcessStudentTaskRules($applicant))->handle($this->assigner);

        $this->assertTrue(StudentTask::where('student_id', $target->id)->where('task_list_id', $task->id)->where('assigned_via', 'auto')->exists());
        $this->assertSame(TaskAssigner::TRIGGER_STUDENT_CREATED, TaskAssignmentRun::where('student_id', $target->id)->latest('id')->value('trigger'));
    }

    public function test_changing_the_status_on_the_student_record_runs_the_rules_once(): void
    {
        $this->clearStudentRules();
        $task = $this->freshLiveTask();
        $target = Status::where('type', 'Student')->where('id', '!=', $this->student->status_id)
            ->whereNotIn('id', [21, 26, 27, 31, 42, 22, 45])->orderBy('id')->firstOrFail();
        $this->rule($task->id, TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, [['status_id', 'is', (string) $target->id]]);
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $previous = (int) $this->student->status_id;

        $this->actingAs($staff)->postJson(route('student.update.status'), ['student_id' => $this->student->id, 'status_id' => $target->id, 'status_change_date' => now()->format('d-m-Y')])->assertOk();

        $this->assertSame(1, StudentTask::where('student_id', $this->student->id)->where('task_list_id', $task->id)->count());
        $run = TaskAssignmentRun::where('student_id', $this->student->id)->latest('id')->firstOrFail();
        $this->assertSame(TaskAssigner::TRIGGER_STUDENT_STATUS_CHANGED, $run->trigger);
        $this->assertSame($previous, $run->facts['previous_status_id']);

        // the same status again: nothing changes, so no event and no second task
        Event::fake([StudentStatusChanged::class]);
        $this->actingAs($staff)->postJson(route('student.update.status'), ['student_id' => $this->student->id, 'status_id' => $target->id, 'status_change_date' => now()->format('d-m-Y')]);
        Event::assertNotDispatched(StudentStatusChanged::class);
        $this->assertSame(1, StudentTask::where('student_id', $this->student->id)->where('task_list_id', $task->id)->count());
    }

    public function test_the_old_hard_wired_assignments_no_longer_run(): void
    {
        // A status that still has a process attached in Status settings (SLC New → SLC NEW).
        $status = Status::where('type', 'Student')->where('process_list_id', '>', 0)->first();
        if (!$status) {
            $this->markTestSkipped('no student status has a process attached');
        }
        $this->clearStudentRules();
        $processTasks = TaskList::where('process_list_id', $status->process_list_id)->pluck('id')->all();
        $from = Status::where('type', 'Student')->where('id', '!=', $status->id)->whereNotIn('id', [21, 26, 27, 31, 42, 22, 45])->orderBy('id')->firstOrFail();
        $this->student->update(['status_id' => $from->id]);
        $before = StudentTask::where('student_id', $this->student->id)->whereIn('task_list_id', $processTasks)->count();
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();

        $this->actingAs($staff)->postJson(route('student.update.status'), ['student_id' => $this->student->id, 'status_id' => $status->id, 'status_change_date' => now()->format('d-m-Y')])->assertOk();

        // no rule → the attached process gives nothing any more
        $this->assertSame($before, StudentTask::where('student_id', $this->student->id)->whereIn('task_list_id', $processTasks)->count());
        $this->assertSame((int) $status->id, (int) $this->student->fresh()->status_id);
        // and the migration no longer seeds rules
        $this->assertSame(0, TaskAssignmentRule::forSubject(TaskAssignmentRule::SUBJECT_STUDENT)->where('notes', 'like', 'Migrated from the %')->count());
    }

    /* ---------------- settings screen ---------------- */

    public function test_the_settings_screen_keeps_the_two_halves_apart(): void
    {
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->rule($this->freshLiveTask()->id, TaskAssigner::TRIGGER_STUDENT_CREATED);

        $applicantRules = $this->actingAs($staff)->getJson(route('taskrules.list'))->assertOk()->json('data');
        $studentRules = $this->actingAs($staff)->getJson(route('taskrules.list', ['subject' => 'student']))->assertOk()->json('data');
        $this->assertSame(TaskAssignmentRule::forSubject('applicant')->count(), count($applicantRules));
        $this->assertSame(TaskAssignmentRule::forSubject('student')->count(), count($studentRules));
        $this->assertEmpty(array_intersect(array_column($applicantRules, 'id'), array_column($studentRules, 'id')));
        $this->assertNotEmpty(array_filter(array_column($studentRules, 'process')));

        $page = $this->actingAs($staff)->get(route('taskrules', ['subject' => 'student']))->assertOk()->getContent();
        $this->assertStringContainsString('data-taskrule-subject="student"', $page);
        $this->assertStringContainsString('id="rule_process_id"', $page);
        $this->assertStringContainsString('Student status changed', $page);
        $this->assertStringNotContainsString('value="application_submitted"', $page);
        $applicantPage = $this->actingAs($staff)->get(route('taskrules'))->assertOk()->getContent();
        $this->assertStringContainsString('data-taskrule-subject="applicant"', $applicantPage);
        $this->assertStringContainsString('value="application_submitted"', $applicantPage);
        $this->assertStringNotContainsString('value="student_created"', $applicantPage);
    }

    public function test_the_student_list_says_which_rules_auto_complete_at_conversion(): void
    {
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->clearStudentRules();
        $tasks = TaskList::whereIn('process_list_id', ProcessList::where('phase', 'Live')->pluck('id'))->orderBy('id')->limit(6)->pluck('id')->all();
        $this->assertCount(6, $tasks);
        $make = function (int $task, string $trigger, ?string $job) {
            $rule = $this->rule($task, $trigger);
            $rule->update(['after_assign_job' => $job]);

            return $rule->id;
        };
        $expect = [
            $make($tasks[0], TaskAssigner::TRIGGER_STUDENT_CREATED, \App\Jobs\TaskAssignment\CreateStudentEmailJob::class) => true,
            $make($tasks[1], TaskAssigner::TRIGGER_STUDENT_CREATED, \App\Jobs\TaskAssignment\SendStudentWelcomeLetterJob::class) => true,
            $make($tasks[2], TaskAssigner::TRIGGER_STUDENT_CREATED, \App\Jobs\TaskAssignment\SendInductionInvitationJob::class) => true,
            $make($tasks[3], TaskAssigner::TRIGGER_STUDENT_CREATED, \App\Jobs\TaskAssignment\UpdateStudentStatusToSlcNewJob::class) => false, // waits for the rest
            $make($tasks[4], TaskAssigner::TRIGGER_STUDENT_CREATED, null) => false,                                                        // staff complete it
            $make($tasks[5], 'student_status_changed', \App\Jobs\TaskAssignment\CreateStudentEmailJob::class) => false,                    // not at conversion
        ];

        $rows = $this->actingAs($staff)->getJson(route('taskrules.list', ['subject' => 'student']))->assertOk()->json('data');
        $got = array_column($rows, 'auto_complete', 'id');
        ksort($got);
        ksort($expect);
        $this->assertSame($expect, $got);

        $applicantRows = $this->actingAs($staff)->getJson(route('taskrules.list'))->assertOk()->json('data');
        $this->assertSame([], array_filter(array_column($applicantRows, 'auto_complete'), fn ($v) => $v !== null));
    }

    public function test_saving_a_rule_checks_the_task_and_conditions_against_its_side(): void
    {
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $liveTask = TaskList::whereIn('process_list_id', ProcessList::where('phase', 'Live')->pluck('id'))->firstOrFail();
        $applicantTask = TaskList::whereIn('process_list_id', ProcessList::where('phase', 'Applicant')->pluck('id'))->firstOrFail();
        $status = Status::where('type', 'Student')->firstOrFail();

        // a student rule with a student condition saves
        $this->actingAs($staff)->postJson(route('taskrules.store'), ['task_list_id' => $liveTask->id, 'trigger' => 'student_status_changed', 'priority' => 50, 'active' => 1,
            'conditions' => [['field' => 'status_id', 'operator' => 'is', 'value' => (string) $status->id]]])->assertOk();
        // an applicant task on a student trigger is refused
        $this->actingAs($staff)->postJson(route('taskrules.store'), ['task_list_id' => $applicantTask->id, 'trigger' => 'student_created', 'priority' => 50, 'active' => 1])
            ->assertStatus(422)->assertJsonValidationErrors(['task_list_id']);
        // a student-only field on an applicant rule is refused
        $this->actingAs($staff)->postJson(route('taskrules.store'), ['task_list_id' => $applicantTask->id, 'trigger' => 'application_submitted', 'priority' => 50, 'active' => 1,
            'conditions' => [['field' => 'status_id', 'operator' => 'is', 'value' => (string) $status->id]]])->assertStatus(422)->assertJsonValidationErrors(['conditions.0.field']);
        // an applicant rule as before still saves
        $this->actingAs($staff)->postJson(route('taskrules.store'), ['task_list_id' => $applicantTask->id, 'trigger' => 'application_submitted', 'priority' => 50, 'active' => 1,
            'conditions' => [['field' => 'age', 'operator' => 'gte', 'value' => '21']]])->assertOk();
    }

    public function test_the_student_preview_and_runs(): void
    {
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();

        $preview = $this->actingAs($staff)->getJson(route('taskrules.preview', ['subject' => 'student', 'student' => $this->student->registration_no, 'trigger' => 'student_created']))->assertOk();
        $preview->assertJsonPath('student.id', $this->student->id);
        $this->assertSame(array_keys(StudentFacts::fields()), array_keys($preview->json('facts')));
        $this->assertSame(TaskAssignmentRule::forTrigger('student_created')->active()->count(), count($preview->json('evaluations')));
        $this->actingAs($staff)->getJson(route('taskrules.preview', ['subject' => 'student', 'student' => 'nobody']))->assertStatus(404);

        $this->assigner->applyStudent($this->student, TaskAssigner::TRIGGER_STUDENT_CREATED);
        $runs = $this->actingAs($staff)->getJson(route('taskrules.runs', ['subject' => 'student']))->assertOk()->json('data');
        $this->assertSame($this->student->registration_no, $runs[0]['application_no']);
        $applicantRuns = $this->actingAs($staff)->getJson(route('taskrules.runs'))->assertOk()->json('data');
        $this->assertNotContains($this->student->id, array_column(array_filter($applicantRuns, fn ($r) => $r['application_no'] === $this->student->registration_no), 'applicant_id'));
    }
}
