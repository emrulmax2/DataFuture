<div class="ss-table-card ss-bankholidays-card">
    <div class="ss-table-card__header">
        <h2>Bank Holidays</h2>
        <button data-tw-toggle="modal" data-tw-target="#bankholidayAddModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
            <i data-lucide="plus"></i>
            Add Bank Holiday
        </button>
    </div>

    <div class="ss-table-tools">
        <form id="tabulatorFilterForm" class="ss-table-filter">
            <div class="ss-filter-field">
                <span>Query</span>
                <label class="ss-filter-input" for="query">
                    <i data-lucide="search"></i>
                    <input id="query" name="query" type="text" placeholder="Search...">
                </label>
            </div>
            <div class="ss-filter-field">
                <span>Status</span>
                <label class="ss-filter-select" for="status">
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                    <i data-lucide="chevron-down"></i>
                </label>
            </div>
            <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
            <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
        </form>

        <div class="ss-table-actions">
            <button id="tabulator-print" type="button" class="ss-btn ss-btn--light ss-btn--tool">
                <i data-lucide="printer"></i>
                Print
            </button>
            <div class="dropdown ss-export-dropdown">
                <button type="button" class="dropdown-toggle ss-btn ss-btn--light ss-btn--tool" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="download"></i>
                    Export
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="dropdown-menu ss-export-menu">
                    <ul class="dropdown-content">
                        <li>
                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text"></i>
                                Export CSV
                            </a>
                        </li>
                        <li>
                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-spreadsheet"></i>
                                Export XLSX
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <button data-tw-toggle="modal" data-tw-target="#bankholidayImportModal" type="button" class="ss-btn ss-btn--light ss-btn--tool">
                <i data-lucide="upload"></i>
                Import Holiday
            </button>
        </div>
    </div>

    <div class="ss-tabulator-wrap">
        <div id="bankholidayTableId" data-academicyearid="{{ $academicyear->id }}" class="ss-tabulator table-report table-report--tabulator"></div>
    </div>
</div>
