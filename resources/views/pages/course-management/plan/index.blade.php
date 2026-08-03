@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        // The class-day picker below is a chip row writing into a hidden input,
        // so the posted payload is unchanged from the legacy radio group.
        $planClassTypes = ['Theory', 'Practical', 'Tutorial', 'Seminar'];
        $planDays = [
            'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu',
            'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun',
        ];
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-card cm-tablecard">
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Class plans List</h2>
                        <span class="cm-tablecard__count" data-cm-count="plan"></span>
                    </div>
                    <a href="{{ route('class.plan.add') }}" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add New Plan
                    </a>
                </div>

                {{-- The field `name`s are load-bearing: `exportPlans` reads this
                     form as multipart FormData, so they must stay as they were
                     (`groups`, `tutors[]`, `date_cpl`, …) even though the list
                     and grid endpoints take differently-named parameters. --}}
                <form class="cm-planfilters" id="planFilterForm" autocomplete="off">
                    <div class="cm-planfilters__grid">
                        <div class="cm-field">
                            <label for="plan-courses">Courses</label>
                            <select id="plan-courses" name="courses" class="cm-select">
                                <option value="">All</option>
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-term">Terms</label>
                            <select id="plan-term" name="term_declaration_id" class="cm-select">
                                <option value="">All</option>
                                @foreach($terms as $trm)
                                    <option value="{{ $trm->id }}">{{ $trm->name }} - {{ $trm->termType->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filled from `class.plan.get.group.filter` once both a
                             course and a term are chosen. --}}
                        <div class="cm-field">
                            <label for="plan-group">Groups</label>
                            <select id="plan-group" name="groups" class="cm-select">
                                <option value="">All</option>
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-tutor">Tutors</label>
                            <select id="plan-tutor" name="tutors[]" class="cm-select" multiple>
                                @foreach($tutor as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-ptutor">Personal Tutors</label>
                            <select id="plan-ptutor" name="ptutors[]" class="cm-select" multiple>
                                @foreach($ptutor as $ptr)
                                    <option value="{{ $ptr->id }}">{{ $ptr->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-room">Rooms</label>
                            <select id="plan-room" name="rooms[]" class="cm-select" multiple>
                                @foreach($room as $rm)
                                    <option value="{{ $rm->id }}">{{ $rm->venue->name ?? '' }} - {{ $rm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-days">Days</label>
                            <select id="plan-days" name="days[]" class="cm-select" multiple>
                                @foreach($planDays as $dayValue => $dayLabel)
                                    <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-date">Date</label>
                            <input id="plan-date" name="date_cpl" type="text" class="cm-input datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>

                        {{-- The legacy markup called this `statusCPL` while the
                             script read `status-CPL`, so `status` was never sent
                             and Archived plans were unreachable. --}}
                        <div class="cm-field">
                            <label for="plan-status">Status</label>
                            <select id="plan-status" name="status" class="cm-select">
                                <option value="1" selected>Active</option>
                                <option value="2">Archived</option>
                            </select>
                        </div>

                        <div class="cm-field">
                            <label for="plan-view">Views</label>
                            <select id="plan-view" name="view" class="cm-select">
                                <option value="1" selected>List View</option>
                                <option value="2">Calendar View</option>
                                <option value="3">Tree View</option>
                            </select>
                        </div>
                    </div>

                    <div class="cm-planfilters__foot">
                        <button type="submit" id="planFilterGo" class="cm-btn cm-btn--go">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                            Go
                        </button>
                        <button type="button" id="planFilterReset" class="cm-btn cm-btn--ghost">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                            Reset
                        </button>

                        <span class="cm-planfilters__spacer"></span>

                        {{-- Both buttons carry the `.cm-spinner` / `data-cm-btn-icon`
                             pair `setBusy()` toggles. --}}
                        <button type="button" id="exportPlansXLSX" class="cm-pillbtn">
                            <svg style="display: none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                            </svg>
                            <svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                            Export XL
                        </button>

                        {{-- Revealed by the table's selection handler. --}}
                        <button type="button" id="generateDaysBtn" class="cm-btn cm-btn--go" hidden>
                            <svg style="display: none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                                <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                            </svg>
                            <svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect></svg>
                            Generate Days
                        </button>
                    </div>
                </form>

                {{-- Two hosts, not one: the calendar view is server-rendered HTML
                     and the legacy code wrote it over the Tabulator element,
                     which left the table unusable until a full reload. --}}
                <div class="cm-tabulator-wrap" data-cm-pane="list">
                    <div id="classPlansListTable" class="cm-tabulator"></div>
                </div>
                <div class="cm-planscal" data-cm-pane="grid" hidden></div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Edit plan                                                         --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="editPlanModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
            <form method="POST" action="#" id="editPlanForm" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span>Class plan</span></div>
                            <h2 class="cm-modal__title cm-serif">Edit Plan</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body cm-modal__body--grid3">
                        <div class="cm-readout">
                            <span class="cm-readout__label">Term</span>
                            <span class="cm-readout__value termName">&mdash;</span>
                        </div>
                        <div class="cm-readout cm-field--span2">
                            <span class="cm-readout__label">Course</span>
                            <span class="cm-readout__value courseName">&mdash;</span>
                        </div>
                        <div class="cm-readout cm-field--span3">
                            <span class="cm-readout__label">Group</span>
                            <span class="cm-readout__value groupName">&mdash;</span>
                        </div>

                        <div class="cm-field">
                            <label for="module_creation_id">Module <span>*</span></label>
                            {{-- Options arrive from `class.plan.edit` as a ready
                                 <option> string, keyed to the plan's term. --}}
                            <select id="module_creation_id" name="module_creation_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-module_creation_id"></div>
                        </div>

                        <div class="cm-field">
                            <label for="rooms_id">Room <span>*</span></label>
                            <select id="rooms_id" name="rooms_id" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($room as $rm)
                                    <option value="{{ $rm->id }}">{{ $rm->name }} - {{ $rm->venue->name ?? '' }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-rooms_id"></div>
                        </div>

                        <div class="cm-field">
                            <label for="class_type">Class Type <span>*</span></label>
                            <select id="class_type" name="class_type" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($planClassTypes as $planType)
                                    <option value="{{ $planType }}">{{ $planType }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-class_type"></div>
                        </div>

                        {{-- Tutorial and Seminar classes are led by the personal
                             tutor, so the Tutor field is withdrawn for them —
                             the same split the list's Tutor column uses. --}}
                        <div class="cm-field tutorWrap">
                            <label for="tutor_id">Tutor</label>
                            <select id="tutor_id" name="tutor_id" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($tutor as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-tutor_id"></div>
                        </div>

                        <div class="cm-field">
                            <label for="personal_tutor_id">Personal Tutor</label>
                            <select id="personal_tutor_id" name="personal_tutor_id" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($ptutor as $ptr)
                                    <option value="{{ $ptr->id }}">{{ $ptr->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-personal_tutor_id"></div>
                        </div>

                        <div class="cm-field">
                            <label for="start_time">Start Time <span>*</span></label>
                            <input id="start_time" type="text" name="start_time" class="cm-input theTimeField" placeholder="09:15">
                            <div class="acc__input-error error-start_time"></div>
                        </div>

                        <div class="cm-field">
                            <label for="end_time">End Time <span>*</span></label>
                            <input id="end_time" type="text" name="end_time" class="cm-input theTimeField" placeholder="12:15">
                            <div class="acc__input-error error-end_time"></div>
                        </div>

                        <div class="cm-field">
                            <label for="submission_date">Submission Date</label>
                            <input id="submission_date" type="text" name="submission_date" class="cm-input datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                            <div class="acc__input-error error-submission_date"></div>
                        </div>

                        <div class="cm-field cm-field--span3">
                            <label>Class Day <span>*</span></label>
                            <div class="cm-optpick" data-cm-optpick="class_day">
                                @foreach($planDays as $dayValue => $dayLabel)
                                    <button type="button" class="cm-optpick__btn" data-cm-opt="{{ $dayValue }}">
                                        <span class="cm-optpick__box">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        </span>
                                        {{ $dayLabel }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" id="class_day" name="class_day" value="">
                            <div class="acc__input-error error-class_day"></div>
                        </div>

                        <div class="cm-field">
                            <label for="virtual_room">Virtual Room</label>
                            <textarea id="virtual_room" name="virtual_room" class="cm-input cm-textarea"></textarea>
                            <div class="acc__input-error error-virtual_room"></div>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label for="note">Note</label>
                            <textarea id="note" name="note" class="cm-input cm-textarea"></textarea>
                            <div class="acc__input-error error-note"></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="updatePlans" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Update
                        </button>
                        <input type="hidden" name="id" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-plans.js')
@endsection
