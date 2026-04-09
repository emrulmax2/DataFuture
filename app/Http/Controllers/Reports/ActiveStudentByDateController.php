<?php

namespace App\Http\Controllers\Reports;

use App\Exports\ActiveStudentsExport;
use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Plan;
use App\Models\Student;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ActiveStudentByDateController extends Controller
{
    public function index(){
        return view('pages.reports.active-students.index', [
            'title' => 'Active Students By Date Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Active Students By Date', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $theDate = (isset($request->the_date) && !empty($request->the_date) ? date('Y-m-d', strtotime($request->the_date)) : date('Y-m-d'));
        $termDeclaration = TermDeclaration::where('start_date', '<=', $theDate)->where('end_date', '>=', $theDate)->first();
        $student_ids = [0];
        if(isset($termDeclaration->id) && $termDeclaration->id > 0):
            $plan_ids = Plan::where('term_declaration_id', $termDeclaration->id)->pluck('id')->unique()->toArray();
            $student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : []);
        endif;

        $inactiveStatuses = [14, 16, 17, 21, 22, 26, 27, 30, 31, 33, 36, 42, 43, 45, 46, 47];

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $latestStatusSub = DB::table('student_attendance_term_statuses as s1')
            ->select('s1.student_id', 's1.status_id')
            ->whereRaw('s1.id = (
                SELECT MAX(s2.id)
                FROM student_attendance_term_statuses s2
                WHERE s2.student_id = s1.student_id
                AND s2.created_at <= ?
            )', [$theDate]);

        $query = DB::table('students')
            ->joinSub($latestStatusSub, 'latest_status', function ($join) {
                $join->on('students.id', '=', 'latest_status.student_id');
            })
            ->leftJoin('titles', 'students.title_id', '=', 'titles.id')
            ->leftJoin('statuses', 'students.status_id', '=', 'statuses.id')

            // JOIN extra tables instead of Eloquent (NO N+1)
            ->leftJoin('student_course_relations as cr', function ($join) {
                $join->on('students.id', '=', 'cr.student_id')
                     ->where('cr.active', 1);
            })
            ->leftJoin('course_creations as cc', 'cr.course_creation_id', '=', 'cc.id')
            ->leftJoin('student_proposed_courses as pr', 'cr.id', '=', 'pr.student_course_relation_id')
            ->leftJoin('semesters', 'pr.semester_id', '=', 'semesters.id')
            ->leftJoin('courses', 'cc.course_id', '=', 'courses.id')
            ->leftJoin('student_other_details as so', 'students.id', '=', 'so.student_id')

            ->whereNotIn('latest_status.status_id', $inactiveStatuses)
            ->whereIn('students.id', $student_ids)

            ->select(
                'titles.name as title',
                'students.id',
                'students.registration_no',
                'students.first_name',
                'students.last_name',
                DB::raw('COALESCE(pr.full_time, 0) as full_time'),
                DB::raw('COALESCE(so.disability_status, 0) as disability'),
                // DB::raw('COALESCE(students.multi_agreement_status, 0) as multi_agreement'),
                // DB::raw('CASE WHEN students.due > 0 THEN 1 ELSE 0 END as has_due'),
                'semesters.name as semester',
                'courses.name as course',
                'statuses.name as current_status'
            )->orderBy('students.id', 'DESC');


        

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $studentIds = $query->pluck('id')->toArray();
        $students = Student::whereIn('id', $studentIds)->get()->keyBy('id');

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $student = $students[$list->id] ?? null;
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'first_name' => $list->title.' '.$list->first_name.' '.$list->last_name,
                    'registration_no' => $list->registration_no,
                    'current_status' => $list->current_status,
                    'course_name' => $list->course,
                    'semester_name' => $list->semester,
                    'flag_html' => (isset($student->flag_html) && !empty($student->flag_html) ? $student->flag_html : ''),
                    'due' => (isset($student->due) ? $student->due : 0),
                    'multi_agreement_status' => $student->multi_agreement_status ?? 0,
                    'disability' =>  $list->disability,
                    'full_time' => $list->full_time,
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'total' => $total_rows]);
    }

    public function exportList(Request $request){
        $theDate = $request->the_date ? date('Y-m-d', strtotime($request->the_date)) : date('Y-m-d');

        return Excel::download(
            new ActiveStudentsExport($theDate),
            'Active_Student_By_Date.xlsx'
        );
    }
}
