@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
    @include('pages.users.my-account.show-info')

    @php
        $staffCardPageSize = $staffCardPageSize ?? 5;
        $pendingTotal = $pendingTotal ?? $pendingLeaves->count();
        $absentTotal = $absentTotal ?? (!empty($absentToday) ? count($absentToday) : 0);
        $holidayTotal = $holidayTotal ?? $holidays->count();
        $appraisalTotal = $appraisalTotal ?? $appraisal->count();
    @endphp

    <section class="myhr-staff" data-screen-label="My Staff">
        <article class="myhr-staff-card myhr-staff-card--pending">
            <header class="myhr-staff-card__header">
                <span class="myhr-staff-card__icon">
                    <i data-lucide="clock"></i>
                </span>
                <h2>Pending Holiday Request</h2>
                <span class="myhr-staff-count">{{ $pendingTotal }}</span>

                <div class="dropdown myhr-staff-menu">
                    <button class="dropdown-toggle myhr-staff-menu__toggle" type="button" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="more-vertical"></i>
                    </button>
                    <div class="dropdown-menu w-56">
                        <ul class="dropdown-content">
                            <li>
                                <a href="{{ route('hr.portal.leave.calendar') }}" class="dropdown-item">
                                    <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i> Leave Calendar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('user.account.staff.team.holiday') }}" class="dropdown-item">
                                    <i data-lucide="calendar-x" class="w-4 h-4 mr-2"></i> My Staff Holidays
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="myhr-staff-card__body myhr-staff-scroll" id="myStaffPendingList"
                 data-url="{{ route('user.account.staff.pending.leave.rows') }}"
                 data-page="1"
                 data-has-more="{{ $pendingTotal > $staffCardPageSize ? '1' : '0' }}">
                @include('pages.users.my-account.partials.staff-pending-rows', ['pendingLeaves' => $pendingLeaves->take($staffCardPageSize)])
                <div class="myhr-staff-loader" id="myStaffPendingLoader" hidden>
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

        <article class="myhr-staff-card myhr-staff-card--absent">
            <header class="myhr-staff-card__header">
                <span class="myhr-staff-card__icon">
                    <i data-lucide="user-x"></i>
                </span>
                <h2>Absent Today</h2>
                <span class="myhr-staff-count">{{ $absentTotal }}</span>
            </header>

            <div class="myhr-staff-card__body myhr-staff-scroll" id="myStaffAbsentList"
                 data-url="{{ route('user.account.staff.absent.rows') }}"
                 data-page="1"
                 data-has-more="{{ $absentTotal > $staffCardPageSize ? '1' : '0' }}">
                @include('pages.users.my-account.partials.staff-absent-rows', ['absentToday' => array_slice($absentToday, 0, $staffCardPageSize, true)])
                <div class="myhr-staff-loader" id="myStaffAbsentLoader" hidden>
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
        </article>

        <article class="myhr-staff-card myhr-staff-card--holiday">
            <header class="myhr-staff-card__header">
                <span class="myhr-staff-card__icon">
                    <i data-lucide="sun"></i>
                </span>
                <h2>Holiday Today</h2>
                <span class="myhr-staff-count">{{ $holidayTotal }}</span>
            </header>

            <div class="myhr-staff-card__body myhr-staff-scroll" id="myStaffHolidayList"
                 data-url="{{ route('user.account.staff.holiday.rows') }}"
                 data-page="1"
                 data-has-more="{{ $holidayTotal > $staffCardPageSize ? '1' : '0' }}">
                @include('pages.users.my-account.partials.staff-holiday-rows', ['holidays' => $holidays->take($staffCardPageSize)])
                <div class="myhr-staff-loader" id="myStaffHolidayLoader" hidden>
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

        <article class="myhr-staff-card myhr-staff-card--appraisal">
            <header class="myhr-staff-card__header">
                <span class="myhr-staff-card__icon">
                    <i data-lucide="star"></i>
                </span>
                <h2>Upcoming Appraisal <span>60 Days</span></h2>
                <span class="myhr-staff-count">{{ $appraisalTotal }}</span>
            </header>

            <div class="myhr-staff-card__body myhr-staff-scroll" id="myStaffAppraisalList"
                 data-url="{{ route('user.account.staff.appraisal.rows') }}"
                 data-page="1"
                 data-has-more="{{ $appraisalTotal > $staffCardPageSize ? '1' : '0' }}">
                @include('pages.users.my-account.partials.staff-appraisal-rows', ['appraisal' => $appraisal->take($staffCardPageSize)])
                <div class="myhr-staff-loader" id="myStaffAppraisalLoader" hidden>
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
        </article>
    </section>

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

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/user-holiday.js')
@endsection
