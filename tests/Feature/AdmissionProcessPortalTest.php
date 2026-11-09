<?php

namespace Tests\Feature;

use App\Models\ApplicantTask;
use App\Models\ApplicantTaskLog;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The applicant process page on the admission portal shell: one board per
 * process, the archived list with each task's former status, portal markup
 * for the outcome and staff dialogs, and outcome notes kept in the task log.
 */
class AdmissionProcessPortalTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    private function liveTask(): ApplicantTask
    {
        $task = ApplicantTask::whereHas('task')->orderBy('id', 'desc')->first();
        $this->assertNotNull($task, 'An applicant task is needed for this test.');

        return $task;
    }

    public function test_the_process_page_renders_one_board_per_process(): void
    {
        $task = $this->liveTask();

        $response = $this->actingAs($this->staff())->get(route('admission.process', $task->applicant_id));

        $response->assertOk()
            ->assertSee('data-adp-proc', false)
            ->assertSee('class="adp-board"', false)
            ->assertSee('adp-lane__h', false)
            ->assertSee('id="addTasksModal"', false)
            ->assertSee('data-adp-task="'.$task->id.'"', false)
            ->assertSee('id="changeStatusModal"', false)
            ->assertDontSee('editAdmissionPersonalDetailsModal');
    }

    public function test_the_archived_list_reports_the_former_status_and_who_archived(): void
    {
        $archived = ApplicantTask::onlyTrashed()->whereHas('task')->orderBy('id', 'desc')->first();
        if (!$archived) {
            $this->markTestSkipped('No archived applicant task on this database.');
        }

        $response = $this->actingAs($this->staff())->getJson(route('admission.archived.process.list', [
            'applicantId' => $archived->applicant_id,
            'processId' => $archived->task->process_list_id,
            'size' => 50,
            'page' => 1,
        ]));

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $archived->id);
        $this->assertNotNull($row, 'The archived task should be listed.');
        $this->assertSame($archived->status, $row['status']);
        $this->assertArrayHasKey('archived_by', $row);
        $this->assertArrayHasKey('deleted_at', $row);
    }

    public function test_outcome_statuses_render_as_portal_chips_with_a_note(): void
    {
        $task = ApplicantTask::whereHas('task.statuses')->orderBy('id', 'desc')->first();
        $this->assertNotNull($task, 'A task with outcome statuses is needed for this test.');

        $response = $this->actingAs($this->staff())->postJson(route('admission.show.task.outmoce.statuses'), ['taskId' => $task->id, 'portal' => 1]);

        $response->assertOk();
        $this->assertSame(1, (int) $response->json('message.suc'));
        $html = $response->json('message.res');
        $this->assertStringContainsString('class="adp-outcome adp-outcome--', $html);
        $this->assertStringContainsString('class="adp-opt__input resultStatus"', $html);
        $this->assertStringContainsString('name="note"', $html);
        $this->assertStringNotContainsString('form-check', $html);
    }

    public function test_the_legacy_outcome_markup_is_untouched_without_the_portal_flag(): void
    {
        $task = ApplicantTask::whereHas('task.statuses')->orderBy('id', 'desc')->first();
        $this->assertNotNull($task);

        $response = $this->actingAs($this->staff())->postJson(route('admission.show.task.outmoce.statuses'), ['taskId' => $task->id]);

        $response->assertOk();
        $this->assertStringContainsString('form-check-input resultStatus', $response->json('message.res'));
    }

    public function test_an_outcome_note_is_kept_in_the_task_log(): void
    {
        $task = ApplicantTask::whereHas('task.statuses')->orderBy('id', 'desc')->first();
        $this->assertNotNull($task);
        $statusId = $task->task->statuses->first()->task_status_id;
        $note = 'Portal test note '.uniqid();

        $response = $this->actingAs($this->staff())->postJson(route('admission.process.task.result.update'), [
            'applicant_id' => $task->applicant_id,
            'applicant_task_id' => $task->id,
            'result_statuses' => $statusId,
            'note' => $note,
        ]);

        $response->assertOk();
        $this->assertSame((int) $statusId, (int) $task->fresh()->task_status_id);
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Note')->where('current_field_value', $note)->exists());
    }

    public function test_a_task_needing_outcome_and_upload_stays_pending_after_the_outcome_alone(): void
    {
        $task = ApplicantTask::where('status', 'Pending')
            ->whereHas('task', fn ($q) => $q->where('status', 'Yes')->where('upload', 'Yes'))
            ->whereHas('task.statuses')
            ->doesntHave('documents')
            ->orderBy('id', 'desc')->first();
        if (!$task) {
            $this->markTestSkipped('No pending task needing both an outcome and an upload.');
        }

        $response = $this->actingAs($this->staff())->postJson(route('admission.process.task.result.update'), [
            'applicant_id' => $task->applicant_id,
            'applicant_task_id' => $task->id,
            'result_statuses' => $task->task->statuses->first()->task_status_id,
        ]);

        $response->assertOk();
        $this->assertSame('Pending', $response->json('status'));
        $this->assertStringContainsString('upload', $response->json('hint'));
        $this->assertSame('Pending', $task->fresh()->status);
    }

    public function test_a_task_needing_only_an_upload_moves_to_in_progress_on_upload(): void
    {
        Storage::fake('local');
        $task = ApplicantTask::where('status', 'Pending')
            ->whereHas('task', fn ($q) => $q->where('status', 'No')->where('upload', 'Yes'))
            ->doesntHave('documents')
            ->orderBy('id', 'desc')->first();
        if (!$task) {
            $this->markTestSkipped('No pending task needing only an upload.');
        }

        $response = $this->actingAs($this->staff())->post(route('admission.upload.task.documents'), [
            'applicant_id' => $task->applicant_id,
            'applicant_task_id' => $task->id,
            'hard_copy_check' => 0,
            'file' => UploadedFile::fake()->create('evidence.pdf', 8, 'application/pdf'),
        ]);

        $response->assertOk();
        $this->assertSame('In Progress', $response->json('status'));
        $this->assertSame('In Progress', $task->fresh()->status);
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Status Changed')
            ->where('prev_field_value', 'Pending')->where('current_field_value', 'In Progress')->exists());
    }

    public function test_reopening_a_completed_task_whose_requirements_are_in_lands_in_progress(): void
    {
        $task = ApplicantTask::where('status', 'Completed')
            ->whereHas('task', fn ($q) => $q->where('status', 'No')->where('upload', 'Yes'))
            ->has('documents')
            ->orderBy('id', 'desc')->first();
        if (!$task) {
            $this->markTestSkipped('No completed task with its upload in.');
        }

        $response = $this->actingAs($this->staff())->postJson(route('admission.pending.task'), [
            'applicant' => $task->applicant_id,
            'recordid' => $task->id,
        ]);

        $response->assertOk();
        $this->assertSame('In Progress', $response->json('status'));
        $this->assertSame('In Progress', $task->fresh()->status);
    }

    public function test_assigned_staff_rows_use_portal_markup(): void
    {
        $taskList = TaskList::whereHas('users')->orderBy('id')->first();
        $this->assertNotNull($taskList, 'A task type with assigned staff is needed for this test.');

        $response = $this->actingAs($this->staff())->postJson(route('admission.process.task.users'), ['task_id' => $taskList->id, 'portal' => 1]);

        $response->assertOk();
        $this->assertStringContainsString('class="adp-namecell"', $response->json('res'));
        $this->assertStringNotContainsString('btn-success', $response->json('res'));
    }
}
