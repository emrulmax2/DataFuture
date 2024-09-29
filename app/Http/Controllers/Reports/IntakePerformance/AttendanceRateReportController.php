<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceRateReportController extends Controller
{
    public function getAttendanceRateReport(Request $request){
        $atn_semester_id = (isset($request->atn_semester_id) && !empty($request->atn_semester_id) ? $request->atn_semester_id : []);
        $html = $this->getHtml($atn_semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printAttendanceRateReport($semester_ids = null){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterNames = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_ids);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Intake Attendance Rate Reports';
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
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function getHtml($atn_semester_id = []){
        $res = $this->refineResult($atn_semester_id);

        $html = '';
        $html .= '<table class="table table-bordered table-sm attenRateReportTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="w-2/6">&nbsp;</th>';
                    $html .= '<th>Registered Students</th>';
                    $html .= '<th>A</th>';
                    $html .= '<th>P</th>';
                    $html .= '<th>L</th>';
                    $html .= '<th>LE</th>';
                    $html .= '<th>E</th>';
                    $html .= '<th>M</th>';
                    $html .= '<th>H</th>';
                    $html .= '<th>O</th>';
                    $html .= '<th>Total</th>';
                    $html .= '<th>Rate</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                $OAD = $OA = $OP = $OL = $OLE = $OE = $OM = $OH = $OO = $OTOTAL = $OPRESENT = 0;
                $courseReplace = [];
                $semesterReplace = [];
                if(isset($res['result']) && !empty($res['result'])):
                    foreach($res['result'] as $semesterId => $theResult):
                        $SAD = $SA = $SP = $SL = $SLE = $SE = $SM = $SH = $SO = $STOTAL = $SPRESENT = 0;
                        $OAD += $theResult['registered_students'];
                        
                        $html .= '<tr class="semesterRow semesterRow_'.$semesterId.'" data-semesterid="'.$semesterId.'">';
                            $html .= '<th><a href="javascript:void(0);" class="semesterToggle" data-semesterid="'.$semesterId.'">+ '.$theResult['name'].'</a></th>';
                            $html .= '<th>'.$theResult['registered_students'].'</th>';
                            $html .= '<th class="semesterA">['.$semesterId.'_semesterA]</th>';
                            $html .= '<th class="semesterP">['.$semesterId.'_semesterP]</th>';
                            $html .= '<th class="semesterL">['.$semesterId.'_semesterL]</th>';
                            $html .= '<th class="semesterLE">['.$semesterId.'_semesterLE]</th>';
                            $html .= '<th class="semesterE">['.$semesterId.'_semesterE]</th>';
                            $html .= '<th class="semesterM">['.$semesterId.'_semesterM]</th>';
                            $html .= '<th class="semesterH">['.$semesterId.'_semesterH]</th>';
                            $html .= '<th class="semesterO">['.$semesterId.'_semesterO]</th>';
                            $html .= '<th class="semesterTotal">['.$semesterId.'_semesterTotal]</th>';
                            $html .= '<th class="semesterRate">['.$semesterId.'_semesterRate]</th>';
                        $html .= '</tr>';
                            if(isset($theResult['course']) && !empty($theResult['course'])):
                                foreach($theResult['course'] as $theCourseId => $theCrRes):
                                    $CAD = $CA = $CP = $CL = $CLE = $CE = $CM = $CH = $CO = $CTOTAL = $CPRESENT = 0;
                                    $courseRegisteredStudents = $theCrRes['registered_students'];
                                    
                                    $html .= '<tr class="courseRow courseRow_'.$theCourseId.' semesterCourseRow_'.$semesterId.'" data-semesterid="'.$semesterId.'" data-courseid="'.$theCourseId.'" style="display: none;">';
                                        $html .= '<th style="padding-left: 25px"><a href="javascript:void(0);" class="courseToggle" data-semesterid="'.$semesterId.'" data-courseid="'.$theCourseId.'">+ '.$theCrRes['name'].'</a></th>';
                                        $html .= '<th>'.$courseRegisteredStudents.'</th>';
                                        $html .= '<th class="courseA">['.$semesterId.'_'.$theCourseId.'_courseA]</th>';
                                        $html .= '<th class="courseP">['.$semesterId.'_'.$theCourseId.'_courseP]</th>';
                                        $html .= '<th class="courseL">['.$semesterId.'_'.$theCourseId.'_courseL]</th>';
                                        $html .= '<th class="courseLE">['.$semesterId.'_'.$theCourseId.'_courseLE]</th>';
                                        $html .= '<th class="courseE">['.$semesterId.'_'.$theCourseId.'_courseE]</th>';
                                        $html .= '<th class="courseM">['.$semesterId.'_'.$theCourseId.'_courseM]</th>';
                                        $html .= '<th class="courseH">['.$semesterId.'_'.$theCourseId.'_courseH]</th>';
                                        $html .= '<th class="courseO">['.$semesterId.'_'.$theCourseId.'_courseO]</th>';
                                        $html .= '<th class="courseTotal">['.$semesterId.'_'.$theCourseId.'_courseTotal]</th>';
                                        $html .= '<th class="courseRate">['.$semesterId.'_'.$theCourseId.'_courseRate]</th>';
                                    $html .= '</tr>';
                                    
                                    if(isset($theCrRes['term_result']) && !empty($theCrRes['term_result'])):
                                        $i = 1;
                                        $allTermDrropedOut = 0;
                                        $prev_start_date =  $theCrRes['start_date'];
                                        foreach($theCrRes['term_result'] as $termResult):
                                            $sd = $prev_start_date;
                                            $ed = $termResult->start_date;

                                            $student_data_ids = (isset($termResult->student_ids) && !empty($termResult->student_ids) ? explode(',', str_replace(' ', '', $termResult->student_ids)) : []);
                                            $CAD += (!empty($student_data_ids) ? count($student_data_ids) : 0);
                                            $SAD += (!empty($student_data_ids) ? count($student_data_ids) : 0);
                                            $A = (isset($termResult->A) && $termResult->A > 0 ? $termResult->A : 0);
                                            $CA += $A;
                                            $SA += $A;
                                            $OA += $A;
                                            $P = (isset($termResult->P) && $termResult->P > 0 ? $termResult->P : 0);
                                            $CP += $P;
                                            $SP += $P;
                                            $OP += $P;
                                            $L = (isset($termResult->L) && $termResult->L > 0 ? $termResult->L : 0);
                                            $CL += $L;
                                            $SL += $L;
                                            $OL += $L;
                                            $LE = (isset($termResult->LE) && $termResult->LE > 0 ? $termResult->LE : 0);
                                            $CLE += $LE;
                                            $SLE += $LE;
                                            $OLE += $LE;
                                            $E = (isset($termResult->E) && $termResult->E > 0 ? $termResult->E : 0);
                                            $CE += $E;
                                            $SE += $E;
                                            $OE += $E;
                                            $M = (isset($termResult->M) && $termResult->M > 0 ? $termResult->M : 0);
                                            $CM += $M;
                                            $SM += $M;
                                            $OM += $M;
                                            $H = (isset($termResult->H) && $termResult->H > 0 ? $termResult->H : 0);
                                            $CH += $H;
                                            $SH += $H;
                                            $OH += $H;
                                            $O = (isset($termResult->O) && $termResult->O > 0 ? $termResult->O : 0);
                                            $CO += $O;
                                            $SO += $O;
                                            $OO += $O;
                                            $Total = (isset($termResult->TOTAL) && $termResult->TOTAL > 0 ? $termResult->TOTAL : 0);
                                            $CTOTAL += $Total;
                                            $STOTAL += $Total;
                                            $OTOTAL += $Total;
                                            $TotalPresent = (isset($termResult->TOTALPRESENT) && $termResult->TOTALPRESENT > 0 ? $termResult->TOTALPRESENT : 0);
                                            $CPRESENT += $TotalPresent;
                                            $SPRESENT += $TotalPresent;
                                            $OPRESENT += $TotalPresent;
                                            
                                            $html .= '<tr class="termRow semesterTermRow_'.$semesterId.' termRow_'.$semesterId.'_'.$theCourseId.'" style="display: none;">';
                                                $html .= '<td style="padding-left: 35px">'.$termResult->term_name.'</td>';
                                                $html .= '<td>';
                                                    if($i != 1):
                                                        $termDroppedOut = 0; //get_last_term_dropped_out_students($semesterId, $theCourseId, $sd, $ed);
                                                        $allTermDrropedOut += $termDroppedOut;
                                                        $html .= ($courseRegisteredStudents - $allTermDrropedOut);
                                                    else:
                                                        $html .= $courseRegisteredStudents;
                                                    endif;
                                                $html .= '</td>';
                                                $html .= '<td>'.$A.'</td>';
                                                $html .= '<td>'.$P.'</td>';
                                                $html .= '<td>'.$L.'</td>';
                                                $html .= '<td>'.$LE.'</td>';
                                                $html .= '<td>'.$E.'</td>';
                                                $html .= '<td>'.$M.'</td>';
                                                $html .= '<td>'.$H.'</td>';
                                                $html .= '<td>'.$O.'</td>';
                                                $html .= '<td>'.$Total.'</td>';
                                                $html .= '<td>';
                                                    $html .= ($TotalPresent > 0 ? number_format((($TotalPresent / $Total) * 100), 2) : '0').'%';
                                                $html .= '</td>';
                                            $html .= '</tr>';
                                            
                                            $prev_start_date = $termResult->start_date;
                                            $i++;
                                        endforeach;
                                    endif;

                                    $CRES = ($CPRESENT > 0 ? number_format((($CPRESENT / $CTOTAL) * 100), 2) : '0').'%';
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseA]'] = $CA;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseP]'] = $CP;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseL]'] = $CL;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseLE]'] = $CLE;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseE]'] = $CE;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseM]'] = $CM;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseH]'] = $CH;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseO]'] = $CO;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseTotal]'] = $CTOTAL;
                                    $courseReplace['['.$semesterId.'_'.$theCourseId.'_courseRate]'] = $CRES;
                                endforeach;
                            endif;
                            $SRES = ($SPRESENT > 0 ? number_format((($SPRESENT / $STOTAL) * 100), 2) : '0').'%';
                            $semesterReplace['['.$semesterId.'_semesterA]'] = $SA;
                            $semesterReplace['['.$semesterId.'_semesterP]'] = $SP;
                            $semesterReplace['['.$semesterId.'_semesterL]'] = $SL;
                            $semesterReplace['['.$semesterId.'_semesterLE]'] = $SLE;
                            $semesterReplace['['.$semesterId.'_semesterE]'] = $SE;
                            $semesterReplace['['.$semesterId.'_semesterM]'] = $SM;
                            $semesterReplace['['.$semesterId.'_semesterH]'] = $SH;
                            $semesterReplace['['.$semesterId.'_semesterO]'] = $SO;
                            $semesterReplace['['.$semesterId.'_semesterTotal]'] = $STOTAL;
                            $semesterReplace['['.$semesterId.'_semesterRate]'] = $SRES;
                    endforeach;
                else:
                    $html .= '<tr>';
                        $html .= '<td colspan="12" class="font-medium text-center">';
                            $html .= 'Data not found for selected semesters.';
                        $html .= '</td>';
                    $html .= '</tr>';
                endif;
            $html .= '</tbody>';
            $html .= '<tfoot>';
                $html .= '<tr>';
                    $html .= '<th>Overall</th>';
                    $html .= '<th>'.$OAD.'</th>';
                    $html .= '<th>'.$OA.'</th>';
                    $html .= '<th>'.$OP.'</th>';
                    $html .= '<th>'.$OL.'</th>';
                    $html .= '<th>'.$OLE.'</th>';
                    $html .= '<th>'.$OE.'</th>';
                    $html .= '<th>'.$OM.'</th>';
                    $html .= '<th>'.$OH.'</th>';
                    $html .= '<th>'.$OO.'</th>';
                    $html .= '<th>'.$OTOTAL.'</th>';
                    $html .= '<th>'.($OPRESENT > 0 ? number_format((($OPRESENT / $OTOTAL) * 100), 2) : '0').'%'.'</th>';
                $html .= '</tr>';
            $html .= '</tfoot>';
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
                        $courseCreationId = $creation->id;
                        $student_ids = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)->pluck('student_id')->unique()->toArray();

                        if(!empty($student_ids) && count($student_ids) > 0):
                            if(!empty($student_ids) && count($student_ids) > 0):
                                $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                        $q->where('course_creation_id', $courseCreationId);
                                                    })->pluck('student_id')->unique()->toArray();
                                $terminated_std_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);
                                $class_plan_ids = Assign::whereIn('student_id', $registered_std_ids)->pluck('plan_id')->unique()->toArray();

                                if(!empty($class_plan_ids) && count($class_plan_ids) > 0):
                                    $query = DB::table('attendances as atn')
                                            ->select(
                                                'std.id', 'trm.id as term_id', 'trm.name as term_name', 'trm.teaching_start_date as start_date', 'trm.teaching_end_date as end_date',
                                                DB::raw('group_concat(atn.student_id) as student_ids'),

                                                DB::raw('COUNT(atn.attendance_feed_status_id) AS TOTAL'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) AS P'), 
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END) AS O'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) AS LE'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 4 THEN 1 ELSE 0 END) AS A'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) AS L'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) AS E'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) AS M'),
                                                DB::raw('SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) AS H'),
                                                DB::raw('(
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 1 THEN 1 ELSE 0 END) + 
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 5 THEN 1 ELSE 0 END) +
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 3 THEN 1 ELSE 0 END) +
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 6 THEN 1 ELSE 0 END) +
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 7 THEN 1 ELSE 0 END) +
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 8 THEN 1 ELSE 0 END) +
                                                    SUM(CASE WHEN atn.attendance_feed_status_id = 2 THEN 1 ELSE 0 END)
                                                ) as TOTALPRESENT')
                                            )
                                            ->leftJoin('plans as pln', 'atn.plan_id', 'pln.id')
                                            ->leftJoin('students as std', 'atn.student_id', 'std.id')
                                            ->leftJoin('term_declarations as trm', 'pln.term_declaration_id', 'trm.id')
                                            ->whereIn('atn.plan_id', $class_plan_ids)
                                            ->whereIn('atn.student_id', $registered_std_ids)
                                            ->groupBy('pln.term_declaration_id')
                                            ->orderBY('pln.term_declaration_id', 'ASC')->get();
                                    $res['result'][$semester_id]['course'][$course_id]['term_result'] = $query;
                                endif;

                                $res['result'][$semester_id]['course'][$course_id]['name'] = $course->name;
                                $res['result'][$semester_id]['course'][$course_id]['start_date'] = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                                $res['result'][$semester_id]['course'][$course_id]['admitted_students'] = (!empty($student_ids) ? count($student_ids) : 0);
                                $res['result'][$semester_id]['course'][$course_id]['registered_students'] = (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                                $res['result'][$semester_id]['registered_students'] += (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            endif;
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $res;
    }
}
