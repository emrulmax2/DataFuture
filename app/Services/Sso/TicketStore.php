<?php

namespace App\Services\Sso;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Short-lived, single-use handoff tickets.
 *
 * The ticket travels through the user's browser as a query string, so it
 * carries no identity of its own - it is only a lookup key. The identity
 * payload stays server side in the cache and is released once, over a
 * back-channel request that is authenticated with the client secret.
 */
class TicketStore
{
    private const PREFIX = 'sso:ticket:';

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function issue(array $payload): string
    {
        $ticket = Str::random(64);

        self::store()->put(self::PREFIX.hash('sha256', $ticket), $payload, self::ttl());

        return $ticket;
    }

    /**
     * Redeem a ticket, removing it so a replay finds nothing.
     *
     * @return array<string, mixed>|null
     */
    public static function consume(?string $ticket): ?array
    {
        if (! is_string($ticket) || $ticket === '') {
            return null;
        }

        $payload = self::store()->pull(self::PREFIX.hash('sha256', $ticket));

        return is_array($payload) ? $payload : null;
    }

    /**
     * A dedicated store, kept clear of the application cache that sign-out
     * flushes wholesale.
     */
    private static function store(): Repository
    {
        return Cache::store(config('sso.ticket_store', 'sso'));
    }

    private static function ttl(): int
    {
        return max(10, (int) config('sso.ticket_ttl', 60));
    }
}
