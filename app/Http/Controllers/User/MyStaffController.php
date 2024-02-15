<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeHourAuthorisedBy;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Employment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MyStaffController extends Controller
{
    public function index(){
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id", $employeeId)->get()->first();

        $auth_emp_ids = EmployeeHourAuthorisedBy::where('user_id', $employee->user_id)->pluck('employee_id')->unique()->toArray();
        $auth_emp_ids = !empty($auth_emp_ids) ? $auth_emp_ids : [0];

        return view('pages.users.my-account.my-staff',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'employment' => $employment,
            'pendingLeaves' => EmployeeLeave::whereIn('employee_id', $auth_emp_ids)->where('status', 'Pending')->orderBy('id', 'ASC')->skip(0)->take(5)->get(),
            'absentToday' => $this->getAbsentEmployees(date('Y-m-d'), $auth_emp_ids),
            'holidays' => EmployeeLeaveDay::where('leave_date', date('Y-m-d'))->where('status', 'Active')->whereHas('leave', function($query) use($auth_emp_ids){
                              $query->whereIn('employee_id', $auth_emp_ids)->where('status', 'Approved')->where('leave_type', 1);
                          })->skip(0)->limit(5)->get(),
            'appraisal' => EmployeeAppraisal::whereIn('employee_id', $auth_emp_ids)->where('due_on', '<=', $expireDate)->whereNull('completed_on')
                          ->whereHas('employee', function($q){
                               $q->where('status', 1);
                          })->orderBy('due_on', 'ASC')->skip(0)->limit(5)->get()
        ]);
    }

    public function getAbsentEmployees($date = '', $auth_emp_ids = [0]){
        $theDate = (empty($date) ? date('Y-m-d') : $date);
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');
        $employees = Employee::whereIn('id', $auth_emp_ids)->has('activePatterns')->where('status', 1)->orderBy('first_name', 'ASC')->get();

        $row = 0;
        $res = [];
        foreach($employees as $employee):
            if($row > 5): 
                break; 
            endif;

            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employeeLeaveDay = EmployeeLeaveDay::where('status', 'Active')
                                    ->where('leave_date', $theDate)
                                    ->whereHas('leave', function($q) use($employee_id){
                                        $q->where('employee_id', $employee_id)->where('status', 'Approved');
                                    })
                                    ->get()->first();
                $leave_status = (isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0 && isset($employeeLeaveDay->leave->status) && $employeeLeaveDay->leave->status == 'Approved' ? true : false);

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
                        $res[$employee_id]['date'] =  date('jS M, Y', strtotime($theDate));
                        $res[$employee_id]['hourMinute'] =  $patternDay->total;
                        $res[$employee_id]['minute'] =  $this->convertStringToMinute($patternDay->total);

                        $row += 1;
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
