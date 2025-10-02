<div class="intro-y box mt-5">
    <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">
            @if($reportItAll->status == 'Resolved' || $reportItAll->status == 'Rejected')
            Report Logs <span class="inline-block px-2 py-1 text-xs font-semibold text-green-800 bg-green-200 rounded">Case Closed By {{ $reportItAll->employee_name }}</span>
            @else
            Report Logs 
            @endif
        </h2>
        @if($reportItAll->status != 'Resolved' && $reportItAll->status != 'Rejected')
        <a href="javascript:;" class="flex btn btn-danger mr-2 click-close" data-id="{{ $reportItAll->id }}">
            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Close/Resolved
        </a>
        @else
           <a href="javascript:;" class="flex btn btn-primary mr-2 click-open" data-id="{{ $reportItAll->id }}">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Re-Open Case
                
            </a> 
        @endif
        @if($reportItAll->status != 'Resolved' && $reportItAll->status != 'Rejected')
        <a href="javascript:;" class="flex btn btn-primary" data-tw-toggle="modal" data-tw-target="#addModal">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add New
        </a>
        @endif
    </div>
    <div class="p-5">
        <form id="tabulatorFilterForm">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Log Entry By</div>
                        <input type="text" id="query" name="querystr" placeholder="Full name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-2">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Status</div>
                        <select id="status" name="status" class="w-full tom-selects" >
                            <option value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="col-span-3">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-4 text-right">
                    <div class="flex mt-5 sm:mt-0 justify-end">
                        <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                        </button>
                        <button id="tabulator-export-xlsx" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel
                            <svg id="excelExportBtn" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="gray" class="w-4 h-4 ml-2">
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
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="reportItAllTableId" data-report_id="{{ $reportItAll->id }}" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
</div>