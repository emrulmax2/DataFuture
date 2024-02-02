@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
</div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Daily Sales -->
        @php $termstart=0 @endphp
        @foreach($dataSet as $termId =>$dataStartPoint)
        @php $termstart++ @endphp
        <div class="intro-y box col-span-12 p-5 mt-5">
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
                                <a href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file" class="w-4 h-4 mr-2"></i> Print
                                </a>
                            </li>
                            @if($termstart==1)
                            <li>
                                <a href="{{ route('student.attendance.edit',$student->id) }}" class="dropdown-item">
                                    <i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
                <button class="btn btn-outline-secondary hidden sm:flex">
                    <i data-lucide="file" class="w-4 h-4 mr-2"></i> Print Now
                </button>
                @if($termstart==1)
                <a href="{{ route('student.attendance.edit',$student->id) }}" class="btn btn-primary hidden sm:flex ml-2">
                    <i data-lucide="pencil" class="w-4 h-4 mr-2"></i> Edit
                </a>
                @endif
            </div>
            @foreach($dataStartPoint as $moduleDetails => $data)
            
            <div class="p-5">

                <div class="relative flex items-center mb-5">
                    <div id="tablepoint-{{ $termId }}" class="tablepoint-toggle flex-none image-fit table-collapsed cursor-pointer">
                        <i data-lucide="minus" class="plusminus w-6 h-6 mr-2"></i>
                            <i data-lucide="plus" class="plusminus w-6 h-6 mr-2 hidden"></i>
                    </div>
                    @php
                        $start_time = date("Y-m-d ".$planDetails[$termId][$moduleDetails]->start_time);
                        $start_time = date('h:i A', strtotime($start_time));
                        
                        $end_time = date("Y-m-d ".$planDetails[$termId][$moduleDetails]->end_time);
                        $end_time = date('h:i A', strtotime($end_time));  
                        if(!isset($planDetails[$termId][$moduleDetails]->tutor->employee))
                            dd($planDetails[$termId][$moduleDetails]->tutor);
                    @endphp
                    <div class="ml-4 mr-auto">
                        <a href="" class="font-medium flex">{{ $moduleDetails }} <span class="text-slate-500 inline-flex" ><i data-lucide="clock" class="w-4 h-4 ml-2 mr-1 " style="margin-top:2px"></i> {{  $start_time }} - {{  $end_time }}   </span></a>
                        <div class="text-slate-500 mr-5 sm:mr-5 inline-flex mt-1"><i data-lucide="user" class="w-4 h-4 mr-1"></i> {{ $planDetails[$termId][$moduleDetails]->tutor->employee->full_name }}</div>
                    </div>
                    <div class="font-medium dark:text-slate-500 bg-{{ ($avarageDetails[$termId][$moduleDetails]>79)? "success" : "warning" }}/20 text-{{ ($avarageDetails[$termId][$moduleDetails]>79)? "success" : "warning" }} rounded px-2 mt-1.5">{{ $avarageDetails[$termId][$moduleDetails] }}%</div>
                    <div class="flex-none"></div>
                </div>
                
                
                <div id="tabledata{{ $planDetails[$termId][$moduleDetails]->id }}" class="tabledataset overflow-x-auto p-5 pt-0">
                    <table data-tw-merge class="w-full text-left">
                        <thead data-tw-merge class="">
                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
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

                            @foreach($data as $planDateList)
                            
                                @if($planDateList["attendance"]!=null)

                                @php
                                    // $start_time = date("Y-m-d ".$planDateList["attendance_information"]->start_time);
                                    // $start_time = date('h:i A', strtotime($start_time));
                                    
                                    // $end_time = date("Y-m-d ".$planDateList["attendance_information"]->end_time);
                                    // $end_time = date('h:i A', strtotime($end_time));  
                                    
                                @endphp
                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                        {{ date('d F, Y',strtotime($planDateList["date"]))  }}
                                    </td>
                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                        {{ $start_time }} - {{ $end_time  }}
                                    </td>
                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                        {{ $planDateList["attendance_information"]->tutor->employee->full_name }}
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
                        </tbody>
                        <tfoot>
                            <tr class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                <th colspan="3" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">Total</th>
                                <th colspan="4" data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">{{ $totalFeedList[$termId][$moduleDetails] }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    
    
    <!-- END: Daily Sales -->


@endsection

@section('script')
    <script type="module">
        (function () {
            $(".tablepoint-toggle").on('click', function(e) {
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
        })()
    </script>
@endsection
