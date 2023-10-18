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
use App\Models\ApplicantInterview;
use App\Models\ApplicantKin;
use App\Models\ApplicantTask;
use App\Models\Student;
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\StudentDocument;
use App\Models\StudentInterview;
use App\Models\StudentKin;
use App\Models\StudentTask;
use App\Models\StudentUser;

class ProcessStudentInterview implements ShouldQueue
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

        //Student Interview
        $applicantSetData= ApplicantInterview::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            $dataArray = [
                'student_id' => $student->id,
                'user_id' => $applicantSet->user_id,	
                'interview_date' =>	$applicantSet ->interview_date,
                'start_time' =>	date("H:i", strtotime($applicantSet->start_time)),
                'end_time' =>	date("H:i", strtotime($applicantSet->end_time)) ,
                'interview_result' =>	$applicantSet->interview_result,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                
            ];

            if($applicantSet->applicant_task_id) {
                $applicantTaskData =ApplicantTask::find($applicantSet->applicant_task_id);

                $dataSet = new StudentTask();

                $applicantTaskArray = [
                    'student_id' => $student->id,
                    'applicant_task_id' =>$applicantTaskData->id,
                    'task_list_id' => $applicantTaskData->task_list_id,
                    'external_link_ref'=> isset($applicantTaskData->external_link_ref) ? ($applicantTaskData->external_link_ref) : 'NULL',
                    'status'=> isset($applicantTaskData->status) ? ($applicantTaskData->status) : NULL,
                    'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                ];
                if($applicantTaskData->task_status_id) {
                    $applicantTaskArray = array_merge($applicantTaskArray,['task_status_id' => $applicantTaskData->task_status_id]);
                }
                
                $dataSet->fill($applicantTaskArray);
                $dataSet->save();

                $dataArray = array_merge($dataArray, ["student_task_id"=>$dataSet->id]);
            }
            if($applicantSet->applicant_document_id) { 

                $applicantDocument = ApplicantDocument::find($applicantSet->applicant_document_id);
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
            $data = new StudentInterview();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
