<?php

namespace App\Http\Controllers\Reports\IntakePerformance;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendanceTermStatus;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

use App\Exports\ArrayCollectionExport;
use Maatwebsite\Excel\Facades\Excel;

class RetentionRateReportController extends Controller
{
    public function getRetentionReport(Request $request){
        $rtn_semester_id = (isset($request->rtn_semester_id) && !empty($request->rtn_semester_id) ? $request->rtn_semester_id : []);
        $html = $this->getHtml($rtn_semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printRetentionRateReport($semester_ids = null){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterNames = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_ids);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Intake Retention Rate Reports';
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
            ->setPaper('a4', 'portrait')//landscape
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
                    $html .= '<th>Total Student</th>';
                    $html .= '<th>Registered</th>';
                    $html .= '<th>Dropped Out</th>';
                    $html .= '<th class="text-right">Rate</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($res['result']) && !empty($res['result'])):
                    $totalAdmission = 0;
                    $totalRegistered = 0;
                    $totalDroppedout = 0;
                    foreach($res['result'] as $keySemesterId => $theResult):
                        $theSemesterId = $theResult['semester_id'];
                        $admission = (isset($theResult['admissions']) && !empty($theResult['admissions']) ? $theResult['admissions'] : 0);
                        $registered = (isset($theResult['registered']) && !empty($theResult['registered']) ? $theResult['registered'] : 0);
                        $droppedout = (isset($theResult['droppedout']) && !empty($theResult['droppedout']) ? $theResult['droppedout'] : 0);
                        $actualAdmission = $registered;
                        $rate = (($actualAdmission - $droppedout) / $actualAdmission) * 100;

                        $totalAdmission += $admission;
                        $totalRegistered += $registered;
                        $totalDroppedout += $droppedout;

                        $html .= '<tr class="semesterRow">';
                            $html .= '<th class="w-2/6"><a href="javascript:void(0);" class="semisterToggle" data-semesterid="'.$theSemesterId.'">+ '.$theResult['name'].'</a></th>';
                            $html .= '<th>'.$admission.'</th>';
                            $html .= '<th>'.$actualAdmission.'</th>';
                            $html .= '<th>'.$droppedout.'</th>';
                            $html .= '<th style="text-align: right;">'.number_format($rate, 2).'%</th>';
                        $html .= '</tr>';

                        if(isset($theResult['course']) && !empty($theResult['course'])):
                            foreach($theResult['course'] as $theCourseId => $theCrsResult):
                                $admissionCrs = (isset($theCrsResult['admissions']) && !empty($theCrsResult['admissions']) ? $theCrsResult['admissions'] : 0);
                                $registeredCrs = (isset($theCrsResult['registered']) && !empty($theCrsResult['registered']) ? $theCrsResult['registered'] : 0);
                                $droppedoutCrs = (isset($theCrsResult['droppedout']) && !empty($theCrsResult['droppedout']) ? $theCrsResult['droppedout'] : 0);
                                $actualAdmissionCrs = $registeredCrs;
                                $rateCrs = (($actualAdmissionCrs - $droppedoutCrs) / $actualAdmissionCrs) * 100;
                                
                                $html .= '<tr class="courseRow courseRow_'.$theSemesterId.'" style="display: none;">';
                                    $html .= '<th class="w-2/6">'.$theCrsResult['name'].' '.($theCrsResult['status'] == 0 ? ' <span class="text-danger">(Incomplete)</span>' : '').'</th>';
                                    $html .= '<td>'.$admissionCrs.'</td>';
                                    $html .= '<td>'.$actualAdmissionCrs.'</td>';
                                    $html .= '<td>'.$droppedoutCrs.'</td>';
                                    $html .= '<td style="text-align: right;">'.number_format($rateCrs, 2).'%</td>';
                                $html .= '</tr>';
                                
                            endforeach;
                        endif;
                    endforeach;
                    $totalActualAdmission = $totalRegistered;
                    $totalRate = (($totalActualAdmission - $totalDroppedout) / $totalActualAdmission) * 100;
                    
                    $html .= '<tr>';
                        $html .= '<th class="w-2/6">Total</th>';
                        $html .= '<th>'.$totalAdmission.'</th>';
                        $html .= '<th>'.$totalActualAdmission.'</th>';
                        $html .= '<th>'.$totalDroppedout.'</th>';
                        $html .= '<th style="text-align: right;">'.number_format($totalRate, 2).'%</th>';
                    $html .= '</tr>';
                    
                else:
                    $html .= '<tr>';
                        $html .= '<td colspan="5"><div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Data not foud for selected semsters.</div></td>';
                    $html .= '</tr>';
                endif;
            $html .= '</tbody>';
        $html .= '</table>';


        return $html;
    }

    public function refineResult($semester_ids){
        $res = [];
        if(!empty($semester_ids)):
            $courseCount = 0;
            foreach($semester_ids as $semester_id):
                $semester = Semester::find($semester_id);
                $res['result'][$semester_id]['semester_id'] = $semester_id;
                $res['result'][$semester_id]['name'] = $semester->name;

                $course_ids = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'DESC')->pluck('course_id')->unique()->toArray();
                $semesterAdmission = 0;
                $semesterRegistered = 0;
                $semesterDroppedOut = 0;
                if(!empty($course_ids)):
                    foreach($course_ids as $course_id):
                        $course = Course::find($course_id);
                        $creation = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->orderBy('id', 'DESC')->get()->first();
                        $courseCreationId = $creation->id;
                        $courseStartDate = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                        $courseEndDate = (isset($creation->available->course_end_date) && !empty($creation->available->course_end_date) ? date('Y-m-d', strtotime($creation->available->course_end_date)) : '');
                        $refund_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 28 days')) : '');
                        $completion_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 380 days')) : '');

                        $student_ids = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)->pluck('student_id')->unique()->toArray();
                        if(!empty($student_ids) && count($student_ids) > 0):
                            $courseCount += 1;

                            $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                    $q->where('course_creation_id', $courseCreationId);
                                                })->pluck('student_id')->unique()->toArray();
                            $terminated_std_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);

                            $droppedOutStdents = DB::table('students as std')
                                                 ->leftJoin('student_attendance_term_statuses as sats', function($j){
                                                    $j->on('std.id', 'sats.student_id');
                                                    $j->on('std.status_id', 'sats.status_id');
                                                 })->whereIn('std.id', $registered_std_ids)
                                                 ->whereIn('sats.status_id', [22, 27, 30, 31, 42, 43, 45])
                                                 ->where(function($q) use($refund_date, $courseEndDate){
                                                    $q->whereDate('sats.status_change_date', '>=', date('Y-m-d', strtotime($refund_date)))->whereDate('sats.status_change_date', '<=', date('Y-m-d', strtotime($courseEndDate)));
                                                 })->pluck('std.id')->unique()->toArray();

                            $res['result'][$semester_id]['course'][$course_id]['name'] = $course->name;
                            $res['result'][$semester_id]['course'][$course_id]['admissions'] = (!empty($student_ids) ? count($student_ids) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['registered'] = (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['droppedout'] = (!empty($droppedOutStdents) ? count($droppedOutStdents) : 0);
                            $res['result'][$semester_id]['course'][$course_id]['status'] = ($courseEndDate < date('Y-m-d') ? 1 : 0);

                            $semesterAdmission += (!empty($student_ids) ? count($student_ids) : 0);
                            $semesterRegistered += (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $semesterDroppedOut += (!empty($droppedOutStdents) ? count($droppedOutStdents) : 0);
                        endif;
                    endforeach;
                    $res['result'][$semester_id]['admissions'] = $semesterAdmission;
                    $res['result'][$semester_id]['registered'] = $semesterRegistered;
                    $res['result'][$semester_id]['droppedout'] = $semesterDroppedOut;
                endif;
            endforeach;
            $res['course_count'] = $courseCount;
        endif;

        return $res;
    }

    public function exportRetentionRateReport($semester_ids = null){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterAdmission = 0;
        $semesterRegistered = 0;
        $semesterDroppedOut = 0;
        $courseCount = 0;

        $theCollection = [];
        $theCollection[1][] = 'Total Student';
        $theCollection[1][] = 'Semester';
        $theCollection[1][] = 'Course';
        $theCollection[1][] = 'Status';
        $theCollection[1][] = 'Status Changed Date';
        $theCollection[1][] = 'Registered';
        $theCollection[1][] = 'Dropped Out';
        $theCollection[1][] = 'Rate';

        $row = 3;
        if(!empty($semester_ids)):
            foreach($semester_ids as $semester_id):
                $semester = Semester::find($semester_id);

                $course_ids = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'DESC')->pluck('course_id')->unique()->toArray();
                if(!empty($course_ids)):
                    foreach($course_ids as $course_id):
                        $course = Course::find($course_id);
                        $creation = CourseCreation::where('semester_id', $semester_id)->where('course_id', $course_id)->orderBy('id', 'DESC')->get()->first();
                        $courseCreationId = $creation->id;
                        $courseStartDate = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                        $courseEndDate = (isset($creation->available->course_end_date) && !empty($creation->available->course_end_date) ? date('Y-m-d', strtotime($creation->available->course_end_date)) : '');
                        $refund_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 28 days')) : '');
                        $completion_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 380 days')) : '');

                        $student_ids = StudentCourseRelation::where('course_creation_id', $creation->id)->where('active', 1)->pluck('student_id')->unique()->toArray();
                        if(!empty($student_ids) && count($student_ids) > 0):
                            $courseCount += 1;

                            $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                    $q->where('course_creation_id', $courseCreationId);
                                                })->pluck('student_id')->unique()->toArray();

                            $droppedOutStdents = DB::table('students as std')
                                                 ->select('sats.student_id', 'sats.status_change_date')
                                                 ->leftJoin('student_attendance_term_statuses as sats', function($j){
                                                    $j->on('std.id', 'sats.student_id');
                                                    $j->on('std.status_id', 'sats.status_id');
                                                 })->whereIn('std.id', $registered_std_ids)
                                                 ->whereIn('sats.status_id', [22, 27, 30, 31, 42, 43, 45])
                                                 ->where(function($q) use($refund_date, $courseEndDate){
                                                    $q->whereDate('sats.status_change_date', '>=', date('Y-m-d', strtotime($refund_date)))->whereDate('sats.status_change_date', '<=', date('Y-m-d', strtotime($courseEndDate)));
                                                 })->get();
                            $droppedoutStudentIds = $droppedOutStdents->pluck('student_id')->unique()->toArray();
                            $droppedoutCount = !empty($droppedoutStudentIds) ? count($droppedoutStudentIds) : 0;
                            $droppedStatusChanged = [];
                            if($droppedOutStdents->count() > 0):
                                foreach($droppedOutStdents as $dps):
                                    $droppedStatusChanged[$dps->student_id] = (isset($dps->status_change_date) && !empty($dps->status_change_date) ? date('d-m-Y', strtotime($dps->status_change_date)) : '');
                                endforeach;
                            endif;

                            $semesterAdmission += count($student_ids);
                            $semesterRegistered += (!empty($registered_std_ids) ? count($registered_std_ids) : 0);
                            $semesterDroppedOut += $droppedoutCount;

                            foreach($student_ids as $student_id):
                                $std = Student::with('status')->find($student_id);
                                $theCollection[$row][] = (isset($std->registration_no) && !empty($std->registration_no) ? $std->registration_no : '');
                                $theCollection[$row][] = $semester->name;
                                $theCollection[$row][] = $course->name.' '.($courseEndDate < date('Y-m-d') ? '' : ' (Incomplete)');
                                $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');
                                $theCollection[$row][] = (in_array($student_id, $droppedoutStudentIds) && isset($droppedStatusChanged[$student_id]) && !empty($droppedStatusChanged[$student_id]) ? $droppedStatusChanged[$student_id] : '');;
                                $theCollection[$row][] = (in_array($student_id, $registered_std_ids) && (isset($std->award->reference) && !empty($std->award->reference)) ? $std->award->reference : '');
                                $theCollection[$row][] = (in_array($student_id, $droppedoutStudentIds) ? 'Yes' : '');
                                $theCollection[$row][] = '';

                                $row += 1;
                            endforeach;
                        endif;
                    endforeach;
                endif;
            endforeach;
            $res['course_count'] = $courseCount;
        endif;

        if($courseCount > 0):
            $actualAdmission = $semesterRegistered;
            $rate = (($actualAdmission - $semesterDroppedOut) / $actualAdmission) * 100;

            $theCollection[2][] = $semesterAdmission;
            $theCollection[2][] = (!empty($semester_ids) ? count($semester_ids) : '0');
            $theCollection[2][] = $courseCount;
            $theCollection[2][] = '';
            $theCollection[2][] = '';
            $theCollection[2][] = $actualAdmission;
            $theCollection[2][] = $semesterDroppedOut;
            $theCollection[2][] = number_format($rate, 2).'%';
        else:
            $excelArray[2][] = 'Too early to do the calculation.';
        endif;

        ksort($theCollection);

        $fileName = 'Retention_Rate_Reports.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $fileName);
    }
}
