@extends('../layout/' . $layout)

@section('subhead')
    <title>Document Request Checkout Page</title>
@endsection

@section('subcontent')
<div class="max-w-5xl mx-auto text-right mt-10">
   <a href="{{ route('students.document-request-form.products') }}" class=" btn btn-primary text-white shadow-md "><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Products</a>
</div>
    <div class="max-w-5xl mx-auto bg-white rounded-lg shadow-md p-8 mt-10">
        
      <h2 class="text-3xl font-bold mb-6 ">Checkout</h2>
     <form id="checkoutForm" method="POST" action="#" enctype="multipart/form-data" class="grid grid-cols-12 gap-4">
        @csrf
        <!-- Customer Info -->
        <div class="col-span-12 sm:col-span-7 space-y-6">
          <!-- Personal Details -->
          <div>
            <h3 class="text-xl font-semibold mb-4">Student Info</h3>
            <div class="grid grid-cols-12 gap-2">
              
                <div class="col-span-3">
                    <span class="font-semibold mx-2">Full Name:</span>
                </div>
                <div class="col-span-9 text-left">
                    <span class="ml-2 font-semibold">{{ $student->full_name }}</span>
                </div>
                <div class="col-span-3 ">
                    <span class="font-semibold  mx-2">Registration:</span>
                </div>
        
                <div class="col-span-9 text-left">
                    <span class="ml-2 font-semibold ">{{ $student->registration_no }}</span>
                </div>
                <div class="col-span-3">
                    <span class="font-semibold  mx-2">Phone Number:</span>
                </div>
                <div class="col-span-9 text-left">
                    <span class="ml-2 font-semibold">{{ $student->contact->mobile }}</span>
                </div>
            </div>
          </div>
  
          <!-- Shipping Address -->
          <div>
            <h3 class="text-xl font-semibold mb-4">Shipping Address</h3>
            <div class="col-span-12 font-medium mx-2">
                @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                    @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                        <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span><br/>
                    @endif
                    @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                        <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span><br/>
                    @endif
                    @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                        <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                    @endif
                    @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                        <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                    @endif
                    @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                        <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                    @endif
                    @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                        <span class="font-medium">{{ $student->contact->termaddress->country }}</span><br/>
                    @endif
                @else 
                    <span class="font-medium text-warning">Not Set Yet!</span><br/>
                @endif
            </div>
          </div>
          @php
                $total = 0;
       
                foreach($shoppingCart as $item):
                  $total += ($item->total_amount + $item->tax_amount);
                endforeach;
            @endphp
          <!-- Payment Method -->
          @if($total > 0)
          <div>
            <h3 class="text-xl font-semibold mb-4 payment_method">Payment Method <span class="text-danger">*</span></h3>
            <div class="space-y-3">
              <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio"  name="payment_method" value="Card" class="form-check-input" />
                <span>Credit/Debit Card</span>
              </label>
              <div id="card-element" class="mb-4"></div>
              {{-- <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="payment_method" value="PayPal" class="form-check-input " />
                <span>PayPal</span>
              </label> --}}
              <div class="acc__input-error error-payment_method text-danger mt-2"></div>
            </div>
          </div>
          @else
            <label class="flex items-center gap-2 cursor-pointer hidden">
                <input type="radio"   name="payment_method" value="N/A" checked class="form-check-input" />
                <span>Free</span>
              </label>
          @endif

          
        </div>
  
        <!-- Order Summary -->
        <div class="col-span-12 bg-gray-50 p-6 rounded-lg shadow-sm sm:col-span-5 ">
          <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
          <ul class="space-y-2 mb-4">
            @php
                $subtotal = 0;
                $tax = 0;
                $total = 0;
                $totalPaidItemQty = 0;
            @endphp
            
            @foreach($shoppingCart as $item)
            <input type="hidden" name="shopping_cart_ids[]" value="{{ $item->id }}">
            <input type="hidden" name="product_type[]" value="{{ $item->product_type }}">
            <input type="hidden" name="letter_set_id[]" value="{{ $item->letterSet->id }}">
            <input type="hidden" name="sub_amount[]" value="{{ $item->sub_amount }}">
            <input type="hidden" name="tax_amount[]" value="{{ $item->tax_amount }}">
            <input type="hidden" name="total_amount[]" value="{{ $item->total_amount }}">
            <input type="hidden" name="quantity[]" value="{{ $item->quantity }}">
            <input type="hidden" name="status" value="Pending">
            <li class="flex justify-between">
              <span class="flex">{{ $item->letterSet->letter_title }} (Qty: {{ $item->quantity }})</span>
              <span class="flex"><i data-lucide="pound-sterling" class="w-4 h-4 mr-1"></i>{{ number_format($item->total_amount, 2) }}</span>
            </li>
            @php
                $subtotal += $item->total_amount;
                $tax += $item->tax_amount;
                $total += $item->total_amount + $item->tax_amount;
                $totalPaidItemQty += ($item->quantity - $item->number_of_free);
            @endphp
            @endforeach
          </ul>
          <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between">
              <span class="flex">Subtotal</span>
              <span class="flex"><i data-lucide="pound-sterling" class="w-3 h-3 mt-1"></i>{{ number_format($subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="flex">Tax</span>
              <span class="flex"><i data-lucide="pound-sterling" class="w-3 h-3 mt-1"></i>{{ number_format($tax, 2) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg">
              <span class="flex">Total</span>
              <span class="flex"><i data-lucide="pound-sterling" class="w-5 h-5 mt-1"></i>{{ number_format($total, 2) }}</span>
            </div>
          </div>
          <div class="text-center mt-10">
            <input type="hidden" id="student_id" name="student_id" value="{{ $student->id }}">
            <input type="hidden" id="amount" name="amount" value="{{ $total *100 }}">
            <input type="hidden" id="currency" name="currency" value="GBP">
            <input type="hidden" id="quantity_without_free" name="quantity_without_free" value="{{ $totalPaidItemQty }}">
            <input type="hidden" id="invoice_number" name="invoice_number" value="INV-250508000001">

            <button id="payButton" type="button" class="hidden payCard w-48 h-10 text-lg transition duration-200 border shadow-sm  items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 ">
              Pay with Card
              <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden"></i>
            </button>  
            <button id="paypalButton" type ="button" class="hidden">
              Pay with PayPal
            </button>
            <button id="saveBtn" type="submit" class="saveBtn w-48 h-10 text-lg transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 ">
              Place Order
              <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden"></i>
            </button>
          </div>
        </div>
      </form>
    </div>
        <!-- BEGIN: Success Modal Content -->
        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 successModalTitle"></div>
                            <div class="text-slate-500 mt-2 successModalDesc"></div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Success Modal Content -->
    
            <!-- BEGIN: Success Modal Content -->
        
        <!-- BEGIN: Success Modal Content -->
        <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 text-danger errorModalTitle"></div>
                            <div class="text-slate-500 mt-2 errorModalDesc"></div>
                        </div>
                        
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-danger w-24">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Success Modal Content -->

        
@endsection

@section('script')
 @vite('resources/js/checkout.js')
 @vite('resources/js/stripe-checkout.js')


@endsection