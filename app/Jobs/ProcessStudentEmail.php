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
use App\Models\ApplicantDocument;
use App\Models\ApplicantEmail;
use App\Models\ApplicantEmailsAttachment;
use App\Models\Student;
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\StudentDocument;
use App\Models\StudentEmail;
use App\Models\StudentEmailsAttachment;
use App\Models\StudentUser;

class ProcessStudentEmail implements ShouldQueue
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

        //Applicant Email
        $applicantSetData= ApplicantEmail::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            
            $dataArray = [
                'student_id' => $student->id,
                'common_smtp_id' => $applicantSet->comon_smtp_id,
                'email_template_id' => ($applicantSet->email_template_id) ?? NULL,
                'subject' => $applicantSet->subject,
                'body' => $applicantSet->body,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                
            ];
            
            $dataEmail = new StudentEmail();
            $dataEmail->fill($dataArray);
            $dataEmail->save();

            $applicantEmailAttachments = ApplicantEmailsAttachment::where("applicant_email_id",$applicantSet->id)->get();

            foreach($applicantEmailAttachments as $applicantEmailAttachmentFile)
                if($applicantEmailAttachmentFile->applicant_document_id) { 

                    $applicantDocument = ApplicantDocument::find($applicantEmailAttachmentFile->applicant_document_id);
                    $studentDocument = new StudentDocument();

                    $applicantArray = [
                        'student_id' => $student->id,
                        'hard_copy_check' => $applicantDocument->hard_copy_check,
                        'doc_type' => $applicantDocument->doc_type,
                        'disk_type' => $applicantDocument->disk_type,
                        'path' => $applicantDocument->path,
                        'display_file_name' =>	 $applicantDocument->display_file_name,
                        'current_file_name' => $applicantDocument->current_file_name,
                        'created_by'=> ($applicantDocument->updated_by) ? $applicantDocument->updated_by : $applicantDocument->created_by,
                    ];

                    if($applicantDocument->document_setting_id) {
                        $applicantArray = array_merge($applicantArray,['document_setting_id' => $applicantDocument->document_setting_id]);
                    }

                    $studentDocument->fill($applicantArray);
                    $studentDocument->save();
                    $dataArray = array_merge($dataArray,['student_document_id' => $studentDocument->id]);
                    $studentDocument->fill($dataArray);
                    $studentDocument->save();
            
                    //add the attachments to Email Attachment
                    $studentEmailAttachment = new StudentEmailsAttachment();
                    $emailAttachmentArray  = [
                        'student_email_id'=>$dataEmail->id, 
                        'student_document_id'=>$studentDocument->id, 
                        'created_by'=> ($applicantEmailAttachmentFile->updated_by) ? $applicantEmailAttachmentFile->updated_by : $applicantEmailAttachmentFile->created_by,
                    ];

                    $studentEmailAttachment->fill($emailAttachmentArray);
                    $studentEmailAttachment->save();
                
                }
        endforeach;


    }
}
