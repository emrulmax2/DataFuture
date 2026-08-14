{{--
    The time-slot accordions on the Programme Dashboard.

    Rendered both on first paint and by the class-info XHR, so the filter
    round-trip and the initial page agree exactly. Every row carries the data-*
    attributes the browser-side filters (free-text search, term pills and the
    quick-view chips) read, so those never need a round-trip.
--}}
@php
    $pgdStatusMeta = [
        'scheduled' => ['label' => 'Scheduled', 'class' => 'is-scheduled'],
        'shortly' => ['label' => 'Starting shortly', 'class' => 'is-shortly'],
        'ongoing' => ['label' => 'Ongoing', 'class' => 'is-ongoing'],
        'completed' => ['label' => 'Completed', 'class' => 'is-completed'],
        'notstarted' => ['label' => 'Not Started', 'class' => 'is-notstarted'],
        'cancelled' => ['label' => 'Cancelled', 'class' => 'is-cancelled'],
    ];
@endphp

@forelse($slots as $slot)
    <section class="pgd-slot" data-pgd-slot>
        <button type="button" class="pgd-slot__head" data-pgd-slot-toggle>
            <span class="pgd-slot__time">
                <svg class="pgd-slot__caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                {{ $slot['time'] }}
            </span>
            <span class="pgd-slot__meta">
                <span>{{ count($slot['rows']) }} {{ count($slot['rows']) == 1 ? 'class' : 'classes' }}</span>
                <span class="pgd-slot__rule"></span>
                <span>{{ count($slot['rooms']) }} {{ count($slot['rooms']) == 1 ? 'location' : 'locations' }}</span>
            </span>
            <span class="pgd-slot__flags">
                {{-- Always rendered so browser-side filtering can reveal it
                     when the visible rows change. --}}
                <span class="pgd-slot__alert" {{ $slot['alerts'] > 0 ? '' : 'hidden' }}>{{ $slot['alerts'] }} not started</span>
                <span class="pgd-slot__count">{{ $slot['finished'] }}/{{ count($slot['rows']) }} finished</span>
            </span>
        </button>

        <div class="pgd-slot__body">
            <div class="pgd-row pgd-row--head">
                <span>Module</span>
                <span>Group</span>
                <span>Tutor</span>
                <span>Room</span>
                <span>Status</span>
                <span></span>
            </div>

            @foreach($slot['rows'] as $row)
                <div class="pgd-row {{ $row['needs_attendance'] ? 'pgd-row--needs' : ($row['status'] == 'notstarted' ? 'pgd-row--late' : '') }}"
                     data-pgd-row
                     data-term="{{ $row['term_id'] }}"
                     data-kind="{{ $row['status'] }}"
                     data-fed="{{ $row['fed'] ? '1' : '0' }}"
                     data-needs="{{ $row['needs_attendance'] ? '1' : '0' }}"
                     data-online="{{ $row['is_online'] ? '1' : '0' }}"
                     data-search="{{ $row['search'] }}">

                    <div class="pgd-row__module">
                        <a href="{{ $row['module_url'] }}" class="pgd-row__name">{{ $row['module_name'] }}</a>
                        <div class="pgd-row__sub">
                            {{-- Session type wears the shape the term badge used
                                 to, colour-coded per type so the mix of theory /
                                 tutorial / seminar is scannable down the column. --}}
                            @if(!empty($row['session_type']))
                                <span class="pgd-row__type"
                                      style="color: {{ $row['session_ink'] }}; background: {{ $row['session_tint'] }}; border-color: {{ $row['session_tint'] }};"
                                      title="{{ $row['session_type'] }} · {{ $row['term_name'] }}">
                                    <span style="background: {{ $row['session_ink'] }};"></span>{{ $row['session_type'] }}
                                </span>
                            @endif
                            <span class="pgd-row__course">{{ $row['course'] }}</span>
                            @if(!empty($row['tags']))
                                <span class="pgd-row__tags">
                                    @foreach($row['tags'] as $tag)
                                        <span class="pgd-tag pgd-tag--{{ $tag['tone'] }}" title="{{ $tag['title'] }}">{{ $tag['label'] }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if(!empty($row['group']))
                            <span class="pgd-group">{{ $row['group'] }}</span>
                        @endif
                    </div>

                    @if($row['tutor_record_url'])
                        <a href="{{ $row['tutor_record_url'] }}" class="pgd-tutor">
                    @else
                        <span class="pgd-tutor">
                    @endif
                        <span class="pgd-avatar" style="background: {{ $row['tutor_color'] }};">
                            @if(!empty($row['tutor_photo']))
                                <img src="{{ $row['tutor_photo'] }}" alt="{{ $row['tutor_name'] }}">
                            @else
                                {{ $row['tutor_initials'] }}
                            @endif
                        </span>
                        <span class="pgd-tutor__copy">
                            @if($row['is_swapped'])
                                <span class="pgd-tutor__former" @if(!empty($row['proxy_reason'])) title="{{ $row['proxy_reason'] }}" @endif>{{ $row['former_tutor'] }}</span>
                            @endif
                            <span class="pgd-tutor__name {{ $row['tutor_present'] ? 'is-in' : 'is-out' }}">{{ $row['tutor_name'] }}</span>
                            @if(!$row['is_swapped'] && !empty($row['tutor_phone']) && !$row['tutor_present'])
                                <span class="pgd-tutor__phone is-out">{{ $row['tutor_phone'] }}</span>
                            @elseif($row['is_swapped'])
                                <span class="pgd-tutor__covering">COVERING</span>
                            @elseif(!empty($row['tutor_phone']))
                                <span class="pgd-tutor__phone">{{ $row['tutor_phone'] }}</span>
                            @endif
                        </span>
                    @if($row['tutor_record_url'])
                        </a>
                    @else
                        </span>
                    @endif

                    <div class="pgd-row__room">{{ $row['room'] }}</div>

                    <div class="pgd-row__status">
                        <span class="pgd-status">
                            @if($row['status'] == 'ongoing' && $row['fed'])
                                <span class="pgd-fed" title="Attendance fed">A</span>
                            @endif

                            @if($row['status'] == 'scheduled')
                                <span class="pgd-pill pgd-pill--scheduled"><span></span>Scheduled</span>
                            @elseif($row['status'] == 'shortly')
                                <span class="pgd-pill pgd-pill--shortly"><span></span>Starting shortly</span>
                            @elseif($row['status'] == 'ongoing')
                                <span class="pgd-pill pgd-pill--ongoing"><span></span>Started {{ $row['started'] }}</span>
                            @elseif($row['status'] == 'completed')
                                <span class="pgd-done {{ $row['fed'] ? 'is-fed' : '' }}">
                                    <span class="pgd-done__mark">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"></path></svg>
                                    </span>
                                    <span class="pgd-done__times">{{ $row['started'] }}<span>—</span>{{ $row['finished'] }}</span>
                                    @if(!empty($row['duration']))
                                        <span class="pgd-done__dur">{{ $row['duration'] }}</span>
                                    @endif
                                </span>
                            @elseif($row['status'] == 'notstarted')
                                <span class="pgd-pill pgd-pill--notstarted"><span></span>Not Started</span>
                            @elseif($row['status'] == 'cancelled')
                                <span class="pgd-pill pgd-pill--cancelled"><span></span>Cancelled</span>
                            @endif
                        </span>

                        @if($row['needs_attendance'] && $row['feed_url'])
                            <a href="{{ $row['feed_url'] }}" class="pgd-feednow">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#B3261E" stroke-width="2.2" stroke-linecap="round"><path d="M12 8v5"></path><path d="M12 16.5v.5"></path><circle cx="12" cy="12" r="9"></circle></svg>
                                Feed attendance
                            </a>
                        @endif
                    </div>

                    <div class="pgd-row__menu" data-pgd-menu>
                        @if($row['can_feed'] || $row['can_view_feed'] || $row['can_swap'] || $row['can_end'] || $row['can_cancel'] || $row['tutor_record_url'])
                            <button type="button" class="pgd-kebab" data-pgd-menu-toggle aria-label="Class actions">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="5" r="0.6"></circle><circle cx="12" cy="12" r="0.6"></circle><circle cx="12" cy="19" r="0.6"></circle></svg>
                            </button>
                            <div class="pgd-menu">
                                @if($row['can_feed'] && $row['feed_url'])
                                    <a href="{{ $row['feed_url'] }}" class="pgd-menu__item pgd-menu__item--teal">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5Z"></path><circle cx="12" cy="12" r="1.6"></circle></svg>
                                        Feed attendance
                                    </a>
                                @endif
                                @if($row['can_view_feed'] && $row['feed_url'])
                                    <a href="{{ $row['feed_url'] }}" class="pgd-menu__item pgd-menu__item--teal">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5Z"></path><circle cx="12" cy="12" r="1.6"></circle></svg>
                                        View feed
                                    </a>
                                @endif
                                @if($row['can_swap'])
                                    <button type="button" class="pgd-menu__item pgd-menu__item--green proxyClass"
                                            data-tutorid="{{ $row['owner_user_id'] }}"
                                            data-planid="{{ $row['plan_id'] }}"
                                            data-plandateid="{{ $row['id'] }}"
                                            data-module="{{ $row['module_name'] }}"
                                            data-group="{{ $row['group'] }}"
                                            data-meta="{{ $row['time'] }} · {{ $row['room'] }}"
                                            data-tutorname="{{ $row['former_tutor'] }}"
                                            data-tutorinitials="{{ $row['tutor_initials'] }}"
                                            data-tutorstatus="{{ $row['tutor_present'] ? 'Clocked in today' : 'Not clocked in today' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg>
                                        Swap class
                                    </button>
                                @endif
                                @if($row['can_end'])
                                    <button type="button" class="pgd-menu__item pgd-menu__item--red endClassBtn"
                                            data-attendanceinfo="{{ $row['attendance_information_id'] }}"
                                            data-plandateid="{{ $row['id'] }}"
                                            data-group="{{ $row['group'] }}"
                                            data-meta="{{ $row['time'] }} · {{ $row['room'] }} · {{ $row['tutor_name'] }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                                        End class
                                    </button>
                                @endif
                                {{-- No "open tutor record" entry: the tutor cell
                                     itself is already a link to that record. --}}
                                @if($row['can_cancel'])
                                    <span class="pgd-menu__sep"></span>
                                    <button type="button" class="pgd-menu__item pgd-menu__item--red cancelClass"
                                            data-planid="{{ $row['plan_id'] }}"
                                            data-plandateid="{{ $row['id'] }}"
                                            data-group="{{ $row['group'] }}"
                                            data-meta="{{ $row['module_name'] }} · {{ $row['time'] }} · {{ $row['room'] }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m15 9-6 6"></path><path d="m9 9 6 6"></path></svg>
                                        Cancel class
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@empty
    <div class="pgd-empty" data-pgd-empty-server>
        <span class="pgd-empty__icon" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        </span>
        <div class="pgd-empty__title">No classes match those filters</div>
        <div class="pgd-empty__body">There is no class plan for the selected date and filters.</div>
    </div>
@endforelse

<div class="pgd-empty" data-pgd-empty-client hidden>
    <span class="pgd-empty__icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
    </span>
    <div class="pgd-empty__title">No classes match those filters</div>
    <div class="pgd-empty__body">Try clearing the search or resetting the filters.</div>
</div>
