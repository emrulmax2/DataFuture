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
            config(['services.google.redirect' => env('GOOGLE_REDIRECT_URL')]);
            $user = Socialite::driver('google')->user();
            
            $finduser = User::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::login($finduser);
                Cache::forever('employeeCashe'.$finduser->id, Auth::user()->load('employee'));
                return redirect('/');
      
            }else{
                
                $finduser = User::where('email', $user->email)->first();
                
                $finduser = User::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='google';
                $finduser->save();
                
                Auth::login($finduser);
                Cache::forever('employeeCache'.$finduser->id, Auth::user()->load('employee'));
                return redirect('/');
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('google', "Your email not linked with google account");  
        }
    }
}
