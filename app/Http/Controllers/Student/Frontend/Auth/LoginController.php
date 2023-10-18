<?php

namespace App\Http\Controllers\Student\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Request\StudentLoginRequest;

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
        return view('login.student', [
            'layout' => 'login'
        ]);
    }

    
    /**
     * Authenticate login user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(StudentLoginRequest $request)
    {
        if (!\Auth::guard('student')->attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            throw new \Exception('Wrong email or password.');
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
        \Auth::guard('student')->logout();
        return redirect()->route('students.login');
    }

}
