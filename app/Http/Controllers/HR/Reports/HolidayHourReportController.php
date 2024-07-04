<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HolidayHourReportController extends Controller
{
    public function index(Request $request){
        $action = (isset($request->action) && !empty($request->action) ? $request->action : '');
        $from_date = (!empty($action) && isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : ''); 
        $to_date = (!empty($action) && isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : ''); 
        $search_result = '';
        if(!empty($action) && $action == 'search'):
            $search_result = $this->getSearchResult($from_date, $to_date);
        endif;

        return view('pages.hr.portal.reports.holiday-hour', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Holiday Hour Reports', 'href' => 'javascript:void(0);']
            ],
            'from_date' => $from_date,
            'to_date' => $to_date,
            'searched' => (!empty($action) ? true : false),
            'search_result' => $search_result
        ]);
    }

    public function getSearchResult($from_date, $to_date = ''){
        $to_date = !empty($to_date) ? $to_date : date('Y-m-d');
        $attendEmployees = EmployeeAttendance::where('date', '>=', $from_date)->where('date', '<=', $to_date)->pluck('employee_id')->unique()->toArray();
        $employees = Employee::whereIn('id', $attendEmployees)->orderBy('first_name', 'ASC')->get();

        $html = '';
        if($employees->count() > 0):
            $html .= '<table class="table table-bordered table-hover table-sm">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th class="whitespace-nowrap">Employee</th>';
                        $html .= '<th class="whitespace-nowrap">Worked Hour</th>';
                        $html .= '<th class="whitespace-nowrap">Holiday Hour</th>';
                        $html .= '<th class="whitespace-nowrap">Total</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    foreach($employees as $emp):
                        if($this->employeeHasSyncdAttendance($emp->id, $from_date, $to_date)):
                            $workDetails = $this->getEmployeeCurrentPeriodWorkDetails($emp->id, $from_date, $to_date);
                            $holidayDetails = $this->getEmployeeCurrentPeriodHolidayDetails($emp->id, $from_date, $to_date);
                            $bankHolidayDetails = $this->getEmployeeCurrentPeriodBankHolidayDetails($emp->id, $from_date, $to_date);

                            $working_days = (isset($workDetails['working_days']) ? $workDetails['working_days'] : 0);
                            $working_hours = (isset($workDetails['working_hours']) ? $workDetails['working_hours'] : 0); 

                            $holiday_days = (isset($holidayDetails['holiday_days']) ? $holidayDetails['holiday_days'] : 0);
                            $holiday_days += (isset($bankHolidayDetails['bank_holiday_days']) ? $bankHolidayDetails['bank_holiday_days'] : 0);

                            $holiday_hours = (isset($holidayDetails['holiday_hours']) ? $holidayDetails['holiday_hours'] : 0);
                            $holiday_hours += (isset($bankHolidayDetails['bank_holiday_hours']) ? $bankHolidayDetails['bank_holiday_hours'] : 0);

                            $html .= '<tr>';
                                $html .= '<td>'; 
                                    $html .= '<div>';
                                        $html .= '<a href="'.route('profile.employee.view', $emp->id).'" class="font-medium text-primary whitespace-nowrap underline">'.$emp->full_name.'</a>';
                                        if(isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name)):
                                            $html .= ' - <span>'.$emp->employment->employeeJobTitle->name.'</span>';
                                        endif;
                                    $html .= '</div>';
                                    $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'; 
                                        $html .= (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : '');
                                        $html .= (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? ' - '.$emp->employment->works_number : '');
                                    $html .= '</div>';
                                $html .= '</td>';
                                $html .= '<td>'.$this->calculateHourMinute($working_hours).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($holiday_hours).'</td>';
                                $html .= '<td class="font-medium">'.$this->calculateHourMinute(($working_hours + $holiday_hours)).'</td>';
                            $html .= '</tr>';
                        endif;
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
        else:
            $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Employees not available!</div>';
        endif;

        return $html;
    }

    public function exportExcel($from_date, $to_date = ''){
        $from_date = (!empty($from_date) ? date('Y-m-d', $from_date) : date('Y-m-d'));
        $to_date = (!empty($to_date) ? date('Y-m-d', $to_date) : date('Y-m-d'));

        $attendEmployees = EmployeeAttendance::where('date', '>=', $from_date)->where('date', '<=', $to_date)->pluck('employee_id')->unique()->toArray();
        $employees = Employee::whereIn('id', $attendEmployees)->orderBy('first_name', 'ASC')->get();

        $theCollection = [];
        $theCollection[1][] = 'Work Number';
        $theCollection[1][] = 'NI Number';
        $theCollection[1][] = 'Name';
        $theCollection[1][] = 'Position';
        $theCollection[1][] = 'Employee/Contractor';
        $theCollection[1][] = 'Worked Hour';
        $theCollection[1][] = 'Holiday Hour';
        $theCollection[1][] = 'Total';

        $row = 2;
        if($employees->count() > 0):
            foreach($employees as $emp):
                if($this->employeeHasSyncdAttendance($emp->id, $from_date, $to_date)):
                    $workDetails = $this->getEmployeeCurrentPeriodWorkDetails($emp->id, $from_date, $to_date);
                    $holidayDetails = $this->getEmployeeCurrentPeriodHolidayDetails($emp->id, $from_date, $to_date);
                    $bankHolidayDetails = $this->getEmployeeCurrentPeriodBankHolidayDetails($emp->id, $from_date, $to_date);

                    $working_days = (isset($workDetails['working_days']) ? $workDetails['working_days'] : 0);
                    $working_hours = (isset($workDetails['working_hours']) ? $workDetails['working_hours'] : 0); 

                    $holiday_days = (isset($holidayDetails['holiday_days']) ? $holidayDetails['holiday_days'] : 0);
                    $holiday_days += (isset($bankHolidayDetails['bank_holiday_days']) ? $bankHolidayDetails['bank_holiday_days'] : 0);

                    $holiday_hours = (isset($holidayDetails['holiday_hours']) ? $holidayDetails['holiday_hours'] : 0);
                    $holiday_hours += (isset($bankHolidayDetails['bank_holiday_hours']) ? $bankHolidayDetails['bank_holiday_hours'] : 0);

                    $theCollection[$row][] = (isset($emp->employment->works_number) && !empty($emp->employment->works_number) ? $emp->employment->works_number : '');
                    $theCollection[$row][] = (isset($emp->ni_number) && !empty($emp->ni_number) ? $emp->ni_number : '');
                    $theCollection[$row][] = $emp->full_name;
                    $theCollection[$row][] = (isset($emp->employment->employeeJobTitle->name) && !empty($emp->employment->employeeJobTitle->name) ? $emp->employment->employeeJobTitle->name : '');
                    $theCollection[$row][] = (isset($emp->employment->employeeWorkType->name) && !empty($emp->employment->employeeWorkType->name) ? $emp->employment->employeeWorkType->name : '');
                    $theCollection[$row][] = $this->calculateHourMinute($working_hours);
                    $theCollection[$row][] = $this->calculateHourMinute($holiday_hours);
                    $theCollection[$row][] = $this->calculateHourMinute(($working_hours + $holiday_hours));

                    $row += 1;
                endif;
            endforeach;
        else:
            $theCollection[$row][] = 'No Data Found!';
        endif;

        return Excel::download(new ArrayCollectionExport($theCollection), date('d_M_Y', strtotime($from_date)).'_'.date('d_M_Y', strtotime($to_date)).'_Hour_Report.xlsx');
    }

    public function employeeHasSyncdAttendance($employee_id, $from_date, $to_date){
        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $from_date)->where('date', '<=', $to_date)
                       ->get()->count();

        return ($attendances > 0 ? true : false);
    }

    public function getEmployeeCurrentPeriodWorkDetails($employee_id, $from_date, $to_date){
        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $from_date)->where('date', '<=', $to_date)->where(function($q){
            $q->whereNotNull('clockin_system')->where('clockin_system', '!=', '00:00');
        })->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['working_days' => $attendances->count(), 'working_hours' => $attendances->sum('total_work_hour')];
        else:
            return ['working_days' => 0, 'working_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentPeriodHolidayDetails($employee_id, $from_date, $to_date){
        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->where('date', '>=', $from_date)->where('date', '<=', $to_date)
                       ->where('leave_status', 1)->orderBy('date', 'ASC')->get();

        if($attendances->count() > 0):
            return ['holiday_days' => $attendances->count(), 'holiday_hours' => $attendances->sum('leave_hour')];
        else:
            return ['holiday_days' => 0, 'holiday_hours' => 0];
        endif;
    }

    public function getEmployeeCurrentPeriodBankHolidayDetails($employee_id, $from_date, $to_date){
        $employee = Employee::find($employee_id);
        $hrHolidayYear = HrHolidayYear::where(function($q) use($from_date, $to_date){
                            $q->where('start_date', '>=', $from_date)->where('start_date', '<=', $to_date);
                        })->orWhere(function($q) use($from_date, $to_date){
                            $q->where('end_date', '>=', $from_date)->where('end_date', '<=', $to_date);
                        })->orderBy('id', 'ASC')->get();
        $day = 0;
        $hours = 0;
        if($hrHolidayYear->count() > 0 && isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes'):
            foreach($hrHolidayYear as $year):
                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                 ->where(function($q) use($from_date, $to_date){
                                    $q->where('effective_from', '>=', $from_date)->where('effective_from', '<=', $to_date);
                                 })->orWhere(function($q) use($from_date, $to_date){
                                    $q->whereNull('end_to')->orWhere(function($qr) use($from_date, $to_date){
                                        $qr->where('end_to', '>=', $from_date)->where('end_to', '<=', $to_date);
                                    });
                                 })->orderBy('id', 'DESC')->get()->first();
                if(isset($activePattern->id) && $activePattern->id > 0):
                    $bankHoliday = HrBankHoliday::where('hr_holiday_year_id', $year->id)->where('start_date', '>=', $from_date)
                                   ->where('start_date', '<=', $to_date)->orderBy('start_date', 'DESC')->get();
                    if(!empty($bankHoliday) && $bankHoliday->count() > 0):
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
                    endif;
                endif;
            endforeach;
        endif;

        return ['bank_holiday_days' => $day, 'bank_holiday_hours' => $hours];
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
}
