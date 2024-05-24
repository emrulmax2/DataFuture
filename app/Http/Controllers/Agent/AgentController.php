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
        $agentUserList = AgentUser::whereNull('parent_id')->pluck('id')->toArray();
        
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
                $agentUserCount = AgentUser::where('parent_id',$list->agent_user_id)->get()->count();
                $data[] = [
                    'id' => $list->id,
                    'sl' => $i,
                    'name' => $list->full_name,
                    'organization' => ($agentUserCount) ? $list->organization." [ ".$agentUserCount." ]" : $list->organization,
                    'code' => $list->code,
                    'deleted_at' => $list->deleted_at
                ];
                $i++;
            endforeach;
        endif;
        return response()->json(['last_page' => $last_page, 'data' => $data]);
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
                'created_by' => auth()->user()->id,
                
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
    public function show(Agent $agent_user)
    {

        $employee = $agent_user;
        $userData = AgentUser::find($employee->agent_user_id);
        $PostCodeAPI = Option::where('category', 'ADDR_ANYWHR_API')->where('name', 'anywhere_api')->pluck('value')->first();

        return view('pages.agent.profile.show',[
            'title' => 'Welcome - London Churchill College',
            'breadcrumbs' => [],
            "user" => $userData,
            "employee" => $employee,
            "postcodeApi" => $PostCodeAPI,
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
        
        $request->request->add(['agent_user_id' => $agent_user->AgentUser->id]);
        $agenUser = AgentUser::find($agent_user->AgentUser->id);
        
        $agenUser->email=$request->input('email');
        $agenUser->save();
        $request->merge(['updated_by' => auth()->user()->id]);
        if($agenUser->wasChanged()) { 

            $agenUser->email_verified_at=null;
            $agenUser->save();
            event(new Registered($agenUser));

        } else {
            $agenUser->fill($request->all());
            $agenUser->save();
        }
        
        $agent_user->fill($request->all());
        $agent_user->save();

        //$request->request->remove('email');
        //$request->request->add(['email' => $request->input('contact_email')]);
        //$request->request->remove('contact_email');

        $agent= Agent::where("agent_user_id",$agent_user->AgentUser->id)->get()->first();

        $agent->fill($request->all());
        $agent->save();
        
        if($agenUser->wasChanged() || $agent_user->wasChanged() || $agent->wasChanged()){
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
