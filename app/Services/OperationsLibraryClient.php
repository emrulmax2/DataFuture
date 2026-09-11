<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads the library catalogue from Operations.
 *
 * The catalogue (cam_library_titles / cam_library_copies) lives there and is
 * maintained there; this app holds only deposits and loans. Nothing is cached
 * beyond the request, because copy availability is the whole point of the
 * search and a stale "available" sends a student to an empty shelf.
 *
 * Follows OperationsBudgetClient: shared-secret header, short timeout, and null
 * on failure rather than an exception, so a catalogue outage renders a portal
 * page that says so instead of a 500 in a student's face.
 */
class OperationsLibraryClient
{
    private function request()
    {
        $key = (string) config('services.operations.api_key');

        if ($key === ''):
            Log::warning('[Operations] No API key configured; library catalogue unavailable.');

            return null;
        endif;

        return Http::acceptJson()
            ->timeout((int) config('services.operations.timeout', 10))
            ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
            ->withHeaders(['X-Operations-Key' => $key]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.operations.url'), '/').'/api/library/'.ltrim($path, '/');
    }

    /**
     * Search the catalogue.
     *
     * @param  array{q?:string,venue?:string,course?:int,module?:int,availability?:string,page?:int}  $filters
     * @return array{data:array,total:int,page:int,per_page:int}|null  null = the
     *         call failed, which is not the same as "no results".
     */
    public function search(array $filters): ?array
    {
        $http = $this->request();

        if (!$http):
            return null;
        endif;

        try {
            $response = $http->get($this->url('titles'), array_filter([
                'q' => $filters['q'] ?? null,
                'venue' => $filters['venue'] ?? null,
                'course' => $filters['course'] ?? null,
                'module' => $filters['module'] ?? null,
                'availability' => $filters['availability'] ?? null,
                'page' => $filters['page'] ?? 1,
                'per_page' => $filters['per_page'] ?? 12,
            ], fn ($v) => $v !== null && $v !== ''));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Library search failed.', ['error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()):
            Log::warning('[Operations] Library search rejected.', ['status' => $response->status()]);

            return null;
        endif;

        return [
            'data' => (array) $response->json('data', []),
            'total' => (int) $response->json('meta.total', 0),
            'page' => (int) $response->json('meta.page', 1),
            'per_page' => (int) $response->json('meta.per_page', 12),
        ];
    }

    /**
     * Options for the search form: venues, courses and modules that actually
     * have books behind them.
     *
     * Cached briefly — this changes when the catalogue is re-tagged, not per
     * request, and every student loading the page would otherwise call out.
     * Empty arrays on failure, so the form still renders with a working search
     * box rather than taking the whole page down.
     */
    public function filters(): array
    {
        $empty = ['venues' => [], 'courses' => [], 'modules' => []];

        return Cache::remember('library.filters', now()->addMinutes(10), function () use ($empty) {
            $http = $this->request();

            if (!$http):
                return $empty;
            endif;

            try {
                $response = $http->get($this->url('filters'));
            } catch (\Throwable $e) {
                Log::warning('[Operations] Library filters failed.', ['error' => $e->getMessage()]);

                return $empty;
            }

            return $response->successful() ? ((array) $response->json('data')) + $empty : $empty;
        });
    }

    /** One title with its copies, for the detail dialog and the borrow guard. */
    public function title($titleId): ?array
    {
        $http = $this->request();

        if (!$http):
            return null;
        endif;

        try {
            $response = $http->get($this->url('titles/'.$titleId));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Library title lookup failed.', ['id' => $titleId, 'error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? (array) $response->json('data') : null;
    }

    /**
     * Hold a copy for a student.
     *
     * Operations owns copy status, so it decides which copy is free and marks
     * it — this app must never guess, or two students booking at once both get
     * told the same copy is theirs.
     *
     * @return array{copy:array}|null  null when no copy could be held.
     */
    public function holdCopy($titleId, array $payload): ?array
    {
        $http = $this->request();

        if (!$http):
            return null;
        endif;

        try {
            $response = $http->post($this->url('titles/'.$titleId.'/hold'), $payload);
        } catch (\Throwable $e) {
            Log::warning('[Operations] Library hold failed.', ['id' => $titleId, 'error' => $e->getMessage()]);

            return null;
        }

        if (!$response->successful()):
            Log::warning('[Operations] Library hold rejected.', ['id' => $titleId, 'status' => $response->status()]);

            return null;
        endif;

        return (array) $response->json('data');
    }

    /**
     * Every copy Operations currently has off the shelf.
     *
     * @return array<int,array>|null  null when the call fails — which must not
     *                                be read as "nothing is held".
     */
    public function heldCopies(): ?array
    {
        $http = $this->request();

        if (!$http):
            return null;
        endif;

        try {
            $response = $http->get($this->url('copies/held'));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Held-copy list failed.', ['error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? (array) $response->json('data', []) : null;
    }

    /**
     * Move a reserved copy to on-loan when the desk hands it over.
     *
     * Best effort: the book is physically with the student either way, so a
     * failed call is logged rather than blocking the issue.
     */
    public function issueCopy($copyId): bool
    {
        $http = $this->request();

        if (!$http || !$copyId):
            return false;
        endif;

        try {
            $response = $http->post($this->url('copies/'.$copyId.'/issue'));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Library issue failed.', ['copy' => $copyId, 'error' => $e->getMessage()]);

            return false;
        }

        return $response->successful();
    }

    /** Give a held or borrowed copy back. Best effort: the loan closes either way. */
    public function releaseCopy($copyId, array $payload = []): bool
    {
        $http = $this->request();

        if (!$http || !$copyId):
            return false;
        endif;

        try {
            $response = $http->post($this->url('copies/'.$copyId.'/release'), $payload);
        } catch (\Throwable $e) {
            Log::warning('[Operations] Library release failed.', ['copy' => $copyId, 'error' => $e->getMessage()]);

            return false;
        }

        return $response->successful();
    }
}
