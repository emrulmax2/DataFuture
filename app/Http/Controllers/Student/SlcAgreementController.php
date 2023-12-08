<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcAgreementUpdateRequest;
use App\Models\SlcAgreement;
use Illuminate\Http\Request;

class SlcAgreementController extends Controller
{
    

    public function edit(Request $request){
        $agreement_id = $request->agreement_id;
        $slcAgreement = SlcAgreement::find($agreement_id);

        return response()->json(['res' => $slcAgreement], 200);
    }

    public function update(SlcAgreementUpdateRequest $request){
        $studen_id = $request->studen_id;
        $slc_agreement_id = $request->slc_agreement_id;

        $discount = (isset($request->discount) && $request->discount > 0 ? $request->discount : 0);
        $fees = (isset($request->fees) && $request->fees > 0 ? $request->fees : 0);
        $agreementData = [];
        $agreementData['slc_coursecode'] = $request->slc_coursecode;
        $agreementData['is_self_funded'] = (isset($request->is_self_funded) && $request->is_self_funded > 0 ? $request->is_self_funded : 0);
        $agreementData['date'] = (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : null);
        $agreementData['year'] = $request->year;
        $agreementData['fees'] = $fees;
        $agreementData['discount'] = $discount;
        $agreementData['total'] = ($fees - $discount);
        $agreementData['updated_by'] = auth()->user()->id;

        $slcAgreement = SlcAgreement::where('id', $slc_agreement_id)->update($agreementData);

        return response()->json(['res' => 'Student Slc Agreement successfully updated.'], 200);
    }
}
