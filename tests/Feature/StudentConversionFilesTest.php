<?php

namespace Tests\Feature;

use App\Jobs\ProcessStudentEmail;
use App\Jobs\ProcessStudents;
use App\Jobs\ProcessStudentTaskDocument;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantTask;
use App\Models\ApplicantTaskDocument;
use App\Models\StudentUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * On a local / testing install the conversion copies the applicant's files on the
 * local disk (ApplicantDocumentDisk) instead of S3, which has no working key there
 * and used to stop every local conversion at "Create Student Record".
 */
class StudentConversionFilesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_student_record_and_task_documents_copy_on_the_local_disk(): void
    {
        // An applicant whose login already has a student user (the conversion's first step).
        $applicant = Applicant::with('users')->whereHas('users')->whereHas('course')->orderByDesc('id')->get()
            ->first(fn ($a) => StudentUser::where('email', $a->users->email)->exists());
        $this->assertNotNull($applicant, 'needs an applicant with a student user');
        Storage::fake('local');
        Storage::fake('s3');
        $folder = 'public/applicants/'.$applicant->id;
        Storage::disk('local')->put($folder.'/passport.pdf', '%PDF-1 passport');

        // a task document stored locally (as the process board stores them here)
        $task = ApplicantTask::create(['applicant_id' => $applicant->id, 'task_list_id' => 1, 'status' => 'Completed', 'created_by' => 1]);
        $doc = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'disk_type' => 'local', 'path' => 'x', 'display_file_name' => 'Task file', 'current_file_name' => 'task-file.pdf', 'created_by' => 1]);
        ApplicantTaskDocument::create(['applicant_task_id' => $task->id, 'applicant_document_id' => $doc->id, 'created_by' => 1]);
        Storage::disk('local')->put($folder.'/task-file.pdf', '%PDF-1 task');
        // and an old one on S3 (no disk_type): skipped locally, not fatal
        $old = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'disk_type' => null, 'path' => 'y', 'display_file_name' => 'Old file', 'current_file_name' => 'old-file.pdf', 'created_by' => 1]);
        ApplicantTaskDocument::create(['applicant_task_id' => $task->id, 'applicant_document_id' => $old->id, 'created_by' => 1]);

        (new ProcessStudents($applicant))->handle();
        $student = \App\Models\Student::where('applicant_id', $applicant->id)->orderByDesc('id')->firstOrFail();
        Storage::disk('local')->assertExists('public/students/'.$student->id.'/passport.pdf');

        // Copy Task Documents needs the student's tasks (Copy Tasks runs before it).
        \App\Models\StudentTask::create(['student_id' => $student->id, 'applicant_task_id' => $task->id, 'task_list_id' => 1, 'status' => 'Completed', 'created_by' => 1]);
        (new ProcessStudentTaskDocument($applicant))->handle();
        Storage::disk('local')->assertExists('public/students/'.$student->id.'/task-file.pdf');

        $this->assertSame([], Storage::disk('s3')->allFiles());
    }

    public function test_emailed_attachments_become_student_email_documents_not_student_documents(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('student_emails_attachments'));
        $applicant = Applicant::with('users')->whereHas('users')->orderByDesc('id')->get()
            ->first(fn ($a) => ($u = StudentUser::where('email', $a->users->email)->first()) && \App\Models\Student::where('student_user_id', $u->id)->exists());
        $this->assertNotNull($applicant, 'needs an applicant whose login has a student');
        // the student the conversion steps fill (StudentConversion::studentFor)
        $student = \App\Services\Admission\StudentConversion::studentFor($applicant, StudentUser::where('email', $applicant->users->email)->first());

        // an email sent from the admission communication page with one attachment, and a normal upload
        $email = \App\Models\ApplicantEmail::create(['applicant_id' => $applicant->id, 'comon_smtp_id' => 1, 'subject' => 'Offer pack', 'body' => '<p>Hello</p>', 'created_by' => 1]);
        $attachment = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'disk_type' => 's3', 'path' => 'p', 'display_file_name' => '1700000000_offer-pack.pdf', 'current_file_name' => '1700000000_offer-pack.pdf', 'created_by' => 7]);
        \App\Models\ApplicantEmailsAttachment::create(['applicant_email_id' => $email->id, 'applicant_document_id' => $attachment->id, 'created_by' => 7]);
        $upload = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 1, 'doc_type' => 'pdf', 'disk_type' => 's3', 'path' => 'q', 'display_file_name' => 'Proof of ID - Passport '.uniqid(), 'current_file_name' => 'passport-'.uniqid().'.pdf', 'created_by' => 7]);

        (new ProcessStudentEmail($applicant))->handle();

        $studentEmail = \App\Models\StudentEmail::where('student_id', $student->id)->where('subject', 'Offer pack')->latest('id')->firstOrFail();
        $row = \App\Models\StudentEmailsDocument::where('student_email_id', $studentEmail->id)->sole();
        $this->assertSame($student->id, (int) $row->student_id);
        $this->assertSame(0, (int) $row->is_bulk);
        $this->assertSame('1700000000_offer-pack.pdf', $row->current_file_name);
        $this->assertSame('1700000000_offer-pack.pdf', $row->display_file_name);
        $this->assertStringContainsString('public/students/'.$student->id.'/1700000000_offer-pack.pdf', $row->path);
        // the student email screen's list reads it
        $this->assertContains('1700000000_offer-pack.pdf', $studentEmail->fresh()->document_list);
        // ... and it is not a student document
        $this->assertFalse(\App\Models\StudentDocument::where('student_id', $student->id)->where('current_file_name', '1700000000_offer-pack.pdf')->exists());

        (new \App\Jobs\ProcessStudentDocuments($applicant))->handle();

        $this->assertFalse(\App\Models\StudentDocument::where('student_id', $student->id)->where('current_file_name', '1700000000_offer-pack.pdf')->exists());
        $this->assertTrue(\App\Models\StudentDocument::where('student_id', $student->id)->where('current_file_name', $upload->current_file_name)->exists());
    }

    public function test_copy_notes_brings_applicant_notes_and_their_attachments_to_the_student(): void
    {
        $applicant = Applicant::with('users')->whereHas('users')->orderByDesc('id')->get()
            ->first(fn ($a) => ($u = StudentUser::where('email', $a->users->email)->first()) && \App\Models\Student::where('student_user_id', $u->id)->exists());
        $this->assertNotNull($applicant, 'needs an applicant whose login has a student');
        // the student the conversion steps fill (StudentConversion::studentFor)
        $student = \App\Services\Admission\StudentConversion::studentFor($applicant, StudentUser::where('email', $applicant->users->email)->first());
        $before = \App\Models\StudentNote::where('student_id', $student->id)->count();

        $file = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 0, 'doc_type' => 'png', 'disk_type' => 's3', 'path' => 'p', 'display_file_name' => '1792684338_sidebar_logo.png', 'current_file_name' => '1792684338_sidebar_logo.png', 'created_by' => 7]);
        $withFile = \App\Models\ApplicantNote::create(['applicant_id' => $applicant->id, 'applicant_document_id' => $file->id, 'note' => '<p>Interview went well.</p>', 'phase' => 'Admission', 'created_by' => 7]);
        $withFile->forceFill(['created_at' => '2026-10-22 16:47:00', 'updated_at' => '2026-10-22 16:52:00'])->save();
        $plain = \App\Models\ApplicantNote::create(['applicant_id' => $applicant->id, 'note' => '<p>Called the applicant.</p>', 'phase' => 'Admission', 'created_by' => 1]);
        $deleted = \App\Models\ApplicantNote::create(['applicant_id' => $applicant->id, 'note' => '<p>Deleted note.</p>', 'phase' => 'Admission', 'created_by' => 1]);
        $deleted->delete();

        (new \App\Jobs\ProcessStudentNoteDetails($applicant))->handle();

        $notes = \App\Models\StudentNote::where('student_id', $student->id)->orderBy('id')->get()->slice($before)->values();
        $copied = $notes->whereIn('note', ['<p>Interview went well.</p>', '<p>Called the applicant.</p>']);
        $this->assertCount(2, $copied);
        $this->assertFalse($notes->contains('note', '<p>Deleted note.</p>'));
        $good = $copied->firstWhere('note', '<p>Interview went well.</p>');
        $this->assertSame('Live', $good->phase);
        $this->assertSame(7, (int) $good->created_by);
        $this->assertSame('2026-10-22 16:47:00', $good->created_at->format('Y-m-d H:i:s'));
        $doc = \App\Models\StudentNotesDocument::where('student_note_id', $good->id)->sole();
        $this->assertSame('1792684338_sidebar_logo.png', $doc->current_file_name);
        $this->assertStringContainsString('public/students/'.$student->id.'/1792684338_sidebar_logo.png', $doc->path);
        $this->assertFalse(\App\Models\StudentNotesDocument::where('student_note_id', $copied->firstWhere('note', '<p>Called the applicant.</p>')->id)->exists());

        // the student notes page lists them, the first with its attachment
        $staff = \App\Models\User::where('email', 'sakib@lcc.ac.uk')->first() ?? \App\Models\User::where('active', 1)->first();
        $list = collect($this->actingAs($staff)->getJson(route('student.note.list', ['studentId' => $student->id, 'size' => 100]))->assertOk()->json('data'));
        $row = $list->firstWhere('id', $good->id);
        $this->assertNotNull($row);
        $this->assertSame($doc->id, (int) $row['note_document_id']);
        $this->assertNotNull($list->firstWhere('id', $copied->firstWhere('note', '<p>Called the applicant.</p>')->id));
    }
}
