<?php

namespace App\Http\Controllers;

use App\Http\Request\LoginRequest;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Support\Str;
use Session;
class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login.main', [
            'layout' => 'login'
        ]);
    }

    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        
        $random = Str::random(40);
        
        
        if (!\Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            throw new \Exception('Wrong email or password.');
        }

        DB::connection('account')->table("users")->insert(['id'=>\Auth::user()->id, 'email'=>$request->email, 'login_token'=>$random]);

        Session::put('accToken', $random);
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        
        DB::connection('account')->table("users")->where(['email'=>\Auth::user()->email])->delete();
        \Auth::logout();
        return redirect('login');
    }
}
