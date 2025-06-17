<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\Semester;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SubmissionPassRateReportController extends Controller
{
    public function getSubmissionPassRatReport(Request $request){
        $sub_pass_semester_id = (isset($request->sub_pass_semester_id) && !empty($request->sub_pass_semester_id) ? $request->sub_pass_semester_id : []);
        $html = $this->getHtml($sub_pass_semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printSubmissionPassRatReport($semester_ids = null){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterNames = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_ids);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Intake Submission And Pass Rate Reports';
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
                                .table.attenRateReportTable tr th, .table.attenRateReportTable tr td{ text-align: left;}
                                .table.attenRateReportTable tr th a{ text-decoration: none; color: #1e293b; }
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

                $PDFHTML .= $html;

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $report_title).'.pdf';
        $pdf = PDF::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//landscape portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function getHtml($semester_ids = []){
        $res = $this->refineResult($semester_ids);

        $html = '';
        $html .= '<table class="table table-bordered attenRateReportTable  table-sm" id="continuationListTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="w-2/6">&nbsp;</th>';
                    $html .= '<th>No Of Students</th>';
                    $html .= '<th>Expected Submission</th>';
                    $html .= '<th>Actual Submission</th>';
                    $html .= '<th>Passed</th>';
                    $html .= '<th class="text-right">Submission Rate</th>';
                    $html .= '<th class="text-right">Pass Rate</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($res['result']) && !empty($res['result'])):
                    $courseReplace = [];
                    $semesterReplace = [];
                    $OSTD = $OA = $OC = $OD = $OM = $OP = $OR = $OS = $OU = $OW = $OTOTAL = $OPASS = 0;
                    foreach($res['result'] as $semesterId => $theResult):
                        $SSTD = $SA = $SC = $SD = $SM = $SP = $SR = $SS = $SU = $SW = $STOTAL = $SPASS = 0;
                        
                        $html .= '<tr class="semesterRow semesterRow_'.$semesterId.'" data-semesterid="'.$semesterId.'">';
                            $html .= '<th><a href="javascript:void(0);" class="semesterToggle" data-semesterid="'.$semesterId.'">+ '.$theResult['name'].'</a></th>';
                            $html .= '<th class="semesterNoOfStudents">[SEM_NO_STD_'.$semesterId.']</th>';
                            $html .= '<th class="semesterExpSubmission">[SEM_EXP_SUB_'.$semesterId.']</th>';
                            $html .= '<th class="semesterActSubmission">[SEM_ACT_SUB_'.$semesterId.']</th>';
                            $html .= '<th class="semesterPass">[SEM_PASSED_'.$semesterId.']</th>';
                            $html .= '<th class="semesterSubmissionRate text-right">[SEM_SUB_RAT_'.$semesterId.']</th>';
                            $html .= '<th class="semesterPassRate text-right">[SEM_PAS_RAT_'.$semesterId.']</th>';
                        $html .= '</tr>';

                        if(isset($theResult['course']) && !empty($theResult['course'])):
                            foreach($theResult['course'] as $theCourseId => $theCrRes):
                                $CSTD = $CA = $CC = $CD = $CM = $CP = $CR = $CS = $CU = $CW = $CTOTAL = $CPASS = 0;
                                $html .= '<tr class="courseRow courseRow_'.$theCourseId.' semesterCourseRow_'.$semesterId.'" data-semesterid="'.$semesterId.'" data-courseid="'.$theCourseId.'" style="display: none;">';
                                    $html .= '<th style="padding-left: 25px"><a href="javascript:void(0);" class="courseToggle" data-semesterid="'.$semesterId.'" data-courseid="'.$theCourseId.'">+ '.$theCrRes['name'].'</a></th>';
                                    $html .= '<th class="courceNoOfStudents">[CRS_NO_STD_'.$semesterId.'_'.$theCourseId.']</th>';
                                    $html .= '<th class="courceExpSubmission">[CRS_EXP_SUB_'.$semesterId.'_'.$theCourseId.']</th>';
                                    $html .= '<th class="courceActSubmission">[CRS_ACT_SUB_'.$semesterId.'_'.$theCourseId.']</th>';
                                    $html .= '<th class="courcePass">[CRS_PASSED_'.$semesterId.'_'.$theCourseId.']</th>';
                                    $html .= '<th class="courceSubmissionRate text-right">[CRS_SUB_RAT_'.$semesterId.'_'.$theCourseId.']</th>';
                                    $html .= '<th class="courcePassRate text-right">[CRS_PAS_RAT_'.$semesterId.'_'.$theCourseId.']</th>';
                                $html .= '</tr>';
                                
                                if(isset($theCrRes['term_result']) && !empty($theCrRes['term_result'])):
                                    $i = 1;
                                    foreach($theCrRes['term_result'] as $termResult):
                                        $TOTAL = (isset($termResult->TOTAL) && $termResult->TOTAL > 0 ? $termResult->TOTAL : 0);
                                        $STOTAL += $TOTAL;
                                        $CTOTAL += $TOTAL;
                                        $OTOTAL += $TOTAL;

                                        $TOTALPASS = (isset($termResult->TOTALPASS) && $termResult->TOTALPASS > 0 ? $termResult->TOTALPASS : 0);
                                        $SPASS += $TOTALPASS;
                                        $CPASS += $TOTALPASS;
                                        $OPASS += $TOTALPASS;

                                        $STD = (isset($termResult->student_data_ids) && !empty($termResult->student_data_ids) ? explode(',', str_replace(' ', '', $termResult->student_data_ids)) : []);
                                        $STD = (!empty($STD) ? count($STD) : 0);
                                        $OSTD += $STD;
                                        $SSTD += $STD;
                                        $CSTD += $STD;

                                        $A = (isset($termResult->A) && $termResult->A > 0 ? $termResult->A : 0);
                                        $SA += $A;
                                        $CA += $A;
                                        $OA += $A;

                                        $ASUB = $TOTAL - $A;
                                        $RATE = ($ASUB > 0 ? ($ASUB / $TOTAL) * 100 : 0);
                                        $PASSRATE = ($TOTALPASS > 0 ? ($TOTALPASS / $TOTAL) * 100 : 0);
                                        
                                        $html .= '<tr class="termRow semesterTermRow_'.$semesterId.' termRow_'.$semesterId.'_'.$theCourseId.'" style="display: none;">';
                                            $html .= '<td style="padding-left: 35px">'.$termResult->term_name.'</td>';
                                            $html .= '<td>'.$STD.'</td>';
                                            $html .= '<td>'.$TOTAL.'</td>';
                                            $html .= '<td>'.($TOTAL - $A).'</td>';
                                            $html .= '<td>'.$TOTALPASS.'</td>';
                                            $html .= '<td class="text-right">';
                                                $html .= number_format($RATE, 2).'%';
                                            $html .= '</td>';
                                            $html .= '<td class="text-right">';
                                                $html .= number_format($PASSRATE, 2).'%';
                                            $html .= '</td>';
                                        $html .= '</tr>';
                                        
                                        $i++;
                                    endforeach;
                                endif;
                                $CASUB = $CTOTAL - $CA;
                                $CRATE = ($CASUB > 0 ? ($CASUB / $CTOTAL) * 100 : 0);
                                $CPASSRATE = ($CPASS > 0 ? ($CPASS / $CTOTAL) * 100 : 0);

                                $courseReplace['[CRS_NO_STD_'.$semesterId.'_'.$theCourseId.']'] = $CSTD;
                                $courseReplace['[CRS_EXP_SUB_'.$semesterId.'_'.$theCourseId.']'] = $CTOTAL;
                                $courseReplace['[CRS_ACT_SUB_'.$semesterId.'_'.$theCourseId.']'] = $CASUB;
                                $courseReplace['[CRS_PASSED_'.$semesterId.'_'.$theCourseId.']'] = $CPASS;
                                $courseReplace['[CRS_SUB_RAT_'.$semesterId.'_'.$theCourseId.']'] = number_format($CRATE, 2).'%';
                                $courseReplace['[CRS_PAS_RAT_'.$semesterId.'_'.$theCourseId.']'] = number_format($CPASSRATE, 2).'%';
                            endforeach;
                        endif;
                        $SASUB = $STOTAL - $SA;
                        $SRATE = ($SASUB > 0 ? ($SASUB / $STOTAL) * 100 : 0);
                        $SPASSRATE = ($SPASS > 0 ? ($SPASS / $STOTAL) * 100 : 0);

                        $semesterReplace['[SEM_NO_STD_'.$semesterId.']'] = $SSTD;
                        $semesterReplace['[SEM_EXP_SUB_'.$semesterId.']'] = $STOTAL;
                        $semesterReplace['[SEM_ACT_SUB_'.$semesterId.']'] = $SASUB;
                        $semesterReplace['[SEM_PASSED_'.$semesterId.']'] = $SPASS;
                        $semesterReplace['[SEM_SUB_RAT_'.$semesterId.']'] = number_format($SRATE, 2).'%';
                        $semesterReplace['[SEM_PAS_RAT_'.$semesterId.']'] = number_format($SPASSRATE, 2).'%';
                        
                    endforeach;
                else:
                    $html .= '<tr>';
                        $html .= '<td colspan="6" class="text-danger font-medium text-center">';
                            $html .= 'Data not found!';
                        $html .= '</td>';
                    $html .= '</tr>';
                endif;
            $html .= '</tbody>';
            if(isset($res['result']) && !empty($res['result'])):
                $html .= '<tfoot>';
                    $OASUB = $OTOTAL - $OA;
                    $ORATE = ($OASUB > 0 ? ($OASUB / $OTOTAL) * 100 : 0);
                    $OPASSRATE = ($OPASS > 0 ? ($OPASS / $OTOTAL) * 100 : 0);
                    
                    $html .= '<tr>';
                        $html .= '<th>Overall</th>';
                        $html .= '<th>'.$OSTD.'</th>';
                        $html .= '<th>'.$OTOTAL.'</th>';
                        $html .= '<th>'.$OASUB.'</th>';
                        $html .= '<th>'.$OPASS.'</th>';
                        $html .= '<th class="text-right">'.number_format($ORATE, 2).'%</th>';
                        $html .= '<th class="text-right">'.number_format($OPASSRATE, 2).'%</th>';
                    $html .= '</tr>';
                $html .= '</tfoot>';
            endif;
        $html .= '</table>';

        if(!empty($courseReplace)):
            foreach($courseReplace as $search => $replace):
                $html = str_replace($search, $replace, $html);
            endforeach;
        endif;
        if(!empty($semesterReplace)):
            foreach($semesterReplace as $search => $replace):
                $html = str_replace($search, $replace, $html);
            endforeach;
        endif;

        return $html;
    }

    public function refineResult($semester_ids){
        $res = [];
        if(!empty($semester_ids)):
            foreach($semester_ids as $semester_id):
                $semester = Semester::find($semester_id);
                $res['result'][$semester_id]['semester_id'] = $semester_id;
                $res['result'][$semester_id]['name'] = $semester->name;
                $res['result'][$semester_id]['registered_students'] = 0;

                $course_ids = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'DESC')->pluck('course_id')->unique()->toArray();
                if(!empty($course_ids)):
                    foreach($course_ids as $course_id):
                        $course = Course::find($course_id);
                        $creation = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->orderBy('id', 'DESC')->get()->first();
                        $courseStartDate = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                        $courseEndDate = (isset($creation->available->course_end_date) && !empty($creation->available->course_end_date) ? date('Y-m-d', strtotime($creation->available->course_end_date)) : '');
                        $courseCreationId = $creation->id;

                        $student_ids = StudentCourseRelation::where('course_creation_id', $courseCreationId)->pluck('student_id')->unique()->toArray();
                        if(!empty($student_ids) && count($student_ids) > 0):
                            $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                    $q->where('course_creation_id', $courseCreationId);
                                                })->pluck('student_id')->unique()->toArray();
                            $terminated_std_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);
                            $plan_ids = Assign::whereIn('student_id', $registered_std_ids)->pluck('plan_id')->unique()->toArray();
                            if(!empty($plan_ids) && count($plan_ids) > 0):
                                $examResult = DB::table('results as rs')
                                            ->select(
                                                'td.id as term_declaration_id', 'td.name as term_name', 'td.start_date', 'td.end_date',
                                                
                                                DB::raw('GROUP_CONCAT(DISTINCT(std.id)) as student_data_ids'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 2 THEN 1 ELSE 0 END) AS A'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 3 THEN 1  ELSE 0 END) AS C'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 4 THEN 1 ELSE 0 END) AS D'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 5 THEN 1 ELSE 0 END) AS M'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 6 THEN 1 ELSE 0 END) AS P'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 7 THEN 1 ELSE 0 END) AS R'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 8 THEN 1 ELSE 0 END) AS S'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 9 THEN 1 ELSE 0 END) AS U'),
                                                DB::raw('SUM(CASE WHEN rs.grade_id = 10 THEN 1 ELSE 0 END) AS W'),
                                                DB::raw('COUNT(rs.grade_id) as TOTAL'), 
                                                DB::raw('(SUM(CASE WHEN rs.grade_id = 6 THEN 1 ELSE 0 END) + SUM(CASE WHEN rs.grade_id = 5 THEN 1 ELSE 0 END) + SUM(CASE WHEN rs.grade_id = 4 THEN 1 ELSE 0 END)) as TOTALPASS') 
                                            )
                                            ->leftJoin('students as std', 'rs.student_id', 'std.id')
                                            ->leftJoin('plans as pln', 'rs.plan_id', 'pln.id')
                                            ->leftJoin('term_declarations as td', 'pln.term_declaration_id', 'td.id')
                                            ->whereIn('rs.plan_id', $plan_ids)
                                            ->whereIn('rs.student_id', $registered_std_ids)
                                            ->whereIn('std.id', $registered_std_ids)
                                            ->groupBy('pln.term_declaration_id')->orderBy('pln.term_declaration_id', 'ASC')->get();
                                $res['result'][$semester_id]['course'][$course_id]['term_result'] = $examResult;
                            endif;
                            $res['result'][$semester_id]['course'][$course_id]['name'] = $course->name;
                            $res['result'][$semester_id]['course'][$course_id]['start_date'] = $courseStartDate;
                            $res['result'][$semester_id]['course'][$course_id]['admitted_students'] = (!empty($student_ids) ? count($student_ids) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['registered_students'] = (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $res['result'][$semester_id]['registered_students'] += (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $res;
    }
}
