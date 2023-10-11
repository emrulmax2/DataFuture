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
use App\Models\ApplicantLetter;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentLetter;
use App\Models\StudentDocument;
use App\Models\StudentUser;
use App\Models\User;

class ProcessStudentLetter implements ShouldQueue
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

        //Begin
        $applicantSetData = ApplicantLetter::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            
            $dataArray = [
                'student_id' => $student->id,
                'applicant_letter_id' =>$applicantSet->id,
                'is_email_or_attachment' => isset($applicantSet->is_email_or_attachment) ?? 0,
                'issued_by' => ($applicantSet->issued_by) ? $applicantSet->issued_by : $this->applicant->created_by,
                'issued_date' => ($applicantSet->issued_date) ? $applicantSet->issued_date : $this->applicant->created_at,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            if($applicantSet->letter_set_id) {
                
                $dataArray = array_merge($dataArray,['letter_set_id' => $applicantSet->letter_set_id]);
            
            }

            if($applicantSet->signatory_id) {
                $dataArray = array_merge($dataArray,['signatory_id' => $applicantSet->signatory_id]);
            }
            if($applicantSet->comon_smtp_id) {
                $dataArray = array_merge($dataArray,['comon_smtp_id' => $applicantSet->comon_smtp_id]);
            }
            $applicantDocument = ApplicantDocument::find($applicantSet->applicant_document_id);

            if($applicantSet->applicant_document_id) {

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
            }
            
            $data = new StudentLetter();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;
       

    }
}
