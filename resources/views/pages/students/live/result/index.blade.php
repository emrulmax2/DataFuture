@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
 
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Page Title -->
    <div class="intro-y flex items-center p-5 mt-5 box">
        <h2 class="text-lg font-medium mr-auto">
            {{ $title }}
        </h2>
    </div>
    <!-- END: Page Title -->

    <!-- BEGIN: Daily Sales -->
    <div class="intro-y box col-span-12 p-5 mt-5">
        <div id="tabledata1" class=" overflow-x-auto p-5 pt-5" >
            <table id="sortable-table" data-tw-merge class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                        <th data-sort="id" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between">
                            Id <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="attendance_term" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Attendance Term <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="module" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Module <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="awarding_body" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Awarding Body <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="module_code" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Module Code <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="exam_published_date" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Exam Published Date <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="grade" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Grade <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="merit" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Merit <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="attempted" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Attempted <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="last_updated_by" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between ">
                            Last Updated By <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                        <th data-sort="action" data-tw-merge class="cursor-pointer font-medium px-5 py-3 border-b-2  dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap items-center justify-between w-80">
                            Action <i data-lucide="arrow-up-down" class="w-4 h-4 ml-2 inline-flex"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    
                    @if($dataSet)
                    @foreach($dataSet as $termId =>$dataStartPoint)
                        @foreach($dataStartPoint as $moduleDetails => $data)
                            @foreach($data["results"] as $assessmentPlan => $resultSet)
                            @if($resultSet->isNotEmpty())
                                @foreach($resultSet as $key => $result)
                                
                                    @if($key==0)
                                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $result->id  }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $term[$termId]["name"] }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $data['module_name'] }} - {{ $data['level'] }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $data['awardingBody'] }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $data['code'] }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ date('d F, Y',strtotime($result->published_at))  }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $result->grade->code }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                {{ $result->grade->name }}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                 <a href="javascript:;" data-theme="light" data-tw-toggle="modal" data-tw-target="#callLockModal{{ $result->id  }}" data-trigger="click" class="intro-x text-slate-500 block mt-2 text-xs sm:text-sm" title="attempt count">{{ $resultSet->count() }}</a>
                                                 
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">

                                                {{ isset($result->updatedBy) ? $result->updatedBy->employee->full_name : $result->createdBy->employee->full_name}}
                                            </td>
                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                
                                                <button class="mr-3 items-center inline-flex add_btn" type="button" data-tw-toggle="modal" data-tw-target="#addAttemptModal" data-plan="{{ $planDetails[$termId][$moduleDetails]->id }}" data-assessmentPlan="{{  $assessmentPlan }}">
                                                    <i data-lucide="plus-square" class="w-4 h-4 mr-1"></i>
                                                    Add New
                                                </button>
                                                <button class="mr-3 items-center inline-flex edit_btn" type="button" data-tw-toggle="modal" data-tw-target="#editAttemptModal" data-publishTime={{ date('h:m',strtotime($result->published_at))  }} data-publishDate={{ date('d-m-Y',strtotime($result->published_at))  }} data-grade="{{ $result->grade->id }}" data-id="{{ $result->id  }}">
                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                                    Edit
                                                </button>
                                                <button class="items-center text-danger inline-flex delete_btn" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal" data-id="{{ $result->id  }}">
                                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @else
                                <tr class="text-center">
                                    <td colspan="7" >No data available</td>
                                </tr>
                            @endif
                            @endforeach
                        @endforeach
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- END: Daily Sales -->
<!-- BEGIN: Student Profile Lock Modal -->
@if($dataSet)
    @foreach($dataSet as $termId =>$dataStartPoint)
        @foreach($dataStartPoint as $moduleDetails => $data)
            @foreach($data["results"] as $assessmentPlan => $resultSet)
                @if($resultSet->isNotEmpty())
                    @foreach($resultSet as $key => $result)
                        <div id="callLockModal{{ $result->id  }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
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
                                                        
                                                        <th data-tw-merge class="font-medium px-5 py-3 border-b-2 dark:border-darkmode-300 border-l border-r border-t whitespace-nowrap">
                                                            Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($resultSet as $assessmentResult)

                                                        <tr data-tw-merge class="[&:hover_td]:bg-slate-100 [&:hover_td]:dark:bg-darkmode-300 [&:hover_td]:dark:bg-opacity-50">
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ date('d F, Y',strtotime($assessmentResult->published_at))  }}</td>
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ $assessmentResult->grade->code }} - {{ $assessmentResult->grade->name }}</td>
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">{{ isset($assessmentResult->updatedBy) ? $assessmentResult->updatedBy->employee->full_name : $assessmentResult->createdBy->employee->full_name}}</td>
                                                            <td data-tw-merge class="px-5 py-3 border-b dark:border-darkmode-300 border-l border-r border-t">
                                                                <button class="mr-3 items-center inline-flex edit_btn" type="button" data-tw-toggle="modal" data-tw-target="#editAttemptModal" data-publishTime={{ date('h:m',strtotime($assessmentResult->published_at))  }} data-publishDate={{ date('d-m-Y',strtotime($assessmentResult->published_at))  }} data-grade="{{ $assessmentResult->grade->id }}" data-id="{{ $assessmentResult->id  }}">
                                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                                                    Edit
                                                                </button>
                                                                <button class="items-center text-danger inline-flex delete_btn" data-tw-toggle="modal" data-tw-target="#delete-confirmation-modal" data-id="{{ $assessmentResult->id  }}">
                                                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                                                    Delete
                                                                </button>
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
            @endforeach
        @endforeach
    @endforeach
@endif
<!-- END: Student Profile Lock Modal -->

<!-- BEGIN: Add Modal -->
<div id="addAttemptModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="addAttemptForm">

            <input type="hidden" name="assessment_plan_id" value="" />
            <input type="hidden" name="plan_id" value="" />
            <input type="hidden" name="student_id" value="{{ $student->id }}" />
            <input type="hidden" name="created_by" value="{{ Auth::id() }}" />
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add New Submission</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <div>
                            <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
                            <select id="grade_id" name="grade_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($grades))
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }} - {{ $grade->code }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-process_list_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div>
                            <label class="form-label" for="published_at">Publish Date</label>
                            <input id="published_at" placeholder="DD-MM-YYYY" class="datepicker form-control w-full" name="published_at" data-single-mode="true">
                        </div>
                    </div>
                    <div class="mt-3">
                        <div>
                            <label class="form-label" for="published_time">Publish Time</label>
                            <input id="published_time" autocomplete="off" placeholder="HH:MM" class="timeMask form-control w-full" name="published_time" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="save" class="btn btn-primary w-auto">
                        Add Now <i class="w-4 h-4 ml-2 text-white hidden" data-loading-icon="oval" ></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->
<!-- BEGIN: Edit Modal -->
<div id="editAttemptModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editAttemptForm">
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <div>
                            <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
                            <select id="grade_id" name="grade_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($grades))
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name }} - {{ $grade->code }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-process_list_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div>
                            <label class="form-label" for="published_at">Publish Date</label>
                            <input id="published_at" placeholder="DD-MM-YYYY" class="datepicker form-control w-full" name="published_at" data-single-mode="true">
                        </div>
                    </div>
                    <div class="mt-3">
                        <div>
                            <label class="form-label" for="published_time">Publish Time</label>
                            <input id="published_time" autocomplete="off" placeholder="HH:MM" class="timeMask form-control w-full" name="published_time" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="update" class="btn btn-primary w-auto">
                        Update <i class="w-4 h-4 ml-2 text-white hidden" data-loading-icon="oval" ></i>
                    </button>
                    <input type="hidden" name="id" value="" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- BEGIN: Delete Confirmation Modal -->
<div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5">Are you sure?</div>
                    <div class="text-slate-500 mt-2">Do you really want to delete these records? <br>This process cannot be undone.</div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                    <button type="button" data-id="0" data-action="DELETE" class="agreeWith btn btn-danger w-24">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
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
                    <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')

        @vite('resources/js/student-results.js')
@endsection
