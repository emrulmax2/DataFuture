<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentKinDetailsRequest;
use App\Models\StudentArchive;
use App\Models\StudentKin;
use Illuminate\Http\Request;

class KinDetailController extends Controller
{
    public function update(StudentKinDetailsRequest $request){
        $student_id = $request->student_id;
        $kinOldRow = StudentKin::find($request->id);

        $kin = StudentKin::find($request->id);
        $kin->fill([
            'name' => $request->name,
            'kins_relation_id' => $request->kins_relation_id,
            'mobile' => $request->kins_mobile,
            'email' => (isset($request->kins_email) && !empty($request->kins_email) ? $request->kins_email : null),
            'address_id' => (isset($request->address_id) && !empty($request->address_id) ? $request->address_id : null),
            'updated_by' => auth('student')->user()->id
        ]);
        $changes = $kin->getDirty();
        $kin->save();

        if($kin->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_kins';
                $data['field_name'] = $field;
                $data['field_value'] = $kinOldRow->$field;
                $data['field_new_value'] = $value;
                $data['student_user_id'] = auth('student')->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Next of Kin Details Successfully Updated.'], 200);
    }
}
