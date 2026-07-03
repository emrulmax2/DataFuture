@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Holiday Management</h2>
            <p class="text-sm text-slate-400 mt-1">Leave requests by holiday year &middot; London Churchill College</p>
        </div>
        <a href="{{ route('hr.portal.leave.calendar') }}" class="btn btn-outline-secondary h-[42px] text-sm">
            <i data-lucide="calendar-days" class="w-4 h-4 mr-1.5"></i> Holiday Calendar
        </a>
    </div>
    <!-- END: Page Header -->
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="employeeHolidayAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
            @if($years->count() > 0)
                @foreach($years as $year)
                    <div class="accordion-item bg-slate-100">
                        <div id="employeeHolidayAccordion-{{ $loop->index }}" class="accordion-header">
                            <button  data-year="{{ $year->id }}" class="holidayCollapseBtns accordion-button {{ ($loop->index == 0 ? '' : 'collapsed') }} relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ ($loop->index == 0 ? 'true' : 'false') }}" aria-controls="employeeHolidayAccordion-collapse-{{ $loop->index }}">
                                <span class="font-normal">Holiday Year:</span> {{ date('Y', strtotime($year->start_date)).' - '.date('Y', strtotime($year->end_date)) }}
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="employeeHolidayAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ ($loop->index == 0 ? 'show' : '') }}" aria-labelledby="employeeHolidayAccordion-{{ $loop->index }}" data-tw-parent="#employeeHolidayAccordion">
                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <div class="intro-y box p-5 pb-7">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none"><i data-lucide="clock" class="w-3.5 h-3.5"></i></span>
                                        <div class="font-semibold text-base text-slate-700 dark:text-white">Pending Leaves</div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-darkmode-400"></div>
                                    <div class="grid grid-cols-12 gap-4"> 
                                        <div class="col-span-12">
                                            <div class="overflow-x-auto scrollbar-hidden">
                                                <div id="leaveListTable-pending-{{ $year->id }}" data-year="{{ $year->id }}" data-type="pending" class="manageHolidayListTables mt-2 table-report table-report--tabulator"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="intro-y box p-5 pb-7 mt-5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i></span>
                                        <div class="font-semibold text-base text-slate-700 dark:text-white">Approved Leaves</div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-darkmode-400"></div>
                                    <div class="grid grid-cols-12 gap-4"> 
                                        <div class="col-span-12">
                                            <div class="overflow-x-auto scrollbar-hidden">
                                                <div id="leaveListTable-approved-{{ $year->id }}" data-year="{{ $year->id }}" data-type="approved" class="manageHolidayListTables mt-2 table-report table-report--tabulator"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="intro-y box p-5 pb-7 mt-5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-soft text-soft-text flex-none"><i data-lucide="x-circle" class="w-3.5 h-3.5"></i></span>
                                        <div class="font-semibold text-base text-slate-700 dark:text-white">Rejected Leaves</div>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-darkmode-400"></div>
                                    <div class="grid grid-cols-12 gap-4"> 
                                        <div class="col-span-12">
                                            <div class="overflow-x-auto scrollbar-hidden">
                                                <div id="leaveListTable-rejected-{{ $year->id }}" data-year="{{ $year->id }}" data-type="rejected" class="manageHolidayListTables mt-2 table-report table-report--tabulator"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

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
                    <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->

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
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-holiday-manager.js')
@endsection