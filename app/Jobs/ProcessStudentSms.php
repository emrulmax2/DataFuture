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
use App\Models\ApplicantSms;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentSms;
use App\Models\User;

class ProcessStudentSms implements ShouldQueue
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

        //Begin
        $applicantSetData = ApplicantSms::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            $dataArray = [
                'student_id' => $student->id,
                'subject' => $applicantSet->subject,
                'sms' => $applicantSet->sms,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            if($applicantSet->sms_template_id) {
                $dataArray = array_merge($dataArray,['sms_template_id' => $applicantSet->sms_template_id]);
            }

            $data = new StudentSms();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
