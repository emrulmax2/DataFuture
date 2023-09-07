<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Country;
use App\Models\Disability;
use App\Models\Ethnicity;
use App\Models\Title;
use App\Models\ApplicantViewUnlock;
use App\Models\ApplicantInterview;
use App\Http\Requests\InterviewerUpdateRequest;
use App\Http\Requests\InterviewerUnlockRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InterviewAssignedController extends Controller
{
    public function index()
    {
        return view('pages.interview.assigned.index', [
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
            $status = (isset($request->status) && $request->status !="" ? $request->status : '');
            // $tasklist = (isset($request->tasklist) && $request->tasklist > 0 ? $request->tasklist : '');
            // $applicanttask = (isset($request->applicanttask) && $request->applicanttask > 0 ? $request->applicanttask : '');
            //$applicant = (isset($request->applicant) && $request->applicant > 0 ? $request->applicant : '');

            $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
            $sorts = [];
            foreach($sorters as $sort):
                $sorts[] = $sort['field'].' '.$sort['dir'];
            endforeach;
            $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
            $perpage = (isset($request->size) && $request->size > 0 ? $request->size : 10);
            
            $query = TaskList::with('applicant')->orderByRaw(implode(',', $sorts));
            
            
            // if(!empty($queryStr)):
            //     $query->where('name','LIKE','%'.$queryStr.'%');
            // endif;
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
                    
                    // \DB::enableQueryLog();
                    // $list->applicant;
                    // dd(\DB::getQueryLog());
                    $k =0;
                    $nestedDataContainer = [];
                    
                        foreach ($list->applicant as $applicantData) {
                            $ApplicantTaskInfo = ApplicantTask::where(["applicant_id"=>$applicantData->id,"task_list_id"=>$list->id])->get()->first();
                            $ApplicantInterviewData = ApplicantInterview::where(["applicant_id"=>$applicantData->id,"applicant_task_id"=>$ApplicantTaskInfo->id])->get()->first();
                            $ApplicantInterviewData->user;
                           
                            $isFilterd = 0;
                            if(isset($request->status) && $status=="applicantNumber") {
                               $isFilterd = ($applicantData->application_no==$queryStr) ? 'Filtered' : 0;

                               if($isFilterd) {
                                if($ApplicantInterviewData)
                                    $nestedDataContainer[$k++] = ["data"=> [ 
                                                                            "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                            "id"=>$applicantData->id,
                                                                            'register'=>$applicantData->application_no,
                                                                            'task_list_id'=>$list->id
                                                                        ], 
                                                                        "gender" =>$applicantData->gender, 
                                                                        "col" =>$applicantData->application_no, 
                                                                        "interviewer" => $ApplicantInterviewData->user->name, 
                                                                        "assignedDate" =>date("d/m/Y h:i a",strtotime($ApplicantInterviewData->updated_at)),
                                                                        "status" => $ApplicantTaskInfo->status
                                                                ];

                               }

                            } else if(isset($request->status) && $status=="applicantName") {
                                $isFilterd = ( stristr($applicantData->full_name,$queryStr) ) ? 'Filtered' : 0;

                               if($isFilterd) {
                                if($ApplicantInterviewData)
                                    $nestedDataContainer[$k++] = ["data"=> [ 
                                                                            "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                            "id"=>$applicantData->id,
                                                                            'register'=>$applicantData->application_no,
                                                                            'task_list_id'=>$list->id
                                                                        ], 
                                                                        "gender" =>$applicantData->gender, 
                                                                        "col" =>$applicantData->application_no, 
                                                                        "interviewer" => $ApplicantInterviewData->user->name, 
                                                                        "assignedDate" =>date("d/m/Y h:i a",strtotime($ApplicantInterviewData->updated_at)),
                                                                        "status" => $ApplicantTaskInfo->status
                                                                ];
                                }

                            } else {

                                if($ApplicantInterviewData)
                                    $nestedDataContainer[$k++] = ["data"=> [ 
                                                                            "name" => $applicantData->title->name." ".$applicantData->full_name,
                                                                            "id"=>$applicantData->id,
                                                                            'register'=>$applicantData->application_no,
                                                                            'task_list_id'=>$list->id
                                                                        ], 
                                                                        "gender" =>$applicantData->gender, 
                                                                        "col" =>$applicantData->application_no, 
                                                                        "interviewer" => $ApplicantInterviewData->user->name, 
                                                                        "assignedDate" =>date("d/m/Y h:i a",strtotime($ApplicantInterviewData->updated_at)),
                                                                        "status" => $ApplicantTaskInfo->status
                                                                ];
                            }
                        }
                    if($nestedDataContainer) {
                        $data[] = [
                            'id' => $list->id,
                            'sl' => $i,
                            'taskname' => $list->name,
                            '_children' => $nestedDataContainer,
                            
                        ];
                        $i++;
                    }
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $data]);
        
    }

    
}
