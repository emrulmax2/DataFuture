<div class="tm-section-head">
    <h2 class="tm-section-title">Student List</h2>
    <form id="tabulatorFilterForm-CLTML" class="tm-filter tm-participant-filter">
        <label>
            Status
            <select id="status-CLTML" name="status" class="form-select">
                <option value="1">Active</option>
                <option value="2">Archived</option>
            </select>
        </label>
        <button id="tabulator-html-filter-go-CLTML" type="button" class="btn btn-primary">Go</button>
        <button id="tabulator-html-filter-reset-CLTML" type="button" class="btn btn-secondary">Reset</button>
    </form>
</div>

<div class="tm-selected-bar" id="actionButtonWrap" style="display: none;">
    <div class="tm-selection-meta">
        <span class="tm-selected-count">0</span>
        <span class="tm-selection-label">selected</span>
        <button type="button" id="clearClassStudentSelection" class="tm-selection-clear">Clear</button>
    </div>
    <div class="tm-actions">
        <button type="button" class="sendBulkSmsBtn btn btn-pending shadow-md text-white">
            <i data-lucide="smartphone" class="w-4 h-4"></i> Send SMS
        </button>
        <button type="button" class="sendBulkMailBtn btn btn-success shadow-md text-white">
            <i data-lucide="mail" class="w-4 h-4"></i> Send Email
        </button>
        @if(isset(auth()->user()->priv()['participant_export']) && auth()->user()->priv()['participant_export'] == 1)
            <button data-filename="{{ (isset($data->module) && !empty($data->module) ? str_replace(' ', '_', $data->module).'_student_lists.xlsx' : 'student_lists.xlsx') }}" data-planid="{{ $plan->id }}" id="exportStudentList" type="button" class="btn btn-primary shadow-md w-auto">
                <i data-lucide="file-text" class="w-4 h-4"></i> Export
                <svg class="loaders" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
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
        @endif
    </div>
</div>

<div class="tm-table-wrap">
    <div id="classStudentListTutorModuleTable" data-planid="{{ $plan->id }}" class="table-report table-report--tabulator tm-table tm-participants-table"></div>
</div>
