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
use App\Models\StudentEmailsDocument;
use Illuminate\Support\Facades\Storage;
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
        // $student = Student::where(["student_user_id"=> $user->id])->get()->first(); // the login's first student, not the one this conversion created
        $student = \App\Services\Admission\StudentConversion::studentFor($this->applicant, $user);

        //Applicant Email
        // All or nothing: a failure part-way used to leave half the emails copied, so a
        // retry of this step duplicated them.
        \Illuminate\Support\Facades\DB::transaction(function () use ($student) {
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

                    $applicantDocument = ApplicantDocument::where('id',$applicantEmailAttachmentFile->applicant_document_id)->withTrashed()->get()->first();
                    if (!$applicantDocument) {
                        continue;
                    }

                    /* Students keep an email's attachments as student_emails_documents rows only
                       (Student\EmailController::SendMail), never as student documents; the file is in
                       public/students/{id}/, where Create Student Record copied the applicant's folder
                       and where the student email screen downloads it from. Same row shape here. */
                    $docDisk = \App\Support\ApplicantDocumentDisk::of($applicantDocument);
                    StudentEmailsDocument::create([
                        'student_id' => $student->id,
                        'student_email_id' => $dataEmail->id,
                        'is_bulk' => 0,
                        'hard_copy_check' => ($applicantDocument->hard_copy_check > 0 ? $applicantDocument->hard_copy_check : 0),
                        'doc_type' => $applicantDocument->doc_type,
                        'disk_type' => $applicantDocument->disk_type,
                        'path' => Storage::disk($docDisk)->url('public/students/'.$student->id.'/'.$applicantDocument->current_file_name),
                        'display_file_name' => $applicantDocument->display_file_name,
                        'current_file_name' => $applicantDocument->current_file_name,
                        'created_by' => ($applicantDocument->updated_by) ? $applicantDocument->updated_by : $applicantDocument->created_by,
                        'deleted_at' => $applicantDocument->deleted_at != null ? $applicantDocument->deleted_at : null,
                    ]);

                    /* Was: the attachment also became a student document (it then showed among the
                       student's uploaded documents) before the email document was saved. Kept for
                       reference, not run:
                    $studentDocument = new StudentDocument();

                    $applicantArray = [
                    'student_id' => $student->id,
                    'hard_copy_check' => ($applicantDocument->hard_copy_check > 0 ? $applicantDocument->hard_copy_check : 0),
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
                    $dataArray = array_merge($dataArray,['student_document_id' => $studentDocument->id]);
                    $studentDocument->fill($dataArray);
                    $studentDocument->save();


                    $applicantArray = array_merge($applicantArray,['student_email_id' => $dataEmail->id]);
                    $studentEmailDocument = new StudentEmailsDocument();
                    $studentEmailDocument->fill($applicantArray);
                    $studentEmailDocument->save();
                    */

                    /* The attachment is recorded above as a student_emails_documents row, which the
                       student email screens read (StudentEmail::documents). The old link table,
                       student_emails_attachments, no longer exists in the database (replaced by
                       student_emails_documents in 2024), so writing to it stopped every conversion
                       of an applicant who had been emailed an attachment. Kept for reference, not run:
                    $studentEmailAttachment = new StudentEmailsAttachment();
                    $emailAttachmentArray  = [
                        'student_email_id'=>$dataEmail->id, 
                        'student_document_id'=>$studentDocument->id, 
                        'created_by'=> ($applicantEmailAttachmentFile->updated_by) ? $applicantEmailAttachmentFile->updated_by : $applicantEmailAttachmentFile->created_by,
                        'deleted_at' => $applicantDocument->deleted_at!=null ? $applicantDocument->deleted_at : null,
                    ];

                    $studentEmailAttachment->fill($emailAttachmentArray);
                    $studentEmailAttachment->save();
                    */
                
                }
        endforeach;
        });


    }
}
