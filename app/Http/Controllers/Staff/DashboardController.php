<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index()
    {
        $userData = \Auth::guard('web')->user();
        $taskListData = TaskList::with('applicant')->where('interview','yes')->get();
        $user = User::find($userData->id);
        $TotalInterviews = 0;
        $unfinishedInterviewCount = 0;
        foreach ($taskListData as $task) {
            $TotalInterviews += $task->applicant->count();
        }
        foreach ($user->interviews as $interview) {
            $ApplicantTask = ApplicantTask::find($interview->applicant_task_id);
             if($ApplicantTask->status!="Completed") {
                 $unfinishedInterviewCount++;
            }
        }

        return view('pages.users.staffs.dashboard.index', [
            'title' => 'Applicant Dashboard - LCC Data Future Managment',
            'breadcrumbs' => [],
            'user' => $userData,
            "interview" => $unfinishedInterviewCount."/".$TotalInterviews,
            'applicant' => Applicant::all()->count(),
            'student' => Student::all()->count(),
        ]);
    }
    
}
