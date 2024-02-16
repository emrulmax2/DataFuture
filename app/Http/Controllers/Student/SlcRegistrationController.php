<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddRegistrationRequest;
use App\Http\Requests\SlcRegistrationUpdateRequest;
use App\Models\AttendanceCode;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\SlcAgreement;
use App\Models\SlcAttendance;
use App\Models\SlcCoc;
use App\Models\SlcInstallment;
use App\Models\SlcRegistration;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;

class SlcRegistrationController extends Controller
{
    public function getRegistrationConfirmationDetails(Request $request){
        $studen_id = $request->studen_id;
        $academic_year_id = $request->academic_year_id;
        $course_creation_instance_id = $request->course_creation_instance_id;

        $instance = CourseCreationInstance::find($course_creation_instance_id);
        $fees = (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0);

        return response()->json(['fees' => $fees], 200);
    }

    public function store(AddRegistrationRequest $request){
        $studen_id = $request->studen_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $course_creation_id = $request->course_creation_id;

        $existRegistration = SlcRegistration::where('student_id', $studen_id)->where('student_course_relation_id', $student_course_relation_id)
                             ->where('registration_year', $request->registration_year)->get()->first();
        if(isset($existRegistration->id) && $existRegistration->id > 0):
            return response()->json(['msg' => 'Registration exist under selected registration year.'], 304);
        endif;

        $regData = [];
        $regData['student_id'] = $studen_id;
        $regData['student_course_relation_id'] = $student_course_relation_id;
        $regData['course_creation_instance_id'] = $request->course_creation_instance_id;
        $regData['ssn'] = $request->studen_ssn;
        $regData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $regData['academic_year_id'] = $request->academic_year_id;
        $regData['registration_year'] = $request->registration_year;
        $regData['slc_registration_status_id'] = $request->slc_registration_status_id;
        $regData['note'] = (isset($request->note) && !empty($request->note) ? $request->note : '');
        $regData['created_by'] = auth()->user()->id;

        $slcRegistration = SlcRegistration::create($regData);

        if($slcRegistration):
            $agreementData = [];
            $agreementData['student_id'] = $studen_id;
            $agreementData['student_course_relation_id'] = $student_course_relation_id;
            $agreementData['course_creation_instance_id'] = $request->course_creation_instance_id;
            $agreementData['slc_registration_id'] = $slcRegistration->id;
            $agreementData['slc_coursecode'] = $request->slc_course_code;
            $agreementData['is_self_funded'] = (isset($request->is_self_funded) && $request->is_self_funded > 0 ? $request->is_self_funded : 0);
            $agreementData['date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
            $agreementData['year'] = $request->registration_year;
            $agreementData['fees'] = $request->instance_fees;
            $agreementData['total'] = $request->instance_fees;
            $agreementData['created_by'] = auth()->user()->id;

            $slcAgreement = SlcAgreement::create($agreementData);

            $confirm_attendance = (isset($request->confirm_attendance) && $request->confirm_attendance > 0 ? true : false);
            if($confirm_attendance):
                $attendance_code_id = $request->attendance_code_id;
                $attendanceCode = AttendanceCode::find($attendance_code_id);
                $cocRequired = (isset($attendanceCode->coc_required) && $attendanceCode->coc_required == 1 ? true : false);

                $attenData = [];
                $attenData['student_id'] = $studen_id;
                $attenData['student_course_relation_id'] = $student_course_relation_id;
                $attenData['course_creation_instance_id'] = $request->course_creation_instance_id;
                $attenData['slc_registration_id'] = $slcRegistration->id;
                $attenData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                $attenData['attendance_year'] = $request->registration_year;
                $attenData['term_declaration_id'] = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
                $attenData['session_term'] = $request->session_term;
                $attenData['attendance_code_id'] = $attendance_code_id;
                $attenData['note'] = (isset($request->attendance_note) && !empty($request->attendance_note) ? $request->attendance_note : '');
                $attenData['created_by'] = auth()->user()->id;

                $slcAttendance = SlcAttendance::create($attenData);
                if($cocRequired):
                    $cocData = [];
                    $cocData['student_id'] = $studen_id;
                    $cocData['student_course_relation_id'] = $student_course_relation_id;
                    $cocData['course_creation_instance_id'] = $request->course_creation_instance_id;
                    $cocData['slc_registration_id'] = $slcRegistration->id;
                    $cocData['slc_attendance_id'] = $slcAttendance->id;
                    $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                    $cocData['coc_type'] = 'Outstanding';
                    $cocData['created_by'] = auth()->user()->id;

                    $slcCoc = SlcCoc::create($cocData);
                endif;

                if($slcAttendance && $attendance_code_id == 1):
                    $session_term = $request->session_term;
                    $course_creation_instance_id = $request->course_creation_instance_id;
                    $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
                    $termDeclaration = TermDeclaration::find($term_declaration_id);
                    $term_type_id = (isset($termDeclaration->term_type_id) && $termDeclaration->term_type_id > 0 ? $termDeclaration->term_type_id : null);

                    $installmentData = [];
                    $installmentData['student_id'] = $studen_id;
                    $installmentData['student_course_relation_id'] = $student_course_relation_id;
                    $installmentData['course_creation_instance_id'] = $request->course_creation_instance_id;
                    $installmentData['slc_attendance_id'] = $slcAttendance->id;
                    $installmentData['slc_agreement_id'] = (isset($slcAgreement->id) ? $slcAgreement->id : null);
                    $installmentData['installment_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                    $installmentData['amount'] = $request->installment_amount;
                    $installmentData['term_type_id'] = $term_type_id;
                    $installmentData['session_term'] = $session_term;
                    $installmentData['term_declaration_id'] = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
                    $installmentData['created_by'] = auth()->user()->id;

                    $installment = SlcInstallment::create($installmentData);
                endif;
            endif;

            return response()->json(['msg' => 'Registration successfully inserted!'], 200);
        else: 
            return response()->json(['msg' => 'Something went wrong. Please try latter.'], 422);
        endif;
    }

    public function edit(Request $request) {
        $reg_id = $request->reg_id;
        $slcRegistration = SlcRegistration::find($reg_id);

        return response()->json(['res' => $slcRegistration], 200);
    }

    public function update(SlcRegistrationUpdateRequest $request){
        $slc_registration_id = $request->slc_registration_id;
        $theRegistration = SlcRegistration::find($slc_registration_id);
        $existRegistration = SlcRegistration::where('student_id', $theRegistration->student_id)->where('student_course_relation_id', $theRegistration->student_course_relation_id)
                            ->where('registration_year', $request->registration_year)->where('id', '!=', $slc_registration_id)->get()->first();
        if(isset($existRegistration->id) && $existRegistration->id > 0):
            return response()->json(['msg' => 'Registration exist under selected registration year.'], 304);
        endif;

        $regData = [];
        $regData['course_creation_instance_id'] = $request->course_creation_instance_id;
        $regData['ssn'] = $request->studen_ssn;
        $regData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $regData['academic_year_id'] = $request->academic_year_id;
        $regData['registration_year'] = $request->registration_year;
        $regData['slc_registration_status_id'] = $request->slc_registration_status_id;
        $regData['note'] = $request->note;
        $regData['updated_by'] = auth()->user()->id;

        $slcRegistration = SlcRegistration::where('id', $slc_registration_id)->update($regData);

        return response()->json(['res' => 'Registration data successfully updated.'], 200);
    }

    public function hasData(Request $request){
        $slc_registration_id = $request->slc_registration_id;

        $slcAttendance = SlcAttendance::where('slc_registration_id', $slc_registration_id)->get()->count();
        $slcCoc = SlcCoc::where('slc_registration_id', $slc_registration_id)->get()->count();
        $slcAgreement = SlcAgreement::where('slc_registration_id', $slc_registration_id)->get()->count();

        if($slcCoc > 0 || $slcAttendance > 0 || $slcAgreement > 0):
            return response()->json(['res' => 0], 200);
        else:
            return response()->json(['res' => 1], 200);
        endif;
    }

    public function destroy(Request $request){
        $student_id = $request->student;
        $recordid = $request->recordid;

        SlcRegistration::where('student_id', $student_id)->where('id', $recordid)->delete();

        return response()->json(['res' => 'Success'], 200);
    }
}
