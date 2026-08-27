@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->

    @include('pages.students.admission.show-info')

    <!-- END: Profile Info -->
    <div class="adm-sections adm-comm">
    <div class="adm-section">
        <div class="adm-section__head">
            <div class="adm-section__title">Student Conversion Log</div>
        </div>
        <div class="adm-sectionbody">
            <div class="adm-tabletools">
                <form id="tabulatorFilterForm-CL" class="adm-tabletools__filters">
                    <span class="adm-filter-text">Query</span>
                    <input id="query-CL" name="query" type="text" class="adm-input" placeholder="Search...">
                    <span class="adm-filter-text">Status</span>
                    <div class="adm-field adm-field--narrow">
                        <select id="status-CL" name="status" class="adm-select">
                            <option selected value="">All</option>
                            <option value="queued">Queued</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <svg class="adm-field__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                    </div>
                    <button id="tabulator-html-filter-go-CL" type="button" class="adm-btn adm-btn--primary">Go</button>
                    <button id="tabulator-html-filter-reset-CL" type="button" class="adm-btn adm-btn--soft">Reset</button>
                </form>
                <div class="adm-tabletools__actions">
                    <button type="button" id="tabulator-print-CL" class="adm-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>Print</button>
                    <div class="dropdown adm-dropdown">
                        <button type="button" class="dropdown-toggle adm-btn" aria-expanded="false" data-tw-toggle="dropdown"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>Export<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg></button>
                        <div class="dropdown-menu">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-CL" href="javascript:;" class="dropdown-item">Export CSV</a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-CL" href="javascript:;" class="dropdown-item">Export XLSX</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="adm-table-wrap">
                <div id="conversionLogListTable" data-applicant="{{ $applicant->id }}" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    </div>

    <!-- BEGIN: Error Details Modal -->
    <div id="viewConversionErrorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Error Details: <span id="conversionErrorTitle"></span></h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <pre id="conversionErrorContent" class="text-xs whitespace-pre-wrap break-words bg-slate-50 p-4 rounded" style="max-height: 60vh; overflow-y: auto;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Error Details Modal -->
@endsection

@section('script')
    @vite('resources/js/admission-conversion-log.js')
    @vite('resources/js/admission-vue.js')
@endsection
