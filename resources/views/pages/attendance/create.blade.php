@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Feed Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
          <a href="{{ route('attendance') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Attendance</a>
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
    <form id="attendanceFeed" method="post" >
    <div class="intro-y box p-5 mt-5">
      <div class="overflow-x-auto">
        <table class="table table-bordered w-full text-left">
            <thead>
                <tr>
                    <th>Serial</th>                                                
                    <th>Student</th>       
                    <th>Attendance</th>
                    <th>
                        <div data-tw-merge class="flex items-center mt-2 mt-2"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" id="checkbox-switch-1" value="" />
                            <label data-tw-merge for="checkbox-switch-1" class="cursor-pointer ml-2">Notify By Email</label>
                        </div>
                    </th>
                    <th>
                        <div data-tw-merge class="flex items-center mt-2 mt-2"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" id="checkbox-switch-2" value="" />
                            <label data-tw-merge for="checkbox-switch-2" class="cursor-pointer ml-2">Notify By SMS</label>
                        </div>
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="att_count_area">
                        <span class="btn btn-xs btn-default">Present = <span class="p_val">0</span></span>
                        <span class="btn btn-xs btn-lightblue-2">Online Present = <span class="o_val">0</span></span>
                        <span class="btn btn-xs btn-primary">Left early = <span class="le_val">0</span></span>
                        <span class="btn btn-xs btn-success">Absence = <span class="a_val">1</span></span>
                        <span class="btn btn-xs btn-info">Late = <span class="l_val">0</span></span>
                        <span class="btn btn-xs btn-warning">Excuse = <span class="ex_val">0</span></span>                                
                        <span class="btn btn-xs btn-pending">Medical = <span class="med_val">0</span></span>                                
                        <span class="btn btn-xs btn-danger">Exceptional = <span class="exc_val">0</span></span>                                
                    </th>
                    <th></th>
                    <th></th>
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
                        <div>
                            <div class="font-medium whitespace-nowrap">{{ $list->student->full_name }}</div>
                            <div class="text-slate-500 text-xs whitespace-nowrap">{{ $list->student->registration_no }}</div>
                        </div>    
                        <input type="hidden" name="student_id[]" value="{{ $list->student->id }}">
                    </td>
                    
                    <td width="40%" class="attendance-column">
                        <div class="mt-3">
                            
                            <div class="mt-2 flex flex-col sm:flex-row">
                                @foreach($data["AttendanceFeedStatus"] as $feedType)
                                <div data-tw-merge class="flex items-center mr-2 ">
                                    <input id="radio-switch-{{ $feedType->id }}" name="attendance_feed_status_id[]" value="{{ $feedType->id }}" data-tw-merge type="radio" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50"  />
                                    <label data-tw-merge for="radio-switch-{{ $feedType->id }}" class="cursor-pointer ml-2">{{ $feedType->name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </td>
                    <td><div data-tw-merge class="flex items-center mt-2 mt-2"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" id="checkbox-switch-3" value="" />
                        <label data-tw-merge for="checkbox-switch-3" class="cursor-pointer ml-2">Check Here</label>
                    </div></td>
                    <td><div data-tw-merge class="flex items-center mt-2 mt-2"><input data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;[type=&#039;radio&#039;]]:checked:bg-primary [&amp;[type=&#039;radio&#039;]]:checked:border-primary [&amp;[type=&#039;radio&#039;]]:checked:border-opacity-10 [&amp;[type=&#039;checkbox&#039;]]:checked:bg-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-primary [&amp;[type=&#039;checkbox&#039;]]:checked:border-opacity-10 [&amp;:disabled:not(:checked)]:bg-slate-100 [&amp;:disabled:not(:checked)]:cursor-not-allowed [&amp;:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&amp;:disabled:checked]:opacity-70 [&amp;:disabled:checked]:cursor-not-allowed [&amp;:disabled:checked]:dark:bg-darkmode-800/50" id="checkbox-switch-4" value="" />
                        <label data-tw-merge for="checkbox-switch-4" class="cursor-pointer ml-2">Check Here</label>
                    </div></td>
                </tr>   
                
                <input type="hidden" name="plans_date_list_id[]" value="{{ $list->id }}">
                @endforeach                                     
            </tbody>
        </table>
      </div>
    </div>
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
    @include('pages.attendance.modals')
@endsection

@section('script')
    @vite('resources/js/attendance-feed.js')
@endsection