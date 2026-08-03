@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->

    @include('pages.students.admission.show-info')

    <!-- END: Profile Info -->
    <div class="adm-sections adm-comm adm-notes">
    <div class="adm-section">
        <div class="adm-section__head">
            <div class="adm-section__title">Notes</div>
            <div class="adm-tabletools__actions">
                <button data-tw-toggle="modal" data-tw-target="#addNoteModal" type="button" class="adm-btn adm-btn--primary">
                    <i data-lucide="sticky-note" class="w-4 h-4"></i>
                    Add Notes
                </button>
            </div>
        </div>
        <div class="adm-sectionbody">
            <div class="adm-tabletools">
                <form id="tabulatorFilterForm-AN" class="adm-tabletools__filters">
                    <span class="adm-filter-text">Query</span>
                    <input id="query-AN" name="query" type="text" class="adm-input" placeholder="Search...">
                    <span class="adm-filter-text">Status</span>
                    <div class="adm-field adm-field--narrow">
                        <select id="status-AN" name="status" class="adm-select">
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                        <svg class="adm-field__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                    </div>
                    <button id="tabulator-html-filter-go-AN" type="button" class="adm-btn adm-btn--primary">Go</button>
                    <button id="tabulator-html-filter-reset-AN" type="button" class="adm-btn adm-btn--soft">Reset</button>
                </form>
                <div class="adm-tabletools__actions">
                    <button type="button" id="tabulator-print-AN" class="adm-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>Print</button>
                    <div class="dropdown adm-dropdown">
                        <button type="button" class="dropdown-toggle adm-btn" aria-expanded="false" data-tw-toggle="dropdown"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>Export<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg></button>
                        <div class="dropdown-menu">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">Export CSV</a>
                                </li>
                                {{-- <li>
                                    <a id="tabulator-export-json-AN" href="javascript:;" class="dropdown-item">Export JSON</a>
                                </li> --}}
                                <li>
                                    <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">Export XLSX</a>
                                </li>
                                {{-- <li>
                                    <a id="tabulator-export-html-AN" href="javascript:;" class="dropdown-item">Export HTML</a>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="adm-table-wrap">
                <div id="applicantNotesListTable" data-applicant="{{ $applicant->id }}" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    </div>

    <!-- BEGIN: View Modal -->
    <div id="viewNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Note</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="adm-note-view" id="viewNoteContent"></div>
                </div>
                <div class="modal-footer">
                    <div class="footerBtns adm-note-view__footer-left"></div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Modal -->

    <!-- BEGIN: Edit Modal -->
    <div id="editNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editNoteForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Note</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="editEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-content text-danger mt-2"></div>
                        </div>
                        <div class="adm-mail-upload adm-note-upload mt-3">
                            <a href="#" download class="downloadExistAttachment adm-note-upload__existing" style="display: none;">
                                <i data-lucide="download" class="w-4 h-4"></i>
                                <span>Current Attachment</span>
                            </a>
                            <label for="editNoteDocument" class="adm-mail-upload__button">
                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                                <span>Upload Attachment</span>
                            </label>
                            <span class="adm-mail-upload__hint">PDF, DOC, image, spreadsheet</span>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editNoteDocument"/>
                        </div>
                        <div id="editNoteDocumentName" class="documentNoteName adm-mail-upload__files mt-3" style="display: none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="UpdateNote" class="btn btn-primary w-auto">
                            Update
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="adm-btn-loader w-4 h-4 ml-2">
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
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

    <!-- BEGIN: Add Modal -->
    <div id="addNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addNoteForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Note</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="addEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-content text-danger mt-2"></div>
                        </div>
                        <div class="adm-mail-upload adm-note-upload mt-3">
                            <label for="addNoteDocument" class="adm-mail-upload__button">
                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                                <span>Upload Attachment</span>
                            </label>
                            <span class="adm-mail-upload__hint">PDF, DOC, image, spreadsheet</span>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addNoteDocument"/>
                        </div>
                        <div id="addNoteDocumentName" class="documentNoteName adm-mail-upload__files mt-3" style="display: none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveNote" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="adm-btn-loader w-4 h-4 ml-2">
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
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/admission-notes.js')
    @vite('resources/js/admission-vue.js')
@endsection
