@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'rit-page')

@section('styles')
    @vite('resources/css/report-it.css')
@endsection

@section('subcontent')
    <div class="rit">
        <div class="rit-head">
            <div>
                <h1 class="rit-head__title">All reported issues</h1>
                <div class="rit-head__sub">Reports raised by employees and students, across every campus</div>
            </div>
            <div class="rit-head__actions">
                <a href="{{ route('report.any.it.employee') }}" class="rit-btn rit-btn--ghost">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 6l-6 6 6 6"></path></svg>
                    My reports
                </a>
                <a href="{{ route('dashboard') }}" class="rit-btn rit-btn--solid">Back to Dashboard</a>
            </div>
        </div>

        <div class="rit-panel rit-panel--filters">
            <form id="tabulatorFilterForm">
                <div class="rit-filters rit-filters--wide">
                    <div class="rit-field">
                        <label for="report_number" class="rit-field__label">Reference</label>
                        <input type="text" id="report_number" name="report_number" value="" placeholder="Reference number" class="rit-input">
                    </div>
                    <div class="rit-field">
                        <label for="query" class="rit-field__label">Name</label>
                        <input type="text" id="query" name="querystr" value="" placeholder="Full name" class="rit-input">
                    </div>
                    <div class="rit-field rit-field--wide">
                        <label for="reportFrom" class="rit-field__label">From</label>
                        <select id="reportFrom" name="reportFrom[]" class="tom-selects rit-select w-full" multiple>
                            <option value="">Please Select</option>
                            <option value="Employee">Employee</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div class="rit-field rit-field--wide">
                        <label for="issue_type_id" class="rit-field__label">Type</label>
                        <select id="issue_type_id" name="issue_type_id[]" class="tom-selects rit-select w-full">
                            <option value="">Please Select</option>
                            @foreach($issueList as $issue)
                                <option value="{{ $issue->id }}">{{ $issue->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rit-field rit-field--wide">
                        <label for="statuses" class="rit-field__label">Condition</label>
                        <select id="statuses" name="statuses[]" class="tom-selects rit-select w-full" multiple>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="rit-field">
                        <label for="status" class="rit-field__label">Status</label>
                        <select id="status" name="status" class="tom-selects rit-select w-full">
                            <option value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>

                    <div class="rit-filters__actions">
                        <button id="tabulator-html-filter-go" type="button" class="rit-btn rit-btn--gold">Go</button>
                        <button id="tabulator-html-filter-reset" type="button" class="rit-btn rit-btn--ghost">Reset</button>
                    </div>
                    <div class="rit-filters__tools">
                        <button id="tabulator-print" type="button" class="rit-btn rit-btn--ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 9V4h11v5"></path><rect x="3.5" y="9" width="17" height="7" rx="2"></rect><path d="M6.5 16h11v4h-11z"></path></svg>
                            Print
                        </button>
                        <button id="tabulator-export-xlsx" type="button" class="rit-btn rit-btn--ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8.5L13.5 3z"></path><path d="M13.5 3v5.5H19M9.5 13l5 5M14.5 13l-5 5"></path></svg>
                            Export Excel
                            <svg id="excelExportBtn" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
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
                    </div>
                </div>
            </form>
        </div>

        <div class="rit-panel">
            <div class="rit-tablewrap scrollbar-hidden">
                <div id="reportItAllTableId" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

    {{-- Shared with the detail page, which is on the same shell. --}}
    @include('pages.students.report-it.modals.add-edit')
    @include('pages.students.report-it.modals.confirmation')
    @include('pages.students.report-it.modals.success')
    @include('pages.students.report-it.modals.error')
@endsection

@section('script')
    @vite('resources/js/report-it-all.js')
@endsection
