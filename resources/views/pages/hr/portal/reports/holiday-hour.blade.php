@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-hour-report.css')
@endsection

@section('subcontent')
    {{-- New version (user's employee-hour-report design, 26 Nov 2026) beside the old one, as on the other HR
         reports. The Old pane is moved off screen rather than display:none. A New search fills the Old pane
         with the old result for the same dates; an Old search reloads the page, and the New version then
         loads the same dates and opens on the Old version. --}}
    <div class="ehr" data-ehr
         data-data-url="{{ route('hr.portal.reports.holiday.hour.new.data') }}"
         data-old-url="{{ route('hr.portal.reports.holiday.hour.new.old') }}"
         data-excel-url="{{ route('hr.portal.reports.holiday.hour.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}"
         data-from="{{ $from_date }}" data-to="{{ $to_date }}" data-searched="{{ $searched ? 1 : 0 }}">
        <div class="ehr-head">
            <div class="ehr-head__lead">
                <span class="ehr-head__icon" aria-hidden="true"><i data-lucide="clock-3"></i></span>
                <div>
                    <h1 class="ehr-head__title">Employee Hour Report</h1>
                    <p class="ehr-head__sub" id="ehrSub">London Churchill College · worked and holiday hours</p>
                </div>
            </div>
            <div class="ehr-head__actions">
                <div class="ehr-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-ehr-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-ehr-version="old">Old version</button>
                </div>
                <button type="button" class="ehr-btn" data-ehr-export data-ehr-new-only disabled>Download Excel</button>
                <a class="ehr-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="ehr-pane" data-ehr-pane="new">
            <form class="ehr-panel ehr-filters" role="search" aria-label="Date range" data-ehr-form novalidate>
                <div class="ehr-form">
                    <label class="ehr-field"><span>From<span class="ehr-req" aria-hidden="true"> *</span></span>
                        <input class="ehr-ctl" type="text" data-hr-date data-ehr-f="from" required>
                    </label>
                    <label class="ehr-field"><span>To</span>
                        <input class="ehr-ctl" type="text" data-hr-date data-ehr-f="to">
                    </label>
                    <div class="ehr-presets" role="group" aria-label="Quick date ranges">
                        <button type="button" class="ehr-preset" data-range="month">This month</button>
                        <button type="button" class="ehr-preset" data-range="last">Last month</button>
                        <button type="button" class="ehr-preset" data-range="term">1 Sep to 30 Nov</button>
                    </div>
                    <div class="ehr-form__end"><button type="submit" class="ehr-btn ehr-btn--primary">Search</button></div>
                </div>
                <p class="ehr-formnote" data-ehr-formnote hidden></p>
            </form>
            <div class="ehr-panel ehr-app" data-ehr-app aria-live="polite">
                <div class="ehr-empty">Choose a date range and select Search.</div>
            </div>
        </div>

        <div class="ehr-pane is-off" data-ehr-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="{{ route('hr.portal.reports.holiday.hour') }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">From <span class="text-danger">*</span></label>
                    <input readonly required type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="{{ (!empty($from_date) ? date('d-m-Y', strtotime($from_date)) : '') }}" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">To</label>
                    <input readonly required type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="{{ (!empty($to_date) ? date('d-m-Y', strtotime($to_date)) : '') }}" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-8 text-right mt-7">
                    <input type="hidden" name="action" value="search"/>
                    <button onclick="this.form.action.value = this.value" value="search" type="submit" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Search</button>
                    @if($searched)
                        <a href="{{ route('hr.portal.reports.holiday.hour.export', [(!empty($from_date) ? strtotime($from_date) : strtotime(date('Y-m-d'))), (!empty($to_date) ? strtotime($to_date) : '')]) }}" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Download Excel</a>
                    @endif
                </div>
            </div>
        </form>
        
        <div class="overflow-x-auto scrollbar-hidden mt-5" style="display: none;">
            <div id="employeeHOurCalcTable" class="table-report table-report--tabulator"></div>
        </div>

        @if($searched && !empty($search_result))
        <div class="overflow-x-auto mt-5">
            {!! $search_result !!}
        </div>
        @endif
    </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/holiday-hour-report.js')
    @vite('resources/js/hr-hour-report-new.js')
@endsection
