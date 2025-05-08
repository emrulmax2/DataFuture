<?php

namespace App\Http\Controllers;

use App\Models\LetterSet;
use App\Models\Student;
use App\Models\StudentDocumentRequestForm;
use App\Models\StudentOrder;
use App\Models\StudentOrderItem;
use App\Models\StudentShoppingCart;
use App\Models\StudentTask;
use Illuminate\Http\Request;

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
            'payment_method' => 'required|in:Card,PayPal',
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

}
