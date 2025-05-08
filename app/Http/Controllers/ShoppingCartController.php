<?php

namespace App\Http\Controllers;

use App\Models\StudentShoppingCart;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Stripe\Stripe;
use Stripe\Charge;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;


class ShoppingCartController extends Controller
{
    public function index()
    {
        $studentUserId = auth('student')->user()->id;
        $student = Student::where('student_user_id', $studentUserId)->first();
        $shoppingCart = StudentShoppingCart::with('letterSet')->where('student_id', $student->id)->get();
        if ($shoppingCart->isEmpty()) {
            return response()->json(['message' => 'No items in cart'], 404);
        }
        // set session to expire in 30 days
        $expire_at = now()->addDays(30);
        foreach ($shoppingCart as $item) {
            if ($item->expire_at < now()) {
                $item->delete();
            }
        }
        session(['shopping_cart' => $shoppingCart]);
        return response()->json(['cart' => $shoppingCart]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'letter_set_id' => 'required|exists:letter_sets,id',
            'status' => 'nullable|in:Pending,Completed',
            'sub_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'total_amount' => 'nullable|numeric',
            'product_type' => 'nullable|in:Free,Paid',
        ]);
        // Logic to add item to cart
        $expire_at = now()->addDays(30); // Set expiration date to 30 days from now
        
        $shoppingCart = StudentShoppingCart::where('student_id', $request->student_id)
        ->where('letter_set_id', $request->letter_set_id)->get();

        if ($shoppingCart->isNotEmpty()) {
            // Check if the item already exists in the cart
            foreach ($shoppingCart as $item) {
                if ($item->letter_set_id == $request->letter_set_id) {
                    $itemInProductCart= StudentShoppingCart::find($item->id);
                    if ($itemInProductCart->expire_at < now()) {
                        $itemInProductCart->delete();
                    } else {
                        
                        //return response()->json(['message' => 'Item already exists in cart'], 409);

                        // Update the quantity and other details
                        $itemInProductCart->quantity += 1;
                        if(isset($request->product_type) && $request->product_type == 'Paid'){
                            
                            $itemInProductCart->sub_amount += $request->sub_amount;
                            $itemInProductCart->tax_amount += $request->tax_amount;
                            $itemInProductCart->total_amount += $request->total_amount;

                        } else {
                            
                            $itemInProductCart->sub_amount += 0;
                            $itemInProductCart->tax_amount += 0;
                            $itemInProductCart->total_amount += 0;

                            $itemInProductCart->number_of_free += 1;
                            
                
                        }
                        if($itemInProductCart->number_of_free != $itemInProductCart->quantity) {
                            $itemInProductCart->product_type = 'Paid';
                        }
                        $itemInProductCart->save();
                    }

                    
                   
                }
            }
            $shoppingCart= StudentShoppingCart::with('letterSet')->where('student_id', $request->student_id)->get();
            session(['shopping_cart' => $shoppingCart]);
            return response()->json(['message' => 'Item Updated in cart']);
            
        } else {
            // Check if the item already exists in the cart
            $product_type = 'Paid';
            $sub_amount = 0;
            $tax_amount = 0;
            $total_amount = 0;
            $number_of_free=0;
            if(isset($request->product_type)){
                $product_type = $request->product_type;
                if($product_type == 'Free'){
                    $number_of_free=1;
                    $sub_amount = 0;
                    $tax_amount = 0;
                    $total_amount = 0;
                } else {
                    $number_of_free=0;
                    $sub_amount = $request->sub_amount;
                    $tax_amount = $request->tax_amount;
                    $total_amount = $request->total_amount;
                }
            }

            

            StudentShoppingCart::create([
                'student_id' => $request->student_id,
                'letter_set_id' => $request->letter_set_id,
                'status' => $request->status,
                'term_declaration_id' => $request->term_declaration_id,
                'product_type' => $product_type,
                'number_of_free' => $number_of_free,
                'quantity' => 1,
                'expire_at' => $expire_at,
                'sub_amount' => $sub_amount,
                'tax_amount' => $tax_amount,
                'total_amount' => $total_amount,

            ]);
            $shoppingCart = StudentShoppingCart::with('letterSet')->where('student_id', $request->student_id)->get();
            session(['shopping_cart' => $shoppingCart]);
            return response()->json(['message' => 'Item added to cart']);
        }
        
        
    }

    public function delete($id)
    {
        $cartItem = StudentShoppingCart::find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        $studentId = $cartItem->student_id;
        $cartItem->delete();

        $shoppingCart = StudentShoppingCart::with('letterSet')->where('student_id', $studentId)->where('expire_at', '>', now())->get();
        if($shoppingCart->isEmpty()){
            
            session()->forget('shopping_cart');
            return response()->json(['message' => 'Item removed from cart']);
        }
        session(['shopping_cart' => $shoppingCart]);
        // Logic to remove item from cart
        return response()->json(['message' => 'Item removed from cart', 'cart' => $shoppingCart]);
    }

    public function update(Request $request, $id)
    {
        
        // Logic to update item in cart
        $cartItem = StudentShoppingCart::find($id);
        if (!$cartItem) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $cartItem->save();
    

        // Logic to update cart item quantity
        return response()->json(['message' => 'Cart updated']);
    }

    public function checkout(Request $request)
    {
        $studentUserId = auth('student')->user()->id;
        $student = Student::with('contact')->where('student_user_id', $studentUserId)->first();
        $shoppingCart = StudentShoppingCart::where('student_id', $student->id)->get();
        if ($shoppingCart->isEmpty()) {
            return redirect()->back()->with('message', 'No items in cart');
        }
        
        foreach ($shoppingCart as $item) {
            if ($item->expire_at < now()) {
                $item->delete();
            }
        }
        $shoppingCart = StudentShoppingCart::with('letterSet')->where('student_id', $student->id)->get();
        session(['shopping_cart' => $shoppingCart]);
        // Logic to proceed to checkout
        return view('pages.students.frontend.checkout.index', ['shoppingCart' => $shoppingCart,'student' => $student]);
    }


    public function capturePaypal(Request $request)
    {
        $env = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'));
        $client = new PayPalHttpClient($env);

        $captureRequest = new OrdersCaptureRequest($request->orderID);
        $captureRequest->prefer('return=representation');
        $response = $client->execute($captureRequest);

        return response()->json(['message' => 'Payment captured successfully', 'details' => $response->result]);
    }

}

