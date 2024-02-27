<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrCondition;
use Illuminate\Http\Request;

class EmployeeAttendanceController extends Controller
{
    public function index(){
        return view('pages.hr.attendance.index', [
            'title' => 'HR Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => 'javascript:void(0);']
            ]
        ]);
    }

    public function list(Request $request){
        $attendanceDate = (isset($request->attendanceDate) && !empty($request->attendanceDate) ? date('Y-m-d', strtotime('01-'.$request->attendanceDate)) : date('Y-m-d'));
        
        $total_rows = date('t', strtotime($attendanceDate));
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 31));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $data = array();

        $month = date('m', strtotime($attendanceDate));
        $year = date('Y', strtotime($attendanceDate));
        for($i = 1; $i <= date('t', strtotime($attendanceDate)); $i++):
            $todayDate = $year.'-'.$month.'-'.($i < 10 ? '0'.$i: $i);
            $data[] = [
                'sl' => $i,
                'date' => date('Y-m-d', strtotime($todayDate)),
                'dateUnix' => strtotime($todayDate),
                'theDate' => date('D jS M, Y', strtotime($todayDate)),
                'synchronise' => $this->isSynchronised($todayDate),
                'issues' => EmployeeAttendance::where('date', $todayDate)->where('user_issues', '>', 0)->get()->count(),
                'absents' => EmployeeAttendance::where('date', $todayDate)->where('leave_status', '>', 1)->get()->count(),
                'overtime' => EmployeeAttendance::where('date', $todayDate)->where('overtime_status', 1)->get()->count(),
                'pendings' => EmployeeAttendance::where('date', $todayDate)->whereNull('updated_by')->get()->count(),
                'allRows' => EmployeeAttendance::where('date', $todayDate)->get()->count(),
            ];
        endfor;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function isSynchronised($theDate){
        $employeeAttendance = EmployeeAttendance::where('date', $theDate)->get()->count();
        return ($employeeAttendance > 0 ? 1 : 0);
    }

    public function show($date){
        return view('pages.hr.attendance.show', [
            'title' => 'HR Daily Attendance - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'HR Daily Attendance', 'href' => 'javascript:void(0);']
            ],
            'date' => $date,
            'theDate' => date('D jS F, Y', $date),
            'issues' => EmployeeAttendance::where('date', date('Y-m-d', $date))->where('user_issues', '>', 0)->orderBy('id', 'ASC')->get(),
            'absents' => EmployeeAttendance::where('date', date('Y-m-d', $date))->where('leave_status', '>', 1)->orderBy('id', 'ASC')->get(),
            'overtime' => EmployeeAttendance::where('date', date('Y-m-d', $date))->where('overtime_status', 1)->orderBy('id', 'ASC')->get(),
            'noissues' => EmployeeAttendance::where('date', date('Y-m-d', $date))
                          ->where('overtime_status', 0)->where('leave_status', '<', 2)
                          ->where('user_issues', 0)
                          ->orderBy('id', 'ASC')->get(),
        ]);
    }

    public function syncronise(Request $request){
        $theDate = date('Y-m-d', strtotime($request->theDate));
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $employees = Employee::has('activePatterns')->where('status', 1)->orderBy('first_name', 'ASC')->get();

        foreach($employees as $employee):
            if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes'):
                $employee_id = $employee->id;
                $employee = Employee::find($employee_id);

                $data               = [];
                $issues             = 0;
                $issues_array       = [];

                $todayAttendance = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->orderBy('id', 'ASC')->get();
                $todayLastOutRow = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $theDate)->where('attendance_type', 4)->orderBy('id', 'DESC')->get()->first();
                $todayLastOutId = (isset($todayLastOutRow->id) && $todayLastOutRow->id > 0 ? $todayLastOutRow->id : 0);
                
                $breakArray         = [];
                $break_return       = [];
                $work_start         = '';
                $work_end           = '';
                $system_work_start  = '';
                $system_work_end    = '';
                $br                 = 1;
                $day_user_pay_info  = [];
                $start_contract = $end_contract = $paid_break = $unpaid_break = $n_dif = $p_dif = $en_dif = $ep_dif = '';

                $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $theDate)
                                         ->where(function($query) use($theDate){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                                         })->get()->first();
                $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                $patternPay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $activePatternId)
                              ->where('effective_from', '<=', $theDate)
                              ->where(function($query) use($theDate){
                                    $query->whereNull('end_to')->orWhere('end_to', '>=', $theDate);
                              })->where('active', 1)->get()->first();
                $activePatternPayId = (isset($patternPay->id) && $patternPay->id > 0 ? $patternPay->id : 0);

                $patternDay         = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $theDayNum)->get()->first();
                $day_status         = (isset($patternDay->id) && $patternDay->id > 0 ? 1 : 2);
                $contractStart      = (isset($patternDay->start) && !empty($patternDay->start)) ? $patternDay->start : '00:00';
                $contractEnd        = (isset($patternDay->end) && !empty($patternDay->end)) ? $patternDay->end : '00:00';
                $paid_break         = (isset($patternDay->paid_br) && !empty($patternDay->paid_br)) ? $patternDay->paid_br : '00:00';
                $unpaid_break       = (isset($patternDay->unpaid_br) && !empty($patternDay->unpaid_br)) ? $patternDay->unpaid_br : '00:00';
                $total_hour         = (isset($patternDay->total) && !empty($patternDay->total)) ? $patternDay->total : '00:00';

                $employeeLeaveIds   = EmployeeLeave::where('employee_id', $employee_id)->where('from_date', '<=', $theDate)->where('to_date', '>=', $theDate)
                                      ->where('status', 'Approved')->pluck('id')->toArray();
                $employeeLeaveDay   = EmployeeLeaveDay::whereIn('employee_leave_id', $employeeLeaveIds)->where('status', 'Active')
                                      ->where('leave_date', $theDate)->get()->first();

                $is_leave_day       = 0;
                $today_leave_id     = 0;
                $leave_type         = 0;
                $leave_day_hours    = 0;

                $total_hours_day    = $total_hour;
                $total_mints_day    = $this->convertStringToMinute($total_hour);
                if(!empty($employeeLeaveDay) && isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0):
                    $is_leave_day   = 1;
                    $today_leave_id = $employeeLeaveDay->id;
                    $todayHour = ($total_hour != '00:00' && $total_hour != '') ? $this->convertStringToMinute($total_hour) : 0;
                    $leaveHour = ($employeeLeaveDay->hour > 0 ? $employeeLeaveDay->hour : $this->convertStringToMinute($total_hour));
                    
                    $leave_day_hours = $leaveHour;

                    $total_hours_day = ($leaveHour <= $todayHour) ? $this->calculateHourMinute($leaveHour) : $this->calculateHourMinute($todayHour);
                    $total_mints_day = ($leaveHour <= $todayHour) ? $leaveHour : $todayHour;

                    $leave_type = (isset($employeeLeaveDay->leave->leave_type) && $employeeLeaveDay->leave->leave_type > 0) ? $employeeLeaveDay->leave->leave_type : 0;
                endif;
                
                $data['employee_id'] = $employee_id;
                $data['employee_working_pattern_id'] = $activePatternId;
                $data['employee_working_pattern_pay_id'] = $activePatternPayId;
                $data['date'] = $theDate;

                if(($day_status == 1 && $todayAttendance->count() > 0) || ($day_status == 2 && $todayAttendance->count() > 0)):
                    /* Start If clock in not found */
                    if(!$this->isPunchExist($employee_id, $theDate, 1)):
                        if($day_status == 2):
                            $value = '';
                        else:
                            $work_start = 1;
                            $notify = ($this->getConditionSet('Clock In', 4, 'notify', 0) == 1) ? 'notfy_input' : '';
                            if($this->getConditionSet('Clock In', 4, 'notify', 0) == 1){
                                $issues += 1;
                                $issues_array['clockin_system'] = 1;
                            }
                            $value = '';
                            $action = $this->getConditionSet('Clock In', 4, 'action', 0);
                            if($this->getConditionSet('Clock In', 4, 'action', 0) == 1){
                                $value = date('H:i', strtotime($contractStart));
                            }
                        endif;

                        $data['clockin_contract'] = date('H:i', strtotime($contractEnd));
                        $data['clockin_punch'] = '00:00';
                        $data['clockin_system'] = $value;
                    endif;
                    /* End If clock in not found */

                    /* Start Loop for Attendance Feed from Live */
                    $in_status = 0;
                    $out_status = 0;
                    foreach($todayAttendance as $k => $clocks):
                        if($clocks->attendance_type == 1 && $day_status == 1 && $in_status == 0):
                            $in_status += 1;
                            $work_start = date('H:i', strtotime($clocks->time));

                            $to_time = strtotime($contractStart);
                            $from_time = strtotime($work_start);
                            if($to_time > $from_time):
                                $n_dif = round(abs($to_time - $from_time) / 60,2);
                            else:
                                $p_dif = round(abs($to_time - $from_time) / 60,2);
                            endif;

                            if($clocks->time != ''):
                                if($n_dif != '' && ($n_dif > 0 && $n_dif <= $this->getConditionSet('Clock In', 1, 'time', 0))):
                                    $notify = ($this->getConditionSet('Clock In', 1, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 1, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 1, 'action', 0);
                                    if($this->getConditionSet('Clock In', 1, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 1, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                elseif($n_dif != '' && $n_dif > $this->getConditionSet('Clock In', 1, 'time', 0)):
                                    $system_work_start = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockin_system'] = 1;
                                elseif($p_dif != '' && $p_dif > 0 && $p_dif <= $this->getConditionSet('Clock In', 2, 'time', 0)):
                                    $notify = ($this->getConditionSet('Clock In', 2, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 2, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 2, 'action', 0);
                                    if($this->getConditionSet('Clock In', 2, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 2, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                elseif($p_dif != '' && $p_dif > $this->getConditionSet('Clock In', 3, 'time', 0)):
                                    $notify = ($this->getConditionSet('Clock In', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock In', 3, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockin_system'] = 1;
                                    }
                                    $value = '';
                                    $action = $this->getConditionSet('Clock In', 3, 'action', 0);
                                    if($this->getConditionSet('Clock In', 3, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractStart));
                                    }elseif($this->getConditionSet('Clock In', 3, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_start = $value;
                                else:
                                    $system_work_start = date('H:i', strtotime(strtr($clocks->time, '/', '-')));
                                endif;
                            else:
                                $notify = ($this->getConditionSet('Clock In', 4, 'notify', 0) == 1) ? 'notfy_input' : '';
                                if($this->getConditionSet('Clock In', 4, 'notify', 0) == 1){
                                    $issues += 1;
                                    $issues_array['clockin_system'] = 1;
                                }
                                $value = '';
                                $action = $this->getConditionSet('Clock In', 4, 'action', 0);
                                if($this->getConditionSet('Clock In', 4, 'action', 0) == 1){
                                    $value = date('H:i', strtotime($contractStart));
                                }elseif($this->getConditionSet('Clock In', 4, 'action', 0) == 2){
                                    $value = date('H:i', strtotime($clocks->time));
                                } 
                                $system_work_start = $value;
                            endif;
                            $data['clockin_contract'] = date('H:i', strtotime($contractStart));
                            $data['clockin_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockin_system'] = $system_work_start;
                        elseif($clocks->attendance_type == 1 && $day_status == 2 && $in_status == 0):
                            $in_status += 1;
                            $system_work_start = date('H:i', strtotime($clocks->time));

                            $data['clockin_contract'] = date('H:i', strtotime($contractStart));
                            $data['clockin_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockin_system'] = date('H:i', strtotime($clocks->time));
                        elseif($clocks->attendance_type == 4 && $day_status == 1 && $out_status == 0 && $todayLastOutId == $clocks->id):
                            $out_status += 1;
                            $work_end = date('H:i', strtotime($clocks->time));

                            $eto_time = strtotime($contractEnd);
                            $efrom_time = strtotime($work_end);

                            if($eto_time > $efrom_time):
                                $en_dif = round(abs($eto_time - $efrom_time) / 60,2);
                            else:
                                $ep_dif = round(abs($eto_time - $efrom_time) / 60,2);
                            endif;

                            if($clocks->time != ''):
                                if($en_dif != '' && $en_dif > 0 && $en_dif <= $this->getConditionSet('Clock Out', 1, 'time', 0)):
                                    $notify = ($this->getConditionSet('Clock Out', 1, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock Out', 1, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockout_system'] = 1;
                                    }
                                    $value = '';
                                    $action2 = $this->getConditionSet('Clock Out', 1, 'action', 0);
                                    if($this->getConditionSet('Clock Out', 1, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractEnd));
                                    }elseif($this->getConditionSet('Clock Out', 1, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_end = $value;
                                elseif($en_dif != '' && $en_dif > $this->getConditionSet('Clock Out', 1, 'time', 0)):
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                elseif($ep_dif != '' && $ep_dif > 0 && $ep_dif <= $this->getConditionSet('Clock Out', 2, 'time', 0)):
                                    $notify = ($this->getConditionSet('Clock Out', 2, 'notify', 0) == 1) ? 'notfy_input' : '';
                                    if($this->getConditionSet('Clock Out', 2, 'notify', 0) == 1){
                                        $issues += 1;
                                        $issues_array['clockout_system'] = 1;
                                    }
                                    $value = '';
                                    $action2 = $this->getConditionSet('Clock Out', 2, 'action', 0);
                                    if($this->getConditionSet('Clock Out', 2, 'action', 0) == 1){
                                        $value = date('H:i', strtotime($contractEnd));
                                    }elseif($this->getConditionSet('Clock Out', 2, 'action', 0) == 2){
                                        $value = date('H:i', strtotime($clocks->time));
                                    }
                                    $system_work_end = $value;
                                elseif($ep_dif != '' && $ep_dif > $this->getConditionSet('Clock Out', 2, 'time', 0)):
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                else:
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                endif;
                            else:
                                $notify = ($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                                if($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1){
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                }
                                $value = '';
                                $action2 = $this->getConditionSet('Clock Out', 3, 'action', 0);
                                if($this->getConditionSet('Clock Out', 3, 'action', 0) == 1){
                                    $value = date('H:i', strtotime($contractEnd));
                                }elseif($this->getConditionSet('Clock Out', 3, 'action', 0) == 2){
                                    $value = date('H:i', strtotime($clocks->time));
                                }
                                $system_work_end = $value;
                            endif; 
                            $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                            $data['clockout_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockout_system'] = $system_work_end;
                        elseif($clocks->attendance_type == 4 && $day_status == 2 && $out_status == 0 && $todayLastOutId == $clocks->id):
                            $out_status += 1;
                            $system_work_end = date('H:i', strtotime($clocks->time));

                            $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                            $data['clockout_punch'] = date('H:i', strtotime($clocks->time));
                            $data['clockout_system'] = date('H:i', strtotime($clocks->time));
                        elseif($clocks->attendance_type == 2):
                            $break_return['break_'.$br] = $clocks->time;
                            $br++;
                        elseif($clocks->attendance_type == 3):
                            $break_return['return_'.$br] = $clocks->time;
                            $br++;
                        endif;
                    endforeach;
                    /* End Loop for Attendance Feed from Live */

                    /* Start If clock Out not found */
                    if(!$this->isPunchExist($employee_id, $theDate, 4)):
                        if($day_status == 2):
                            $value = '';
                            $system_work_end = $value;
                        else:
                            $work_end = 1;
                            $notify = ($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1) ? 'notfy_input' : '';
                            if($this->getConditionSet('Clock Out', 3, 'notify', 0) == 1){
                                $issues += 1;
                                $issues_array['clockout_system'] = 1;
                            }
                            $value = '';
                            $action2 = $this->getConditionSet('Clock Out', 3, 'action', 0);
                            if($this->getConditionSet('Clock Out', 3, 'action', 0) == 1){
                                $value = date('H:i', strtotime($contractEnd));
                            }
                            $system_work_end = $value;
                        endif;

                        $data['clockout_contract'] = date('H:i', strtotime($contractEnd));
                        $data['clockout_punch'] = '00:00';
                        $data['clockout_system'] = $value;
                    endif;
                    /* End If clock Out not found */

                    /* Start Break Calculations */
                    $total_break = 0;
                    $b = 1;
                    $b_start = '';
                    $break_details = '';
                    $count = (!empty($break_return) ? count($break_return) : 0);

                    if(is_array($break_return) && !empty($break_return)):
                        $break_details .= '<ol class="return_list">';
                        foreach($break_return as $key => $time):
                            if($b % 2 == 0){
                                if(strpos($key, 'return_') !== false){
                                    $bs = strtotime($b_start);
                                    $be = strtotime(date('H:i', strtotime(strtr($time, '/', '-'))));
                                    $total_break += round(abs($bs - $be) / 60,2);
                                    $break_details .= ' - <span class="ret">'.date('H:i', strtotime(strtr($time, '/', '-'))).'</span></span></li>';
                                }else{
                                    $break_details .= ' - <span class="ret">00:00</span></span><br/><input type="text" value="00:00" class="form-control edit_this"/></li>';
                                    $b_start = date('H:i', strtotime(strtr($time, '/', '-')));
                                    $break_details .= '<li><span class="re_br"><span class="bre">'.$b_start.'</span>';
                                    $issues += 1;
                                    $issues_array['break_return'] = 1;
                                }
                            }else{
                                if(strpos($key, 'break_') !== false){
                                    $b_start = date('H:i', strtotime(strtr($time, '/', '-')));
                                    $break_details .= '<li><span class="re_br"><span class="bre">'.$b_start.'</span>';
                                    if($b == $count){
                                        $break_details .= ' - <span class="ret">00:00</span></span><br/><input type="text" value="00:00" class="form-control edit_this"/></li>';
                                        $issues += 1;
                                        $issues_array['break_return'] = 1;
                                    }
                                }else{
                                    $bs = strtotime($b_start);
                                    $be = strtotime(date('H:i', strtotime(strtr($time, '/', '-'))));
                                    $total_break += round(abs($bs - $be) / 60, 2);
                                    $break_details .= ' - '.date('H:i', strtotime(strtr($time, '/', '-'))).'</span></li>';
                                }
                            }
                            $b++;
                        endforeach;
                        $break_details .= '</ol>';
                    endif;
                    $break = ($this->convertStringToMinute($paid_break) + $this->convertStringToMinute($unpaid_break));
                                                                    
                    $actualBreak = 0;
                    if($break < $total_break):
                        $actualBreak = $total_break - $break;
                    endif;

                    $data['break_details_html'] = $break_details;
                    $data['total_break'] = $total_break;
                    /* End Break Calculations */

                    $data['paid_break'] = $paid_break;
                    $data['unpadi_break'] = $unpaid_break;
                    $data['adjustment'] = '+00:00';

                    if($work_start == 1 || $work_end == 1):
                        $total_work = 0;
                        $hours = '00';
                        $minutes = '00';
                    else:
                        $work_start = strtotime($work_start);
                        $work_end = strtotime($work_end);

                        $system_start = ($system_work_start != '' ? strtotime($system_work_start) : 0);
                        $system_end = ($system_work_end != '' ? strtotime($system_work_end) : 0);

                        if($system_end != '' && $system_end > 0 && $system_start != '' && $system_start > 0):
                            $total_today_break = $actualBreak + $this->convertStringToMinute($unpaid_break);

                            $total_today = round(abs($system_start - $system_end) / 60,2);
                            $total_work = ($total_today > $total_today_break ? ($total_today - $total_today_break) : $total_today);
                        else:
                            $total_work = 0;
                        endif;
                        $hours = floor($total_work / 60);
                        $hours = ($hours < 10 ? '0'.$hours : $hours);
                        $minutes = $total_work % 60;
                        $minutes = ($minutes < 10 ? '0'.$minutes : $minutes);
                    endif;
                    //if($is_leave_day == 1 && $today_leave_id > 0 && ($leave_type == 1 || $leave_type == 2)):
                        //$total_work += $leave_day_hours;
                    if($is_leave_day == 1 && $today_leave_id > 0):
                        $issues += 1;
                        $issues_array['work_leave'] = 1;
                    endif;

                    if($day_status == 2): 
                        $issues += 1; 
                        $issues_array['over_time'] = 1;
                    endif;

                    $data['total_work_hour'] = $total_work;
                    $data['user_issues'] = ($issues > 0 ? $issues : 0);
                    $data['leave_status'] = $leave_type;
                    $data['leave_hour'] = ($leave_type > 0) ? $leave_day_hours : 0;
                    $data['leave_adjustment'] = '+00:00';
                    $data['employee_leave_day_id'] = (isset($today_leave_id) && $today_leave_id > 0 && $leave_type > 0 ? $today_leave_id : null);
                    $data['overtime_status'] = ($day_status == 2) ? 1 : 0;
                    $data['isses_field'] = (!empty($issues_array) ? base64_encode(serialize($issues_array)) : null);
                    $data['note'] = '';
                    $data['status'] = 1; 
                    $data['created_by'] = auth()->user()->id;
                    
                    EmployeeAttendance::create($data);
                elseif($day_status == 1 && $todayAttendance->count() == 0):
                    $leave_type = ($leave_type > 0) ? $leave_type : 4;
                    $data['clockin_contract'] = '';
                    $data['clockin_punch'] = '';
                    $data['clockin_system'] = '';
                    $data['clockout_contract'] = '';
                    $data['clockout_punch'] = '';
                    $data['clockout_system'] = '';
                    $data['total_break'] = 0;
                    $data['paid_break'] = $paid_break;
                    $data['unpadi_break'] = $unpaid_break;
                    $data['adjustment'] = '+00:00';
                    $data['total_work_hour'] = 0;
                    $data['employee_leave_day_id'] = (isset($today_leave_id) && $today_leave_id > 0 ? $today_leave_id : null);
                    $data['leave_status'] = $leave_type;
                    $data['leave_hour'] = ($leave_type > 0) ? $total_mints_day : 0;
                    $data['leave_adjustment'] = '+00:00';
                    $data['note'] = '';
                    $data['user_issues'] = 0;
                    $data['isses_field'] = '';
                    $data['overtime_status'] = 0;
                    $data['status'] = 1; 
                    $data['created_by'] = auth()->user()->id;

                    EmployeeAttendance::create($data);
                endif;
            endif;
        endforeach;

        return response()->json(['res' => 'Employee attendance successfully sincronised.', 'date' => date('D jS M', strtotime($theDate)), 'url' => url('hr/attendance/show/'.strtotime($theDate))], 200);
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

    function isPunchExist($employee_id, $date, $punch){
        $live = EmployeeAttendanceLive::where('employee_id', $employee_id)->where('date', $date)->where('attendance_type', $punch)->get()->first();
        return (isset($live->id) && $live->id > 0 ? true : false);
    }

    function getConditionSet($type, $frame, $col, $default = ''){
        $condition = HrCondition::where('type', $type)->where('time_frame', $frame)->get()->first();
        return (isset($condition->$col) && $condition->$col != '' ? $condition->$col : $default);
    }

    public function update(Request $request){
        parse_str($request->rowData, $rowData);
        $attendance = $rowData['attendance'];

        $rowNote = (isset($request->rowNote) && !empty($request->rowNote) ? $request->rowNote : null);

        parse_str($request->leaveData, $leaveData);
        $leave = (isset($leaveData['attendance']) && !empty($leaveData['attendance']) ? $leaveData['attendance'] : []);

        if(!empty($attendance)):
            foreach($attendance as $attenid => $atten):
                $attendance_id = $atten['attendance_id'];
                
                $data = [];
                $data['adjustment'] = $atten['adjustment'];
                $data['clockin_system'] = $atten['clockin_system'];
                $data['clockout_system'] = $atten['clockout_system'];
                $data['total_work_hour'] = $atten['total_work_hour'];
                $data['total_break'] = $atten['total_break'];
                $data['paid_break'] = $atten['paid_break'];
                $data['unpadi_break'] = $atten['unpadi_break'];
                $data['user_issues'] = 0;
                $data['isses_field'] = null;
                $data['note'] = $rowNote;
                $data['updated_by'] = auth()->user()->id;

                if(isset($leave[$attenid]['leave_status']) &&  $leave[$attenid]['leave_status'] > 0):
                    $data['leave_adjustment'] = $leave[$attenid]['leave_adjustment'];
                    $data['leave_hour'] = $leave[$attenid]['leave_hour'];
                    $data['leave_status'] = $leave[$attenid]['leave_status'];
                elseif(isset($atten['leave_status']) && $atten['leave_status'] > 0):
                    $data['leave_adjustment'] = $atten['leave_adjustment'];
                    $data['leave_hour'] = $atten['leave_hour'];
                    $data['leave_status'] = $atten['leave_status'];
                else:
                    $leave_status = (isset($atten['leave_status']) && $atten['leave_status'] > 0 ? $atten['leave_status'] : 0);
                    $data['leave_status'] = $leave_status;
                endif;

                EmployeeAttendance::where('id', $attendance_id)->update($data);
            endforeach;

            return response()->json(['res' => 'The attendance row has been successfully updated.'], 200);
        else:
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function updateAll(Request $request){
        parse_str($request->allData, $rowData);
        $attendance = $rowData['attendance'];

        if(!empty($attendance)):
            foreach($attendance as $atten):
                if(isset($atten['id']) && $atten['id'] > 0):
                    $attendance_id = $atten['attendance_id'];
                    $leave_status = (isset($atten['leave_status']) && $atten['leave_status'] > 0 ? $atten['leave_status'] : 0);
                    $data = [];
                    $data['adjustment'] = $atten['adjustment'];
                    $data['clockin_system'] = $atten['clockin_system'];
                    $data['clockout_system'] = $atten['clockout_system'];
                    $data['total_work_hour'] = $atten['total_work_hour'];
                    $data['total_break'] = $atten['total_break'];
                    $data['paid_break'] = $atten['paid_break'];
                    $data['unpadi_break'] = $atten['unpadi_break'];
                    $data['user_issues'] = 0;
                    $data['isses_field'] = null;
                    $data['note'] = (isset($atten['note']) && !empty($atten['note']) ? $atten['note'] : null);
                    $data['leave_status'] = $leave_status;
                    $data['updated_by'] = auth()->user()->id;

                    if(isset($atten['leave_status']) &&  $atten['leave_status'] > 0):
                        $data['leave_adjustment'] = $atten['leave_adjustment'];
                        $data['leave_hour'] = $atten['leave_hour'];
                    endif;

                    EmployeeAttendance::where('id', $attendance_id)->update($data);
                endif;
            endforeach;

            return response()->json(['res' => 'The attendance row has been successfully updated.'], 200);
        else:
            return response()->json(['res' => 'Something went wrong. Please try later.'], 422);
        endif;
    }

    public function edit(Request $request){
        $rowID = $request->rowID;
        $attendance = EmployeeAttendance::find($rowID);

        return response()->json(['res' => $attendance], 200);
    }

    public function updateBreak(Request $request){
        $rowID = $request->rowID;
        $employeeAttendance = EmployeeAttendance::find($rowID);
        $total_break = (isset($employeeAttendance->total_break) && $employeeAttendance->total_break > 0 ? $employeeAttendance->total_break : 0);
        $total_work_hour = (isset($employeeAttendance->total_work_hour) && $employeeAttendance->total_work_hour > 0 ? $employeeAttendance->tottotal_work_hourl_break : 0);
        $user_issues = (isset($employeeAttendance->user_issues) && $employeeAttendance->user_issues > 0 ? $employeeAttendance->user_issues : 0);
        $isses_field = (isset($employeeAttendance->isses_field) && !empty($employeeAttendance->isses_field) ? unserialize(base64_decode($employeeAttendance->isses_field)) : []);
        $break_return = (isset($isses_field['break_return']) && $isses_field['break_return'] == 1) ? 1 : 0;
        if($user_issues > 0 && $break_return == 1):
            $user_issues -= 1;
            unset($isses_field['break_return']);
        endif;

        $break_hrml = $request->break_hrml;

        $total_min = 0;
        $breakTimes = (!empty($request->breakTimes) ? $request->breakTimes : []);
        foreach($breakTimes as $brTimes):
            $brTimesArr = (!empty($brTimes) ? explode('-', $brTimes) : []);
            $startTime = (isset($brTimesArr[0]) && !empty($brTimesArr[0]) ? $brTimesArr[0] : '00:00');
            $endTime = (isset($brTimesArr[1]) && !empty($brTimesArr[1]) ? $brTimesArr[1] : '00:00');

            if($startTime != '00:00' && $endTime != '00:00'):
                $bs = strtotime(date('H:i', strtotime(strtr($startTime, '/', '-'))));
                $be = strtotime(date('H:i', strtotime(strtr($endTime, '/', '-'))));
                $total_min += round(abs($bs - $be) / 60, 2);
            endif;
        endforeach;
        
        $paid_break = (!empty($employeeAttendance->paid_break) ? $this->convertStringToMinute($employeeAttendance->paid_break) : 0);
        $unpaid_break = (!empty($employeeAttendance->unpadi_break) ? $this->convertStringToMinute($employeeAttendance->unpadi_break) : 0);
        $allowedBreak = ($paid_break + $unpaid_break);

        $data = [];
        $new_total_break = $total_break + $total_min;                             
        if($new_total_break > $allowedBreak){
            if ($total_break > $allowedBreak) {
                $deduct = ($total_break - $allowedBreak);
                $prev_total_work_hour = ($total_work_hour + $deduct);
                $new_total_work_hour = ($total_work_hour - $unpaid_break) - $deduct;

                $data['total_work_hour'] = $new_total_work_hour;
            }else{
                $deduct = ($new_total_break - $allowedBreak);
                $new_total_work_hour = ($total_work_hour - $deduct) - $unpaid_break;
                
                $data['total_work_hour'] = $new_total_work_hour;
            }
            $data['total_break'] = $new_total_break;
            $data['break_details_html'] = $break_hrml;
        }else{
            $data['total_break'] = $new_total_break;
            $data['break_details_html'] = $break_hrml;
        }
        $data['user_issues'] = $user_issues;
        $data['isses_field'] = base64_encode(serialize($isses_field));

        EmployeeAttendance::where('id', $rowID)->update($data);

        return response()->json(['res' => 'Break HTML successfully updated'], 200);
    }
}
