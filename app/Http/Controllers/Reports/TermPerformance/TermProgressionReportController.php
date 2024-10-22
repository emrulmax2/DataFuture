<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentCourseRelation;
use App\Models\TermDeclaration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TermProgressionReportController extends Controller
{
    public function generateReport(Request $request){
        $semester_id = (isset($request->progression_semester_id) && !empty($request->progression_semester_id) ? $request->progression_semester_id : 0);
        $html = $this->getHtml($semester_id);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($semester_id = 0){
        $termName = ($semester_id > 0 ? Semester::whereIn('id', [$semester_id])->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($semester_id);
        $html = str_replace('style="display: none;"', '', $html);

        $report_title = 'Term Progression Reports';
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
                            $PDFHTML .= '<td>Intake Semester</td>';
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
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')//landscape portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }

    public function exportReport($semester_id = 0){
        $res = $this->refineResult($semester_id);

        $semester = Semester::where('id', $semester_id)->get()->first();
        $user = User::find(auth()->user()->id);

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = "Report Name";
        $theCollection[$row][] = "Progression Report";
        $row += 1;

        $theCollection[$row][] = "Intake Semester";
        $theCollection[$row][] = $semester->name;
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
            $theCollection[$row][] = '';
            $theCollection[$row][] = 'Number Enrolled';
            $theCollection[$row][] = 'Year 1 (SLC year 1 registration confirmed)';
            $theCollection[$row][] = 'Year 2 (SLC year 2 registration confirmed)';
            $row += 1;
            foreach($res['result'] as $semester_id => $semester_data):
                $theCollection[$row][] = $semester_data['name'];
                $theCollection[$row][] = $semester_data['no_of_enrolled'];
                $theCollection[$row][] = $semester_data['year_1_confirmed'];
                $theCollection[$row][] = $semester_data['year_2_confirmed'];
                $row += 1;

                if(isset($semester_data['course']) && !empty($semester_data['course'])):
                    foreach($semester_data['course'] as $course_id => $course):
                        $theCollection[$row][] = $course['name'];
                        $theCollection[$row][] = $course['no_of_enrolled'];
                        $theCollection[$row][] = $course['year_1_confirmed'];
                        $theCollection[$row][] = $course['year_2_confirmed'];
                        $row += 1;
                    endforeach;
                endif;

                $theCollection[$row][] = '';
                $row += 1;
                $theCollection[$row][] = '';
                $row += 1;
            endforeach;
        endif;

        $report_title = 'Term_Progression_Reports.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function getHtml($semester_id = 0){
        $res = $this->refineResult($semester_id);

        $html = '';
        if(!empty($res['result'])):
            $html .= '<table class="table table-bordered submissionPerformanceReportTable table-sm mb-5" id="submissionPerformanceReportTable">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th>&nbsp;</th>';
                        $html .= '<th>Number Enrolled</th>';
                        $html .= '<th>Year 1 (SLC year 1 registration confirmed )</th>';
                        $html .= '<th>Year 2 (SLC year 2 registration confirmed )</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    foreach($res['result'] as $semester_id => $semester_data):
                        $html .= '<tr>';
                            $html .= '<td><a href="javascript:void(0);" class="semesterRowToggle text-primary underline font-medium" data-semester="'.$semester_id.'">+ '.$semester_data['name'].'</a></td>';
                            $html .= '<td class="w-1/6">'.$semester_data['no_of_enrolled'].'</td>';
                            $html .= '<td class="w-1/6">'.$semester_data['year_1_confirmed'].'</td>';
                            $html .= '<td class="w-1/6">'.$semester_data['year_2_confirmed'].'</td>';
                        $html .= '</tr>';
                        if(isset($semester_data['course']) && !empty($semester_data['course'])):
                            foreach($semester_data['course'] as $course_id => $course):
                                $html .= '<tr class="course_row_'.$semester_id.'" style="display: none;">'; 
                                    $html .= '<td>'.$course['name'].'</a></td>';
                                    $html .= '<td class="w-1/6"><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$course['no_of_enrolled_ids'].'">'.$course['no_of_enrolled'].'</a></td>';
                                    $html .= '<td class="w-1/6"><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$course['year_1_confirmed_ids'].'">'.$course['year_1_confirmed'].'</a></td>';
                                    $html .= '<td class="w-1/6"><a href="javascript:void(0);" class="subPerfmStdBtn text-primary font-medium underline" data-ids="'.$course['year_2_confirmed_ids'].'">'.$course['year_2_confirmed'].'</a></td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
        endif;

        return $html;
    }

    public function refineResult($semester_id = 0){
        $res = [];
        $semester = Semester::find($semester_id);
        $courseCreations = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'ASC')->get();
        $progressionStatus = [21, 23, 24, 26, 27, 28, 29, 30, 31, 42, 43, 44];

        if($semester_id > 0):
            $semester = Semester::find($semester_id);
            $courseCreations = CourseCreation::where('semester_id', $semester_id)->orderBy('course_id', 'ASC')->get();
            if(!empty($courseCreations) && $courseCreations->count() > 0):
                $tNoEnrolled = $tYear_1 = $tYear_2 = 0;
                foreach($courseCreations as $creation):
                    $noEnrolled = $year_1 = $year_2 = 0;
                    $enrolled_ids = $year_1_ids = $year_2_ids = '';

                    $student_ids = StudentCourseRelation::where('course_creation_id', $creation->id)->pluck('student_id')->unique()->toArray();
                    if(!empty($student_ids) && count($student_ids) > 0):
                        foreach($student_ids as $student_id):
                            $student = Student::find($student_id);
                            $studentCr = StudentCourseRelation::where('student_id', $student_id)->where('course_creation_id', $creation->id)->get()->first();
                            if(in_array($student->status_id, $progressionStatus)):
                                $noEnrolled += 1;
                                $tNoEnrolled += 1;
                                $enrolled_ids .= $student_id.',';

                                $slcRegistrations = SlcRegistration::where('student_id', $student_id)->where('student_course_relation_id', $studentCr->id)
                                                    ->where('slc_registration_status_id', 1)->orderBy('id', 'ASC')->get();
                                if($slcRegistrations->count() > 0):
                                    $s = $s2 = 0;

                                    foreach ($slcRegistrations as $reg):
                                        if (isset($reg->slc_registration_status_id) && $reg->slc_registration_status_id == 1):
                                            $s += 1;
                                            if ($reg->registration_year == 2):
                                                $s2 += 1;
                                            endif;
                                        endif;
                                    endforeach;

                                    if($s >= 2 && $s2 > 0):
                                        $year_2 += 1;
                                        $tYear_2 += 1;
                                        $year_2_ids .= $student_id.',';
                                    elseif ($s <= 2 && $s >= 1):
                                        $year_1 += 1;
                                        $tYear_1 += 1;
                                        $year_1_ids .= $student_id.',';
                                    endif;
                                endif;
                            endif;
                        endforeach;
                    endif;
                    $res['result'][$semester_id]['course'][$creation->course_id]['name'] = (isset($creation->course->name) && !empty($creation->course->name) ? $creation->course->name : '');
                    $res['result'][$semester_id]['course'][$creation->course_id]['no_of_enrolled'] = $noEnrolled;
                    $res['result'][$semester_id]['course'][$creation->course_id]['no_of_enrolled_ids'] = $enrolled_ids;
                    $res['result'][$semester_id]['course'][$creation->course_id]['year_1_confirmed'] = ($year_1 + $year_2);
                    $res['result'][$semester_id]['course'][$creation->course_id]['year_1_confirmed_ids'] = $year_1_ids.$year_2_ids;
                    $res['result'][$semester_id]['course'][$creation->course_id]['year_2_confirmed'] = $year_2;
                    $res['result'][$semester_id]['course'][$creation->course_id]['year_2_confirmed_ids'] = $year_2_ids;
                endforeach;
                $res['result'][$semester_id]['name'] = $semester->name;
                $res['result'][$semester_id]['no_of_enrolled'] = $tNoEnrolled;
                $res['result'][$semester_id]['year_1_confirmed'] = ($tYear_1 + $tYear_2);
                $res['result'][$semester_id]['year_2_confirmed'] = $tYear_2;
            endif;

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
