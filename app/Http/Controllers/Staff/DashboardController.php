<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\Employment;
use App\Models\ProcessList;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskListUser;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('web')->user();
        $taskListData = TaskList::with('applicant')->where('interview','yes')->get();
        //$user = User::find($userData->id);
        $TotalInterviews = 0;
        $unfinishedInterviewCount = 0;
        foreach ($taskListData as $task) {
            
            foreach($task->applicant as $applicant) {
                $applicantTask = ApplicantTask::where("applicant_id",$applicant->id)->where("task_list_id",$task->id)->get()->first();
                if($applicantTask->status=="Pending" || $applicantTask->status=="In Progress") {
                    $TotalInterviews++;
                } 
                if($applicantTask->status=="In Progress"){
                    $unfinishedInterviewCount++;
                }
            }
        }
        // foreach ($user->interviews as $interview) {
        //     $ApplicantTask = ApplicantTask::find($interview->applicant_task_id);
        //      if($ApplicantTask->status!="Completed") {
        //          $unfinishedInterviewCount++;
        //     }
        // }

        $work_home = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'work_home')->get()->first();
        $desktop_login = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'desktop_login')->get()->first();
        $ips = VenueIpAddress::pluck('ip')->unique()->toArray();
        $ips = (!empty($ips) ? $ips : ['62.31.168.43', '79.171.153.100', '149.34.178.243']);
        return view('pages.users.staffs.dashboard.index', [
            'title' => 'Applicant Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            "interview" => $unfinishedInterviewCount."/".$TotalInterviews,
            'applicant' => Applicant::all()->count(),
            'student' => Student::all()->count(),
            'myPendingTask' => $this->getUserPendingTask(),
            'home_work' => (isset($work_home->access) && $work_home->access == 1 ? true : false),
            'desktop_login' => (isset($desktop_login->access) && $desktop_login->access == 1 ? true : false),
            'home_work_history' => $this->getUserAttendanceLiveHistory(),
            'venue_ips' => $ips
        ]);
    }

    public function getUserPendingTask(){
        $result = [];
        $assignedTaskIds = TaskListUser::where('user_id', auth()->user()->id)->pluck('task_list_id')->unique()->toArray();

        if(!empty($assignedTaskIds)):
            $assignedProcess = TaskList::whereIn('id', $assignedTaskIds)->orderBy('process_list_id', 'ASC')->pluck('process_list_id')->unique()->toArray();
            if(!empty($assignedProcess)):
                foreach($assignedProcess as $prs):
                    $theProcess = ProcessList::find($prs);
                    $result[$prs]['name'] = $theProcess->name;
                    $result[$prs]['outstanding_tasks'] = 0;
                    $processTasks = TaskList::whereIn('id', $assignedTaskIds)->where('process_list_id', $prs)->orderBy('name', 'ASC')->get();
                    if(!empty($processTasks) && $processTasks->count() > 0):
                        foreach($processTasks as $atsk):
                            $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
                            $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->whereIn('status', ['Pending', 'In Progress'])->get();
                            if($aplPendingTask->count() > 0 || $stdPendingTask->count() > 0):
                                $result[$prs]['tasks'][$atsk->id] = $atsk;
                                $result[$prs]['tasks'][$atsk->id]['pending_task'] = $aplPendingTask->count() + $stdPendingTask->count();
                                $result[$prs]['outstanding_tasks'] += $aplPendingTask->count();
                                $result[$prs]['outstanding_tasks'] += $stdPendingTask->count();
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif;

            /*$res = [];
            $res['tasks'] = [];
            $res['outstanding_tasks'] = 0;
            $assignedTasks = TaskList::whereIn('id', $assignedTaskIds)->orderBy('name', 'ASC')->get();
            if(!empty($assignedTasks)):
                foreach($assignedTasks as $atsk):
                    $aplPendingTask = ApplicantTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
                    $stdPendingTask = StudentTask::where('task_list_id', $atsk->id)->where('status', 'Pending')->get();
                    if($aplPendingTask->count() > 0 || $stdPendingTask->count() > 0):
                        $res['tasks'][$atsk->id] = $atsk;
                        $res['tasks'][$atsk->id]['pending_task'] = $aplPendingTask->count() + $stdPendingTask->count();
                        $res['outstanding_tasks'] += $aplPendingTask->count();
                        $res['outstanding_tasks'] += $stdPendingTask->count();
                    endif;
                endforeach;
            endif;*/
        endif;

        return $result;
    }

    public function getUserAttendanceLiveHistory(){
        $user_id = auth()->user()->id;
        $employee_id = auth()->user()->employee->id;
        $today = date('Y-m-d');
        $employee = Employee::find($employee_id);

        $last_date = (isset($employee->employment->last_action_date) && $employee->employment->last_action_date != '') ?$employee->employment->last_action_date : '';
        $row = array();
        if(isset($employee->employment->id) && $employee->employment->id > 0):
            if($today == $last_date):
                $row['loc'] = $loc = (isset($employee->employment->last_action) && $employee->employment->last_action > 0) ? $employee->employment->last_action : 'error';
            else:
                $row['loc'] = $loc = 0;
            endif;
            $row['name'] = (isset($employee->full_name) && $employee->full_name != '') ? $employee->full_name : '';
        else:
            $row['loc'] = $loc = 'error';
            $row['name'] = '';
        endif;
        //return $row;

        $html = '';
        $svg = '<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"stroke="white" class="w-4 h-4 ml-2 loaderSvg"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        if($loc == 0):
            $html .= '<button data-value="1" type="button" class="btn btn-facebook btn-sm attendance_action_btn text-white">Clock In '.$svg.'</button>';
        elseif($loc == 1):
            $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2) * 1000;
            
            $html .= '<span class="text-primary font-bold mr-2">'.(!empty($rtime) ? date('H:i', $rtime) : '').'</span>&nbsp;';
            $html .= '<button data-value="2" type="button" class="btn btn btn-twitter btn-sm attendance_action_btn">Take Break  '.$svg.'</button>';
            $html .= '&nbsp;<button data-value="4" type="button" class="btn btn-danger btn-sm attendance_action_btn">Clock Out  '.$svg.'</button>';
        elseif($loc == 2):
            $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2);
            $html .= '<span class="text-primary font-bold mr-2" >'.(!empty($rtime) ? date('H:i', $rtime) : '').'</span>&nbsp;';
            
            $live = EmployeeAttendanceLive::where('attendance_type', 2)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2) * 1000;
            $html .= '<span class="text-success font-bold clockin_from mr-2" data-delays="'.$duration_seconds.'">00:00:00</span>&nbsp;';
            $html .= '<button data-value="3" type="button" class="btn btn-warning text-white btn-sm attendance_action_btn">Return  '.$svg.'</button>';
        elseif($loc == 3):
            $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2) * 1000;
            
            $html .= '<span class="text-primary font-bold mr-2">'.(!empty($rtime) ? date('H:i', $rtime) : '').'</span>&nbsp;';
            $html .= '<button data-value="2" type="button" class="btn btn-twitter btn-sm attendance_action_btn">Take Break  '.$svg.'</button>';
            $html .= '&nbsp;<button data-value="4" type="button" class="btn btn-danger btn-sm attendance_action_btn">Clock Out  '.$svg.'</button>';
        elseif($loc == 4):
            $html .= '<button data-value="1" type="button" class="btn btn-facebook btn-sm attendance_action_btn">Clock In  '.$svg.'</button>';
        else:
            $html .= '';
        endif;

        return $html;
    }

    public function feeAttendance(Request $request){
        $venuIpAddresses        = VenueIpAddress::pluck('ip')->unique()->toArray();

        $user_id                = auth()->user()->id;
        $employees_id           = auth()->user()->employee->id;
        $employee               = Employee::find($employees_id);

        $attendance_type        = $request->action_type;
        $today                  = date('Y-m-d');
        $time                   = date('H:i:s');
        $user_ip                = $request->ip();
        
        $venu_ips               = (!empty($venuIpAddresses) ? $venuIpAddresses : ['62.31.168.43', '79.171.153.100', '149.34.178.243']);

        $type_name = '';
        switch ($attendance_type):
            case 1:
                $type_name = 'Clock-In';
                break;
            case 2:
                $type_name = 'Break';
                break;
            case 3:
                $type_name = 'Break-Return';
                break;
            case 4:
                $type_name = 'Clock-Out';
                break;
            default :
                $type_name = 'Unknown';
                break;
        endswitch;

        $data                       = [];
        $data['employee_id']        = $employees_id;
        $data['attendance_type']    = $attendance_type;
        $data['date']               = $today;
        $data['time']               = $time;
        $data['employee_attendance_machine_id'] = 0;
        $data['ip']                 = $user_ip;
        $data['created_by']         = $user_id;
        
        $employeeLiveAttendance = EmployeeAttendanceLive::create($data);

        $data                       = array();
        $data['last_action']        = $attendance_type;
        $data['last_action_date']   = $today;
        $data['last_action_time']   = $time;
        $employment = Employment::where('id', $employee->employment->id)->update($data);

        $res = $type_name.' type successfully feeded to your live attendance table.';
        if(!empty($venu_ips) && !in_array($user_ip, $venu_ips)):
            $res = 'Your '.$type_name.' is recorded away from the campus. Please ensure this has been authorised by the HR/Department manager.';
        endif;

        return response()->json(['res' => $res], 200);
    }
    
}
