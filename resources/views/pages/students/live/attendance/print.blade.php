@extends('../layout/' . $layout)

@section('subhead')
    <title></title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    <div class="intro-y flex flex-col md:flex-row items-center mt-1 md:mt-8">
        <div class="flex flex-row justify-center md:justify-normal items-center gap-2 flex-wrap mb-4 md:mb-0 w-full">
            <h2 class="text-lg font-medium text-center md:text-left">Profile of</h2>
            <u><strong class="text-lg">{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u>
        </div>
    
        <div class="md:ml-auto md:w-full flex flex-wrap sm:flex-row gap-2 justify-end">
    
            <button type="button" class="btn btn-success text-white flex-1 sm:flex-none md:w-auto min-w-max">
                {{ $student->status->name }}
            </button>

        </div>
        
        <div class="intro-y box px-5 pt-5 mt-5">
            <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
                <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                        <img alt="{{ $student->full_name.' '.$student->last_name }}" class="rounded-full" src="{{ (isset($student->photo_url) && !empty($student->photo_url) ? $student->photo_url : asset('build/assets/images/avater.png')) }}">
                        
                    </div>
                    
                    @php
                        if($student->course->full_time==1):
                            $day = 'text-slate-900' ;
                        else:
                            $day = 'text-amber-600';
                        endif;
                        $html = '<div class="inline-flex sm:ml-auto">';
                            if(isset($student->multi_agreement_status) && $student->multi_agreement_status > 1):
                                $html .= '<div class="mr-2 inline-flex  intro-x  sm:ml-auto" style="color:#f59e0b"><i data-lucide="alert-octagon" class="w-6 h-6"></i></div>';
                            endif;
                            $html .= (isset($student->flag_html) && !empty($student->flag_html) ? $student->flag_html : '');
                            if($student->due > 1):
                                $html .= '<div class="mr-2 '.($student->due == 2 ? 'text-success' : ($student->due == 3 ? 'text-warning' : 'text-danger')).'"><i data-lucide="badge-pound-sterling" class="w-6 h-6"></i></div>';
                            endif;
                            $html .= '<div class="w-8 h-8 '.$day.' intro-x inline-flex">';
                                if($student->course->full_time==1):
                                    $html .= '<i data-lucide="sunset" class="w-6 h-6"></i>';
                                else:
                                    $html .= '<i data-lucide="sun" class="w-6 h-6"></i>';
                                endif;
                            $html .= '</div>';
                            if($student->other->disability_status==1):
                                $html .= '<div class="inline-flex  intro-x  ml-auto" style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                            endif;
                        $html .= '</div>';
                    @endphp
                    <div class="ml-5">
                        <div class="w-full flex flex-col sm:flex-row truncate sm:whitespace-normal font-medium text-lg">{{ !empty($student->registration_no) ? $student->registration_no : '' }} {!! $html !!} </div>
                        <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $student->title->name.' '.$student->first_name }} <span class="font-black">{{ $student->last_name }}</span></div>
                        <div class="text-slate-500">
                            @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) <span class="bg-danger text-white inline pl-1 pr-1"> @endif
                                {{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '' }} - {{ isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '' }}
                            @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) </span> @endif
                            @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0)
                                <a href="{{ route('student.set.default.course', $student->id) }}" class="inline ml-1 bg-success px-1 text-white">Reset</a>
                            @endif
                        </div>
                        <div class="text-slate-500">{{ isset($student->crel->creation->available->type) ? $student->crel->creation->available->type : '' }}</div>
                    </div>
                </div>
                
                <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                    <div class="font-medium text-left lg:mt-3">Contact Details</div>
                    <div class="flex flex-col justify-center items-start md:items-center lg:items-start mt-4">
                        <div class="truncate sm:whitespace-normal flex items-center">
                            <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> {{ $student->users->email }}
                        </div>
                        <div class="truncate sm:whitespace-normal flex items-center mt-3">
                            <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $student->contact->home }}
                        </div>
                        <div class="truncate sm:whitespace-normal flex items-center mt-3">
                            <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $student->contact->mobile }}
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
                    <div class="font-medium text-left lg:mt-5">Address</div>
                    <div class="flex flex-col justify-center items-start md:items-center lg:items-start mt-4">
                        <div class="truncate sm:whitespace-normal flex items-start">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2" style="padding-top: 3px;"></i> 
                            <span class="">
                                @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                    @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                        <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span> <br/>
                                    @endif
                                    @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                        <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span> <br/>
                                    @endif
                                    @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                        <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                                    @endif
                                    @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                        <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                                    @endif
                                    @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                        <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                                    @endif
                                    @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                        <span class="font-medium">{{ $student->contact->termaddress->country }}</span>
                                    @endif
                                @else 
                                    <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
    <!-- END: Profile Info -->

    <!-- BEGIN: Daily Sales -->
        @php $termstart=0 @endphp
        @foreach($dataSet as $termId =>$dataStartPoint)
            
        @php $termstart++; $planId=1; @endphp
        <div class="intro-y box col-span-12 p-5 mt-5  ">
            <div class="md:flex items-center px-5 py-5 sm:py-3  border-slate-200/60 {{ (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0 ? "bg-red-600" : "bg-teal-600 " ) }} text-slate-100 rounded-tl rounded-tr">
                
                <h2 class="font-medium text-base mr-auto ">{{ $term[$termId]["name"] }} 
                    @if(isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0)
                    <div class="font-medium dark:text-slate-500 text-white rounded px-2 mt-1.5  w-{{ $avarageTotalPercentage[$termId]/5 }} inline-flex ml-2">{{ $avarageTotalPercentage[$termId] }}%</div>
                    
                    @else
                    <div class="font-medium dark:text-slate-500 {{ ($avarageTotalPercentage[$termId]>79)? "bg-teal-900" : "bg-warning" }} {{ ($avarageTotalPercentage[$termId]>79)? "text-white" : "text-white" }} rounded px-2 mt-1.5  w-{{ $avarageTotalPercentage[$termId]/5 }} inline-flex ml-2">{{ $avarageTotalPercentage[$termId] }}%</div>
                    @endif
                    <div class="text-slate-100 sm:mr-5 ml-auto text-sm mt-2">{{ strlen($totalFullSetFeedList[$termId]) > 0 ? "[".$totalFullSetFeedList[$termId]."]" : ""  }} {{ (isset($totalClassFullSet[$termId]) && $totalClassFullSet[$termId]!=0) ? "Total: ".$totalClassFullSet[$termId]. " days class" : "No class found" }} </div>
                </h2>
                <div class="text-slate-100 sm:mr-5 ml-auto">
                    Date From {{ date("d-m-Y",strtotime($term[$termId]["start_date"])) }} To {{ date("d-m-Y",strtotime($term[$termId]["end_date"])) }} 
                    <div class="col-span-12 pt-1">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-12 text-slate-100 font-medium">Last Attendance: {{ isset($lastAttendanceDate[$termId]) && !empty($lastAttendanceDate[$termId] && $lastAttendanceDate[$termId]!="N/A") ?  date("jS F, Y",strtotime($lastAttendanceDate[$termId])) : '---' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full py-3  {{ (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0 ? "border-red-600" : "border-teal-600" ) }} border-2 rounded-b-lg bg-transparent h-full">
                @foreach($dataStartPoint as $planId => $data)
                    @if(isset($planDetails[$termId][$planId]) && !empty($planDetails[$termId][$planId]))
                        
                    <div class="p-5 ">

                        <div class="relative md:flex items-center mb-5">
                            <div id="tablepoint-{{ $termId }}" class="tablepoint-toggle flex-none image-fit table-collapsed cursor-pointer ">
                                <i data-lucide="minus" class="plusminus w-6 h-6 mr-2 hidden"></i>
                                    <i data-lucide="plus" class="plusminus w-6 h-6 mr-2 "></i>
                            </div>
                            @php
                            if(isset($planDetails[$termId][$planId]->start_time) && isset($planDetails[$termId][$planId]->end_time)){

                                $start_time = date("Y-m-d ".$planDetails[$termId][$planId]->start_time);
                                $start_time = date('h:i A', strtotime($start_time));
                                
                                $end_time = date("Y-m-d ".$planDetails[$termId][$planId]->end_time);
                                $end_time = date('h:i A', strtotime($end_time));  
                            } else {
                                $start_time = "N/A";
                                $end_time = "N/A";
                            }
                            @endphp
                            <div class="ml-4 mr-auto toggle-heading">
                                <a href="" class="font-medium flex flex-col md:flex-row gap-2 md:gap-0">{{ $moduleNameList[$planId] }} <span class="text-teal-700 ml-1">[ {{ $planId }} ]</span> <span class="text-slate-500 inline-flex" ><i data-lucide="clock" class="w-4 h-4 ml-2 mr-1 " style="margin-top:2px"></i> {{  $start_time }} - {{  $end_time }}   </span> <span class="rounded cursor-pointer font-medium w-auto border-slate-100 border inline-flex justify-center items-center min-w-10 px-3 py-0.5 ml-2 -mt-1 transition duration-200  shadow-sm  focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&:hover:not(:disabled)]:bg-slate-100 [&:hover:not(:disabled)]:border-slate-100 [&:hover:not(:disabled)]:dark:border-darkmode-300/80 [&:hover:not(:disabled)]:dark:bg-darkmode-300/80">{{ $planDetails[$termId][$planId]->group->name }}</span></a>
                                
                                <div class="text-slate-500 text-xs md:text-md mr-5 sm:mr-5 inline-flex mt-4 md:mt-1">
                                    <i data-lucide="book" class="w-4 h-4 mr-1"></i> {{ $ClassType[$planId] }}  
                                    <i data-lucide="user" class="w-4 h-4 mr-1 ml-2"></i> 
                                    @if($ClassType[$planId]!="Tutorial")
                                        {{ !empty($planDetails[$termId][$planId]->tutor->employee) ? $planDetails[$termId][$planId]->tutor->employee->full_name : "N/A" }}
                                    @else
                                        {{ !empty($planDetails[$termId][$planId]->personalTutor->employee) ? $planDetails[$termId][$planId]->personalTutor->employee->full_name : "N/A" }} 
                                    @endif
                                </div>
                            </div>
                            <div class="font-medium dark:text-slate-500 bg-{{ ($avarageDetails[$termId][$planId]>79)? "success" : "warning" }}/20 text-{{ ($avarageDetails[$termId][$planId]>79)? "success" : "warning" }} rounded px-2 mt-1.5">{{ $avarageDetails[$termId][$planId] }}%</div>
                            <div class="flex-none"></div>
                        </div>
                        
                        
                        <div id="tabledata{{ $planDetails[$termId][$planId]->id }}" class="tabledataset overflow-x-auto p-5 pt-0" style="display: none;">
                            <table data-tw-merge class="w-full text-left">
                                <thead data-tw-merge class="">
                                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            ID
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Date
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Time
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Taken By
                                        </th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Code
                                        </th>
                                        
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($data) && count($data)>0)
                                    @foreach($data as $planDateList)
                                    
                                        @if(isset($planDateList["attendance"]) && $planDateList["attendance"]!=null)

                                        @php
                                            // $start_time = date("Y-m-d ".$planDateList["attendance_information"]->start_time);
                                            // $start_time = date('h:i A', strtotime($start_time));
                                            
                                            // $end_time = date("Y-m-d ".$planDateList["attendance_information"]->end_time);
                                            // $end_time = date('h:i A', strtotime($end_time));  
                                            
                                        @endphp
                                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                            
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t inline-flex w-full">
                                                {{ $planDateList["attendance"]->id }} 
                                                @if(isset($planDateList["prev_plan_id"]))
                                                <!-- BEGIN: Custom Tooltip Toggle -->
                                                <a href="javascript:;" data-theme="light" data-tooltip-content="#custom-content-tooltip" data-trigger="click" class="tooltip intro-x text-slate-500 block ml-2" title="old group"><i data-lucide="info" class="w-4 h-4 ml-1"></i></a>
                                                <!-- END: Custom Tooltip Toggle -->
                                                <!-- BEGIN: Custom Tooltip Content -->
                                                <div class="tooltip-content">
                                                    <div id="custom-content-tooltip" class="relative flex items-center py-1">
                                                        <span class="rounded btn-primary text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center min-w-10 px-3 py-0.5 ml-2 -mt-1">{{ $planDateList["prev_plan_id"]->group->name }}</span>
                                                        <span class="rounded text-slate-500 cursor-pointer font-medium w-auto inline-flex justify-center items-center min-w-10 px-3 py-0.5 ml-2 -mt-1">[ {{ $planDateList["prev_plan_id"]->id }} ]</span>
                                                    </div>
                                                </div>
                                                <!-- END: Custom Tooltip Content -->
                                                @endif
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ date('d F, Y',strtotime($planDateList["date"]))  }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $start_time }} - {{ $end_time  }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ !empty($planDateList["attendance_information"]->tutor->employee) ? $planDateList["attendance_information"]->tutor->employee->full_name : "Tutor Not Found" }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $planDateList["attendance"]->feed->code }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">

                                                {{ $planDateList["attendance"]->feed->name }}
                                        
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <th colspan="3" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">Total</th>
                                        <th colspan="4" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">{{ $totalFeedList[$termId][$planId] }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endforeach
    
    
    <!-- END: Daily Sales -->
     <!-- BEGIN: Edit Personal Details Modal -->
    <div id="stdAtnTermStatusHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Attendance Term Status History</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="stdAtnTermStatusHistoryTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Edit Personal Details Modal -->


@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-attendance-term-status.js')
    <script type="module">
        (function () {
            $(".tablepoint-toggle").on('click', function(e) {
                e.preventDefault();
                let tthis = $(this)
                let currentThis=tthis.children(".plusminus").eq(0);
                console.log(currentThis);
                let nextThis=tthis.children(".plusminus").eq(1);
                if(currentThis.hasClass('hidden') ) {
                    currentThis.removeClass('hidden')
                    nextThis.addClass('hidden')
                }else {
                    nextThis.removeClass('hidden')
                    currentThis.addClass('hidden')
                }

                tthis.parent().siblings('div.tabledataset').slideToggle();

            });
            $(".toggle-heading").on('click', function(e) {
                e.preventDefault();
                let tthis = $(this)
                tthis.siblings("div.tablepoint-toggle").trigger('click')
            })
        })()
    </script>
@endsection
