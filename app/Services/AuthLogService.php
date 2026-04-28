<?php

namespace App\Services;

use App\Models\LoginLog;
use Carbon\Carbon;

class AuthLogService
{
    // Reason constants used across the app
    const REASON_MANUAL    = 'manual_logout';
    const REASON_TIMEOUT   = 'session_timeout';
    const REASON_INVALID   = 'session_invalidated';

    /**
     * Open a new login log record.
     *
     * Also closes any stale open records for the same actor that are older
     * than the configured session lifetime (handles sessions lost to PHP GC
     * without our middleware ever seeing them).
     */
    public static function logLogin(
        int    $actorId,
        string $actorType,
        string $guardName,
        string $sessionId,
        ?string $ipAddress = null,
        ?string $userAgent = null
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

        return LoginLog::create([
            'actor_id'   => $actorId,
            'actor_type' => $actorType,
            'guard_name' => $guardName,
            'session_id' => $sessionId,
            'login_at'   => Carbon::now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Close all open log records for the given actor.
     *
     * Idempotent: calling it when already closed has no effect.
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
