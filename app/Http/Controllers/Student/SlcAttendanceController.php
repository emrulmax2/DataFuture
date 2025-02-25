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
use App\Models\SlcCocDocument;
use App\Models\SlcInstallment;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentDocument;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SlcAttendanceController extends Controller
{
    public function store(SlcAttendanceRequest $request){
        $studen_id = $request->studen_id;
        $student = Student::find($studen_id);
        $student_course_relation_id = (isset($student->activeCR->id) && $student->activeCR->id > 0 ? $student->activeCR->id : null);
        $student_course_creation_id = (isset($student->activeCR->course_creation_id) && $student->activeCR->course_creation_id > 0 ? $student->activeCR->course_creation_id : null);
        $slc_registration_id = $request->slc_registration_id;
        $instance_fees = $request->instance_fees;

        $slcReg = SlcRegistration::find($slc_registration_id);

        $attendance_code_id = $request->attendance_code_id;
        $attendanceCode = AttendanceCode::find($attendance_code_id);
        $cocRequired = (isset($attendanceCode->coc_required) && $attendanceCode->coc_required == 1 ? true : false);

        $attenData = [];
        $attenData['student_id'] = $studen_id;
        $attenData['course_relation_id'] = $student_course_creation_id;
        $attenData['student_course_relation_id'] = (isset($slcReg->student_course_relation_id) && $slcReg->student_course_relation_id > 0 ? $slcReg->student_course_relation_id : $student_course_relation_id);
        $attenData['course_creation_instance_id'] = (isset($slcReg->course_creation_instance_id) && $slcReg->course_creation_instance_id > 0 ? $slcReg->course_creation_instance_id : null);
        $attenData['slc_registration_id'] = ($slc_registration_id > 0 ? $slc_registration_id : 0);
        $attenData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $attenData['attendance_year'] = (isset($request->attendance_year) && $request->attendance_year > 0 ? $request->attendance_year : null);
        $attenData['term_declaration_id'] = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
        $attenData['session_term'] = $request->session_term;
        $attenData['attendance_code_id'] = $attendance_code_id;
        $attenData['note'] = $request->attendance_note;
        $attenData['created_by'] = auth()->user()->id;

        $slcAttendance = SlcAttendance::create($attenData);
        if($slcAttendance):
            if($slc_registration_id > 0):
                if($cocRequired):
                    $cocData = [];
                    $cocData['student_id'] = $studen_id;
                    $cocData['student_course_relation_id'] = $slcReg->student_course_relation_id;
                    $cocData['course_creation_instance_id'] = $slcReg->course_creation_instance_id;
                    $cocData['slc_registration_id'] = $slc_registration_id;
                    $cocData['slc_attendance_id'] = $slcAttendance->id;
                    $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
                    $cocData['coc_type'] = 'Outstanding';
                    $cocData['created_by'] = auth()->user()->id;

                    $slcCoc = SlcCoc::create($cocData);
                endif;

                $session_term = $request->session_term;
                $course_creation_instance_id = $slcReg->course_creation_instance_id;

                $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
                $termDeclaration = TermDeclaration::find($term_declaration_id);
                $term_type_id = (isset($termDeclaration->term_type_id) && $termDeclaration->term_type_id > 0 ? $termDeclaration->term_type_id : null);

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
                    $installmentData['term_type_id'] = $term_type_id;
                    $installmentData['session_term'] = $session_term;
                    $installmentData['term_declaration_id'] = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
                    $installmentData['created_by'] = auth()->user()->id;

                    $installment = SlcInstallment::create($installmentData);
                endif;
            endif;

            return response()->json(['res' => 'Attendance successfully inserted.'], 200);
        else: 
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function edit(Request $request){
        $attendance_id = $request->attendance_id;
        $slcAttendance = SlcAttendance::find($attendance_id);
        $regYear = (isset($slcAttendance->registration->registration_year) && $slcAttendance->registration->registration_year > 0 ? $slcAttendance->registration->registration_year : 0);
        if(isset($slcAttendance->attendance_year) && ($slcAttendance->attendance_year == 0 || empty($slcAttendance->attendance_year))):
            $slcAttendance->attendance_year = $regYear;
        endif;

        return response()->json(['res' => $slcAttendance], 200);
    }

    public function update(SlcAttendanceUpdateRequest $request){
        $slc_attendance_id = $request->slc_attendance_id;
        
        $attendanceOldRow = SlcAttendance::find($slc_attendance_id);
        
        $attendance_code_id = $request->attendance_code_id;
        $attendanceCode = AttendanceCode::find($attendance_code_id);
        $cocRequired = (isset($attendanceCode->coc_required) && $attendanceCode->coc_required == 1 ? true : false);

        $attenData = [];
        $attenData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
        $attenData['term_declaration_id'] = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : null);
        $attenData['session_term'] = $request->session_term;
        $attenData['attendance_code_id'] = $attendance_code_id;
        $attenData['note'] = $request->attendance_note;
        $attenData['updated_by'] = auth()->user()->id;

        $attendance = SlcAttendance::find($slc_attendance_id);
        $attendance->fill($attenData);
        $changes = $attendance->getDirty();
        $attendance->save();

        if($attendance->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                StudentArchive::create([
                    'student_id' => $attendanceOldRow->student_id,
                    'table' => 'slc_attendances',
                    'field_name' => $field,
                    'field_value' => $attendanceOldRow->$field,
                    'field_new_value' => $value,
                    'created_by' => auth()->user()->id
                ]);
            endforeach;
        endif;

        if($cocRequired):
            $existingCoc = SlcCoc::where('slc_attendance_id', $slc_attendance_id)->get()->first();
            $attendanceRow = SlcAttendance::find($slc_attendance_id);

            $cocData = [];
            $cocData['student_id'] = $attendanceRow->student_id;
            $cocData['student_course_relation_id'] = $attendanceRow->student_course_relation_id;
            $cocData['course_creation_instance_id'] = $attendanceRow->course_creation_instance_id;
            $cocData['slc_registration_id'] = $attendanceRow->slc_registration_id;
            $cocData['slc_attendance_id'] = $slc_attendance_id;
            $cocData['confirmation_date'] = (!empty($request->confirmation_date) ? date('Y-m-d', strtotime($request->confirmation_date)) : null);
            if(empty($existingCoc) && !isset($existingCoc->id)):
                $cocData['coc_type'] = 'Outstanding';
                $cocData['created_by'] = auth()->user()->id;

                $slcCoc = SlcCoc::create($cocData);
            else:
                $cocData['updated_by'] = auth()->user()->id;

                $slcCoc = SlcCoc::where('slc_attendance_id', $slc_attendance_id)->where('id', $existingCoc->id)->update($cocData);
            endif;
        else:
            $attendanceRow = SlcAttendance::find($slc_attendance_id);
            $student = Student::find($attendanceRow->student_id);
            $allCOC = SlcCoc::where('slc_attendance_id', $slc_attendance_id)->get();
            if(!empty($allCOC) && $allCOC->count() > 0):
                foreach($allCOC as $coc):
                    $cocDocs = SlcCocDocument::where('slc_coc_id', $coc->id)->get();
                    if(!empty($cocDocs) && $cocDocs->count() > 0):
                        foreach($cocDocs as $cdc):
                            if(isset($cdc->current_file_name) && !empty($cdc->current_file_name) && Storage::disk('s3')->exists('public/students/'.$student->id.'/'.$cdc->current_file_name)):
                                Storage::disk('s3')->delete('public/students/'.$student->id.'/'.$cdc->current_file_name);
                            endif;
                        endforeach;
                        SlcCocDocument::where('slc_coc_id', $coc->id)->delete();
                    endif;
                endforeach;
                SlcCoc::where('slc_attendance_id', $slc_attendance_id)->delete();
            endif;
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

        return response()->json(['res' => $res], 200);
    }

    public function checkInstallmentExistence(Request $request){
        $studen_id = $request->studen_id;
        $slc_registration_id = $request->slc_registration_id;
        $attendance_year = $request->attendance_year;
        $session_term = $request->session_term;

        $student = Student::find($studen_id);
        $slcAgreement = SlcAgreement::where('slc_registration_id', $slc_registration_id)->where('year', $attendance_year)
                            ->where('student_id', $studen_id)->where('student_course_relation_id', $student->crel->id)
                            ->get()->first();
        if(isset($slcAgreement->id) && $slcAgreement->id > 0):
            $installment = SlcInstallment::where('student_id', $studen_id)->where('student_course_relation_id', $student->crel->id)
                       ->where('slc_agreement_id', $slcAgreement->id)->where('session_term', $session_term)
                       ->get()->first();

            //if(isset($installment->id) && $installment->id > 0):
            if(isset($installment->id) && $installment->id > 0 && $slcAgreement->is_self_funded != 1):
                return response()->json(['res' => 1], 200);
            else:
                return response()->json(['res' => 0], 200);
            endif;
        else:
            return response()->json(['res' => 2], 200);
        endif;
    }

    public function hasData(Request $request){
        $slc_attendance_id = $request->slc_attendance_id;
        $slcCoc = SlcCoc::where('slc_attendance_id', $slc_attendance_id)->get()->count();
        $slcInst = SlcInstallment::where('slc_attendance_id', $slc_attendance_id)->get()->count();

        if($slcCoc > 0 || $slcInst > 0):
            return response()->json(['res' => 0], 200);
        else:
            return response()->json(['res' => 1], 200);
        endif;
    }

    public function destroy(Request $request){
        $student_id = $request->student;
        $recordid = $request->recordid;

        SlcAttendance::where('student_id', $student_id)->where('id', $recordid)->delete();

        return response()->json(['res' => 'Success'], 200);
    }

    public function syncAttendance(Request $request){
        $student = $request->student;
        $ids = (isset($request->recordid) && !empty($request->recordid) ? explode('_', $request->recordid) : []);
        if(!empty($ids) && count($ids) == 2):
            $slc_reg_id = (isset($ids[0]) && $ids[0] > 0 ? $ids[0] : 0);
            $slc_atn_id = (isset($ids[1]) && $ids[1] > 0 ? $ids[1] : 0);
            if($slc_reg_id > 0 && $slc_atn_id > 0):
                $slcReg = SlcRegistration::find($slc_reg_id);
                $data = [];
                $data['course_relation_id'] = $slcReg->course_relation_id;
                $data['course_creation_instance_id'] = $slcReg->course_creation_instance_id;
                $data['slc_registration_id'] = $slcReg->id;
                $data['attendance_year'] = $slcReg->registration_year;
                SlcAttendance::where('id', $slc_atn_id)->update($data);

                return response()->json(['res' => 'Success'], 200);
            else:
                return response()->json(['res' => 'Error'], 422);
            endif;
        else:
            return response()->json(['res' => 'Error'], 422);
        endif;
    }
}
