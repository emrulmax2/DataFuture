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
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\User;

use App\Models\ApplicantProofOfId;
use App\Models\StudentProofOfId;
use App\Models\StudentUser;

class ProcessStudentProofOfId implements ShouldQueue
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
        $applicantSetData = ApplicantProofOfId::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            $dataArray = [
                'student_id' => $student->id,
                'proof_type' => ($applicantSet->proof_type) ?? NULL,
                'proof_id' => ($applicantSet->proof_id) ?? NULL,
                'proof_expiredate' => ($applicantSet->proof_expiredate) ?? NULL,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            $data = new StudentProofOfId();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
