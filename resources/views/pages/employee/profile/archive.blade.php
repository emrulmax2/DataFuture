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

            <!-- ============= EMPLOYEE ARCHIVE ============= -->
            <section class="ep-doc-card ep-doc-card--accent-teal">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                            <i data-lucide="archive" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Archive</h2>
                            <p id="employeeArchiveSummary" class="ep-doc-card__meta">Audit trail of changes recorded against this employee.</p>
                        </div>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm-AN" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--query">
                                <label for="query-ARC">Query</label>
                                <input id="query-ARC" name="query" type="text" class="form-control" placeholder="Search archive...">
                            </div>
                            <div class="ep-doc-toolbar__filters">
                                <button id="tabulator-html-filter-go-ARC" type="button" class="ep-doc-btn ep-doc-btn--primary">Go</button>
                                <button id="tabulator-html-filter-reset-ARC" type="button" class="ep-doc-btn ep-doc-btn--ghost">Reset</button>
                            </div>
                        </form>

                        <div class="ep-doc-toolbar__actions">
                            <button id="tabulator-print-ARC" type="button" class="ep-doc-btn ep-doc-btn--ghost">
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
                                            <a id="tabulator-export-csv-ARC" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                                Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-ARC" href="javascript:;" class="dropdown-item">
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
                        <div id="employeeArchiveListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </div>
            </section>

        </div>

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
    @vite('resources/js/employee-archive.js')
@endsection
