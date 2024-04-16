<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function index($date){
        $theDate = (!empty($date) ? date('Y-m-d', strtotime('01-'.$date)) : date('Y-m-d'));
        return view('pages.hr.portal.reports.attendance', [
            'title' => 'Attendance Report - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
            'theDate' => $theDate,
            'reportHtml' => $this->generateReport($theDate)
        ]);
    }

    public function filterReport(Request $request){
        $the_date = (isset($request->the_date) && !empty($request->the_date) ? date('Y-m-d', strtotime($request->the_date)) : date('Y-m-d'));
        $employee_id = (isset($request->employee_id) && !empty($request->employee_id) ? $request->employee_id : []);

        $res = $this->generateReport($the_date, $employee_id);
        return response()->json(['res' => $res], 200);
    }

    public function generateReport($the_month, $employee_id = []){
        $res = [];
        if(!empty($the_month)):
            $query = Employee::has('activePatterns')->where('status', 1);
            if(!empty($employee_id)) : $query->whereIn('id', $employee_id); endif;
            $employees = $query->orderBy('first_name', 'ASC')->get();
            if($employees->count() > 0):
                $html = '';
                $html .= '<table class="table table-bordered">';
                    $html .= '<thead>';
                        $html .= '<tr>';
                            $html .= '<th class="whitespace-nowrap">Name</th>';
                            $html .= '<th class="whitespace-nowrap">Rate</th>';
                            $html .= '<th class="whitespace-nowrap">Working Hour</th>';
                            $html .= '<th class="whitespace-nowrap">Holiday Hour</th>';
                            $html .= '<th class="whitespace-nowrap">Working Pay</th>';
                            $html .= '<th class="whitespace-nowrap">Holiday Pay</th>';
                            $html .= '<th class="whitespace-nowrap">Sick/SSP</th>';
                            $html .= '<th class="whitespace-nowrap">Gross Pay</th>';
                        $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                        foreach($employees as $emp):
                            $payRate = $this->getEmployeeActivePatternsActivePayRate($emp->id);
                            $workDetails = $this->getEmployeeCurrentMonthWorkDetails($emp->id, $the_month);
                            $meetingAuthPaid = $this->getEmployeeCurrentMonthExtraWorkingDetails($emp->id, $the_month);
                            $holidayDetails = $this->getEmployeeCurrentMonthHolidayDetails($emp->id, $the_month);
                            $bankHolidayDetails = $this->getEmployeeCurrentMonthBankHolidayDetails($emp->id, $the_month);
                            $sickDays = $this->getEmployeeCurrentMonthSickDays($emp->id, $the_month);
                            
                            $working_days = (isset($workDetails['working_days']) ? $workDetails['working_days'] : 0); 
                            $working_days += (isset($meetingAuthPaid['working_days']) ? $meetingAuthPaid['working_days'] : 0);

                            $working_hours = (isset($workDetails['working_hours']) ? $workDetails['working_hours'] : 0); 
                            $working_hours += (isset($meetingAuthPaid['working_hours']) ? $meetingAuthPaid['working_hours'] : 0); 
                            $working_pays = $this->calculateHoursPayment($working_hours, $payRate);

                            $holiday_days = (isset($holidayDetails['holiday_days']) ? $holidayDetails['holiday_days'] : 0);
                            $holiday_days += (isset($bankHolidayDetails['bank_holiday_days']) ? $bankHolidayDetails['bank_holiday_days'] : 0);

                            $holiday_hours = (isset($holidayDetails['holiday_hours']) ? $holidayDetails['holiday_hours'] : 0);
                            $holiday_hours += (isset($bankHolidayDetails['bank_holiday_hours']) ? $bankHolidayDetails['bank_holiday_hours'] : 0);
                            $holiday_pays = $this->calculateHoursPayment($holiday_hours, $payRate);
                            $html .= '<tr>';
                                $html .= '<td>';
                                    $html .= '<div>';
                                        $html .= '<a href="'.route('hr.portal.reports.attendance.show', [$emp->id, date('m-Y', strtotime($the_month))]).'" class="font-medium text-primary whitespace-nowrap underline">'.$emp->full_name.'</a>';
                                        if(isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name)):
                                            $html .= ' - <span>'.$emp->employment->employeeJobTitle->name.'</span>';
                                        endif;
                                    $html .= '</div>';
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'; 
                                        $html .= (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : '');
                                        $html .= (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? ' - '.$emp->employment->works_number : '');
                                    $html .= '</div>';
                                $html .= '</td>';
                                $html .= '<td>';
                                    $html .= '£'.number_format($payRate, 2);
                                $html .= '</td>';
                                $html .= '<td>'.$this->calculateHourMinute($working_hours).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($holiday_hours).'</td>';
                                $html .= '<td>£'.number_format($working_pays, 2).'</td>';
                                $html .= '<td>£'.number_format($holiday_pays, 2).'</td>';
                                $html .= '<td>'.($sickDays > 0 ? ($sickDays == 1 ? $sickDays.' Day' : $sickDays.' Days') : '').'</td>';
                                $html .= '<td>£'.number_format(($working_pays + $holiday_pays), 2).'</td>';
                            $html .= '</tr>';
                        endforeach;
                    $html .= '</tbody>';
                $html .= '</table>';

                $res['suc'] = 1;
                $res['html'] = $html;
            else:
                $res['suc'] = 2;
                $res['html'] = '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Employees not found based on query parameters.</div>';
            endif;
        else:
            $res['suc'] = 2;
            $res['html'] = '<div class="alert alert-warning-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> The date can not be empty.</div>';
        endif;

        return $res;
    }

    public function getEmployeeActivePatternsActivePayRate($employee_id){
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
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
        endif;
    }

    public function getEmployeeCurrentMonthWorkDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)->where(function($q){
            $q->whereNotNull('clockin_system')->where('clockin_system', '!=', '00:00');
        })->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['working_days' => $attendances->count(), 'working_hours' => $attendances->sum('total_work_hour')];
        else:
            return ['working_days' => 0, 'working_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentMonthExtraWorkingDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)
                       ->whereIn('leave_status', [2, 5])->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['working_days' => $attendances->count(), 'working_hours' => $attendances->sum('total_work_hour')];
        else:
            return ['working_days' => 0, 'working_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentMonthHolidayDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        
        $employeeLeaveIds = EmployeeLeave::where('employee_id', $employee_id)->where('status', 'Approved')->pluck('id')->unique()->toArray();
        if(!empty($employeeLeaveIds)):
            $employee_leave_day = EmployeeLeaveDay::whereIn('employee_leave_id', $employeeLeaveIds)->where('leave_date', '>=', $monthStart)->where('leave_date', '<=', $monthEnd)
                        ->where('is_taken', 1)->orderBy('leave_date', 'ASC')->get();

            if($employee_leave_day->count() > 0):
                return ['holiday_days' => $employee_leave_day->count(), 'holiday_hours' => $employee_leave_day->sum('hour')];
            else:
                return ['holiday_days' => 0, 'holiday_hours' => 0];
            endif;
        else:
            return ['holiday_days' => 0, 'holiday_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentMonthBankHolidayDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));
        $employee = Employee::find($employee_id);
        $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $monthEnd)->where('end_date', '>=', $monthStart)->where('active', 1)
                         ->get()->first();
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();

        if(isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' && (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0) && (isset($activePattern->id) && $activePattern->id > 0)):
            $bankHoliday = HrBankHoliday::where('hr_holiday_year_id', $hrHolidayYear->id)->where('start_date', '>=', $monthStart)
                            ->where('start_date', '<=', $monthEnd)->orderBy('start_date', 'DESC')->get();
            if(!empty($bankHoliday) && $bankHoliday->count() > 0):
                $day = 0;
                $hours = 0;
                foreach($bankHoliday as $bh):
                    $start_date = (isset($bh->start_date) && !empty($bh->start_date) ? date('Y-m-d', strtotime($bh->start_date)) : '');
                    if(!empty($start_date)):
                        $dayNumber = date('N', strtotime($start_date));
                        $dayName = ucfirst(date('D', strtotime($start_date)));

                        $dayPatterm = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePattern->id)->where('day', $dayNumber)->get()->first();
                        if(isset($dayPatterm->total) && !empty($dayPatterm->total) && $dayPatterm->total != '00:00'):
                            $hours += $this->convertStringToMinute($dayPatterm->total);
                            $day += 1;
                        endif;
                    endif;
                endforeach;
                return ['bank_holiday_days' => $day, 'bank_holiday_hours' => $hours];
            else:
                return ['bank_holiday_days' => 0, 'bank_holiday_hours' => 0];
            endif;
        else:
            return ['bank_holiday_days' => 0, 'bank_holiday_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentMonthSickDays($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        
        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)
                       ->where('leave_status', 3)->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return $attendances->count();
        else:
            return 0;
        endif;
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

    public function show($employee_id, $date){
        $date = (!empty($date) ? strtotime(date('Y-m-d', strtotime('01-'.$date))) : strtotime(date('Y-m-d')));
        return view('pages.hr.portal.reports.attendance-show', [
            'title' => 'Employee Attendance Report Details - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Attendance', 'href' => route('hr.portal.reports.attendance', date('m-Y', $date))],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'employee' => Employee::find($employee_id),
            'date' => date('Y-m-d', $date),
            'attendance' => $this->getEmployeeMonthlyAttendanceDetails($employee_id, $date),
        ]);
    }

    public function getEmployeeMonthlyAttendanceDetails($employee_id, $date){
        $employee = Employee::find($employee_id);
        $monthStart = date('Y-m', $date).'-01';
        $monthEnd = date('Y-m-t', $date);
        $lastDate = date('t', $date);

        $bhAutoBook = (isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' ? true : false);
        $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $monthEnd)->where('end_date', '>=', $monthStart)->where('active', 1)
                         ->get()->first();
        $yearID = (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0 ? $hrHolidayYear->id : 0);
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();
        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id);

        $html = '';
        $nwDay = $wkDay = $ovDay = $bhDay = $hvDay = $uaDay = $skDay = $auDay = $apDay = 0;
        $workingHoursTotal = $holidayHoursTotal = $monthTotalPay = 0;
        for($i = 1; $i <= $lastDate; $i++):
            $today = date('Y-m', $date).($i < 10 ? '-0'.$i : '-'.$i);
            $D = date('D', strtotime($today));
            $N = date('N', strtotime($today));
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

            $dayClass = '';
            $dayHour = 0;
            $holidayHour = 0;
            $dayStatus = '';
            if(!$isWorkingDay && !$isClockedIn):
                $dayClass .= ' nwRow ';
                $dayStatus = 'Not in Schedule';
                $dayHour += 0;
                $nwDay += 1;
            elseif($isWorkingDay && $isClockedIn):
                $dayClass .= ' wkRow expandRow ';
                $dayStatus = 'Working';
                $dayHour += $todayWorkingHour;
                $wkDay += 1;
                $workingHoursTotal += $dayHour;
            elseif(!$isWorkingDay && $isClockedIn):
                $dayClass .= ' ovRow expandRow ';
                $dayStatus = 'Overtime';
                $dayHour += $todayWorkingHour;
                $ovDay += 1;
                $workingHoursTotal += $dayHour;
            elseif($bhAutoBook && $isBankHoliday):
                $dayClass .= ' bhRow expandRow ';
                $dayStatus = 'Bank Holiday';
                $holidayHour += $todayContractedHour;
                $holidayHoursTotal += $todayContractedHour;
                $bhDay += 1;
            endif;

            $leaveType = 0;
            $leaveExpandedTitle = '';
            if(isset($todayLeave->id) && $todayLeave->id > 0):
                $leaveType = $todayLeave->leave_status;
                $leaveHour = (isset($todayLeave->leaveDay->hour) && $todayLeave->leaveDay->hour > 0 ? $todayLeave->leaveDay->hour : (isset($todayLeave->leave_hour) && $todayLeave->leave_hour > 0 ? $todayLeave->leave_hour : 0));
                switch($todayLeave->leave_status):
                    case 1:
                        $dayClass .= 'hvRow expandRow';
                        $dayStatus = 'Holiday Vacation';
                        $holidayHour += $leaveHour;
                        $holidayHoursTotal += $leaveHour;
                        $hvDay += 1;
                        $leaveExpandedTitle = 'Holiday / Vacation found for the day.';
                        break;
                    case 2:
                        $dayClass .= 'mtRow expandRow';
                        $dayStatus = 'Unauthorised Absent';
                        $leaveExpandedTitle = 'Unauthorised Absent found for the day.';
                        $uaDay += 1;
                        break;
                    case 3:
                        $dayClass .= 'slRow expandRow';
                        $dayStatus = 'Sick';
                        $leaveExpandedTitle = 'Sick Leave found for the day.';
                        $skDay += 1;
                        break;
                    case 4:
                        $dayClass .= 'auRow expandRow';
                        $dayStatus = 'Authorise Unpaid';
                        $leaveExpandedTitle = 'Authorise Unpaid found for the day.';
                        $auDay += 1;
                        break;
                    case 5:
                        $dayClass .= 'apRow expandRow';
                        $dayStatus = 'Authorise Paid';
                        $dayHour += $leaveHour; 
                        $workingHoursTotal += $leaveHour;
                        $leaveExpandedTitle = 'Authorise Paid found for the day.';
                        $apDay += 1;
                        break;
                endswitch;
            endif;

            $html .= '<tr class="'.$dayClass.'" data-expandid="#attenTR_'.$i.'">';
                $html .= '<td class="font-medium whitespace-nowrap">'.date('l, jS F', strtotime($today)).'</td>';
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
                    if($isLeaveDay && isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note)):
                        $html .= $todayLeave->leaveDay->leave->note;
                    elseif($isBankHoliday && $todayBankHoliday->name && !empty($todayBankHoliday->name) && $isWorkingDay):
                        $html .= $todayBankHoliday->name;
                    elseif(isset($todayAttendance->note) && !empty($todayAttendance->note)):
                        $html .= $todayAttendance->note;
                    endif;
                $html .= '</td>';
            $html .= '</tr>';
            if(($isWorkingDay && $isClockedIn) || (!$isWorkingDay && $isClockedIn) || ($isWorkingDay && $isBankHoliday) || ($isWorkingDay && $isLeaveDay)):
                $html .= '<tr class="expandableRow" id="attenTR_'.$i.'">';
                    $html .= '<td colspan="8">';
                        if(($isWorkingDay && $isClockedIn) || (!$isWorkingDay && $isClockedIn)):
                            $html .= '<table class="table table-bordered table-sm '.($isLeaveDay ? 'mb-2' : '').'">';
                                $html .= '<thead>';
                                    $html .= '<th>Clock In</th>';
                                    $html .= '<th>Clock Out</th>';
                                    $html .= '<th>Break</th>';
                                    $html .= '<th>Adjustment</th>';
                                    $html .= '<th>Hour</th>';
                                $html .= '</thead>';
                                $html .= '<tbody>';
                                    $html .= '<td>';
                                        $html .= (isset($todayAttendance->clockin_system) ? $todayAttendance->clockin_system : '');
                                        if(isset($todayAttendance->clock_in_location) && !empty($todayAttendance->clock_in_location)):
                                            if($todayAttendance->clock_in_location['suc'] == 0):
                                                $html .= '<span class="text-white ml-5 bg-danger px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= 'Away '.(isset($todayAttendance->clock_in_location['ip']) && !empty($todayAttendance->clock_in_location['ip']) ? '('.$todayAttendance->clock_in_location['ip'].')' : '');
                                                $html .= '</span>';
                                            elseif($todayAttendance->clock_in_location['suc'] == 2):
                                                $html .= '<span class="text-white ml-5 bg-warning px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= 'Punch Not Found ';
                                                $html .= '</span>';
                                            else:
                                                $html .= '<span class="text-white ml-5 bg-success px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= $todayAttendance->clock_in_location['venue'];
                                                $html .= '</span>';
                                            endif;
                                        endif;
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= (isset($todayAttendance->clockout_system) ? $todayAttendance->clockout_system : '');
                                        if(isset($todayAttendance->clock_out_location) && !empty($todayAttendance->clock_out_location)):
                                            if($todayAttendance->clock_out_location['suc'] == 0):
                                                $html .= '<span class="text-white ml-5 bg-danger px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= 'Away '.(isset($todayAttendance->clock_out_location['ip']) && !empty($todayAttendance->clock_out_location['ip']) ? '('.$todayAttendance->clock_out_location['ip'].')' : '');
                                                $html .= '</span>';
                                            elseif($todayAttendance->clock_out_location['suc'] == 2):
                                                $html .= '<span class="text-white ml-5 bg-warning px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= 'Punch Not Found ';
                                                $html .= '</span>';
                                            else:
                                                $html .= '<span class="text-white ml-5 bg-success px-2 py-1 font-medium" style="padding-top: .125rem; padding-bottom: .125rem;">';
                                                    $html .= $todayAttendance->clock_out_location['venue'];
                                                $html .= '</span>';
                                            endif;
                                        endif;
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= (isset($todayAttendance->break_time) ? $todayAttendance->break_time : '00:00');
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= (isset($todayAttendance->adjustment) ? $todayAttendance->adjustment : '+00:00');
                                    $html .= '</td>';
                                    $html .= '<td>';
                                        $html .= (isset($todayAttendance->work_hour) ? $todayAttendance->work_hour : '00:00');
                                    $html .= '</td>';
                                $html .= '</tbody>';
                            $html .= '</table>';
                        endif;
                        if(($isWorkingDay && $isBankHoliday) || ($isWorkingDay && $isLeaveDay)):
                            $html .= '<table class="table table-bordered table-sm">';
                                $html .= '<thead>';
                                    $html .= '<th>Details</th>';
                                    $html .= '<th>Hour</th>';
                                $html .= '</thead>';
                                $html .= '<tbody>';
                                    if($isWorkingDay && $isBankHoliday):
                                        $html .= '<tr>';
                                            $html .= '<td>Bank Holiday: '.(isset($todayBankHoliday->name) ? $todayBankHoliday->name : '').'</td>';
                                            $html .= '<td>'.($holidayHour > 0 ? $this->calculateHourMinute($holidayHour) : '00:00').'</td>';
                                        $html .= '</tr>';
                                    else:
                                        $html .= '<tr>';
                                            $html .= '<td>'.$leaveExpandedTitle.'</td>';
                                            $html .= '<td>'.($holidayHour > 0 ? $this->calculateHourMinute($holidayHour) : '00:00').'</td>';
                                        $html .= '</tr>';
                                    endif;
                                $html .= '</tbody>';
                            $html .= '</table>';
                        endif;
                    $html .= '</td>';
                $html .= '</tr>';
            endif;
        endfor;

        $res = [];
        $res['workingHourTotal'] = ($workingHoursTotal > 0 ? $this->calculateHourMinute($workingHoursTotal) : '00:00');
        $res['holidayHourTotal'] = ($holidayHoursTotal > 0 ? $this->calculateHourMinute($holidayHoursTotal) : '00:00');
        $res['monthTotalPay'] = ($monthTotalPay > 0 ? '£'.number_format($monthTotalPay, 2) : '£0.00');
        $res['html'] = $html;
        $res['dayCount'] = [
            'nwday' => $nwDay,
            'wkday' => $wkDay,
            'ovday' => $ovDay,
            'bhday' => $bhDay,
            'hvday' => $hvDay,
            'uaday' => $uaDay,
            'skday' => $skDay,
            'auday' => $auDay,
            'apday' => $apDay
        ];

        return $res;
    }

    public function exportExcel($date){
        $the_date = (!empty($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d'));
        $theCollection = $this->generateReportArray($the_date);

        return Excel::download(new ArrayCollectionExport($theCollection), date('F_Y', strtotime($the_date)).'_Attendance_Report.xlsx');
    }

    public function generateReportArray($the_month){
        $theCollection = [];
        $theCollection[1][] = 'Name';
        $theCollection[1][] = 'Position';
        $theCollection[1][] = 'NI Number';
        $theCollection[1][] = 'Work Number';
        $theCollection[1][] = 'Rate';
        $theCollection[1][] = 'Working Hour';
        $theCollection[1][] = 'Holiday Hour';
        $theCollection[1][] = 'Working Pay';
        $theCollection[1][] = 'Holiday Pay';
        $theCollection[1][] = 'Sick/SSP';
        $theCollection[1][] = 'Other Pay';
        $theCollection[1][] = 'Gross Pay';
        $theCollection[1][] = 'Note';

        if(!empty($the_month)):
            $query = Employee::has('activePatterns')->where('status', 1);
            $employees = $query->orderBy('first_name', 'ASC')->get();

            $row = 2;
            if($employees->count() > 0):
                foreach($employees as $emp):
                    $payRate = $this->getEmployeeActivePatternsActivePayRate($emp->id);
                    $workDetails = $this->getEmployeeCurrentMonthWorkDetails($emp->id, $the_month);
                    $meetingAuthPaid = $this->getEmployeeCurrentMonthExtraWorkingDetails($emp->id, $the_month);
                    $holidayDetails = $this->getEmployeeCurrentMonthHolidayDetails($emp->id, $the_month);
                    $bankHolidayDetails = $this->getEmployeeCurrentMonthBankHolidayDetails($emp->id, $the_month);
                    $sickDays = $this->getEmployeeCurrentMonthSickDays($emp->id, $the_month);
                    
                    $working_days = (isset($workDetails['working_days']) ? $workDetails['working_days'] : 0); 
                    $working_days += (isset($meetingAuthPaid['working_days']) ? $meetingAuthPaid['working_days'] : 0);

                    $working_hours = (isset($workDetails['working_hours']) ? $workDetails['working_hours'] : 0); 
                    $working_hours += (isset($meetingAuthPaid['working_hours']) ? $meetingAuthPaid['working_hours'] : 0); 
                    $working_pays = $this->calculateHoursPayment($working_hours, $payRate);

                    $holiday_days = (isset($holidayDetails['holiday_days']) ? $holidayDetails['holiday_days'] : 0);
                    $holiday_days += (isset($bankHolidayDetails['bank_holiday_days']) ? $bankHolidayDetails['bank_holiday_days'] : 0);

                    $holiday_hours = (isset($holidayDetails['holiday_hours']) ? $holidayDetails['holiday_hours'] : 0);
                    $holiday_hours += (isset($bankHolidayDetails['bank_holiday_hours']) ? $bankHolidayDetails['bank_holiday_hours'] : 0);
                    $holiday_pays = $this->calculateHoursPayment($holiday_hours, $payRate);

                    $grossPay = $working_pays + $holiday_pays;

                    $theCollection[$row][] = $emp->full_name;
                    $theCollection[$row][] = (isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name) ? $emp->employment->employeeJobTitle->name : '');
                    $theCollection[$row][] = (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : '');
                    $theCollection[$row][] = (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? $emp->employment->works_number : '');
                    $theCollection[$row][] = '£'.number_format($payRate, 2);
                    $theCollection[$row][] = $this->calculateHourMinute($working_hours);
                    $theCollection[$row][] = $this->calculateHourMinute($holiday_hours);
                    $theCollection[$row][] = '£'.number_format($working_pays, 2);
                    $theCollection[$row][] = '£'.number_format($holiday_pays, 2);
                    $theCollection[$row][] = ($sickDays > 0 ? ($sickDays == 1 ? $sickDays.' Day' : $sickDays.' Days') : '');
                    $theCollection[$row][] = '';
                    $theCollection[$row][] = '£'.number_format($grossPay, 2);
                    $theCollection[$row][] = '';
                    
                    $row++;
                endforeach;
            endif;
        endif;

        return $theCollection;
    }
}
