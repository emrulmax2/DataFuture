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
            <table data-tw-merge class="table w-full text-left">
                <thead data-tw-merge class="">
                    <tr data-tw-merge class="">
                        
                            <th>Class Plan ID</th>                                                
                            <th>Term</th>
                            <th>Course & Module</th>
                            <th>Group</th>
                            <th>Tutor name</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Date</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <tr data-tw-merge class="">
                        <td>{{ $data["plan_id"] }}</td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["term_name"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["term"] }}</div>
                        </td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["course"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["module"] }}</div>
                        </td>
                        <td>{{ $data["group"] }}</td>
                        <td>{{ $data["tutor"] }}</td>
                        <td>{{ $data["start_time"] }} - {{ $data["end_time"] }}</td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["venue"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["room"] }}</div>
                        </td>
                        <td>{{ $data["date"] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <form id="attendanceFeed" method="post" action="#">
        <div class="intro-y box p-5 mt-5">
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
								$existAttendance = (isset($data['exist_attendances'][$list->student->id]) && $data['exist_attendances'][$list->student->id] > 0 ? $data['exist_attendances'][$list->student->id] : 0);
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
                                        <input type="checkbox"  class="form-check-input checkEmailNotify" id="email_notify_{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" name="attendances[{{$data['id']}}][{{$serial}}][email_notify]" value="1" />
                                    </div>
                                </td>
                                <td style="width: 150px;">
                                    <div class="flex items-center justify-center m-0">
                                        <input type="checkbox" class="form-check-input checkSmsNotify" id="sms_notify_{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" name="attendances[{{$data['id']}}][{{$serial}}][sms_notify]" value="1" />
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
                <button type="submit" class="save btn btn-success shadow-md text-white">Save Attendance
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
                <input type="hidden" name="url" value="{{ route('attendance.store') }}" />
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