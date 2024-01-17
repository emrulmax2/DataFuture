<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\ProcessList;
use App\Models\Student;
use App\Models\StudentTask;
use App\Models\TaskListUser;
use App\Models\User;
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


        return view('pages.users.staffs.dashboard.index', [
            'title' => 'Applicant Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            "interview" => $unfinishedInterviewCount."/".$TotalInterviews,
            'applicant' => Applicant::all()->count(),
            'student' => Student::all()->count(),
            'myPendingTask' => $this->getUserPendingTask(),
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
    
}
