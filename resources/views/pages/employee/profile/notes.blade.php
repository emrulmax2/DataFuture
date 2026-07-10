@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid ep-doc-page">
    <div class="ep-col">
        <div class="ep-doc-shell">
            <section class="ep-doc-card">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--gold">
                            <i data-lucide="sticky-note" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Notes</h2>
                            <p id="employeeNotesSummary" class="ep-doc-card__meta">Record, manage and archive notes for this employee.</p>
                        </div>
                    </div>
                    <div class="ep-doc-card__head-actions">
                        <button data-tw-toggle="modal" data-tw-target="#addEmpNoteModal" type="button" class="ep-doc-btn ep-doc-btn--soft">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Notes
                        </button>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm-AN" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--query">
                                <label for="query-EN">Query</label>
                                <input id="query-EN" name="query" type="text" class="form-control" placeholder="Search by note...">
                            </div>
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="status-EN">Status</label>
                                <select id="status-EN" name="status" class="form-select">
                                    <option selected value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="ep-doc-toolbar__filters">
                                <button id="tabulator-html-filter-go-EN" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                <button id="tabulator-html-filter-reset-EN" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                            </div>
                        </form>

                        <div class="ep-doc-toolbar__actions">
                            <button id="tabulator-print-EN" type="button" class="ep-doc-btn ep-doc-btn--ghost">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                Print
                            </button>
                            <div class="dropdown ep-doc-export">
                                <button class="dropdown-toggle ep-doc-btn ep-doc-btn--ghost" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                    Export
                                    <i data-lucide="chevron-down" class="w-4 h-4 opacity-70"></i>
                                </button>
                                <div class="dropdown-menu ep-doc-export__dropdown w-44">
                                    <ul class="dropdown-content ep-doc-export__menu">
                                        <li>
                                            <a id="tabulator-export-csv-EN" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-EN" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                                Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-doc-table-wrap">
                        <div id="employeeNotesListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </div>
            </section>
        </div>

        <!-- BEGIN: View Modal -->
        <div id="viewEmpNoteModal" class="modal ep-doc-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header ep-doc-modal__header">
                        <div class="ep-doc-modal__intro">
                            <span class="ep-doc-modal__icon">
                                <i data-lucide="sticky-note" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2>Note</h2>
                                <p>Full note content and attachment for this employee.</p>
                            </div>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <div class="footerBtns" style="margin-right: auto;"></div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: View Modal -->

        <!-- BEGIN: Add Modal -->
        <div id="addEmpNoteModal" class="modal ep-doc-modal ep-doc-modal--note" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="addEmpNoteForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="sticky-note" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Add Note</h2>
                                    <p>Create a new note and optionally attach a supporting document.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-note-modal__surface">
                                <div class="ep-doc-form-grid">
                                    <div class="ep-note-modal__field">
                                        <label for="opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__editor-block">
                                        <div class="ep-note-modal__section-head">
                                            <label for="addEmpNoteEditor" class="form-label">Note <span class="text-danger">*</span></label>
                                            <p class="ep-note-modal__hint">Write the note content and keep the important details together.</p>
                                        </div>
                                        <div class="editor document-editor">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable" id="addEmpNoteEditor"></div>
                                            </div>
                                        </div>
                                        <div class="acc__input-error error-content text-danger mt-2"></div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__attachment-block">
                                        <div class="ep-note-modal__section-head">
                                            <label class="form-label">Attachment</label>
                                        </div>
                                        <div class="ep-doc-file-picker">
                                            <label for="addEmpNoteDocument" class="ep-doc-btn ep-doc-btn--soft ep-doc-file-picker__trigger">
                                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                                                Upload Document
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addEmpNoteDocument"/>
                                            <span id="addEmpNoteDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                        </div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__reminder-block">
                                        <div class="ep-doc-reminder-row">
                                            <div class="ep-doc-switch">
                                                <label class="form-check form-switch mb-0">
                                                    <input id="reminder" class="form-check-input" type="checkbox" name="reminder" value="1">
                                                    <span class="ep-doc-switch__label">Reminder</span>
                                                </label>
                                            </div>
                                            <div class="ep-doc-reminder-date reminderDateWrap" style="display: none;">
                                                <label for="reminder_date" class="form-label">Reminder Date <span class="text-danger">*</span></label>
                                                <input type="text" value="" placeholder="DD-MM-YYYY" id="reminder_date" class="form-control datepicker" name="reminder_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                                <div class="acc__input-error error-reminder_date text-danger mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="saveEmpNote" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Save Note
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </button>
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END: Add Modal -->

        <!-- BEGIN: Edit Modal -->
        <div id="editEmpNoteModal" class="modal ep-doc-modal ep-doc-modal--note" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="editEmpNoteForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Edit Note</h2>
                                    <p>Update the note content, attachment or reminder.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-note-modal__surface">
                                <div class="ep-doc-form-grid">
                                    <div class="ep-note-modal__field">
                                        <label for="edit_opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="edit_opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__editor-block">
                                        <div class="ep-note-modal__section-head">
                                            <label for="editEmpNoteEditor" class="form-label">Note <span class="text-danger">*</span></label>
                                            <p class="ep-note-modal__hint">Revise the note body, formatting and attached context.</p>
                                        </div>
                                        <div class="editor document-editor">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable" id="editEmpNoteEditor"></div>
                                            </div>
                                        </div>
                                        <div class="acc__input-error error-content text-danger mt-2"></div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__attachment-block">
                                        <div class="ep-note-modal__section-head">
                                            <label class="form-label">Attachment</label>
                                        </div>
                                        <div class="ep-doc-file-picker">
                                            <a href="#" download class="ep-doc-btn ep-doc-btn--soft downloadExistAttachment inline-flex" style="display: none;">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                                Current file
                                            </a>
                                            <label for="editEmpNoteDocument" class="ep-doc-btn ep-doc-btn--soft ep-doc-file-picker__trigger">
                                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                                                Upload Document
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editEmpNoteDocument"/>
                                            <span id="editEmpNoteDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                        </div>
                                    </div>

                                    <div class="ep-doc-form-grid__full ep-note-modal__section ep-note-modal__reminder-block">
                                        <div class="ep-doc-reminder-row">
                                            <div class="ep-doc-switch">
                                                <label class="form-check form-switch mb-0">
                                                    <input id="edit_reminder" class="form-check-input" type="checkbox" name="reminder" value="1">
                                                    <span class="ep-doc-switch__label">Reminder</span>
                                                </label>
                                            </div>
                                            <div class="ep-doc-reminder-date reminderDateWrap" style="display: none;">
                                                <label for="edit_reminder_date" class="form-label">Reminder Date <span class="text-danger">*</span></label>
                                                <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_reminder_date" class="form-control datepicker" name="reminder_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                                <div class="acc__input-error error-reminder_date text-danger mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="updateEmpNote" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Update
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </button>
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                            <input type="hidden" name="id" value="0"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- END: Edit Modal -->

        <!-- BEGIN: Success Modal Content -->
        <div id="successModal" class="modal ep-holiday-state-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="ep-holiday-state-modal__body">
                            <div class="ep-holiday-state-modal__icon">
                                <i data-lucide="check" class="w-10 h-10"></i>
                            </div>
                            <div class="ep-holiday-state-modal__title successModalTitle"></div>
                            <div class="ep-holiday-state-modal__desc successModalDesc"></div>
                        </div>
                        <div class="ep-holiday-state-modal__actions">
                            <button type="button" data-action="DISMISS" class="successCloser btn btn-primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Success Modal Content -->

        <!-- BEGIN: Warning Modal Content -->
        <div id="warningModal" class="modal ep-holiday-state-modal ep-holiday-state-modal--warning" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="ep-holiday-state-modal__body">
                            <div class="ep-holiday-state-modal__icon">
                                <i data-lucide="alert-octagon" class="w-10 h-10"></i>
                            </div>
                            <div class="ep-holiday-state-modal__title warningModalTitle"></div>
                            <div class="ep-holiday-state-modal__desc warningModalDesc"></div>
                        </div>
                        <div class="ep-holiday-state-modal__actions">
                            <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Warning Modal Content -->

        <!-- BEGIN: Delete Confirm Modal Content -->
        <div id="confirmModal" class="modal ep-holiday-state-modal ep-holiday-state-modal--danger" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="ep-holiday-state-modal__body">
                            <div class="ep-holiday-state-modal__icon">
                                <i data-lucide="x" class="w-10 h-10"></i>
                            </div>
                            <div class="ep-holiday-state-modal__title confModTitle">Are you sure?</div>
                            <div class="ep-holiday-state-modal__desc confModDesc"></div>
                        </div>
                        <div class="ep-holiday-state-modal__actions">
                            <button type="button" class="disAgreeWith btn btn-outline-secondary">No, Cancel</button>
                            <button type="button" data-recordid="0" data-status="none" data-employee="{{ $employee->id }}" class="agreeWith btn btn-danger">Yes, I agree</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Delete Confirm Modal Content -->
    </div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-note.js')
@endsection
