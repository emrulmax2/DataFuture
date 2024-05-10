<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HrPortalAbsentUpdateRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAppraisal;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeEligibilites;
use App\Models\EmployeeHolidayAuthorisedBy;
use App\Models\EmployeeLeave;
use App\Models\EmployeeLeaveDay;
use App\Models\EmployeeWorkingPattern;
use App\Models\EmployeeWorkingPatternDetail;
use App\Models\EmployeeWorkingPatternPay;
use App\Models\Employment;
use App\Models\HrBankHoliday;
use App\Models\HrHolidayYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class EmployeePortalController extends Controller
{
    public function index(){
        $expireDate = Carbon::now()->addDays(60)->format('Y-m-d');

        return view('pages.hr.portal.index', [
            'title' => 'HR Portal - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => 'javascript:void(0);']
            ],
            'pendingLeaves' => EmployeeLeave::where('status', 'Pending')->orderBy('id', 'DESC')->skip(0)->take(5)->get(),
            'absentToday' => $this->getAbsentEmployees(date('Y-m-d')),
            'holidays' => EmployeeLeaveDay::where('leave_date', date('Y-m-d'))->where('status', 'Active')->whereHas('leave', function($query){
                              $query->where('status', 'Approved')->where('leave_type', 1);
                          })->skip(0)->limit(5)->get(),
            'passExpiry' => EmployeeEligibilites::where('document_type', 1)->where('doc_expire', '<=', $expireDate)
                            ->whereHas('employee', function($q){
                                $q->where('status', 1);
                            })->orderBy('doc_expire', 'ASC')->skip(0)->limit(5)->get(),
            'visaExpiry' => EmployeeEligibilites::where('eligible_to_work', 'Yes')->where('employee_work_permit_type_id', 3)
                            ->whereDate('workpermit_expire', '<=', $expireDate)
                            ->whereHas('employee', function($q){
                                $q->where('status', 1);
                            })->orderBy('workpermit_expire', 'ASC')->skip(0)->limit(5)->get(),
            'appraisal' => EmployeeAppraisal::where('due_on', '<=', $expireDate)->whereNull('completed_on')
                           ->whereHas('employee', function($q){
                                $q->where('status', 1);
                           })->orderBy('due_on', 'ASC')->skip(0)->limit(5)->get()
        ]);
    }

    public function getAbsentEmployees($date = ''){
        $theDate = (empty($date) ? date('Y-m-d') : $date);
        $theDay = date('D', strtotime($theDate));
        $theDayNum = date('N', strtotime($theDate));
        $time = date('H:i');
        $employees = Employee::has('activePatterns')->where('status', 1)->orderBy('first_name', 'ASC')->get();

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
                        $res[$employee_id]['the_date'] =  date('Y-m-d', strtotime($theDate));
                        $res[$employee_id]['hourMinute'] =  $patternDay->total;
                        $res[$employee_id]['minute'] =  $this->convertStringToMinute($patternDay->total);
                        $res[$employee_id]['start'] =  (isset($patternDay->start) ? $patternDay->start : '00:00');
                        $res[$employee_id]['end'] =  (isset($patternDay->end) ? $patternDay->end : '00:00');

                        $row += 1;
                    endif;
                endif;
            endif;
        endforeach;

        return $res;
    }

    public function manageHolidays(){
        return view('pages.hr.portal.manage-holidays', [
            'title' => 'HR Portal Holidays - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Manage Holidays', 'href' => 'javascript:void(0);']
            ],
            'years' => HrHolidayYear::where('active', 1)->orderBy('id', 'DESC')->get(),
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get()
        ]);
    }

    public function list(Request $request){
        $yearid = (isset($request->yearid) && $request->yearid > 0 ? $request->yearid : 0);
        $type = (isset($request->type) && !empty($request->type) ? $request->type : '');

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        if($type == 'approved'):
            $approvedLeaveIds = EmployeeLeave::where('hr_holiday_year_id', $yearid)->where('status', 'Approved')->pluck('id')->toArray();
            $query = EmployeeLeaveDay::orderBy('updated_at', 'DESC')->whereIn('employee_leave_id', $approvedLeaveIds)->where('status', 'Active');
        elseif($type == 'rejected'):
            $rejectedLeaveIds = EmployeeLeave::where('hr_holiday_year_id', $yearid)->where('status', '!=', 'Pending')->pluck('id')->toArray();
            $query = EmployeeLeaveDay::orderBy('updated_at', 'DESC')->whereIn('employee_leave_id', $rejectedLeaveIds)->where('status', 'In Active');
        else:
            $query = EmployeeLeave::where('status', 'Pending')->where('hr_holiday_year_id', $yearid);
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

        if(!empty($Query) && $type == 'approved'):
            $i = 1;
            foreach($Query as $list):
                $employeeApprover = EmployeeHolidayAuthorisedBy::where('employee_id', $list->leave->employee_id)->pluck('user_id')->unique()->toArray();
                $status = 'Approved ';
                if(isset($list->leave->leave_type) && $list->leave->leave_type > 0):
                    switch($list->leave->leave_type):
                        case(1):
                            $status .= 'Holiday / Vacation';
                            break;
                        case(2):
                            $status .= 'Unauthorised Absent';
                            break;
                        case(3):
                            $status .= 'Sick Leave';
                            break;
                        case(4):
                            $status .= 'Authorised Unpaid';
                            break;
                        case(5):
                            $status .= 'Authorised Paid';
                            break;
                        endswitch;
                    endif;
                $createdAt = (isset($list->created_at) && !empty($list->created_at) ? $list->created_at : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'url' => route('employee.holiday', $list->leave->employee_id),
                    'photo_url' => $list->leave->employee->photo_url,
                    'name' => $list->leave->employee->first_name.' '.$list->leave->employee->last_name,
                    'designation' => (isset($list->leave->employee->employment->employeeJobTitle->name) ? $list->leave->employee->employment->employeeJobTitle->name : ''),
                    'status' => $status,
                    'start_date' => date('D jS F, Y', strtotime($list->leave_date)),
                    'end_date' => date('D jS F, Y', strtotime($list->leave_date)),
                    'title' => isset($list->leave->note) && !empty($list->leave->note) ? $list->leave->note : '',
                    'hour' => $this->calculateHourMinute($list->hour),
                    'type' => 'approved',
                    'can_auth' => (!empty($employeeApprover) && in_array(auth()->user()->id, $employeeApprover) ? 1 : 0),
                    'approved_by' => (isset($list->leave->approved->employee->full_name) && !empty($list->leave->approved->employee->full_name) ? $list->leave->approved->employee->full_name : ''),
                    'approved_at' => (isset($list->leave->approved_at) && !empty($list->leave->approved_at) ? date('jS M, Y', strtotime($list->leave->approved_at)) : ''),
                    'created_at' => (!empty($createdAt) ? date('jS F, Y', strtotime($createdAt)).' ('.$list->created_at->diffForHumans().')' : '')
                ];
                $i++;
            endforeach;
        elseif(!empty($Query) && $type == 'rejected'):
            $i = 1;
            foreach($Query as $list):
                $employeeApprover = EmployeeHolidayAuthorisedBy::where('employee_id', $list->leave->employee_id)->pluck('user_id')->unique()->toArray();
                $status = 'Rejected ';
                if(isset($list->leave->leave_type) && $list->leave->leave_type > 0):
                    switch($list->leave->leave_type):
                        case(1):
                            $status .= 'Holiday / Vacation';
                            break;
                        case(2):
                            $status .= 'Unauthorised Absent';
                            break;
                        case(3):
                            $status .= 'Sick Leave';
                            break;
                        case(4):
                            $status .= 'Authorised Unpaid';
                            break;
                        case(5):
                            $status .= 'Authorised Paid';
                            break;
                        endswitch;
                    endif;
                $createdAt = (isset($list->created_at) && !empty($list->created_at) ? $list->created_at : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'url' => route('employee.holiday', $list->leave->employee_id),
                    'photo_url' => $list->leave->employee->photo_url,
                    'name' => $list->leave->employee->first_name.' '.$list->leave->employee->last_name,
                    'designation' => (isset($list->leave->employee->employment->employeeJobTitle->name) ? $list->leave->employee->employment->employeeJobTitle->name : ''),
                    'status' => $status,
                    'start_date' => date('D jS F, Y', strtotime($list->leave_date)),
                    'end_date' => date('D jS F, Y', strtotime($list->leave_date)),
                    'title' => isset($list->leave->note) && !empty($list->leave->note) ? $list->leave->note : '',
                    'hour' => $this->calculateHourMinute($list->hour),
                    'type' => 'rejected',
                    'can_auth' => (!empty($employeeApprover) && in_array(auth()->user()->id, $employeeApprover) ? 1 : 0),
                    'approved_by' => (isset($list->uuser->employee->full_name) && !empty($list->uuser->employee->full_name) ? $list->uuser->employee->full_name : ''),
                    'approved_at' => (isset($list->updated_at) && !empty($list->updated_at) ? date('jS M, Y', strtotime($list->updated_at)) : ''),
                    'created_at' => (!empty($createdAt) ? date('jS F, Y', strtotime($createdAt)).' ('.$list->created_at->diffForHumans().')' : ''),
                ];
                $i++;
            endforeach;
        elseif(!empty($Query) && $type == 'pending'):
            $i = 1;
            foreach($Query as $list):
                $employeeApprover = EmployeeHolidayAuthorisedBy::where('employee_id', $list->employee_id)->pluck('user_id')->unique()->toArray();
                $leaveHours = 0;
                $leaveDays = 0;
                if(isset($list->leaveDays)):
                    foreach($list->leaveDays as $day):
                        $leaveHours += $day->hour;
                        $leaveDays += 1;
                    endforeach;
                endif;
                $createdAt = (isset($list->created_at) && !empty($list->created_at) ? $list->created_at : '');
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'url' => route('employee.holiday', $list->employee_id),
                    'photo_url' => $list->employee->photo_url,
                    'name' => $list->employee->first_name.' '.$list->employee->last_name,
                    'designation' => (isset($list->employee->employment->employeeJobTitle->name) ? $list->employee->employment->employeeJobTitle->name : ''),
                    'status' => 'Request for approval '.($leaveDays > 1 ? $leaveDays.' days' : $leaveDays.' day'),
                    'start_date' => date('D jS F, Y', strtotime($list->from_date)),
                    'end_date' => date('D jS F, Y', strtotime($list->to_date)),
                    'title' => 'Holiday / Vacation',
                    'hour' => $this->calculateHourMinute($leaveHours),
                    'type' => 'pending',
                    'can_auth' => (!empty($employeeApprover) && in_array(auth()->user()->id, $employeeApprover) ? 1 : 0),
                    'approved_by' => '',
                    'approved_at' => '',
                    'created_at' => (!empty($createdAt) ? date('jS F, Y', strtotime($createdAt)).' ('.$list->created_at->diffForHumans().')' : '')
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }

    public function employmentReportShow()
    {
        return view('pages.hr.portal.reports.showreport', [
            'title' => 'Employment Reports - LCC Data Future Managment',
            'subtitle' => 'Employment Reports',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Employment Reports', 'href' => 'javascript:void(0);']
            ]
        ]);
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

    public function leaveCalendar(){
        $html = '';
        $html .= '<thead>';
            $html .= $this->getCalendarHeader(date('Y-m-d'));
        $html .= '</thead>';
        $html .= '<tbody>';
            $html .= $this->getCalendarBody(date('Y-m-d'));
        $html .= '</tbody>';
        
        return view('pages.hr.portal.leave-calendar', [
            'title' => 'HR Portal Calendar - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'HR Portal', 'href' => route('hr.portal')],
                ['label' => 'Leave Calendar', 'href' => 'javascript:void(0);']
            ],
            'department' => Department::orderBy('name', 'ASC')->get(),
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
            'calendarHtml' => $html
        ]);
    }

    public function filterLeaveCalendar(Request $request){
        $department = (isset($request->department) && $request->department > 0 ? $request->department : 0);
        $employee = (isset($request->employee) && !empty($request->employee) ? $request->employee : []);
        $month = ($request->month < 10 ? '0'.$request->month : $request->month);
        $year = $request->year;

        $theDate = $year.'-'.$month.'-01';

        $html = '';
        $html .= '<thead>';
            $html .= $this->getCalendarHeader($theDate);
        $html .= '</thead>';
        $html .= '<tbody>';
            $html .= $this->getCalendarBody($theDate, $department, $employee);
        $html .= '</tbody>';


        return response()->json(['res' => $html], 200);
    }

    public function navigateLeaveCalendar(Request $request){
        $department = (isset($request->department) && $request->department > 0 ? $request->department : 0);
        $employee = (isset($request->employee) && !empty($request->employee) ? $request->employee : []);
        $theMonthStatus = (isset($request->theMonthStatus) && !empty($request->theMonthStatus) ? $request->theMonthStatus : 'prev');
        $thedate = (isset($request->thedate) && !empty($request->thedate) ? $request->thedate : date('Y-m-d'));

        if($theMonthStatus == 'prev'){
            $theDate = date('Y-m-d', strtotime('-1 months', strtotime($thedate)));
        }else{
            $theDate = date('Y-m-d', strtotime('+1 months', strtotime($thedate)));
        }

        $html = '';
        $html .= '<thead>';
            $html .= $this->getCalendarHeader($theDate);
        $html .= '</thead>';
        $html .= '<tbody>';
            $html .= $this->getCalendarBody($theDate, $department, $employee);
        $html .= '</tbody>';


        return response()->json(['res' => $html, 'date' => $theDate], 200);
    }

    public function getCalendarHeader($date){
        $html = '';
        $html .= '<th class="whitespace-nowrap text-left">Employee</th>';

        $start_date = date('Y-m', strtotime($date)).'-01';
        $end_date = date('Y-m-t', strtotime($date));
        $today = date('Y-m-d');

        while (strtotime($start_date) <= strtotime($end_date)) {
            $html .= '<th class="'.($start_date == $today ? 'today' : '').' whitespace-nowrap text-center">';
                $html .= '<span>'.date('d', strtotime($start_date)).'</span>';
                $html .= '<span>'.substr(date('D', strtotime($start_date)), 0, 1).'</span>';
            $html .= '</th>';

            $start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));
        }

        return $html;
    }

    public function getCalendarBody($theDate, $department = 0, $employee = []){
        $query = Employee::where('status', 1)->orderBy('first_name', 'ASC');
        if($department > 0):
            $query->whereHas('employment', function($q) use ($department){
                $q->where('department_id', $department);
            });
        endif;
        if(!empty($employee)):
            $query->whereIn('id', $employee);
        endif;
        $employees = $query->get();
        
        $today = date('Y-m-d');

        $html = '';
        if(!empty($employees) && $employees->count() > 0):
            foreach($employees as $emp):
                $employee_id = $emp->id;
                $start_date = date('Y-m', strtotime($theDate)).'-01';
                $end_date = date('Y-m-t', strtotime($theDate));

                $html .= '<tr>';
                    $html .= '<td><span class="font-medium">'.(isset($emp->title->name) ? $emp->title->name.' ' : '').$emp->first_name.' '.$emp->last_name.'</span></td>';

                    while(strtotime($start_date) <= strtotime($end_date)):
                        $class = '';
                        $label = '';
                        $title = '';
                        $style = '';
                        $dataAttr = '';

                        $date = date('Y-m-d', strtotime($start_date));
                        $d = strtolower(date('D', strtotime($start_date)));
                        $l = strtolower(date('l', strtotime($start_date)));
                        $n = strtolower(date('N', strtotime($start_date)));

                        /* Check if Today */
                        if($date == $today){ $class .= ' today';}
                        /* Check if Today */

                        /* Check if None working day / Weekend */
                        $activePattern = EmployeeWorkingPattern::where('employee_id', $employee_id)
                                         ->where('effective_from', '<=', $date)
                                         ->where(function($query) use($date){
                                            $query->whereNull('end_to')->orWhere('end_to', '>=', $date);
                                         })->get()->first();
                        if(isset($activePattern->id) && $activePattern->id > 0):
                            $activePatternId = (isset($activePattern->id) && $activePattern->id > 0 ? $activePattern->id : 0);
                            $patternDay = EmployeeWorkingPatternDetail::where('employee_working_pattern_id', $activePatternId)->where('day', $n)->get()->first();
                            $day_Status = (isset($patternDay->id) && $patternDay->id > 0 ? 1 : 0);

                            $class .= ($day_Status == 1) ? '' : ' NonWorkingDay';
                            $label = ($day_Status == 0) ? 'x' : '';
                            /* Check if None working day / Weekend */

                            /* Check if Leave day */
                            $leaveday = DB::table('employee_leave_days as eld')->select('eld.leave_date', 'eld.id as eld_id', 'el.*')
                                    ->leftJoin('employee_leaves as el', 'eld.employee_leave_id', 'el.id')
                                    ->where('eld.leave_date', $date)
                                    ->where('eld.status', 'Active')
                                    ->where('el.status', '!=', 'Canceled')
                                    ->where('el.employee_id', $employee_id)
                                    ->get()->first();
                            if(!empty($leaveday) && (isset($leaveday->eld_id) && $leaveday->eld_id > 0) > 0 && $day_Status > 0):
                                $dataAttr .= ' data-leaveday-id="'.$leaveday->eld_id.'" data-employee="'.$employee_id.'" data-date="'.$date.'"';
                                $class .= ' view_leave';
                            endif;

                            if(isset($leaveday->status) && $leaveday->status == 'Approved' && $day_Status > 0):
                                $class .= ' approvedDay approved_'.$leaveday->leave_type;
                            elseif(isset($leaveday->status) && $leaveday->status == 'Pending' && $day_Status > 0):
                                $class .= ' pendingDay pending_'.$leaveday->leave_type;
                            endif;
                            if(isset($leaveday->leave_type) && $leaveday->leave_type > 0 && $day_Status > 0):
                                switch ($leaveday->leave_type):
                                    case 1:
                                        $label = 'H';
                                        $title = 'Holiday / Vacation';
                                        $class .= ' holidayVacationBG';
                                        break;
                                    case 2:
                                        $label = 'A';
                                        $title = 'Unauthorised Absent';
                                        $class .= ' meetingTrainingBG';
                                        break;
                                    case 3:
                                        $label = 'S';
                                        $title = 'Sick Leave';
                                        $class .= ' sickLeaveBG';
                                        break;
                                    case 4:
                                        $label = 'U';
                                        $title = 'Authorised Unpaid';
                                        $class .= ' authoriseUnpaidBG';
                                        break;
                                    case 5:
                                        $label = 'P';
                                        $title = 'Authorised Paid';
                                        $class .= ' authorisedPaidBG';
                                        break;
                                endswitch;
                            endif;
                            /* Check if Leave day */

                            /* Check if Bank Holiday day */
                            if((isset($emp->payment->bank_holiday_auto_book) && $emp->payment->bank_holiday_auto_book == 'Yes') && $day_Status > 0):
                                $hrBankHoliday = HrBankHoliday::where('start_date', '<=', $date)->where('end_date', '>=', $date)->get()->first();
                                if(isset($hrBankHoliday->id) && $hrBankHoliday->id > 0):
                                    $label = 'BH';
                                    $title = 'Bank Holiday';
                                    $style = '';
                                    $class .= 'bankHolidayBG';
                                endif;
                            endif;
                            /* Check if Bank Holiday day */
                        else:
                            $class .= ' NonWorkingDay';
                            $label = 'x';
                        endif;

                        $theTitle = ($title != '') ? 'title="'.$title.'" ' : '' ;
                        $html .= '<td '.$theTitle.' class="'.$class.' text-center" style="'.$style.'" '.$dataAttr.'>';
                            $html .= $label;
                        $html .= '</td>';

                        $start_date = date ("Y-m-d", strtotime("+1 day", strtotime($start_date)));
                    endwhile;

                $html .= '</tr>';
            endforeach;
        else:
            $html .= '<tr>';
                $html .= '<td class="text-center font-medium" style="padding: 1.2rem 1rem; background: rgba(245, 158, 11, .2); color: rgb(245, 158, 11);" colspan="'.(date('t', strtotime($theDate)) + 1).'">';
                    $html .= 'No item found to display!';
                $html .= '</td>';
            $html .= '</tr>';
        endif;

        return $html;
    }

    public function updateAbsent(HrPortalAbsentUpdateRequest $request){
        $date = $request->date;
        $employee_id = $request->employee_id;
        $minutes = $request->minutes;
        $leave_day_id = (isset($request->leave_day_id) && $request->leave_day_id > 0 ? $request->leave_day_id : 0);

        $leave_type = $request->leave_type;
        $hour = (isset($request->hour) && !empty($request->hour) ? $this->convertStringToMinute($request->hour) : 0);
        $note = $request->note;

        $HrHolidayYears = HrHolidayYear::where('start_date', '<=', $date)->where('end_date', '>=', $date)->where('active', 1)->get()->first();
        $holidayYearId = (isset($HrHolidayYears->id) && $HrHolidayYears->id > 0 ? $HrHolidayYears->id : 0);
        $activePatternId = $this->employeePossibleActivePattern($employee_id, $holidayYearId);

        if($holidayYearId > 0 && $activePatternId > 0 && $leave_day_id == 0):
            $data = [];
            $data['employee_id'] = $employee_id;
            $data['hr_holiday_year_id'] = $holidayYearId;
            $data['employee_working_pattern_id'] = $activePatternId;
            $data['leave_type'] = $leave_type;
            $data['from_date'] = $date;
            $data['to_date'] = $date;
            $data['days'] = 1;
            $data['note'] = $note;
            $data['status'] = 'Approved';
            $data['approved_by'] = auth()->user()->id;
            $data['approver_note'] = $note;
            $data['approved_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = auth()->user()->id;
            $employeeLeave = EmployeeLeave::create($data);


            $data = [];
            $data['employee_leave_id'] = $employeeLeave->id;
            $data['leave_date'] = $date;
            $data['hour'] = ($hour > 0 && $leave_type == 5 ? $hour : 0);
            $data['status'] = 'Active';
            $data['was_absent_day'] = 1;
            $data['created_by'] = auth()->user()->id;
            EmployeeLeaveDay::create($data);

            return response()->json(['res' => 'success'], 200);
        elseif($leave_day_id > 0):
            $leave_day = EmployeeLeaveDay::find($leave_day_id);
            $leave_id = $leave_day->leave->id;

            $data = [];
            $data['leave_type'] = $leave_type;
            $data['note'] = $note;
            $data['updated_by'] = auth()->user()->id;
            EmployeeLeave::where('id', $leave_id)->update($data);

            $data = [];
            $data['hour'] = ($hour > 0 && $leave_type == 5 ? $hour : 0);
            $data['updated_by'] = auth()->user()->id;
            EmployeeLeaveDay::where('id', $leave_day_id)->update($data);

            return response()->json(['res' => 'success'], 200);
        else:
            return response()->json(['res' => 'Holiday year or Working pattern not found.'], 422);
        endif;
    }

    public function employeePossibleActivePattern($employee_id, $activeHolidayYearId){
        $today = date('Y-m-d');
        if($activeHolidayYearId > 0):
            $hrHolidayYear = HrHolidayYear::find($activeHolidayYearId);
        else:
            $hrHolidayYear = HrHolidayYear::where('start_date <=', $today)->where('end_date >=', $today)->where('active', 1)->get()->first();
        endif;

        $start = (isset($hrHolidayYear->start_date) && $hrHolidayYear->start_date != '' ? $hrHolidayYear->start_date : '');
        $end = (isset($hrHolidayYear->end_date) && $hrHolidayYear->end_date != '' ? $hrHolidayYear->end_date : '');

        $pattern = 0;
        $patternRes = EmployeeWorkingPattern::where('employee_id', $employee_id)->where('active', 1)->orderBy('id', 'DESC')->get();
        if(!empty($patternRes) && $patternRes->count() > 0):
            foreach($patternRes as $r):
                $effective_from = (isset($r->effective_from) && $r->effective_from != '' & $r->effective_from != '0000-00-00' ? $r->effective_from : '');
                $end_to = (isset($r->end_to) && $r->end_to != '' & $r->end_to != '0000-00-00' ? $r->end_to : '');

                if(
                    ($end_to != '' && $end_to > $start && $end_to < $end) && ($effective_from < $start || ($effective_from > $start && $effective_from < $end)) 
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

    public function getLeaveDayDetails(Request $request){
        $theLeaveDayId = $request->theLeaveDayId;
        $theLeaveDate = $request->theLeaveDate;
        $theEmployee = $request->theEmployee;

        $empLeaveDay = EmployeeLeaveDay::find($theLeaveDayId);
        $employee_id = $empLeaveDay->leave->employee_id;
        $employee = Employee::find($employee_id);

        $title = $employee->full_name.' ('.date('D, jS M, Y', strtotime($theLeaveDate)).')';

        $leave_type = 'Unknown';
        switch ($empLeaveDay->leave->leave_type) {
            case 1:
                $leave_type = 'Holiday / Vacation';
                break;
            case 2:
                $leave_type = 'Unauthorised Absent';
                break;
            case 3:
                $leave_type = 'Sick Leave';
                break;
            case 4:
                $leave_type = 'Authorised Unpaid';
                break;
            case 5:
                $leave_type = 'Authorised Paid';
                break;
        }
        $html = '';
        $html .= '<div class="grid grid-cols-12 gap-4">';
            $html .= '<div class="col-span-4 text-slate-500 font-medium">Type</div>';
            $html .= '<div class="col-span-8 font-medium">'.$leave_type.'</div>';
            $html .= '<div class="col-span-4 text-slate-500 font-medium">Status</div>';
            $html .= '<div class="col-span-8 font-medium">';
                if($empLeaveDay->leave->status == 'Approved'):
                    $html .= '<span class="btn btn-sm rounded-0 btn-success text-white">Approved</span>';
                elseif($empLeaveDay->leave->status == 'Pending'):
                    $html .= '<span class="btn btn-sm rounded-0 btn-warning text-white">Pending</span>';
                else:
                    $html .= '<span class="btn btn-sm rounded-0 btn-danger text-white">Rejected</span>';
                endif;
            $html .='</div>';
            $html .= '<div class="col-span-4 text-slate-500 font-medium">Date</div>';
            $html .= '<div class="col-span-8 font-medium">'.date('jS F, Y', strtotime($theLeaveDate)).'</div>';
            $html .= '<div class="col-span-4 text-slate-500 font-medium">Hours</div>';
            $html .= '<div class="col-span-8 font-medium">'.$this->calculateHourMinute($empLeaveDay->hour).' Hours</div>';
            if(isset($empLeaveDay->leave->note) && !empty($empLeaveDay->leave->note)):
                $html .= '<div class="col-span-4 text-slate-500 font-medium">Note</div>';
                $html .= '<div class="col-span-8 font-medium">'.$empLeaveDay->leave->note.'</div>';
            endif;
        $html .= '</div>';

        return response()->json(['htm' => $html, 'title' => $title], 200);
    }

    public function checkIfisPendingLeaveExist(Request $request){
        $employee = $request->employee;
        $the_date = date('Y-m-d', strtotime($request->the_date));

        $leaveIds = EmployeeLeave::where(function($q) use($the_date){
            $q->where('from_date', '<=', $the_date)->where('to_date', '>=', $the_date);
        })->where('employee_id', $employee)->whereIn('status', ['Approved', 'Pending'])->pluck('id')->unique()->toArray();

        
        if(!empty($leaveIds)):
            $leaveDay = EmployeeLeaveDay::whereIn('employee_leave_id', $leaveIds)->where('leave_date', $the_date)->where('status', 'Active')->where('is_taken', '0')->get()->count();
            if($leaveDay > 0):
                return response()->json(['suc' => 2, 'msg' => '<strong>Oops!</strong> A Pending leave found for the day '.date('jS F, Y', strtotime($the_date)).'. Please take a action on that pending leave first.'], 200);
            else:
                return response()->json(['suc' => 1, 'msg' => ''], 200);
            endif;
        else:
            return response()->json(['suc' => 1, 'msg' => ''], 200);
        endif;
    }
}
