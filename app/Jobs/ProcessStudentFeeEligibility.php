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
use App\Models\ApplicantFeeEligibility;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentFeeEligibility;
use App\Models\StudentProposedCourse;
use App\Models\StudentUser;
use App\Models\User;

class ProcessStudentFeeEligibility implements ShouldQueue
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
        
        $getStudentCourseRelationData= StudentProposedCourse::where('student_id',$student->id)->get()->first();
        //Begin
        $applicantSetData = ApplicantFeeEligibility::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            $dataArray = [
                'student_id' => $student->id,
                'student_course_relation_id' => $getStudentCourseRelationData->student_course_relation_id,
                'fee_eligibility_id' => ($applicantSet->fee_eligibility_id) ?? NULL,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            $data = new StudentFeeEligibility();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
