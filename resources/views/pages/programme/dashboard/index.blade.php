@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
@php

    // One "view all" link per card, as designed: it carries the total and lists
    // every term behind it, pointing at the most recent one.
    $pgdTermNames = $terms->pluck('name')->implode(', ');
    $pgdPrimaryTermId = $terms->max('id');
@endphp

<main class="pgd-main">
    <div class="pgd-col">

        <div class="pgd-pagehead">
            <div>
                <h1 class="pgd-pagehead__title">Daily class information</h1>
                <p class="pgd-pagehead__sub" data-pgd-summary>{{ $summaryLine }}</p>
            </div>
            <div class="pgd-pagehead__actions">
                @if(isset(auth()->user()->priv()['reports']) && auth()->user()->priv()['reports'] == 1)
                    <a href="{{ route('reports') }}" class="pgd-btn pgd-btn--ghost">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v5h5"></path><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2Z"></path><path d="M9 13h6"></path><path d="M9 17h4"></path></svg>
                        Reports
                    </a>
                @endif
            </div>
        </div>

        <div class="pgd-tiles">
            <button type="button" class="pgd-tile pgd-tile--amber" data-pgd-quick="unknown" {{ $stats['not_started'] === 0 ? 'disabled' : '' }}>
                <span class="pgd-tile__label"><span></span>Not started</span>
                <span class="pgd-tile__value" data-pgd-stat="not_started">{{ $stats['not_started'] }}</span>
                <span class="pgd-tile__note">classes past start time</span>
            </button>
            <button type="button" class="pgd-tile pgd-tile--red" data-pgd-quick="nofeed" {{ $stats['no_attendance'] === 0 ? 'disabled' : '' }}>
                <span class="pgd-tile__label"><span></span>Attendance missing</span>
                <span class="pgd-tile__value" data-pgd-stat="no_attendance">{{ $stats['no_attendance'] }}</span>
                <span class="pgd-tile__note">finished, not fed</span>
            </button>
            <button type="button" class="pgd-tile" data-pgd-open="followup">
                <span class="pgd-tile__label"><span style="background: #2AA9C4;"></span>Tutors below 50%</span>
                <span class="pgd-tile__value">{{ count($lowTutors) }}</span>
                <span class="pgd-tile__note">across all open terms</span>
            </button>
            <button type="button" class="pgd-tile" data-pgd-scroll="#pgd-absence">
                <span class="pgd-tile__label"><span style="background: #6A7B84;"></span>Staff absent</span>
                <span class="pgd-tile__value">{{ count($absentToday) }}</span>
                <span class="pgd-tile__note">{{ $absentCover }}</span>
            </button>
        </div>

        <div class="pgd-filters">
            <div class="pgd-filters__row">
                <select class="pgd-select pgd-filters__status" name="plan_status" id="planClassStatus">
                    <option value="All">All statuses</option>
                    <option value="Scheduled">Scheduled &amp; starting shortly</option>
                    <option value="Ongoing">Ongoing</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Cancelled</option>
                    <option value="Unknown">Not started</option>
                </select>

                <select class="pgd-select pgd-filters__course" name="course_id" id="planCourseId">
                    <option value="0">All courses</option>
                    @foreach($courses as $cr)
                        <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                    @endforeach
                </select>

                <div class="pgd-pop" data-pgd-pop="modules">
                    <button type="button" class="pgd-pop__trigger" data-pgd-pop-toggle>
                        <span data-pgd-pop-label>All modules</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                    </button>
                    <input type="hidden" name="module_creation_id" id="planModuleCreationId" value="0">
                    <div class="pgd-pop__panel pgd-pop__panel--wide">
                        <input type="text" class="pgd-pop__search" placeholder="Search here…" data-pgd-pop-search>
                        <div class="pgd-pop__list" data-pgd-pop-list>
                            <button type="button" class="pgd-pop__opt is-on" data-value="0"><span>All modules</span><em>{{ collect($modules)->sum('count') }}</em></button>
                            @foreach($modules as $mds)
                                <button type="button" class="pgd-pop__opt" data-value="{{ $mds['id'] }}"><span>{{ $mds['name'] }}</span><em>{{ $mds['count'] }}</em></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pgd-pop" data-pgd-pop="groups">
                    <button type="button" class="pgd-pop__trigger" data-pgd-pop-toggle>
                        <span data-pgd-pop-label>All groups</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                    </button>
                    <input type="hidden" name="group_id" id="planGroupId" value="0">
                    <div class="pgd-pop__panel">
                        <input type="text" class="pgd-pop__search" placeholder="Search here…" data-pgd-pop-search>
                        <div class="pgd-pop__list" data-pgd-pop-list>
                            <button type="button" class="pgd-pop__opt is-on" data-value="0"><span>All groups</span><em>{{ collect($groups)->sum('count') }}</em></button>
                            @foreach($groups as $gr)
                                <button type="button" class="pgd-pop__opt" data-value="{{ $gr['id'] }}"><span>{{ $gr['name'] }}</span><em>{{ $gr['count'] }}</em></button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="pgd-pop pgd-pop--cal" data-pgd-pop="calendar">
                    <button type="button" class="pgd-pop__trigger pgd-pop__trigger--cal" data-pgd-pop-toggle>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#65767E" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4M16 3v4M3 10h18"></path></svg>
                        <span data-pgd-cal-label>{{ $theDate }}</span>
                    </button>
                    <input type="hidden" name="class_date" id="theClassDate" value="{{ $theDate }}" data-iso="{{ $theDateIso }}">
                    <div class="pgd-pop__panel pgd-cal" data-pgd-cal>
                        <div class="pgd-cal__head">
                            <button type="button" data-pgd-cal-prev aria-label="Previous month">‹</button>
                            <div data-pgd-cal-title></div>
                            <button type="button" data-pgd-cal-next aria-label="Next month">›</button>
                        </div>
                        <div class="pgd-cal__dow">
                            <span>MON</span><span>TUE</span><span>WED</span><span>THU</span><span>FRI</span><span>SAT</span><span>SUN</span>
                        </div>
                        <div class="pgd-cal__grid" data-pgd-cal-grid></div>
                    </div>
                </div>

                <label class="pgd-searchbox">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#59696F" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"></circle><path d="M16 16l4 4"></path></svg>
                    <input type="text" placeholder="Search classes — module, tutor, group, room or phone" data-pgd-rowsearch>
                    <span class="pgd-searchbox__tail" data-pgd-rowsearch-tail hidden>
                        <span data-pgd-matchlabel></span>
                        <button type="button" data-pgd-rowsearch-clear aria-label="Clear">✕</button>
                    </span>
                </label>
                <button type="button" class="pgd-btn pgd-btn--ghost pgd-btn--sm" data-pgd-reset>Reset all</button>
            </div>

            <div class="pgd-quick">
                <span class="pgd-quick__label">Quick views</span>
                <button type="button" class="pgd-quick__chip" data-pgd-quick="needs" style="--pgd-chip-dot: #C9922B;" {{ $stats['needs_action'] === 0 ? 'disabled' : '' }}>
                    <span></span>Needs action <em data-pgd-stat="needs_action">{{ $stats['needs_action'] }}</em>
                </button>
                <button type="button" class="pgd-quick__chip" data-pgd-quick="ongoing" style="--pgd-chip-dot: #1B7F5A;" {{ $stats['ongoing'] === 0 ? 'disabled' : '' }}>
                    <span></span>Ongoing now <em data-pgd-stat="ongoing">{{ $stats['ongoing'] }}</em>
                </button>
                <button type="button" class="pgd-quick__chip" data-pgd-quick="nofeed" style="--pgd-chip-dot: #B3261E;" {{ $stats['no_attendance'] === 0 ? 'disabled' : '' }}>
                    <span></span>Attendance not fed <em data-pgd-stat="no_attendance">{{ $stats['no_attendance'] }}</em>
                </button>
                <button type="button" class="pgd-quick__chip" data-pgd-quick="online" style="--pgd-chip-dot: #2A8FA8;" {{ $stats['online'] === 0 ? 'disabled' : '' }}>
                    <span></span>Online <em data-pgd-stat="online">{{ $stats['online'] }}</em>
                </button>
                <button type="button" class="pgd-quick__chip" data-pgd-quick="campus" style="--pgd-chip-dot: #5C2E7E;" {{ $stats['campus'] === 0 ? 'disabled' : '' }}>
                    <span></span>On campus <em data-pgd-stat="campus">{{ $stats['campus'] }}</em>
                </button>
            </div>
        </div>

        <div class="pgd-filterbar" data-pgd-filterbar hidden>
            <strong data-pgd-filterlabel></strong>
            <span><span data-pgd-visible>0</span> of <span data-pgd-total>{{ $stats['total'] }}</span> classes shown</span>
            <button type="button" data-pgd-reset>Clear</button>
        </div>

        <div class="pgd-slots" data-pgd-slots>
            <div class="pgd-loader" data-pgd-loader>
                <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#0E5A61">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </div>
            <div data-pgd-slots-body>
                @include('pages.programme.dashboard.partials.slots', ['slots' => $slots, 'stats' => $stats])
            </div>
        </div>

        <div class="pgd-staff">
            <section class="pgd-card pgd-staffbox">
                <div class="pgd-card__head">
                    <h2>Tutors <span class="tutorCount">{{ $classTutor['count'] }}</span></h2>
                    <span class="pgd-card__note">most modules first</span>
                </div>
                <div class="pgd-staffbox__body tutorWrap">
                    <div class="theHolder">
                        @include('pages.programme.dashboard.partials.staff-people', ['people' => $classTutor['people'], 'perTerm' => $classTutor['per_term'], 'kind' => 'tutor'])
                    </div>
                </div>
            </section>

            <section class="pgd-card pgd-staffbox">
                <div class="pgd-card__head">
                    <h2>Personal Tutors <span class="personalTutorCount">{{ $classPTutor['count'] }}</span></h2>
                    <span class="pgd-card__note">most modules first</span>
                </div>
                <div class="pgd-staffbox__body personalTutorWrap">
                    <div class="theHolder">
                        @include('pages.programme.dashboard.partials.staff-people', ['people' => $classPTutor['people'], 'perTerm' => $classPTutor['per_term'], 'kind' => 'personal'])
                    </div>
                </div>
            </section>
        </div>
    </div>

    <aside class="pgd-aside">
        <section class="pgd-card pgd-attendance" data-pgd-attendance>
            @include('pages.programme.dashboard.partials.attendance', ['termAttendanceRates' => $termAttendanceRates])
        </section>

        <section class="pgd-card pgd-absence" id="pgd-absence">
            <div class="pgd-card__head">
                <h2>Staff absence today</h2>
                <span class="pgd-absence__count">{{ count($absentToday) }}</span>
            </div>
            <p class="pgd-absence__cover">{{ $absentCover }}</p>

            <label class="pgd-absence__search">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#65767E" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"></circle><path d="M16 16l4 4"></path></svg>
                <input type="text" placeholder="Find staff member" data-pgd-absence-search>
            </label>

            @php
                $pgdAbsencePage = \App\Http\Controllers\Programme\DashboardController::ABSENCE_PAGE_SIZE;
                $pgdAbsenceTotal = count($absentToday);
                $pgdAbsenceFirst = array_slice($absentToday, 0, $pgdAbsencePage, true);
            @endphp

            {{-- Rows stream in a page at a time as the list is scrolled; the
                 "View all" button below pulls the remainder in one go. --}}
            <div class="pgd-absence__list"
                 data-pgd-absence-list
                 data-page="1"
                 data-total="{{ $pgdAbsenceTotal }}"
                 data-loaded="{{ count($pgdAbsenceFirst) }}"
                 data-has-more="{{ count($pgdAbsenceFirst) < $pgdAbsenceTotal ? '1' : '0' }}">
                @if($pgdAbsenceTotal === 0)
                    <div class="pgd-note pgd-note--ok" data-pgd-absence-empty>
                        <span class="pgd-note__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m8.5 12.5 2.5 2.5 4.5-5"></path></svg>
                        </span>
                        <span>
                            <strong>Nobody absent</strong>
                            Everyone expected on site today has clocked in.
                        </span>
                    </div>
                @else
                    @include('pages.programme.dashboard.partials.absence-rows', ['absents' => $pgdAbsenceFirst, 'showEmpty' => false])
                @endif
                <div class="pgd-absence__more" data-pgd-absence-spinner hidden>Loading…</div>
            </div>

            {{-- One button, as designed: it leaves for the full live-attendance
                 screen. Browsing within the card is the infinite scroll above. --}}
            <a href="{{ route('hr.portal.live.attedance') }}"
               class="pgd-btn pgd-btn--ghost pgd-btn--block"
               data-pgd-absence-toggle
               data-total="{{ $pgdAbsenceTotal }}"
               {{ $pgdAbsenceTotal === 0 ? 'hidden' : '' }}>View all {{ $pgdAbsenceTotal }}</a>
        </section>
    </aside>
</main>

{{-- ============================ MODALS ============================ --}}

<!-- BEGIN: Swap tutor -->
<div class="pgd-modal" data-pgd-modal="swap" hidden>
    <div class="pgd-modal__scrim" data-pgd-modal-close></div>
    <form method="POST" action="#" id="proxyClassForm" class="pgd-modal__box pgd-modal__box--md">
        <div class="pgd-modal__head">
            <div>
                <div class="pgd-modal__eyebrow">Swap tutor</div>
                <h2 data-pgd-active-module></h2>
                <div class="pgd-modal__meta">
                    <span class="pgd-group" data-pgd-active-group></span>
                    <span data-pgd-active-meta></span>
                </div>
            </div>
            <button type="button" class="pgd-modal__x" data-pgd-modal-close aria-label="Close">✕</button>
        </div>

        <div class="pgd-swap__from">
            <span class="pgd-avatar" data-pgd-swap-initials></span>
            <span>
                <span class="pgd-swap__label">Currently assigned</span>
                <span class="pgd-swap__name" data-pgd-swap-name></span>
            </span>
            <span class="pgd-swap__status" data-pgd-swap-status></span>
        </div>

        <div class="pgd-swap__search">
            <label class="pgd-searchbox">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#59696F" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"></circle><path d="M16 16l4 4"></path></svg>
                <input type="text" placeholder="Search staff to cover this class" data-pgd-swap-search>
            </label>
        </div>

        <div class="pgd-swap__list" data-pgd-swap-list></div>

        <div class="pgd-swap__reason">
            <label for="proxy_reason">Reason <span>*</span></label>
            <textarea id="proxy_reason" name="proxy_reason" rows="2" placeholder="Why is this class being covered?"></textarea>
            <div class="pgd-error error-proxy_reason"></div>
            <div class="pgd-error error-proxy_tutor_id"></div>
        </div>

        <div class="pgd-modal__foot">
            <span class="pgd-modal__hint" data-pgd-swap-footer>Pick a staff member to cover this class.</span>
            <span class="pgd-modal__buttons">
                <button type="button" class="pgd-btn pgd-btn--ghost" data-pgd-modal-close>Cancel</button>
                <button type="submit" id="saveReAsignBtn" class="pgd-btn pgd-btn--primary" disabled>
                    Confirm swap
                    <svg class="pgd-btn__spin" style="display:none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                        <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                    </svg>
                </button>
            </span>
        </div>

        <input type="hidden" name="proxy_tutor_id" id="proxy_tutor_id" value="">
        <input type="hidden" name="plan_id" value="0">
        <input type="hidden" name="plans_date_list_id" value="0">
        <input type="hidden" name="org_tutor_id" value="0">
    </form>
</div>
<!-- END: Swap tutor -->

<!-- BEGIN: End class -->
<div class="pgd-modal" data-pgd-modal="end" hidden>
    <div class="pgd-modal__scrim" data-pgd-modal-close></div>
    <form method="POST" action="#" id="endClassModalForm" class="pgd-modal__box pgd-modal__box--sm pgd-modal__box--center">
        <div class="pgd-confirm__icon pgd-confirm__icon--danger">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#B3261E" stroke-width="2.2" stroke-linecap="round"><path d="m6 6 12 12"></path><path d="m18 6-12 12"></path></svg>
        </div>
        <h2 class="pgd-confirm__title">End Now?</h2>
        <p class="pgd-confirm__body">Do you want to end this class?</p>
        <p class="pgd-confirm__meta"><span data-pgd-active-group></span> · <span data-pgd-active-meta></span></p>
        <div class="pgd-confirm__actions">
            <button type="button" class="pgd-btn pgd-btn--ghost" data-pgd-modal-close>No, keep it running</button>
            <button type="submit" id="endClassBtn" class="pgd-btn pgd-btn--danger">
                Yes, I do
                <svg class="pgd-btn__spin" style="display:none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                    <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                </svg>
            </button>
        </div>
        <input type="hidden" name="plan_date_list_id" class="plan_date_list_id" value="0">
        <input type="hidden" name="attendance_information_id" class="attendance_information_id" value="0">
    </form>
</div>
<!-- END: End class -->

<!-- BEGIN: Cancel class -->
<div class="pgd-modal" data-pgd-modal="cancel" hidden>
    <div class="pgd-modal__scrim" data-pgd-modal-close></div>
    <form method="POST" action="#" id="cancelClassForm" class="pgd-modal__box pgd-modal__box--sm">
        <div class="pgd-modal__head pgd-modal__head--tight">
            <h2>Cancel Class</h2>
            <button type="button" class="pgd-modal__x" data-pgd-modal-close aria-label="Close">✕</button>
        </div>
        <div class="pgd-modal__body">
            <div class="pgd-modal__strip">
                <span class="pgd-group" data-pgd-active-group></span>
                <span data-pgd-active-meta></span>
            </div>

            <label class="pgd-label" for="canceled_reason">Reason <span>*</span></label>
            <textarea id="canceled_reason" name="canceled_reason" rows="4" class="pgd-textarea" placeholder="Why is this class being cancelled?"></textarea>
            <div class="pgd-error error-canceled_reason"></div>

            <div class="pgd-label pgd-label--mt">Send Notifications</div>
            <div class="pgd-checks">
                <label class="pgd-check">
                    <input type="checkbox" id="notify_student" name="notify_student" value="1" checked>
                    <span class="pgd-check__box"></span>Notify Students
                </label>
                <label class="pgd-check">
                    <input type="checkbox" id="notify_tutors" name="notify_tutors" value="1" checked>
                    <span class="pgd-check__box"></span>Notify Tutors
                </label>
            </div>
        </div>
        <div class="pgd-modal__foot pgd-modal__foot--end">
            <button type="button" class="pgd-btn pgd-btn--ghost" data-pgd-modal-close>Close</button>
            <button type="submit" id="saveCancelBtn" class="pgd-btn pgd-btn--danger">
                Cancel Class
                <svg class="pgd-btn__spin" style="display:none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#fff">
                    <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                </svg>
            </button>
        </div>
        <input type="hidden" name="plan_id" value="0">
        <input type="hidden" name="plans_date_list_id" value="0">
    </form>
</div>
<!-- END: Cancel class -->

<!-- BEGIN: Follow-up queue -->
<div class="pgd-modal" data-pgd-modal="followup" hidden>
    <div class="pgd-modal__scrim" data-pgd-modal-close></div>
    <div class="pgd-modal__box pgd-modal__box--lg">
        <div class="pgd-modal__head">
            <div>
                <div class="pgd-modal__eyebrow">Follow-up queue</div>
                <h2>Tutors below the {{ \App\Http\Controllers\Programme\DashboardController::FOLLOW_UP_THRESHOLD }}% attendance threshold</h2>
                <div class="pgd-modal__meta"><span>All open terms · ordered by students affected</span></div>
            </div>
            <button type="button" class="pgd-modal__x" data-pgd-modal-close aria-label="Close">✕</button>
        </div>
        <div class="pgd-modal__body pgd-modal__body--scroll">
            <div class="pgd-followup__head">
                <span>Tutor</span>
                <span>Students</span>
                <span>Load</span>
                <span>Attendance</span>
                <span></span>
            </div>
            @forelse($lowTutors as $lt)
                <div class="pgd-followup__row">
                    <span class="pgd-followup__who">
                        <span class="pgd-avatar pgd-avatar--sm" style="background: {{ $lt['color'] }};">
                            @if(!empty($lt['photo']))<img src="{{ $lt['photo'] }}" alt="{{ $lt['name'] }}">@else{{ $lt['initials'] }}@endif
                        </span>
                        <span>
                            <span class="pgd-followup__name">{{ $lt['name'] }}</span>
                            <span class="pgd-followup__role">{{ $lt['role'] }}</span>
                        </span>
                    </span>
                    <span class="pgd-followup__num">{{ $lt['students'] }}</span>
                    <span class="pgd-followup__num is-muted">{{ $lt['load'] }}</span>
                    <span class="pgd-followup__rate">{{ $lt['rate'] }}</span>
                    <span class="pgd-followup__act">
                        <button type="button" class="pgd-btn pgd-btn--ghost pgd-btn--xs" data-pgd-remind="{{ $lt['id'] }}">Remind</button>
                    </span>
                </div>
            @empty
                <div class="pgd-followup__empty">No tutor is below the threshold in the open terms.</div>
            @endforelse
        </div>
        <div class="pgd-modal__foot">
            <span class="pgd-modal__hint">Reminders are emailed to each tutor's work address.</span>
            @if(count($lowTutors) > 0)
                <button type="button" class="pgd-btn pgd-btn--primary" data-pgd-remind-all>Remind all</button>
            @endif
        </div>
    </div>
</div>
<!-- END: Follow-up queue -->


<div class="pgd-toast" data-pgd-toast hidden><span></span><em></em></div>

<script type="application/json" data-pgd-config>
{!! json_encode([
    'routes' => [
        'classInfo' => route('programme.dashboard.class.info'),
        'cancel' => route('programme.dashboard.cancel.class'),
        'end' => route('programme.dashboard.end.class'),
        'reassign' => route('programme.dashboard.reassign.class'),
        'remind' => route('programme.dashboard.remind.tutor'),
        'absentRows' => route('programme.dashboard.absent.rows'),
    ],
    'absencePageSize' => \App\Http\Controllers\Programme\DashboardController::ABSENCE_PAGE_SIZE,
    'candidates' => $swapCandidates,
    'busyByTime' => $busyByTime,
    'absentUserIds' => $absentUserIds,
    'date' => $theDateIso,
]) !!}
</script>
@endsection
