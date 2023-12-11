<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcAgreementRequest;
use App\Http\Requests\SlcAgreementUpdateRequest;
use App\Models\CourseCreationInstance;
use App\Models\SlcAgreement;
use App\Models\Student;
use Illuminate\Http\Request;

class SlcAgreementController extends Controller
{
    public function store(SlcAgreementRequest $request){
        $student_id = $request->student_id;
        $student = Student::find($student_id);
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);

        $fees = (isset($request->fees) && $request->fees > 0 ? $request->fees : 0);
        $agreementData = [];
        $agreementData['student_id'] = $student_id;
        $agreementData['student_course_relation_id'] = $courseRelationId;
        $agreementData['course_creation_instance_id'] = $request->course_creation_instance_id;
        $agreementData['slc_registration_id'] = null;
        $agreementData['slc_coursecode'] = $request->slc_coursecode;
        $agreementData['is_self_funded'] = (isset($request->is_self_funded) && $request->is_self_funded > 0 ? $request->is_self_funded : 0);
        $agreementData['date'] = (!empty($request->date) ? date('Y-m-d', strtotime($request->date)) : null);
        $agreementData['year'] = $request->year;
        $agreementData['fees'] = $fees;
        $agreementData['discount'] = 0;
        $agreementData['total'] = $fees;
        $agreementData['created_by'] = auth()->user()->id;

        $slcAgreement = SlcAgreement::create($agreementData);

        return response()->json(['res' => 'Student Slc Agreement successfully inserted.'], 200);
    }

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

    public function getInstanceFees(Request $request){
        $studen_id = $request->studen_id;
        $course_creation_instance_id = $request->course_creation_instance_id;

        $courseCreationInstance = CourseCreationInstance::find($course_creation_instance_id);
        $fees = (isset($courseCreationInstance->fees) && $courseCreationInstance->fees > 0 ? $courseCreationInstance->fees : 0);

        return response()->json(['fees' => $fees], 200);
    }
}
