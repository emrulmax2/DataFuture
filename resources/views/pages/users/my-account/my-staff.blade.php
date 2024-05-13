@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My Holidays</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
    <!-- END: Profile Info -->
 
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
            <div class="intro-x flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Pending Holiday Request</h2>
                {{--<a href="{{ route('hr.portal.holiday') }}" class="ml-auto text-primary truncate">Manage Holidays</a>
                <a href="{{ route('hr.portal.leave.calendar') }}" class="ml-auto text-primary truncate">Leave Calendar</a>
                <a href="{{ route('hr.portal.leave.calendar') }}" class="ml-auto text-primary truncate">Staff Holidays</a>--}}

                <div class="dropdown ml-auto">
                    <a class="dropdown-toggle w-5 h-5 block -mr-2" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="more-vertical" class="w-5 h-5 text-slate-500"></i>
                    </a>
                    <div class="dropdown-menu w-48">
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
                        @endphp
                        <div class="intro-x">
                            <div class="flex items-center px-5 py-3 mb-3 box zoom-in actPendingHoliday" data-leave="{{ $leave->id }}">
                            {{--<div class="flex items-center px-5 py-3 mb-3 box zoom-in">--}}
                                <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                    <img src="{{ $leave->employee->photo_url }}" alt="{{ $leave->employee->first_name.' '.$leave->employee->last_name }}">
                                </div>
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium uppercase">{{ $leave->employee->first_name.' '.$leave->employee->last_name }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ date('jS M, Y', strtotime($leave->from_date)).' - '.date('jS M, Y', strtotime($leave->to_date))}}
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
                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are not panding leave available.
                    </div>
                @endif
            </div>
        </div>
        <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
            <div class="intro-x flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Absent Today</h2>
                {{--<a href="{{ route('hr.attendance') }}" class="ml-auto text-primary truncate">Manage Attendance</a>--}}
            </div>
            <div class="mt-5">
                @if(!empty($absentToday))
                    @foreach($absentToday as $employee_id => $absent)
                        <div class="intro-x">
                            <div class="flex items-center px-5 py-3 mb-3 box zoom-in">
                                <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                    <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}">
                                </div>
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium uppercase">{{ $absent['full_name'] }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500">
                                        {{ $absent['date'] }}
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
                {{-- <a href="{{ route('hr.portal.absent.employee', strtotime(date('Y-m-d'))) }}" class="intro-x block w-full rounded-md border border-dotted border-slate-400 py-3 text-center text-slate-500 dark:border-darkmode-300">
                    View More
                </a> --}}
            </div>
        </div>
        <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
            <div class="intro-x flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Holiday Today</h2>
                
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
        <div class="col-span-12 sm:col-span-6 2xl:col-span-3">
            <div class="intro-x flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Upcoming Appraisal in 60 Days</h2>
                {{-- <a href="{{ route('hr.portal.upcoming.appraisal') }}" class="ml-auto text-primary truncate">Show More</a> --}}
            </div>
            <div class="mt-5">
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
                            <a href="javascript:void(0);" class="box px-5 py-3 ml-4 flex-1 zoom-in">
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