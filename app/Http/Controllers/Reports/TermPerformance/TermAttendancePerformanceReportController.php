<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Group;
use App\Models\ModuleCreation;
use App\Models\Plan;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TermAttendancePerformanceReportController extends Controller
{
    /*public function index(Request $request){
        $term_declaration_ids = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
        return view('pages.reports.term-performance.index-old', [
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
    }*/

    public function generateReport(Request $request){
        $term_declaration_id = (isset($request->term_declaration_id) && $request->term_declaration_id > 0 ? $request->term_declaration_id : 0);
        $theTerm = TermDeclaration::find($term_declaration_id);
        $result = $this->getTermAttendance($term_declaration_id);

        $html = '';
        if($term_declaration_id > 0 && !empty($result) && count($result) > 0):
            $overAll = 0;
            $row = 1;
            if($result && !empty($result)):
                $perticipents = $result->sum('TOTAL');
                $attendances = $result->sum('P') + $result->sum('O') + $result->sum('E') + $result->sum('M') + $result->sum('H') + $result->sum('L');
                $overAll = ($attendances > 0 && $perticipents > 0 ? round($attendances * 100 / $perticipents, 2) : 0);
            endif;

            $bgs = ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(255, 159, 64, 0.2)'];
            $bds = ['rgb(75, 192, 192)', 'rgb(54, 162, 235)', 'rgb(153, 102, 255)', 'rgb(255, 99, 132)', 'rgb(255, 159, 64)'];
           
            $html .= '<div class="overflow-x-auto scrollbar-hidden mt-5" id="attendanceRateWrap">';
                $html .= '<div class="grid grid-cols-12 gap-0">';
                    $html .= '<div class="col-span-12">';
                        $html .= '<div class="chartWrap mb-7" style="max-width: 70%;">';
                            $html .= '<canvas height="300" id="attendanceRateBarChart"></canvas>';
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<table class="table table-bordered table-sm" id="attendanceRateOvTable" data-title="'.(isset($theTerm->name) && !empty($theTerm->name) ? $theTerm->name : 'Undefined').'">';
                    $html .= '<tbody>';
                        if($result && !empty($result)):
                            $html .= '<tr class="rateRow" data-label="Overall" data-rate="'.($overAll > 0 ? $overAll : 0).'" data-bg="'.$bgs[0].'" data-bd="'.$bds[0].'">';
                                $html .= '<td class="w-20">';
                                    $html .= '<div class="form-check m-0 justify-center">';
                                        $html .= '<input checked id="rateRowCheck_0" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">';
                                    $html .= '</div>';
                                $html .= '</td>';
                                $html .= '<th>Overall</th>';
                                $html .= '<th>';
                                    $html .= $overAll > 0 ? $overAll.'%' : '0.00%';
                                $html .= '</th>';
                            $html .= '</tr>';
                            foreach($result as $res):
                                $html .= '<tr class="rateRow" data-label="'.$res->course_name.'" data-rate="'.($res->percentage_withexcuse > 0 ? round($res->percentage_withexcuse, 2) : 0).'" data-bg="'.$bgs[$row].'" data-bd="'.$bds[$row].'">';
                                    $html .= '<td class="w-20">';
                                        $html .= '<div class="form-check m-0 justify-center">';
                                            $html .= '<input checked id="rateRowCheck_'.$row.'" class="form-check-input rateRowCheck" type="checkbox" name="rateRowCheck[]" value="1">';
                                        $html .= '</div>';
                                    $html .= '</td>';    
                                    $html .= '<th><a href="'.route('reports.term.performance.course.view', [$term_declaration_id, $res->course_id]).'">'.$res->course_name.'</a></th>';
                                    $html .= '<th>'.($res->percentage_withexcuse > 0 ? number_format(round($res->percentage_withexcuse, 2), 2).'%' : '0.00%').'</th>';
                                $html .= '</tr>';
                                $row++;
                            endforeach;
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
            $html .= '</div>';
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-center mt-5" role="alert">';
                $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not found';
            $html .= '</div>';
        endif;

        return response()->json(['htm' => $html], 200);
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
                ->whereNull('atn.deleted_at')
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
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
        $course_ids = Plan::where('term_declaration_id', $term_id)->pluck('course_id')->unique()->toArray();
        sort($course_ids);

        $res = [];
        if(!empty($start_date) && !empty($end_date)):
            $week = 1;
            while (strtotime($theStart) <= strtotime($theEnd)):
                $batchStart = $theStart;
                $batchEnd = date("Y-m-d", strtotime("+6 day", strtotime($theStart)));
                $batchEnd = ($batchEnd > $end_date ? $end_date : $batchEnd);
                
                $res[$week]['start'] = $batchStart;
                $res[$week]['end'] = $batchEnd;

                $TOTAL = $ATTENDANCE = 0;
                foreach($course_ids as $course_id):
                    $row = DB::table('attendances as atn')
                            ->select(
                                DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                                DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                                /*DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),*/
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                                DB::raw('(SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END)) as TOTALATTENDANCE')
                            )
                            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                            ->leftJoin('courses as cr', 'pln.course_id', 'cr.id')
                            ->leftJoin('students as std', 'atn.student_id', 'std.id')
                            ->whereIn('atn.plan_id', $plan_ids)
                            ->whereIn('atn.student_id', $student_ids)
                            ->where('pln.course_id', $course_id)
                            ->whereNull('atn.deleted_at')
                            ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
                            ->where(function($q) use($batchStart, $batchEnd){
                                $q->whereDate('atn.attendance_date', '>=', $batchStart)->whereDate('atn.attendance_date', '<=', $batchEnd);
                            })->get()->first();

                    $TOTAL += (isset($row->TOTAL) && $row->TOTAL > 0 ? $row->TOTAL : 0);
                    $ATTENDANCE += (isset($row->TOTALATTENDANCE) && $row->TOTALATTENDANCE > 0 ? $row->TOTALATTENDANCE : 0);
                    
                    $res[$week]['rows'][$course_id] = (!empty($row) ? $row : []);
                endforeach;
                $res[$week]['overall_attendance'] = ($ATTENDANCE > 0 ? $ATTENDANCE : 0);
                $res[$week]['overall_count'] = ($TOTAL > 0 ? $TOTAL : 0);
                $res[$week]['overall'] = ($TOTAL > 0 && $ATTENDANCE > 0 ? round($ATTENDANCE * 100 / $TOTAL, 2) : 0);
                
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
        $plan_ids = Plan::where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();
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
                ->where('pln.course_id', $course_id)
                ->whereIn('atn.plan_id', $plan_ids)
                ->whereIn('atn.student_id', $student_ids)
                ->whereNull('atn.deleted_at')
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
                ->groupBy('gr.name')->orderBy('gr.name', 'ASC')->get();
        return $query;
    }

    public function courseTrendView($term_id, $course_id){
        $plan_ids = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
        return view('pages.reports.term-performance.course-trend', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Course Trend', 'href' => 'javascript:void(0);']
            ],
            'term' => TermDeclaration::find($term_id),
            'course' => Course::find($course_id),
            'groups' => DB::table('plans as pln')
                        ->select('gr.name as group_name', 'gr.id as groups_id')
                        ->leftJoin('groups as gr', 'pln.group_id', 'gr.id')
                        ->whereIn('pln.id', $plan_ids)->groupBy('gr.name')->orderBy('gr.name', 'ASC')->get(),
            'result' => $this->getTermCourseTrendAttendance($term_id, $course_id)
        ]);
    }

    public function getTermCourseTrendAttendance($term_id, $course_id){
        $term_declaration = TermDeclaration::find($term_id);
        $start_date = $theStart = (isset($term_declaration->start_date) && !empty($term_declaration->start_date) ? date('Y-m-d', strtotime($term_declaration->start_date)) : '');
        $end_date = $theEnd = (isset($term_declaration->end_date) && !empty($term_declaration->end_date) ? date('Y-m-d', strtotime($term_declaration->end_date)) : '');

        $plan_ids = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $groups = DB::table('plans as pln')
                ->select('gr.name as group_name', 'gr.id as group_id')
                ->leftJoin('groups as gr', 'pln.group_id', 'gr.id')
                ->whereIn('pln.id', $plan_ids)->groupBy('gr.name')->orderBy('gr.name', 'ASC')->get();

        $res = [];
        if(!empty($start_date) && !empty($end_date)):
            $week = 1;
            while (strtotime($theStart) <= strtotime($theEnd)):
                $batchStart = $theStart;
                $batchEnd = date("Y-m-d", strtotime("+6 day", strtotime($theStart)));
                $batchEnd = ($batchEnd > $end_date ? $end_date : $batchEnd);
                
                $res[$week]['start'] = $batchStart;
                $res[$week]['end'] = $batchEnd;

                $TOTAL = $ATTENDANCE = 0;
                foreach($groups as $gr):
                    $group_ids = Group::where('name', $gr->group_name)->where('course_id', $course_id)->where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();
                    $row = DB::table('attendances as atn')
                        ->select(
                            DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                            DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                            /*DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                            DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                            DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),*/
                            DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                            DB::raw('(SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END)) as TOTALATTENDANCE')
                        )
                        ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                        ->leftJoin('students as std', 'atn.student_id', 'std.id')
                        ->whereIn('atn.plan_id', $plan_ids)
                        ->whereIn('atn.student_id', $student_ids)
                        ->whereNull('atn.deleted_at')
                        ->whereIn('pln.group_id', $group_ids)
                        ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
                        ->where(function($q) use($batchStart, $batchEnd){
                            $q->whereDate('atn.attendance_date', '>=', $batchStart)->whereDate('atn.attendance_date', '<=', $batchEnd);
                        })->get()->first();
                    $TOTAL += (isset($row->TOTAL) && $row->TOTAL > 0 ? $row->TOTAL : 0);
                    $ATTENDANCE += (isset($row->TOTALATTENDANCE) && $row->TOTALATTENDANCE > 0 ? $row->TOTALATTENDANCE : 0);

                    $res[$week]['rows'][str_replace(' ', '_', $gr->group_name)] = (!empty($row) ? $row : []);
                endforeach;
                $res[$week]['overall_attendance'] = ($ATTENDANCE > 0 ? $ATTENDANCE : 0);
                $res[$week]['overall_count'] = ($TOTAL > 0 ? $TOTAL : 0);
                $res[$week]['overall'] = ($TOTAL > 0 && $ATTENDANCE > 0 ? round($ATTENDANCE * 100 / $TOTAL, 2) : 0);
                
                $theStart = date("Y-m-d", strtotime("+7 day", strtotime($theStart)));
                $week++;
            endwhile;
        endif;
        //dd($res);
        return $res;
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

        $plan_ids = Plan::where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();
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
                ->where('pln.course_id', $course_id)
                ->whereIn('pln.group_id', $group_ids)
                ->whereNull('atn.deleted_at')
                ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
                ->groupBy('pln.module_creation_id')->orderBy('mc.module_name', 'ASC')->get();
        return $query;
    }

    public function groupTrendView($term_id, $course_id, $group_id){
        $group = Group::find($group_id);
        $group_ids = Group::where('name', $group->name)->where('course_id', $course_id)->where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();
        $module_ids = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->whereIn('group_id', $group_ids)->pluck('module_creation_id')->unique()->toArray();
        return view('pages.reports.term-performance.group-trend', [
            'title' => 'Term Performance Reports - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Term Performance Reports', 'href' => 'javascript:void(0);'],
                ['label' => 'Group Trend', 'href' => 'javascript:void(0);']
            ],
            'term' => TermDeclaration::find($term_id),
            'course' => Course::find($course_id),
            'group' => $group,
            'modules' => ModuleCreation::whereIn('id', $module_ids)->orderBy('id', 'ASC')->get(),
            'result' => $this->getTermCourseGroupTrendAttendance($term_id, $course_id, $group_id)
        ]);
    }

    public function getTermCourseGroupTrendAttendance($term_id, $course_id, $group_id){
        $group = Group::find($group_id);
        $group_ids = Group::where('name', $group->name)->where('course_id', $course_id)->where('term_declaration_id', $term_id)->pluck('id')->unique()->toArray();

        $term_declaration = TermDeclaration::find($term_id);
        $start_date = $theStart = (isset($term_declaration->start_date) && !empty($term_declaration->start_date) ? date('Y-m-d', strtotime($term_declaration->start_date)) : '');
        $end_date = $theEnd = (isset($term_declaration->end_date) && !empty($term_declaration->end_date) ? date('Y-m-d', strtotime($term_declaration->end_date)) : '');

        $plans = Plan::where('term_declaration_id', $term_id)->where('course_id', $course_id)->whereIn('group_id', $group_ids)->get();
        $plan_ids = $plans->pluck('id')->unique()->toArray();
        $module_ids = $plans->pluck('module_creation_id')->unique()->toArray();
        sort($module_ids);
        $student_ids = Assign::whereIn('plan_id', $plan_ids)->pluck('student_id')->unique()->toArray();

        $res = [];
        if(!empty($start_date) && !empty($end_date)):
            $week = 1;
            while (strtotime($theStart) <= strtotime($theEnd)):
                $batchStart = $theStart;
                $batchEnd = date("Y-m-d", strtotime("+6 day", strtotime($theStart)));
                $batchEnd = ($batchEnd > $end_date ? $end_date : $batchEnd);

                $res[$week]['start'] = $batchStart;
                $res[$week]['end'] = $batchEnd;

                $TOTAL = $ATTENDANCE = 0;
                foreach($module_ids as $mod_id):
                    $row = DB::table('attendances as atn')
                            ->select(
                                DB::raw('GROUP_CONCAT(DISTINCT atn.student_id) as student_ids'),

                                DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                                /*DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))* 100 / Count(*), 2) ) as percentage_withoutexcuse'),*/
                                DB::raw('(ROUND((SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)+sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END))*100 / Count(*), 2) ) as percentage_withexcuse'),
                                DB::raw('(SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) + sum(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) + SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END)) as TOTALATTENDANCE')
                            )
                            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                            ->leftJoin('students as std', 'atn.student_id', 'std.id')
                            ->whereIn('atn.plan_id', $plan_ids)
                            ->whereIn('atn.student_id', $student_ids)
                            ->where('pln.module_creation_id', $mod_id)
                            ->whereNull('atn.deleted_at')
                            ->whereIn('std.status_id', [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 45, 13, 15, 16, 17, 18, 20, 33, 36, 47, 48, 50])
                            ->where(function($q) use($batchStart, $batchEnd){
                                $q->whereDate('atn.attendance_date', '>=', $batchStart)->whereDate('atn.attendance_date', '<=', $batchEnd);
                            })->get()->first();
                    $TOTAL += (isset($row->TOTAL) && $row->TOTAL > 0 ? $row->TOTAL : 0);
                    $ATTENDANCE += (isset($row->TOTALATTENDANCE) && $row->TOTALATTENDANCE > 0 ? $row->TOTALATTENDANCE : 0);

                    $res[$week]['rows'][$mod_id] = (!empty($row) ? $row : []);
                endforeach;
                $res[$week]['overall_attendance'] = ($ATTENDANCE > 0 ? $ATTENDANCE : 0);
                $res[$week]['overall_count'] = ($TOTAL > 0 ? $TOTAL : 0);
                $res[$week]['overall'] = ($TOTAL > 0 && $ATTENDANCE > 0 ? round($ATTENDANCE * 100 / $TOTAL, 2) : 0);
                
                $theStart = date("Y-m-d", strtotime("+7 day", strtotime($theStart)));
                $week++;
            endwhile;
        endif;
        //dd($res);
        return $res;
    }
}
