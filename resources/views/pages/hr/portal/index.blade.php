@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 2xl:col-span-9"> 
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">HR Dashboard</h2>
                        <a href="{{ route('hr.portal.employment.reports.show') }}" class="ml-auto flex items-center text-primary">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 mr-3"></i> Reports
                        </a>
                    </div>
                    
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12">
                    <div class="intro-y box p-5 mt-5">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                    <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="2">Temporary</option>
                                        <option value="4">Submitted</option>
                                        <option value="3">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div class="flex mt-5 sm:mt-0">
                                <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                </button>
                                <div class="dropdown w-1/2 sm:w-auto mr-2">
                                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
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
                                <a href="{{ route('employee.create') }}" class="btn btn-success text-white w-auto"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Employee</a>
                                <button data-tw-toggle="modal" data-tw-target="#addTempEmployeeModal" type="button" class="btn btn-facebook text-white w-auto ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Temp Employee</button>
                                
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="employeeListTable" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6 mt-5 pt-5">
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Pending Holiday Request</h2>
                        <a href="{{ route('hr.portal.holiday') }}" class="ml-auto text-primary truncate">Manage Holidays</a>
                    </div>
                    <div class="mt-5">
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
                                    if(isset($leave->employee->holidayAuth) && $leave->employee->holidayAuth->count() > 0):
                                        foreach($leave->employee->holidayAuth as $hau):
                                            if($hau->user_id == auth()->user()->id):
                                                $authUsers = true;
                                            endif;
                                        endforeach;
                                    endif;
                                @endphp
                                <div class="intro-x">
                                    <div class="flex items-center px-5 py-3 mb-3 box zoom-in {{ ($authUsers ? 'actPendingHoliday' : '') }}" data-leave="{{ $leave->id }}">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                            <img src="{{ $leave->employee->photo_url }}" alt="{{ $leave->employee->first_name.' '.$leave->employee->last_name }}">
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium uppercase">{{ $leave->employee->first_name.' '.$leave->employee->last_name }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ date('jS M, Y', strtotime($leave->from_date)).' - '.date('jS M, Y', strtotime($leave->to_date))}}
                                            </div>
                                        </div>
                                        @if(isset($leave->supervisedDays) && $leave->supervisedDays->count() > 0)
                                            <span class="w-auto px-2 text-success py-0 ml-auto"><i data-lucide="shield-check" class="w-6 h-6"></i></span>
                                        @endif
                                        <div class="text-danger">
                                            {{ $hourMins }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else 
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are not panding leave available.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Absent Today</h2>
                        <a href="{{ route('hr.attendance') }}" class="ml-auto text-primary truncate">Manage Attendance</a>
                    </div>
                    <div class="mt-5">
                        @if(!empty($absentToday))
                            @foreach($absentToday as $employee_id => $absent)
                                <div data-tw-toggle="modal" data-tw-target="#absentUpdateModal" class="intro-x absentToday" data-emloyee="{{ $employee_id }}" data-date="{{ $absent['the_date'] }}" data-minute="{{ $absent['minute'] }}"  data-hour-min="{{ $absent['hourMinute'] }}">
                                    <div class="flex items-center px-5 py-3 mb-3 box zoom-in">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                            <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}">
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium uppercase">{{ $absent['full_name'] }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ $absent['start'].' - '.$absent['end'] }}
                                            </div>
                                        </div>
                                        <div class="text-danger">
                                            {{ $absent['hourMinute'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else 
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are not absent attendance found for today.
                            </div>
                        @endif
                        <a href="{{ route('hr.portal.absent.employee', date('d-m-Y')) }}" class="intro-x block w-full rounded-md border border-dotted border-slate-400 py-3 text-center text-slate-500 dark:border-darkmode-300">
                            View More
                        </a>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6 2xl:col-span-4">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Holiday Today</h2>
                        <a href="{{ route('hr.portal.leave.calendar') }}" class="ml-auto text-primary truncate">Leave Calendar</a>
                    </div>
                    <div class="mt-5">
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
                                    <div class="flex items-center px-5 py-3 mb-3 box zoom-in">
                                        <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                            <img src="{{ $hol->leave->employee->photo_url }}" alt="{{ $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name }}">
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium uppercase">{{ $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ date('jS M, Y', strtotime($hol->leave_date)) }}
                                            </div>
                                        </div>
                                        <div class="text-danger">
                                            {{ $hourMins }}
                                        </div>
                                    </div>
                                </div>  
                            @endforeach     
                        @else 
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are no Holiday / Vacation found for today.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l 2xl:h-full -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6 relative">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Passport Expiry</h2>
                            <a href="{{ route('hr.portal.passport.expiry') }}" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            @if($passExpiry->count() > 0)
                                @foreach($passExpiry as $pass)
                                    <div class="intro-x relative flex items-center mb-3">
                                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                                <img src="{{ $pass->employee->photo_url }}" alt="{{ $pass->employee->first_name.' '.$pass->employee->last_name }}">
                                            </div>
                                        </div>
                                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                            <div class="flex items-center">
                                                <div class="font-medium uppercase">{{ $pass->employee->first_name.' '.$pass->employee->last_name }}</div>
                                                <div class="text-xs text-slate-500 ml-auto">{{ date('jS F, Y', strtotime($pass->doc_expire))}}</div>
                                            </div>
                                            <div class="text-slate-500 mt-1">
                                                @php 
                                                    $expiryDate = date('Y-m-d', strtotime($pass->doc_expire));
                                                    if(date('Y-m-d') > $expiryDate){
                                                        $date = \Carbon\Carbon::parse($expiryDate);
                                                        $now = \Carbon\Carbon::now();

                                                        echo '<span class="text-danger">'.$date->diffInDays($now).' Days</span>';
                                                    }else{
                                                        $date = \Carbon\Carbon::parse($expiryDate);
                                                        $now = \Carbon\Carbon::now();

                                                        echo '<span class="text-warning">'.$date->diffInDays($now).' Days</span>';
                                                    }
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Visa Expiry</h2>
                            <a href="{{ route('hr.portal.visa.expiry') }}" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            @if($visaExpiry->count() > 0)
                                @foreach($visaExpiry as $pass)
                                    <div class="intro-x relative flex items-center mb-3">
                                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                                <img src="{{ $pass->employee->photo_url }}" alt="{{ $pass->employee->first_name.' '.$pass->employee->last_name }}">
                                            </div>
                                        </div>
                                        <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                            <div class="flex items-center">
                                                <div class="font-medium uppercase">{{ $pass->employee->first_name.' '.$pass->employee->last_name }}</div>
                                                <div class="text-xs text-slate-500 ml-auto">{{ date('jS F, Y', strtotime($pass->workpermit_expire))}}</div>
                                            </div>
                                            <div class="text-slate-500 mt-1">
                                                @php 
                                                    $expiryDate = date('Y-m-d', strtotime($pass->workpermit_expire));
                                                    if(date('Y-m-d') > $expiryDate){
                                                        $date = \Carbon\Carbon::parse($expiryDate);
                                                        $now = \Carbon\Carbon::now();

                                                        echo '<span class="text-danger">'.$date->diffInDays($now).' Days</span>';
                                                    }else{
                                                        $date = \Carbon\Carbon::parse($expiryDate);
                                                        $now = \Carbon\Carbon::now();

                                                        echo '<span class="text-warning">'.$date->diffInDays($now).' Days</span>';
                                                    }
                                                @endphp
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Upcoming Appraisal in 60 Days</h2>
                            <a href="{{ route('hr.portal.upcoming.appraisal') }}" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            @if($appraisal->count() > 0)
                                @foreach($appraisal as $apr)
                                    @php 
                                        $today = date('Y-m-d');
                                        $dueOn = date('Y-m-d', strtotime($apr->due_on));
                                        $label = ($dueOn < $today ? 'Overdue' : 'Due');
                                    @endphp
                                    <div class="intro-x relative flex items-center mb-3">
                                        <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                            <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                                <img src="{{ $apr->employee->photo_url }}" alt="{{ $apr->employee->first_name.' '.$apr->employee->last_name }}">
                                            </div>
                                        </div>
                                        <a href="{{ route('employee.appraisal', $apr->employee_id) }}" class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                            <div class="flex items-center">
                                                <div class="font-medium uppercase">{{ $apr->employee->first_name.' '.$apr->employee->last_name }}</div>
                                                <div class="text-xs text-slate-500 ml-auto">{{ date('jS M, Y', strtotime($apr->due_on)) }}</div>
                                            </div>
                                            <div class="text-slate-500 mt-1">
                                                <span class="{{ ($dueOn < $today ? 'text-danger' : 'text-warning') }}">{{ $label }}</span>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <a href="{{ route('hr.portal.vacancy') }}" class="btn btn-twitter w-auto justify-center absolute b-0 r-0 mb-6 mr-6"><i data-lucide="list-todo" class="w-4 h-4 mr-2"></i> Vacancies</a>
            </div>
        </div> 
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
