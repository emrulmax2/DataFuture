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
use App\Models\Student;
use App\Models\User;
use App\Models\ApplicantUser;

use App\Models\Role;
use App\Models\StudentUser;
use App\Models\UserRole;

class ProcessStudents implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
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
        $student = new Student();
        $applicantArray = [
            'applicant_id' => $this->applicant->id,
            'applicant_user_id' => $this->applicant->applicant_user_id,
            'student_user_id' => $user->id,
            'application_no'=> $this->applicant->application_no,
            'title_id'=> $this->applicant->title_id,
            'first_name'=> $this->applicant->first_name,
            'last_name'=> $this->applicant->last_name,
            'photo'=> $this->applicant->photo,
            'date_of_birth'=> $this->applicant->date_of_birth,
            'marital_status'=> $this->applicant->marital_status,
            'gender'=> $this->applicant->gender,
            'submission_date'=> $this->applicant->submission_date,
            'status_id'=> $this->applicant->status_id,
            'nationality_id'=> $this->applicant->nationality_id,
            'country_id'=> $this->applicant->country_id,
            'referral_code' => $this->applicant->referral_code,
            'is_referral_varified' => $this->applicant->is_referral_varified,
            'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
        ];
        $student->fill($applicantArray);

        $student->save();

    }
}
