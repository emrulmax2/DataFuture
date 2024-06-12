<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGroupMailRequest;
use App\Jobs\UserMailerJob;
use App\Mail\CommunicationSendMail;
use App\Models\Applicant;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\ComonSmtp;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendanceLive;
use App\Models\EmployeeGroup;
use App\Models\EmployeeGroupMember;
use App\Models\Employment;
use App\Models\InternalLink;
use App\Models\ProcessList;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskListUser;
use App\Models\User;
use App\Models\UserPrivilege;
use App\Models\VenueIpAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;


class DashboardController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('web')->user();
        $userEmployeeId = $userData->employee->id;
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

        $work_home = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'work_home')->get()->first();
        $desktop_login = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'desktop_login')->get()->first();
        $ips = VenueIpAddress::pluck('ip')->unique()->toArray();
        $ips = (!empty($ips) ? $ips : ['62.31.168.43', '79.171.153.100', '149.34.178.243']);
        $workHistory = $this->getUserAttendanceLiveBtns();
        return view('pages.users.staffs.dashboard.index', [
            'title' => 'Applicant Dashboard - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            "interview" => $unfinishedInterviewCount."/".$TotalInterviews,
            'applicant' => Applicant::where('status_id', '>', 1)->get()->count(),
            'student' => Student::all()->count(),
            'myPendingTask' => $this->getUserPendingTask(),
            'home_work' => (isset($work_home->access) && $work_home->access == 1 ? true : false),
            'desktop_login' => (isset($desktop_login->access) && $desktop_login->access == 1 ? true : false),
            'home_work_statistics' => $this->getUserAttendanceLiveStatistics(),
            'home_work_history_btns' => (isset($workHistory['html']) && !empty($workHistory['html']) ? $workHistory['html'] : ''),
            'work_history_lock' => (isset($workHistory['loc']) ? $workHistory['loc'] : false),
            'work_history_lock_no' => (isset($workHistory['loc_no']) ? $workHistory['loc_no'] : 0),
            'internal_link_buttons' => $this->getInternalLinkBtns(),
            'venue_ips' => $ips,
            'departments' => Department::where('available_for_all', 1)->orderBy('name', 'ASC')->get(),
            'employees' => Employee::where('status', 1)->orderBy('first_name', 'ASC')->get(),
            'groups' => EmployeeGroup::where('type', 2)->orWhere(function($q) use($userEmployeeId){
                $q->where('employee_id', $userEmployeeId)->whereIn('type', [1, 2]);
            })->orderBy('name', 'ASC')->get()
        ]);
    }

    public function getAccountDashBoard()
    {
        return view('pages.accounting.dashboard.index');
    }

    public function parentLinkBox($id)
    {
        $userData = \Auth::guard('web')->user();

        $work_home = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'work_home')->get()->first();
        $desktop_login = UserPrivilege::where('user_id', auth()->user()->id)->where('category', 'remote_access')->where('name', 'desktop_login')->get()->first();
        $ips = VenueIpAddress::pluck('ip')->unique()->toArray();
        $ips = (!empty($ips) ? $ips : ['62.31.168.43', '79.171.153.100', '149.34.178.243']);

        return view('pages.users.staffs.dashboard.internal-links', [
            'title' => 'Internal Link - London Churchill College',
            'subtitle' => '',
            'breadcrumbs' => [
                ['label' => 'Internal Site Link', 'href' => 'javascript:void(0);']
            ],
            'parents' => InternalLink::where('parent_id', $id)->get(),
            'user' => $userData,
            
            'myPendingTask' => $this->getUserPendingTask(),

            'internal_link_buttons' => $this->getInternalChildLinkBtns($id),
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
                    $result[$prs]['image'] = $theProcess->image;
                    $result[$prs]['image_url'] = $theProcess->image_url;
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

    public function getUserAttendanceLiveStatistics(){
        $user_id = auth()->user()->id;
        $employee_id = auth()->user()->employee->id;
        $today = date('Y-m-d');
        $employee = Employee::find($employee_id);

        $html = '';
        $last_date = (isset($employee->employment->last_action_date) && $employee->employment->last_action_date != '') ? $employee->employment->last_action_date : '';
        $last_action = (isset($employee->employment->last_action) && $employee->employment->last_action > 0) ? $employee->employment->last_action : 0;
        $last_action_label = '';
        switch ($last_action) {
            case 1:
                $last_action_label = 'Working';
                break;
            case 2:
                $last_action_label = 'Break';
                break;
            case 3:
                $last_action_label = 'Working';
                break;
            case 4:
                $last_action_label = 'Clocked Out';
                break;
            default:
                $last_action_label = 'No clock-in';
        }
        $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
        $liveLast = EmployeeAttendanceLive::where('attendance_type', 4)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
        if(isset($employee->employment->id) && $employee->employment->id > 0):
            if($today == $last_date && (isset($live->id) && $live->id > 0)):
                $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : strtotime(date('H:i:s')));
                $duration_seconds = $rtime * 1000;

                $html .= '<div class="clockinStatistics inline-flex justify-end items-start ml-auto">';
                    $html .= '<div class="statusArea">';
                        $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">Status</div>';
                        $html .= '<div class="font-medium whitespace-nowrap uppercase">'.$last_action_label.'</div>';
                    $html .= '</div>';
                    $html .= '<div class="sinceArea">';
                        $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">since</div>';
                        $html .= '<div class="font-medium whitespace-nowrap uppercase">'.date('H:i A', strtotime($live->time)).(isset($liveLast->time) && !empty($liveLast->time) ? ' - '.date('H:i A', strtotime($liveLast->time)) : '').'</div>';
                        if($last_action != 4):
                            $html .= '<div class="text-slate-500 text-xs whitespace-nowrap clockedInFrom" id="clockedInFrom" data-starts="'.$duration_seconds.'">00:00</div>';
                        endif;
                    $html .= '</div>';
                $html .= '</div>';
            else:
                $html .= '<div class="clockinStatistics inline-flex justify-end items-start ml-auto">';
                    $html .= '<div class="statusArea">';
                        $html .= '<div class="text-slate-500 text-xs whitespace-nowrap uppercase">Status</div>';
                        $html .= '<div class="font-medium whitespace-nowrap uppercase text-danger">No clock-in</div>';
                    $html .= '</div>';
                $html .= '</div>';
            endif;
        endif;

        return $html;
    }

    public function getUserAttendanceLiveBtns(){
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

        $html = '';
        if($loc == 0):
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="1">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Clock_In.png').'">';
            $html .= '</a>';
        elseif($loc == 1):
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="2">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Break.png').'">';
            $html .= '</a>';
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="4">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Clock_Out.png').'">';
            $html .= '</a>';
        elseif($loc == 2):
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="3">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Return.png').'">';
            $html .= '</a>';
        elseif($loc == 3):
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="2">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Break.png').'">';
            $html .= '</a>';
            $html .= '<a href="javascript:void(0);" class="block col-span-6 2xl:col-span-4 attendance_action_btn" data-value="4">';
                $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.asset('build/assets/images/hr/Clock_Out.png').'">';
            $html .= '</a>';
        elseif($loc == 4):
            $html .= '<div class="col-span-12">';
                $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                    $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> It seems that you are already clocked out for the day.';
                $html .= '</div>';
            $html .= '</div>';
        else:
            $html .= '<div class="col-span-12">';
                $html .= '<div class="alert alert-danger-soft show flex items-center mb-2" role="alert">';
                    $html .= '<i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Something went wrong. Please Try Later.';
                $html .= '</div>';
            $html .= '</div>';
        endif;

        $res = [];
        $res['html'] = $html;
        $res['loc'] = ($loc == 0 || $loc == 2 ? true : false);
        $res['loc_no'] = ($loc == 0 ? 1 : ($loc == 2 ? 3 : 0));

        return $res;
        //return $html;
    }

    public function getInternalLinkBtns(){
        $user_id = auth()->user()->id;
        $employee_id = auth()->user()->employee->id;
        $today = date('Y-m-d');
        $parentLinkIds = UserPrivilege::where('user_id', $user_id)->where('employee_id', $employee_id)->where('category', 'parent_internal_links')->pluck('name')->unique()->toArray();
        
        $html = '';
        if(!empty($parentLinkIds)):
            $parentLinks = InternalLink::whereIn('id', $parentLinkIds)->get();
            if($parentLinks->count() > 0):
                foreach($parentLinks as $link):
                    if((empty($link->start_date) || empty($link->end_date)) || ((!empty($link->start_date) && !empty($link->end_date)) && ($link->start_date <= $today && $link->end_date >= $today))): 
                        if(isset($link->children) && $link->children->count() > 0):
                            $html .= '<a href="'.route('dashboard.internal-link.parent', $link->id).'" target="_blank" class="block relative col-span-6 2xl:col-span-4 mb-3" data-value="1">';
                        else:
                            $html .= '<a href="'.$link->link.'" target="_blank" class="block col-span-6 2xl:col-span-4 mb-3 relative" data-value="1">';
                        endif;
                            if(empty($link->image)):
                                $html .= '<h6 class="absolute text-sm w-full text-center uppercase text-white font-medium z-10 px-2" style="top: 50%; transform:translateY(-50%);">'.$link->name.'</h6>';
                            endif;
                            $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.(!empty($link->image) ? $link->image : asset('build/assets/images/blan_logo.png')).'">';
                        $html .= '</a>';
                    endif;
                endforeach;
            endif;
        endif;

        return $html;
    }

    public function getInternalChildLinkBtns($parent){
        $user_id = auth()->user()->id;
        $employee_id = auth()->user()->employee->id;
        $today = date('Y-m-d');
        $category = 'parent_child_'.$parent.'_links';
        $childLinkIds = UserPrivilege::where('user_id', $user_id)->where('employee_id', $employee_id)->where('category', $category)->pluck('name')->unique()->toArray();
        
        $html = '';
        if(!empty($childLinkIds)):
            $childLinks = InternalLink::whereIn('id', $childLinkIds)->get();
            if($childLinks->count() > 0):
                foreach($childLinks as $link):
                    if((empty($link->start_date) || empty($link->end_date)) || ((!empty($link->start_date) && !empty($link->end_date)) && ($link->start_date <= $today && $link->end_date >= $today))): 
                        $html .= '<a href="'.(!empty($link->link) ? $link->link : 'javascript:void(0)').'" target="_blank" class="block col-span-2 mb-3">';
                        if(!empty($link->image)):
                            $html .= '<img class="block w-full h-auto shadow-md zoom-in rounded" src="'.$link->image.'">';
                        else:
                            $html .= '<span class="inline-flex w-full h-full shadow-md zoom-in rounded bg-primary text-white text-lg uppercase justify-center items-center py-6 px-6">'.$link->name.'</span>';
                        endif;
                        $html .= '</a>';
                    endif;
                endforeach;
            else:
                $html .= '<div class="col-span-12">';
                    $html .= '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There no links found for this category.</div>';
                $html .= '</div>';
            endif;
        endif;

        return $html;
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
            $html .= '<button data-value="1" type="button" class="btn btn-facebook attendance_action_btn text-white">Clock In '.$svg.'</button>';
        elseif($loc == 1):
            $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2) * 1000;
            
            $html .= '<span class="text-primary font-bold mr-2">'.(!empty($rtime) ? date('H:i', $rtime) : '').'</span>&nbsp;';
            $html .= '<button data-value="2" type="button" class="btn btn btn-twitter attendance_action_btn">Take Break  '.$svg.'</button>';
            $html .= '&nbsp;<button data-value="4" type="button" class="btn btn-danger attendance_action_btn">Clock Out  '.$svg.'</button>';
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
            $html .= '<button data-value="3" type="button" class="btn btn-warning text-white attendance_action_btn">Return  '.$svg.'</button>';
        elseif($loc == 3):
            $live = EmployeeAttendanceLive::where('attendance_type', 1)->where('date', $today)->where('employee_id', $employee_id)->orderBy('id', 'DESC')->get()->first();
            $rtime = (isset($live->time) && $live->time != '00:00:00' && $live->time ? strtotime($live->time) : '');
            $ntime = strtotime(date('H:i:s'));
            $duration_seconds = round(abs($rtime - $ntime), 2) * 1000;
            
            $html .= '<span class="text-primary font-bold mr-2">'.(!empty($rtime) ? date('H:i', $rtime) : '').'</span>&nbsp;';
            $html .= '<button data-value="2" type="button" class="btn btn-twitter attendance_action_btn">Take Break  '.$svg.'</button>';
            $html .= '&nbsp;<button data-value="4" type="button" class="btn btn-danger attendance_action_btn">Clock Out  '.$svg.'</button>';
        elseif($loc == 4):
            $html .= '<button data-value="1" type="button" class="btn btn-facebook attendance_action_btn">Clock In  '.$svg.'</button>';
        else:
            $html .= '';
        endif;

        return $html;
    }

    public function ignoreFeeAttendance(Request $request){
        Session::put('work_history_lock_first_time', 1);
        return response()->json(['res' => 'Success!'], 200);
    }

    public function feeAttendance(Request $request){
        Session::forget('work_history_lock_first_time');
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


    public function getDeptEmployeeIds(Request $request){
        $department_ids = (isset($request->department_ids) && !empty($request->department_ids) ? $request->department_ids : []);
        $group_ids = (isset($request->group_ids) && !empty($request->group_ids) ? $request->group_ids : []);

        $employee_ids = [];
        if(!empty($department_ids)):
            $employee_ids = Employee::where('status', 1)->orderBy('first_name', 'ASC')
                    ->whereHas('employment', function($q) use($department_ids){
                        $q->whereIn('department_id', $department_ids);
                    })->pluck('id')->unique()->toArray();
        elseif(!empty($group_ids)):
            $employees = EmployeeGroupMember::whereIn('employee_group_id', $group_ids)->pluck('employee_id')->unique()->toArray();
            if(!empty($employees)):
                $employee_ids = Employee::where('status', 1)->orderBy('first_name', 'ASC')
                    ->whereIn('id', $employees)->pluck('id')->unique()->toArray();
            endif;
        endif;
        return response()->json(['emps' => $employee_ids], 200);
    }

    public function sendGroupEmail(SendGroupMailRequest $request){
        $employee_ids = $request->employee_ids;
        $subject = $request->subject;
        $mail_body = $request->mail_body;

        $mailTos = Employment::whereIn('employee_id', $employee_ids)->pluck('email')->unique()->toArray();

        $crntUser = Employee::where('user_id', auth()->user()->id)->get()->first();
        $fromEmail = (isset($crntUser->employment->email) && !empty($crntUser->employment->email) ? $crntUser->employment->email : $crntUser->email);
        $commonSmtp = ComonSmtp::where('smtp_user', 'internal@lcc.ac.uk')->get()->first();

        if(!empty($mailTos) && (isset($commonSmtp->id) && $commonSmtp->id > 0)):
            $mailTos[] = $fromEmail;
            $configuration = [
                'smtp_host'         => $commonSmtp->smtp_host,
                'smtp_port'         => $commonSmtp->smtp_port,
                'smtp_username'     => $commonSmtp->smtp_user,
                'smtp_password'     => $commonSmtp->smtp_pass,
                'smtp_encryption'   => $commonSmtp->smtp_encryption,
                
                'from_email'        => $fromEmail,
                'from_name'         => $crntUser->full_name,
            ];

            $attachmentInfo = [];
            if($request->hasFile('documents')):
                $documents = $request->file('documents');
                $docCounter = 1;
                foreach($documents as $document):
                    $documentName = time().'_'.$document->getClientOriginalName();
                    $path = $document->storeAs('public/tmps', $documentName, 's3');

                    $attachmentInfo[$docCounter++] = [
                        "pathinfo" => $path,
                        "nameinfo" => $document->getClientOriginalName(),
                        "mimeinfo" => $document->getMimeType(),
                        'disk'     => 's3'      
                    ];
                    $docCounter++;
                endforeach;
            endif;

            UserMailerJob::dispatch($configuration, $mailTos, new CommunicationSendMail($subject, $mail_body, $attachmentInfo));
            return response()->json(['suc' => 1, 'res' => 'Mail successfully sent.'], 200);
        else:
            return response()->json(['suc' => 2, 'res' => 'Mail successfully sent.'], 200);
        endif;
    }
    
}
