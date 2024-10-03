<h2 class="text-lg font-medium mr-auto mb-5">Student List</h2>
<div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
    <form id="tabulatorFilterForm-CLTML" class="xl:flex sm:mr-auto" >
        
        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
            <select id="status-CLTML" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                <option value="1">Active</option>
                <option value="2">Archived</option>
            </select>
        </div>
        <div class="mt-2 xl:mt-0">
            <button id="tabulator-html-filter-go-CLTML" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
            <button id="tabulator-html-filter-reset-CLTML" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
        </div>
    </form>
    <div class="flex justify-end mt-5 sm:mt-0" id="actionButtonWrap" style="display: none;">
        <button type="button" class="sendBulkSmsBtn btn btn-pending shadow-md text-white"><i data-lucide="smartphone" class="w-4 h-4 mr-2"></i>Send SMS</button>
        <button type="button" class="sendBulkMailBtn btn btn-success shadow-md text-white ml-1"><i data-lucide="mail" class="w-4 h-4 mr-2"></i>Send Email</button>
        @if(isset(auth()->user()->priv()['participant_export']) && auth()->user()->priv()['participant_export'] == 1)
        <button data-filename="{{ (isset($data->module) && !empty($data->module) ? str_replace(' ', '_', $data->module).'_student_lists.xlsx' : 'student_lists.xlsx') }}" data-planid="{{ $plan->id }}" id="exportStudentList" type="button" class="btn btn-primary shadow-md w-auto ml-1"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export
            <svg class="loaders" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                stroke="white" class="w-4 h-4 ml-2">
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
        @endif
    </div>
</div>
            
<div class="overflow-x-auto scrollbar-hidden">
    <div id="classStudentListTutorModuleTable" data-planid="{{ $plan->id }}" class="mt-5 table-report table-report--tabulator"></div>
</div>