<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Applicant;
use App\Models\ApplicantInterview;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\User;
use App\Http\Requests\InterviewerUpdateRequest;


class ApplicantInterviewListController extends Controller
{
    public function index()
    {
        return view('pages/users/access/staff', [
            'title' => 'Interview List - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Interview List', 'href' => 'javascript:void(0);']],
            'tasklists' => TaskList::all(),
            'applicanttasks' => ApplicantTask::all(),
            'applicants' => Applicant::all(),
            'users' => User::all(),
        ]);
    }

    public function list(Request $request){
            $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
            $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
            $tasklist = (isset($request->tasklist) && $request->tasklist > 0 ? $request->tasklist : '');
            $applicanttask = (isset($request->applicanttask) && $request->applicanttask > 0 ? $request->applicanttask : '');
            $applicant = (isset($request->applicant) && $request->applicant > 0 ? $request->applicant : '');

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
            $sorts = [];
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;
            $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
            $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
            
            $query = ApplicantInterview::with('applicant','task','document')->orderByRaw(implode(',', $sorts));
            if(!empty($queryStr)):
                $query->where('name','LIKE','%'.$queryStr.'%');
            endif;
            $query->whereHas('task', function($query){
                $query->where('status',"<>",'Completed');  
            });
            
            $total_rows = $query->count();
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
                        $data[] = [
                            'id' => $list->id,
                            'sl' => $i,
                            "name" => $list->applicant->title->name." ".$list->applicant->full_name,
                            'applicant_number'=> $list->applicant->application_no,
                            'gender' =>$list->applicant->gender,
                            'status' =>$list->task->status,
                            'time' => ($list->start_time ? $list->start_time : "00:00") ." - ". ($list->end_time ? $list->end_time : "00:00") ,
                            'date' => $list->interview_date,
                            'result' => $list->interview_status,
                            
                        ];
                        $i++;
                    
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $data]);      
    }

    public function interviewResultUpdate(Request $request) {


        $ApplicantInterview = ApplicantInterview::find($request->id);
 
        $ApplicantInterview->interview_status = $request->interview_status;
            
        $ApplicantInterview->save();
                    
        if($ApplicantInterview->wasChanged())      
            return response()->json(["msg"=>"Result Updated","result"=>$request->interview_status],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);

    }

    
    public function interviewTaskUpdate(Request $request) {
        
        $ApplicantInterview = ApplicantInterview::find($request->id);

        $task = ApplicantTask::find($ApplicantInterview->task->id);

        $task->status = "Completed";
        
        $task->save();
                
        if($task->wasChanged())      
            return response()->json(["msg"=>"Task Finished","status"=>"Completed"],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);

    }

    
    public function interviewStartTimeUpdate(Request $request) {
        
        $ApplicantInterview = ApplicantInterview::find($request->id);

        $ApplicantInterview->start_time = $startTime = date("H:i",time());
        
        $ApplicantInterview->save();

        $task = ApplicantTask::find($ApplicantInterview->task->id);

        $task->status = "In Progress";
        
        $task->save();

        if($ApplicantInterview->wasChanged())  
           
            return response()->json(["msg"=>"Time Started", "data"=>["start"=> date("h:i a", strtotime($startTime)),"status"=>"In Progress"]],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);
    }

    public function interviewEndTimeUpdate(Request $request) {

        $ApplicantInterview = ApplicantInterview::find($request->id);

        $ApplicantInterview->end_time = $endTime = date("H:i",time());
        
        $ApplicantInterview->save();
        
        if($ApplicantInterview->wasChanged())     

            return response()->json(["msg"=>"Time End","data"=>["end"=> date("h:i a", strtotime($endTime))]],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);
    }
}
