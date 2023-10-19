<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtherPersonalInformationRequest;
use App\Http\Requests\StudentOtherPersonalInformationRequest;
use App\Models\StudentArchive;
use App\Models\StudentDisability;
use App\Models\StudentOtherDetail;
use Illuminate\Http\Request;

class OtherPersonalInformationController extends Controller
{
    public function update(StudentOtherPersonalInformationRequest $request){
        $student_id = $request->student_id;
        $student_other_detail_id = $request->student_other_detail_id;
        $otherDetailsOldRow = StudentOtherDetail::where('student_id', $student_id)->where('id', $student_other_detail_id)->first();

        $disability_status = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
        $disability_id = ($disability_status == 1 && isset($request->disability_id) && !empty($request->disability_id) ? $request->disability_id : []);
        $disabilty_allowance = ($disability_status == 1 && !empty($disability_id) && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0);

        $otherDetails = StudentOtherDetail::where('student_id', $student_id)->where('id', $student_other_detail_id)->first();
        $otherDetails->fill([
            'disability_status' => $disability_status,
            'disabilty_allowance' => $disabilty_allowance
        ]);
        $changes = $otherDetails->getDirty();
        $otherDetails->save();

        if($otherDetails->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_other_details';
                $data['field_name'] = $field;
                $data['field_value'] = $otherDetailsOldRow->$field;
                $data['field_new_value'] = $value;
                $data['student_user_id'] = auth('student')->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        $applicantDisablities = StudentDisability::where('student_id', $student_id)->get();
        $existingIds = [];
        if(!empty($applicantDisablities)):
            foreach($applicantDisablities as $dis):
                $existingIds[] = $dis->disabilitiy_id;
            endforeach;
        endif;
        if($disability_status == 1 && !empty($disability_id)):
            $applicantDisablityDel = StudentDisability::where('student_id', $student_id)->forceDelete();
            foreach($disability_id as $disabilityID):
                $applicantDisabilitiesCr = StudentDisability::create([
                    'student_id' => $student_id,
                    'disability_id' => $disabilityID,
                    'created_by' => auth('student')->user()->id,
                ]);
            endforeach;

            $data = [];
            $data['student_id'] = $student_id;
            $data['table'] = 'student_disabilities';
            $data['field_name'] = 'disabilitiy_id';
            $data['field_value'] = implode(',', $existingIds);
            $data['field_new_value'] = implode(',', $disability_id);
            $data['student_user_id'] = auth('student')->user()->id;

            StudentArchive::create($data);
        else:
            if(!empty($existingIds)):
                $applicantDisablityDel = StudentDisability::where('student_id', $student_id)->forceDelete();
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_disabilities';
                $data['field_name'] = 'disabilitiy_id';
                $data['field_value'] = implode(',', $existingIds);
                $data['field_new_value'] = implode(',', $disability_id);
                $data['student_user_id'] = auth('student')->user()->id;

                StudentArchive::create($data);
            endif;
        endif;

        return response()->json(['msg' => 'Other Personal Informations Successfully Updated.'], 200);
    }
}
