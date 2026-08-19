<?php

namespace App\Services\Sso;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Back-channel single logout.
 *
 * When a staff user signs out of the identity provider we tell every relying
 * application to drop their session too, so that "log out" means logged out
 * everywhere rather than logged out here and silently re-admitted on the
 * next zero-click handshake.
 */
class LogoutBroadcaster
{
    public static function broadcast(?string $email, ?string $sid): void
    {
        if (! config('sso.broadcast_logout', true) || ! config('sso.server_enabled', false)) {
            return;
        }

        if (! is_string($email) || $email === '') {
            return;
        }

        // Normalised before it is signed. Staff addresses are stored with
        // whatever casing they were created with - 93 of them carry capitals -
        // and the relying application lowercases before it verifies, so an
        // un-normalised address here produces a signature that cannot match.
        $email = strtolower(trim($email));

        $timeout = max(1, (int) config('sso.logout_timeout', 5));

        foreach (ClientRegistry::all() as $client) {
            if (! $client['logout_url']) {
                continue;
            }

            if (! ClientRegistry::allowsEmail($client, $email)) {
                continue;
            }

            $payload = [
                'client_id' => $client['id'],
                'email'     => $email,
                'sid'       => $sid,
                'issued_at' => time(),
            ];

            // The signature lets the client trust the payload without us
            // putting the shared secret on the wire.
            $payload['signature'] = hash_hmac(
                'sha256',
                $payload['client_id'].'|'.$payload['email'].'|'.$payload['sid'].'|'.$payload['issued_at'],
                $client['secret']
            );

            try {
                Http::timeout($timeout)->asJson()->post($client['logout_url'], $payload);
            } catch (\Throwable $e) {
                // A relying app being unreachable must never block sign-out here.
                Log::warning('SSO logout broadcast failed', [
                    'client' => $client['id'],
                    'error'  => $e->getMessage(),
                ]);
            }
        }
    }
}
