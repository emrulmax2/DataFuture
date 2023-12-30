<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Employment;
use App\Models\HrCondition;
use App\Models\HrHolidayYear;
use Illuminate\Http\Request;

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
                    /*while($theStart < $theEnd):
                        $theMonthStart = date('Y-m', $theStart).'-01';
                        $theMonthEnd = date('Y-m-t', $theStart);

                        $attendances = EmployeeAttendance::where('employee_id', $employee_id)->whereBetween('date', [$theMonthStart, $theMonthEnd])->orderBy('date', 'ASC')->get();
                        if($attendances->count() > 0):
                            $res[$year->id]['month'][date('n', strtotime($theMonthStart))]['start_date'] = $theMonthStart;
                            $res[$year->id]['month'][date('n', strtotime($theMonthStart))]['attendances'] = $attendances;
                        endif;

                        $theStart = strtotime("+1 month", $theStart);
                    endwhile;*/
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
}
