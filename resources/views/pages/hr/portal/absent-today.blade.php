@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Absent Employees of <u>{{ date('jS M, Y', $date)}}</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            {{--<a href="{{ route('hr.portal.absent.employee', strtotime($yesterday)) }}" class="transition duration-200 border shadow-sm justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed !box !box flex items-center text-slate-600 dark:text-slate-300">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Prev Day
            </a>
            <a href="{{ route('hr.portal.absent.employee', strtotime($tomorrow)) }}" class="transition duration-200 border shadow-sm justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed !box !box ml-3 flex items-center text-slate-600 dark:text-slate-300">
                Next Day
                <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
            </a>--}}
            <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 mr-2">
                <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i>
                <input type="text" readonly name="class_date" class="w-full h-full form-control border-0 absentAttendanceDate" id="absentAttendanceDate" value="{{ date('d-m-Y', $date) }}" style="max-width: 110px;"/>
            </div>
        </div>
    </div>
    <div class="intro-y mt-8 overflow-auto sm:mt-0 lg:overflow-visible">
        <table class="w-full text-left border-separate border-spacing-y-[10px] sm:mt-2">
            <thead>
                <tr>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase">Image</th>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase">Name</th>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center">Date</th>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center">Contract</th>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap border-b-0 uppercase text-center">Authorised Hour</th>
                    <th class="font-medium px-5 py-3 dark:border-darkmode-300 whitespace-nowrap text-right border-b-0 uppercase">Reason</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($absents))
                    @foreach($absents  as $employee_id => $absent)
                    <tr class="absentTodayTr cursor-pointer" 
                        data-emloyee="{{ $employee_id }}" 
                        data-minute="{{ $absent['minute'] }}"  
                        data-hour-min="{{ $absent['hourMinute'] }}" 
                        data-leavetype="{{ $absent['leave_type'] }}" 
                        data-leavedayid="{{ $absent['leave_day_id'] }}" 
                        data-leavedayminute="{{ $absent['leave_day_minute'] }}" 
                        data-leavedayhourminute="{{ $absent['leave_day_hour_minute'] }}" 
                        data-leavenote="{{ $absent['leave_note'] }}" 
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
                            <label for="hour" class="form-label">Contracted Hour <span class="text-danger">*</span></label>
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