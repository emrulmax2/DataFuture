<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeHolidayAdjustmentRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\ComonSmtp;
use App\Models\Employee;
use App\Models\EmployeeHolidayAdjustment;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeePaymentSetting;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Employment;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use App\Models\Option;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeHolidayController extends Controller
{
    public function index($id){
        $today = date('Y-m-d');
        $employee = Employee::find($id);
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id",$id)->get()->first();
        $hrHolidayYear = HrHolidayYear::where('active', 1)->where('start_date', '<=', $today)->where('end_date', '>=', $today)->orderBy('start_date', 'ASC')->get()->first();
        
        $empExistingLeaveDates = $this->employeeExistingLeaveDates($id);
        $empBankHolidayDates = $this->employeeBankHolidayDates($id);
        $empLeaveDisableDates = array_merge($empBankHolidayDates, $empExistingLeaveDates);
        $empLeaveDisableDates = (!empty($empLeaveDisableDates) ? implode(',', $empLeaveDisableDates) : '');

        $activePattern = $this->employeePossibleActivePattern($id);
        $empLeaveDisableDays = $this->employeeNonWorkingDays($id, $activePattern);
        $empLeaveDisableDays = (!empty($empLeaveDisableDays) ? implode(',', $empLeaveDisableDays) : '');

        $can_auth = false;
        if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0):
            foreach($employee->holidayAuth as $empAuth):
                if($empAuth->user_id == auth()->user()->id):
                    $can_auth = true;
                endif;
            endforeach;
        endif;

        return view('pages.employee.profile.holiday', [
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Employee Holidays', 'href' => 'javascript:void(0);']
            ],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            'holidayDetails' => $this->employeeHolidayDetails($id),
            'holidayStatistics' => $this->employeeLeaveStatistics($id),
            'holidayYears' => HrHolidayYear::where('active', 1)->orderBy('start_date', 'DESC')->get(),
            'empPatterns' => EmployeeWorkingPattern::where('employee_id', $id)->where('active', 1)->where(
                    function($query) use ($today){
                        $query->whereNull('end_to')->orWhere('end_to', '>=', $today);
                    })->where('effective_from', '<=', $today)->where('active', 1)->orderBy('effective_from', 'ASC')->get(),
            'activePattern' => $this->employeePossibleActivePattern($id),
            'leaveOptionTypes' => $this->employeeLeaveOptionTypes($id),
            'calendarOptions' => [
                'startDate' => $this->employeeLeaveStartDate($id, 0, 0),
                'endDate' => (isset($hrHolidayYear->end_date) && !empty($hrHolidayYear->end_date) ? date('Y-m-d', strtotime($hrHolidayYear->end_date)) : 'unknown'),
                'disableDates' => $empLeaveDisableDates,
                'disableDays' => $empLeaveDisableDays,
            ],
            'can_auth' => $can_auth
        ]);
    }

    protected function employeeHolidayDetails($employee_id){
        $response = [];
        $employment = Employment::where('employee_id', $employee_id)->get()->first();

        $holidayYears = HrHolidayYear::where('active', 1)->orderBy('start_date', 'DESC')->get();
        if(!empty($holidayYears) && $holidayYears->count() > 0):
            foreach($holidayYears as $year):
                $yearStart = date('Y-m-d', strtotime($year->start_date));
                $yearEnd = date('Y-m-d', strtotime($year->end_date));
                
                $hrEmployeePatterns = EmployeeWorkingPattern::where('employee_id', $employee_id)->orderBy('id', 'ASC')->get();
                $empPatterms = [];
                if(!empty($hrEmployeePatterns)):
                    foreach($hrEmployeePatterns as $pattern):
                        $effective_from = (isset($pattern->effective_from) && !empty($pattern->effective_from) & $pattern->effective_from != '0000-00-00' ? date('Y-m-d', strtotime($pattern->effective_from)) : '');
                        $end_to = (isset($pattern->end_to) && !empty($pattern->end_to) & $pattern->end_to != '0000-00-00' ? date('Y-m-d', strtotime($pattern->end_to)) : '');
                        //dd($yearStart.' - '.$yearEnd.' - '.$effective_from.' '.$end_to);
                        if(
                            ((!empty($end_to) && $end_to > $yearStart && ($end_to <= $yearEnd || $end_to >= $yearEnd)) && ($effective_from < $yearStart || ($effective_from > $yearStart && $effective_from < $yearEnd)))
                            || 
                            ($end_to != '' && $effective_from < $yearStart && $end_to > $yearEnd) 
                            || 
                            ($end_to == '' && $effective_from < $yearEnd)
                        ):
                            $psd = ($yearStart < $effective_from && $effective_from <= date('Y-m-d') ? $effective_from : $yearStart);
                            $ped = (($end_to != '' && $end_to != '0000-00-00') && $end_to < $yearEnd ? $end_to : $yearEnd);
                            $pattern['pattern_start'] = $psd;
                            $pattern['pattern_end'] = $ped;
                            
                            
                            $holidayEntitlement = $this->employeeHolidayEntitlement($employee_id, $year->id, $pattern->id, $psd, $ped);
                            $pattern['holidayEntitlement'] = $this->calculateHourMinute($holidayEntitlement);

                            $adjustmentRow = $this->employeeHolidayAdjustment($employee_id, $year->id, $pattern->id);
                            $adjustmentHour = (isset($adjustmentRow['hours']) && $adjustmentRow['hours'] > 0 ? $adjustmentRow['hours'] : 0);
                            $adjustmentOpt = (isset($adjustmentRow['opt']) && $adjustmentRow['opt'] > 0 ? $adjustmentRow['opt'] : 1);
                            $pattern['adjustmentHtml'] = (isset($adjustmentRow['opt']) && $adjustmentRow['opt'] == 1 ? '+' : '-');
                            $pattern['adjustmentHtml'] .= $this->calculateHourMinute($adjustmentHour);

                            $totalHolidayEntitlement = ($adjustmentOpt == 2 ? ($holidayEntitlement - $adjustmentHour) : ($holidayEntitlement + $adjustmentHour));
                            $pattern['totalHolidayEntitlement'] = $this->calculateHourMinute($totalHolidayEntitlement);

                            $autoBookedBankHoliday = $this->employeeAutoBookedBankHoliday($employee_id, $year->id, $pattern->id, $psd, $ped);
                            $pattern['autoBookedBankHoliday'] = (isset($autoBookedBankHoliday['bank_holiday_total']) ? $this->calculateHourMinute($autoBookedBankHoliday['bank_holiday_total']) : '00:00');
                            $pattern['bankHolidays'] = (isset($autoBookedBankHoliday['bank_holidays']) && !empty($autoBookedBankHoliday['bank_holidays']) ? $autoBookedBankHoliday['bank_holidays'] : []);
                            $pattern['requestedLeaves'] = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year->id)
                                                          ->where('employee_working_pattern_id', $pattern->id)
                                                          ->where('status', 'Pending')
                                                          ->where('to_date', '<=', $ped)
                                                          ->orderBy('from_date', 'ASC')
                                                          ->get();
                            $pattern['approvedLeaves'] = $this->employeesApprovedLeaves($employee_id, $year->id, $pattern->id);
                            $pattern['takenLeaves'] = $this->employeesTakenLeaves($employee_id, $year->id, $pattern->id);
                            $pattern['rejectedLeaves'] = $this->employeesRejectedLeaves($employee_id, $year->id, $pattern->id);
                            $empPatterms[] = $pattern;
                        endif;
                    endforeach;
                    if(!empty($empPatterms)):
                        $response[$year->id]['is_active'] = (date('Y-m-d') >= $yearStart && date('Y-m-d') <= $yearEnd ? 1 : 0);
                        $response[$year->id]['start'] = $yearStart;
                        $response[$year->id]['end'] = $yearEnd;
                        $response[$year->id]['patterns'] = $empPatterms;
                    endif;
                endif;

            endforeach;
        endif;

        return $response;
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

    public function employeeLeaveStatistics($employee_id, $year_id = 0, $pattern_id = 0){
        $today = date('Y-m-d');

        if($year_id > 0):
            $holidayYear = HrHolidayYear::find($year_id);
        else:
            $holidayYear = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        endif;
        $year_id = (isset($holidayYear->id) && $holidayYear->id > 0 ? $holidayYear->id : $year_id);
        $year_start = (isset($holidayYear->start_date) && !empty($holidayYear->start_date) ? date('Y-m-d', strtotime($holidayYear->start_date)) : '');
        $year_end = (isset($holidayYear->end_date) && !empty($holidayYear->end_date) ? date('Y-m-d', strtotime($holidayYear->end_date)) : '');

        if($pattern_id == 0):
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

            $html = '';
            if($bank_holiday > $balance){
                $balance_left = ($bank_holiday - $balance);
                $balance_left = '-'.$this->calculateHourMinute($balance_left);
            }else{
                $balance_left = ($balance - $bank_holiday);
                $balance_left = $this->calculateHourMinute($balance_left);
            }

            
            $html .= '<div class="grid grid-cols-12 gap-4 mb-3">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Opening This Year</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$this->calculateHourMinute($openingBalance).'</div>';
            $html .= '</div>';
            $html .= '<div class="grid grid-cols-12 gap-4 mb-3">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Bank holiday auto book</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$this->calculateHourMinute($bank_holiday).'</div>';
            $html .= '</div>';
            $html .= '<div class="grid grid-cols-12 gap-4 mb-3">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Taken</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$this->calculateHourMinute($leaveTaken).'</div>';
            $html .= '</div>';
            $html .= '<div class="grid grid-cols-12 gap-4 mb-3">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Booked</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$this->calculateHourMinute($leaveBooked).'</div>';
            $html .= '</div>';
            $html .= '<div class="grid grid-cols-12 gap-4 mb-3">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Requested</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$this->calculateHourMinute($leaveRequested).'</div>';
            $html .= '</div>';
            $html .= '<div class="grid grid-cols-12 gap-4">';
                $html .= '<div class="col-span-6 text-slate-500 font-medium">Left This Year</div>';
                $html .= '<div class="col-span-6 text-right font-medium">'.$balance_left.'</div>';
            $html .= '</div>';
        else:
            $html = '';
            $html .= '<div class="grid grid-cols-12 gap-4">';
                $html .= '<div class="col-span-12">';
                    $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                        $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Working pattern not found!';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
        endif;

        return $html;
    }

    public function employeePossibleActivePattern($employee_id, $year_id = 0){
        $today = date('Y-m-d');
        $activeHolidayYearId = $year_id;

        if($activeHolidayYearId > 0):
            $hrHolidayYear = HrHolidayYear::find($activeHolidayYearId);
        else:
            $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        endif;
        
        $start = (isset($hrHolidayYear->start_date) && $hrHolidayYear->start_date != '' ? date('Y-m-d', strtotime($hrHolidayYear->start_date)) : '');
        $end = (isset($hrHolidayYear->end_date) && $hrHolidayYear->end_date != '' ? date('Y-m-d', strtotime($hrHolidayYear->end_date)) : '');

        $pattern = 0;
        $patternRes = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)->orderBy('id', 'DESC')->get();
        
        if(!empty($patternRes) && $patternRes->count() > 0):
            foreach($patternRes as $r):
                $effective_from = (isset($r->effective_from) && $r->effective_from != '' & $r->effective_from != '0000-00-00' ? date('Y-m-d', strtotime($r->effective_from)) : '');
                $end_to = (isset($r->end_to) && $r->end_to != '' & $r->end_to != '0000-00-00' ? date('Y-m-d', strtotime($r->end_to)) : '');
                
                if(
                    ($end_to != '' && $end_to > $start && ($end_to <= $end || $end_to >= $end)) && ($effective_from < $start || ($effective_from > $start && $effective_from < $end)) 
                    || 
                    ($end_to != '' && $effective_from < $start && $end_to > $end) 
                    || 
                    ($end_to == '' && $effective_from < $end)
                ):
                    $pattern = $r->id;
                endif;
            endforeach;
        endif;

        return $pattern;
    }

    public function employeeLeaveOptionTypes($employee_id){
        $today = date('Y-m-d');
        $hrHolidayYears = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        
        $html = '';
        $html .= '<option value="">Select Leave Type</option>';
        if(isset($hrHolidayYears->leaveOption) && $hrHolidayYears->leaveOption->count() > 0){
            foreach($hrHolidayYears->leaveOption as $lvo):
                switch ($lvo->leave_option) {
                    case 1:
                        $html .= '<option selected="selected" value="1">Holiday / Vacation</option>';
                        break;
                    case 2:
                        $html .= '<option value="2">Meeting / Training</option>';
                        break;
                    case 3:
                        $html .= '<option value="3">Sick Leave</option>';
                        break;
                    case 4:
                        $html .= '<option value="4">Authorised Unpaid</option>';
                        break;
                    case 5:
                        $html .= '<option value="5">Authorised Paid</option>';
                        break;
                }
            endforeach;
        }else{
            $html .= '<option selected="selected" value="1">Holiday / Vacation</option>';
        }

        return $html;
    }

    public function employeeLeaveStartDate($employee_id, $year_id = 0, $leave_start = 0){
        $today = date('Y-m-d');
        if($year_id > 0):
            $hrHolidayYear = HrHolidayYear::find($year_id);
        else:
            $hrHolidayYear = HrHolidayYear::where('start_date', '<=', $today)->where('end_date', '>=', $today)->where('active', 1)->get()->first();
        endif;

        if(!isset($hrHolidayYear->id)):
            return 'unknown';
        endif;

        $hrHolidayYearStart = (isset($hrHolidayYear->start_date) && !empty($hrHolidayYear->start_date) ? date('Y-m-d', strtotime($hrHolidayYear->start_date)) : '');
        $noticePeriod = (isset($hrHolidayYear->notice_period) && $hrHolidayYear->notice_period > 0 ? $hrHolidayYear->notice_period : 1);
        if(!empty($hrHolidayYearStart) && $hrHolidayYearStart < $today):
            if($noticePeriod > 0 && $leave_start == 1):
                $today = date('Y-m-d');
                $next_today = date('Y-m-d', strtotime($today. ' + '.$noticePeriod.' days'));
                if($next_today > $hrHolidayYear->end_date):
                    $today = $hrHolidayYear->end_date;
                else:
                    $today = $next_today;
                endif;
            else:
                $today = '';
            endif;
        else:
            if($leave_start == 1):
                $date1 = new DateTime($today);
                $date2 = new DateTime($hrHolidayYearStart);
                $interval = $date1->diff($date2);
                $days = $interval->days;
                if($days >= $noticePeriod):
                    $today = date('Y-m-d', strtotime($hrHolidayYearStart));
                else:
                    $newNoticePeriod = $noticePeriod - $days;
                    $today = date('Y-m-d', strtotime($hrHolidayYearStart. ' + '.$newNoticePeriod.' days'));
                endif;
            else:
                $today = date('Y-m-d', strtotime($hrHolidayYearStart));
            endif;
        endif;

        return ($today != '' ? date('Y-m-d', strtotime($today)) : $hrHolidayYearStart);
    }

    public function employeeExistingLeaveDates($employee_id, $year_id = 0, $pattern_id = 0, $current_leave = 0){
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

        $psd = (!empty($effective_from) && $year_start < $effective_from ? $effective_from : $year_start);
        $ped = (!empty($end_to) && $end_to < $year_end ? $end_to : $year_end);

        $leaveDates = [];
        $empLeaves = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                     ->where('employee_working_pattern_id', $pattern_id)
                     ->where('id', '!=', $current_leave)
                     ->where('status', '!=', 'Canceled')
                     ->where('from_date', '<=', $ped)
                     ->get();
        if(!empty($empLeaves)):
            foreach($empLeaves as $leave):
                $leaveDays = EmployeeLeaveDay::where('employee_leave_id', $leave->id)->orderBy('leave_date', 'ASC')->get();
                if(!empty($leaveDays) && $leaveDays->count() > 0):
                    foreach($leaveDays as $day):
                        if($day->status == 'Active'):
                            $leaveDates[] = date('Y-m-d', strtotime($day->leave_date));
                        endif;
                    endforeach;
                endif;
            endforeach;
        endif;

        return $leaveDates;
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
                        if($rld->status == 'Active' && ($bld->is_taken == 0 || $bld->is_taken == '')):
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

    public function employeesApprovedLeaves($employee_id, $year_id, $pattern_id){
        $employee_leave_ids = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                              ->where('employee_working_pattern_id', $pattern_id)
                              ->where('status', 'Approved')
                              ->pluck('id')->toArray();
        if(!empty($employee_leave_ids)):
            return EmployeeLeaveDay::whereIn('employee_leave_id', $employee_leave_ids)->where('status', 'Active')->where('is_taken', '!=', 1)->orderBy('leave_date', 'ASC')->get();
        else:
            return [];
        endif;
    }

    public function employeesTakenLeaves($employee_id, $year_id, $pattern_id){
        $employee_leave_ids = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                              ->where('employee_working_pattern_id', $pattern_id)
                              ->where('status', 'Approved')
                              ->pluck('id')->toArray();
        if(!empty($employee_leave_ids)):
            return EmployeeLeaveDay::whereIn('employee_leave_id', $employee_leave_ids)->where('status', 'Active')->where('is_taken', 1)->orderBy('leave_date', 'ASC')->get();
        else:
            return [];
        endif;
    }

    public function employeesRejectedLeaves($employee_id, $year_id, $pattern_id){
        $employee_leave_ids = EmployeeLeave::where('employee_id', $employee_id)->where('hr_holiday_year_id', $year_id)
                              ->where('employee_working_pattern_id', $pattern_id)
                              ->where('status', '!=', 'Pending')
                              ->pluck('id')->toArray();
        if(!empty($employee_leave_ids)):
            return EmployeeLeaveDay::whereIn('employee_leave_id', $employee_leave_ids)->where('status', 'In Active')->orderBy('leave_date', 'ASC')->get();
        else:
            return [];
        endif;
    }

    public function employeeBankHolidayDates($employee_id, $year_id = 0, $pattern_id = 0){
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

        $psd = (!empty($effective_from) && $year_start < $effective_from ? $effective_from : $year_start);
        $ped = (!empty($end_to) && $end_to < $year_end ? $end_to : $year_end);

        $bankHolidayAutoBook = $this->employeeAutoBookedBankHoliday($employee_id, $year_id, $pattern_id, $psd, $ped);
        $bankHolidays = (isset($bankHolidayAutoBook['bank_holidays']) && !empty($bankHolidayAutoBook['bank_holidays']) ? $bankHolidayAutoBook['bank_holidays'] : []);

        $dates = [];
        if(!empty($bankHolidays)):
            foreach($bankHolidays as $bh):
                if(isset($bh['start_date']) && !empty($bh['start_date'])):
                    $dates[] = date('Y-m-d', strtotime($bh['start_date']));
                endif;
            endforeach;
        endif;

        return $dates;
    }

    public function employeeNonWorkingDays($employee_id, $pattern_id = 0, $leave_type = ''){
        $nonWorkingDays = [];
        if($pattern_id == 0):
            $empWorkPattern = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
            $pattern_id = (isset($empWorkPattern->id) && $empWorkPattern->id > 0 ? $empWorkPattern->id : 0);
        endif;

        if($pattern_id > 0):
            for($i = 1; $i <= 7; $i++):
                $workDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $pattern_id)->where('day', $i)->get()->first();
                if(empty($workDay) || $workDay->count() == 0):
                    $nonWorkingDays[] = ($i == 7 ? 0 : $i);
                endif;
            endfor;
        endif;

        return $nonWorkingDays;
    }

    public function employeeAjaxLeaveStatistics(Request $request){
        $employee_id = $request->EmployeeId;
        $year_id = $request->LeaveYear;
        $pattern_id = $request->LeavePattern;
        $LeaveType = $request->LeaveType;

        $hrHolidayYear = HrHolidayYear::find($year_id);
        
        $empExistingLeaveDates = $this->employeeExistingLeaveDates($employee_id, $year_id, $pattern_id);
        $empBankHolidayDates = $this->employeeBankHolidayDates($employee_id, $year_id, $pattern_id);
        $empLeaveDisableDates = array_merge($empBankHolidayDates, $empExistingLeaveDates);
        $empLeaveDisableDates = (!empty($empLeaveDisableDates) ? implode(',', $empLeaveDisableDates) : '');

        $empLeaveDisableDays = $this->employeeNonWorkingDays($employee_id, $pattern_id, $LeaveType);
        $empLeaveDisableDays = (!empty($empLeaveDisableDays) ? implode(',', $empLeaveDisableDays) : []);

        $res = [];
        $res['statistics'] = $this->employeeLeaveStatistics($employee_id, $year_id, $pattern_id);
        $res['startDate'] = $this->employeeLeaveStartDate($employee_id, $year_id, 0);
        $res['endDate'] = (isset($hrHolidayYear->end_date) && !empty($hrHolidayYear->end_date) ? date('Y-m-d', strtotime($hrHolidayYear->end_date)) : 'unknown');
        $res['disableDates'] = $empLeaveDisableDates;
        $res['disableDays'] = $empLeaveDisableDays;

        return response()->json(['res' => $res], 200);
    }

    public function employeeAjaxLeaveLimit(Request $request){
        $LeaveTypes = [1 => 'Holiday / Vacation', 2 => 'Meeting / Training', 3 => 'Sick Leave', 4 => 'Authorised Unpaid', 5 => 'Authorised Paid'];

        $employee_id = $request->EmployeeId;
        $year_id = $request->LeaveYear;
        $pattern_id = $request->LeavePattern;
        $LeaveType = $request->LeaveType;
        $LeaveDates = $request->LeaveDates;

        $LeaveTypeName = $LeaveTypes[$LeaveType];
        $Dates = [];
        if(!empty($LeaveDates)):
            foreach($LeaveDates as $date):
                $Dates[] = date('Y-m-d', strtotime(str_replace('/', '-', $date)));
            endforeach;
        endif;

        $HrHolidayYear = HrHolidayYear::find($year_id);
        $year_start = (isset($HrHolidayYear->start_date) && !empty($HrHolidayYear->start_date) ? date('Y-m-d', strtotime($HrHolidayYear->start_date)) : '');
        $year_end = (isset($HrHolidayYear->end_date) && !empty($HrHolidayYear->end_date) ? date('Y-m-d', strtotime($HrHolidayYear->end_date)) : '');

        $empWorkingPattern = EmployeeWorkingPattern::find($pattern_id);
        $effective_from = (isset($empWorkingPattern->effective_from) && ($empWorkingPattern->effective_from != '' && $empWorkingPattern->effective_from != '0000-00-00') ? date('Y-m-d', strtotime($empWorkingPattern->effective_from)) : '');
        $end_to = (isset($empWorkingPattern->end_to) && ($empWorkingPattern->end_to != '' && $empWorkingPattern->end_to != '0000-00-00') ? date('Y-m-d', strtotime($empWorkingPattern->end_to)) : '');

        $psd = (!empty($effective_from) && $year_start < $effective_from ? $effective_from : $year_start);
        $ped = (!empty($end_to) && $end_to < $year_end ? $end_to : $year_end);


        $holiday_entitlement = $this->employeeHolidayEntitlement($employee_id, $year_id, $pattern_id, $psd, $ped);
        $adjustmentArr = $this->employeeHolidayAdjustment($employee_id, $year_id, $pattern_id);
        $adjustmentOpt = (isset($adjustmentArr['opt']) && !empty($adjustmentArr['opt']) && $adjustmentArr['opt'] > 0 ? $adjustmentArr['opt'] : 1);
        $adjustmentHour = (isset($adjustmentArr['hours']) && !empty($adjustmentArr['hours']) && $adjustmentArr['hours'] > 0 ? $adjustmentArr['hours'] : 0);
        $bankHolidayArr = $this->employeeAutoBookedBankHoliday($employee_id, $year_id, $pattern_id, $psd, $ped);
        $bank_holiday = (isset($bankHolidayArr['bank_holiday_total']) && !empty($bankHolidayArr['bank_holiday_total']) ? $bankHolidayArr['bank_holiday_total'] : 0);
        $taken_holiday = 0;

        $balance = (($holiday_entitlement - $taken_holiday) > 0 ? ($holiday_entitlement - $taken_holiday) : ($holiday_entitlement - $taken_holiday));
        if($adjustmentOpt == 1):
            $balance = $balance + $adjustmentHour;
        elseif($adjustmentOpt == 2):
            $balance = $balance - $adjustmentHour;
        endif;

        if($bank_holiday > $balance){
            $balance_left = -($bank_holiday - $balance);
        }else{
            $balance_left = ($balance - $bank_holiday);
        }


        if($balance_left > 0):
            $bookedHours = 0;
            $fractionFound = false;
            $leaveDayHtml = '';
            $i = 1;
            $dayCount = 0;
            foreach($Dates as $theDate):
                $day = date('N', strtotime($theDate));
                $day_name = ucfirst(date('D', strtotime($theDate)));

                $empPatternDetail = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $pattern_id)->where('day', $day)->get()->first();
                $todayHours = (isset($empPatternDetail->total) && !empty($empPatternDetail->total) ? $this->convertStringToMinute($empPatternDetail->total) : 0);
                
                $todayIsFraction = false;
                $todaysFractionHour = 0;
                if(($bookedHours + $todayHours) > $balance_left && $bookedHours < $balance_left ):
                    $todayIsFraction = $fractionFound =  true;
                    $todaysFractionHour = ($balance_left - $bookedHours);
                endif;

                $leaveDayHtml .= '<tr class="'.($todayIsFraction ? 'defaultFractionRow' : '').'">';
                    $leaveDayHtml .= '<td>';
                        $leaveDayHtml .= date('d-m-Y', strtotime($theDate));
                        $leaveDayHtml .= '<input type="hidden" name="leave['.$i.'][date]" value="'.date('Y-m-d', strtotime($theDate)).'"/>';
                    $leaveDayHtml .= '</td>';
                    $leaveDayHtml .= '<td>';
                        $leaveDayHtml .= '<div class="form-check m-0 justify-center">';
                            $leaveDayHtml .= '<input '.($todayIsFraction ? 'checked' : '').' name="leave['.$i.'][fraction]" id="live_fraction_'.strtotime($theDate).'" class="form-check-input m-0 fractionIndicator" type="checkbox" value="1">';
                        $leaveDayHtml .= '</div>';
                    $leaveDayHtml .= '</td>';
                    $leaveDayHtml .= '<td>';
                        $leaveDayHtml .= '<input type="text" 
                                            class="form-control w-full leaveDatesHours timeMask" 
                                            name="leave['.$i.'][hours]"
                                            '.($todayIsFraction ? '' : 'readonly').'
                                            data-daymax="'.$todayHours.'"
                                            data-maxhour="'.($todayIsFraction ? $todaysFractionHour : $todayHours).'" 
                                            value="'.($todayIsFraction ? $this->calculateHourMinute($todaysFractionHour) : $this->calculateHourMinute($todayHours)).'"/>';
                    $leaveDayHtml .= '</td>';
                $leaveDayHtml .= '</tr>';

                $bookedHours += ($todayIsFraction ? $todaysFractionHour : $todayHours);
                $dayCount += 1;
                $i++;
            endforeach;

            $res = [];
            if($bookedHours > $balance_left):
                $res['suc'] = 2;
                $res['html'] = '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops</strong> You do not have sufficient allowance to book holidays.</span></div>';
            else:
                $res['suc'] = 1;
                $html = '';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12">';
                        $html .= '<div class="notes p-5 border"><strong>Please check and confirm the following:</strong><br>'.$this->calculateHourMinute($bookedHours).' HOURS will be removed from the staff members account.</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12 sm:col-span-4">';
                        $html .= '<label class="font-medium block pt-2">Dates</label>';
                    $html .= '</div>';
                    $html .= '<div class="col-span-12 sm:col-span-8">';
                        $html .= '<table class="leaveDayTable">';
                            $html .= '<tbody>';
                                $html .= $leaveDayHtml;
                            $html .= '</tbody>';
                        $html .= '</table>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12 sm:col-span-4">';
                        $html .= '<label class="font-medium block pt-2">Days</label>';
                    $html .= '</div>';
                    $html .= '<div class="col-span-12 sm:col-span-8">';
                        $html .= '<div class="font-medium text-right">'.$dayCount.'</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12 sm:col-span-4">';
                        $html .= '<label class="font-medium block pt-2">Requested</label>';
                    $html .= '</div>';
                    $html .= '<div class="col-span-12 sm:col-span-8">';
                        $html .= '<div class="font-medium text-right requestedHours">'.$this->calculateHourMinute($bookedHours).'</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12 sm:col-span-4">';
                        $html .= '<label class="font-medium block pt-2">Allowance Left</label>';
                    $html .= '</div>';
                    $html .= '<div class="col-span-12 sm:col-span-8">';
                        $html .= '<div class="font-medium text-right balanceLeft">'.$this->calculateHourMinute(($balance_left - $bookedHours)).'</div>';
                    $html .= '</div>';
                $html .= '</div>';
                $html .= '<div class="grid grid-cols-12 gap-0 mt-5">';
                    $html .= '<div class="col-span-12">';
                        $html .= '<label class="font-medium block mb-2">Note <span class="text-danger">*</span></label>';
                    $html .= '</div>';
                    $html .= '<div class="col-span-12">';
                        $html .= '<textarea class="form-control w-full" rows="3" name="note"></textarea>';
                    $html .= '</div>';
                $html .= '</div>';

                $html .= '<input type="hidden" name="booked_hours" value="'.$bookedHours.'"/>';
                $html .= '<input type="hidden" name="booked_days" value="'.$dayCount.'"/>';
                $html .= '<input type="hidden" name="is_fraction_found" value="'.($fractionFound ? 1 : 0).'"/>';
                $html .= '<input type="hidden" name="balance_left" value="'.$balance_left.'"/>';

                $res['html'] = $html;
            endif;
        else:
            $res['suc'] = 2;
            $res['html'] = '<div class="alert alert-danger-soft show flex items-start mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span><strong>Oops</strong> You do not have sufficient allowance to book holidays.</span></div>';
        endif;

        return response()->json(['res' => $res], 200);
    }

    public function employeeLeaveSubmission(Request $request){
        $employee_id = $request->employee_id;
        $employee = Employee::find($employee_id);
        $employee_emails = [];
        if(!empty($employee->email) && !empty($employee->email)):
            $employee_emails[] = $employee->email;
        endif;
        if(!empty($employee->employment->email) && !empty($employee->employment->email)):
            $employee_emails[] = $employee->employment->email;
        endif;

        $year_id = $request->leave_holiday_years;
        $pattern_id = $request->leave_pattern;
        $leave_type = $request->leave_type;
        $booked_hours = $request->booked_hours;
        $booked_days = $request->booked_days;
        $is_fraction_found = $request->is_fraction_found;
        $balance_left = $request->balance_left;
        $note = (isset($request->note) && !empty($request->note) ? $request->note : '');
        $leaves = (isset($request->leave) && !empty($request->leave) ? $request->leave : []);

        $employeeName = (isset($employee->titlle->name) ? $employee->titlle->name.' ' : '').$employee->first_name.' '.$employee->last_name;
        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $siteName,
        ];

        $leaveData = [];
        $leaveData['employee_id'] = $employee_id;
        $leaveData['hr_holiday_year_id'] = $year_id;
        $leaveData['employee_working_pattern_id'] = $pattern_id;
        $leaveData['leave_type'] = $leave_type;
        $leaveData['note'] = $note;
        $leaveData['status'] = 'Pending';
        $leaveData['created_by'] = auth()->user()->id;

        if(!empty($leaves) && isset($commonSmtp->id) && $commonSmtp->id > 0):
            $empLeaves = EmployeeLeave::create($leaveData);
            if($empLeaves):
                $startDate = '';
                $endDate = '';
                $fractionCount = 0;
                $totalHours = 0;
                $days = 0;
                $daysHtml = '';
                $i = 1;
                foreach($leaves as $leave):
                    $leave_date = (isset($leave['date']) && !empty($leave['date']) ? date('Y-m-d', strtotime($leave['date'])) : '');
                    $isFraction = (isset($leave['fraction']) && $leave['fraction'] > 0 ? 1 : 0);
                    $leave_hour = (isset($leave['hours']) && !empty($leave['hours']) ? $this->convertStringToMinute($leave['hours']) : 0);

                    if($i == 1): $startDate = $leave_date; endif;
                    if($i == count($leaves)): $endDate = $leave_date; endif;

                    $leaveDaysData = [];
                    $leaveDaysData['employee_leave_id'] = $empLeaves->id;
                    $leaveDaysData['leave_date'] = $leave_date;
                    $leaveDaysData['hour'] = $leave_hour;
                    $leaveDaysData['is_fraction'] = $isFraction;
                    $leaveDaysData['status'] = 'Active';
                    $leaveDaysData['created_by'] = auth()->user()->id;
                    EmployeeLeaveDay::create($leaveDaysData);

                    $totalHours += $leave_hour;
                    $daysHtml .= '<li>'.date('d-m-Y', strtotime($leave_date)).' => '.$this->calculateHourMinute($leave_hour).'</li>';
                    $fractionCount += $isFraction;
                    $days += 1;
                    $i++;
                endforeach;

                $leaveUpdateData = [];
                $leaveUpdateData['from_date'] = $startDate;
                $leaveUpdateData['to_date'] = $endDate;
                $leaveUpdateData['days'] = $days;
                $leaveUpdateData['is_fraction'] = ($fractionCount > 0 ? 1 : 0);

                EmployeeLeave::where('id', $empLeaves->id)->update($leaveUpdateData);
                if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0):
                    foreach($employee->holidayAuth as $authUsers):
                        $approver = User::find($authUsers->user_id);
                        $approverName = (isset($approver->employee->titlle->name) ? $approver->employee->titlle->name.' ' : '');
                        $approverName .= (isset($approver->employee->first_name) ? $approver->employee->first_name.' ' : ''); 
                        $approverName .= (isset($approver->employee->last_name) ? $approver->employee->last_name.' ' : '');
                        $approverEmail = (isset($approver->employee->employment->email) && !empty($approver->employee->employment->email) ? $approver->employee->employment->email : '');
                        
                        if(!empty($approverEmail)):
                            $message = '';
                            $message .= 'Dear '.$approverName.',<br/>';
                            $message .= $employeeName.' has submitted a request for leave from '.date('d-m-Y', strtotime($startDate)).' to '.date('d-m-Y', strtotime($endDate)).' and has designated you as the approver.<br/>';
                            $message .= 'Please log in <a href="'.url('/').'">here</a> to review and approve the leave.<br/>';
                            $message .= 'Leave details are provided below for your reference.<br/><br/>';

                            $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                                $message .= '<tr>';
                                    $message .= '<th>No of Days</th>';
                                    $message .= '<td>'.$days.'Days</td>';
                                $message .= '</tr>';
                                if(!empty($daysHtml)):
                                    $message .= '<tr>';
                                        $message .= '<th>Dates</th>';
                                        $message .= '<td>'; 
                                            $message .= '<ul>'; 
                                                $message .= $daysHtml;
                                            $message .= '</ul>'; 
                                        $message .= '</td>'; 
                                    $message .= '</tr>';
                                endif;
                                $message .= '<tr>';
                                    $message .= '<th>Hours</th>';
                                    $message .= '<td>'.$this->calculateHourMinute($totalHours).'</td>';
                                $message .= '</tr>';
                                if(!empty($note)):
                                $message .= '<tr>';
                                    $message .= '<th>Notes</th>';
                                    $message .= '<td>'.$note.'</td>';
                                $message .= '</tr>';
                                endif;
                                $message .= '<tr>';
                                    $message .= '<th>Requested By</th>';
                                    $message .= '<td>'.$employeeName.' on '.date('jS F, Y H:i').'</td>';
                                $message .= '</tr>';
                            $message .= '</table><br/>';
                            $message .= 'Thank you for your attention to this matter.<br/><br/>';
                            $message .= 'Sincerely,<br/>'.$siteName;

                            UserMailerJob::dispatch($configuration, [$approverEmail], new CommunicationSendMail('Leave Request', $message, []));
                        endif;
                    endforeach;
                endif;

                if(!empty($employee_emails)):
                    $message2 = 'Dear '.$employeeName.',<br/><br/>';
                    $message2 .= 'We are writing to inform you that your leave request has been successfully submitted for review. You may monitor the status of your request by accessing the following link:<br/><br/>';
                    $message2 .= '<a href="'.url('/').'">Click Here</a><br/><br/>';
                    $message2 .= 'Thank you for your cooperation.<br/>Sincerely,<br/>'.$siteName;

                    UserMailerJob::dispatch($configuration, $employee_emails, new CommunicationSendMail('Leave Request', $message2, []));
                endif;

                return response()->json(['res' => 'Request successfully submitted'], 200);
            else:
                return response()->json(['res' => 'Something went wrong!'], 422);
            endif;
        else:
            return response()->json(['res' => 'Something went wrong!'], 422);
        endif;
    }

    public function updateAdjustment(EmployeeHolidayAdjustmentRequest $request){
        $employee_id = $request->employee_id;
        $hr_holiday_year_id = $request->hr_holiday_year_id;
        $employee_working_pattern_id = $request->employee_working_pattern_id;
        $adjustmentOpt = (isset($request->adjustmentOpt) && !empty($request->adjustmentOpt) ? $request->adjustmentOpt : 0);
        $adjustment = (isset($request->adjustment) && !empty($request->adjustment) ? $request->adjustment : 0);

        if($adjustmentOpt > 0 && !empty($adjustment) && $adjustment != '00:00'):
            $holidayAdjustment = EmployeeHolidayAdjustment::where('employee_id', $employee_id)->where('hr_holiday_year_id', $hr_holiday_year_id)
                                 ->where('employee_working_pattern_id', $employee_working_pattern_id)->get()->first();
            $data = [];
            $data['employee_id'] = $employee_id;
            $data['hr_holiday_year_id'] = $hr_holiday_year_id;
            $data['employee_working_pattern_id'] = $employee_working_pattern_id;
            $data['operator'] = $adjustmentOpt;
            $data['hours'] = $this->convertStringToMinute($adjustment);

            if(!empty($holidayAdjustment) && isset($holidayAdjustment->id) && $holidayAdjustment->id > 0):
                $data['updated_by'] = auth()->user()->id;
                EmployeeHolidayAdjustment::where('employee_id', $employee_id)->where('hr_holiday_year_id', $hr_holiday_year_id)
                                 ->where('employee_working_pattern_id', $employee_working_pattern_id)->update($data);
            else:
                $data['created_by'] = auth()->user()->id;

                EmployeeHolidayAdjustment::create($data);
            endif;

            return response()->json(['message' => 'Holiday adjustment successfully updated'], 200);
        else:
            return response()->json(['message' => 'Operator or adjustment hour missing'], 422);
        endif;

    }

    public function getEmployeeLeave(Request $request){
        $employee_leave_id = $request->employee_leave_id;

        $html = '';
        $employeeLeave = EmployeeLeave::find($employee_leave_id);
        
        $html .= '<table class="table table-bordered table-hover leaveRequestDaysTable">';
            $html .= '<thead>';
                $html .= '<tr>';
                    $html .= '<th class="whitespace-nowrap">Date</th>';
                    $html .= '<th class="whitespace-nowrap">Hour</th>';
                    $html .= '<th class="whitespace-nowrap text-center">Approve</th>';
                    $html .= '<th class="whitespace-nowrap text-center">Deny</th>';
                $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
                if(isset($employeeLeave->leaveDays) && $employeeLeave->leaveDays->count() > 0):
                    foreach($employeeLeave->leaveDays as $leaveDay):
                        $html .= '<tr>';
                            $html .= '<td>'.date('D jS F, Y', strtotime($leaveDay->leave_date)).'</td>';
                            $html .= '<td>'.$this->calculateHourMinute($leaveDay->hour).'</td>';
                            $html .= '<td>';
                                $html .= '<div class="form-check m-0 justify-center">';
                                    $html .= '<input class="form-check-input m-0" name="leaveDay['.$leaveDay->id.']" type="radio" value="Active">';
                                $html .= '</div>';
                            $html .= '</td>';
                            $html .= '<td>';
                                $html .= '<div class="form-check m-0 justify-center">';
                                    $html .= '<input class="form-check-input m-0" name="leaveDay['.$leaveDay->id.']" type="radio" value="In Active">';
                                $html .= '</div>';
                            $html .= '</td>';
                        $html .= '</tr>';
                    endforeach;
                endif;
            $html .= '</tbody>';
        $html .= '</table>';

        return response()->json(['res' => $html], 200);
    }

    public function employeeUpdateLeave(Request $request){
        $employee_leave_id = $request->employee_leave_id;
        $employeeLeave = EmployeeLeave::find($employee_leave_id);

        $employee_id = (isset($request->employee_id) && $request->employee_id > 0 ? $request->employee_id : $employeeLeave->employee_id);
        $employee = Employee::find($employee_id);
        $employee_emails = [];
        if(!empty($employee->email) && !empty($employee->email)):
            $employee_emails[] = $employee->email;
        endif;
        if(!empty($employee->employment->email) && !empty($employee->employment->email)):
            $employee_emails[] = $employee->employment->email;
        endif;

        $employeeName = (isset($employee->titlle->name) ? $employee->titlle->name.' ' : '').$employee->first_name.' '.$employee->last_name;
        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $siteName,
        ];

        $leaveDayHTML = '';
        $requestedHour = 0;
        $approvedHour = 0;
        $leaveDay = isset($request->leaveDay) && !empty($request->leaveDay) ? $request->leaveDay : [];
        $activeCount = 0;
        $inActiveCount = 0;
        foreach($leaveDay as $leaveDayId => $status):
            $employeeLeaveDay = EmployeeLeaveDay::find($leaveDayId);
            $lvUpdateData = [];
            $lvUpdateData['status'] = $status;
            $lvUpdateData['updated_by'] = auth()->user()->id;

            EmployeeLeaveDay::where('employee_leave_id', $employee_leave_id)->where('id', $leaveDayId)->update($lvUpdateData);
            $activeCount += ($status == 'Active' ? 1 : 0);
            $inActiveCount += ($status == 'In Active' ? 1 : 0);

            $requestedHour += $employeeLeaveDay->hour;
            $approvedHour += ($status == 'Active' ? $employeeLeaveDay->hour : 0);
            $leaveDayHTML .= '<tr>'; 
                $leaveDayHTML .= '<td style="padding: 5px 10px;">'.date('jS F, Y', strtotime($employeeLeaveDay->leave_date)).'</td>';
                $leaveDayHTML .= '<td style="padding: 5px 10px;">'.($status == 'Active' ? '<span style="color: green;">Approved</span>' : '<span style="color: red;">Rejected</span>').'</td>';
                $leaveDayHTML .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($employeeLeaveDay->hour).'</td>';
            $leaveDayHTML .= '</tr>';
        endforeach;

        $leaveStatus = ($activeCount > 0 ? 'Approved' : ($activeCount == 0 && $inActiveCount > 0 ? 'Canceled' : 'Pending'));
        $data = [];
        if($leaveStatus == 'Approved'):
            $data['approved_by'] = auth()->user()->id;
            $data['approved_at'] = date('Y-m-d H:i:s');
        elseif($leaveStatus == 'Canceled'):
            $data['canceled_by'] = auth()->user()->id;
            $data['canceled_at'] = date('Y-m-d H:i:s');
        endif;
        $data['status'] = $leaveStatus;
        $data['updated_by'] = auth()->user()->id;

        EmployeeLeave::where('id', $employee_leave_id)->update($data);
        
        $currentUserName = (isset(Auth::user()->load('employee')->employee->full_name) ? Auth::user()->load('employee')->employee->full_name.' ' : ''); 
        if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0):
            foreach($employee->holidayAuth as $authUsers):
                if($authUsers->user_id != auth()->user()->id):
                    $approver = User::find($authUsers->user_id);
                    $approverName = (isset($approver->employee->titlle->name) ? $approver->employee->titlle->name.' ' : '');
                    $approverName .= (isset($approver->employee->first_name) ? $approver->employee->first_name.' ' : ''); 
                    $approverName .= (isset($approver->employee->last_name) ? $approver->employee->last_name.' ' : '');
                    $approverEmail = (isset($approver->employee->employment->email) && !empty($approver->employee->employment->email) ? $approver->employee->employment->email : '');

                    if(!empty($approverEmail)):
                        $message            = '';
                        $message .= 'Hi '.$approverName.'<br/><br/>';
                        $message .= 'An update regarding '.$employeeName.'\'s leave request is available.';
                        $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                            $message .= '<tr>';
                                $message .= '<td style="padding: 5px 10px;">Requested</td>';
                                $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($requestedHour).'</td>';
                            $message .= '</tr>';
                            $message .= '<tr>';
                                $message .= '<td style="padding: 5px 10px;">Approved</td>';
                                $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($approvedHour).'</td>';
                            $message .= '</tr>';
                            $message .= '<tr>';
                                $message .= '<td colspan="3" style="padding: 5px 10px;"><strong>Details</strong></td>';
                            $message .= '</tr>';
                            $message .= $leaveDayHTML;
                            $message .= '<tr>';
                                $message .= '<td colspan="3" style="padding: 5px 10px;"><strong>By '.$currentUserName.'</strong></td>';
                            $message .= '</tr>';
                        $message .= '</table><br/>';
                        $message .= 'Thank you for your cooperation.<br/><br/>';
                        $message .= 'Sincerely,<br/>'.$siteName;

                        UserMailerJob::dispatch($configuration, [$approverEmail], new CommunicationSendMail('Leave Request Reviewed', $message, []));
                    endif;
                endif;
            endforeach;
        endif;

        if(!empty($employee_emails)):
            $message = 'Hi '.$employeeName.'<br/><br/>';
            $message .= 'You have an update of your holiday request. Please <a href="'.url('/').'">login</a> on HR System for update.<br/><br/>';
            
            $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                $message .= '<tr>';
                    $message .= '<td style="padding: 5px 10px;">Requested</td>';
                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($requestedHour).'</td>';
                $message .= '</tr>';
                $message .= '<tr>';
                    $message .= '<td style="padding: 5px 10px;">Approved</td>';
                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($approvedHour).'</td>';
                $message .= '</tr>';
                $message .= '<tr>';
                    $message .= '<td colspan="3" style="padding: 5px 10px;"><strong>Details</strong></td>';
                $message .= '</tr>';
                $message .= $leaveDayHTML;
            $message .= '</table>';
            
            $message .= '<br/><br/>Thanks<br/><br/>';
            $message .= $siteName;

            UserMailerJob::dispatch($configuration, $employee_emails, new CommunicationSendMail('Leave Request Review', $message, []));
        endif;

        return response()->json(['res' => 'Leave request successfully udpated.'], 200);
    }

    public function employeeApproveLeave(Request $request){
        $emp_leave_day_id = $request->row_id;
        $empLeaveDay = EmployeeLeaveDay::find($emp_leave_day_id);
        $empLeaveId = $empLeaveDay->employee_leave_id;
        $employee_id = $empLeaveDay->leave->employee_id;
        $employee = Employee::find($employee_id);
        $employee_emails = [];
        if(!empty($employee->email) && !empty($employee->email)):
            $employee_emails[] = $employee->email;
        endif;
        if(!empty($employee->employment->email) && !empty($employee->employment->email)):
            $employee_emails[] = $employee->employment->email;
        endif;

        $employeeName = (isset($employee->titlle->name) ? $employee->titlle->name.' ' : '').$employee->first_name.' '.$employee->last_name;
        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $siteName,
        ];

        if(isset($commonSmtp->id) && $commonSmtp->id > 0):
            $lvUpdateData = [];
            $lvUpdateData['status'] = 'Active';
            $lvUpdateData['updated_by'] = auth()->user()->id;
            EmployeeLeaveDay::where('id', $emp_leave_day_id)->update($lvUpdateData);

            $data = [];
            $data['status'] = 'Approved';
            $data['updated_by'] = auth()->user()->id;

            EmployeeLeave::where('id', $empLeaveId)->update($data);

            $currentUserName = (isset(Auth::user()->load('employee')->employee->full_name) ? Auth::user()->load('employee')->employee->full_name.' ' : ''); 
            if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0):
                foreach($employee->holidayAuth as $authUsers):
                    if($authUsers->user_id != auth()->user()->id):
                        $approver = User::find($authUsers->user_id);
                        $approverName = (isset($approver->employee->titlle->name) ? $approver->employee->titlle->name.' ' : '');
                        $approverName .= (isset($approver->employee->first_name) ? $approver->employee->first_name.' ' : ''); 
                        $approverName .= (isset($approver->employee->last_name) ? $approver->employee->last_name.' ' : '');
                        $approverEmail = (isset($approver->employee->employment->email) && !empty($approver->employee->employment->email) ? $approver->employee->employment->email : '');
    
                        if(!empty($approverEmail)):
                            $message = '';
                            $message .= 'Hi '.$approverName.'<br/><br/>';
                            $message .= 'An update regarding '.$employeeName.'\'s leave request is available.';
                            $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Requested Date</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.date('jS F, Y', strtotime($empLeaveDay->leave_date)).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Requested</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Approved</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td colspan="3" style="padding: 5px 10px;"><strong>By '.$currentUserName.'</strong></td>';
                                $message .= '</tr>';
                            $message .= '</table><br/>';
                            $message .= 'Thank you for your cooperation.<br/><br/>';
                            $message .= 'Sincerely,<br/>'.$siteName;
    
                            UserMailerJob::dispatch($configuration, [$approverEmail], new CommunicationSendMail('Leave Request Single Day Approved', $message, []));
                        endif;
                    endif;
                endforeach;
            endif;

            if(!empty($employee_emails)):
                $message = 'Hi '.$employeeName.'<br/><br/>';
                $message .= 'You have an update of your holiday request. Please <a href="'.url('/').'">login</a> on HR System for update.<br/><br/>';
                
                $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Requested Date</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.date('jS F, Y', strtotime($empLeaveDay->leave_date)).'</td>';
                    $message .= '</tr>';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Requested</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                    $message .= '</tr>';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Approved</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                    $message .= '</tr>';
                $message .= '</table>';
                
                $message .= '<br/><br/>Thanks<br/><br/>';
                $message .= $siteName;

                UserMailerJob::dispatch($configuration, $employee_emails, new CommunicationSendMail('Leave Request Single Day Approved', $message, []));
            endif;

            return response()->json(['res' => 'Success'], 200);
        else: 
            return response()->json(['res' => 'Common SMTP Error'], 422);
        endif;
    }

    public function employeeRejectLeave(Request $request){
        $emp_leave_day_id = $request->row_id;
        $empLeaveDay = EmployeeLeaveDay::find($emp_leave_day_id);
        $empLeaveId = $empLeaveDay->employee_leave_id;
        $employee_id = $empLeaveDay->leave->employee_id;
        $employee = Employee::find($employee_id);
        $employee_emails = [];
        if(!empty($employee->email) && !empty($employee->email)):
            $employee_emails[] = $employee->email;
        endif;
        if(!empty($employee->employment->email) && !empty($employee->employment->email)):
            $employee_emails[] = $employee->employment->email;
        endif;

        $employeeName = (isset($employee->titlle->name) ? $employee->titlle->name.' ' : '').$employee->first_name.' '.$employee->last_name;
        $siteName = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_name')->value('value');
        $siteEmail = Option::where('category', 'SITE_SETTINGS')->where('name', 'company_email')->value('value');
        $commonSmtp = ComonSmtp::where('is_default', 1)->get()->first();
        $configuration = [
            'smtp_host'    => $commonSmtp->smtp_host,
            'smtp_port'    => $commonSmtp->smtp_port,
            'smtp_username'  => $commonSmtp->smtp_user,
            'smtp_password'  => $commonSmtp->smtp_pass,
            'smtp_encryption'  => $commonSmtp->smtp_encryption,
            
            'from_email'    => $commonSmtp->smtp_user,
            'from_name'    =>  $siteName,
        ];

        if(isset($commonSmtp->id) && $commonSmtp->id > 0):
            $lvUpdateData = [];
            $lvUpdateData['status'] = 'In Active';
            $lvUpdateData['updated_by'] = auth()->user()->id;
            EmployeeLeaveDay::where('id', $emp_leave_day_id)->update($lvUpdateData);

            $ActiveLeaveRow = EmployeeLeaveDay::where('employee_leave_id', $empLeaveId)->where('status', 'Active')->get();
            $InActiveLeaveRow = EmployeeLeaveDay::where('employee_leave_id', $empLeaveId)->where('status', 'In Active')->get();


            $data = [];
            $data['status'] = ($ActiveLeaveRow->count() > 0 ? 'Approved' : ($InActiveLeaveRow->count() > 0 ? 'Canceled' : 'Approved'));
            $data['updated_by'] = auth()->user()->id;

            EmployeeLeave::where('id', $empLeaveId)->update($data);

            $currentUserName = (isset(Auth::user()->load('employee')->employee->full_name) ? Auth::user()->load('employee')->employee->full_name.' ' : ''); 
            if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0):
                foreach($employee->holidayAuth as $authUsers):
                    if($authUsers->user_id != auth()->user()->id):
                        $approver = User::find($authUsers->user_id);
                        $approverName = (isset($approver->employee->titlle->name) ? $approver->employee->titlle->name.' ' : '');
                        $approverName .= (isset($approver->employee->first_name) ? $approver->employee->first_name.' ' : ''); 
                        $approverName .= (isset($approver->employee->last_name) ? $approver->employee->last_name.' ' : '');
                        $approverEmail = (isset($approver->employee->employment->email) && !empty($approver->employee->employment->email) ? $approver->employee->employment->email : '');
    
                        if(!empty($approverEmail)):
                            $message = '';
                            $message .= 'Hi '.$approverName.'<br/><br/>';
                            $message .= 'An update regarding '.$employeeName.'\'s leave request is available.';
                            $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Requested Date</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.date('jS F, Y', strtotime($empLeaveDay->leave_date)).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Requested</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td style="padding: 5px 10px;">Rejected</td>';
                                    $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                                    $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                                $message .= '</tr>';
                                $message .= '<tr>';
                                    $message .= '<td colspan="3" style="padding: 5px 10px;"><strong>By '.$currentUserName.'</strong></td>';
                                $message .= '</tr>';
                            $message .= '</table><br/>';
                            $message .= 'Thank you for your cooperation.<br/><br/>';
                            $message .= 'Sincerely,<br/>'.$siteName;
    
                            UserMailerJob::dispatch($configuration, [$approverEmail], new CommunicationSendMail('Leave Request Single Day Rejected', $message, []));
                        endif;
                    endif;
                endforeach;
            endif;

            if(!empty($employee_emails)):
                $message = 'Hi '.$employeeName.'<br/><br/>';
                $message .= 'You have an update of your holiday request. Please <a href="'.url('/').'">login</a> on HR System for update.<br/><br/>';
                
                $message .= '<table border="1" style="text-align: left; margin: 0 !important;">';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Requested Date</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.date('jS F, Y', strtotime($empLeaveDay->leave_date)).'</td>';
                    $message .= '</tr>';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Requested</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                    $message .= '</tr>';
                    $message .= '<tr>';
                        $message .= '<td style="padding: 5px 10px;">Rejected</td>';
                        $message .= '<td style="padding: 5px 10px;">&nbsp;</td>';
                        $message .= '<td style="padding: 5px 10px;">'.$this->calculateHourMinute($empLeaveDay->hour).'</td>';
                    $message .= '</tr>';
                $message .= '</table>';
                
                $message .= '<br/><br/>Thanks<br/><br/>';
                $message .= $siteName;

                UserMailerJob::dispatch($configuration, $employee_emails, new CommunicationSendMail('Leave Request Single Day Rejected', $message, []));
            endif;

            return response()->json(['res' => 'Success'], 200);
        else:
            return response()->json(['res' => 'Common SMTP Error'], 422);
        endif;
    }

    public function employeeCheckLeaveDayIsApproved(Request $request){
        $leave_day_id = $request->leave_day_id;
        $empLeaveDay = EmployeeLeaveDay::find($leave_day_id);
        $employee_id = $empLeaveDay->leave->employee_id;
        $leaveDate = date('Y-m-d', strtotime($empLeaveDay->leave_date));
        
        $existingLeave = EmployeeLeaveDay::where('leave_date', $leaveDate)->where('status', 'Active')
                        ->whereHas('leave', function($q) use($employee_id){
                            $q->where('employee_id', $employee_id);
                        })->get();
        if($existingLeave->count() > 0){
            return response()->json(['res' => 1], 200);
        }else{
            return response()->json(['res' => 0], 200);
        }
    }
}
