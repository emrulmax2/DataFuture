<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceDayBreak;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrCondition;
use App\Models\HrHolidayYear;
use App\Models\PaySlipUploadSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;
use ZipArchive;

class EmployeeAttendanceController extends Controller
{

    public function index(Request $request){
        
        $holidayList = HrHolidayYear::orderBy('start_date','desc')->get();
      
        return view('pages.hr.attendance.index', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => 'javascript:void(0);']
            ],
            'html_table' => $this->listHtml(date('d-m-Y')),
            'holiday_years' => $holidayList,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:zip|max:200480',
        ]);
        $type = $request->type;
        $file = $request->file('file');
        
        $dirName = $request->dir_name;
        // Define a temporary location to store the uploaded zip file
        $tempPath = $file->storeAs('temp', $file->getClientOriginalName());
        
        $zip = new ZipArchive();

        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {
            $extractPath = storage_path('app/temp/extracted');
            $zip->extractTo($extractPath);
            $zip->close();
            // Get all extracted files
            $files = File::files($extractPath);

            // Loop through the files and store them in the desired location
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    // Store the file in the 'public' disk with its original name
                    $fileName = $file->getFilename();
                    // Get the original name without extension
                    $originalNameWithoutExtension = pathinfo($fileName, PATHINFO_FILENAME);
                    $destinationPath = 'public/uploads/employee_payslips/'.$dirName ;// Define the destination path
                    
                    Storage::disk('public')->put($destinationPath . '/' . $fileName, File::get($file));
                    
                    // Get the file path after storage
                    $filePath = Storage::disk('public')->url($destinationPath . '/' . $fileName);
                    
                    $paySlipUploadSync = [];
                    $employeeList =Employee::all();

                    foreach($employeeList as $employee) {
                        // find employee first_name and last_name from $fileName string
                        // Extract first_name and last_name from the filename

                        $fileNameArray = explode(' ', strtoupper($originalNameWithoutExtension));
                        
                        if(in_array(strtoupper(trim($employee->first_name)), $fileNameArray) && in_array(strtoupper(trim($employee->last_name)), $fileNameArray)){

                            $employeeFound = $employee->id;
                            break;

                        } else {
                                $employeeFound = 0;
                                $paySync=PaySlipUploadSync::all();
                                foreach($paySync as $pay) {
                
                                    if($pay->file_name == $fileName && $pay->employe_id != null) {
                
                                        $employeeFound = $employee->id;
                                        break;
                                    }
                                }
                        }   
                    
                        
                    }

                    // payslipuploadSync table data insert if file_name and month_year not exist
                    $paySlipUploadSync = PaySlipUploadSync::updateOrCreate(
                        [
                            
                            'file_name' => $fileName,
                            'month_year' => $dirName,

                        ],[
                        'employee_id' => ($employeeFound) ? $employeeFound : NULL,
                        'file_name' => $fileName,
                        'file_path' => $filePath,
                        'month_year' => $dirName,
                        'type' => isset($type) ? $type : 'Payslip',
                        'is_file_exist' => ($employeeFound) ? 1 : 0,
                        'file_transffered' => 0,
                        'file_transffered_at' => null,
                        'is_file_uploaded' => 1,

                    ]);
                    
                
                }
            }

            // Clean up the temporary files
            Storage::delete($tempPath);
            array_map('unlink', glob("$extractPath/*"));
            rmdir($extractPath);
        }

       
        
        
        
        
        if($paySlipUploadSync){
            return response()->json(['success' => 'File uploaded successfully']);
        }else{
            return response()->json(['error' => 'Failed to upload the file'], 500);
        }
    }

    public function payrollSyncShow($month_year){
        $paySlipUploadSync = PaySlipUploadSync::where('month_year', $month_year)->get();
            $checkEmploye =  PaySlipUploadSync::where('month_year', $month_year)->pluck('employee_id')->unique()->toArray();
        return view('pages.hr.attendance.payroll_sync', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'Payroll Sync', 'href' => 'javascript:void(0);']
            ],
            'employees'=> Employee::all(),
            'paySlipUploadSync' => $paySlipUploadSync,
            'month_year' => $month_year,
            'checkEmploye' => count($checkEmploye) ?? 0
        ]);
    }
    public function getListHtml(Request $request){
        $queryDate = (isset($request->queryDate) && !empty($request->queryDate) ? date('Y-m-d', strtotime('01-'.$request->queryDate)) : date('Y-m-d'));
        $html = $this->listHtml($queryDate);

        return response()->json(['res' => $html], 200);
    }

    public function listHtml($attendanceDate){
        $html = '';
        $month = date('m', strtotime($attendanceDate));
        $year = date('Y', strtotime($attendanceDate));
        for($i = 1; $i <= date('t', strtotime($attendanceDate)); $i++):
            $todayDate = $year.'-'.$month.'-'.($i < 10 ? '0'.$i: $i);
            $isSyncronised = $this->isSynchronised($todayDate);
            $theUrl = $isSyncronised == 1 ? route('hr.attendance.show',strtotime($todayDate)) : 'javascript:void(0);';
            
            $issues = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('user_issues', '>', 0)->where('overtime_status', '!=', 1)->get()->count() : 0);
            $absents = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('leave_status', '>', 1)->get()->count() : 0);
            $overtime = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->where('overtime_status', 1)->get()->count() : 0);
            $pendings = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->whereNull('updated_by')->get()->count() : 0);
            $allRows = ($isSyncronised == 1 ? EmployeeAttendance::where('date', $todayDate)->get()->count() : 0);
            $html .= '<tr>';
                $html .= '<td>'.date('D jS M, Y', strtotime($todayDate)).'</td>';
                $html .= '<td>';
                    if($isSyncronised == 1):
                        $html .= '<button class="btn btn-sm btn-primary rounded-0 w-auto text-white" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check-circle" class="lucide lucide-check-circle w-4 h-4 mr-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    Synchronised
                                </button>';
                    else:
                        $html .= '<button type="button"
                                    data-date="'.$todayDate.'"
                                    class="btn btn-sm btn-success text-white rounded-0 w-auto syncroniseAttendance">
                                    Synchronise
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>';
                    endif;
                $html .= '</td>';

                $html .= '<td>';
                    if($issues > 0):
                        $html .= '<a href="'.$theUrl.'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'.$issues.' Issues</a>';
                    else:
                        $html .= '<a href="'.$theUrl.'" class="btn btn-sm btn-success text-white rounded-0">0 Issues</a>';
                    endif;
                $html .= '</td>';

                $html .= '<td>';
                    if($absents > 0):
                        $html .= '<a href="'.$theUrl.'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'.$absents.' Absents</a>';
                    else:
                        $html .= '<a href="'.$theUrl.'" class="btn btn-sm btn-success text-white rounded-0">0 Absents</a>';
                    endif;
                $html .= '</td>';

                $html .= '<td>';
                    if($overtime > 0):
                        $html .= '<a href="'.$theUrl.'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'.$overtime.' Overtime</a>';
                    else:
                        $html .= '<a href="'.$theUrl.'" class="btn btn-sm btn-success text-white rounded-0">0 Overtime</a>';
                    endif;
                $html .= '</td>';
                $html .= '<td>';
                    if($pendings > 0):
                        $html .= '<a href="'.$theUrl.'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'.$pendings.' Pendings</a>';
                    else:
                        $html .= '<a href="'.$theUrl.'" class="btn btn-sm btn-success text-white rounded-0">0 Pendings</a>';
                    endif;
                $html .= '</td>';
                $html .= '<td>';
                    if($allRows > 0):
                        $html .= '<a href="'.$theUrl.'" target="_blank" class="btn btn-sm btn-warning text-white rounded-0">'.$allRows.' Attendances</a>';
                        if(isset(auth()->user()->priv()['del_attendance']) && auth()->user()->priv()['del_attendance'] == 1):
                            $html .= '<button data-date="'.date('Y-m-d', strtotime($todayDate)).'" class="deleteAllSyncd btn btn-sm btn-danger text-white rounded-0 ml-1 relative" style="top: 4px;" type="button"><i data-lucide="trash-2" class="w-4 h-4"></i></button>';
                        endif;
                    else:
                        $html .= '<a href="'.$theUrl.'" class="btn btn-sm btn-success text-white rounded-0">0 Attendances</a>';
                    endif;
                $html .= '</td>';
            $html .= '</tr>';
        endfor;
        return $html;
    }

    public function isSynchronised($theDate){
        $employeeAttendance = EmployeeAttendance::where('date', $theDate)->get()->count();
        return ($employeeAttendance > 0 ? 1 : 0);
    }

    public function show($date){
        return view('pages.hr.attendance.show', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'HR Monthly Attendance', 'href' => route('hr.attendance')],
                ['label' => 'HR Daily Attendance', 'href' => 'javascript:void(0);']
            ],
            'date' => $date,
            'theDate' => date('D jS F, Y', $date),
            'issues' => EmployeeAttendance::where('date', date('Y-m-d', $date))->where('user_issues', '>', 0)->where('overtime_status', '!=', 1)->orderBy('id', 'ASC')->get(),
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
        $syncronised = $this->syncroniseAttendanceData($theDate);
        return response()->json(['res' => 'Employee attendance successfully sincronised.', 'date' => date('D jS M', strtotime($theDate)), 'url' => url('hr/attendance/show/'.strtotime($theDate))], 200);
    }

    public function syncroniseAttendanceData($theDate, $employee_id = 0){
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        if($employee_id > 0):
            $employees = Employee::where('id', $employee_id)->where('status', 1)->orderBy('first_name', 'ASC')->get();
        else:
            $employees = Employee::has('activePatterns')->where('status', 1)->orderBy('first_name', 'ASC')->get();
        endif;

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
                $leave_note         = '';
                if(!empty($employeeLeaveDay) && isset($employeeLeaveDay->id) && $employeeLeaveDay->id > 0):
                    $is_leave_day   = 1;
                    $today_leave_id = $employeeLeaveDay->id;
                    $leave_note = (isset($employeeLeaveDay->leave->note) && !empty($employeeLeaveDay->leave->note) ? $employeeLeaveDay->leave->note : '');
                    $todayHour = ($total_hour != '00:00' && $total_hour != '') ? $this->convertStringToMinute($total_hour) : 0;
                    //$leaveHour = ($employeeLeaveDay->hour > 0 ? $employeeLeaveDay->hour : $this->convertStringToMinute($total_hour));
                    $leaveHour = ($employeeLeaveDay->hour > 0 ? $employeeLeaveDay->hour : 0);
                    
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
                                if($n_dif != '' && ($n_dif > 0 && $n_dif <= $this->getConditionSet('Clock In', 1, 'minutes', 0))):
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
                                elseif($n_dif != '' && $n_dif > $this->getConditionSet('Clock In', 1, 'minutes', 0)):
                                    $system_work_start = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockin_system'] = 1;
                                elseif($p_dif != '' && $p_dif > 0 && $p_dif <= $this->getConditionSet('Clock In', 2, 'minutes', 0)):
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
                                elseif($p_dif != '' && $p_dif > $this->getConditionSet('Clock In', 3, 'minutes', 0)):
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
                                if($en_dif != '' && $en_dif > 0 && $en_dif <= $this->getConditionSet('Clock Out', 1, 'minutes', 0)):
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
                                elseif($en_dif != '' && $en_dif > $this->getConditionSet('Clock Out', 1, 'minutes', 0)):
                                    $system_work_end = date('H:i', strtotime($clocks->time));
                                    $issues += 1;
                                    $issues_array['clockout_system'] = 1;
                                elseif($ep_dif != '' && $ep_dif > 0 && $ep_dif <= $this->getConditionSet('Clock Out', 2, 'minutes', 0)):
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
                                elseif($ep_dif != '' && $ep_dif > $this->getConditionSet('Clock Out', 2, 'minutes', 0)):
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
                    
                    $b = 1;
                    $b_start = '';
                    $break_details = '';

                    $total_break = 0;
                    $break_ids = [];
                    $breakArray = [];
                    $count = (!empty($break_return) ? count($break_return) : 0);
                    $break_issue_count = 0;

                    if(is_array($break_return) && !empty($break_return)):
                        $bi = 1;
                        $bik = 1;
                        $br_issue = 0;
                        foreach($break_return as $key => $time):
                            if($bi % 2 == 0){
                                if(strpos($key, 'return_') !== false){
                                    if(!isset($breakArray[$bik]['start'])):
                                        $breakArray[$bik]['start'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                    endif;

                                    $breakArray[$bik]['end'] = $time;
                                    $bik += 1;
                                }else{
                                    if(!isset($breakArray[$bik]['end'])):
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    endif;

                                    $breakArray[$bik]['start'] = $time;

                                    if($bi == $count){
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    }
                                }
                            }else{
                                if(strpos($key, 'break_') !== false){
                                    if(!isset($breakArray[$bik]['end']) && isset($breakArray[$bik]['start']) && $bik > 1):
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                    endif;

                                    $breakArray[$bik]['start'] = $time;

                                    if($bi == $count){
                                        $breakArray[$bik]['end'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                        $bik += 1;
                                        $br_issue += 1;
                                    }
                                }else{
                                    if(!isset($breakArray[$bik]['start'])):
                                        $breakArray[$bik]['start'] = '00:00:00';
                                        $issues += 1;
                                        $break_issue_count += 1;
                                    endif;

                                    $breakArray[$bik]['end'] = $time;
                                    $bik += 1;
                                }
                            }
                            $bi++;
                        endforeach;
                    endif;
                    if(!empty($breakArray)):
                        foreach($breakArray as $brks):
                            $breakData = [];
                            $breakData['employee_id'] = $employee_id;
                            $breakData['date'] = $theDate;
                            $breakData['start'] = (isset($brks['start']) && !empty($brks['start']) && $brks['end'] != '00:00:00' ? date('H:i', strtotime(strtr($brks['start'], '/', '-'))) : '00:00');
                            $breakData['end'] = (isset($brks['end']) && !empty($brks['end']) && $brks['end'] != '00:00:00' ? date('H:i', strtotime(strtr($brks['end'], '/', '-'))) : '00:00');
                            $breakData['created_by'] = auth()->user()->id;
                            $breakData['total'] = 0;

                            if((isset($brks['start']) && !empty($brks['start']) && $brks['start'] != '00:00:00') && (isset($brks['end']) && !empty($brks['end']) && $brks['end'] != '00:00:00')):
                                $start = strtotime(date('H:i', strtotime(strtr($brks['start'], '/', '-'))));
                                $end = strtotime(date('H:i', strtotime(strtr($brks['end'], '/', '-'))));
                                $theBreakTotal = round(abs($start - $end) / 60, 2);
                                $total_break += $theBreakTotal;
                                $breakData['total'] = $theBreakTotal;
                            endif;
                            $theBreakRow = EmployeeAttendanceDayBreak::create($breakData);
                            $break_ids[] = $theBreakRow->id;
                        endforeach;
                    endif;

                    $break = ($this->convertStringToMinute($paid_break) + $this->convertStringToMinute($unpaid_break));
                    $unpaidBreakMinute = $this->convertStringToMinute($unpaid_break);
                    $actualBreak = 0;
                    if($break < $total_break):
                        $actualBreak = $total_break - $break;
                    endif;
                    $break_issue_count += ($total_break == 0 && $unpaidBreakMinute > 0 ? 1 : 0);
                    $issues += ($total_break == 0 && $unpaidBreakMinute > 0 ? 1 : 0);
                    $break_issue_count += ($unpaidBreakMinute > 0 && $total_break > $unpaidBreakMinute && ($total_break - $unpaidBreakMinute) > 15 ? 1 : 0);
                    $issues += ($unpaidBreakMinute > 0 && $total_break > $unpaidBreakMinute && ($total_break - $unpaidBreakMinute) > 15 ? 1 : 0);

                    if($break_issue_count > 0):
                        $issues_array['break_issue'] = $break_issue_count;
                    endif;
                    $data['break_details_html'] = '';//$break_details;
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
                            //$total_work = ($total_today > $total_today_break ? ($total_today - $total_today_break) : $total_today);
                            $total_work = ($total_today > $total_today_break ? ($total_today - $total_today_break) : 0);
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
                    
                    $EmployeeAttendance = EmployeeAttendance::create($data);
                    if($EmployeeAttendance->id && !empty($break_ids)):
                        EmployeeAttendanceDayBreak::where('employee_id', $employee_id)->where('date', $theDate)->whereIn('id', $break_ids)->update(['employee_attendance_id' => $EmployeeAttendance->id]);
                    endif;
                    if(isset($today_leave_id) && $today_leave_id > 0 && $leave_type > 0):
                        EmployeeLeaveDay::where('id', $today_leave_id)->update(['is_taken' => 1]);
                    endif;
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
                    $data['leave_hour'] = ($leave_type > 0) ? ($leave_day_hours > 0 ? $leave_day_hours : 0) : 0;
                    //$data['leave_hour'] = ($leave_type > 0) ? $total_mints_day : 0;
                    $data['leave_adjustment'] = '+00:00';
                    $data['note'] = '';
                    $data['user_issues'] = 0;
                    $data['isses_field'] = '';
                    $data['overtime_status'] = 0;
                    $data['status'] = 1; 
                    $data['created_by'] = auth()->user()->id;

                    EmployeeAttendance::create($data);
                    if(isset($today_leave_id) && $today_leave_id > 0):
                        EmployeeLeaveDay::where('id', $today_leave_id)->update(['is_taken' => 1]);
                    endif;
                endif;
            endif;
        endforeach;

        return 1;
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

        if(!empty($request->leaveData)):
            parse_str($request->leaveData, $leaveData);
        endif;
        $leave = (isset($leaveData['attendance']) && !empty($leaveData['attendance']) ? $leaveData['attendance'] : []);
        $isLeaveRow = (isset($request->isLeaveRow) && $request->isLeaveRow ? true : false);

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
                elseif((isset($atten['leave_status']) && $atten['leave_status'] > 0) || ($isLeaveRow && isset($atten['leave_status']) && $atten['leave_status'] > 0)):
                    $data['leave_adjustment'] = $atten['leave_adjustment'];
                    $data['leave_hour'] = $atten['leave_hour'];
                    $data['leave_status'] = $atten['leave_status'];
                else:
                    $leave_status = (isset($atten['leave_status']) && $atten['leave_status'] > 0 ? $atten['leave_status'] : 0);
                    $data['leave_status'] = $leave_status;
                endif;

                //return response()->json($data);
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
                    $isOnlyLeave = (isset($atten['only_leave']) && $atten['only_leave'] == 1 ? true : false);
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

                    if((isset($atten['leave_status']) &&  $atten['leave_status'] > 0) || $isOnlyLeave):
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
        $theDayTotal = 0;

        $html = '';
        $html .= '<div class="overflow-x-auto">';
            $html .= '<table class="table table-bordered table-sm">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th class="whitespace-nowrap">#</th>';
                        $html .= '<th class="whitespace-nowrap">Start</th>';
                        $html .= '<th class="whitespace-nowrap">End</th>';
                        $html .= '<th class="whitespace-nowrap">Duration</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    if(isset($attendance->breaks) && $attendance->breaks->count() > 0):
                        $i = 1;
                        foreach($attendance->breaks as $brks):
                            $html .= '<tr class="breakRow">';
                                $html .= '<td>'.$i.'</td>';
                                $html .= '<td><input value="'.$brks->start.'" type="text" class="form-control breakStart w-full timepicker" name="breaks['.$rowID.']['.$brks->id.'][start]"/></td>';
                                $html .= '<td><input value="'.$brks->end.'" type="text" class="form-control breakEnd w-full timepicker" name="breaks['.$rowID.']['.$brks->id.'][end]"/></td>';
                                $html .= '<td><input readonly value="'.$this->calculateHourMinute($brks->total).'" type="text" class="form-control breakRowTotal w-full timepicker" name="breaks['.$rowID.']['.$brks->id.'][total]"/></td>';
                            $html .= '</tr>';
                            $theDayTotal += $brks->total;
                            $i++;
                        endforeach;
                    else:
                        $html .= '<tr class="breakRow">';
                            $html .= '<td>1</td>';
                            $html .= '<td><input value="" type="text" class="form-control breakStart w-full timepicker" name="newBreaks['.$rowID.'][start]"/></td>';
                            $html .= '<td><input value="" type="text" class="form-control breakEnd w-full timepicker" name="newBreaks['.$rowID.'][end]"/></td>';
                            $html .= '<td><input readonly value="" type="text" class="form-control breakRowTotal w-full timepicker" name="newBreaks['.$rowID.'][total]"/></td>';
                        $html .= '</tr>';
                    endif;
                $html .= '</tbody>';
                $html .= '<tfoot>';
                    $html .= '<tr>';
                        $html .= '<td colspan="3"><strong>Day Total</strong></td>';
                        $html .= '<td><input value="'.$this->calculateHourMinute($theDayTotal).'" type="text" class="form-control w-full breakGrandTotal" readonly name="total_break"/></td>';
                    $html .= '</tr>';
                $html .= '</tfoot>';
            $html .= '</table>';
        $html .= '</div>';

        return response()->json(['res' => $html], 200);
    }

    public function updateBreak(Request $request){
        $attendance_id = $request->id;
        $employeeAttendance = EmployeeAttendance::find($attendance_id);
        $total_break = (isset($request->total_break) && !empty($request->total_break) && $request->total_break> 0 ?  $this->convertStringToMinute($request->total_break) : 0);
        $breaks = (isset($request->breaks) && !empty($request->breaks) ? $request->breaks : []);
        $newBreaks = (isset($request->newBreaks) && !empty($request->newBreaks) ? $request->newBreaks : []);
        //return response()->json($breaks);

        $grand_total = 0;
        if(!empty($breaks)):
            foreach($breaks as $attendance_id => $break):
                foreach($break as $break_id => $brk):
                    $total = (isset($brk['total']) && !empty($brk['total']) ? $this->convertStringToMinute($brk['total']) : 0);
                    $grand_total += $total;

                    $data = [];
                    $data['start'] = (isset($brk['start']) && !empty($brk['start']) ? $brk['start'] : '00:00');
                    $data['end'] = (isset($brk['end']) && !empty($brk['end']) ? $brk['end'] : '00:00');
                    $data['total'] = $total;
                    $data['updated_by'] = auth()->user()->id;

                    EmployeeAttendanceDayBreak::where('id', $break_id)->update($data);
                endforeach;
            endforeach;
        elseif(!empty($newBreaks)):
            $brk = (isset($newBreaks[$attendance_id]) && !empty($newBreaks[$attendance_id]) ? $newBreaks[$attendance_id] : []);
            if(!empty($brk)):
                $total = (isset($brk['total']) && !empty($brk['total']) ? $this->convertStringToMinute($brk['total']) : 0);
                $grand_total += $total;

                $data = [];
                $data['employee_attendance_id'] = $attendance_id;
                $data['employee_id'] = $employeeAttendance->employee_id;
                $data['date'] = (isset($employeeAttendance->date) && !empty($employeeAttendance->date) ? date('Y-m-d', strtotime($employeeAttendance->date)) : null);
                $data['start'] = (isset($brk['start']) && !empty($brk['start']) ? $brk['start'] : '00:00');
                $data['end'] = (isset($brk['end']) && !empty($brk['end']) ? $brk['end'] : '00:00');
                $data['total'] = $total;
                $data['created_by'] = auth()->user()->id;

                EmployeeAttendanceDayBreak::create($data);
            endif;
        endif;
        $actualBreakTaken = ($total_break == $grand_total ? $total_break : $grand_total);

        $isses_field = (isset($employeeAttendance->isses_field) && !empty($employeeAttendance->isses_field) ? unserialize(base64_decode($employeeAttendance->isses_field)) : []);
        $user_issues = (isset($employeeAttendance->user_issues) && $employeeAttendance->user_issues > 0 ? $employeeAttendance->user_issues : 0);
        $break_issue = (isset($isses_field['break_issue']) && $isses_field['break_issue'] == 1) ? 1 : 0;
        if($user_issues > 0 && $break_issue == 1):
            $user_issues -= 1;
            unset($isses_field['break_issue']);
        endif;

        $total_break = (isset($employeeAttendance->total_break) && $employeeAttendance->total_break > 0 ? $employeeAttendance->total_break : 0);
        $total_work_hour = (isset($employeeAttendance->total_work_hour) && $employeeAttendance->total_work_hour > 0 ? $employeeAttendance->tottotal_work_hourl_break : 0);
        
        $paid_break = (!empty($employeeAttendance->paid_break) ? $this->convertStringToMinute($employeeAttendance->paid_break) : 0);
        $unpaid_break = (!empty($employeeAttendance->unpadi_break) ? $this->convertStringToMinute($employeeAttendance->unpadi_break) : 0);
        $allowedBreak = ($paid_break + $unpaid_break);

        $data = [];                            
        if($actualBreakTaken > $allowedBreak){
            $deduct = ($actualBreakTaken - $allowedBreak);
            $new_total_work_hour = ($total_work_hour - $unpaid_break) - $deduct;
            $total_work_hour = ($new_total_work_hour > 0 ? $new_total_work_hour : $total_work_hour);

            $data['total_work_hour'] = $total_work_hour;
            $data['total_break'] = $actualBreakTaken;
            $data['break_details_html'] = '';
        }else{
            $data['total_break'] = $actualBreakTaken;
            $data['break_details_html'] = '';
        }
        $data['user_issues'] = $user_issues;
        $data['isses_field'] = base64_encode(serialize($isses_field));

        EmployeeAttendance::where('id', $attendance_id)->update($data); 
        
        return response()->json(['res' => $isses_field], 200);
    }


    public function destroy(Request $request){
        $theDate = (isset($request->theDate) && !empty($request->theDate) ? date('Y-m-d', strtotime($request->theDate)) : '');
        if(!empty($theDate)):
            $leaveDayIds = EmployeeAttendance::where('date', $theDate)->pluck('employee_leave_day_id')->unique()->toArray();
            $empAttendance = EmployeeAttendance::where('date', $theDate)->forceDelete();
            if(!empty($leaveDayIds)):
                $leaveDays = EmployeeLeaveDay::whereIn('id', $leaveDayIds)->update(['is_taken' => 0]);
            endif;
            return response()->json(['suc' => 1, 'msg' => 'Employee attendance of <strong>'.date('jS F, Y').'</strong> successfully deleted.'], 200);
        else:
            return response()->json(['suc' => 2, 'msg' => 'Something went wrong. Please try later.'], 200);
        endif;
    }

    public function reSyncronise(Request $request){
        $employee_id = $request->employee_id;
        $the_date = date('Y-m-d', strtotime($request->the_date));

        $empLeaveDay = EmployeeLeaveDay::where('leave_date', $the_date)->where('was_absent_day', 1)
                        ->whereHas('leave', function($q) use($employee_id, $the_date){
                            $q->where('employee_id', $employee_id)->where('from_date', $the_date)->where('to_date', $the_date)
                                ->whereIn('leave_type', [2, 3, 4, 5]);
                        })->get()->first();
        if(isset($empLeaveDay->id) && $empLeaveDay->id > 0):
            $leave_day_id = $empLeaveDay->id;
            $leave_id = $empLeaveDay->leave->id;
            EmployeeLeaveDay::where('id', $leave_day_id)->forceDelete();
            EmployeeLeave::where('id', $leave_id)->forceDelete();
        endif;

        $deleteAttendance = EmployeeAttendance::where('employee_id', $employee_id)->where('date', $the_date)->forceDelete();
        $syncronised = $this->syncroniseAttendanceData($the_date, $employee_id);

        return response()->json(['res' => 1], 200);
    }
}
