<?php

namespace App\Http\Controllers\Reports\TermPerformance;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Assign;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAwardingBodyDetails;
use App\Models\StudentCourseRelation;
use App\Models\TermDeclaration;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class TermRetentionReportController extends Controller
{
    public function generateReport(Request $request){
        $term_declaration_ids = (isset($request->retention_term_id) && !empty($request->retention_term_id) ? $request->retention_term_id : []);
        $html = $this->getHtml($term_declaration_ids);
        
        return response()->json(['htm' => $html], 200);
    }

    public function printReport($term_declaration_ids = ''){
        $term_declaration_ids = (!empty($term_declaration_ids) ? explode('_', $term_declaration_ids) : []);
        $termName = (!empty($term_declaration_ids) ? TermDeclaration::whereIn('id', [$term_declaration_ids])->pluck('name')->unique()->toArray() : []);
        $user = User::find(auth()->user()->id);

        $html = $this->getHtml($term_declaration_ids);
        $html = str_replace('style="display: none;"', '', $html);

        $report_title = 'Term Retention Reports';
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
                                .table.termRetentionTable tr th, .table.termRetentionTable tr td{ text-align: left; padding: 5px;}
                                .table.termRetentionTable tr a{ text-decoration: none; color: #1e293b; }
                                .table.termRetentionTable tr th.text-right, .table.termRetentionTable tr td.text-right{ text-align: right; }
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

    public function exportReport($term_declaration_ids = ''){
        $term_declaration_ids = (!empty($term_declaration_ids) ? explode('_', $term_declaration_ids) : []);
        $termName = (!empty($term_declaration_ids) ? TermDeclaration::whereIn('id', [$term_declaration_ids])->pluck('name')->unique()->toArray() : []);
        $res = $this->refineResult($term_declaration_ids);

        $term = TermDeclaration::where('id', $term_declaration_ids)->get()->first();
        $user = User::find(auth()->user()->id);

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = "Report Name";
        $theCollection[$row][] = "Retention Rate Report";
        $row += 1;

        $theCollection[$row][] = "Attendance Term";
        $theCollection[$row][] = (!empty($termName) ? implode(', ', $termName) : '');
        $row += 1;

        $theCollection[$row][] = "Report created date time";
        $theCollection[$row][] = date('jS F, Y H:i');
        $row += 1;

        $theCollection[$row][] = "Created By";
        $theCollection[$row][] = (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name);
        $row += 1;

        $theCollection[$row][] = '';
        $row += 1;

        if(isset($res['result']) && !empty($res['result'])):
            foreach($res['result'] as $term_id => $term_data):
                $term_rate = ($term_data['registered'] > 0 && $term_data['droppedout'] > 0 ? (($term_data['registered'] - $term_data['droppedout']) / $term_data['registered']) * 100 : 0);
                $theCollection[$row][] = $term_data['name'];
                $theCollection[$row][] = $term_data['admissions'];
                $theCollection[$row][] = $term_data['registered'];
                $theCollection[$row][] = $term_data['droppedout'];
                $theCollection[$row][] = number_format($term_rate, 2);
                $row += 1;

                if(isset($term_data['semester']) && !empty($term_data['semester'])):
                    foreach($term_data['semester'] as $semester_id => $semester_data):
                        $sems_rate = ($semester_data['registered'] > 0 && $semester_data['droppedout'] > 0 ? (($semester_data['registered'] - $semester_data['droppedout']) / $semester_data['registered']) * 100 : 0);
                        $theCollection[$row][] = $semester_data['name'];
                        $theCollection[$row][] = $semester_data['admissions'];
                        $theCollection[$row][] = $semester_data['registered'];
                        $theCollection[$row][] = $semester_data['droppedout'];
                        $theCollection[$row][] = number_format($sems_rate, 2);
                        $row += 1;
                    endforeach;
                endif;

                $theCollection[$row][] = '';
                $row += 1;
                $theCollection[$row][] = '';
                $row += 1;
            endforeach;
        endif;

        $report_title = 'Term_Retention_Reports.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
    }

    public function getHtml($term_declaration_ids = []){
        $res = $this->refineResult($term_declaration_ids);

        $html = '';
        $html .= '<table class="table table-bordered termRetentionTable  table-sm" id="termRetentionTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th>&nbsp;</th>';
                    $html .= '<th>Total Student</th>';
                    $html .= '<th>Registered</th>';
                    $html .= '<th>Dropped Out</th>';
                    $html .= '<th class="text-right">Rate</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($res['result']) && !empty($res['result'])):
                    foreach($res['result'] as $term_id => $term_data):
                        $term_rate = ($term_data['registered'] > 0 && $term_data['droppedout'] > 0 ? (($term_data['registered'] - $term_data['droppedout']) / $term_data['registered']) * 100 : 0);
                        $html .= '<tr class="termRow">';
                            $html .= '<th><a href="javascript:void(0);" class="semisterToggle text-primary font-medium underline" data-termid="'.$term_id.'">+ '.$term_data['name'].'</a></th>';
                            $html .= '<th class="w-[150px]">'.$term_data['admissions'].'</th>';
                            $html .= '<th class="w-[150px]">'.$term_data['registered'].'</th>';
                            $html .= '<th class="w-[150px]">'.$term_data['droppedout'].'</th>';
                            $html .= '<th class="w-[150px]" style="text-align: right;">'.number_format($term_rate, 2).'%</th>';
                        $html .= '</tr>';

                        if(isset($term_data['semester']) && !empty($term_data['semester'])):
                            foreach($term_data['semester'] as $semester_id => $semester_data):
                                $sems_rate = ($semester_data['registered'] > 0 && $semester_data['droppedout'] > 0 ? (($semester_data['registered'] - $semester_data['droppedout']) / $semester_data['registered']) * 100 : 0);
                                $html .= '<tr class="semester_row_'.$term_id.'" style="display: none;">';
                                    $html .= '<td style="padding-left: 28px;">'.$semester_data['name'].'</td>';
                                    $html .= '<td class="w-[150px]"><a href="javascript:void(0);" class="trmRetnStdBtn text-primary font-medium underline" data-ids="'.(!empty($semester_data['admissions_ids']) ? implode(',', $semester_data['admissions_ids']) : '').'">'.$semester_data['admissions'].'</a></td>';
                                    $html .= '<td class="w-[150px]"><a href="javascript:void(0);" class="trmRetnStdBtn text-primary font-medium underline" data-ids="'.(!empty($semester_data['registered_ids']) ? implode(',', $semester_data['registered_ids']) : '').'">'.$semester_data['registered'].'</a></td>';
                                    $html .= '<td class="w-[150px]"><a href="javascript:void(0);" class="trmRetnStdBtn text-primary font-medium underline" data-ids="'.(!empty($semester_data['droppedout_ids']) ? implode(',', $semester_data['droppedout_ids']) : '').'">'.$semester_data['droppedout'].'</a></td>';
                                    $html .= '<td class="w-[150px]" style="text-align: right;">'.number_format($sems_rate, 2).'%</td>';
                                $html .= '</tr>';
                            endforeach;
                        endif;
                    endforeach;
                endif;
            $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    public function refineResult($term_declaration_ids = []){
        $res = [];
        if(!empty($term_declaration_ids)):
            foreach($term_declaration_ids as $term_declaration_id):
                $term = TermDeclaration::find($term_declaration_id);
                $planIds = $term->plans->pluck('id')->toArray();
                $studentIdsFromAssignTable = Assign::whereIn('plan_id', $planIds)->pluck('student_id')->unique()->toArray();

                $creationInstancesIds = InstanceTerm::where('term_declaration_id', $term_declaration_id)->pluck('course_creation_instance_id')->unique()->toArray();
                $creationIds = CourseCreationInstance::whereIn('id', $creationInstancesIds)->orderBy('course_creation_id', 'DESC')->pluck('course_creation_id')->unique()->toArray();
                $semester_ids = CourseCreation::whereIn('id', $creationIds)->orderBy('semester_id', 'DESC')->pluck('semester_id')->unique()->toArray();
                
                $term_admissions = $term_registered = $term_droppedout = 0;
                foreach($semester_ids as $semester_id):
                    $semester = Semester::find($semester_id);
                    $res['result'][$term->id]['semester'][$semester_id]['semester_id'] = $semester_id;
                    $res['result'][$term->id]['semester'][$semester_id]['name'] = $semester->name;

                    $admissions = $registered = $droppedout = 0;
                    $admissions_ids = $registered_ids = $droppedout_ids = [];
                    $course_creations = CourseCreation::where('semester_id', $semester_id)->get();
                    if($course_creations->count() > 0):
                        foreach($course_creations as $creation):
                            $courseCreationId = $creation->id;
                            $courseStartDate = (isset($creation->available->course_start_date) && !empty($creation->available->course_start_date) ? date('Y-m-d', strtotime($creation->available->course_start_date)) : '');
                            $courseEndDate = (isset($creation->available->course_end_date) && !empty($creation->available->course_end_date) ? date('Y-m-d', strtotime($creation->available->course_end_date)) : '');
                            $refund_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 28 days')) : '');
                            $completion_date = (!empty($courseStartDate) ? date('Y-m-d', strtotime($courseStartDate.' + 380 days')) : '');

                            $student_ids = StudentCourseRelation::where('course_creation_id', $courseCreationId)->where('active', 1)->pluck('student_id')->unique()->toArray();
                            // intersect with
                            $student_ids = array_intersect($student_ids, $studentIdsFromAssignTable);
                            // check is there any duplicate registration_no found. if found take the latest id only
                            if(!empty($student_ids)){
                                $rows = DB::table('students')->whereIn('id', $student_ids)->orderBy('id', 'DESC')->select('id', 'registration_no')->get();
                                $seenReg = [];
                                $filtered = [];
                                foreach($rows as $r){
                                    $reg = trim((string)$r->registration_no);
                                    if(!empty($reg)){
                                        if(!isset($seenReg[$reg])){
                                            $seenReg[$reg] = $r->id;
                                            $filtered[] = $r->id;
                                        }
                                        // else duplicate registration_no found, skip (we keep the first which is latest id because of orderBy id DESC)
                                    }else{
                                        // no registration_no, keep the record
                                        $filtered[] = $r->id;
                                    }
                                }
                                $student_ids = array_values(array_unique($filtered));
                            }


                            if(!empty($student_ids) && count($student_ids) > 0):
                                $registered_std_ids = StudentAwardingBodyDetails::whereIn('student_id', $student_ids)->whereNotNull('reference')->whereHas('studentcrel', function($q) use($courseCreationId){
                                                        $q->where('course_creation_id', $courseCreationId);
                                                    })->pluck('student_id')->unique()->toArray();
                                //$terminated_std_ids = (!empty($terminatedStudents) ? array_diff($student_ids, $terminatedStudents) : $student_ids);

                                $droppedOutStdents = DB::table('students as std')
                                                    ->leftJoin('student_attendance_term_statuses as sats', function($j){
                                                        $j->on('std.id', 'sats.student_id');
                                                        $j->on('std.status_id', 'sats.status_id');
                                                    })->whereIn('std.id', $registered_std_ids)
                                                    ->whereIn('sats.status_id', [22, 27, 30, 31, 42, 43, 45, 14, 17, 33, 36, 47, 50])
                                                    ->where(function($q) use($refund_date, $courseEndDate){
                                                        $q->whereDate('sats.status_change_date', '>=', date('Y-m-d', strtotime($refund_date)))->whereDate('sats.status_change_date', '<=', date('Y-m-d', strtotime($courseEndDate)));
                                                    })->pluck('std.id')->unique()->toArray();

                                $admissions += (!empty($student_ids) ? count($student_ids) : 0);
                                $admissions_ids = array_merge($admissions_ids, $student_ids);

                                $droppedout += (!empty($droppedOutStdents) ? count($droppedOutStdents) : 0);
                                $droppedout_ids = array_merge($droppedout_ids, $droppedOutStdents);
                                
                                $registered_ids = !empty($droppedOutStdents) ? array_diff($registered_std_ids, $droppedOutStdents) : $registered_ids;

                                
                                $registered += (!empty($registered_ids) ? count($registered_ids) : 0);
                                
                                // remove dropped out ids from registered ids
                                
                            endif;
                        endforeach;
                    endif;
                    
                    $res['result'][$term->id]['semester'][$semester_id]['admissions'] = $admissions;
                    $res['result'][$term->id]['semester'][$semester_id]['admissions_ids'] = $admissions_ids;
                    $res['result'][$term->id]['semester'][$semester_id]['registered'] = $registered;
                    $res['result'][$term->id]['semester'][$semester_id]['registered_ids'] = $registered_ids;
                    $res['result'][$term->id]['semester'][$semester_id]['droppedout'] = $droppedout;
                    $res['result'][$term->id]['semester'][$semester_id]['droppedout_ids'] = $droppedout_ids;

                    $term_admissions += $admissions;
                    $term_registered += $registered;
                    $term_droppedout += $droppedout;
                endforeach;
                $res['result'][$term->id]['term_id'] = $term->id;
                $res['result'][$term->id]['name'] = $term->name;
                $res['result'][$term->id]['admissions'] = $term_admissions;
                $res['result'][$term->id]['registered'] = $term_registered;
                $res['result'][$term->id]['droppedout'] = $term_droppedout;
            endforeach;
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
