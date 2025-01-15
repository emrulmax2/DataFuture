<?php

namespace App\Http\Controllers\HR\Reports;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeHolidayAdjustment;
use App\Models\EmployeeLeave;
use App\Models\EmployeePaymentSetting;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use DateTime;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OutstandingHolidayReportController extends Controller
{
    public function index(Request $request){
        return view('pages.hr.portal.reports.outstanding-holiday', [
            'title' => 'HR Portal - London Churchill College',
            'breadcrumbs' => [
                ['label' => 'Reports', 'href' => route('hr.portal.employment.reports.show')],
                ['label' => 'Outstanding Holiday Reports', 'href' => 'javascript:void(0);']
            ],
            'holiday_years' => HrHolidayYear::OrderBy('start_date', 'DESC')->get()
        ]);
    }

    public function list(Request $request){
        $holiday_year_id = (isset($request->holiday_year_id) && $request->holiday_year_id > 0 ? $request->holiday_year_id : 0);
        $holiday_year = HrHolidayYear::find($holiday_year_id);
        $from_date = (isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : '');
        $from_date = (empty($from_date) || $from_date < $holiday_year->start_date ? date('Y-m-d', strtotime($holiday_year->start_date)) : $from_date);
        $to_date = (isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : '');
        $to_date = (empty($to_date) || $to_date > $holiday_year->end_date ? date('Y-m-d', strtotime($holiday_year->end_date)) : $to_date);
        $status = (isset($request->status) ? $request->status : 2);

        $endMonth = new DateTime($to_date); 
        $startMonth = new DateTime($from_date);                                  
        $Months = $startMonth->diff($endMonth); 
        $numberOfMonths = (($Months->y) * 12) + ($Months->m);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'first_name', 'dir' => 'ASC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $query = Employee::orderByRaw(implode(',', $sorts));
        if($status != 2):
            $query->where('status', $status);
        endif;

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
                $activePattern = $this->employeePossibleActivePattern($list->id, $holiday_year_id);
                if($activePattern > 0):
                    $pattern = EmployeeWorkingPattern::find($activePattern);
                    $effectiveForm = (isset($pattern->effective_from) && ($pattern->effective_from != '' && $pattern->effective_from != '0000-00-00') ? date('Y-m-d', strtotime($pattern->effective_from)) : '');
                    $endTo = (isset($pattern->end_to) && ($pattern->end_to != '' && $pattern->end_to != '0000-00-00') ? date('Y-m-d', strtotime($pattern->end_to)) : '');

                    if($effectiveForm < $to_date && ($endTo == '' || $endTo > $from_date)):
                        $psd = ($from_date < $effectiveForm && ($effectiveForm <= $to_date || $effectiveForm <= date('Y-m-d')) ? $effectiveForm : $from_date);
                        $ped = (($endTo != '' && $endTo != '0000-00-00') && $endTo < $to_date ? $endTo : $to_date);

                        $holidayEntitlement = $this->employeeHolidayEntitlement($list->id, $holiday_year_id, $activePattern, $psd, $ped);
                        $adjustmentRow = $this->employeeHolidayAdjustment($list->id, $holiday_year_id, $activePattern);
                        $adjustmentHour = (isset($adjustmentRow['hours']) && $adjustmentRow['hours'] > 0 ? $adjustmentRow['hours'] : 0);
                        $adjustmentOpt = (isset($adjustmentRow['opt']) && $adjustmentRow['opt'] > 0 ? $adjustmentRow['opt'] : 1);
                        $totalHolidayEntitlement = ($adjustmentOpt == 2 ? ($holidayEntitlement - $adjustmentHour) : ($holidayEntitlement + $adjustmentHour));

                        $autoBookedBankHoliday = $this->employeeAutoBookedBankHoliday($list->id, $holiday_year_id, $pattern->id, $psd, $ped);
                        $bankHolidayTotal = (isset($autoBookedBankHoliday['bank_holiday_total']) && $autoBookedBankHoliday['bank_holiday_total'] > 0 ? $autoBookedBankHoliday['bank_holiday_total'] : 0);
                        $leaveHourse = $this->employeeExistingLeaveHours($list->id, $holiday_year_id, $activePattern, $psd, $ped);
                        $takenOnly = (isset($leaveHourse['taken']) && $leaveHourse['taken'] > 0 ? $leaveHourse['taken'] : 0);
                        $bookedOnly = (isset($leaveHourse['booked']) && $leaveHourse['booked'] > 0 ? $leaveHourse['booked'] : 0);
                        $totalTaken = $takenOnly + $bookedOnly + $bankHolidayTotal;

                        $balanceHour = $totalHolidayEntitlement - $totalTaken;

                        $activePay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $activePattern)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                        $payRate = (isset($activePay->hourly_rate) && $activePay->hourly_rate > 0 ? $activePay->hourly_rate : 0);
                        $balanceAmount = ($balanceHour > 0 ? '£'.number_format(($balanceHour / 60) * $payRate, 2) : ($balanceHour < 1 && $balanceHour != 0 ? '-£'.number_format((str_replace('-', '', $balanceHour) / 60) * $payRate, 2) : '£0.00'));
                        $data[] = [
                            'id' => $list->id,
                            'sl' => $i,
                            'full_name' => $list->full_name,
                            'department' => isset($list->employment->department->name) && !empty($list->employment->department->name) ? $list->employment->department->name : '',
                            'allocation' => $this->calculateHourMinute($totalHolidayEntitlement),
                            'taken' => $this->calculateHourMinute($totalTaken),
                            'balance_hour' => ($balanceHour > 0 ? $this->calculateHourMinute($balanceHour) : ($balanceHour < 1 && $balanceHour != 0 ? '-'.$this->calculateHourMinute(str_replace('-', '', $balanceHour)) : '00:00')),
                            'hourly_rate' => '£'.number_format($payRate, 2),
                            'balance_amount' => $balanceAmount,
                            'status' => ($list->status == 1 ? 'Active' : 'Inactive'),
                        ];
                        $i++;
                    endif;
                endif;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function export(Request $request){
        $holiday_year_id = (isset($request->holiday_year_id) && $request->holiday_year_id > 0 ? $request->holiday_year_id : 0);
        $holiday_year = HrHolidayYear::find($holiday_year_id);
        $from_date = (isset($request->from_date) && !empty($request->from_date) ? date('Y-m-d', strtotime($request->from_date)) : '');
        $from_date = (empty($from_date) || $from_date < $holiday_year->start_date ? date('Y-m-d', strtotime($holiday_year->start_date)) : $from_date);
        $to_date = (isset($request->to_date) && !empty($request->to_date) ? date('Y-m-d', strtotime($request->to_date)) : '');
        $to_date = (empty($to_date) || $to_date > $holiday_year->end_date ? date('Y-m-d', strtotime($holiday_year->end_date)) : $to_date);
        $status = (isset($request->status) ? $request->status : 2);

        $endMonth = new DateTime($to_date); 
        $startMonth = new DateTime($from_date);                                  
        $Months = $startMonth->diff($endMonth); 
        $numberOfMonths = (($Months->y) * 12) + ($Months->m);

        $query = Employee::orderBy('first_name', 'ASC');
        if($status != 2):
            $query->where('status', $status);
        endif;
        $Query= $query->get();

        $row = 1;
        $theCollection = [];
        $theCollection[$row][] = "Employee";
        $theCollection[$row][] = "Department";
        $theCollection[$row][] = "Allocation";
        $theCollection[$row][] = "Taken";
        $theCollection[$row][] = "Balance Hour";
        $theCollection[$row][] = "Hourly Rate";
        $theCollection[$row][] = "Balance";
        $theCollection[$row][] = "Status";

        if($Query->count() > 0):
            $row = 2;
            foreach($Query as $list):
                $activePattern = $this->employeePossibleActivePattern($list->id, $holiday_year_id);
                if($activePattern > 0):
                    $pattern = EmployeeWorkingPattern::find($activePattern);
                    $effectiveForm = (isset($pattern->effective_from) && ($pattern->effective_from != '' && $pattern->effective_from != '0000-00-00') ? date('Y-m-d', strtotime($pattern->effective_from)) : '');
                    $endTo = (isset($pattern->end_to) && ($pattern->end_to != '' && $pattern->end_to != '0000-00-00') ? date('Y-m-d', strtotime($pattern->end_to)) : '');

                    if($effectiveForm < $to_date && ($endTo == '' || $endTo > $from_date)):
                        $psd = ($from_date < $effectiveForm && ($effectiveForm <= $to_date || $effectiveForm <= date('Y-m-d')) ? $effectiveForm : $from_date);
                        $ped = (($endTo != '' && $endTo != '0000-00-00') && $endTo < $to_date ? $endTo : $to_date);

                        $holidayEntitlement = $this->employeeHolidayEntitlement($list->id, $holiday_year_id, $activePattern, $psd, $ped);
                        $adjustmentRow = $this->employeeHolidayAdjustment($list->id, $holiday_year_id, $activePattern);
                        $adjustmentHour = (isset($adjustmentRow['hours']) && $adjustmentRow['hours'] > 0 ? $adjustmentRow['hours'] : 0);
                        $adjustmentOpt = (isset($adjustmentRow['opt']) && $adjustmentRow['opt'] > 0 ? $adjustmentRow['opt'] : 1);
                        $totalHolidayEntitlement = ($adjustmentOpt == 2 ? ($holidayEntitlement - $adjustmentHour) : ($holidayEntitlement + $adjustmentHour));

                        $autoBookedBankHoliday = $this->employeeAutoBookedBankHoliday($list->id, $holiday_year_id, $pattern->id, $psd, $ped);
                        $bankHolidayTotal = (isset($autoBookedBankHoliday['bank_holiday_total']) && $autoBookedBankHoliday['bank_holiday_total'] > 0 ? $autoBookedBankHoliday['bank_holiday_total'] : 0);
                        $leaveHourse = $this->employeeExistingLeaveHours($list->id, $holiday_year_id, $activePattern, $psd, $ped);
                        $takenOnly = (isset($leaveHourse['taken']) && $leaveHourse['taken'] > 0 ? $leaveHourse['taken'] : 0);
                        $bookedOnly = (isset($leaveHourse['booked']) && $leaveHourse['booked'] > 0 ? $leaveHourse['booked'] : 0);
                        $totalTaken = $takenOnly + $bookedOnly + $bankHolidayTotal;

                        $balanceHour = $totalHolidayEntitlement - $totalTaken;

                        $activePay = EmployeeWorkingPatternPay::where('employee_working_pattern_id', $activePattern)->where('active', 1)->orderBy('id', 'DESC')->get()->first();
                        $payRate = (isset($activePay->hourly_rate) && $activePay->hourly_rate > 0 ? $activePay->hourly_rate : 0);
                        $balanceAmount = ($balanceHour > 0 ? '£'.number_format(($balanceHour / 60) * $payRate, 2) : ($balanceHour < 1 && $balanceHour != 0 ? '-£'.number_format((str_replace('-', '', $balanceHour) / 60) * $payRate, 2) : '£0.00'));
                        
                        $theCollection[$row][] = $list->full_name;
                        $theCollection[$row][] = (isset($list->employment->department->name) && !empty($list->employment->department->name) ? $list->employment->department->name : '');
                        $theCollection[$row][] = $this->calculateHourMinute($totalHolidayEntitlement);
                        $theCollection[$row][] = $this->calculateHourMinute($totalTaken);
                        $theCollection[$row][] = ($balanceHour > 0 ? $this->calculateHourMinute($balanceHour) : ($balanceHour < 1 && $balanceHour != 0 ? '-'.$this->calculateHourMinute(str_replace('-', '', $balanceHour)) : '00:00'));
                        $theCollection[$row][] = number_format($payRate, 2);
                        $theCollection[$row][] = $balanceAmount;
                        $theCollection[$row][] = ($list->status == 1 ? 'Active' : 'Inactive');

                        $row++;
                    endif;
                endif;
            endforeach;
        endif;

        $report_title = 'Outstanding_Holiday_Report.xlsx';
        return Excel::download(new ArrayCollectionExport($theCollection), $report_title);
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
