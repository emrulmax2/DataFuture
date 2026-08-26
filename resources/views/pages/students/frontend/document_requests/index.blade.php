@extends('../layout/' . $layout)

@section('subhead')
    <title>Order list - London Churchill College</title>
@endsection

@section('subcontent')
    @php
        $statusTone = [
            'Completed'   => 'spf-chip--green',
            'In Progress' => 'spf-chip--cream',
            'Pending'     => 'spf-chip--grey',
            'Rejected'    => 'spf-chip--rust',
            'Cancelled'   => 'spf-chip--rust',
            'Canceled'    => 'spf-chip--rust',
        ];

        $orderStatuses = collect($studentOrderList)->pluck('status')->filter()->unique()->values();
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Do it online &middot; Document requests</div>
            <h1 class="spf-h1">Order list</h1>
        </div>
        <div class="spf-spacer"></div>
        <a href="{{ route('students.document-request-form.products') }}" class="spf-btn spf-btn--sm">&larr; Back to documents</a>
    </div>

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
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
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
        <button id="error-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
    @endif

    {{-- Filters run client-side over the rows below (see student-portal.js). --}}
    <form class="spf-toolbar" onsubmit="return false">
        <input type="text" id="orderSearch" class="spf-input--pill" placeholder="Search by invoice...">
        <div class="spf-dd">
            <button type="button" class="spf-chip spf-chip--cream spf-chip--lg spf-chipbtn" data-spf-dd="orderStatusMenu">
                Status &middot; <span id="orderStatusLabel">All</span> &#9662;
            </button>
            <div id="orderStatusMenu" class="spf-dd__menu" style="width:200px">
                <a href="javascript:;" class="spf-dd__item" data-order-status="">All</a>
                @foreach($orderStatuses as $status)
                    <a href="javascript:;" class="spf-dd__item" data-order-status="{{ $status }}">{{ $status }}</a>
                @endforeach
            </div>
        </div>
    </form>

    <div class="spf-rtable-wrap">
        <div class="spf-otable" data-order-table>
            <div class="spf-otable__head">
                <div>S/N</div>
                <div>Invoice</div>
                <div>Product</div>
                <div>Status</div>
                <div>Payment</div>
                <div>Total</div>
                <div>Actions</div>
            </div>

            @forelse($studentOrderList as $index => $order)
                @php
                    $tone = $statusTone[$order->status] ?? 'spf-chip--grey';
                    $isPaid = $order->payment_status == 'Completed';
                @endphp
                <div class="spf-otable__row"
                     data-order-row
                     data-invoice="{{ strtolower($order->invoice_number) }}"
                     data-status="{{ $order->status }}">
                    <div class="spf-cell--muted">{{ $index + 1 }}</div>

                    <div class="spf-cell--accent" style="word-break:break-word">#{{ $order->invoice_number }}</div>

                    <div>
                        @foreach ($order->studentOrderItems as $item)
                            <div class="spf-otable__product">
                                <div class="spf-otable__product-name">
                                    {{ $item->letterSet->letter_title }}
                                    <span class="spf-otable__qty">[Qty: {{ $item->quantity }}]</span>
                                </div>
                                <div class="spf-dtable__sub">
                                    @if($item->product_type == 'Paid')
                                        {{ $item->letterSet->id == 159
                                            ? '3 Working Days (cost £10.00)'
                                            : ($item->letterSet->id == 165 ? 'Printer Top Up (cost £5.00)' : 'Same Day (£10.00)') }}
                                        [{{ $item->quantity - $item->number_of_free }}]
                                    @else
                                        3 Working Days (Free)
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <span class="spf-chip {{ $tone }}">
                            @if($order->status == 'Completed')<i data-lucide="check" class="w-3 h-3"></i>@endif
                            {{ $order->status }}
                        </span>
                        @if($isPaid && !empty($order->formatted_updated_at))
                            <span class="spf-dtable__sub">{{ $order->formatted_updated_at }}</span>
                        @endif
                    </div>

                    <div>
                        @if($order->payment_method == 'Card' && $isPaid)
                            <div>Debit or credit card</div>
                            <span class="spf-dtable__sub">{{ $order->formatted_transaction_date }}</span>
                        @elseif($order->payment_method == 'PayPal' && $isPaid)
                            <div>PayPal</div>
                            <span class="spf-dtable__sub">{{ $order->formatted_transaction_date }}</span>
                        @else
                            <div class="spf-cell--muted">N/A</div>
                            <span class="spf-dtable__sub">{{ $order->formatted_created_at }}</span>
                        @endif
                    </div>

                    <div style="font-weight:600">&pound;{{ number_format($order->total_amount, 2) }}</div>

                    <div class="spf-otable__actions">
                        <a href="{{ route('students.order.print.pdf', $order->id) }}" class="viewInvoiceForStudent spf-linkbtn">
                            {{ $isPaid ? 'Download receipt' : 'Download invoice' }}
                        </a>

                        @if(!$isPaid && $order->status != 'Rejected')
                            <div class="spf-dd">
                                <button type="button" class="spf-user__logout" style="color:var(--spf-muted)" data-spf-dd="orderMenu{{ $order->id }}" aria-label="More actions">
                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                </button>
                                <div id="orderMenu{{ $order->id }}" class="spf-dd__menu" style="width:190px">
                                    <a id="payButton_{{ $order->id }}" href="javascript:;" class="spf-dd__item payByCard"
                                       data-quantity-wihout-free="{{ $order->total_paid_quantity }}"
                                       data-currency="GBP"
                                       data-invoice-number="{{ $order->invoice_number }}"
                                       data-amount="{{ $order->total_amount * 100 }}"
                                       data-action="confirm">
                                        <i data-lucide="credit-card" class="w-4 h-4"></i> Pay by card
                                        <i data-loading-icon="oval" class="w-4 h-4 loadingIcon hidden"></i>
                                    </a>
                                    <a href="javascript:;" data-tw-toggle="modal" data-order_id="{{ $order->id }}" data-tw-target="#confirmModal"
                                       class="spf-dd__item cancelOrder" data-id="{{ $order->id }}" style="color:var(--spf-rust)">
                                        <i data-lucide="ban" class="w-4 h-4"></i> Cancel order
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="spf-otable__row" style="grid-template-columns:1fr">
                    <div class="spf-cell--muted">You have not placed any document requests yet.</div>
                </div>
            @endforelse

            <div class="spf-otable__foot">
                Showing <strong data-order-count>{{ $studentOrderList->count() }}</strong>
                <span data-order-noun>{{ $studentOrderList->count() === 1 ? 'order' : 'orders' }}</span>
            </div>
        </div>
    </div>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--ok">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </span>
                <div class="successModalTitle"></div>
                <div class="successModalDesc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn spf-btn--dark">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--warn">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </span>
                <div class="errorModalTitle"></div>
                <div class="errorModalDesc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Error Modal Content -->

    <!-- BEGIN: Cancel Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--warn">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </span>
                <div class="successModalTitle modal-title">Are you sure?</div>
                <div class="successModalDesc modal-desc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="spf-btn">No, cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWith spf-btn spf-btn--dark">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Cancel Confirm Modal Content -->
@endsection

@section('script')
    @vite(['resources/js/document-requests.js'])
    @vite(['resources/js/stripe-class-checkout.js'])
@endsection
