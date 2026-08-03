@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-card cm-tablecard">
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Groups List</h2>
                        {{-- Filled from the list response so it tracks the active filters. --}}
                        <span class="cm-tablecard__count" data-cm-count="group"></span>
                        <span class="cm-tablecard__sel" data-cm-selcount hidden></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add New Group
                    </button>
                </div>

                {{-- Four filters on one row, the way the endpoint takes them. The
                     Course list narrows to the term's own courses once a term is
                     picked (`group.courselist.by.term`), as it did before. --}}
                <form class="cm-groupfilters" id="groupFilterForm" autocomplete="off">
                    <div class="cm-field cm-groupfilters__query">
                        <label for="group-query">Query</label>
                        <span class="cm-inputsearch">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                            <input id="group-query" name="query" type="search" class="cm-input" placeholder="Search groups">
                        </span>
                    </div>

                    <div class="cm-field cm-groupfilters__term">
                        <label for="group-term">Term</label>
                        <select id="group-term" name="term" class="cm-select">
                            <option value="">All terms</option>
                            @if(!empty($term_decs))
                                @foreach($term_decs as $td)
                                    <option value="{{ $td->id }}">{{ $td->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="cm-field cm-groupfilters__course">
                        <label for="group-course">Course</label>
                        <select id="group-course" name="course_id" class="cm-select">
                            <option value="">All courses</option>
                            @if(!empty($courses))
                                @foreach($courses as $cr)
                                    <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="cm-field cm-groupfilters__status">
                        <label for="group-status">Status</label>
                        <select id="group-status" name="status" class="cm-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                            <option value="2">Archived</option>
                            <option value="all">All</option>
                        </select>
                    </div>

                    <div class="cm-groupfilters__actions">
                        <button type="submit" id="groupFilterGo" class="cm-btn cm-btn--go">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                            Go
                        </button>
                        <button type="button" id="groupFilterReset" class="cm-btn cm-btn--ghost">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                            Reset
                        </button>
                    </div>

                    <div class="cm-groupfilters__tools">
                        <button type="button" data-cm-print class="cm-pillbtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                            Print
                        </button>

                        {{-- Revealed by the table's selection handler; the actions
                             post to `groups.bulk.action` as they always have. --}}
                        <div class="dropdown" data-cm-bulk hidden>
                            <button type="button" id="groupActionDropdown" class="dropdown-toggle cm-btn cm-btn--pill" aria-expanded="false" data-tw-toggle="dropdown">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                Group Actions
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="dropdown-menu cm-export-menu cm-actionmenu">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="activeSelected" data-action="ACTIVEALL" href="javascript:;" class="dropdown-item groupActionBTN">
                                            <span class="cm-menuicon cm-menuicon--ok">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="M22 4L12 14.01l-3-3"></path></svg>
                                            </span>
                                            Mark as Active
                                        </a>
                                    </li>
                                    <li>
                                        <a id="inactiveSelected" data-action="INACTIVEALL" href="javascript:;" class="dropdown-item groupActionBTN">
                                            <span class="cm-menuicon cm-menuicon--warn">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M15 9l-6 6M9 9l6 6"></path></svg>
                                            </span>
                                            Mark as Inactive
                                        </a>
                                    </li>
                                    <li>
                                        <a id="deleteSelected" data-action="DELETEALL" href="javascript:;" class="dropdown-item groupActionBTN">
                                            <span class="cm-menuicon cm-menuicon--danger">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path></svg>
                                            </span>
                                            Move to Archive
                                        </a>
                                    </li>
                                    <li>
                                        <a id="restoreSelected" data-action="RESTOREALL" href="javascript:;" class="dropdown-item groupActionBTN">
                                            <span class="cm-menuicon cm-menuicon--info">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                                            </span>
                                            Restore
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="cm-tabulator-wrap">
                    <div id="groupsTableId" class="cm-tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Add / edit group — one block, two modals                          --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $groupMode)
        @php
            $isAdd = $groupMode === 'add';
            $modalId = $isAdd ? 'addModal' : 'editModal';
            $formId = $isAdd ? 'addForm' : 'editForm';
            $prefix = $isAdd ? 'add' : 'edit';
        @endphp

        <div id="{{ $modalId }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $formId }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $isAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $isAdd ? 'Add Group' : 'Edit Group' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid2">
                            <div class="cm-field">
                                {{-- Marked required because `GroupsRequests` rejects a
                                     blank term, which the legacy form never said. --}}
                                <label for="{{ $prefix }}_term_declaration_id">Term <span>*</span></label>
                                <select id="{{ $prefix }}_term_declaration_id" name="term_declaration_id" class="cm-select term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($term_decs))
                                        @foreach($term_decs as $td)
                                            <option value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $prefix }}_course_id">Course <span>*</span></label>
                                <select id="{{ $prefix }}_course_id" name="course_id" class="cm-select course_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $cr)
                                            <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-course_id"></div>
                            </div>

                            <div class="cm-field cm-field--span2">
                                <label for="{{ $prefix }}_name">Group Name <span>*</span></label>
                                <input id="{{ $prefix }}_name" type="text" name="name" class="cm-input name" placeholder="e.g. JAN26-BUS-A">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            {{-- Real checkboxes: the controller reads `evening_and_weekend`
                                 and `active` as posted values, so the payload is
                                 unchanged from the legacy form. --}}
                            <label class="cm-switchfield">
                                <span>Evening &amp; Weekend</span>
                                <input id="{{ $prefix }}_evening_and_weekend" class="cm-switchcard__input" name="evening_and_weekend" value="1" type="checkbox">
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Yes" data-off="No"></span>
                                        <span class="cm-switchcard__desc" data-on="Evening and weekend delivery" data-off="Weekday delivery only"></span>
                                    </span>
                                </span>
                            </label>

                            <label class="cm-switchfield">
                                <span>Status</span>
                                <input id="{{ $prefix }}_active" class="cm-switchcard__input" name="active" value="1" type="checkbox" checked>
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Active" data-off="Inactive"></span>
                                        <span class="cm-switchcard__desc" data-on="Available for planning" data-off="Hidden from planning"></span>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $isAdd ? 'save' : 'update' }}" class="cm-btn cm-btn--save">
                                <svg style="display: none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                                <svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path></svg>
                                {{ $isAdd ? 'Save' : 'Update' }}
                            </button>
                            @unless($isAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-groups.js')
@endsection
