@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Report any IT Issue For {{ $employee->full_name }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                <button data-tw-toggle="modal" data-tw-target="#addModal" class="add_btn btn btn-primary shadow-md mr-2"><i data-lucide="plus" class="w-4 h-4 mr-2"></i>Add New</button>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1 whitespace-nowrap">Name.</div>
                        <input type="text" id="query" name="querystr" placeholder="Full name" value="" class="w-full"/>
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="flex">
                        <div class="z-30 px-2 rounded-l w-auto flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400 -mr-1">Condition</div>
                        <select id="statuses" name="statuses[]" class="w-full tom-selects" multiple>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
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
                <div class="col-span-12"></div>
                <div class="col-span-6">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-6 text-right">
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
            <div id="reportItAllTableId" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>

    @include('pages.students.report-it.employee.modals.add-edit')
    @include('pages.students.report-it.employee.modals.confirmation')
    @include('pages.students.report-it.employee.modals.success')
    @include('pages.students.report-it.employee.modals.error')
@endsection

@section('script')
    @vite('resources/js/report-any-it-employee.js')
@endsection