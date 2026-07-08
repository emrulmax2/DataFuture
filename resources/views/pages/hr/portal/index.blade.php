@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12">
            <!-- BEGIN: Breadcrumb -->
            <nav class="intro-y mt-5 ml-2" aria-label="breadcrumb">
                <ol class="flex items-center flex-wrap gap-1.5 text-sm text-slate-400">
                    <li><a href="javascript:void(0);" class="hover:text-primary transition-colors">User</a></li>
                    <li class="text-slate-300 dark:text-darkmode-300">&rsaquo;</li>
                    <li><a href="{{ route('staff.dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a></li>
                    <li class="text-slate-300 dark:text-darkmode-300">&rsaquo;</li>
                    <li class="font-semibold text-primary" aria-current="page">HR Portal</li>
                </ol>
            </nav>
            <!-- END: Breadcrumb -->

            <!-- BEGIN: Dashboard Header -->
            <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-2 mb-2">
                <div>
                    <h2 class="font-display text-3xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">HR Dashboard</h2>
                    <p class="text-sm text-slate-400 mt-1">Workforce overview &middot; London Churchill College</p>
                </div>
                <a href="{{ route('hr.portal.employment.reports.show') }}" class="btn btn-outline-primary h-[42px] text-sm text-primary">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 mr-1.5"></i> Reports
                </a>
            </div>
            <!-- END: Dashboard Header -->
        </div>
        <div class="col-span-12 xl:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Employee Table Panel -->
            <div class="intro-y box mt-3">
                <!-- Toolbar -->
                <div class="flex flex-col xl:flex-row xl:items-end gap-4 px-5 py-4 border-b border-slate-100 dark:border-darkmode-400">
                    <form id="tabulatorFilterForm" class="flex flex-wrap items-center gap-2 mr-auto">
                        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 dark:bg-darkmode-800 dark:border-darkmode-400 rounded-lg px-3 h-[42px] w-72 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 flex-none"></i>
                            <input id="query" name="query" type="text" class="bg-transparent border-0 outline-none text-sm text-slate-700 dark:text-slate-300 w-full placeholder:text-slate-400" placeholder="Search name, role, department...">
                        </div>
                        <select id="status" name="status" class="form-select h-[42px] rounded-lg border-slate-200 dark:border-darkmode-400 bg-slate-50 dark:bg-darkmode-800 text-sm font-semibold w-36">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                            <option value="2">Temporary</option>
                            <option value="4">Submitted</option>
                            <option value="3">Archived</option>
                        </select>
                        <button id="tabulator-html-filter-reset" type="button" class="btn btn-outline-secondary h-[42px] px-5 text-sm">Reset</button>
                    </form>
                    <div class="flex flex-wrap gap-2">
                        <button id="tabulator-print" class="btn btn-outline-secondary h-[42px] text-sm">
                            <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i> Print
                        </button>
                        <div class="dropdown">
                            <button class="dropdown-toggle btn btn-outline-secondary h-[42px] text-sm" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1.5"></i>
                            </button>
                            <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    <li>
                                        <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <a href="{{ route('employee.create') }}" class="btn btn-primary text-white h-[42px] text-sm">
                            <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i> Add Employee
                        </a>
                        <button data-tw-toggle="modal" data-tw-target="#addTempEmployeeModal" type="button" class="btn btn-outline-accent h-[42px] text-sm">
                            <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i> Temp
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden px-5 pb-5">
                    <div id="employeeListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
            <!-- END: Employee Table Panel -->

            <!-- BEGIN: Bottom Three Sections -->
            <div class="grid grid-cols-12 gap-6 mt-6">

                <!-- Pending Holiday Request -->
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center gap-2 mb-4">
                        <h2 class="text-base font-semibold text-slate-700 dark:text-white truncate">Pending Holiday Request</h2>
                        @if($pendingLeaves->count() > 0)
                            <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ $pendingLeaves->count() }}</span>
                        @endif
                        <a href="{{ route('hr.portal.holiday') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                            Manage Holidays <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <div class="overflow-y-auto max-h-96 space-y-2 pr-0.5">
                        @if($pendingLeaves->count() > 0)
                            @foreach($pendingLeaves as $leave)
                                @php
                                    $leaveMinute = 0;
                                    $hourMins = '00:00';
                                    if( $leave->leaveDays->count() > 0 ):
                                        foreach( $leave->leaveDays as $ld):
                                            if($ld->status == 'Active'):
                                                $leaveMinute += $ld->hour;
                                            endif;
                                        endforeach;
                                    endif;
                                    $hours = (intval(trim($leaveMinute)) / 60 >= 1) ? intval(intval(trim($leaveMinute)) / 60) : '00';
                                    $mins = (intval(trim($leaveMinute)) % 60 != 0) ? intval(trim($leaveMinute)) % 60 : '00';

                                    $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                    $hourMins .= ':';
                                    $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;

                                    $authUsers = false;
                                    if(isset($leave->employee->approvers) && $leave->employee->approvers->count() > 0):
                                        foreach($leave->employee->approvers as $hau):
                                            if($hau->user_id == auth()->user()->id):
                                                $authUsers = true;
                                            endif;
                                        endforeach;
                                    endif;
                                @endphp
                                <div class="intro-x">
                                    <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in {{ ($authUsers ? 'actPendingHoliday' : '') }}" data-leave="{{ $leave->id }}">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                            <img src="{{ $leave->employee->photo_url }}" alt="{{ $leave->employee->first_name.' '.$leave->employee->last_name }}">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $leave->employee->first_name.' '.$leave->employee->last_name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ date('jS M, Y', strtotime($leave->from_date)).' - '.date('jS M, Y', strtotime($leave->to_date)) }}</div>
                                        </div>
                                        @if(isset($leave->supervisedDays) && $leave->supervisedDays->count() > 0)
                                            <span class="text-success"><i data-lucide="shield-check" class="w-5 h-5"></i></span>
                                        @endif
                                        <span class="text-sm font-bold text-danger">{{ $hourMins }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No pending leave available.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Absent Today -->
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center gap-2 mb-4">
                        <h2 class="text-base font-semibold text-slate-700 dark:text-white truncate">Absent Today</h2>
                        @if(!empty($absentToday))
                            <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ count($absentToday) }}</span>
                        @endif
                        <a href="{{ route('hr.attendance') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                            Manage Attendance <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <div class="overflow-y-auto max-h-96 space-y-2 pr-0.5">
                        @if(!empty($absentToday))
                            @foreach($absentToday as $employee_id => $absent)
                                <div data-tw-toggle="modal" data-tw-target="#absentUpdateModal" class="intro-x absentToday cursor-pointer" data-emloyee="{{ $employee_id }}" data-date="{{ $absent['the_date'] }}" data-minute="{{ $absent['minute'] }}" data-hour-min="{{ $absent['hourMinute'] }}">
                                    <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in hover:border-primary/30 transition-colors">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                            <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $absent['full_name'] }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $absent['start'].' - '.$absent['end'] }}</div>
                                        </div>
                                        <span class="text-sm font-bold text-danger">{{ $absent['hourMinute'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No absent attendance found for today.
                            </div>
                        @endif
                        <a href="{{ route('hr.portal.absent.employee', date('d-m-Y')) }}" class="intro-x flex items-center justify-center w-full rounded-xl border border-dashed border-slate-300 dark:border-darkmode-400 py-3 text-sm text-slate-400 hover:border-primary hover:text-primary transition-colors mt-1">
                            View More
                        </a>
                    </div>
                </div>

                <!-- Holiday Today -->
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center gap-2 mb-4">
                        <h2 class="text-base font-semibold text-slate-700 dark:text-white truncate">Holiday Today</h2>
                        @if($holidays->count() > 0)
                            <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ $holidays->count() }}</span>
                        @endif
                        <a href="{{ route('hr.portal.leave.calendar') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                            Leave Calendar <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                    <div class="overflow-y-auto max-h-96 space-y-2 pr-0.5">
                        @if($holidays->count() > 0)
                            @foreach($holidays as $hol)
                                @php
                                    $hours = (intval(trim($hol->hour)) / 60 >= 1) ? intval(intval(trim($hol->hour)) / 60) : '00';
                                    $mins = (intval(trim($hol->hour)) % 60 != 0) ? intval(trim($hol->hour)) % 60 : '00';

                                    $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                    $hourMins .= ':';
                                    $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                @endphp
                                <div class="intro-x">
                                    <div class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                            <img src="{{ $hol->leave->employee->photo_url }}" alt="{{ $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name }}">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">
                                                {{ date('jS M, Y', strtotime($hol->leave_date)) }}
                                            </div>
                                        </div>
                                        <span class="text-sm font-bold text-danger">{{ $hourMins }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No Holiday / Vacation found for today.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            <!-- END: Bottom Three Sections -->

        </div>
        <!-- BEGIN: Right Sidebar -->
        <div class="col-span-12 xl:col-span-4 2xl:col-span-3">
            <div class="dark:border-darkmode-400 2xl:h-full -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6 relative">

                    <!-- Passport Expiry -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center gap-2 mb-4">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none">
                                <i data-lucide="scan-face" class="w-3.5 h-3.5"></i>
                            </span>
                            <h2 class="text-base font-semibold text-slate-700 dark:text-white truncate">Passport Expiry</h2>
                            @if($passExpiry->count() > 0)
                                <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ $passExpiry->count() }}</span>
                            @endif
                            <a href="{{ route('hr.portal.passport.expiry') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                                Show More <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                        <div class="space-y-2 overflow-y-auto max-h-72 pr-0.5">
                            @if($passExpiry->count() > 0)
                                @foreach($passExpiry as $pass)
                                    @php
                                        $expiryDate = date('Y-m-d', strtotime($pass->doc_expire));
                                        $isExpired = date('Y-m-d') > $expiryDate;
                                        $diffDays = \Carbon\Carbon::parse($expiryDate)->diffInDays(\Carbon\Carbon::now());
                                    @endphp
                                    <div class="intro-x flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                            <img src="{{ $pass->employee->photo_url }}" alt="{{ $pass->employee->first_name.' '.$pass->employee->last_name }}">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $pass->employee->first_name.' '.$pass->employee->last_name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ date('jS F, Y', strtotime($pass->doc_expire)) }}</div>
                                        </div>
                                        <span class="lcc-badge {{ $isExpired ? 'lcc-badge--critical' : 'lcc-badge--warning' }}">
                                            {{ $diffDays }} Days
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No data found.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Visa Expiry -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center gap-2 mb-4">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                            </span>
                            <h2 class="text-base font-semibold text-slate-700 dark:text-white truncate">Visa Expiry</h2>
                            @if($visaExpiry->count() > 0)
                                <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ $visaExpiry->count() }}</span>
                            @endif
                            <a href="{{ route('hr.portal.visa.expiry') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                                Show More <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                        <div class="space-y-2 overflow-y-auto max-h-72 pr-0.5">
                            @if($visaExpiry->count() > 0)
                                @foreach($visaExpiry as $pass)
                                    @php
                                        $expiryDate = date('Y-m-d', strtotime($pass->workpermit_expire));
                                        $isExpired = date('Y-m-d') > $expiryDate;
                                        $diffDays = \Carbon\Carbon::parse($expiryDate)->diffInDays(\Carbon\Carbon::now());
                                    @endphp
                                    <div class="intro-x flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                            <img src="{{ $pass->employee->photo_url }}" alt="{{ $pass->employee->first_name.' '.$pass->employee->last_name }}">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $pass->employee->first_name.' '.$pass->employee->last_name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ date('jS F, Y', strtotime($pass->workpermit_expire)) }}</div>
                                        </div>
                                        <span class="lcc-badge {{ $isExpired ? 'lcc-badge--critical' : 'lcc-badge--warning' }}">
                                            {{ $diffDays }} Days
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No data found.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Upcoming Appraisal -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center gap-2 mb-4">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none">
                                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            </span>
                            <h2 class="text-base font-semibold text-slate-700 dark:text-white">Upcoming Appraisal in 60 Days</h2>
                            @if($appraisal->count() > 0)
                                <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-xs font-bold text-white bg-success rounded-full">{{ $appraisal->count() }}</span>
                            @endif
                            <a href="{{ route('hr.portal.upcoming.appraisal') }}" class="ml-auto text-xs font-semibold text-primary hover:underline flex items-center gap-1 whitespace-nowrap">
                                Show More <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                        <div class="space-y-2 overflow-y-auto max-h-72 pr-0.5">
                            @if($appraisal->count() > 0)
                                @foreach($appraisal as $apr)
                                    @php
                                        $dueDate = date('Y-m-d', strtotime($apr->due_on));
                                        $isOverdue = date('Y-m-d') > $dueDate;
                                        $diffDays = \Carbon\Carbon::parse($dueDate)->diffInDays(\Carbon\Carbon::now());
                                    @endphp
                                    <div class="intro-x">
                                        <a href="{{ route('employee.appraisal', $apr->employee_id) }}" class="flex items-center gap-3 px-4 py-3 bg-white dark:bg-darkmode-600 rounded-xl border border-slate-100 dark:border-darkmode-400 shadow-sm zoom-in hover:border-primary/30 transition-colors">
                                            <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit ring-2 ring-slate-100 dark:ring-darkmode-400">
                                                <img src="{{ $apr->employee->photo_url }}" alt="{{ $apr->employee->first_name.' '.$apr->employee->last_name }}">
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="font-semibold text-sm text-slate-700 dark:text-slate-200 uppercase truncate">{{ $apr->employee->first_name.' '.$apr->employee->last_name }}</div>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ date('jS M, Y', strtotime($apr->due_on)) }}</div>
                                                <span class="text-xs font-medium {{ $isOverdue ? 'text-danger' : 'text-warning' }}">{{ $isOverdue ? 'Overdue' : 'Due to Complete' }}</span>
                                            </div>
                                            <span class="lcc-badge {{ $isOverdue ? 'lcc-badge--critical' : 'lcc-badge--warning' }}">
                                                {{ $isOverdue ? 'by ' : 'in ' }}{{ $diffDays }} days
                                            </span>
                                        </a>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex items-center px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-sm">
                                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-2 flex-none"></i> No data found.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>

                <a href="{{ route('hr.portal.vacancy') }}" class="btn btn-primary text-white w-auto justify-center absolute b-0 r-0 mb-6 mr-6"><i data-lucide="list-todo" class="w-4 h-4 mr-2"></i> Vacancies</a>
            </div>
        </div>
        <!-- END: Right Sidebar -->
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
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateNLR" class="btn btn-primary w-auto">     
                            Save                  
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
