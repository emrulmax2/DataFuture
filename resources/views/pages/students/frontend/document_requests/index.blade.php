@extends('../layout/' . $layout)

@section('subhead')
    <title>My Orders List</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">Order List</h2>
    @if (session('paymentSuccessMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success !</div>
                <div class="text-slate-500 mt-1">{{ session('paymentSuccessMessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif
    @if (session('paymentErrorMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="error-notification-content" class="toastify-content hidden flex">
            <i class="text-danger" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Payment Failed</div>
                <div class="text-slate-500 mt-1">{{ session('paymentErrorMessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="error-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap xl:flex-nowrap items-center mt-2">
            <div class="flex w-full sm:w-auto">
                <div class="w-48 relative text-slate-500">
                    <input type="text" class="form-control w-48 box pr-10" placeholder="Search by invoice...">
                    <i class="w-4 h-4 absolute my-auto inset-y-0 mr-3 right-0" data-lucide="search"></i>
                </div>
                <select class="form-select box ml-2">
                    <option>Status</option>
                    <option>Waiting Payment</option>
                    <option>Confirmed</option>
                    <option>Packing</option>
                    <option>Delivered</option>
                    <option>Completed</option>
                </select>
            </div>
            <div class="hidden xl:block mx-auto text-slate-500">Showing {{ ($studentOrderList->count()>0) ? 1 : 0 }} to {{$studentOrderList->count() }} of {{ $studentOrderList->count() }} entries</div>
            <div class="w-full xl:w-auto flex items-center mt-3 xl:mt-0">
                {{-- <button class="btn btn-primary shadow-md mr-2">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export to Excel
                </button> --}}
                <a href="{{ route('students.document-request-form.products') }}" class=" btn btn-primary text-white shadow-md mr-2"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Products</a>
                
            </div>
        </div>
        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto 2xl:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">
                            S/N
                        </th>
                        <th class="whitespace-nowrap">INVOICE</th>
                        <th class="whitespace-nowrap">PRODUCT</th>
                        <th class="text-center whitespace-nowrap">STATUS</th>
                        <th class="whitespace-nowrap">PAYMENT</th>
                        <th class="text-right whitespace-nowrap">
                            <div class="pr-16">TOTAL TRANSACTION</div>
                        </th>
                        <th class="text-center whitespace-nowrap">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $serial = 0;
                    @endphp
                    @foreach ($studentOrderList as $order)
                        @php
                            $serial++;
                        @endphp
                        <tr class="intro-x">
                            <td class="w-10">
                                <span class="font-medium whitespace-nowrap">{{ $serial }}</span>
                            </td>
                            <td class="w-40 !py-4">
                                <a href="" class="underline decoration-dotted whitespace-nowrap">{{ '#'.$order->invoice_number }}</a>
                            </td>
                            <td class="w-40">
                               
                                @foreach ($order->studentOrderItems as $item)
                                <a href="" class="font-medium whitespace-nowrap">{{ $item->letterSet->letter_title}} [ Qty: {{ $item->quantity }}]</a>
                                @if($item->product_type == 'Paid')   
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">Same Day (£10.00) [{{ $item->quantity - $item->number_of_free }}]</div>
                                @else
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">3 Working Days (Free)</div>
                                @endif
                                @endforeach
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center whitespace-nowrap {{ $order->status == 'Completed' ? 'text-success' : '' }}{{ $order->status == 'In Progress' ? 'text-info' : 'text-pending' }}">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-2"></i> {{ $order->status == 'Completed' ? 'Completed' : $order->status }} {{ $order->payment_status == 'Completed' ? 'on '.$order->formatted_updated_at : 'Payment' }}
                                </div>
                            </td>
                            <td>
                                @if ($order->payment_method == 'Card' && $order->payment_status=="Completed")
                                    <div class="whitespace-nowrap">Debit or Credit Card</div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $order->formatted_transaction_date }}</div>
                                @elseif ($order->payment_method == 'PayPal' && $order->payment_status=="Completed")
                                <div class="whitespace-nowrap">PayPal</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $order->formatted_transaction_date }}</div>
                                @else
                                    <div class="whitespace-nowrap">N/A</div>
                                    <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $order->formatted_created_at }}</div>
                                @endif
                            </td>
                            <td class="w-40 text-right">
                                <div class="pr-16">£{{ number_format($order->total_amount, 2) }}</div>
                            </td>
                            <td class="table-report__action">
                                <div class="flex justify-center items-center">
                                    {{--  href="javascript:;" data-tw-toggle="modal" data-order_id="{{ $order->id }}" data-tw-target="#viewInvoiceModal" --}}
                                    <a href="{{ route("students.order.print.pdf", $order->id) }}"  class="viewInvoiceForStudent flex items-center text-primary whitespace-nowrap mr-5" >
                                        <i data-lucide="cloud-download" class="w-4 h-4 mr-1"></i> 
                                            @if($order->payment_status!="Completed")
                                                Download Invoice
                                            @else
                                                Download Receipt
                                            @endif
                                    </a>
                                    @if($order->payment_status!="Completed")
                                        {{-- <a class="flex items-center text-primary whitespace-nowrap" href="javascript:;" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal">
                                            <i data-lucide="arrow-left-right" class="w-4 h-4 mr-1"></i> Change Status
                                        </a> --}}
                                        <div class="dropdown">
                                            <button class="dropdown-toggle btn px-2 border-0" aria-expanded="false" data-tw-toggle="dropdown">
                                                <span class="w-5 h-5 flex items-center justify-center">
                                                    <i class="w-4 h-4" data-lucide="grip"></i>
                                                </span>
                                            </button>
                                            <div class="dropdown-menu w-40">
                                                <ul class="dropdown-content">
                                                    @if($order->status!="Rejected")
                                                    <li>
                                                        <a id="payButton_{{ $order->id }}" href="" class="dropdown-item payByCard" 
                                                        data-quantity-wihout-free="{{ $order->total_paid_quantity }}" 
                                                        data-currency="GBP" 
                                                        data-invoice-number="{{ $order->invoice_number }}" 
                                                        data-amount="{{ $order->total_amount * 100 }}"  
                                                        data-action="confirm" >
                                                            <i data-lucide="credit-card" class="w-4 h-4 mr-2"></i> Pay By Card
                                                            <i data-loading-icon="oval" class="w-4 h-4 ml-1 loadingIcon hidden"></i>
                                                        </a>
                                                    </li>
                                                    {{-- <li>
                                                        <a href="" id="payPaypalButton_{{ $order->id }}" class="dropdown-item payByPayPal" 
                                                        data-quantity-wihout-free="{{ $order->total_paid_quantity }}" 
                                                        data-currency="GBP" 
                                                        data-invoice-number="{{ $order->invoice_number }}" 
                                                        data-amount="{{ $order->total_amount * 100 }}"  
                                                        data-action="confirm">
                                                            <i data-lucide="arrow-left-right" class="w-4 h-4 mr-2"></i> Pay By PayPal
                                                        </a>
                                                    </li> --}}
                                                    <li>
                                                        <a href="" data-tw-toggle="modal" data-order_id="{{ $order->id }}" data-tw-target="#confirmModal" class="dropdown-item text-danger cancelOrder" data-id="{{ $order->id }}" >
                                                            <i data-lucide="ban" class="w-4 h-4 mr-2"></i> Cancel Order
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- END: Data List -->
        <!-- BEGIN: Pagination -->
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-row sm:flex-nowrap items-center">
            <nav class="w-full sm:w-auto sm:mr-auto">
                <ul class="pagination">
                    {{-- <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="w-4 h-4" data-lucide="chevrons-left"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="w-4 h-4" data-lucide="chevron-left"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">...</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">...</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="w-4 h-4" data-lucide="chevron-right"></i>
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">
                            <i class="w-4 h-4" data-lucide="chevrons-right"></i>
                        </a>
                    </li> --}}
                </ul>
            </nav>
            <select class="w-20 form-select box mt-3 sm:mt-0">
                <option>All</option>
            </select>
        </div>
        <!-- END: Pagination -->
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
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="circle-x" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 text-danger errorModalTitle"></div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 modal-title">Are you sure?</div>
                        <div class="text-slate-500 mt-2 modal-desc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Super Large Modal Content -->
    <div id="viewInvoiceModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-body p-10 text-center">
                    This is totally awesome superlarge modal!
                </div>
            </div>
        </div>
    </div>
    <!-- END: Super Large Modal Content -->
@endsection


@section('script')
 @vite(['resources/js/document-requests.js'])
 @vite(['resources/js/stripe-class-checkout.js'])
@endsection
