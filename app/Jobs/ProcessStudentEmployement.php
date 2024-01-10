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
use App\Models\ApplicantEmployment;
use App\Models\EmploymentReference;
use App\Models\StudentEmployment;
use App\Models\StudentEmploymentReference;
use App\Models\StudentUser;

class ProcessStudentEmployement implements ShouldQueue
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

        //StudentEmployments
        $applicantEmploymentsData= ApplicantEmployment::where('applicant_id',$this->applicant->id)->get();

        foreach($applicantEmploymentsData as $applicantEmployments):
            $applicantEmploymentArray = [
                'student_id' => $student->id,
                'company_name' => $applicantEmployments->company_name,
                'company_phone' => $applicantEmployments->company_phone,
                'position'=> $applicantEmployments->position,
                'start_date' => $applicantEmployments->start_date,
                'end_date' => isset($applicantEmployments->end_date) ? ($applicantEmployments->end_date) : NULL,
                'continuing' => isset($applicantEmployments->continuing) ? ($applicantEmployments->continuing) : '0',
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];

            $Address = new Address();
            $dataAddress = [
                "address_line_1" => $applicantEmployments->address_line_1,
                "address_line_2" => isset($applicantEmployments->address_line_2) ? ($applicantEmployments->address_line_2) : NULL,
                "state"	=> isset($applicantEmployments->state) ? ($applicantEmployments->state) : NULL,
                "post_code"	=> $applicantEmployments->post_code,
                "city" =>$applicantEmployments->city,
                "country" =>$applicantEmployments->country,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];
       
            $Address->fill($dataAddress);
            $Address->save();
            if($Address->id) {
                $applicantEmploymentArray = array_merge($applicantEmploymentArray,["address_id"=>$Address->id]);
            }
            $data = new StudentEmployment();
            $data->fill($applicantEmploymentArray);
            $data->save();
            unset ($dataArray);

            $dataSet = EmploymentReference::where('applicant_employment_id',$applicantEmployments->id)->get();
            foreach($dataSet as $employmentData):
                $applicantEmploymentArray = [

                    'student_employment_id' => $data->id, 
                    'name' => $employmentData->name,
                    'position' => $employmentData->position,
                    'phone' => $employmentData->phone,
                    'email' => $employmentData->email,
                    'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
                ];
    
                $data = new StudentEmploymentReference();
                $data->fill($applicantEmploymentArray);
                $data->save();
                unset ($applicantEmploymentArray);
            endforeach;

        endforeach;
  

    }
}
