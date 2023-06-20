<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskList;
use App\Models\ApplicantTask;
use App\Models\Applicant;
use App\Models\User;

class InterviewListController extends Controller
{
    public function index()
    {
        return view('pages/interviewlist/index', [
            'title' => 'Interview List - LCC Data Future Managment',
            'breadcrumbs' => [['label' => 'Interview List', 'href' => 'javascript:void(0);']],
            'tasklists' => TaskList::all(),
            'applicanttasks' => ApplicantTask::all(),
            'applicants' => Applicant::all(),
            'users' => User::all(),
        ]);
    }

    public function list(Request $request){
        $taskInterviewdata = TaskList::where('interview', '=', 'Yes');
        //foreach($taskInterviewdata as $interviewdata) {
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
            
            $query = TaskList::with('applicant')->orderByRaw(implode(',', $sorts));
            if(!empty($queryStr)):
                $query->where('name','LIKE','%'.$queryStr.'%');
            endif;
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
                        $nestedDataContainer[$k++] = ["data"=>["name" => $applicantData->title->name." ".$applicantData->full_name,"id"=>$applicantData->id,'register'=>$applicantData->application_no], "location" => "Germany", "gender" =>$applicantData->gender, "col" =>$applicantData->application_no, "dob" =>date("d/m/Y",strtotime($applicantData->date_of_birth))];
                    }
                    $data[] = [
                        'id' => $list->id,
                        'sl' => $i,
                        'taskname' => $list->name,
                        '_children' => $nestedDataContainer,
                        
                    ];
                    $i++;
                endforeach;
            endif;
            return response()->json(['last_page' => $last_page, 'data' => $data]);
        //}
    }

    public function assaignInterviewer(Request $request){
        
    }
}
