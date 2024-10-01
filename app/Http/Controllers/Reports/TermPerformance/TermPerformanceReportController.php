<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Group;
use App\Models\Plan;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermPerformanceReportController extends Controller
{
    public function index(Request $request){
        $term_declaration_ids = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
        return view('pages.reports.term-performance.index', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);']
            ],
            'terms' => TermDeclaration::orderBy('id', 'DESC')->get(),
            'searched_terms' => ($term_declaration_ids > 0 ? $term_declaration_ids : false), 
            'theTerm' => ($term_declaration_ids > 0 ? TermDeclaration::find($term_declaration_ids) : []), 
            'result' => ($term_declaration_ids > 0 ? $this->getTermAttendance($term_declaration_ids) : false)
        ]);
    }

    public function getTermAttendance($term_declaration_ids){
        $plan_ids = Plan::where('term_declaration_id', $term_declaration_ids)->pluck('id')->unique()->toArray();
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $query = DB::table('attendances as atn')
                ->select(
                    'cr.name as course_name', 'cr.id as course_id',
                    DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                    DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                )
                ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                ->leftJoin('courses as cr', 'pln.course_id', 'cr.id')
                ->leftJoin('students as std', 'atn.student_id', 'std.id')
                ->whereIn('atn.plan_id', $plan_ids)
                ->whereIn('atn.student_id', $student_ids)
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45])
                ->groupBy('pln.course_id')->orderBy('pln.course_id', 'ASC')->get();
        return $query;
    }

    public function viewTermTrend($term_id){
        $course_ids = Plan::where('term_declaration_id', $term_id)->pluck('course_id')->unique()->toArray();
        return view('pages.reports.term-performance.term-trend', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Trend', 'href' => 'javascript:void(0);']
            ],
            'term' => TermDeclaration::find($term_id),
            'courses' => Course::whereIn('id', $course_ids)->orderBy('id', 'ASC')->get(),
            'result' => $this->getTermTrendAttendance($term_id)
        ]);
    }

    public function getTermTrendAttendance($term_id){
        $term_declaration = TermDeclaration::find($term_id);
        $start_date = $theStart = (isset($term_declaration->start_date) && !empty($term_declaration->start_date) ? date('Y-m-d', strtotime($term_declaration->start_date)) : '');
        $end_date = $theEnd = (isset($term_declaration->end_date) && !empty($term_declaration->end_date) ? date('Y-m-d', strtotime($term_declaration->end_date)) : '');

        $plan_ids = Plan::where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $res = [];
        if(!empty($start_date) && !empty($end_date)):
            $week = 1;
            while (strtotime($theStart) <= strtotime($theEnd)):
                $batchStart = $theStart;
                $batchEnd = date("Y-m-d", strtotime("+6 day", strtotime($theStart)));
                $batchEnd = ($batchEnd > $end_date ? $end_date : $batchEnd);
                
                $result = DB::table('attendances as atn')
                        ->select(
                            'cr.name as course_name', 'cr.id as course_id',
                            DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                            DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                            DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                            DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                        )
                        ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                        ->leftJoin('courses as cr', 'pln.course_id', 'cr.id')
                        ->leftJoin('students as std', 'atn.student_id', 'std.id')
                        ->whereIn('atn.plan_id', $plan_ids)
                        ->whereIn('atn.student_id', $student_ids)
                        ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45])
                        ->where(function($q) use($batchStart, $batchEnd){
                            $q->whereDate('atn.attendance_date', '>=', $batchStart)->whereDate('atn.attendance_date', '<=', $batchEnd);
                        })->groupBy('pln.course_id')->orderBy('pln.course_id', 'ASC')->get();
                $res[$week]['start'] = $batchStart;
                $res[$week]['end'] = $batchEnd;
                $res[$week]['result'] = $result;
                
                $theStart = date("Y-m-d", strtotime("+7 day", strtotime($theStart)));
                $week++;
            endwhile;
        endif;

        return $res;
    }

    public function courseView($term_id, $course_id){
        return view('pages.reports.term-performance.course', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Course View', 'href' => 'javascript:void(0);']
            ],
            'term' => TermDeclaration::find($term_id),
            'course' => Course::find($course_id),
            'result' => $this->getTermCourseAttendance($term_id, $course_id)
        ]);
    }

    public function getTermCourseAttendance($term_id, $course_id){
        $plan_ids = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $query = DB::table('attendances as atn')
                ->select(
                    'gr.name as group_name', 'gr.id as group_id',
                    DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                    DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                )
                ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                ->leftJoin('groups as gr', 'pln.group_id', 'gr.id')
                ->leftJoin('students as std', 'atn.student_id', 'std.id')
                ->whereIn('atn.plan_id', $plan_ids)
                ->whereIn('atn.student_id', $student_ids)
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45])
                ->groupBy('gr.name')->orderBy('gr.name', 'ASC')->get();
        return $query;
    }


    public function groupView($term_id, $course_id, $group_id){
        return view('pages.reports.term-performance.group', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Group View', 'href' => 'javascript:void(0);']
            ],
            'term' => TermDeclaration::find($term_id),
            'course' => Course::find($course_id),
            'group' => Group::find($group_id),
            'result' => $this->getTermCourseGroupAttendance($term_id, $course_id, $group_id)
        ]);
    }

    public function getTermCourseGroupAttendance($term_id, $course_id, $group_id){
        $group = Group::find($group_id);
        $group_ids = Group::where('name', $group->name)->where('course_id', $course_id)->where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();

        $plan_ids = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->whereIn('group_id', $group_ids)->pluck('id')->unique()->toArray();
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $query = DB::table('attendances as atn')
                ->select(
                    'mc.module_name', 'mc.id as module_creations_id',
                    DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                    DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                    DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),
                    DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                )
                ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                ->leftJoin('module_creations as mc', 'pln.module_creation_id', 'mc.id')
                ->leftJoin('students as std', 'atn.student_id', 'std.id')
                ->whereIn('atn.plan_id', $plan_ids)
                ->whereIn('atn.student_id', $student_ids)
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45])
                ->groupBy('pln.module_creation_id')->orderBy('mc.module_name', 'ASC')->get();
        return $query;
    }
}
