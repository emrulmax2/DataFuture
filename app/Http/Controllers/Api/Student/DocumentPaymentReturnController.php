<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentOrderPayment;
use App\Services\DocumentRequestPayments;

/**
 * Where Stripe sends the in-app browser when the student finishes on, or backs
 * out of, the payment page.
 *
 * Deliberately outside the token guard: it is a browser arriving from Stripe,
 * with no way to carry the app's bearer token. What makes that safe is that
 * the link carries no instruction. It names one attempt by a random token, and
 * the outcome is read from Stripe, never from the query string - so opening it
 * by hand can only make the server look up what really happened a little early.
 */
class DocumentPaymentReturnController extends Controller
{
    public function __construct(private DocumentRequestPayments $payments)
    {
    }

    public function success($token)
    {
        $payment = StudentOrderPayment::where('token', $token)->first();
        if($payment):
            $this->payments->refresh($payment);
        endif;

        return $this->leave($payment);
    }

    public function cancel($token)
    {
        $payment = StudentOrderPayment::where('token', $token)->first();
        if($payment):
            $this->payments->cancel($payment);
        endif;

        return $this->leave($payment);
    }

    /** Back into the app if it said where, otherwise a page telling the student to go back themselves. */
    private function leave($payment)
    {
        $link = ($payment ? $this->payments->returnLink($payment) : null);
        if($link):
            return redirect()->away($link);
        endif;

        return response()->view('pages.students.api.payment-return', [
            'status' => ($payment ? $payment->status : 'unknown'),
        ], ($payment ? 200 : 404));
    }
}
