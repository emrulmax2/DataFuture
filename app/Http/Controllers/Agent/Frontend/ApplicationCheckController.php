<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentApplicationChecktRequest;
use App\Models\AgentApplicationCheck;
use Illuminate\Http\Request;

class ApplicationCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAgentApplicationChecktRequest $request)
    {
        
        $request->request->add(['created_by' => auth('agent')->user()->id]);
        $request->request->add(['agent_user_id' => auth('agent')->user()->id]);
        $request->request->add(['verify_code' => rand(1111,9999)]);
        $request->request->add(['email_verify_code' => rand(1111,9999)]);
        $data = AgentApplicationCheck::create($request->all());
        if($data) {
            $data = AgentApplicationCheck::where('agent_user_id',auth('agent')->user()->id)->whereNull("applicant_id")->get();

        }
        return response()->json($data);
    }

    public function verifyMobile(Request $request)
    {

        $applicantEmail = $this->verifyEmail($request);
        
        

        $ApplicantFound = AgentApplicationCheck::where('agent_user_id',$request->user_id)->whereNull("applicant_id")->where("verify_code",$request->verify_code)->where("active",0)->get();

        if($ApplicantFound) {

            $ApplicantFound->fill(["mobile_verified_at",date("Y-m-d H:i:s")]);
            $ApplicantFound->save();

            return response()->json($ApplicantFound);

        }

        if($applicantEmail) {

            return response()->json($applicantEmail);

        }
        
        return response()->json(["message"=>"invalid code"],422);
    }
    
    public function verifyEmail(Request $request)
    {
        

        $ApplicantFound = AgentApplicationCheck::where('agent_user_id',$request->user_id)->whereNull("applicant_id")->where("email_verify_code",$request->email_verify_code)->where("active",0)->get();

        if($ApplicantFound) {
            $ApplicantFound->fill(["email_verified_at",date("Y-m-d H:i:s")]);
            $ApplicantFound->save();

            return response()->json($ApplicantFound);
        }
        
        return response()->json(["message"=>"invalid code"],422);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
