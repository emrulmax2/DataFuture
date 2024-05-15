<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Address;
use App\Models\AgentUser;
use App\Models\Applicant;
use App\Models\CourseCreationInstance;
use App\Models\InstanceTerm;
use App\Models\Option;
use App\Models\ReferralCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $academicyear = (isset($request->academicyear) && $request->academicyear > 0 ? $request->academicyear : '');
        $agentUserList = AgentUser::where('parent_id',$request->id)->pluck('id')->toArray();
        
        $query = Agent::whereIn('agent_user_id', $agentUserList);
        if(!empty($queryStr)):
            $query->where('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
        endif;
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
        if(!empty($queryStr)):
            $query->where('title','LIKE','%'.$queryStr.'%');
            $query->orWhere('first_name','LIKE','%'.$queryStr.'%');
            $query->orWhere('last_name','LIKE','%'.$queryStr.'%');
        endif;
        if($status == 2):
            $query->onlyTrashed();
        endif;
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
                    'organization' => $list->organization,
                    'code' => $list->code,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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

        return view('pages.agent.profile.sub.show',[
            'title' => 'Welcome - LCC Data Future Managment',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "postcodeApi" => $PostCodeAPI,
            "unique" => Str::random(10),
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

        return response()->json($data);
    }

    public function restore($sub_agent) {
        
        $data = Agent::where('id', $sub_agent)->withTrashed()->restore();
        $dataSet = Agent::find($sub_agent);
        AgentUser::where('id',$dataSet->agent_user_id)->withTrashed()->restore();
        response()->json($data);
    }
}
