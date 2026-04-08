@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Active Students by Date</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form action="#" method="post" id="activeStudentsListForm">
            @csrf
            <div class="flex justify-start">
                <div>
                    <label for="date" class="form-label block">Date <span class="text-danger">*</span></label>
                    <input id="date" type="text" name="date" class="datepicker form-control w-80" data-format="DD-MM-YYYY"  data-single-mode="true">
                    <div class="acc__input-error error-date text-danger hidden"></div>
                </div>
                <div class="ml-3 pt-7">
                    <button id="activeStudentsListFormBtn" type="submit" class="btn btn-success text-white ml-auto  w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden loadingCall"></i></button>
                    <button id="activeStudentsListFormReset"type="button" class="btn btn-secondary w-auto inline-flex ml-1"><i class="w-4 h-4 mr-2" data-lucide="refresh-cw"></i> Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div class=" intro-y box p-5 mt-5 mb-5">
        <div class="grid grid-cols-12 items-center" id="reportRowCountWrap">
            <div id="reportTotalRowCount" class="col-span-12 sm:col-span-6 items-center text-left font-medium ">Total Student(s) Found: <div id="totalCount" class="inline-block ml-2">0</div></div>
            <div class="col-span-12 sm:col-span-6 text-right">
                <button type="button" id="activeStudentReportExcelBtn" class="btn btn-primary w-auto" disabled>
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel 
                    <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 loadingCall" style="display: none;">
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
            </div>
        </div>
        <div id="activeStudentsTableWrap" class="overflow-x-auto scrollbar-hidden pt-5 statusReportListTableWrap" style="display: none;">
            <div id="activeStudentsTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/active_students_by_date.js')
@endsection