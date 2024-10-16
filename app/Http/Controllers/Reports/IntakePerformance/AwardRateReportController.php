<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AwardRateReportController extends Controller
{
    public function getAwardRatReport(Request $request){
        $award_semester_id = (isset($request->award_semester_id) && !empty($request->award_semester_id) ? $request->award_semester_id : []);
        $html = $this->getHtml($award_semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printAwardRatReport($semester_ids = null){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterNames = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_ids);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Intake Award Rate Reports';
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
                    $html .= '<th>Registered Students</th>';
                    $html .= '<th>Completed</th>';
                    $html .= '<th class="text-right">Rate</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($res['result']) && !empty($res['result'])):
                    $OR = $OC = 0;
                    foreach($res['result'] as $semesterId => $theResult):
                        $OR += $theResult['registered_students'];
                        $OC += $theResult['awarded_students'];

                        $SRATE = ($theResult['awarded_students'] > 0 ? ($theResult['awarded_students'] / $theResult['registered_students']) * 100 : 0);
                        $html .= '<tr class="semesterRow semesterRow_'.$semesterId.'" data-semesterid="'.$semesterId.'">';
                            $html .= '<th><a href="javascript:void(0);" class="semesterToggle" data-semesterid="'.$semesterId.'">+ '.$theResult['name'].'</a></th>';
                            $html .= '<th>'.$theResult['registered_students'].'</th>';
                            $html .= '<th>'.$theResult['awarded_students'].'</th>';
                            $html .= '<th>'.number_format($SRATE, 2).'%</th>';
                        $html .= '</tr>';
                        if(isset($theResult['course']) && !empty($theResult['course'])):
                            foreach($theResult['course'] as $theCourseId => $theCrRes):
                                $CRATE = ($theCrRes['awarded_students'] > 0 ? ($theCrRes['awarded_students'] / $theCrRes['registered_students']) * 100 : 0);
                                
                                $html .= '<tr class="courseRow courseRow_'.$theCourseId.' semesterCourseRow_'.$semesterId.'" data-semesterid="'.$semesterId.'" data-courseid="'.$theCourseId.'" style="display: none;">';
                                    $html .= '<th style="padding-left: 25px">'.$theCrRes['name'].'</th>';
                                    $html .= '<th>'.$theCrRes['registered_students'].'</th>';
                                    $html .= '<th>'.$theCrRes['awarded_students'].'</th>';
                                    $html .= '<th>'.number_format($CRATE, 2).'%</th>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    endforeach;
                endif;
            $html .= '</tbody>';
            if(isset($res['result']) && !empty($res['result'])):
                $html .= '<tfoot>';
                    $ORATE = ($OC > 0 ? ($OC / $OR) * 100 : 0);
                    $html .= '<tr>';
                        $html .= '<th>Overall</th>';
                        $html .= '<th>'.$OR.'</th>';
                        $html .= '<th>'.$OC.'</th>';
                        $html .= '<th>'.number_format($ORATE, 2).'%</th>';
                    $html .= '</tr>';
                $html .= '</tfoot>';
            endif;
        $html .= '</table>';

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
                $res['result'][$semester_id]['awarded_students'] = 0;

                $course_ids = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'DESC')->pluck('course_id')->unique()->toArray();
                if(!empty($course_ids)):
                    foreach($course_ids as $course_id):
                        $course = Course::find($course_id);
                        $creation = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->orderBy('id', 'DESC')->get()->first();
                        $courseCreationId = $creation->id;

                        $student_ids = StudentCourseRelation::where('course_creation_id', $courseCreationId)->pluck('student_id')->unique()->toArray();
                        if(!empty($student_ids) && count($student_ids) > 0):
                            $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                    $q->where('course_creation_id', $courseCreationId);
                                                })->pluck('student_id')->unique()->toArray();
                            $terminated_std_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);

                            $awarded_std_ids = Student::whereIn('id', $registered_std_ids)->where('status_id', 21)->pluck('id')->unique()->toArray();
                            
                            $res['result'][$semester_id]['course'][$course_id]['name'] = $course->name;
                            $res['result'][$semester_id]['course'][$course_id]['admitted_students'] = (!empty($student_ids) ? count($student_ids) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['registered_students'] = (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['awarded_students'] = (!empty($awarded_std_ids) ? count($awarded_std_ids) : 0);
                            $res['result'][$semester_id]['registered_students'] += (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $res['result'][$semester_id]['awarded_students'] += (!empty($awarded_std_ids) ? count($awarded_std_ids) : 0);
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $res;
    }
}
