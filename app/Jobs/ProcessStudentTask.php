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
use App\Models\StudentUser;

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
        $user = StudentUser::where(["email"=> $ApplicantUser->email])->get()->first();
        $student = Student::where(["student_user_id"=> $user->id])->get()->first();       
        //ApplicantTask
        $applicantTaskList = ApplicantTask::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantTaskList as $applicantTaskData):
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
            $dataTask = new StudentTask();
            $dataTask->fill($applicantTaskArray);
            $dataTask->save();
        endforeach;  

        unset ($applicantTaskArray);

    }
}
