<?php

namespace App\Services;

use App\Models\StudentOrder;
use App\Models\StudentOrderPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Paying for a document request order from the student app.
 *
 * The app opens Stripe's hosted payment page in its in-app browser, so card
 * details never pass through the app or this server. Each page opened is one
 * row in student_order_payments.
 *
 * An order is only ever marked paid from what Stripe says about the session -
 * read when Stripe sends the student back, and again whenever the app asks how
 * the attempt ended - never from anything the app reports. The web checkout's
 * own return page cannot be used for this: it sits behind the web login, which
 * the in-app browser does not have.
 */
class DocumentRequestPayments
{
    public function __construct(
        private StripeCheckoutClient $stripe,
        private DocumentRequestOrders $orders,
    ) {
    }

    public function latest(StudentOrder $order): ?StudentOrderPayment
    {
        return StudentOrderPayment::where('student_order_id', $order->id)->orderBy('id', 'DESC')->first();
    }

    /**
     * Get the payment page to send the student to, opening one if need be.
     *
     * There is only ever one open page for an order. An earlier one left open
     * could still be paid after a new one, charging the student twice, so it is
     * handed back if it will still do, and otherwise settled or closed first.
     * The order row is held while that is decided, so two taps on Pay cannot
     * each open a page.
     *
     * @return array{payment:?StudentOrderPayment,error:?string} error is 'paid'
     *         when the order turns out to be paid already, or 'unavailable'
     *         when Stripe could not be reached
     */
    public function start(StudentOrder $order, $studentUserId, $returnUrl = null): array
    {
        $returnUrl = (!empty($returnUrl) ? $returnUrl : null);

        return DB::transaction(function () use ($order, $studentUserId, $returnUrl) {
            $order = StudentOrder::where('id', $order->id)->lockForUpdate()->first();
            if(!$order):
                return ['payment' => null, 'error' => 'unavailable'];
            endif;

            $open = StudentOrderPayment::where('student_order_id', $order->id)->where('status', StudentOrderPayment::STATUS_PENDING)->orderBy('id', 'DESC')->get();
            foreach($open as $attempt):
                $this->refresh($attempt);
                if($this->reusable($attempt, $returnUrl)):
                    return ['payment' => $attempt, 'error' => null];
                endif;
                if(!$this->shut($attempt)):
                    return ['payment' => null, 'error' => 'unavailable'];
                endif;
            endforeach;

            $order->refresh();
            if($this->orders->isPaid($order)):
                return ['payment' => null, 'error' => 'paid'];
            endif;

            $amount = DocumentRequestCatalogue::pence($order->total_amount);

            /* The row comes first: its token is what the return links carry. */
            $payment = StudentOrderPayment::create([
                'student_order_id' => $order->id,
                'student_id' => $order->student_id,
                'provider' => 'stripe',
                'token' => Str::random(48),
                'status' => StudentOrderPayment::STATUS_PENDING,
                'amount' => $amount,
                'currency' => DocumentRequestCatalogue::CURRENCY,
                'return_url' => $returnUrl,
                'created_by' => $studentUserId,
            ]);

            $session = $this->stripe->createSession([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower(DocumentRequestCatalogue::CURRENCY),
                        /* Named by the invoice, as the web checkout names it. */
                        'product_data' => ['name' => $order->invoice_number],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'client_reference_id' => (string) $order->id,
                'metadata' => [
                    'student_order_id' => (string) $order->id,
                    'student_order_payment_id' => (string) $payment->id,
                    'source' => 'student-app',
                ],
                'success_url' => route('api.user.document-requests.payment.return', ['token' => $payment->token]),
                'cancel_url' => route('api.user.document-requests.payment.cancel', ['token' => $payment->token]),
            ]);

            if(!$session || empty($session['url'])):
                $payment->update(['status' => StudentOrderPayment::STATUS_FAILED, 'failure_message' => 'The payment could not be started.']);

                return ['payment' => null, 'error' => 'unavailable'];
            endif;

            $payment->update([
                'provider_session_id' => $session['id'],
                'checkout_url' => $session['url'],
                'expires_at' => (!empty($session['expires_at']) ? date('Y-m-d H:i:s', $session['expires_at']) : null),
            ]);

            return ['payment' => $payment, 'error' => null];
        });
    }

    /** An open page can be handed out again if it leads back to the same place and has time left. */
    private function reusable(StudentOrderPayment $payment, $returnUrl): bool
    {
        return $payment->status == StudentOrderPayment::STATUS_PENDING && !empty($payment->checkout_url)
            && $payment->return_url === $returnUrl
            && !empty($payment->expires_at) && $payment->expires_at->gt(now()->addMinutes(10));
    }

    /**
     * Ask Stripe how a pending attempt ended, and act on the answer.
     *
     * Safe to call as often as anyone likes: a paid session marks the order
     * paid once, however many callers notice it.
     */
    public function refresh(StudentOrderPayment $payment): StudentOrderPayment
    {
        if($payment->status != StudentOrderPayment::STATUS_PENDING || empty($payment->provider_session_id)):
            return $payment;
        endif;

        $session = $this->stripe->retrieveSession($payment->provider_session_id);
        if(!$session):
            return $payment;
        endif;

        if($session['payment_status'] == 'paid'):
            $transactionId = (!empty($session['payment_intent']) ? $session['payment_intent'] : $session['id']);
            $payment->update([
                'status' => StudentOrderPayment::STATUS_SUCCEEDED,
                'provider_payment_id' => $transactionId,
                'paid_at' => now(),
            ]);

            $order = StudentOrder::withTrashed()->find($payment->student_order_id);
            if($order && !$this->orders->markPaid($order, $transactionId, $payment->created_by)):
                /* Not recorded, and not because this same payment got there
                   first: the order was already paid another way, or has been
                   cancelled. The student has been charged for nothing and
                   somebody needs to refund it. */
                if($order->fresh()->transaction_id != $transactionId):
                    Log::warning('[Stripe] Payment taken for a document request order that was already paid or cancelled.', ['order' => $order->id, 'payment' => $payment->id, 'transaction' => $transactionId]);
                endif;
            endif;
        elseif($session['status'] == 'expired'):
            $payment->update([
                'status' => StudentOrderPayment::STATUS_FAILED,
                'failure_message' => 'The payment page expired before the payment was completed.',
            ]);
        endif;

        return $payment;
    }

    /** The student backed out of the payment page. */
    public function cancel(StudentOrderPayment $payment): StudentOrderPayment
    {
        $this->close($payment);

        return $payment;
    }

    /**
     * Make sure nothing can still be paid through an attempt.
     *
     * @return bool false while Stripe may still be holding the page open
     */
    private function close(StudentOrderPayment $payment): bool
    {
        $this->refresh($payment);

        return $this->shut($payment);
    }

    /** close(), for an attempt Stripe has just been asked about. */
    private function shut(StudentOrderPayment $payment): bool
    {
        if($payment->status != StudentOrderPayment::STATUS_PENDING):
            return true;
        endif;

        if(!empty($payment->provider_session_id) && !$this->stripe->expireSession($payment->provider_session_id)):
            return false;
        endif;

        $payment->update(['status' => StudentOrderPayment::STATUS_CANCELLED]);

        return true;
    }

    /** Where to send the in-app browser afterwards, or null to show the plain page. */
    public function returnLink(StudentOrderPayment $payment): ?string
    {
        if(empty($payment->return_url)):
            return null;
        endif;

        return $payment->return_url.(str_contains($payment->return_url, '?') ? '&' : '?').http_build_query([
            'order_id' => $payment->student_order_id,
            'payment_id' => $payment->id,
            'status' => $payment->status,
        ]);
    }

    public function present(StudentOrderPayment $payment): array
    {
        return [
            'id' => (string) $payment->id,
            'status' => $payment->status,
            'amount' => (int) $payment->amount,
            'currency' => $payment->currency,
            'checkout_url' => ($payment->status == StudentOrderPayment::STATUS_PENDING ? $payment->checkout_url : null),
            'expires_at' => (!empty($payment->expires_at) ? $payment->expires_at->toIso8601String() : null),
            'paid_at' => (!empty($payment->paid_at) ? $payment->paid_at->toIso8601String() : null),
            'failure_message' => (!empty($payment->failure_message) ? $payment->failure_message : null),
        ];
    }
}
