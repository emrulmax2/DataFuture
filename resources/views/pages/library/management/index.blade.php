@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/library-management.css')
@endsection

@section('subcontent')
    @php
        /* `all` leads because it is the default view — a selected tab sitting
           last reads as an afterthought rather than the state you are in. */
        $tabs = [
            'all' => ['All', 'layers'],
            'requested' => ['Awaiting collection', 'clock'],
            'issued' => ['On loan', 'book-open'],
            'returned' => ['Returned', 'check-circle'],
            'not_collected' => ['Not collected', 'alarm-clock-off'],
            'cancelled' => ['Cancelled', 'ban'],
        ];

        $types = [
            'all' => ['Both types', 'list-filter'],
            'take_home' => ['Take home', 'home'],
            'day_reading' => ['Day reading', 'glasses'],
        ];

        /* Each tile links to the rows it counts. A tile reading 1 beside a list
           showing none is the quickest way to make a working page look broken.

           `tone` reuses the status palette rather than inventing a second one,
           so a tile and the pills it leads to are the same colour. */
        $tiles = [
            ['label' => 'Awaiting collection', 'value' => number_format($counts['requested']), 'icon' => 'clock',
             'tone' => 'amber', 'params' => ['status' => 'requested', 'type' => 'all']],
            ['label' => 'Out on loan', 'value' => number_format($counts['issued']), 'icon' => 'book-open',
             'tone' => 'indigo', 'params' => ['status' => 'issued', 'type' => 'all']],
            ['label' => 'Overdue', 'value' => number_format($counts['overdue']), 'icon' => 'alert-triangle',
             'tone' => 'red', 'params' => ['status' => 'all', 'type' => 'all', 'overdue' => 1]],
            ['label' => 'Day reading out', 'value' => number_format($counts['day_reading']), 'icon' => 'glasses',
             'tone' => 'teal', 'params' => ['status' => 'issued', 'type' => 'day_reading']],
            /* Money, not a count: fines owed over deposits held, on one line and
               colour-coded, so the tile says which way each figure cuts without
               needing a second line to label them. */
            /* The only tile that leaves this screen: the other four filter the
               list below, but money is not a loan status and has nowhere to
               filter to — it gets its own ledger. */
            ['label' => 'Deposit & fines', 'icon' => 'wallet', 'tone' => 'green', 'money' => true,
             'value' => '<b class="lib-stat__fig lib-stat__fig--red">£' . number_format($money['fines'], 2) . '</b>'
                 . '<span class="lib-stat__slash">/</span>'
                 . '<b class="lib-stat__fig lib-stat__fig--green">£' . number_format($money['deposits'], 2) . '</b>',
             'route' => 'library.management.money', 'params' => []],
        ];
    @endphp

    <div class="lib-desk" id="libraryDesk"
         data-status="{{ $status }}"
         data-type="{{ $type }}"
         data-overdue="{{ $overdue ? 1 : 0 }}"
         data-q="{{ $search }}">

        @if(session('library_success'))
            <div class="lib-flash lib-flash--ok" role="status">
                <i data-lucide="check-circle"></i> {{ session('library_success') }}
            </div>
        @endif
        @if(session('library_error'))
            <div class="lib-flash lib-flash--bad" role="alert">
                <i data-lucide="alert-octagon"></i> {{ session('library_error') }}
            </div>
        @endif

        {{-- One bordered grid rather than four separate cards: the hairline
             between cells reads as a single instrument panel, and each cell
             still links to the rows it counts. --}}
        <div class="lib-stats">
            @foreach($tiles as $tile)
                <a href="{{ route($tile['route'] ?? 'library.management', $tile['params']) }}"
                   class="lib-stat lib-stat--{{ $tile['tone'] }}">
                    <span class="lib-stat__top">
                        <span class="lib-stat__label">{{ $tile['label'] }}</span>
                        <span class="lib-stat__icon" aria-hidden="true"><i data-lucide="{{ $tile['icon'] }}"></i></span>
                    </span>

                    {{-- Only the money tile carries markup, and every part of it
                         is built above from number_format output. --}}
                    <span class="lib-stat__value {{ ($tile['money'] ?? false) ? 'lib-stat__value--money' : '' }}">
                        @if($tile['money'] ?? false){!! $tile['value'] !!}@else{{ $tile['value'] }}@endif
                    </span>

                    <span class="lib-stat__foot">
                        View records
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"></path></svg>
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Day reading: the student is at the desk, so there is no reservation
             step — find the book, find the student, hand it over. --}}
        <div class="lib-card lib-card--open lib-panel" id="dayReadingPanel"
             data-catalogue-url="{{ route('library.management.catalogue') }}"
             data-students-url="{{ route('library.management.students') }}">
            {{-- Closed by default: most of a shift is issuing and returning
                 reserved books, and an open form pushes that list down the page. --}}
            <button type="button" class="lib-cardhead lib-panel__head" id="dayReadingToggle"
                    aria-expanded="false" aria-controls="dayReadingBody">
                <span class="lib-cardhead__titles">
                    <span class="lib-cardhead__title">Issue for day reading</span>
                </span>
                <span class="lib-cardhead__spacer lib-cardhead__count">Read in the library &middot; back today &middot; does not use the take-home allowance</span>
                <i data-lucide="chevron-down" class="lib-panel__chevron"></i>
            </button>
            <form method="post" action="{{ route('library.management.day.reading') }}" class="p-5 border-t border-slate-200/60" id="dayReadingBody" hidden>
                @csrf
                {{-- Three steps, revealed in the order the desk works: who is
                     standing there, what they picked up, then hand it over.
                     Showing all three at once invites the book being chosen
                     first, which is the one that has to be re-picked if the
                     student turns out to be barred or already holding it. --}}
                <div class="lib-steps">
                    <div class="lib-step" data-step="1">
                        <div class="lib-step__head">
                            <span class="lib-step__num">1</span>
                            <label class="lib-step__label">Student <span>*</span></label>
                        </div>
                        {{-- Closed it shows the choice; open it is a search box over
                             a list. One control instead of a field, a results list
                             and a chosen card all stacked down the page. --}}
                        <div class="lib-combo" id="drStudentCombo" data-kind="student">
                            <button type="button" class="lib-combo__trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="lib-combo__value lib-combo__value--empty">
                                    <i data-lucide="user"></i>Name or registration number...
                                </span>
                                <i data-lucide="chevron-down" class="lib-combo__caret"></i>
                            </button>
                            <div class="lib-combo__panel" hidden>
                                <div class="lib-combo__search">
                                    <i data-lucide="search"></i>
                                    <input type="text" class="lib-combo__input" autocomplete="off"
                                           placeholder="Type a name or registration number...">
                                </div>
                                <div class="lib-combo__list" role="listbox"></div>
                            </div>
                        </div>
                        <input type="hidden" name="student_id" id="drStudentId">
                    </div>

                    <div class="lib-step" data-step="2" id="drStepBook" hidden>
                        <div class="lib-step__head">
                            <span class="lib-step__num">2</span>
                            <label class="lib-step__label">Book <span>*</span></label>
                        </div>
                        <div class="lib-combo" id="drBookCombo" data-kind="book">
                            <button type="button" class="lib-combo__trigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="lib-combo__value lib-combo__value--empty">
                                    <i data-lucide="book"></i>Title, author, ISBN or barcode...
                                </span>
                                <i data-lucide="chevron-down" class="lib-combo__caret"></i>
                            </button>
                            <div class="lib-combo__panel" hidden>
                                <div class="lib-combo__search">
                                    <i data-lucide="search"></i>
                                    <input type="text" class="lib-combo__input" autocomplete="off"
                                           placeholder="Title, author, ISBN or barcode...">
                                </div>
                                <div class="lib-combo__list" role="listbox"></div>
                            </div>
                        </div>
                        <input type="hidden" name="title_id" id="drTitleId">
                    </div>

                    <div class="lib-step" data-step="3" id="drStepIssue" hidden>
                        <div class="lib-step__head">
                            <span class="lib-step__num">3</span>
                            <label class="lib-step__label" for="drNote">Note <span class="lib-media__sub">(optional)</span></label>
                        </div>
                        {{-- Note and the action share the row: the whole form is one
                             line, and the button sits where the last answer is
                             given rather than under it. --}}
                        <div class="lib-step__row">
                            <input type="text" id="drNote" name="staff_note" class="lib-text" maxlength="255" placeholder="Desk note">
                            <button type="submit" id="drSubmit" class="lib-b lib-b--gold lib-b--lg" disabled>
                                <i data-lucide="check"></i> Issue
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="lib-card lib-panel">
            {{-- Search lives in the head rather than a toolbar row of its own:
                 it acts on this table and nothing else on the page. --}}
            <div class="lib-cardhead lib-cardhead--divided">
                <span class="lib-cardhead__titles">
                    <span class="lib-cardhead__title">Issues &amp; reservations</span>
                    <span class="lib-cardhead__count" id="libRecordCount"></span>
                </span>

                <form method="get" class="lib-cardhead__spacer lib-headsearch">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    @if($overdue)<input type="hidden" name="overdue" value="1">@endif
                    <label class="lib-headsearch__box">
                        <i data-lucide="search"></i>
                        <input type="search" name="q" value="{{ $search }}"
                               placeholder="Reference, book, barcode, student...">
                    </label>
                    @if($search)
                        <a href="{{ route('library.management', ['status' => $status, 'type' => $type]) }}" class="lib-headsearch__clear">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Two segmented controls, not eleven loose chips: status and type are
                 separate single-choice axes, and a shared track says so without
                 needing a divider between them. --}}
            <div class="lib-segs">
                <div class="lib-seg" role="group" aria-label="Filter by status">
                    @foreach($tabs as $key => [$label, $icon])
                        @php $n = $key === 'all' ? array_sum($tabCounts) : ($tabCounts[$key] ?? 0); @endphp
                        @php $on = $status === $key && !$overdue; @endphp
                        <a href="{{ route('library.management', ['status' => $key, 'q' => $search, 'type' => $type]) }}"
                           class="lib-seg__item {{ $on ? 'lib-seg__item--on' : '' }}"
                           @if($on) aria-current="true" @endif>
                            <i data-lucide="{{ $icon }}"></i>{{ $label }}
                            <span class="lib-seg__count">{{ $n }}</span>
                        </a>
                    @endforeach
                </div>

                <div class="lib-seg" role="group" aria-label="Filter by loan type">
                    @foreach($types as $key => [$label, $icon])
                        <a href="{{ route('library.management', ['status' => $status, 'q' => $search, 'type' => $key]) }}"
                           class="lib-seg__item {{ $type === $key ? 'lib-seg__item--on' : '' }}"
                           @if($type === $key) aria-current="true" @endif>
                            <i data-lucide="{{ $icon }}"></i>{{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Not part of either group: this one is a filter you take off
                     again, so it stays a standalone chip. --}}
                @if($overdue)
                    <a href="{{ route('library.management', ['status' => $status, 'type' => $type]) }}"
                       class="lib-filter-btn lib-filter-btn--alert">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-1.5"></i>Overdue only &times;
                    </a>
                @endif
            </div>

            <div class="p-5">
                {{-- Rows are fetched by Tabulator. The action URL is a named
                     route with a placeholder id, so the endpoints are never
                     hand-built in JavaScript. --}}
                <div id="libraryIssuesTable" class="lib-grid lib-tabulator"
                     data-url="{{ route('library.management.list') }}"
                     data-issue-url="{{ route('library.management.issue', ['id' => '__ID__']) }}"
                     data-return-url="{{ route('library.management.return', ['id' => '__ID__']) }}"
                     data-cancel-url="{{ route('library.management.cancel', ['id' => '__ID__']) }}"></div>
            </div>
        </div>
    </div>
    {{-- Returning settles any charge and puts the copy back, so it is confirmed
         in the same place the charge is shown rather than in a browser box that
         cannot show one. --}}
    <div class="lib-modal" id="libReturnModal" role="dialog" aria-modal="true" aria-labelledby="libReturnTitle" hidden>
        <div class="lib-modal__box lib-modal__box--sm">
            <form method="post" id="libReturnForm">
                @csrf
                <div class="lib-modal__head">
                    <span class="lib-modal__icon"><i data-lucide="corner-down-left"></i></span>
                    <div class="lib-modal__heading">
                        <div class="lib-modal__title" id="libReturnTitle">Mark as returned?</div>
                        <div class="lib-modal__sub" id="libReturnRef"></div>
                    </div>
                    <button type="button" class="lib-modal__x" data-lib-close aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>

                <div class="lib-modal__body">
                    <div class="lib-return__book">
                        <div class="lib-picked__name" id="libReturnBook"></div>
                        <div class="lib-picked__line" id="libReturnStudent"></div>
                    </div>

                    {{-- Only shown when something is owed. The desk has to collect
                         it before the book goes back on the shelf, so it is stated
                         here rather than discovered afterwards. --}}
                    <div class="lib-return__charge" id="libReturnCharge" hidden>
                        <i data-lucide="receipt"></i>
                        <span>Outstanding charge <strong id="libReturnFine"></strong> — collect before returning.</span>
                    </div>

                    <label class="lib-field__label" for="libReturnNote">
                        Note <span class="lib-media__sub">(optional)</span>
                    </label>
                    <input type="text" id="libReturnNote" name="staff_note" class="lib-input"
                           maxlength="255" autocomplete="off" placeholder="Damage, part return, anything worth recording">
                </div>

                <div class="lib-modal__foot">
                    <button type="button" class="lib-b lib-b--quiet" data-lib-close>Not yet</button>
                    <button type="submit" class="lib-b lib-b--gold" id="libReturnGo">
                        <i data-lucide="check"></i>Confirm return
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Cancelling frees the copy and the student is told why, so the reason is
         required rather than optional: "Cancelled at the desk" tells them
         nothing when they come back asking. --}}
    <div class="lib-modal" id="libCancelModal" role="dialog" aria-modal="true" aria-labelledby="libCancelTitle" hidden>
        <div class="lib-modal__box lib-modal__box--sm">
            <form method="post" id="libCancelForm">
                @csrf
                <div class="lib-modal__head">
                    <span class="lib-modal__icon lib-modal__icon--danger"><i data-lucide="x-circle"></i></span>
                    <div class="lib-modal__heading">
                        <div class="lib-modal__title" id="libCancelTitle">Cancel this reservation?</div>
                        <div class="lib-modal__sub" id="libCancelRef"></div>
                    </div>
                    <button type="button" class="lib-modal__x" data-lib-close aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>

                <div class="lib-modal__body">
                    <p class="lib-cancel__lead">
                        The copy goes back on the shelf straight away, and
                        <strong id="libCancelStudent">the student</strong> will see this reason in their portal.
                    </p>

                    <label class="lib-field__label" for="libCancelReason">
                        Reason <span class="lib-field__req">*</span>
                    </label>

                    {{-- Common reasons as one click, with free text underneath —
                         a desk mid-queue should not have to type a sentence. --}}
                    <div class="lib-chips" id="libCancelChips">
                        @foreach([
                            'Not collected in time',
                            'Student asked to cancel',
                            'Copy damaged or withdrawn',
                            'Reserved in error',
                            'Needed for a class',
                        ] as $reason)
                            <button type="button" class="lib-chip" data-reason="{{ $reason }}">{{ $reason }}</button>
                        @endforeach
                    </div>

                    <input type="text" id="libCancelReason" name="cancel_reason" class="lib-input"
                           maxlength="191" autocomplete="off"
                           placeholder="Pick one above, or type your own reason">

                    <div class="lib-modal__error" id="libCancelError" hidden></div>
                </div>

                <div class="lib-modal__foot">
                    <button type="button" class="lib-b lib-b--quiet" data-lib-close>Keep it</button>
                    <button type="submit" class="lib-b lib-b--red" id="libCancelGo" disabled>
                        <i data-lucide="x-circle"></i>Cancel reservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- History dialog. Populated from the grid row, which already carries the
         book, the student and the full trail — no second request. --}}
    <div class="lib-modal" id="libHistoryModal" role="dialog" aria-modal="true" aria-labelledby="libHistoryTitle" hidden>
        <div class="lib-modal__box">
            <div class="lib-modal__head">
                <span class="lib-modal__icon"><i data-lucide="history" class="w-5 h-5"></i></span>
                <div class="lib-modal__heading">
                    <div class="lib-modal__title" id="libHistoryTitle"></div>
                    <div class="lib-modal__ref" id="libHistoryRef"></div>
                </div>
                <button type="button" class="lib-modal__x" data-lib-close aria-label="Close">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="lib-modal__body">
                <div class="lib-parties">
                    <div class="lib-party" id="libHistoryBook"></div>
                    <div class="lib-party" id="libHistoryStudent"></div>
                </div>

                <div class="lib-section-title">History</div>
                <ol class="lib-timeline" id="libHistoryTrail"></ol>
            </div>
            <div class="lib-modal__foot">
                <button type="button" class="lib-b lib-b--redline" data-lib-close>
                    <i data-lucide="x" class="w-4 h-4 mr-1.5"></i>Close
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/library-management.js')
@endsection
