<div class="tm-section-head">
    <h2 class="tm-section-title">Module Assessment</h2>
    <button data-tw-merge data-tw-toggle="modal" data-tw-target="#addAssessmentModal" class="btn btn-primary">
        <i data-lucide="plus" class="w-4 h-4"></i> Add An Assessment
        <i data-loading-icon="oval" data-color="white" class="w-4 h-4 hidden"></i>
    </button>
</div>

<div class="tm-toolbar">
    <form id="tabulatorFilterForm-ASMT" class="tm-filter">
        <label>
            Status
            <select id="status-ASMT" name="status" class="form-select">
                <option value="1">Active</option>
                <option value="2">Archived</option>
            </select>
        </label>
        <button id="tabulator-html-filter-go-ASMT" type="button" class="btn btn-primary">Go</button>
        <button id="tabulator-html-filter-reset-ASMT" type="button" class="btn btn-secondary">Reset</button>
    </form>
    <div class="tm-actions">
        <button id="tabulator-print-ASMT" class="btn btn-outline-secondary">
            <i data-lucide="printer" class="w-4 h-4"></i> Print
        </button>
        <div class="dropdown">
            <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                <i data-lucide="file-text" class="w-4 h-4"></i> Export <i data-lucide="chevron-down" class="w-4 h-4"></i>
            </button>
            <div class="dropdown-menu w-40">
                <ul class="dropdown-content">
                    <li>
                        <a id="tabulator-export-csv-ASMT" href="javascript:;" class="dropdown-item">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                        </a>
                    </li>
                    <li>
                        <a id="tabulator-export-xlsx-ASMT" href="javascript:;" class="dropdown-item">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="tm-table-wrap">
    <div id="classPlanAssessmentModuleTable" data-planid="{{ $plan->id }}" class="table-report table-report--tabulator tm-table tm-assessments-table"></div>
</div>

<!-- BEGIN: Add Assessment Modal -->
<div id="addAssessmentModal" class="modal tm-modal-hero" size="xl" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <span class="tm-modal-icon"><i data-lucide="file-plus-2" class="w-5 h-5"></i></span>
                <div class="tm-modal-heading">
                    <h2>Add an Assessment</h2>
                    <p>Schedule a new assessment for this module</p>
                </div>
                <a class="tm-modal-close" data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
            </div>
            <form method="post" id="saveModuleAssesment">
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12">
                            @if(!$assessmentlist->isEmpty())
                                <label for="course_module_base_assesment_id" class="form-label">Assessment <span class="text-danger">*</span></label>
                                <select id="course_module_base_assesment_id" class="assementlccTom lcc-tom-select form-select w-full" name="course_module_base_assesment_id">
                                    <option value="" selected>Please Select</option>
                                    @foreach($assessmentlist as $t)
                                        <option value="{{ $t->id }}">{{ $t->type->name }} - {{ $t->type->code }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-course_module_base_assesment_id text-danger mt-2"></div>
                            @else
                                <div class="alert alert-pending-soft show flex items-center col-span-12" role="alert">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-1"></i> No Assessment found!
                                </div>
                            @endif
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label for="publish_at" class="form-label">Publish Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="publish_at" class="form-control datepicker" name="publish_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-publish_at text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label for="visible_at" class="form-label">Visible Publish Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="visible_at" class="form-control datepicker" name="visible_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-visible_at text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label for="resubmission_at" class="form-label">Resubmission Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="resubmission_at" class="form-control datepicker" name="resubmission_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-resubmission_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                            <label for="resubmission_visible_at" class="form-label">Visible Resubmission Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="resubmission_visible_at" class="form-control datepicker" name="resubmission_visible_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-resubmission_visible_at text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                @if(!$assessmentlist->isEmpty())
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">Cancel</button>
                        <button type="submit" id="save" class="btn btn-primary w-auto">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Now
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
                @endif
                <input type="hidden" value="{{ $plan->id }}" name="plan_id"/>
            </form>
        </div>
    </div>
</div>
<!-- END: Add Assessment Modal -->

<!-- BEGIN: Import Result Modal -->
<div id="resultImportModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex items-center gap-3">
                    <span class="tm-modal-icon"><i data-lucide="file-up" class="w-5 h-5"></i></span>
                    <h2 class="font-medium text-base mr-auto">Import Result</h2>
                </div>
                <a data-tw-dismiss="modal" href="javascript:void(0);">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('result.upload-excel') }}" class="dropzone" id="bankholidayImportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="import_holiday_file" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop an excel file here with a selected option.</div>
                        <div class="text-gray-600">
                            Please choose <span class="font-medium">an option</span> below to upload result as new records or update the old records.
                        </div>
                    </div>
                    <input type="hidden" name="assessment_plan_id" value=""/>
                    <input type="hidden" name="upload_type" value="add"/>
                </form>

                <div class="tm-radio-stack">
                    <label for="radio-switch-1">
                        <input id="radio-switch-1" name="upload_type_select" value="add" checked type="radio" />
                        <span><i data-lucide="plus-square" class="w-4 h-4"></i>Add Results</span>
                    </label>
                    <label for="radio-switch-2">
                        <input id="radio-switch-2" name="upload_type_select" value="update" type="radio" />
                        <span><i data-lucide="check-square" class="w-4 h-4"></i>Update Results</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                <button id="saveImportResult" class="btn btn-primary w-auto">
                    Upload
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-1">
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
<!-- END: Import Result Modal -->

<style>
    /* The theme reparents .modal to <body> on show, so these are keyed off the body class
       rather than #tutorModuleDetails, which is no longer an ancestor at that point. */
    body.tutor-module-body .tm-modal-hero .modal-dialog {
        max-width: 640px;
    }

    body.tutor-module-body .tm-modal-hero .modal-content {
        border-radius: 16px;
        overflow: hidden;
    }

    /* ---- Hero header ---- */

    body.tutor-module-body .tm-modal-hero .modal-header {
        align-items: center;
        background: linear-gradient(135deg, #0f2d2a 0%, #17423b 100%);
        border-bottom: 0;
        display: flex;
        gap: 14px;
        padding: 20px 22px;
        position: relative;
    }

    body.tutor-module-body .tm-modal-hero .modal-header::after {
        background: radial-gradient(circle, rgba(198,164,78,.18), transparent 70%);
        border-radius: 999px;
        content: "";
        height: 190px;
        pointer-events: none;
        position: absolute;
        right: -50px;
        top: -90px;
        width: 190px;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-icon {
        align-items: center;
        background: rgba(198,164,78,.18);
        border: 1px solid rgba(198,164,78,.38);
        border-radius: 11px;
        color: #dcb964;
        display: inline-flex;
        flex: 0 0 42px;
        height: 42px;
        justify-content: center;
        position: relative;
        width: 42px;
        z-index: 1;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-heading {
        margin-right: auto;
        min-width: 0;
        position: relative;
        z-index: 1;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-heading h2 {
        color: #fff;
        font-family: "IBM Plex Serif", Georgia, serif;
        font-size: 17px;
        font-weight: 600;
        line-height: 1.25;
        margin: 0;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-heading p {
        color: rgba(255,255,255,.58);
        font-size: 12px;
        line-height: 1.4;
        margin: 3px 0 0;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-close {
        align-items: center;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 9px;
        color: rgba(255,255,255,.72);
        display: inline-flex;
        flex: 0 0 32px;
        height: 32px;
        justify-content: center;
        position: relative;
        transition: background .12s ease-in-out, color .12s ease-in-out;
        width: 32px;
        z-index: 1;
    }

    body.tutor-module-body .tm-modal-hero .tm-modal-close:hover {
        background: rgba(255,255,255,.16);
        color: #fff;
    }

    /* ---- Body ---- */

    body.tutor-module-body .tm-modal-hero .modal-body {
        background: #fff;
        padding: 22px;
    }

    body.tutor-module-body .tm-modal-hero .form-label {
        color: #12312e;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    body.tutor-module-body .tm-modal-hero .form-label .text-danger {
        color: #b3261e;
    }

    /* The shared modal rule paints controls cream; this design wants them white. */
    body.tutor-module-body .tm-modal-hero .form-control,
    body.tutor-module-body .tm-modal-hero .form-select {
        background-color: #fff !important;
        border: 1px solid #e2e6e3 !important;
        border-radius: 10px !important;
        color: #12312e !important;
        font-size: 13.5px !important;
        min-height: 46px;
        padding: 12px 14px !important;
    }

    /* Keep the gutter the select's chevron is painted into — the padding above would
       otherwise let the chosen option run underneath it. */
    body.tutor-module-body .tm-modal-hero .form-select {
        padding-right: 38px !important;
    }

    body.tutor-module-body .tm-modal-hero .form-control:focus,
    body.tutor-module-body .tm-modal-hero .form-select:focus {
        border-color: #0d7c73 !important;
        box-shadow: 0 0 0 3px rgba(13,124,115,.12) !important;
    }

    /* Dates read as mono and carry a calendar affordance on the right. */
    body.tutor-module-body .tm-modal-hero .datepicker {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%238b9995' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='4' rx='2'/%3E%3Cpath d='M3 10h18M8 2v4M16 2v4'/%3E%3C/svg%3E");
        background-position: right 13px center;
        background-repeat: no-repeat;
        background-size: 16px 16px;
        font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
        letter-spacing: .02em;
        padding-right: 40px !important;
    }

    body.tutor-module-body .tm-modal-hero .datepicker::placeholder {
        color: #b6c0bd;
    }

    /* ---- Footer ---- */

    body.tutor-module-body .tm-modal-hero .modal-footer {
        background: #fafaf8;
        border-top: 1px solid #eef0ea;
        gap: 10px;
        padding: 16px 22px;
    }

    body.tutor-module-body .tm-modal-hero .modal-footer .btn {
        border-radius: 10px !important;
        min-height: 42px;
        padding: 10px 20px !important;
    }

    body.tutor-module-body .tm-modal-hero .modal-footer .btn-outline-secondary {
        background: #fff !important;
        border: 1px solid #dfe4e2 !important;
        color: #3f524f !important;
        margin-right: 0 !important;
    }

    /* Add Now is the primary action here, so it reads solid rather than the tinted
       btn-primary the shared modal rule applies. */
    body.tutor-module-body .tm-modal-hero .modal-footer .btn-primary {
        background: #0d7c73 !important;
        border: 1px solid #0d7c73 !important;
        color: #fff !important;
    }

    body.tutor-module-body .tm-modal-hero .modal-footer .btn-primary:hover {
        background: #0a655d !important;
        border-color: #0a655d !important;
    }

    body.tutor-module-body .tm-modal-icon {
        align-items: center;
        background: rgba(13,124,115,.12);
        border-radius: 11px;
        color: #0d7c73;
        display: inline-flex;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    body.tutor-module-body .dropzone {
        background: #fbfcfb !important;
        border: 2px dashed #cdd6cf !important;
        border-radius: 16px !important;
        color: #3a4a47;
        min-height: 170px;
    }

    body.tutor-module-body .tm-radio-stack {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 20px;
    }

    body.tutor-module-body .tm-radio-stack label {
        align-items: center;
        border: 1px solid #e6e8e3;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        gap: 12px;
        padding: 13px 16px;
    }

    body.tutor-module-body .tm-radio-stack label:has(input:checked) {
        background: rgba(13,124,115,.06);
        border-color: #0d7c73;
    }

    body.tutor-module-body .tm-radio-stack span {
        align-items: center;
        color: #12312e;
        display: inline-flex;
        font-size: 13.5px;
        font-weight: 600;
        gap: 8px;
    }
</style>
