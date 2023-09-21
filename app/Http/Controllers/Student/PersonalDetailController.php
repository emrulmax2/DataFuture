<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdmissionPersonalDetailsRequest;
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
        $disability_status = (isset($request->disability_status) && $request->disability_status > 0 ? $request->disability_status : 0);
        $disability_id = ($disability_status == 1 && isset($request->disability_id) && !empty($request->disability_id) ? $request->disability_id : []);
        $disabilty_allowance = ($disability_status == 1 && !empty($disability_id) && (isset($request->disabilty_allowance) && $request->disabilty_allowance > 0) ? $request->disabilty_allowance : 0);

        $request->request->remove('ethnicity_id');
        $request->request->remove('disability_status');
        $request->request->remove('disability_id');
        $request->request->remove('disabilty_allowance');

        $student = Student::find($student_id);
        $student->fill($request->input());
        $changes = $student->getDirty();
        $student->save();

        if($student->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'applicants';
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
            'ethnicity_id' => $ethnicity_id,
            'disability_status' => $disability_status,
            'disability_status' => $disability_status,
            'disabilty_allowance' => $disabilty_allowance,
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
                $applicantDisabilitiesCr = ApplicantDisability::create([
                    'student_id' => $student_id,
                    'disabilitiy_id' => $disabilityID,
                    'created_by' => auth()->user()->id,
                ]);
            endforeach;

            $data = [];
            $data['student_id'] = $student_id;
            $data['table'] = 'student_disabilities';
            $data['field_name'] = 'disabilitiy_id';
            $data['field_value'] = implode(',', $existingIds);
            $data['field_new_value'] = implode(',', $disability_id);
            $data['created_by'] = auth()->user()->id;

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
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endif;
        endif;


        return response()->json(['msg' => 'Personal Data Successfully Updated.'], 200);
    }
}
