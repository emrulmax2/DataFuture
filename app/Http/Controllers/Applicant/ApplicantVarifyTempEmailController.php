<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantTemporaryEmail;
use App\Models\ApplicantUser;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApplicantVarifyTempEmailController extends Controller
{
    public function varifyTempEmail($token){
        $applicant = Applicant::find($token);
        $applicantTemp = ApplicantTemporaryEmail::where('applicant_id', $token)->orderBy('id', 'desc')->first();
        if(isset($applicantTemp->applicant_id) && $applicantTemp->applicant_id > 0 && (isset($applicantTemp->status) && $applicantTemp->status == 'Pending')){
            $newEmail = $applicantTemp->email;
            $oldEmail = $applicant->users->email;
            $applicantUserId = $applicant->applicant_user_id;

            $applicantUser = ApplicantUser::where('id', $applicantUserId)->update([
                'email' => $newEmail
            ]);
            if($applicantUser):
                $tempUpdate = ApplicantTemporaryEmail::where('id', $applicantTemp->id)->update([
                    'status' => 'Active',
                    'activated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_by' => $token
                ]);
                $data = [];
                $data['applicant_id'] = $token;
                $data['table'] = 'applicant_users';
                $data['field_name'] = 'email';
                $data['field_value'] = $oldEmail;
                $data['field_new_value'] = $newEmail;
                $data['created_by'] = $token;

                ApplicantArchive::create($data);
                return redirect()->route('applicant.login')->with('verifySuccessMessage', 'New email address successfully changed and activated.');
            else:
                return redirect()->route('applicant.login')->with('verifySuccessMessage', 'Something went wrong. Please try later.');
            endif;
        }else{
            return redirect()->route('applicant.login')->with('verifySuccessMessage', 'There are no such records found. Contact with the administrator');
        }
    }
}
