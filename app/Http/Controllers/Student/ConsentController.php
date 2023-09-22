<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentConsentRequest;
use App\Models\StudentConsent;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function update(StudentConsentRequest $request){
        $student_id = $request->student_id;
        $consent = $request->student_consent;
        
        $stdConsent = StudentConsent::where('student_id', $student_id)->get();
        foreach($stdConsent as $con):
            $consentPolicyId = $con->consent_policy_id;

            $data = [];
            $data['status'] = (isset($consent[$consentPolicyId]) && $consent[$consentPolicyId] == 1 ? 'Agree' : 'Unknown');
            $data['updated_by'] = auth()->user()->id;
            
            StudentConsent::where('id', $con->id)->update($data);
        endforeach;

        return response()->json(['msg' => 'Consent Successfully updated'], 200);
    }
}
