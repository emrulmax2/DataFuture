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
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\ApplicantTaskDocument;
use App\Models\ApplicantDocument;
use App\Models\StudentDocument;
use App\Models\StudentTask;
use App\Models\StudentTaskDocument;
use App\Models\StudentUser;
use Illuminate\Support\Facades\Storage;

class ProcessStudentDocuments implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $applicant;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Applicant $applicant)
    {
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
        //--BEGIN: Student Document Sync
        $applicantTaskidList = [];
        $studentDocumentList = [];
    
        // Email attachments are not student documents: Copy Emails turns the attachments of the
        // emails it copies (live ones) into student_emails_documents rows, the student-side
        // storage, so skip exactly those here.
        $emailAttachmentDocIds = \App\Models\ApplicantEmailsAttachment::whereIn('applicant_email_id', \App\Models\ApplicantEmail::where('applicant_id', $this->applicant->id)->pluck('id'))
            ->whereNotNull('applicant_document_id')->pluck('applicant_document_id')->map(fn ($id) => (int) $id)->all();

        foreach($this->applicant->docses as $applicantDoc):
            if (in_array((int) $applicantDoc->id, $emailAttachmentDocIds, true)) {
                continue;
            }
            //Applicant Task wise Document capture

 
            $studentDocumentData = StudentDocument::where(['student_id'=>$student->id])->where(['display_file_name'=>$applicantDoc->display_file_name])->withTrashed()->get()->first();
            
            if(!isset($studentDocumentData->id)) {

                    $applicantDocument = ApplicantDocument::where(['applicant_id'=>$this->applicant->id])->where(['display_file_name'=>$applicantDoc->display_file_name])->withTrashed()->get()->first();                    
                    
                    if($applicantDocument!=null) {
                        $studentDocument = new StudentDocument();
                        $applicantArray = [
                            'student_id' => $student->id,
                            'hard_copy_check' => ($applicantDocument->hard_copy_check==1) ? 1 : 0,
                            'doc_type' => $applicantDocument->doc_type,
                            'disk_type' => $applicantDocument->disk_type,
                            'path' => $applicantDocument->path,
                            'display_file_name' =>	 $applicantDocument->display_file_name,
                            'current_file_name' => $applicantDocument->current_file_name,
                            'created_by'=> ($applicantDocument->updated_by) ? $applicantDocument->updated_by : $applicantDocument->created_by,
                            'deleted_at' => $applicantDocument->deleted_at!=null ? $applicantDocument->deleted_at : null,
                        ];

                        if($applicantDocument->document_setting_id) {
                            $applicantArray = array_merge($applicantArray,['document_setting_id' => $applicantDocument->document_setting_id]);
                        }
                        $studentDocument->fill($applicantArray);

                        $studentDocument->save();

                    }
                
            }
        endforeach;
        //--END: Student Document Sync
    }
}
