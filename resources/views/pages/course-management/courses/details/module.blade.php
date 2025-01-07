
<div class="intro-y box p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                    <option value="2">Archived</option>
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
            </div>
        </form>
        <div class="flex mt-5 sm:mt-0">
            <a href="{{ route('course.module.export', $course->id) }}" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Download Excel
            </a>
            <button data-tw-toggle="modal" data-tw-target="#courseModuleAddModal" type="button" class="add_btn btn btn-primary shadow-md ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Module</button>
        </div>
    </div>
    <div class="overflow-x-auto scrollbar-hidden">
        <div id="courseModuleTableId" data-courseid="{{ $course->id }}" class="mt-5 table-report table-report--tabulator"></div>
    </div>
</div>