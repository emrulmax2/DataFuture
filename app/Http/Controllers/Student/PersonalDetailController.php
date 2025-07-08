<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdmissionPersonalDetailsRequest;
use App\Http\Requests\StudentOtherIdentificationUpdateRequest;
use App\Http\Requests\StudentPersonalDetailsRequest;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantOtherDetail;
use App\Models\ApplicantDisability;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentDisability;
use App\Models\StudentOtherDetail;

class PersonalDetailController extends Controller
{
    public function update(StudentPersonalDetailsRequest $request){
        $student_id = $request->id;
        $studentOldRow = Student::find($student_id);
        $otherDetailsOldRow = StudentOtherDetail::where('student_id', $student_id)->first();

        $ethnicity_id = $request->ethnicity_id;

        $request->request->remove('ethnicity_id');

        $student = Student::find($student_id);
        $student->fill($request->input());
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'students';
                $data['field_name'] = $field;
                $data['field_value'] = $studentOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;
        $request->request->remove('id');

        $otherDetails = StudentOtherDetail::where('student_id', $student_id)->first();
        $otherDetails->fill([
            'ethnicity_id' => $ethnicity_id
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
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Personal Data Successfully Updated.'], 200);
    }

    public function updateOtherIdentificationDetails(StudentOtherIdentificationUpdateRequest $request){
        $student_id = $request->student_id;
        $studentOldRow = Student::find($student_id);
        $otherDetailsOldRow = StudentOtherDetail::where('student_id', $student_id)->first();

        $study_mode_id = (isset($request->study_mode_id) && $request->study_mode_id > 0 ? $request->study_mode_id : 1);
        $request->request->remove('ethnicity_id');

        $student = Student::find($student_id);
        $student->fill($request->input());
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'students';
                $data['field_name'] = $field;
                $data['field_value'] = $studentOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        $otherDetails = StudentOtherDetail::where('student_id', $student_id)->first();
        $otherDetails->fill([
            'study_mode_id' => $study_mode_id
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
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Student Other Identification Data Successfully Updated.'], 200);
    }
}
