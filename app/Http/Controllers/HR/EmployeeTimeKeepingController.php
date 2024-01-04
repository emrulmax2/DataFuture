<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Employment;
use App\Models\HrCondition;
use App\Models\HrHolidayYear;
use App\Models\LetterHeaderFooter;
use App\Models\Option;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeTimeKeepingController extends Controller
{
    public function index($id){
        $employee = Employee::find($id);
        $employment = Employment::where("employee_id",$id)->get()->first();
        $clockin = HrCondition::where('type', 'Clock In')->where('time_frame', 3)->get()->first();
        $clockout = HrCondition::where('type', 'Clock Out')->where('time_frame', 1)->get()->first();
        
        return view('pages.employee.profile.time-keeper',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "employee" => $employee,
            "employment" => $employment,
            'empAttendances' => $this->getEmployeeTimeKeepingData($id),
            'clockin' => (isset($clockin->minutes) && $clockin->minutes > 0 ? $clockin->minutes : 7),
            'clockout' => (isset($clockout->minutes) && $clockout->minutes > 0 ? $clockout->minutes : 7),
        ]);
    }

    public function getEmployeeTimeKeepingData($employee_id){
        $attendanceStarts = EmployeeAttendance::where('employee_id', $employee_id)->orderBy('date', 'ASC')->get()->first();
        $AttenStartDate = (isset($attendanceStarts->date) && !empty($attendanceStarts->date) ? date('Y-m-d', strtotime($attendanceStarts->date)) : date('Y-m-d'));
        $holidayYears = HrHolidayYear::where('end_date', '>=', $AttenStartDate)->orderBy('start_date', 'DESC')->get();

        $res = [];
        if(!empty($holidayYears)):
            foreach($holidayYears as $year):
                $yearStart = (isset($year->start_date) && !empty($year->start_date) ? date('Y-m-d', strtotime($year->start_date)) : '');
                $yearEnd = (isset($year->end_date) && !empty($year->end_date) ? date('Y-m-d', strtotime($year->end_date)) : '');

                if($yearStart != '' && $yearEnd != ''):
                    $res[$year->id]['start_date'] = $yearStart;
                    $res[$year->id]['end_date'] = $yearEnd;

                    $theStart = strtotime($yearStart);
                    $theEnd = strtotime($yearEnd);
                    while($theEnd > $theStart):
                        $theMonthStart = date('Y-m', $theEnd).'-01';
                        $theMonthEnd = date('Y-m-t', $theEnd);

                        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->whereBetween('date', [$theMonthStart, $theMonthEnd])->orderBy('date', 'ASC')->get();
                        if($attendances->count() > 0):
                            $res[$year->id]['month'][date('n', strtotime($theMonthStart))]['start_date'] = $theMonthStart;
                            $res[$year->id]['month'][date('n', strtotime($theMonthStart))]['attendances'] = $attendances;
                        endif;

                        $theEnd = strtotime("-1 month", $theEnd);
                    endwhile;
                endif;
            endforeach;
        endif;

        return $res;
    }

    public function downloadPdf($employee_id, $theDate){
        $employee = Employee::find($employee_id);

        $theMonthStart = date('Y-m', strtotime($theDate)).'-01';
        $theMonthEnd = date('Y-m-t', strtotime($theDate));
        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->whereBetween('date', [$theMonthStart, $theMonthEnd])->orderBy('date', 'ASC')->get();

        $clockin = HrCondition::where('type', 'Clock In')->where('time_frame', 3)->get()->first();
        $clockout = HrCondition::where('type', 'Clock Out')->where('time_frame', 1)->get()->first();

        $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
        $LetterHeader = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
        $LetterFooter = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
        $PDF_title = $employee->full_name.' Time Recored for the Month '.date('F Y', strtotime($theDate));

        $PDFHTML = '';
        $PDFHTML .= '<html>';
            $PDFHTML .= '<head>';
                $PDFHTML .= '<title>'.$PDF_title.'</title>';
                $PDFHTML .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
                $PDFHTML .= '<style>
                                body{font-family: Tahoma, sans-serif; font-size: 13px; line-height: normal; color: rgb(30, 41, 59);}
                                table{margin-left: 0px;}
                                figure{margin: 0;}
                                @page{margin-top: 125px;margin-left: 30px;margin-right: 30px;margin-bottom: 90px;}
                                header{position: fixed;left: 0px;right: 0px;height: 90px;margin-top: -100px;}
                                footer{position: fixed;left: 0px;right: 0px;bottom: 0;height: 100px; margin-bottom: -120px;}
                                .regInfoRow td{border-top: 1px solid gray;}
                                .text-center{text-align: center;}
                                .text-left{text-align: left;}
                                .text-right{text-align: right;}

                                .bodyContainer{font-size: 13px; line-height: normal; padding: 0 15px;}
                                .tableTitle{font-size: 22px; font-weight: bold; color: #000; line-height: 22px; margin: 0;}
                                .employeeInfo{line-height: normal;}
                                .mb-30{margin-bottom: 30px;}
                                .mb-20{margin-bottom: 20px;}
                                .mb-15{margin-bottom: 15px;}
                                .text-justify{text-align: justify;}
                                .font-medium{ font-weight: 500; }
                            
                                .table {width: 100%; text-align: left; text-indent: 0; border-color: inherit; border-collapse: collapse;}
                                .table th {font-family: Tahoma, sans-serif; border-style: solid;border-color: #e5e7eb;border-bottom-width: 2px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;font-weight: 500;}
                                .table td {border-style: solid;border-color: #e5e7eb; border-bottom-width: 1px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;}

                                .table.table-bordered th, .table.table-bordered td {border-left-width: 1px;border-right-width: 1px;border-top-width: 1px;}

                                .table.table-sm th {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                .table.table-sm td {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}

                                .timeKeepingRow{ cursor: pointer; }
                                .timeKeepingRow_1{ background: rgb(0 119 181); color: #FFF;}
                                .timeKeepingRow_2{ background: rgb(0 0 0); color: #FFF;}
                                .timeKeepingRow_3{ background: rgb(30 41 59); color: #FFF;}
                                .timeKeepingRow_4{ background: rgb(185 28 28); color: #FFF;}
                                .timeKeepingRow_5{ background: rgb(59 89 152); color: #FFF;}
                                .timeKeepingRow_ov{ background: rgb(217 119 6); color: #FFF;}
                            </style>';
            $PDFHTML .= '</head>';
            $PDFHTML .= '<body>';
                if(isset($LetterHeader->current_file_name) && !empty($LetterHeader->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/header/'.$LetterHeader->current_file_name)):
                    $PDFHTML .= '<header>';
                        $PDFHTML .= '<img style="width: 100%; height: auto;" src="'.url('storage/letterheaderfooter/header/'.$LetterHeader->current_file_name).'"/>';
                    $PDFHTML .= '</header>';
                endif;

                $PDFHTML .= '<footer>';
                    $PDFHTML .= '<table style="width: 100%; border: none; margin: 0; vertical-align: middle !important; 
                                font-size: 8pt; color: #000000; font-weight: bold; font-style: italic;border-spacing: 0;border-collapse: collapse;">';
                        if(isset($LetterFooter->current_file_name) && !empty($LetterFooter->current_file_name) && Storage::disk('local')->exists('public/letterheaderfooter/footer/'.$LetterFooter->current_file_name)):
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<td class="footerPartners" style="text-align: center; vertical-align: middle; padding-bottom: 5px;">';
                                    $PDFHTML .= '<img style=" max-width: 100%; height: auto;" src="'.Storage::disk('local')->url('public/letterheaderfooter/footer/'.$LetterFooter->current_file_name).'" alt="'.$LetterFooter->name.'"/>';
                                $PDFHTML .= '</td>';
                            $PDFHTML .= '</tr>';
                        endif;

                        if(!empty($companyReg) && isset($companyReg->value) && !empty($companyReg->value)):
                        $PDFHTML .= '<tr class="regInfoRow">';
                            $PDFHTML .= '<td class="text-center" style="padding-top: 10px;">';
                                $PDFHTML .= $companyReg->value;
                            $PDFHTML .= '</td>';
                        $PDFHTML .= '</tr>';
                        endif;
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</footer>';

                /*PDF BODY START*/
                $PDFHTML .= '<div class="bodyContainer">';
                    $PDFHTML .= '<table class="mb-15" style="width: 100%;">';
                        $PDFHTML .= '<tr>';
                            $PDFHTML .= '<td><span class="tableTitle">'.$employee->full_name.'</span></td>';
                            $PDFHTML .= '<td class="text-right"><span class="tableTitle">'.date('F Y', strtotime($theMonthStart)).'</span></td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                    $PDFHTML .= '<table class="table table-sm table-bordered">';
                        $PDFHTML .= '<thead>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<th class="text-left">Date</th>';
                                $PDFHTML .= '<th class="text-left">Status</th>';
                                $PDFHTML .= '<th class="text-left">Note</th>';
                                $PDFHTML .= '<th class="text-left">Clock In - Out</th>';
                                $PDFHTML .= '<th class="text-left">Break</th>';
                            $PDFHTML .= '</tr>';
                        $PDFHTML .= '</thead>';
                        $PDFHTML .= '<tbody>';
                            foreach($attendances as $attn):
                                $note = [];
                                $clockin_punch = (isset($attn->clockin_punch) && !empty($attn->clockin_punch) && $attn->clockin_punch != '00:00' ? $attn->clockin_punch.':00' : '');
                                $clockin_contract = (isset($attn->clockin_contract) && !empty($attn->clockin_contract) && $attn->clockin_contract != '00:00' ? $attn->clockin_contract.':00' : '');
                                $clockin_system = (isset($attn->clockin_system) && !empty($attn->clockin_system) && $attn->clockin_system != '00:00' ? $attn->clockin_system.':00' : '');
                                
                                $clockout_punch = (isset($attn->clockout_punch) && !empty($attn->clockout_punch) && $attn->clockout_punch != '00:00' ? $attn->clockout_punch.':00' : '');
                                $clockout_contract = (isset($attn->clockout_contract) && !empty($attn->clockout_contract) && $attn->clockout_contract != '00:00' ? $attn->clockout_contract.':00' : '');
                                $clockout_system = (isset($attn->clockout_system) && !empty($attn->clockout_system) && $attn->clockout_system != '00:00' ? $attn->clockout_system.':00' : '');
                                if($attn->total_work_hour > 0 && ($attn->leave_status == 0 || empty($attn->leave_status)) && $attn->overtime_status != 1):
                                    if(!empty($clockin_punch) && !empty($clockin_contract)):
                                        $lastIn = date('H:i', strtotime('+'.$clockin.' minutes', strtotime($clockin_contract))).':00';
                                        if($clockin_punch > $lastIn):
                                            $note[] = 'Late';
                                        endif;
                                    endif;
                                    if(!empty($clockout_punch) && !empty($clockout_contract)):
                                        $earlyLeave = date('H:i', strtotime('-'.$clockout.' minutes', strtotime($clockout_contract))).':00';
                                        if($clockout_punch < $earlyLeave):
                                            $note[] = 'Leave Early';
                                        endif;
                                    elseif(empty($clockout_punch) && !empty($clockout_contract)):
                                        $note[] = 'Clock Out Not Found';
                                    endif;
                                    if(empty($attn->total_break) || $attn->total_break == 0):
                                        $note[] = 'Break Not Found';
                                    endif;
                                elseif($attn->total_work_hour > 0 && (!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && (($attn->leave_status == 1 || $attn->leave_status == 2) && !empty($attn->leave_status)) && $attn->overtime_status != 1):
                                    if(!empty($clockin_punch) && !empty($clockin_contract)):
                                        $lastIn = date('H:i', strtotime('+'.$clockin.' minutes', strtotime($clockin_contract))).':00';
                                        if($clockin_punch > $lastIn):
                                            $note[] = 'Late';
                                        endif;
                                    endif;
                                    if(!empty($clockout_punch) && !empty($clockout_contract)):
                                        $earlyLeave = date('H:i', strtotime('-'.$clockout.' minutes', strtotime($clockout_contract))).':00';
                                        if($clockout_punch < $earlyLeave):
                                            $note[] = 'Leave Early';
                                        endif;
                                    elseif(empty($clockout_punch) && !empty($clockout_contract)):
                                        $note[] = 'Clock Out Not Found';
                                    endif;
                                    if(empty($attn->total_break) || $attn->total_break == 0):
                                        $note[] = 'Break Not Found';
                                    endif;
                                    if($attn->leave_status == 1 || $attn->leave_status == 2):
                                        $note[] = 'Holiday';
                                    endif;
                                elseif(($attn->leave_status == 1 || $attn->leave_status == 2) && (empty($attn->clockin_punch) || $attn->clockin_punch == '00:00')):
                                    $note[] = 'Holiday';
                                elseif($attn->leave_status == 5):
                                    $note[] = 'Authorised Paid';
                                elseif($attn->leave_status == 4):
                                    $note[] = 'Absent';
                                elseif($attn->leave_status == 3):
                                    $note[] = 'Sick';
                                elseif($attn->overtime_status = 1):
                                    $note[] = 'Overtime';
                                endif;

                                $PDFHTML .= '<tr class="timeKeepingRow timeKeepingRow_'.($attn->leave_status > 0 ? $attn->leave_status : ($attn->overtime_status == 1 ? 'ov' : 0)).'" data-id="'.$attn->id.'">';
                                    $PDFHTML .= '<td>';
                                        $PDFHTML .= '<strong>'.date('jS F, Y, l', strtotime($attn->date)).'</strong><br/>';
                                        $PDFHTML .= '<strong>'.$attn->clockin_contract.' - '.$attn->clockout_contract.'</strong>';
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>';
                                        if($attn->total_work_hour > 0 && ($attn->leave_status == 0 || empty($attn->leave_status))):
                                            $PDFHTML .= 'Worked: '.$attn->work_hour;
                                        elseif($attn->total_work_hour > 0 && (!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && (($attn->leave_status == 1 || $attn->leave_status == 2) && !empty($attn->leave_status))):
                                            $PDFHTML .= 'Worked: '.$attn->work_hour.'<br/>';
                                            $PDFHTML .= 'Holiday: '.$attn->leaves_hour;
                                        elseif(($attn->leave_status == 1 || $attn->leave_status == 2) && (empty($attn->clockin_punch) || $attn->clockin_punch == '00:00')):
                                            $PDFHTML .= 'Holiday: '.$attn->leaves_hour;
                                        elseif($attn->leave_status == 5):
                                            $PDFHTML .= 'Authorised Paid: '.$attn->leave_hour;
                                        elseif($attn->leave_status == 4):
                                            $PDFHTML .= 'Absent';
                                        elseif($attn->leave_status == 3):
                                            $PDFHTML .= 'Sick';
                                        endif;
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>';
                                        $PDFHTML .=implode(', ', $note);
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>';
                                        if($attn->total_work_hour > 0 && ($attn->clockin_punch != '' && $attn->clockin_punch != '00:00')):
                                            $PDFHTML .= 'A: '.$attn->clockin_punch.' - '.$attn->clockout_punch.'<br/>';
                                            $PDFHTML .= 'S: '.$attn->clockin_system.' - '.$attn->clockout_system;
                                        endif;
                                    $PDFHTML .= '</td>';
                                    $PDFHTML .= '<td>';
                                        if((!empty($attn->clockin_punch) && $attn->clockin_punch != '00:00') && $attn->total_work_hour > 0):
                                            $PDFHTML .= $attn->break_time;
                                        endif;
                                    $PDFHTML .= '</td>';
                                $PDFHTML .= '</tr>';
                            endforeach;
                        $PDFHTML .= '</tbody>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</div>';
                /*PDF BODY END*/

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $PDF_title).'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
