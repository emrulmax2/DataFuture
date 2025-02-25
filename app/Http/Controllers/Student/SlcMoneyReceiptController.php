<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcMoneyReceiptRequest;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\StudentArchive;

class SlcMoneyReceiptController extends Controller
{
    public function store(SlcMoneyReceiptRequest $request){
        $studen_id = $request->studen_id;
        $student = Student::find($studen_id);

        $slc_agreement_id = $request->slc_agreement_id;
        $agreement = SlcAgreement::find($slc_agreement_id);

        $payment_type = $request->payment_type;
        $invoice_no = $request->invoice_no;
        $invoice_no = ($payment_type == 'Refund' ? 'R-'.$invoice_no : $invoice_no);

        $mrData = [
            'student_id' => $studen_id,
            'student_course_relation_id' => $agreement->student_course_relation_id,
            'course_creation_instance_id' => $agreement->course_creation_instance_id,
            'slc_agreement_id' => $slc_agreement_id,
            'term_declaration_id' => $request->term_declaration_id,
            'session_term' => $request->session_term,
            'invoice_no' => $invoice_no,
            'slc_coursecode' => $agreement->slc_coursecode,
            'slc_payment_method_id' => $request->slc_payment_method_id,
            'entry_date' => date('Y-m-d'),
            'payment_date' => (!empty($request->payment_date) ? date('Y-m-d', strtotime($request->payment_date)) : null),
            'amount' => $request->amount,
            'payment_type' => $payment_type,
            'remarks' => $request->remarks,
            'received_by' => auth()->user()->id,
            'created_by' => auth()->user()->id,
        ];
        
        $moneyReceipt = SlcMoneyReceipt::create($mrData);
        if($moneyReceipt):
            $slcInstallment = SlcInstallment::where('student_id', $studen_id)->where('student_course_relation_id', $agreement->student_course_relation_id)
                              ->where('slc_agreement_id', $slc_agreement_id)->where('term_declaration_id', $request->term_declaration_id)
                              ->where('session_term', $request->session_term)->where('amount', $request->amount)
                              ->get()->first();
            if(isset($slcInstallment->id) && $slcInstallment->id > 0):
                SlcInstallment::where('id', $slcInstallment->id)->update(['slc_money_receipt_id' => $moneyReceipt->id]);
            endif;
        endif;
        return response()->json(['res' => 'Success'], 200);
    }

    public function edit(Request $request){
        $payment_id = $request->payment_id;
        $slcMoneyReceipt = SlcMoneyReceipt::find($payment_id);

        return response()->json(['res' => $slcMoneyReceipt], 200);
    }

    public function update(SlcMoneyReceiptRequest $request){
        $student_id = $request->student_id;
        $id = $request->id;

        $moneyReceiptOldRow = SlcMoneyReceipt::find($id);

        $payment_type = $request->payment_type;
        $invoice_no = $request->invoice_no;
        $invoice_no = ($payment_type == 'Refund' ? 'R-'.str_replace('R-', '', $invoice_no) : $invoice_no);

        $moneyReceiptModel = SlcMoneyReceipt::find($id);
        $mrData = [
            'term_declaration_id' => $request->term_declaration_id,
            'session_term' => $request->session_term,
            'invoice_no' => $invoice_no,
            'slc_payment_method_id' => $request->slc_payment_method_id,
            'payment_date' => (!empty($request->payment_date) ? date('Y-m-d', strtotime($request->payment_date)) : null),
            'amount' => $request->amount,
            'payment_type' => $payment_type,
            'remarks' => $request->remarks,
            'updated_by' => auth()->user()->id,
        ];
        
        $moneyReceiptModel->fill($mrData);
        $changes = $moneyReceiptModel->getDirty();
        $moneyReceiptModel->save();

        if($moneyReceiptModel->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                StudentArchive::create([
                    'student_id' => $student_id,
                    'table' => 'slc_money_receipts',
                    'field_name' => $field,
                    'field_value' => $moneyReceiptOldRow->$field,
                    'field_new_value' => $value,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        if($moneyReceiptModel->wasChanged()):
            $updateOldInstallments = SlcInstallment::where('slc_money_receipt_id', $id)->update(['slc_money_receipt_id' => null]);
            $slcInstallment = SlcInstallment::where('student_id', $student_id)
                              ->where('student_course_relation_id', $moneyReceiptModel->student_course_relation_id)
                              ->where('slc_agreement_id', $moneyReceiptModel->slc_agreement_id)
                              ->where('term_declaration_id', $request->term_declaration_id)
                              ->where('session_term', $request->session_term)
                              ->where('amount', $request->amount)
                              ->get()->first();
            if(isset($slcInstallment->id) && $slcInstallment->id > 0):
                SlcInstallment::where('id', $slcInstallment->id)->update(['slc_money_receipt_id' => $id]);
            endif;
        endif;

        return response()->json(['res' => 'Success'], 200);
    }

    public function destroy(Request $request){
        $student = $request->student;
        $slc_money_receipt_id = $request->recordid;

        SlcMoneyReceipt::where('student_id', $student)->where('id', $slc_money_receipt_id)->delete();

        return response()->json(['res' => 'Success'], 200);
    }

    public function reAssignPaymentToAgreement(Request $request){
        $student = $request->student;
        $ids = (isset($request->recordid) && !empty($request->recordid) ? explode('_', $request->recordid) : []);
        if(!empty($ids) && count($ids) == 2):
            $slc_agr_id = (isset($ids[0]) && $ids[0] > 0 ? $ids[0] : 0);
            $slc_pay_id = (isset($ids[1]) && $ids[1] > 0 ? $ids[1] : 0);
            if($slc_agr_id > 0 && $slc_pay_id > 0):
                $slcAGR = SlcAgreement::find($slc_agr_id);
                $data = [];
                $data['course_creation_instance_id'] = $slcAGR->course_creation_instance_id;
                $data['slc_agreement_id'] = $slcAGR->id;
                SlcMoneyReceipt::where('id', $slc_pay_id)->update($data);

                return response()->json(['res' => 'Success'], 200);
            else:
                return response()->json(['res' => 'Error'], 422);
            endif;
        else:
            return response()->json(['res' => 'Error'], 422);
        endif;
    }
}
