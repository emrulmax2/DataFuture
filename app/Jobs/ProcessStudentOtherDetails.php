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
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantUser;
use App\Models\Student;
use App\Models\StudentOtherDetail;
use App\Models\User;


class ProcessStudentOtherDetails implements ShouldQueue
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
        $applicantSetData= ApplicantOtherDetail::where('applicant_id',$this->applicant->id)->get();
        foreach($applicantSetData as $applicantSet):
            $dataArray = [
                'student_id' => $student->id,
                'ethnicity_id'=> $applicantSet->ethnicity_id,
                'disability_status'=> ($applicantSet->disability_status) ?? 0,
                'disabilty_allowance'=> ($applicantSet->disabilty_allowance) ?? 0,
                'is_education_qualification'=> ($applicantSet->is_edication_qualification) ?? 0,
                'employment_status'=> $applicantSet->employment_status,
                'college_introduction'=> $applicantSet->college_introduction,
                'gender_identity'=> $applicantSet->gender_identity,
                'created_by'=> ($this->applicant->updated_by) ? $this->applicant->updated_by : $this->applicant->created_by,
            ];
            if($applicantSet->sexual_orientation_id) {
                array_merge($dataArray,['sexual_orientation_id' => $applicantSet->sexual_orientation_id]);
            }
            if($applicantSet->religion_id) {
                array_merge($dataArray,['religion_id' => $applicantSet->religion_id]);
            }

            $data = new StudentOtherDetail();

            $data->fill($dataArray);

            $data->save();
            unset ($dataArray);
        endforeach;

    }
}
