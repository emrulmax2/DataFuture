<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\ApplicantOtherDetail;
use App\Models\CourseCreation;
use App\Models\CourseCreationVenue;
use App\Models\Option;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ApplicantAnalysisReportController extends Controller
{

    public function generateReport(Request $request){
        $semester_id = (isset($request->ap_an_semester_id) && !empty($request->ap_an_semester_id) ? $request->ap_an_semester_id : 0);
        $html = $this->getHtml($semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($semester_id = 0){
        $semesterNames = ($semester_id > 0 ? Semester::whereIn('id', [$semester_id])->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_id);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Application Analysis Report';
        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$report_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: #1e293b; padding-top: 10px;}
                                table{margin-left: 0px; width: 100%; border-collapse: collapse;}
                                figure{margin: 0;}
                                @page{margin-top: 110px;margin-left: 85px !important; margin-right:85px !important; }

                                header{position: fixed;left: 0px;right: 0px;height: 80px;margin-top: -90px;}
                                .headerTable tr td{vertical-align: top; padding: 0; line-height: 13px;}
                                .headerTable img{height: 70px; width: auto;}
                                .headerTable tr td.reportTitle{font-size: 16px; line-height: 16px; font-weight: bold;}

                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px;margin-bottom: -120px;}
                                .pageCounter{position: relative;}
                                .pageCounter:before{content: counter(page);position: relative;display: inline-block;}
                                .pinRow td{border-bottom: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}
                                @media print{ .pageBreak{page-break-after: always;} }
                                .pageBreak{page-break-after: always;}
                                
                                .mb-15{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}

                                .mt-4 {margin-top: 1rem;}
                                .mb-2 {margin-bottom: 0.5rem;}
                                .w-150px {width: 120px;}
                                .theHeadings{font-size: 1rem; line-height: 1.5rem;}
                                body .table tr th, body .table tr td{ text-align: left;}
                                body .table tr .numberColumn{ width: 150px; }
                                .table.offeredBasicAnalysisTable tr .numberColumn{ width: 80px; }
                                .table.offeredCourseAnalysisTable tr .numberColumn{ width: auto; }
                                .table.offeredCourseAnalysisTable tr .courseName, .table.offeredCourseAnalysisTable tr .venueName{ width: 16.666666%; }
                                .table.courseAnalysisTable tr .numberColumn{ width: 100px; }
                            </style>';
            $PDFHTML .= '</head>';

            $PDFHTML .= '<body>';
                $PDFHTML .= '<header>';
                    $PDFHTML .= '<table class="headerTable">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td colspan="2" class="reportTitle">'.$report_title.'</td>';
                            $PDFHTML .= '<td rowspan="3" class="text-right"><img src="https://sms.londonchurchillcollege.ac.uk/sms_new_copy_2/uploads/LCC_LOGO_01_263_100.png" alt="London Churchill College"/></td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Semister</td>';
                            $PDFHTML .= '<td>'.(!empty($semesterNames) ? implode(', ', $semesterNames) : 'Undefined').'</td>';
                        $PDFHTML .= '</tr>';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td>Cereated By</td>';
                            $PDFHTML .= '<td>';
                                $PDFHTML .= (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
                                $PDFHTML .= '<br/>'.date('jS M, Y').' at '.date('h:i A');
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</header>';

                $PDFHTML .= str_replace(['[150px]', 'style="display: none;"'], ['150px', ''], $html);

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//landscape portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
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
                    $html .= '<th class="numberColumn w-[150px]">'.$totalTarget.'</th>';
                $html .= '</tr>';
            $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<table class="table table-bordered basicAnalysisTable table-sm mt-4" id="basicAnalysisTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>Application Analysis</th>';
                    $html .= '<th class="numberColumn w-[150px]">&nbsp;</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                $html .= '<tr>';
                    $html .= '<th>Total Application</th>';
                    $html .= '<th class="numberColumn w-[150px]">'.($basicAnalysis->count() > 0 ? $basicAnalysis->sum('TOTAL') : '0').'</th>';
                $html .= '</tr>';
                if($basicAnalysis->count() > 0):
                    foreach($basicAnalysis as $ba):
                        $html .= '<tr>';
                            $html .= '<td>'.$ba->status_name.'</td>';
                            $html .= '<td class="numberColumn w-[150px]">'.$ba->TOTAL.'</td>';
                        $html .= '</tr>';
                    endforeach;
                endif;
            $html .= '</tbody>';
        $html .= '</table>';

        if(!empty($courseAnalysis)):
            $html .= '<h2 class="font-medium text-base mr-auto mt-4 mb-2 theHeadings">Analysis By Course</h2>';
            $c = 1;
            foreach($courseAnalysis as $course_id => $course):
                $html .= '<table class="table table-bordered courseAnalysisTable table-sm '.($c > 1 ? 'mt-4' : '').'" id="courseAnalysisTable">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
                            $html .= '<th>Venue</th>';
                            $html .= '<th>Weekdays</th>';
                            $html .= '<th>Weekends</th>';
                            $html .= '<th>Total</th>';
                            $html .= '<th>Mature Entry</th>';
                            $html .= '<th>Academic Entry</th>';
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
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekdays'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekends'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['total'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">&nbsp;</td>';
                                    $html .= '<td class="numberColumn w-[150px]">&nbsp;</td>';
                                $html .= '</tr>';
                                $v++;
                            endforeach;
                        endif;
                        if(isset($course['applications']) && !empty($course['applications']) && count($course['applications']) > 0):
                            $html .= '<tr>';
                                $html .= '<td>Total Application</td>';
                                $html .= '<td class="w-1/6"></td>';
                                $html .= '<td class="numberColumn w-[150px]">'.array_sum(array_column($course['applications'], 'WEEKDAYS')).'</td>';
                                $html .= '<td class="numberColumn w-[150px]">'.array_sum(array_column($course['applications'], 'WEEKENDS')).'</td>';
                                $html .= '<td class="numberColumn w-[150px]">'.array_sum(array_column($course['applications'], 'TOTAL')).'</td>';
                                $html .= '<td class="numberColumn w-[150px]">'.array_sum(array_column($course['applications'], 'MATURE')).'</td>';
                                $html .= '<td class="numberColumn w-[150px]">'.array_sum(array_column($course['applications'], 'ACADEMIC')).'</td>';
                            $html .= '</tr>';
                            foreach($course['applications'] as $row):
                                $html .= '<tr>';
                                    $html .= '<td>'.$row['STATUS_NAME'].'</td>';
                                    $html .= '<td class="numberColumn w-1/6"></td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$row['WEEKDAYS'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$row['WEEKENDS'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$row['TOTAL'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$row['MATURE'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$row['ACADEMIC'].'</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
                $html .= '<div class="pageBreak"></div>';
                $c++;
            endforeach;
        else:
            $html .= '<div class="pageBreak"></div>';
        endif;

        $offeredAnalysis = $this->getOfferedStudentsAnalysis($semester_id);
        $offeredCourseAnalysis = $this->getOfferedStudentsCourseAnalysis($semester_id);
        $no_of_applicants = (isset($offeredCourseAnalysis['no_of_applicants']) && !empty($offeredCourseAnalysis['no_of_applicants']) ? $offeredCourseAnalysis['no_of_applicants'] : 0);
        $offeredCourses = (isset($offeredCourseAnalysis['offeredCourses']) && !empty($offeredCourseAnalysis['offeredCourses']) ? $offeredCourseAnalysis['offeredCourses'] : []);
        $offeredPersonal = (isset($offeredCourseAnalysis['offeredPersonal']) && !empty($offeredCourseAnalysis['offeredPersonal']) ? $offeredCourseAnalysis['offeredPersonal'] : []);
        $html .= '<h2 class="font-medium text-base mr-auto mt-4 mb-2 theHeadings">Offered Students Analysis</h2>';
        $html .= '<table class="table table-bordered offeredBasicAnalysisTable table-sm mt-4" id="offeredBasicAnalysisTable">';
            $html .= '<tbody>';
                if($offeredAnalysis->count() > 0):
                    foreach($offeredAnalysis as $oa):
                        $html .= '<tr>';
                            $html .= '<td>'.$oa->status_name.'</td>';
                            $html .= '<td class="numberColumn w-[150px]">'.$oa->TOTAL.'</td>';
                        $html .= '</tr>';
                    endforeach;
                endif;
            $html .= '</tbody>';
            $html .= '<tfoot>';
                $html .= '<tr>';
                    $html .= '<th>Total</th>';
                    $html .= '<th class="numberColumn w-[150px]">'.($offeredAnalysis->count() > 0 ? $offeredAnalysis->sum('TOTAL') : '0').'</th>';
                $html .= '</tr>';
            $html .= '</tfoot>';
        $html .= '</table>';

        if(!empty($offeredCourses)):
            $c = 1;
            foreach($offeredCourses as $course_id => $course):
                $html .= '<table class="table table-bordered offeredCourseAnalysisTable table-sm mt-4" id="offeredCourseAnalysisTable">';
                    $html .= '<thead '.($c > 1 ? 'style="display: none;"' : '').'>';
                        $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
                            $html .= '<th>Venue</th>';
                            $html .= '<th>Weekdays</th>';
                            $html .= '<th>Weekends</th>';
                            $html .= '<th>Total Target</th>';
                            $html .= '<th>Weekdays</th>';
                            $html .= '<th>Weekends</th>';
                            $html .= '<th>Offered</th>';
                            $html .= '<th>Mature Entry</th>';
                            $html .= '<th>Academic Entry</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        if(!empty($course['venues'])):
                            $v = 1;
                            foreach($course['venues'] as $venue_id => $venue):
                                $html .= '<tr>';
                                    if($v == 1):
                                        $html .= '<td class="courseName" '.(count($course['venues']) > 1 ? ' rowspan="'.count($course['venues']).'" ' : '').'>'.$course['name'].'</td>';
                                    endif;
                                    $html .= '<td class="w-1/6">'.$venue['name'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekdays_trget'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekends_trget'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['total_trget'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekdays_offered'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['weekends_offered'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['total_offered'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['mature_entry'].'</td>';
                                    $html .= '<td class="numberColumn w-[150px]">'.$venue['academic_entry'].'</td>';
                                $html .= '</tr>';
                                $v++;
                            endforeach;
                        endif;
                    $html .= '</tbody>';
                $html .= '</table>';
                $c++;
            endforeach;
        endif;

        if(!empty($offeredPersonal)):
            $gender = (isset($offeredPersonal['gender']) && !empty($offeredPersonal['gender']) ? $offeredPersonal['gender'] : []);
            $age = (isset($offeredPersonal['age']) && !empty($offeredPersonal['age']) ? $offeredPersonal['age'] : []);
            $avg_age = (isset($offeredPersonal['avg_age']) && $offeredPersonal['avg_age'] > 0 ? $offeredPersonal['avg_age'] : 0);
            $nationality = (isset($offeredPersonal['nationality']) && !empty($offeredPersonal['nationality']) ? $offeredPersonal['nationality'] : []);
            $html .= '<table class="table table-borderless table-sm mt-4" style="border: none;">';
                $html .= '<tbody>';
                    $html .= '<tr>';
                        $html .= '<td class="w-2/6" style="border: none; padding: 0 12px 0 0; vertical-align: top;">';
                            $html .= '<table class="table table-sm table-bordered">';
                                $html .= '<tr>';
                                    $html .= '<th colspan="3" class="text-left">Gender</th>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="text-left">Male Applicants</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['male']) && $gender['male'] > 0 ? $gender['male'] : 0).'</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['male']) && $gender['male'] > 0 && $no_of_applicants > 0 ? number_format(($gender['male'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="text-left">Female Applicants</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['female']) && $gender['female'] > 0 ? $gender['female'] : 0).'</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['female']) && $gender['female'] > 0 && $no_of_applicants > 0 ? number_format(($gender['female'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                                $html .= '</tr>';
                                $html .= '<tr>';
                                    $html .= '<td class="text-left">Other Applicants</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['other']) && $gender['other'] > 0 ? $gender['other'] : 0).'</td>';
                                    $html .= '<td class="w-1/6">'.(isset($gender['other']) && $gender['other'] > 0 && $no_of_applicants > 0 ? number_format(($gender['other'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        $html .= '</td>';
                        $html .= '<td class="w-2/6" style="border: none; padding: 0 12px; vertical-align: top;">';
                            if(!empty($nationality)):
                                $html .= '<table class="table table-sm table-bordered">';
                                    $html .= '<tr>';
                                        $html .= '<th colspan="3" class="text-left">Nationality</th>';
                                    $html .= '</tr>';
                                    foreach($nationality as $nation):
                                        $html .= '<tr>';
                                            $html .= '<td class="text-left">'.(isset($nation['name']) && !empty($nation['name']) ? $nation['name'] : '').'</td>';
                                            $html .= '<td class="w-1/6">'.(isset($nation['applicants']) && $nation['applicants'] > 0 ? $nation['applicants'] : 0).'</td>';
                                            $html .= '<td class="w-1/6">'.(isset($nation['applicants']) && $nation['applicants'] > 0 && $no_of_applicants > 0 ? number_format(($nation['applicants'] / $no_of_applicants) * 100, 2) : 0).'%</td>';
                                        $html .= '</tr>';
                                    endforeach;
                                $html .= '</table>';
                            endif;
                        $html .= '</td>';
                        $html .= '<td class="w-2/6" style="border: none; padding: 0 0 0 12px; vertical-align: top;">';
                            $html .= '<table class="table table-sm table-bordered">';
                                $html .= '<tr>';
                                    $html .= '<th colspan="3" class="text-left">Age</th>';
                                $html .= '</tr>';
                                if(!empty($age)):
                                    foreach($age as $label => $ag):
                                        $html .= '<tr>';
                                            $html .= '<td class="text-left">Applicants Aged '.$label.'</td>';
                                            $html .= '<td class="w-1/6">'.$ag.'</td>';
                                            $html .= '<td class="w-1/6">'.($ag > 0 && $no_of_applicants > 0 ? number_format(($ag / $no_of_applicants) * 100, 2) : 0).'%</td>';
                                        $html .= '</tr>';
                                    endforeach;
                                endif;
                                $html .= '<tr>';
                                    $html .= '<td class="text-left">Mean Application Age</td>';
                                    $html .= '<td class="w-1/6">'.($avg_age > 0 ? $avg_age : '').'</td>';
                                    $html .= '<td class="w-1/6">&nbsp;</td>';
                                $html .= '</tr>';
                            $html .= '</table>';
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</tbody>';
            $html .= '</table>';
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
                            DB::raw('GROUP_CONCAT(DISTINCT(apc.applicant_id)) as applicant_ids'),
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
                if(!empty($applications) && count($applications) > 0):
                    foreach($applications as $appcnt):
                        $applicant_ids = (isset($appcnt->applicant_ids) && !empty($appcnt->applicant_ids) ? explode(',', str_replace(' ', '', $appcnt->applicant_ids)) : []);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['STATUS_NAME'] = (isset($appcnt->status_name) && !empty($appcnt->status_name) ? $appcnt->status_name : '');
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['TOTAL'] = (isset($appcnt->TOTAL) && $appcnt->TOTAL > 0 ? $appcnt->TOTAL : 0);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['WEEKDAYS'] = (isset($appcnt->WEEKDAYS) && $appcnt->WEEKDAYS > 0 ? $appcnt->WEEKDAYS : 0);
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['WEEKENDS'] = (isset($appcnt->WEEKENDS) && $appcnt->WEEKENDS > 0 ? $appcnt->WEEKENDS : 0);

                        $academicEntry = (!empty($applicant_ids) ? ApplicantOtherDetail::whereIn('applicant_id', $applicant_ids)->where('is_edication_qualification', 1)->get()->count() : 0);
                        $matureEntry = 0;
                        if(!empty($applicant_ids)):
                            $matureEntry = Applicant::whereIn('id', $applicant_ids)->whereHas('other', function($q){
                                                $q->whereNotNull('employment_status');
                                            })->whereHas('employment')->get()->count();
                        endif;
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['MATURE'] = $matureEntry;
                        $res[$creation->course_id]['applications'][$appcnt->status_id]['ACADEMIC'] = $academicEntry;
                    endforeach;
                endif;
                //$res[$creation->course_id]['applications'] = $applications;
            endforeach;
        endif; 

        return $res;
    }

    public function getOfferedStudentsAnalysis($semester_id){
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
                 ->whereIn('ap.status_id', [5, 6, 7])
                 ->groupBy('ap.status_id')->orderBy('ap.status_id', 'ASC')
                 ->get();
        return $Query;
    }

    public function getOfferedStudentsCourseAnalysis($semester_id){
        $res = [];
        $offeredApplicants = [];
        $creations = CourseCreation::where('semester_id', $semester_id)->get();
        if($creations->count() > 0):
            foreach($creations as $creation):
                $res[$creation->course_id]['name'] = (isset($creation->course->name) && !empty($creation->course->name) ? $creation->course->name : '');
                $creationVenues = CourseCreationVenue::where('course_creation_id', $creation->id)->get();
                if($creationVenues->count() > 0):
                    foreach($creationVenues as $venue):
                        $res[$creation->course_id]['venues'][$venue->venue_id]['name'] = (isset($venue->venue->name) && !empty($venue->venue->name) ? $venue->venue->name : '');
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays_trget'] = ($venue->weekdays > 0 ? $venue->weekdays : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends_trget'] = ($venue->weekends > 0 ? $venue->weekends : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total_trget'] = (($venue->weekends > 0 ? $venue->weekends : 0) + ($venue->weekdays > 0 ? $venue->weekdays : 0));

                        $query = DB::table('applicant_proposed_courses as apc')
                                ->select(
                                    'sts.name as status_name', 'ap.status_id',
                                    DB::raw('GROUP_CONCAT(DISTINCT(apc.applicant_id)) as applicant_ids'),
                                    DB::raw('COUNT(ap.id) as TOTAL'),
                                    DB::raw('SUM(CASE WHEN apc.full_time = 0 THEN 1 ELSE 0 END) AS WEEKDAYS'), 
                                    DB::raw('SUM(CASE WHEN apc.full_time = 1 THEN 1 ELSE 0 END) AS WEEKENDS'), 
                                )
                                ->leftJoin('applicants as ap', 'apc.applicant_id', 'ap.id')
                                ->leftJoin('statuses as sts', 'ap.status_id', 'sts.id')
                                ->where('apc.course_creation_id', $creation->id)
                                ->where('apc.semester_id', $semester_id)
                                ->whereIn('ap.status_id', [5, 6, 7])
                                ->where('apc.venue_id', $venue->venue_id)
                                ->get()->first();
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekdays_offered'] = (isset($query->WEEKDAYS) && $query->WEEKDAYS > 0 ? $query->WEEKDAYS : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['weekends_offered'] = (isset($query->WEEKENDS) && $query->WEEKENDS > 0 ? $query->WEEKENDS : 0);
                        $res[$creation->course_id]['venues'][$venue->venue_id]['total_offered'] = (isset($query->TOTAL) && $query->TOTAL > 0 ? $query->TOTAL : 0);

                        $applicant_ids = (isset($query->applicant_ids) && !empty($query->applicant_ids) ? explode(',', str_replace(' ', '', $query->applicant_ids)) : []);
                        $offeredApplicants = array_merge($offeredApplicants, $applicant_ids);
                        $academicEntry = (!empty($applicant_ids) ? ApplicantOtherDetail::whereIn('applicant_id', $applicant_ids)->where('is_edication_qualification', 1)->get()->count() : 0);
                        $matureEntry = 0;
                        if(!empty($applicant_ids)):
                            $matureEntry = Applicant::whereIn('id', $applicant_ids)->whereHas('other', function($q){
                                                $q->whereNotNull('employment_status');
                                            })->whereHas('employment')->get()->count();
                        endif;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['mature_entry'] = $matureEntry;
                        $res[$creation->course_id]['venues'][$venue->venue_id]['academic_entry'] = $academicEntry;
                    endforeach;
                endif;
            endforeach;
        endif;

        $offeredPersonalDataAnalysis = (!empty($offeredApplicants) && count($offeredApplicants) > 0 ? $this->applicantsPersonalDetailsAnalysis($offeredApplicants) : []);
        return ['no_of_applicants' => (!empty($offeredApplicants) ? count($offeredApplicants) : 0), 'offeredCourses' => $res, 'offeredPersonal' => $offeredPersonalDataAnalysis];
    }

    public function applicantsPersonalDetailsAnalysis($applicants){
        $res = [];
        $res['gender']['male'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 2)->get()->count();
        $res['gender']['female'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 1)->get()->count();
        $res['gender']['other'] = Applicant::whereIn('id', $applicants)->where('sex_identifier_id', 3)->get()->count();

        $today = date('Y-m-d');
        $res['age']['18-21'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -18 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -21 years')))
                               ->get()->count();
        $res['age']['21-29'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -21 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -29 years')))
                               ->get()->count();
        $res['age']['30-39'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -30 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -39 years')))
                               ->get()->count();
        $res['age']['40-49'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -40 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -49 years')))
                               ->get()->count();
        $res['age']['50-59'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -50 years')))
                               ->where('date_of_birth', '>=', date('Y-m-d', strtotime($today.' -59 years')))
                               ->get()->count();
        $res['age']['60 and over'] = Applicant::whereIn('id', $applicants)->where('date_of_birth', '<=', date('Y-m-d', strtotime($today.' -60 years')))
                               ->get()->count();
        $avgage = DB::table('applicants')
                    ->select(DB::raw('ROUND(AVG(TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()))) AS average_age'))
                    ->whereNotNull('date_of_birth')
                    ->get()->first();
        $res['avg_age'] = (isset($avgage->average_age) && $avgage->average_age > 0 ? $avgage->average_age : 0);

        $nationalities = DB::table('applicants as ap')
                        ->select('ct.name', 'ap.nationality_id', DB::raw('count(DISTINCT ap.id) as nationality_count'))
                        ->join('countries as ct', 'ap.nationality_id', '=', 'ct.id')
                        ->groupBy('ap.nationality_id')
                        ->whereIn('ap.id', $applicants)
                        ->get();
        if(!empty($nationalities)):
            $i = 1;
            foreach($nationalities as $nations):
                $res['nationality'][$i]['name'] = (isset($nations->name) && !empty($nations->name) ? $nations->name : '');
                $res['nationality'][$i]['applicants'] = (isset($nations->nationality_count) && $nations->nationality_count > 0 ? $nations->nationality_count : 0);

                $i++;
            endforeach;
        endif;

        return $res;
    }
}
