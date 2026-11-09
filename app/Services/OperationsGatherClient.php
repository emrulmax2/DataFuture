<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads a Gather event (Operations › Gather, the booking module) for the induction
 * invitation (user, 30 Oct 2026). A course creation stores the event's booking link;
 * the slug in it is how Operations finds the event (GET /api/gather/events/{slug}).
 *
 * Follows OperationsLibraryClient: the shared X-Operations-Key secret, a short timeout,
 * and a result rather than an exception, so a Gather outage leaves the induction task
 * open with a note instead of breaking a conversion.
 */
class OperationsGatherClient
{
    public const OK = 'ok';
    public const NOT_FOUND = 'not_found';
    public const ERROR = 'error';

    /**
     * The event slug in a booking link: /book/{slug}, /book/event/{slug}, or the admin
     * page /d/gather/admin/events/{slug}. Null when the link is none of those.
     */
    public static function slugFromLink(?string $link): ?string
    {
        $path = trim((string) parse_url(trim((string) $link), PHP_URL_PATH), '/');
        if ($path === '') {
            return null;
        }
        $parts = array_values(array_filter(explode('/', $path), fn ($part) => $part !== ''));

        $slug = null;
        $book = array_search('book', $parts, true);
        if ($book !== false) {
            $next = $parts[$book + 1] ?? null;
            $slug = $next === 'event' ? ($parts[$book + 2] ?? null) : $next;
            if (in_array($slug, ['confirmation', 'pay', 'ticket'], true)) {
                return null; // a booking, not an event
            }
        } else {
            $events = array_search('events', $parts, true);
            if ($events !== false && in_array('gather', $parts, true)) {
                $slug = $parts[$events + 1] ?? null;
            }
        }

        return $slug !== null && preg_match('/^[A-Za-z0-9\-_]+$/', $slug) ? $slug : null;
    }

    /** @return array{status:string, data?:array, message?:string} */
    public function event(string $slug): array
    {
        $key = (string) config('services.operations.api_key');
        if ($key === '') {
            return ['status' => self::ERROR, 'message' => 'No Operations API key is configured (OPERATIONS_API_KEY).'];
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.operations.timeout', 10))
                ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
                ->withHeaders(['X-Operations-Key' => $key])
                ->get(rtrim((string) config('services.operations.url'), '/').'/api/gather/events/'.rawurlencode($slug));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Gather event lookup failed.', ['slug' => $slug, 'error' => $e->getMessage()]);

            return ['status' => self::ERROR, 'message' => 'Operations could not be reached: '.$e->getMessage()];
        }

        if ($response->status() === 404) {
            return ['status' => self::NOT_FOUND, 'message' => 'Operations has no Gather event "'.$slug.'" (it may have been archived).'];
        }
        if (!$response->successful() || !is_array($response->json('data'))) {
            Log::warning('[Operations] Gather event lookup rejected.', ['slug' => $slug, 'status' => $response->status()]);

            return ['status' => self::ERROR, 'message' => 'Operations answered '.$response->status().' for the Gather event "'.$slug.'".'];
        }

        return ['status' => self::OK, 'data' => (array) $response->json('data')];
    }
}
