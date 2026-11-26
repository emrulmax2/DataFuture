@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-birthday-report.css')
@endsection

@section('subcontent')
    {{-- New version (user's birthday-list-report design, 26 Nov 2026) beside the old one, as on
         Reports › Student Result. Toggle buttons, not role="tab": the theme's tab script grabs every
         [role=tab]. The Old pane is moved off screen rather than display:none, so its table can be
         built while hidden when a New search fills it in. --}}
    <div class="bdl" data-bdl
         data-data-url="{{ route('hr.portal.reports.birthdaylist.new.data') }}"
         data-pdf-url="{{ route('hr.portal.reports.birthdaylist.new.pdf') }}"
         data-excel-url="{{ route('hr.portal.reports.birthdaylist.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}">
        <div class="bdl-head">
            <div class="bdl-head__lead">
                <span class="bdl-head__icon" aria-hidden="true"><i data-lucide="cake"></i></span>
                <div>
                    <h1 class="bdl-head__title">Birthday List</h1>
                    <p class="bdl-head__sub" id="bdlSub">Staff birthdays for the year ahead.</p>
                </div>
            </div>
            <div class="bdl-head__actions">
                <div class="bdl-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-bdl-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-bdl-version="old">Old version</button>
                </div>
                <button type="button" class="bdl-btn" data-bdl-export="excel" data-bdl-new-only>Export XLSX</button>
                <button type="button" class="bdl-btn bdl-btn--primary" data-bdl-export="pdf" data-bdl-new-only>Download PDF</button>
                <a class="bdl-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="bdl-pane" data-bdl-pane="new">
            @include('pages.hr.portal.reports.birthday.new')
        </div>

        <div class="bdl-pane is-off" data-bdl-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form id="tabulatorFilterForm-BR">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="text" id="dob-BR" name="dob-BR" placeholder="Select Month of Birth" value="" data-format="YYYY-MM"  data-single-mode="true" class="w-full datepicker form-control"/>
                </div>
                <div class="col-span-4">
                    <label class="form-label">Type</label>
                    <select id="employee_work_type_id-birtdaylist" class="lccTom lcc-tom-select w-full form-control" name="employee_work_type_id">
                        <option value="" selected>Please Select</option>
                        @if($employeeWorkType->count() > 0)
                            @foreach($employeeWorkType as $si)
                                <option {{ isset($employment->employee_work_type_id) && $employment->employee_work_type_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="form-label">Department</label>
                    <select id="department_id-birtdaylist" name="department_id" class="w-full lccTom lcc-tom-select form-control">
                        <option value="" selected>Please Select</option>
                        @foreach($departments as $si)
                            <option {{ isset($employment->department_id) && $employment->department_id == $si->id }} value="{{ $si->id }}">{{ $si->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-4">
                    <label class="form-label">Status</label>
                    <select id="status_id-birtdaylist" name="status_id" class="w-full lccTom lcc-tom-select form-control">
                        <option value="1">Active</option>
                        <option value="0">In Active</option>
                        <option value="2">All</option>
                    </select>
                </div>
                <div class="col-span-4 mt-7">
                    <button id="tabulator-html-filter-go-BR" type="button" class="btn btn-primary w-auto">Go</button>
                    <button id="tabulator-html-filter-reset-BR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
                <div class="col-span-4 mt-7 text-right">
                    <div class="w-full w-auto flex justify-end">
                        <button id="bdayListbySearchExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2" style="display: none">Export XLSX</button>
                        <button id="bdayListbySearchPdfBtn"  class="btn btn-success text-white w-1/2 w-auto mr-2" style="display: none">Download Pdf</button>
                        <a href="{{route('hr.portal.reports.birthdaylist.excel')}}" id="allBdayListExcelBtn" class="btn btn-secondary w-1/2 w-auto mr-2">Export XLSX</a>
                        <a href="{{route('hr.portal.reports.birthdaylist.pdf')}}" id="allBdayListPdfBtn" class="btn btn-success text-white w-1/2 w-auto mr-2">Download Pdf</a>
                    </div>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden w-full">
            <div id="birthdayListSearchTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/hr-portal-birthdayreport.js')
    @vite('resources/js/hr-birthday-report-new.js')
@endsection
