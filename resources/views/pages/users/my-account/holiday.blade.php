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
 
    <div class="intro-y mt-5">
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 sm:col-span-8">
                <div class="intro-y box p-5 pb-7">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">My Holidays</div>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="grid grid-cols-12 gap-4"> 
                        <div class="col-span-12">
                            @if(!empty($holidayDetails))
                            <div id="employeeHolidayAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                @foreach($holidayDetails  as $year => $yearDetails)
                                    <div class="accordion-item bg-slate-100">
                                        <div id="employeeHolidayAccordion-{{ $loop->index }}" class="accordion-header">
                                            <button class="accordion-button {{ $yearDetails['is_active'] == 1 ? '' : 'collapsed' }} relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ $yearDetails['is_active'] == 1 ? 'true' : 'false' }}" aria-controls="employeeHolidayAccordion-collapse-{{ $loop->index }}">
                                                <span class="font-normal">Holiday Year:</span> {{ date('Y', strtotime($yearDetails['start'])) }} - {{ date('Y', strtotime($yearDetails['end'])) }}
                                                <span class="accordionCollaps"></span>
                                            </button>
                                        </div>
                                        <div id="employeeHolidayAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $yearDetails['is_active'] == 1 ? 'show' : '' }}" aria-labelledby="employeeHolidayAccordion-{{ $loop->index }}" data-tw-parent="#employeeHolidayAccordion">
                                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                <div id="employeePatternAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                                    @foreach($yearDetails['patterns'] as $pattern)
                                                        <div class="accordion-item bg-white">
                                                            <div id="employeePatternAccordion-{{ $loop->index }}" class="accordion-header">
                                                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} relative w-full text-lg font-semibold flex" type="button" data-tw-toggle="collapse" data-tw-target="#employeePatternAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="employeePatternAccordion-collapse-{{ $loop->index }}">
                                                                    <span class="font-normal">Pattern ID:</span> {{ $pattern->id }}
                                                                    
                                                                    @if(isset($pattern->patterns) && $pattern->patterns->count() > 0)
                                                                        <span class="patternHours text-sm ml-auto" style="padding: 7px 49px 0 0;">
                                                                            @foreach($pattern->patterns as $pt)
                                                                                <span>[{{ $pt->day_name }} - {{ $pt->total }}]</span>
                                                                            @endforeach
                                                                        </span>
                                                                    @endif

                                                                    <span class="accordionCollaps"></span>
                                                                </button>
                                                            </div>
                                                            <div id="employeePatternAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="employeePatternAccordion-{{ $loop->index }}" data-tw-parent="#employeePatternAccordion">
                                                                <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                                    <div class="grid grid-cols-12 gap-4">
                                                                        <div class="col-span-6 sm:col-span-3">
                                                                            <div class="text-slate-500 font-medium">Start</div>
                                                                            <div class="font-medium">{{ date('jS M, Y', strtotime($pattern->pattern_start)) }}</div>
                                                                        </div>
                                                                        <div class="col-span-6 sm:col-span-3">
                                                                            <div class="text-slate-500 font-medium">End</div>
                                                                            <div class="font-medium">{{ date('jS M, Y', strtotime($pattern->pattern_end)) }}</div>
                                                                        </div>
                                                                        <div class="col-span-6 sm:col-span-3">
                                                                            <div class="text-slate-500 font-medium">Entitlement</div>
                                                                            <div class="font-medium flex justify-start items-center">
                                                                                <span style="line-height: 24px;">
                                                                                    {{ (isset($pattern->holidayEntitlement) && !empty($pattern->holidayEntitlement) ? $pattern->holidayEntitlement : '00:00') }}
                                                                                </span>
                                                                                <span class="line-height: 24px;">{{ $pattern->adjustmentHtml }} = {{ $pattern->totalHolidayEntitlement }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-span-6 sm:col-span-3">
                                                                            <div class="text-slate-500 font-medium">Bank Holiday Auto Book</div>
                                                                            <div class="font-medium">{{ (isset($pattern->autoBookedBankHoliday) && !empty($pattern->autoBookedBankHoliday) ? $pattern->autoBookedBankHoliday : '00:00') }}</div>
                                                                        </div>
                                                                        
                                                                        <div class="col-span-12">
                                                                            <table class="table table-bordered table-hover bankHolidayTable">
                                                                                <thead class="cursor-pointer">
                                                                                    <tr>
                                                                                        <th class="whitespace-nowrap w-1/5">Status</th>
                                                                                        <th class="whitespace-nowrap w-1/5">Start Date</th>
                                                                                        <th class="whitespace-nowrap w-1/5">End Date</th>
                                                                                        <th class="whitespace-nowrap">Title</th>
                                                                                        <th class="whitespace-nowrap w-24">Hour</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @if(isset($pattern->bankHolidays) && !empty($pattern->bankHolidays))
                                                                                        @foreach($pattern->bankHolidays as $bhd)
                                                                                            <tr class="bankHolidayRow">
                                                                                                <td class="w-1/5">Bank Holiday Auto Booked</td>
                                                                                                <td class="w-1/5">{{ isset($bhd['start_date']) && !empty($bhd['start_date']) ? date('D jS F, Y', strtotime($bhd['start_date'])) : '' }}</td>
                                                                                                <td class="w-1/5">{{ isset($bhd['end_date']) && !empty($bhd['end_date']) ? date('D jS F, Y', strtotime($bhd['end_date'])) : '' }}</td>
                                                                                                <td>{{ isset($bhd['name']) && !empty($bhd['name']) ? $bhd['name'] : '' }}</td>
                                                                                                <td class="w-24">{{ isset($bhd['hour']) && !empty($bhd['hour']) ? $bhd['hour'] : '00:00' }}</td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    @endif
                                                                                </tbody>
                                                                            </table>
                                                                        </div>

                                                                        
                                                                        @if(isset($pattern->approvedLeaves) && !empty($pattern->approvedLeaves))
                                                                        <div class="col-span-12">
                                                                            <table class="table table-bordered approvedLeaveTable">
                                                                                <tbody>
                                                                                    @foreach($pattern->approvedLeaves as $leaveDay)
                                                                                        <tr  class="approvedDayRow" data-leavedayid="{{ $leaveDay->id }}">
                                                                                            <td class="w-1/5">
                                                                                                Approved 
                                                                                                @if(isset($leaveDay->leave->leave_type) && $leaveDay->leave->leave_type > 0)
                                                                                                    @switch($leaveDay->leave->leave_type)
                                                                                                        @case(1)
                                                                                                            Holiday / Vacation
                                                                                                            @break
                                                                                                        @case(2)
                                                                                                            Unauthorised Absent
                                                                                                            @break
                                                                                                        @case(3)
                                                                                                            Sick Leave
                                                                                                            @break
                                                                                                        @case(4)
                                                                                                            Authorised Unpaid
                                                                                                            @break
                                                                                                        @case(5)
                                                                                                            Authorised Paid
                                                                                                            @break
                                                                                                    @endswitch
                                                                                                @endif
                                                                                            </td>
                                                                                            <td class="w-1/5">{{ isset($leaveDay->leave_date) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                            <td class="w-1/5">{{ isset($leaveDay['leave_date']) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                            <td>{{ isset($leaveDay->leave->note) && !empty($leaveDay->leave->note) ? $leaveDay->leave->note : '' }}</td>
                                                                                            <td class="w-24">
                                                                                                @php 
                                                                                                    $hours = (intval(trim($leaveDay->hour)) / 60 >= 1) ? intval(intval(trim($leaveDay->hour)) / 60) : '00';
                                                                                                    $mins = (intval(trim($leaveDay->hour)) % 60 != 0) ? intval(trim($leaveDay->hour)) % 60 : '00';
                                                                                                
                                                                                                    $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                                                                    $hourMins .= ':';
                                                                                                    $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                                                                    
                                                                                                    echo $hourMins;
                                                                                                @endphp
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        @endif

                                                                        @if(isset($pattern->takenLeaves) && !empty($pattern->takenLeaves))
                                                                        <div class="col-span-12">
                                                                            <table class="table table-bordered takenLeaveTable">
                                                                                @foreach($pattern->takenLeaves as $leaveDay)
                                                                                    <tr  class="takenDayRow" data-leavedayid="{{ $leaveDay->id }}">
                                                                                        <td class="w-1/5">
                                                                                            Taken 
                                                                                            @if(isset($leaveDay->leave->leave_type) && $leaveDay->leave->leave_type > 0)
                                                                                                @switch($leaveDay->leave->leave_type)
                                                                                                    @case(1)
                                                                                                        Holiday / Vacation
                                                                                                        @break
                                                                                                    @case(2)
                                                                                                        Unauthorised Absent
                                                                                                        @break
                                                                                                    @case(3)
                                                                                                        Sick Leave
                                                                                                        @break
                                                                                                    @case(4)
                                                                                                        Authorised Unpaid
                                                                                                        @break
                                                                                                    @case(5)
                                                                                                        Authorised Paid
                                                                                                        @break
                                                                                                @endswitch
                                                                                            @endif
                                                                                        </td>
                                                                                        <td class="w-1/5">{{ isset($leaveDay->leave_date) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                        <td class="w-1/5">{{ isset($leaveDay['leave_date']) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                        <td>{{ isset($leaveDay->leave->note) && !empty($leaveDay->leave->note) ? $leaveDay->leave->note : '' }}</td>
                                                                                        <td class="w-24">
                                                                                            @php 
                                                                                                $hours = (intval(trim($leaveDay->hour)) / 60 >= 1) ? intval(intval(trim($leaveDay->hour)) / 60) : '00';
                                                                                                $mins = (intval(trim($leaveDay->hour)) % 60 != 0) ? intval(trim($leaveDay->hour)) % 60 : '00';
                                                                                            
                                                                                                $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                                                                $hourMins .= ':';
                                                                                                $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                                                                
                                                                                                echo $hourMins;
                                                                                            @endphp
                                                                                        </td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </table>
                                                                        </div>
                                                                        @endif
                                                                        
                                                                        @if(isset($pattern->requestedLeaves) && !empty($pattern->requestedLeaves))
                                                                        <div class="col-span-12">
                                                                            <table class="table table-bordered">
                                                                                <tbody>
                                                                                    @foreach($pattern->requestedLeaves as $leave)
                                                                                        @php
                                                                                            $leaveHours = 0;
                                                                                            $leaveDays = 0;
                                                                                        @endphp
                                                                                        @if(isset($leave->leaveDays))
                                                                                            @foreach($leave->leaveDays as $day)
                                                                                                @php 
                                                                                                    $leaveHours += $day->hour;
                                                                                                    $leaveDays += 1;
                                                                                                @endphp
                                                                                            @endforeach
                                                                                        @endif
                                                                                        <tr class="newRequestRow">
                                                                                            <td class="w-1/5">Awaiting Approval ({{ ($leaveDays > 1 ? $leaveDays.' days' : $leaveDays.' day') }})</td>
                                                                                            <td class="w-1/5">
                                                                                                {{ date('D jS F, Y', strtotime($leave->from_date))}}
                                                                                            </td>
                                                                                            <td class="w-1/5">
                                                                                                {{ date('D jS F, Y', strtotime($leave->to_date))}}
                                                                                            </td>
                                                                                            <td>Holiday / Vacation</td>
                                                                                            <td class="w-24">
                                                                                                @php 
                                                                                                    $hours = (intval(trim($leaveHours)) / 60 >= 1) ? intval(intval(trim($leaveHours)) / 60) : '00';
                                                                                                    $mins = (intval(trim($leaveHours)) % 60 != 0) ? intval(trim($leaveHours)) % 60 : '00';
                                                                                                
                                                                                                    $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                                                                    $hourMins .= ':';
                                                                                                    $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                                                                    
                                                                                                    echo $hourMins;
                                                                                                @endphp
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        @endif

                                                                        @if(isset($pattern->rejectedLeaves) && !empty($pattern->rejectedLeaves))
                                                                            <div class="col-span-12">
                                                                                <table class="table table-bordered rejectedLeaveTable">
                                                                                    <tbody>
                                                                                        @foreach($pattern->rejectedLeaves as $leaveDay)
                                                                                            <tr class="rejectedDayRow" data-leavedayid="{{ $leaveDay->id }}">
                                                                                                <td class="w-1/5">
                                                                                                    Rejected  
                                                                                                    @if(isset($leaveDay->leave->leave_type) && $leaveDay->leave->leave_type > 0)
                                                                                                        @switch($leaveDay->leave->leave_type)
                                                                                                            @case(1)
                                                                                                                Holiday / Vacation
                                                                                                                @break
                                                                                                            @case(2)
                                                                                                                Unauthorised Absent
                                                                                                                @break
                                                                                                            @case(3)
                                                                                                                Sick Leave
                                                                                                                @break
                                                                                                            @case(4)
                                                                                                                Authorised Unpaid
                                                                                                                @break
                                                                                                            @case(5)
                                                                                                                Authorised Paid
                                                                                                                @break
                                                                                                        @endswitch
                                                                                                    @endif
                                                                                                </td>
                                                                                                <td class="w-1/5">{{ isset($leaveDay->leave_date) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                                <td class="w-1/5">{{ isset($leaveDay['leave_date']) && !empty($leaveDay->leave_date) ? date('D jS F, Y', strtotime($leaveDay->leave_date)) : '' }}</td>
                                                                                                <td>{{ isset($leaveDay->leave->note) && !empty($leaveDay->leave->note) ? $leaveDay->leave->note : '' }}</td>
                                                                                                <td class="w-24">
                                                                                                    @php 
                                                                                                        $hours = (intval(trim($leaveDay->hour)) / 60 >= 1) ? intval(intval(trim($leaveDay->hour)) / 60) : '00';
                                                                                                        $mins = (intval(trim($leaveDay->hour)) % 60 != 0) ? intval(trim($leaveDay->hour)) % 60 : '00';
                                                                                                    
                                                                                                        $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                                                                        $hourMins .= ':';
                                                                                                        $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                                                                        
                                                                                                        echo $hourMins;
                                                                                                    @endphp
                                                                                                </td>
                                                                                            </tr>
                                                                                        @endforeach
                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                        @endif


                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach 
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="intro-y box p-5 pb-7">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">My Leave Allowance</div>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="relative holidayStatistics"> 
                        {!! $holidayStatistics !!}
                    </div>
                </div>
                <div class="intro-y box p-5 pb-7 mt-5">
                    <div class="grid grid-cols-12 gap-0 items-center">
                        <div class="col-span-6">
                            <div class="font-medium text-base">Submit Leave Request</div>
                        </div>
                    </div>
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="relative"> 
                        <form method="post" action="#" id="employeeLeaveForm">
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                            <div class="grid grid-cols-12 gap-0">
                                <div class="col-span-12 sm:col-span-4">
                                    <label class="block font-medium pt-2">Holiday Years</label>
                                </div>
                                <div class="col-span-12 sm:col-span-8">
                                    <select class="form-control w-full" name="leave_holiday_years">
                                        <option value="">Please Select</option>
                                        @if($holidayYears->count() > 0)
                                            @foreach($holidayYears as $hy)
                                                @php
                                                    $today = date('Y-m-d');
                                                    $startDate = (isset($hy->start_date) && !empty($hy->start_date) ? date('Y-m-d', strtotime($hy->start_date)) : '');
                                                    $endDate = (isset($hy->end_date) && !empty($hy->end_date) ? date('Y-m-d', strtotime($hy->end_date)) : '');
                                                    $selected = ($today >= $startDate && $today <= $endDate ? 'selected' : '');
                                                @endphp 
                                                {{--@if($today >= $startDate && $today <= $endDate)--}}
                                                    <option {{ $selected }} data-notice="{{ $hy->notice_period }}" value="{{ $hy->id }}">
                                                        {{ date('Y', strtotime($hy->start_date)) }} - {{ date('Y', strtotime($hy->end_date)) }}
                                                    </option>
                                                {{--@endif--}}
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mt-5">
                                <div class="col-span-12 sm:col-span-4">
                                    <label class="font-medium block pt-2">Work Pattern</label>
                                </div>
                                <div class="col-span-12 sm:col-span-8">
                                    <select class="form-control w-full" name="leave_pattern">
                                        <option value="">Please Select</option>
                                        @if($empPatterns->count() > 0)
                                            @foreach($empPatterns as $pt)
                                                <option {{ $activePattern == $pt->id ? 'Selected' : '' }} value="{{ $pt->id }}">{{ $pt->id }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mt-5">
                                <div class="col-span-12 sm:col-span-4">
                                    <label class="font-medium block pt-2">Type</label>
                                </div>
                                <div class="col-span-12 sm:col-span-8">
                                    <select class="form-control w-full" name="leave_type">
                                        {!! $leaveOptionTypes !!}
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-12 gap-0 mt-5">
                                <div class="col-span-12 sm:col-span-4"></div>
                                <div class="col-span-12 sm:col-span-8">
                                    <div class="leaveCalendar" 
                                        id="leaveCalendar" 
                                        data-start="{{ $calendarOptions['startDate'] }}" 
                                        data-end="{{ $calendarOptions['endDate'] }}" 
                                        data-disable-dates="{{ $calendarOptions['disableDates'] }}" 
                                        data-disable-days="{{ $calendarOptions['disableDays'] }}" 
                                        ></div>
                                </div>
                            </div>
                            
                            <div class="leaveFormStep2" style="display: none;">
                                
                            </div>
                            <div class="grid grid-cols-12 gap-0 mt-5">
                                <div class="col-span-12 sm:col-span-4"></div>
                                <div class="col-span-12 sm:col-span-8 text-right">
                                    <button type="submit" id="confirmRequest" disabled class="btn btn-primary w-auto save">  
                                        <i data-lucide="calendar-check" class="w-4 h-4 mr-2"></i>   
                                        Confirm Request 
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                            stroke="white" class="w-4 h-4 ml-2 loaderSvg">
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
            </div>
        </div>
    </div>


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