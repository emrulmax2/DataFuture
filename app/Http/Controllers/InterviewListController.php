<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Role;
use App\Models\Country;
use App\Models\Disability;
use App\Models\Ethnicity;
use App\Models\Title;
use App\Models\ApplicantViewUnlock;
use App\Models\ApplicantInterview;
use App\Models\TaskListUser;
use App\Http\Requests\InterviewerUpdateRequest;
use App\Http\Requests\InterviewerUnlockRequest;
use App\Http\Requests\InterviewerUnlockDirectRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InterviewListController extends Controller
{
    public function index()
    {
        $user = User::find(\Auth::id());
        
        
        $unfinishedInterviewCount = 0;
        
        foreach ($user->interviews as $interview) {
            $ApplicantTask = ApplicantTask::find($interview->applicant_task_id);
             if($ApplicantTask->status!="Completed") {
                 $unfinishedInterviewCount++;
            }
        }

        return view('pages.interviewlist.index', [
            'title' => 'Interview List - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Interview List', 'href' => 'javascript:void(0);']],
            'tasklists' => TaskList::all(),
            'applicanttasks' => ApplicantTask::all(),
            'applicants' => Applicant::all(),
            'users' => User::all(),
            'unfinishedInterviewCount' =>$unfinishedInterviewCount
        ]);
    }

    public function list(Request $request){
        
            $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
            $status = (isset($request->status) && $request->status !="" ? $request->status : '');

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'asc']));
            $sorts = [];
            
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;
            
            $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
            $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
            
            $query = TaskList::with('applicant')->orderByRaw(implode(',', $sorts));
            
 
            $query->where('interview','yes');
            
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
                    

                    $k =0;
                    $nestedDataContainer = [];
                    
                        foreach ($list->applicant as $applicantData) {
                            $ApplicantTaskInfo = ApplicantTask::where(["applicant_id"=>$applicantData->id,"task_list_id"=>$list->id])->get()->first();
                            
                            $tasklistUserId = TaskListUser::where(["task_list_id"=>$ApplicantTaskInfo->task_list_id])->pluck('user_id')->all();
                            
                            $logId = $request->user()->id;

                            if(in_array($logId, $tasklistUserId)) {
                            
                                $ApplicantInterviewData = ApplicantInterview::where(["applicant_id"=>$applicantData->id,"applicant_task_id"=>$ApplicantTaskInfo->id])->get()->first();
                                $isFilterd = 0;
                                if(isset($request->status) && $status=="applicantNumber") {
                                $isFilterd = ($applicantData->application_no==$queryStr) ? 'Filtered' : 0;

                                if($isFilterd) {
                                    if(!$ApplicantInterviewData)
                                        $nestedDataContainer[$k++] = ["data"=> [ 
                                                                                "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                                "id"=>$applicantData->id,
                                                                                'register'=>$applicantData->application_no,
                                                                                'task_list_id'=>$list->id
                                                                            ], 
                                                                            "location" => $ApplicantTaskInfo->status, 
                                                                            "gender" =>$applicantData->gender, 
                                                                            "col" =>$applicantData->application_no, 
                                                                            "dob" =>date("d/m/Y",strtotime($applicantData->date_of_birth))
                                                                    ];

                                }

                                } else if(isset($request->status) && $status=="applicantName") {
                                    $isFilterd = ( stristr($applicantData->full_name,$queryStr) ) ? 'Filtered' : 0;

                                if($isFilterd) {
                                    if(!$ApplicantInterviewData)
                                        $nestedDataContainer[$k++] = ["data"=> [ 
                                                                                "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                                "id"=>$applicantData->id,
                                                                                'register'=>$applicantData->application_no,
                                                                                'task_list_id'=>$list->id
                                                                            ], 
                                                                            "task" => $list->name,
                                                                            "location" => $ApplicantTaskInfo->status, 
                                                                            "gender" =>$applicantData->gender, 
                                                                            "col" =>$applicantData->application_no, 
                                                                            "dob" =>date("d/m/Y",strtotime($applicantData->date_of_birth))
                                                                    ];
                                    }

                                } else {

                                    if(!$ApplicantInterviewData)
                                        $nestedDataContainer[$k++] = ["data"=> [ 
                                                                                "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                                "id"=>$applicantData->id,
                                                                                'register'=>$applicantData->application_no,
                                                                                'task_list_id'=>$list->id
                                                                            ], 
                                                                            "task" => $list->name,
                                                                            "location" => $ApplicantTaskInfo->status, 
                                                                            "gender" =>$applicantData->gender, 
                                                                            "col" =>$applicantData->application_no, 
                                                                            "dob" =>date("d/m/Y",strtotime($applicantData->date_of_birth))
                                                                    ];
                                }
                            } else {
                                $nestedDataContainer = "No Interview access available for current logged in user";
                            }
                        }
                    // this code is for + layer adding to table
                    // if($nestedDataContainer) {
                    //     $data[] = [
                    //         'id' => $list->id,
                    //         'sl' => $i,
                    //         'taskname' => $list->name,
                    //         '_children' => $nestedDataContainer,
                            
                    //     ];
                    //     $i++;
                    // }
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $nestedDataContainer]);
        //}
    }

    public function interviewAssignedList($userId) {

        return view('pages/users/access/staff', [
            'title' => 'Potential Interviewee List',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('staff.dashboard')],
                ['label' => 'Staff Dashboard', 'href' => 'javascript:void(0);'],
            ],
            'user' => User::find($userId),
            'role' => '',
                   
            
        ]);

    }

    public function assaignInterviewer(InterviewerUpdateRequest $request){
        $applicantList = $request->data;
        
        $request->user;
        foreach ($applicantList as $data) {
           
            $data = json_decode(json_encode((object) $data), FALSE);
            
            $applicantTaskData = ApplicantTask::where(['task_list_id'=>$data->task_list_id,'applicant_id'=>$data->id])->get()->first();
            
            ApplicantInterview::create([
                'user_id' =>$request->user,
                'applicant_id' =>$data->id,
                'applicant_task_id' => $applicantTaskData->id,
                'applicant_document_id' => NULL,
                'interview_date' => date("Y-m-d"),
                'start_time' => NULL,
                'end_time' => NULL,
                'interview_status' =>'N/A',
                'created_by' => \Auth::id()
            ]);
        }
        return response()->json(["msg"=>"Data Created"],200);
    }

    public function updateAssaignInterviewer(InterviewerUpdateRequest $request){
        $applicantList = $request->data;
        $change = 0;
        $request->user;
        foreach ($applicantList as $data) {
           
            $data = json_decode(json_encode((object) $data), FALSE);
            
            $applicantTaskData = ApplicantTask::where(['task_list_id'=>$data->task_list_id,'applicant_id'=>$data->id])->get()->first();
            $applicantInterview = ApplicantInterview::where(['applicant_id' =>$data->id,'applicant_task_id' => $applicantTaskData->id,])->get()->first();

            $data = ApplicantInterview::find($applicantInterview->id);
            $data->user_id = $request->user;
            $data->updated_by = \Auth::id();
            $data->save();

            if($data->wasChanged()) {
                $change =1;
            }
            
        }
        
        return  ($change) ? response()->json(["msg"=>"Data Updated"],200) : response()->json(["msg"=>"No Data Change"],422);
    }

    public function unlockInterView(InterviewerUnlockRequest $request)
    {
        $data = ApplicantInterview::find($request->interviewId);
        DB::enableQueryLog();
        $ApplicantData = Applicant::where(["date_of_birth"=>date("Y-m-d",strtotime($request->dob)),"id"=>$data->applicant_id])->get()->first();
        $query = DB::getQueryLog();
        //dd($query);
        $unlockedData = NULL;
        if($ApplicantData)
        $unlockedData = ApplicantViewUnlock::create([
                'user_id' =>$data->user_id,
                'applicant_id' =>$data->applicant_id,
                'token' => Str::random(16),
                'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
                'created_by' => \Auth::id()
            ]);
        if($unlockedData) {
            $resultData = [
                "applicantId" => $data->applicant_id,
                "interviewId" => ($request->interviewId *1),
                "token" =>  $unlockedData->token
            ];
            return response()->json(["msg"=>"Profile Unlocked",
                "data"=>$resultData,
                "ref"=>route('applicant.interview.profile.view',["id" => $data->applicant_id,"interview" => ($request->interviewId *1),"token" =>  $unlockedData->token] )],200);
        } else {
            return response()->json(["msg"=>"Invalid Birth Date"],404);
        }
    }
    public function unlockInterViewDirect(InterviewerUnlockDirectRequest $request)
    {
        $ApplicantData = Applicant::where(["date_of_birth"=>date("Y-m-d",strtotime($request->dob)),"id"=>$request->applicantId])->get()->first();
        $unlockedData = NULL;

        if($ApplicantData) {

            $applicantTaskData = ApplicantTask::where(['task_list_id'=>$request->taskListId,'applicant_id'=>$request->applicantId])->get()->first();
            $authId = \Auth::id();
            $interview = ApplicantInterview::create([
                                    'user_id' =>$authId,
                                    'applicant_id' =>$request->applicantId,
                                    'applicant_task_id' => $applicantTaskData->id,
                                    'applicant_document_id' => NULL,
                                    'interview_date' => date("Y-m-d"),
                                    'start_time' => NULL,
                                    'end_time' => NULL,
                                    'interview_status' =>'N/A',
                                    'created_by' => $authId
            ]);
            $data = ApplicantInterview::find($interview->id);

            $unlockedData = ApplicantViewUnlock::create([
                    'user_id' =>$data->user_id,
                    'applicant_id' =>$data->applicant_id,
                    'token' => Str::random(16),
                    'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
                    'created_by' => \Auth::id()
                ]);
        }
        if($unlockedData) {

            $resultData = [
                "applicantId" => $data->applicant_id,
                "interviewId" => ($interview->id *1),
                "token" =>  $unlockedData->token
            ];
            return response()->json(["msg"=>"Profile Unlocked",
                "data"=>$resultData,
                "ref"=>route('applicant.interview.profile.view',["id" => $data->applicant_id,"interview" => ($interview->id *1),"token" =>  $unlockedData->token] )],200);
        
        } else {

            return response()->json(["msg"=>"Invalid Birth Date"],404);
        }
    }
    // route applicant.interview.profile.view
    public function profileView($id,$interview,$token) {
        
        return view('pages.interviewlist.profiles.showduplicate', [

            'title' => 'Admission Management - LCC Data Future Managment',
            'breadcrumbs' => [
                ['label' => 'Students Admission', 'href' => route('admission')],
                ['label' => 'Student Details', 'href' => 'javascript:void(0);'],
            ],
            'applicant' => Applicant::find($id),
            'titles' => Title::all(),
            'country' => Country::all(),
            'ethnicity' => Ethnicity::all(),
            'disability' => Disability::all(),
            'users' => User::all(),
            'interview' => ApplicantInterview::find($interview),
        ]);

    } 
    
}
