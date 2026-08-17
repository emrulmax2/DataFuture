<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Sso\ClientRegistry;
use App\Services\Sso\TicketStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SSO identity provider.
 *
 * GET  /sso/authorize   browser lands here, leaves with a single-use ticket
 * POST /api/sso/token   relying app redeems that ticket for the identity
 *
 * Only the staff guard takes part. Students, applicants and agents have their
 * own guards and are never issued a ticket.
 */
class SsoServerController extends Controller
{
    /**
     * Front-channel: decide whether this browser is already signed in here,
     * and if so hand back a ticket.
     */
    public function authorizeClient(Request $request)
    {
        if (! config('sso.server_enabled', false)) {
            abort(404);
        }

        $client = ClientRegistry::find($request->query('client_id'));

        if (! $client) {
            return $this->fail($request, 'Unknown SSO client.');
        }

        $redirectUri = (string) $request->query('redirect_uri', '');

        // Until the redirect URI is known-good we must not bounce the browser
        // to it, so failures before this point render locally instead.
        if (! ClientRegistry::allowsRedirect($client, $redirectUri)) {
            return $this->fail($request, 'This redirect URI is not registered for '.$client['name'].'.');
        }

        $state  = (string) $request->query('state', '');
        $silent = $request->boolean('silent');

        $guard = config('sso.guard', 'web');

        if (! Auth::guard($guard)->check()) {
            // Zero-click probe: the client is asking "is anyone signed in?"
            // and must get a prompt-free answer either way.
            if ($silent) {
                return $this->back($redirectUri, ['sso_error' => 'login_required', 'state' => $state]);
            }

            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route(config('sso.login_route', 'login.index'));
        }

        /** @var User $user */
        $user = Auth::guard($guard)->user();

        if (! $user->active) {
            return $this->back($redirectUri, ['sso_error' => 'account_inactive', 'state' => $state]);
        }

        if (! ClientRegistry::allowsEmail($client, $user->email)) {
            return $this->back($redirectUri, ['sso_error' => 'domain_not_allowed', 'state' => $state]);
        }

        $ticket = TicketStore::issue([
            'client_id'    => $client['id'],
            'redirect_uri' => $redirectUri,
            'user_id'      => $user->id,
            'email'        => $user->email,
            'name'         => $user->name,
            'social_id'    => $user->social_id,
            'social_type'  => $user->social_type,
            'avatar'       => $this->avatarFor($user),
            'sid'          => $this->sessionId($request),
            'issued_at'    => time(),
        ]);

        return $this->back($redirectUri, ['ticket' => $ticket, 'state' => $state]);
    }

    /**
     * Back-channel: exchange the ticket for the identity behind it.
     *
     * Authenticated with the client secret, so this is the only step that
     * actually reveals who the user is.
     */
    public function token(Request $request): JsonResponse
    {
        if (! config('sso.server_enabled', false)) {
            abort(404);
        }

        $client = ClientRegistry::find($request->input('client_id'));

        if (! $client || ! ClientRegistry::secretMatches($client, $request->input('client_secret'))) {
            return response()->json(['error' => 'invalid_client'], 401);
        }

        $payload = TicketStore::consume($request->input('ticket'));

        if (! $payload) {
            return response()->json(['error' => 'invalid_ticket'], 400);
        }

        // A ticket minted for one client must not be redeemable by another,
        // nor against a redirect URI it was not bound to.
        if (! hash_equals((string) $payload['client_id'], $client['id'])) {
            return response()->json(['error' => 'ticket_client_mismatch'], 400);
        }

        if ((string) $request->input('redirect_uri', '') !== (string) $payload['redirect_uri']) {
            return response()->json(['error' => 'redirect_uri_mismatch'], 400);
        }

        if (! ClientRegistry::allowsEmail($client, $payload['email'])) {
            return response()->json(['error' => 'domain_not_allowed'], 403);
        }

        return response()->json([
            'email'       => $payload['email'],
            'name'        => $payload['name'],
            'social_id'   => $payload['social_id'],
            'social_type' => $payload['social_type'],
            'avatar'      => $payload['avatar'],
            'sid'         => $payload['sid'],
            'issued_at'   => $payload['issued_at'],
        ]);
    }

    /**
     * Stable identifier for this identity-provider session, so a later logout
     * broadcast can name the session that is ending.
     */
    private function sessionId(Request $request): string
    {
        if (! $request->session()->has('sso_sid')) {
            $request->session()->put('sso_sid', (string) Str::uuid());
        }

        return (string) $request->session()->get('sso_sid');
    }

    /**
     * The photo accessor reaches for remote storage, so a failure here must
     * not take the whole sign-in down.
     */
    private function avatarFor(User $user): ?string
    {
        try {
            return $user->photo_url ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function back(string $redirectUri, array $params)
    {
        $params = array_filter($params, fn ($v) => $v !== null && $v !== '');

        $glue = Str::contains($redirectUri, '?') ? '&' : '?';

        return redirect()->away($redirectUri.$glue.http_build_query($params));
    }

    private function fail(Request $request, string $message)
    {
        Log::warning('SSO authorize rejected', [
            'message'   => $message,
            'client_id' => $request->query('client_id'),
            'redirect'  => $request->query('redirect_uri'),
            'ip'        => $request->ip(),
        ]);

        return response()->view('errors.sso', ['message' => $message], 400);
    }
}
