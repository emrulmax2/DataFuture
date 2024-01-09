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
use App\Models\UserRole;
use App\Models\Semester;
use App\Models\Course;
use App\Models\Status;
use App\Models\AcademicYear;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\ApplicantProposedCourse;
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
            'semesters' => Semester::all(),
            'courses' => Course::all(),
            'academic' => AcademicYear::all(),
            'statuses' => Status::where('type', 'Applicant')->get(),
            'unfinishedInterviewCount' =>$unfinishedInterviewCount
        ]);
    }

    public function list(Request $request){
        
            $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
            $status = (isset($request->status) && $request->status !="" ? $request->status : '');

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
            $sorts = [];
            
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;
         
            $query = TaskList::with('applicant')->orderByRaw(implode(',', $sorts));
             
            $query->where('interview','yes');
            
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
                    
                        foreach ($list->applicant as $applicantData) {
                            $applicant = Applicant::find($applicantData->id);
                            $ApplicantTaskInfo = ApplicantTask::where(["applicant_id"=>$applicantData->id,"task_list_id"=>$list->id,"status"=>"Pending"])->get()->first();
                            if($ApplicantTaskInfo ) {
                                $tasklistUserId = TaskListUser::where(["task_list_id"=>$ApplicantTaskInfo->task_list_id])->pluck('user_id')->all();
                                $isAdmin = 0;
                                $logId = $request->user()->id;
                                $roles = \Auth::user()->roles;
                                foreach ($roles as $role):
                                    
                                    if($role->type == "Admin") {
                                        $isAdmin =1 ;
                                        break;
                                    }
                                endforeach;
                                
                                if(in_array($logId, $tasklistUserId) || $isAdmin) {
                                
                                    $ApplicantInterviewData = ApplicantInterview::where(["applicant_id"=>$applicantData->id,"applicant_task_id"=>$ApplicantTaskInfo->id,"interview_status"=>'Completed'])
                                                                ->whereIn('interview_result', ['Pass','N/A','NULL'])->get()->first();
                                    //dd($ApplicantInterviewData);      

                                    $isFilterd = 0;
                                    if(isset($request->status) && $status=="applicantNumber") {
                                    $isFilterd = ($applicantData->application_no==$queryStr) ? 'Filtered' : 0;

                                    if($isFilterd) {
                                        if(!$ApplicantInterviewData )
                                            $nestedDataContainer[$k++] = ["data"=> [ 
                                                                                    "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                                    "id"=>$applicantData->id,
                                                                                    'register'=>$applicantData->application_no,
                                                                                    'task_list_id'=>$list->id
                                                                                ], 
                                                                                "location" => $ApplicantTaskInfo->status, 
                                                                                "gender" =>$applicant->sexid->name, 
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
                                                                                "gender" =>$applicant->sexid->name, 
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
                                                                                "gender" =>$applicant->sexid->name, 
                                                                                "col" =>$applicantData->application_no, 
                                                                                "dob" =>date("d/m/Y",strtotime($applicantData->date_of_birth))
                                                                        ];
                                    }
                                } else {
                                    $nestedDataContainer = "No Interview access available for current logged in user";
                                }
                            }
                        }
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $nestedDataContainer]);
        //}
    }

    public function completedList(Request $request){
        $instances = (isset($request->instances) && !empty($request->instances) ? $request->instances : []);
        $courseCreationId = (isset($request->courseCreationId) && !empty($request->courseCreationId) ? $request->courseCreationId : []);

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;

        $applicants = ApplicantProposedCourse::select(DB::raw('group_concat(applicant_id) as applicant_ids'))
                                                    ->where(["course_creation_id" => $courseCreationId])
                                                    ->get()
                                                    ->first();
        
        $query = ApplicantInterview::with('applicant')
                                    ->orderByRaw(implode(',', $sorts))
                                    ->whereIn('applicant_id',[$applicants->applicant_ids])
                                    ->where('interview_status','Completed');

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

                
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);
        $Query = $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();

        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'applicant_number' => $list->applicant->application_no,
                    'name' => $list->applicant->title->name." ".$list->applicant->full_name,
                    'date' => $list->interview_date,
                    'gender' => $list->applicant->gender,
                    'status' => $list->interview_status,
                    'time'=> ($list->start_time ? $list->start_time : "00:00") ." - ". ($list->end_time ? $list->end_time : "00:00"), 
                    'result' => $list->interview_result,
                    'interviewer' => $list->user->name
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    
    }

    public function showInstances(Request $request){
        $semester = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $course   = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $academic  = (isset($request->academic) && !empty($request->academic) ? $request->academic : []);
        $courseCreationId = CourseCreation::where(["semester_id"=>$semester,"course_id"=>$course])->get()->first();
        $instances = CourseCreationInstance::where(['academic_year_id'=>$academic,"course_creation_id"=>$courseCreationId->id])->get();

        return response()->json(['instances' => $instances,'courseCreationId' => $courseCreationId->id]);
    }

    public function interviewAssignedList($userId) {

        return view('pages.interview.assigned.access.staff', [
            'title' => 'Applicant List For Interview Session',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('staff.dashboard')],
                ['label' => 'Interview List', 'href' => route('interviewlist')],
                ['label' => 'Interview Session List', 'href' => 'javascript:void(0);'],
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
                'interview_result' =>'N/A',
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
        $unlockedData = NULL;
        //$userRole = UserRole::where(["user_id"=>$data->user_id])->get()->first();
        //$role = Role::where(['id'=>$userRole->role_id])->get()->first();
        //$roleType = $role->type;
        
        if($data->user_id == \Auth::id()) {
            ApplicantViewUnlock::where(['user_id' =>$data->user_id,'applicant_id' =>$data->applicant_id])->delete();
            $unlockedData = ApplicantViewUnlock::create([
                    'user_id' =>$data->user_id,
                    'applicant_id' =>$data->applicant_id,
                    'token' => Str::random(16),
                    'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
                    'created_by' => \Auth::id()
                ]);

        // } elseif($roleType == "Admin") {

        //     ApplicantViewUnlock::where(['user_id' =>$data->user_id,'applicant_id' =>$data->applicant_id])->delete();
        //     $unlockedData = ApplicantViewUnlock::create([
        //             'user_id' =>$data->user_id,
        //             'applicant_id' =>$data->applicant_id,
        //             'token' => Str::random(16),
        //             'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
        //             'created_by' => \Auth::id()
        //         ]);    
        }
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
            return response()->json(["msg"=>"Invalid Access"],404);
        }
    }

    public function unlockInterViewDirect(InterviewerUnlockDirectRequest $request)
    {
        $ApplicantData = Applicant::where(["date_of_birth"=>date("Y-m-d",strtotime($request->dob)),"id"=>$request->applicantId])->get()->first();
        $unlockedData = NULL;

        if($ApplicantData) {
            $applicantTaskData = ApplicantTask::where(['task_list_id'=>$request->taskListId,'applicant_id'=>$request->applicantId])->get()->first();
            $authId = \Auth::id();

            $findInterview = ApplicantInterview::where("user_id",$authId)
                ->where('applicant_id',$request->applicantId)
                ->where('start_time',NULL)
                ->where('interview_result','<>' , 'Pass')->get()->first();

            if(!$findInterview) {

                $interview = ApplicantInterview::create([
                                        'user_id' =>$authId,
                                        'applicant_id' =>$request->applicantId,
                                        'applicant_task_id' => $applicantTaskData->id,
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

            $data = ApplicantInterview::find($interview->id);
            ApplicantViewUnlock::where(['user_id' =>$data->user_id,'applicant_id' =>$data->applicant_id])->delete();
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
                "ref"=>route('applicant.interview.profile.view',["id" => $data->applicant_id,"interview" => ($interview->id *1),"token" =>$unlockedData->token] )],200);
        
        } else {

            return response()->json(["msg"=>"Invalid Birth Date"],404);
        }
    }
    public function unlockInterViewOnly(InterviewerUnlockDirectRequest $request)
    {
        $ApplicantData = Applicant::where(["date_of_birth"=>date("Y-m-d",strtotime($request->dob)),"id"=>$request->applicantId])->get()->first();
        $unlockedData = NULL;

        if($ApplicantData) {
            $applicantTaskData = ApplicantTask::where(['task_list_id'=>$request->taskListId,'applicant_id'=>$request->applicantId])->get()->first();
            $authId = \Auth::id();
            ApplicantViewUnlock::where(['user_id' =>$authId,'applicant_id' =>$applicantTaskData->applicant_id])->delete();
            
            $unlockedData = ApplicantViewUnlock::create([
                    'user_id' =>$authId,
                    'applicant_id' =>$applicantTaskData->applicant_id,
                    'token' => Str::random(16),
                    'expired_at' => date("Y-m-d H:i:s", strtotime("+1 hours")),
                    'created_by' => \Auth::id()
                ]);
        }
        if($unlockedData) {

            $resultData = [
                "applicantId" => $request->applicantId,
                "token" =>  $unlockedData->token
            ];
            
            return response()->json(["msg"=>"Profile Unlocked",
                "data"=>$resultData,
                "ref"=>route('applicant.interview.profile.viewonly',["id" => $request->applicantId,"applicant_task" => $applicantTaskData->id,"token" =>$unlockedData->token] )],200);
        
        } else {

            return response()->json(["msg"=>"Invalid Birth Date"],404);
        }
    }
    // route applicant.interview.profile.view
    public function profileView($id,$interview,$token) {
        
        return view('pages.interviewlist.profiles.show', [

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

    public function profileViewOnly( $id,$applicant_task,$token) {
        
        return view('pages.interviewlist.profiles.showonly', [

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
            'applicantTask' => ApplicantTask::find($applicant_task)
        ]);

    } 
    
}
