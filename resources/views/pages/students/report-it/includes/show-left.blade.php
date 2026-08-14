@php
    $ritClosed = in_array($reportItAll->status, ['Resolved', 'Rejected'], true);
@endphp

<div class="rit-panel">
    <div class="rit-panel__head">
        <h2 class="rit-panel__title">Report logs</h2>
        @if($ritClosed && !empty($reportItAll->employee_name))
            <span class="rit-chip rit-chip--resolved">Closed by {{ $reportItAll->employee_name }}</span>
        @endif
        <div class="rit-panel__actions">
            @if($ritClosed)
                <a href="javascript:;" class="click-open rit-btn rit-btn--ghost" data-id="{{ $reportItAll->id }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5A8 8 0 1 1 17.6 6"></path><path d="M20 4v5h-5"></path></svg>
                    Re-open case
                </a>
            @else
                <a href="javascript:;" class="click-close rit-btn rit-btn--danger" data-id="{{ $reportItAll->id }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m8.8 8.8 6.4 6.4"></path></svg>
                    Close / Resolve
                </a>
                <a href="javascript:;" class="rit-btn rit-btn--solid" data-tw-toggle="modal" data-tw-target="#addModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5.5v13M5.5 12h13"></path></svg>
                    Add New
                </a>
            @endif
        </div>
    </div>

    <div class="rit-panel__filters">
        <form id="tabulatorFilterForm">
            <div class="rit-filters rit-filters--logs">
                <div class="rit-field">
                    <label for="query" class="rit-field__label">Log entry by</label>
                    <input type="text" id="query" name="querystr" value="" placeholder="Full name" class="rit-input">
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

    <div class="rit-tablewrap scrollbar-hidden">
        <div id="reportItAllTableId" data-report_id="{{ $reportItAll->id }}" class="table-report table-report--tabulator"></div>
    </div>
</div>
