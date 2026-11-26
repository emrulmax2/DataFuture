@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-outstanding-holiday.css')
@endsection

@section('subcontent')
    {{-- New version (user's outstanding-holiday-report design, 26 Nov 2026) beside the old one, as on the other
         HR reports. The Old pane is moved off screen rather than display:none, so a New search can fill its
         form and run it while hidden; an Old search fills the New form and runs it. --}}
    @php
        $eohToday = date('Y-m-d');
        $eohYear = $holiday_years->first(fn ($y) => date('Y-m-d', strtotime($y->start_date)) <= $eohToday && date('Y-m-d', strtotime($y->end_date)) >= $eohToday) ?? $holiday_years->first();
    @endphp
    <div class="eoh" data-eoh
         data-data-url="{{ route('hr.portal.reports.outstanding.holiday.new.data') }}"
         data-excel-url="{{ route('hr.portal.reports.outstanding.holiday.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}">
        <div class="eoh-head">
            <div class="eoh-head__lead">
                <span class="eoh-head__icon" aria-hidden="true"><i data-lucide="calendar-clock"></i></span>
                <div>
                    <h1 class="eoh-head__title">Outstanding Holiday Report</h1>
                    <p class="eoh-head__sub" id="eohSub">London Churchill College · holiday allocated, taken and still owed</p>
                </div>
            </div>
            <div class="eoh-head__actions">
                <div class="eoh-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-eoh-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-eoh-version="old">Old version</button>
                </div>
                <button type="button" class="eoh-btn" data-eoh-export data-eoh-new-only disabled>Download Excel</button>
                <a class="eoh-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="eoh-pane" data-eoh-pane="new">
            <form class="eoh-panel eoh-filters" role="search" aria-label="Report filters" data-eoh-form novalidate>
                <div class="eoh-form">
                    <label class="eoh-field"><span>Holiday year<span class="eoh-req" aria-hidden="true"> *</span></span>
                        <select class="eoh-ctl" data-eoh-f="year" required>
                            @foreach($holiday_years as $y)
                                <option value="{{ $y->id }}" data-start="{{ date('Y-m-d', strtotime($y->start_date)) }}" data-end="{{ date('Y-m-d', strtotime($y->end_date)) }}" @selected($eohYear && $eohYear->id === $y->id)>{{ date('Y', strtotime($y->start_date)).' - '.date('Y', strtotime($y->end_date)) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="eoh-field"><span>From<span class="eoh-req" aria-hidden="true"> *</span></span><input class="eoh-ctl" type="text" data-hr-date data-eoh-f="from" required></label>
                    <label class="eoh-field"><span>To<span class="eoh-req" aria-hidden="true"> *</span></span><input class="eoh-ctl" type="text" data-hr-date data-eoh-f="to" required></label>
                    <label class="eoh-field"><span>Status</span>
                        <select class="eoh-ctl" data-eoh-f="status">
                            @foreach(\App\Services\HR\OutstandingHoliday::STATUSES as $v => $label)<option value="{{ $v }}">{{ $label }}</option>@endforeach
                        </select>
                    </label>
                    <div class="eoh-form__end">
                        <button type="button" class="eoh-btn" data-eoh-reset>Reset</button>
                        <button type="submit" class="eoh-btn eoh-btn--primary">Search</button>
                    </div>
                </div>
                <p class="eoh-formnote" data-eoh-formnote hidden></p>
            </form>
            <div class="eoh-panel eoh-app" data-eoh-app aria-live="polite">
                <div class="eoh-empty">Choose a holiday year and dates, then select Search.</div>
            </div>
        </div>

        <div class="eoh-pane is-off" data-eoh-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="#" id="outstandingHolidayReportForm">
            @csrf
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">Holiday Year <span class="text-danger">*</span></label>
                    <select name="holiday_year_id" id="holiday_year_id" class="w-full tom-selects tomRequire">
                        <option value="">Please Select</option>
                        @if($holiday_years->count() > 0)
                            @foreach($holiday_years as $year)
                                <option data-start="{{ date('Y-m-d', strtotime($year->start_date)) }}" data-end="{{ date('Y-m-d', strtotime($year->end_date)) }}" value="{{ $year->id }}">{{ date('Y', strtotime($year->start_date)).' - '.date('Y', strtotime($year->end_date)) }}</option>
                            @endforeach
                        @endif
                    </select> 
                    <div class="acc__input-error error-holiday_year_id text-danger mt-2"></div>                
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">From <span class="text-danger">*</span></label>
                    <input readonly type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control require"/>   
                    <div class="acc__input-error error-from_date text-danger mt-2"></div>                 
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">To <span class="text-danger">*</span></label>
                    <input readonly type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control require"/>  
                    <div class="acc__input-error error-to_date text-danger mt-2"></div>                  
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="w-full form-control">
                        <option value="2">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>                 
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-4 text-right mt-7">
                    <input type="hidden" name="action" value="search"/>
                    <button onclick="this.form.action.value = this.value" value="search" type="submit" id="oshSearchBtn" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" >
                        Search 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                    <button onclick="this.form.action.value = this.value" value="export" type="submit" id="oshExportBtn" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" style="display: none;" >
                        Download Excel 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                    <button id="oshResetBtn" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        
        <div class="overflow-x-auto scrollbar-hidden mt-5 outStandingHolidayReportWrap" style="display: none;">
            <div id="outStandingHolidayReportTable" class="table-report table-report--tabulator"></div>
        </div>
    </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/outstanding-holiday-report.js')
    @vite('resources/js/hr-outstanding-holiday-new.js')
@endsection
