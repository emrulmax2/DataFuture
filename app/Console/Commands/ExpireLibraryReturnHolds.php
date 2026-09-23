<?php

namespace App\Console\Commands;

use App\Services\LibraryFinePayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Withdraw return submissions whose payment never arrived.
 *
 * A book handed back late is not returned until the charge is paid, and the
 * desk's submission is only good for the day it was made. Past midnight the
 * figure it carries is out of date — a loan still out has accrued another day
 * — so the submission is dropped and the desk takes the return again against
 * what is actually owed.
 *
 * Nothing is undone, because nothing was done: the loan was never closed. This
 * only clears the held submission and stands its payment link down.
 */
class ExpireLibraryReturnHolds extends Command
{
    protected $signature = 'library:expire-return-holds';

    protected $description = 'Drop unpaid library return submissions once their payment link has expired';

    public function handle(LibraryFinePayment $fines): int
    {
        $swept = $fines->sweepExpired();

        if ($swept > 0):
            Log::info('[Library] Unpaid return submissions withdrawn.', ['count' => $swept]);
        endif;

        $this->info($swept === 1
            ? '1 unpaid return submission withdrawn.'
            : $swept.' unpaid return submissions withdrawn.');

        return self::SUCCESS;
    }
}
