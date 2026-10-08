<?php

namespace App\Jobs;

use App\Models\StudentMitigatingCircumstance;
use App\Services\MitigatingCircumstanceTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Second and later attempts at raising an academic appeal ticket.
 *
 * The first attempt happens in the request, so the student normally has their
 * reference before the page reloads. This exists for the times it does not:
 * Operations restarting, a timeout, a network blip. The idempotency key means
 * a retry after a response that never arrived returns the ticket that was in
 * fact created rather than opening a second one.
 */
class RaiseMitigatingCircumstanceTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;

    /** @var array<int, int> */
    public $backoff = [60, 300, 900, 3600];

    public function __construct(public int $requestId)
    {
    }

    public function handle(MitigatingCircumstanceTicket $tickets): void
    {
        $row = StudentMitigatingCircumstance::with('documents')->find($this->requestId);

        if (! $row || $row->ticket_status === 'raised'):
            return;
        endif;

        if (! $tickets->raise($row, $this->storedFiles($row))):
            /* Throwing fails the attempt, which is what puts it back on the
               queue under the backoff above; the reason is already on the row. */
            throw new \RuntimeException(
                'Mitigating circumstances claim '.$row->id.' could not be raised: '.$row->ticket_failed_reason
            );
        endif;
    }

    /**
     * The files the student attached, read back out of storage.
     *
     * @return array<int, array{name: string, contents: string}>
     */
    private function storedFiles(StudentMitigatingCircumstance $row): array
    {
        return $row->documents->map(function ($document) use ($row) {
            try {
                $contents = Storage::disk($document->disk_type ?: 's3')->get($document->storage_key);
            } catch (\Throwable $e) {
                Log::warning('[Mitigating circumstances] Attachment could not be read back.', [
                    'request' => $row->id, 'key' => $document->storage_key, 'error' => $e->getMessage(),
                ]);

                return null;
            }

            return $contents === null ? null : [
                'name' => $document->display_file_name ?: $document->current_file_name,
                'contents' => $contents,
            ];
        })->filter()->values()->all();
    }
}
