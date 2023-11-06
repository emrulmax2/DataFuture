<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeHolidayAdjustment;
use App\Models\EmployeePaymentSetting;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\Employment;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;

class UserHolidayController extends Controller
{
    public function index($id){
        $employee = Employee::find($id)->get()->first();
        $userData = User::find($employee->user_id);
        $employment = Employment::where("employee_id",$id)->get()->first();

        return view('pages.users.my-account.holiday',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "employment" => $employment,
            'holidayYears' => HrHolidayYear::orderBy('start_date', 'ASC')->get(),
            'holidayDetails' => $this->employeeHolidayDetails($id)
        ]);
    }

    protected function employeeHolidayDetails($employee_id){
        $response = [];
        $employment = Employment::where('employee_id', $employee_id)->get()->first();

        $holidayYears = HrHolidayYear::orderBy('start_date', 'DESC')->get();
        if(!empty($holidayYears)):
            foreach($holidayYears as $year):
                $yearStart = date('Y-m-d', strtotime($year->start_date));
                $yearEnd = date('Y-m-d', strtotime($year->end_date));

                $hrEmployeePatterns = EmployeeWorkingPattern::where('employee_id', $employee_id)->orderBy('id', 'ASC')->get();
                $empPatterms = [];
                if(!empty($hrEmployeePatterns)):
                    foreach($hrEmployeePatterns as $pattern):
                        $effective_from = (isset($pattern->effective_from) && !empty($pattern->effective_from) & $pattern->effective_from != '0000-00-00' ? date('Y-m-d', strtotime($pattern->effective_from)) : '');
                        $end_to = (isset($pattern->end_to) && !empty($pattern->end_to) & $pattern->end_to != '0000-00-00' ? date('Y-m-d', strtotime($pattern->end_to)) : '');

                        if(
                            ((!empty($end_to) && $end_to > $yearStart && $end_to <= $yearEnd) && ($effective_from < $yearStart || ($effective_from > $yearStart && $effective_from < $yearEnd)))
                            || 
                            ($end_to != '' && $effective_from < $yearStart && $end_to > $yearEnd) 
                            || 
                            ($end_to == '' && $effective_from < $yearEnd)
                        ):
                            $psd = ($yearStart < $pattern->effective_from ? $pattern->effective_from : $yearStart);
                            $ped = (($pattern->end_to != '' && $pattern->end_to != '0000-00-00') && $pattern->end_to < $yearEnd ? $pattern->end_to : $yearEnd);
                            
                            $holidayEntitlement = $this->employeeHolidayEntitlement($employee_id, $year->id, $pattern->id, $psd, $ped);
                            $pattern['holidayEntitlement'] = $this->calculateHourMinute($holidayEntitlement);

                            $adjustmentRow = $this->employeeHolidayAdjustment($employee_id, $year->id, $pattern->id);
                            $adjustmentHour = (isset($adjustmentRow['hours']) && $adjustmentRow['hours'] > 0 ? $adjustmentRow['hours'] : 0);
                            $pattern['adjustmentHtml'] = (isset($adjustmentRow['opt']) && $adjustmentRow['opt'] == 1 ? '+' : '-');
                            $pattern['adjustmentHtml'] .= $this->calculateHourMinute($adjustmentHour);

                            $pattern['totalHolidayEntitlement'] = $this->calculateHourMinute(($holidayEntitlement + $adjustmentHour));

                            $autoBookedBankHoliday = $this->employeeAutoBookedBankHoliday($employee_id, $year->id, $pattern->id, $psd, $ped);
                            $pattern['autoBookedBankHoliday'] = (isset($autoBookedBankHoliday['bank_holiday_total']) ? $this->calculateHourMinute($autoBookedBankHoliday['bank_holiday_total']) : '00:00');
                            $pattern['bankHolidays'] = (isset($autoBookedBankHoliday['bank_holidays']) && !empty($autoBookedBankHoliday['bank_holidays']) ? $autoBookedBankHoliday['bank_holidays'] : []);
                            $empPatterms[] = $pattern;
                        endif;
                    endforeach;
                    if(!empty($empPatterms)):
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
        $yearStart = date('Y-m-d', strtotime($year->start_date));
        $yearEnd = date('Y-m-d', strtotime($year->end_date));

        $PaymentSettings = EmployeePaymentSetting::where('employee_id', $employee_id)->get()->first();
        $bank_holiday_auto_book = (isset($PaymentSettings->bank_holiday_auto_book) ? $PaymentSettings->bank_holiday_auto_book : 'No');
        if($bank_holiday_auto_book == 'Yes'):
            $bankHoliday = HrBankHoliday::where('hr_holiday_year_id', $year_id)->where('start_date', '>=', $psd)
                            ->where('start_date', '<=', $ped)->orderBy('id', 'DESC')->get();

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
}
