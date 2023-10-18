<?php

namespace App\Http\Controllers\Applicant\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Request\EmailVerificationRequest;

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling email verification for any
    | user that recently registered with the application. Emails may also
    | be re-sent if the user didn't receive the original email message.
    |
    */
    
    //use VerifiesEmails, RedirectsUsers;

    
    /**
     * Show the email verification notice.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(Request $request)
    {
        return $request->user('student')->hasVerifiedEmail()
                        ? redirect()->route('students.dashboard')
                        : view('verification.notice', [
                            'pageTitle' => __('Account Verification')
                        ]);
    }

    public function verify(EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/students/dashboard')->with('verifymessage', 'Your Email Address Verified');
    }

}
