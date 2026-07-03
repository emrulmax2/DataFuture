@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Absent Employees</h2>
            <p class="text-sm text-slate-400 mt-1">{{ date('jS M, Y', $date) }} &middot; London Churchill College</p>
        </div>
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 dark:bg-darkmode-800 dark:border-darkmode-400 rounded-lg px-3 h-[42px] focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
            <i data-lucide="calendar-days" class="w-4 h-4 text-slate-400 flex-none"></i>
            <input type="text" readonly name="class_date" class="bg-transparent border-0 outline-none text-sm font-semibold text-slate-700 dark:text-slate-300 absentAttendanceDate" id="absentAttendanceDate" value="{{ date('d-m-Y', $date) }}" style="max-width: 110px;"/>
        </div>
    </div>
    <!-- END: Page Header -->

    <div class="intro-y mt-5 overflow-auto sm:mt-0 lg:overflow-visible">
        <table class="w-full text-left border-separate border-spacing-y-[10px] sm:mt-2">
            <thead>
                <tr>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-xs font-bold tracking-wider text-slate-400">Image</th>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-xs font-bold tracking-wider text-slate-400">Name</th>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center text-xs font-bold tracking-wider text-slate-400">Date</th>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center text-xs font-bold tracking-wider text-slate-400">Contract</th>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center text-xs font-bold tracking-wider text-slate-400">Authorised Hour</th>
                    <th class="px-5 py-3 dark:border-darkmode-300 whitespace-nowrap text-right border-b-0 uppercase text-xs font-bold tracking-wider text-slate-400">Reason</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($absents))
                    @foreach($absents  as $employee_id => $absent)
                    <tr class="absentTodayTr cursor-pointer" 
                        data-emloyee="{{ $employee_id }}" 
                        data-date="{{ $absent['date'] }}" 
                        data-minute="{{ $absent['minute'] }}"  
                        data-hour-min="{{ $absent['hourMinute'] }}" 
                        data-leavetype="{{ $absent['leave_type'] }}" 
                        data-leavedayid="{{ $absent['leave_day_id'] }}" 
                        data-leavedayminute="{{ $absent['leave_day_minute'] }}" 
                        data-leavedayhourminute="{{ $absent['leave_day_hour_minute'] }}" 
                        data-leavenote="{{ $absent['leave_note'] }}" 
                        data-pendingleave="{{ ($absent['has_peinding_leave'] ? 1 : 0) }}" 
                        data-pendingleavemsg="{{ $absent['has_peinding_msg'] }}" 
                    >
                        <td class="px-5 py-3 dark:border-darkmode-300 w-40 border-b-0 bg-white shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <div class="flex">
                                <div class="image-fit zoom-in h-10 w-10">
                                    <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}" class="cursor-pointer rounded-full shadow">
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <a class="whitespace-nowrap font-medium" href="">
                                {{ $absent['full_name'] }}
                            </a>
                            <div class="mt-0.5 whitespace-nowrap text-xs text-slate-500">
                                {{ $absent['designation'] }}
                            </div>
                        </td>
                        <td class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white text-center shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <span class="font-medium">{{ $absent['the_date'] }}</span>
                        </td>
                        <td class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white text-center shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <span class="font-medium">{{ $absent['start'].' - '.$absent['end'] }}</span><br/>
                            <span class="font-medium">{{ '('.$absent['hourMinute'].')' }}</span>
                        </td>
                        <td class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white text-center shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <span class="font-medium">{{ (!empty($absent['leave_day_id']) && $absent['leave_day_id'] > 0 ? $absent['leave_day_hour_minute'] : '') }}</span>
                        </td>
                        <td class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white text-right shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            {{ $absent['reason_type'] }}<br/>
                            {{ $absent['reason'] }}
                        </td>
                    </tr>
                    @endforeach
                @else:
                    <tr>
                        <td colspan="6" class="px-5 py-3 dark:border-darkmode-300 border-b-0 bg-white text-left shadow-[20px_3px_20px_#0000000b] first:rounded-l-md last:rounded-r-md dark:bg-darkmode-600">
                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> <span>There are no absent for <strong>{{ date('jS M, Y', $date) }}</span></strong>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    <!-- BEGIN: Add Modal -->
    <div id="absentUpdateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="absentUpdateForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Absent Update Modal</h2>
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
                        <button type="submit" id="updateAbsent" class="btn btn-primary w-auto">
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

                        <input type="hidden" name="date" value="{{ date('Y-m-d', $date) }}"/>
                        <input type="hidden" name="employee_id" value="0"/>
                        <input type="hidden" name="minutes" value="0"/>
                        <input type="hidden" name="leave_day_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

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
    @vite('resources/js/hr-absent-today.js')
@endsection