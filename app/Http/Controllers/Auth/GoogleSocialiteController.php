<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use DB;
use Illuminate\Support\Str;
use Exception;
use Session;
use App\Models\User;
use Illuminate\Http\Request;

class GoogleSocialiteController extends Controller
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
            
            $finduser = User::where('social_id', $user->id)->first();
            
            $random = Str::random(40);
            if($finduser){
      
                Auth::login($finduser);
                DB::connection('account')->table("users")->insert(['id'=>\Auth::user()->id,'email'=>$finduser->email, 'login_token'=>$random]);
                Session::put('accToken', $random);
                return redirect('/');
      
            }else{
                
                $finduser = User::where('email', $user->email)->first();
                
                $finduser = User::find($finduser->id);
                
                $finduser->social_id=$user->id;
                $finduser->social_type='google';
                $finduser->save();
                
                Auth::login($finduser);
                DB::connection('account')->table("users")->insert(['id'=>\Auth::user()->id,'email'=>$finduser->email, 'login_token'=>$random]);
                Session::put('accToken', $random);
                return redirect('/');
            }
     
        } catch (Exception $e) {

             return redirect('login')->with('google', "Your email not linked with google account");  
        }
    }
}
