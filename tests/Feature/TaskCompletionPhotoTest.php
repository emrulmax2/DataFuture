<?php

namespace Tests\Feature;

use App\Jobs\ProcessStudents;
use App\Jobs\TaskAssignment\UpdateApplicantPhotoJob;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantTaskLog;
use App\Models\Student;
use App\Models\StudentUser;
use App\Models\TaskAssignmentRule;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task Automation › Applicant action "Update student photo" (runs when the task is completed):
 * the latest image uploaded to the task becomes the applicant photo, and the conversion
 * carries it to the student (user, 27 Oct 2026).
 */
class TaskCompletionPhotoTest extends TestCase
{
    use DatabaseTransactions;

    /** 1x1 PNG */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('s3');
    }

    /** An applicant whose login has a student user (so the conversion's first step can run). */
    private function applicant(): Applicant
    {
        $applicant = Applicant::with('users')->whereHas('users')->whereHas('course')->whereNull('deleted_at')->orderByDesc('id')->get()
            ->first(fn ($a) => StudentUser::where('email', $a->users->email)->exists());
        $this->assertNotNull($applicant);
        $applicant->photo = null;
        $applicant->save();

        return $applicant;
    }

    /** A rule with the photo action on a task list that is deliberately not "Applicant Photo Upload". */
    private function rule(int $taskListId, bool $active = false): TaskAssignmentRule
    {
        return TaskAssignmentRule::create(['task_list_id' => $taskListId, 'trigger' => 'application_submitted', 'priority' => 50, 'active' => $active ? 1 : 0, 'after_assign_job' => UpdateApplicantPhotoJob::class, 'created_by' => 1]);
    }

    private function task(Applicant $applicant, int $taskListId, ?int $ruleId): ApplicantTask
    {
        return ApplicantTask::create(['applicant_id' => $applicant->id, 'task_list_id' => $taskListId, 'status' => 'Pending', 'assigned_via' => $ruleId ? 'auto' : 'manual', 'rule_id' => $ruleId, 'created_by' => 1]);
    }

    private function upload(ApplicantTask $task, string $name, string $type, ?string $bytes = null): ApplicantDocument
    {
        $doc = ApplicantDocument::create(['applicant_id' => $task->applicant_id, 'hard_copy_check' => 0, 'doc_type' => $type, 'disk_type' => 'local', 'path' => 'x', 'display_file_name' => $name, 'current_file_name' => $name, 'created_by' => 1]);
        ApplicantTaskDocument::create(['applicant_task_id' => $task->id, 'applicant_document_id' => $doc->id, 'created_by' => 1]);
        Storage::disk('local')->put('public/applicants/'.$task->applicant_id.'/'.$name, $bytes ?? base64_decode(self::PNG));

        return $doc;
    }

    private function complete(ApplicantTask $task)
    {
        return $this->actingAs(User::find(7))->postJson(route('admission.completed.task'), ['applicant' => $task->applicant_id, 'recordid' => $task->id])->assertOk();
    }

    public function test_completing_the_task_sets_the_latest_uploaded_image_as_the_photo_and_the_conversion_carries_it(): void
    {
        $applicant = $this->applicant();
        $listId = (int) \App\Models\TaskList::orderBy('id')->value('id');
        $task = $this->task($applicant, $listId, $this->rule($listId)->id);
        $this->upload($task, 'first-photo.png', 'png');
        $latest = $this->upload($task, 'second-photo.PNG', 'PNG');
        $this->upload($task, 'notes.pdf', 'pdf', '%PDF-1 not a photo'); // newest, but not an image

        $response = $this->complete($task);

        $this->assertStringContainsString('The applicant photo was updated from "second-photo.PNG"', implode(' ', $response->json('action_notes')));
        $photo = $applicant->fresh()->photo;
        $this->assertMatchesRegularExpression('/^\d+_photo_'.$latest->id.'\.png$/', (string) $photo);
        Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$photo);
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertTrue(ApplicantArchive::where('applicant_id', $applicant->id)->where('field_name', 'photo')->where('field_new_value', $photo)->exists());
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Applicant photo updated')->exists());

        // the conversion's first step: the student gets the same photo, on the local disk
        (new ProcessStudents($applicant->fresh()))->handle();
        $student = Student::where('applicant_id', $applicant->id)->orderByDesc('id')->firstOrFail();
        $this->assertSame($photo, $student->photo);
        Storage::disk('local')->assertExists('public/students/'.$student->id.'/'.$photo);
    }

    public function test_without_an_image_the_task_completes_and_the_photo_is_left_alone(): void
    {
        $applicant = $this->applicant();
        $listId = (int) \App\Models\TaskList::orderBy('id')->value('id');
        $task = $this->task($applicant, $listId, $this->rule($listId)->id);
        $this->upload($task, 'scan.pdf', 'pdf', '%PDF-1 scan');

        $response = $this->complete($task);

        $this->assertStringContainsString('No image', implode(' ', $response->json('action_notes')));
        $this->assertNull($applicant->fresh()->photo);
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Applicant photo not updated')->exists());
    }

    public function test_a_task_added_by_hand_runs_the_action_of_an_active_rule_for_the_same_task(): void
    {
        $applicant = $this->applicant();
        $listId = (int) \App\Models\TaskList::orderByDesc('id')->value('id');
        $this->rule($listId, true);
        $task = $this->task($applicant, $listId, null);
        $this->upload($task, 'hand.jpg', 'jpg');

        $this->complete($task);

        $this->assertNotNull($applicant->fresh()->photo);
    }

    public function test_tasks_without_the_action_complete_as_before(): void
    {
        $applicant = $this->applicant();
        // a task list no rule with the photo action points at
        $listId = (int) \App\Models\TaskList::whereNotIn('id', TaskAssignmentRule::where('after_assign_job', UpdateApplicantPhotoJob::class)->pluck('task_list_id'))->orderBy('id')->value('id');
        $task = $this->task($applicant, $listId, null);
        $this->upload($task, 'photo.png', 'png');

        $response = $this->complete($task);

        $this->assertSame([], $response->json('action_notes'));
        $this->assertNull($applicant->fresh()->photo);
    }

    public function test_the_action_is_offered_under_when_the_task_is_completed(): void
    {
        $html = $this->actingAs(User::find(7))->get(route('taskrules'))->assertOk()->getContent();

        $this->assertStringContainsString('<optgroup label="When the task is completed">', $html);
        $this->assertStringContainsString('value="'.e(UpdateApplicantPhotoJob::class).'"', $html);
    }
}
