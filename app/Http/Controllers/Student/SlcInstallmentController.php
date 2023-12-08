<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcInstallmentUpdateRequest;
use App\Models\SlcInstallment;
use Illuminate\Http\Request;

class SlcInstallmentController extends Controller
{
    public function edit(Request $request){
        $installment_id = $request->installment_id;
        $slcInstallment = SlcInstallment::with('agreement')->find($installment_id);
        $totalAmount = (isset($slcInstallment->agreement->total) && $slcInstallment->agreement->total > 0 ? $slcInstallment->agreement->total : 0);
        $agreementId = $slcInstallment->agreement->id;
        $totalInstAmount = SlcInstallment::where('slc_agreement_id', $agreementId)->sum('amount');
        $remainingAmount = ($totalAmount - $totalInstAmount);

        $slcInstallment['total_amount'] = $totalAmount;
        $slcInstallment['total_amount_html'] = '£'.number_format($totalAmount, 2);
        $slcInstallment['remaining_amount'] = $remainingAmount;
        $slcInstallment['remaining_amount_html'] = '£'.number_format($remainingAmount, 2);

        return response()->json(['res' => $slcInstallment], 200);
    }

    public function update(SlcInstallmentUpdateRequest $request){
        $studen_id = $request->studen_id;
        $slc_installment_id = $request->slc_installment_id;

        $installmentData = [];
        $installmentData['installment_date'] = (!empty($request->installment_date) ? date('Y-m-d', strtotime($request->installment_date)) : null);
        $installmentData['amount'] = $request->amount;
        $installmentData['term'] = $request->term;
        $installmentData['session_term'] = $request->session_term;
        $installmentData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
        $installmentData['updated_by'] = auth()->user()->id;

        $installment = SlcInstallment::where('id', $slc_installment_id)->update($installmentData);

        return response()->json(['res' => 'Student SLC Installment successfully updated!'], 200);
    }
}
