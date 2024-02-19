<?php

namespace App\Http\Controllers;

use App\Http\Request\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

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
        $env= env('APP_ENV');
        return view('login.main', [
            'layout' => 'login',
            'env' => $env,
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
        if (!\Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            throw new \Exception('Wrong email or password.');
        } else {
            User::where('id', auth()->user()->id)->update([
                'last_login_ip' => $request->getClientIp()
            ]);
            Cache::forever('employeeCache'.\Auth::user()->id, \Auth::user()->load('employee'));
        }
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        \Auth::logout();
        Cache::flush();
        return redirect('login');
    }
}
