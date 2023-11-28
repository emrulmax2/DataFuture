<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Employment;
use Illuminate\Http\Request;

class EmployeeAttendanceLiveController extends Controller
{
    public function index(){
        return view('pages.hr.portal.live', [
            'title' => 'HR Portal Live Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Live Attendance', 'href' => 'javascript:void(0);']
            ],
            'departments' => Department::whereHas('employment', function($q){
                $q->whereHas('employee', function($sq){
                    $sq->where('status', 1);
                });
            })->orderBy('name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $department = (isset($request->department) && $request->department > 0 ? $request->department : 0);

        $theDate = date('Y-m-d');
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');

        $active_employee_ids = Employment::where('department_id', $department)->whereHas('employee', function($q){
            $q->where('status', 1);
        })->pluck('employee_id')->toArray();

        $employeeHasPattern = EmployeeWorkingPattern::whereIn('employee_id', $active_employee_ids)
                              ->where('effective_from', '<=', $theDate)
                              ->where(function($query) use($theDate){
                                    $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                              })->whereHas('patterns', function($query) use($theDayNum){
                                    $query->where('day', $theDayNum);
                              })->pluck('employee_id')->toArray();

        $query = Employee::whereIn('id', $employeeHasPattern)->where('status', 1)->orderBy('first_name', 'ASC');

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $dayDetails = $this->getTheDayStatusWithSchedule($list->id, $theDate);
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->first_name.' '.$list->last_name,
                    'designation' => isset($list->employment->employeeJobTitle->name) ? $list->employment->employeeJobTitle->name : '',
                    'photo_url' => $list->photo_url,
                    'works_number' => isset($list->employment->works_number) ? $list->employment->works_number : '',
                    'where' => (isset($dayDetails['where']) && !empty($dayDetails['where']) ? $dayDetails['where'] : ''),
                    'schedule' => (isset($dayDetails['schedule']) && !empty($dayDetails['schedule']) ? $dayDetails['schedule'] : ''),
                    'day_label' => (isset($dayDetails['label']) && !empty($dayDetails['label']) ? $dayDetails['label'] : ''),
                    'day_suffix' => (isset($dayDetails['suffix']) && !empty($dayDetails['suffix']) ? $dayDetails['suffix'] : ''),
                    'day_class' => (isset($dayDetails['class']) && !empty($dayDetails['class']) ? $dayDetails['class'] : ''),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function getTheDayStatusWithSchedule($employee_id, $theDate){
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $currentTime = date('H:i');

        $dayStatus = false;
        $schedule = '---';
        $where = '';
        $statusLabel = '';
        $statusLabelSuffix = '';
        $statusClass = '';

        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                        ->where('effective_from', '<=', $theDate)
                        ->where(function($query) use($theDate){
                        $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                        })->get()->first();
        $workingPatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $workingPatternId)->where('day', $theDayNum)->get()->first();
        if(isset($patternDay->id) && $patternDay->id > 0):
            $start = $patternDay->start;
            $end = $patternDay->end;
            $schedule = $start.' - '.$end;
            $dayStatus = true;
        endif;

        $todaysAttendances = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
        $todaysLastAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
        $last_attendance_type = (isset($todaysLastAttendance->attendance_type) && $todaysLastAttendance->attendance_type > 0 ? $todaysLastAttendance->attendance_type : 0);
        $atten_date = $theDate;
        $time               = (isset($todaysLastAttendance->time) && $todaysLastAttendance->time != '') ? date('h:i:s a', strtotime($todaysLastAttendance->time)) : '';
        $machine_name       = (isset($todaysLastAttendance->machine->name) && $todaysLastAttendance->machine->name != '') ? $todaysLastAttendance->machine->name : '';
    
        $employeeLeaveDay = EmployeeLeaveDay::where('status', 'Active')
                            ->where('leave_date', $theDate)
                            ->whereHas('leave', function($q) use($employee_id){
                                $q->where('employee_id', $employee_id)->where('status', 'Approved');
                            })->get()->first();
        if(isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0):
            $leave_type = (isset($employeeLeaveDay->leave->leave_type) && $employeeLeaveDay->leave->leave_type > 0 ? $employeeLeaveDay->leave->leave_type : 0);
            switch ($leave_type){
                case 1:
                    $statusLabel = 'Holiday / Vacation';
                    $statusClass = 'holidayVacationBG';
                    break;
                case 2:
                    $statusLabel = 'Meeting / Training';
                    $statusClass = 'meetingTrainingBG';
                    break;
                case 3: 
                    $statusLabel = 'Sick';
                    $statusClass = 'sickLeaveBG';
                    break;
                case 4:
                    $statusLabel = 'Authorised Unpaid';
                    $statusClass = 'authoriseUnpaidBG';
                    break;
                case 5:
                    $statusLabel = 'Authorised Paid';
                    $statusClass = 'authorisedPaidBG';
                    break;
            }
        else:
            if(!empty($todaysLastAttendance) && $todaysLastAttendance->count() > 0 && $last_attendance_type > 0):
                if($last_attendance_type == 2):
                    $statusLabel = 'Break';
                    $statusLabelSuffix = '('.$time.')';
                    $where = $machine_name;
                    $statusClass = 'bg-pending';
                elseif($last_attendance_type == 4):
                    $statusLabel = 'Out';
                    $where = $machine_name;
                    $statusClass = 'bg-warning';
                else:
                    $statusLabel = 'In';
                    $where = $machine_name;
                    $statusClass = 'bg-success';
                endif;
            else:
                if($patternDay->start <= $currentTime):
                    $statusLabel = 'Absent';
                    $statusClass = 'bg-danger';
                else:
                    $statusLabel = 'Not Yet Clocked In';
                    $statusClass = 'bg-pending';
                endif;
            endif;
        endif;

        $res = [];
        $res['day_status'] = ($dayStatus || $todaysAttendances->count > 0 ? true : false);
        $res['schedule'] = $schedule;
        $res['where'] = $where;
        $res['label'] = $statusLabel;
        $res['suffix'] = $statusLabelSuffix;
        $res['class'] = $statusClass;

        return $res;
    }
}
