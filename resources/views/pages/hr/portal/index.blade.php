@extends('../layout/' . $layout)

@section('body_class', 'hr-dashboard-v2-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@php
    use App\Models\Option;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Storage;

    $siteLogo = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $logoUrl = ($siteLogo && Storage::disk('local')->exists('public/'.$siteLogo))
        ? Storage::disk('local')->url('public/'.$siteLogo)
        : asset('build/assets/images/placeholders/200x200.jpg');

    $employeeProfile = optional(auth()->user())->employee;
    $currentUserName = trim(
        (optional(optional($employeeProfile)->title)->name ? optional(optional($employeeProfile)->title)->name.' ' : '').
        (optional($employeeProfile)->first_name ?? '').' '.
        (optional($employeeProfile)->last_name ?? '')
    );
    $currentUserName = $currentUserName !== '' ? $currentUserName : (optional(auth()->user())->name ?? 'Super Admin');
    $currentUserEmail = optional(auth()->user())->email ?? '';

    $initials = function ($name) {
        $name = trim(preg_replace('/^(Mrs|Mr|Miss|Ms|Dr)\.?\s+/i', '', (string) $name));
        $parts = preg_split('/\s+/', $name);
        return strtoupper(substr($parts[0] ?? 'L', 0, 1).substr($parts[1] ?? 'C', 0, 1));
    };

    $avatarColor = function ($name) {
        $palette = ['#0F7B76', '#3B5BB5', '#7A3FB0', '#C4432F', '#187A45', '#B07E14', '#2A6FA8', '#B0357E', '#0E7C86', '#8A5A2B'];
        return $palette[abs(crc32((string) $name)) % count($palette)];
    };

    $formatMinutes = function ($minutes) {
        $minutes = (int) $minutes;
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return str_pad((string) $hours, 2, '0', STR_PAD_LEFT).':'.str_pad((string) $mins, 2, '0', STR_PAD_LEFT);
    };

    $routeUrl = function ($name, $fallback = 'javascript:void(0);') {
        return Route::has($name) ? route($name) : $fallback;
    };

    $employeeTotal = $employeeCount ?? 0;
    $pendingTotal = $pendingLeaves->count();
    $absentTotal = !empty($absentToday) ? count($absentToday) : 0;
    $holidayTotal = $holidays->count();
    $passportTotal = $passExpiry->count();
    $visaTotal = $visaExpiry->count();
    $appraisalTotal = $appraisal->count();
@endphp

@section('subcontent')
    <div class="hrd-page">
        @include('layout.components.hr-dashboard-topbar', ['active' => 'dashboard'])

        <main class="hrd-shell">
            <section class="hrd-hero">
                <div>
                    <span class="hrd-eyebrow">HR Portal</span>
                    <h1>Workforce Command Centre</h1>
                    <p>Here is what is happening across your workforce today &middot; London Churchill College</p>
                </div>
                <div class="hrd-hero__actions">
                    <a href="{{ route('employee.create') }}" class="hrd-btn hrd-btn--light">
                        <i data-lucide="plus-circle"></i>
                        Add Employee
                    </a>
                    <a href="{{ route('hr.portal.employment.reports.show') }}" class="hrd-btn hrd-btn--ghost">
                        <i data-lucide="bar-chart-3"></i>
                        Reports
                    </a>
                </div>
            </section>

            <section class="hrd-kpis" aria-label="HR portal summary">
                <article class="hrd-kpi hrd-kpi--teal">
                    <span class="hrd-kpi__icon"><i data-lucide="users"></i></span>
                    <span>
                        <strong>{{ $employeeTotal }}</strong>
                        <small>Total Employees</small>
                    </span>
                </article>
                <article class="hrd-kpi hrd-kpi--red">
                    <span class="hrd-kpi__icon"><i data-lucide="calendar-x"></i></span>
                    <span>
                        <strong>{{ $absentTotal }}</strong>
                        <small>Absent Today</small>
                    </span>
                </article>
                <article class="hrd-kpi hrd-kpi--gold">
                    <span class="hrd-kpi__icon"><i data-lucide="palmtree"></i></span>
                    <span>
                        <strong>{{ $pendingTotal }}</strong>
                        <small>Pending Holidays</small>
                    </span>
                </article>
                <article class="hrd-kpi hrd-kpi--blue">
                    <span class="hrd-kpi__icon"><i data-lucide="clock-3"></i></span>
                    <span>
                        <strong>{{ $appraisalTotal }}</strong>
                        <small>Appraisals Due</small>
                    </span>
                </article>
            </section>

            <div class="hrd-main-grid">
                <section class="hrd-card hrd-employee-card">
                    <div class="hrd-card__toolbar">
                        <h2>All Employees</h2>
                        <form id="tabulatorFilterForm" class="hrd-filter">
                            <label class="hrd-search" for="query">
                                <i data-lucide="search"></i>
                                <input id="query" name="query" type="text" placeholder="Search employees...">
                            </label>
                            <select id="status" name="status" class="hrd-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                                <option value="2">Temporary</option>
                                <option value="4">Submitted</option>
                                <option value="3">Archived</option>
                            </select>
                            <button id="tabulator-html-filter-reset" type="button" class="hrd-btn hrd-btn--muted">Reset</button>
                        </form>
                        <div class="hrd-table-actions">
                            <button id="tabulator-print" type="button" class="hrd-icon-btn" title="Print">
                                <i data-lucide="printer"></i>
                            </button>
                            <div class="dropdown">
                                <button class="dropdown-toggle hrd-icon-btn" aria-expanded="false" data-tw-toggle="dropdown" title="Export">
                                    <i data-lucide="download"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li><a id="tabulator-export-csv" href="javascript:;" class="dropdown-item"><i data-lucide="file-text"></i> Export CSV</a></li>
                                        <li><a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item"><i data-lucide="file-spreadsheet"></i> Export XLSX</a></li>
                                    </ul>
                                </div>
                            </div>
                            <a href="{{ route('employee.create') }}" class="hrd-btn hrd-btn--primary">
                                <i data-lucide="plus-circle"></i>
                                Add Employee
                            </a>
                            <button data-tw-toggle="modal" data-tw-target="#addTempEmployeeModal" type="button" class="hrd-btn hrd-btn--outline">
                                <i data-lucide="plus-circle"></i>
                                Temp
                            </button>
                        </div>
                    </div>
                    <div class="hrd-table-wrap">
                        <div id="employeeListTable" class="table-report table-report--tabulator hrd-tabulator"></div>
                    </div>
                </section>

                <aside class="hrd-side-stack">
                    <section class="hrd-card hrd-list-card">
                        <div class="hrd-card__head">
                            <span class="hrd-small-icon hrd-small-icon--blue"><i data-lucide="scan-face"></i></span>
                            <h3>Passport Expiry</h3>
                            <span class="hrd-count hrd-count--blue">{{ $passportTotal }}</span>
                            <a href="{{ route('hr.portal.passport.expiry') }}">More &rsaquo;</a>
                        </div>
                        @php $passportPageSize = 10; @endphp
                        <div class="hrd-list hrd-list--scroll" id="passportExpiryList"
                             data-url="{{ route('hr.portal.passport.rows') }}"
                             data-page="1"
                             data-has-more="{{ $passportTotal > $passportPageSize ? '1' : '0' }}">
                            @include('pages.hr.portal.partials.passport-rows', ['passExpiry' => $passExpiry->take($passportPageSize)])
                            <div class="hrd-list-loader" id="passportExpiryLoader" hidden>
                                <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#3b5bb5" class="w-5 h-5">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </section>

                    <section class="hrd-card hrd-list-card">
                        <div class="hrd-card__head">
                            <span class="hrd-small-icon hrd-small-icon--teal"><i data-lucide="credit-card"></i></span>
                            <h3>Visa Expiry</h3>
                            <span class="hrd-count hrd-count--teal">{{ $visaTotal }}</span>
                            <a href="{{ route('hr.portal.visa.expiry') }}">More &rsaquo;</a>
                        </div>
                        @php $visaPageSize = 10; @endphp
                        <div class="hrd-list hrd-list--scroll" id="visaExpiryList"
                             data-url="{{ route('hr.portal.visa.rows') }}"
                             data-page="1"
                             data-has-more="{{ $visaTotal > $visaPageSize ? '1' : '0' }}">
                            @include('pages.hr.portal.partials.visa-rows', ['visaExpiry' => $visaExpiry->take($visaPageSize)])
                            <div class="hrd-list-loader" id="visaExpiryLoader" hidden>
                                <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#0f7b76" class="w-5 h-5">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </section>

                    <section class="hrd-card hrd-list-card">
                        <div class="hrd-card__head">
                            <span class="hrd-small-icon hrd-small-icon--gold"><i data-lucide="clock"></i></span>
                            <h3>Appraisal &middot; 60 Days</h3>
                            <span class="hrd-count hrd-count--gold">{{ $appraisalTotal }}</span>
                            <a href="{{ route('hr.portal.upcoming.appraisal') }}">More &rsaquo;</a>
                        </div>
                        @php $appraisalPageSize = 10; @endphp
                        <div class="hrd-list hrd-list--scroll" id="appraisalList"
                             data-url="{{ route('hr.portal.appraisal.rows') }}"
                             data-page="1"
                             data-has-more="{{ $appraisalTotal > $appraisalPageSize ? '1' : '0' }}">
                            @include('pages.hr.portal.partials.appraisal-rows', ['appraisal' => $appraisal->take($appraisalPageSize)])
                            <div class="hrd-list-loader" id="appraisalLoader" hidden>
                                <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#b07e14" class="w-5 h-5">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>

            <section class="hrd-activity-grid">
                <article class="hrd-card hrd-activity-card hrd-activity-card--teal">
                    <div class="hrd-card__head">
                        <h3>Pending Holiday Request</h3>
                        <span class="hrd-count hrd-count--gold">{{ $pendingTotal }}</span>
                        <a href="{{ route('hr.portal.holiday') }}">Manage &rsaquo;</a>
                    </div>
                    @php $pendingPageSize = 10; @endphp
                    <div class="hrd-list hrd-list--padded hrd-list--scroll" id="pendingLeaveList"
                         data-url="{{ route('hr.portal.pending.leave.rows') }}"
                         data-page="1"
                         data-has-more="{{ $pendingTotal > $pendingPageSize ? '1' : '0' }}">
                        @include('pages.hr.portal.partials.pending-leave-rows', ['pendingLeaves' => $pendingLeaves->take($pendingPageSize)])
                        <div class="hrd-list-loader" id="pendingLeaveLoader" hidden>
                            <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#0f7b76" class="w-5 h-5">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                </article>

                <article class="hrd-card hrd-activity-card hrd-activity-card--red">
                    <div class="hrd-card__head">
                        <h3>Absent Today</h3>
                        <span class="hrd-count hrd-count--red">{{ $absentTotal }}</span>
                        <a href="{{ route('hr.attendance') }}">Attendance &rsaquo;</a>
                    </div>
                    @php $absentPageSize = 10; @endphp
                    <div class="hrd-list hrd-list--padded hrd-list--scroll" id="absentTodayList"
                         data-url="{{ route('hr.portal.absent.rows') }}"
                         data-page="1"
                         data-has-more="{{ $absentTotal > $absentPageSize ? '1' : '0' }}">
                        @include('pages.hr.portal.partials.absent-rows', ['absentToday' => array_slice($absentToday, 0, $absentPageSize, true)])
                        <div class="hrd-list-loader" id="absentTodayLoader" hidden>
                            <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#c4432f" class="w-5 h-5">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                    <div class="hrd-list-foot">
                        <a href="{{ route('hr.portal.absent.employee', date('d-m-Y')) }}" class="hrd-more-row">View More</a>
                    </div>
                </article>

                <article class="hrd-card hrd-activity-card hrd-activity-card--gold">
                    <div class="hrd-card__head">
                        <h3>Holiday Today</h3>
                        <span class="hrd-count hrd-count--gold">{{ $holidayTotal }}</span>
                        <a href="{{ route('hr.portal.leave.calendar') }}">Calendar &rsaquo;</a>
                    </div>
                    @php $holidayPageSize = 10; @endphp
                    <div class="hrd-list hrd-list--padded hrd-list--scroll" id="holidayTodayList"
                         data-url="{{ route('hr.portal.holiday.rows') }}"
                         data-page="1"
                         data-has-more="{{ $holidayTotal > $holidayPageSize ? '1' : '0' }}">
                        @include('pages.hr.portal.partials.holiday-rows', ['holidays' => $holidays->take($holidayPageSize)])
                        <div class="hrd-list-loader" id="holidayTodayLoader" hidden>
                            <svg width="22" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#b07e14" class="w-5 h-5">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".3" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                    </div>
                </article>
            </section>
        </main>

        <a href="{{ route('hr.portal.vacancy') }}" class="hrd-vacancies">
            <i data-lucide="briefcase-business"></i>
            Vacancies
        </a>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="absentUpdateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="absentUpdateForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Absent Update</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <select id="leave_type" name="leave_type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="2">Unauthorised Absent</option>
                                <option value="3">Sick Leave</option>
                                <option value="4">Authorised Unpaid</option>
                                <option value="5">Authorised Paid</option>
                            </select>
                            <div class="acc__input-error error-leave_type text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="hour" class="form-label">Hour <span class="text-danger">*</span></label>
                            <input type="text" readonly id="hour" data-todayhour="00:00" value="00:00" name="hour" placeholder="00:00" class="form-control timeMask w-full">
                            <div class="acc__input-error error-hour text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="note" class="form-label">Note <span class="text-danger">*</span></label>
                            <textarea id="note" name="note" rows="3" class="form-control w-full"></textarea>
                            <div class="acc__input-error error-note text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button disabled type="submit" id="updateAbsent" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
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

                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}"/>
                        <input type="hidden" name="employee_id" value="0"/>
                        <input type="hidden" name="minutes" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

    <!-- BEGIN: Edit New Request Modal -->
    <div id="empNewLeaveRequestModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="empNewLeaveRequestForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Leave Request</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateNLR" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
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
                        <input type="hidden" name="employee_leave_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit New Request Modal -->

    <!-- BEGIN: Add Temporary Employee Modal -->
    <div id="addTempEmployeeModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addTempEmployeeForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Temporary Employee</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="w-full form-control" name="email" id="email"/>
                            <div class="acc__input-error error-email text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="tempEmpBtn" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
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
            </form>
        </div>
    </div>
    <!-- END: addTempEmployeeModal Modal -->

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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-portal.js')
@endsection
