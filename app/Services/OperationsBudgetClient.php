<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads a requisition from the Operations system.
 *
 * Budget Management moved there, but transactions are still reconciled here, so
 * the accounts screens need to show what a transaction was spent on without
 * sending the reader to another system.
 *
 * Returns null rather than throwing: a requisition that cannot be fetched is a
 * page that says so, not a 500 on an accounts screen someone is mid-way
 * through reconciling.
 */
class OperationsBudgetClient
{
    /**
     * Every requisition reference Operations holds.
     *
     * Used before converting transaction links: a link pointed at a reference
     * that does not exist there would render a dead briefcase, so the set is
     * checked up front rather than discovered one 404 at a time.
     *
     * @return array<int,string>|null  null when the call fails, which is not
     *                                 the same as "there are none".
     */
    public function references(): ?array
    {
        $key = (string) config('services.operations.api_key');

        if ($key === '') {
            Log::warning('[Operations] No API key configured; cannot list references.');

            return null;
        }

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.operations.timeout', 10))
                ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
                ->withHeaders(['X-Operations-Key' => $key])
                ->get(rtrim((string) config('services.operations.url'), '/') . '/api/budget/references');
        } catch (\Throwable $e) {
            Log::warning('[Operations] Reference list failed.', ['error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? (array) $response->json('data') : null;
    }

    public function requisition(string $reference): ?array
    {
        $key = (string) config('services.operations.api_key');

        if ($key === '') {
            Log::warning('[Operations] No API key configured; cannot read requisitions.');

            return null;
        }

        $url = rtrim((string) config('services.operations.url'), '/')
            . '/api/budget/requisitions/' . urlencode($reference);

        try {
            $response = Http::acceptJson()
                ->timeout((int) config('services.operations.timeout', 10))
                ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
                ->withHeaders(['X-Operations-Key' => $key])
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('[Operations] Requisition fetch failed.', ['ref' => $reference, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('[Operations] Requisition fetch rejected.', [
                'ref' => $reference,
                'status' => $response->status(),
            ]);

            return null;
        }

        return $response->json('data');
    }
}
