<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentUserRequest;
use App\Http\Requests\StoreApplicantUserRequest;
use App\Models\AgentUser;
use Illuminate\Auth\Events\Registered;
use App\Models\ApplicantUser;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{

    public function index()
    {
        
        return view('login.register.agent', [
            'layout' => 'login'
        ]);
    }

    public function store(StoreAgentUserRequest $request) {
        
        $User = AgentUser::create([

             'email' => $request->input("email"),
             'password' => $request->input("password"),
             'active' => 0,
             
        ]);

        event(new Registered($User));

        if($User) {

            Auth::guard('agent')->attempt([
                'email' => $request->input("email"),
                'password' => $request->input("password")
            ]);

            return response()->json(['Agent Created'],200);
            
        }
        else 
            return response()->json(['message'=>'Agent could not created','errors'=>["title"=>"somthing went wrong. Please try again"]],422);
        
    }
}
