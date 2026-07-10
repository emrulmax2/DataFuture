@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }} </title>
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid ep-doc-page">
    <div class="ep-col">
        <div class="ep-doc-shell">

            <!-- ============= EMPLOYEE APPRAISALS ============= -->
            <section class="ep-doc-card ep-doc-card--accent-teal">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                            <i data-lucide="award" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Employee Appraisals</h2>
                            <p id="employeeAppraisalSummary" class="ep-doc-card__meta">Track appraisal cycles, scores and promotion outcomes.</p>
                        </div>
                    </div>
                    <div class="ep-doc-card__head-actions">
                        <button data-tw-toggle="modal" data-tw-target="#addAppraisalModal" type="button" class="add_btn ep-doc-btn ep-doc-btn--soft">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Appraisal
                        </button>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="status">Status</label>
                                <select id="status" name="status" class="form-select">
                                    <option selected value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="ep-doc-toolbar__filters">
                                <button id="tabulator-html-filter-go" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                            </div>
                        </form>

                        <div class="ep-doc-toolbar__actions">
                            <button id="tabulator-print" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
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
                        <div id="employeeAppraisalListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </div>
            </section>

            <!-- ============= EMPLOYEE TRAININGS ============= -->
            <section class="ep-doc-card ep-doc-card--accent-gold {{ (isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1) ? '' : 'hidden' }}">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--gold">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Employee Trainings</h2>
                            <p id="employeeTrainingSummary" class="ep-doc-card__meta">Record training, providers, costs and expiry dates.</p>
                        </div>
                    </div>
                    <div class="ep-doc-card__head-actions">
                        <button data-tw-toggle="modal" data-tw-target="#addTraininglModal" type="button" class="add_btn ep-doc-btn ep-doc-btn--soft">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Training
                        </button>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm-ET" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="status-ET">Status</label>
                                <select id="status-ET" name="status" class="form-select">
                                    <option selected value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="ep-doc-toolbar__filters">
                                <button id="tabulator-html-filter-go-ET" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                <button id="tabulator-html-filter-reset-ET" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                            </div>
                        </form>

                        <div class="ep-doc-toolbar__actions">
                            <button id="tabulator-print-ET" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                            <a id="tabulator-export-csv-ET" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-ET" href="javascript:;" class="dropdown-item">
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
                        <div id="employeeTrainingListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </div>
            </section>

        </div>

        <!-- BEGIN: ADD Training Modal -->
        <div id="addTraininglModal" class="modal ep-doc-modal ep-doc-modal--form ep-doc-modal--gold" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="addTraininglForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Add Training</h2>
                                    <p>Record a training course, provider and supporting document.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-doc-form-grid">
                                <div>
                                    <label for="name" class="form-label">Training Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="provider" class="form-label">Provider <span class="text-danger">*</span></label>
                                    <input id="provider" type="text" name="provider" class="form-control w-full">
                                    <div class="acc__input-error error-provider text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <input id="location" type="text" name="location" class="form-control w-full">
                                    <div class="acc__input-error error-location text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="training_date" class="form-label">Training Date <span class="text-danger">*</span></label>
                                    <input id="training_date" type="text" name="training_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY - DD-MM-YYYY" data-format="DD-MM-YYYY">
                                    <div class="acc__input-error error-training_date text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="cost" class="form-label">Cost</label>
                                    <input id="cost" type="number" step="any" name="cost" class="form-control w-full">
                                </div>
                                <div>
                                    <label for="expire_date" class="form-label">Expire Date</label>
                                    <input id="expire_date" type="text" name="expire_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label class="form-label">Attachment</label>
                                    <div class="ep-doc-file-picker">
                                        <label for="addTraiDocument" class="ep-doc-btn ep-doc-btn--soft ep-doc-file-picker__trigger">
                                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                                            Upload Document
                                        </label>
                                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addTraiDocument"/>
                                        <span id="addTraiDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="saveTraining" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Save Training
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
        <!-- END: ADD Training Modal -->

        <!-- BEGIN: Edit Training Modal -->
        <div id="editTraininglModal" class="modal ep-doc-modal ep-doc-modal--form ep-doc-modal--gold" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="editTraininglForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Update Training</h2>
                                    <p>Revise the training details or replace the attachment.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-doc-form-grid">
                                <div>
                                    <label for="edit_name" class="form-label">Training Name <span class="text-danger">*</span></label>
                                    <input id="edit_name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="edit_provider" class="form-label">Provider <span class="text-danger">*</span></label>
                                    <input id="edit_provider" type="text" name="provider" class="form-control w-full">
                                    <div class="acc__input-error error-provider text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="edit_location" class="form-label">Location <span class="text-danger">*</span></label>
                                    <input id="edit_location" type="text" name="location" class="form-control w-full">
                                    <div class="acc__input-error error-location text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="edit_training_date" class="form-label">Training Date <span class="text-danger">*</span></label>
                                    <input id="edit_training_date" type="text" name="training_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY - DD-MM-YYYY" data-format="DD-MM-YYYY">
                                    <div class="acc__input-error error-training_date text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="edit_cost" class="form-label">Cost</label>
                                    <input id="edit_cost" type="number" step="any" name="cost" class="form-control w-full">
                                </div>
                                <div>
                                    <label for="edit_expire_date" class="form-label">Expire Date</label>
                                    <input id="edit_expire_date" type="text" name="expire_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label class="form-label">Attachment</label>
                                    <div class="ep-doc-file-picker">
                                        <label for="editTraiDocument" class="ep-doc-btn ep-doc-btn--soft ep-doc-file-picker__trigger">
                                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                                            Upload Document
                                        </label>
                                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editTraiDocument"/>
                                        <span id="editTraiDocumentName" class="ep-doc-file-chip" style="display: none;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="updateTraining" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Update Training
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
        <!-- END: Edit Training Modal -->

        <!-- BEGIN: Add Appraisal Modal -->
        <div id="addAppraisalModal" class="modal ep-doc-modal ep-doc-modal--form ep-doc-modal--narrow" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="addAppraisalForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="award" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Add New Appraisal</h2>
                                    <p>Schedule a new appraisal by setting its due date.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-doc-form-grid ep-doc-form-grid--single">
                                <div>
                                    <label for="due_on" class="form-label">Due On <span class="text-danger">*</span></label>
                                    <input id="due_on" type="text" name="due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-due_on text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="saveAppraisal" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Save Appraisal
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
        <!-- END: Add Appraisal Modal -->

        <!-- BEGIN: Edit Appraisal Modal -->
        <div id="editAppraisalModal" class="modal ep-doc-modal ep-doc-modal--form" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form method="POST" action="#" id="editAppraisalForm" enctype="multipart/form-data">
                    <div class="modal-content">
                        <div class="modal-header ep-doc-modal__header">
                            <div class="ep-doc-modal__intro">
                                <span class="ep-doc-modal__icon">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </span>
                                <div>
                                    <h2>Edit Appraisal</h2>
                                    <p>Record outcomes, scores and promotion consideration.</p>
                                </div>
                            </div>
                            <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </a>
                        </div>
                        <div class="modal-body">
                            <div class="ep-doc-form-grid">
                                <div>
                                    <label for="edit_due_on" class="form-label">Due On <span class="text-danger">*</span></label>
                                    <input id="edit_due_on" readonly type="text" name="due_on" class="form-control w-full">
                                    <div class="acc__input-error error-due_on text-danger mt-2"></div>
                                </div>
                                <div>
                                    <label for="edit_completed_on" class="form-label">Completed On</label>
                                    <input id="edit_completed_on" type="text" name="completed_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                </div>
                                <div>
                                    <label for="edit_next_due_on" class="form-label">Next Due On</label>
                                    <input id="edit_next_due_on" type="text" name="next_due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                </div>
                                <div>
                                    <label for="edit_appraised_by" class="form-label">Appraised By</label>
                                    <select id="edit_appraised_by" name="appraised_by" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($activeEmployees))
                                            @foreach($activeEmployees as $aemp)
                                                <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_reviewed_by" class="form-label">Reviewed By</label>
                                    <select id="edit_reviewed_by" name="reviewed_by" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($activeEmployees))
                                            @foreach($activeEmployees as $aemp)
                                                <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label for="edit_total_score" class="form-label">Total Score</label>
                                    <input id="edit_total_score" type="number" step="any" name="total_score" class="form-control w-full">
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <div class="ep-doc-switch">
                                        <label class="form-check form-switch mb-0">
                                            <input id="edit_promotion_consideration" class="form-check-input" name="promotion_consideration" value="1" type="checkbox">
                                            <span class="ep-doc-switch__label">Consider for Promotion</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="ep-doc-form-grid__full">
                                    <label for="edit_notes" class="form-label">Note</label>
                                    <textarea id="edit_notes" name="notes" rows="3" class="form-control w-full"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                                <i data-lucide="x" class="w-4 h-4 mr-1"></i>Cancel
                            </button>
                            <button type="submit" id="updateAppraisal" class="btn btn-primary">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>Update Appraisal
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
        <!-- END: Edit Appraisal Modal -->

        <!-- BEGIN: View Appraisal Note Modal -->
        <div id="viewAppraisalNoteModal" class="modal ep-doc-modal ep-doc-modal--form" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header ep-doc-modal__header">
                        <div class="ep-doc-modal__intro">
                            <span class="ep-doc-modal__icon">
                                <i data-lucide="sticky-note" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2>Appraisal Note</h2>
                                <p>The note recorded against this appraisal.</p>
                            </div>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="ep-doc-modal__close">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="ep-doc-note-view"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn ep-doc-modal__cancel">
                            <i data-lucide="x" class="w-4 h-4 mr-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: View Appraisal Note Modal -->

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
                            <button type="button" data-tw-dismiss="modal" class="successCloser btn btn-primary">Ok</button>
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
                            <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary">Ok</button>
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
                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">No, Cancel</button>
                            <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger">Yes, I agree</button>
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
    @vite('resources/js/employee-appraisal.js')
    @vite('resources/js/employee-training.js')
@endsection
