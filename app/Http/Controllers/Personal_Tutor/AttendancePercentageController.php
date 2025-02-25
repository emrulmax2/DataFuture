<?php

namespace App\Http\Controllers\Personal_Tutor;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Plan;
use App\Models\Student;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendancePercentageController extends Controller
{
    public function index($tutor_id, $term_id){
        // $plan_ids = Plan::where('term_declaration_id', $term_id)->where('personal_tutor_id', $tutor_id)
        // ->whereIn('class_type', ['Tutorial', 'Seminar'])->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();
        $exculdeStatus = [22, 27, 31, 33, 14, 17, 30, 36];
        $plan_ids = Plan::where('term_declaration_id', $term_id)->where(function($q) use($tutor_id){
                        $q->where('tutor_id', $tutor_id)->orWhere('personal_tutor_id', $tutor_id)->orWhereHas('tutorial', function($sq) use($tutor_id){
                            $sq->where('personal_tutor_id', $tutor_id);
                        });
                    })->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();
        $students = [];

        if(!empty($plan_ids)):
            $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();
            if(!empty($student_ids)):
                $attn_student_ids = DB::table('attendances as atn')
                        ->select(
                            'std.id as student_id',
                            DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                        )
                        ->leftJoin('students as std', 'atn.student_id', 'std.id')
                        ->whereIn('atn.plan_id', $plan_ids)
                        ->whereIn('atn.student_id', $student_ids)
                        ->whereNotIn('std.status_id', $exculdeStatus)
                        ->groupBy('atn.student_id')
                        ->havingRaw('percentage_withexcuse < 60 OR round(percentage_withexcuse) = 0')->pluck('student_id')->unique()->toArray();
                if(!empty($attn_student_ids)):
                    foreach($attn_student_ids as $student_id):
                        $student = Student::find($student_id);
                        $students[$student_id] = $student->registration_no.' - '.$student->full_name;
                    endforeach;
                endif;
            endif;
        endif;

        return view('pages.personal-tutor.percentage.index', [
            'title' => 'Students Has Bellow 60% Attendance Rate - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Percentage Bellow 60', 'href' => 'javascript:void(0);'],
            ],
            'terms' => TermDeclaration::find($term_id),
            'tutor' => User::find($tutor_id),
            'students' => $students
        ]);
    }

    public function list(Request $request){
        $student_ids = (isset($request->student_ids) && $request->student_ids > 0 ? $request->student_ids : 0);
        $tutor_id = (isset($request->tutor_id) && $request->tutor_id > 0 ? $request->tutor_id : 0);
        $term_id = (isset($request->term_id) && $request->term_id > 0 ? $request->term_id : 0);
        $exculdeStatus = [22, 27, 31, 33, 14, 17, 30, 36];

        // $plan_ids = Plan::where('term_declaration_id', $term_id)->where('personal_tutor_id', $tutor_id)
        // ->whereIn('class_type', ['Tutorial', 'Seminar'])->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();

        $term_plan_ids = Plan::where('term_declaration_id', $term_id)->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();
        $plan_ids = Plan::where('term_declaration_id', $term_id)->where(function($q) use($tutor_id){
                        $q->where('tutor_id', $tutor_id)->orWhere('personal_tutor_id', $tutor_id)->orWhereHas('tutorial', function($sq) use($tutor_id){
                            $sq->where('personal_tutor_id', $tutor_id);
                        });
                    })->orderBy('id', 'ASC')->pluck('id')->unique()->toArray();
        $assign_student_ids = (!empty($plan_ids) ? Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray() : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'registration_no', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $Query = DB::table('attendances as atn')
                    ->select(
                        'std.id',
                        DB::raw('GROUP_CONCAT(DISTINCT atn.plan_id) as plan_ids'),
                        DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                    )
                    ->leftJoin('students as std', 'atn.student_id', 'std.id')
                    ->whereIn('atn.plan_id', $term_plan_ids)
                    ->whereNotIn('std.status_id', $exculdeStatus)
                    ->groupBy('atn.student_id');
        if($student_ids > 0):
            $Query->where('atn.student_id', $student_ids);
        else:
            $Query->whereIn('atn.student_id', $assign_student_ids);
        endif;
        $Query->havingRaw('percentage_withexcuse < 60 OR round(percentage_withexcuse) = 0');
        $total_rows = $Query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        
        $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
                ->take($limit)
                ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $student = Student::find($list->id);
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'plan_ids' => $list->plan_ids,
                    'disability' =>  (isset($student->other->disability_status) && $student->other->disability_status > 0 ? $student->other->disability_status : 0),
                    'full_time' => (isset($student->activeCR->propose->full_time) && $student->activeCR->propose->full_time > 0) ? $student->activeCR->propose->full_time : 0, 
                    'registration_no' => (!empty($student->registration_no) ? $student->registration_no : $student->application_no),
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'course'=> (isset($student->activeCR->creation->course->name) && !empty($student->activeCR->creation->course->name) ? $student->activeCR->creation->course->name : ''),
                    'semester'=> (isset($student->activeCR->creation->semester->name) && !empty($student->activeCR->creation->semester->name) ? $student->activeCR->creation->semester->name : ''),
                    'status_id'=> (isset($student->status->name) && !empty($student->status->name) ? $student->status->name : ''),
                    'url' => route('student.show', $student->id),
                    'photo_url' => $student->photo_url,
                    'flag_html' => (isset($student->flag_html) && !empty($student->flag_html) ? $student->flag_html : ''),
                    'due' => $student->due,
                    'percentage_withexcuse' => ($list->percentage_withexcuse > 0 ? number_format($list->percentage_withexcuse, 2).'%' : '0.00%')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }
}
