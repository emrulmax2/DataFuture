<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\Course;
use App\Models\Option;
use App\Models\Plan;
use App\Models\Result;
use App\Models\Student;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TermSubmissionPerformanceReportController extends Controller
{
    public function generateReport(Request $request){
        $term_declaration_id = (isset($request->sub_perf_term_id) && !empty($request->sub_perf_term_id) ? $request->sub_perf_term_id : 0);
        $html = $this->getHtml($term_declaration_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($term_declaration_id = 0){
        $termName = ($term_declaration_id > 0 ? TermDeclaration::whereIn('id', [$term_declaration_id])->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($term_declaration_id);
        $html = str_replace('style="display: none;"', '', $html);

        $regNo = Option::where('category', 'SITE')->where('name', 'register_no')->get()->first();
        $regAt = Option::where('category', 'SITE')->where('name', 'register_at')->get()->first();

        $report_title = 'Term Submission Performance Reports';
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
                                .mb-5{margin-bottom: 15px;}
                                .mb-10{margin-bottom: 10px;}
                                .table-bordered th, .table-bordered td {border: 1px solid #e5e7eb;}
                                .table-sm th, .table-sm td{padding: 5px 10px;}
                                .w-1/6{width: 16.666666%;}
                                .w-2/6{width: 33.333333%;}
                                .table.submissionPerformanceReportTable tr th, .table.submissionPerformanceReportTable tr td{ text-align: left; padding: 5px;}
                                .table.submissionPerformanceReportTable tr a{ text-decoration: none; color: #1e293b; }
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
                            $PDFHTML .= '<td>Attendance Terms</td>';
                            $PDFHTML .= '<td>'.(!empty($termName) ? implode(', ', $termName) : 'Undefined').'</td>';
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
            ->setPaper('a3', 'landscape')//landscape portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function exportReport($term_declaration_id = 0){
        $res = $this->refineResult($term_declaration_id);
        //dd($res);

        $term = TermDeclaration::where('id', $term_declaration_id)->get()->first();
        $user = User::find(auth()->user()->id);

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = "Report Name";
        $theCollection[$row][] = "Submission Performance Report";
        $row += 1;

        $theCollection[$row][] = "Attendance Term";
        $theCollection[$row][] = $term->name;
        $row += 1;

        $theCollection[$row][] = "Report created date time";
        $theCollection[$row][] = date('jS F, Y H:i');
        $row += 1;

        $theCollection[$row][] = "Created By";
        $theCollection[$row][] = (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
        $row += 1;

        $theCollection[$row][] = '';
        $row += 1;

        if(!empty($res['result'])):
            $theCollection[$row][] = "First Submission";
            $theCollection[$row][] = "";
            $theCollection[$row][] = "Expected Submission";
            $theCollection[$row][] = "No. Of Submission";
            $theCollection[$row][] = "Total Pass";
            $theCollection[$row][] = "Grade Pass";
            $theCollection[$row][] = "Grade Merit";
            $theCollection[$row][] = "Grade Distinction";
            $theCollection[$row][] = "Grade Referred (R)";
            $theCollection[$row][] = "Grade Plagiarised(C)";
            $theCollection[$row][] = "Grade (Absent)";
            $theCollection[$row][] = "Total Pass Rate (Total Pass / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Pass Rate (Grade Pass / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Merit Rate (Grade Merit / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Distinction Rate (Grade Distinction / No. Of Submission) * 100";
            $theCollection[$row][] = "Assessment Rate (Total Submission / Expected Submission) * 100";
            $row += 1;

            foreach($res['result'] as $term_id => $term_data):
                $trow = $term_data['f_sub'];
                $theCollection[$row][] = $term_data['name'];
                $theCollection[$row][] = $trow['student_status'];
                $theCollection[$row][] = $trow['exp_submission'];
                $theCollection[$row][] = $trow['no_of_submission'];
                $theCollection[$row][] = $trow['total_pass'];
                $theCollection[$row][] = $trow['grade_pass'];
                $theCollection[$row][] = $trow['grade_merit'];
                $theCollection[$row][] = $trow['grade_distinction'];
                $theCollection[$row][] = $trow['grade_reffered'];
                $theCollection[$row][] = $trow['grade_plagiarised'];
                $theCollection[$row][] = $trow['grade_absent'];
                $theCollection[$row][] = $trow['total_pass_rate'];
                $theCollection[$row][] = $trow['grade_pass_rate'];
                $theCollection[$row][] = $trow['grade_merit_rate'];
                $theCollection[$row][] = $trow['grade_distinction_rate'];
                $theCollection[$row][] = $trow['assessment_rate'];
                $row += 1;

                if(isset($term_data['course']) && !empty($term_data['course'])):
                    foreach($term_data['course'] as $course_id => $course):
                        $crow = $course['f_sub'];
                        $theCollection[$row][] = $course['name'];
                        $theCollection[$row][] = $crow['student_status'];
                        $theCollection[$row][] = $crow['exp_submission'];
                        $theCollection[$row][] = $crow['no_of_submission'];
                        $theCollection[$row][] = $crow['total_pass'];
                        $theCollection[$row][] = $crow['grade_pass'];
                        $theCollection[$row][] = $crow['grade_merit'];
                        $theCollection[$row][] = $crow['grade_distinction'];
                        $theCollection[$row][] = $crow['grade_reffered'];
                        $theCollection[$row][] = $crow['grade_plagiarised'];
                        $theCollection[$row][] = $crow['grade_absent'];
                        $theCollection[$row][] = $crow['total_pass_rate'];
                        $theCollection[$row][] = $crow['grade_pass_rate'];
                        $theCollection[$row][] = $crow['grade_merit_rate'];
                        $theCollection[$row][] = $crow['grade_distinction_rate'];
                        $theCollection[$row][] = $crow['assessment_rate'];
                        $row += 1;
                    endforeach;
                endif;
            endforeach;

            $theCollection[$row][] = '';
            $row += 1;
            $theCollection[$row][] = '';
            $row += 1;

            $theCollection[$row][] = "All Submission";
            $theCollection[$row][] = "";
            $theCollection[$row][] = "Expected Submission";
            $theCollection[$row][] = "No. Of Submission";
            $theCollection[$row][] = "Total Pass";
            $theCollection[$row][] = "Grade Pass";
            $theCollection[$row][] = "Grade Merit";
            $theCollection[$row][] = "Grade Distinction";
            $theCollection[$row][] = "Grade Referred (R)";
            $theCollection[$row][] = "Grade Plagiarised(C)";
            $theCollection[$row][] = "Grade (Absent)";
            $theCollection[$row][] = "Total Pass Rate (Total Pass / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Pass Rate (Grade Pass / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Merit Rate (Grade Merit / No. Of Submission) * 100";
            $theCollection[$row][] = "Grade Distinction Rate (Grade Distinction / No. Of Submission) * 100";
            $theCollection[$row][] = "Assessment Rate (Total Submission / Expected Submission) * 100";
            $row += 1;

            foreach($res['result'] as $term_id => $term_data):
                $trow = $term_data['all_sub'];
                $theCollection[$row][] = $term_data['name'];
                $theCollection[$row][] = $trow['student_status'];
                $theCollection[$row][] = $trow['exp_submission'];
                $theCollection[$row][] = $trow['no_of_submission'];
                $theCollection[$row][] = $trow['total_pass'];
                $theCollection[$row][] = $trow['grade_pass'];
                $theCollection[$row][] = $trow['grade_merit'];
                $theCollection[$row][] = $trow['grade_distinction'];
                $theCollection[$row][] = $trow['grade_reffered'];
                $theCollection[$row][] = $trow['grade_plagiarised'];
                $theCollection[$row][] = $trow['grade_absent'];
                $theCollection[$row][] = $trow['total_pass_rate'];
                $theCollection[$row][] = $trow['grade_pass_rate'];
                $theCollection[$row][] = $trow['grade_merit_rate'];
                $theCollection[$row][] = $trow['grade_distinction_rate'];
                $theCollection[$row][] = $trow['assessment_rate'];
                $row += 1;

                if(isset($term_data['course']) && !empty($term_data['course'])):
                    foreach($term_data['course'] as $course_id => $course):
                        $crow = $course['all_sub'];
                        $theCollection[$row][] = $course['name'];
                        $theCollection[$row][] = $crow['student_status'];
                        $theCollection[$row][] = $crow['exp_submission'];
                        $theCollection[$row][] = $crow['no_of_submission'];
                        $theCollection[$row][] = $crow['total_pass'];
                        $theCollection[$row][] = $crow['grade_pass'];
                        $theCollection[$row][] = $crow['grade_merit'];
                        $theCollection[$row][] = $crow['grade_distinction'];
                        $theCollection[$row][] = $crow['grade_reffered'];
                        $theCollection[$row][] = $crow['grade_plagiarised'];
                        $theCollection[$row][] = $crow['grade_absent'];
                        $theCollection[$row][] = $crow['total_pass_rate'];
                        $theCollection[$row][] = $crow['grade_pass_rate'];
                        $theCollection[$row][] = $crow['grade_merit_rate'];
                        $theCollection[$row][] = $crow['grade_distinction_rate'];
                        $theCollection[$row][] = $crow['assessment_rate'];
                        $row += 1;
                    endforeach;
                endif;
            endforeach;
        endif;

        $report_title = 'Term_Submission_Performance_Reports.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function getHtml($term_declaration_id = 0){
        $res = $this->refineResult($term_declaration_id);

        $html = '';
        if(!empty($res['result'])):
            $html .= '<table class="table table-bordered submissionPerformanceReportTable table-sm mb-5" id="firstSubmissionPerformanceReportTable">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th class="w-1/6">First Submission</th>';
                        $html .= '<th>&nbsp;</th>';
                        $html .= '<th>Expected Submission</th>';
                        $html .= '<th>No. Of Submission</th>';
                        $html .= '<th class="tooltip" title="(Total Submission / Expected Submission) * 100">';
                            $html .= 'Submission Rate';
                        $html .= '</th>';
                        $html .= '<th>Total Pass</th>';
                        $html .= '<th>Grade Pass</th>';
                        $html .= '<th>Grade Merit</th>';
                        $html .= '<th>Grade Distinction</th>';
                        $html .= '<th>Grade Referred (R)</th>';
                        $html .= '<th>Grade Plagiarised (C)</th>';
                        $html .= '<th>Grade (Absent)</th>';
                        $html .= '<th class="tooltip" title="(Total Pass / No. Of Submission) * 100">';
                            $html .= 'Total Pass Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Pass / No. Of Submission) * 100">';
                            $html .= 'Grade Pass Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Merit / No. Of Submission) * 100">';
                            $html .= 'Grade Merit Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Distinction / No. Of Submission) * 100">';
                            $html .= 'Grade Distinction Rate';
                        $html .= '</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    foreach($res['result'] as $term_id => $term_data):
                        $row = $term_data['f_sub'];
                        $html .= '<tr>'; 
                            $html .= '<td class="w-1/6"><a href="javascript:void(0);" class="semesterFirstRowToggle text-primary underline font-medium" data-semester="'.$term_id.'">+ '.$term_data['name'].'</a></td>';
                            $html .= '<td>'.$row['student_status'].'</td>';
                            $html .= '<td>'.$row['exp_submission'].'</td>';
                            $html .= '<td>'.$row['no_of_submission'].'</td>';
                            $html .= '<td>'.$row['assessment_rate'].'%</td>';
                            $html .= '<td>'.$row['total_pass'].'</td>';
                            $html .= '<td>'.$row['grade_pass'].'</td>';
                            $html .= '<td>'.$row['grade_merit'].'</td>';
                            $html .= '<td>'.$row['grade_distinction'].'</td>';
                            $html .= '<td>'.$row['grade_reffered'].'</td>';
                            $html .= '<td>'.$row['grade_plagiarised'].'</td>';
                            $html .= '<td>'.$row['grade_absent'].'</td>';
                            $html .= '<td>'.$row['total_pass_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_pass_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_merit_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_distinction_rate'].'%</td>';
                        $html .= '</tr>';
                        if(isset($term_data['course']) && !empty($term_data['course'])):
                            foreach($term_data['course'] as $course_id => $course):
                                $crow = $course['f_sub'];
                                $html .= '<tr class="course_first_row_'.$term_id.'" style="display: none;">'; 
                                    $html .= '<td class="w-1/6">'.$course['name'].'</td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['student_status_ids'].'">'.$crow['student_status'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['exp_submission_ids'].'">'.$crow['exp_submission'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['no_of_submission_ids'].'">'.$crow['no_of_submission'].'</a></td>';
                                    $html .= '<td>'.$crow['assessment_rate'].'%</td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['total_pass_ids'].'">'.$crow['total_pass'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_pass_ids'].'">'.$crow['grade_pass'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_merit_ids'].'">'.$crow['grade_merit'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_distinction_ids'].'">'.$crow['grade_distinction'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_reffered_ids'].'">'.$crow['grade_reffered'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_plagiarised_ids'].'">'.$crow['grade_plagiarised'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_absent_ids'].'">'.$crow['grade_absent'].'</a></td>';
                                    $html .= '<td>'.$crow['total_pass_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_pass_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_merit_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_distinction_rate'].'%</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<table class="table table-bordered submissionPerformanceReportTable table-sm" id="allSubmissionPerformanceReportTable">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th class="w-1/6">All Submission</th>';
                        $html .= '<th>&nbsp;</th>';
                        $html .= '<th>Expected Submission</th>';
                        $html .= '<th>No. Of Submission</th>';
                        $html .= '<th class="tooltip" title="(Total Submission / Expected Submission) * 100">';
                            $html .= 'Submission Rate';
                        $html .= '</th>';
                        $html .= '<th>Total Pass</th>';
                        $html .= '<th>Grade Pass</th>';
                        $html .= '<th>Grade Merit</th>';
                        $html .= '<th>Grade Distinction</th>';
                        $html .= '<th>Grade Referred (R)</th>';
                        $html .= '<th>Grade Plagiarised (C)</th>';
                        $html .= '<th>Grade (Absent)</th>';
                        $html .= '<th class="tooltip" title="(Total Pass / No. Of Submission) * 100">';
                            $html .= 'Total Pass Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Pass / No. Of Submission) * 100">';
                            $html .= 'Grade Pass Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Merit / No. Of Submission) * 100">';
                            $html .= 'Grade Merit Rate';
                        $html .= '</th>';
                        $html .= '<th class="tooltip" title="(Grade Distinction / No. Of Submission) * 100">';
                            $html .= 'Grade Distinction Rate';
                        $html .= '</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    foreach($res['result'] as $term_id => $term_data):
                        $row = $term_data['all_sub'];
                        $html .= '<tr>'; 
                            $html .= '<td class="w-1/6"><a href="javascript:void(0);" class="semesterAllRowToggle text-primary underline font-medium" data-semester="'.$term_id.'">+ '.$term_data['name'].'</a></td>';
                            $html .= '<td>'.$row['student_status'].'</td>';
                            $html .= '<td>'.$row['exp_submission'].'</td>';
                            $html .= '<td>'.$row['no_of_submission'].'</td>';
                            $html .= '<td>'.$row['assessment_rate'].'%</td>';
                            $html .= '<td>'.$row['total_pass'].'</td>';
                            $html .= '<td>'.$row['grade_pass'].'</td>';
                            $html .= '<td>'.$row['grade_merit'].'</td>';
                            $html .= '<td>'.$row['grade_distinction'].'</td>';
                            $html .= '<td>'.$row['grade_reffered'].'</td>';
                            $html .= '<td>'.$row['grade_plagiarised'].'</td>';
                            $html .= '<td>'.$row['grade_absent'].'</td>';
                            $html .= '<td>'.$row['total_pass_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_pass_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_merit_rate'].'%</td>';
                            $html .= '<td>'.$row['grade_distinction_rate'].'%</td>';
                        $html .= '</tr>';
                        if(isset($term_data['course']) && !empty($term_data['course'])):
                            foreach($term_data['course'] as $course_id => $course):
                                $crow = $course['all_sub'];
                                $html .= '<tr class="course_all_row_'.$term_id.'" style="display: none;">'; 
                                    $html .= '<td class="w-1/6">'.$course['name'].'</td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['student_status'].'">'.$crow['student_status'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['exp_submission'].'">'.$crow['exp_submission'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['no_of_submission'].'">'.$crow['no_of_submission'].'</a></td>';
                                    $html .= '<td>'.$crow['assessment_rate'].'%</td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['total_pass'].'">'.$crow['total_pass'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_pass'].'">'.$crow['grade_pass'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_merit'].'">'.$crow['grade_merit'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_distinction'].'">'.$crow['grade_distinction'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_reffered'].'">'.$crow['grade_reffered'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_plagiarised'].'">'.$crow['grade_plagiarised'].'</a></td>';
                                    $html .= '<td><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$crow['grade_absent'].'">'.$crow['grade_absent'].'</a></td>';
                                    $html .= '<td>'.$crow['total_pass_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_pass_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_merit_rate'].'%</td>';
                                    $html .= '<td>'.$crow['grade_distinction_rate'].'%</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
        endif;

        return $html;
    }

    public function refineResult($term_declaration_id = 0){
        $res = [];
        $term = TermDeclaration::find($term_declaration_id);
        $term_plans = Plan::where('term_declaration_id', $term_declaration_id)->get();
        $term_courses = $term_plans->pluck('course_id')->unique()->toArray();
        $term_creations = $term_plans->pluck('course_creation_id')->unique()->toArray();
        $term_plan_ids = $term_plans->pluck('id')->unique()->toArray();

        if(!empty($term_courses)):
            $t_exp_submission = $t_student_status = $t_no_of_submission = $t_total_pass = $t_grade_pass = $t_grade_merit = $t_grade_distinction = $t_grade_referred = $t_grade_plagiarised = $t_grade_absent = 0;
            $al_t_exp_submission = $al_t_student_status = $al_t_no_of_submission = $al_t_total_pass = $al_t_grade_pass = $al_t_grade_merit = $al_t_grade_distinction = $al_t_grade_referred = $al_t_grade_plagiarised = $al_t_grade_absent = 0;
            foreach($term_courses as $course_id):
                $exp_submission = $student_status = $no_of_submission = $total_pass = $grade_pass = $grade_merit = $grade_distinction = $grade_referred = $grade_plagiarised = $grade_absent = 0;
                $al_exp_submission = $al_student_status = $al_no_of_submission = $al_total_pass = $al_grade_pass = $al_grade_merit = $al_grade_distinction = $al_grade_referred = $al_grade_plagiarised = $al_grade_absent = 0;

                $exp_submission_ids = $student_status_ids = $no_of_submission_ids = $total_pass_ids = $grade_pass_ids = $grade_merit_ids = $grade_distinction_ids = $grade_referred_ids = $grade_plagiarised_ids = $grade_absent_ids = '';
                $al_exp_submission_ids = $al_student_status_ids = $al_no_of_submission_ids = $al_total_pass_ids = $al_grade_pass_ids = $al_grade_merit_ids = $al_grade_distinction_ids = $al_grade_referred_ids = $al_grade_plagiarised_ids = $al_grade_absent_ids = '';

                $course = Course::find($course_id);
                $plan_ids = Plan::where('term_declaration_id', $term_declaration_id)->where('course_id', $course_id)->pluck('id')->unique()->toArray();
                if(!empty($plan_ids)):
                    $exam_students = DB::table('results as rs')
                                    ->select(
                                        'rs.student_id', DB::raw('GROUP_CONCAT(DISTINCT(rs.plan_id)) as std_rs_cp_ids')
                                    )->whereIn('rs.plan_id', $plan_ids)
                                    ->groupBy('rs.student_id')->orderBy('rs.student_id', 'ASC')->get();
                    if($exam_students->count() > 0):
                        foreach($exam_students as $estd):
                            $student_id = $estd->student_id;
                            $student_plan_ids = (isset($estd->std_rs_cp_ids) && !empty($estd->std_rs_cp_ids) ? explode(',', str_replace(' ', '', $estd->std_rs_cp_ids)) : [0]);
                            $total_class = Assign::where('student_id', $student_id)->whereIn('plan_id', $student_plan_ids)->where(function($q){
                                                $q->whereNull('attendance')->orWhere('attendance', 1);
                                            })->get()->count();
                            if($total_class > 0):
                                $student_status += 1;
                                $t_student_status += 1;
                                $al_student_status += 1;
                                $al_t_student_status += 1;
                                $student_status_ids .= $student_id.',';
                                $al_student_status_ids .= $student_id.',';
                                
                                if(!empty($student_plan_ids)):
                                    foreach($student_plan_ids as $std_plan_id):
                                        $firstResult = Result::where('plan_id', $std_plan_id)->where('student_id', $student_id)->orderBy('id', 'ASC')->get()->first();
                                        $allResult = Result::where('plan_id', $std_plan_id)->where('student_id', $student_id)->orderBy('id', 'DESC')->get()->first();
                                        if(isset($firstResult->grade_id) && $firstResult->grade_id > 0):
                                            $exp_submission += 1;
                                            $t_exp_submission += 1;
                                            $exp_submission_ids .= $student_id.',';

                                            if ($firstResult->grade_id == 3 || $firstResult->grade_id == 7 || $firstResult->grade_id == 6 || $firstResult->grade_id == 5 || $firstResult->grade_id == 4 || $firstResult->grade_id == 8) {
                                                $no_of_submission += 1;
                                                $t_no_of_submission += 1;
                                                $no_of_submission_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 6 || $firstResult->grade_id == 5 || $firstResult->grade_id == 4) {
                                                $total_pass += 1;
                                                $t_total_pass += 1;
                                                $total_pass_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 6) {
                                                $grade_pass += 1;
                                                $t_grade_pass += 1;
                                                $grade_pass_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 5) {
                                                $grade_merit += 1;
                                                $t_grade_merit += 1;
                                                $grade_merit_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 4) {
                                                $grade_distinction += 1;
                                                $t_grade_distinction += 1;
                                                $grade_distinction_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 7) {
                                                $grade_referred += 1;
                                                $t_grade_referred += 1;
                                                $grade_referred_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 3) {
                                                $grade_plagiarised += 1;
                                                $t_grade_plagiarised += 1;
                                                $grade_plagiarised_ids .= $student_id.',';
                                            }
                                            if ($firstResult->grade_id == 2) {
                                                $grade_absent += 1;
                                                $t_grade_absent += 1;
                                                $grade_absent_ids .= $student_id.',';
                                            }
                                        endif;
                                        if(isset($allResult->grade_id) && $allResult->grade_id > 0):
                                            $al_exp_submission += 1;
                                            $al_t_exp_submission += 1;
                                            $al_exp_submission_ids .= $student_id.',';

                                            if ($allResult->grade_id == 3 || $allResult->grade_id == 7 || $allResult->grade_id == 6 || $allResult->grade_id == 5 || $allResult->grade_id == 4 || $allResult->grade_id == 8) {
                                                $al_no_of_submission += 1;
                                                $al_t_no_of_submission += 1;
                                                $al_no_of_submission_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 6 || $allResult->grade_id == 5 || $allResult->grade_id == 4) {
                                                $al_total_pass += 1;
                                                $al_t_total_pass += 1;
                                                $al_total_pass_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 6) {
                                                $al_grade_pass += 1;
                                                $al_t_grade_pass += 1;
                                                $al_grade_pass_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 5) {
                                                $al_grade_merit += 1;
                                                $al_t_grade_merit += 1;
                                                $al_grade_merit_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 4) {
                                                $al_grade_distinction += 1;
                                                $al_t_grade_distinction += 1;
                                                $al_grade_distinction_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 7) {
                                                $al_grade_referred += 1;
                                                $al_t_grade_referred += 1;
                                                $al_grade_referred_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 3) {
                                                $al_grade_plagiarised += 1;
                                                $al_t_grade_plagiarised += 1;
                                                $al_grade_plagiarised_ids .= $student_id.',';
                                            }
                                            if ($allResult->grade_id == 2) {
                                                $al_grade_absent += 1;
                                                $al_t_grade_absent += 1;
                                                $al_grade_absent_ids .= $student_id.',';
                                            }
                                        endif;
                                    endforeach;
                                endif;
                            endif;
                        endforeach;
                    endif;
                endif;
                $res['result'][$term->id]['course'][$course_id]['name'] = $course->name;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['student_status'] = $student_status;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['student_status_ids'] = $student_status_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['exp_submission'] = $exp_submission;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['exp_submission_ids'] = $exp_submission_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['no_of_submission'] = $no_of_submission;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['no_of_submission_ids'] = $no_of_submission_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['total_pass'] = $total_pass;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['total_pass_ids'] = $total_pass_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_pass'] = $grade_pass;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_pass_ids'] = $grade_pass_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_merit'] = $grade_merit;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_merit_ids'] = $grade_merit_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_distinction'] = $grade_distinction;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_distinction_ids'] = $grade_distinction_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_reffered'] = $grade_referred;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_reffered_ids'] = $grade_referred_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_plagiarised'] = $grade_plagiarised;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_plagiarised_ids'] = $grade_plagiarised_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_absent'] = $grade_absent;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_absent_ids'] = $grade_absent_ids;
                $res['result'][$term->id]['course'][$course_id]['f_sub']['total_pass_rate'] = (($total_pass > 0 && $no_of_submission > 0) ? number_format(($total_pass / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_pass_rate'] = (($grade_pass > 0 && $no_of_submission > 0) ? number_format(($grade_pass / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_merit_rate'] = (($grade_merit > 0 && $no_of_submission > 0) ? number_format(($grade_merit / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['f_sub']['grade_distinction_rate'] = (($grade_distinction > 0 && $no_of_submission > 0) ? number_format(($grade_distinction / $no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['f_sub']['assessment_rate'] = (($exp_submission > 0 && $no_of_submission > 0) ? number_format(($no_of_submission / $exp_submission) * 100, 2) : '0');

                $res['result'][$term->id]['course'][$course_id]['all_sub']['student_status'] = $al_student_status;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['student_status_ids'] = $al_student_status_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['exp_submission'] = $al_exp_submission;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['exp_submission_ids'] = $al_exp_submission_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['no_of_submission'] = $al_no_of_submission;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['no_of_submission_ids'] = $al_no_of_submission_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['total_pass'] = $al_total_pass;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['total_pass_ids'] = $al_total_pass_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_pass'] = $al_grade_pass;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_pass_ids'] = $al_grade_pass_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_merit'] = $al_grade_merit;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_merit_ids'] = $al_grade_merit_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_distinction'] = $al_grade_distinction;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_distinction_ids'] = $al_grade_distinction_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_reffered'] = $al_grade_referred;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_reffered_ids'] = $al_grade_referred_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_plagiarised'] = $al_grade_plagiarised;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_plagiarised_ids'] = $al_grade_plagiarised_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_absent'] = $al_grade_absent;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_absent_ids'] = $al_grade_absent_ids;
                $res['result'][$term->id]['course'][$course_id]['all_sub']['total_pass_rate'] = (($al_total_pass > 0 && $al_no_of_submission > 0) ? number_format(($al_total_pass / $al_no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_pass_rate'] = (($al_grade_pass > 0 && $al_no_of_submission > 0) ? number_format(($al_grade_pass / $al_no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_merit_rate'] = (($al_grade_merit > 0 && $al_no_of_submission > 0) ? number_format(($al_grade_merit / $al_no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['all_sub']['grade_distinction_rate'] = (($al_grade_distinction > 0 && $al_no_of_submission > 0) ? number_format(($al_grade_distinction / $al_no_of_submission) * 100, 2) : '0');
                $res['result'][$term->id]['course'][$course_id]['all_sub']['assessment_rate'] = (($al_exp_submission > 0 && $al_no_of_submission > 0) ? number_format(($al_no_of_submission / $al_exp_submission) * 100, 2) : '0');
            endforeach;

            $res['result'][$term->id]['name'] = $term->name;
            $res['result'][$term->id]['f_sub']['student_status'] = $t_student_status;
            $res['result'][$term->id]['f_sub']['exp_submission'] = $t_exp_submission;
            $res['result'][$term->id]['f_sub']['no_of_submission'] = $t_no_of_submission;
            $res['result'][$term->id]['f_sub']['total_pass'] = $t_total_pass;
            $res['result'][$term->id]['f_sub']['grade_pass'] = $t_grade_pass;
            $res['result'][$term->id]['f_sub']['grade_merit'] = $t_grade_merit;
            $res['result'][$term->id]['f_sub']['grade_distinction'] = $t_grade_distinction;
            $res['result'][$term->id]['f_sub']['grade_reffered'] = $t_grade_referred;
            $res['result'][$term->id]['f_sub']['grade_plagiarised'] = $t_grade_plagiarised;
            $res['result'][$term->id]['f_sub']['grade_absent'] = $t_grade_absent;
            $res['result'][$term->id]['f_sub']['total_pass_rate'] = (($t_total_pass > 0 && $t_no_of_submission > 0) ? number_format(($t_total_pass / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['f_sub']['grade_pass_rate'] = (($t_grade_pass > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_pass / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['f_sub']['grade_merit_rate'] = (($t_grade_merit > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_merit / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['f_sub']['grade_distinction_rate'] = (($t_grade_distinction > 0 && $t_no_of_submission > 0) ? number_format(($t_grade_distinction / $t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['f_sub']['assessment_rate'] = (($t_exp_submission > 0 && $t_no_of_submission > 0) ? number_format(($t_no_of_submission / $t_exp_submission) * 100, 2) : '0');
            
            $res['result'][$term->id]['all_sub']['student_status'] = $al_t_student_status;
            $res['result'][$term->id]['all_sub']['exp_submission'] = $al_t_exp_submission;
            $res['result'][$term->id]['all_sub']['no_of_submission'] = $al_t_no_of_submission;
            $res['result'][$term->id]['all_sub']['total_pass'] = $al_t_total_pass;
            $res['result'][$term->id]['all_sub']['grade_pass'] = $al_t_grade_pass;
            $res['result'][$term->id]['all_sub']['grade_merit'] = $al_t_grade_merit;
            $res['result'][$term->id]['all_sub']['grade_distinction'] = $al_t_grade_distinction;
            $res['result'][$term->id]['all_sub']['grade_reffered'] = $al_t_grade_referred;
            $res['result'][$term->id]['all_sub']['grade_plagiarised'] = $al_t_grade_plagiarised;
            $res['result'][$term->id]['all_sub']['grade_absent'] = $al_t_grade_absent;
            $res['result'][$term->id]['all_sub']['total_pass_rate'] = (($al_t_total_pass > 0 && $al_t_no_of_submission > 0) ? number_format(($al_t_total_pass / $al_t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['all_sub']['grade_pass_rate'] = (($al_t_grade_pass > 0 && $al_t_no_of_submission > 0) ? number_format(($al_t_grade_pass / $al_t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['all_sub']['grade_merit_rate'] = (($al_t_grade_merit > 0 && $al_t_no_of_submission > 0) ? number_format(($al_t_grade_merit / $al_t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['all_sub']['grade_distinction_rate'] = (($al_t_grade_distinction > 0 && $al_t_no_of_submission > 0) ? number_format(($al_t_grade_distinction / $al_t_no_of_submission) * 100, 2) : '0');
            $res['result'][$term->id]['all_sub']['assessment_rate'] = (($al_t_exp_submission > 0 && $al_t_no_of_submission > 0) ? number_format(($al_t_no_of_submission / $al_t_exp_submission) * 100, 2) : '0');
        endif;
        
        return $res;
    }

    public function getStudentList(Request $request){
        $student_ids = (isset($request->student_ids) && !empty($request->student_ids) ? explode(',', $request->student_ids) : [0]);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'registration_no', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        $Query = Student::whereIn('id', $student_ids)->orderByRaw(implode(',', $sorts));

        $total_rows = $Query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 50));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        
        $Query = $Query->orderByRaw(implode(',', $sorts))->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'disability' =>  (isset($list->other->disability_status) && $list->other->disability_status > 0 ? $list->other->disability_status : 0),
                    'full_time' => (isset($list->activeCR->propose->full_time) && $list->activeCR->propose->full_time > 0) ? $list->activeCR->propose->full_time : 0, 
                    'registration_no' => (!empty($list->registration_no) ? $list->registration_no : $list->application_no),
                    'first_name' => $list->first_name,
                    'last_name' => $list->last_name,
                    'course'=> (isset($list->activeCR->creation->course->name) && !empty($list->activeCR->creation->course->name) ? $list->activeCR->creation->course->name : ''),
                    'semester'=> (isset($list->activeCR->creation->semester->name) && !empty($list->activeCR->creation->semester->name) ? $list->activeCR->creation->semester->name : ''),
                    'status_id'=> (isset($list->status->name) && !empty($list->status->name) ? $list->status->name : ''),
                    'url' => route('student.show', $list->id),
                    'photo_url' => $list->photo_url,
                    'flag_html' => (isset($list->flag_html) && !empty($list->flag_html) ? $list->flag_html : ''),
                    'due' => $list->due
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

}
