@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('body_class', 'hr-attendance-body')
@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="att-page att-page--monthly intro-y">
        <div class="att-header att-header--monthly">
            <div class="att-header__lead">
                <div class="att-header__eyebrow">Monthly attendance</div>
                <h2 class="att-header__title">Monthly Attendance</h2>
                <p class="att-header__copy">Daily sync &amp; payroll status &middot; London Churchill College</p>
            </div>
            <div class="att-header__actions">
                <a href="{{ route('hr.portal.leave.calendar') }}" class="att-btn att-btn--outline att-btn--planner">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    Planner
                </a>
                <a href="{{ route('hr.portal.live.attedance') }}" class="att-btn att-btn--solid att-btn--live">
                    <span class="att-live-dot"></span>
                    Live Attendance
                </a>
            </div>
        </div>
    </div>
    <!-- END: Page Header -->

    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box mt-5 att-monthly-shell">
        <!-- Toolbar -->
        <div class="att-page att-monthly-toolbar flex flex-col xl:flex-row xl:items-end gap-4 px-5 py-4 border-b border-slate-100 dark:border-darkmode-400">
            <form id="filterMonthAttenForm" class="att-monthly-toolbar__form flex flex-wrap xl:flex-nowrap gap-3 items-end mr-auto">
                <div class="att-monthly-toolbar__field flex items-center gap-2.5">
                    <label class="att-monthly-toolbar__label whitespace-nowrap">Query</label>
                    <input id="queryDate" readonly data-org="{{ date('m-Y') }}" data-date="{{ date('Y-m-01') }}" value="{{ date('F Y') }}" name="queryDate" type="text" class="att-input att-input--month" placeholder="Month YYYY">
                </div>
                <div class="att-monthly-toolbar__actions flex gap-2">
                    <button type="submit" id="filterMonthAtten" class="att-btn att-btn--solid syncroniseAttendance">
                        Go
                        <svg style="display: none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="currentColor" class="att-spin w-4 h-4">
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
                    <button type="button" id="generateReport" class="att-btn att-btn--outline">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Generate Report
                    </button>
                </div>
            </form>
            <div class="att-monthly-toolbar__uploads relative">
                <div class="dropdown" id="uploadsDropdown">
                    <button class="dropdown-toggle att-btn att-btn--gold" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        Upload PaySlips
                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </button>
                    <div class="dropdown-menu w-72">
                        <ul class="dropdown-content">
                            <li><h6 class="dropdown-header">Pending Payslips</h6></li>
                            <li><hr class="dropdown-divider mt-0"></li>
                            @if(isset($RemainpaySlips) && !empty($RemainpaySlips) && count($RemainpaySlips) > 0)
                                @foreach($RemainpaySlips as $month_year)
                                    <li>
                                        <div class="form-check dropdown-item">
                                            <a href="{{ route('hr.attendance.payroll.sync',$month_year) }}" class="inline-flex items-center cursor-pointer" for="employee_doc_{{ $month_year }}"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> {{ $month_year }}</a>
                                        </div>
                                    </li>
                                @endforeach
                            @else 
                                <li>
                                    <div class="alert alert-pending-soft show flex items-top mb-1 mt-1" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are no settings found!
                                    </div>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div class="flex p-1">
                                    <button id="uploadSync" data-tw-toggle="modal" data-tw-target="#synPaySlipModal" type="button" class="att-btn att-btn--solid att-btn--sm mr-auto">
                                        <i data-lucide="upload" class="w-4 h-4"></i>
                                        Upload Payslips
                                    </button>
                                    <button type="button" id="closeUploadsDropdown" class="att-btn att-btn--outline att-btn--sm ml-auto">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                        Close
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="att-monthly-shell__table overflow-x-auto scrollbar-hidden" id="attendanceSyncListTable">
            <table class="att-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Synchronise</th>
                        <th>Issues</th>
                        <th>Absents</th>
                        <th>Overtime</th>
                        <th>Pendings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $html_table !!}
                </tbody>
            </table>

            <div class="leaveTableLoader">
                <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(255, 255, 255)" class="w-10 h-10 text-danger">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </div>
        </div>
    </div>

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Add synPaySlipModal Modal -->
        <div id="synPaySlipModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ">
                
                    <div class="modal-content">
                        <div class="modal-body p-0"><a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                            <i data-tw-merge data-lucide="x" class="stroke-1.5  h-8 w-8 text-slate-400"></i>
                        </a>
                            <div class="p-5 text-center">
                                <i data-lucide="badge-pound-sterling" class="w-16 h-16 text-success mx-auto mt-3"></i>
                                <div class="text-3xl mt-5 ">Upload Payslips</div>
                                <div class="text-slate-500 mt-2 ">Please Upload payslips from below</div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <form method="post"  action="{{ route('hr.attendance.payslip.upload') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                                        @csrf    
                                        <div class="fallback">
                                            <input name="documents[]"  type="file" />
                                        </div>
                                        <div class="dz-message" data-dz-message>
                                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                                            <div class="text-slate-500">
                                                Upload zip files.
                                            </div>
                                        </div>
                                        <input type="hidden" name="dir_name" value=""/>
                                        <input type="hidden" name="type" value=""/>
                                        <input type="hidden" name="holiday_year_info" value=""/>
                                    </form>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="type" name="typePaySlip" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Type</option>
                                        <option value="Payslips">Payslips</option>
                                        <option value="P45">P45</option>
                                        <option value="P60">P60</option>
                                    </select> 
                                    <div class="acc__input-error error-type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_year" name="holiday_year_id" class="lccTom lcc-tom-select w-full text-left">
                                        <option value="">Please Select Year</option>
                                        @foreach($holiday_years as $list)
                                            <option value="{{ $list->id }}">{{ date('Y', strtotime($list->start_date)).' - '.date('Y', strtotime($list->end_date)) }}</option>
                                        @endforeach
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_month" name="holiday_month" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Month</option>
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="px-5 pb-8 text-center">
                                <button id="uploadEmpDocBtn" type="button" class="btn btn-success w-24 EmpSyncBtn">Save<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg></button>
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    <!-- END: synPaySlipModal Modal -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-date="" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-attedance.js')
    @vite('resources/js/hr-payslipsync.js')
@endsection
