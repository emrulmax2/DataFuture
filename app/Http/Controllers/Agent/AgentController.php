<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Address;
use App\Models\AgentApplicationCheck;
use App\Models\AgentBankDetail;
use App\Models\AgentDocuments;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\CourseCreation;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\Option;
use App\Models\ReferralCode;
use App\Models\Semester;
use App\Models\Status;
use App\Models\Student;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('pages.agent.index', [
            'title' => 'Agent Creations - LCC Data Future Agent Managment',
            'layout' => 'agent-management-top-menu',
            'breadcrumbs' => [['label' => 'Agent Creations', 'href' => 'javascript:void(0);']],
            'agentUser' => AgentUser::all(),
            "unique" => Str::random(10),
        ]);
    }

    public function list(Request $request){
        $queryStr = trim((string) $request->input('querystr', ''));
        $status = (int) $request->input('status', 1);
        $agentUserList = AgentUser::whereNull('parent_id')->pluck('id')->toArray();
        
        $query = Agent::with('AgentUser')->whereIn('agent_user_id', $agentUserList);
        if($status == 2):
            $query->onlyTrashed();
        elseif($status == 0):
            $query->withTrashed();
        endif;

        if(!empty($queryStr)):
            $query->where(function($q) use($queryStr){
                $q->where('first_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('last_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhereRaw("concat(first_name, ' ', last_name) like ?", ['%'.$queryStr.'%'])
                    ->orWhere('organization', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('code', 'LIKE', '%'.$queryStr.'%')
                    ->orWhereHas('AgentUser', function($userQuery) use($queryStr){
                        $userQuery->where('email', 'LIKE', '%'.$queryStr.'%');
                    });
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? ($total_rows > 0 ? $total_rows : 10) : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sortMap = [
            'id' => 'id',
            'name' => 'first_name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'organization' => 'organization',
            'code' => 'code',
            'is_default' => 'is_default',
        ];
        foreach($sorters as $sort):
            $field = $sortMap[$sort['field'] ?? 'id'] ?? 'id';
            $dir = strtoupper((string) ($sort['dir'] ?? 'DESC'));
            $dir = in_array($dir, ['ASC', 'DESC'], true) ? $dir : 'DESC';

            $query->orderBy($field, $dir);
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        $data = array();
        if(!empty($Query)):
            $i = 1;
            foreach($Query as $list):
                $agentUserCount = AgentUser::where('parent_id',$list->agent_user_id)->get()->count();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->full_name,
                    'email' => $list->AgentUser->email ?? '',
                    'initials' => $this->agentInitials($list->full_name),
                    'photo_url' => $this->agentPhotoUrl($list),
                    'organization' => ($agentUserCount) ? $list->organization." [ ".$agentUserCount." ]" : $list->organization,
                    'code' => $list->code,
                    'is_default' => $list->is_default,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function ApplicantionList(Request $request, $id){

        
        //dd($request->id);
        $Agent = Agent::with('AgentUser')->where('id',$id)->get()->first(); 
       
       
        
        $query = Applicant::with(['course','contact','sexid'])->where('agent_user_id', $Agent->AgentUser->id);
        
        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = (isset($request->size) && $request->size == 'true' ? $total_rows : ($request->size > 0 ? $request->size : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : '';

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sorts = [];
        foreach($sorters as $sort):
            $sorts[] = $sort['field'].' '.$sort['dir'];
        endforeach;
        
        $limit = $perpage;
        $offset = ($page > 0 ? ($page - 1) * $perpage : 0);

        $query = $query->orderByRaw(implode(',', $sorts));


        $Query= $query->skip($offset)
               ->take($limit)
               ->get();

        

        $data = array();
        if(!empty($Query)):
            $i = 1;
            $applicant = 0;
            $student = 0;
            foreach($Query as $list):

                if(!isset($data[$list->course->semester->id]["ApplicantCount"])) {
                    $data[$list->course->semester->id]["ApplicantCount"] = 0;
                }
                if(!isset($data[$list->course->semester->id]["StudentCount"])) {
                    $data[$list->course->semester->id]["StudentCount"] = 0;
                }
                $data[$list->course->semester->id]["ApplicantCount"]++;
                $studentData = Student::where("applicant_id",$list->id)->get()->first();
                if($studentData)
                    $data[$list->course->semester->id]["StudentCount"]++;

                $data[$list->course->semester->id]['sl'] = $i;
                $data[$list->course->semester->id]['term'] = $list->course->semester->name;
                $data[$list->course->semester->id]['_children'][] = [
                    "id" =>($studentData) ? $studentData->id : $list->id,
                    "name" => ($studentData) ?  $studentData->full_name : $list->full_name,
                    "status" => ($studentData) ? "Student" : "Applicant",
                    "gender" => ($studentData) ? $studentData->sexid->name : $list->sexid->name,
                    "mobile" =>($studentData) ?  ( ($studentData->contact) ? $studentData->contact->mobile:"" ) : ( ($list->contact) ? $list->contact->mobile : "" ),
                    "ref_no" => ($studentData) ? $studentData->registration_no : $list->application_no,
                ];

                $i++;
            endforeach;
        endif;
        $data = array_values($data);
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }

    public function listByQuery(Request $request, $id){

     
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
        $userData = Agent::find($id);
        $query = Applicant::orderByRaw(implode(',', $sorts));
  
        if(count($agents)<=0) {
            array_push($agents,$userData->agent_user_id);
            $subAgents = AgentUser::where('parent_id',$userData->agent_user_id)->get()->pluck('id')->toArray();
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
                $studentFound = Student::where('applicant_id',$list->id)->get()->first();
                $agentCheck = AgentApplicationCheck::whereIn('agent_user_id',$agents)->where("email",$list->users->email)->where("mobile",$list->users->phone)->get()->first();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'application_no' => $list->application_no,
                    'name' => $list->title->name.' '.$list->first_name.' '.$list->last_name,
                    'initials' => $this->agentInitials($list->first_name.' '.$list->last_name),
                    'photo_url' => $this->applicantPhotoUrl($list),
                    'dob' => $list->date_of_birth,
                    'applicationCheck' => isset($agentCheck->id) && !empty($agentCheck->id) ? $agentCheck->id : '',
                    'gender' => isset($list->sexid->name) && !empty($list->sexid->name) ? $list->sexid->name : '',
                    'course' => (isset($list->course->creation->course->name) ? $list->course->creation->course->name : '').(isset($list->course->semester->name) ? ' - '.$list->course->semester->name : ''),
                    'submission_date' => $list->submission_date,
                    'referral_code' => $list->referral_code,
                    'status' => (!empty($list->submission_date) ? (isset($list->status->name) ? $list->status->name : 'Unknown') : 'Incomplete'),
                    'is_student' => (!empty($studentFound) ? 1 : 0),
                    'current_status' => (!empty($studentFound) ? $studentFound->status->name : ""),
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAgentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgentRequest $request)
    {

        $request->request->add(['created_by' => auth()->user()->id]);

        $User = AgentUser::create([

                'email' => $request->input("email"),
                'password' => $request->input("password"),
                'active' => 1,
                'created_by' => auth()->user()->id,
                
        ]);

        $request->request->add(['agent_user_id' => $User->id]);

        $data = Agent::create($this->agentPayload($request));
       
        $referral = ReferralCode::create([
            'code' => $data->code,
            'type' => 'Agent',
            'agent_user_id' => $data->AgentUser->id,
            'created_by' => auth()->user()->id,
        ]);
        event(new Registered($User));

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function show(Agent $agent_user)
    {

        $employee = $agent_user;
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();

        $agentUserList = AgentUser::where('id',$userData->id)->orWhere('parent_id',$userData->id)->get()->pluck('id')->toArray();
        $agents = Agent::whereIn('agent_user_id',$agentUserList)->orderBy('first_name','ASC')->get();
        $profileName = trim($employee->first_name.' '.$employee->last_name);
        return view('pages.agent.profile.show',[
            'title' => 'Agent Profile - London Churchill College',
            'layout' => 'agent-management-top-menu',
            'breadcrumbs' => [
                ['label' => 'Agent Creations', 'href' => route('agent-user.index')],
                ['label' => 'Profile', 'href' => 'javascript:void(0);'],
            ],
            "user" => $userData,
            "employee" => $employee,
            "postcodeApi" => $PostCodeAPI,
            'courses' => Course::orderBy('name','asc')->get(),
            'semesters' => Semester::orderBy('id','desc')->get(),
            'statuses' => Status::where('type','applicant')->get(),
            'agents' =>$agents,
            'agentProfileInitials' => $this->agentInitials($profileName),
            'agentProfilePhotoUrl' => $this->agentPhotoUrl($employee),
            'profileTabCounts' => [
                'applicants' => Applicant::whereIn('agent_user_id', $agentUserList)->where('status_id', '>', 1)->count(),
                'sub' => AgentUser::where('parent_id', $userData->id)->count(),
                'docs' => AgentDocuments::where('agent_id', $employee->id)->where('type', 1)->count(),
                'pay' => AgentBankDetail::where('agent_id', $employee->id)->count(),
            ],
        ]);
    
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $data = Agent::with('AgentUser')->where('id', $id)->get()->first();
        if($data){
            return response()->json($data);
        }else{
            return response()->json(['message' => 'Something went wrong. Please try later'], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAgentRequest  $request
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAgentRequest $request, Agent $agent_user)
    {
        $is_default = (isset($request->is_default) && $request->is_default > 0 ? $request->is_default : 0);
        $request->request->add(['agent_user_id' => $agent_user->AgentUser->id]);
        $agenUser = AgentUser::find($agent_user->AgentUser->id);

        $emailChanged = $agenUser->email !== $request->input('email');
        $agenUser->email = $request->input('email');
        if($request->filled('password')):
            $agenUser->password = $request->input('password');
        endif;
        $agenUser->updated_by = auth()->user()->id;
        $agenUser->save();
        $agentUserChanged = $agenUser->wasChanged();

        $request->merge(['updated_by' => auth()->user()->id]);
        if($emailChanged) {

            $agenUser->email_verified_at=null;
            $agenUser->save();
            event(new Registered($agenUser));

        }

        $request->merge(['is_default' => $is_default]);
        $agentPayload = $this->agentPayload($request);

        $agent_user->fill($agentPayload);
        $agent_user->save();

        //$request->request->remove('email');
        //$request->request->add(['email' => $request->input('contact_email')]);
        //$request->request->remove('contact_email');

        $agent= Agent::where("agent_user_id",$agent_user->AgentUser->id)->get()->first();

        $agent->fill($agentPayload);
        $agent->save();
        
        if($agentUserChanged || $agent_user->wasChanged() || $agent->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }

    }

    public function addressUpdate(AddressRequest $request, Agent $agent_user) {
        $address_id = $request->address_id;
        $address_line_1 = $request->student_address_address_line_1;
        $address_line_2 = (isset($request->student_address_address_line_2) && !empty($request->student_address_address_line_2) ? $request->student_address_address_line_2 : null);
        $state = (isset($request->student_address_state_province_region) && !empty($request->student_address_state_province_region) ? $request->student_address_state_province_region : null);
        $city = $request->student_address_city;
        $post_code = $request->student_address_postal_zip_code;
        $country = $request->student_address_country;

        $res = [];
        $data = [];
        $data['address_line_1'] = $address_line_1;
        $data['address_line_2'] = $address_line_2;
        $data['state'] = $state;
        $data['post_code'] = $post_code;
        $data['city'] = $city;
        $data['country'] = $country;
        $data['active'] = 1;
        if(!is_null(\Auth::guard('student')->user())):
            $data['student_user_id'] = auth('student')->user()->id;
        else:
            $data['created_by'] = auth()->user()->id;
        endif;

        if($address_id > 0){
            $theAddr = Address::find($address_id);
            if(
                $address_line_1 == $theAddr->address_line_1 && $address_line_2 == $theAddr->address_line_2 && 
                $state == $theAddr->state && $city == $theAddr->city && $post_code == $theAddr->post_code && 
                $country == $theAddr->country
            ):
                $res['id'] = $address_id;
            else:
                $updateData = [];
                $updateData['active'] = 0;
                if(!is_null(\Auth::guard('student')->user())):
                    $updateData['student_user_id'] = auth('student')->user()->id;
                else:
                    $updateData['updated_by'] = auth()->user()->id;
                endif;
                Address::where('id', $address_id)->update($updateData);

                $address = Address::create($data);
                $insertId = $address->id;

                $res['id'] = $insertId;
            endif;
        }else{
            $address = Address::create($data);
            $insertId = $address->id;

            $res['id'] = $insertId;
        }

        $agent= Agent::where("agent_user_id",$agent_user->AgentUser->id)->get()->first();

        $agent->address_id=$res['id'];
        $agent->save();
        if($agent->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }

    }

    private function agentInitials(?string $name): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', (string) $name));
        if($clean === ''):
            return 'AG';
        endif;

        $parts = explode(' ', $clean);
        $first = mb_substr($parts[0] ?? 'A', 0, 1);
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

        return mb_strtoupper($first.$last);
    }

    private function agentPayload(Request $request): array
    {
        return $request->only([
            'title_id',
            'first_name',
            'last_name',
            'email',
            'mobile',
            'address_id',
            'photo',
            'code',
            'organization',
            'agent_user_id',
            'is_default',
            'created_by',
            'updated_by',
        ]);
    }

    private function agentPhotoUrl(Agent $agent): ?string
    {
        $photoUrl = (string) ($agent->photo_url ?? '');
        if($photoUrl !== '' && !Str::startsWith($photoUrl, 'data:')):
            return $photoUrl;
        endif;

        $photo = trim((string) ($agent->photo ?? ''));
        if($photo === ''):
            return null;
        endif;

        if(ctype_digit($photo)):
            $documentUrl = Document::find((int) $photo)?->download_url;

            return !empty($documentUrl) && !Str::startsWith((string) $documentUrl, 'data:') ? $documentUrl : null;
        endif;

        if(filter_var($photo, FILTER_VALIDATE_URL)):
            return $photo;
        endif;

        $photo = ltrim($photo, '/');
        $possiblePaths = [
            'public/agents/'.$agent->id.'/'.$photo,
            'public/agents/'.$agent->agent_user_id.'/'.$photo,
            Str::startsWith($photo, 'storage/') ? preg_replace('/^storage\//', 'public/', $photo) : 'public/'.$photo,
        ];

        foreach($possiblePaths as $path):
            if(!empty($path) && Storage::disk('local')->exists($path)):
                $url = Storage::disk('local')->url($path);

                return !Str::startsWith((string) $url, 'data:') ? $url : null;
            endif;
        endforeach;

        return null;
    }

    private function applicantPhotoUrl(Applicant $applicant): ?string
    {
        $photo = trim((string) ($applicant->photo ?? ''));
        if($photo === ''):
            return null;
        endif;

        $path = 'public/applicants/'.$applicant->id.'/'.$photo;
        if(Storage::disk('local')->exists($path)):
            return Storage::disk('local')->url($path);
        endif;

        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Agent $agent_user)
    {
        
        $data = AgentUser::find($agent_user->agent_user_id)->delete();

        $agent_user->delete();

        return response()->json($data);
    }

    public function restore($agent_user) {
        
        $data = Agent::where('id', $agent_user)->withTrashed()->restore();
        $dataSet = Agent::find($agent_user);
        AgentUser::where('id',$dataSet->agent_user_id)->withTrashed()->restore();
        return response()->json($data);
    }
}
