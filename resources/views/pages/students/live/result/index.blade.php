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
        @if($dataSet)
        @foreach($dataSet as $termId =>$dataStartPoint)
            @php $termstart++ @endphp
            <div class="intro-y box col-span-12 p-5 mt-5">
                <div class="flex items-center px-5 py-5 sm:py-3 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">{{ $term[$termId]["name"] }}</h2>
                    <div class="text-slate-500 sm:mr-5 ml-auto"></div>
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
                            </ul>
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary hidden sm:flex">
                        <i data-lucide="file" class="w-4 h-4 mr-2"></i> Print Now
                    </button>
                </div>
                @foreach($dataStartPoint as $moduleDetails => $data)
                
                <div class="p-5">

                    <div class="relative flex items-center mb-5">
                        <div id="tablepoint-{{ $termId }}" class="tablepoint-toggle flex-none image-fit table-collapsed cursor-pointer">
                            <i data-lucide="minus" class="plusminus w-6 h-6 mr-2 "></i>
                                <i data-lucide="plus" class="plusminus w-6 h-6 mr-2 hidden"></i>
                        </div>
                        <div class="ml-4 mr-auto toggle-heading">
                            <a href="" class="font-medium flex">{{ $moduleDetails }} <span class="text-slate-500 inline-flex" ><i data-lucide="clock" class="w-4 h-4 ml-2 mr-1 " style="margin-top:2px"></i>  </span></a>
                            <div class="text-slate-500 mr-5 sm:mr-5 inline-flex mt-1"><i data-lucide="user" class="w-4 h-4 mr-1"></i> {{ $planDetails[$termId][$moduleDetails]->tutor->employee->full_name }}</div>
                        </div>
                        <div class="flex-none"></div>
                    </div>
                    
                    <div id="tabledata{{ $planDetails[$termId][$moduleDetails]->id }}" class="tabledataset overflow-x-auto p-5 pt-0" style="display: inline-block;">
                        <table data-tw-merge class="w-full text-left">
                            <thead data-tw-merge class="">
                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Id
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Exam Published Date
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Grade
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Assessment
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Attemped
                                    </th>
                                    
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Last Updated By
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data["results"] as $assessmentPlan => $resultSet)
                                    @foreach($resultSet as $key => $result)
                                        @if($key==0)
                                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ $result->id  }}
                                                </td>
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ date('d F, Y',strtotime($result->published_at))  }}
                                                </td>
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ $result->grade->code }} - {{ $result->grade->name }}
                                                </td>
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    {{ $result->assementPlan->courseModuleBase->type->name }} - {{ $result->assementPlan->courseModuleBase->type->code }} ({{$result->assementPlan->id}})
                                                </td>
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                    
                                                    <!-- BEGIN: Custom Tooltip Toggle -->
                                                <a href="javascript:;" data-theme="light" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $result->id  }}" data-trigger="click" class="intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="attempt count">{{ $resultSet->count() }}</a>
                                                <!-- END: Custom Tooltip Toggle -->
                                                <!-- BEGIN: Student Profile Lock Modal -->
                                                <div id="callLockModal{{ $result->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                             <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h2 class="font-medium text-base mr-auto">Attempt List</h2>
                                                                    <a data-tw-dismiss="modal" href="javascript:;">
                                                                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="modal-body ">
                                                                    <table class="w-full text-left table-auto overflow-scroll">
                                                                        <thead data-tw-merge class="">
                                                                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                                                    Exam Published Date
                                                                                </th>
                                                                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                                                    Grade
                                                                                </th>
                                                                                <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                                                    Last Updated By
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($resultSet as $assessmentResult)
            
                                                                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ date('d F, Y',strtotime($assessmentResult->published_at))  }}</td>
                                                                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ $assessmentResult->grade->code }} - {{ $assessmentResult->grade->name }}</td>
                                                                                    <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ isset($assessmentResult->updatedBy) ? $assessmentResult->updatedBy->employee->full_name : $assessmentResult->createdBy->employee->full_name}}</td>
                                                                                </tr>
                                                                                
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                    </div>
                                                </div>
                                                <!-- END: Student Profile Lock Modal -->

                                                </td>
                                                <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">

                                                    {{ isset($result->updatedBy) ? $result->updatedBy->employee->full_name : $result->createdBy->employee->full_name}}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        @endforeach
        @endif
    
    
    <!-- END: Daily Sales -->


@endsection

@section('script')
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
