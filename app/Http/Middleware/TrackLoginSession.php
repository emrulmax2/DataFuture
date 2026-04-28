<?php

namespace App\Http\Middleware;

use App\Services\AuthLogService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackLoginSession
{
    /**
     * Guards (and their actor_type) that this middleware tracks.
     */
    private const TRACKED_GUARDS = [
        'web'     => 'user',
        'student' => 'student_user',
    ];

    /**
     * Session key prefix for last-activity timestamps.
     */
    private const LAST_ACTIVITY_KEY = '_login_log_last_activity_';

    public function handle(Request $request, Closure $next)
    {
        $lifetime = (int) config('session.lifetime', 120); // minutes

        foreach (self::TRACKED_GUARDS as $guard => $actorType) {
            if (!Auth::guard($guard)->check()) {
                continue;
            }

            $actor      = Auth::guard($guard)->user();
            $sessionKey = self::LAST_ACTIVITY_KEY . $guard;
            $lastActivity = session($sessionKey);

            if ($lastActivity !== null) {
                $idleMinutes = Carbon::now()->diffInMinutes(Carbon::parse($lastActivity));

                if ($idleMinutes >= $lifetime) {
                    // Session has timed out — close the log and force logout
                    AuthLogService::logLogout(
                        $actor->id,
                        $actorType,
                        AuthLogService::REASON_TIMEOUT
                    );

                    Auth::guard($guard)->logout();
                    session()->forget($sessionKey);
                    session()->invalidate();
                    session()->regenerateToken();

                    $redirectRoute = ($guard === 'student')
                        ? route('students.login')
                        : url('login');

                    return redirect($redirectRoute)
                        ->with('session_expired', 'Your session has expired. Please log in again.');
                }
            }

            // Refresh last-activity timestamp for this guard
            session([$sessionKey => Carbon::now()->toDateTimeString()]);
        }

        return $next($request);
    }
}
