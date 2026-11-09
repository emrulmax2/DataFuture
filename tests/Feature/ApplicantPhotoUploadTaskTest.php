<?php

namespace Tests\Feature;

use App\Jobs\TaskAssignment\UpdateApplicantPhotoJob;
use App\Models\Applicant;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskLog;
use App\Models\TaskAssignmentRule;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * A photo uploaded from the applicant record (#addApplicantPhotoModal) becomes the applicant
 * photo and is filed in the applicant's photo task (the one whose rule runs "Update student
 * photo"): added to its uploads, and the task completed when it is still open (user, 28 Oct 2026).
 */
class ApplicantPhotoUploadTaskTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('s3');
        // only this test's rule carries the action (e.g. local rule 832 would match real tasks)
        TaskAssignmentRule::where('after_assign_job', UpdateApplicantPhotoJob::class)->update(['after_assign_job' => null]);
    }

    private function applicant(): Applicant
    {
        $applicant = Applicant::whereNull('deleted_at')->whereHas('users')->orderByDesc('id')->first();
        $this->assertNotNull($applicant);
        $applicant->photo = null;
        $applicant->save();

        return $applicant;
    }

    /** The applicant's photo task, assigned by a rule with the action. */
    private function photoTask(Applicant $applicant, string $status): ApplicantTask
    {
        $list = TaskList::orderBy('id')->first();
        $rule = TaskAssignmentRule::create(['task_list_id' => $list->id, 'trigger' => 'application_submitted', 'priority' => 50, 'active' => 0, 'after_assign_job' => UpdateApplicantPhotoJob::class, 'created_by' => 1]);

        return ApplicantTask::create(['applicant_id' => $applicant->id, 'task_list_id' => $list->id, 'status' => $status, 'assigned_via' => 'auto', 'rule_id' => $rule->id, 'created_by' => 1]);
    }

    private function upload(Applicant $applicant, string $name = 'face.jpg')
    {
        return $this->actingAs(User::find(7))->post(route('admission.upload.photo'), [
            'applicant_id' => $applicant->id,
            'file' => UploadedFile::fake()->image($name, 300, 400),
        ], ['Accept' => 'application/json'])->assertOk();
    }

    public function test_an_open_photo_task_is_completed_with_the_photo_in_its_uploads(): void
    {
        $applicant = $this->applicant();
        $task = $this->photoTask($applicant, 'Pending');

        $response = $this->upload($applicant);

        $photo = $applicant->fresh()->photo;
        $this->assertStringEndsWith('_face.jpg', (string) $photo);
        Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$photo);
        $this->assertSame('Completed', $task->fresh()->status);
        $doc = $task->fresh()->documents()->first();
        $this->assertNotNull($doc);
        $this->assertSame($task->task->name, $doc->display_file_name);
        $this->assertSame('local', $doc->disk_type); // S3 on the servers
        Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$doc->current_file_name);
        $this->assertNotSame($photo, $doc->current_file_name);
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Document')->where('current_field_value', $doc->id)->exists());
        $this->assertTrue(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Status Changed')->where('current_field_value', 'Completed')->exists());
        // the completion action is not run on top (the photo is the one just uploaded)
        $this->assertFalse(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Applicant photo updated')->exists());
        $this->assertStringContainsString('task is now complete', (string) $response->json('task_note'));
    }

    public function test_a_completed_photo_task_just_gets_the_upload(): void
    {
        $applicant = $this->applicant();
        $task = $this->photoTask($applicant, 'Completed');

        $response = $this->upload($applicant);

        $this->assertNotNull($applicant->fresh()->photo);
        $this->assertSame('Completed', $task->fresh()->status);
        $this->assertSame(1, $task->fresh()->documents()->count());
        $this->assertFalse(ApplicantTaskLog::where('applicant_tasks_id', $task->id)->where('actions', 'Status Changed')->exists());
        $this->assertStringContainsString('added to the uploads', (string) $response->json('task_note'));
    }

    public function test_a_new_photo_replaces_the_old_one_but_the_task_keeps_both_uploads(): void
    {
        $applicant = $this->applicant();
        $task = $this->photoTask($applicant, 'Pending');

        $this->upload($applicant, 'first.jpg');
        $first = $applicant->fresh()->photo;
        $this->upload($applicant, 'second.png');

        Storage::disk('local')->assertMissing('public/applicants/'.$applicant->id.'/'.$first);
        $this->assertSame(2, $task->fresh()->documents()->count());
        foreach ($task->fresh()->documents as $doc) {
            Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$doc->current_file_name);
        }
    }

    public function test_without_a_photo_task_only_the_photo_changes(): void
    {
        $applicant = $this->applicant();

        $response = $this->upload($applicant);

        $this->assertNotNull($applicant->fresh()->photo);
        $this->assertNull($response->json('task_note'));
    }
}
