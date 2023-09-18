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
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\ApplicantTask;
use App\Models\StudentTask; 
use App\Models\ApplicantTaskDocument;
use App\Models\StudentTaskDocument; 
use App\Models\StudentDocument; 

class ProcessStudentTask implements ShouldQueue
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
        $user = User::where(["email"=> $ApplicantUser->email])->get()->first();
        $student = Student::where(["user_id"=> $user->id])->get()->first();        
        //ApplicantTask
        $applicantTaskList = ApplicantTask::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantTaskList as $applicantTaskData):
            $applicantTaskArray = [
                'student_id' => $student->id,
                'task_list_id' => $applicantTaskData->task_list_id,
                'external_link_ref'=> isset($applicantTaskData->external_link_ref) ? ($applicantTaskData->external_link_ref) : 'NULL',
                'status'=> isset($applicantTaskData->status) ? ($applicantTaskData->status) : NULL,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];
            if($applicantTaskData->task_status_id) {
                array_merge($applicantTaskArray,['task_status_id' => $applicantTaskData->task_status_id]);
            }
            $dataTask = new StudentTask();
            $dataTask->fill($applicantTaskArray);
            $dataTask->save();
            //Applicant Task wise Document capture
            $applicantTaskDocumentData = ApplicantTaskDocument::where(['applicant_task_id'=>$applicantTaskData->id])->get();
            foreach($applicantTaskDocumentData as $applicantTaskDocument):
                $applicantDocument = ApplicantDocument::find($applicantTaskDocument->applicant_document_id);
                //find the document and put it in student document
                // then insert it into studentDocument and applicantTaskDocument
                $studentDocument = new StudentDocument();
                //DB::enableQueryLog();

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
                    array_merge($applicantArray,['document_setting_id' => $applicantDocument->document_setting_id]);
                }
                $studentDocument->fill($applicantArray);

                $studentDocument->save();
                //endDocuemnt saved
                $applicantTaskDocArray = [
                    'student_id' => $student->id,
                    'student_task_id' => $dataTask->id,
                    'student_document_id' => $studentDocument->id,
                    'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                ];

                $data = new StudentTaskDocument();

                $data->fill($applicantTaskDocArray);
                $dataTask->save();
                unset ($applicantTaskArray);
            endforeach;
        endforeach;

        unset ($applicantTaskArray);

    }
}
