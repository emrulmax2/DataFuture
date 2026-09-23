<?php

namespace App\Services;

use App\Mail\LibraryFineLink;
use App\Models\LibraryBookIssue;
use App\Models\LibraryDeposit;
use App\Traits\SendSmsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Returning a book that is late, when the charge has to be paid first.
 *
 * The desk's submission does not close the loan. It is held: everything they
 * typed is kept on the loan row, the loan itself carries on running late, and
 * a payment link goes to the student. The money completing is what turns the
 * submission into a return — copy back on the shelf, charge frozen and
 * settled, borrowing block lifted, all in one step.
 *
 * Held only until midnight. A loan still out accrues by the day, so a
 * submission carrying yesterday's figure would settle the wrong amount: the
 * unpaid ones are swept overnight, the charge grows by another day, and the
 * desk submits again against what is actually owed.
 *
 * The link is a signed route to our own URL rather than PayPal's approve link,
 * which is good for one fixed amount and a few hours. Ours is good for exactly
 * as long as the submission behind it, and the PayPal order is opened at the
 * moment the student presses Pay.
 */
class LibraryFinePayment
{
    use SendSmsTrait;

    /**
     * Prefix on the PayPal `custom_id`, echoed back on the capture and on the
     * webhook. Deliberately distinct from the deposit's `LIBDEP-`: the two
     * settle differently, and a fine mistaken for a bond would let a student
     * borrow on money they paid for being late.
     */
    public const REFERENCE_PREFIX = 'LIBFIN-';

    public const CHANNELS = ['email', 'sms'];

    public function __construct(
        private PayPalClient $paypal,
        private OperationsLibraryClient $catalogue,
    ) {
    }

    public function isAvailable(): bool
    {
        return $this->paypal->isConfigured();
    }

    /** Midnight tonight: how long a submission and its link are good for. */
    public function goodUntil(): Carbon
    {
        return Carbon::now()->endOfDay();
    }

    /* ------------------------------------------------------------------ */
    /* Submitting                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Hold the desk's return against the charge, and open a link to pay it.
     *
     * The loan is not closed and not touched beyond the pending fields — its
     * status, due date and fine clock all carry on, because as far as the
     * ledger is concerned this book is still out until the money lands.
     *
     * Re-submitting the same loan on the same day lands on the same ledger
     * row. Two rows for one charge read as a student who paid twice.
     */
    public function submitReturn(LibraryBookIssue $issue, float $fine, ?string $note): LibraryDeposit
    {
        $fine = round($fine, 2);
        $expires = $this->goodUntil();

        return DB::transaction(function () use ($issue, $fine, $note, $expires) {
            $issue->update([
                'pending_return_at' => Carbon::now(),
                'pending_return_by' => auth()->id(),
                'pending_return_note' => $note,
                'pending_return_fine' => $fine,
                'pending_return_expires_at' => $expires,
                'updated_by' => auth()->id(),
            ]);

            $issue->log(
                'return_pending',
                $issue->status,
                'Return held — £'.number_format($fine, 2).' to pay by midnight'
            );

            return $this->openLedgerRow($issue, $fine, $expires);
        });
    }

    /** The pending fine row this loan's link is paid against. */
    private function openLedgerRow(LibraryBookIssue $issue, float $fine, Carbon $expires): LibraryDeposit
    {
        $deposit = LibraryDeposit::fines()
            ->where('library_book_issue_id', $issue->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        $attributes = [
            'amount' => $fine,
            'currency' => 'GBP',
            'provider' => 'paypal',
            'description' => 'Overdue charge — '.$issue->title,
            'expires_at' => $expires,
            'failure_reason' => null,
        ];

        if ($deposit):
            /* A PayPal order is for one fixed amount. If the charge has moved
               since the last link, the order behind it is for the wrong money
               and must not be offered again. */
            if ((float) $deposit->amount !== $fine):
                $attributes['provider_order_id'] = null;
            endif;

            $deposit->update($attributes);

            return $deposit->refresh();
        endif;

        return LibraryDeposit::create($attributes + [
            'student_id' => $issue->student_id,
            'type' => LibraryDeposit::TYPE_FINE,
            'library_book_issue_id' => $issue->id,
            'status' => 'pending',
        ]);
    }

    /**
     * The link itself.
     *
     * Signed, so the id cannot be typed over to reach somebody else's charge,
     * and expiring with the submission it belongs to rather than never — the
     * page behind a stale link would be offering to take money for a figure
     * that has since gone up.
     */
    public function link(LibraryDeposit $deposit): string
    {
        return URL::temporarySignedRoute(
            'library.fine.pay',
            $deposit->expires_at ?: $this->goodUntil(),
            ['deposit' => $deposit->id]
        );
    }

    /* ------------------------------------------------------------------ */
    /* Paying                                                              */
    /* ------------------------------------------------------------------ */

    /**
     * Open a PayPal order for this row and hand back where to send the payer.
     *
     * A fresh order every time. The previous one is left to expire on PayPal's
     * side — reusing it would mean trusting a remote status we would have to
     * fetch and interpret, to save a call we are already making.
     */
    public function startOrder(LibraryDeposit $deposit): ?string
    {
        $order = $this->paypal->createOrder([
            'amount' => (float) $deposit->amount,
            'currency' => $deposit->currency ?: 'GBP',
            'reference' => self::REFERENCE_PREFIX.$deposit->id,
            'item_name' => 'Library charge',
            'description' => (string) ($deposit->description ?: 'Library charge'),
            'return_url' => route('library.fine.complete'),
            'cancel_url' => route('library.fine.cancel'),
        ]);

        if (!$order):
            $deposit->update(['failure_reason' => 'PayPal order could not be created.']);

            return null;
        endif;

        $deposit->update(['provider_order_id' => $order['id'], 'failure_reason' => null]);

        return $order['approve_url'];
    }

    /** The fine row a PayPal `custom_id` refers to, or null if it is not one. */
    public static function fromReference(string $reference): ?LibraryDeposit
    {
        if (!str_starts_with($reference, self::REFERENCE_PREFIX)):
            return null;
        endif;

        return LibraryDeposit::fines()->find((int) substr($reference, strlen(self::REFERENCE_PREFIX)));
    }

    /**
     * Write a completed capture onto the ledger, and complete the return.
     *
     * The conditional update is the lock: the browser coming back and the
     * webhook arriving at the same moment both run this, and only the one that
     * actually changes a row goes on to close the loan. Returns whether this
     * call was the one that settled it.
     */
    public function settle(LibraryDeposit $deposit, array $capture): bool
    {
        $claimed = LibraryDeposit::where('id', $deposit->id)
            ->where('status', '!=', 'paid')
            ->update([
                'status' => 'paid',
                'provider_capture_id' => $capture['capture_id'] ?? $deposit->provider_capture_id,
                'payer_email' => $capture['payer_email'] ?? $deposit->payer_email,
                'paid_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        if (!$claimed):
            return false;
        endif;

        $this->completeReturn($deposit->refresh());

        return true;
    }

    /**
     * Turn a paid charge into a finished return.
     *
     * Everything the desk submitted is applied now: the loan closes on the
     * figure it was submitted against, the charge is stamped paid — which is
     * what lifts the borrowing block — and the copy goes back on the shelf.
     *
     * `returned_at` is the moment the desk took the book, not the moment the
     * money arrived. The student handed it over then, and dating it later
     * would quietly add hours to an overdue record.
     */
    public function completeReturn(LibraryDeposit $deposit): void
    {
        if (!$deposit->isFine() || !$deposit->library_book_issue_id):
            return;
        endif;

        $issue = LibraryBookIssue::find($deposit->library_book_issue_id);

        if (!$issue):
            return;
        endif;

        /* Already settled — a cash payment at the desk got here first, or this
           is a second webhook for the same capture. */
        if ($issue->fine_paid_at):
            return;
        endif;

        if ($issue->pending_return_at):
            $this->applySubmission($issue, (float) $deposit->amount, 'paid online');

            return;
        endif;

        /* Paid, but there is no submission left to complete: the sweep ran
           while they were on the PayPal page, or the desk withdrew it. The
           money is real and is kept on the ledger, but the loan is still out
           and still accruing, so it is left open and flagged rather than
           closed on a figure nobody agreed. */
        Log::warning('[Library] Fine paid with no return submission waiting.', [
            'deposit' => $deposit->id,
            'reference' => $issue->reference,
        ]);

        $issue->log(
            'fine_paid',
            null,
            '£'.number_format($deposit->amount, 2).' paid online — no return was waiting, please check at the desk',
            'system'
        );
    }

    /**
     * Apply a held submission now that the charge has been paid.
     *
     * Shared by both ways money arrives — the link and the till — because a
     * return completed two different ways would drift: one of them would
     * forget the Operations release, or date the return differently.
     *
     * `returned_at` is the moment the desk took the book, not the moment the
     * money arrived. The student handed it over then, and dating it later
     * would quietly add hours to an overdue record.
     */
    public function applySubmission(LibraryBookIssue $issue, float $amount, string $howPaid): void
    {
        /* The submission itself is the lock. It is cleared inside the
           transaction below, so whichever of the two payment routes gets here
           second finds nothing to apply and does nothing.

           Deliberately not guarded on `fine_paid_at`: the desk stamps that as
           it takes the cash, moments before calling this. */
        if (!$issue->pending_return_at || $issue->status !== LibraryBookIssue::STATUS_ISSUED):
            return;
        endif;

        $returnedAt = $issue->pending_return_at;
        $note = $issue->pending_return_note;
        $actor = $howPaid === 'paid online' ? 'system' : 'staff';

        DB::transaction(function () use ($issue, $amount, $returnedAt, $note, $howPaid, $actor) {
            $issue->update([
                'status' => LibraryBookIssue::STATUS_RETURNED,
                'returned_at' => $returnedAt,
                'returned_by' => $issue->pending_return_by,
                'fine_amount' => $amount,
                'fine_paid_at' => Carbon::now(),
                'staff_note' => $note ?: $issue->staff_note,
                'pending_return_at' => null,
                'pending_return_by' => null,
                'pending_return_note' => null,
                'pending_return_fine' => null,
                'pending_return_expires_at' => null,
            ]);

            $issue->log(
                'returned',
                LibraryBookIssue::STATUS_ISSUED,
                '£'.number_format($amount, 2).' '.$howPaid.' — return completed',
                $actor
            );
        });

        $this->catalogue->releaseCopy($issue->ops_copy_id, ['reason' => 'returned']);
    }

    /* ------------------------------------------------------------------ */
    /* Sending, standing down, sweeping                                    */
    /* ------------------------------------------------------------------ */

    /**
     * Send the link to the student, by the channels the desk picked.
     *
     * Each channel reports separately. A student with no mobile on file is a
     * normal state, not an error, and the desk needs to be told which half
     * went out rather than a single "sent" that may be half true.
     *
     * @param  array<int,string>  $channels
     * @return array{sent: array<int,string>, failed: array<int,string>}
     */
    public function sendLink(LibraryDeposit $deposit, array $channels): array
    {
        $channels = array_values(array_intersect(self::CHANNELS, $channels));
        $issue = $deposit->issue;
        $student = $deposit->student;
        $link = $this->link($deposit);

        $sent = [];
        $failed = [];

        foreach ($channels as $channel):
            $ok = $channel === 'email'
                ? $this->emailLink($deposit, $issue, $student, $link)
                : $this->smsLink($deposit, $issue, $student, $link);

            $ok ? $sent[] = $channel : $failed[] = $channel;
        endforeach;

        if ($sent):
            $deposit->update([
                'sent_via' => implode(',', $sent),
                'sent_at' => Carbon::now(),
            ]);
        endif;

        return ['sent' => $sent, 'failed' => $failed];
    }

    private function emailLink(LibraryDeposit $deposit, $issue, $student, string $link): bool
    {
        $to = $this->emailFor($student);

        if (!$to):
            return false;
        endif;

        try {
            Mail::to($to)->send(new LibraryFineLink(
                $deposit,
                $issue,
                trim(optional($student)->first_name.' '.optional($student)->last_name),
                $link
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error('[Library] Could not email a fine payment link.', [
                'deposit' => $deposit->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function smsLink(LibraryDeposit $deposit, $issue, $student, string $link): bool
    {
        $mobile = optional(optional($student)->contact)->mobile;

        if (empty($mobile)):
            return false;
        endif;

        /* Short on purpose: the link is long, and anything past one segment is
           charged twice and arrives split on some handsets. */
        $message = 'London Churchill College Library: £'.number_format($deposit->amount, 2)
            .' is due on '.($issue->reference ?? 'your loan')
            .'. Pay today to complete your return: '.$link;

        try {
            $this->sendSms($mobile, $message);

            return true;
        } catch (\Throwable $e) {
            Log::error('[Library] Could not text a fine payment link.', [
                'deposit' => $deposit->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /** The address to write to: their own before the college one. */
    public function emailFor($student): ?string
    {
        $contact = optional($student)->contact;

        return $contact->personal_email ?? $contact->institutional_email ?? null;
    }

    /** Whether each channel has something to send to. */
    public function reachable($student): array
    {
        return [
            'email' => (bool) $this->emailFor($student),
            'sms' => (bool) optional(optional($student)->contact)->mobile,
        ];
    }

    /**
     * Stand down an unpaid link, because the money came in another way.
     *
     * Called when the desk takes the charge in cash: leaving the row pending
     * would keep a live link out there for money nobody owes any more.
     */
    public function voidPending(LibraryBookIssue $issue, string $reason): void
    {
        LibraryDeposit::fines()
            ->where('library_book_issue_id', $issue->id)
            ->where('status', 'pending')
            ->update(['status' => 'failed', 'failure_reason' => $reason, 'updated_at' => Carbon::now()]);
    }

    /**
     * Drop submissions whose day has passed.
     *
     * Nothing is undone, because nothing was done: the loan was never closed.
     * It simply carries on running late, and tomorrow's charge is a day bigger
     * — which is the whole point of the deadline.
     *
     * @return int how many were swept
     */
    public function sweepExpired(): int
    {
        $stale = LibraryBookIssue::whereNotNull('pending_return_at')
            ->whereNotNull('pending_return_expires_at')
            ->where('pending_return_expires_at', '<', Carbon::now())
            ->get();

        foreach ($stale as $issue):
            $this->voidPending($issue, 'Payment link expired — return not completed.');

            $issue->log(
                'return_expired',
                $issue->status,
                'Unpaid by midnight — return withdrawn, charge continues',
                'system'
            );

            $issue->clearPendingReturn();
        endforeach;

        return $stale->count();
    }
}
