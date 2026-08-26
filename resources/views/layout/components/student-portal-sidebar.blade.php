{{--
    Student portal primary navigation.

    Off-canvas below 1024px — `student-portal-topbar` owns the toggle and
    `js/student-portal.js` wires the two together.
--}}
@php
    /* Brand mark for the dark sidebar, in order of preference:
       the dark-background logo from Site Settings, then the main company logo
       from Site Settings, then the bundled white vector mark. Always an image —
       never a text stand-in. */
    $portalLogoUrl = null;

    foreach (['site_dark_logo', 'site_logo'] as $optionName) {
        $file = cache()->get($optionName);

        if (!$file) {
            $file = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', $optionName)->value('value');

            if ($file) {
                cache()->forever($optionName, $file);
            }
        }

        if (!empty($file) && Storage::disk('local')->exists('public/'.$file)) {
            $portalLogoUrl = Storage::disk('local')->url('public/'.$file);
            break;
        }
    }

    if (!$portalLogoUrl) {
        $portalLogoUrl = asset('build/assets/images/logo_white.svg');
    }

    $current = Route::currentRouteName();

    $isActive = function ($names) use ($current) {
        return in_array($current, (array) $names, true) ? ' is-active' : '';
    };

    $hasWorkplacement = isset($student->crel->creation->is_workplacement) && $student->crel->creation->is_workplacement == 1;
    $studentUser = auth('student')->user();
    $cart = is_countable(session('shopping_cart')) ? collect(session('shopping_cart')) : collect();
    $cartCount = $cart->count();
    $cartTotal = $cart->sum(function ($item) {
        return (float) $item->total_amount + (float) $item->tax_amount;
    });
@endphp

<aside id="spfSidebar" class="spf-sidebar">
    <a href="{{ route('students.dashboard') }}" class="spf-sidebar__brand" aria-label="London Churchill College Student Portal">
        <img src="{{ $portalLogoUrl }}" alt="London Churchill College" class="spf-sidebar__logo">
    </a>

    <div class="spf-sidebar__scroll">
        <nav class="spf-nav">
            <a href="{{ route('students.dashboard') }}" class="spf-nav__link{{ $isActive('students.dashboard') }}">Dashboard</a>
            <a href="{{ route('students.dashboard.profile') }}" class="spf-nav__link{{ $isActive('students.dashboard.profile') }}">Profile</a>

            @if($student)
                <a href="{{ route('students.results.frontend.index', $student->id) }}" class="spf-nav__link{{ $isActive('students.results.frontend.index') }}">Results</a>
                <a href="{{ route('students.performance.frontend.index', $student->id) }}" class="spf-nav__link{{ $isActive('students.performance.frontend.index') }}">Performance</a>
            @endif

            <a href="{{ route('students.dashboard.forms') }}" class="spf-nav__link{{ $isActive('students.dashboard.forms') }}">Do it online</a>

            @if($hasWorkplacement)
                <a href="{{ route('students.dashboard.workplacement') }}" class="spf-nav__link{{ $isActive('students.dashboard.workplacement') }}">Work placement</a>
            @endif
        </nav>

    </div>

    <div class="spf-sidebar__spacer"></div>

    @impersonating($guard = 'student')
        <a href="{{ route('impersonate.leave') }}" class="spf-sidebar__action">
            <i data-lucide="corner-down-left" class="w-4 h-4"></i> Leave impersonation
        </a>
    @endImpersonating

    <div class="spf-user">
        @if($student)
            <img src="{{ $student->photo_url }}" alt="{{ $student->first_name }}" class="spf-user__avatar">
        @endif
        <div class="spf-user__meta">
            <div class="spf-user__name">
                @if($student)
                    {{ strtok(trim($student->first_name), ' ') }} {{ substr($student->last_name, 0, 1) }}.
                @else
                    {{ isset($studentUser->name) ? $studentUser->name : 'Student' }}
                @endif
            </div>
            <div class="spf-user__role">Student user</div>
        </div>
        <div class="spf-cartwrap">
            <a href="{{ $cartCount > 0 ? route('students.shopping.cart.checkout') : route('students.document-request-form.products') }}"
               class="spf-user__logout spf-cart"
               aria-label="Basket{{ $cartCount > 0 ? ' — '.$cartCount.' item'.($cartCount === 1 ? '' : 's') : ' (empty)' }}">
                <i data-lucide="shopping-basket" class="w-4 h-4"></i>
                @if($cartCount > 0)
                    <span class="spf-cart__badge">{{ $cartCount }}</span>
                @endif
            </a>

            <div class="spf-cart__pop" id="spfCartPop" role="tooltip">
                <div class="spf-cart__pop-head">Your basket</div>
                @if($cartCount > 0)
                    <div class="spf-cart__items" id="spfCartItems"
                         data-spf-cart-remove-url="{{ route('students.shopping.cart.destory', '__ID__') }}"
                         data-spf-cart-limit="4">
                        @foreach($cart->take(4) as $item)
                            @php $itemName = $item->letterSet->letter_title ?? 'Item'; @endphp
                            <div class="spf-cart__item" data-spf-cart-item="{{ $item->id }}">
                                <span class="spf-cart__item-name">{{ $itemName }}</span>
                                <span class="spf-cart__item-qty">&times;{{ $item->quantity }}</span>
                                <span class="spf-cart__item-price">&pound;{{ number_format((float) $item->total_amount, 2) }}</span>
                                <button type="button" class="spf-cart__remove"
                                        data-spf-cart-remove="{{ route('students.shopping.cart.destory', $item->id) }}"
                                        title="Remove from basket"
                                        aria-label="Remove {{ $itemName }} from basket">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    @if($cartCount > 4)
                        <div class="spf-cart__more" id="spfCartMore">+ {{ $cartCount - 4 }} more</div>
                    @endif
                    <div class="spf-cart__total" id="spfCartTotal">
                        <span>Total</span>
                        <span>&pound;{{ number_format($cartTotal, 2) }}</span>
                    </div>
                    <a href="{{ route('students.shopping.cart.checkout') }}" class="spf-cart__cta"
                       data-spf-empty-href="{{ route('students.document-request-form.products') }}">Go to checkout &rarr;</a>
                @else
                    <div class="spf-cart__empty">Your basket is empty.</div>
                    <a href="{{ route('students.document-request-form.products') }}" class="spf-cart__cta">Browse documents &rarr;</a>
                @endif
            </div>
        </div>

        <a href="{{ route('students.logout') }}" class="spf-user__logout" title="Sign out" aria-label="Sign out">
            <i data-lucide="log-out" class="w-4 h-4"></i>
        </a>
    </div>
</aside>
