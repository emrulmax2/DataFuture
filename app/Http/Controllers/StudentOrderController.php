<?php

namespace App\Http\Controllers;

use App\Models\CourseCreationVenue;
use App\Models\LetterSet;
use App\Models\Option;
use App\Models\SlcAgreement;
use App\Models\SlcMoneyReceipt;
use App\Models\Status;
use App\Models\Student;
use App\Models\StudentDocumentRequestForm;
use App\Models\StudentOrder;
use App\Models\StudentOrderItem;
use App\Models\StudentProposedCourse;
use App\Models\StudentShoppingCart;
use App\Models\StudentTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use PDF;

class StudentOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $studentUserId = auth('student')->user()->id;
        $student = Student::where('student_user_id', $studentUserId)->first();
        $orders = StudentOrder::with('studentOrderItems')->where('student_id', $student->id)->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No orders found'], 404);
        }

        return response()->json(['orders' => $orders]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {   
        $sub_amount =0;
        $tax_amount =0;
        $total_amount =0;
        if(isset($request->total_amount) && !empty($request->total_amount)) {
            
            foreach($request->shopping_cart_ids as $cartId) {
                $cartItem = StudentShoppingCart::find($cartId);
                $sub_amount += $cartItem->sub_amount;
                $tax_amount += $cartItem->tax_amount;
                $total_amount += $cartItem->total_amount;
            }
        }
        //check shopping_cart_ids array
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status' => 'nullable|in:Pending,Completed,In Progress,Approved,Rejected',
            'shopping_cart_ids.*' => 'exists:student_shopping_carts,id',
            'letter_set_id.*' => 'exists:letter_sets,id',
            'quantity.*' => 'required|integer|min:1',
            'sub_amount.*' => 'required|numeric',
            'tax_amount.*' => 'required|numeric',
            'total_amount.*' => 'required|numeric',
            'product_type.*' => 'nullable|in:Free,Paid',
            'payment_method' => [
                function ($attribute, $value, $fail) use ($total_amount) {
                    if ($total_amount > 0 && empty($value)) {
                        $fail('The payment method field is required if there is a payment involved.');
                    }
                },'in:Card,PayPal,N/A',
            ],
        ]);
        

        // Logic to create a new order
        $student_order = StudentOrder::create([

            'student_id' => $request->student_id,
            'status' => $request->status,
            'payment_method' => isset($request->payment_method) ? $request->payment_method : 'N/A',
            'total_amount' => $total_amount,
            'sub_amount' => $sub_amount,
            'tax_amount' => $tax_amount,
        ]);
        if(isset($student_order->id)) {
            $originalString = "00000";

            // Replace the string while keeping leading zeros
            $invNo = str_pad($student_order->id, strlen($originalString), "0", STR_PAD_LEFT);
            $invoice_number =  'INV-'.date("ymd").$invNo;
            $student_order = StudentOrder::find($student_order->id);
            $student_order->invoice_number = $invoice_number;
            $student_order->save();
        foreach($request->shopping_cart_ids as $cartId) {

            $cartItem = StudentShoppingCart::find($cartId);

            StudentOrderItem::create([

                'student_order_id' => $student_order->id,
                'letter_set_id' => $cartItem->letter_set_id,
                'term_declaration_id' => $cartItem->term_declaration_id,
                'student_id' => $cartItem->student_id,
                'quantity' => $cartItem->quantity,
                'number_of_free' => $cartItem->number_of_free,
                'sub_amount' => $cartItem->sub_amount,
                'tax_amount' => $cartItem->tax_amount,
                'total_amount' => $cartItem->total_amount,
                'product_type' => $cartItem->product_type,

            ]);


            
            

            if ($cartItem) {
                $cartItem->delete();
            } else {
                return response()->json(['message' => 'Cart item not found'], 404);
            }
        }
        } else {
            return response()->json(['message' => 'Order creation failed'], 400);
        }
    
        if($total_amount > 0) {
            // Process the payment here
            // For example, you can redirect to a payment gateway or perform a payment transaction
            return response()->json(['message' => 'Order Created, Please wait for the payment', 'order' => $student_order]);
        }
        return $this->freeOrderComplete($invoice_number);
    }


    private function freeOrderComplete($invoice_number)
    {
            
            $invoiceNumber = $invoice_number;  // this holds the product name if using price_data->product_data
            
            $studentOrder = StudentOrder::with('studentOrderItems')->where('invoice_number', $invoiceNumber)->first();
            $student = Student::where('id', $studentOrder->student_id)->first();
            $studentOrder->payment_status = 'Completed';
            $studentOrder->payment_method = 'N/A';
            $studentOrder->status = 'In Progress';
            $studentOrder->transaction_date = now();
            $studentOrder->transaction_id = 'Free Order';
            $studentOrder->save();
            
            foreach ($studentOrder->studentOrderItems as $cartItem) {
                $quantity = $cartItem->quantity;
                $free = $cartItem->number_of_free;
                
                if($free > 0) {
                    $quantity = $cartItem->quantity - $cartItem->number_of_free;

                    for($iJount=0; $iJount < $cartItem->number_of_free; $iJount++) {
                        
                        $studentDocumentRequestForm = new StudentDocumentRequestForm();
                        $studentDocumentRequestForm->student_id = $cartItem->student_id;
                        $studentDocumentRequestForm->term_declaration_id = isset($cartItem->term_declaration_id) ? $cartItem->term_declaration_id : $student->current_term->id;
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
                        $data = [];
                        $data['student_id'] = $studentOrder->student_id;
                        $data['task_list_id'] = 20; // Document Request Task
                        $data['student_document_request_form_id'] = $studentDocumentRequestForm->id;
                        $data['status'] = "Pending";
                        $data['created_by'] = 1;

                        StudentTask::create($data);
                    }
        
                }
                if($quantity > 0) {
                    for($iCount=0; $iCount < $quantity; $iCount++) {
                        $studentDocumentRequestFormPaid = new StudentDocumentRequestForm();
                        $studentDocumentRequestFormPaid->student_id = $cartItem->student_id;
                        $studentDocumentRequestFormPaid->term_declaration_id = isset($cartItem->term_declaration_id) ? $cartItem->term_declaration_id : $student->current_term->id;
                        $studentDocumentRequestFormPaid->letter_set_id = $cartItem->letter_set_id;
                        $studentDocumentRequestFormPaid->name = !isset($cartItem->name) ? LetterSet::where('id',$cartItem->letter_set_id)->get()->first()->letter_title : $cartItem->name;
                        $studentDocumentRequestFormPaid->description = $cartItem->description;
                        $studentDocumentRequestFormPaid->service_type = 'Same Day (cost £10.00)';
                        $studentDocumentRequestFormPaid->status = 'Pending';
                        $studentDocumentRequestFormPaid->email_status = 'Pending';
                        $studentDocumentRequestFormPaid->student_consent = 1;
                        $studentDocumentRequestFormPaid->created_by = auth('student')->user()->id;
                        
                        $studentDocumentRequestFormPaid->student_order_id = $studentOrder->id;
                        $studentDocumentRequestFormPaid->save();
                        $data = [];
                        $data['student_id'] = $studentOrder->student_id;
                        $data['task_list_id'] = 20; // Document Request Task
                        $data['student_document_request_form_id'] = $studentDocumentRequestFormPaid->id;
                        $data['status'] = "Pending";
                        $data['created_by'] = 1;

                        StudentTask::create($data);
                    }
                }
                
            }
            
            
        
        // Process the payment success here
        // For example, you can update your order status in the database

        // Redirect to a success page or return a response
        return redirect()->route('students.document-request-form.index')->with('paymentSuccessMessage', 'Order successful! Your order is being processed.');
    }
    /**
     * Display the specified resource.
     */
    public function show(StudentOrder $studentOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentOrder $studentOrder)
    {
        $studentOrder->load('studentOrderItems');
        // Logic to show the edit form for the order
        return response()->json(['order' => $studentOrder]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudentOrder $studentOrder)
    {
        
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'status' => 'nullable|in:Pending,Completed,In Progress,Approved,Rejected',
            'sub_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
        ]);

        // Logic to update the order
        $studentOrder->update([
            'student_id' => $request->student_id,
            'status' => $request->status,
            'sub_amount' => $request->sub_amount,
            'tax_amount' => $request->tax_amount,
            'total_amount' => $request->total_amount,
        ]);

        return response()->json(['message' => 'Order updated successfully']);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentOrder $studentOrder)
    {
        
        // Logic to delete the order
        $studentOrder->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function printPdf(StudentOrder $studentOrder) {
        $student_id = $studentOrder->student_id;
       
        $studentOrder = StudentOrder::with('studentOrderItems','letterSet')->where('id',$studentOrder->id)->first();
        set_time_limit(300);
		$opt = Option::where('category', 'SITE_SETTINGS')->where('name','site_logo')->pluck('value', 'name')->toArray(); 
		$logoUrl = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']) ? public_path('storage/'.$opt['site_logo']) : asset('build/assets/images/logo.svg'));

        $student = Student::find($student_id);
        //Not using currently this part
        $courseRelationId = (isset($student->crel->id) && $student->crel->id > 0 ? $student->crel->id : 0);
        $courseCreationID = (isset($student->crel->course_creation_id) && $student->crel->course_creation_id > 0 ? $student->crel->course_creation_id : 0);

        $currentCourse = StudentProposedCourse::with('venue')->where('student_id',$student->id)
                        ->where('course_creation_id', $courseCreationID)
                        ->where('student_course_relation_id', $courseRelationId)
                        ->get()
                        ->first();
        $venue_id = (isset($currentCourse->venue_id) && $currentCourse->venue_id > 0 ? $currentCourse->venue_id : 0);

        
        //End of not using part

        $address = '';
        if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0):
            if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1)):
                $address .= $student->contact->termaddress->address_line_1.'<br/>';
            endif;
            if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2)):
                $address .= $student->contact->termaddress->address_line_2.'<br/>';
            endif;
            if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city)):
                $address .= $student->contact->termaddress->city.', ';
            endif;
            if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state)):
                $address .= $student->contact->termaddress->state.', <br/>';
            endif;
            if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code)):
                $address .= $student->contact->termaddress->post_code.', ';
            endif;
            if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country)):
                $address .= '<br/>'.$student->contact->termaddress->country;
            endif;
        endif;

        // return view('pages.students.frontend.document_requests.pdf.moneyreceipt', [
        //     'logoUrl' => $logoUrl,
        //     'student' => $student,
        //     'address' => $address,
        //     'studentOrders' => $studentOrder::with('studentOrderItems','letterSet'),
        //     'statuses' => Status::where('type', 'Student')->orderBy('id', 'ASC')->get(),
        // ]);

        
        $studentOrders = $studentOrder;
        //dd($studentOrders);
         $pdf = PDF::loadView('pages.students.frontend.document_requests.pdf.moneyreceipt',compact('logoUrl','student','address','studentOrders'));
         return $pdf->download('student_document_request_payment.pdf');
    }

}
