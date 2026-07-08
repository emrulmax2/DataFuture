<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\User;
use App\Services\AuthLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MicrosoftSocialiteController extends Controller
{
    public function redirectToMicrosoft()
    {
        config(['services.microsoft.redirect' => env('MICROSOFT_REDIRECT_URL')]);
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
            config(['services.microsoft.redirect' => env('MICROSOFT_REDIRECT_URL')]);
            config(['services.microsoft.tenant' => env('MICROSOFT_TENANT', 'organizations')]);
            $user = Socialite::driver('microsoft')->user();

            $finduser = User::where('social_id', $user->id)->first();

            if ($finduser) {
                $isFirstLogin = is_null($finduser->last_login_ip);
                Auth::login($finduser);
                User::where('id', $finduser->id)->update([
                    'last_login_ip' => request()->ip()
                ]);
                Cache::forever('employeeCashe' . $finduser->id, Auth::user()->load('employee'));
                AuthLogService::logLogin($finduser->id, 'user', 'web', session()->getId(), request()->ip(), request()->userAgent(), AuthLogService::resolveExtra(request()));
                return $this->afterLoginRedirect($finduser, $isFirstLogin);
            } else {
                $finduser = User::where('email', $user->email)->first();

                $finduser = User::find($finduser->id);

                $isFirstLogin = is_null($finduser->last_login_ip);
                $finduser->social_id = $user->id;
                $finduser->social_type = 'microsoft';
                $finduser->save();

                Auth::login($finduser);
                User::where('id', $finduser->id)->update([
                    'last_login_ip' => request()->ip()
                ]);
                Cache::forever('employeeCache' . $finduser->id, Auth::user()->load('employee'));
                AuthLogService::logLogin($finduser->id, 'user', 'web', session()->getId(), request()->ip(), request()->userAgent(), AuthLogService::resolveExtra(request()));
                return $this->afterLoginRedirect($finduser, $isFirstLogin);
            }
        } catch (Exception $e) {
            return redirect('login')->with('microsoft', 'Your email not linked with microsoft account');
        }
    }

    /**
     * Send first-time users through the welcome interstitial; everyone else to the dashboard.
     */
    private function afterLoginRedirect(User $user, bool $isFirstLogin)
    {
        if ($isFirstLogin) {
            $first = trim(explode(' ', trim((string) $user->name))[0]);
            session()->flash('login_welcome', $first !== '' ? $first : null);
            return redirect()->route('welcome.first');
        }
        return redirect('/');
    }
}
