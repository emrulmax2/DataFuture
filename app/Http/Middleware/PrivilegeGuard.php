<?php

namespace App\Http\Middleware;

use App\Services\RemoteAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces privileges on the request itself, not just on the menus.
 *
 * Until this existed, privilege was applied by hiding links: anyone who knew a
 * URL could type it in and reach the controller. This closes that hole.
 *
 * SAFETY: with config('privileges.enforce') = false (the default) this
 * middleware CANNOT block anything. It records the decision it would have taken
 * and lets the request through, so the route map can be completed from real
 * traffic before a single user is ever denied.
 */
class PrivilegeGuard
{
    public function __construct(private RemoteAccessService $remoteAccess)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        // Only staff sessions are governed here. Students, applicants and agents
        // use their own guards and their own rules.
        if (!$user) {
            return $next($request);
        }

        // Break-glass: super admins are never gated, so a bad rule can never lock
        // out the people who have to fix it.
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($this->isAlwaysAllowed($routeName)) {
            return $next($request);
        }

        // 1. May they be here at all, from where they are?
        $remote = $this->remoteAccess->decide($user);

        if (!$remote['allowed']) {
            return $this->deny(
                $request,
                $user,
                $routeName,
                'remote_access:'.$remote['reason'],
                (bool) config('privileges.remote_enforce'),
                $next,
                $remote
            );
        }

        // 2. Do they hold a permission that covers this route?
        $required = $this->requiredFor($routeName);

        if ($required === null) {
            // No mapping. Strictly this is a denial, but only once enforcement and
            // deny_unmapped are both on; otherwise it is simply recorded so the
            // route can be mapped.
            if (config('privileges.deny_unmapped')) {
                return $this->deny(
                    $request,
                    $user,
                    $routeName,
                    'unmapped_route',
                    (bool) config('privileges.enforce'),
                    $next
                );
            }

            return $next($request);
        }

        if (!$this->holdsAny($user, $required)) {
            return $this->deny(
                $request,
                $user,
                $routeName,
                'missing_permission:'.implode('|', $required),
                $this->isEnforced($routeName),
                $next
            );
        }

        return $next($request);
    }

    /**
     * Enforcement is either global, or switched on for named routes so a screen
     * can be locked down without turning enforcement on everywhere at once.
     */
    private function isEnforced(?string $routeName): bool
    {
        if (config('privileges.enforce')) {
            return true;
        }

        if ($routeName === null) {
            return false;
        }

        foreach (config('privileges.enforce_routes', []) as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Blocks the request, or - when enforcement is off - records what it would
     * have blocked and lets it through untouched.
     */
    private function deny(
        Request $request,
        $user,
        ?string $routeName,
        string $reason,
        bool $enforcing,
        Closure $next,
        array $remote = []
    ): Response {
        Log::channel('privileges')->info($enforcing ? 'blocked' : 'would_block', array_filter([
            'user_id' => $user->id,
            'user' => $user->name,
            'route' => $routeName ?: '(unnamed)',
            'uri' => $request->path(),
            'method' => $request->method(),
            'reason' => $reason,
            'login_ip' => $remote['login_ip'] ?? null,
            'request_ip' => $remote['request_ip'] ?? $request->ip(),
        ]));

        if (!$enforcing) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You are not permitted to perform this action.',
            ], 403);
        }

        abort(403, 'You are not permitted to access this page.');
    }

    /**
     * @return array<string>|null  null when the route has no mapping at all
     */
    private function requiredFor(?string $routeName): ?array
    {
        if ($routeName === null) {
            return null;
        }

        $routes = config('privileges.routes', []);

        // An exact name wins over a wildcard, so a specific route can be given a
        // different key from the group it sits in.
        if (isset($routes[$routeName])) {
            return (array) $routes[$routeName];
        }

        foreach ($routes as $pattern => $keys) {
            if (Str::is($pattern, $routeName)) {
                return (array) $keys;
            }
        }

        return null;
    }

    private function holdsAny($user, array $keys): bool
    {
        $priv = $user->priv();

        foreach ($keys as $key) {
            if (!empty($priv[$key]) && $priv[$key] != '0') {
                return true;
            }
        }

        return false;
    }

    private function isAlwaysAllowed(?string $routeName): bool
    {
        if ($routeName === null) {
            // An unnamed route cannot be mapped, so it can never be matched
            // against the map. Letting it through keeps this from silently
            // blocking things the map has no way to describe.
            return true;
        }

        foreach (config('privileges.always_allow', []) as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }
}
