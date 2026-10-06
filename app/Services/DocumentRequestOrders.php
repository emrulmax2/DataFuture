<?php

namespace App\Services;

use App\Models\LetterSet;
use App\Models\Student;
use App\Models\StudentDocumentRequestForm;
use App\Models\StudentOrder;
use App\Models\StudentOrderItem;
use App\Models\StudentTask;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

/**
 * Document request orders, for the student app.
 *
 * These are the same student_orders the web portal places, and an order placed
 * here goes through the same steps: it is written as Pending, and once it is
 * paid for (or straight away if it costs nothing) each item on it becomes a
 * document request with a task for staff to work.
 *
 * The web does that last part in StudentOrderController::freeOrderComplete()
 * and StripeCheckoutController::success(), both of which read the signed-in
 * web session. The app has no web session, so the steps are repeated here
 * against the student passed in.
 */
class DocumentRequestOrders
{
    /* student_orders.status as it is stored => as the app reads it. */
    const STATUSES = [
        'Pending' => 'pending',
        'In Progress' => 'in_progress',
        'Approved' => 'approved',
        'Rejected' => 'rejected',
        'Completed' => 'completed',
    ];

    /* The staff task raised for each request - the ids the web checkout uses. */
    const TASK_DOCUMENT_REQUEST = 20;
    const TASK_PRINTER_TOP_UP = 26;

    public function __construct(
        private DocumentRequestCatalogue $catalogue,
        private DocumentRequestBasket $basket,
    ) {
    }

    /**
     * Turn the student's basket into an order and empty the basket.
     *
     * Nothing the app sent is used for the amounts: every line is priced again
     * from the catalogue, so a basket row cannot be made to cost less than the
     * product does.
     *
     * @return array{order:?StudentOrder,error:?string}
     */
    public function place(Student $student, $studentUserId, $idempotencyKey = null): array
    {
        return DB::transaction(function () use ($student, $studentUserId, $idempotencyKey) {
            /* Locked, so two taps on Place order queue up here: the second
               finds the basket already emptied by the first. */
            $rows = $this->basket->rows($student->id, true);

            if(!empty($idempotencyKey)):
                $placed = StudentOrder::withTrashed()->where('student_id', $student->id)->where('idempotency_key', $idempotencyKey)->first();
                if($placed):
                    return ($placed->trashed() ? ['order' => null, 'error' => 'This order has already been placed and cancelled.'] : ['order' => $placed, 'error' => null]);
                endif;
            endif;

            if($rows->isEmpty()):
                return ['order' => null, 'error' => 'Your basket is empty.'];
            endif;

            $onOffer = $this->catalogue->letterSets()->pluck('id')->toArray();
            $items = [];
            $total = 0;
            foreach($rows as $row):
                if(!in_array($row->letter_set_id, $onOffer)):
                    $name = (isset($row->letterSet->letter_title) && !empty($row->letterSet->letter_title) ? $row->letterSet->letter_title : 'An item in your basket');

                    return ['order' => null, 'error' => $name.' is no longer available. Remove it from your basket to continue.'];
                endif;

                $free = max(0, (int) $row->number_of_free);
                $paid = max(0, (int) $row->quantity - $free);
                $amount = ($paid * $this->catalogue->paidOption($row->letter_set_id)['price']) / 100;
                $total += $amount;

                $items[] = [
                    'letter_set_id' => $row->letter_set_id,
                    'term_declaration_id' => $row->term_declaration_id,
                    'student_id' => $student->id,
                    'quantity' => $free + $paid,
                    'number_of_free' => $free,
                    'sub_amount' => $amount,
                    'tax_amount' => 0,
                    'total_amount' => $amount,
                    'product_type' => ($paid > 0 ? 'Paid' : 'Free'),
                ];
            endforeach;

            $order = StudentOrder::create([
                'student_id' => $student->id,
                'status' => 'Pending',
                'payment_method' => ($total > 0 ? 'Card' : 'N/A'),
                'sub_amount' => $total,
                'tax_amount' => 0,
                'total_amount' => $total,
                'idempotency_key' => (!empty($idempotencyKey) ? $idempotencyKey : null),
            ]);
            $order->invoice_number = 'INV-'.date('ymd').str_pad($order->id, 5, '0', STR_PAD_LEFT);
            $order->save();

            foreach($items as $item):
                StudentOrderItem::create(['student_order_id' => $order->id] + $item);
            endforeach;

            $this->basket->clear($student->id);

            /* Nothing to pay, so there is nothing to wait for. */
            if($total <= 0):
                $order->payment_status = 'Completed';
                $order->payment_method = 'N/A';
                $order->status = 'In Progress';
                $order->transaction_date = now();
                $order->transaction_id = 'Free Order';
                $order->save();

                $this->raiseRequests($order, $studentUserId);
            endif;

            return ['order' => $order, 'error' => null];
        });
    }

    public function isPaid(StudentOrder $order): bool
    {
        return $order->payment_status == 'Completed';
    }

    /**
     * Record a card payment against an order and raise its requests.
     *
     * The update only matches an order that is not yet paid, so when the return
     * link, a status check and the web checkout all report the same payment,
     * only the first of them raises the requests.
     *
     * @return bool false when the order had been paid already
     */
    public function markPaid(StudentOrder $order, $transactionId, $studentUserId): bool
    {
        return DB::transaction(function () use ($order, $transactionId, $studentUserId) {
            $updated = StudentOrder::where('id', $order->id)->where(function($q){
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'Completed');
            })->update([
                'payment_status' => 'Completed',
                'payment_method' => 'Card',
                'status' => 'In Progress',
                'transaction_date' => now(),
                'transaction_id' => $transactionId,
            ]);

            if(!$updated):
                return false;
            endif;

            $this->raiseRequests($order, $studentUserId);

            return true;
        });
    }

    /** One document request, and one staff task, for every copy on the order. */
    private function raiseRequests(StudentOrder $order, $studentUserId): void
    {
        $student = Student::find($order->student_id);
        $items = StudentOrderItem::where('student_order_id', $order->id)->orderBy('id', 'ASC')->get();

        foreach($items as $item):
            $free = max(0, (int) $item->number_of_free);
            $paid = max(0, (int) $item->quantity - $free);
            $letterSet = LetterSet::withTrashed()->find($item->letter_set_id);
            $termId = (!empty($item->term_declaration_id) ? $item->term_declaration_id : (isset($student->current_term->id) ? $student->current_term->id : null));

            $serviceTypes = array_merge(
                array_fill(0, $free, $this->catalogue->freeOption($item->letter_set_id)['service_type']),
                array_fill(0, $paid, $this->catalogue->paidOption($item->letter_set_id)['service_type'])
            );

            foreach($serviceTypes as $serviceType):
                $form = StudentDocumentRequestForm::create([
                    'student_id' => $item->student_id,
                    'term_declaration_id' => $termId,
                    'letter_set_id' => $item->letter_set_id,
                    'student_order_id' => $order->id,
                    'name' => (isset($letterSet->letter_title) ? $letterSet->letter_title : ''),
                    'service_type' => $serviceType,
                    'status' => 'Pending',
                    'email_status' => 'Pending',
                    'student_consent' => 1,
                    'created_by' => $studentUserId,
                ]);

                StudentTask::create([
                    'student_id' => $order->student_id,
                    'task_list_id' => ($item->letter_set_id == DocumentRequestCatalogue::PRINTER_TOP_UP ? self::TASK_PRINTER_TOP_UP : self::TASK_DOCUMENT_REQUEST),
                    'student_document_request_form_id' => $form->id,
                    'status' => 'Pending',
                    'created_by' => 1,
                ]);
            endforeach;
        endforeach;
    }

    /* ------------------------------------------------------------------ */
    /* What the app is shown                                               */
    /* ------------------------------------------------------------------ */

    /** Eager-load for summary() and detail(), keeping items whose letter has since been withdrawn. */
    public function withItems(): array
    {
        return ['studentOrderItems.letterSet' => function($q){
            $q->withTrashed();
        }];
    }

    public function status(StudentOrder $order): string
    {
        return (isset(self::STATUSES[$order->status]) ? self::STATUSES[$order->status] : 'pending');
    }

    /** not_required for an order that costs nothing; otherwise whether it has been paid. */
    public function paymentStatus(StudentOrder $order): string
    {
        if((float) $order->total_amount <= 0):
            return 'not_required';
        endif;

        return ($this->isPaid($order) ? 'paid' : 'unpaid');
    }

    /** A row of My orders. */
    public function summary(StudentOrder $order): array
    {
        $names = [];
        foreach($order->studentOrderItems as $item):
            if(isset($item->letterSet->letter_title) && !in_array($item->letterSet->letter_title, $names)):
                $names[] = $item->letterSet->letter_title;
            endif;
        endforeach;

        return [
            'id' => $order->id,
            'invoice_no' => (string) $order->invoice_number,
            'placed_at' => (!empty($order->created_at) ? $order->created_at->toIso8601String() : null),
            'status' => $this->status($order),
            'payment_status' => $this->paymentStatus($order),
            'product_names' => $names,
            'total' => DocumentRequestCatalogue::pence($order->total_amount),
            'currency' => DocumentRequestCatalogue::CURRENCY,
        ];
    }

    /** The Order details screen. */
    public function detail(StudentOrder $order, Student $student): array
    {
        $order->loadMissing($this->withItems());

        $items = [];
        foreach($order->studentOrderItems as $item):
            foreach($this->catalogue->lines($item, false) as $line):
                $items[] = $line;
            endforeach;
        endforeach;

        $summary = $this->summary($order);
        unset($summary['product_names']);

        return $summary + [
            'items' => $items,
            'subtotal' => DocumentRequestCatalogue::pence($order->sub_amount),
            'tax' => DocumentRequestCatalogue::pence($order->tax_amount),
            'student' => $this->studentInfo($student),
            'shipping_address' => $this->shippingAddress($student),
            'status_history' => $this->statusHistory($order),
        ];
    }

    /**
     * The steps an order has been through, oldest first.
     *
     * No history is kept, so this is read off the order's own dates: when it
     * was placed, when it was paid for and went to staff, and when it reached
     * the status it has now.
     */
    private function statusHistory(StudentOrder $order): array
    {
        $history = [];
        if(!empty($order->created_at)):
            $history[] = ['status' => 'pending', 'at' => $order->created_at->toIso8601String()];
        endif;

        if($order->status != 'Pending'):
            if(!empty($order->transaction_date)):
                $history[] = ['status' => 'in_progress', 'at' => date('c', strtotime($order->transaction_date))];
            endif;
            if($order->status != 'In Progress' && !empty($order->updated_at)):
                $history[] = ['status' => $this->status($order), 'at' => $order->updated_at->toIso8601String()];
            endif;
        endif;

        return $history;
    }

    public function studentInfo(Student $student): array
    {
        return [
            'full_name' => trim($student->full_name),
            'registration_no' => (string) $student->registration_no,
            'phone' => (isset($student->contact->mobile) && !empty($student->contact->mobile) ? $student->contact->mobile : null),
        ];
    }

    /** The term-time address, as the web checkout shows it. Null when the student has not set one. */
    public function shippingAddress(Student $student): ?array
    {
        $address = (isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0 ? $student->contact->termaddress : null);
        if(!$address):
            return null;
        endif;

        return [
            'line1' => (string) $address->address_line_1,
            'line2' => (!empty($address->address_line_2) ? $address->address_line_2 : null),
            'city' => (!empty($address->city) ? $address->city : null),
            'region' => (!empty($address->state) ? $address->state : null),
            'postcode' => (string) $address->post_code,
            'country' => (string) $address->country,
        ];
    }

    /** The invoice (a receipt once paid) - the PDF the web's My orders page downloads. */
    public function invoice(StudentOrder $order, Student $student)
    {
        $order->loadMissing($this->withItems());

        $address = '';
        $shipping = $this->shippingAddress($student);
        if($shipping):
            $address .= e($shipping['line1']).'<br/>';
            $address .= (!empty($shipping['line2']) ? e($shipping['line2']).'<br/>' : '');
            $address .= (!empty($shipping['city']) ? e($shipping['city']).', ' : '');
            $address .= (!empty($shipping['region']) ? e($shipping['region']).', <br/>' : '');
            $address .= (!empty($shipping['postcode']) ? e($shipping['postcode']).', ' : '');
            $address .= (!empty($shipping['country']) ? '<br/>'.e($shipping['country']) : '');
        endif;

        return Pdf::loadView('pages.students.frontend.document_requests.pdf.moneyreceipt', [
            'student' => $student,
            'address' => $address,
            'studentOrders' => $order,
        ]);
    }
}
