<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\TaskList;
use App\Models\ApplicantTask;
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
        $res = [];
        $res['tasks'] = [];
        $res['outstanding_tasks'] = 0;
        $assignedTaskIds = TaskListUser::where('user_id', auth()->user()->id)->pluck('task_list_id')->unique()->toArray();

        if(!empty($assignedTaskIds)):
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
            endif;
        endif;

        return $res;
    }
    
}
