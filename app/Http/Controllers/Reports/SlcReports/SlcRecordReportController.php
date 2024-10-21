<?php

namespace App\Http\Controllers\Reports\SlcReports;

use App\Http\Controllers\Controller;
use App\Models\CourseCreation;
use App\Models\Option;
use App\Models\Semester;
use App\Models\SlcAttendance;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayCollectionExport;

class SlcRecordReportController extends Controller
{
    public function generateReport(Request $request){
        $semester_ids = (isset($request->srr_semester_id) && !empty($request->srr_semester_id) ? $request->srr_semester_id : []);
        $html = $this->getHtml($semester_ids);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($semester_ids){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $semesterNames = (!empty($semester_ids) ? Semester::whereIn('id', $semester_ids)->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_ids);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'SLC Record Report';
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
                                .table.slcRecordReportTable tr th, .table.slcRecordReportTable tr td{ text-align: left;}
                                .table.slcRecordReportTable tr th a{ text-decoration: none; color: #1e293b; }
                                .table.slcRecordReportTable tr th.exportAction, .table.slcRecordReportTable tr td.exportAction{ display: none !important; }
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

    public function getHtml($semester_ids){
        $res = $this->refineResult($semester_ids);

        $html = '';
        $html .= '<table class="table table-bordered slcRecordReportTable  table-sm" id="slcRecordReportTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>Intake</th>';
                    $html .= '<th>Initial LCC SMS Registration (excluding discarded)</th>';
                    $html .= '<th>Student Registered with Awarding Body</th>';
                    $html .= '<th>SLC Registered</th>';
                    $html .= '<th>SLC Attendance Confirmed</th>';
                    $html .= '<th>SLC Withdrawn</th>';
                    $html .= '<th>Student Withdrawn or Intermittent</th>';
                    $html .= '<th>Self funded students</th>';
                    $html .= '<th class="exportAction text-right">&nbsp;</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(!empty($res)):
                    foreach($res as $semester_id => $row):
                        $html .= '<tr>';
                            $html .= '<td class="w-1/6">'.$row['name'].'</td>';
                            $html .= '<td>'.$row['slc_sms_registered'].'</td>';
                            $html .= '<td>'.$row['slc_awb_registered'].'</td>';
                            $html .= '<td>'.$row['year_1_registered'].'</td>';
                            $html .= '<td>'.$row['year_1_attendance'].'</td>';
                            $html .= '<td>'.$row['slc_withdrawn'].'</td>';
                            $html .= '<td>'.$row['slc_interminate'].'</td>';
                            $html .= '<td>'.$row['slc_self_funded'].'</td>';
                            $html .= '<td class="text-right exportAction"><a href="'.route('reports.slc.record.export.details.report', $semester_id).'" class="btn btn-sm btn-success text-white"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export</a></td>';
                        $html .= '</tr>';
                    endforeach;
                else:
                    $html .= '<tr><td colspan="8" class="text-center font-medium">Data not available</td></tr>';
                endif;
            $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public function exportReport($semester_ids){
        $semester_ids = (!empty($semester_ids) ? explode('_', $semester_ids) : []);
        $res = $this->refineResult($semester_ids);

        $theCollection = [];
        $row = 1;
        $theCollection[$row][] = "Intake";
        $theCollection[$row][] = "Initial LCC SMS Registration (excluding discarded)";
        $theCollection[$row][] = "Student Registered with Awarding Body";
        $theCollection[$row][] = "SLC Registered";
        $theCollection[$row][] = "SLC Attendance Confirmed";
        $theCollection[$row][] = "SLC Withdrawn";
        $theCollection[$row][] = "Student Withdrawn or Intermittent";
        $theCollection[$row][] = "Self funded students";
        $row += 1;

        
        if(!empty($res)):
            foreach($res as $ro):
                $theCollection[$row][] = $ro['name'];
                $theCollection[$row][] = ($ro['slc_sms_registered'] > 0 ? $ro['slc_sms_registered'] : '0');
                $theCollection[$row][] = ($ro['slc_awb_registered'] > 0 ? $ro['slc_awb_registered'] : '0');
                $theCollection[$row][] = ($ro['year_1_registered'] > 0 ? $ro['year_1_registered'] : '0');
                $theCollection[$row][] = ($ro['year_1_attendance'] > 0 ? $ro['year_1_attendance'] : '0');
                $theCollection[$row][] = ($ro['slc_withdrawn'] > 0 ? $ro['slc_withdrawn'] : '0');
                $theCollection[$row][] = ($ro['slc_interminate'] > 0 ? $ro['slc_interminate'] : '0');
                $theCollection[$row][] = ($ro['slc_self_funded'] > 0 ? $ro['slc_self_funded'] : '0');

                $row += 1;
            endforeach;
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), 'slc_Record_report.xlsx');
    }

    public function refineResult($semester_ids){
        $res = [];
        if(!empty($semester_ids)):
            $slc_statuses = [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43];
            $slc_withdrawn_satuses = [30, 31, 43];
            $slc_interminate_satuses = [27, 42];
            $slc_self_funded_satuses = [15];
            foreach($semester_ids as $semester_id):
                $semester = Semester::find($semester_id);
                $creations = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
                $student_ids = StudentCourseRelation::whereIn('course_creation_id', $creations)->where('active', 1)->pluck('student_id')->unique()->toArray();

                $res[$semester_id]['name'] = $semester->name;
                $res[$semester_id]['slc_sms_registered'] = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_statuses)->get()->count();
                $res[$semester_id]['slc_awb_registered'] = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($creations){
                                                                $q->whereIn('course_creation_id', $creations);
                                                            })->pluck('student_id')->unique()->count();
                $res[$semester_id]['year_1_registered'] = SlcRegistration::whereIn('student_id', $student_ids)->where('registration_year', 1)->whereIn('slc_registration_status_id', [1, 3])
                                                          ->pluck('student_id')->unique()->count();
                $res[$semester_id]['year_1_attendance'] = SlcAttendance::whereIn('student_id', $student_ids)->where('attendance_year', 1)->where('attendance_code_id', 1)
                                                          ->pluck('student_id')->unique()->count();
                $res[$semester_id]['slc_withdrawn'] = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_withdrawn_satuses)->get()->count();
                $res[$semester_id]['slc_interminate'] = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_interminate_satuses)->get()->count();
                $res[$semester_id]['slc_self_funded'] = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_self_funded_satuses)->get()->count();
            endforeach;
        endif;

        return $res;
    }

    public function exportDetailsReport($semester_id){
        $slc_statuses = [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43];
        $slc_withdrawn_satuses = [30, 31, 43];
        $slc_interminate_satuses = [27, 42];
        $slc_self_funded_satuses = [15];

        $theCollection = [];
        $row = 1;
        $theCollection[$row][] = "Retistration No";
        $theCollection[$row][] = "First Name";
        $theCollection[$row][] = "Last Name";
        $theCollection[$row][] = "Semester";
        $theCollection[$row][] = "Course";
        $theCollection[$row][] = "Status";
        $row += 1; 
        
        $semester = Semester::find($semester_id);
        $creations = CourseCreation::where('semester_id', $semester_id)->pluck('id')->unique()->toArray();
        $student_ids = StudentCourseRelation::whereIn('course_creation_id', $creations)->where('active', 1)->pluck('student_id')->unique()->toArray();
        $slc_sms_registered = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_statuses)->orderBy('first_name', 'ASC')->get();
        $theCollection[$row][] = "Initial LCC SMS Registration (excluding discarded)";
        $row += 1; 
        if(!empty($slc_sms_registered) && $slc_sms_registered->count() > 0):
            foreach($slc_sms_registered as $std):
                $theCollection[$row][] = $std->registration_no;
                $theCollection[$row][] = $std->first_name;
                $theCollection[$row][] = $std->last_name;
                $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                $row += 1; 
            endforeach;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "Student Registered with Awarding Body";
        $row += 1; 
        $slc_awb_registered_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($creations){
                                    $q->whereIn('course_creation_id', $creations);
                                })->pluck('student_id')->unique()->toArray();
        if(!empty($slc_awb_registered_ids)):
            $slc_awb_registered = Student::whereIn('id', $slc_awb_registered_ids)->orderBy('first_name', 'ASC')->get();
            if(!empty($slc_awb_registered) && $slc_awb_registered->count() > 0):
                foreach($slc_awb_registered as $std):
                    $theCollection[$row][] = $std->registration_no;
                    $theCollection[$row][] = $std->first_name;
                    $theCollection[$row][] = $std->last_name;
                    $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                    $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                    $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                    $row += 1; 
                endforeach;
            endif;
        else:
            $theCollection[$row][] = "Student not found.";
            $row += 1;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "SLC Registered";
        $row += 1; 
        $year_1_registered_ids = SlcRegistration::whereIn('student_id', $student_ids)->where('registration_year', 1)->whereIn('slc_registration_status_id', [1, 3])
                                                          ->pluck('student_id')->unique()->toArray();
        if(!empty($year_1_registered_ids)):
            $year_1_registered = Student::whereIn('id', $year_1_registered_ids)->orderBy('first_name', 'ASC')->get();
            if(!empty($year_1_registered) && $year_1_registered->count() > 0):
                foreach($year_1_registered as $std):
                    $theCollection[$row][] = $std->registration_no;
                    $theCollection[$row][] = $std->first_name;
                    $theCollection[$row][] = $std->last_name;
                    $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                    $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                    $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                    $row += 1; 
                endforeach;
            endif;
        else:
            $theCollection[$row][] = "Student not found.";
            $row += 1;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "SLC Attendance Confirmed";
        $row += 1; 
        $year_1_attendance_ids = SlcAttendance::whereIn('student_id', $student_ids)->where('attendance_year', 1)->where('attendance_code_id', 1)
                                                          ->pluck('student_id')->unique()->toArray();
        if(!empty($year_1_attendance_ids)):
            $year_1_attendance = Student::whereIn('id', $year_1_attendance_ids)->orderBy('first_name', 'ASC')->get();
            if(!empty($year_1_attendance) && $year_1_attendance->count() > 0):
                foreach($year_1_attendance as $std):
                    $theCollection[$row][] = $std->registration_no;
                    $theCollection[$row][] = $std->first_name;
                    $theCollection[$row][] = $std->last_name;
                    $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                    $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                    $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                    $row += 1; 
                endforeach;
            endif;
        else:
            $theCollection[$row][] = "Student not found.";
            $row += 1;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "SLC Withdrawn";
        $row += 1;
        $slc_withdrawn = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_withdrawn_satuses)->orderBy('first_name', 'ASC')->get();
        if(!empty($slc_withdrawn) && $slc_withdrawn->count() > 0):
            foreach($slc_withdrawn as $std):
                $theCollection[$row][] = $std->registration_no;
                $theCollection[$row][] = $std->first_name;
                $theCollection[$row][] = $std->last_name;
                $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                $row += 1; 
            endforeach;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "Student Withdrawn or Intermittent";
        $row += 1;
        $slc_intermittent = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_interminate_satuses)->orderBy('first_name', 'ASC')->get();
        if(!empty($slc_intermittent) && $slc_intermittent->count() > 0):
            foreach($slc_intermittent as $std):
                $theCollection[$row][] = $std->registration_no;
                $theCollection[$row][] = $std->first_name;
                $theCollection[$row][] = $std->last_name;
                $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                $row += 1; 
            endforeach;
        endif;

        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = '';
        $row += 1;
        $theCollection[$row][] = "Self funded students";
        $row += 1;
        $slc_self_funded = Student::whereIn('id', $student_ids)->whereIn('status_id', $slc_self_funded_satuses)->orderBy('first_name', 'ASC')->get();
        if(!empty($slc_self_funded) && $slc_self_funded->count() > 0):
            foreach($slc_self_funded as $std):
                $theCollection[$row][] = $std->registration_no;
                $theCollection[$row][] = $std->first_name;
                $theCollection[$row][] = $std->last_name;
                $theCollection[$row][] = (isset($std->activeCR->creation->semester->name) && !empty($std->activeCR->creation->semester->name) ? $std->activeCR->creation->semester->name : '');
                $theCollection[$row][] = (isset($std->activeCR->creation->course->name) && !empty($std->activeCR->creation->course->name) ? $std->activeCR->creation->course->name : '');
                $theCollection[$row][] = (isset($std->status->name) && !empty($std->status->name) ? $std->status->name : '');

                $row += 1; 
            endforeach;
        endif;

        $fileName = str_replace(' ', '_', $semester->name).'_slc_record_details_report.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $fileName);
    }
}
