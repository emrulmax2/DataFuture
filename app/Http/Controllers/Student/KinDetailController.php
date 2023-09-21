<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdmissionKinDetailsRequest;

use App\Models\ApplicantKin;
use App\Models\ApplicantArchive;
use App\Models\StudentArchive;
use App\Models\StudentKin;

class KinDetailController extends Controller
{
    public function update(AdmissionKinDetailsRequest $request){
        $student_id = $request->student_id;
        $kinOldRow = StudentKin::find($request->id);

        $kin = StudentKin::find($request->id);
        $kin->fill([
            'name' => $request->name,
            'kins_relation_id' => $request->kins_relation_id,
            'mobile' => $request->kins_mobile,
            'email' => (isset($request->kins_email) && !empty($request->kins_email) ? $request->kins_email : null),
            'address_line_1' => (isset($request->kin_address_line_1) && !empty($request->kin_address_line_1) ? $request->kin_address_line_1 : null),
            'address_line_2' => (isset($request->kin_address_line_2) && !empty($request->kin_address_line_2) ? $request->kin_address_line_2 : null),
            'state' => (isset($request->kin_address_state) && !empty($request->kin_address_state) ? $request->kin_address_state : null),
            'post_code' => (isset($request->kin_address_postal_zip_code) && !empty($request->kin_address_postal_zip_code) ? $request->kin_address_postal_zip_code : null),
            'city' => (isset($request->kin_address_city) && !empty($request->kin_address_city) ? $request->kin_address_city : null),
            'country' => (isset($request->kin_address_country) && !empty($request->kin_address_country) ? $request->kin_address_country : null),
            'updated_by' => auth()->user()->id
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
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Next of Kin Details Successfully Updated.'], 200);
    }
}
