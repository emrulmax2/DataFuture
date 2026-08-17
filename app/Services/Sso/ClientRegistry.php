<?php

namespace App\Services\Sso;

use Illuminate\Support\Str;

/**
 * Resolves relying applications from config/sso.php and answers the three
 * questions the authorize/token endpoints need: is this a real client, is
 * this redirect URI one it registered, and may it see this email address.
 */
class ClientRegistry
{
    /**
     * @return array<string, mixed>|null
     */
    public static function find(?string $clientId): ?array
    {
        if (! is_string($clientId) || $clientId === '') {
            return null;
        }

        $config = config('sso.clients.'.$clientId);

        // A client with no secret provisioned is treated as absent rather than
        // as a client that accepts an empty secret.
        if (! is_array($config) || empty($config['secret'])) {
            return null;
        }

        return [
            'id'         => $clientId,
            'name'       => $config['name'] ?? $clientId,
            'secret'     => (string) $config['secret'],
            'redirects'  => self::listFrom($config['redirects'] ?? ''),
            'domains'    => array_map('strtolower', self::listFrom($config['domains'] ?? '')),
            'logout_url' => $config['logout_url'] ?: null,
        ];
    }

    /**
     * Constant-time secret check.
     */
    public static function secretMatches(array $client, ?string $presented): bool
    {
        if (! is_string($presented) || $presented === '') {
            return false;
        }

        return hash_equals($client['secret'], $presented);
    }

    /**
     * Redirect URIs are compared exactly - no prefix or wildcard matching -
     * so that a client cannot be talked into forwarding a ticket elsewhere.
     */
    public static function allowsRedirect(array $client, ?string $redirectUri): bool
    {
        if (! is_string($redirectUri) || $redirectUri === '') {
            return false;
        }

        return in_array($redirectUri, $client['redirects'], true);
    }

    /**
     * An empty domain list means the client accepts any staff address.
     */
    public static function allowsEmail(array $client, ?string $email): bool
    {
        if (! is_string($email) || ! Str::contains($email, '@')) {
            return false;
        }

        if ($client['domains'] === []) {
            return true;
        }

        $domain = strtolower(Str::afterLast($email, '@'));

        return in_array($domain, $client['domains'], true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $clients = [];

        foreach (array_keys((array) config('sso.clients', [])) as $id) {
            if ($client = self::find($id)) {
                $clients[] = $client;
            }
        }

        return $clients;
    }

    /**
     * @return array<int, string>
     */
    private static function listFrom($value): array
    {
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = explode(',', (string) $value);
        }

        return array_values(array_filter(array_map('trim', $parts), fn ($v) => $v !== ''));
    }
}
