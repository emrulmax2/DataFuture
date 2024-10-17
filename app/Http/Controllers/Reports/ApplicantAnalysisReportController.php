<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantAnalysisReportController extends Controller
{
    public function index(){
        return view('pages.reports.applicant-analysis.index', [
            'title' => 'Applicant Analysis Report - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('reports')],
                ['label' => 'Applicant Analysis Reports', 'href' => 'javascript:void(0);']
            ],
            'semester' => Semester::orderBy('id', 'DESC')->get(),
        ]);
    }

    public function generateReport(Request $request){
        $semester_id = (isset($request->ap_an_semester_id) && !empty($request->ap_an_semester_id) ? $request->ap_an_semester_id : 0);
        $html = $this->getHtml($semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($semester_id = 0){

    }

    public function getHtml($semester_id){
        $html = '';

        $totalTarget = $this->getTotalApplicantTarget($semester_id);
        $basicAnalysis = $this->getApplicationCoreAnalysis($semester_id);
        $courseAnalysis = $this->getApplicationCourseAnalysis($semester_id);
        $html .= '<table class="table table-bordered totalTargetTable table-sm" id="totalTargetTable">';
            $html .= '<tbody>';
                $html .= '<tr>';
                    $html .= '<th>Total Target</th>';
                    $html .= '<th class="w-[150px]">'.$totalTarget.'</th>';
                $html .= '</tr>';
            $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<table class="table table-bordered basicAnalysisTable table-sm mt-4" id="basicAnalysisTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>Application Analysis</th>';
                    $html .= '<th class="w-[150px]">&nbsp;</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                $html .= '<tr>';
                    $html .= '<th>Total Application</th>';
                    $html .= '<th class="w-[150px]">'.($basicAnalysis->count() > 0 ? $basicAnalysis->sum('TOTAL') : '0').'</th>';
                $html .= '</tr>';
                if($basicAnalysis->count() > 0):
                    foreach($basicAnalysis as $ba):
                        $html .= '<tr>';
                            $html .= '<td>'.$ba->status_name.'</td>';
                            $html .= '<td class="w-[150px]">'.$ba->TOTAL.'</td>';
                        $html .= '</tr>';
                    endforeach;
                endif;
            $html .= '</tbody>';
        $html .= '</table>';

        if(!empty($courseAnalysis)):
            foreach($courseAnalysis as $course_id => $course):
                $html .= '<table class="table table-bordered courseAnalysisTable table-sm mt-4" id="courseAnalysisTable">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
                            $html .= '<th>Venue</th>';
                            $html .= '<th>Total</th>';
                            $html .= '<th>Weekdays</th>';
                            $html .= '<th>Weekends</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        if(!empty($course['venues'])):
                            $v = 1;
                            foreach($course['venues'] as $venue_id => $venue):
                                $html .= '<tr>';
                                    if($v == 1):
                                        $html .= '<td '.(count($course['venues']) > 1 ? ' rowspan="'.count($course['venues']).'" ' : '').'>'.$course['name'].'</td>';
                                    endif;
                                    $html .= '<td class="w-1/6">'.$venue['name'].'</td>';
                                    $html .= '<td class="w-[150px]">'.$venue['total'].'</td>';
                                    $html .= '<td class="w-[150px]">'.$venue['weekdays'].'</td>';
                                    $html .= '<td class="w-[150px]">'.$venue['weekends'].'</td>';
                                $html .= '</tr>';
                                $v++;
                            endforeach;
                        endif;
                        if(isset($course['applications']) && $course['applications']->count() > 0):
                            $html .= '<tr>';
                                $html .= '<td>Total Application</td>';
                                $html .= '<td class="w-1/6"></td>';
                                $html .= '<td class="w-[150px]">'.$course['applications']->sum('TOTAL').'</td>';
                                $html .= '<td class="w-[150px]">'.$course['applications']->sum('WEEKDAYS').'</td>';
                                $html .= '<td class="w-[150px]">'.$course['applications']->sum('WEEKENDS').'</td>';
                            $html .= '</tr>';
                            foreach($course['applications'] as $row):
                                $html .= '<tr>';
                                    $html .= '<td>'.$row->status_name.'</td>';
                                    $html .= '<td class="w-1/6"></td>';
                                    $html .= '<td class="w-[150px]">'.$row->TOTAL.'</td>';
                                    $html .= '<td class="w-[150px]">'.$row->WEEKDAYS.'</td>';
                                    $html .= '<td class="w-[150px]">'.$row->WEEKENDS.'</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
            endforeach;
        endif;

        return $html;
    }

    public function getTotalApplicantTarget($semester_id){
        $totalTarget = 0;
        $courseCreationsIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $crVenues = CourseCreationVenue::whereIn('course_creation_id', $courseCreationsIds)->get();
        $totalTarget += $crVenues->sum('weekdays');
        $totalTarget += $crVenues->sum('weekends');

        return $totalTarget;
    }

    public function getApplicationCoreAnalysis($semester_id){
        $courseCreationsIds = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $Query = DB::table('applicant_proposed_courses as apc')
                 ->select(
                    'sts.name as status_name', 'ap.status_id',
                    DB::raw('COUNT(ap.id) as TOTAL'),
                 )
                 ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                 ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                 ->whereIn('apc.course_creation_id', $courseCreationsIds)
                 ->where('apc.semester_id', $semester_id)
                 ->where('ap.status_id', '>', 1)
                 ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                 ->get();
        return $Query;
    }

    public function getApplicationCourseAnalysis($semester_id){
        $res = [];
        $creations = CourseCreation::where('semester_id', $semester_id)->get();
        if($creations->count() > 0):
            foreach($creations as $creation):
                $res[$creation->course_id]['name'] = (isset($creation->course->name) && !empty($creation->course->name) ? $creation->course->name : '');
                $creationVenues = CourseCreationVenue::where('course_creation_id', $creation->id)->get();
                if($creationVenues->count() > 0):
                    foreach($creationVenues as $venue):
                        $res[$creation->course_id]['venues'][$venue->venue_id]['name'] = (isset($venue->venue->name) && !empty($venue->venue->name) ? $venue->venue->name : '');
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays'] = ($venue->weekdays > 0 ? $venue->weekdays : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends'] = ($venue->weekends > 0 ? $venue->weekends : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total'] = (($venue->weekends > 0 ? $venue->weekends : 0) + ($venue->weekdays > 0 ? $venue->weekdays : 0));
                    endforeach;
                endif;

                $applications = DB::table('applicant_proposed_courses as apc')
                        ->select(
                            'sts.name as status_name', 'ap.status_id',
                            DB::raw('COUNT(ap.id) as TOTAL'),
                            DB::raw('SUM(CASE WHEN apc.full_time = 0 THEN 1 ELSE 0 END) AS WEEKDAYS'), 
                            DB::raw('SUM(CASE WHEN apc.full_time = 1 THEN 1 ELSE 0 END) AS WEEKENDS'), 
                        )
                        ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                        ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                        ->where('apc.course_creation_id', $creation->id)
                        ->where('apc.semester_id', $semester_id)
                        ->where('ap.status_id', '>', 1)
                        ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                        ->get();
                $res[$creation->course_id]['applications'] = $applications;
            endforeach;
        endif; 

        return $res;
    }
}
