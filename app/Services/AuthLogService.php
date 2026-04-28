<?php

namespace App\Services;

use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

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
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        $deviceName = $agent->device() ?: '';
        $deviceType = $agent->isDesktop() ? 'Desktop'
                    : ($agent->isTablet() ? 'Tablet' : 'Mobile');
        $device   = $deviceName ? "{$deviceType} ({$deviceName})" : $deviceType;
        $platform = $agent->platform() ?: '';
        $browser  = $agent->browser()  ?: '';

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
