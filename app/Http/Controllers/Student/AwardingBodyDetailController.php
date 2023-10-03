<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentArchive;
use App\Models\StudentAwardingBodyDetails;
use Illuminate\Http\Request;

class AwardingBodyDetailController extends Controller
{
    public function update(Request $request){
        $student_id = $request->student_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $id = (isset($request->id) && $request->id > 0 ? $request->id : 0);
        $existRow = StudentAwardingBodyDetails::find($id);

        $data = [];
        $data['student_id'] = $student_id;
        $data['student_course_relation_id'] = $student_course_relation_id;
        $data['reference'] = (isset($request->reference) && !empty($request->reference) ? $request->reference : null);
        $data['course_code'] = (isset($request->course_code) && !empty($request->course_code) ? $request->course_code : null);
        $data['registration_date'] = (isset($request->registration_date) && !empty($request->registration_date) ? $request->registration_date : null);
        $data['registration_expire_date'] = (isset($request->registration_expire_date) && !empty($request->registration_expire_date) ? $request->registration_expire_date : null);
        
        if($id == 0 && empty($existRow)):
            $data['created_by'] = auth()->user()->id;
            StudentAwardingBodyDetails::create($data);
        else:
            $data['updated_by'] = auth()->user()->id;

            $awardingBody = StudentAwardingBodyDetails::find($id);
            $awardingBody->fill($data);
            $changes = $awardingBody->getDirty();
            $awardingBody->save();

            if($awardingBody->wasChanged() && !empty($changes)):
                foreach($changes as $field => $value):
                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['table'] = 'student_awarding_body_details';
                    $data['field_name'] = $field;
                    $data['field_value'] = $existRow->$field;
                    $data['field_new_value'] = $value;
                    $data['created_by'] = auth()->user()->id;

                    StudentArchive::create($data);
                endforeach;
            endif;
        endif;

        return response()->json(['msg' => 'Student awarding body Successfully Updated.'], 200);
    }

    public function updateStatus(Request $request){
        $student_id = $request->student_id;
        $student_crel_id = $request->student_crel_id;
        $row_id = (isset($request->row_id) && $request->row_id > 0 ? (int) $request->row_id : 0);
        $status = (isset($request->status) && !empty($request->status) ? $request->status : '');
        $status = ($status == 'Reset' ? null : $status);

        $existRow = StudentAwardingBodyDetails::find($row_id);
        
        $data = [];
        $data['registration_document_verified'] = $status;
        $data['student_id'] = $student_id;

        if($row_id > 0 && !empty($existRow)):
            $data['updated_by'] = auth()->user()->id;

            $awardingBody = StudentAwardingBodyDetails::find($row_id);
            $awardingBody->fill($data);
            $changes = $awardingBody->getDirty();
            $awardingBody->save();

            if($awardingBody->wasChanged() && !empty($changes)):
                foreach($changes as $field => $value):
                    $data = [];
                    $data['student_id'] = $student_id;
                    $data['table'] = 'student_awarding_body_details';
                    $data['field_name'] = $field;
                    $data['field_value'] = $existRow->$field;
                    $data['field_new_value'] = $value;
                    $data['created_by'] = auth()->user()->id;

                    StudentArchive::create($data);
                endforeach;
            endif;
        else:
            $data['student_course_relation_id'] = $student_crel_id;
            $data['created_by'] = auth()->user()->id;

            StudentAwardingBodyDetails::create($data);
        endif;

        return response()->json(['msg' => 'Student awarding body Successfully Updated.'], 200);
    }
}
