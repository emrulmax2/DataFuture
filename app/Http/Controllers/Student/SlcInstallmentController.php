<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcInstallmentUpdateRequest;
use App\Models\SlcAgreement;
use App\Models\SlcAttendance;
use App\Models\SlcInstallment;
use App\Models\SlcRegistration;
use App\Models\Student;
use Illuminate\Http\Request;

class SlcInstallmentController extends Controller
{
    public function store(SlcInstallmentUpdateRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);

        $slc_agreement_id = $request->slc_agreement_id;
        $slcAgreement = SlcAgreement::find($slc_agreement_id);
        $slc_registration_id = (isset($slcAgreement->slc_registration_id) && $slcAgreement->slc_registration_id > 0 ? $slcAgreement->slc_registration_id : 0);
        $slcAttendance = SlcAttendance::where('slc_registration_id', $slc_registration_id)->get()->first();
        $slc_attendance_id = (isset($slcAttendance->id) && $slcAttendance->id > 0 ? $slcAttendance->id : 0);


        $installmentData = [];
        $installmentData['student_id'] = $student_id;
        $installmentData['student_course_relation_id'] = $courseRelationId;
        $installmentData['course_creation_instance_id'] = $slcAgreement->course_creation_instance_id;
        $installmentData['slc_attendance_id'] = $slc_attendance_id;
        $installmentData['slc_agreement_id'] = $slc_agreement_id;
        $installmentData['installment_date'] = (!empty($request->installment_date) ? date('Y-m-d', strtotime($request->installment_date)) : null);
        $installmentData['amount'] = $request->amount;
        $installmentData['term'] = $request->term;
        $installmentData['session_term'] = $request->session_term;
        $installmentData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
        $installmentData['created_by'] = auth()->user()->id;

        $installment = SlcInstallment::create($installmentData);

        return response()->json(['res' => 'Student SLC Installment successfully added!'], 200);
    }


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

    public function getDetails(Request $request){
        $agreement_id = $request->agreement_id;
        $slcAgreement = SlcAgreement::find($agreement_id);

        $totalAmount = (isset($slcAgreement->total) && $slcAgreement->total > 0 ? $slcAgreement->total : 0);
        $totalInstAmount = SlcInstallment::where('slc_agreement_id', $agreement_id)->sum('amount');
        $remainingAmount = ($totalAmount - $totalInstAmount);

        $res['total_amount'] = $totalAmount;
        $res['total_amount_html'] = '£'.number_format($totalAmount, 2);
        $res['remaining_amount'] = $remainingAmount;
        $res['remaining_amount_html'] = '£'.number_format($remainingAmount, 2);

        return response()->json(['res' => $res], 200);
    }
}
