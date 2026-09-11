<?php

namespace App\Console\Commands;

use App\Models\LibraryBookIssue;
use App\Services\OperationsLibraryClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Put back copies Operations is holding for a booking that does not exist here.
 *
 * Holding a copy and writing the borrow record are two systems and two calls.
 * If the second fails, the copy is off the shelf for a booking nobody can see,
 * cancel or expire — invisible to the student, the desk and the expiry job, and
 * the book simply reads as unavailable forever.
 *
 * The borrow paths now compensate on failure, so this is the backstop for
 * anything that slipped through, including a request that died mid-flight.
 */
class ReleaseOrphanedLibraryHolds extends Command
{
    protected $signature = 'library:release-orphans {--dry-run : List them without releasing}';

    protected $description = 'Release library copies held in Operations with no matching open issue';

    public function handle(OperationsLibraryClient $catalogue): int
    {
        $held = $catalogue->heldCopies();

        if ($held === null) {
            $this->error('Could not read held copies from Operations.');

            return self::FAILURE;
        }

        /* Only open issues justify a hold. A returned or cancelled one means the
           copy should already be back. */
        $accountedFor = LibraryBookIssue::open()
            ->whereNotNull('ops_copy_id')
            ->pluck('ops_copy_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $orphans = collect($held)->reject(fn ($copy) => $accountedFor->has((int) ($copy['id'] ?? 0)));

        if ($orphans->isEmpty()) {
            $this->info(count($held).' copy/copies held, all accounted for.');

            return self::SUCCESS;
        }

        $this->warn($orphans->count().' held copy/copies have no open issue.');

        $this->table(
            ['Copy', 'Barcode', 'Status', 'Title', 'Held since'],
            $orphans->take(25)->map(fn ($c) => [
                $c['id'] ?? '?', $c['barcode'] ?? '?', $c['status'] ?? '?',
                mb_substr((string) ($c['title'] ?? ''), 0, 40), $c['held_since'] ?? '?',
            ])->all()
        );

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $released = 0;

        foreach ($orphans as $copy) {
            if ($catalogue->releaseCopy($copy['id'] ?? null, ['reason' => 'orphaned_hold'])) {
                $released++;
                Log::info('[Library] Orphaned hold released.', ['copy' => $copy['id'] ?? null]);
            }
        }

        $this->info('Released '.$released.' copy/copies back to the shelf.');

        return self::SUCCESS;
    }
}
