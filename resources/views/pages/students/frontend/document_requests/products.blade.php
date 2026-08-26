@extends('../layout/' . $layout)

@section('subhead')
    <title>Document requests - London Churchill College</title>
@endsection

@section('subcontent')
    @php
        /* The basket is the live shopping cart the session middleware keeps;
           `$countPendingOrders` is a different thing — submitted orders. */
        $basketCount = is_countable(session('shopping_cart')) ? count(session('shopping_cart')) : 0;

        /* Two letters from the product title, for the card tile. */
        $initials = function ($name) {
            $letters = '';

            foreach (preg_split('/\s+/', trim((string) $name)) as $word) {
                if ($word !== '' && ctype_alpha($word[0])) {
                    $letters .= strtoupper($word[0]);
                }

                if (strlen($letters) === 2) {
                    break;
                }
            }

            return $letters !== '' ? $letters : 'DR';
        };

        /* A stable tint per product, so the same item keeps its colour. */
        $tile = function ($name) {
            $variants = ['', ' spf-module__icon--v1', ' spf-module__icon--v2', ' spf-module__icon--v3', ' spf-module__icon--v4'];

            return 'spf-module__icon' . $variants[crc32((string) $name) % count($variants)];
        };
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Do it online &middot; Services</div>
            <h1 class="spf-h1">Document requests</h1>
            <div class="spf-page-head__sub">Document / ID card replacement / printer balance top-up.</div>
        </div>
        <div class="spf-spacer"></div>
        @if($basketCount > 0)
            <a href="{{ route('students.shopping.cart.checkout') }}" class="spf-chip spf-chip--cream spf-chip--lg" style="text-decoration:none">
                Basket &middot; {{ $basketCount }} &rarr;
            </a>
        @else
            <span class="spf-chip spf-chip--grey spf-chip--lg">Basket &middot; 0</span>
        @endif
        <a href="{{ route('students.document-request-form.index') }}" class="spf-btn spf-btn--sm">
            My orders
            @if($countPendingOrders > 0)
                <span class="spf-nav__badge" id="orderCountBadge">{{ $countPendingOrders }}</span>
            @endif
        </a>
        <a href="{{ route('students.dashboard') }}" class="spf-btn spf-btn--sm">&larr; Back to dashboard</a>
    </div>

    @if(count($letter_sets) > 0)
        <div class="spf-cardgrid spf-cardgrid--3">
            @foreach ($letter_sets as $letter_set)
                @php
                    $isPrinterTopUp = $letter_set->id == 165;
                    $isPaidOnly = $letter_set->id == 159;
                    $amount = $isPrinterTopUp ? '5.00' : '10.00';
                @endphp
                <form id="LetterFormRequest{{ $letter_set->id }}" class="LetterFormRequest spf-doccard" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="letter_set_id" value="{{ $letter_set->id }}">
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    <input type="hidden" name="description" value="{{ $letter_set->letter_title }}" />
                    <input type="hidden" name="student_consent" value="1" />
                    <input type="hidden" name="term_declaration_id" value="{{ $current_term_id->id }}">
                    <input type="hidden" name="status" value="Pending">
                    <input type="hidden" name="sub_amount" value="{{ $amount }}">
                    <input type="hidden" name="tax_amount" value="0.00">
                    <input type="hidden" name="total_amount" value="{{ $amount }}">

                    <div class="spf-doccard__head">
                        <span class="{{ $tile($letter_set->letter_title) }}">{{ $initials($letter_set->letter_title) }}</span>
                        <div style="min-width:0">
                            @if(!empty($letter_set->letter_type))
                                <span class="spf-chip spf-chip--cream spf-chip--wrap">{{ $letter_set->letter_type }}</span>
                            @endif
                            <div class="spf-doccard__title" style="margin-top:7px">{{ $letter_set->letter_title }}</div>
                        </div>
                    </div>

                    @if(!empty($letter_set->letter_description))
                        <div class="spf-doccard__body">
                            <div class="spf-doccard__meta"><span>{{ $letter_set->letter_description }}</span></div>
                        </div>
                    @endif

                    <div class="spf-doccard__options">
                        @if(!$isPaidOnly && !$isPrinterTopUp)
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="3 Working Days (Free)" data-studentid="{{ $student->id }}" class="add-tofree-cart spf-optionbtn">
                                <span>3 working days</span>
                                <span class="spf-optionbtn__price">Free</span>
                                <i data-loading-icon="puff" class="w-4 h-4 hidden"></i>
                            </button>
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="Same Day (cost £10.00)" data-studentid="{{ $student->id }}" class="add-topaid-cart spf-optionbtn spf-optionbtn--paid">
                                <span>Same day</span>
                                <span class="spf-optionbtn__price">&pound;10.00</span>
                                <i data-loading-icon="puff" class="w-4 h-4 hidden"></i>
                            </button>
                        @elseif($isPrinterTopUp)
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="Printer Top Up (cost £5.00)" data-studentid="{{ $student->id }}" class="add-topaid-cart spf-optionbtn spf-optionbtn--paid">
                                <span>Printer top up</span>
                                <span class="spf-optionbtn__price">&pound;5.00</span>
                                <i data-loading-icon="puff" class="w-4 h-4 hidden"></i>
                            </button>
                        @else
                            <button type="button" data-letterid="{{ $letter_set->id }}" data-service_type="3 Working Days (cost £10.00)" data-studentid="{{ $student->id }}" class="add-topaid-cart spf-optionbtn spf-optionbtn--paid">
                                <span>3 working days</span>
                                <span class="spf-optionbtn__price">&pound;10.00</span>
                                <i data-loading-icon="puff" class="w-4 h-4 hidden"></i>
                            </button>
                        @endif
                    </div>
                </form>
            @endforeach
        </div>
    @else
        <div class="spf-empty">No documents are available to request at the moment.</div>
    @endif

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
    @vite(['resources/js/document-requests.js'])
    @vite(['resources/js/add-to-cart.js'])
@endsection
