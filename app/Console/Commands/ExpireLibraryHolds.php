<?php

namespace App\Console\Commands;

use App\Models\LibraryBookIssue;
use App\Services\OperationsLibraryClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Release copies reserved by students who never collected them.
 *
 * A reservation takes a copy off the shelf the moment it is made. Without an
 * expiry, one student who changes their mind keeps that copy out of circulation
 * indefinitely — and the next borrower is told the book is unavailable when it
 * is sitting on the hold shelf.
 *
 * The window comes from Site Settings (Loan Rule → Collection window), so the
 * library can lengthen it over a holiday without a deploy.
 */
class ExpireLibraryHolds extends Command
{
    protected $signature = 'library:expire-holds {--limit=200 : Most reservations to release in one run}';

    protected $description = 'Cancel uncollected library reservations and put the copies back on the shelf';

    public function handle(OperationsLibraryClient $catalogue): int
    {
        $expired = LibraryBookIssue::where('status', LibraryBookIssue::STATUS_REQUESTED)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::now())
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No reservations have expired.');

            return self::SUCCESS;
        }

        $released = 0;

        foreach ($expired as $issue) {
            /* The local record closes either way. A copy that could not be
               released is logged for the desk rather than left as a live
               reservation the student can still turn up for. */
            $ok = $catalogue->releaseCopy($issue->ops_copy_id, ['reason' => 'not_collected']);

            $issue->update([
                'status' => LibraryBookIssue::STATUS_NOT_COLLECTED,
                'cancel_reason' => 'Not collected within the collection window',
                'returned_at' => Carbon::now(),
            ]);

            $issue->logs()->create([
                'action' => 'not_collected',
                'from_status' => LibraryBookIssue::STATUS_REQUESTED,
                'to_status' => LibraryBookIssue::STATUS_NOT_COLLECTED,
                'performed_by_type' => 'system',
                'performed_by_name' => 'Collection window expired',
                'note' => 'Held until '.optional($issue->expires_at)->format('j M Y'),
            ]);

            $ok ? $released++ : Log::warning('[Library] Expired hold could not be released in Operations.', [
                'reference' => $issue->reference,
                'copy' => $issue->ops_copy_id,
            ]);
        }

        $this->info(sprintf(
            '%d reservation(s) expired, %d copy/copies returned to the shelf.',
            $expired->count(),
            $released
        ));

        return self::SUCCESS;
    }
}
