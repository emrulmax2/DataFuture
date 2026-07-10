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

            <!-- ============= EMPLOYEE LOGIN LOG ============= -->
            <section class="ep-doc-card ep-doc-card--accent-teal">
                <div class="ep-doc-card__head">
                    <div class="ep-doc-card__head-main">
                        <span class="ep-doc-card__icon ep-doc-card__icon--teal">
                            <i data-lucide="monitor-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h2 class="ep-doc-card__title">Login Log</h2>
                            <p id="loginLogSummary" class="ep-doc-card__meta">Sign-in history and active sessions recorded for this employee.</p>
                        </div>
                    </div>
                </div>

                <div class="ep-doc-card__body">
                    <div class="ep-doc-toolbar">
                        <form id="tabulatorFilterForm" class="ep-doc-toolbar__form">
                            <div class="ep-doc-field ep-doc-field--status">
                                <label for="logout_reason">Status</label>
                                <select id="logout_reason" name="logout_reason" class="form-select">
                                    <option value="">All</option>
                                    <option value="active">Active (logged in)</option>
                                    <option value="manual_logout">Manual Logout</option>
                                    <option value="session_timeout">Session Timeout</option>
                                    <option value="session_invalidated">Session Invalidated</option>
                                </select>
                            </div>
                            <div class="ep-doc-field ep-doc-field--date">
                                <label for="date_from">From</label>
                                <input id="date_from" name="date_from" type="date" class="form-control">
                            </div>
                            <div class="ep-doc-field ep-doc-field--date">
                                <label for="date_to">To</label>
                                <input id="date_to" name="date_to" type="date" class="form-control">
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
                        <div id="loginLogTable" class="table-report table-report--tabulator ep-doc-table"></div>
                    </div>
                </div>
            </section>

        </div>

        <input type="hidden" id="actor_id" value="{{ $employee->user_id }}"/>
        <input type="hidden" id="actor_type" value="user"/>

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

    </div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/login-log_users.js')
@endsection
