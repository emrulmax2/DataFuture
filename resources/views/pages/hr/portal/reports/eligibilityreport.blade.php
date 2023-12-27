@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Eligibility Expiry Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button id="tabulator-export-xlsx-VEXR" href="javascript:;" class="btn btn-outline-secondary w-1/2 w-auto mr-2">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
            </button>
            <a style="float: right;" type="button" href="{{ route('hr.portal.reports.eligibilityreport.pdf') }}" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Employment Reports</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm-SR" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                            <input id="query-VEXR" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go-VEXR" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset-VEXR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                        </div>
                    </form>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="visaExpiryListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm-SR" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                            <input id="query-PEXR" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go-PEXR" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset-PEXR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                        </div>
                    </form>
                    <div class="flex mt-5 sm:mt-0">
                        <button id="tabulator-export-xlsx-PEXR" href="javascript:;" class="btn btn-outline-secondary w-1/2 w-auto mr-2">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="passportExpiryListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-eligibilityreport.js')
@endsection