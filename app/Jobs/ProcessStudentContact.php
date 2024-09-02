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
use App\Models\ApplicantContact;
use App\Models\StudentContact;
use App\Models\StudentUser;

class ProcessStudentContact implements ShouldQueue
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
        //StudentContacts
        $applicantContactData= ApplicantContact::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantContactData as $applicantContact):
            $dataArray = [
                'student_id' => $student->id,
                'home' => $applicantContact->home,
                'mobile' => $applicantContact->mobile,
                'external_link_ref'=> isset($applicantContact->external_link_ref) ? $applicantContact->external_link_ref : NULL,
                'mobile_verification' => isset($applicantContact->mobile_verification) ? $applicantContact->mobile_verification : '0',
                'permanent_post_code' => isset($applicantContact->permanent_post_code) ? ($applicantContact->permanent_post_code) : NULL,
                'personal_email' => isset($ApplicantUser->email) ? ($ApplicantUser->email) : NULL,
                'personal_email_verification' => isset($user->email_verified_at) && !empty($user->email_verified_at) ? 1 : 0,
                
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            if($applicantContact->country_id) {
                $dataArray = array_merge($dataArray,['country_id' => $applicantContact->country_id]);
            }

            if($applicantContact->permanent_country_id) {
                $dataArray = array_merge($dataArray,['permanent_country_id' => $applicantContact->permanent_country_id]);
            }

            if($applicantContact->post_code) {
                $dataArray = array_merge($dataArray,['term_time_post_code' => $applicantContact->post_code]);
            }

            $Address = new Address();
            $dataAddress = [
                "address_line_1" => $applicantContact->address_line_1,
                "address_line_2" => isset($applicantContact->address_line_2) ? ($applicantContact->address_line_2) : NULL,
                "state"	=> isset($applicantContact->state) ? ($applicantContact->state) : NULL,
                "post_code"	=> $applicantContact->post_code,
                "city" =>$applicantContact->city,
                "country" =>$applicantContact->country,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];
       
            $Address->fill($dataAddress);
            $Address->save();
            if($Address->id) {
                $dataArray = array_merge($dataArray,["term_time_address_id"=>$Address->id]);
            }
            $data = new StudentContact();

            $data->fill($dataArray);
            $data->save();
            unset ($dataArray);

        endforeach;
    }
}
