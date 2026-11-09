<?php

namespace Tests\Feature;

use App\Events\StudentStatusChanged;
use App\Jobs\TaskAssignment\UpdateStudentStatusToSlcNewJob;
use App\Models\ProcessList;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentTask;
use App\Models\StudentTaskLog;
use App\Models\TaskAssignmentRule;
use App\Models\TaskList;
use App\Models\User;
use App\Services\TaskAssignment\StudentProcessWrapUp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Task Automation action "Change student status to SLC New" (user, 28 Oct 2026): the rule's task
 * waits until every other task the student has in the same process is done, then the student
 * becomes SLC New and the task is completed. Checked after updates from the task manager, the
 * student process page and model saves.
 */
class StudentSlcNewStatusActionTest extends TestCase
{
    use DatabaseTransactions;

    private User $staff;
    private Status $slcNew;
    /** @var TaskList[] [wrap-up, sibling, sibling] from one Live process */
    private array $lists;
    private TaskList $otherProcessList;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake([StudentStatusChanged::class]);
        // only this test's rule carries the action (e.g. local rule 194 would match real tasks)
        TaskAssignmentRule::where('after_assign_job', UpdateStudentStatusToSlcNewJob::class)->update(['after_assign_job' => null]);

        $this->staff = User::find(7);
        $this->slcNew = Status::where('type', 'Student')->where('name', config('tasks.slc_new_status'))->firstOrFail();

        $process = ProcessList::where('phase', 'Live')->get()->first(fn ($p) => TaskList::where('process_list_id', $p->id)->count() >= 3);
        $this->assertNotNull($process);
        $this->lists = TaskList::where('process_list_id', $process->id)->orderBy('id')->take(3)->get()->all();
        $this->otherProcessList = TaskList::where('process_list_id', '!=', $process->id)->whereHas('processlist', fn ($q) => $q->where('phase', 'Live'))->orderBy('id')->firstOrFail();

        // a student with no tasks of their own in that process, and not SLC New yet
        $taken = StudentTask::withTrashed()->whereIn('task_list_id', TaskList::where('process_list_id', $process->id)->pluck('id'))->pluck('student_id');
        $this->student = Student::whereNotIn('id', $taken)->where('status_id', '!=', $this->slcNew->id)->orderByDesc('id')->firstOrFail();
    }

    private function wrapUpTask(bool $byRule = true): StudentTask
    {
        $rule = TaskAssignmentRule::create([
            'task_list_id' => $this->lists[0]->id, 'trigger' => 'student_created', 'priority' => 50,
            'active' => $byRule ? 0 : 1, 'after_assign_job' => UpdateStudentStatusToSlcNewJob::class, 'created_by' => 1,
        ]);

        return StudentTask::create([
            'student_id' => $this->student->id, 'task_list_id' => $this->lists[0]->id, 'status' => 'Pending',
            'assigned_via' => $byRule ? 'auto' : null, 'rule_id' => $byRule ? $rule->id : null, 'created_by' => 1,
        ]);
    }

    private function sibling(int $index, string $status = 'Pending'): StudentTask
    {
        return StudentTask::create(['student_id' => $this->student->id, 'task_list_id' => $this->lists[$index]->id, 'status' => $status, 'created_by' => 1]);
    }

    private function assertSlcNew(StudentTask $task): void
    {
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertSame((int) $this->slcNew->id, (int) $this->student->fresh()->status_id);
        $this->assertTrue(StudentArchive::where('student_id', $this->student->id)->where('field_name', 'status_id')->where('field_new_value', $this->slcNew->id)->exists());
        $this->assertTrue(StudentAttendanceTermStatus::where('student_id', $this->student->id)->where('status_id', $this->slcNew->id)->exists());
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Status Changed')->where('current_field_value', 'Completed')->exists());
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Student Status')->where('current_field_value', $this->slcNew->name)->exists());
        Event::assertDispatched(StudentStatusChanged::class, fn ($e) => $e->studentId === (int) $this->student->id && $e->statusId === (int) $this->slcNew->id);
    }

    private function assertWaiting(StudentTask $task, int $previousStatus): void
    {
        $this->assertSame('Pending', $task->fresh()->status);
        $this->assertSame($previousStatus, (int) $this->student->fresh()->status_id);
    }

    public function test_it_waits_at_assignment_then_the_task_manager_completes_it(): void
    {
        $before = (int) $this->student->status_id;
        $task = $this->wrapUpTask();
        $this->sibling(1);
        $this->sibling(2);

        app()->call([new UpdateStudentStatusToSlcNewJob($this->student->id, $task->id, $task->rule_id), 'handle']);
        $this->assertWaiting($task, $before);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Status change waiting')->where('current_field_value', 'like', 'Waiting for 2 other%')->exists());

        // Mark as completed on /task-manager/show/{id}: one task still open
        $this->actingAs($this->staff)->post(route('task.manager.update.task.status'), [
            'student_ids' => [$this->student->id], 'task_id' => $this->lists[1]->id, 'status' => 'Completed', 'phase' => 'Live', 'student_task_id' => 0,
        ], ['Accept' => 'application/json'])->assertOk();
        $this->assertWaiting($task, $before);

        // Mark as cancelled for the last one: cancelled counts as done
        $this->actingAs($this->staff)->post(route('task.manager.canceled.task'), [
            'ids' => (string) $this->student->id, 'task_id' => $this->lists[2]->id, 'phase' => 'Live', 'canceled_reason' => 'Not needed',
        ], ['Accept' => 'application/json'])->assertOk();
        $this->assertSlcNew($task);
    }

    public function test_the_student_process_page_completes_it_and_other_processes_do_not_count(): void
    {
        $task = $this->wrapUpTask();
        $one = $this->sibling(1);
        StudentTask::create(['student_id' => $this->student->id, 'task_list_id' => $this->otherProcessList->id, 'status' => 'Pending', 'created_by' => 1]);

        // Complete on /student/process/{id}
        $this->actingAs($this->staff)->post(route('student.completed.task'), ['student' => $this->student->id, 'recordid' => $one->id], ['Accept' => 'application/json'])->assertOk();

        $this->assertSlcNew($task);
    }

    public function test_completing_it_by_hand_with_tasks_open_asks_first_and_leaves_the_status(): void
    {
        $before = (int) $this->student->status_id;
        $task = $this->wrapUpTask();
        $this->sibling(1);
        $this->sibling(2, 'In Progress');
        $this->sibling(1, 'Completed');

        // Mark as Complete on /student/process/{id} without confirming: refused, with what is open
        $this->actingAs($this->staff)->post(route('student.completed.task'), ['student' => $this->student->id, 'recordid' => $task->id], ['Accept' => 'application/json'])
            ->assertStatus(409)
            ->assertJsonCount(2, 'wrap_up.open')
            ->assertJsonPath('wrap_up.open.0.name', $this->lists[1]->name)
            ->assertJsonPath('wrap_up.open.1.status', 'In Progress');
        $this->assertWaiting($task, $before);

        // staff agreed: the task is completed, the status stays
        $this->actingAs($this->staff)->post(route('student.completed.task'), ['student' => $this->student->id, 'recordid' => $task->id, 'force' => 1], ['Accept' => 'application/json'])
            ->assertOk();
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertSame($before, (int) $this->student->fresh()->status_id);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $task->id)->where('actions', 'Completed by hand')->where('prev_field_value', '2 other tasks open')->exists());
        Event::assertNotDispatched(StudentStatusChanged::class);
    }

    public function test_completing_it_by_hand_with_nothing_open_changes_the_status(): void
    {
        $task = $this->wrapUpTask();
        $this->sibling(1, 'Completed');

        $this->actingAs($this->staff)->post(route('student.completed.task'), ['student' => $this->student->id, 'recordid' => $task->id], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('wrap_up', true);

        $this->assertSlcNew($task);
    }

    public function test_the_process_page_gives_mark_as_complete_the_open_tasks(): void
    {
        $task = $this->wrapUpTask();
        $this->sibling(1);
        \App\Models\TaskListUser::create(['task_list_id' => $this->lists[0]->id, 'user_id' => $this->staff->id, 'created_by' => 1]);

        $html = $this->actingAs($this->staff)->get(route('student.process', $this->student->id))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-recordid="'.$task->id.'"\s+data-wrapup="[^"]*&quot;open&quot;:\[\{&quot;id&quot;:\d+/', $html);
        $this->assertStringContainsString(e(json_encode($this->lists[1]->name)), $html);
    }

    public function test_a_removed_task_does_not_hold_it_up(): void
    {
        $task = $this->wrapUpTask();
        $this->sibling(1, 'Completed');
        $two = $this->sibling(2);

        $this->actingAs($this->staff)->delete(route('student.destory.task'), ['student' => $this->student->id, 'recordid' => $two->id], ['Accept' => 'application/json'])->assertOk();

        $this->assertSlcNew($task);
    }

    public function test_a_task_completed_through_the_model_runs_it_too(): void
    {
        $task = $this->wrapUpTask();
        $one = $this->sibling(1);

        // e.g. the automatic student email task (StudentEmailProvisioner) saves this way
        $one->update(['status' => 'Completed', 'updated_by' => 1]);

        $this->assertSlcNew($task);
    }

    public function test_a_task_added_by_hand_is_found_by_its_rule(): void
    {
        $task = $this->wrapUpTask(false);
        $this->sibling(1, 'Completed');

        $result = app(StudentProcessWrapUp::class)->check($this->student->id);

        $this->assertSame(StudentProcessWrapUp::DONE, $result['status']);
        $this->assertSlcNew($task);
    }

    public function test_a_student_already_slc_new_just_gets_the_task_completed(): void
    {
        $this->student->update(['status_id' => $this->slcNew->id]);
        $task = $this->wrapUpTask();

        app(StudentProcessWrapUp::class)->check($this->student->id);

        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertFalse(StudentArchive::where('student_id', $this->student->id)->where('field_new_value', $this->slcNew->id)->where('created_at', '>=', now()->subMinute())->exists());
        Event::assertNotDispatched(StudentStatusChanged::class);
    }

    public function test_the_action_is_offered_for_student_rules(): void
    {
        $this->actingAs($this->staff)->get(route('taskrules', ['subject' => 'student']))
            ->assertOk()
            ->assertSee(UpdateStudentStatusToSlcNewJob::class, false)
            ->assertSee('Change student status to SLC New and complete the task, once every other task in its process is done');
    }

    public function test_nothing_happens_without_the_action(): void
    {
        $this->sibling(1, 'Completed');
        $before = (int) $this->student->status_id;

        $this->assertNull(app(StudentProcessWrapUp::class)->check($this->student->id));
        $this->assertSame($before, (int) $this->student->fresh()->status_id);
    }
}
