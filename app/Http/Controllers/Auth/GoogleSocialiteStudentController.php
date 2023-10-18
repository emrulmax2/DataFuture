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
        return Socialite::driver('google')->redirect();
    }
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {
     
            $user = Socialite::driver('google')->user();
            
            $finduser = StudentUser::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::guard('student')->login($user);
                return redirect('/');
      
            } else {
                
                $finduser = StudentUser::where('email', $user->email)->first();
                
                $finduser = StudentUser::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='google';
                $finduser->save();
                
                Auth::guard('student')->login($finduser);
      
                return redirect('/');
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('google', "Your email not linked with google account");  
        }
    }
}
