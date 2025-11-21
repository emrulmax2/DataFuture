<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcAgreementRequest;
use App\Http\Requests\SlcAgreementUpdateRequest;
use App\Models\CourseCreationInstance;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\SlcRegistration;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\StudentArchive;

class SlcAgreementController extends Controller
{
    public function store(SlcAgreementRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $agreement_year = $request->year;

        $existingAgreement = SlcAgreement::where('student_id', $student_id)->where('student_course_relation_id', $courseRelationId)->where('year', $agreement_year)->get()->first();

        if(isset($existingAgreement->id) && $existingAgreement->id > 0):
            return response()->json(['res' => 'Existing agreement found under this sutdent active course relation for the year '.$agreement_year], 304);
        else:
            $fees = (isset($request->fees) && $request->fees > 0 ? $request->fees : 0);
            $commission = (isset($request->commission_amount) && $request->commission_amount > 0 ? $request->commission_amount : 0);
            $agreementData = [];
            $agreementData['student_id'] = $student_id;
            $agreementData['student_course_relation_id'] = $courseRelationId;
            $agreementData['course_creation_instance_id'] = $request->course_creation_instance_id;
            $agreementData['slc_registration_id'] = null;
            $agreementData['slc_coursecode'] = $request->slc_coursecode;
            $agreementData['is_self_funded'] = (isset($request->is_self_funded) && $request->is_self_funded > 0 ? $request->is_self_funded : 0);
            $agreementData['date'] = (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : null);
            $agreementData['year'] = $agreement_year;
            $agreementData['commission_amount'] = $commission;
            $agreementData['fees'] = $fees;
            $agreementData['discount'] = 0;
            $agreementData['total'] = $fees;
            $agreementData['created_by'] = auth()->user()->id;

            $slcAgreement = SlcAgreement::create($agreementData);

            return response()->json(['res' => 'Student Slc Agreement successfully inserted.'], 200);
        endif;
    }

    public function edit(Request $request){
        $agreement_id = $request->agreement_id;
        $slcAgreement = SlcAgreement::find($agreement_id);

        return response()->json(['res' => $slcAgreement], 200);
    }

    public function update(SlcAgreementUpdateRequest $request){
        $studen_id = $request->studen_id;
        $slc_agreement_id = $request->slc_agreement_id;

        $agreementOldRow = SlcAgreement::find($slc_agreement_id);

        $discount = (isset($request->discount) && $request->discount > 0 ? $request->discount : 0);
        $fees = (isset($request->fees) && $request->fees > 0 ? $request->fees : 0);
        
        $agreement = SlcAgreement::find($slc_agreement_id);
        $agreementData = [
            'slc_coursecode' => $request->slc_coursecode,
            'is_self_funded' => (isset($request->is_self_funded) && $request->is_self_funded > 0 ? $request->is_self_funded : 0),
            'date' => (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : null),
            'year' => $request->year,
            'commission_amount' => (isset($request->commission_amount) && $request->commission_amount > 0 ? $request->commission_amount : 0),
            'fees' => $fees,
            'discount' => $discount,
            'total' => ($fees - $discount),
            'note' => (isset($request->note) && !empty($request->note) ? $request->note : ''),
            'updated_by' => auth()->user()->id
        ];
        
        $agreement->fill($agreementData);
        $changes = $agreement->getDirty();
        $agreement->save();

        if($agreement->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                StudentArchive::create([
                    'student_id' => $studen_id,
                    'table' => 'slc_agreements',
                    'field_name' => $field,
                    'field_value' => $agreementOldRow->$field,
                    'field_new_value' => $value,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        return response()->json(['res' => 'Student Slc Agreement successfully updated.'], 200);
    }

    public function getInstanceFees(Request $request){
        $studen_id = $request->studen_id;
        $course_creation_instance_id = $request->course_creation_instance_id;

        $courseCreationInstance = CourseCreationInstance::find($course_creation_instance_id);
        $commissionPercent = 0;
        if(isset($courseCreationInstance->university_commission) && $courseCreationInstance->university_commission > 0):
            $commissionPercent = $courseCreationInstance->university_commission;
        elseif(isset($courseCreationInstance->creation->university_commission) && $courseCreationInstance->creation->university_commission > 0):
            $commissionPercent = $courseCreationInstance->creation->university_commission;
        endif;

        $totalFees = (isset($courseCreationInstance->fees) && $courseCreationInstance->fees > 0 ? $courseCreationInstance->fees : 0);
        $commission = ($totalFees * $commissionPercent) / 100;
        $fees = $totalFees - $commission;

        return response()->json(['fees' => $fees, 'commission' => $commission, 'percentage' => $commissionPercent], 200);
    }

    public function hasData(Request $request){
        $slc_agreement_id = $request->slc_agreement_id;

        $slcInst = SlcInstallment::where('slc_agreement_id', $slc_agreement_id)->get()->count();
        $slcMoneyReceipt = SlcMoneyReceipt::where('slc_agreement_id', $slc_agreement_id)->get()->count();

        if($slcMoneyReceipt > 0 || $slcInst > 0):
            return response()->json(['res' => 0], 200);
        else:
            return response()->json(['res' => 1], 200);
        endif;
    }

    public function destroy(Request $request){
        $student = $request->student;
        $slc_agreement_id = $request->recordid;

        SlcAgreement::where('student_id', $student)->where('id', $slc_agreement_id)->delete();

        return response()->json(['res' => 'Success'], 200);
    }

    public function assignAgrToReg(Request $request){
        $student = $request->student;
        $ids = (isset($request->recordid) && !empty($request->recordid) ? explode('_', $request->recordid) : []);
        if(!empty($ids) && count($ids) == 2):
            $slc_reg_id = (isset($ids[0]) && $ids[0] > 0 ? $ids[0] : 0);
            $slc_agr_id = (isset($ids[1]) && $ids[1] > 0 ? $ids[1] : 0);
            if($slc_reg_id > 0 && $slc_agr_id > 0):
                $slcReg = SlcRegistration::find($slc_reg_id);
                $data = [];
                $data['course_creation_instance_id'] = $slcReg->course_creation_instance_id;
                $data['slc_registration_id'] = $slcReg->id;
                SlcAgreement::where('id', $slc_agr_id)->update($data);

                return response()->json(['res' => 'Success'], 200);
            else:
                return response()->json(['res' => 'Error'], 422);
            endif;
        else:
            return response()->json(['res' => 'Error'], 422);
        endif;
    }
}
