<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Agent;
use App\Models\ReferralCode;
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
        $userData = Auth::guard('agent')->user();
        $agentUserList = AgentUser::where('id', $userData->id)->orWhere('parent_id', $userData->id)->get()->pluck('id')->toArray();
        $agents = Agent::whereIn('agent_user_id', $agentUserList)->orderBy('first_name', 'ASC')->get();
        $data = AgentApplicationCheck::where('agent_user_id', $userData->id)->whereNull("applicant_id")->get();

        /* The hero's figures and the side column, all over the same scope the
           list uses: this agent and their sub-agents. */
        $scoped = Applicant::whereIn('agent_user_id', $agentUserList);
        $stats = [
            'submitted'  => (clone $scoped)->where('status_id', '>', 1)->count(),
            'processing' => (clone $scoped)->whereIn('status_id', [2, 3, 4])->count(),
            'offers'     => (clone $scoped)->whereIn('status_id', [5, 6, 7])->count(),
            'awaiting'   => $data->filter(fn($c) => !($c->email_verified_at && $c->mobile_verified_at))->count(),
            'active'     => (clone $scoped)->where('status_id', '>', 1)->whereNotIn('status_id', [8, 9, 46])->count(),
        ];

        /* Drafts: applications this agent started but has not submitted. The
           column draws five (plus one under the fold) and pages the rest in
           over AJAX as it is scrolled - see drafts(). */
        $draftsPage = $this->draftsPage($agentUserList, 0, self::DRAFTS_FIRST);
        $drafts = $draftsPage['data'];

        $agentRow = $userData->agent;

        /* The agent / sub-agent tag selector: one option per agent user - the
           signed-in agency first, then its sub-agents - not one per row of
           `agents`, which holds hundreds of legacy contacts per user. The list
           filters on agent_user_id, so that is the option's value. */
        $codes = ReferralCode::whereIn('agent_user_id', $agentUserList)->pluck('code', 'agent_user_id');
        $agentOptions = AgentUser::whereIn('id', $agentUserList)->with('agent')->get()
            ->sortBy(fn($u) => $u->id === $userData->id ? 0 : 1)
            ->map(fn($u) => [
                'value' => $u->id,
                'label' => $u->agent ? ($u->agent->organization ?: trim($u->agent->first_name.' '.$u->agent->last_name)) : $u->email,
                'code'  => $codes[$u->id] ?? null,
            ])->values();

        return view('pages.agent.dashboard.index', [
            'title' => 'Agent Dashboard - London Churchill College',
            'layout' => 'agent-portal',
            'breadcrumbs' => [],
            'user' => $userData,
            'agentName' => $agentRow ? ($agentRow->organization ?: trim($agentRow->first_name.' '.$agentRow->last_name)) : $userData->email,
            'referralCode' => ReferralCode::where('agent_user_id', $userData->id)->value('code'),
            'verified' => (bool) $userData->email_verified_at,
            'stats' => $stats,
            'drafts' => $drafts,
            'draftsOffset' => self::DRAFTS_FIRST,
            'draftsMore' => $draftsPage['has_more'],
            'recentData' => $data,
            'totalRecentApplication' => count($data),
            'academicYears' => AcademicYear::all(),
            'courses' => Course::where('active', 1)->orderBy('name', 'asc')->get(),
            'semesters' => Semester::orderBy('id', 'desc')->get(),
            'statuses' => Status::where('type', 'applicant')->whereIn('id', [1, 2, 3, 4, 5, 6, 7, 8, 9])->orderBy('id')->get(),
            'agents' => $agents,
            'agentOptions' => $agentOptions,
        ]);
    }

    /** The drafts column: five in view and one under the fold on the page, then five a scroll. */
    private const DRAFTS_FIRST = 6;
    private const DRAFTS_PAGE = 5;

    /**
     * One page of the agent's unsubmitted applications, newest first. The wizard
     * opens a draft through the verification record it came from, matched the
     * way the list matches it - by email and mobile. Offsets count query rows:
     * a draft with no such record is left out of the page (it cannot be opened)
     * but still counted, so the next offset never repeats or skips a row.
     */
    private function draftsPage(array $agentUserList, int $offset, int $limit, string $q = ''): array
    {
        $query = Applicant::whereIn('agent_user_id', $agentUserList)->whereNull('submission_date');
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($w) use ($like) {
                $w->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', first_name, last_name) like ?", [$like])
                    ->orWhereHas('users', fn($u) => $u->where('email', 'like', $like)->orWhere('phone', 'like', $like))
                    ->orWhereHas('contact', fn($c) => $c->where('mobile', 'like', $like));
            });
        }
        $rows = (clone $query)->with('users')->orderByDesc('id')->skip($offset)->take($limit)->get();
        $hasMore = (clone $query)->count() > $offset + $limit;

        $data = $rows->map(function ($a) use ($agentUserList) {
            $check = ($a->users)
                ? AgentApplicationCheck::whereIn('agent_user_id', $agentUserList)->where('email', $a->users->email)->where('mobile', $a->users->phone)->first()
                : null;
            return [
                'id' => $a->id,
                'name' => trim($a->first_name.' '.$a->last_name) ?: optional($a->users)->email,
                'created' => $a->created_at,
                'check_id' => optional($check)->id,
            ];
        })->filter(fn($d) => $d['check_id'])->values();

        return ['data' => $data, 'has_more' => $hasMore];
    }

    /** AJAX: the next page of drafts for the dashboard column. */
    public function drafts(Request $request)
    {
        $userData = Auth::guard('agent')->user();
        $agentUserList = AgentUser::where('id', $userData->id)->orWhere('parent_id', $userData->id)->pluck('id')->toArray();
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = min(20, max(1, (int) $request->input('limit', self::DRAFTS_PAGE)));
        $page = $this->draftsPage($agentUserList, $offset, $limit, trim((string) $request->input('q', '')));

        return response()->json(['data' => $page['data'], 'has_more' => $page['has_more'], 'next_offset' => $offset + $limit]);
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
        return response()->json(['last_page' => $last_page, 'total' => $total_rows, 'data' => $data]);
    }
}
