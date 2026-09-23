<?php

namespace App\Console\Commands;

use App\Services\LibraryDepositReconciler;
use Illuminate\Console\Command;

/**
 * Sweep for library deposits PayPal took but this app never recorded.
 *
 * The student portal reconciles its own pending rows on page load, which covers
 * anyone who comes back. This catches the ones who do not — so support is not
 * fielding "I paid and it still says I owe a deposit" by hand.
 */
class ReconcileLibraryDeposits extends Command
{
    protected $signature = 'library:reconcile-deposits
                            {--minutes=5 : Only touch deposits started at least this long ago}
                            {--limit=200 : Most deposits to check in one run}';

    protected $description = 'Settle library deposits against what PayPal actually holds';

    public function handle(LibraryDepositReconciler $reconciler): int
    {
        $minutes = (int) $this->option('minutes');
        $limit = (int) $this->option('limit');

        $counts = $reconciler->reconcileAll($minutes, $limit);

        /* Overdue charges too. A fine settling is what completes a return, so
           one this app never hears about leaves a book reading as out on loan
           and a student blocked on money they have already paid. */
        foreach ($reconciler->reconcileFines($minutes, $limit) as $key => $value):
            $counts[$key] += $value;
        endforeach;

        $this->info(sprintf(
            'Reconciled: %d paid, %d failed, %d still pending, %d skipped.',
            $counts['paid'],
            $counts['failed'],
            $counts['pending'],
            $counts['skipped']
        ));

        return self::SUCCESS;
    }
}
