<?php

namespace App\Http\Controllers\Machine\Auth;

use App\Http\Controllers\Controller;
use App\Http\Request\MachineLoginRequest;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView()
    {
        return view('login.machine', [
            'layout' => 'login'
        ]);
    }

    
    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(MachineLoginRequest $request)
    {
        if (!\Auth::guard('machine')->attempt([
            'username' => $request->username,
            'password' => $request->password
        ])) {
            throw new \Exception('Wrong user name or password.');
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
        \Auth::guard('machine')->logout();
        return redirect()->route('machine.logout');
    }
}
