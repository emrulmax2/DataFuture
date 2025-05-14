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
            'payment_method' => 'required|in:Card,PayPal,N/A',
        ]);
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

        // Logic to create a new order
        $student_order = StudentOrder::create([

            'student_id' => $request->student_id,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'total_amount' => $total_amount,
            'sub_amount' => $sub_amount,
            'tax_amount' => $tax_amount,
        ]);
        if(isset($student_order->id)) {
            $originalString = "00000";

            // Replace the string while keeping leading zeros
            $invNo = str_pad($student_order->id, strlen($originalString), "0", STR_PAD_LEFT);

            $student_order = StudentOrder::find($student_order->id);
            $student_order->invoice_number = 'INV-'.date("ymd").$invNo;
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
    

        return response()->json(['message' => 'Order Created, Please wait for the payment', 'order' => $student_order]);
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
