<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentCourseDetailsRequest;
use App\Models\StudentArchive;
use App\Models\StudentFeeEligibility;
use App\Models\StudentProposedCourse;
use Illuminate\Http\Request;

class CourseDetailController extends Controller
{
    public function update(StudentCourseDetailsRequest $request){
        $student_id = $request->student_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $ProposedCourseOldRow = StudentProposedCourse::find($request->id);

        $proposedCourse = StudentProposedCourse::find($request->id);
        $proposedCourse->fill([
            'full_time' => (isset($request->full_time) && $request->full_time > 0 ? $request->full_time : 0),
            'updated_by' => auth()->user()->id
        ]);
        $changes = $proposedCourse->getDirty();
        $proposedCourse->save();

        $student_fee_eligibility_id = (isset($request->student_fee_eligibility_id) && $request->student_fee_eligibility_id > 0 ? $request->student_fee_eligibility_id : 0);
        $fee_eligibility_id = (isset($request->fee_eligibility_id) && $request->fee_eligibility_id > 0 ? $request->fee_eligibility_id : 0);
        if($fee_eligibility_id > 0):
            $studentEligibility = StudentFeeEligibility::updateOrCreate([ 'student_id' => $student_id, 'student_course_relation_id' => $student_course_relation_id, 'id' => $student_fee_eligibility_id ], [
                'student_id' => $student_id,
                'student_course_relation_id' => $student_course_relation_id,
                'fee_eligibility_id' => $fee_eligibility_id,
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]);
        endif;

        if($proposedCourse->wasChanged() && !empty($changes)):
            foreach($changes as $field => $value):
                $data = [];
                $data['student_id'] = $student_id;
                $data['table'] = 'student_proposed_courses';
                $data['field_name'] = $field;
                $data['field_value'] = $ProposedCourseOldRow->$field;
                $data['field_new_value'] = $value;
                $data['created_by'] = auth()->user()->id;

                StudentArchive::create($data);
            endforeach;
        endif;

        return response()->json(['msg' => 'Course Details Successfully Updated.'], 200);
    }
}
