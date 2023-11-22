@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Feed Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
          <!-- BEGIN: THIS TUTOR ID SHOULD BE CHANGE TO AUTH ID FOR FINAL FINISHING -->  
          @if($data["attendanceInformation"]->end_time==null)
          <button id="dataclassend"  data-classend="0" data-tw-target="#endClassModal" data-tw-toggle="modal" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-danger border-danger text-white dark:border-danger mb-2 mr-1">End Class</button>
              
          @else
            
          <div id="dataclassend" data-classend="1" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&amp;:hover:not(:disabled)]:bg-success/10 mb-2 mr-1  w-auto  ">Class Ended</div>    
          
          @endif
          <a href="{{ route('tutor-dashboard.show',[$data["tutor_id"]]) }}" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 ">Back to Attendance</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="overflow-x-auto">
            <table data-tw-merge class="table w-full text-left table-bordered">
                
                <tbody>
                    <tr data-tw-merge >
                        <td class="font-medium">Term</td>
                        <td class="border-r">
                            <div class="font-medium whitespace-nowrap">{{ $data["term_name"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["term"] }}</div>
                        </td>
                        <td class="border-r font-medium text-center">Status</td>
                        <td class="font-medium">Schedule Date & Time</td>
                        <td class="border-r">
                            <div class="font-medium whitespace-nowrap">{{ $data["date"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["start_time"] }} - {{ $data["end_time"] }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-medium">Course & Module</td>
                        <td class="border-r">
                            <div class="font-medium whitespace-nowrap">{{ $data["course"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["module"] }}</div>
                        </td>
                        <td class="border-r font-medium  text-center">Started 
                            <div class="font-medium whitespace-nowrap">{{ date('h:i A', strtotime($data["attendanceInformation"]->start_time));  }} TO</div>
                        @if($data["attendanceInformation"]->end_time!=null)
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ date('h:i A', strtotime($data["attendanceInformation"]->end_time));  }}</div>
                        @else 
                            <div class="text-slate-500 text-xs whitespace-nowrap">Continuing..</div>
                        @endif
                        </td>
                        
                        <td class="font-medium ">Room</td>
                        <td>
                            <div class="font-medium whitespace-nowrap">{{ $data["venue"] }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $data["room"] }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-medium">Group</td>
                        <td class="border-r">{{ $data["group"] }}</td>
                        <td rowspan="2" class="border-r text-center">
                            <h4 class="font-medium whitespace-nowrap text-4xl"> 
                                
                                <label id="hours">{{ $data["classTakenTimeHour"] }}</label>:<label id="minutes">{{ $data["classTakenTimeMin"] }}</label>:<label id="seconds">{{ $data["classTakenTimeSeconds"] }}</label>
                            </h4>
                        </td>
                        <td class="font-medium ">Tutor</td>
                        <td>{{ $data["tutor"] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    @if($data["attendanceInformation"]->end_time==null)
    <form id="attendanceFeed" method="post" >
    @endif
    <div class="intro-y box p-5 mt-5">
      <div class="overflow-x-auto">
        <table class="table table-bordered w-full text-left">
            <thead>
                <tr>
                    <th>Serial</th>                                                
                    <th style="max-width: 200px">Student</th>       
                    <th>Attendance</th>
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
        </table>
      </div>
    </div>
    @if($data["attendanceInformation"]->end_time==null)
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto"></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
          <button type="submit" class="save btn btn-success shadow-md mr-2">Save Attendance
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
        </div>
    </div>
    
    </form>
    @endif

    @include('pages.tutor.attendance.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-attendance-feed.js')
@endsection