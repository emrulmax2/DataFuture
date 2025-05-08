<?php

namespace App\Http\Controllers;

use App\Models\LetterSet;
use App\Models\Student;
use App\Models\StudentDocumentRequestForm;
use App\Models\StudentOrder;
use App\Models\StudentTask;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeCheckoutController extends Controller
{
    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $request->currency,
                    'product_data' => [
                        'name' => $request->invoice_number,
                    ],
                    'unit_amount' => 1000, // $10.00
                ],
                'quantity' => $request->quantity,
            ]],
            'mode' => 'payment',
            'success_url' => route('students.checkout.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('students.checkout.stripe.cancel'),
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function success(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        // Retrieve the session ID from the request
        $sessionId = $request->query('session_id'); 

        // Optionally, you can retrieve the session details using the session ID
         $session = CheckoutSession::retrieve($sessionId);
         $paymentIntentId = $session->payment_intent;
         $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
         $transactionId = $paymentIntent->id; // same as $paymentIntentId
         $lineItems = CheckoutSession::allLineItems($sessionId, ['limit' => 100]);
         foreach ($lineItems->data as $item) {
            
            $invoiceNumber = $item->description;  // this holds the product name if using price_data->product_data
            
            $studentOrder = StudentOrder::with('studentOrderItems')->where('invoice_number', $invoiceNumber)->first();

            $studentOrder->payment_status = 'Completed';
            $studentOrder->payment_method = 'Card';
            $studentOrder->status = 'In Progress';
            $studentOrder->transaction_date = now();
            $studentOrder->transaction_id = $transactionId;
            $studentOrder->save();
            foreach ($studentOrder->studentOrderItems as $cartItem) {
                $quantity = $cartItem->quantity;
                $free = $cartItem->number_of_free;
                if($free > 0) {
                    $quantity = $studentOrder->quantity - $studentOrder->number_of_free;

                    for($iJount=0; $iJount < $studentOrder->number_of_free; $iJount++) {

                        $studentDocumentRequestForm = new StudentDocumentRequestForm();
                        $studentDocumentRequestForm->student_id = $cartItem->student_id;
                        $studentDocumentRequestForm->term_declaration_id = $cartItem->term_declaration_id;
                        $studentDocumentRequestForm->letter_set_id = $cartItem->letter_set_id;
                        $studentDocumentRequestForm->name = !isset($cartItem->name) ? LetterSet::where('id',$cartItem->letter_set_id)->get()->first()->letter_title : $cartItem->name;
                        $studentDocumentRequestForm->description = $cartItem->description;
                        $studentDocumentRequestForm->service_type = '3 Working Days (Free)';
                        $studentDocumentRequestForm->status = 'Pending';
                        $studentDocumentRequestForm->email_status = 'Pending';
                        $studentDocumentRequestForm->student_consent = 1;
                        $studentDocumentRequestForm->created_by = auth('student')->user()->id;
                        $studentDocumentRequestForm->student_order_id = $studentOrder->id;
                        $studentDocumentRequestForm->save();

                        $data['student_id'] = $studentOrder->student_id;
                        $data['task_list_id'] = 20; // Document Request Task
                        $data['student_document_request_form_id'] = $studentDocumentRequestForm->id;
                        $data['status'] = "Pending";
                        $data['created_by'] = 1;

                        StudentTask::create($data);
                    }
        
                }
                for($iCount=0; $iCount <= $quantity; $iCount++) {
                    $studentDocumentRequestForm = new StudentDocumentRequestForm();
                    $studentDocumentRequestForm->student_id = $cartItem->student_id;
                    $studentDocumentRequestForm->term_declaration_id = $cartItem->term_declaration_id;
                    $studentDocumentRequestForm->letter_set_id = $cartItem->letter_set_id;
                    $studentDocumentRequestForm->name = !isset($cartItem->name) ? LetterSet::where('id',$cartItem->letter_set_id)->get()->first()->letter_title : $cartItem->name;
                    $studentDocumentRequestForm->description = $cartItem->description;
                    $studentDocumentRequestForm->service_type = 'Same Day (cost £10.00)';
                    $studentDocumentRequestForm->status = 'Pending';
                    $studentDocumentRequestForm->email_status = 'Pending';
                    $studentDocumentRequestForm->student_consent = 1;
                    $studentDocumentRequestForm->created_by = auth('student')->user()->id;
                    
                    $studentDocumentRequestForm->student_order_id = $studentOrder->id;
                    $studentDocumentRequestForm->save();

                    $data['student_id'] = $studentOrder->student_id;
                    $data['task_list_id'] = 20; // Document Request Task
                    $data['student_document_request_form_id'] = $studentDocumentRequestForm->id;
                    $data['status'] = "Pending";
                    $data['created_by'] = 1;

                    StudentTask::create($data);
                }
            }
            
            
        }
        // Process the payment success here
        // For example, you can update your order status in the database

        // Redirect to a success page or return a response
        return redirect()->route('students.document-request-form.index')->with('paymentSuccessMessage', 'Payment successful! Your order is being processed.');
    }

    public function cancel()
    {
        return redirect()->route('students.document-request-form.index')->with('paymentErrorMessage', 'Payment canceled. Please try again.');
    }
}
