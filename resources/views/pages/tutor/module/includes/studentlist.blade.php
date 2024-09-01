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
    </div>
</div>
            
<div class="overflow-x-auto scrollbar-hidden">
    <div id="classStudentListTutorModuleTable" data-planid="{{ $plan->id }}" class="mt-5 table-report table-report--tabulator"></div>
</div>