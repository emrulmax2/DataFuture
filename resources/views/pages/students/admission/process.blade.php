@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $applicant->title->name.' '.$applicant->first_name.' '.$applicant->last_name }}</strong></u></h2>
    </div>
    <!-- BEGIN: Profile Info -->

    @include('pages.students.admission.show-info')
    @include('pages.students.admission.show-menu')
    
    <!-- END: Profile Info -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">My Task</div>
            </div>
            <div class="col-span-6 text-right relative">
                <div class="dropdown" id="processDropdown">
                    <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-4 h-4 mr-2"></i>  Add Task</button>
                    <div class="dropdown-menu w-72">
                        <form method="post" action="#" id="studentProcessListForm">
                            <ul class="dropdown-content">
                                <li><h6 class="dropdown-header">Task List</h6></li>
                                <li><hr class="dropdown-divider mt-0"></li>
                                @if(isset($process->tasks) && !empty($process->tasks))
                                    @foreach($process->tasks as $task)
                                        <li>
                                            <div class="form-check dropdown-item">
                                                <label class="inline-flex items-center cursor-pointer" for="process_task_{{ $task->id }}"><i data-lucide="activity" class="w-4 h-4 mr-2"></i> {{ $task->name }}</label>
                                                <input {{ (in_array($task->id, $existingTask) ? 'checked' : '') }} id="process_task_{{ $task->id }}" name="task_list_ids[]" class="form-check-input task_list_id ml-auto" type="checkbox" value="{{ $task->id }}">
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="flex p-1">
                                        <button type="submit" id="addProcessItemsAdd" class="btn btn-primary py-1 px-2 w-auto">     
                                            Add Items                      
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
                                        <button type="button" id="closeProcessDropdown" class="btn btn-secondary py-1 px-2 ml-auto">Close</button>
                                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                                    </div>
                                </li>
                            </ul>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y pt-2">
            <ul class="nav nav-link-tabs border-b border-slate-200/60" role="tablist">
                <li id="process-1-tab" class="nav-item mr-10 flex" role="presentation">
                    <button class="nav-link font-medium text-slate-500 py-2 px-0 active" data-tw-toggle="pill" data-tw-target="#process-tab-1" type="button" role="tab" aria-controls="process-tab-1" aria-selected="true">
                        In Progress
                    </button>
                </li>
                <li id="process-2-tab" class="nav-item flex" role="presentation">
                    <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" data-tw-target="#process-tab-2" type="button" role="tab" aria-controls="process-tab-2" aria-selected="false">
                        Completed
                    </button>
                </li>
                <li id="process-3-tab" class="nav-item ml-10 flex" role="presentation">
                    <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" data-tw-target="#process-tab-3" type="button" role="tab" aria-controls="process-tab-3" aria-selected="false">
                        Archived
                    </button>
                </li>
            </ul>
            <div class="tab-content mt-5">
                <div id="process-tab-1" class="tab-pane leading-relaxed active" role="tabpanel" aria-labelledby="process-1-tab">
                    @if($applicantPendingTask->count() > 0)
                        @foreach($applicantPendingTask as $task)
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <div class="relative ">
                                        <div class="intro-x relative flex items-center mb-3">
                                            <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden bg-white">
                                                    <i data-lucide="minus-circle" class="text-danger absolute w-full h-full"></i>
                                                </div>
                                            </div>
                                            <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                                <div class="flex items-center">
                                                    <div class="font-medium">
                                                        {{ $task->task->name }}
                                                        @if($task->task_status_id > 0 && isset($task->applicatnTaskStatus->name) && !empty($task->applicatnTaskStatus->name))
                                                            (<u>Outcome: {{ $task->applicatnTaskStatus->name }}</u>)
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-slate-500 ml-auto">{{ date('h:i a', strtotime($task->created_at)) }}</div>
                                                </div>
                                                <div class="text-slate-500">
                                                    @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                                    <div class="mt-1">{{ $task->task->short_description }}</div>
                                                    @endif
                                                    @if(isset($task->documents) && !empty($task->documents))
                                                        <div class="flex mt-2">
                                                            @foreach($task->documents as $tdoc)
                                                                @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                                                    <a target="_blank" class="w-8 h-8 image-fit mr-1 zoom-in" href="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" download>
                                                                        <img alt="{{ $task->task->name }}" class="rounded-md border border-white" src="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}">
                                                                    </a>
                                                                @else 
                                                                    <a target="_blank" class="w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" download>
                                                                        <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <!--<div class="flex mt-2">
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-12.jpg">
                                                        </div>
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-3.jpg">
                                                        </div>
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-12.jpg">
                                                        </div>
                                                    </div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-3 sm:col-span-4">
                                    <div class="flex items-center justify-end assignedUserWrap" id="assignedUserWrap_{{ $task->id }}">
                                        <div class="font-medium text-base mr-5 ml-auto">Assigned To:</div>
                                        @if(isset($task->task->users) && !empty($task->task->users))
                                            @foreach($task->task->users as $userser)
                                                @if($loop->first)
                                                    <div class="flex items-center justify-start">
                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                                            <img class="assignedUserPhoto" alt="Assign To" src="{{ (isset($userser->user->photo_url) && !empty($userser->user->photo_url) ? $userser->user->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="font-medium assignedUserName">{{ $userser->user->name }}</div>
                                                            <div class="text-slate-500 text-xs mt-0.5 assignedUserDesig">
                                                                @if(isset($userser->user->userRole[0]->role->display_name) && !empty($userser->user->userRole[0]->role->display_name))
                                                                    {{ $userser->user->userRole[0]->role->display_name }}
                                                                @else 
                                                                    {{ 'Unknown' }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div> 
                                                @endif
                                            @endforeach
                                        @else 
                                            <div class="ml-0">
                                                <div class="font-medium assignedUserName">Not Found</div>
                                            </div>
                                        @endif
                                        <div class="ml-5">
                                            <div class="dropdown" id="assignedUserDropdown_{{ $task->id }}">
                                                <button class="dropdown-toggle p-1 text-slate-500 rounded-full border border-slate-500" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="chevron-down" class="w-4 h-4"></i></button>
                                                <div class="dropdown-menu w-64">
                                                    <ul class="dropdown-content overflow-y-auto m-h-56">
                                                        @if(isset($task->task->users) && !empty($task->task->users))
                                                            @foreach($task->task->users as $userser)
                                                                <li class="{{ (!$loop->last ? 'mb-2' : '') }}">
                                                                    <div class="flex items-center justify-start">
                                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                                                            <img class="assignedUserPhoto" alt="Assign To" src="{{ (isset($userser->user->photo_url) && !empty($userser->user->photo_url) ? $userser->user->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                                        </div>
                                                                        <div class="ml-4">
                                                                            <div class="font-medium assignedUserName">{{ $userser->user->name }}</div>
                                                                            <div class="text-slate-500 text-xs mt-0.5 assignedUserDesig">
                                                                                @if(isset($userser->user->userRole[0]->role->display_name) && !empty($userser->user->userRole[0]->role->display_name))
                                                                                    {{ $userser->user->userRole[0]->role->display_name }}
                                                                                @else 
                                                                                    {{ 'Unknown' }}
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div> 
                                                                </li>
                                                            @endforeach
                                                        @else 
                                                            <li>
                                                                <div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                                                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No assigned user found!
                                                                </div>
                                                            </li> 
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-3 sm:col-span-4 text-right">
                                    <div class="flex justify-end">
                                        <div class="dropdown">
                                            <button class="dropdown-toggle btn btn-warning text-white" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Update</button>
                                            <div class="dropdown-menu w-64">
                                                <ul class="dropdown-content">
                                                    <li>
                                                        <a href="javascript:void(0);" data-applicanttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item">
                                                            <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Log
                                                        </a>
                                                    </li>
                                                    @if(isset($task->task->status) && $task->task->status == 'Yes')
                                                    <li>
                                                        <a data-applicanttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskOutcomeModal" class="updateTaskOutcome dropdown-item">
                                                            <i data-lucide="award" class="w-4 h-4 mr-2"></i> Update Outcome
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if(isset($task->task->upload) && $task->task->upload == 'Yes')
                                                    <li>
                                                        <a data-applicanttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#uploadTaskDocumentModal" class="uploadTaskDoc dropdown-item">
                                                            <i data-lucide="cloud-lightning" class="w-4 h-4 mr-2"></i> Upload Documents
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if(($task->task->status == 'No' || ($task->task->status == 'Yes' && $task->task_status_id > 0)) && ($task->task->upload == 'No' || ($task->task->upload == 'Yes' && $task->documents->count() > 0)))
                                                    <li>
                                                        <a data-recordid="{{ $task->id }}" href="javascript:void(0);" class="markAsCompleted dropdown-item">
                                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Mark as Complete
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        @if(($task->task->status == 'No' || ($task->task->status == 'Yes' && $task->task_status_id == '')) && ($task->task->upload == 'No' || ($task->task->upload == 'Yes' && $task->documents->count() == 0)))
                                        <button type="button" data-taskid="{{ $task->id }}" class="deleteApplicantTask btn btn-danger ml-2">
                                            <i data-lucide="trash" class="w-5 h-5"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else 
                        <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! There are no pending process found for this applicant.
                        </div>
                    @endif
                </div>
                <div id="process-tab-2" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-2-tab">
                    @if($applicantCompletedTask->count() > 0)
                        @foreach($applicantCompletedTask as $task)
                        <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6 sm:col-span-4">
                                    <div class="relative ">
                                        <div class="intro-x relative flex items-center mb-3">
                                            <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden bg-white">
                                                    <i data-lucide="check-circle" class="text-success absolute w-full h-full"></i>
                                                </div>
                                            </div>
                                            <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                                <div class="flex items-center">
                                                    <div class="font-medium">
                                                        {{ $task->task->name }}
                                                        @if($task->task_status_id > 0 && isset($task->applicatnTaskStatus->name) && !empty($task->applicatnTaskStatus->name))
                                                            (<u>Outcome: {{ $task->applicatnTaskStatus->name }}</u>)
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-slate-500 ml-auto">{{ date('h:i a', strtotime($task->created_at)) }}</div>
                                                </div>
                                                <div class="text-slate-500">
                                                    @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                                    <div class="mt-1">{{ $task->task->short_description }}</div>
                                                    @endif
                                                    @if(isset($task->documents) && !empty($task->documents))
                                                        <div class="flex mt-2">
                                                            @foreach($task->documents as $tdoc)
                                                                @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                                                    <a target="_blank" class="w-8 h-8 image-fit mr-1 zoom-in" href="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" download>
                                                                        <img alt="{{ $task->task->name }}" class="rounded-md border border-white" src="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}">
                                                                    </a>
                                                                @else 
                                                                    <a target="_blank" class="w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="{{ asset('storage/applicants/'.$tdoc->applicant_id.'/'.$tdoc->current_file_name) }}" download>
                                                                        <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <!--<div class="flex mt-2">
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-12.jpg">
                                                        </div>
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-3.jpg">
                                                        </div>
                                                        <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in">
                                                            <img alt="Midone - HTML Admin Template" class="rounded-md border border-white" src="http://127.0.0.1:8000/build/assets/images/preview-12.jpg">
                                                        </div>
                                                    </div>-->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-3 sm:col-span-4">
                                    <div class="flex items-center justify-end assignedUserWrap" id="assignedUserWrap_{{ $task->id }}">
                                        <div class="font-medium text-base mr-5 ml-auto">Assigned To:</div>
                                        @if(isset($task->task->users) && !empty($task->task->users))
                                            @foreach($task->task->users as $userser)
                                                @if($loop->first)
                                                    <div class="flex items-center justify-start">
                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                                            <img class="assignedUserPhoto" alt="Assign To" src="{{ (isset($userser->user->photo_url) && !empty($userser->user->photo_url) ? $userser->user->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="font-medium assignedUserName">{{ $userser->user->name }}</div>
                                                            <div class="text-slate-500 text-xs mt-0.5 assignedUserDesig">
                                                                @if(isset($userser->user->userRole[0]->role->display_name) && !empty($userser->user->userRole[0]->role->display_name))
                                                                    {{ $userser->user->userRole[0]->role->display_name }}
                                                                @else 
                                                                    {{ 'Unknown' }}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div> 
                                                @endif
                                            @endforeach
                                        @else 
                                            <div class="ml-0">
                                                <div class="font-medium assignedUserName">Not Found</div>
                                            </div>
                                        @endif
                                        <div class="ml-5">
                                            <div class="dropdown" id="assignedUserDropdown_{{ $task->id }}">
                                                <button class="dropdown-toggle p-1 text-slate-500 rounded-full border border-slate-500" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="chevron-down" class="w-4 h-4"></i></button>
                                                <div class="dropdown-menu w-64">
                                                    <ul class="dropdown-content overflow-y-auto m-h-56">
                                                        @if(isset($task->task->users) && !empty($task->task->users))
                                                            @foreach($task->task->users as $userser)
                                                                <li class="{{ (!$loop->last ? 'mb-2' : '') }}">
                                                                    <div class="flex items-center justify-start">
                                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                                                            <img class="assignedUserPhoto" alt="Assign To" src="{{ (isset($userser->user->photo_url) && !empty($userser->user->photo_url) ? $userser->user->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                                        </div>
                                                                        <div class="ml-4">
                                                                            <div class="font-medium assignedUserName">{{ $userser->user->name }}</div>
                                                                            <div class="text-slate-500 text-xs mt-0.5 assignedUserDesig">
                                                                                @if(isset($userser->user->userRole[0]->role->display_name) && !empty($userser->user->userRole[0]->role->display_name))
                                                                                    {{ $userser->user->userRole[0]->role->display_name }}
                                                                                @else 
                                                                                    {{ 'Unknown' }}
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div> 
                                                                </li>
                                                            @endforeach
                                                        @else 
                                                            <li>
                                                                <div class="alert alert-danger-soft show flex items-start mb-2" role="alert">
                                                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> No assigned user found!
                                                                </div>
                                                            </li> 
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-3 sm:col-span-4 text-right">
                                    <div class="flex justify-end">
                                        <div class="dropdown">
                                            <button class="dropdown-toggle btn btn-success text-white" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Actions</button>
                                            <div class="dropdown-menu w-64">
                                                <ul class="dropdown-content">
                                                    <li>
                                                        <a href="javascript:void(0);" data-applicanttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item">
                                                            <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Log
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a data-recordid="{{ $task->id }}" href="javascript:void(0);" class="markAsPending dropdown-item">
                                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Mark as Pending
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else 
                        <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! There are no completed process found for this applicant.
                        </div>
                    @endif
                </div>
                <div id="process-tab-3" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-3-tab">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="processTaskArchiveListTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <!-- BEGIN: View Log Modal -->
    <div id="viewTaskLogModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Task Change Log</h2>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="processTaskLogTable" data-applicanttaskid="0" class="mt-0 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-0">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Log Modal -->
    
    <!-- BEGIN: Update Outcome Modal -->
    <div id="updateTaskOutcomeModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="updateTaskOutcomeForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Outcome</h2>
                    </div>
                    <div class="modal-body">
                         
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateOutcomeBtn" class="btn btn-primary w-auto">
                            Update
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
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="applicant_task_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Outcome Modal -->

    <!-- BEGIN: Import Modal -->
    <div id="uploadTaskDocumentModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('admission.upload.task.documents') }}" class="dropzone" id="uploadTaskDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="applicant_task_id" value="0"/>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="uploadProcessDoc" class="btn btn-primary w-auto">     
                        Upload                      
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
                </div>
            </div>
        </div>
    </div>
    <!-- END: Import Modal -->

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

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

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
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/admission-process.js')
@endsection