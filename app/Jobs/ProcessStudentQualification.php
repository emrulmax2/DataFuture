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
use App\Models\ApplicantQualification;
use App\Models\StudentQualification;
use App\Models\StudentUser;

class ProcessStudentQualification implements ShouldQueue
{
    use Batchable,Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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

        //StudentQualificationDocument
        $applicantQualificationData= ApplicantQualification::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantQualificationData as $applicantQualification):
            $applicantQualificationArray = [
                'student_id' => $student->id,
                'awarding_body' => $applicantQualification->awarding_body,
                'highest_academic' => $applicantQualification->highest_academic,
                'subjects' => $applicantQualification->subjects,
                'result' => $applicantQualification->result,
                'degree_award_date' => $applicantQualification->degree_award_date,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            $data = new StudentQualification();

            $data->fill($applicantQualificationArray);

            $data->save();
            unset ($applicantTaskArray);
        endforeach;
    }
}
