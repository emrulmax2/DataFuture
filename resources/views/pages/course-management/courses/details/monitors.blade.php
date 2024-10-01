<div class="intro-y box p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulatorFilterForm03" class="xl:flex sm:mr-auto" >
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                <input id="query-03" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                <select id="status-03" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                    <option value="1">Active</option>
                    <option value="2">Archived</option>
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go-03" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                <button id="tabulator-html-filter-reset-03" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
            </div>
        </form>
        <div class="flex mt-5 sm:mt-0">
            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
            </button>
            <div class="dropdown w-1/2 sm:w-auto">
                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                </button>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li>
                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                            </a>
                        </li>
                        {{-- <li>
                            <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                            </a>
                        </li> --}}
                        <li>
                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                            </a>
                        </li>
                        {{-- <li>
                            <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </div>
            <button data-tw-toggle="modal" data-tw-target="#courseMonitorAddModal" type="button" class="add_btn btn btn-primary shadow-md ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Monitory Account</button>
        </div>
    </div>
    <div class="overflow-x-auto scrollbar-hidden">
        <div id="courseMonitorTableId" data-courseid="{{ $course->id }}" class="mt-5 table-report table-report--tabulator"></div>
    </div>
</div>