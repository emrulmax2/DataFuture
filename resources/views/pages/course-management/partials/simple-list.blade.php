{{--
    Shared markup for the "id + name" list screens in Course Management
    (Semesters, Module Levels, …) — the table card plus the add / edit /
    success / confirm modals. Behaviour comes from `js/course-list-table.js`,
    which is why the element ids here are fixed rather than parameterised.

    Required:
      $listTitle       Card heading, e.g. "Semesters"
      $listNoun        Singular, used in the modal titles, e.g. "Semester"
      $listTableId     DOM id for the Tabulator host, e.g. "semesterTableId"
      $listFieldLabel  Field label, e.g. "Semester Name"
      $listPrivilege   priv() key that reveals the card

    Optional:
      $listPlaceholder Example value for the name input
--}}
@php
    $listPlaceholder = $listPlaceholder ?? '';
    $listCan = isset(auth()->user()->priv()[$listPrivilege]) && auth()->user()->priv()[$listPrivilege] == 1;
@endphp

@if($listCan)
    <div class="cm-card cm-tablecard">
        <div class="cm-tablecard__head">
            <div class="cm-tablecard__titles">
                <h2 class="cm-tablecard__title cm-serif">{{ $listTitle }}</h2>
                {{-- Filled from the list response so it tracks the active filters. --}}
                <span class="cm-tablecard__count" data-cm-count></span>
            </div>
            <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="cm-btn cm-btn--pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                New {{ $listNoun }}
            </button>
        </div>

        {{-- The simple lists know two states: 1 = active, 2 = archived
             (soft-deleted), which is the toolbar's default chip set. --}}
        @include('pages.course-management.partials.list-toolbar', [
            'toolbarSearchLabel' => 'Search ' . strtolower($listTitle),
        ])

        <div class="cm-tabulator-wrap">
            <div id="{{ $listTableId }}" class="cm-tabulator"></div>
        </div>
    </div>
@else
    <div class="cm-card">
        <div class="cm-empty">
            <span class="cm-empty__icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
            </span>
            <div class="cm-empty__title cm-serif">Permission required</div>
            <div class="cm-empty__text">You do not have permission to view {{ strtolower($listTitle) }}. Use the menu on the left to reach a section you can access.</div>
        </div>
    </div>
@endif

<!-- BEGIN: Add Modal -->
<div id="addModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog">
        <form method="POST" action="#" id="addForm" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span>New record</span></div>
                        <h2 class="cm-modal__title cm-serif">Add {{ $listNoun }}</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="cm-modal__body">
                    <div class="cm-field">
                        <label for="add_name">{{ $listFieldLabel }} <span>*</span></label>
                        <input id="add_name" type="text" name="name" class="cm-input name" placeholder="{{ $listPlaceholder }}">
                        <div class="acc__input-error error-name"></div>
                    </div>
                </div>

                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="save" class="cm-btn cm-btn--save">
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
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->

<!-- BEGIN: Edit Modal -->
<div id="editModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog">
        <form method="POST" action="#" id="editForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span>Edit record</span></div>
                        <h2 class="cm-modal__title cm-serif">Edit {{ $listNoun }}</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="cm-modal__body">
                    <div class="cm-field">
                        <label for="edit_name">{{ $listFieldLabel }} <span>*</span></label>
                        <input id="edit_name" type="text" name="name" class="cm-input name" placeholder="{{ $listPlaceholder }}">
                        <div class="acc__input-error error-name"></div>
                    </div>
                </div>

                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="update" class="cm-btn cm-btn--save">
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
                        Update
                    </button>
                    <input type="hidden" name="id" value="0">
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->

@include('pages.course-management.partials.list-dialogs')
