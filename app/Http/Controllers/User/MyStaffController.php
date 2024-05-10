<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeHolidayAdjustment;
use App\Models\EmployeeHolidayAuthorisedBy;
use App\Models\EmployeeHourAuthorisedBy;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeePaymentSetting;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Employment;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use App\Models\User;
use DateTime;
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

        $hour_auth_ids = EmployeeHourAuthorisedBy::where('user_id', $employee->user_id)->pluck('employee_id')->unique()->toArray();
        $holiday_auth_ids = EmployeeHolidayAuthorisedBy::where('user_id', $employee->user_id)->pluck('employee_id')->unique()->toArray();
        $auth_emp_ids = array_unique(array_merge($hour_auth_ids, $holiday_auth_ids));

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

    public function staffsUpdateLeave(Request $request){
        $employee_leave_id = $request->employee_leave_id;
        $employeeLeave = EmployeeLeave::find($employee_leave_id);

        $employee_id = (isset($employeeLeave->employee_id) && $employeeLeave->employee_id > 0 ? $employeeLeave->employee_id : 0);
        $employee = Employee::find($employee_id);

        $leaveDay = isset($request->leaveDay) && !empty($request->leaveDay) ? $request->leaveDay : [];
        foreach($leaveDay as $leaveDayId => $status):
            $employeeLeaveDay = EmployeeLeaveDay::find($leaveDayId);
            $lvUpdateData = [];
            $lvUpdateData['supervision_status'] = ($status == 'In Active' ? 2 : 1);
            $lvUpdateData['updated_by'] = auth()->user()->id;

            EmployeeLeaveDay::where('employee_leave_id', $employee_leave_id)->where('id', $leaveDayId)->update($lvUpdateData);
        endforeach;

        return response()->json(['res' => 'Leave request supervisor suggestion successfully udpated.'], 200);
    }


    public function myTeamHoliday(){
        $employee = Employee::where('user_id', auth()->user()->id)->get()->first();
        $employeeId = $employee->id;
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id", $employeeId)->get()->first();

        $hour_auth_ids = EmployeeHourAuthorisedBy::where('user_id', $employee->user_id)->pluck('employee_id')->unique()->toArray();
        $holiday_auth_ids = EmployeeHolidayAuthorisedBy::where('user_id', $employee->user_id)->pluck('employee_id')->unique()->toArray();
        $auth_emp_ids = array_unique(array_merge($hour_auth_ids, $holiday_auth_ids));

        $hrHolidayYear = HrHolidayYear::where('active', 1)->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->orderBy('start_date', 'ASC')->get()->first();
        $runningHolidayYear = (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0 ? $hrHolidayYear->id : 0);

        return view('pages.users.my-account.my-team', [
            'title' => 'My Team Holidays - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            'employee' => $employee,
            'employment' => $employment,
            'holiday_years' => HrHolidayYear::where('active', 1)->orderBy('end_date', 'DESC')->get(),
            'runningHolidayYear' => $runningHolidayYear,
            'teamHolidays' => $this->myStaffHolidays($auth_emp_ids, $runningHolidayYear)
        ]);
    }

    public function myStaffHolidays($employee_ids = [], $year){
        $html = '';
        if(!empty($employee_ids)):
            $html .= '<table class="table table-bordered table-sm">';
                $html .= '<thead>';
                    $html .= '<tr>';
                        $html .= '<th>Name</th>';
                        $html .= '<th>Start - End</th>';
                        $html .= '<th>Entitlement</th>';
                        $html .= '<th>Booked</th>';
                        $html .= '<th>Taken</th>';
                        $html .= '<th>Requested</th>';
                        $html .= '<th>Bank Holiday</th>';
                        $html .= '<th>Adjustment</th>';
                        $html .= '<th>Balance</th>';
                    $html .= '</tr>';
                $html .= '</thead>';
                $html .= '<tbody>';
                    foreach($employee_ids as $employee_id):
                        $employee = Employee::find($employee_id);
                        if(isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes'):
                            $stats = $this->employeeLeaveStatistics($employee_id, $year);
                            $html .= '<tr>';
                                $html .= '<td>'.$stats['pattern_id'].' - '.$employee->full_name.'</td>';
                                $html .= '<td>'.$stats['start_end'].'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($stats['opening_balance']).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($stats['booked']).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($stats['taken']).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($stats['requested']).'</td>';
                                $html .= '<td>'.$this->calculateHourMinute($stats['bank_holiday']).'</td>';
                                $html .= '<td>'.($stats['adjustment_opt'] == 2 ? '-' : '+').''.$this->calculateHourMinute($stats['adjustment']).'</td>';
                                $html .= '<td>'.$stats['balacne_left'].'</td>';
                            $html .= '</tr>';
                        endif;
                    endforeach;
                $html .= '</tbody>';
            $html .= '</table>';
        else:
            $html .= 'None';
        endif;

        return $html;
    }

    public function employeeLeaveStatistics($employee_id, $year_id = 0){
        $today = date('Y-m-d');

        if($year_id > 0):
            $holidayYear = HrHolidayYear::find($year_id);
        else:
            $holidayYear = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        endif;

        $year_id = (isset($holidayYear->id) && $holidayYear->id > 0 ? $holidayYear->id : $year_id);
        $year_start = (isset($holidayYear->start_date) && !empty($holidayYear->start_date) ? date('Y-m-d', strtotime($holidayYear->start_date)) : '');
        $year_end = (isset($holidayYear->end_date) && !empty($holidayYear->end_date) ? date('Y-m-d', strtotime($holidayYear->end_date)) : '');

        $pattern_id = 0;
        $patternRes = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)->orderBy('id', 'DESC')->get();
        if(!empty($patternRes) && $patternRes->count() > 0):
            foreach($patternRes as $ptr):
                $effective_from = (isset($ptr->effective_from) && !empty($ptr->effective_from) ? date('Y-m-d', strtotime($ptr->effective_from)) : '');
                $end_to = (isset($ptr->end_to) && !empty($ptr->end_to) ? date('Y-m-d', strtotime($ptr->end_to)) : '');
                if(
                    (($end_to != '' && $end_to > $year_start && ($end_to <= $year_end || $end_to >= $year_end)) && ($effective_from < $year_start || ($effective_from > $year_start && $effective_from < $year_end)))
                    || ($end_to != '' && $effective_from < $year_start && $end_to > $year_end)
                    || ($end_to == '' && $effective_from < $year_end)
                ):
                    $pattern_id = $ptr->id;
                endif;
            endforeach;
        endif;

        if($pattern_id > 0):
            $pattern = EmployeeWorkingPattern::find($pattern_id);
            $effective_from = (isset($pattern->effective_from) && ($pattern->effective_from != '' && $pattern->effective_from != '0000-00-00') ? date('Y-m-d', strtotime($pattern->effective_from)) : '');
            $end_to = (isset($pattern->end_to) && ($pattern->end_to != '' && $pattern->end_to != '0000-00-00') ? date('Y-m-d', strtotime($pattern->end_to)) : '');

            $sd = ($effective_from != '' && $year_start < $effective_from && $effective_from <= date('Y-m-d') ? $effective_from : $year_start);
            $ed = ($end_to != '' && $end_to < $year_end ? $end_to : $year_end);

            $holiday_entitlement = $this->employeeHolidayEntitlement($employee_id, $year_id, $pattern_id, $sd, $ed);
            $adjustmentArr = $this->employeeHolidayAdjustment($employee_id, $year_id, $pattern_id);
            $adjustmentOpt = (isset($adjustmentArr['opt']) && !empty($adjustmentArr['opt']) && $adjustmentArr['opt'] > 0 ? $adjustmentArr['opt'] : 1);
            $adjustmentHour = (isset($adjustmentArr['hours']) && !empty($adjustmentArr['hours']) && $adjustmentArr['hours'] > 0 ? $adjustmentArr['hours'] : 0);
            $bankHolidayArr = $this->employeeAutoBookedBankHoliday($employee_id, $year_id, $pattern_id, $sd, $ed);
            $bank_holiday = (isset($bankHolidayArr['bank_holiday_total']) && !empty($bankHolidayArr['bank_holiday_total']) ? $bankHolidayArr['bank_holiday_total'] : 0);
            
            $taken_holiday = $this->employeeExistingLeaveHours($employee_id, $year_id, $pattern_id, $sd, $ed);
            $leaveTaken = $taken_holiday['taken'];
            $leaveBooked = $taken_holiday['booked'];
            $leaveRequested = $taken_holiday['requested'];
            $totalTaken = $taken_holiday['totalTaken'];


            $openingBalance = $holiday_entitlement;
            $balance = (($holiday_entitlement - $totalTaken) > 0 ? ($holiday_entitlement - $totalTaken) : ($holiday_entitlement - $totalTaken));
            if($adjustmentOpt == 1):
                $balance = $balance + $adjustmentHour;
                $openingBalance = $holiday_entitlement + $adjustmentHour;
            elseif($adjustmentOpt == 2):
                $balance = $balance - $adjustmentHour;
                $openingBalance = $holiday_entitlement - $adjustmentHour;
            endif;

            if($bank_holiday > $balance){
                $balance_left = ($bank_holiday - $balance);
                $balance_left = '-'.$this->calculateHourMinute($balance_left);
            }else{
                $balance_left = ($balance - $bank_holiday);
                $balance_left = $this->calculateHourMinute($balance_left);
            }

            $taken_holiday['adjustment_opt'] = $adjustmentOpt;
            $taken_holiday['adjustment'] = $adjustmentHour;
            $taken_holiday['bank_holiday'] = $bank_holiday;
            $taken_holiday['opening_balance'] = $openingBalance;
            $taken_holiday['balacne_left'] = $balance_left;
            $taken_holiday['pattern_id'] = $pattern_id;
            $taken_holiday['start_end'] = date('d-m-Y', strtotime($effective_from)).' - '.(!empty($end_to) ? date('d-m-d', strtotime($end_to)) : date('d-m-Y', strtotime($ed)));
            
            return $taken_holiday;
        else:
            return false;
        endif;
    }

    public function employeeAutoBookedBankHoliday($employee_id, $year_id, $pattern_id, $psd, $ped){
        $bank_holiday_total = 0;
        $bank_holiday_data = [];

        $year = HrHolidayYear::find($year_id);
        $yearStart = (isset($year->start_date) && !empty($year->start_date) ? date('Y-m-d', strtotime($year->start_date)) : '');
        $yearEnd = (isset($year->end_date) && !empty($year->end_date) ? date('Y-m-d', strtotime($year->end_date)) : '');

        $PaymentSettings = EmployeePaymentSetting::where('employee_id', $employee_id)->get()->first();
        $bank_holiday_auto_book = (isset($PaymentSettings->bank_holiday_auto_book) ? $PaymentSettings->bank_holiday_auto_book : 'No');
        if($bank_holiday_auto_book == 'Yes'):
            $bankHoliday = HrBankHoliday::where('hr_holiday_year_id', $year_id)->where('start_date', '>=', $psd)
                            ->where('start_date', '<=', $ped)->orderBy('start_date', 'DESC')->get();

            if(!empty($bankHoliday) && $bankHoliday->count() > 0):
                $i = 1;
                foreach($bankHoliday as $bh):
                    $start_date = (isset($bh->start_date) && !empty($bh->start_date) ? date('Y-m-d', strtotime($bh->start_date)) : '');
                    if(!empty($start_date)):
                        $dayNumber = date('N', strtotime($start_date));
                        $dayName = ucfirst(date('D', strtotime($start_date)));

                        $dayPatterm = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $pattern_id)->where('day', $dayNumber)->get()->first();
                        if(isset($dayPatterm->total) && !empty($dayPatterm->total) && $dayPatterm->total != '00:00'):
                            $bank_holiday_total += $this->convertStringToMinute($dayPatterm->total);
                            $bank_holiday_data[$i]['name'] = $bh->name;
                            $bank_holiday_data[$i]['start_date'] = $bh->start_date;
                            $bank_holiday_data[$i]['end_date'] = $bh->end_date;
                            $bank_holiday_data[$i]['duration'] = $bh->duration;
                            $bank_holiday_data[$i]['hour'] = $dayPatterm->total;
                        endif;
                    endif;
                    $i++;
                endforeach;
            endif;
        endif;

        return ['bank_holiday_total' => $bank_holiday_total, 'bank_holidays' => $bank_holiday_data];
    }

    public function employeeHolidayEntitlement($employee_id, $year_id, $pattern_id, $psd, $ped){
        $dayPerWeek = 0;
        $hoursPerWeek = 0;
        $start_date = '';
        $end_date = '';

        $holiday_years = HrHolidayYear::find($year_id);
        $holiday_base = 5.6;
        $holiday_start = strtotime($psd);
        $holiday_end = strtotime($ped);

        $empPaySetting = EmployeePaymentSetting::where('employee_id', $employee_id)->get()->first();
        $holiday_base = (isset($empPaySetting->holiday_base) && !empty($empPaySetting->holiday_base) ? $empPaySetting->holiday_base : 5.6);
        if(!isset($empPaySetting->holiday_entitled) || $empPaySetting->holiday_entitled != 'Yes'):
            return 0;
        endif;

        $active_patterns = EmployeeWorkingPattern::find($pattern_id);
        $patternStartedDate = strtotime($active_patterns->effective_from);
        $patternEndDate = (isset($active_patterns->end_to) && $active_patterns->end_to != '' && $active_patterns->end_to != '0000-00-00') ? $active_patterns->end_to : '';

        $year_status = false;
        if($holiday_start >= $patternStartedDate):
            $start_date = $holiday_start;
            $year_status = true;
        elseif($holiday_start < $patternStartedDate):
            $start_date = $patternStartedDate;
        else:
            $start_date = $patternStartedDate;
        endif;

        if(($patternEndDate != '') && ($holiday_end <= strtotime($patternEndDate))):
            $end_date = $holiday_end;
        elseif (($patternEndDate != '') && ($holiday_end >= strtotime($patternEndDate))):
            $end_date = strtotime($patternEndDate);
        else:
            $end_date = $holiday_end;
        endif;

        $EmpWorkingPatDetails = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $pattern_id)->orderBy('day', 'ASC')->get();
        foreach($EmpWorkingPatDetails as $workDay):
            $dayPerWeek += 1;
            $hoursPerWeek += (isset($workDay->total) && !empty($workDay->total) ? $this->convertStringToMinute($workDay->total) : 0);
        endforeach;

        $dStart = date('Y-m-d', $start_date);
        $dEnd = date('Y-m-d', $end_date);
        $hoursPerWeek = $hoursPerWeek / 60;

        $fd = new DateTime($dStart);
        $ed = new DateTime($dEnd);
        $df = $fd->diff($ed);
        $years_working_days = $df->format('%a');
        $years_working_days += 1;
        $base_hours = $hoursPerWeek * $holiday_base;

        $year_status = false;
        if(!$year_status){
            $calc_hours = ($base_hours / 365) * $years_working_days;
        }else{
            $calc_hours = $base_hours;
        }

        $calc_hours = explode('.', round($calc_hours, 2));
        $holiday_hour_pattern_duration = (isset($calc_hours[0]) && $calc_hours[0] != '') ? $calc_hours[0] * 60 : 0;
        $decimal = (isset($calc_hours[1]) ? (float) "0.$calc_hours[1]" : '');
        $holiday_hour_pattern_duration += (isset($calc_hours[1]) && $calc_hours[1] != '') ? round((60 * $decimal)) : '0';
        
        return $holiday_hour_pattern_duration;
    }

    public function employeeHolidayAdjustment($employee_id, $year_id, $pattern_id){
        $holidayAdjustment = EmployeeHolidayAdjustment::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                                 ->where('employee_working_pattern_id', $pattern_id)->get()->first();
        if(!empty($holidayAdjustment) && isset($holidayAdjustment->id) && $holidayAdjustment->id > 0):
            return ['opt' => $holidayAdjustment->operator, 'hours' => $holidayAdjustment->hours];
        else:
            return ['opt' => 1, 'hours' => 0];
        endif;
    }

    public function employeeExistingLeaveHours($employee_id, $year_id = 0, $pattern_id = 0, $psd = '', $ped = ''){
        $today = date('Y-m-d');
        if($year_id == 0):
            $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        else:
            $hrHolidayYear = HrHolidayYear::find($year_id);
        endif;

        $year_id = (isset($hrHolidayYear->id) && $hrHolidayYear->id > 0 ? $hrHolidayYear->id : 0);
        $year_start = (isset($hrHolidayYear->start_date) && !empty($hrHolidayYear->start_date) ? date('Y-m-d', strtotime($hrHolidayYear->start_date)) : '');
        $year_end = (isset($hrHolidayYear->end_date) && !empty($hrHolidayYear->end_date) ? date('Y-m-d', strtotime($hrHolidayYear->end_date)) : '');

        $pattern_id = ($pattern_id > 0 ? $pattern_id : $this->employeePossibleActivePattern($employee_id, $year_id));
        $workingPattern = EmployeeWorkingPattern::find($pattern_id);
        $effective_from = (isset($workingPattern->effective_from) && ($workingPattern->effective_from != '' && $workingPattern->effective_from != '0000-00-00') ? date('Y-m-d', strtotime($workingPattern->effective_from)) : '');
        $end_to = (isset($workingPattern->end_to) && ($workingPattern->end_to != '' && $workingPattern->end_to != '0000-00-00') ? date('Y-m-d', strtotime($workingPattern->end_to)) : '');

        $psd = (!empty($psd) ? $psd : (!empty($effective_from) && $year_start < $effective_from ? $effective_from : $year_start));
        $ped = (!empty($ped) ? $ped : (!empty($end_to) && $end_to < $year_end ? $end_to : $year_end));

        $leaveTaken = 0;
        $leaveBooked = 0;
        $leaveRequested = 0;
        $bookedLeaves = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                     ->where('employee_working_pattern_id', $pattern_id)
                     ->where('status', 'Approved')
                     ->where('leave_type', 1)
                     ->where('to_date', '<=', $ped)
                     ->get();
        if(!empty($bookedLeaves)):
            foreach($bookedLeaves as $bl):
                if(isset($bl->leaveDays) && $bl->leaveDays->count() > 0):
                    foreach($bl->leaveDays as $bld):
                        if($bld->status == 'Active' && ($bld->is_taken == 0 || $bld->is_taken == '')):
                            $leaveBooked += $bld->hour;
                        endif;
                    endforeach;
                endif;
            endforeach;
            foreach($bookedLeaves as $tkn):
                if(isset($tkn->leaveDays) && $tkn->leaveDays->count() > 0):
                    foreach($tkn->leaveDays as $tknd):
                        if($tknd->status == 'Active' && $tknd->is_taken == 1):
                            $leaveTaken += $tknd->hour;
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;
        $requestedLeaves = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                     ->where('employee_working_pattern_id', $pattern_id)
                     ->where('status', 'Pending')
                     ->where('leave_type', 1)
                     ->where('to_date', '<=', $ped)
                     ->get();
        if(!empty($requestedLeaves)):
            foreach($requestedLeaves as $rl):
                if(isset($rl->leaveDays) && $rl->leaveDays->count() > 0):
                    foreach($rl->leaveDays as $rld):
                        if($rld->status == 'Active' && ($rld->is_taken == 0 || $rld->is_taken == '')):
                            $leaveRequested += $rld->hour;
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        $totalTaken = $leaveTaken + $leaveBooked + $leaveRequested;
        $res['taken'] = $leaveTaken;
        $res['booked'] = $leaveBooked;
        $res['requested'] = $leaveRequested;
        $res['totalTaken'] = $totalTaken;

        return $res;
    }
}
