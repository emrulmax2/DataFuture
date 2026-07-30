@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $bDays = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
        $bDate = fn ($v) => (!empty($v) && $v != '0000-00-00') ? date('d-m-Y', strtotime($v)) : '—';

        // Rooms are grouped under their venue so the venue chips can show a
        // count and hide their rooms in one go.
        $bRoomsByVenue = $rooms->groupBy('venue_id');
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <form method="POST" action="#" id="classPlanBuilderForm" autocomplete="off">

                {{-- ------------------------------------------------------ --}}
                {{-- What this sheet is for                                  --}}
                {{-- ------------------------------------------------------ --}}
                <div class="cm-card cm-planhead">
                    <div class="cm-planhead__top">
                        <div class="cm-planhead__id">
                            <div class="cm-detail__eyebrow">
                                <span class="cm-detail__badge">Group</span>
                                <span class="cm-detail__sub">{{ $academic->name ?? '' }} · {{ $termDec->name ?? '' }}</span>
                            </div>
                            <h2 class="cm-planhead__title cm-serif">{{ $group->name }}</h2>
                            <p class="cm-planhead__course">{{ $creation->course->name ?? '' }}</p>
                        </div>

                        <div class="cm-planhead__actions">
                            <div class="cm-planhead__count">
                                <span class="cm-planhead__countlabel">Classes</span>
                                <span class="cm-planhead__countvalue cm-serif" data-cm-total>0</span>
                            </div>
                            <button type="submit" id="saveUpdatePlans" class="cm-btn cm-btn--pill">
                                @include('pages.course-management.partials.save-glyphs')
                                Save or Update
                            </button>
                        </div>
                    </div>

                    <div class="cm-planhead__meta">
                        @foreach([
                            ['Term', $termDec->name ?? '—', 'calendar'],
                            ['Session Term', $instanceTerm->termType->name ?? '—', 'layers'],
                            ['Start Date', $bDate($instanceTerm->start_date ?? null), 'calendar'],
                            ['End Date', $bDate($instanceTerm->end_date ?? null), 'calendar'],
                            ['Teaching Weeks', $instanceTerm->total_teaching_weeks ?? '—', 'grid'],
                            ['Teaching Start', $bDate($instanceTerm->teaching_start_date ?? null), 'book'],
                            ['Teaching End', $bDate($instanceTerm->teaching_end_date ?? null), 'book'],
                            ['Revision Start', $bDate($instanceTerm->revision_start_date ?? null), 'shield'],
                        ] as $bMeta)
                            <div class="cm-meta">
                                <span class="cm-meta__icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        @switch($bMeta[2])
                                            @case('calendar')<path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect>@break
                                            @case('layers')<path d="M12 2l9 5-9 5-9-5z"></path><path d="M3 12l9 5 9-5M3 17l9 5 9-5"></path>@break
                                            @case('book')<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>@break
                                            @case('shield')<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>@break
                                            @default<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>
                                        @endswitch
                                    </svg>
                                </span>
                                <div style="min-width:0;">
                                    <div class="cm-meta__label">{{ $bMeta[0] }}</div>
                                    <div class="cm-meta__value">{{ $bMeta[1] !== '' ? $bMeta[1] : '—' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ------------------------------------------------------ --}}
                {{-- Venues — which rooms are on the sheet                   --}}
                {{-- ------------------------------------------------------ --}}
                <div class="cm-card cm-venuepick">
                    <div class="cm-venuepick__label">Venues</div>
                    @if($venues->count() > 0)
                        <div class="cm-venuepick__row">
                            @foreach($venues as $bVenue)
                                <label class="cm-venuechip is-on">
                                    <input type="checkbox" class="cm-venuechip__input" data-cm-venue="{{ $bVenue->id }}" value="{{ $bVenue->id }}" checked>
                                    <span class="cm-venuechip__box">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                    </span>
                                    <span class="cm-venuechip__name">{{ $bVenue->name }}</span>
                                    <span class="cm-venuechip__count" data-cm-venue-count="{{ $bVenue->id }}">0</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="cm-finder__note">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                            No venues found, so there is nowhere to plan a class.
                        </div>
                    @endif
                </div>

                {{-- ------------------------------------------------------ --}}
                {{-- The planner                                             --}}
                {{-- ------------------------------------------------------ --}}
                <div class="cm-card cm-routine">
                    <div class="cm-routine__head">
                        <div class="cm-routine__headleft">
                            <h3 class="cm-routine__title cm-serif">Routine sets</h3>

                            <label class="cm-headsearch__box cm-routine__search">
                                <span class="cm-sr-only">Find a room</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                                <input type="search" data-cm-roomsearch placeholder="Find a room">
                            </label>

                            <button type="button" class="cm-pillbtn" data-cm-toggle-all>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                                <span data-cm-toggle-all-label>Collapse all</span>
                            </button>
                        </div>

                        {{-- Filled when a card is copied; paste targets appear
                             on every room while it holds something. --}}
                        <div class="cm-clip" data-cm-clip hidden>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                            <span class="cm-clip__copy">
                                <span class="cm-clip__label">Copied</span>
                                <span class="cm-clip__value" data-cm-clip-label></span>
                            </span>
                            <button type="button" class="cm-clip__clear" data-cm-clip-clear title="Clear clipboard">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-daytabs" role="tablist">
                            @foreach($bDays as $bDayNum => $bDayLabel)
                                <button type="button"
                                        class="cm-daytab {{ $bDayNum === 1 ? 'is-active' : '' }}"
                                        role="tab"
                                        aria-selected="{{ $bDayNum === 1 ? 'true' : 'false' }}"
                                        data-cm-daytab="{{ $bDayNum }}">
                                    {{ $bDayLabel }}
                                    <span class="cm-daytab__badge" data-cm-day-count="{{ $bDayNum }}" hidden>0</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @foreach($bDays as $bDayNum => $bDayLabel)
                        <div class="cm-routine__pane" data-cm-daypane="{{ $bDayNum }}" @if($bDayNum !== 1) hidden @endif>
                            @if($rooms->count() > 0)
                                <div class="cm-roomgrid">
                                    @foreach($venues as $bVenue)
                                        @foreach($bRoomsByVenue[$bVenue->id] ?? [] as $bRoom)
                                            <div class="cm-roompanel"
                                                 data-cm-room="{{ $bRoom->id }}"
                                                 data-cm-room-venue="{{ $bVenue->id }}"
                                                 data-cm-room-name="{{ strtolower($bRoom->name.' '.$bVenue->name) }}">
                                                <button type="button" class="cm-roompanel__head" data-cm-room-toggle>
                                                    <span class="cm-roompanel__names">
                                                        <span class="cm-roompanel__room">{{ $bRoom->name }}</span>
                                                        <span class="cm-roompanel__venue">{{ $bVenue->name }}</span>
                                                    </span>
                                                    <span class="cm-roompanel__count" data-cm-room-count>0</span>
                                                </button>

                                                <div class="cm-roompanel__body">
                                                    <div class="cm-roompanel__cards" data-cm-cards>
                                                        @if(!empty($plans[$bDayNum][$bRoom->id]))
                                                            @foreach($plans[$bDayNum][$bRoom->id] as $bCard)
                                                                {!! $bCard !!}
                                                            @endforeach
                                                        @endif
                                                    </div>

                                                    <div class="cm-roompanel__empty" data-cm-room-empty>No classes</div>

                                                    <div class="cm-roompanel__foot">
                                                        <button type="button"
                                                                class="cm-addclass"
                                                                data-cm-add
                                                                data-day="{{ $bDayNum }}"
                                                                data-venue="{{ $bVenue->id }}"
                                                                data-room="{{ $bRoom->id }}">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                                            Add class
                                                        </button>
                                                        <button type="button" class="cm-pastebtn" data-cm-paste title="Paste copied class" hidden>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                                                            Paste
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>

                                <div class="cm-routine__noroom" data-cm-noroom hidden>
                                    No room matches that search.
                                </div>
                            @else
                                <div class="cm-finder__note">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                                    No rooms found, so there is nowhere to plan a class.
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- The combination the sheet saves against; read straight off
                     these by the submit handler. --}}
                <input type="hidden" id="term_declaration_id" name="term_declaration_id" value="{{ $termDec->id }}">
                <input type="hidden" id="academic_year_id" name="academic_year_id" value="{{ $academic->id }}">
                <input type="hidden" id="course_creation_id" name="course_creation_id" value="{{ $creation->id }}">
                <input type="hidden" id="instance_term_id" name="instance_term_id" value="{{ $instanceTerm->id }}">
                <input type="hidden" id="course_id" name="course_id" value="{{ $creation->course_id }}">
                <input type="hidden" id="group_id" name="group_id" value="{{ $group->id }}">
            </form>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-plan-builder.js')
@endsection
