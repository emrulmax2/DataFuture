<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;

class DueReportController extends Controller
{
    

    public function getCourseStatusBySemester(Request $request){
        $theSemesters = (isset($request->theSemesters) && !empty($request->theSemesters) ? $request->theSemesters : []);
        $courses = [];
        $statuses = [];
        if(!empty($theSemesters)):
            $courseCreations = CourseCreation::whereIn('semester_id', $theSemesters)->whereHas('course', function($q){
                $q->where('active', 1);
            })->get();
            $creation_ids = $courseCreations->pluck('id')->unique()->toArray();
            $course_ids = $courseCreations->pluck('course_id')->unique()->toArray();
            $courses = Course::whereIn('id', $course_ids)->orderBy('name', 'asc')->get(['id', 'name'])->toArray();

            $student_ids = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->pluck('student_id')->unique()->toArray();
            $status_ids = Student::whereIn('id', $student_ids)->pluck('status_id')->unique()->toArray();
            $statuses = Status::whereIn('id', $status_ids)->orderBy('name', 'ASC')->get(['id', 'name']);
        endif;

        return response()->json(['courses' => $courses, 'statuses' => $statuses], 200);
    }

    public function getStatusBySemesterCourse(Request $request){
        $theSemesters = (isset($request->theSemesters) && !empty($request->theSemesters) ? $request->theSemesters : []);
        $theCourses = (isset($request->theCourses) && !empty($request->theCourses) ? $request->theCourses : []);

        $statuses = [];
        $creation_ids = CourseCreation::whereIn('semester_id', $theSemesters)->whereIn('course_id', $theCourses)->pluck('id')->unique()->toArray();
        $student_ids = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->pluck('student_id')->unique()->toArray();
        $status_ids = Student::whereIn('id', $student_ids)->pluck('status_id')->unique()->toArray();
        $statuses = Status::whereIn('id', $status_ids)->orderBy('name', 'ASC')->get(['id', 'name']);

        return response()->json(['statuses' => $statuses], 200);
    }
}
