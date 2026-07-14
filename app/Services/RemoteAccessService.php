<?php

namespace App\Services;

use App\Models\User;
use App\Models\VenueIpAddress;

/**
 * Decides whether a user may use the portal from wherever they currently are.
 *
 * Two ways to be allowed:
 *   1. they are on a college network, or
 *   2. they hold remote access (optionally only within a date range).
 *
 * Reads privileges through User::priv(), so it automatically follows
 * config('privileges.source') and behaves identically on either source.
 */
class RemoteAccessService
{
    /**
     * Used when venue_ip_addresses is empty, matching the legacy fallback.
     */
    private const FALLBACK_IPS = ['62.31.168.43', '79.171.153.100', '149.34.178.243'];

    private ?array $venueIps = null;
    private array $decisions = [];

    public function allows(User $user): bool
    {
        return $this->decide($user)['allowed'];
    }

    /**
     * The full decision, so the audit middleware can log *why* a user would have
     * been blocked rather than just that they were.
     *
     * @return array{allowed:bool, reason:string, on_site:bool, login_ip:?string, request_ip:?string}
     */
    public function decide(User $user, ?string $mode = null): array
    {
        $mode = $mode ?: config('privileges.ip_mode', 'login');

        // A console run has no meaningful request IP, so fall back to judging on
        // the login IP alone. Returning a blanket "allowed" here instead would
        // fabricate access and hide the true value from commands and reports.
        if (app()->runningInConsole() && $mode !== 'login') {
            $mode = 'login';
        }

        $cacheKey = $user->id.':'.$mode;

        if (isset($this->decisions[$cacheKey])) {
            return $this->decisions[$cacheKey];
        }

        $loginIp = $user->last_login_ip;
        $requestIp = request()?->ip();
        $onSite = $this->isOnCollegeNetwork($loginIp, $requestIp, $mode);

        $priv = $user->priv();
        $hasRemote = !empty($priv['ra_status']) && $priv['ra_status'] == 1;

        if (!$hasRemote) {
            // No remote privilege: they must physically be on a college network.
            return $this->decisions[$cacheKey] = $this->result(
                $onSite,
                $onSite ? 'on_site' : 'off_site_without_remote_access',
                $onSite,
                $loginIp,
                $requestIp
            );
        }

        $isTemporary = !empty($priv['in_range']) && $priv['in_range'] == 1;
        $dateRange = $priv['date_range'] ?? '';

        if (!$isTemporary || empty($dateRange)) {
            // Remote access with no time limit.
            return $this->decisions[$cacheKey] = $this->result(true, 'remote_access_granted', $onSite, $loginIp, $requestIp);
        }

        [$start, $end] = $this->parseDateRange($dateRange);

        if ($start === null || $end === null) {
            // Unparseable range. The legacy code treated this as "no limit", and
            // narrowing it here would silently revoke access for those users.
            return $this->decisions[$cacheKey] = $this->result(true, 'remote_access_granted_unparsable_range', $onSite, $loginIp, $requestIp);
        }

        $today = date('Y-m-d');

        if ($today >= $start && $today <= $end) {
            return $this->decisions[$cacheKey] = $this->result(true, 'remote_access_within_date_range', $onSite, $loginIp, $requestIp);
        }

        // Outside the temporary window they fall back to being on a college network.
        return $this->decisions[$cacheKey] = $this->result(
            $onSite,
            $onSite ? 'on_site_outside_date_range' : 'off_site_outside_date_range',
            $onSite,
            $loginIp,
            $requestIp
        );
    }

    /**
     * config('privileges.ip_mode'):
     *   both    - the login IP AND the current request IP must both be college
     *             IPs. Strictest: a session that started on-site but is now being
     *             used from elsewhere no longer counts as on-site.
     *   request - only where they are right now matters.
     *   login   - only where they logged in from (the legacy behaviour, which
     *             trusts an IP recorded at login and never re-checked).
     */
    private function isOnCollegeNetwork(?string $loginIp, ?string $requestIp, string $mode): bool
    {
        $ips = $this->venueIps();
        $loginOnSite = $loginIp !== null && in_array($loginIp, $ips, true);
        $requestOnSite = $requestIp !== null && in_array($requestIp, $ips, true);

        return match ($mode) {
            'request' => $requestOnSite,
            'login' => $loginOnSite,
            default => $loginOnSite && $requestOnSite,
        };
    }

    private function venueIps(): array
    {
        if ($this->venueIps === null) {
            $ips = VenueIpAddress::pluck('ip')->filter()->unique()->values()->toArray();
            $this->venueIps = !empty($ips) ? $ips : self::FALLBACK_IPS;
        }

        return $this->venueIps;
    }

    /**
     * Legacy stores the range as "dd-mm-yyyy - dd-mm-yyyy". Parsed the same way it
     * always has been, so existing ranges keep resolving to the same dates.
     */
    private function parseDateRange(string $range): array
    {
        $parts = explode(' - ', $range);

        if (count($parts) !== 2) {
            return [null, null];
        }

        $start = strtotime(trim($parts[0]));
        $end = strtotime(trim($parts[1]));

        if ($start === false || $end === false) {
            return [null, null];
        }

        return [date('Y-m-d', $start), date('Y-m-d', $end)];
    }

    private function result(bool $allowed, string $reason, bool $onSite, ?string $loginIp, ?string $requestIp): array
    {
        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'on_site' => $onSite,
            'login_ip' => $loginIp,
            'request_ip' => $requestIp,
        ];
    }
}
