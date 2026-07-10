@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Leave Calendar</h2>
            <p class="text-sm text-slate-400 mt-1">Monthly leave overview &middot; London Churchill College</p>
        </div>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Portal
        </a>
    </div>
    <!-- END: Page Header -->

    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box mt-5">
        <!-- Toolbar -->
        <div class="px-5 py-4 border-b border-slate-100 dark:border-darkmode-400">
        <form method="post" action="#" id="leaveCalendarFilterForm">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6 sm:col-span-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Department</label>
                    <select name="department" id="department" class="form-control w-full">
                        <option value="">Please Select</option>
                        @if($department->count() > 0)
                            @foreach($department as $dpt)
                                <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Employee</label>
                    <select name="employee[]" multiple id="employee" class="w-full tom-selects">
                        <option value="">Please Select</option>
                        @if($employees->count() > 0)
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Month</label>
                    <select name="month" id="month" class="form-control w-full">
                        @php
                            for($i = 1; $i <= 12; $i++):
                                $y = date('Y');
                                $cm = date('m');
                                $m = date('F', strtotime($y.'-'.$i.'-1'));
                                if($cm == $i):
                                    echo '<option Selected value="'.$i.'">'.$m.'</option>';
                                else:
                                    echo '<option value="'.$i.'">'.$m.'</option>';
                                endif;
                            endfor;
                        @endphp
                    </select>
                </div>
                <div class="col-span-6 sm:col-span-2">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1.5">Year</label>
                    <select name="year" id="year" class="form-control w-full">
                        @php
                            for($i = 2015; $i <= date('Y'); $i++):
                                $y = date('Y');
                                if($y == $i):
                                    echo '<option Selected value="'.$i.'">'.$i.'</option>';
                                else:
                                    echo '<option value="'.$i.'">'.$i.'</option>';
                                endif;
                            endfor;
                        @endphp
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-4 flex justify-end items-end gap-2">
                    <button id="leave-calendar-prev" data-value="prev" data-date="{{ date('Y-m-d') }}" class="leaveCalendarActionBtn btn btn-outline-secondary h-[42px] text-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5"></i>Prev Month
                    </button>
                    <button id="leave-calendar-next" data-value="next" data-date="{{ date('Y-m-d') }}" class="leaveCalendarActionBtn btn btn-outline-secondary h-[42px] text-sm">
                        Next Month<i data-lucide="arrow-right" class="w-4 h-4 ml-1.5"></i>
                    </button>
                </div>
            </div>
        </form>
        </div>
        <div class="px-5 py-5">
        <div class="leaveCalendarWrap">
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
            <table class="table table-bordered table-sm leaveCalendarTable">
                {!! $calendarHtml !!}
            </table>
        </div>
        <div class="flex flex-wrap justify-start items-center gap-2 pt-5 labelsBtnsGroup">
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md holidayVacationBG">Holiday / Vacation</span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md meetingTrainingBG">Unauthorised Absent</span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md sickLeaveBG">Sick Leave</span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md authoriseUnpaidBG">Authorise Unpaid</span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md authorisedPaidBG">Authorise Paid</span>
            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-md bankHolidayBG">Bank Holiday</span>
        </div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Edit New Request Modal -->
    <div id="viewLeaveModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto modal-titles uppercase">Leave Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="leaveDetailsModalLoader">
                        <div class=" flex justify-center items-center px-10 py-10">
                            <i data-loading-icon="oval" class="w-10 h-10"></i>
                        </div>
                    </div>
                    <div class="leaveDetailsModalContent" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Edit New Request Modal -->
@endsection

@section('script')
    @vite('resources/js/leave-calendar.js')
@endsection