@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/student-library.css')
@endsection

@section('subcontent')
    @php
        $depositHeld = (bool) $deposit;
        $openCount = $openLoans->count();
        $owed = $openLoans->sum(fn ($loan) => $rules->fineFor($loan));

        /* Some catalogue "covers" are PDFs, which an <img> cannot draw — the
           markup falls back to the placeholder icon underneath. */
        $drawable = function ($url) {
            $ext = strtolower(pathinfo((string) parse_url((string) $url, PHP_URL_PATH), PATHINFO_EXTENSION));

            return $url && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'], true);
        };
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Library</div>
            <h1 class="spf-h1">Borrow from the college library</h1>
            <div class="spf-page-head__sub">
                Search the shelves, reserve a copy for collection and keep track of what you have out.
            </div>
        </div>
    </div>

    <div class="slib" id="studentLibrary"
         data-search-url="{{ route('students.library.search') }}"
         data-borrow-url="{{ route('students.library.borrow') }}"
         data-blocked-reason="{{ $blockedReason }}"
         data-held-titles="{{ json_encode($heldTitles) }}">

        {{-- Flash messages from the PayPal round trip. --}}
        @if(session('library_success'))
            <div class="spf-notice">
                <span class="spf-notice__icon"><i data-lucide="check-circle" class="w-4 h-4"></i></span>
                <div class="spf-notice__text">{{ session('library_success') }}</div>
            </div>
        @endif
        @if(session('library_error'))
            <div class="spf-notice">
                <span class="spf-notice__icon"><i data-lucide="alert-octagon" class="w-4 h-4"></i></span>
                <div class="spf-notice__text">{{ session('library_error') }}</div>
            </div>
        @endif

        {{-- Deposit gate. Until this is paid there is nothing else worth doing
             on the page, so it sits above the catalogue rather than beside it.
             Only a gate while deposits are actually enforced — asking for money
             that is not required would be misleading, so with the rule off the
             panel does not appear at all. --}}
        @if($rules->requiresDeposit() && !$depositHeld)
            <div class="spf-panel" style="margin-bottom: 22px;">
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:18px;">
                    <span class="slib-modal__icon"><i data-lucide="wallet"></i></span>
                    <div style="flex:1 1 260px; min-width:0;">
                        <h2 class="spf-h2">Pay your refundable deposit to start borrowing</h2>
                        <div class="spf-page-head__sub">
                            A one-off <strong>£{{ number_format($rules->depositAmount(), 2) }}</strong> bond held on
                            your account and refunded when you finish your course. Overdue charges are billed
                            separately.
                        </div>
                    </div>
                    {{-- Sends the student to PayPal to approve; PayPal returns
                         them to deposit/complete where the order is captured
                         server-side. --}}
                    <form method="post" action="{{ route('students.library.deposit.checkout') }}">
                        @csrf
                        <button type="submit" class="spf-btn spf-btn--dark spf-btn--choice">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                            Pay £{{ number_format($rules->depositAmount(), 2) }} with PayPal
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="spf-stats">
            <div class="spf-stat">
                <div class="spf-stat__label">Deposit</div>
                @if($depositHeld)
                    {{-- No refund control here. Releasing the bond is handled
                         at the desk, so the card reports what is held and
                         leaves it at that — a Request refund button the
                         student cannot actually complete is worse than none. --}}
                    <div class="spf-stat__value spf-stat__value--green">£{{ number_format($deposit->amount, 2) }}</div>
                    <div class="spf-page-head__sub">
                        Held since {{ optional($deposit->paid_at)->format('j M Y') }}
                    </div>
                @elseif($rules->requiresDeposit())
                    <div class="spf-stat__value spf-stat__value--rust">Not paid</div>
                    <div class="spf-page-head__sub">£{{ number_format($rules->depositAmount(), 2) }} required</div>
                @else
                    <div class="spf-stat__value">Not required</div>
                    <div class="spf-page-head__sub">No deposit is needed to borrow at the moment.</div>
                @endif
            </div>

            <div class="spf-stat">
                <div class="spf-stat__label">Books out</div>
                <div class="spf-stat__value">{{ $openCount }}<small> / {{ $rules->maxBooks() }} allowed</small></div>
                <div class="spf-page-head__sub">Loan period {{ $rules->loanPeriodDays() }} days</div>
            </div>

            <div class="spf-stat">
                <div class="spf-stat__label">Overdue charges</div>
                <div class="spf-stat__value {{ $owed > 0 ? 'spf-stat__value--rust' : '' }}">£{{ number_format($owed, 2) }}</div>
                <div class="spf-page-head__sub">
                    £{{ number_format($rules->penaltyPerDay(), 2) }} per day after
                    {{ $rules->graceDays() }} day{{ $rules->graceDays() == 1 ? '' : 's' }} grace
                </div>
            </div>
        </div>

        @if($blockedReason && $depositHeld)
            <div class="spf-notice">
                <span class="spf-notice__icon"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
                <div class="spf-notice__text">{{ $blockedReason }}</div>
            </div>
        @endif

        {{-- Catalogue --}}
        <section class="spf-section">
            <div class="spf-section__head">
                <h2 class="spf-h2">Search the library</h2>
                <span class="spf-section__note">Reserved books are held at the desk for {{ $rules->holdDays() }} days.</span>
            </div>

            <div class="spf-panel">
                <div class="slib-search__row">
                    <div class="slib-search__field">
                        <i data-lucide="search"></i>
                        <input id="libSearch" type="text" class="slib-input"
                               placeholder="Search by title, author or ISBN..." autocomplete="off">
                    </div>
                    <div class="slib-search__field" style="flex:0 0 210px">
                        <i data-lucide="check-circle"></i>
                        <select id="libAvailability" class="slib-select">
                            <option value="">Any availability</option>
                            <option value="available">Available now</option>
                            <option value="out">All copies out</option>
                        </select>
                    </div>
                </div>

                {{-- Only venues, courses and modules that actually have books are
                     offered, so a filter can never return an empty shelf. --}}
                <div class="slib-search__filters">
                    <div>
                        <label class="slib-search__label" for="libVenue">Venue</label>
                        <div class="slib-search__field">
                            <i data-lucide="map-pin"></i>
                            <select id="libVenue" class="slib-select">
                                <option value="">All venues</option>
                                @foreach($filterOptions['venues'] ?? [] as $venue)
                                    <option value="{{ $venue['id'] }}">{{ $venue['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="slib-search__label" for="libCourse">Course</label>
                        <div class="slib-search__field">
                            <i data-lucide="graduation-cap"></i>
                            <select id="libCourse" class="slib-select">
                                <option value="">All courses</option>
                                @foreach($filterOptions['courses'] ?? [] as $course)
                                    <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="slib-search__label" for="libModule">Course module</label>
                        <div class="slib-search__field">
                            <i data-lucide="layers"></i>
                            {{-- Not narrowed by Course: a module's parent is an
                                 iv_programme in Operations while titles are tagged
                                 against academic_courses, and the two share no key. --}}
                            <select id="libModule" class="slib-select">
                                <option value="">All modules</option>
                                @foreach($filterOptions['modules'] ?? [] as $module)
                                    <option value="{{ $module['id'] }}">
                                        {{ $module['name'] }}{{ $module['code'] ? ' ('.$module['code'].')' : '' }}{{ !empty($module['programme']) ? ' — '.$module['programme'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Sits in the filter row rather than under it: it acts on
                         these three fields, so it belongs beside them. --}}
                    <div class="slib-search__action">
                        <button type="button" id="libReset" class="slib-reset">
                            <i data-lucide="rotate-ccw" class="w-4 h-4" style="margin-right:6px"></i>Clear filters
                        </button>
                    </div>
                </div>

                {{-- Hidden until something is actually searched for: an empty
                     results panel is dead space on first load. --}}
                <div id="libResultsWrap" class="slib-results" hidden>
                    <div class="slib-results__head">
                        <span id="libCount" class="slib-count"></span>
                    </div>
                    <div id="libResults"></div>
                </div>
            </div>
        </section>

        {{-- Current loans --}}
        <section class="spf-section">
            <div class="spf-section__head">
                <h2 class="spf-h2">My books</h2>
                @if($openCount > 0)
                    <span class="spf-chip spf-chip--grey">{{ $openCount }}</span>
                @endif
            </div>

            <div class="spf-panel--flush">
                <div class="slib-panel">
                    <table class="slib-table">
                        <thead>
                            <tr>
                                <th class="slib-table__book">Book</th>
                                <th>Where</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th class="slib-table__right">Charge</th>
                                <th class="slib-table__right">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($openLoans as $loan)
                                @php
                                    $fine = $rules->fineFor($loan);
                                    $left = $rules->daysRemaining($loan);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="slib-media">
                                            <span class="slib-media__cover">
                                                <i data-lucide="book"></i>
                                                @if($drawable($loan->cover_url))
                                                    <img src="{{ $loan->cover_url }}" alt="" loading="lazy" onerror="this.remove()">
                                                @endif
                                            </span>
                                            <div class="slib-media__copy">
                                                <div class="slib-media__title">
                                                    {{ $loan->title }}
                                                    @if($loan->isDayReading())
                                                        <span class="slib-badge">Day reading</span>
                                                    @endif
                                                </div>
                                                <div class="slib-table__sub">{{ $loan->author }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="slib-table__sub">
                                        {{ $loan->campus ?: '—' }}
                                        @if($loan->location)<div>{{ $loan->location }}</div>@endif
                                    </td>
                                    <td>
                                        @if($loan->status == 'requested')
                                            <div class="slib-dates">Collect by {{ optional($loan->expires_at)->format('j M Y') }}</div>
                                            <div class="slib-dates__sub">Your loan starts when you collect it</div>
                                        @else
                                            <div class="slib-dates">{{ optional($loan->due_at)->format('j M Y') }}</div>
                                            <div class="{{ $fine > 0 ? 'slib-dates__late' : 'slib-dates__sub' }}">
                                                {{ $fine > 0 ? abs($left).' days overdue' : $left.' days left' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($loan->status == 'requested')
                                            <span class="slib-status slib-status--wait">Awaiting collection</span>
                                        @elseif($fine > 0)
                                            <span class="slib-status slib-status--late">Overdue</span>
                                        @else
                                            <span class="slib-status slib-status--out">On loan</span>
                                        @endif
                                    </td>
                                    <td class="slib-table__right {{ $fine > 0 ? 'slib-charge slib-charge--due' : 'slib-charge' }}">
                                        £{{ number_format($fine, 2) }}
                                    </td>
                                    <td class="slib-table__right">
                                        @if($loan->status == 'requested')
                                            <button class="slib-btn slib-btn--danger libCancel" data-id="{{ $loan->id }}"
                                                    data-title="{{ $loan->title }}"
                                                    data-where="{{ trim(($loan->campus ?: '').(($loan->campus && $loan->location) ? ' · ' : '').($loan->location ?: '')) }}">
                                                <i data-lucide="x-circle"></i>Cancel
                                            </button>
                                        @elseif(!$loan->isDayReading() && $fine <= 0 && $loan->renewals < $rules->maxRenewals())
                                            <button class="slib-btn slib-btn--ghost libRenew" data-id="{{ $loan->id }}">
                                                <i data-lucide="refresh-cw"></i>Renew ({{ $rules->maxRenewals() - $loan->renewals }} left)
                                            </button>
                                        @else
                                            <span class="slib-table__sub">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="slib-note">
                                        <i data-lucide="book-open"></i>
                                        You have no books out.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- History --}}
        @if($pastLoans->isNotEmpty())
            <section class="spf-section">
                <div class="spf-section__head">
                    <h2 class="spf-h2">Previously borrowed</h2>
                    <span class="spf-section__note">{{ $pastLoans->count() }} closed {{ $pastLoans->count() == 1 ? 'loan' : 'loans' }}</span>
                </div>

                <div class="spf-panel--flush">
                    <div class="slib-panel">
                        <table class="slib-table">
                            <tbody>
                                @foreach($pastLoans as $loan)
                                    @php
                                        $ended = [
                                            'cancelled' => 'Cancelled',
                                            'not_collected' => 'Not collected',
                                        ][$loan->status] ?? 'Returned';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="slib-media__title">{{ $loan->title }}</div>
                                            <div class="slib-table__sub">{{ $loan->author }}</div>
                                        </td>
                                        <td>
                                            <div class="slib-dates">
                                                {{ $ended }} {{ optional($loan->returned_at)->format('j M Y') }}
                                            </div>
                                            {{-- Why it ended, in the student's own view: staff give a
                                                 reason at the desk and this is where it is answered. --}}
                                            @if($loan->cancel_reason)
                                                <div class="slib-reason">
                                                    <i data-lucide="info"></i>{{ $loan->cancel_reason }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="slib-table__right {{ $loan->fine_amount > 0 ? 'slib-charge slib-charge--due' : 'slib-charge' }}">
                                            @if($loan->fine_amount > 0)
                                                £{{ number_format($loan->fine_amount, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </div>

    {{-- Reserving takes a copy off the shelf for everyone else, so it is
         confirmed rather than fired on a single click. Self-contained rather
         than the theme's modal API: that API is not defined on every layout,
         and a throw while wiring it takes every later binding with it. --}}
    <div class="slib-modal" id="libConfirmModal" role="dialog" aria-modal="true"
         aria-labelledby="libConfirmHeading" hidden>
        <div class="slib-modal__box">
            <div class="slib-modal__head">
                <span class="slib-modal__icon"><i data-lucide="book-plus"></i></span>
                <div class="slib-modal__heading">
                    <div class="slib-modal__title" id="libConfirmHeading">Reserve this book?</div>
                    <div class="slib-modal__sub">It will be held at the desk for you to collect.</div>
                </div>
                <button type="button" class="slib-modal__x" data-slib-close aria-label="Close">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="slib-modal__body">
                {{-- Everything the catalogue knows, so the choice can be checked
                     without leaving the dialog. --}}
                <div class="slib-book">
                    <span class="slib-cover slib-cover--lg" id="libConfirmCover"></span>
                    <div class="slib-book__copy">
                        <div class="slib-book__title" id="libConfirmTitle"></div>
                        <div class="slib-book__author" id="libConfirmAuthor"></div>
                        <div id="libConfirmPill"></div>
                        <dl class="slib-book__facts" id="libConfirmFacts"></dl>
                    </div>
                </div>

                <ul class="slib-rules">
                    {{-- Where to collect is the student's choice, not a fact
                         to be told: a title can sit on shelves at more than one
                         campus, and the old line simply listed them all. The
                         options are built from the copies Operations reports as
                         available, so a pickup point with nothing on the shelf
                         is never offered. --}}
                    <li>
                        <i data-lucide="map-pin"></i>
                        <span class="slib-rules__pick">
                            <label for="libConfirmLocation">Collect from</label>
                            <span class="slib-search__field slib-search__field--pick">
                                <select id="libConfirmLocation" class="slib-select"></select>
                            </span>
                        </span>
                    </li>
                    <li>
                        <i data-lucide="alarm-clock"></i>
                        <span>Held for <strong>{{ $rules->holdDays() }} days</strong> — after that it goes back on the shelf.</span>
                    </li>
                    <li>
                        <i data-lucide="calendar-days"></i>
                        <span>Your <strong>{{ $rules->loanPeriodDays() }}-day</strong> loan starts when staff hand it over.</span>
                    </li>
                    <li>
                        <i data-lucide="receipt"></i>
                        <span>Late returns are charged <strong>£{{ number_format($rules->penaltyPerDay(), 2) }} per day</strong>.</span>
                    </li>
                </ul>

                <div id="libConfirmError" class="slib-modal__error" hidden></div>
            </div>

            <div class="slib-modal__foot">
                <button type="button" class="slib-btn slib-btn--danger" data-slib-close>
                    <i data-lucide="x"></i>Cancel
                </button>
                <button type="button" id="libConfirmGo" class="slib-borrow">
                    <i data-lucide="check"></i>Yes, reserve it
                </button>
            </div>
        </div>
    </div>

    {{-- Cancelling puts the copy back on the shelf and cannot be undone from
         here, so it is confirmed in the page's own dialog rather than a browser
         confirm() — the same weight as reserving, which already has one. --}}
    <div class="slib-modal" id="libCancelModal" role="dialog" aria-modal="true"
         aria-labelledby="libCancelHeading" hidden>
        <div class="slib-modal__box slib-modal__box--sm">
            <div class="slib-modal__head">
                <span class="slib-modal__icon slib-modal__icon--danger"><i data-lucide="x-circle"></i></span>
                <div class="slib-modal__heading">
                    <div class="slib-modal__title" id="libCancelHeading">Cancel this reservation?</div>
                    <div class="slib-modal__sub">The copy goes straight back on the shelf for someone else.</div>
                </div>
                <button type="button" class="slib-modal__x" data-slib-cancel-close aria-label="Close">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="slib-modal__body">
                <div class="slib-book__title" id="libCancelTitle"></div>
                <div class="slib-book__author" id="libCancelWhere"></div>
                <div id="libCancelError" class="slib-modal__error" hidden></div>
            </div>

            <div class="slib-modal__foot">
                <button type="button" class="slib-btn" data-slib-cancel-close>
                    Keep it
                </button>
                <button type="button" id="libCancelGo" class="slib-borrow slib-borrow--danger">
                    <i data-lucide="x-circle"></i>Yes, cancel it
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-library.js')
@endsection
