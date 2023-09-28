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
use App\Models\ApplicantKin;
use App\Models\Student;
use App\Models\User;
use App\Models\ApplicantUser;
use App\Models\StudentKin;

class ProcessStudentKinDetail implements ShouldQueue
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

        //StudentKinDetails
        $applicantKinData= ApplicantKin::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantKinData as $applicantKin):
            $dataArray = [
                'student_id' => $student->id,
                'name' => $applicantKin->name,	
                'kins_relation_id' => $applicantKin->kins_relation_id,
                'mobile' =>	$applicantKin->mobile,
                'email' =>	$applicantKin ->email,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                
            ];
            $Address = new Address();
            $dataAddress = [
                "address_line_1" => $applicantKin->address_line_1,
                "address_line_2" => isset($applicantKin->address_line_2) ? ($applicantKin->address_line_2) : 'NULL',
                "state"	=> isset($applicantKin->state) ? ($applicantKin->state) : 'NULL',
                "post_code"	=> $applicantKin->post_code,
                "city" =>$applicantKin->city,
                "country" =>$applicantKin->country,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];
       
            $Address->fill($dataAddress);
            $Address->save();
            if($Address->id) {
                $dataArray = array_merge($dataArray,["address_id"=>$Address->id]);
            }

            $data = new StudentKin();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
