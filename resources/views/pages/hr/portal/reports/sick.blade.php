@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/hr-sick-leave.css')
@endsection

@section('subcontent')
    {{-- New version (user's employee-sick-leave design, 26 Nov 2026) beside the old one, as on the other HR
         reports. The Old pane is moved off screen rather than display:none, so a New search can fill its form
         and run it while hidden; an old Search fills the New form and runs it. --}}
    <div class="esk" data-esk
         data-data-url="{{ route('hr.portal.reports.sick.leave.new.data') }}"
         data-excel-url="{{ route('hr.portal.reports.sick.leave.new.excel') }}"
         data-profile-url="{{ route('profile.employee.view', '__ID__') }}">
        <div class="esk-head">
            <div class="esk-head__lead">
                <span class="esk-head__icon" aria-hidden="true"><i data-lucide="thermometer"></i></span>
                <div>
                    <h1 class="esk-head__title">Employee Sick Leave</h1>
                    <p class="esk-head__sub" id="eskSub">London Churchill College · HR reports</p>
                </div>
            </div>
            <div class="esk-head__actions">
                <div class="esk-versions" role="group" aria-label="Report version">
                    <button type="button" aria-pressed="true" data-esk-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-esk-version="old">Old version</button>
                </div>
                <button type="button" class="esk-btn" data-esk-export data-esk-new-only disabled>Export XL</button>
                <a class="esk-back" href="{{ route('hr.portal.employment.reports.show') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Employment Reports
                </a>
            </div>
        </div>

        <div class="esk-pane" data-esk-pane="new">
            <form class="esk-panel esk-filters" role="search" aria-label="Report filters" data-esk-form novalidate>
                <div class="esk-form">
                    <label class="esk-field"><span>Employee</span>
                        <select class="esk-ctl" data-esk-f="emp">
                            <option value="">All employees</option>
                            @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ trim($emp->first_name.' '.$emp->last_name) }}</option>@endforeach
                        </select>
                    </label>
                    <label class="esk-field"><span>Period</span>
                        <select class="esk-ctl" data-esk-f="period">
                            @foreach(\App\Services\HR\SickLeave::PERIODS as $v => $label)<option value="{{ $v }}" @selected((string) $v === '30')>{{ $label }}</option>@endforeach
                        </select>
                    </label>
                    <label class="esk-field"><span>From</span><input class="esk-ctl" type="text" data-hr-date data-esk-f="from" disabled></label>
                    <label class="esk-field"><span>To</span><input class="esk-ctl" type="text" data-hr-date data-esk-f="to" disabled></label>
                    <div class="esk-form__end"><button type="submit" class="esk-btn esk-btn--primary">Search</button></div>
                </div>
                <p class="esk-formnote" data-esk-formnote hidden></p>
            </form>
            <div class="esk-panel esk-app" data-esk-app aria-live="polite">
                <div class="esk-empty">Choose a period and select Search.</div>
            </div>
        </div>

        <div class="esk-pane is-off" data-esk-pane="old" aria-hidden="true">
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="#">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id[]" multiple class="lccTom lcc-tom-select w-full form-control">
                        @if(!empty($employees))
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach 
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="form-label">Days</label>
                    <select id="no_of_days" name="no_of_days" class="w-full form-control">
                        <option value="">Please Select</option>
                        <option selected value="30">Last 30 Days</option>
                        <option value="45">Last 45 Days</option>
                        <option value="60">Last 60 Days</option>
                        <option value="90">Last 90 Days</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-2 xl:col-span-2">
                    <label class="form-label">From</label>
                    <input readonly required type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-2 xl:col-span-2">
                    <label class="form-label">To</label>
                    <input readonly required type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-4 xl:col-span-4 text-right mt-7">
                    <button type="button" id="searchSickLeave" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" ><i data-lucide="search" class="w-4 h-4 mr-2"></i>Search</button>
                    <button type="button" style="display: none;" id="exportSickLeave" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export XL
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
                    
                </div>
            </div>
        </form>
        
        <div class="empSickLeaveWrap overflow-x-auto scrollbar-hidden mt-5" style="display: none;">
            <div id="empSickLeaveListTable" class="table-report table-report--tabulator"></div>
        </div>
    </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-employee-sick-leave-report.js')
    @vite('resources/js/hr-sick-leave-new.js')
@endsection
