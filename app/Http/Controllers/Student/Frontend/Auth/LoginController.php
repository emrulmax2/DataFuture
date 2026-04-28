<?php

namespace App\Http\Controllers\Student\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Request\StudentLoginRequest;
use App\Services\AuthLogService;

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
        $extra = AuthLogService::resolveExtra($request);
        AuthLogService::logLogin(
            \Auth::guard('student')->user()->id,
            'student_user',
            'student',
            session()->getId(),
            $request->getClientIp(),
            $request->userAgent(),
            $extra
        );
    }
    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        if (\Auth::guard('student')->check()) {
            AuthLogService::logLogout(\Auth::guard('student')->user()->id, 'student_user', AuthLogService::REASON_MANUAL);
        }
        session()->forget('selected_student_id');
        \Auth::guard('student')->logout();
        return redirect()->route('students.login');
    }

}
