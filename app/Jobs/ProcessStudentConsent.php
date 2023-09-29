<?php

namespace App\Jobs;

use App\Models\Address;
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
use App\Models\ConsentPolicy;
use App\Models\StudentConsent;

class ProcessStudentConsent implements ShouldQueue
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
        //StudentContacts
        $dataConsents= ConsentPolicy::all();
        foreach($dataConsents as $consent):
            $dataArray = [
                'student_id' => $student->id,
                'consent_policy_id' => $consent->id,
                'status' => ($consent->is_required=="Yes") ? 'Agree' : 'Unknown' ,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];




            $data = new StudentConsent();

            $data->fill($dataArray);
            $data->save();
            unset ($dataArray);

        endforeach;
    }
}
