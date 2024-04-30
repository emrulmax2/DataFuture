<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\StudentUser;
use Illuminate\Http\Request;

class GoogleSocialiteStudentController extends Controller
{
    public function redirectToGoogle()
    {
        config(['services.google.redirect' => env('GOOGLE_STUDENT_REDIRECT_URL')]);
        return Socialite::driver('s3')->redirect();
    }
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {
            config(['services.google.redirect' => env('GOOGLE_STUDENT_REDIRECT_URL')]);
            $user = Socialite::driver('s3')->user();
            
            $finduser = StudentUser::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::guard('student')->login($finduser);
                return redirect(route('students.dashboard'));
      
            } else {
                
                $finduser = StudentUser::where('email', $user->email)->first();
                
                $finduser = StudentUser::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='s3';
                $finduser->save();
                
                Auth::guard('student')->login($finduser);
      
                return redirect(route('students.dashboard'));
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('s3', "Your email not linked with google account");  
        }
    }
}
