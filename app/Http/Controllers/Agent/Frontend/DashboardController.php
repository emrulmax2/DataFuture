<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\AgentApplicationCheck;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        //$terms = TermDeclaration::orderBy('id','desc')->get();
        $userData = Auth::guard('agent')->user();
        $agentUserList = AgentUser::where('id',$userData->id)->orWhere('parent_id',$userData->id)->get()->pluck('id')->toArray();
        $agents = Agent::whereIn('agent_user_id',$agentUserList)->orderBy('first_name','ASC')->get();
        $data = AgentApplicationCheck::where('agent_user_id',$userData->id)->whereNull("applicant_id")->get();
        return view('pages.agent.dashboard.index', [
            'title' => 'Agent Dashboard - London Churchill College',
            'breadcrumbs' => [],
            'user' => $userData,
            'recentData' => $data,
            'totalRecentApplication' => count($data),
            'academicYears' => AcademicYear::all(),
            'courses' => Course::orderBy('name','asc')->get(),
            'semesters' => Semester::orderBy('id','desc')->get(),
            'statuses' => Status::where('type','applicant')->get(),
            'agents' =>$agents,
        ]);
    }

    public function list(Request $request){

     
        $semesters = (isset($request->semesters) && !empty($request->semesters) ? $request->semesters : []);
        $courses = (isset($request->courses) && !empty($request->courses) ? $request->courses : []);
        $statuses = (isset($request->statuses) && !empty($request->statuses) ? $request->statuses : []);
        $agents = (isset($request->agents) && !empty($request->agents) ? $request->agents : []);
        $refno = (isset($request->refno) && !empty($request->refno) ? $request->refno : '');
        $email = (isset($request->email) && !empty($request->email) ? $request->email : '');
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $phone = (isset($request->phone) && !empty($request->phone) ? $request->phone : '');
        $dob = (isset($request->dob) && !empty($request->dob) ? date('Y-m-d', strtotime($request->dob)) : '');

        $courseCreationId = [];
        if(!empty($courses)):
            $courseCreations = CourseCreation::whereIn('course_id', $courses)->get();
            if(!$courseCreations->isEmpty()):
                foreach($courseCreations as $cc):
                    $courseCreationId[] = $cc->id;
                endforeach;
            else:
                $courseCreationId[1] = '0';
            endif;
        endif;

        

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        $userData = Auth::guard('agent')->user();
        $query = Applicant::orderByRaw(implode(',', $sorts));
  
        if(count($agents)<=0) {
            array_push($agents,$userData->id);
            $subAgents = AgentUser::where('parent_id',$userData->id)->get()->pluck('id')->toArray();
            $agents = array_merge($agents,$subAgents);
            

        }

        if(!empty($refno)): $query->where('application_no', $refno); endif;
        if(!empty($email)): $query->where('email', $email); endif;
        if(!empty($phone)): $query->where('phone', $phone); endif;
        if(!empty($dob)): $query->where('date_of_birth', $dob); endif;
        if(!empty($statuses)): $query->whereIn('status_id', $statuses); else: $query->where('status_id', '>', 1); endif;
        if(!empty($queryStr)):
            $query->where('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
            $query->orWhereRaw(
                "concat(first_name, ' ', last_name) like '%" . $queryStr . "%' "
            );
        endif;
        if(!empty($semesters) || !empty($courseCreationId)):

            $query->whereHas('course', function($qs) use($semesters, $courses, $courseCreationId){
                if(!empty($semesters)): $qs->whereIn('semester_id', $semesters); endif;
                if(!empty($courses) && !empty($courseCreationId)): $qs->whereIn('course_creation_id', $courseCreationId); endif;
            });

        endif;
        if(!empty($agents)): $query->whereIn('agent_user_id', $agents); endif;

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
                $newApply =false;
                if($list->status->id==8) {
                    $applicationList = Applicant::where('applicant_user_id',$list->applicant_user_id)->orderBy('id', 'DESC')->get()->first();
                    
                    if($applicationList->status_id==8)
                        $newApply = true;
                    
                }
                $studentFound = Student::where('applicant_id',$list->id)->get()->first();
                $agentCheck = AgentApplicationCheck::whereIn('agent_user_id',$agents)->where("email",$list->users->email)->where("mobile",$list->users->phone)->get()->first();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => $list->application_no,
                    'name' => $list->title->name.' '.$list->first_name.' '.$list->last_name,
                    'dob' => $list->date_of_birth,
                    'applicationCheck' => isset($agentCheck->id) && !empty($agentCheck->id) ? $agentCheck->id : '',
                    'gender' => isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : '',
                    'course' => (isset($list->course->creation->course->name) ? $list->course->creation->course->name : '').(isset($list->course->semester->name) ? ' - '.$list->course->semester->name : ''),
                    'submission_date' => $list->submission_date,
                    'referral_code' => $list->referral_code,
                    'status' => (!empty($list->submission_date) ? (isset($list->status->name) ? $list->status->name : 'Unknown') : 'Incomplete'),
                    'is_student' => (!empty($studentFound) ? 1 : 0),
                    'deleted_at' => $list->deleted_at,
                    'applicant_user_id' => $list->applicant_user_id,
                    'new_apply' => $newApply
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
    }
}
