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
use App\Models\HrHolidayYear;
use App\Models\VenueIpAddress;
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
                            })->orderBy('name', 'ASC')->get(),
            'live' => $this->getEmployeeLiveAttendanceTableHtml()
        ]);
    }

    public function ajaxLiveData(Request $request){
        $emp = (isset($request->emp) && $request->emp != '' ? $request->emp : '');
        $departement = (isset($request->departement) && $request->departement > 0 ? $request->departement : 0);
        $theDate = (isset($request->date) && !empty($request->date) ? date('Y-m-d', strtotime($request->date)) : date('Y-m-d'));

        $res = [];
        $res['the_date'] = date('jS M, Y', strtotime($theDate));
        $res['htm'] = $this->getEmployeeLiveAttendanceTableHtml($departement, $theDate, $emp);

        return response()->json(['res' => $res], 200);
    }

    public function getEmployeeLiveAttendanceTableHtml($department = 0, $theDate = '', $emp = ''){
        $theDate = (!empty($theDate) ? $theDate : date('Y-m-d'));

        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');

        $employeeHasPattern = EmployeeWorkingPattern::where('effective_from', '<=', $theDate)
                              ->where(function($query) use($theDate){
                                    $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                              })->whereHas('patterns', function($query) use($theDayNum){
                                    $query->where('day', $theDayNum);
                              })->pluck('employee_id')->unique()->toArray();

        $query = Employee::whereIn('id', $employeeHasPattern)->where('status', 1)->orderBy('first_name', 'ASC');
        if($department > 0):
            $query->whereHas('employment', function($q) use($department){
                $q->where('department_id', $department);
            });
        endif;
        if(!empty($emp)):
            $query->where(function($q) use($emp){
                $q->where('first_name', 'LIKE', '%'.$emp.'%')->orWhere('last_name', 'LIKE', '%'.$emp.'%');
            });
        endif;
        $Query= $query->get();

        $data = [];
        $html = '';
        if(!empty($Query) && $Query->count() > 0):
            $i = 1;
            foreach($Query as $list):
                $day = $this->getTheDayStatusWithSchedule($list->id, $theDate);
                $department = (isset($list->employment->department->name) ? $list->employment->department->name : '');
                $job_title = (isset($list->employment->employeeJobTitle->name) ? $list->employment->employeeJobTitle->name : '');

                $html .= '<tr>';

                    $html .= '<td class="w-2/5">';
                        $html .= '<a href="javascript:void(0);" class="block">';
                            $html .= '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                $html .= '<img alt="'.$list->full_name.'" class="rounded-full shadow" src="'.$list->photo_url.'">';
                            $html .= '</div>';
                            $html .= '<div class="inline-block relative" style="top: -5px;">';
                                $html .= '<div class="font-medium whitespace-nowrap">'.$list->full_name.'</div>';
                                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap">'.$job_title.(!empty($department) ? ' - '.$department : '').'</div>';
                            $html .= '</div>';
                        $html .= '</a>';
                    $html .= '</td>';

                    $html .= '<td class="text-center w-1/5">';
                        $html .= (isset($list->employment->office_telephone) && !empty($list->employment->office_telephone) ? '<span class="bg-primary text-white font-medium px-3 py-1 inline-flex justify-center items-center rounded text-lg mb-2"><i data-lucide="phone" class="w-4 h-4 mr-2"></i>'.$list->employment->office_telephone.'</span>' : '');
                        $html .= (isset($day['schedule']) && !empty($day['schedule']) ? '<div class="text-slate-500 whitespace-nowrap">'.$day['schedule'].'</div>' : '');
                    $html .= '</td>';

                    $html .= '<td class="text-left w-2/5">';
                        $html .= '<div>';
                            $html .= (isset($day['label']) && !empty($day['label']) ? '<span class="font-medium uppercase '.(isset($day['class']) ? $day['class'] : '').'">'.$day['label'].'</span>' : '');
                            $html .= (isset($day['where']) && !empty($day['where']) ? ' - <span class="text-slate-500">'.$day['where'].'</span>' : '');
                        $html .= '</div>';
                        $html .= '<div>';
                            $html .= (isset($day['since']) && !empty($day['since']) ? '<span>'.$day['since'].'</span>' : '');
                        $html .= '</div>';
                    $html .= '</td>';

                $html .= '</tr>';

                $i++;
            endforeach;
        else:
            $html .= '<tr><td colspan="3" class="text-center">Attendance data not found for the day.</td></tr>';
        endif;

        return $html;
    }

    public function getTheDayStatusWithSchedule($employee_id, $theDate){
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $currentTime = date('H:i');

        $dayStatus = false;
        $schedule = '---';
        $where = '';
        $statusLabel = '';
        $statusClass = '';
        $since = '';

        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                        ->where('effective_from', '<=', $theDate)
                        ->where(function($query) use($theDate){
                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                        })->get()->first();
        $workingPatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $workingPatternId)->where('day', $theDayNum)->get()->first();
        if(isset($patternDay->id) && $patternDay->id > 0):
            $schedule = $patternDay->start.' - '.$patternDay->end;
            $dayStatus = true;
        endif;

        $todaysAttendances = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
        $todaysLastAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
        $last_attendance_type = (isset($todaysLastAttendance->attendance_type) && $todaysLastAttendance->attendance_type > 0 ? $todaysLastAttendance->attendance_type : 0);       
        $time               = (isset($todaysLastAttendance->time) && $todaysLastAttendance->time != '') ? date('h:i:s a', strtotime($todaysLastAttendance->time)) : '';
        
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
                    $statusClass = 'text-success';
                    break;
                case 2:
                    $statusLabel = 'Unauthorised Absent';
                    $statusClass = 'text-success';
                    break;
                case 3: 
                    $statusLabel = 'Sick';
                    $statusClass = 'text-success';
                    break;
                case 4:
                    $statusLabel = 'Authorised Unpaid';
                    $statusClass = 'text-success';
                    break;
                case 5:
                    $statusLabel = 'Authorised Paid';
                    $statusClass = 'text-success';
                    break;
            }
        else:
            if(isset($todaysLastAttendance->id) && $todaysLastAttendance->id > 0 && $last_attendance_type > 0):
                $todayClockIn = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->where('attendance_type', 1)->orderBy('id', 'DESC')->get()->first();
                $todayClockInIp = (isset($todayClockIn->ip) && !empty($todayClockIn->ip) ? trim($todayClockIn->ip) : '');
                if(!empty($todayClockInIp)):
                    $venueIpAddress = VenueIpAddress::where('ip', $todayClockInIp)->orderBy('id', 'desc')->get()->first();
                    if(isset($venueIpAddress->venue->name) && !empty($venueIpAddress->venue->name)):
                        $where = $venueIpAddress->venue->name;
                    else:
                        $where = 'Away';
                    endif;
                else:
                    $where = 'Away';
                endif;
                if($last_attendance_type == 2):
                    $todayBreak = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->where('attendance_type', 2)->orderBy('id', 'DESC')->get()->first();
                    $statusLabel = 'Break';
                    $statusClass = 'text-pending';
                    $since = (isset($todayBreak->time) && !empty($todayBreak->time) ? 'Since: '.date('H:i A', strtotime($todayBreak->time)) : '');
                elseif($last_attendance_type == 4):
                    $todayClockOut = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->where('attendance_type', 4)->orderBy('id', 'DESC')->get()->first();
                    $statusLabel = 'Clock Out';
                    $statusClass = 'text-danger';
                    $since = (isset($todayClockIn->time) && !empty($todayClockIn->time) ? date('H:i A', strtotime($todayClockIn->time)) : '');
                    $since .= (isset($todayClockOut->time) && !empty($todayClockOut->time) ? ' - '.date('H:i A', strtotime($todayClockOut->time)) : '');
                else:
                    $statusLabel = 'Working';
                    $statusClass = 'text-success';
                    $since = (isset($todayClockIn->time) && !empty($todayClockIn->time) ? 'Since: '.date('H:i A', strtotime($todayClockIn->time)) : '');
                endif;
            else:
                if(isset($patternDay->start) && !empty($patternDay->start) && $patternDay->start <= $currentTime):
                    $statusLabel = 'Awaiting Clock In / Absent';
                    $statusClass = 'text-danger';
                else:
                    $statusLabel = 'No Clock-In';
                    $statusClass = 'text-danger';
                endif;
            endif;
        endif;

        $res = [];
        $res['overtime_status'] = (!$dayStatus && $todaysAttendances->count > 0 ? 1 : 0);
        $res['schedule'] = $schedule;
        $res['where'] = $where;
        $res['label'] = $statusLabel;
        $res['class'] = $statusClass;
        $res['since'] = $since;

        return $res;
    }


    public function add(){
        return view('pages.hr.portal.live-add', [
            'title' => 'HR Portal Add Live Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Live', 'href' => route('hr.portal.live.attedance')],
                ['label' => 'Add Attendance', 'href' => 'javascript:void(0);']
            ],
            'employee' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function getDayAttendanceData(Request $request){
        $employee_id = $request->employee_id;
        $theDate = (isset($request->theDate) && !empty($request->theDate) ? date('Y-m-d', strtotime($request->theDate)) : date('Y-m-d'));
        $D = date('D', strtotime($theDate));
        $N = date('N', strtotime($theDate));

        $employee = Employee::find($employee_id);
        $bhAutoBook = (isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' ? true : false);
        $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $theDate)->where('end_date', '>=', $theDate)->where('active', 1)
                         ->get()->first();
        $yearID = (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0 ? $hrHolidayYear->id : 0);
        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)
                         ->orderBy('id', 'DESC')->get()->first();
        $patternID = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
        $todayPattern = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $patternID)->where('day_name', $D)->orderBy('id', 'desc')->get()->first();
        $isWorkingDay = (isset($todayPattern->id) && !empty($todayPattern->total) && $todayPattern->total != '00:00' ? true : false);
        $patternStart = (isset($todayPattern->start) && !empty($todayPattern->start) ? $todayPattern->start : 'NWD');
        $patternEnd = (isset($todayPattern->end) && !empty($todayPattern->end) ? $todayPattern->end : 'NWD');

        $todaysClockIn = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('attendance_type', 1)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
        $todaysClockInTime = (isset($todaysClockIn->time) && !empty($todaysClockIn->time) ? date('H:i', strtotime($todaysClockIn->time)) : '');
        $todaysClockOut = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('attendance_type', 4)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
        $todaysClockOutTime = (isset($todaysClockOut->time) && !empty($todaysClockOut->time) ? date('H:i', strtotime($todaysClockOut->time)) : '');

        $todaysBreak = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('attendance_type', 2)->where('date', $theDate)->get();
        $todaysReturn = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('attendance_type', 3)->where('date', $theDate)->get();

        $todaysLastBreak = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('attendance_type', 2)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
        $lastBreak = (isset($todaysLastBreak->time) && !empty($todaysLastBreak->time) && ($todaysBreak->count() > $todaysReturn->count()) ? date('H:i', strtotime($todaysLastBreak->time)) : '');

        $html = '';
        $html .= '<tr class="employeeAttendanceRow" id="employeeAttendanceRow_'.$employee_id.'">';
            $html .= '<td>';
                $html .= '<div class="font-medium text-primary whitespace-nowrap">'.$employee->full_name.'</div>';
                $html .= '<div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">'; 
                    $html .= (isset($employee->employment->employeeJobTitle->name) && !empty($employee->employment->employeeJobTitle->name) ? $employee->employment->employeeJobTitle->name : '');
                $html .= '</div>';
            $html .= '</td>';
            $html .= '<td>';
                $html .= '<table class="table table-sm">';
                    $html .= '<tr>';
                        $html .= '<td class="font-medium text-primary whitespace-nowrap bg-slate-200 w-30">'.$patternStart.'</td>';
                        $html .= '<td class="attendanceFormCtrlTd">';
                            $html .= '<input type="text" name="attendance['.$employee_id.'][clockin]" value="'.$todaysClockInTime.'" placeholder="00:00" class="form-control clockMask"/>';
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</table>';
            $html .= '</td>';
            $html .= '<td>';
                $html .= '<table class="table table-sm">';
                    $html .= '<tr>';
                        $html .= '<td class="attendanceFormCtrlTd">';
                            $html .= '<input type="text" name="attendance['.$employee_id.']['.(empty($lastBreak) ? 'break' : 'exist_break').']" value="'.$lastBreak.'" placeholder="00:00" class="form-control clockMask"/>';
                        $html .= '</td>';
                        $html .= '<td class="attendanceFormCtrlTd">';
                            $html .= '<input type="text" name="attendance['.$employee_id.'][return]" value="" placeholder="00:00" class="form-control clockMask"/>';
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</table>';
            $html .= '</td>';
            $html .= '<td>';
                $html .= '<table class="table table-sm">';
                    $html .= '<tr>';
                        $html .= '<td class="font-medium text-primary whitespace-nowrap bg-slate-200 w-30">'.$patternEnd.'</td>';
                        $html .= '<td class="attendanceFormCtrlTd">';
                            $html .= '<input type="text" name="attendance['.$employee_id.'][clockout]" value="'.$todaysClockOutTime.'" placeholder="00:00" class="form-control clockMask"/>';
                        $html .= '</td>';
                    $html .= '</tr>';
                $html .= '</table>';
            $html .= '</td>';
        $html .= '</tr>';

        return response()->json(['res' => $html], 200);
    }


    public function feeAttendanceLive(Request $request){
        $theDate = (isset($request->the_date) ? date('Y-m-d', strtotime($request->the_date)) : '');
        $employees = (isset($request->employees) && !empty($request->employees) ? $request->employees : []);
        $attendance = (isset($request->attendance) && !empty($request->attendance) ? $request->attendance : []);
        $currentEmployee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $currentEmployeeId = (isset($currentEmployee->id) ? $currentEmployee->id : auth()->user()->id);

        if(!empty($employees) && !empty($attendance) && !empty($theDate)):
            foreach($employees as $emp):
                $empAtten = (isset($attendance[$emp]) && !empty($attendance[$emp]) ? $attendance[$emp] : []);
                if(!empty($empAtten)):
                    $todaysClockIn = EmployeeAttendanceLive::where('employee_id', $emp)->where('attendance_type', 1)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
                    $todaysClockOut = EmployeeAttendanceLive::where('employee_id', $emp)->where('attendance_type', 4)->where('date', $theDate)->orderBy('id', 'DESC')->get()->first();
                    
                    if(isset($empAtten['clockin']) && !empty($empAtten['clockin'])):
                        $data = [];
                        $data['employee_id'] = $emp;
                        $data['attendance_type'] = 1;
                        $data['date'] = $theDate;
                        $data['time'] = $empAtten['clockin'].':00';
                        if(isset($todaysClockIn->id) && ($todaysClockIn->id > 0)):
                            $data['updated_by'] = $currentEmployeeId;
                            $data['updated_at'] = date('Y-m-d H:i:s');

                            EmployeeAttendanceLive::where('id', $todaysClockIn->id)->update($data);
                        else:
                            $data['ip'] = $request->getClientIp();
                            $data['created_by'] = $currentEmployeeId;

                            EmployeeAttendanceLive::create($data);
                        endif;
                    endif;
                    if(isset($empAtten['break']) && !empty($empAtten['break'])):
                        $data = [];
                        $data['employee_id'] = $emp;
                        $data['attendance_type'] = 2;
                        $data['date'] = $theDate;
                        $data['time'] = $empAtten['break'].':00';
                        $data['ip'] = $request->getClientIp();
                        $data['created_by'] = $currentEmployeeId;

                        EmployeeAttendanceLive::create($data);
                    endif;
                    if(isset($empAtten['return']) && !empty($empAtten['return'])):
                        $data = [];
                        $data['employee_id'] = $emp;
                        $data['attendance_type'] = 3;
                        $data['date'] = $theDate;
                        $data['time'] = $empAtten['return'].':00';
                        $data['ip'] = $request->getClientIp();
                        $data['created_by'] = $currentEmployeeId;

                        EmployeeAttendanceLive::create($data);
                    endif;

                    if(isset($empAtten['clockout']) && !empty($empAtten['clockout'])):
                        $data = [];
                        $data['employee_id'] = $emp;
                        $data['attendance_type'] = 4;
                        $data['date'] = $theDate;
                        $data['time'] = $empAtten['clockout'].':00';
                        if(isset($todaysClockOut->id) && ($todaysClockOut->id > 0)):
                            $data['updated_by'] = $currentEmployeeId;
                            $data['updated_at'] = date('Y-m-d H:i:s');

                            EmployeeAttendanceLive::where('id', $todaysClockOut->id)->update($data);
                        else:
                            $data['ip'] = $request->getClientIp();
                            $data['created_by'] = $currentEmployeeId;

                            EmployeeAttendanceLive::create($data);
                        endif;
                    endif;
                endif;
            endforeach;
            return response()->json(['res' => 1], 200);
        else:
            return response()->json(['res' => 2], 200);
        endif;
    }
}
