<?php

namespace App\Services;

use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthLogService
{
    const REASON_MANUAL  = 'manual_logout';
    const REASON_TIMEOUT = 'session_timeout';
    const REASON_INVALID = 'session_invalidated';

    /**
     * Resolve device, platform, browser and geo-location from a request.
     * Uses jenssegers/agent for UA parsing and ip-api.com (free, no key) for geo.
     */
    public static function resolveExtra(Request $request): array
    {
        $uaDetails = self::parseUserAgent($request->userAgent());
        $device = $uaDetails['device'];
        $platform = $uaDetails['platform'];
        $browser = $uaDetails['browser'];

        $country = null;
        $city    = null;
        $lat     = null;
        $lng     = null;

        $ip = $request->ip();
        $isPrivate = !$ip
            || $ip === '127.0.0.1'
            || $ip === '::1'
            || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

        if (!$isPrivate) {
            try {
                $response = Http::timeout(3)
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,city,lat,lon',
                    ]);
                if ($response->successful()) {
                    $geo = $response->json();
                    if (($geo['status'] ?? '') === 'success') {
                        $country = $geo['country'] ?? null;
                        $city    = $geo['city']    ?? null;
                        $lat     = isset($geo['lat']) ? (float) $geo['lat'] : null;
                        $lng     = isset($geo['lon']) ? (float) $geo['lon'] : null;
                    }
                }
            } catch (\Throwable $e) {
                // Geo lookup is best-effort; never block login
            }
        }

        return compact('device', 'platform', 'browser', 'country', 'city', 'lat', 'lng');
    }

    private static function parseUserAgent(?string $userAgent): array
    {
        if (class_exists(\Jenssegers\Agent\Agent::class)) {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($userAgent);

            $deviceName = $agent->device() ?: '';
            $deviceType = $agent->isDesktop() ? 'Desktop'
                        : ($agent->isTablet() ? 'Tablet' : 'Mobile');

            return [
                'device' => $deviceName ? "{$deviceType} ({$deviceName})" : $deviceType,
                'platform' => $agent->platform() ?: '',
                'browser' => $agent->browser() ?: '',
            ];
        }

        $userAgent = (string) $userAgent;
        $deviceType = preg_match('/ipad|tablet|kindle|silk/i', $userAgent)
            ? 'Tablet'
            : (preg_match('/mobile|iphone|ipod|android|blackberry|phone/i', $userAgent) ? 'Mobile' : 'Desktop');
        $deviceName = '';

        if (preg_match('/iphone/i', $userAgent)) {
            $deviceName = 'iPhone';
        } elseif (preg_match('/ipad/i', $userAgent)) {
            $deviceName = 'iPad';
        } elseif (preg_match('/android/i', $userAgent)) {
            $deviceName = 'Android';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $deviceName = 'Mac';
        } elseif (preg_match('/windows/i', $userAgent)) {
            $deviceName = 'Windows PC';
        }

        $platform = '';
        if (preg_match('/windows nt/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/mac os x|macintosh/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        }

        $browser = '';
        if (preg_match('/edg\//i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/opr\//i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/chrome\//i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari\//i', $userAgent) && !preg_match('/chrome\//i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox\//i', $userAgent)) {
            $browser = 'Firefox';
        }

        return [
            'device' => $deviceName ? "{$deviceType} ({$deviceName})" : $deviceType,
            'platform' => $platform,
            'browser' => $browser,
        ];
    }

    /**
     * Open a new login log record.
     *
     * Also closes any stale open records for the same actor that are older
     * than the configured session lifetime (handles sessions lost to PHP GC
     * without our middleware ever seeing them).
     *
     * @param  array  $extra  Optional: device, platform, browser, country, city, lat, lng
     */
    public static function logLogin(
        int     $actorId,
        string  $actorType,
        string  $guardName,
        string  $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array   $extra     = []
    ): LoginLog {
        $staleThreshold = Carbon::now()->subMinutes(
            (int) config('session.lifetime', 120)
        );

        LoginLog::where('actor_id', $actorId)
            ->where('actor_type', $actorType)
            ->whereNull('logout_at')
            ->where('login_at', '<', $staleThreshold)
            ->update([
                'logout_at'     => Carbon::now(),
                'logout_reason' => self::REASON_INVALID,
            ]);

        return LoginLog::create(array_merge([
            'actor_id'   => $actorId,
            'actor_type' => $actorType,
            'guard_name' => $guardName,
            'session_id' => $sessionId,
            'login_at'   => Carbon::now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ], $extra));
    }

    /**
     * Close all open log records for the given actor. Idempotent.
     */
    public static function logLogout(
        int    $actorId,
        string $actorType,
        string $reason = self::REASON_MANUAL
    ): void {
        LoginLog::where('actor_id', $actorId)
            ->where('actor_type', $actorType)
            ->whereNull('logout_at')
            ->update([
                'logout_at'     => Carbon::now(),
                'logout_reason' => $reason,
            ]);
    }
}
