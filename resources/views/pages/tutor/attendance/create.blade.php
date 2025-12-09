@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
	<div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance Tracking</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
			@if(isset($data["attendanceInformation"]->end_time) && $data["attendanceInformation"]->end_time != null)
				<div class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer border-success text-success mb-2 sm:mb-0 mr-1  w-auto  ">Class Ended</div>    
			@endif
			<a href="{{ ($type == 1 ? route('pt.dashboard') : ($type == 2 ? route('tutor-dashboard.plan.module.show', $data['plan_id']) : ($type == 3 ? route('dashboard') : route('tutor-dashboard.show.new')))) }}" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 sm:mb-0 mr-1 ">Back to Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: HTML Table Data -->
    <div class="mt-5">
		<div class="grid grid-cols-12 gap-4">
			<div class="col-span-4">
				<div class="intro-y box p-5 h-auto sm:h-full">
					<div class="grid grid-cols-12 gap-0 mb-3">
						<div class="col-span-4 text-slate-500 font-medium">Term</div>
						<div class="col-span-8 font-medium">{{ $data['term_dec_name'] }}</div>
					</div>
					<div class="grid grid-cols-12 gap-0 mb-3">
						<div class="col-span-4 text-slate-500 font-medium">Module</div>
						<div class="col-span-8 font-medium">{{ $data['module'].(isset($data['group']) && !empty($data['group']) ? ' - '.$data['group'] : '') }}</div>
					</div>
					<div class="grid grid-cols-12 gap-0">
						<div class="col-span-4 text-slate-500 font-medium">{{ ($data['tutor_id'] > 0 ? 'Tutor' : ($data['personal_tutor_id'] > 0 ? 'Personal Tutor' : 'Tutor')) }}</div>
						<div class="col-span-8 font-medium">{{ ($data['tutor_id'] > 0 ? $data['tutor'] : ($data['personal_tutor_id'] > 0 ? $data['personal_tutor'] : 'Unknown')) }}</div>
					</div>
				</div>
			</div>
			<div class="col-span-2">
				<div class="intro-y box p-5 h-auto sm:h-full bg-success">
					<div class="theClockWrap h-full flex justify-center items-center font-bold whitespace-nowrap text-4xl text-white"  id="dataclassend" data-classend="{{ ($data['attendanceInformation']->end_time == null ? 0 : 1) }}">
						<label id="hours">{{ ($data['classTakenTimeHour'] < 10 ? '0'.$data['classTakenTimeHour'] : $data['classTakenTimeHour']) }}</label>:<label id="minutes">{{ ($data["classTakenTimeMin"] < 10 ? '0'.$data["classTakenTimeMin"] : $data["classTakenTimeMin"]) }}</label>:<label id="seconds">{{ ($data["classTakenTimeSeconds"] < 10 ? '0'.$data["classTakenTimeSeconds"] : $data["classTakenTimeSeconds"]) }}</label>
					</div>
				</div>
			</div>
			<div class="col-span-4">
				<div class="intro-y box p-5 h-auto sm:h-full">
					<div class="grid grid-cols-12 gap-0 mb-3">
						<div class="col-span-4 text-slate-500 font-medium">Started</div>
						<div class="col-span-8 font-medium">
							{{ date('h:i A', strtotime($data['attendanceInformation']->start_time)).($data["attendanceInformation"]->end_time != null ? ' - '.date('h:i A', strtotime($data["attendanceInformation"]->end_time)) : '') }}
						</div>
					</div>
					<div class="grid grid-cols-12 gap-0 mb-3">
						<div class="col-span-4 text-slate-500 font-medium">Date</div>
						<div class="col-span-8 font-medium">
							{{ $data['date'] }}<br/>
							{{ $data['start_time'] }} - {{ $data['end_time'] }}
						</div>
					</div>
					<div class="grid grid-cols-12 gap-0">
						<div class="col-span-4 text-slate-500 font-medium">Location</div>
						<div class="col-span-8 font-medium">
							{{ $data['venue'] }}<br/>
							<span class="text-success">{{ $data['room'] }}</span>
						</div>
					</div>
				</div>
			</div>
			<div class="col-span-2">
				<div class="intro-y box p-5 h-auto sm:h-full bg-primary">
					<div data-numofstd="{{ $data['assignStudentList']->count() }}" class="attendanceCountWrap h-full flex justify-center items-center font-bold whitespace-nowrap text-4xl text-white">0/{{ $data["assignStudentList"]->count() }}</div>
				</div>
			</div>
		</div>
	</div>

    
    @if($data["attendanceInformation"]->end_time == null || ($data['feed_given'] != 1 && $data['feed_count'] == 0) || (isset(auth()->user()->priv()['edit_attendance']) && auth()->user()->priv()['edit_attendance'] == 1))
    <form id="attendanceFeed" method="post" >
    @endif
    	<div class="intro-y box p-5 mt-5">
      		<div class="overflow-x-auto">
				<table class="table table-bordered text-left" id="feedAttendanceTable">
					<thead>
						<tr>
							<th style="width: 150px;">#SL</th>
							<th class="w-2/6">Student</th>
							<th style="width: 150px;" class="text-center">Status</th>
							<th class="att_count_area">
								&nbsp;
								<!--<div class="flex justify-end items-center">
									<span>Attendance</span>
									<div class="infoBtns ml-auto">
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
													@break
											@endswitch
											@if($feedType->tutor_availability==1)
												<span class="{{ $button }}">{{ $feedType->name }}&nbsp;=&nbsp;<span class="{{ $feedType->code }}-val">{{ isset($data["feedCount"][$feedType->id]) ? $data["feedCount"][$feedType->id] : 0 }}</span></span>
											@endif
										@endforeach
									</div>
								</div>-->
							</th>
						</tr>
					</thead>
					<tbody class="send-notofication">
						@php
							$serial = 1
						@endphp
                		@foreach($data["assignStudentList"] as $list)
							@php 
								$existAttendance = (isset($data['attendanceFeed'][$list->student->id]) && $data['attendanceFeed'][$list->student->id] > 0 ? $data['attendanceFeed'][$list->student->id] : 0);
								$statusActive = (isset($list->student->status->active) && $list->student->status->active == 1 ? 1 : 0);
							@endphp
							@if($existAttendance > 0 || $statusActive == 1 || ($list->student->status->id == 43 || $list->student->status->id == 47))
								<tr class="gradeA theAttendanceRow">
									<td width="150px">{{ $serial }}</td>
									<td width="w-2/6">
										<div class="block">
											<div class="w-10 h-10 intro-x image-fit mr-3 inline-block">
												<img alt="{{ $list->student->full_name }}" class="rounded-full shadow" src="{{ $list->student->photo_url }}">
											</div>
											<div class="inline-block relative" >
												<div class="font-medium whitespace-nowrap {{ $list->student->status->id==43 ? 'text-danger': ''; }}">{{ $list->student->registration_no }}</div>
                                                <div class="text-slate-500 text-xs whitespace-nowrap">{{ $list->student->full_name }}</div>
                                                @if($list->student->status->id==43)
                                                    <div class="text-danger text-xs whitespace-nowrap">{{ $list->student->status->name }}</div>
                                                @endif
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
												@if($feedType->tutor_availability == 1)
													<span class="attendanceCheckbox mb-2 sm:mb-0 ml-1">
														<input class="attendanceRadio" data-type="{{ $feedType->name }}" data-color="{{ $color }}" id="radio-switch-{{$data['id']}}-{{$serial}}-{{ $feedType->id }}" {{ ($existAttendance > 0 && $existAttendance == $feedType->id) ? ' checked ' : ($existAttendance == 0 && $feedType->id == 4 ? 'checked' : '') }} name="attendances[{{$data['id']}}][{{$serial}}][attendance_feed_status_id]" value="{{ $feedType->id }}" type="radio"  />
														<label class="{{ $button }}" for="radio-switch-{{$data['id']}}-{{$serial}}-{{ $feedType->id }}"><span class="mr-2"><i data-lucide="check-circle" class="w-4 h-4 checkedIcon"></i><i data-lucide="x-circle" class="w-4 h-4 unCheckedIcon"></i></span>{{ $feedType->name }}</label>
													</span>
												@endif
											@endforeach
										</div>
									</td>
								</tr>
								<input type="hidden" name="attendances[{{$data['id']}}][{{$serial}}][plans_date_list_id]" value="{{ $data['id'] }}">
								@php $serial++; @endphp
							@endif
                		@endforeach   
					</tbody>
				</table>



        		<!--<table class="table table-bordered w-full text-left">
            		<thead>
						<tr>
							<th>Serial</th>                                                
							<th style="max-width: 200px">Student</th>       
							<th>
								Attendance
							</th>
						</tr>
						<tr>
							<th></th>
							<th></th>
							<th class="att_count_area">
								@foreach($data["AttendanceFeedStatus"] as $feedType)
									@php $buttonDefault = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80 mb-2 mr-1 w-24" @endphp
									
									@switch($feedType->id)
										@case(2)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 w-36" @endphp
											@break
										@case(3)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-success border-success text-slate-900 dark:border-success mb-2 mr-1 w-36" @endphp
											@break
										@case(4)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-warning border-warning text-slate-900 dark:border-warning mb-2 mr-1 w-24" @endphp
											@break
										@case(5)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-warning border-warning text-slate-900 dark:border-warning mb-2 mr-1 w-24" @endphp
											@break
										@case(6)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-pending border-pending text-white dark:border-pending mb-2 mr-1 w-24" @endphp
											@break
										@case(7)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-dark border-dark text-white dark:bg-darkmode-800 dark:border-transparent dark:text-slate-300 [&amp;:hover:not(:disabled)]:dark:dark:bg-darkmode-800/70 mb-2 mr-1 w-24 mb-2 mr-1 w-24" @endphp
											@break
										@case(8)
											@php $button = "transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger mb-2 mr-1 w-24 mb-2 mr-1 w-24" @endphp
											@break
										@default
											@php $button = $buttonDefault @endphp
											@break
									@endswitch
									@if($feedType->tutor_availability==1)
										<span class="{{ $button }}">{{ $feedType->name }}=<span class="{{ $feedType->code }}-val">{{ isset($data["feedCount"][$feedType->id]) ? $data["feedCount"][$feedType->id] : 0 }}</span></span>
									@endif
								@endforeach                           
							</th>
						</tr>                            
            		</thead>
            		<tbody class="send-notofication">
						@php
							$serial = 1
						@endphp
                		@foreach($data["assignStudentList"] as $list)   
							<tr class="gradeA">
								<td width="13%">{{ $serial++ }}</td>
								<td width="28%">
									<div class="text-lg">
										<div class="font-medium whitespace-nowrap">{{ $list->student->registration_no }}</div>
										<div class="text-slate-500 text-xs whitespace-nowrap">{{ $list->student->full_name }} </div>
									</div>    
									<input type="hidden" name="student_id[]" value="{{ $list->student->id }}">
								</td>
								
								<td width="40%" class="attendance-column">
									<div class="mt-3">
										<div class="mt-2 flex flex-col sm:flex-row">
											@foreach($data["AttendanceFeedStatus"] as $feedType)
											@if($feedType->tutor_availability==1)
												<div data-tw-merge class="flex items-center mr-2 ">
													<input id="radio-switch-{{ $feedType->id }}" {{ (isset($data["attendanceFeed"][$list->student->id]) && $data["attendanceFeed"][$list->student->id]==$feedType->id) ? "checked=checked" : ""  }} name="attendance_feed_status_id[]" value="{{ $feedType->id }}" data-tw-merge type="radio" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50"  />
													<label data-tw-merge for="radio-switch-{{ $feedType->id }}" class="cursor-pointer ml-2">{{ $feedType->name }}</label>
												</div>
											@endif
											@endforeach
										</div>
									</div>
								</td>
							</tr>
							<input type="hidden" name="plans_date_list_id[]" value="{{ $data['id'] }}">
                		@endforeach                                     
            		</tbody>
        		</table>-->
      		</div>
			@if($data["attendanceInformation"]->end_time == null || ($data['feed_given'] != 1 && $data['feed_count'] == 0) || (isset(auth()->user()->priv()['edit_attendance']) && auth()->user()->priv()['edit_attendance'] == 1))
			<div class="intro-y flex flex-col sm:flex-row items-center justify-end mt-5">
				<div class="w-full sm:w-auto flex mt-4 sm:mt-0">
					<button type="submit" class="save btn btn-success text-white shadow-md">Save Attendance
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
					<input type="hidden" name="tutor_id" value="{{ $data['tutor_id'] }}" />
				</div>
			</div>
			@endif
    	</div>
	@if($data["attendanceInformation"]->end_time == null || ($data['feed_given'] != 1 && $data['feed_count'] == 0) || (isset(auth()->user()->priv()['edit_attendance']) && auth()->user()->priv()['edit_attendance'] == 1))
	</form>
	@endif

    @include('pages.tutor.attendance.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-attendance-feed.js')
@endsection