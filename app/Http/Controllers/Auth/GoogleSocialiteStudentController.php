<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\StudentUser;
use App\Services\AuthLogService;
use Illuminate\Http\Request;

class GoogleSocialiteStudentController extends Controller
{
    public function redirectToGoogle()
    {
        config(['services.google.redirect' => env('GOOGLE_STUDENT_REDIRECT_URL')]);
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
            config(['services.google.redirect' => env('GOOGLE_STUDENT_REDIRECT_URL')]);
            $user = Socialite::driver('google')->user();
            
            $finduser = StudentUser::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::guard('student')->login($finduser);
                AuthLogService::logLogin($finduser->id, 'student_user', 'student', session()->getId(), request()->ip(), request()->userAgent());
                return redirect(route('students.dashboard'));
      
            } else {
                
                $finduser = StudentUser::where('email', $user->email)->first();
                
                $finduser = StudentUser::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='google';
                $finduser->save();
                
                Auth::guard('student')->login($finduser);
                AuthLogService::logLogin($finduser->id, 'student_user', 'student', session()->getId(), request()->ip(), request()->userAgent());
      
                return redirect(route('students.dashboard'));
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('google', "Your email not linked with google account");  
        }
    }
}
