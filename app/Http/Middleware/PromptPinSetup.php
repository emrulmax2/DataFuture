<?php

namespace App\Http\Middleware;

use App\Models\VenueIpAddress;
use App\Services\StaffDocumentVaultService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Once HR has switched "PIN Enabled" on for a member of staff, they set up
 * their PIN before anything else: from their next sign-in, every page they go
 * to becomes the PIN set-up page until the PIN is set. After that they carry
 * on as usual and this never interrupts them again.
 *
 * One thing comes before it: clocking in. Somebody who clocks in from their
 * dashboard and has not done so yet today is left alone until they have, and
 * the page after that is the PIN set-up page.
 *
 * It lives here rather than in the login controllers because there are three
 * ways in (password, Google, Microsoft) and all of them should behave alike.
 *
 * Nothing is remembered about somebody who has no PIN to set up: the question
 * is asked afresh on every page, so switching PIN on for them takes effect on
 * the very next page they open. (An earlier version remembered "nothing to do"
 * in the session. Signing out here does not clear the session, so signing out
 * and in again after being given the permission changed nothing.) Only "has a
 * PIN" is remembered, because that does not change back.
 */
class PromptPinSetup
{
    /**
     * Holds the id of the user this session knows to have a PIN already.
     *
     * Not 'pin_setup_settled_for': sessions written by the earlier version
     * carry that key with the old meaning ("nothing to set up"), and reading
     * it now would skip the very people this is for.
     */
    const SETTLED_KEY = 'pin_setup_has_pin_for';

    /** Where they were first heading when they were sent to set a PIN. */
    const RETURN_KEY = 'pin_setup_return_to';

    /** Flashed for the PIN page, so it can say why they are looking at it. */
    const PROMPT_FLASH = 'pin_setup_prompt';

    /**
     * Pages that are never interrupted: signing in and out, the first-login
     * welcome, single sign-on hand-offs, impersonation, and the PIN page.
     */
    const EXEMPT = ['login', 'logout', 'welcome.first', 'privilege.denied', 'impersonate*', 'sso.*', 'user.account.document.pin*'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        // Only a page the browser is navigating to. Background requests,
        // images and downloads are left alone.
        if (!$user || !$request->isMethod('GET') || $request->ajax() || !$request->hasSession()
            || !Str::contains((string) $request->header('Accept'), 'text/html')) {
            return $next($request);
        }

        if ((int) $request->session()->get(self::SETTLED_KEY) === (int) $user->id) {
            return $next($request);
        }

        // The account being impersonated is not the person at the keyboard,
        // and its PIN cannot be set from an impersonated session anyway.
        if (StaffDocumentVaultService::isImpersonating()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null || Str::is(self::EXEMPT, $routeName)) {
            return $next($request);
        }

        $state = $this->state($user);

        // Has a PIN (or the check failed): nothing more to ask in this session.
        if ($state === 'done') {
            $request->session()->put(self::SETTLED_KEY, $user->id);
        }

        if ($state !== 'due') {
            return $next($request);
        }

        // Clocking in comes first. Nothing is settled yet, so the page after
        // they have clocked in is the one that becomes PIN set-up.
        if ($this->awaitingClockIn($user)) {
            return $next($request);
        }

        if (!$request->session()->has(self::RETURN_KEY)) {
            $request->session()->put(self::RETURN_KEY, $request->fullUrl());
        }

        return redirect()->route('user.account.document.pin')->with(self::PROMPT_FLASH, true);
    }

    /**
     * Where this user stands:
     *   none  PIN is not switched on for them - ask again on the next page
     *   due   switched on, and no PIN yet
     *   done  they have a PIN
     *
     * If it cannot be worked out the answer is "done", for the rest of the
     * session. A fault here must never come between somebody and every page
     * of the application.
     */
    private function state($user): string
    {
        try {
            $vault = app(StaffDocumentVaultService::class);

            if (!$vault->switchedOnFor($user)) {
                return 'none';
            }

            return $vault->hasPin($user) ? 'done' : 'due';
        } catch (\Throwable $e) {
            Log::error('PIN set-up check failed for user '.$user->id.': '.$e->getMessage());

            return 'done';
        }
    }

    /**
     * Is this somebody who clocks in from their dashboard, and has not yet
     * done so today?
     *
     * "Clocks in from their dashboard" is User::canPunchFromDesktop(), the
     * same rule the dashboard draws its Clock In button from and the punch
     * endpoint enforces. Anybody else has nothing to clock in on here, so
     * there is nothing to wait for. A break or a clock-out later in the day
     * still counts as having clocked in.
     */
    private function awaitingClockIn($user): bool
    {
        try {
            if (!$user->canPunchFromDesktop(VenueIpAddress::pluck('ip')->unique()->toArray())) {
                return false;
            }

            $employment = optional($user->employee)->employment;
            if (!$employment) {
                return false;
            }

            return !($employment->last_action_date == date('Y-m-d') && (int) $employment->last_action > 0);
        } catch (\Throwable $e) {
            Log::error('Clock-in check before PIN set-up failed for user '.$user->id.': '.$e->getMessage());

            return false;
        }
    }
}
