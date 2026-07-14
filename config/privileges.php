<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Privilege source
    |--------------------------------------------------------------------------
    | Which table User::priv() reads from.
    |
    |   legacy -> user_privileges       (the live system; unchanged behaviour)
    |   new    -> employee_permissions  (the new system, reverse-mapped to the
    |                                    legacy key names so every existing
    |                                    priv()['x'] check keeps working)
    |
    | Flipping this is the cutover. It is a single env var so it can be reverted
    | instantly, with no deploy. Do not flip it until `php artisan
    | privileges:verify` reports zero differences for every user.
    */
    'source' => env('PRIVILEGE_SOURCE', 'legacy'),

    /*
    |--------------------------------------------------------------------------
    | Route enforcement
    |--------------------------------------------------------------------------
    | false -> middleware logs what it WOULD have blocked and lets the request
    |          through. Nobody is ever denied. Use this to collect evidence from
    |          real traffic before switching enforcement on.
    | true  -> the middleware actually returns 403.
    */
    'enforce' => (bool) env('PRIVILEGE_ENFORCE', false),

    /*
    | Same idea, for the off-campus / remote-access gate. Kept separate because
    | it can lock out every user at once, so it is the riskiest switch here.
    */
    'remote_enforce' => (bool) env('PRIVILEGE_REMOTE_ENFORCE', false),

    /*
    |--------------------------------------------------------------------------
    | How the college-network check treats the two IPs
    |--------------------------------------------------------------------------
    |   both    -> on-site only if the login IP AND the current request IP are
    |              college IPs (strictest; catches a session that moved off-site)
    |   request -> only the current request IP has to be a college IP
    |   login   -> only the login IP (the legacy behaviour)
    |
    | Defaults to `login` ON PURPOSE. remote_access already gates the live
    | Accounts pages, so shipping `both` immediately would revoke access from
    | anyone whose current IP differs from the one recorded at login (mobile
    | networks, VPNs, proxies) with no warning.
    |
    | `both` is the target. Get there safely: leave this on `login`, let the
    | audit middleware log what `both` WOULD have decided against real traffic,
    | review with `php artisan privileges:audit-report`, then flip to `both`.
    */
    'ip_mode' => env('PRIVILEGE_IP_MODE', 'login'),

    /*
    |--------------------------------------------------------------------------
    | Break-glass
    |--------------------------------------------------------------------------
    | These users are never blocked by the privilege or remote-access
    | middleware. Without this, a bad rule could lock every administrator out of
    | the very screens needed to fix it.
    */
    'bypass_user_ids' => array_values(array_filter(array_map(
        'intval',
        explode(',', (string) env('PRIVILEGE_BYPASS_IDS', '1,7'))
    ))),

    /*
    | Same, by employee id. Employee #1 is the super admin's staff record, and it
    | is listed separately because an employee can be relinked to a different
    | user account, which would otherwise silently drop the bypass.
    */
    'bypass_employee_ids' => array_values(array_filter(array_map(
        'intval',
        explode(',', (string) env('PRIVILEGE_BYPASS_EMPLOYEE_IDS', '1'))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Unmapped routes
    |--------------------------------------------------------------------------
    | true  -> a route with no entry in the `routes` map below is DENIED. This is
    |          the strict, correct posture: a new screen is locked until somebody
    |          deliberately says who may see it.
    | false -> unmapped routes are allowed.
    |
    | Only meaningful once `enforce` is true. Until then every decision is merely
    | logged, which is how the map below gets filled in from real traffic.
    */
    'deny_unmapped' => (bool) env('PRIVILEGE_DENY_UNMAPPED', true),

    /*
    | Never guarded, whatever else is set. Auth, the landing dashboard and a
    | user's own profile must stay reachable or a user cannot even log in to be
    | told they lack access. Matched against the route name with * as a wildcard.
    */
    'always_allow' => [
        'login', 'logout', 'register', 'password.*', 'verification.*',
        'dashboard', 'home',
        'my-account*', 'profile*',
        'impersonate*',
        'privilege.denied',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route name => permission key(s)
    |--------------------------------------------------------------------------
    | The user needs ANY ONE of the listed keys. Keys are the legacy names, i.e.
    | exactly what priv() returns, so they read the same on either source.
    |
    | Wildcards allowed: 'site-settings.*' => 'site_settings'.
    | This map is deliberately incomplete: run in log-only mode, then use
    | `php artisan privileges:audit-report` to see which real, used routes are
    | still unmapped and add them here.
    */
    'routes' => [
        // ── VERIFIED ────────────────────────────────────────────────────────
        // The Live Student section. Both entries are needed: gating only the page
        // would still leave its data readable through the list endpoint.
        //
        // Deliberately NOT 'student.*'. 60 tutors and personal tutors hold no
        // `live` privilege yet legitimately reach student records through tutor
        // flows, so gating all 304 student.* routes on `live` would lock them out
        // of their daily work. Only the section entry is gated.
        'student' => 'live',
        'student.list' => 'live',

        // ── UNVERIFIED ──────────────────────────────────────────────────────
        // Add entries here ONLY with evidence. A wrong mapping here locks real
        // staff out of a screen they use every day, and a plausible-looking
        // wildcard is exactly how that happens (see the tutor case above).
        //
        // The safe way to fill this in:
        //   1. leave `enforce` false so nothing is blocked,
        //   2. let staff work,
        //   3. `php artisan privileges:audit-report` shows the routes really in
        //      use and who would be denied,
        //   4. map them, confirm the blast radius, then enforce.
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes enforced right now, regardless of `enforce`
    |--------------------------------------------------------------------------
    | Lets a single screen be locked down without switching enforcement on for
    | the whole application. This is how privileges get enforced incrementally:
    | prove one area, add it here, move on - instead of one flip that either
    | works or locks out 200 people.
    |
    | Matched against the route name, * allowed.
    */
    'enforce_routes' => [
        'student',
        'student.list',
    ],

    /*
    |--------------------------------------------------------------------------
    | Accounts roles (accounts_privilege_type)
    |--------------------------------------------------------------------------
    | Audit does not just gate entry: it narrows the rows a user may see (the
    | $audit_status pattern in the Accounts controllers).
    */
    'accounts_roles' => [
        'admin' => 1,
        'user' => 2,
        'audit' => 3,
    ],

];
