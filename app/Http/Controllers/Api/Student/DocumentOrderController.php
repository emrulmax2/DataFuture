<?php

namespace App\Http\Controllers\Api\Student;

use App\Models\StudentOrder;
use App\Models\StudentOrderPayment;
use App\Services\DocumentRequestOrders;
use App\Services\DocumentRequestPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Document requests in the student app: placing an order, My orders, the
 * invoice, and paying by card.
 */
class DocumentOrderController extends StudentApiController
{
    public function __construct(
        private DocumentRequestOrders $orders,
        private DocumentRequestPayments $payments,
    ) {
    }

    /** My orders, newest first. */
    public function index(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $validator = Validator::make($request->query(), [
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:'.implode(',', DocumentRequestOrders::STATUSES),
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        $query = StudentOrder::with($this->orders->withItems())->where('student_id', $student->id)->orderBy('id', 'DESC');
        if(!empty($request->query('search'))):
            $query->where('invoice_number', 'LIKE', '%'.addcslashes($request->query('search'), '%_\\').'%');
        endif;
        if(!empty($request->query('status'))):
            $query->where('status', array_search($request->query('status'), DocumentRequestOrders::STATUSES));
        endif;

        [$rows, $meta] = $this->page($query, $request);

        return $this->ok($rows->map(function($order){
            return $this->orders->summary($order);
        })->all(), 200, $meta);
    }

    /** The Place order / Pay button: the basket becomes an order. */
    public function store(Request $request)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $idempotencyKey = trim((string) $request->header('Idempotency-Key', ''));
        if(strlen($idempotencyKey) > 64):
            return $this->invalid(['Idempotency-Key' => ['The Idempotency-Key header may not be longer than 64 characters.']]);
        endif;

        $placed = $this->orders->place($student, $request->user()->id, $idempotencyKey);
        if(!$placed['order']):
            return $this->fail($placed['error'], 422);
        endif;

        return $this->ok($this->orders->detail($placed['order'], $student), 201);
    }

    /** Order details. Also how the app sees a payment land, so an attempt still open is checked first. */
    public function show(Request $request, $orderId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $order = $this->order($student, $orderId);
        if(!$order):
            return $this->fail('Order not found.', 404);
        endif;

        if(!$this->orders->isPaid($order)):
            $payment = $this->payments->latest($order);
            if($payment && $payment->status == StudentOrderPayment::STATUS_PENDING):
                $this->payments->refresh($payment);
                $order = $order->fresh();
            endif;
        endif;

        return $this->ok($this->orders->detail($order, $student));
    }

    /** The invoice as a PDF. */
    public function invoice(Request $request, $orderId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $order = $this->order($student, $orderId);
        if(!$order):
            return $this->fail('Order not found.', 404);
        endif;

        /* Every blade view builds the staff menu for whoever the default guard
           holds (MenuComposer), and a student user has no priv() for it to
           read. The student is known by now, so the default guard goes back
           to the staff one, which is empty here, before the invoice view
           renders. Named outright: the token guard put itself in the config. */
        Auth::shouldUse('web');

        return $this->orders->invoice($order, $student)->download($order->invoice_number.'.pdf');
    }

    /** Pay, Try again and Pay now: a new payment page for an unpaid order. */
    public function startPayment(Request $request, $orderId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $order = $this->order($student, $orderId);
        if(!$order):
            return $this->fail('Order not found.', 404);
        endif;

        $validator = Validator::make($request->all(), [
            'return_url' => ['nullable', 'string', 'max:2000', function($attribute, $value, $fail){
                /* Usually the app's own deep link, so any scheme will do except
                   the ones a browser would run instead of open. */
                $isLink = preg_match('/^([a-z][a-z0-9+.\-]*):\S+$/i', $value, $matches);
                if(!$isLink || in_array(strtolower($matches[1]), ['javascript', 'data', 'vbscript', 'file', 'blob'])):
                    $fail('The return URL is not a link the app can be sent back to.');
                endif;
            }],
        ]);
        if($validator->fails()):
            return $this->invalid($validator);
        endif;

        if((float) $order->total_amount <= 0):
            return $this->fail('This order does not need a payment.', 409);
        endif;
        if($this->orders->isPaid($order)):
            return $this->fail('This order has already been paid.', 409);
        endif;
        if($order->status == 'Rejected'):
            return $this->fail('This order can no longer be paid.', 409);
        endif;

        $started = $this->payments->start($order, $request->user()->id, $request->input('return_url'));
        if($started['error'] == 'paid'):
            return $this->fail('This order has already been paid.', 409);
        endif;
        if(!$started['payment']):
            return $this->fail('We could not start the payment. Please try again.', 503);
        endif;

        return $this->ok($this->payments->present($started['payment']), 201);
    }

    /** How the last attempt ended - asked when the payment page closes. */
    public function payment(Request $request, $orderId)
    {
        $student = $this->student($request);
        if(!$student):
            return $this->noStudent();
        endif;

        $order = $this->order($student, $orderId);
        if(!$order):
            return $this->fail('Order not found.', 404);
        endif;

        $payment = $this->payments->latest($order);
        if(!$payment):
            return $this->fail('No payment has been started for this order.', 404);
        endif;

        return $this->ok($this->payments->present($this->payments->refresh($payment)));
    }

    /** The id comes off the request, so the order has to be this student's. */
    private function order($student, $orderId)
    {
        return StudentOrder::where('student_id', $student->id)->where('id', (int) $orderId)->first();
    }
}
