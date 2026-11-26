@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-eligibility-expiry.css')
@endsection

@section('subcontent')
    {{-- New version (user's eligibility-expiry-report design, 26 Nov 2026) beside the old one, as on the other
         HR reports. The Old pane is moved off screen rather than display:none, so a New search can fill it
         and run it while hidden. --}}
    @php($eerEthnicities = \App\Models\Ethnicity::orderBy('name')->get()->map(fn ($e) => [$e->id, $e->name, (int) $e->active])->values())
    <div class="eer" data-eer
         data-data-url="{{ route('hr.portal.reports.eligibilityreport.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.eligibilityreport.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.eligibilityreport.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-ethnicities='@json($eerEthnicities)'>
        <div class="eer-head">
            <div class="eer-head__lead">
                <span class="eer-head__icon" aria-hidden="true"><i data-lucide="shield-check"></i></span>
                <div>
                    <h1 class="eer-head__title">Eligibility Expiry Report</h1>
                    <p class="eer-head__sub" id="eerSub">London Churchill College · visa, work permit and passport expiry</p>
                </div>
            </div>
            <div class="eer-head__actions">
                <div class="eer-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-eer-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-eer-version="old">Old version</button>
                </div>
                <button type="button" class="eer-btn" data-eer-export="excel" data-eer-new-only>Export XLSX</button>
                <button type="button" class="eer-btn eer-btn--primary" data-eer-export="pdf" data-eer-new-only>Download PDF</button>
                <a class="eer-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="eer-pane" data-eer-pane="new">
            @include('pages.hr.portal.reports.eligibility.new')
        </div>

        <div class="eer-pane is-off" data-eer-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <h2 class="text-lg font-medium mr-auto">Visa Expiry Report</h2>
        <div class="grid grid-cols-12 gap-4 p-5">
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
                    <div class="flex mt-5 sm:mt-0">
                        <button id="tabulator-export-xlsx-VEXR" href="javascript:;" class="btn btn-outline-secondary w-1/2 w-auto mr-2">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                        </button>
                        <a style="float: right;" type="button" href="{{ route('hr.portal.reports.eligibilityreport.visa.pdf') }}" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="visaExpiryListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <h2 class="text-lg font-medium mr-auto">Passport Expiry Report</h2>
        <div class="grid grid-cols-12 gap-4 p-5">
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
                        <a style="float: right;" type="button" href="{{ route('hr.portal.reports.eligibilityreport.passport.pdf') }}" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="passportExpiryListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-eligibilityreport.js')
    @vite('resources/js/hr-eligibility-expiry-new.js')
@endsection
