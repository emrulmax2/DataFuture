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
use App\Models\ApplicantProposedCourse;
use App\Models\StudentProposedCourse;

class ProcessStudentProposedCourse implements ShouldQueue
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
        $user = User::where(["email"=> $ApplicantUser->email])->get()->first();
        $student = Student::where(["user_id"=> $user->id])->get()->first(); 
        
        //StudentDisabilities
        $applicantProposedCourseData= ApplicantProposedCourse::where('applicant_id',$student->id)->get();
        foreach($applicantProposedCourseData as $applicantProposedCourse):
            
            $dataArray = [
                'student_id' => $student->id,
                'course_creation_id'=>$applicantProposedCourse->course_creation_id,
                'semester_id'=>$applicantProposedCourse->semester_id,
                'academic_year_id'=>$applicantProposedCourse->academic_year_id,
                'student_loan'=>$applicantProposedCourse->student_loan,
                'student_finance_england'=>$applicantProposedCourse->student_finance_england,
                'fund_receipt'=>$applicantProposedCourse->fund_receipt,
                'applied_received_fund'=>$applicantProposedCourse->applied_received_fund,
                'full_time'=>$applicantProposedCourse->full_time,
                'other_funding'=>$applicantProposedCourse->other_funding,
                'created_by'=>($applicantProposedCourse->updated_by) ? $applicantProposedCourse->updated_by : $applicantProposedCourse->created_by,
            ];

            $data = new StudentProposedCourse();
            $data->fill($dataArray);
            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
