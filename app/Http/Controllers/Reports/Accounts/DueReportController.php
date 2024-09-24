<?php

namespace App\Http\Controllers\Reports\Accounts;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayCollectionExport;

class DueReportController extends Controller
{
    public function exportExcel(Request $request){
        $semester_ids = (isset($request->due_semester_id) && !empty($request->due_semester_id) ? $request->due_semester_id : []);
        $course_ids = (isset($request->due_course_id) && !empty($request->due_course_id) ? $request->due_course_id : []);
        $status_ids = (isset($request->due_status_id) && !empty($request->due_status_id) ? $request->due_status_id : []);
        $due_date = (isset($request->due_date) && !empty($request->due_date) ? date('Y-m-d', strtotime($request->due_date)) : date('Y-m-d'));

        $creations = CourseCreation::whereIn('semester_id', $semester_ids);
        if(!empty($course_ids)): $creations->whereIn('course_id', $course_ids); endif;
        $creation_ids = $creations->pluck('id')->unique()->toArray();
        $student_ids = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->pluck('student_id')->unique()->toArray();
        //dd($student_ids);

        $Query = Student::whereIn('id', $student_ids);
        if(!empty($status_ids)):
            $Query->whereIn('status_id', $status_ids);
        endif;
        $Query= $Query->get();

        $theCollection[1][] = 'SL No';
        $theCollection[1][] = 'Student ID';
        $theCollection[1][] = 'Course';
        $theCollection[1][] = 'Start Date';
        $theCollection[1][] = 'End Date';
        $theCollection[1][] = 'Status';
        $theCollection[1][] = 'No of Agreement';
        $theCollection[1][] = 'Claim Total';
        $theCollection[1][] = 'Received total';
        $theCollection[1][] = 'Due';

        if(!empty($Query)):
            $i = 1;
            $row = 2;
            foreach($Query as $list):
                $studentCourseRelation = $list->activeCR->id;
                $slcAgreement = SlcAgreement::where('student_course_relation_id', $studentCourseRelation)->where('student_id', $list->id)->where('date', '<=', $due_date)->orderBy('id', 'ASC')->get();
                $agreement_ids = $slcAgreement->pluck('id')->unique()->toArray();

                $installment = SlcInstallment::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('installment_date', '<=', $due_date)->sum('amount');
                $totalReceived = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '!=', 'Refund')->sum('amount');
                $totalRefund = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '=', 'Refund')->sum('amount');
                $due = $installment - $totalReceived + $totalRefund;

                if($due > 0):
                    $theCollection[$row][] = $i;
                    $theCollection[$row][] = $list->registration_no;
                    $theCollection[$row][] = (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : '');
                    $theCollection[$row][] = (isset($list->activeCR->course_start_date) && !empty($list->activeCR->course_start_date) ? date('d-m-Y', strtotime($list->activeCR->course_start_date)) : '');
                    $theCollection[$row][] = (isset($list->activeCR->course_end_date) && !empty($list->activeCR->course_end_date) ? date('d-m-Y', strtotime($list->activeCR->course_end_date)) : '');
                    $theCollection[$row][] = (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : '');
                    $theCollection[$row][] = $slcAgreement->count();
                    $theCollection[$row][] = $installment;
                    $theCollection[$row][] = $totalReceived - $totalRefund;
                    $theCollection[$row][] = $due;

                    $i++;
                    $row++;
                endif;
            endforeach;
        endif;
        
        return Excel::download(new ArrayCollectionExport($theCollection), 'Due_Report.xlsx');
    }

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
