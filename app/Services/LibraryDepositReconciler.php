<?php

namespace App\Services;

use App\Models\LibraryDeposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Settles deposits that PayPal took but this app never heard about.
 *
 * The return URL only fires if the student comes back from PayPal. Close the
 * tab, lose signal, or approve on a phone and give up — the money is taken and
 * the row is still `pending`, so borrowing stays locked and the student is
 * asked to pay a second time.
 *
 * The webhook normally covers that. Until PAYPAL_WEBHOOK_ID is configured it
 * cannot, so pending deposits are reconciled by asking PayPal directly. This
 * is not a duplicate of the webhook — it is the fallback that makes the webhook
 * an optimisation rather than a requirement.
 */
class LibraryDepositReconciler
{
    public function __construct(
        private PayPalClient $paypal,
        private LibraryFinePayment $fines,
    ) {
    }

    /**
     * Bring one pending deposit up to date with what PayPal actually holds.
     *
     * @return string  paid | failed | pending | skipped
     */
    public function reconcile(LibraryDeposit $deposit): string
    {
        if ($deposit->status !== 'pending' || !$deposit->provider_order_id):
            return 'skipped';
        endif;

        $order = $this->paypal->getOrder($deposit->provider_order_id);

        if (!$order):
            // A call that failed says nothing about the payment; leave it be.
            return 'pending';
        endif;

        switch ((string) ($order['status'] ?? '')):
            case 'COMPLETED':
                /* Captured already — most likely the webhook or another tab got
                   there first. Copy the receipt onto our row. */
                return $this->settle($deposit, $order) ? 'paid' : 'pending';

            case 'APPROVED':
                /* The payer approved but capture never ran, which is exactly
                   the abandoned-tab case. Take the money now. */
                $capture = $this->paypal->captureOrder($deposit->provider_order_id);

                if ($capture && $capture['status'] === 'COMPLETED'):
                    $this->settleFromCapture($deposit, $capture);

                    return 'paid';
                endif;

                return 'pending';

            case 'VOIDED':
                $deposit->update(['status' => 'failed', 'failure_reason' => 'Order voided at PayPal.']);

                return 'failed';

            default:
                /* CREATED / SAVED — the student never approved. Left pending so
                   a later visit can still complete it; expiry is the caller's
                   decision, not ours. */
                return 'pending';
        endswitch;
    }

    /** Every stale pending deposit, for the scheduled sweep. */
    public function reconcileAll(int $olderThanMinutes = 5, int $limit = 200): array
    {
        $counts = ['paid' => 0, 'failed' => 0, 'pending' => 0, 'skipped' => 0];

        LibraryDeposit::deposits()
            ->where('status', 'pending')
            ->whereNotNull('provider_order_id')
            ->where('created_at', '<=', Carbon::now()->subMinutes($olderThanMinutes))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (LibraryDeposit $deposit) use (&$counts) {
                $counts[$this->reconcile($deposit)]++;
            });

        return $counts;
    }

    /** Anything outstanding for one student — cheap enough to run on page load. */
    public function reconcileStudent($studentId): array
    {
        $counts = ['paid' => 0, 'failed' => 0, 'pending' => 0, 'skipped' => 0];

        LibraryDeposit::where('student_id', $studentId)
            ->deposits()
            ->where('status', 'pending')
            ->whereNotNull('provider_order_id')
            ->orderByDesc('id')
            ->limit(3)
            ->get()
            ->each(function (LibraryDeposit $deposit) use (&$counts) {
                $counts[$this->reconcile($deposit)]++;
            });

        return $counts;
    }

    /**
     * The same sweep for overdue charges paid from a link.
     *
     * Needed more than the deposit sweep, not less: a fine settling is what
     * completes a return, so a payment this app never hears about leaves a
     * book reading as out on loan and a student blocked from borrowing on
     * money they have already paid.
     *
     * Only today's are worth asking about. Past midnight the submission behind
     * the charge is gone, and a capture arriving then is handled — and
     * flagged — by the service rather than quietly closing a stale return.
     */
    public function reconcileFines(int $olderThanMinutes = 5, int $limit = 200): array
    {
        $counts = ['paid' => 0, 'failed' => 0, 'pending' => 0, 'skipped' => 0];

        LibraryDeposit::fines()
            ->where('status', 'pending')
            ->whereNotNull('provider_order_id')
            ->where('created_at', '<=', Carbon::now()->subMinutes($olderThanMinutes))
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->each(function (LibraryDeposit $deposit) use (&$counts) {
                $counts[$this->reconcile($deposit)]++;
            });

        return $counts;
    }

    private function settle(LibraryDeposit $deposit, array $order): bool
    {
        $capture = data_get($order, 'purchase_units.0.payments.captures.0');

        if (!$capture):
            return false;
        endif;

        $this->settleFromCapture($deposit, [
            'capture_id' => (string) ($capture['id'] ?? ''),
            'payer_email' => data_get($order, 'payer.email_address'),
        ]);

        return true;
    }

    /**
     * Conditional update so this and the webhook and the return URL can all
     * arrive at once without double-settling the row.
     */
    private function settleFromCapture(LibraryDeposit $deposit, array $capture): void
    {
        /* A fine goes through the service, because settling the ledger row is
           only half of it: the return waiting on that charge has to complete
           in the same step. */
        if ($deposit->isFine()):
            if ($this->fines->settle($deposit, $capture)):
                Log::info('[Library] Fine reconciled against PayPal.', ['deposit' => $deposit->id]);
            endif;

            return;
        endif;

        $updated = LibraryDeposit::where('id', $deposit->id)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'provider_capture_id' => $capture['capture_id'] ?: $deposit->provider_capture_id,
                'payer_email' => $capture['payer_email'] ?? $deposit->payer_email,
                'paid_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        if ($updated):
            Log::info('[Library] Deposit reconciled against PayPal.', ['deposit' => $deposit->id]);
        endif;
    }
}
