<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Http\Controllers\Controller;
use App\Http\Request\ApplicantForgetPasswordRequest;
use App\Http\Request\ApplicantForgetPasswordUpdateRequest;
use App\Models\ApplicantUser;
use App\Mail\ResetPasswordLink;
use Illuminate\Http\Response;
use DB; 
use Carbon\Carbon; 
use Mail; 
use Hash;
//use Illuminate\Support\Str;

class ForgetPasswordController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showForgetPasswordForm()
    {
        return view('login.forgetpassword.agent', [
            'layout' => 'login'
        ]);
    }

    
    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function submitForgetPasswordForm(ApplicantForgetPasswordRequest $request)
    {
       

        $applicantUser = ApplicantUser::where('email',$request->email)->get()->first();
        $token = base64_encode($request->email);
        if($applicantUser) {

                DB::table('password_resets')->insert([
                    'email' => $request->email, 
                    'token' => $token, 
                    'created_at' => Carbon::now()
                ]);

                Mail::to($request->email)->send(new ResetPasswordLink($token));

                return response()->json(['message'=>'A mail has been sent'],200);
        }else    
            return response()->json(['message'=>'No User Found'],422);

        
    }

    public function showResetPasswordForm($token) { 
        $tokenset = DB::table('password_resets')
                              ->where([ 'token' => $token])
                              ->first();
        if(!$tokenset) {
            return redirect('agent/login')->with(['message'=> 'Invalid token','title'=>'Token Error!','error'=>'token']);
        }
        return view('login.forgetpassword.link', [
            'email' => $tokenset->email,
            'token' => $token,
            'layout' => 'login'
        ]);
    }

          /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitResetPasswordForm(ApplicantForgetPasswordUpdateRequest $request)
      {
  
          $updatePassword = DB::table('password_resets')
                              ->where([
                                'email' => $request->email, 
                                'token' => $request->token
                              ])
                              ->first();
  
          if(!$updatePassword){
            //return response()->json(['message'=>'Applicant could not created','errors'=>["title"=>"somthing went wrong. Please try again"]],422);
            return back()->withInput()->with('error', 'Invalid token!');
          }
  
          ApplicantUser::where('email', $updatePassword->email)
                      ->update(['password' => Hash::make($request->password)]);
 
          DB::table('password_resets')->where(['email'=> $updatePassword->email])->delete();
  
          return response()->json(['Password Updated'],200);
      }


}
