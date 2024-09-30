<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;

class ContinuationReportController extends Controller
{
    public function getContinuationReport(Request $request){
        $cr_semester_ids = (isset($request->cr_semester_id) && !empty($request->cr_semester_id) ? $request->cr_semester_id : []);
        
        $res = [];
        if(!empty($cr_semester_ids)):
            $courseCount = 0;
            foreach($cr_semester_ids as $semester_id):
                $semester = Semester::find($semester_id);
                $res['result'][$semester_id]['semester_id'] = $semester_id;
                $res['result'][$semester_id]['name'] = $semester->name;

                $course_ids = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'DESC')->pluck('course_id')->unique()->toArray();
                $semesterAdmission = 0;
                $semesterTerminated = 0;
                $semesterCompletion = 0;
                if(!empty($course_ids)):
                    foreach($course_ids as $course_id):
                        $creation = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->orderBy('id', 'DESC')->get()->first();
                        $courseStartDate = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                        $refund_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 28 days')) : '');
                        $completion_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 380 days')) : '');

                        if($completion_date < date('Y-m-d')):
                            $student_ids = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)->pluck('student_id')->unique()->toArray();
                            if(!empty($student_ids) && count($student_ids) > 0):
                                $courseCount += 1;
                                $totalAdmissionCount = (!empty($student_ids) ? count($student_ids) : 0);

                                $terminatedStudents = Student::whereIn('id', $student_ids)->whereDoesntHave('award')->orWhereDoesntHave('award', function($q){
                                                        $q->whereNull('reference');
                                                    })->pluck('id')->unique()->toArray();
                                $continue_student_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);
                                
                            endif;
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;
    }
}
