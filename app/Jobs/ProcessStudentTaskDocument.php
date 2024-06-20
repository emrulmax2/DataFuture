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

class ProcessStudentTaskDocument implements ShouldQueue
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
        //--BEGIN: Student Document Sync
        $applicantTaskidList = [];
        $studentDocumentList = [];
        foreach($this->applicant->allTasks as $applicantTaskData):
            //Applicant Task wise Document capture
            $applicantTaskDocumentData = ApplicantTaskDocument::where(['applicant_task_id'=>$applicantTaskData->id])->get();
            
            if(!in_array($applicantTaskData->id, $applicantTaskidList)) {
                array_push($applicantTaskidList,$applicantTaskData->id);
                foreach($applicantTaskDocumentData as $applicantTaskDocument):
                    $applicantDocument = ApplicantDocument::find($applicantTaskDocument->applicant_document_id);
                    //find the document and put it in student document
                    // then insert it into studentDocument and applicantTaskDocument
                    $studentDocument = new StudentDocument();
                    //DB::enableQueryLog();

                    $applicantArray = [
                        'student_id' => $student->id,
                        'hard_copy_check' => ($applicantDocument->hard_copy_check==1) ? 1 : 0,
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
                    //endDocuemnt saved
                    $studentDocumentList[$applicantTaskData->id][] = $studentDocument->id;
                    
                endforeach;
                
            }
        endforeach;
        //--END: Student Document Sync

        //--BEGIN: Student Task Document Sync
        $studentTaskList = StudentTask::where("student_id", $student->id)->get();
        foreach($studentTaskList as $task):
            foreach ($studentDocumentList as $applicantTaskId => $studentDocuementArray):
                if($applicantTaskId==$task->applicant_task_id) {
                    foreach ($studentDocuementArray as $studentDocId):
                    $applicantArray = [
                        'student_id' => $student->id,
                        'student_task_id' => $task->id,
                        'student_document_id' => $studentDocId,
                        'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                    ];
                    $data = new StudentTaskDocument();
                    $data->fill($applicantArray);
                    $data->save();
                    endforeach;
                }
            endforeach;
        endforeach;
        unset($applicantArray);
        //--END: Student Task Document Sync

    }
}
