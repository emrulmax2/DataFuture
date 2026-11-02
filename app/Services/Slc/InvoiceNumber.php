<?php

namespace App\Services\Slc;

use Illuminate\Support\Facades\DB;

/**
 * Money receipt invoice numbers. Refunds are stored as "R-<number>", so a plain MAX(invoice_no) is
 * a text comparison that returns the highest refund ("R-1792513549") instead of the highest number
 * (seen 2 Nov 2026: 1792774372) — new numbers then repeat ones already used. These read the highest
 * number whatever its prefix.
 */
class InvoiceNumber
{
    public static function highest(): int
    {
        return (int) DB::table('slc_money_receipts')->selectRaw("MAX(CAST(REPLACE(invoice_no, 'R-', '') AS UNSIGNED)) as n")->value('n');
    }

    /**
     * Runs $fn with a function that hands out the next free number each time it is called, holding a
     * database lock so two uploads at once can't take the same numbers.
     */
    public static function allocate(callable $fn)
    {
        DB::select("SELECT GET_LOCK('slc_money_receipt_invoice_no', 15) as l");
        try {
            $n = self::highest();

            return $fn(function () use (&$n) {
                return ++$n;
            });
        } finally {
            DB::select("SELECT RELEASE_LOCK('slc_money_receipt_invoice_no') as l");
        }
    }
}
