<?php

namespace App\Http\Controllers\Agent\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentApplicationChecktRequest;
use App\Http\Requests\UpdateAgenApplicationChecktRequest;
use App\Mail\ApplicantAgentBasisEmailVerification;
use App\Models\AgentApplicationCheck;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Mail;

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
        
        $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
        $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
        $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();
        if($active_api == 1 && !empty($textlocal_api)):
            $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                'apikey' => $textlocal_api, 
                'message' => "One Time Password (OTP) for your application account is ".$data->verify_code.
                                ".use this OTP to complete the application. OTP will valid for next 24 hours.", 
                'sender' => 'London Churchill College', 
                'numbers' => $data->mobile
            ]);
        elseif($active_api == 2 && !empty($smseagle_api)):
            $response = Http::timeout(-1)->withHeaders([
                'access-token' => $smseagle_api,
                'Content-Type' => 'application/json',
            ])->post('https://79.171.153.104/api/v2/messages/sms', [
                'to' => [$data->mobile],
                'text' => "One Time Password (OTP) for your application account is ".$data->verify_code.
                            ".Use this OTP to complete the application. OTP will valid for next 24 hours",
            ]);
        endif;

        Mail::to($data->email)->send(new ApplicantAgentBasisEmailVerification($data->first_name." ".$data->last_name, $data->email, $data->email_verify_code));

        if($data) {
            $data = AgentApplicationCheck::where('agent_user_id',auth('agent')->user()->id)->whereNull("applicant_id")->get();

        }

                
        return response()->json($data);

    }

    public function verifyMobile(Request $request)
    {
        
        if($request->email_verify_code)
            $applicantEmail = $this->verifyEmail($request);
        
        if($request->verify_code)
        {
            $ApplicantFound = AgentApplicationCheck::where('id',$request->id)->where('agent_user_id',$request->user_id)
                                ->whereNull("applicant_id")
                                ->where("verify_code",$request->verify_code)
                                ->where("active",0)
                                ->get()
                                ->first();

            if($ApplicantFound) {
                
                $ApplicantFound->mobile_verified_at = date("Y-m-d H:i:s");
                $ApplicantFound->save();
                
                $data = AgentApplicationCheck::where('agent_user_id',auth('agent')->user()->id)->whereNull("applicant_id")->get();
    
                return response()->json($data);

            } else {

            }
        }
        if($applicantEmail) {
            $data = AgentApplicationCheck::where('agent_user_id',auth('agent')->user()->id)->whereNull("applicant_id")->get();
            
            return response()->json($data);

        }
        
        return response()->json(["errors"=>["verify_code"=>"invalid mobile otp"],"message"=>"invalid code"],422);
    }
    
    public function verifyEmail(Request $request)
    {
        

        $ApplicantFound = AgentApplicationCheck::where('id',$request->id)
                            ->where('agent_user_id',$request->user_id)
                            ->whereNull("applicant_id")
                            ->where("email_verify_code",$request->email_verify_code)
                            ->where("active",0)
                            ->get()
                            ->first();
        
        if($ApplicantFound) {

            $ApplicantFound->email_verified_at = date("Y-m-d H:i:s");
            $ApplicantFound->save();


            $data = AgentApplicationCheck::where('agent_user_id',auth('agent')->user()->id)->whereNull("applicant_id")->get();
    
            return response()->json($data);
        }

        return response()->json(["errors"=>["email_verify_code"=>"invalid email code"],"message"=>"invalid code"],422);
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
    public function update(UpdateAgenApplicationChecktRequest $request, $id)
    {

        $AgentApplicationCheck = AgentApplicationCheck::find($id);
 
        $AgentApplicationCheck->updated_by= auth('agent')->user()->id;
        $AgentApplicationCheck->verify_code= rand(1111,9999);
        $AgentApplicationCheck->email_verify_code= rand(1111,9999);
        
        $AgentApplicationCheck->save();
        $data = $AgentApplicationCheck;
        
        $active_api = Option::where('category', 'SMS')->where('name', 'active_api')->pluck('value')->first();
        $textlocal_api = Option::where('category', 'SMS')->where('name', 'textlocal_api')->pluck('value')->first();
        $smseagle_api = Option::where('category', 'SMS')->where('name', 'smseagle_api')->pluck('value')->first();

        if($active_api == 1 && !empty($textlocal_api)):
            $response = Http::timeout(-1)->post('https://api.textlocal.in/send/', [
                'apikey' => $textlocal_api, 
                'message' => "One Time Password (OTP) for your application account is ".$data->verify_code.
                                ".Use this OTP to complete the application. OTP will valid for next 24 hours.", 
                'sender' => 'London Churchill College', 
                'numbers' => $data->mobile
            ]);
        elseif($active_api == 2 && !empty($smseagle_api)):

            $response = Http::withHeaders([
                'access-token' => $smseagle_api,
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->withOptions([
                "verify" => false
            ])->post('https://79.171.153.104/api/v2/messages/sms', [
                'to' => [$data->mobile],
                'text' => "One Time Password (OTP) for your application account is ".$data->verify_code.
                            ".Use this OTP to complete the application. OTP will valid for next 24 hours",
            ]);

        endif;

        Mail::to($data->email)->send(new ApplicantAgentBasisEmailVerification($data->first_name." ".$data->last_name, $data->email, $data->email_verify_code));
                
        return response()->json($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = AgentApplicationCheck::find($id)->forceDelete();
        return response()->json($data);
    }
}
