<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Applicant;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\StudentDocument;
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\ApplicantNote;
use App\Models\ApplicantDocument;
use App\Models\StudentUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStudentNoteDetails implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $applicant;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( 
        Applicant $applicant
    ){
        $this->applicant = $applicant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $ApplicantUser = ApplicantUser::find($this->applicant->applicant_user_id);
        $user = StudentUser::where(["email"=> $ApplicantUser->email])->get()->first();
        // $student = Student::where(["student_user_id"=> $user->id])->get()->first(); // the login's first student, not the one this conversion created
        $student = \App\Services\Admission\StudentConversion::studentFor($this->applicant, $user);

        /*
         * Copy Notes, brought back (23 Oct 2026). The block commented out below stopped working in
         * Aug 2024, when student_notes.student_document_id was dropped and note attachments moved to
         * student_notes_documents; it was switched off rather than updated, so no applicant note has
         * reached a student since. Same steps as the old code, on today's tables:
         *  - one student_notes row per live applicant note (phase Live, as the old code and every
         *    existing student note), keeping the note's author and dates;
         *  - its attachment as a student_notes_documents row, as Student\NoteController::store saves
         *    one; the file is in public/students/{id}/, copied there by Create Student Record.
         * Copy Documents still copies the attachment as a student document too, as the old code did.
         * All or nothing, so a retry cannot duplicate notes.
         */
        DB::transaction(function () use ($student) {
            foreach ($this->applicant->notes as $note):
                $studentNote = new StudentNote();
                $studentNote->fill([
                    'student_id' => $student->id,
                    'note' => $note->note,
                    'phase' => 'Live',
                    'followed_up' => 'no',
                    'is_flaged' => 'No',
                    'student_flag_id' => 0,
                    'created_by' => $note->created_by ?: (($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by),
                    'updated_by' => $note->updated_by,
                ]);
                $studentNote->created_at = $note->created_at;
                $studentNote->updated_at = $note->updated_at;
                $studentNote->save();

                $document = $note->applicant_document_id ? ApplicantDocument::withTrashed()->find($note->applicant_document_id) : null;
                if ($document) {
                    \App\Models\StudentNotesDocument::create([
                        'student_id' => $student->id,
                        'student_note_id' => $studentNote->id,
                        'hard_copy_check' => ($document->hard_copy_check > 0 ? $document->hard_copy_check : 0),
                        'doc_type' => $document->doc_type,
                        'disk_type' => $document->disk_type,
                        'path' => \Illuminate\Support\Facades\Storage::disk(\App\Support\ApplicantDocumentDisk::of($document))->url('public/students/'.$student->id.'/'.$document->current_file_name),
                        'display_file_name' => $document->display_file_name,
                        'current_file_name' => $document->current_file_name,
                        'created_by' => ($document->updated_by) ? $document->updated_by : $document->created_by,
                    ]);
                }
            endforeach;
        });

        // foreach ($this->applicant->notes as $note):
            
        //     $note = ApplicantNote::find($note->id);

        //     $studentDocument = new StudentDocument();
        //     //DB::enableQueryLog();

        //     $applicantArray = [
        //         'student_id' => $student->id,
        //         'hard_copy_check' => $note->document->hard_copy_check,
        //         'doc_type' => $note->document->doc_type,
        //         'disk_type' => $note->document->disk_type,
        //         'path' => $note->document->path,
        //         'display_file_name' =>	 $note->document->display_file_name,
        //         'current_file_name' => $note->document->current_file_name,
        //         'created_by'=> ($note->document->updated_by) ? $note->document->updated_by : $note->document->created_by,
        //     ];
        //     if(isset($note->document) && isset($note->document->document_setting_id)) {
        //         $applicantArray = array_merge($applicantArray,['document_setting_id' => $note->document->document_setting_id]);
        //     }
        //     $studentDocument->fill($applicantArray);

        //     $studentDocument->save();
        //     //$queries = DB::getQueryLog();

        //     // Log::debug($queries);

        //     unset ($applicantArray);
        //     $studentNote = new StudentNote();
        //     $applicantArray = [
        //         'student_id' => $student->id,
        //         'note' => $note->note,
        //         'phase'=> 'Admission',
        //         'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
        //     ];
        //     $studentNote->fill($applicantArray);
        //     $studentNote->save();
        //     unset ($applicantArray);
        // endforeach;
    }
}
