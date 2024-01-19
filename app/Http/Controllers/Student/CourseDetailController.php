<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentCourseDetailsRequest;
use App\Http\Requests\StudentNewCourseAssignedRequest;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\Semester;
use App\Models\StudentArchive;
use App\Models\StudentCourseRelation;
use App\Models\StudentFeeEligibility;
use App\Models\StudentProposedCourse;
use App\Models\TermDeclaration;
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

    public function getSemesterByAcademic(Request $request){
        $res = [];
        $academic_year_id = $request->academic_year_id;
        $termDeclarationIds = TermDeclaration::where('academic_year_id', $academic_year_id)->pluck('id')->unique()->toArray();
        if(!empty($termDeclarationIds)):
            $courseCreationInstanceIds = InstanceTerm::whereIn('term_declaration_id', $termDeclarationIds)->pluck('course_creation_instance_id')->unique()->toArray();
            if(!empty($courseCreationInstanceIds)):
                $courseCreationIds = CourseCreationInstance::where('academic_year_id', $academic_year_id)->whereIn('id', $courseCreationInstanceIds)->pluck('course_creation_id')->unique()->toArray();
                if(!empty($courseCreationIds)):
                    $semesterIds = CourseCreation::whereIn('id', $courseCreationIds)->pluck('semester_id')->unique()->toArray();
                    if(!empty($semesterIds)):
                        $semesters = Semester::whereIn('id', $semesterIds)->orderBy('id', 'DESC')->get();
                        if(!empty($semesters)):
                            $i = 1;
                            foreach($semesters as $sem):
                                $res[$i]['id'] = $sem->id;
                                $res[$i]['name'] = $sem->name;
                                $i++;
                            endforeach;
                        endif;
                    endif;
                endif;
            endif;
        endif;

        if(!empty($res)):
            return response()->json(['res' => $res], 200);
        else:
            return response()->json(['res' => ''], 422);
        endif;
    }

    public function getCourseByAcademicSemester(Request $request){
        $res = [];
        $academic_year_id = $request->academic_year_id;
        $semester_id = $request->semester_id;
        $courseIds = CourseCreation::where('semester_id', $semester_id)->pluck('course_id')->unique()->toArray();
        if(!empty($courseIds)):
            $courses = Course::whereIn('id', $courseIds)->orderBy('id', 'DESC')->get();
            if(!empty($courses)):
                $i = 1;
                foreach($courses as $crs):
                    $res[$i]['id'] = $crs->id;
                    $res[$i]['name'] = $crs->name;

                    $i++;
                endforeach;
            endif;
        endif;
        
        if(!empty($res)):
            return response()->json(['res' => $res], 200);
        else:
            return response()->json(['res' => ''], 422);
        endif;
    }

    public function assignedNewCourse(StudentNewCourseAssignedRequest $request){
        $academic_year_id = $request->academic_year_id;
        $semester_id = $request->semester_id;
        $course_id = $request->course_id;
        $student_id = $request->student_id;
        $student_course_relation_id = $request->student_course_relation_id;
        $studentCourseRel = StudentCourseRelation::find($student_course_relation_id);

        $courseCreationIds = CourseCreationInstance::where('academic_year_id', $academic_year_id)->pluck('course_creation_id')->unique()->toArray();
        $courseCreation = CourseCreation::whereIn('id', $courseCreationIds)->where('course_id', $course_id)->where('semester_id', $semester_id)->orderBy('id', 'DESC')->get()->first();

        if(isset($courseCreation->id) && $courseCreation->id > 0 && $courseCreation->id != $studentCourseRel->course_creation_id):
            $studentOCR = StudentCourseRelation::where('id', $student_course_relation_id)->update(['active' => 0, 'updated_by' => auth()->user()->id]);
            $data = [];
            $data['student_id'] = $student_id;
            $data['table'] = 'student_course_relations';
            $data['field_name'] = 'active';
            $data['field_value'] = '1';
            $data['field_new_value'] = '0';
            $data['created_by'] = auth()->user()->id;

            StudentArchive::create($data);
            $studetNCR = StudentCourseRelation::create([
                'course_creation_id' => $courseCreation->id,
                'student_id' => $student_id,
                'active' => 1,
                'created_by' => auth()->user()->id
            ]);

            $studentProposedCourse = StudentProposedCourse::create([
                'student_course_relation_id' => $studetNCR->id,
                'student_id' => $student_id,
                'academic_year_id' => $academic_year_id,
                'course_creation_id' => $courseCreation->id,
                'semester_id' => $semester_id,
                'created_by' => auth()->user()->id,
            ]);

            return response()->json(['msg' => 'Studdent successfully assigned to new course.'], 200);
        else: 
            $msg = 'Something went wrong. Please try later.';
            if(!isset($courseCreation->id)):
                $msg = 'Course Creation not found.';
            elseif($courseCreation->id == $studentCourseRel->course_creation_id):
                $msg = 'The student already assigned under this course relation.';
            endif;
            return response()->json(['msg' => $msg], 304);
        endif;
    }
}
