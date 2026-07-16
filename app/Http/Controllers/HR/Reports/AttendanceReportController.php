<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkType;
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
        $monthOptions = $this->attendanceReportMonths($theDate);

        return view('pages.hr.portal.reports.attendance', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Attendance', 'href' => 'javascript:void(0);']
            ],
            'employees' => Employee::with(['employment.employeeJobTitle'])->where('status', 1)->whereHas('payment', function($q){
                                $q->where('subject_to_clockin', 'Yes');
                            })->orderBy('first_name', 'ASC')->get(),
            'workTypes' => EmployeeWorkType::orderBy('name', 'ASC')->get(),
            'theDate' => $theDate,
            'reportMonths' => $monthOptions,
            'reportHtml' => $this->generateReport($theDate)
        ]);
    }

    public function filterReport(Request $request){
        $the_date = (isset($request->the_date) && !empty($request->the_date) ? date('Y-m-d', strtotime($request->the_date)) : date('Y-m-d'));
        $employee_id = (isset($request->employee_id) && !empty($request->employee_id) ? $request->employee_id : []);
        $employee_work_type_id = (isset($request->employee_work_type_id) && $request->employee_work_type_id > 0 ? $request->employee_work_type_id : 0);

        $res = $this->generateReport($the_date, $employee_id, $employee_work_type_id);
        return response()->json(['res' => $res], 200);
    }

    public function generateReport($the_month, $employee_id = [], $employee_work_type_id = 0){
        $res = [];
        if(!empty($the_month)):
            $monthStart = date('Y-m-01', strtotime($the_month));
            $monthEnd = date('Y-m-t', strtotime($the_month));
            $attendEmployees = EmployeeAttendance::where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)->pluck('employee_id')->unique()->toArray();
            $query = Employee::with(['employment.employeeJobTitle', 'employment.employeeWorkType'])
                    ->has('activePatterns')->whereHas('payment', function($q){
                        $q->where('subject_to_clockin', 'Yes');
                    });

            if($employee_work_type_id > 0):
                $query->whereHas('employment', function($q) use($employee_work_type_id){
                    $q->where('employee_work_type_id', $employee_work_type_id);
                });
            endif;

            if(!empty($employee_id)): 
                $query->whereIn('id', $employee_id); 
            elseif(!empty($attendEmployees)):  
                $query->whereIn('id', $attendEmployees); 
            endif;
            $employees = $query->orderBy('first_name', 'ASC')->get();
            if($employees->count() > 0):
                $html = '';
                $TBHTML = '';
                $totalRows = 0;
                $totalWorkingHours = 0;
                $totalHolidayHours = 0;
                $totalWorkingPay = 0;
                $totalHolidayPay = 0;
                $totalGrossPay = 0;

                $html .= '<div class="hr-att-report-card">';
                    $html .= '<div class="hr-att-report-scroll">';
                        $html .= '<div class="hr-att-report-table">';
                            $html .= '<div class="hr-att-report-row hr-att-report-head">';
                                $html .= '<span>Name</span>';
                                $html .= '<span class="text-right">Rate</span>';
                                $html .= '<span class="text-right">Working Hr</span>';
                                $html .= '<span class="text-right">Holiday Hr</span>';
                                $html .= '<span class="text-right">Working Pay</span>';
                                $html .= '<span class="text-right">Holiday Pay</span>';
                                $html .= '<span class="text-right">Bnk / SSP</span>';
                                $html .= '<span class="text-right hr-att-gross-head">Gross Pay</span>';
                            $html .= '</div>';

                        foreach($employees as $emp):
                            if($this->employeeHasSyncdAttendance($emp->id, $the_month)):
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
                                $employeeName = $emp->first_name.' '.$emp->last_name;
                                $jobTitle = (isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name) ? $emp->employment->employeeJobTitle->name : 'Staff');
                                $worksNumber = (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? $emp->employment->works_number : (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : $emp->id));
                                $sickLabel = ($sickDays > 0 ? ($sickDays == 1 ? $sickDays.' Day' : $sickDays.' Days') : '');
                                $avatar = $this->attendanceInitials($employeeName);
                                $avatarColour = $this->attendanceAvatarColour($employeeName);
                                $holidayHourText = $this->calculateHourMinute($holiday_hours);
                                $holidayPayText = '£'.number_format($holiday_pays, 2);

                                $TBHTML .= '<a href="'.route('hr.portal.reports.attendance.show', [$emp->id, date('m-Y', strtotime($the_month))]).'" class="hr-att-report-row hr-att-report-body-row">';
                                    $TBHTML .= '<span class="hr-att-person">';
                                        $TBHTML .= '<span class="hr-att-avatar" style="--hr-att-avatar-bg: '.$avatarColour.';">'.e($avatar).'</span>';
                                        $TBHTML .= '<span class="min-w-0">';
                                            $TBHTML .= '<span class="hr-att-name">'.e($employeeName).'</span>';
                                            $TBHTML .= '<span class="hr-att-role">'.e($jobTitle).' &middot; <span>'.e($worksNumber).'</span></span>';
                                        $TBHTML .= '</span>';
                                    $TBHTML .= '</span>';
                                    $TBHTML .= '<span class="hr-att-money">£'.number_format($payRate, 2).'</span>';
                                    $TBHTML .= '<span class="hr-att-strong">'.$this->calculateHourMinute($working_hours).'</span>';
                                    $TBHTML .= '<span class="'.($holiday_hours > 0 ? 'hr-att-warn' : 'hr-att-muted').'">'.$holidayHourText.'</span>';
                                    $TBHTML .= '<span class="hr-att-money">£'.number_format($working_pays, 2).'</span>';
                                    $TBHTML .= '<span class="'.($holiday_pays > 0 ? 'hr-att-warn' : 'hr-att-muted').'">'.$holidayPayText.'</span>';
                                    $TBHTML .= '<span>'.(!empty($sickLabel) ? '<span class="hr-att-chip">'.e($sickLabel).'</span>' : '<span class="hr-att-muted">£0.00</span>').'</span>';
                                    $TBHTML .= '<span class="hr-att-gross">£'.number_format($grossPay, 2).'</span>';
                                $TBHTML .= '</a>';

                                $totalRows++;
                                $totalWorkingHours += $working_hours;
                                $totalHolidayHours += $holiday_hours;
                                $totalWorkingPay += $working_pays;
                                $totalHolidayPay += $holiday_pays;
                                $totalGrossPay += $grossPay;
                            endif;
                        endforeach;
                        if(!empty($TBHTML)):
                            $html .= $TBHTML;
                            $html .= '<div class="hr-att-report-row hr-att-report-total">';
                                $html .= '<span>Totals &middot; '.$totalRows.' staff</span>';
                                $html .= '<span></span>';
                                $html .= '<span class="hr-att-strong">'.$this->calculateHourMinute($totalWorkingHours).'</span>';
                                $html .= '<span class="hr-att-strong">'.$this->calculateHourMinute($totalHolidayHours).'</span>';
                                $html .= '<span class="hr-att-strong">£'.number_format($totalWorkingPay, 2).'</span>';
                                $html .= '<span class="hr-att-strong">£'.number_format($totalHolidayPay, 2).'</span>';
                                $html .= '<span></span>';
                                $html .= '<span class="hr-att-gross">£'.number_format($totalGrossPay, 2).'</span>';
                            $html .= '</div>';
                        else:
                            $html .= '<div class="hr-att-empty"><i data-lucide="alert-octagon" class="w-5 h-5"></i><span>Employee attendance data not found</span></div>';
                        endif;
                        $html .= '</div>';
                    $html .= '</div>';
                $html .= '</div>';

                $res['suc'] = 1;
                $res['html'] = $html;
                $res['count'] = $totalRows;
            else:
                $res['suc'] = 2;
                $res['count'] = 0;
                $res['html'] = '<div class="hr-att-empty"><i data-lucide="alert-circle" class="w-5 h-5"></i><span>Employees not found based on query parameters.</span></div>';
            endif;
        else:
            $res['suc'] = 2;
            $res['count'] = 0;
            $res['html'] = '<div class="hr-att-empty"><i data-lucide="alert-circle" class="w-5 h-5"></i><span>The date can not be empty.</span></div>';
        endif;

        return $res;
    }

    private function attendanceInitials($name)
    {
        $name = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
        $first = $parts[0] ?? 'L';
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : 'C';

        return strtoupper(substr($first, 0, 1).substr($last, 0, 1));
    }

    private function attendanceAvatarColour($seed)
    {
        $colours = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
        $hash = 0;

        foreach(str_split((string) $seed) as $char):
            $hash = (($hash * 31) + ord($char)) & 0xffffffff;
        endforeach;

        return $colours[$hash % count($colours)];
    }

    private function attendanceReportMonths($selectedDate)
    {
        $selectedRouteValue = date('m-Y', strtotime($selectedDate));
        $selectedMonthStart = date('Y-m-01', strtotime($selectedDate));

        $months = EmployeeAttendance::selectRaw("DATE_FORMAT(date, '%m-%Y') as route_value, DATE_FORMAT(date, '%Y-%m-01') as date_value, MIN(date) as first_date")
            ->whereNotNull('date')
            ->groupByRaw("DATE_FORMAT(date, '%Y-%m')")
            ->orderByRaw('MIN(date) DESC')
            ->get()
            ->map(function($month) use($selectedRouteValue) {
                $dateValue = date('Y-m-d', strtotime($month->date_value));

                return [
                    'route' => $month->route_value,
                    'date' => $dateValue,
                    'label' => date('F Y', strtotime($dateValue)),
                    'selected' => $month->route_value === $selectedRouteValue,
                ];
            })
            ->values();

        if(!$months->contains('route', $selectedRouteValue)):
            $months->push([
                'route' => $selectedRouteValue,
                'date' => $selectedMonthStart,
                'label' => date('F Y', strtotime($selectedMonthStart)),
                'selected' => true,
            ]);
        endif;

        return $months->sortByDesc('date')->values();
    }

    public function employeeHasSyncdAttendance($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)
                       ->get()->count();

        return ($attendances > 0 ? true : false);
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
                       ->where('leave_status', 5)->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['working_days' => $attendances->count(), 'working_hours' => $attendances->sum('leave_hour')];
        else:
            return ['working_days' => 0, 'working_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentMonthHolidayDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));

        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)
                       ->where('leave_status', 1)->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['holiday_days' => $attendances->count(), 'holiday_hours' => $attendances->sum('leave_hour')];
        else:
            return ['holiday_days' => 0, 'holiday_hours' => 0];
        endif;

        
        /*$employeeLeaveIds = EmployeeLeave::where('employee_id', $employee_id)->where('status', 'Approved')->pluck('id')->unique()->toArray();
        if(!empty($employeeLeaveIds)):
            $employee_leave_day = EmployeeLeaveDay::whereIn('employee_leave_id', $employeeLeaveIds)->where('leave_date', '>=', $monthStart)->where('leave_date', '<=', $monthEnd)
                        ->where('is_taken', 1)->whereHas('leave', function($q){
                            $q->where('leave_type', 1);
                        })->orderBy('leave_date', 'ASC')->get();

            if($employee_leave_day->count() > 0):
                return ['holiday_days' => $employee_leave_day->count(), 'holiday_hours' => $employee_leave_day->sum('hour')];
            else:
                return ['holiday_days' => 0, 'holiday_hours' => 0];
            endif;
        else:
            return ['holiday_days' => 0, 'holiday_hours' => 0];
        endif;*/
    }

    public function getEmployeeCurrentMonthBankHolidayDetails($employee_id, $the_month){
        $monthStart = date('Y-m-d', strtotime($the_month));
        $monthEnd = date('Y-m-t', strtotime($the_month));
        $employee = Employee::find($employee_id);
        $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $monthEnd)->where('end_date', '>=', $monthStart)->where('active', 1)
                         ->get()->first();
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();
        $effective_from = (isset($activePattern->effective_from) && !empty($activePattern->effective_from) ? date('Y-m-d', strtotime($activePattern->effective_from)) : '');
        $monthStart = (!empty($effective_from) ? ($effective_from > $monthStart ? $effective_from : $monthStart) : $monthStart);

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
            'title' => 'HR Portal - London Churchill College',
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
        $effective_from = (isset($activePattern->effective_from) && !empty($activePattern->effective_from) ? date('Y-m-d', strtotime($activePattern->effective_from)) : '');
        $workStart = (!empty($effective_from) ? ($effective_from > $monthStart ? $effective_from : $monthStart) : $monthStart);
        
        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $payRate = $this->getEmployeeActivePatternsActivePayRate($employee_id);

        $html = '';
        $nwDay = $wkDay = $ovDay = $bhDay = $hvDay = $uaDay = $skDay = $auDay = $apDay = 0;
        $workingHoursTotal = $holidayHoursTotal = $monthTotalPay = 0;
        for($i = 1; $i <= $lastDate; $i++):
            $today = date('Y-m', $date).($i < 10 ? '-0'.$i : '-'.$i);
            $D = date('D', strtotime($today));
            $N = date('N', strtotime($today));
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

            $dayClass = '';
            $dayHour = 0;
            $holidayHour = 0;
            $dayStatus = '';
            if((!$isWorkingDay && !$isClockedIn) || !$isWorkStarted):
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

            $totalHourToday = ($dayHour + $holidayHour);
            $todaysPay = $this->calculateHoursPayment($totalHourToday, $payRate);
            $monthTotalPay += $todaysPay;
            $note = '';
            if($isLeaveDay && isset($todayLeave->leaveDay->leave->note) && !empty($todayLeave->leaveDay->leave->note)):
                $note = $todayLeave->leaveDay->leave->note;
            elseif($isBankHoliday && $todayBankHoliday->name && !empty($todayBankHoliday->name) && $isWorkingDay):
                $note = $todayBankHoliday->name;
            elseif(isset($todayAttendance->note) && !empty($todayAttendance->note)):
                $note = $todayAttendance->note;
            endif;

            $displayStatus = ($dayStatus == 'Holiday Vacation' ? 'Holiday / Vacation' : ($dayStatus == 'Sick' ? 'Sick Leave' : $dayStatus));
            $html .= '<div class="ar-detail-grid ar-detail-row '.$dayClass.'" data-expandid="#attenTR_'.$i.'">';
                $html .= '<span class="ar-detail-date">'.date('l, jS F', strtotime($today)).'</span>';
                $html .= '<span>'.($isWorkStarted && $isWorkingDay ? '<span class="ar-detail-pill ar-detail-pill--contracted">'.e($todayPattern->total).'</span>' : '').'</span>';
                $html .= '<span>'.(!empty($displayStatus) ? '<span class="ar-detail-status">'.e($displayStatus).'</span>' : '').'</span>';
                $html .= '<span class="text-right">'.($dayHour > 0 || $holidayHour > 0 ? '<span class="ar-detail-pill ar-detail-pill--rate">£'.number_format($payRate, 2).'</span>' : '').'</span>';
                $html .= '<span class="text-right">'.($dayHour > 0 ? '<span class="ar-detail-pill ar-detail-pill--work">'.$this->calculateHourMinute($dayHour).'</span>' : '').'</span>';
                $html .= '<span class="text-right">'.($holidayHour > 0 ? '<span class="ar-detail-pill ar-detail-pill--holiday">'.$this->calculateHourMinute($holidayHour).'</span>' : '').'</span>';
                $html .= '<span class="text-right">'.($todaysPay > 0 ? '<span class="ar-detail-pill ar-detail-pill--pay">£'.number_format($todaysPay, 2).'</span>' : '').'</span>';
                $html .= '<span class="ar-detail-note">'.e($note).'</span>';
            $html .= '</div>';
            if(($isWorkingDay && $isClockedIn) || (!$isWorkingDay && $isClockedIn) || ($isWorkingDay && $isBankHoliday) || ($isWorkingDay && $isLeaveDay)):
                $html .= '<div class="ar-detail-expand" id="attenTR_'.$i.'">';
                    $html .= '<div class="ar-detail-expand-inner">';
                        if(($isWorkingDay && $isClockedIn) || (!$isWorkingDay && $isClockedIn)):
                            $clockInLocation = '';
                            if(isset($todayAttendance->clock_in_location) && !empty($todayAttendance->clock_in_location)):
                                if($todayAttendance->clock_in_location['suc'] == 0):
                                    $clockInLocation = 'Away '.(isset($todayAttendance->clock_in_location['ip']) && !empty($todayAttendance->clock_in_location['ip']) ? '('.$todayAttendance->clock_in_location['ip'].')' : '');
                                elseif($todayAttendance->clock_in_location['suc'] == 2):
                                    $clockInLocation = 'Punch Not Found';
                                else:
                                    $clockInLocation = $todayAttendance->clock_in_location['venue'];
                                endif;
                            endif;
                            $clockOutLocation = '';
                            if(isset($todayAttendance->clock_out_location) && !empty($todayAttendance->clock_out_location)):
                                if($todayAttendance->clock_out_location['suc'] == 0):
                                    $clockOutLocation = 'Away '.(isset($todayAttendance->clock_out_location['ip']) && !empty($todayAttendance->clock_out_location['ip']) ? '('.$todayAttendance->clock_out_location['ip'].')' : '');
                                elseif($todayAttendance->clock_out_location['suc'] == 2):
                                    $clockOutLocation = 'Punch Not Found';
                                else:
                                    $clockOutLocation = $todayAttendance->clock_out_location['venue'];
                                endif;
                            endif;
                            $html .= '<div class="ar-detail-subgrid '.($isLeaveDay ? 'mb-2' : '').'">';
                                $html .= '<span><strong>Clock In</strong><em>'.e(isset($todayAttendance->clockin_system) ? $todayAttendance->clockin_system : '').'</em>'.(!empty($clockInLocation) ? '<small>'.e($clockInLocation).'</small>' : '').'</span>';
                                $html .= '<span><strong>Clock Out</strong><em>'.e(isset($todayAttendance->clockout_system) ? $todayAttendance->clockout_system : '').'</em>'.(!empty($clockOutLocation) ? '<small>'.e($clockOutLocation).'</small>' : '').'</span>';
                                $html .= '<span><strong>Break</strong><em>'.e(isset($todayAttendance->break_time) ? $todayAttendance->break_time : '00:00').'</em></span>';
                                $html .= '<span><strong>Adjustment</strong><em>'.e(isset($todayAttendance->adjustment) ? $todayAttendance->adjustment : '+00:00').'</em></span>';
                                $html .= '<span><strong>Hour</strong><em>'.e(isset($todayAttendance->work_hour) ? $todayAttendance->work_hour : '00:00').'</em></span>';
                            $html .= '</div>';
                        endif;
                        if(($isWorkingDay && $isBankHoliday) || ($isWorkingDay && $isLeaveDay)):
                            $html .= '<div class="ar-detail-leave">';
                                if($isWorkingDay && $isBankHoliday):
                                    $html .= '<span><strong>Details</strong><em>Bank Holiday: '.e(isset($todayBankHoliday->name) ? $todayBankHoliday->name : '').'</em></span>';
                                else:
                                    $html .= '<span><strong>Details</strong><em>'.e($leaveExpandedTitle).'</em></span>';
                                endif;
                                $html .= '<span><strong>Hour</strong><em>'.($holidayHour > 0 ? $this->calculateHourMinute($holidayHour) : '00:00').'</em></span>';
                            $html .= '</div>';
                        endif;
                    $html .= '</div>';
                $html .= '</div>';
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

    public function exportExcel(Request $request, $date){
        $the_date = (!empty($date) ? date('Y-m-d', strtotime($date)) : date('Y-m-d'));
        $employee_id = (array) $request->query('employee_id', []);
        $employee_work_type_id = ($request->query('employee_work_type_id') > 0 ? $request->query('employee_work_type_id') : 0);
        $theCollection = $this->generateReportArray($the_date, $employee_id, $employee_work_type_id);

        return Excel::download(new ArrayCollectionExport($theCollection), date('F_Y', strtotime($the_date)).'_Attendance_Report.xlsx');
    }

    public function generateReportArray($the_month, $employee_id = [], $employee_work_type_id = 0){
        $theCollection = [];
        $theCollection[1][] = 'Work Number';
        $theCollection[1][] = 'NI Number';
        $theCollection[1][] = 'Name';
        $theCollection[1][] = 'Position';
        $theCollection[1][] = 'Employee/Contractor';
        $theCollection[1][] = 'Rate (£)';
        $theCollection[1][] = 'Working Hour';
        $theCollection[1][] = 'Holiday Hour';
        $theCollection[1][] = 'Working Pay (£)';
        $theCollection[1][] = 'Holiday Pay (£)';
        $theCollection[1][] = 'Sick/SSP';
        $theCollection[1][] = 'Other Pay (£)';
        $theCollection[1][] = 'Gross Pay (£)';
        $theCollection[1][] = 'Note';

        if(!empty($the_month)):
            $monthStart = date('Y-m-01', strtotime($the_month));
            $monthEnd = date('Y-m-t', strtotime($the_month));
            $attendEmployees = EmployeeAttendance::where('date', '>=', $monthStart)->where('date', '<=', $monthEnd)->pluck('employee_id')->unique()->toArray();

            $query = Employee::has('activePatterns')->whereHas('payment', function($q){
                $q->where('subject_to_clockin', 'Yes');
            });

            if($employee_work_type_id > 0):
                $query->whereHas('employment', function($q) use($employee_work_type_id){
                    $q->where('employee_work_type_id', $employee_work_type_id);
                });
            endif;

            if(!empty($employee_id)):
                $query->whereIn('id', $employee_id);
            elseif(!empty($attendEmployees)):
                $query->whereIn('id', $attendEmployees);
            endif;

            $employees = $query->orderBy('first_name', 'ASC')->get();

            $row = 2;
            if($employees->count() > 0):
                foreach($employees as $emp):
                    if($this->employeeHasSyncdAttendance($emp->id, $the_month)):
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

                        $theCollection[$row][] = (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? $emp->employment->works_number : '');
                        $theCollection[$row][] = (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : '');
                        $theCollection[$row][] = $emp->full_name;
                        $theCollection[$row][] = (isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name) ? $emp->employment->employeeJobTitle->name : '');
                        $theCollection[$row][] = (isset($emp->employment->employeeWorkType->name) && !empty($emp->employment->employeeWorkType->name) ? $emp->employment->employeeWorkType->name : '');
                        $theCollection[$row][] = number_format($payRate, 2, '.', '');
                        $theCollection[$row][] = $this->calculateHourMinute($working_hours);
                        $theCollection[$row][] = $this->calculateHourMinute($holiday_hours);
                        $theCollection[$row][] = number_format($working_pays, 2, '.', '');
                        $theCollection[$row][] = number_format($holiday_pays, 2, '.', '');
                        $theCollection[$row][] = ($sickDays > 0 ? ($sickDays == 1 ? $sickDays.' Day' : $sickDays.' Days') : '');
                        $theCollection[$row][] = '';
                        $theCollection[$row][] = number_format($grossPay, 2, '.', '');
                        $theCollection[$row][] = '';
                        
                        $row++;
                    endif;
                endforeach;
            endif;
        endif;

        return $theCollection;
    }
}
