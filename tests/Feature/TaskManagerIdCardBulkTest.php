<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskDocument;
use App\Models\StudentTaskLog;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * "Download & Mark as Complete" in the Update Task Status menu (user, 28 Oct 2026): offered only on
 * student tasks with ID card = Yes. The cards are made in the browser and zipped there; each card is
 * uploaded to the student's task through task.manager.upload.document (as the Upload documents dialog
 * does), then the existing task.manager.update.task.status route completes those students.
 */
class TaskManagerIdCardBulkTest extends TestCase
{
    use DatabaseTransactions;

    private function liveTask(string $idCard): ?TaskList
    {
        return TaskList::where('id_card', $idCard)
            ->where('org_email', '!=', 'Yes')->where('pearson_reg', '!=', 'Yes')
            ->where('attendance_excuses', '!=', 'Yes')->where('address_request', '!=', 'Yes')
            ->whereHas('processlist', fn ($q) => $q->where('phase', 'Live'))
            ->orderBy('id')->first();
    }

    public function test_an_id_card_task_offers_download_and_mark_as_complete(): void
    {
        $task = $this->liveTask('Yes');
        if (!$task) {
            $this->markTestSkipped('No live task with ID card = Yes in this database.');
        }

        $this->actingAs(User::find(7))->get(route('task.manager.show', $task->id))
            ->assertOk()
            ->assertSee('data-tkm-act="bulk-idcards"', false)
            ->assertSee('Download &amp; Mark as Complete', false)
            ->assertSee('data-tkm-act="bulk-complete"', false);
    }

    /**
     * What the browser sends for each card before the zip: the PNG goes to the student's task the
     * same way the Upload documents dialog does, then the status update completes the task.
     */
    public function test_each_card_is_uploaded_to_the_students_task_then_completed(): void
    {
        $task = $this->liveTask('Yes');
        $studentTask = $task ? StudentTask::where('task_list_id', $task->id)->whereHas('student')->orderByDesc('id')->first() : null;
        if (!$studentTask) {
            $this->markTestSkipped('No student task on an ID card task in this database.');
        }
        Storage::fake('s3');
        $studentTask->update(['status' => 'Pending']);
        $student = Student::find($studentTask->student_id);
        $user = User::find(7);

        $this->actingAs($user)->post(route('task.manager.upload.document'), [
            'file' => UploadedFile::fake()->image($student->registration_no.'.png', 663, 1038),
            'student_id' => $student->id,
            'task_id' => $task->id,
            'phase' => 'Live',
            'display_file_name' => 'ID Card',
            'hard_copy_check' => 0,
        ], ['Accept' => 'application/json'])->assertOk();

        $link = StudentTaskDocument::where('student_task_id', $studentTask->id)->orderByDesc('id')->first();
        $this->assertNotNull($link);
        $doc = StudentDocument::find($link->student_document_id);
        $this->assertSame((int) $student->id, (int) $doc->student_id);
        $this->assertSame('ID Card - '.$task->name, $doc->display_file_name);
        $this->assertSame('png', $doc->doc_type);
        $this->assertStringEndsWith('_'.$student->registration_no.'.png', $doc->current_file_name);
        Storage::disk('s3')->assertExists('public/applicants/'.$student->applicant_id.'/'.$doc->current_file_name);
        $this->assertTrue(StudentTaskLog::where('student_tasks_id', $studentTask->id)->where('actions', 'Document')->where('current_field_value', $doc->id)->exists());

        $this->actingAs($user)->post(route('task.manager.update.task.status'), [
            'student_ids' => [$student->id],
            'task_id' => $task->id,
            'status' => 'Completed',
            'phase' => 'Live',
            'student_task_id' => 0,
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertSame('Completed', StudentTask::where('student_id', $student->id)->where('task_list_id', $task->id)->first()->status);
    }

    public function test_other_tasks_do_not(): void
    {
        $task = $this->liveTask('No');
        if (!$task) {
            $this->markTestSkipped('No live task without an ID card in this database.');
        }

        $this->actingAs(User::find(7))->get(route('task.manager.show', $task->id))
            ->assertOk()
            ->assertDontSee('bulk-idcards', false)
            ->assertSee('data-tkm-act="bulk-complete"', false);
    }
}
