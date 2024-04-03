<?php

namespace App\Http\Controllers\HR\Reports;

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

class AttendanceReportController extends Controller
{
    public function index(Request $request){
        $departments = Department::all();
        return view('pages.hr.portal.reports.attendance', [
            'title' => 'Attendance Report - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'departments' => $departments,
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
        ]);
    }

    public function generateReport(Request $request){
        $the_month = (isset($request->the_month) && !empty($request->the_month) ? '01-'.$request->the_month : '');
        $department_id = (isset($request->department_id) && $request->department_id > 0 ? $request->department_id : 0);
        $employee_id = (isset($request->employee_id) && !empty($request->employee_id) ? $request->employee_id : []);

        $res = [];
        if(!empty($the_month)):
            $query = Employee::has('activePatterns')->where('status', 1);
            if(!empty($employee_id)) : $query->whereIn('id', $employee_id); endif;
            if($department_id > 0):
                $query->whereHas('employment', function($q) use($department_id){
                    $q->where('department_id', $department_id);
                });
            endif;
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
                                        $html .= '<a href="'.route('hr.portal.reports.attendance.show', [$emp->id, strtotime($the_month)]).'" class="font-medium text-primary whitespace-nowrap underline">'.$emp->full_name.'</a>';
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
                                $html .= '<td>'.($sickDays ? ($sickDays == 1 ? $sickDays.' Day' : $sickDays.' Days') : '0 Days').'</td>';
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

        return response()->json(['res' => $res], 200);
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
        return view('pages.hr.portal.reports.attendance-show', [
            'title' => 'Employee Attendance Report Details - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Attendance', 'href' => route('hr.portal.reports.attendance.generate')],
                ['label' => 'Details', 'href' => 'javascript:void(0);'],
            ],
            'employee' => Employee::find($employee_id),
            'date' => date('Y-m-d', $date),
            'attendance' => $this->getEmployeeMonthlyAttendanceDetails($employee_id, $date)
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

        $html = '';
        for($i = 1; $i <= $lastDate; $i++):
            $today = date('Y-m', $date).($i < 10 ? '-0'.$i : '-'.$i);
            $D = date('D', strtotime($today));
            $N = date('N', strtotime($today));
            $todayPattern = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $patternID)->where('day_name', $D)->orderBy('id', 'desc')->get()->first();
            $isWorkingDay = (isset($todayPattern->id) && !empty($todayPattern->total) && $todayPattern->total != '00:00' ? true : false);
            $todayContractedHour = (isset($todayPattern->id) && !empty($todayPattern->total) && $todayPattern->total != '00:00' ? $this->convertStringToMinute($todayPattern->total) : 0);
            $todayAttendance = EmployeeAttendance::where('employee_id', $employee_id)->where('date', $today)->where(function($q){
                                    $q->whereNotNull('clockin_system')->where('clockin_system', '!=', '00:00');
                                })->get()->first();
            $todayWorkingHour = (isset($todayAttendance->total_work_hour) && $todayAttendance->total_work_hour > 0 ? $todayAttendance->total_work_hour : 0);
            $todayBankHoliday = HrBankHoliday::where('hr_holiday_year_id', $yearID)->where('start_date', $today)->get()->first();

            $dayClass = '';
            $dayHour = '';
            if(!$isWorkingDay && !isset($todayAttendance->id)):
                $dayClass .= ' nwRow ';
                $dayHour = 0;
            elseif($isWorkingDay && (isset($todayAttendance->id) && $todayAttendance->id > 0)):
                $dayClass .= ' wkRow expandRow ';
                $dayHour = $todayWorkingHour;
            elseif(!$isWorkingDay && (isset($todayAttendance->id) && $todayAttendance->id > 0)):
                $dayClass .= ' ovRow expandRow ';
                $dayHour = $todayWorkingHour;
            elseif($bhAutoBook && (isset($todayBankHoliday->id) && $todayBankHoliday->id > 0)):
                $dayClass .= ' bhRow expandRow ';
                $dayHour = $todayContractedHour;
            endif;

            $html .= '<tr class="'.$dayClass.'">';
                $html .= '<td class="font-medium whitespace-nowrap w-1/5">'.date('l, jS F', strtotime($today)).'</td>';
                $html .= '<td class="w-2/5">';
                    $html .= ($isWorkingDay ? $todayPattern->total : '&nbsp;');
                $html .= '</td>';
                $html .= '<td class="w-2/5">'.($isWorkingDay ? $this->calculateHourMinute($dayHour) : '').'</td>';
            $html .= '</tr>';
        endfor;

        return $html;
    }
}
