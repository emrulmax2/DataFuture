@extends('../layout/' . $layout)

@section('subhead')
    <title>Document Request Checkout</title>
@endsection

@section('subcontent')
    @php
        $subtotal = 0;
        $tax = 0;
        $total = 0;
        $totalPaidItemQty = 0;

        foreach ($shoppingCart as $item) {
            $subtotal += $item->total_amount;
            $tax += $item->tax_amount;
            $total += $item->total_amount + $item->tax_amount;
            $totalPaidItemQty += ($item->quantity - $item->number_of_free);
        }

        $shippingParts = [];

        if (isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0) {
            $shippingParts = array_values(array_filter([
                $student->contact->termaddress->address_line_1 ?? null,
                $student->contact->termaddress->address_line_2 ?? null,
                trim(implode(', ', array_filter([
                    $student->contact->termaddress->city ?? null,
                    $student->contact->termaddress->state ?? null,
                    $student->contact->termaddress->post_code ?? null,
                ]))) ?: null,
                $student->contact->termaddress->country ?? null,
            ]));
        }
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Do it online &middot; Document requests</div>
            <h1 class="spf-h1">Checkout</h1>
        </div>
        <div class="spf-spacer"></div>
        <a href="{{ route('students.document-request-form.products') }}" class="spf-btn spf-btn--sm">&larr; Back to documents</a>
    </div>

    <form id="checkoutForm" method="POST" action="#" enctype="multipart/form-data">
        @csrf
        <div class="spf-split2" style="max-width:1000px">
            <section class="spf-panel">
                <div class="spf-panel__head">
                    <span class="spf-railcard__title">Student info</span>
                </div>
                <div class="spf-deflist">
                    <div class="spf-deflist__row"><span>Full name</span><span>{{ $student->full_name }}</span></div>
                    <div class="spf-deflist__row"><span>Registration</span><span>{{ $student->registration_no }}</span></div>
                    <div class="spf-deflist__row"><span>Phone number</span><span>{{ !empty($student->contact->mobile) ? $student->contact->mobile : '—' }}</span></div>
                </div>

                <div class="spf-railcard__divider"></div>

                <div class="spf-railcard__title" style="margin-bottom:12px">Shipping address</div>
                <div class="spf-hero__list">
                    @forelse($shippingParts as $line)
                        {{ $line }}<br>
                    @empty
                        <span class="spf-warn">Not set yet</span>
                    @endforelse
                </div>

                @if($total > 0)
                    <div class="spf-railcard__divider"></div>
                    <div class="spf-railcard__title payment_method" style="margin-bottom:12px">Payment method <span style="color:var(--spf-rust)">*</span></div>
                    <label class="spf-radio">
                        <input type="radio" name="payment_method" value="Card" />
                        <span>Credit / debit card</span>
                    </label>
                    <div id="card-element" style="margin-top:14px"></div>
                    <div class="acc__input-error error-payment_method spf-modal__note"></div>
                @else
                    <label class="spf-radio hidden">
                        <input type="radio" name="payment_method" value="N/A" checked />
                        <span>Free</span>
                    </label>
                @endif
            </section>

            <section class="spf-panel">
                <div class="spf-panel__head">
                    <span class="spf-railcard__title">Order summary</span>
                </div>

                @forelse($shoppingCart as $item)
                    <input type="hidden" name="shopping_cart_ids[]" value="{{ $item->id }}">
                    <input type="hidden" name="product_type[]" value="{{ $item->product_type }}">
                    <input type="hidden" name="letter_set_id[]" value="{{ $item->letterSet->id }}">
                    <input type="hidden" name="sub_amount[]" value="{{ $item->sub_amount }}">
                    <input type="hidden" name="tax_amount[]" value="{{ $item->tax_amount }}">
                    <input type="hidden" name="total_amount[]" value="{{ $item->total_amount }}">
                    <input type="hidden" name="quantity[]" value="{{ $item->quantity }}">
                    <input type="hidden" name="status" value="Pending">
                    <div class="spf-summary__row">
                        <span>
                            {{ $item->letterSet->letter_title }}
                            <span class="spf-summary__qty">(Qty: {{ $item->quantity }})</span>
                        </span>
                        <span style="font-weight:500;white-space:nowrap">&pound;{{ number_format($item->total_amount, 2) }}</span>
                    </div>
                @empty
                    <div class="spf-notice">Your basket is empty.</div>
                    <a href="{{ route('students.document-request-form.products') }}" class="spf-btn spf-btn--sm" style="margin-top:12px">Browse documents</a>
                @endforelse

                @if(count($shoppingCart) > 0)
                    <div class="spf-summary__line"><span>Subtotal</span><span>&pound;{{ number_format($subtotal, 2) }}</span></div>
                    <div class="spf-summary__line"><span>Tax</span><span>&pound;{{ number_format($tax, 2) }}</span></div>
                    <div class="spf-summary__total"><span>Total</span><span>&pound;{{ number_format($total, 2) }}</span></div>

                    <input type="hidden" id="student_id" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" id="amount" name="amount" value="{{ $total * 100 }}">
                    <input type="hidden" id="currency" name="currency" value="GBP">
                    <input type="hidden" id="quantity_without_free" name="quantity_without_free" value="{{ $totalPaidItemQty }}">
                    <input type="hidden" id="invoice_number" name="invoice_number" value="INV-250508000001">

                    <button id="payButton" type="button" class="hidden payCard spf-btn spf-btn--dark" style="width:100%;justify-content:center;margin-top:20px">
                        Pay with card
                        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 hidden"></i>
                    </button>
                    <button id="paypalButton" type="button" class="hidden">Pay with PayPal</button>
                    <button id="saveBtn" type="submit" class="saveBtn spf-btn spf-btn--dark" style="width:100%;justify-content:center;margin-top:20px">
                        Place order
                        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 hidden"></i>
                    </button>
                @endif
            </section>
        </div>
    </form>

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
@endsection

@section('script')
    @vite('resources/js/checkout.js')
    @vite('resources/js/stripe-checkout.js')
@endsection
