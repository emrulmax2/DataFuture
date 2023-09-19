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
use App\Models\ApplicantDisability;
use App\Models\StudentDisability;

class ProcessStudentDisability implements ShouldQueue
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
        $user = User::where(["email"=> $ApplicantUser->email])->get()->first();
        $student = Student::where(["user_id"=> $user->id])->get()->first(); 
        
        //StudentDisabilities
        $applicantDisabilityData= ApplicantDisability::where('applicant_id',$student->id)->get();
        foreach($applicantDisabilityData as $applicantDisability):
            $dataArray = [
                'student_id' => $student->id,
                'disability_id' => $applicantDisability->disabilitiy_id,
            ];

            $data = new StudentDisability();
            $data->fill($dataArray);
            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
