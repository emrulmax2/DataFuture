@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $defaultEmailContent = 'Dear ' . $employee->full_name . ',<br/><p>Enclosed herewith is an important communication from the Human Resources Department.</p><br/> Best regards,<br/>Human Resources Department<br/>London Churchill College';
    @endphp

    @include('pages.employee.profile.partials.cover-header')

    @include('pages.employee.profile.partials.side-tabs')

    <div class="ep-grid ep-doc-page">
        <div class="ep-col">
            <div class="ep-doc-shell">
                <section class="ep-doc-card">
                    <div class="ep-doc-card__head">
                        <div class="ep-doc-card__head-main">
                            <span class="ep-doc-card__icon ep-doc-card__icon--gold">
                                <i data-lucide="folder-closed" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="ep-doc-card__title">Documents</h2>
                                <p id="employeeDocumentSummary" class="ep-doc-card__meta">Upload, manage and archive employee documents.</p>
                            </div>
                        </div>
                        <div class="ep-doc-card__head-actions">
                            <div class="dropdown ep-doc-dropdown" id="uploadsDropdown">
                                <button class="dropdown-toggle ep-doc-btn ep-doc-btn--soft ep-doc-dropdown__toggle" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="plus" class="w-4 h-4 ep-doc-dropdown__toggle-icon"></i>
                                    <span>Add Document List</span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 ep-doc-dropdown__caret"></i>
                                </button>
                                <div class="dropdown-menu ep-doc-dropdown__menu">
                                    <div class="dropdown-content ep-doc-dropdown__content">
                                        <div class="ep-doc-dropdown__header">
                                            <div class="ep-doc-dropdown__title">Document List</div>
                                        </div>
                                        @if(isset($docSettings) && !empty($docSettings) && $docSettings->count() > 0)
                                            <div class="ep-doc-dropdown__list">
                                                @foreach($docSettings as $ds)
                                                    <label class="ep-doc-type" for="employee_doc_{{ $ds->id }}">
                                                        <input id="employee_doc_{{ $ds->id }}" name="employee_doc_ids[]" class="employee_doc_ids ep-doc-type__input" type="radio" value="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                                        <span class="ep-doc-type__content">
                                                            <span class="ep-doc-type__icon">
                                                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                                            </span>
                                                            <span class="ep-doc-type__label">{{ $ds->name }}</span>
                                                        </span>
                                                        <span class="ep-doc-type__check"><span></span></span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="ep-doc-dropdown__empty">
                                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                                <span>There are no document settings available right now.</span>
                                            </div>
                                        @endif
                                        <div class="ep-doc-dropdown__footer">
                                            <button type="button" id="employeeDocumentUploaders" class="ep-doc-btn ep-doc-btn--soft">
                                                <i data-lucide="upload" class="w-4 h-4"></i>
                                                Upload Documents
                                            </button>
                                            <button type="button" id="closeUploadsDropdown" class="ep-doc-btn ep-doc-btn--ghost">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-doc-card__body">
                        <div class="ep-doc-toolbar">
                            <form id="tabulatorFilterForm-UP" class="ep-doc-toolbar__form">
                                <div class="ep-doc-field ep-doc-field--query">
                                    <label for="query-ED">Query</label>
                                    <input id="query-ED" name="query" type="text" class="form-control" placeholder="Search by document name...">
                                </div>
                                <div class="ep-doc-field ep-doc-field--status">
                                    <label for="status-ED">Status</label>
                                    <select id="status-ED" name="status" class="form-select">
                                        <option selected value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="ep-doc-toolbar__filters">
                                    <button id="tabulator-html-filter-go-ED" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                    <button id="tabulator-html-filter-reset-ED" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                                </div>
                            </form>

                            <div class="ep-doc-toolbar__actions">
                                <button id="tabulator-print-ED" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                                <a id="tabulator-export-csv-ED" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-ED" href="javascript:;" class="dropdown-item">
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
                            <div id="employeeDocumentListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                        </div>
                    </div>
                </section>

                <section class="ep-doc-card">
                    <div class="ep-doc-card__head">
                        <div class="ep-doc-card__head-main">
                            <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="ep-doc-card__title">Communications</h2>
                                <p id="employeeCommunicationSummary" class="ep-doc-card__meta">Track HR emails, attachments and archived communication records.</p>
                            </div>
                        </div>
                        <div class="ep-doc-card__head-actions">
                            <button data-tw-toggle="modal" data-tw-target="#addCommunicationModal" type="button" class="ep-doc-btn ep-doc-btn--soft">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Send Email
                            </button>
                        </div>
                    </div>

                    <div class="ep-doc-card__body">
                        <div class="ep-doc-toolbar">
                            <form id="tabulatorFilterForm-EDC" class="ep-doc-toolbar__form">
                                <div class="ep-doc-field ep-doc-field--query">
                                    <label for="query-EDC">Query</label>
                                    <input id="query-EDC" name="query" type="text" class="form-control" placeholder="Search by communication name...">
                                </div>
                                <div class="ep-doc-field ep-doc-field--status">
                                    <label for="status-EDC">Status</label>
                                    <select id="status-EDC" name="status" class="form-select">
                                        <option selected value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="ep-doc-toolbar__filters">
                                    <button id="tabulator-html-filter-go-EDC" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                    <button id="tabulator-html-filter-reset-EDC" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                                </div>
                            </form>

                            <div class="ep-doc-toolbar__actions">
                                <button id="tabulator-print-EDC" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                                <a id="tabulator-export-csv-EDC" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-EDC" href="javascript:;" class="dropdown-item">
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
                            <div id="employeeCommunicationDocumentListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                        </div>
                    </div>
                </section>
            </div>

            <div id="addCommunicationModal" class="modal ep-doc-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form method="post" action="#" id="addCommunicationForm" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header ep-doc-modal__header">
                                <div class="ep-doc-modal__intro">
                                    <span class="ep-doc-modal__icon">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <h2>Send Email</h2>
                                        <p>Create and record an HR communication for this employee.</p>
                                    </div>
                                </div>
                                <a data-tw-dismiss="modal" href="javascript:;">
                                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                <div class="ep-doc-form-grid">
                                    <div class="ep-doc-form-grid__full">
                                        <label for="email_template_id" class="form-label">Template</label>
                                        <select id="email_template_id" placeholder="Select Template" name="email_template_id" class="w-full tom-selects email_template_id">
                                            <option value="">Please Select a Template</option>
                                            @if(!empty($emailTemplates))
                                                @foreach($emailTemplates as $et)
                                                    <option value="{{ $et->id }}">{{ $et->email_title }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="ep-doc-form-grid__full sendEmailContent" data-content="{{ $defaultEmailContent }}">
                                        <label class="form-label">Mail Content <span class="text-danger">*</span></label>
                                        <div class="editor document-editor email_body">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable" id="email_body">{!! $defaultEmailContent !!}</div>
                                            </div>
                                        </div>
                                        <div class="acc__input-error error-email_body text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <label class="form-label">Attachment</label>
                                        <div class="ep-doc-file-picker">
                                            <label for="editComDocument" class="ep-doc-btn ep-doc-btn--soft ep-doc-file-picker__trigger">
                                                <i data-lucide="paperclip" class="w-4 h-4"></i>
                                                Upload Document
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="document absolute w-0 h-0 overflow-hidden opacity-0" id="editComDocument"/>
                                            <span id="editComDocumentName" class="editComDocumentName ep-doc-file-picker__name">No file selected</span>
                                        </div>
                                        <div class="acc__input-error error-document text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <label class="form-label">Document Name</label>
                                        <input type="text" name="document_name" class="form-control w-full document_name" placeholder="Attachment name"/>
                                        <div class="acc__input-error error-document_name text-danger mt-2"></div>
                                    </div>

                                    <div class="ep-doc-form-grid__full">
                                        <label class="form-label">Hard Copy Checked?</label>
                                        <div class="ep-doc-choice-group">
                                            <label class="ep-doc-choice" for="hard_copy_check-11">
                                                <input id="hard_copy_check-11" class="ep-doc-choice__input" type="radio" value="1" name="hard_copy_check_status">
                                                <span class="ep-doc-choice__control">
                                                    <span class="ep-doc-choice__radio"><span></span></span>
                                                    <span class="ep-doc-choice__label">Yes</span>
                                                </span>
                                            </label>
                                            <label class="ep-doc-choice" for="hard_copy_check-22">
                                                <input checked id="hard_copy_check-22" class="ep-doc-choice__input" type="radio" value="0" name="hard_copy_check_status">
                                                <span class="ep-doc-choice__control">
                                                    <span class="ep-doc-choice__radio"><span></span></span>
                                                    <span class="ep-doc-choice__label">No</span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                                <button type="submit" id="sendEmail" class="btn btn-primary">
                                    Send
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

            <div id="uploadEmployeeDocumentModal" class="modal ep-doc-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="upload" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Upload Documents</h2>
                                    <p>Add employee records and mark whether the physical copy has been checked.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="{{ route('employee.documents.upload.documents') }}" class="dropzone ep-doc-upload-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                                @csrf
                                <div class="fallback">
                                    <input name="documents[]" multiple type="file" />
                                </div>
                                <div class="dz-message" data-dz-message>
                                    <div class="ep-doc-upload-dropzone__icon">
                                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                                    </div>
                                    <div class="ep-doc-upload-dropzone__title">Drop files here or click to upload</div>
                                    <div class="ep-doc-upload-dropzone__meta">Max file size 5MB and up to 10 files per upload.</div>
                                </div>
                                <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                                <input type="hidden" name="document_setting_id" value="0"/>
                                <input type="hidden" name="hard_copy_check" value="0"/>
                                <input type="hidden" name="display_file_name" value=""/>
                            </form>

                            <div class="ep-doc-upload-meta">
                                <div class="ep-doc-upload-name">
                                    <div class="ep-doc-upload-name__prefix">Document Type</div>
                                    <div id="documentNameDisplay" class="ep-doc-upload-name__value">Selected document type</div>
                                </div>

                                <div>
                                    <label class="form-label ep-doc-upload-label">Document Name</label>
                                    <input type="text" name="doc_name" class="displayNameInput form-control w-full" placeholder="Document name"/>
                                </div>

                                <div>
                                    <label class="form-label ep-doc-upload-label">Hard Copy Checked?</label>
                                    <div class="ep-doc-choice-group">
                                        <label class="ep-doc-choice" for="hard_copy_check-1">
                                            <input id="hard_copy_check-1" class="ep-doc-choice__input" type="radio" value="1" name="hard_copy_check_status">
                                            <span class="ep-doc-choice__control">
                                                <span class="ep-doc-choice__radio"><span></span></span>
                                                <span class="ep-doc-choice__label">Yes</span>
                                            </span>
                                        </label>
                                        <label class="ep-doc-choice" for="hard_copy_check-2">
                                            <input checked id="hard_copy_check-2" class="ep-doc-choice__input" type="radio" value="0" name="hard_copy_check_status">
                                            <span class="ep-doc-choice__control">
                                                <span class="ep-doc-choice__radio"><span></span></span>
                                                <span class="ep-doc-choice__label">No</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                            <button type="button" id="uploadEmpDocBtn" class="btn btn-primary">
                                Upload
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
                        </div>
                    </div>
                </div>
            </div>

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
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/employee-upload.js')
@endsection
