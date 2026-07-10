@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $dueDate = !empty($appraisal->due_on) ? date('jS M, Y', strtotime($appraisal->due_on)) : 'N/A';
        $completedDate = !empty($appraisal->completed_on) ? date('jS M, Y', strtotime($appraisal->completed_on)) : 'N/A';
        $nextDueDate = !empty($appraisal->next_due_on) ? date('jS M, Y', strtotime($appraisal->next_due_on)) : 'N/A';
        $appraisedBy = trim((isset($appraisal->appraisedby->first_name) ? $appraisal->appraisedby->first_name . ' ' : '') . (isset($appraisal->appraisedby->last_name) ? $appraisal->appraisedby->last_name : '')) ?: 'N/A';
        $reviewedBy = trim((isset($appraisal->reviewedby->first_name) ? $appraisal->reviewedby->first_name . ' ' : '') . (isset($appraisal->reviewedby->last_name) ? $appraisal->reviewedby->last_name : '')) ?: 'N/A';
        $cycleStart = !empty($appraisal->due_on) ? date('Y', strtotime($appraisal->due_on)) : date('Y');
        $cycleEnd = !empty($appraisal->next_due_on) ? date('Y', strtotime($appraisal->next_due_on)) : $cycleStart;
        $scoreValue = isset($appraisal->total_score) && $appraisal->total_score !== null && $appraisal->total_score !== '' ? rtrim(rtrim(number_format((float) $appraisal->total_score, 2, '.', ''), '0'), '.') : null;
        $dueDateValue = !empty($appraisal->due_on) ? date('Y-m-d', strtotime($appraisal->due_on)) : '';
        $completedDateValue = !empty($appraisal->completed_on) ? date('Y-m-d', strtotime($appraisal->completed_on)) : '';
        $today = date('Y-m-d');
        $statusLabel = 'Due';
        $statusClass = 'ep-appraisal-summary__badge ep-appraisal-summary__badge--amber';
        $statusIcon = 'clock-3';

        if (!empty($completedDateValue) && $completedDateValue <= $today) {
            $statusLabel = 'Completed';
            $statusClass = 'ep-appraisal-summary__badge ep-appraisal-summary__badge--success';
            $statusIcon = 'check';
        } elseif (!empty($dueDateValue) && $dueDateValue < $today) {
            $statusLabel = 'Overdue';
            $statusClass = 'ep-appraisal-summary__badge ep-appraisal-summary__badge--danger';
            $statusIcon = 'alert-circle';
        }
    @endphp

    @include('pages.employee.profile.partials.cover-header')

    @include('pages.employee.profile.partials.side-tabs')

    <div class="ep-grid ep-doc-page ep-appraisal-page">
        <div class="ep-col">
            <div class="ep-doc-shell">
                <section class="ep-doc-card ep-appraisal-summary">
                    <div class="ep-appraisal-summary__head">
                        <div class="ep-appraisal-summary__intro">
                            <span class="ep-appraisal-summary__icon">
                                <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="ep-appraisal-summary__title">Appraisal Details</h2>
                                <p class="ep-appraisal-summary__meta">Appraisal #{{ $appraisal->id }} &middot; cycle {{ $cycleStart }} - {{ $cycleEnd }}</p>
                            </div>
                        </div>
                        <a href="{{ route('employee.appraisal', $employee->id) }}" class="ep-doc-btn ep-doc-btn--ghost">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Back to List
                        </a>
                    </div>

                    <div class="ep-appraisal-summary__body">
                        <div class="ep-appraisal-summary__row">
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Due On</div>
                                <div class="ep-appraisal-summary__value">{{ $dueDate }}</div>
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Completed On</div>
                                <div class="ep-appraisal-summary__value">{{ $completedDate }}</div>
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Next Due</div>
                                <div class="ep-appraisal-summary__value">{{ $nextDueDate }}</div>
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Appraised By</div>
                                <div class="ep-appraisal-summary__value">{{ $appraisedBy }}</div>
                            </div>
                        </div>

                        <div class="ep-appraisal-summary__row ep-appraisal-summary__row--bordered">
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Reviewed By</div>
                                <div class="ep-appraisal-summary__value">{{ $reviewedBy }}</div>
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Total Score</div>
                                <div class="ep-appraisal-summary__score">
                                    @if($scoreValue !== null)
                                        <span>{{ $scoreValue }}</span>
                                        <small>/ 5</small>
                                    @else
                                        <span class="ep-appraisal-summary__score-empty">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Promotion Consideration</div>
                                @if($appraisal->promotion_consideration == 1)
                                    <span class="ep-appraisal-summary__badge ep-appraisal-summary__badge--success">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Yes
                                    </span>
                                @else
                                    <span class="ep-appraisal-summary__badge ep-appraisal-summary__badge--danger">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        No
                                    </span>
                                @endif
                            </div>
                            <div class="ep-appraisal-summary__item">
                                <div class="ep-appraisal-summary__label">Status</div>
                                <span class="{{ $statusClass }}">
                                    <i data-lucide="{{ $statusIcon }}" class="w-3.5 h-3.5"></i>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        @if(!empty($appraisal->notes))
                            <div class="ep-appraisal-summary__note">
                                <div class="ep-appraisal-summary__label">Appraisal Note</div>
                                <div class="ep-appraisal-summary__note-copy">{!! nl2br(e($appraisal->notes)) !!}</div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="ep-doc-card ep-doc-card--accent-gold ep-appraisal-card">
                    <div class="ep-doc-card__head">
                        <div class="ep-doc-card__head-main">
                            <span class="ep-doc-card__icon ep-doc-card__icon--gold">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="ep-doc-card__title">Employee Appraisal Document</h2>
                                <p id="employeeAppraisalDocumentSummary" class="ep-doc-card__meta">Upload, manage and archive appraisal documents.</p>
                            </div>
                        </div>
                        <div class="ep-doc-card__head-actions">
                            <a href="{{ route('employee.appraisal', $employee->id) }}" class="ep-doc-btn ep-doc-btn--ghost">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                Back to Appraisal
                            </a>
                            <button data-tw-toggle="modal" data-tw-target="#addAppraisalDocModal" type="button" class="ep-doc-btn ep-doc-btn--soft">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                                Upload Document
                            </button>
                        </div>
                    </div>

                    <div class="ep-appraisal-toolbar">
                        <form id="tabulatorFilterForm-APD" class="ep-appraisal-toolbar__filters">
                            <div class="ep-appraisal-toolbar__field ep-appraisal-toolbar__field--query">
                                <label for="query-APD">Query</label>
                                <div class="ep-appraisal-toolbar__search">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                    <input id="query-APD" name="query" type="text" class="form-control" placeholder="Search...">
                                </div>
                            </div>
                            <div class="ep-appraisal-toolbar__field ep-appraisal-toolbar__field--status">
                                <label for="status-APD">Status</label>
                                <select id="status-APD" name="status" class="form-select">
                                    <option value="1" selected>Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <button id="tabulator-html-filter-go-APD" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                            <button id="tabulator-html-filter-reset-APD" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                        </form>

                        <div class="ep-appraisal-toolbar__actions">
                            <button id="tabulator-print-APD" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                            <a id="tabulator-export-csv-APD" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-APD" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                                Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-appraisal-table-wrap">
                        <div id="employeeAppraisalDocListTable" data-employee="{{ $employee->id }}" data-appraisal="{{ $appraisal->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </section>

                <section class="ep-doc-card ep-doc-card--accent-blue ep-appraisal-card">
                    <div class="ep-doc-card__head">
                        <div class="ep-doc-card__head-main">
                            <span class="ep-doc-card__icon ep-doc-card__icon--blue">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="ep-doc-card__title">Employee Appraisal Note</h2>
                                <p id="employeeAppraisalNoteSummary" class="ep-doc-card__meta">Record and manage notes linked to this appraisal.</p>
                            </div>
                        </div>
                        <div class="ep-doc-card__head-actions">
                            <button data-tw-toggle="modal" data-tw-target="#addAppraisalNoteModal" type="button" class="ep-doc-btn ep-doc-btn--soft">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                Add Note
                            </button>
                        </div>
                    </div>

                    <div class="ep-appraisal-toolbar">
                        <form id="tabulatorFilterForm-APN" class="ep-appraisal-toolbar__filters">
                            <div class="ep-appraisal-toolbar__field ep-appraisal-toolbar__field--query">
                                <label for="query-APN">Query</label>
                                <div class="ep-appraisal-toolbar__search">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                    <input id="query-APN" name="query" type="text" class="form-control" placeholder="Search...">
                                </div>
                            </div>
                            <div class="ep-appraisal-toolbar__field ep-appraisal-toolbar__field--status">
                                <label for="status-APN">Status</label>
                                <select id="status-APN" name="status" class="form-select">
                                    <option value="1" selected>Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <button id="tabulator-html-filter-go-APN" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                            <button id="tabulator-html-filter-reset-APN" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                        </form>

                        <div class="ep-appraisal-toolbar__actions">
                            <button id="tabulator-print-APN" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                            <a id="tabulator-export-csv-APN" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-APN" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                                                Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ep-appraisal-table-wrap">
                        <div id="employeeAppraisalNoteListTable" data-employee="{{ $employee->id }}" data-appraisal="{{ $appraisal->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </section>
            </div>

            <div id="addAppraisalDocModal" class="modal ep-doc-modal ep-doc-modal--form ep-doc-modal--gold ep-doc-modal--narrow ep-doc-modal--appraisal-upload" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="#" id="addAppraisalDocForm" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header ep-doc-modal__header">
                                <div class="ep-doc-modal__intro">
                                    <span class="ep-doc-modal__icon">
                                        <i data-lucide="upload" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <h2>Upload Appraisal Documents</h2>
                                    </div>
                                </div>
                                <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                <div class="ep-doc-form-grid ep-doc-form-grid--single">
                                    <div>
                                        <label for="appraisal_document_name" class="form-label">Document Name <span class="text-danger">*</span></label>
                                        <input id="appraisal_document_name" type="text" name="display_file_name" class="form-control w-full" placeholder="Document name">
                                        <div class="acc__input-error error-display_file_name text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="file" class="absolute w-0 h-0 overflow-hidden opacity-0" id="appraisalDocumentFile"/>
                                        <label for="appraisalDocumentFile" class="ep-appraisal-upload-zone">
                                            <span class="ep-appraisal-upload-zone__icon">
                                                <i data-lucide="upload" class="w-5 h-5"></i>
                                            </span>
                                            <span class="ep-appraisal-upload-zone__title">Drop files here or click to upload.</span>
                                            <span class="ep-appraisal-upload-zone__meta">Max file size 5MB &amp; max file limit 1.</span>
                                        </label>
                                        <div class="acc__input-error error-file text-danger mt-2"></div>
                                        <span id="appraisalDocumentFileName" class="ep-doc-file-chip mt-3" style="display: none;"></span>
                                    </div>

                                    <div>
                                        <label class="form-label">Hard Copy Checked?</label>
                                        <div class="ep-doc-choice-group">
                                            <label class="ep-doc-choice" for="appraisal_hard_copy_check_1">
                                                <input id="appraisal_hard_copy_check_1" class="ep-doc-choice__input" type="radio" value="1" name="hard_copy_check_status">
                                                <span class="ep-doc-choice__control">
                                                    <span class="ep-doc-choice__radio"><span></span></span>
                                                    <span class="ep-doc-choice__label">Yes</span>
                                                </span>
                                            </label>
                                            <label class="ep-doc-choice" for="appraisal_hard_copy_check_2">
                                                <input checked id="appraisal_hard_copy_check_2" class="ep-doc-choice__input" type="radio" value="0" name="hard_copy_check_status">
                                                <span class="ep-doc-choice__control">
                                                    <span class="ep-doc-choice__radio"><span></span></span>
                                                    <span class="ep-doc-choice__label">No</span>
                                                </span>
                                            </label>
                                        </div>
                                        <div class="acc__input-error error-hard_copy_check text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                                </button>
                                <button type="submit" id="uploadAppraisalDocBtn" class="btn btn-primary">
                                    <i data-lucide="upload" class="w-4 h-4 mr-2"></i>Upload
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
                                <input type="hidden" name="employee_appraisal_id" value="{{ $appraisal->id }}"/>
                                <input type="hidden" name="hard_copy_check" value="0"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="viewAppraisalNoteModal" class="modal ep-doc-modal ep-doc-modal--form ep-doc-modal--appraisal-view" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Note</h2>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div id="viewAppraisalNoteContent" class="ep-appraisal-note-view"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="addAppraisalNoteModal" class="modal ep-doc-modal ep-doc-modal--note ep-doc-modal--appraisal-note" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="#" id="addAppraisalNoteForm" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header ep-doc-modal__header">
                                <div class="ep-doc-modal__intro">
                                    <span class="ep-doc-modal__icon">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <h2>Add Note</h2>
                                    </div>
                                </div>
                                <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                <div class="ep-doc-form-grid ep-doc-form-grid--single">
                                    <div>
                                        <label for="appraisal_opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="appraisal_opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <label for="addAppraisalNoteEditor" class="form-label">Note <span class="text-danger">*</span></label>
                                        <div class="editor document-editor">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable" id="addAppraisalNoteEditor"></div>
                                            </div>
                                        </div>
                                        <div class="acc__input-error error-content text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <div class="ep-doc-file-picker">
                                            <label for="addAppraisalNoteDocument" class="ep-doc-file-picker__trigger">
                                                <i data-lucide="send" class="w-4 h-4"></i>
                                                Upload Document
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addAppraisalNoteDocument"/>
                                            <span id="addAppraisalNoteDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                                </button>
                                <button type="submit" id="saveAppraisalNote" class="btn btn-primary">
                                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>Save
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
                                <input type="hidden" name="employee_appraisal_id" value="{{ $appraisal->id }}"/>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="editAppraisalNoteModal" class="modal ep-doc-modal ep-doc-modal--note ep-doc-modal--appraisal-note" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="#" id="editAppraisalNoteForm" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header ep-doc-modal__header">
                                <div class="ep-doc-modal__intro">
                                    <span class="ep-doc-modal__icon">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <h2>Edit Note</h2>
                                    </div>
                                </div>
                                <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </a>
                            </div>
                            <div class="modal-body">
                                <div class="ep-doc-form-grid ep-doc-form-grid--single">
                                    <div>
                                        <label for="edit_appraisal_opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                                        <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="edit_appraisal_opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <label for="editAppraisalNoteEditor" class="form-label">Note <span class="text-danger">*</span></label>
                                        <div class="editor document-editor">
                                            <div class="document-editor__toolbar"></div>
                                            <div class="document-editor__editable-container">
                                                <div class="document-editor__editable" id="editAppraisalNoteEditor"></div>
                                            </div>
                                        </div>
                                        <div class="acc__input-error error-content text-danger mt-2"></div>
                                    </div>

                                    <div>
                                        <div class="ep-doc-file-picker">
                                            <a href="#" download class="downloadExistAttachment ep-doc-file-picker__trigger" style="display: none;">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                                Current file
                                            </a>
                                            <label for="editAppraisalNoteDocument" class="ep-doc-file-picker__trigger">
                                                <i data-lucide="send" class="w-4 h-4"></i>
                                                Upload Document
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editAppraisalNoteDocument"/>
                                            <span id="editAppraisalNoteDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                                </button>
                                <button type="submit" id="updateAppraisalNote" class="btn btn-primary">
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
                                <input type="hidden" name="employee_appraisal_id" value="{{ $appraisal->id }}"/>
                                <input type="hidden" name="id" value="0"/>
                            </div>
                        </div>
                    </form>
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
                                <button type="button" data-recordid="0" data-status="none" class="agreeWith btn btn-danger">Yes, I agree</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/employee-appraisal-documents.js')
    @vite('resources/js/employee-appraisal-note.js')
@endsection
