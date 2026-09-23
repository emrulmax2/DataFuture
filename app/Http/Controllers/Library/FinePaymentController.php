<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryDeposit;
use App\Services\LibraryFinePayment;
use App\Services\PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The page a library payment link opens.
 *
 * Public on purpose. The link is generated at the desk and goes to the
 * student's phone or inbox, and a charge is often paid by a parent or a
 * sponsor who has no portal login at all — putting it behind the student guard
 * would mean the link only worked for the one person least likely to be
 * holding the phone.
 *
 * What makes that safe is that the URL is signed and carries no instruction:
 * it names a charge, nothing else. Every figure is read from our own row, and
 * money is only ever recognised from a capture we make server-side.
 */
class FinePaymentController extends Controller
{
    public function __construct(
        private PayPalClient $paypal,
        private LibraryFinePayment $fines,
    ) {
    }

    /** The charge, and a button. Signed. */
    public function show(Request $request, $deposit)
    {
        $deposit = $this->fine($deposit);

        return $this->page($deposit);
    }

    /**
     * Press Pay: open the PayPal order and send them there.
     *
     * Signed, and a POST rather than a GET, so a link preview or a mail
     * scanner opening the page cannot leave a trail of abandoned orders
     * behind it.
     */
    public function start(Request $request, $deposit)
    {
        $deposit = $this->fine($deposit);

        if ($this->isSettled($deposit) || $this->hasLapsed($deposit)):
            return $this->page($deposit);
        endif;

        if (!$this->fines->isAvailable()):
            Log::error('[Library] PayPal is not configured; a fine link cannot be paid.', ['deposit' => $deposit->id]);

            return $this->page($deposit, 'Card payments are not available right now. Please contact the library.');
        endif;

        $approveUrl = $this->fines->startOrder($deposit);

        if (!$approveUrl):
            return $this->page($deposit, 'We could not start the payment. Please try again in a moment.');
        endif;

        return redirect()->away($approveUrl);
    }

    /**
     * PayPal sends the payer back here with ?token=<order id>.
     *
     * Unsigned, because PayPal appends its own query string and a signature
     * would never survive it. That costs nothing: the token only says which
     * order to look at, and the money is recognised from the capture we make
     * here, never from the query string.
     */
    public function complete(Request $request)
    {
        $orderId = (string) $request->query('token');

        $deposit = $orderId !== ''
            ? LibraryDeposit::fines()->where('provider_order_id', $orderId)->first()
            : null;

        if (!$deposit):
            return view('pages.library.pay.done', [
                'title' => 'Payment - London Churchill College',
                'deposit' => null,
                'issue' => null,
                'error' => 'That payment could not be matched to a charge. Please contact the library.',
            ]);
        endif;

        /* Refreshing the return URL must not try to take the money twice. */
        if ($deposit->status === 'paid'):
            return $this->receipt($deposit);
        endif;

        $capture = $this->paypal->captureOrder($orderId);

        if (!$capture || $capture['status'] !== 'COMPLETED'):
            return $this->page($deposit, 'The payment was not completed. Nothing has been charged.');
        endif;

        $this->fines->settle($deposit, $capture);

        return $this->receipt($deposit->refresh());
    }

    /**
     * Backed out at PayPal.
     *
     * The row stays pending and the link stays live — they walked away from
     * one attempt, not from the charge, and they will come back to the same
     * link to try again.
     */
    public function cancel(Request $request)
    {
        $deposit = LibraryDeposit::fines()
            ->where('provider_order_id', (string) $request->query('token'))
            ->first();

        if (!$deposit):
            return redirect()->route('login');
        endif;

        return $this->page($deposit, 'Payment cancelled. Nothing has been charged — you can try again below.');
    }

    /* ------------------------------------------------------------------ */

    /** The row this link names, or a 404. Fines only; a bond is refundable. */
    private function fine($id): LibraryDeposit
    {
        return LibraryDeposit::fines()->with('student', 'issue')->findOrFail($id);
    }

    /** Settled, whether here or at the desk in cash. */
    private function isSettled(LibraryDeposit $deposit): bool
    {
        return $deposit->status === 'paid'
            || $deposit->status === 'refunded'
            || (bool) optional($deposit->issue)->fine_paid_at;
    }

    /**
     * Past midnight, so there is nothing to pay here any more.
     *
     * The signature would have stopped them at the door anyway; this is for
     * the person already on the page when the day turned, and for saying
     * plainly what happened rather than showing them a signature error.
     */
    private function hasLapsed(LibraryDeposit $deposit): bool
    {
        return $deposit->hasExpired() || $deposit->status === 'failed';
    }

    private function page(LibraryDeposit $deposit, ?string $error = null)
    {
        $deposit->loadMissing('student', 'issue');

        return view('pages.library.pay.show', [
            'title' => 'Library charge - London Churchill College',
            'deposit' => $deposit,
            'issue' => $deposit->issue,
            'settled' => $this->isSettled($deposit),
            'lapsed' => $this->hasLapsed($deposit),
            /* Built rather than taken from the current URL: this page is also
               rendered on the way back from a cancelled or failed payment,
               where the address bar holds PayPal's return route and posting to
               it would go nowhere. */
            'payUrl' => $this->fines->link($deposit),
            'error' => $error,
        ]);
    }

    private function receipt(LibraryDeposit $deposit)
    {
        $deposit->loadMissing('student', 'issue');

        return view('pages.library.pay.done', [
            'title' => 'Payment received - London Churchill College',
            'deposit' => $deposit,
            'issue' => $deposit->issue,
            'error' => null,
        ]);
    }
}
