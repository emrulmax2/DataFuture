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
        $counts = $reconciler->reconcileAll(
            (int) $this->option('minutes'),
            (int) $this->option('limit')
        );

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
