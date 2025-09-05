<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\SlcAgreement;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class StudentDueReportController extends Controller
{
    public function index(){
        return view('pages.reports.student-due.index', [
            'title' => 'Attendance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Attendance Reports', 'href' => 'javascript:void(0);']
            ],
            'semester' => Semester::where('id', '>=', 121)->orderBy('name', 'DESC')->get(),
            'courses' => Course::all(),
            'status' => Status::where('type', 'Student')->get(),
        ]);
    }

    public function list(Request $request){
        $semester_ids = (isset($request->semester_ids) && !empty($request->semester_ids) ? $request->semester_ids : []);
        $course_ids = (isset($request->course_ids) && !empty($request->course_ids) ? $request->course_ids : []);
        $status_ids = (isset($request->status_ids) && !empty($request->status_ids) ? $request->status_ids : [23,24,25,26,27,28,29,30,31,42,42,45]);
        $due_date = date('Y-m-d');

        $creations = CourseCreation::orderBy('id', 'ASC');
        if(!empty($semester_ids)): $creations->whereIn('semester_id', $semester_ids); else: $creations->where('semester_id', '>', 121); endif;
        if(!empty($course_ids)): $creations->whereIn('course_id', $course_ids); endif;
        $creation_ids = $creations->pluck('id')->unique()->toArray();
        $students_ids = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->whereHas('student', function($q) use($status_ids){
                        $q->whereIn('status_id', $status_ids)->where('has_due', 1);
                    })->pluck('student_id')->unique()->toArray();

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Student::with('activeCR')->orderByRaw(implode(',', $sorts))->whereIn('id', (!empty($students_ids) ? $students_ids : [0]))
                ->whereIn('status_id', $status_ids)->where('has_due', 1);

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query = $query->skip($offset)
               ->take($limit)->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $studentCourseRelation = $list->activeCR->id;
                $allAgreementCount = SlcAgreement::where('student_course_relation_id', $studentCourseRelation)->where('student_id', $list->id)
                                    ->get()->count();
                $agreement_ids = SlcAgreement::where('student_course_relation_id', $studentCourseRelation)->where('student_id', $list->id)
                                ->where('date', '<=', $due_date)->where('has_due', 1)->orderBy('id', 'ASC')->get()
                                ->pluck('id')->unique()->toArray();

                $installment = SlcInstallment::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('installment_date', '<=', $due_date)->get();
                $installment_dates = $installment->pluck('installment_date')->unique()->toArray();
                $totalInstallment = $installment->sum('amount');

                $totalReceived = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '!=', 'Refund')->sum('amount');
                $totalRefund = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '=', 'Refund')->sum('amount');
                $receivedTotal = $totalReceived - $totalRefund;
                $due = $totalInstallment - $totalReceived + $totalRefund;

                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'student_id' => $list->registration_no,
                    'course' => (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester' => (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'start_date' => (isset($list->activeCR->course_start_date) && !empty($list->activeCR->course_start_date) ? date('d-m-Y', strtotime($list->activeCR->course_start_date)) : ''),
                    'end_date' => (isset($list->activeCR->course_end_date) && !empty($list->activeCR->course_end_date) ? date('d-m-Y', strtotime($list->activeCR->course_end_date)) : ''),
                    'status' => (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'no_of_agreement' => (!empty($agreement_ids) ? count($agreement_ids) : 0),
                    'no_of_agreement_all' => (!empty($allAgreementCount) ? $allAgreementCount : 0),
                    'claim_total' => ($totalInstallment > 0 ? Number::currency($totalInstallment, 'GBP') : Number::currency(0, 'GBP')),
                    'received_total' =>  ($receivedTotal > 0 ? Number::currency($receivedTotal, 'GBP') : Number::currency(0, 'GBP')),
                    'due' => ($due > 0 ? Number::currency($due, 'GBP') : Number::currency(0, 'GBP')),
                    'due_date' => (!empty($installment_dates) ? implode(', ', $installment_dates) : ''),
                    'url' => route('student.accounts', $list->id),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function excelDownload(Request $request){
        $semester_ids = (isset($request->semester_ids) && !empty($request->semester_ids) ? $request->semester_ids : []);
        $course_ids = (isset($request->course_ids) && !empty($request->course_ids) ? $request->course_ids : []);
        $status_ids = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : [23,24,25,26,27,28,29,30,31,42,42,45]);
        $due_date = date('Y-m-d');

        $creations = CourseCreation::orderBy('id', 'ASC');
        if(!empty($semester_ids)): $creations->whereIn('semester_id', $semester_ids); else: $creations->where('semester_id', '>', 121); endif;
        if(!empty($course_ids)): $creations->whereIn('course_id', $course_ids); endif;
        $creation_ids = $creations->pluck('id')->unique()->toArray();
        $students_ids = StudentCourseRelation::whereIn('course_creation_id', $creation_ids)->where('active', 1)->whereHas('student', function($q) use($status_ids){
                        $q->whereIn('status_id', $status_ids)->where('has_due', 1);
                    })->pluck('student_id')->unique()->toArray();

        $Query = Student::with('activeCR')->whereIn('id', (!empty($students_ids) ? $students_ids : [0]))
                ->whereIn('status_id', $status_ids)->where('has_due', 1)->get();

        $theCollection[1][] = 'SL No';
        $theCollection[1][] = 'Student ID';
        $theCollection[1][] = 'Course';
        $theCollection[1][] = 'Intake';
        $theCollection[1][] = 'Status';
        $theCollection[1][] = 'No of Agreement';
        $theCollection[1][] = 'Claim Total';
        $theCollection[1][] = 'Received total';
        $theCollection[1][] = 'Due';
        $theCollection[1][] = 'Due Dates';

        if(!empty($Query)):
            $i = 1;
            $row = 2;
            foreach($Query as $list):
                $studentCourseRelation = $list->activeCR->id;
                $agreement_ids = SlcAgreement::where('student_course_relation_id', $studentCourseRelation)->where('student_id', $list->id)
                                ->where('date', '<=', $due_date)->where('has_due', 1)->orderBy('id', 'ASC')->get()
                                ->pluck('id')->unique()->toArray();

                $installment = SlcInstallment::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('installment_date', '<=', $due_date)->get();
                $installment_dates = $installment->pluck('installment_date')->unique()->toArray();
                $totalInstallment = $installment->sum('amount');

                $totalReceived = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '!=', 'Refund')->sum('amount');
                $totalRefund = SlcMoneyReceipt::whereIn('slc_agreement_id', $agreement_ids)->where('student_id', $list->id)->where('payment_date', '<=', $due_date)->where('payment_type', '=', 'Refund')->sum('amount');
                $receivedTotal = $totalReceived - $totalRefund;
                $due = $totalInstallment - $totalReceived + $totalRefund;

                $theCollection[$row][] = $i;
                $theCollection[$row][] = $list->registration_no;
                $theCollection[$row][] = (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : '');
                $theCollection[$row][] = (!empty($agreement_ids) ? count($agreement_ids) : 0);
                $theCollection[$row][] = ($totalInstallment > 0 ? number_format($totalInstallment, 2, '.', '') : '0.00');
                $theCollection[$row][] = ($receivedTotal > 0 ? number_format($receivedTotal, 2, '.', '') : '0.00');
                $theCollection[$row][] = ($due > 0 ? number_format($due, 2, '.', '') : '0.00');
                $theCollection[$row][] = (!empty($installment_dates) ? implode(', ', $installment_dates) : '');

                $i++;
                $row++;
            endforeach;
        endif;
        
        return Excel::download(new ArrayCollectionExport($theCollection), 'Student_Due_Report.xlsx');
    }
}
