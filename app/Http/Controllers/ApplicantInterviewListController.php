<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Applicant;
use App\Models\ApplicantInterview;
use App\Models\ApplicantDocument;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\User;
use App\Http\Requests\InterviewerUpdateRequest;
use App\Models\ApplicantViewUnlock;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'applicantdocuments' => ApplicantDocument::all(),
            'users' => User::where('active', 1)->orderBy('name', 'ASC')->get(),
        ]);
    }

    public function list(Request $request){
            $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
            $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
            $tasklist = (isset($request->tasklist) && $request->tasklist > 0 ? $request->tasklist : '');
            $applicanttask = (isset($request->applicanttask) && $request->applicanttask > 0 ? $request->applicanttask : '');
            $applicant = (isset($request->applicant) && $request->applicant > 0 ? $request->applicant : '');

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
            $sorts = [];
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;
            
            $query = ApplicantInterview::with('applicant','task','document')->orderByRaw(implode(',', $sorts));
            if(!empty($queryStr)):
                $query->where('name','LIKE','%'.$queryStr.'%');
            endif;
            $query->whereHas('task', function($query) {

                $query->where('status',"<>",'Completed');  

            });
            
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
                    $k =0;
                    $nestedDataContainer = [];
                    
                        $data[] = [
                            'id' => $list->id,
                            'sl' => $i,
                            "name" => $list->applicant->title->name." ".$list->applicant->full_name,
                            'applicant_number'=> $list->applicant->application_no,
                            'gender' =>$list->applicant->gender,
                            'status' =>$list->interview_status,
                            'time' => ($list->start_time ? $list->start_time : "00:00") ." - ". ($list->end_time ? $list->end_time : "00:00") ,
                            'date' => $list->interview_date,
                            'result' => $list->interview_result,
                            'interviewer' => $list->user->name
                        ];
                        $i++;
                    
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $data]);      
    }

    public function interviewResultUpdate(Request $request){
        
        $applicantInterviewData = ApplicantInterview::find($request->id);
        
        if(!$applicantInterviewData->applicant_document_id) {

            $document = $request->file('file');
            $imageName = time().'_'.$document->getClientOriginalName();
            $path = $document->storeAs('public/interviewresult/'.$applicantInterviewData->applicant_id, $imageName, 'google');
            $data = [];
            $data['applicant_id'] = $applicantInterviewData->applicant_id;
            $data['doc_type'] = $document->getClientOriginalExtension();
            $data['path'] = Storage::disk('google')->url($path);
            $data['display_file_name'] = $imageName;
            $data['current_file_name'] = $imageName;
            $data['created_by'] = auth()->user()->id;
            $applicantDoc = ApplicantDocument::create($data);
            if($applicantDoc):
                $applicantInterviewUpdate = $applicantInterviewData->update([
                    'applicant_document_id' => $applicantDoc->id,
                    'interview_result' => $request->resultValue
                ]);
            endif;
            return response()->json(['message' => 'Upload Successful.'], 200);
        } else
           return response()->json(['message' => 'Document already exist. Please remove file before new upload'], 422);
        
    }

    
    public function interviewTaskUpdate(Request $request) {
        
        $ApplicantInterview = ApplicantInterview::find($request->id);

        $ApplicantInterview->interview_status = 'Completed';
        
        if($ApplicantInterview->interview_result == "Pass")  {

            $task = ApplicantTask::find($ApplicantInterview->task->id);
            $task->status = "Completed";
            $task->task_status_id = 1;
            $task->updated_by = \Auth::user()->id;
            $task->save();

        } else {
            $task = ApplicantTask::find($ApplicantInterview->task->id);
            $task->status = "Completed";
            $task->task_status_id = 2;
            $task->updated_by = \Auth::user()->id;
            $task->save();
        }
        $ApplicantInterview->updated_by = \Auth::user()->id;
        $ApplicantInterview->save();
                
        if($ApplicantInterview->wasChanged())      
            return response()->json(["msg"=>"Task Finished","status"=>"Completed"],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);

    }

    
    public function interviewStartTimeUpdate(Request $request) {
        
        $unlockedData = NULL;
        //dd($request->applicant_task_id);
        if($request->applicant_id && $request->applicant_task_id) {
            $applicantTaskId = $request->applicant_task_id;
            $authId = \Auth::id();

            $findInterview = ApplicantInterview::where("user_id",$authId)
                ->where('applicant_id',$request->applicant_id)
                ->where('start_time',NULL)
                ->where('interview_result','<>' , 'Pass')->get()->first();

            if(!$findInterview) {

                $interview = ApplicantInterview::create([
                                        'user_id' =>$authId,
                                        'applicant_id' =>$request->applicant_id,
                                        'applicant_task_id' => $applicantTaskId,
                                        'applicant_document_id' => NULL,
                                        'interview_date' => date("Y-m-d"),
                                        'start_time' => NULL,
                                        'end_time' => NULL,
                                        'interview_result' =>'N/A',
                                        'created_by' => $authId
                ]);

            } else {
                $interview = $findInterview;
            }
            
            $ApplicantInterview = $data = ApplicantInterview::find($interview->id);
            ApplicantViewUnlock::where(['user_id' =>$data->user_id,'applicant_id' =>$data->applicant_id])->delete();
            $unlockedData = ApplicantViewUnlock::create([
                    'user_id' =>$data->user_id,
                    'applicant_id' =>$data->applicant_id,
                    'token' => Str::random(16),
                    'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
                    'created_by' => \Auth::id()
            ]);

            $ref = route('applicant.interview.profile.view',[$data->applicant_id,$interview->id,$unlockedData->token]);
        }
        if(!isset($ApplicantInterview ))
        $ApplicantInterview = ApplicantInterview::find($request->id);

        $ApplicantInterview->start_time = $startTime = date("H:i",time());
        
        $ApplicantInterview->save();

        $task = ApplicantTask::find($ApplicantInterview->task->id);

        $task->status = "In Progress";
        $task->updated_by = \Auth::id();
        
        $task->save();

        if($ApplicantInterview->wasChanged())  
           
            return response()->json(["msg"=>"Time Started", "data"=>["start"=> date("h:i a", strtotime($startTime)),"status"=>"In Progress","ref"=> (isset($ref))? $ref : null]],200);
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

    public function interviewFileRemove($id) {

        $ApplicantInterview = ApplicantInterview::find($id);

        $ApplicantDocument = ApplicantDocument::find($ApplicantInterview->applicant_document_id);
        $ApplicantDocument->forceDelete();
        //ApplicantDocument::destroy($ApplicantInterview->applicant_document_id);

        $ApplicantInterview->applicant_document_id = null;
        
        $ApplicantInterview->save();

        if($ApplicantInterview->wasChanged())     

            return response()->json(["msg"=>"Removed"],200);
        else
            return response()->json(["msg"=>"Nothing Changed"],422);
    }
}
