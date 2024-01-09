<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\AgentUser;
use App\Models\ReferralCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Auth\Events\Registered;

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
            'breadcrumbs' => [['label' => 'Agent Creations', 'href' => 'javascript:void(0);']],
            'agentUser' => AgentUser::all(),
            "unique" => Str::random(10),
        ]);
    }

    public function list(Request $request){
        $queryStr = (isset($request->querystr) && !empty($request->querystr) ? $request->querystr : '');
        $status = (isset($request->status) && $request->status > 0 ? $request->status : 1);
        $academicyear = (isset($request->academicyear) && $request->academicyear > 0 ? $request->academicyear : '');

        $query = Agent::where('id', '!=', 0);
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

        $query = Agent::orderByRaw(implode(',', $sorts));
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
                
        ]);

        $request->request->add(['agent_user_id' => $User->id]);

        $data = Agent::create($request->all());
       
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
    public function show(Agent $agent)
    {
        //
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

        $request->request->add(['agent_user_id' => $agent_user->AgentUser->id]);
        $agenUser = AgentUser::find($agent_user->AgentUser->id);
        $agenUser->email=$request->input('email');
        $agenUser->save();
        if($agenUser->wasChanged()) { 

            $agenUser->email_verified_at=null;
            $agenUser->save();
            event(new Registered($agenUser));
        }
        $request->request->add(['updated_by' => auth()->user()->id]);

        $agent_user->fill($request->all());
        $agent_user->save();
        
        if($agenUser->wasChanged() || $agent_user->wasChanged()){
            return response()->json(['message' => 'Data updated'], 200);
        }else{
            return response()->json(['message' => 'something went wrong'], 422);
        }

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
        response()->json($data);
    }
}
