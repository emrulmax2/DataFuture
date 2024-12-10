@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
 
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->
    @include('pages.students.frontend.dashboard.show-info')
    <!-- END: Profile Info -->  

    <!-- BEGIN: Page Title -->
    <div class="intro-y flex items-center p-5 mt-5 box">
        <h2 class="text-lg font-medium mr-auto">
            {{ $title }}
        </h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0 items-center">
            <h4 id="frequency-distribution" class="text-sm font-medium mr-2 py-5">[  ]</h4>
            <a href="{{ route('students.results.print',$student->id) }}" id="tabulator-print-x" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
            </a>
        </div>
    </div>
    <!-- END: Page Title -->
     
    <!-- BEGIN: Daily Sales -->
    <div class="intro-y box col-span-12 p-5 mt-5">
        <div id="tabledata1" class=" overflow-x-auto p-5 pt-5" >
            <table id="sortable-table" data-tw-merge class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                        <th data-sort="s/n" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between">
                            S/N <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="id" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between">
                            Id <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="attendance_term" data-tw-merge class="cursor-pointer text-center font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Attendance <br/>Term <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="module" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Module <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="module_code" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Code <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="awarding_body" data-tw-merge class="cursor-pointer text-center font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Awarding <br/>Body <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="exam_published_date" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Published Date <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="grade" data-tw-merge class=" text-center cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Grade <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="merit" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Merit <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="attempted" data-tw-merge class="text-center cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Attempted <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        {{-- <th data-sort="last_updated_by" data-tw-merge class="cursor-pointer font-medium px-3 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Updated By <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th> --}}
                    </tr>
                </thead>
                <tbody>
                    @if($dataSet)
                        @php
                            $serial = 1;
                        @endphp
                        @foreach($dataSet as $moduleDetails => $resultSet)
                            @php
                                $currentResult = $resultSet[0];
                            @endphp
                            <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $serial++ }}
                                    </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    
                                    {{ $currentResult->id  }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    @if($currentResult->term_declaration_id == Null)
                                        {{ $currentResult->plan->attenTerm->name }}
                                    @else
                                        {{ $currentResult->term->name }}
                                    @endif
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $currentResult->plan->creations->module_name }} - {{ $currentResult->plan->creations->level->name }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $currentResult->plan->creations->code }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $currentResult->plan->course->body->name }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ date('d F, Y ',strtotime($currentResult->published_at))  }}<br />
                                    {{ date('h:i a',strtotime($currentResult->published_at))  }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 text-center border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $currentResult->grade->code }} 
                                </td>
                                <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    {{ $currentResult->grade->name }}
                                </td>
                                <td data-tw-merge class="px-3 py-3 text-center border-b dark:border-darkmode-300 border-l border-r border-t">
                                        <a href="javascript:;" data-theme="light" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $resultSet[0]->id }}" data-trigger="click" class="intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="attempt count">{{ count($resultSet) }}</a>
                                        
                                </td>
                                {{-- <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                    
                                    {{ isset($currentResult->updatedBy->employee->full_name)  ? $currentResult->updatedBy->employee->full_name : (isset($currentResult->createdBy->employee->full_name) ? $currentResult->createdBy->employee->full_name : $currentResult->createdBy->name) }}
                                </td> --}}
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- END: Daily Sales -->

<!-- BEGIN: Student Profile Lock Modal -->
@if($dataSet)
@foreach($dataSet as $key => $resultSet)
    <div id="callLockModal{{ $resultSet[0]->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Attempt List</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body  overflow-x-auto">
                        <div class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap mb-3">
                            <div class="flex justify-between">
                                <div class="flex">
                                    <div class="mr-2">
                                        <label class="form-label">Module</label>
                                        <div class="text-base font-medium">{{  $resultSet[0]->plan->creations->module_name }}</div>
                                        <div class="font-medium text-slate-600">Level: {{  $resultSet[0]->plan->creations->level->name }}</div>
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead data-tw-merge class="">
                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Term
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Code
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Created At
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Published At
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Grade
                                    </th>
                                    <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resultSet as $result)
                                @php
                                    if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                                            $termData = $result->term->name;
                                        else
                                            $termData = $result->plan->attenTerm->name;
                                    @endphp
                                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                            {{ $termData }} 
                                        </td>
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                            {{ ($result->module_code)? $result->module_code :$result->plan->creations->code }}
                                        </td>
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            {{ date('d F,Y h:i a',strtotime($result->created_at))  }}
                                        </td>
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            {{ date('d F,Y h:i a',strtotime($result->published_at))  }}
                                        </td>
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            {{ $result->grade->code }} 
                                        </td>
                                        <td data-tw-merge class="px-3 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            {{ $result->grade->name }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
@endforeach
@endif
<!-- END: Student Profile Lock Modal -->



@if($dataSet)
    @foreach($dataSet as $key => $resultSet)
    <!-- BEGIN: Edit Modal -->
    <div id="editAttemptModal{{ $resultSet[0]->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" id="editAttemptForm{{ $resultSet[0]->id  }}">
                <div class="modal-content ">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Result Update</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                        
                    </div>
                    <div class="modal-body ">
                        <div role="alert" class="alert hidden relative border rounded-md px-5 py-4 bg-warning border-warning text-slate-900 dark:border-warning mb-2 flex items-center"><i data-tw-merge data-lucide="alert-circle" class="stroke-1.5 w-5 h-5 mr-2 h-6 w-6 mr-2 h-6 w-6"></i>
                            <span class="error-text">Awesome alert with icon</span>
                            <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close" type="button" aria-label="Close" class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-tw-merge data-lucide="x" class="stroke-1.5 w-5 h-5 h-4 w-4 h-4 w-4"></i></button>
                        </div>
                        <div class="flex">
                            <div class="mr-2 mb-5">
                                <label class="form-label">Module</label>
                                <div class="text-base font-medium">{{  $key }}</div>
                                <div class=" font-normal  text-slate-500">Level {{  $resultSet[0]->plan->creations->level->name }}</div>
                            </div>
                        </div>
                        <table id="result-bulk{{ $resultSet[0]->id }}" class="min-w-full divide-y divide-gray-200">
                            <thead data-tw-merge class="">
                                <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                    <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Term
                                    </th>
                                    <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Code
                                    </th>
                                    <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Created At
                                    </th>
                                    <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Published At
                                    </th>
                                    <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Grade
                                    </th>
                                    {{-- <th data-tw-merge class="font-medium px-2 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                        Last Updated By
                                    </th> --}}
                                </tr>
                            </thead>
                            <tbody class="bulk-update">
                                @php $index= 0; @endphp
                                @foreach($resultSet as $result)
                                @php
                                    
                                    if(isset($result->term_declaration_id) && !empty($result->term_declaration_id))
                                        $termData = $result->term_declaration_id;

                                    else
                                        $termData = $result->plan->attenTerm->id;
                                @endphp
                                    <tr data-tw-merge class="items-center justify-between [&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                        <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t relative w-36">
                                            <input type="hidden" name="id[]" value="{{ $result->id }}" />
                                            <input type="hidden" name="plan_id[]" value="{{ $result->plan_id }}" />
                                            <input type="hidden" name="student_id[]" value="{{ $result->student_id }}" />
                                            <input type="hidden" name="created_by[]" value="{{ $result->created_by }}" />
                                            <input type="hidden" name="updated_by[]" value="{{ auth('student')->user()->id }}" />
                                            
                                            <select id="term-data{{ $result->id }}" data-index="{{ $index }}" class="w-full lccTom lcc-tom-select" name="term_declaration_id[]">
                                                <option value="">Please Select</option>
                                                @if(isset($termSet[$result->plan->creations->module_name]))
                                                    @foreach($termSet[$result->plan->creations->module_name] as $trm)
                                                        <option  {{ $termData==$trm->id ? 'selected' : '' }} value="{{ $trm->id }}">{{ $trm->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-term_declaration_id text-danger mt-2" data-index="{{ $index }}"></div>
                                            
                                        </td>
                                        <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t relative">
                                            <input type="text" class="form-control mt-2 sm:mt-0" value="{{ ($result->module_code)? $result->module_code :$result->plan->creations->code }}" placeholder="{{ ($result->module_code) ? $result->module_code :$result->plan->creations->code }}"  name="module_code[]" >
                                        </td>
                                        <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t w-40">
                                            <input id="created" placeholder="{{ date('d-m-Y H:i',strtotime($result->created_at))  }}" autocomplete="off"  class="datepicker_custom form-control w-full placeholder:text-slate-700" value=""  data-single-mode="true">
                                            <input name="created_at[]" data-index="{{ $index }}" type="hidden" value="{{ date('Y-m-d H:i:s',strtotime($result->created_at))  }}">
                                            <div class="acc__input-error error-created_at text-danger mt-2" data-index="{{ $index }}"></div>
                                        </td>
                                        <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t  w-40">
                                            <input id="published"   placeholder="{{ $result->published_at  }}" autocomplete="off" class="datepicker_custom form-control w-full  placeholder:text-slate-700" value=""  data-single-mode="true">
                                            <input name="published_at[]" data-index="{{ $index }}" type="hidden" value="{{ $result->published_at  }}">
                                            <div class="acc__input-error error-published_at text-danger mt-2" data-index="{{ $index }}"></div>
                                        </td>
                                        <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            <select id="grade_id" name="grade_id[]" data-index="{{ $index }}" class="form-control w-full">
                                                <option value="">Please Select</option>
                                                @if(!empty($grades))
                                                    @foreach($grades as $grade)
                                                        <option {{ $result->grade->id == $grade->id  ? 'selected' : "" }}  value="{{ $grade->id }}">{{ $grade->code }} - {{ $grade->name }} </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-grade_id text-danger mt-2" data-index="{{ $index }}"></div>
                                        </td>
                                        {{-- <td data-tw-merge class="px-1 py-1 border-b dark:border-darkmode-300 border-l border-r border-t">
                                            <div class="flex justify-between items-stretch ">
                                                <div class="item updated-name"> 
                                                    {{ isset($result->updatedBy->employee->full_name)  ? $result->updatedBy->employee->full_name : (isset($result->createdBy->employee->full_name) ? $result->createdBy->employee->full_name: $result->createdBy->name) }}
                                                </div>
                                            </div>
                                        </td> --}}
                                    </tr>
                                    @php $index++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                            
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endif

@if($prev_result_count > 0)
<div class="intro-y flex items-center p-5 mt-5 box">
    <h2 class="text-lg font-medium mr-auto">
        Previous Results
    </h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <h4 id="frequency-distribution-old" class="text-sm font-medium mr-auto ">[  ]</h4>
    </div>
</div>
<div class="intro-y box col-span-12 p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulatorFilterForm-AN" class="xl:flex sm:mr-auto" >
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                <input id="query-AN" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                <select id="status-AN" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                    <option selected value="1">Active</option>
                    <option value="2">Archived</option>
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go-AN" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                <button id="tabulator-html-filter-reset-AN" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
            </div>
        </form>
        <div class="flex mt-5 sm:mt-0">
            <button id="tabulator-print-AN" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
            </button>
            <div class="dropdown w-1/2 sm:w-auto">
                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                </button>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li>
                            <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                            </a>
                        </li>
                        <li>
                            <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
   
    <div class="overflow-x-auto scrollbar-hidden">
        <div id="studentNotesListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
    </div>
</div>
@endif

@endsection

@section('script')

        @vite('resources/js/student-results-frontend.js')
@endsection
