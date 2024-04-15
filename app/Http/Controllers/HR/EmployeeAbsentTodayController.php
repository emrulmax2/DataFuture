<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeAbsentTodayController extends Controller
{
    public function index($date){
        $date = (!empty($date) ? strtotime($date) : strtotime(date('Y-m-d')));
        $theDate = date('Y-m-d', $date);
        return view('pages.hr.portal.absent-today', [
            'title' => 'HR Portal Absent Today - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Absent Today', 'href' => 'javascript:void(0);']
            ],
            'date' => $date,
            'absents' => $this->getAbsentEmployees($date),
            'yesterday' => date('Y-m-d', strtotime($theDate .' -1 day')),
            'tomorrow' => date('Y-m-d', strtotime($theDate .' +1 day'))
        ]);
    }

    public function getAbsentEmployees($date){
        $theDate = (empty($date) ? date('Y-m-d') : date('Y-m-d', $date));
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');
        $employees = Employee::where('status', 1)->orderBy('first_name', 'ASC')->get();

        $res = [];
        foreach($employees as $employee):
            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employeeLeaveDay = EmployeeLeaveDay::where('status', 'Active')
                                    ->where('leave_date', $theDate)
                                    ->where('was_absent_day', '!=', 1)
                                    ->whereHas('leave', function($q) use($employee_id){
                                        $q->where('employee_id', $employee_id)->where('status', 'Approved');
                                    })
                                    ->get()->first();
                $leave_status = (isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0 && isset($employeeLeaveDay->leave->status) && $employeeLeaveDay->leave->status == 'Approved' ? true : false);
                $absentLeave = EmployeeLeaveDay::where('status', 'Active')
                                    ->where('leave_date', $theDate)
                                    ->where('was_absent_day', 1)
                                    ->whereHas('leave', function($q) use($employee_id){
                                        $q->where('employee_id', $employee_id)->where('status', 'Approved');
                                    })
                                    ->get()->first();
                $absentLeaveType = '';
                if(isset($absentLeave->leave->leave_type) && $absentLeave->leave->leave_type > 0):
                    switch ($absentLeave->leave->leave_type):
                        case 2:
                            $absentLeaveType = 'Unauthorised Absent';
                            break;
                        case 3:
                            $absentLeaveType = 'Sick Leave';
                            break;
                        case 4:
                            $absentLeaveType = 'Authorised Unpaid';
                            break;
                        case 5:
                            $absentLeaveType = 'Authorised Paid';
                            break;
                    endswitch;
                endif;
                $absentReason = (isset($absentLeave->leave->note) && !empty($absentLeave->leave->note) ? $absentLeave->leave->note : '');
                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $theDate)
                                         ->where(function($query) use($theDate){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                                         })->get()->first();
                $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $theDayNum)->get()->first();
                $day_status = (isset($patternDay->id) && $patternDay->id > 0 ? true : false);
                if($day_status && !$leave_status):
                    $todayAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
                    if($todayAttendance->count() == 0 && $patternDay->start <= $time):
                        $res[$employee_id]['photo_url'] = $employee->photo_url;
                        $res[$employee_id]['full_name'] = $employee->full_name;
                        $res[$employee_id]['designation'] = (isset($employee->employment->employeeJobTitle->name) ? $employee->employment->employeeJobTitle->name : '');
                        $res[$employee_id]['the_date'] =  date('jS M, Y', strtotime($theDate));
                        $res[$employee_id]['hourMinute'] =  $patternDay->total;
                        $res[$employee_id]['minute'] =  $this->convertStringToMinute($patternDay->total);
                        $res[$employee_id]['start'] =  $patternDay->start;
                        $res[$employee_id]['end'] =  $patternDay->end;
                        $res[$employee_id]['day_id'] =  $patternDay->id;
                        $res[$employee_id]['pattern_id'] =  $patternDay->employee_working_pattern_id;
                        $res[$employee_id]['reason'] =  $absentReason;
                        $res[$employee_id]['reason_type'] =  $absentLeaveType;
                        $res[$employee_id]['leave_type'] =  (isset($absentLeave->leave->leave_type) && $absentLeave->leave->leave_type > 0 ? $absentLeave->leave->leave_type : 0);
                        $res[$employee_id]['leave_note'] =  (isset($absentLeave->leave->note) && $absentLeave->leave->note != '' ? $absentLeave->leave->note : '');
                        $res[$employee_id]['leave_day_id'] =  (isset($absentLeave->id) && $absentLeave->id > 0 ? $absentLeave->id : 0);
                        $res[$employee_id]['leave_day_minute'] =  (isset($absentLeave->hour) && $absentLeave->hour > 0 ? $absentLeave->hour : 0);
                        $res[$employee_id]['leave_day_hour_minute'] =  (isset($absentLeave->hour) && $absentLeave->hour > 0 ? $this->calculateHourMinute($absentLeave->hour) : '00:00');
                    endif;
                endif;
            endif;
        endforeach;

        return $res;
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
