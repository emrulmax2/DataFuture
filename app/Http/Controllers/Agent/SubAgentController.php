<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Address;
use App\Models\AgentBankDetail;
use App\Models\AgentDocuments;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\CourseCreationInstance;
use App\Models\Document;
use App\Models\InstanceTerm;
use App\Models\Option;
use App\Models\ReferralCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Illuminate\Auth\Events\Registered;

class SubAgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function list(Request $request){
        $queryStr = trim((string) $request->input('querystr', ''));
        $status = (int) $request->input('status', 1);
        $parentId = (int) $request->input('id', 0);
        $agentUserList = AgentUser::withTrashed()->where('parent_id', $parentId)->pluck('id')->toArray();

        $query = Agent::with(['AgentUser' => function($agentUserQuery) {
            $agentUserQuery->withTrashed();
        }])->whereIn('agent_user_id', $agentUserList);

        if($status === 2):
            $query->withTrashed()->where(function($statusQuery) {
                $statusQuery->whereNotNull('deleted_at')
                    ->orWhereHas('AgentUser', function($agentUserQuery) {
                        $agentUserQuery->withTrashed()->whereNotNull('deleted_at');
                    });
            });
        elseif($status === 0):
            $query->withTrashed();
        else:
            $query->whereHas('AgentUser', function($agentUserQuery) {
                $agentUserQuery->whereNull('deleted_at');
            });
        endif;

        if(!empty($queryStr)):
            $query->where(function($searchQuery) use($queryStr) {
                $searchQuery->where('first_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('last_name', 'LIKE', '%'.$queryStr.'%')
                    ->orWhereRaw("concat(first_name, ' ', last_name) like ?", ['%'.$queryStr.'%'])
                    ->orWhere('organization', 'LIKE', '%'.$queryStr.'%')
                    ->orWhere('code', 'LIKE', '%'.$queryStr.'%')
                    ->orWhereHas('AgentUser', function($agentUserQuery) use($queryStr) {
                        $agentUserQuery->withTrashed()->where('email', 'LIKE', '%'.$queryStr.'%');
                    });
            });
        endif;

        $total_rows = $query->count();
        $page = (isset($request->page) && $request->page > 0 ? $request->page : 0);
        $perpage = ($request->input('size') === 'true' ? ($total_rows > 0 ? $total_rows : 10) : ((int) $request->input('size', 10) > 0 ? (int) $request->input('size', 10) : 10));
        $last_page = $total_rows > 0 ? ceil($total_rows / $perpage) : 1;

        $sorters = (isset($request->sorters) && !empty($request->sorters) ? $request->sorters : array(['field' => 'id', 'dir' => 'DESC']));
        $sortMap = [
            'id' => 'id',
            'name' => 'first_name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'organization' => 'organization',
            'code' => 'code',
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
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->full_name,
                    'email' => $list->AgentUser->email ?? '',
                    'initials' => $this->agentInitials($list->full_name),
                    'photo_url' => $this->agentPhotoUrl($list),
                    'organization' => $list->organization,
                    'code' => $list->code,
                    'deleted_at' => $list->deleted_at ?? ($list->AgentUser->deleted_at ?? null),
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data, 'all_rows' => $total_rows]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAgentRequest $request)
    {

        $request->request->add(['created_by' => auth()->user()->id]);

        $User = AgentUser::create([

                'email' => $request->input("email"),
                'password' => $request->input("password"),
                'active' => 1,
                'created_by' => auth()->user()->id,
                'parent_id' => $request->input("parent_id"),
                
        ]);

        $request->request->add(['agent_user_id' => $User->id]);

        $data = Agent::create($request->all());
       
        $referral = ReferralCode::create([
            'code' => $data->code,
            'type' => 'Agent',
            'agent_user_id' => $data->AgentUser->id,
            'created_by' => auth()->user()->id,
        ]);
        //event(new Registered($User));

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $sub_agent)
    {

        $employee = $sub_agent;
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();
        $agentUserList = AgentUser::where('id', $userData->id)->orWhere('parent_id', $userData->id)->get()->pluck('id')->toArray();

        return view('pages.agent.profile.sub.show',[
            'title' => 'Sub Agents - London Churchill College',
            'layout' => 'agent-management-top-menu',
            'breadcrumbs' => [
                ['label' => 'Agent Creations', 'href' => route('agent-user.index')],
                ['label' => 'Sub Agents', 'href' => 'javascript:void(0);'],
            ],
            "user" => $userData,
            "employee" => $employee,
            "postcodeApi" => $PostCodeAPI,
            "unique" => Str::random(10),
            'agentProfileInitials' => $this->agentInitials($employee->full_name),
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
     */
    public function update(UpdateAgentRequest $request, Agent $sub_agent)
    {
        
        $request->request->add(['agent_user_id' => $sub_agent->AgentUser->id]);
        $agenUser = AgentUser::find($sub_agent->AgentUser->id);
        
        $agenUser->email=$request->input('email');
        $agenUser->save();
        $request->merge(['updated_by' => auth()->user()->id]);
        if($agenUser->wasChanged()) { 

            $agenUser->email_verified_at=null;
            $agenUser->save();
            //event(new Registered($agenUser));

        } else {
            $agenUser->fill($request->all());
            $agenUser->save();
        }
        
        $sub_agent->fill($request->all());
        $sub_agent->save();

        //$request->request->remove('email');
        //$request->request->add(['email' => $request->input('contact_email')]);
        //$request->request->remove('contact_email');

        $agent= Agent::where("agent_user_id",$sub_agent->AgentUser->id)->get()->first();

        $agent->fill($request->all());
        $agent->save();
        
        if($agenUser->wasChanged() || $sub_agent->wasChanged() || $agent->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $sub_agent)
    {
        
        $data = AgentUser::find($sub_agent->agent_user_id)->delete();
        $sub_agent->delete();

        return response()->json($data);
    }

    public function restore($sub_agent) {
        
        $data = Agent::where('id', $sub_agent)->withTrashed()->restore();
        $dataSet = Agent::find($sub_agent);
        AgentUser::where('id',$dataSet->agent_user_id)->withTrashed()->restore();

        return response()->json($data);
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
}
