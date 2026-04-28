<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\StudentUser;
use App\Services\AuthLogService;
use Illuminate\Http\Request;

class MicrosoftSocialiteStudentController extends Controller
{
    public function redirectToMicrosoft()
    {
        config(['services.microsoft.redirect' => env('MICROSOFT_STUDENT_REDIRECT_URL')]);
        config(['services.microsoft.tenant' => env('MICROSOFT_TENANT', 'organizations')]);
        return Socialite::driver('microsoft')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {
        try {
            config(['services.microsoft.redirect' => env('MICROSOFT_STUDENT_REDIRECT_URL')]);
            config(['services.microsoft.tenant' => env('MICROSOFT_TENANT', 'organizations')]);
            $user = Socialite::driver('microsoft')->user();

            $finduser = StudentUser::where('social_id', $user->id)->first();

            if ($finduser) {
                Auth::guard('student')->login($finduser);
                AuthLogService::logLogin($finduser->id, 'student_user', 'student', session()->getId(), request()->ip(), request()->userAgent(), AuthLogService::resolveExtra(request()));
                return redirect(route('students.dashboard'));
            } else {
                $finduser = StudentUser::where('email', $user->email)->first();

                $finduser = StudentUser::find($finduser->id);

                $finduser->social_id = $user->id;
                $finduser->social_type = 'microsoft';
                $finduser->save();

                Auth::guard('student')->login($finduser);
                AuthLogService::logLogin($finduser->id, 'student_user', 'student', session()->getId(), request()->ip(), request()->userAgent(), AuthLogService::resolveExtra(request()));
                return redirect(route('students.dashboard'));
            }
        } catch (Exception $e) {
            return redirect('login')->with('microsoft', 'Your email not linked with microsoft account');
        }
    }
}
