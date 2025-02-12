<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\Employment;
use App\Models\HrBankHoliday;
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
        
        return view('pages.employee.profile.time-keeper', [
            'title' => 'HR Portal - London Churchill College',
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
                            $res[$year->id]['month'][date('n', strtotime($theMonthStart))]['attendances'] =  $attendances;
                        endif;

                        $theEnd = strtotime("-1 month", $theEnd);
                    endwhile;
                endif;
            endforeach;
        endif;

        return $res;
    }

    public function generateRecored(Request $request){
        $employee_id = $request->employee_id;
        $holiday_year = $request->holiday_year;
        $the_date = (isset($request->the_date) && !empty($request->the_date) ? date('Y-m-d', strtotime($request->the_date)) : date('Y-m-d'));

        $res = $this->getEmployeeMonthlyAttendanceDetails($employee_id, $the_date, $holiday_year);

        return response()->json(['res' => $res], 200);
    }

    public function getEmployeeMonthlyAttendanceDetails($employee_id, $date, $holiday_year){
        $employee = Employee::find($employee_id);
        $monthStart = date('Y-m-d', strtotime($date));
        $monthEnd = date('Y-m-t', strtotime($date));
        $lastDate = date('t', strtotime($date));

        $clockinRow = HrCondition::where('type', 'Clock In')->where('time_frame', 3)->get()->first();
        $clockin = (isset($clockinRow->minutes) && $clockinRow->minutes > 0 ? $clockinRow->minutes : 7);
        $clockoutRow = HrCondition::where('type', 'Clock Out')->where('time_frame', 1)->get()->first();
        $clockout = (isset($clockoutRow->minutes) && $clockoutRow->minutes > 0 ? $clockoutRow->minutes : 7);

        $bhAutoBook = (isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' ? true : false);
        $hrHolidayYear = HrHolidayYear::find($holiday_year);
        $yearID = (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0 ? $hrHolidayYear->id : 0);
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();
        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        //$payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id);

        $html = '';
        $workingHoursTotal = $holidayHoursTotal = $monthTotalPay = 0;
        for($i = 1; $i <= $lastDate; $i++):
            $today = date('Y-m', strtotime($date)).($i < 10 ? '-0'.$i : '-'.$i);
            $D = date('D', strtotime($today));
            $N = date('N', strtotime($today));
            $payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id, $patternID, $today);
            
            $todayPattern = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $patternID)->where('day_name', $D)->orderBy('id', 'desc')->get()->first();
            $isWorkingDay = (isset($todayPattern->id) && !empty($todayPattern->total) && $todayPattern->total != '00:00' ? true : false);
            $todayContractedHour = (isset($todayPattern->id) && !empty($todayPattern->total) && $todayPattern->total != '00:00' ? $this->convertStringToMinute($todayPattern->total) : 0);
            $todayAttendance = EmployeeAttendance::where('employee_id', $employee_id)->where('date', $today)->where(function($q){
                                    $q->whereNotNull('clockin_system')->where('clockin_system', '!=', '00:00')->where('clockin_system', '!=', '');
                                })->get()->first();
            $isClockedIn = (isset($todayAttendance->id) && $todayAttendance->id > 0 ? true : false);
            $todayLeave = EmployeeAttendance::where('employee_id', $employee_id)->where('date', $today)->whereIn('leave_status', [1, 2, 3, 4, 5])->get()->first();
            $isLeaveDay = (isset($todayLeave->id) && $todayLeave->id > 0 ? true : false);
            
            $todayWorkingHour = (isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0 ? $todayAttendance->total_work_hour : 0);
            $todayBankHoliday = HrBankHoliday::where('hr_holiday_year_id', $yearID)->where('start_date', $today)->get()->first();
            $isBankHoliday = (isset($todayBankHoliday->id) && $todayBankHoliday->id > 0 ? true : false);

            $note = [];
            $clockin_punch = (isset($todayAttendance->clockin_punch) && !empty($todayAttendance->clockin_punch) && $todayAttendance->clockin_punch != '00:00' ? $todayAttendance->clockin_punch.':00' : '');
            $clockin_contract = (isset($todayAttendance->clockin_contract) && !empty($todayAttendance->clockin_contract) && $todayAttendance->clockin_contract != '00:00' ? $todayAttendance->clockin_contract.':00' : '');
            $clockin_system = (isset($todayAttendance->clockin_system) && !empty($todayAttendance->clockin_system) && $todayAttendance->clockin_system != '00:00' ? $todayAttendance->clockin_system.':00' : '');
            
            $clockout_punch = (isset($todayAttendance->clockout_punch) && !empty($todayAttendance->clockout_punch) && $todayAttendance->clockout_punch != '00:00' ? $todayAttendance->clockout_punch.':00' : '');
            $clockout_contract = (isset($todayAttendance->clockout_contract) && !empty($todayAttendance->clockout_contract) && $todayAttendance->clockout_contract != '00:00' ? $todayAttendance->clockout_contract.':00' : '');
            $clockout_system = (isset($todayAttendance->clockout_system) && !empty($todayAttendance->clockout_system) && $todayAttendance->clockout_system != '00:00' ? $todayAttendance->clockout_system.':00' : '');

            if((isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0) && ($todayAttendance->leave_status == 0 || empty($todayAttendance->leave_status)) && $todayAttendance->overtime_status != 1):
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
                if(empty($todayAttendance->total_break) || $todayAttendance->total_break == 0):
                    $note[] = 'Break Not Found';
                endif;
            elseif((isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0) && (!empty($todayAttendance->clockin_punch) && $todayAttendance->clockin_punch != '00:00') && ($todayAttendance->leave_status == 1 && !empty($todayAttendance->leave_status)) && $todayAttendance->overtime_status != 1):
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
                if(empty($todayAttendance->total_break) || $todayAttendance->total_break == 0):
                    $note[] = 'Break Not Found';
                endif;
                if($todayAttendance->leave_status == 1):
                    $note[] = (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note) ? ': '.$todayLeave->leaveDay->leave->note : '');
                endif;
            elseif($isLeaveDay && !$isClockedIn && $todayLeave->leave_status == 1 && (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note))):
                $note[] = $todayLeave->leaveDay->leave->note;
            elseif($isLeaveDay && !$isClockedIn && $todayLeave->leave_status == 2 && (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note))):
                $note[] = $todayLeave->leaveDay->leave->note;
            elseif($isLeaveDay && !$isClockedIn && $todayLeave->leave_status == 5 && (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note))):
                $note[] = $todayLeave->leaveDay->leave->note;
            elseif($isLeaveDay && !$isClockedIn && $todayLeave->leave_status == 4 && (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note))):
                $note[] = $todayLeave->leaveDay->leave->note;
            elseif($isLeaveDay && !$isClockedIn && $todayLeave->leave_status == 3 && (isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note))):
                $note[] = $todayLeave->leaveDay->leave->note;
            elseif(isset($todayAttendance->leave_status) && $todayAttendance->overtime_status = 1):
                $note[] = 'Overtime';
            elseif($isWorkingDay && $isBankHoliday && $bhAutoBook && (isset($todayBankHoliday->name) && !empty($todayBankHoliday->name))):
                $note[] = $todayBankHoliday->name;
            endif;

            $dayClass = '';
            $dayHour = 0;
            $holidayHour = 0;
            $dayStatus = '';
            if(!$isWorkingDay && !$isClockedIn):
                $dayClass .= ' nwRow ';
                $dayStatus = 'Not in Schedule';
                $dayHour += 0;
            elseif($isWorkingDay && $isClockedIn):
                $dayClass .= ' wkRow ';
                $dayStatus = 'Working';
                $dayHour += $todayWorkingHour;
                $workingHoursTotal += $dayHour;
            elseif(!$isWorkingDay && $isClockedIn):
                $dayClass .= ' ovRow ';
                $dayStatus = 'Overtime';
                $dayHour += $todayWorkingHour;
                $workingHoursTotal += $dayHour;
            elseif($bhAutoBook && $isBankHoliday):
                $dayClass .= ' bhRow ';
                $dayStatus = 'Bank Holiday';
                $holidayHour += $todayContractedHour;
                $holidayHoursTotal += $todayContractedHour;
            endif;

            if(isset($todayLeave->id) && $todayLeave->id > 0):
                $leaveHour = (isset($todayLeave->leaveDay->hour) && $todayLeave->leaveDay->hour > 0 ? $todayLeave->leaveDay->hour : (isset($todayLeave->leave_hour) && $todayLeave->leave_hour > 0 ? $todayLeave->leave_hour : 0));
                switch($todayLeave->leave_status):
                    case 1:
                        $dayClass .= 'hvRow';
                        $dayStatus = 'Holiday Vacation';
                        $holidayHour += $leaveHour;
                        $holidayHoursTotal += $leaveHour;
                        break;
                    case 2:
                        $dayClass .= 'mtRow';
                        $dayStatus = 'Unauthorised Absent';
                        break;
                    case 3:
                        $dayClass .= 'slRow';
                        $dayStatus = 'Sick';
                        break;
                    case 4:
                        $dayClass .= 'auRow';
                        $dayStatus = 'Authorise Unpaid';
                        break;
                    case 5:
                        $dayClass .= 'apRow';
                        $dayStatus = 'Authorise Paid';
                        $dayHour += $leaveHour; 
                        $workingHoursTotal += $leaveHour;
                        break;
                endswitch;
            endif;

            $html .= '<tr class="'.$dayClass.'">';
                $html .= '<td class="font-medium whitespace-nowrap">';
                    $html .= date('l, jS F', strtotime($today));
                    if($isWorkingDay && !$isLeaveDay && !$isBankHoliday && isset($todayPattern->start) && !empty($todayPattern->start) && isset($todayPattern->end) && !empty($todayPattern->end)):
                        $html .= '<br/>('.$todayPattern->start.' - '.$todayPattern->end.')';
                    endif;
                $html .= '</td>';
                $html .= '<td>';
                    $html .= ($isWorkingDay ? $todayPattern->total : '&nbsp;');
                $html .= '</td>';
                $html .= '<td>'.$dayStatus.'</td>';
                $html .= '<td>';
                    $html .= ($dayHour > 0 || $holidayHour > 0 ? '£'.number_format($payRate, 2) : '');
                $html .= '</td>';
                $html .= '<td>';
                    $html .= ($dayHour > 0 ? $this->calculateHourMinute($dayHour) : '');
                $html .= '</td>';
                $html .= '<td>';
                    $html .= ($holidayHour > 0 ? $this->calculateHourMinute($holidayHour) : '');
                $html .= '</td>';
                $html .= '<td>';
                    $totalHourToday = ($dayHour + $holidayHour);
                    $todaysPay = $this->calculateHoursPayment($totalHourToday, $payRate);
                    $monthTotalPay += $todaysPay;
                    $html .= ($todaysPay > 0 ? '£'.number_format($todaysPay, 2) : '');
                $html .= '</td>';
                $html .= '<td>';
                    if(isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0 && $isClockedIn):
                        $html .= 'A: '.$todayAttendance->clockin_punch.' - '.$todayAttendance->clockout_punch.'<br/>';
                        $html .= 'S: '.$todayAttendance->clockin_system.' - '.$todayAttendance->clockout_system;
                    endif;
                $html .= '</td>';
                $html .= '<td>';
                    $html .= ($isClockedIn && (isset($todayAttendance->break_time) && !empty($todayAttendance->break_time)) ? $todayAttendance->break_time : '');
                $html .= '</td>';
                $html .= '<td>';
                    $html .= implode(', ', $note);
                $html .= '</td>';
            $html .= '</tr>';
        endfor;

        $res = [];
        $res['workingHourTotal'] = ($workingHoursTotal > 0 ? $this->calculateHourMinute($workingHoursTotal) : '00:00');
        $res['holidayHourTotal'] = ($holidayHoursTotal > 0 ? $this->calculateHourMinute($holidayHoursTotal) : '00:00');
        $res['monthTotalPay'] = ($monthTotalPay > 0 ? '£'.number_format($monthTotalPay, 2) : '£0.00');
        $res['html'] = $html;
        return $res;
    }

    public function getEmployeeActivePatternsActivePayRate($employee_id, $pattern_id, $the_date){
        $the_date = date('Y-m-d', strtotime($the_date));
        $activePay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $pattern_id)
                    ->where(function($q) use($the_date){
                        $q->where('effective_from', '<=', $the_date)->where(function($sq) use($the_date){
                            $sq->whereNull('end_to')->orWhere('end_to', '>=', $the_date);
                        });
                    })->where('active', 1)->orderBy('id', 'DESC')->get()->first();
        if(isset($activePay->id) && $activePay->id > 0):
            return (isset($activePay->hourly_rate) && $activePay->hourly_rate > 0 ? $activePay->hourly_rate : 0);
        else:
            $activePay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $pattern_id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
            if(isset($activePay->id) && $activePay->id > 0):
                return (isset($activePay->hourly_rate) && $activePay->hourly_rate > 0 ? $activePay->hourly_rate : 0);
            else:
                return 0;
            endif;
        endif;

        /*$activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                                ->orderBy('id', 'DESC')->get()->first();
        if(isset($activePattern->id) && $activePattern->id > 0):
            $activePay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $activePattern->id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
            if(isset($activePay->id) && $activePay->id > 0):
                return (isset($activePay->hourly_rate) && $activePay->hourly_rate > 0 ? $activePay->hourly_rate : 0);
            else:
                return 0;
            endif;
        else:
            return 0;
        endif;*/
    }

    public function convertStringToMinute($string){
        $min = 0;
        $str = explode(':', $string);

        $min += (isset($str[0]) && $str[0] != '') ? $str[0] * 60 : 0;
        $min += (isset($str[1]) && $str[1] != '') ? $str[1] : 0;

        return $min;
    }

    function calculateHourMinute($minutes){
        $hours = (intval(trim($minutes)) / 60 >= 1) ? intval(intval(trim($minutes)) / 60) : '00';
        $mins = (intval(trim($minutes)) % 60 != 0) ? intval(trim($minutes)) % 60 : '00';
     
        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
        $hourMins .= ':';
        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
        
        return $hourMins;
    }

    public function calculateHoursPayment($minutes, $rates){
        $amount = ($minutes / 60) * $rates;
        return $amount;
    }

    public function downloadPdf($employee_id, $the_date, $holiday_year){
        $employee = Employee::find($employee_id);

        $res = $this->getEmployeeMonthlyAttendanceDetails($employee_id, $the_date, $holiday_year);

        $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
        $LetterHeader = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Header')->orderBy('id', 'DESC')->get()->first();
        $LetterFooter = LetterHeaderFooter::where('for_staff', 'Yes')->where('type', 'Footer')->orderBy('id', 'DESC')->get()->first();
        $PDF_title = $employee->full_name.' Time Recored for the Month '.date('F Y', strtotime($the_date));

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
                                .font-medium{ font-weight: bold; }
                            
                                .table {width: 100%; text-align: left; text-indent: 0; border-color: inherit; border-collapse: collapse;}
                                .table th {font-family: Tahoma, sans-serif; border-style: solid;border-color: #e5e7eb;border-bottom-width: 2px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;font-weight: bold;}
                                .table td {border-style: solid;border-color: #e5e7eb; border-bottom-width: 1px;padding-left: 1.25rem;padding-right: 1.25rem;padding-top: 0.75rem;padding-bottom: 0.75rem;}

                                .table.table-bordered th, .table.table-bordered td {border-left-width: 1px;border-right-width: 1px;border-top-width: 1px;}

                                .table.table-sm th {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}
                                .table.table-sm td {padding-left: 1rem;padding-right: 1rem;padding-top: 0.5rem;padding-bottom: 0.5rem;}

                                .attendanceDetailsTable tbody tr.hvRow td{background: rgb(0 119 181);color: #FFF;}
                                .attendanceDetailsTable tbody tr.mtRow td{background: rgb(98, 23, 8);color: #FFF;}
                                .attendanceDetailsTable tbody tr.slRow td{background: rgb(185 28 28);color: #FFF;}
                                .attendanceDetailsTable tbody tr.auRow td{background: rgb(132, 71, 255);color: #FFF;}
                                .attendanceDetailsTable tbody tr.apRow td{background: rgb(13 148 136);color: #FFF;}
                                .attendanceDetailsTable tbody tr.bhRow td{background: rgb(243, 110, 38);color: #FFF;}
                                .attendanceDetailsTable tbody tr.ovRow td{background: rgb(244, 169, 113);color: #FFF;}
                                .attendanceDetailsTable tbody tr.nwRow td{background: rgba(22, 78, 99, .05);}
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
                            $PDFHTML .= '<td class="text-right"><span class="tableTitle">'.date('F Y', strtotime($the_date)).'</span></td>';
                        $PDFHTML .= '</tr>';
                    $PDFHTML .= '</table>';
                    $PDFHTML .= '<table class="table table-sm table-bordered attendanceDetailsTable">';
                        $PDFHTML .= '<thead>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<th>Date</th>';
                                $PDFHTML .= '<th>Contracted Hour</th>';
                                $PDFHTML .= '<th>Status</th>';
                                $PDFHTML .= '<th>Rate</th>';
                                $PDFHTML .= '<th>Working Hour</th>';
                                $PDFHTML .= '<th>Holiday Hour</th>';
                                $PDFHTML .= '<th>Pay</th>';
                                $PDFHTML .= '<th>Clock In - Out</th>';
                                $PDFHTML .= '<th>Break</th>';
                                $PDFHTML .= '<th>Note</th>';
                            $PDFHTML .= '</tr>';
                        $PDFHTML .= '</thead>';
                        $PDFHTML .= '<tbody>';
                            $PDFHTML .= (isset($res['html']) && !empty($res['html']) ? $res['html'] : '');
                        $PDFHTML .= '</tbody>';
                        $PDFHTML .= '<tfoot>';
                            $PDFHTML .= '<tr>';
                                $PDFHTML .= '<th colspan="4"></th>';
                                $PDFHTML .= '<th>'.(isset($res['workingHourTotal']) ? $res['workingHourTotal'] : '00:00').'</th>';
                                $PDFHTML .= '<th>'.(isset($res['holidayHourTotal']) ? $res['holidayHourTotal'] : '00:00').'</th>';
                                $PDFHTML .= '<th>'.(isset($res['monthTotalPay']) ? $res['monthTotalPay'] : '£0.00').'</th>';
                                $PDFHTML .= '<th colspan="3"></th>';
                            $PDFHTML .= '</tr>';
                        $PDFHTML .= '</tfoot>';
                    $PDFHTML .= '</table>';
                $PDFHTML .= '</div>';
                /*PDF BODY END*/

            $PDFHTML .= '</body>';
        $PDFHTML .= '</html>';

        $fileName = str_replace(' ', '_', $PDF_title).'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true])
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
