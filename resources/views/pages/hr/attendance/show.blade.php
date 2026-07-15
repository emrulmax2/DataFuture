@extends('../layout/' . $layout)

{{-- The portal topbar stays as-is; the attendance surface below it follows the
     standalone 1C redesign. --}}
@section('body_class', 'hr-attendance-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $reviewedPct = $counts['all'] > 0 ? round($counts['reviewed'] / $counts['all'] * 100) : 0;
        $tabs = [
            ['key' => 'all',      'label' => 'All',             'count' => $counts['all'],      'icon' => 'list'],
            ['key' => 'absents',  'label' => 'Absents',         'count' => $counts['absents'],  'icon' => 'user-x'],
            ['key' => 'noissues', 'label' => 'No issues',       'count' => $counts['noissues'], 'icon' => 'check-circle'],
            ['key' => 'issues',   'label' => 'Issues',          'count' => $counts['issues'],   'icon' => 'alert-triangle'],
            ['key' => 'overtime', 'label' => 'Not in schedule', 'count' => $counts['overtime'], 'icon' => 'clock'],
        ];
    @endphp

    <div class="att-page" data-date="{{ date('Y-m-d', $date) }}">

        {{-- Which day is on screen, and the way back to the month. The box below is
             about the decisions; this is about the day. --}}
        <div class="att-header">
            <div class="att-header__lead">
                <div class="att-header__eyebrow">Daily attendance</div>
                <h2 class="att-header__title">
                    Attendances of <span class="att-header__date">{{ $theDate }}</span>
                </h2>
            </div>

            <a href="{{ route('hr.attendance') }}" class="att-header__back att-btn att-btn--outline">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                Back to Attendance
            </a>
        </div>

        <div class="att-shell">

            {{-- Which day, what is left to do, and the two ways to clear the easy part
                 of it. Branding stays in the portal topbar. --}}
            <div class="att-summary">
                <div class="att-summary__lead">
                    <div class="att-summary__eyebrow">Timeline compare</div>
                    <div class="att-summary__headline">
                        <span id="attPendingHeadline">{{ $counts['pending'] }}</span> of <span id="attSummaryTotal">{{ $counts['all'] }}</span> still need a decision
                    </div>
                </div>

                <div class="att-summary__progress">
                    <div class="att-progress">
                        <div class="att-progress__fill" id="attProgressFill" style="width: {{ $reviewedPct }}%;"></div>
                    </div>
                    <span class="att-progress__label" id="attProgressLabel">
                        {{ $counts['reviewed'] }} of {{ $counts['all'] }} reviewed
                    </span>
                </div>

                <div class="att-summary__actions">
                    {{-- Accept-all is scoped to the No issues tab: the JS only reveals it there,
                         its count is the no-issue rows, and it copies their clocked punches into
                         the record. Hidden by default; refreshBulkAction() toggles it. --}}
                    <button type="button" id="attAcceptAll" class="att-btn att-btn--solid" style="display:none;"
                            title="On the No issues tab, copy each remaining row's clocked punch into its recorded time, then approve it.">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Accept all clocked ( <span id="attNoIssuesCount">{{ $counts['noissues'] }}</span> )
                    </button>
                </div>
            </div>

            <div class="att-tabs">
                @foreach($tabs as $tab)
                    <button type="button" class="att-tab att-tab--{{ $tab['key'] }} {{ $tab['key'] === 'all' ? 'is-active' : '' }}" data-filter="{{ $tab['key'] }}">
                        <i data-lucide="{{ $tab['icon'] }}" class="w-4 h-4"></i>
                        {{ $tab['label'] }}<span class="att-tab__count" data-count-for="{{ $tab['key'] }}">{{ $tab['count'] }}</span>
                    </button>
                @endforeach

                <div class="att-legend">
                    <span><i class="att-legend__sched"></i> Rostered shift</span>
                    <span><i class="att-legend__clock"></i> Actually worked</span>
                </div>
            </div>

            {{-- The cell wrapper mirrors .att-row__mid exactly, so the scale and the
                 bars below it resolve to the same box and a tick really does sit over
                 the hour it labels. --}}
            <div class="att-clockhead">
                <span></span>
                <div class="att-clockhead__cell">
                    <div class="att-clockhead__track">
                        @foreach([8, 10, 12, 14, 16, 18, 20] as $hour)
                            <span style="left: {{ round((($hour * 60) - 420) / 840 * 100, 3) }}%;">{{ sprintf('%02d', $hour) }}</span>
                        @endforeach
                    </div>
                </div>
                <span></span>
            </div>

            <div class="att-rows" id="attRows">
                @forelse($rows as $row)
                    @include('pages.hr.attendance.partials.row', ['row' => $row])
                @empty
                    <div class="att-empty">
                        <i data-lucide="calendar-off" class="w-6 h-6"></i>
                        <p>Nothing has been synchronised for {{ $theDate }} yet.</p>
                    </div>
                @endforelse

                <div class="att-empty" id="attNoMatch" style="display: none;">
                    <i data-lucide="filter-x" class="w-6 h-6"></i>
                    <p>No rows match this filter.</p>
                </div>
            </div>
        </div>

        {{-- The open row's editor is moved in here, so only one fixed panel exists. --}}
        <div class="att-backdrop" id="attBackdrop"></div>
        <aside class="att-drawer" id="attDrawer" aria-hidden="true"></aside>

    {{--
        The modals live inside .att-page on purpose. The theme's .modal carries no
        z-index, so it would paint behind the drawer - and the break, re-sync and
        warning modals are all opened FROM the drawer. Nesting them here lets one
        scoped rule (.att-page .modal) lift them above it. They are position:fixed,
        so nesting changes nothing about how they lay out.
    --}}

    <!-- BEGIN: Attendance Break Modal -->
    {{-- att-page rides on the modal itself: the theme's Modal JS moves it to <body>
         on show, so the scope + design tokens must travel with it, not sit on an
         ancestor it gets torn away from. --}}
    <div id="viewBreakModal" class="modal att-modal att-page" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="viewBreakForm" enctype="multipart/form-data">
                <div class="modal-content att-modal__content">
                    <div class="att-modal__head">
                        <div class="att-modal__heading">
                            <div class="att-modal__eyebrow">Attendance</div>
                            <div class="att-modal__title">Break times</div>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="att-modal__close" aria-label="Close">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    </div>

                    {{-- The break table is injected here by hr.attendance.edit. --}}
                    <div class="modal-body att-modal__body"></div>

                    <div class="att-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="att-btn att-btn--outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            Cancel
                        </button>
                        <button type="submit" id="updateBreak" class="att-btn att-btn--solid">
                            <i data-lucide="check" class="w-4 h-4"></i> Update breaks
                            <svg class="att-spin" style="display:none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Attendance Break Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal att-modal att-page" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="att-btn att-btn--solid successCloser">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Ok
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal att-modal att-page" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="att-btn att-btn--solid">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Ok
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Confirm Modal Content -->
    <div id="confirmModal" data-tw-backdrop="static" class="modal att-modal att-page" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="att-btn att-btn--outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-date="" data-action="" class="agreeWith att-btn att-btn--danger">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Confirm Modal Content -->
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-deaily-attedance.js')
@endsection
