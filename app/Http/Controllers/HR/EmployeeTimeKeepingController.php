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
use App\Models\Option;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf;

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

        $res = $this->getEmployeeMonthlyAttendanceDetails($employee_id, $the_date, $holiday_year, true);

        return response()->json(['res' => $res], 200);
    }

    public function getEmployeeMonthlyAttendanceDetails($employee_id, $date, $holiday_year, $forWeb = false){
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
        $effective_from = (isset($activePattern->effective_from) && !empty($activePattern->effective_from) ? date('Y-m-d', strtotime($activePattern->effective_from)) : '');
        $workStart = (!empty($effective_from) ? ($effective_from > $monthStart ? $effective_from : $monthStart) : $monthStart);

        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        //$payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id);

        $html = '';
        $workingHoursTotal = $holidayHoursTotal = $monthTotalPay = 0;

        // Modern (web) rendering helpers: status pill map, note-warning icon, and an
        // off-schedule "gap" buffer used to collapse consecutive non-working days.
        $pillMap = [
            'Working'             => ['wk', 'Working'],
            'Overtime'            => ['ov', 'Overtime'],
            'Bank Holiday'        => ['bh', 'Bank holiday'],
            'Holiday Vacation'    => ['hv', 'Holiday'],
            'Unauthorised Absent' => ['mt', 'Unauthorised absent'],
            'Sick'                => ['sl', 'Sick'],
            'Authorise Unpaid'    => ['au', 'Authorised unpaid'],
            'Authorise Paid'      => ['ap', 'Authorised paid'],
        ];
        $noteWarnings = ['Late', 'Leave Early', 'Clock Out Not Found', 'Break Not Found'];
        $warnSvg = '<svg class="ep-tk-note__ico" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        $gapStart = $gapEnd = null; $gapCount = 0;

        for($i = 1; $i <= $lastDate; $i++):
            $today = date('Y-m', strtotime($date)).($i < 10 ? '-0'.$i : '-'.$i);
            $D = date('D', strtotime($today));
            $N = date('N', strtotime($today));
            $payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id, $patternID, $today);
            $isWorkStarted = $today >= $workStart ? true : false;
            
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
            $isBankHoliday = ($today >= $workStart && isset($todayBankHoliday->id) && $todayBankHoliday->id > 0 ? true : false);

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
            if((!$isWorkingDay && !$isClockedIn) || !$isWorkStarted):
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

            // Pay is computed for both web + PDF so the month totals stay identical.
            $totalHourToday = ($dayHour + $holidayHour);
            $todaysPay = $this->calculateHoursPayment($totalHourToday, $payRate);
            $monthTotalPay += $todaysPay;

            if($forWeb):
                // Collapse consecutive off-schedule days into a single slim gap row.
                if($dayStatus === 'Not in Schedule'):
                    if($gapStart === null){ $gapStart = $today; }
                    $gapEnd = $today; $gapCount++;
                    continue;
                endif;
                if($gapCount > 0):
                    $gapLabel = date('D j', strtotime($gapStart)).($gapEnd !== $gapStart ? ' &rarr; '.date('D j', strtotime($gapEnd)) : '');
                    $gapMeta  = 'Not in schedule'.($gapCount > 1 ? ' &middot; '.$gapCount.' days' : '');
                    $html .= '<tr class="ep-tk-row ep-tk-gap"><td class="ep-tk-td ep-tk-gap__cell"><span class="ep-tk-gap__label">'.$gapLabel.'</span><span class="ep-tk-gap__meta">'.$gapMeta.'</span></td></tr>';
                    $gapStart = $gapEnd = null; $gapCount = 0;
                endif;

                if(isset($pillMap[$dayStatus])):
                    $pillMod = $pillMap[$dayStatus][0]; $pillLabel = $pillMap[$dayStatus][1];
                else:
                    $pillMod = 'nt'; $pillLabel = ($dayStatus === '' ? 'Scheduled' : $dayStatus);
                endif;

                $dateCell = '<span class="ep-tk-date__day">'.date('D j', strtotime($today)).'</span>';
                if($isWorkingDay && $isWorkStarted && !empty($todayPattern->start) && !empty($todayPattern->end)):
                    $dateCell .= '<span class="ep-tk-date__sub">'.htmlspecialchars($todayPattern->start, ENT_QUOTES).' &ndash; '.htmlspecialchars($todayPattern->end, ENT_QUOTES).'</span>';
                endif;

                if($isClockedIn && isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0):
                    $clockCell  = '<span class="ep-tk-clock__main">'.htmlspecialchars($todayAttendance->clockin_punch ?? '', ENT_QUOTES).' &ndash; '.htmlspecialchars($todayAttendance->clockout_punch ?? '', ENT_QUOTES).'</span>';
                    $clockCell .= '<span class="ep-tk-clock__sub">Sched '.htmlspecialchars($todayAttendance->clockin_system ?? '', ENT_QUOTES).' &ndash; '.htmlspecialchars($todayAttendance->clockout_system ?? '', ENT_QUOTES).'</span>';
                else:
                    $clockCell = '<span class="ep-tk-muted">&ndash;</span>';
                endif;

                $breakCell      = ($isClockedIn && !empty($todayAttendance->break_time)) ? '<span>'.htmlspecialchars($todayAttendance->break_time, ENT_QUOTES).'</span>' : '<span class="ep-tk-muted">&ndash;</span>';
                $contractedCell = ($isWorkingDay && $isWorkStarted && !empty($todayPattern->total)) ? '<span>'.htmlspecialchars($todayPattern->total, ENT_QUOTES).'</span>' : '<span class="ep-tk-muted">&ndash;</span>';
                $workedCell     = ($dayHour > 0) ? '<span class="ep-tk-strong">'.$this->calculateHourMinute($dayHour).'</span>' : '<span class="ep-tk-muted">&ndash;</span>';
                $holCell        = ($holidayHour > 0) ? '<span class="ep-tk-hol">'.$this->calculateHourMinute($holidayHour).'</span>' : '<span class="ep-tk-muted">&ndash;</span>';

                if($todaysPay > 0):
                    $payCell = '<span class="ep-tk-pay__amt">&pound;'.number_format($todaysPay, 2).'</span>';
                    if($dayHour > 0 || $holidayHour > 0):
                        $payCell .= '<span class="ep-tk-pay__rate">&pound;'.number_format($payRate, 2).' / hr</span>';
                    endif;
                else:
                    $payCell = '<span class="ep-tk-muted">&ndash;</span>';
                endif;

                $noteCell = '';
                foreach($note as $n):
                    $n = trim($n, " \t\n\r\0\x0B:");
                    if($n === ''){ continue; }
                    if(in_array($n, $noteWarnings)):
                        $noteCell .= '<span class="ep-tk-note ep-tk-note--warn">'.$warnSvg.htmlspecialchars($n, ENT_QUOTES).'</span>';
                    else:
                        $noteCell .= '<span class="ep-tk-note">'.htmlspecialchars($n, ENT_QUOTES).'</span>';
                    endif;
                endforeach;
                if($noteCell === ''){ $noteCell = '<span class="ep-tk-muted">&ndash;</span>'; }

                $html .= '<tr class="ep-tk-row">';
                    $html .= '<td class="ep-tk-td ep-tk-date">'.$dateCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-status"><span class="ep-tk-pill ep-tk-pill--'.$pillMod.'"><span class="ep-tk-dot"></span>'.htmlspecialchars($pillLabel, ENT_QUOTES).'</span></td>';
                    $html .= '<td class="ep-tk-td ep-tk-clock">'.$clockCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-num">'.$breakCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-num">'.$contractedCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-num">'.$workedCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-num">'.$holCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-pay">'.$payCell.'</td>';
                    $html .= '<td class="ep-tk-td ep-tk-notes">'.$noteCell.'</td>';
                $html .= '</tr>';
            else:
                if(isset($pillMap[$dayStatus])):
                    $pillMod = $pillMap[$dayStatus][0]; $pillLabel = $pillMap[$dayStatus][1];
                else:
                    $pillMod = 'nt'; $pillLabel = ($dayStatus === '' ? 'Scheduled' : $dayStatus);
                endif;

                $dateCell = '<span class="pdf-date-main">'.date('D j', strtotime($today)).'</span>';
                if($isWorkingDay && $isWorkStarted && isset($todayPattern->start) && !empty($todayPattern->start) && isset($todayPattern->end) && !empty($todayPattern->end)):
                    $dateCell .= '<span class="pdf-date-sub">'.htmlspecialchars($todayPattern->start, ENT_QUOTES).' &ndash; '.htmlspecialchars($todayPattern->end, ENT_QUOTES).'</span>';
                endif;

                if(isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0 && $isClockedIn):
                    $clockCell  = '<span class="pdf-clock-main">'.htmlspecialchars($todayAttendance->clockin_punch ?? '', ENT_QUOTES).' &ndash; '.htmlspecialchars($todayAttendance->clockout_punch ?? '', ENT_QUOTES).'</span>';
                    $clockCell .= '<span class="pdf-clock-sub">Sched '.htmlspecialchars($todayAttendance->clockin_system ?? '', ENT_QUOTES).' &ndash; '.htmlspecialchars($todayAttendance->clockout_system ?? '', ENT_QUOTES).'</span>';
                else:
                    $clockCell = '<span class="pdf-muted">&ndash;</span>';
                endif;

                $breakCell      = ($isClockedIn && isset($todayAttendance->break_time) && !empty($todayAttendance->break_time)) ? htmlspecialchars($todayAttendance->break_time, ENT_QUOTES) : '<span class="pdf-muted">&ndash;</span>';
                $contractedCell = ($isWorkingDay && $isWorkStarted && isset($todayPattern->total) && !empty($todayPattern->total)) ? htmlspecialchars($todayPattern->total, ENT_QUOTES) : '<span class="pdf-muted">&ndash;</span>';
                $workedCell     = ($dayHour > 0) ? '<span class="pdf-strong">'.$this->calculateHourMinute($dayHour).'</span>' : '<span class="pdf-muted">&ndash;</span>';
                $holCell        = ($holidayHour > 0) ? '<span class="pdf-muted-strong">'.$this->calculateHourMinute($holidayHour).'</span>' : '<span class="pdf-muted">&ndash;</span>';

                if($todaysPay > 0):
                    $payCell = '<span class="pdf-pay-main">&pound;'.number_format($todaysPay, 2).'</span>';
                    if($dayHour > 0 || $holidayHour > 0):
                        $payCell .= '<span class="pdf-pay-sub">&pound;'.number_format($payRate, 2).' / hr</span>';
                    endif;
                else:
                    $payCell = '<span class="pdf-muted">&ndash;</span>';
                endif;

                $noteCell = '';
                foreach($note as $n):
                    $n = trim($n, " \t\n\r\0\x0B:");
                    if($n === ''){ continue; }
                    $noteClass = in_array($n, $noteWarnings) ? ' pdf-note--warn' : '';
                    $noteCell .= '<span class="pdf-note'.$noteClass.'">'.htmlspecialchars($n, ENT_QUOTES).'</span>';
                endforeach;
                if($noteCell === ''){ $noteCell = '<span class="pdf-muted">&ndash;</span>'; }

                $html .= '<tr class="pdf-tk-row '.trim($dayClass).'">';
                    $html .= '<td class="pdf-date">'.$dateCell.'</td>';
                    $html .= '<td><span class="pdf-status pdf-status--'.$pillMod.'"><span class="pdf-status-dot"></span>'.htmlspecialchars($pillLabel, ENT_QUOTES).'</span></td>';
                    $html .= '<td>'.$clockCell.'</td>';
                    $html .= '<td class="pdf-center">'.$breakCell.'</td>';
                    $html .= '<td class="pdf-center">'.$contractedCell.'</td>';
                    $html .= '<td class="pdf-center">'.$workedCell.'</td>';
                    $html .= '<td class="pdf-center">'.$holCell.'</td>';
                    $html .= '<td class="pdf-left">'.$payCell.'</td>';
                    $html .= '<td class="pdf-notes">'.$noteCell.'</td>';
                $html .= '</tr>';
            endif;
        endfor;

        if($forWeb && $gapCount > 0):
            $gapLabel = date('D j', strtotime($gapStart)).($gapEnd !== $gapStart ? ' &rarr; '.date('D j', strtotime($gapEnd)) : '');
            $gapMeta  = 'Not in schedule'.($gapCount > 1 ? ' &middot; '.$gapCount.' days' : '');
            $html .= '<tr class="ep-tk-row ep-tk-gap"><td class="ep-tk-td ep-tk-gap__cell"><span class="ep-tk-gap__label">'.$gapLabel.'</span><span class="ep-tk-gap__meta">'.$gapMeta.'</span></td></tr>';
        endif;

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
        if(!$employee):
            abort(404);
        endif;

        $the_date = date('Y-m-d', strtotime($the_date));
        $res = $this->getEmployeeMonthlyAttendanceDetails($employee_id, $the_date, $holiday_year);

        $companyReg = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_registration')->get()->first();
        $employment = Employment::where("employee_id", $employee_id)->orderBy('id', 'DESC')->get()->first();
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();
        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id, $patternID, $the_date);

        $monthTitle = date('F Y', strtotime($the_date));
        $employeeName = htmlspecialchars($employee->full_name ?? '', ENT_QUOTES);
        $employeeNo = htmlspecialchars($employment->works_number ?? $employment->punch_number ?? 'N/A', ENT_QUOTES);
        $companyRegText = htmlspecialchars($companyReg->value ?? 'Company Reg. No. 5995926, Companies House, England and Wales', ENT_QUOTES);
        $generatedDate = date('jS F, Y');
        $payRateText = number_format($payRate, 2);
        $workedTotal = $res['workingHourTotal'] ?? '00:00';
        $holidayTotal = $res['holidayHourTotal'] ?? '00:00';
        $monthTotalPay = $res['monthTotalPay'] ?? '&pound;0.00';
        $rowsHtml = $res['html'] ?? '';

        $logoPath = public_path('build/assets/images/LCC-logo.png');
        if(!file_exists($logoPath)):
            $logoPath = storage_path('app/public/company_logo.png');
        endif;
        $logoHtml = file_exists($logoPath)
            ? '<img class="brand-logo" src="'.$logoPath.'" alt="London Churchill College">'
            : '<div class="brand-fallback">LC</div><div class="brand-name">LONDON<br>CHURCHILL COLLEGE</div>';

        $PDF_title = $employee->full_name.' Time Recorded for the Month '.$monthTitle;

        $PDFHTML = <<<HTML
<!doctype html>
<html>
<head>
    <title>{$PDF_title}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 10mm; size: A4 landscape; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #ffffff;
            color: #17312f;
            font-family: "Public Sans", "DejaVu Sans", Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.3;
        }
        table { border-collapse: collapse; border-spacing: 0; }
        .report-header {
            width: 100%;
            border-bottom: 2px solid #0b2b28;
            margin-bottom: 12px;
            padding-bottom: 12px;
        }
        .report-header td { vertical-align: top; }
        .brand-cell { width: 36%; }
        .brand-logo { width: 155px; height: auto; display: block; }
        .brand-fallback {
            display: inline-block;
            width: 34px;
            height: 34px;
            line-height: 34px;
            border-radius: 9px;
            background: #e4b33c;
            color: #0a2724;
            text-align: center;
            font-family: Georgia, serif;
            font-weight: 700;
            font-size: 13px;
        }
        .brand-name {
            display: inline-block;
            margin-left: 10px;
            color: #12302d;
            font-family: Georgia, serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: 0.04em;
        }
        .title-cell { text-align: right; }
        .report-title {
            color: #12302d;
            font-size: 19px;
            font-weight: 800;
            line-height: 1.15;
            margin: 0;
        }
        .report-meta {
            margin-top: 3px;
            color: #5c7977;
            font-size: 12px;
            font-weight: 500;
        }
        .report-generated {
            margin-top: 2px;
            color: #8aa3a0;
            font-size: 11px;
        }
        .timesheet-wrap {
            border: 1px solid #eaf0ef;
            border-radius: 12px;
            overflow: hidden;
        }
        .timesheet {
            width: 100%;
            table-layout: fixed;
        }
        .timesheet thead { display: table-header-group; }
        .timesheet th {
            background: #f5f8f8;
            border-bottom: 1px solid #e7eeed;
            color: #7a938f;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.07em;
            line-height: 1.2;
            padding: 8px 7px;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
        }
        .timesheet td {
            border-bottom: 1px solid #f1f5f4;
            color: #43605d;
            font-size: 10px;
            padding: 7px;
            vertical-align: middle;
        }
        .timesheet tbody tr:nth-child(even) td { background: #fbfcfc; }
        .timesheet tbody tr.nwRow td { background: #f7f9f9; color: #8aa3a0; }
        .timesheet tbody tr.hvRow td,
        .timesheet tbody tr.apRow td { background: #f7fbfa; }
        .timesheet tbody tr.mtRow td,
        .timesheet tbody tr.slRow td { background: #fff8f7; }
        .timesheet tbody tr.auRow td { background: #fbf9ff; }
        .timesheet tbody tr.bhRow td,
        .timesheet tbody tr.ovRow td { background: #fffaf2; }
        .pdf-date-main {
            display: block;
            color: #17312f;
            font-size: 11px;
            font-weight: 700;
        }
        .pdf-date-sub,
        .pdf-clock-sub,
        .pdf-pay-sub {
            display: block;
            color: #8aa3a0;
            font-size: 9px;
            margin-top: 1px;
        }
        .pdf-clock-main {
            display: block;
            color: #17312f;
            font-size: 10.5px;
            font-weight: 600;
        }
        .pdf-status {
            color: #5c7977;
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
        }
        .pdf-status-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            margin-right: 5px;
            border-radius: 6px;
            background: currentColor;
            vertical-align: 1px;
        }
        .pdf-status--wk,
        .pdf-status--ap { color: #187a45; }
        .pdf-status--ov { color: #a35f00; }
        .pdf-status--bh { color: #b35418; }
        .pdf-status--hv { color: #1f689c; }
        .pdf-status--mt,
        .pdf-status--sl { color: #b42318; }
        .pdf-status--au { color: #6f4aa3; }
        .timesheet th.pdf-center,
        .timesheet td.pdf-center { text-align: center; }
        .timesheet th.pdf-left,
        .timesheet td.pdf-left { text-align: left; }
        .timesheet th.pdf-right,
        .timesheet td.pdf-right,
        .timesheet td.pdf-notes { text-align: right; }
        .pdf-strong,
        .pdf-pay-main {
            color: #12302d;
            font-weight: 800;
        }
        .pdf-muted,
        .pdf-muted-strong {
            color: #b8c6c4;
        }
        .pdf-muted-strong { font-weight: 700; }
        .pdf-note {
            display: inline-block;
            color: #5c7977;
            font-size: 9.5px;
            font-weight: 600;
            margin: 0 0 3px 3px;
        }
        .pdf-notes {
            text-align: right;
        }
        .pdf-note--warn {
            background: #fbf1dc;
            border: 1px solid #f0e2be;
            border-radius: 7px;
            color: #96690a;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
        }
        .timesheet tfoot td {
            background: #0b2b28;
            border-bottom: 0;
            color: #ffffff;
            padding: 10px 8px;
        }
        .total-title {
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.02em;
        }
        .total-label {
            color: #8fb0ac;
            display: block;
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .total-value {
            color: #ffffff;
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-top: 1px;
        }
        .total-pay { color: #e4b33c; font-size: 15px; }
        .report-footer {
            border-top: 1px solid #e1eae9;
            color: #8aa3a0;
            font-size: 10px;
            margin-top: 12px;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="report-header">
        <tr>
            <td class="brand-cell">{$logoHtml}</td>
            <td class="title-cell">
                <div class="report-title">Time Recorded &mdash; {$monthTitle}</div>
                <div class="report-meta">{$employeeName} &middot; Employee No. {$employeeNo} &middot; Rate &pound;{$payRateText} / hr</div>
                <div class="report-generated">Generated {$generatedDate}</div>
            </td>
        </tr>
    </table>

    <div class="timesheet-wrap">
        <table class="timesheet">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 11%;">
                <col style="width: 14%;">
                <col style="width: 7%;">
                <col style="width: 9%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 23%;">
            </colgroup>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Clock In - Out</th>
                    <th class="pdf-center">Break</th>
                    <th class="pdf-center">Contracted</th>
                    <th class="pdf-center">Worked</th>
                    <th class="pdf-center">Holiday</th>
                    <th class="pdf-left">Pay</th>
                    <th class="pdf-right">Notes</th>
                </tr>
            </thead>
            <tbody>
                {$rowsHtml}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"><span class="total-title">{$monthTitle} totals</span></td>
                    <td class="pdf-center"><span class="total-label">Worked</span><span class="total-value">{$workedTotal}</span></td>
                    <td class="pdf-center"><span class="total-label">Holiday</span><span class="total-value">{$holidayTotal}</span></td>
                    <td colspan="2" class="pdf-right"><span class="total-label">Gross Pay</span><span class="total-value total-pay">{$monthTotalPay}</span></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="report-footer">{$companyRegText}</div>
</body>
</html>
HTML;

        $fileName = str_replace(' ', '_', $PDF_title).'.pdf';
        $pdf = Pdf::loadHTML($PDFHTML)->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true])
            ->setPaper('a4', 'landscape')//portrait
            ->setWarnings(false);
        return $pdf->download($fileName);
    }
}
