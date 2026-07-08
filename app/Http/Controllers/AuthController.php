<?php

namespace App\Http\Controllers;

use App\Http\Request\LoginRequest;
use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\User;
use App\Services\AuthLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show specified view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loginView(Request $request)
    {
        $env= env('APP_ENV');
        return view('login.main', [
            'layout' => 'login',
            'env' => $env,
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray()
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
            // Detect a genuine first login (no previous login recorded) for the welcome state.
            $isFirstLogin = is_null(auth()->user()->last_login_ip);

            User::where('id', auth()->user()->id)->update([
                'last_login_ip' => $request->getClientIp()
            ]);
            Cache::forever('employeeCache'.\Auth::user()->id, \Auth::user()->load('employee'));
            $extra = AuthLogService::resolveExtra($request);
            AuthLogService::logLogin(
                auth()->user()->id,
                'user',
                'web',
                session()->getId(),
                $request->getClientIp(),
                $request->userAgent(),
                $extra
            );

            $redirect = session()->pull('url.intended');
            if ($redirect && Str::startsWith($redirect, '/')):
                session()->forget('url.intended');
                return response()->json(['redirect' => $redirect]);
            endif;

            // First-time users get the welcome interstitial before the dashboard.
            if ($isFirstLogin) {
                session()->flash('login_welcome', $this->firstName(auth()->user()->name));
                return response()->json(['redirect' => route('welcome.first')]);
            }

            return response()->json(['redirect' => '/dashboard']);
        }
    }

    /**
     * First-login welcome interstitial (shown once, right after the first sign in).
     *
     * @return \Illuminate\Http\Response
     */
    public function welcomeView(Request $request)
    {
        // If the welcome flash is gone (e.g. page refresh), fall straight through to the dashboard.
        if (!session()->has('login_welcome')) {
            return redirect('/');
        }
        // Keep the flash alive for this render.
        session()->keep(['login_welcome']);

        return view('login.welcome', [
            'layout' => 'login',
            'name' => $this->firstName(auth()->user()->name ?? null),
            'dashboardUrl' => '/',
            'opt' => Option::where('category', 'SITE_SETTINGS')->pluck('value', 'name')->toArray(),
        ]);
    }

    /**
     * Extract a display first name from a full name.
     */
    private function firstName(?string $name): ?string
    {
        if (empty($name)) {
            return null;
        }
        return trim(explode(' ', trim($name))[0]);
    }

    /**
     * Logout user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        if (\Auth::check()) {
            AuthLogService::logLogout(auth()->user()->id, 'user', AuthLogService::REASON_MANUAL);
        }
        \Auth::logout();
        Cache::flush();
        return redirect('login');
    }
}
