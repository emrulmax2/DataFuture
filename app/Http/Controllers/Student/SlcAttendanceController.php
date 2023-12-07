<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SlcAttendanceRequest;
use App\Http\Requests\SlcAttendanceUpdateRequest;
use App\Models\AttendanceCode;
use App\Models\InstanceTerm;
use App\Models\SlcAgreement;
use App\Models\SlcAttendance;
use App\Models\SlcCoc;
use App\Models\SlcInstallment;
use App\Models\SlcRegistration;
use Illuminate\Http\Request;

class SlcAttendanceController extends Controller
{
    public function store(SlcAttendanceRequest $request){
        $studen_id = $request->studen_id;
        $slc_registration_id = $request->slc_registration_id;
        $instance_fees = $request->instance_fees;

        $slcReg = SlcRegistration::find($slc_registration_id);

        $attendance_code_id = $request->attendance_code_id;
        $attendanceCode = AttendanceCode::find($attendance_code_id);
        $cocRequired = (isset($attendanceCode->coc_required) && $attendanceCode->coc_required == 1 ? true : false);

        $attenData = [];
        $attenData['student_id'] = $studen_id;
        $attenData['student_course_relation_id'] = $slcReg->student_course_relation_id;
        $attenData['course_creation_instance_id'] = $slcReg->course_creation_instance_id;
        $attenData['slc_registration_id'] = $slc_registration_id;
        $attenData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $attenData['attendance_year'] = $request->attendance_year;
        $attenData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
        $attenData['session_term'] = $request->session_term;
        $attenData['attendance_code_id'] = $attendance_code_id;
        $attenData['note'] = $request->attendance_note;
        $attenData['created_by'] = auth()->user()->id;

        $slcAttendance = SlcAttendance::create($attenData);
        if($slcAttendance):
            if($cocRequired):
                $cocData = [];
                $cocData['student_id'] = $studen_id;
                $cocData['student_course_relation_id'] = $slcReg->student_course_relation_id;
                $cocData['course_creation_instance_id'] = $slcReg->course_creation_instance_id;
                $cocData['slc_attendance_id'] = $slcAttendance->id;
                $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                $cocData['coc_type'] = 'Outstanding';
                $cocData['created_by'] = auth()->user()->id;

                $slcCoc = SlcCoc::create($cocData);
            endif;

            $session_term = $request->session_term;
            $course_creation_instance_id = $slcReg->course_creation_instance_id;
            $instanceTerm = InstanceTerm::where('course_creation_instance_id', $course_creation_instance_id)->where('session_term', $session_term)->get()->first();
            $term = (isset($instanceTerm->term) && !empty($instanceTerm->term) ? $instanceTerm->term : '');

            $slcAgreement = SlcAgreement::where('slc_registration_id', $slc_registration_id)->where('year', $slcReg->registration_year)
                            ->where('student_id', $studen_id)->where('student_course_relation_id', $slcReg->student_course_relation_id)
                            ->get()->first();
            $agreementId = (isset($slcAgreement->id) && $slcAgreement->id > 0 ? $slcAgreement->id : null);

            if($attendance_code_id == 1):
                $installmentData = [];
                $installmentData['student_id'] = $studen_id;
                $installmentData['student_course_relation_id'] = $slcReg->student_course_relation_id;
                $installmentData['course_creation_instance_id'] = $course_creation_instance_id;
                $installmentData['slc_attendance_id'] = $slcAttendance->id;
                $installmentData['slc_agreement_id'] = $agreementId;
                $installmentData['installment_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                $installmentData['amount'] = $request->installment_amount;
                $installmentData['term'] = $term;
                $installmentData['session_term'] = $session_term;
                $installmentData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
                $installmentData['created_by'] = auth()->user()->id;

                $installment = SlcInstallment::create($installmentData);
            endif;

            return response()->json(['res' => 'Attendance successfully inserted.'], 200);
        else: 
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function edit(Request $request){
        $attendance_id = $request->attendance_id;
        $slcAttendance = SlcAttendance::find($attendance_id);

        return response()->json(['res' => $slcAttendance], 200);
    }

    public function update(SlcAttendanceUpdateRequest $request){
        $slc_attendance_id = $request->slc_attendance_id;
        
        $attendance_code_id = $request->attendance_code_id;
        $attendanceCode = AttendanceCode::find($attendance_code_id);
        $cocRequired = (isset($attendanceCode->coc_required) && $attendanceCode->coc_required == 1 ? true : false);

        $attenData = [];
        $attenData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $attenData['attendance_year'] = $request->attendance_year;
        $attenData['attendance_term'] = (isset($request->attendance_term) && $request->attendance_term > 0 ? $request->attendance_term : null);
        $attenData['session_term'] = $request->session_term;
        $attenData['attendance_code_id'] = $attendance_code_id;
        $attenData['note'] = $request->attendance_note;
        $attenData['updated_by'] = auth()->user()->id;

        $slcAttendance = SlcAttendance::where('id', $slc_attendance_id)->update($attenData);
        if($cocRequired):
            $existingCoc = SlcCoc::where('slc_attendance_id', $slc_attendance_id)->get()->first();
            if(empty($existingCoc) && !isset($existingCoc->id)):
                $attendanceRow = SlcAttendance::find($slc_attendance_id);

                $cocData = [];
                $cocData['student_id'] = $attendanceRow->student_id;
                $cocData['student_course_relation_id'] = $attendanceRow->student_course_relation_id;
                $cocData['course_creation_instance_id'] = $attendanceRow->course_creation_instance_id;
                $cocData['slc_attendance_id'] = $slc_attendance_id;
                $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                $cocData['coc_type'] = 'Outstanding';
                $cocData['created_by'] = auth()->user()->id;

                $slcCoc = SlcCoc::create($cocData);
            endif;
        else:
            SlcCoc::where('slc_attendance_id', $slc_attendance_id)->forceDelete();
        endif;

        return response()->json(['res' => 'Attendance successfully updated.'], 200);
    }

    public function populateAttendanceForm(Request $request){
        $reg_id = $request->reg_id;
        $slcRegistration = SlcRegistration::find($reg_id);
        $fees = $slcRegistration->instance->fees;
        $year = $slcRegistration->registration_year;

        $res['year'] = $year;
        $res['fees'] = $fees;

        $html = '';
        $instanceTerm = InstanceTerm::where('course_creation_instance_id', $slcRegistration->course_creation_instance_id)->orderBy('session_term', 'ASC')->get();
        if(!empty($instanceTerm) && $instanceTerm->count() > 0):
            $html .= '<option value="">Please Selects</option>';
            foreach($instanceTerm as $insTerm):
                $html .= '<option value="'.$insTerm->session_term.'">Term 0'.$insTerm->session_term.'</option>';
            endforeach;
        else:
            $html .= '<option value="">Please Selects</option>';
        endif;
        $res['sterm'] = $html;

        return response()->json(['res' => $res], 200);
    }
}
