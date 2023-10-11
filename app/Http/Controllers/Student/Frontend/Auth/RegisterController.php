<?php

namespace App\Http\Controllers\Applicant\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicantUserRequest;
use Illuminate\Auth\Events\Registered;
use App\Models\ApplicantUser;

class RegisterController extends Controller
{

    public function index()
    {
        
        return view('login.register', [
            'layout' => 'login'
        ]);
    }

    public function store(StoreApplicantUserRequest $request) {
        
        $ApplicantUser = ApplicantUser::create([

             'email' => $request->input("email"),
             'password' => $request->input("password"),
             'active' => 0,
             
        ]);

        event(new Registered($ApplicantUser));

        if($ApplicantUser) {

            \Auth::guard('applicant')->attempt([
                'email' => $request->input("email"),
                'password' => $request->input("password")
            ]);

            return response()->json(['Applicant Created'],200);
            
        }
        else 
            return response()->json(['message'=>'Applicant could not created','errors'=>["title"=>"somthing went wrong. Please try again"]],422);
        
    }
}
