@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Feed Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('attendance') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Attendance</a>
            <a style="float: right;" target="_blank" href="{{ route('attendance.print',$dateListId) }}" data-id="{{ $dateListId }}" class="btn btn-success text-white w-auto"><i data-lucide="printer" class="w-4 h-4 mr-2"></i>Print</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="overflow-x-auto">
            <table class="table w-full text-left">
                <thead class="">
                    <tr class="">
                        <th>Class Plan ID</th>                                                
                        <th>Term</th>
                        <th>Course & Module</th>
                        <th>Group</th>
                        @if(isset($data['plan']->class_type) && ($data['plan']->class_type == 'Tutorial' || $data['plan']->class_type == 'Seminar'))
                        <th>Personal Tutor</th>
                        @else
                        <th>Tutor</th>
                        @endif
                        <th>Date & Time</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td>{{ $data["plan_id"] }}</td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ isset($data["plan"]->attenTerm->name) && !empty($data["plan"]->attenTerm->name) ? $data["plan"]->attenTerm->name : '' }}</div>
                        </td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["module"] }}{{ (isset($data['plan']->class_type) && !empty($data['plan']->class_type) ? ' - '.$data['plan']->class_type : '')}}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["course"] }}</div>
                        </td>
                        <td>{{ $data["group"] }}</td>
                        @if(isset($data['plan']->class_type) && ($data['plan']->class_type == 'Tutorial' || $data['plan']->class_type == 'Seminar'))
                            <td>{{ (isset($data['plan']->personalTutor->employee->full_name) ? $data['plan']->personalTutor->employee->full_name : '') }}</td>
                        @else
                            <td>{{ (isset($data['plan']->tutor->employee->full_name) ? $data['plan']->tutor->employee->full_name : '') }}</td>
                        @endif
                        <td>
                            {{ $data["date"] }}<br/>
                            {{ $data["start_time"] }} - {{ $data["end_time"] }}
                        </td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["venue"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["room"] }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <form id="attendanceFeedForm" method="post" action="#">
        <div class="intro-y box mt-5 relative z-20">
            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">Attendance Informations</h2>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 sm:col-span-3">
                        <label class="form-label">Tutor</label>
                        <select id="tutor_id" name="attendanceInfo_tutor_id" class="tom-selects w-full">
                            <option value="">Please Select</option>
                            @if($users->count() > 0)
                                @foreach($users as $usr)
                                    <option {{ (isset($atninfo->tutor_id) && $atninfo->tutor_id == $usr->id ? 'Selected' : '') }} value="{{ $usr->id }}">{{ (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : $usr->name )}}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-attendanceInfo_tutor_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-2">
                        <label class="form-label">Start Time</label>
                        <input type="text" name="attendanceInfo_start_time" class="form-control timePicker w-full" value="{{ (isset($atninfo->start_time) && !empty($atninfo->start_time) ? date('H:i', strtotime($atninfo->start_time)) : '') }}" placeholder="14:30"/>
                        <div class="acc__input-error error-attendanceInfo_start_time text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-2">
                        <label class="form-label">End Time</label>
                        <input type="text" name="attendanceInfo_end_time" class="form-control timePicker w-full" value="{{ (isset($atninfo->end_time) && !empty($atninfo->end_time) ? date('H:i', strtotime($atninfo->end_time)) : '') }}" placeholder="16:30"/>
                        <div class="acc__input-error error-attendanceInfo_end_time text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label class="form-label">Short Note</label>
                        <textarea type="text" name="attendanceInfo_note" rows="1" class="form-control w-full">{{ (isset($atninfo->note) && !empty($atninfo->note) ? $atninfo->note : '') }}</textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-2">
                        <label class="form-label">Day Status</label>
                        <select name="plans_date_list_status" class="form-control w-full">
                            <option value="">Please Select</option>
                            <option {{ ($data['status'] == 'Scheduled' ? 'Selected' : '') }} value="Scheduled">Scheduled</option>
                            <option {{ ($data['status'] == 'Ongoing' ? 'Selected' : '') }} value="Ongoing">Ongoing</option>
                            <option {{ ($data['status'] == 'Completed' ? 'Selected' : '') }} value="Completed">Completed</option>
                            <option {{ ($data['status'] == 'Canceled' ? 'Selected' : '') }} value="Canceled">Canceled</option>
                            <option {{ ($data['status'] == 'Unknown' ? 'Selected' : '') }} value="Unknown">Unknown</option>
                        </select>
                        <div class="acc__input-error error-plans_date_list_status text-danger mt-2"></div>
                    </div>
                    <input type="hidden" name="attendanceInfo_id" value="{{ (isset($atninfo->id) && $atninfo->id > 0 ? $atninfo->id : '0') }}"/>
                </div>
            </div>
        </div>
    
    
        <div class="intro-y box p-5 mt-5 relative z-10">
            <div class="overflow-x-auto">
                <table class="table table-bordered w-full text-left" id="feedAttendanceTable">
                    <thead>
                        <tr>
                            <th>Serial</th>                                                
                            <th>Student</th>       
                            <th class="text-center">Attendance</th>
                            <th>
                                <div class="flex justify-end flex-wrap">
                                    @foreach($data["AttendanceFeedStatus"] as $feedType)
                                        @php $buttonDefault = "btn btn-success text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto" @endphp
                                        @switch($feedType->id)
                                            @case(2)
                                                @php $button = 'btn btn-facebook text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(3)
                                                @php $button = 'btn btn-pending text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(4)
                                                @php $button = 'btn btn-danger text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(5)
                                                @php $button = 'btn btn-warning text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(6)
                                                @php $button = 'btn btn-dark text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(7)
                                                @php $button = 'btn btn-instagram text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @case(8)
                                                @php $button = 'btn btn-twitter text-white btn-sm mb-2 sm:mb-0 ml-1 w-auto'; @endphp
                                                @break;
                                            @default
                                                @php $button = $buttonDefault @endphp
                                                @break;
                                        @endswitch
                                        <span data-id="{{ $feedType->id }}" class="{{ $button }} attendanceButon attendanceHeader_{{ $feedType->id }}">{{ $feedType->name }}&nbsp;=&nbsp;<span class="attendanceHeaderCount_{{ $feedType->id }}">{{ '0' }}</span></span>
                                    @endforeach
                                </div>
                            </th>
                            <th>
                                <div class="flex items-start m-0">
                                    <input type="checkbox" class="form-check-input checkAllEmailNotify" id="checkAllEmailNotify" name="checkAllEmailNotify" value="1" />
                                    <label for="checkAllEmailNotify" class="cursor-pointer ml-2">Notify By Email</label>
                                </div>
                            </th>
                            <th>
                                <div class="flex items-start m-0">
                                    <input type="checkbox" class="form-check-input checkAllSmsNotify" id="checkAllSmsNotify" value="1" />
                                    <label for="checkAllSmsNotify" class="cursor-pointer ml-2">Notify By SMS</label>
                                </div>
                            </th>
                        </tr>                    
                    </thead>
                    <tbody class="send-notofication">
                        @php
                            $serial = 1;
                        @endphp
                        @foreach($data["assignStudentList"] as $list) 
                            @php 
								$existAttendance = (isset($data['existAttendances'][$list->student->id]) && $data['existAttendances'][$list->student->id] > 0 ? $data['existAttendances'][$list->student->id] : 0);
							@endphp      
                            <tr class="theAttendanceRow">
                                <td width="100px">{{ $serial }}</td>
                                <td width="w-2/6">
                                    <div class="block">
                                        <div class="w-10 h-10 intro-x image-fit mr-3 inline-block">
                                            <img alt="{{ $list->student->full_name }}" class="rounded-full shadow" src="{{ $list->student->photo_url }}">
                                        </div>
                                        <div class="inline-block relative" style="top: -5px;" >
                                            <div class="font-medium whitespace-nowrap">{{ $list->student->registration_no }}</div>
                                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $list->student->full_name }}</div>
                                        </div>
                                    </div>   
                                    <input type="hidden" name="attendances[{{$data['id']}}][{{$serial}}][student_id]" value="{{ $list->student->id }}">
                                </td>
                                <td style="width: 150px;" class="text-center feedTypeCol font-medium capitalize"></td>
                                <td class="attendance-column">
                                    <div class="flex flex-col sm:flex-row justify-end">
										@foreach($data["AttendanceFeedStatus"] as $feedType)
											@php 
												$buttonDefault = "btn btn-success text-white btn-sm w-auto";
												$color = '#0f9488';
											@endphp
											@switch($feedType->id)
												@case(2)
													@php $button = 'btn btn-facebook text-white btn-sm w-auto'; $color = '#3b5998e6'; @endphp
													@break;
												@case(3)
													@php $button = 'btn btn-pending text-white btn-sm w-auto'; $color = '#d97706e6'; @endphp
													@break;
												@case(4)
													@php $button = 'btn btn-danger text-white btn-sm w-auto'; $color = '#b91c1ce6'; @endphp
													@break;
												@case(5)
													@php $button = 'btn btn-warning text-white btn-sm w-auto'; $color = '#f59e0b'; @endphp
													@break;
												@case(6)
													@php $button = 'btn btn-dark text-white btn-sm w-auto'; $color = '#1e293be6'; @endphp
													@break;
												@case(7)
													@php $button = 'btn btn-instagram text-white btn-sm w-auto'; $color = '#517fa4'; @endphp
													@break;
												@case(8)
													@php $button = 'btn btn-twitter text-white btn-sm w-auto'; $color = '#4ab3f4e6'; @endphp
													@break;
												@default
													@php $button = $buttonDefault; $color = '#0f9488'; @endphp
													@break
											@endswitch
                                            <span class="attendanceCheckbox mb-2 sm:mb-0 ml-1">
                                                <input class="attendanceRadio attendanceRadio_{{ $feedType->id }}" data-type="{{ $feedType->name }}" data-color="{{ $color }}" id="radio-switch-{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" {{ ($existAttendance > 0 && $existAttendance == $feedType->id) ? ' Checked ' : ($existAttendance == 0 && $feedType->id == 4 ? 'Checked' : '') }} name="attendances[{{$data['id']}}][{{$serial}}][attendance_feed_status_id]" value="{{ $feedType->id }}" type="radio"  />
                                                <label class="{{ $button }}" for="radio-switch-{{$data['id']}}-{{$serial}}-{{ $feedType->id }}"><span class="mr-2"><i data-lucide="check-circle" class="w-4 h-4 checkedIcon"></i><i data-lucide="x-circle" class="w-4 h-4 unCheckedIcon"></i></span>{{ $feedType->name }}</label>
                                            </span>
										@endforeach
									</div>
                                </td>
                                <td style="width: 150px;">
                                    <div class="flex items-center justify-center m-0">
                                        <input type="checkbox"  class="form-check-input checkEmailNotify" id="email_notify_{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" name="attendances[{{$data['id']}}][{{$serial}}][email_notify]" value="{{ $list->student->id }}" />
                                    </div>
                                </td>
                                <td style="width: 150px;">
                                    <div class="flex items-center justify-center m-0">
                                        <input type="checkbox" class="form-check-input checkSmsNotify" id="sms_notify_{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" name="attendances[{{$data['id']}}][{{$serial}}][sms_notify]" value="{{ $list->student->id }}" />
                                    </div>
                                </td>
                            </tr>   
                        
                            <input type="hidden" name="attendances[{{$data['id']}}][{{$serial}}][plans_date_list_id]" value="{{ $list->id }}">
                            @php
                                $serial++;
                            @endphp
                        @endforeach                                     
                    </tbody>
                </table>
            </div>
            <div class="flex justify-end mt-5">
                <button type="submit" id="saveAtnBtn" class="btn btn-success shadow-md text-white">Save Attendance
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

                <input type="hidden" name="plan_date_list_id" value="{{ $data['id'] }}" />
                <input type="hidden" name="plan_id" value="{{ $data['plan_id'] }}" />
                <input type="hidden" name="tutor_id" value="{{ $data['plan']->tutor_id }}" />
            </div>
        </div>
    </form>
    @include('pages.attendance.modals')
@endsection

@section('script')
    @vite('resources/js/attendance-feed.js')
@endsection