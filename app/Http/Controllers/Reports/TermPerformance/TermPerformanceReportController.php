<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
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
                    'cr.name as course_name',
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
}
