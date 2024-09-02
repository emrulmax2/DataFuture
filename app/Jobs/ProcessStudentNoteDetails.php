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
        $student = Student::where(["student_user_id"=> $user->id])->get()->first(); 

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
        //     if($note->document->document_setting_id) {
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
        //         'student_document_id' => $studentDocument->id,
        //         'note' => $note->note,
        //         'phase'=> 'Live',
        //         'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
        //     ];
        //     $studentNote->fill($applicantArray);
        //     $studentNote->save();
        //     unset ($applicantArray);
        // endforeach;
    }
}
