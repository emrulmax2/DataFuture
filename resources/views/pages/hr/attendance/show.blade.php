@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendances Of <u>{{ $theDate }}</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.attendance') }}" class="btn btn-primary shadow-md mr-2">Back to Attendance</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="attendanceAccordion" class="accordion">
            <div class="accordion-item">
                <div id="attendanceAccordion-absents" class="accordion-header">
                    <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#attendanceAccordion-collapse-absents" aria-expanded="false" aria-controls="attendanceAccordion-collapse-absents">
                        Absents <strong>{{ ($absents->count() > 0 ? '('.$absents->count().')' : '' )}}</strong>
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="attendanceAccordion-collapse-absents" class="accordion-collapse collapse" aria-labelledby="attendanceAccordion-absents" data-tw-parent="#attendanceAccordion">
                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                    <div data-table="#absentsAttendance" class="saveAllRow text-right mb-5" style="display: none;">
                            <button data-table="#absentsAttendance" type="button" class="saveAllAttendance btn btn-primary w-auto"> 
                                <i data-lucide="save-all" class="w-4 h-4 mr-2"></i>    
                                Save Selected                    
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                        <table class="table table-sm table-bordered dailyAttendanceTable" id="absentsAttendance">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center inputCheckbox">
                                        <div class="form-check m-0 justify-center">
                                            <input  class="form-check-input checkAll m-0" type="checkbox" name="checkAllAttendance" value="1"/>
                                        </div>
                                    </th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Reasons</th>
                                    <th class="whitespace-nowrap">Adjustment</th>
                                    <th class="whitespace-nowrap">Hour</th>
                                    <th class="whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($absents) && $absents->count() > 0)
                                    @foreach($absents as $atten)
                                        <tr class="attendanceRow attendanceAbsentRow attendanceRow_{{ $atten->id }}" id="attendanceRow_{{ $atten->id }}" data-id="{{ $atten->id }}">
                                            <td class="text-center inputCheckbox">
                                                <div class="form-check m-0 justify-center">
                                                    <input  class="form-check-input employee_attendance_id m-0" type="checkbox" name="attendance[{{ $atten->id }}][id]" value="{{ $atten->id }}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="m-0 font-medium block">
                                                    {{ (isset($atten->employee->title->name) && !empty($atten->employee->title->name) ? $atten->employee->title->name : '') }}
                                                    {{ (isset($atten->employee->first_name) && !empty($atten->employee->first_name) ? ' '.$atten->employee->first_name : '') }}
                                                    {{ (isset($atten->employee->last_name) && !empty($atten->employee->last_name) ? ' '.$atten->employee->last_name : '') }}
                                                </span>
                                                @if(isset($atten->employee->employment->employeeJobTitle->name) && !empty($atten->employee->employment->employeeJobTitle->name))
                                                    <span class="m-0 font-medium text-slate-400 block">
                                                        {{ $atten->employee->employment->employeeJobTitle->name }}
                                                    </span>
                                                @endif
                                                @if(isset($atten->pay->hourly_rate) && !empty($atten->pay->hourly_rate))
                                                    <span class="m-0 font-medium block">
                                                        £{{ $atten->pay->hourly_rate }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($atten->employee_leave_day_id > 0)
                                                    @php 
                                                        $leaveName = '';
                                                        switch($atten->leave_status):
                                                            case 1:
                                                                $leaveName = 'Holiday / Vacation';
                                                                break;
                                                            case 2:
                                                                $leaveName = 'Meeting / Training';
                                                                break;
                                                            case 3:
                                                                $leaveName = 'Sick Leave';
                                                                break;
                                                            case 4:
                                                                $leaveName = 'Authorised Unpaid';
                                                                break;
                                                            case 5:
                                                                $leaveName = 'Authorised Paid';
                                                                break;
                                                        endswitch
                                                    @endphp
                                                    @if($atten->leave_status == 1)
                                                        @php 
                                                            $leaveNote = (isset($atten->leaveDay->leave->note) && !empty($atten->leaveDay->leave->note) ? $atten->leaveDay->leave->note : '');
                                                            $leaveHour = (isset($atten->leaveDay->hour) && $atten->leaveDay->hour > 0 ? $atten->leaveDay->hour : 0);
                                                            $hours = (intval(trim($leaveHour)) / 60 >= 1) ? intval(intval(trim($leaveHour)) / 60) : '00';
                                                            $mins = (intval(trim($leaveHour)) % 60 != 0) ? intval(trim($leaveHour)) % 60 : '00';
                                                        
                                                            $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                            $hourMins .= ':';
                                                            $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                        @endphp
                                                        <p class="leaveAttendance">Holiday found for this day {{ $hourMins }} Hours.</p>
                                                    @else
                                                        <p class="leaveAttendance">{{ $leaveName }} {{ (!empty($leaveNote) ? ': '.$leaveNote : '') }}</p>
                                                    @endif
                                                @else
                                                    <div class="flex flex-col sm:flex-row m-0">
                                                        <div class="form-check mr-5">
                                                            <input {{ ($atten->leave_status == 2 ? 'checked' : '') }} id="leave_status_2" class="form-check-input" type="radio" name="attendance[{{ $atten->id }}][leave_status]" value="2">
                                                            <label class="form-check-label" for="leave_status_2">Meeting / Training</label>
                                                        </div>
                                                        <div class="form-check mr-5">
                                                            <input {{ ($atten->leave_status == 3 ? 'checked' : '') }} id="leave_status_3" class="form-check-input" type="radio" name="attendance[{{ $atten->id }}][leave_status]" value="3">
                                                            <label class="form-check-label" for="leave_status_3">Sick Leave</label>
                                                        </div>
                                                        <div class="form-check mr-5">
                                                            <input {{ ($atten->leave_status == 4 ? 'checked' : '') }} id="leave_status_4" class="form-check-input" type="radio" name="attendance[{{ $atten->id }}][leave_status]" value="4">
                                                            <label class="form-check-label" for="leave_status_4">Authorised Unpaid</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input {{ ($atten->leave_status == 5 ? 'checked' : '') }} id="leave_status_5" class="form-check-input" type="radio" name="attendance[{{ $atten->id }}][leave_status]" value="5">
                                                            <label class="form-check-label" for="leave_status_5">Authorised Paid</label>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][leave_adjustment]" value="{{ $atten->leave_adjustment }}" type="text" placeholder="+/-00:00" class="absent_adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="absent_hour_text font-medium">{{ $atten->leaves_hour }}</span>
                                                <input type="hidden" class="absent_hour" name="attendance[{{ $atten->id }}][leave_hour]" data-prev="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}" value="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}"/>
                                            </td>
                                            <td>
                                                <input name="attendance[{{ $atten->id }}][adjustment]" value="{{ $atten->adjustment }}" type="hidden" placeholder="+/-00:00" class="adjustment"/>
                                                <input type="hidden" class="total_work_hour" name="attendance[{{ $atten->id }}][total_work_hour]" value="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][clockin_system]" value="{{ $atten->clockin_system }}" class="clockin_system"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][clockout_system]" value="{{ $atten->clockout_system }}" class="clockout_system"/>
                                                <input type="hidden" class="paid_break" name="attendance[{{ $atten->id }}][paid_break]" value="{{ $atten->paid_break }}"/>
                                                <input type="hidden" class="unpadi_break" name="attendance[{{ $atten->id }}][unpadi_break]" value="{{ $atten->unpadi_break }}"/>
                                                <input type="hidden" class="total_break" name="attendance[{{ $atten->id }}][total_break]" value="{{ (!empty($atten->total_break) ? $atten->total_break : 0) }}"/>
                                                <input type="hidden" class="allowed_br" name="attendance[{{ $atten->id }}][allowed_br]" value="{{ $atten->allowed_break }}"/>

                                                <button data-id="{{ $atten->id }}" type="button" class="editRowNote btn-rounded btn btn-primary text-white p-0 w-9 h-9 ml-1"><i data-lucide="scroll-text" class="w-4 h-4"></i></button>
                                                <button data-id="{{ $atten->id }}" type="button" class="saveRow btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="save" class="w-4 h-4"></i></button>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][attendance_id]" value="{{ $atten->id }}"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][user_issues]" value="{{ $atten->user_issues }}"/>
                                            </td>
                                        </tr>
                                        <tr class="attendanceNoteRow attendanceNoteRow_{{ $atten->id }}" id="attendanceNoteRow_{{ $atten->id }}" style="display: none;">
                                            <td colspan="8">
                                                <textarea name="attendance[{{ $atten->id }}][note]" class="w-full form-control rounded-0" rows="2">{{ $atten->note }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else 
                                    <tr>
                                        <td colspan="8">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No data found with in absent.
                                            </div>
                                        </td>
                                    </tr>
                                @endif 
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="accordion-item"> 
                <div id="attendanceAccordion-noissues" class="accordion-header">
                    <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#attendanceAccordion-collapse-noissues" aria-expanded="false" aria-controls="attendanceAccordion-collapse-noissues">
                        No Issues <strong>{{ ($noissues->count() > 0 ? '('.$noissues->count().')' : '' )}}</strong>
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="attendanceAccordion-collapse-noissues" class="accordion-collapse collapse" aria-labelledby="attendanceAccordion-noissues" data-tw-parent="#attendanceAccordion">
                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                        <div data-table="#noIssuedAttendance" class="saveAllRow text-right mb-5" style="display: none;">
                            <button data-table="#noIssuedAttendance" type="button" class="saveAllAttendance btn btn-primary w-auto"> 
                                <i data-lucide="save-all" class="w-4 h-4 mr-2"></i>    
                                Save Selected                    
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                        <table class="table table-sm table-bordered dailyAttendanceTable" id="issuedAttendance">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center inputCheckbox">
                                        <div class="form-check m-0 justify-center">
                                            <input  class="form-check-input checkAll m-0" type="checkbox" name="checkAllAttendance" value="1"/>
                                        </div>
                                    </th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Clock In</th>
                                    <th class="whitespace-nowrap">Clock Out</th>
                                    <th class="whitespace-nowrap">Break</th>
                                    <th class="whitespace-nowrap">Adjustment</th>
                                    <th class="whitespace-nowrap">Hour</th>
                                    <th class="whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($noissues) && $noissues->count() > 0)
                                    @foreach($noissues as $atten)
                                        @php 
                                            $isses_field = (isset($atten->isses_field) && !empty($atten->isses_field) ? unserialize(base64_decode($atten->isses_field)) : []);
                                            $clockin = (isset($isses_field['clockin_system']) && $isses_field['clockin_system'] == 1) ? 1 : 0;
                                            $clockout = (isset($isses_field['clockout_system']) && $isses_field['clockout_system'] == 1) ? 1 : 0;
                                            $break_return = (isset($isses_field['break_return']) && $isses_field['break_return'] == 1) ? 1 : 0;
                                        @endphp
                                        <tr class="attendanceRow attendanceRow_{{ $atten->id }}" id="attendanceRow_{{ $atten->id }}" data-id="{{ $atten->id }}">
                                            <td class="text-center inputCheckbox" rowspan="{{ ($atten->leave_status > 0 ? 2 : 0) }}">
                                                <div class="form-check m-0 justify-center">
                                                    <input  class="form-check-input employee_attendance_id m-0" type="checkbox" name="attendance[{{ $atten->id }}][id]" value="{{ $atten->id }}"/>
                                                </div>
                                            </td>
                                            <td rowspan="{{ ($atten->leave_status > 0 ? 2 : 0) }}">
                                                <span class="m-0 font-medium block">
                                                    {{ (isset($atten->employee->title->name) && !empty($atten->employee->title->name) ? $atten->employee->title->name : '') }}
                                                    {{ (isset($atten->employee->first_name) && !empty($atten->employee->first_name) ? ' '.$atten->employee->first_name : '') }}
                                                    {{ (isset($atten->employee->last_name) && !empty($atten->employee->last_name) ? ' '.$atten->employee->last_name : '') }}
                                                </span>
                                                @if(isset($atten->employee->employment->employeeJobTitle->name) && !empty($atten->employee->employment->employeeJobTitle->name))
                                                    <span class="m-0 font-medium text-slate-400 block">
                                                        {{ $atten->employee->employment->employeeJobTitle->name }}
                                                    </span>
                                                @endif
                                                @if(isset($atten->pay->hourly_rate) && !empty($atten->pay->hourly_rate))
                                                    <span class="m-0 font-medium block">
                                                        £{{ $atten->pay->hourly_rate }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockin_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockin_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockin_system]" value="{{ $atten->clockin_system }}" 
                                                                type="text" 
                                                                class="clockin_system rounded-0 time form-control w-full"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockout_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockout_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockout_system]" value="{{ $atten->clockout_system }}" 
                                                                type="text" 
                                                                class="clockout_system time rounded-0 form-control w-full"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->paid_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Un Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->unpadi_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Taken</span></td>
                                                        <td>
                                                            @if(!empty($atten->break_details_html))
                                                                <a data-haserror="{{ $break_return }}" data-id="{{ $atten->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#viewBreakModal" class="view_break font-medium text-primary"><u>{{ $atten->break_time }}</u></a>
                                                            @else:
                                                                <a href="javascript:void(0);" class="view_break font-medium text-primary"><u>{{ $atten->break_time }}</u></a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                                <input type="hidden" class="paid_break" name="attendance[{{ $atten->id }}][paid_break]" value="{{ $atten->paid_break }}"/>
                                                <input type="hidden" class="unpadi_break" name="attendance[{{ $atten->id }}][unpadi_break]" value="{{ $atten->unpadi_break }}"/>
                                                <input type="hidden" class="total_break" name="attendance[{{ $atten->id }}][total_break]" value="{{ (!empty($atten->total_break) ? $atten->total_break : 0) }}"/>
                                                <input type="hidden" class="allowed_br" name="attendance[{{ $atten->id }}][allowed_br]" value="{{ $atten->allowed_break }}"/>
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][adjustment]" value="{{ $atten->adjustment }}" type="text" placeholder="+/-00:00" class="adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="total_work_hour_text font-medium">{{ $atten->work_hour }}</span>
                                                <input type="hidden" class="total_work_hour" name="attendance[{{ $atten->id }}][total_work_hour]" data-prev="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}" value="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}"/>
                                            </td>
                                            <td rowspan="{{ ($atten->leave_status > 0 ? 2 : 0) }}">
                                                <button data-id="{{ $atten->id }}" type="button" class="editRowNote btn-rounded btn btn-primary text-white p-0 w-9 h-9 ml-1"><i data-lucide="scroll-text" class="w-4 h-4"></i></button>
                                                <button data-id="{{ $atten->id }}" type="button" class="saveRow btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="save" class="w-4 h-4"></i></button>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][attendance_id]" value="{{ $atten->id }}"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][user_issues]" value="{{ $atten->user_issues }}"/>
                                            </td>
                                        </tr>
                                        @if($atten->leave_status > 0)
                                        <tr class="attendanceLeaveRow attendanceLeaveRow_{{ $atten->id }} bg-slate-100" id="attendanceLeaveRow_{{ $atten->id }}">
                                            <td colspan="3">
                                                @if($atten->employee_leave_day_id > 0)
                                                    @php 
                                                        $leaveName = '';
                                                        switch($atten->leave_status):
                                                            case 1:
                                                                $leaveName = 'Holiday / Vacation';
                                                                break;
                                                            case 2:
                                                                $leaveName = 'Meeting / Training';
                                                                break;
                                                            case 3:
                                                                $leaveName = 'Sick Leave';
                                                                break;
                                                            case 4:
                                                                $leaveName = 'Authorised Unpaid';
                                                                break;
                                                            case 5:
                                                                $leaveName = 'Authorised Paid';
                                                                break;
                                                        endswitch
                                                    @endphp
                                                    @if($atten->leave_status == 1)
                                                        @php 
                                                            $leaveNote = (isset($atten->leaveDay->leave->note) && !empty($atten->leaveDay->leave->note) ? $atten->leaveDay->leave->note : '');
                                                            $leaveHour = (isset($atten->leave_hour) && $atten->leave_hour > 0 ? $atten->leave_hour : 0);
                                                            $hours = (intval(trim($leaveHour)) / 60 >= 1) ? intval(intval(trim($leaveHour)) / 60) : '00';
                                                            $mins = (intval(trim($leaveHour)) % 60 != 0) ? intval(trim($leaveHour)) % 60 : '00';
                                                        
                                                            $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                            $hourMins .= ':';
                                                            $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                        @endphp
                                                        <p class="leaveAttendance font-medium">{{ $leaveName }} found for this day.</p>
                                                    @else
                                                        <p class="leaveAttendance font-medium">{{ $leaveName }} {{ (!empty($leaveNote) ? ': '.$leaveNote : '') }}</p>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][leave_adjustment]" value="{{ $atten->leave_adjustment }}" type="text" placeholder="+/-00:00" class="leave_adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="leave_hour_text font-medium">{{ $atten->leaves_hour }}</span>
                                                <input type="hidden" class="leave_hour" name="attendance[{{ $atten->id }}][leave_hour]" data-prev="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}" value="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}"/>
                                                <input type="hidden" class="leave_status" name="attendance[{{ $atten->id }}][leave_status]" value="{{ ($atten->leave_status > 0 ? $atten->leave_status : 0) }}"/>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr class="attendanceNoteRow attendanceNoteRow_{{ $atten->id }}" id="attendanceNoteRow_{{ $atten->id }}" style="display: none;">
                                            <td colspan="8">
                                                <textarea name="attendance[{{ $atten->id }}][note]" class="w-full form-control rounded-0" rows="2">{{ $atten->note }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else 
                                    <tr>
                                        <td colspan="8">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No data found with issues.
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="attendanceAccordion-issues" class="accordion-header">
                    <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#attendanceAccordion-collapse-issues" aria-expanded="false" aria-controls="attendanceAccordion-collapse-issues">
                        Issues <strong>{{ ($issues->count() > 0 ? '('.$issues->count().')' : '' )}}</strong>
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="attendanceAccordion-collapse-issues" class="accordion-collapse collapse" aria-labelledby="attendanceAccordion-issues" data-tw-parent="#attendanceAccordion">
                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                        <div data-table="#issuedAttendance" class="saveAllRow text-right mb-5" style="display: none;">
                            <button data-table="#issuedAttendance" type="button" class="saveAllAttendance btn btn-primary w-auto"> 
                                <i data-lucide="save-all" class="w-4 h-4 mr-2"></i>    
                                Save Selected                    
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                        <table class="table table-sm table-bordered dailyAttendanceTable" id="issuedAttendance">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center inputCheckbox">
                                        <div class="form-check m-0 justify-center">
                                            <input  class="form-check-input checkAll m-0" type="checkbox" name="checkAllAttendance" value="1"/>
                                        </div>
                                    </th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Clock In</th>
                                    <th class="whitespace-nowrap">Clock Out</th>
                                    <th class="whitespace-nowrap">Break</th>
                                    <th class="whitespace-nowrap">Adjustment</th>
                                    <th class="whitespace-nowrap">Hour</th>
                                    <th class="whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($issues) && $issues->count() > 0)
                                    @foreach($issues as $atten)
                                        @php 
                                            $isses_field = (isset($atten->isses_field) && !empty($atten->isses_field) ? unserialize(base64_decode($atten->isses_field)) : []);
                                            $clockin = (isset($isses_field['clockin_system']) && $isses_field['clockin_system'] == 1) ? 1 : 0;
                                            $clockout = (isset($isses_field['clockout_system']) && $isses_field['clockout_system'] == 1) ? 1 : 0;
                                            $break_return = (isset($isses_field['break_return']) && $isses_field['break_return'] == 1) ? 1 : 0;
                                        @endphp
                                        <tr class="attendanceRow attendanceRow_{{ $atten->id }}" id="attendanceRow_{{ $atten->id }}" data-id="{{ $atten->id }}">
                                            <td class="text-center inputCheckbox" {{ ($atten->leave_status > 0 ? 'rowspan=2' : '') }}>
                                                <div class="form-check m-0 justify-center">
                                                    <input  class="form-check-input employee_attendance_id m-0" type="checkbox" name="attendance[{{ $atten->id }}][id]" value="{{ $atten->id }}"/>
                                                </div>
                                            </td>
                                            <td {{ ($atten->leave_status > 0 ? 'rowspan=2' : '') }}>
                                                <span class="m-0 font-medium block">
                                                    {{ (isset($atten->employee->title->name) && !empty($atten->employee->title->name) ? $atten->employee->title->name : '') }}
                                                    {{ (isset($atten->employee->first_name) && !empty($atten->employee->first_name) ? ' '.$atten->employee->first_name : '') }}
                                                    {{ (isset($atten->employee->last_name) && !empty($atten->employee->last_name) ? ' '.$atten->employee->last_name : '') }}
                                                </span>
                                                @if(isset($atten->employee->employment->employeeJobTitle->name) && !empty($atten->employee->employment->employeeJobTitle->name))
                                                    <span class="m-0 font-medium text-slate-400 block">
                                                        {{ $atten->employee->employment->employeeJobTitle->name }}
                                                    </span>
                                                @endif
                                                @if(isset($atten->pay->hourly_rate) && !empty($atten->pay->hourly_rate))
                                                    <span class="m-0 font-medium block">
                                                        £{{ $atten->pay->hourly_rate }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockin_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium {{ ($clockin == 1 ? 'text-danger' : '') }}">{{ $atten->clockin_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockin_system]" value="{{ $atten->clockin_system }}" 
                                                                type="text" 
                                                                class="clockin_system rounded-0 time form-control w-full {{ ($clockin == 1 ? 'border-danger' : '') }}"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockout_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium {{ ($clockout == 1 ? 'text-danger' : '') }}">{{ $atten->clockout_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockout_system]" value="{{ $atten->clockout_system }}" 
                                                                type="text" 
                                                                class="clockout_system time rounded-0 form-control w-full {{ ($clockout == 1 ? 'border-danger' : '') }}" />
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->paid_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Un Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->unpadi_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Taken</span></td>
                                                        <td>
                                                            @if(!empty($atten->break_details_html))
                                                                <a data-haserror="{{ $break_return }}" data-id="{{ $atten->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#viewBreakModal" class="view_break font-medium {{ ($break_return == 1 ? 'text-danger' : 'text-primary') }}"><u>{{ $atten->break_time }}</u></a>
                                                            @else:
                                                                <a href="javascript:void(0);" class="view_break font-medium text-primary"><u>{{ $atten->break_time }}</u></a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                                <input type="hidden" class="paid_break" name="attendance[{{ $atten->id }}][paid_break]" value="{{ $atten->paid_break }}"/>
                                                <input type="hidden" class="unpadi_break" name="attendance[{{ $atten->id }}][unpadi_break]" value="{{ $atten->unpadi_break }}"/>
                                                <input type="hidden" class="total_break" name="attendance[{{ $atten->id }}][total_break]" value="{{ (!empty($atten->total_break) ? $atten->total_break : 0) }}"/>
                                                <input type="hidden" class="allowed_br" name="attendance[{{ $atten->id }}][allowed_br]" value="{{ $atten->allowed_break }}"/>
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][adjustment]" value="{{ $atten->adjustment }}" type="text" placeholder="+/-00:00" class="adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="total_work_hour_text font-medium">{{ $atten->work_hour }}</span>
                                                <input type="hidden" class="total_work_hour" name="attendance[{{ $atten->id }}][total_work_hour]" data-prev="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}" value="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}"/>
                                            </td>
                                            <td {{ ($atten->leave_status > 0 ? 'rowspan=2' : '') }}>
                                                <button data-id="{{ $atten->id }}" type="button" class="editRowNote btn-rounded btn btn-primary text-white p-0 w-9 h-9 ml-1"><i data-lucide="scroll-text" class="w-4 h-4"></i></button>
                                                <button data-id="{{ $atten->id }}" type="button" class="saveRow btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="save" class="w-4 h-4"></i></button>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][attendance_id]" value="{{ $atten->id }}"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][user_issues]" value="{{ $atten->user_issues }}"/>
                                            </td>
                                        </tr>
                                        @if($atten->leave_status > 0)
                                        <tr class="attendanceLeaveRow attendanceLeaveRow_{{ $atten->id }} bg-slate-100" id="attendanceLeaveRow_{{ $atten->id }}">
                                            <td colspan="3">
                                                @if($atten->employee_leave_day_id > 0)
                                                    @php 
                                                        $leaveName = '';
                                                        switch($atten->leave_status):
                                                            case 1:
                                                                $leaveName = 'Holiday / Vacation';
                                                                break;
                                                            case 2:
                                                                $leaveName = 'Meeting / Training';
                                                                break;
                                                            case 3:
                                                                $leaveName = 'Sick Leave';
                                                                break;
                                                            case 4:
                                                                $leaveName = 'Authorised Unpaid';
                                                                break;
                                                            case 5:
                                                                $leaveName = 'Authorised Paid';
                                                                break;
                                                        endswitch
                                                    @endphp
                                                    @if($atten->leave_status == 1)
                                                        @php 
                                                            $leaveNote = (isset($atten->leaveDay->leave->note) && !empty($atten->leaveDay->leave->note) ? $atten->leaveDay->leave->note : '');
                                                            $leaveHour = (isset($atten->leave_hour) && $atten->leave_hour > 0 ? $atten->leave_hour : 0);
                                                            $hours = (intval(trim($leaveHour)) / 60 >= 1) ? intval(intval(trim($leaveHour)) / 60) : '00';
                                                            $mins = (intval(trim($leaveHour)) % 60 != 0) ? intval(trim($leaveHour)) % 60 : '00';
                                                        
                                                            $hourMins = (($hours < 10 && $hours != '00') ? '0' . $hours : $hours);
                                                            $hourMins .= ':';
                                                            $hourMins .= ($mins < 10 && $mins != '00') ? '0'.$mins : $mins;
                                                        @endphp
                                                        <p class="leaveAttendance font-medium">{{ $leaveName }} found for this day.</p>
                                                    @else
                                                        <p class="leaveAttendance font-medium">{{ $leaveName }} {{ (!empty($leaveNote) ? ': '.$leaveNote : '') }}</p>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][leave_adjustment]" value="{{ $atten->leave_adjustment }}" type="text" placeholder="+/-00:00" class="leave_adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="leave_hour_text font-medium">{{ $atten->leaves_hour }}</span>
                                                <input type="hidden" class="leave_hour" name="attendance[{{ $atten->id }}][leave_hour]" data-prev="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}" value="{{ ($atten->leave_hour > 0 ? $atten->leave_hour : 0) }}"/>
                                                <input type="hidden" class="leave_status" name="attendance[{{ $atten->id }}][leave_status]" value="{{ ($atten->leave_status > 0 ? $atten->leave_status : 0) }}"/>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr class="attendanceNoteRow attendanceNoteRow_{{ $atten->id }}" id="attendanceNoteRow_{{ $atten->id }}" style="display: none;">
                                            <td colspan="8">
                                                <textarea name="attendance[{{ $atten->id }}][note]" class="w-full form-control rounded-0" rows="2">{{ $atten->note }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else 
                                    <tr>
                                        <td colspan="8">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No data found with issues.
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="attendanceAccordion-notinschedule" class="accordion-header">
                    <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#attendanceAccordion-collapse-notinschedule" aria-expanded="false" aria-controls="attendanceAccordion-collapse-notinschedule">
                        Not In Schedule <strong>{{ ($overtime->count() > 0 ? '('.$overtime->count().')' : '' )}}</strong>
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="attendanceAccordion-collapse-notinschedule" class="accordion-collapse collapse" aria-labelledby="attendanceAccordion-notinschedule" data-tw-parent="#attendanceAccordion">
                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                        <div data-table="#overtimeAttendance" class="saveAllRow text-right mb-5" style="display: none;">
                            <button data-table="#overtimeAttendance" type="button" class="saveAllAttendance btn btn-primary w-auto"> 
                                <i data-lucide="save-all" class="w-4 h-4 mr-2"></i>    
                                Save Selected                    
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                        <table class="table table-sm table-bordered dailyAttendanceTable" id="overtimeAttendance">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap text-center inputCheckbox">
                                        <div class="form-check m-0 justify-center">
                                            <input  class="form-check-input checkAll m-0" type="checkbox" name="checkAllAttendance" value="1"/>
                                        </div>
                                    </th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Clock In</th>
                                    <th class="whitespace-nowrap">Clock Out</th>
                                    <th class="whitespace-nowrap">Break</th>
                                    <th class="whitespace-nowrap">Adjustment</th>
                                    <th class="whitespace-nowrap">Hour</th>
                                    <th class="whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($overtime) && $overtime->count() > 0)
                                    @foreach($overtime as $atten)
                                        @php 
                                            $isses_field = (isset($atten->isses_field) && !empty($atten->isses_field) ? unserialize(base64_decode($atten->isses_field)) : []);
                                            $clockin = (isset($isses_field['clockin_system']) && $isses_field['clockin_system'] == 1) ? 1 : 0;
                                            $clockout = (isset($isses_field['clockout_system']) && $isses_field['clockout_system'] == 1) ? 1 : 0;
                                            $break_return = (isset($isses_field['break_return']) && $isses_field['break_return'] == 1) ? 1 : 0;
                                        @endphp
                                        <tr class="attendanceRow attendanceRow_{{ $atten->id }}" id="attendanceRow_{{ $atten->id }}" data-id="{{ $atten->id }}">
                                            <td class="text-center inputCheckbox">
                                                <div class="form-check m-0 justify-center">
                                                    <input  class="form-check-input employee_attendance_id m-0" type="checkbox" name="attendance[{{ $atten->id }}][id]" value="{{ $atten->id }}"/>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="m-0 font-medium block">
                                                    {{ (isset($atten->employee->title->name) && !empty($atten->employee->title->name) ? $atten->employee->title->name : '') }}
                                                    {{ (isset($atten->employee->first_name) && !empty($atten->employee->first_name) ? ' '.$atten->employee->first_name : '') }}
                                                    {{ (isset($atten->employee->last_name) && !empty($atten->employee->last_name) ? ' '.$atten->employee->last_name : '') }}
                                                </span>
                                                @if(isset($atten->employee->employment->employeeJobTitle->name) && !empty($atten->employee->employment->employeeJobTitle->name))
                                                    <span class="m-0 font-medium text-slate-400 block">
                                                        {{ $atten->employee->employment->employeeJobTitle->name }}
                                                    </span>
                                                @endif
                                                @if(isset($atten->pay->hourly_rate) && !empty($atten->pay->hourly_rate))
                                                    <span class="m-0 font-medium block">
                                                        £{{ $atten->pay->hourly_rate }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockin_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium {{ ($clockin == 1 ? 'text-danger' : '') }}">{{ $atten->clockin_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockin_system]" value="{{ $atten->clockin_system }}" 
                                                                type="text" 
                                                                class="clockin_system rounded-0 time form-control w-full {{ ($clockin == 1 ? 'border-danger' : '') }}"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Contract</span></td>
                                                        <td><span class="font-medium">{{ $atten->clockout_contract }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Punch</span></td>
                                                        <td><span class="font-medium {{ ($clockout == 1 ? 'text-danger' : '') }}">{{ $atten->clockout_punch }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">System</span></td>
                                                        <td>
                                                            <input name="attendance[{{ $atten->id }}][clockout_system]" value="{{ $atten->clockout_system }}" 
                                                                type="text" 
                                                                class="clockout_system time rounded-0 form-control w-full {{ ($clockout == 1 ? 'border-danger' : '') }}"/>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td>
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->paid_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Un Paid</span></td>
                                                        <td><span class="font-medium">{{ $atten->unpadi_break }}</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td><span class="font-medium text-slate-400">Taken</span></td>
                                                        <td>
                                                            @if(!empty($atten->break_details_html))
                                                                <a data-haserror="{{ $break_return }}" data-id="{{ $atten->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#viewBreakModal" class="view_break font-medium {{ ($break_return == 1 ? 'text-danger' : 'text-primary') }}"><u>{{ $atten->break_time }}</u></a>
                                                            @else:
                                                                <a href="javascript:void(0);" class="view_break font-medium text-primary"><u>{{ $atten->break_time }}</u></a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                                <input type="hidden" class="paid_break" name="attendance[{{ $atten->id }}][paid_break]" value="{{ $atten->paid_break }}"/>
                                                <input type="hidden" class="unpadi_break" name="attendance[{{ $atten->id }}][unpadi_break]" value="{{ $atten->unpadi_break }}"/>
                                                <input type="hidden" class="total_break" name="attendance[{{ $atten->id }}][total_break]" value="{{ (!empty($atten->total_break) ? $atten->total_break : 0) }}"/>
                                                <input type="hidden" class="allowed_br" name="attendance[{{ $atten->id }}][allowed_br]" value="{{ $atten->allowed_break }}"/>
                                            </td>
                                            <td>
                                                <input data-id="{{ $atten->id }}" name="attendance[{{ $atten->id }}][adjustment]" value="{{ $atten->adjustment }}" type="text" placeholder="+/-00:00" class="adjustment rounded-0 form-control w-full"/>
                                            </td>
                                            <td>
                                                <span class="total_work_hour_text font-medium">{{ $atten->work_hour }}</span>
                                                <input type="hidden" class="total_work_hour" name="attendance[{{ $atten->id }}][total_work_hour]" data-prev="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}" value="{{ ($atten->total_work_hour > 0 ? $atten->total_work_hour : 0) }}"/>
                                            </td>
                                            <td>
                                                <button data-id="{{ $atten->id }}" type="button" class="editRowNote btn-rounded btn btn-primary text-white p-0 w-9 h-9 ml-1"><i data-lucide="scroll-text" class="w-4 h-4"></i></button>
                                                <button data-id="{{ $atten->id }}" type="button" class="saveRow btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="save" class="w-4 h-4"></i></button>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][attendance_id]" value="{{ $atten->id }}"/>
                                                <input type="hidden" name="attendance[{{ $atten->id }}][user_issues]" value="{{ $atten->user_issues }}"/>
                                            </td>
                                        </tr>
                                        <tr class="attendanceNoteRow attendanceNoteRow_{{ $atten->id }}" id="attendanceNoteRow_{{ $atten->id }}" style="display: none;">
                                            <td colspan="8">
                                                <textarea name="attendance[{{ $atten->id }}][note]" class="w-full form-control rounded-0" rows="2">{{ $atten->note }}</textarea>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else 
                                    <tr>
                                        <td colspan="8">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No data found with issues.
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Attendance Break Modal -->
    <div id="viewBreakModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="viewBreakForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Attendance Breaks</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20">Cancel</button>
                        <button type="submit" id="updateBreak" style="display: none;" class="btn btn-primary w-auto ml-1">     
                            Update Breaks                      
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Attendance Break Modal -->

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
                        <button type="button" data-action="NONE" class="btn successCloser btn-primary w-24">Ok</button>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-deaily-attedance.js')
@endsection