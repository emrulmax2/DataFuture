<?php

namespace Tests\Feature;

use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\User;
use App\Support\ApplicantDocumentDisk;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task documents go to the local disk on a local/testing install and are
 * served back from it; S3 stays the disk everywhere else and for rows that
 * were written before disk_type was recorded.
 */
class AdmissionTaskDocumentDiskTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    public function test_a_local_install_uses_the_local_disk(): void
    {
        $this->assertSame('local', ApplicantDocumentDisk::name());
    }

    public function test_a_task_document_is_stored_locally_and_served_from_storage(): void
    {
        Storage::fake('local');
        $task = ApplicantTask::whereHas('task')->orderBy('id', 'desc')->first();
        $this->assertNotNull($task);

        $response = $this->actingAs($this->staff())->post(route('admission.upload.task.documents'), [
            'applicant_id' => $task->applicant_id,
            'applicant_task_id' => $task->id,
            'display_file_name' => 'Disk test',
            'hard_copy_check' => 0,
            'file' => UploadedFile::fake()->create('disk-test.pdf', 12, 'application/pdf'),
        ]);

        $response->assertOk();
        $link = ApplicantTaskDocument::where('applicant_task_id', $task->id)->orderBy('id', 'desc')->first();
        $this->assertNotNull($link);
        $doc = ApplicantDocument::find($link->applicant_document_id);
        $this->assertSame('local', $doc->disk_type);
        Storage::disk('local')->assertExists('public/applicants/'.$task->applicant_id.'/'.$doc->current_file_name);

        $download = $this->actingAs($this->staff())->postJson(route('admission.document.download'), ['row_id' => $doc->id]);
        $download->assertOk();
        $this->assertStringContainsString('/storage/applicants/'.$task->applicant_id.'/'.$doc->current_file_name, $download->json('res'));
        $this->assertStringStartsWith('http', $download->json('res'));
    }

    public function test_rows_without_a_recorded_disk_still_point_at_s3(): void
    {
        $doc = new ApplicantDocument(['applicant_id' => 1, 'current_file_name' => 'legacy.pdf']);

        $this->assertSame('s3', ApplicantDocumentDisk::of($doc));
        $this->assertSame('local', ApplicantDocumentDisk::of(new ApplicantDocument(['disk_type' => 'local'])));
    }
}
