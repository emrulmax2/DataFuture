<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GoogleSocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        config(['services.google.redirect' => env('GOOGLE_REDIRECT_URL')]);
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
            config(['services.google.redirect' => env('GOOGLE_REDIRECT_URL')]);
            $user = Socialite::driver('s3')->user();
            
            $finduser = User::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::login($finduser);
                User::where('id', $finduser->id)->update([
                    'last_login_ip' => request()->ip()
                ]);
                Cache::forever('employeeCashe'.$finduser->id, Auth::user()->load('employee'));
                return redirect('/');
      
            }else{
                
                $finduser = User::where('email', $user->email)->first();
                
                $finduser = User::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='s3';
                $finduser->save();
                
                Auth::login($finduser);
                User::where('id', $finduser->id)->update([
                    'last_login_ip' => request()->ip()
                ]);
                Cache::forever('employeeCache'.$finduser->id, Auth::user()->load('employee'));
                return redirect('/');
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('s3', "Your email not linked with google account");  
        }
    }
}
