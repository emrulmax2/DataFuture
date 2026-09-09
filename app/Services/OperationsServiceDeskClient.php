<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads Service Desk tickets from Operations.
 *
 * Staff there tag a student on a ticket to record who it concerns; this brings
 * those onto the student's record here. Tickets live in Operations and are
 * worked there — nothing is written back, and nothing is stored locally, so
 * what a member of staff sees on this page is the ticket as it stands now.
 *
 * Follows OperationsLibraryClient: shared-secret header, short timeout, and
 * null on failure rather than an exception, so an Operations outage leaves the
 * student record rendering a line that says so instead of a 500.
 *
 * Internal notes are filtered out at the far end, not here. That is deliberate:
 * the rule belongs with the data, and this client must not be the thing anyone
 * relies on to enforce it.
 */
class OperationsServiceDeskClient
{
    private function request()
    {
        $key = (string) config('services.operations.api_key');

        if ($key === ''):
            Log::warning('[Operations] No API key configured; Service Desk tickets unavailable.');

            return null;
        endif;

        return Http::acceptJson()
            ->timeout((int) config('services.operations.timeout', 10))
            ->withOptions(['verify' => (bool) config('services.operations.verify_tls', true)])
            ->withHeaders(['X-Operations-Key' => $key]);
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.operations.url'), '/').'/api/service-desk/'.ltrim($path, '/');
    }

    /**
     * Every ticket this student has been tagged on.
     *
     * @return array<int, array<string, mixed>>|null  null means Operations could not be reached
     */
    public function ticketsForStudent(int|string $studentId): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get($this->url("students/{$studentId}/tickets"));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk ticket list failed.', ['student' => $studentId, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()):
            Log::warning('[Operations] Service Desk ticket list refused.', ['student' => $studentId, 'status' => $response->status()]);

            return null;
        endif;

        return $response->json('data', []);
    }

    /**
     * A file from a ticket's conversation.
     *
     * Fetched rather than linked. The Operations endpoint is guarded by a
     * shared key that only a server can present, so a browser sent straight at
     * it is refused — this app holds the key and its own session, and hands the
     * file to a member of staff who is already signed in here.
     *
     * Read whole rather than streamed: ticket attachments are capped at 10MB at
     * the far end, and a passthrough stream would buy little for the complexity.
     *
     * @return array{body: string, type: string, name: string}|null
     */
    public function attachment(int $attachmentId): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get($this->url("attachments/{$attachmentId}"));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk attachment failed.', ['attachment' => $attachmentId, 'error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()):
            return null;
        endif;

        return [
            'body' => $response->body(),
            'type' => $response->header('Content-Type') ?: 'application/octet-stream',
            /* Operations names the file on the way out; fall back only if it did not. */
            'name' => $this->filenameFrom($response->header('Content-Disposition')) ?: 'attachment',
        ];
    }

    /** The filename out of a Content-Disposition header, if it carries one. */
    private function filenameFrom(?string $disposition): ?string
    {
        if (! $disposition):
            return null;
        endif;

        return preg_match('/filename\*?=(?:UTF-8\'\'|")?([^";]+)/i', $disposition, $found)
            ? urldecode(trim($found[1], '"'))
            : null;
    }

    /**
     * One ticket, with the conversation the student's record may show.
     *
     * @return array<string, mixed>|null
     */
    public function ticket(int $ticketId): ?array
    {
        $request = $this->request();

        if (! $request):
            return null;
        endif;

        try {
            $response = $request->get($this->url("tickets/{$ticketId}"));
        } catch (\Throwable $e) {
            Log::warning('[Operations] Service Desk ticket failed.', ['ticket' => $ticketId, 'error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? $response->json('data') : null;
    }
}
