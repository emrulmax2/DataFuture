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
use Illuminate\Http\Request;

class SlcRegistrationController extends Controller
{
    public function getRegistrationConfirmationDetails(Request $request){
        $studen_id = $request->studen_id;
        $academic_year_id = $request->academic_year_id;
        $course_creation_instance_id = $request->course_creation_instance_id;

        $instance = CourseCreationInstance::find($course_creation_instance_id);
        $fees = (isset($instance->fees) && $instance->fees > 0 ? $instance->fees : 0);

        $html = '';
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $course_creation_instance_id)->orderBy('session_term', 'ASC')->get();
        if(!empty($instanceTerm) && $instanceTerm->count() > 0):
            $html .= '<option value="">Please Selects</option>';
            foreach($instanceTerm as $insTerm):
                $html .= '<option value="'.$insTerm->session_term.'">Term 0'.$insTerm->session_term.'</option>';
            endforeach;
        endif;

        return response()->json(['fees' => $fees, 'session_term_html' => $html], 200);
    }

    public function store(AddRegistrationRequest $request){
        $studen_id = $request->studen_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $course_creation_id = $request->course_creation_id;

        $regData = [];
        $regData['student_id'] = $studen_id;
        $regData['student_course_relation_id'] = $student_course_relation_id;
        $regData['course_creation_instance_id'] = $request->course_creation_instance_id;
        $regData['ssn'] = $request->studen_ssn;
        $regData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $regData['academic_year_id'] = $request->academic_year_id;
        $regData['registration_year'] = $request->registration_year;
        $regData['slc_registration_status_id'] = $request->slc_registration_status_id;
        $regData['note'] = $request->note;
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
                $attenData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
                $attenData['session_term'] = $request->session_term;
                $attenData['attendance_code_id'] = $attendance_code_id;
                $attenData['note'] = $request->attendance_note;
                $attenData['created_by'] = auth()->user()->id;

                $slcAttendance = SlcAttendance::create($attenData);
                if($cocRequired):
                    $cocData = [];
                    $cocData['student_id'] = $studen_id;
                    $cocData['student_course_relation_id'] = $student_course_relation_id;
                    $cocData['course_creation_instance_id'] = $request->course_creation_instance_id;
                    $cocData['slc_attendance_id'] = $slcAttendance->id;
                    $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                    $cocData['coc_type'] = 'Outstanding';
                    $cocData['created_by'] = auth()->user()->id;

                    $slcCoc = SlcCoc::create($cocData);
                endif;

                if($slcAttendance && $attendance_code_id == 1):
                    $session_term = $request->session_term;
                    $course_creation_instance_id = $request->course_creation_instance_id;
                    $instanceTerm = InstanceTerm::where('course_creation_instance_id', $course_creation_instance_id)->where('session_term', $session_term)->get()->first();
                    $term = (isset($instanceTerm->term) && !empty($instanceTerm->term) ? $instanceTerm->term : '');

                    $installmentData = [];
                    $installmentData['student_id'] = $studen_id;
                    $installmentData['student_course_relation_id'] = $student_course_relation_id;
                    $installmentData['course_creation_instance_id'] = $request->course_creation_instance_id;
                    $installmentData['slc_attendance_id'] = $slcAttendance->id;
                    $installmentData['slc_agreement_id'] = (isset($slcAgreement->id) ? $slcAgreement->id : null);
                    $installmentData['installment_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                    $installmentData['amount'] = $request->installment_amount;
                    $installmentData['term'] = $term;
                    $installmentData['session_term'] = $session_term;
                    $installmentData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
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
}
