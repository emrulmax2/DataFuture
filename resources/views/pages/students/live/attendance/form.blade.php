@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Daily Sales -->
    
    <form id="attendance-update_all" method="post">
        @foreach($dataSet as $termId =>$dataStartPoint)
        <div id="attendance-editAll" class="intro-y box col-span-12 p-5 mt-5">
            <div class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">{{ $term[$termId]["name"] }} <div class="font-medium dark:text-slate-500 bg-{{ ($avarageTotalPercentage[$termId]>79)? "success" : "warning" }}/20 text-{{ ($avarageTotalPercentage[$termId]>79)? "success" : "warning" }} rounded px-2 mt-1.5  w-{{ $avarageTotalPercentage[$termId]/5 }} inline-flex ml-2">{{ $avarageTotalPercentage[$termId] }}%</div>
                    <div class="text-slate-500 sm:mr-5 ml-auto text-sm mt-2">[ {{ $totalFullSetFeedList[$termId] }} ] Total: {{ $totalClassFullSet[$termId] }} days class</div>
                </h2>
                <div class="text-slate-500 sm:mr-5 ml-auto">Date From {{ date("d-m-Y",strtotime($term[$termId]["start_date"])) }} To {{ date("d-m-Y",strtotime($term[$termId]["end_date"])) }} </div>
                <div class="dropdown ml-auto sm:hidden">
                    <a class="dropdown-toggle w-5 h-5 block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="more-horizontal" class="w-5 h-5 text-slate-500"></i>
                    </a>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a href="{{ route('student.attendance',$student->id) }}" class="dropdown-item">
                                    <i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Back To Attendance View
                                </a>
                            </li>
                            <li>
                                <button type="submit" class="dropdown-item update-all">
                                    <i data-lucide="save-all" class="w-4 h-4 mr-2"></i> Update
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <a href="{{ route('student.attendance',$student->id) }}" class="btn btn-outline-primary hidden sm:flex ml-2">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> back to view attendance
                </a>
                <button type="submit" class="btn btn-outline-success text-success hidden sm:flex ml-2 update-all">
                    <i data-lucide="save-all" class="w-4 h-4 mr-2"></i> Update <i  data-loading-icon="oval" class="load-update w-4 h-4 ml-2 hidden"></i>
                </button>
            </div>
                @foreach($dataStartPoint as $planId => $data)
                    <div class="p-5">

                        <div class="relative flex items-center mb-5">
                            <div id="tablepoint-{{ $termId }}" class="tablepoint-toggle flex-none image-fit table-collapsed cursor-pointer">
                                <i data-lucide="minus" class="plusminus w-6 h-6 mr-2"></i>
                                <i data-lucide="plus" class="plusminus w-6 h-6 mr-2 hidden"></i>
                            </div>
                            @php
                                
                                $start_time = date("Y-m-d ".$planDetails[$termId][$planId]->start_time);
                                $start_time = date('h:i A', strtotime($start_time));
                                
                                $end_time = date("Y-m-d ".$planDetails[$termId][$planId]->end_time);
                                $end_time = date('h:i A', strtotime($end_time));  
                            @endphp
                            <div class="ml-4 mr-auto toggle-heading">
                                <a href="" class="font-medium flex">{{ $moduleNameList[$planId] }} <span class="text-slate-500 inline-flex" ><i data-lucide="clock" class="w-4 h-4 ml-2 mr-1 " style="margin-top:2px"></i> {{  $start_time }} - {{  $end_time }}   </span></a>
                                <div class="text-slate-500 mr-5 sm:mr-5 inline-flex mt-1">
                                    <i data-lucide="user" class="w-4 h-4 mr-1"></i> 
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
                        
                        
                        <div id="tabledata{{ $planDetails[$termId][$planId]->id }}" class="tabledataset overflow-x-auto p-5 pt-0">
                            <table data-tw-merge class="w-full text-left">
                                <thead data-tw-merge class="">
                                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Date
                                        </th>
                                        @foreach($attendanceFeedStatus as $status)
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            {{  $status->code }}
                                        </th>
                                        @endforeach
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($data as $planDateList)
                                    
                                        @if($planDateList["attendance"]!=null)
                                            @php $iCountColSpan = 0; @endphp
                                           
                                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                        {{ date('d F, Y',strtotime($planDateList["date"]))  }}
                                                    </td>
                                                    <input name="id[]" value="{{ $planDateList["attendance"]->id }}" type="hidden" />
                                                    @foreach($attendanceFeedStatus as $status)
                                                        @php $iCountColSpan++; @endphp    
                                                        @if($planDateList["attendance"]->feed->code == $status->code)
                                                        
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t {{ ($planDateList["attendance"]->feed->attendance_count ? "text-emerald-600" : "text-red-600") }}  ">
                                                                {{ $planDateList["attendance"]->feed->code }} - {{ $planDateList["attendance"]->feed->name }}
                                                            </td>
                                                        @else
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                                <input data-tw-merge  id="radio-switch-{{ $planDateList["attendance"]->id}}{{ $status->id }}" data-attendanceId="{{ $planDateList["attendance"]->id}}" name="attendance_feed[{{ $planDateList["attendance"]->id}}]" value="{{ $status->id }}"  type="radio" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"/>
                                                                <label data-tw-merge for="radio-switch-{{ $planDateList["attendance"]->id}}{{ $status->id }}" class="cursor-pointer ml-2">{{ $status->name }}</label>
                                                                
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                    <td data-tw-merge class="px-5 py-3 text-danger dark:border-darkmode-300  border-r border-b">
                                                        <span data-tw-target="#confirmModal" data-tw-toggle="modal" data-id={{ $planDateList["attendance"]->id}} class="delete_btn inline-flex cursor-pointer"><i data-lucide="trash-2" class="w-4 h-4 mr-1"></i>Delete</span>
                                                    </td>
                                                </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <th colspan="1" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">Total</th>
                                        <th colspan="{{ $iCountColSpan }}" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">{{ $totalFeedList[$termId][$planId] }}</th>
                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">Total</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
        </div>
        @endforeach

    </form>
    <!-- END: Daily Sales -->

    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 errorModalTitle"></div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    <!-- END: Error Modal Content -->


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

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

@endsection

@section('script')
    @vite('resources/js/attendance-studentstaff.js')
@endsection