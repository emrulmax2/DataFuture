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

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">My Task</div>
            </div>
            <div class="col-span-6 text-right relative">
                <div class="dropdown" id="processDropdown">
                    <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-4 h-4 mr-2"></i>  Add Task <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                    <div class="dropdown-menu w-72">
                        <form method="post" action="#" id="studentProcessListForm">
                            <ul class="dropdown-content">
                                <li><h6 class="dropdown-header">Task List</h6></li>
                                <li><hr class="dropdown-divider mt-0"></li>
                                <li class="processAccrodionWrap">
                                    <div id="processListAccordion" class="accordion">
                                    @if(isset($process) && !empty($process))
                                        @foreach($process as $pro)
                                            @php 
                                                $exists = 0;
                                                if(isset($pro->tasks) && !empty($pro->tasks)):
                                                    foreach($pro->tasks as $task):
                                                        $exists += (in_array($task->id, $existingTask) ? 1 : 0);
                                                    endforeach;
                                                endif;
                                            @endphp
                                            <div class="accordion-item">
                                                <div id="faq-accordion-content-{{ $pro->id }}" class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#faq-accordion-collapse-{{ $pro->id }}" aria-expanded="false" aria-controls="faq-accordion-collapse-{{ $pro->id }}">
                                                        @if($exists)    
                                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2 inline-block text-primary"></i>
                                                        @else 
                                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2 inline-block text-danger"></i>
                                                        @endif
                                                        {{ $pro->name }}
                                                    </button>
                                                </div>
                                                <div id="faq-accordion-collapse-{{ $pro->id }}" class="accordion-collapse collapse" aria-labelledby="faq-accordion-content-{{ $pro->id }}" data-tw-parent="#faq-accordion-1">
                                                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                        @if(isset($pro->tasks) && !empty($pro->tasks))
                                                            @foreach($pro->tasks as $task)
                                                                <div class="form-check dropdown-item">
                                                                    <label class="inline-flex items-center cursor-pointer" for="process_task_{{ $task->id }}"><i data-lucide="activity" class="w-4 h-4 mr-2"></i> {{ $task->name }}</label>
                                                                    <input {{ (in_array($task->id, $existingTask) ? 'checked' : '') }} id="process_task_{{ $task->id }}" name="task_list_ids[]" class="form-check-input task_list_id ml-auto" type="checkbox" value="{{ $task->id }}">
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <div class="flex p-1">
                                        <button type="submit" id="addProcessItemsAdd" class="btn btn-primary py-1 px-2 w-auto">     
                                            <i data-lucide="plus-circle" class="w-3 h-3 mr-2"></i> Add Items                      
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                                    </div>
                                </li>
                            </ul>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        @if(!empty($processGroup))
            <div id="studentProcessAccordion" class="accordion">
                @foreach($processGroup as $proGroup)
                    <div class="accordion-item">
                        <div id="studentProcessAccordion-{{ $loop->index }}" class="accordion-header">
                            <button class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentProcessAccordion-collapse-{{ $loop->index }}" aria-expanded="false" aria-controls="studentProcessAccordion-collapse-{{ $loop->index }}">
                                {{ $proGroup['name'] }} 
                                @if($proGroup['pendingTask']->count() > 0)
                                    <span class="py-1 px-4 inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-2 relative">{{ $proGroup['pendingTask']->count() }} Pendings</span>
                                @endif
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="studentProcessAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="studentProcessAccordion-{{ $loop->index }}" data-tw-parent="#studentProcessAccordion">
                            <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                <ul class="nav nav-link-tabs border-b border-slate-200/60" role="tablist">
                                    <li id="process-{{ $loop->index }}-1-tab" class="nav-item mr-10 flex" role="presentation">
                                        <button class="nav-link font-medium text-slate-500 py-2 px-0 active" data-tw-toggle="pill" 
                                            data-tw-target="#process-tab-{{ $loop->index }}-1" type="button" role="tab" aria-controls="process-tab-{{ $loop->index }}-1" 
                                            aria-selected="true">
                                            Pending
                                        </button>
                                    </li>
                                    <li id="process-{{ $loop->index }}-4-tab" class="nav-item  mr-10 flex" role="presentation">
                                        <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" 
                                            data-tw-target="#process-tab-{{ $loop->index }}-4" type="button" role="tab" aria-controls="process-tab-{{ $loop->index }}-4" 
                                            aria-selected="false">
                                            In Progress
                                        </button>
                                    </li>
                                    <li id="process-{{ $loop->index }}-2-tab" class="nav-item flex" role="presentation">
                                        <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" 
                                            data-tw-target="#process-tab-{{ $loop->index }}-2" type="button" role="tab" aria-controls="process-tab-{{ $loop->index }}-2" 
                                            aria-selected="false">
                                            Completed
                                        </button>
                                    </li>
                                    <li id="process-{{ $loop->index }}-3-tab" class="nav-item ml-10 flex" role="presentation">
                                        <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" 
                                            data-tw-target="#process-tab-{{ $loop->index }}-3" type="button" role="tab" aria-controls="process-tab-{{ $loop->index }}-3" 
                                            aria-selected="false">
                                            Archived
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content mt-5">
                                    <div id="process-tab-{{ $loop->index }}-1" class="tab-pane leading-relaxed active" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-1-tab">
                                        @if($proGroup['pendingTask']->count() > 0)
                                            @foreach($proGroup['pendingTask'] as $task)
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
                                                                        {{--<div class="text-xs text-slate-500 ml-auto">{{ date('h:i a', strtotime($task->created_at)) }}</div>--}}
                                                                    </div>
                                                                    <div class="text-slate-500">
                                                                        @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                                                        <div class="mt-1">{{ $task->task->short_description }}</div>
                                                                        @endif
                                                                        @if(isset($task->documents) && !empty($task->documents))
                                                                            <div class="flex mt-2">
                                                                                @foreach($task->documents as $tdoc)
                                                                                    @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="image" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @else 
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4">
                                                        <div class="flex items-center justify-end assignedUserWrap" id="assignedUserWrap_{{ $task->id }}">
                                                            <div class="font-medium text-base mr-5 ml-auto">Assigned To:</div>
                                                            @if(isset($task->task->users) && !empty($task->task->users))
                                                                <div class="flex taskUserLoader" data-taskid="{{ $task->task->id }}">
                                                                    @foreach($task->task->users as $usr)
                                                                        @if($loop->index > 2) 
                                                                            @break 
                                                                        @endif
                                                                        <div class="w-10 h-10 image-fit zoom-in {{ ($loop->first ? '' : ' -ml-5') }}">
                                                                            <img alt="{{ (isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee') }}" class="rounded-full" src="{{ (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else 
                                                                <div class="ml-0">
                                                                    <div class="font-medium assignedUserName">Not Found</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4 text-right">
                                                        <div class="flex justify-end">
                                                            <div class="dropdown">
                                                                <button class="dropdown-toggle btn btn-warning text-white" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Update  <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                                                                <div class="dropdown-menu w-64">
                                                                    <ul class="dropdown-content">
                                                                        <li>
                                                                            <a href="javascript:void(0);" data-interview="{{ $task->task->interview == 'Yes' ? 1 : 0 }}" data-studentid="{{ $student->id }}" data-studenttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item">
                                                                                <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Log
                                                                            </a>
                                                                        </li>
                                                                        @if(isset($task->task->status) && $task->task->status == 'Yes')
                                                                        <li>
                                                                            <a data-studenttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskOutcomeModal" class="updateTaskOutcome dropdown-item">
                                                                                <i data-lucide="award" class="w-4 h-4 mr-2"></i> Update Outcome
                                                                            </a>
                                                                        </li>
                                                                        @endif
                                                                        @if(isset($task->task->upload) && $task->task->upload == 'Yes')
                                                                        <li>
                                                                            <a data-studenttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#uploadTaskDocumentModal" class="uploadTaskDoc dropdown-item">
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
                                                            <button type="button" data-taskid="{{ $task->id }}" class="deletestudentTask btn btn-danger ml-2">
                                                                <i data-lucide="Trash2" class="w-5 h-5"></i>
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else 
                                            <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! There are no pending process found for this student.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-4" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-4-tab">
                                        @if($proGroup['inProgressTask']->count() > 0)
                                            @foreach($proGroup['inProgressTask'] as $task)
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
                                                                        {{--<div class="text-xs text-slate-500 ml-auto">{{ date('h:i a', strtotime($task->created_at)) }}</div>--}}
                                                                    </div>
                                                                    <div class="text-slate-500">
                                                                        @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                                                        <div class="mt-1">{{ $task->task->short_description }}</div>
                                                                        @endif
                                                                        @if(isset($task->documents) && !empty($task->documents))
                                                                            <div class="flex mt-2">
                                                                                @foreach($task->documents as $tdoc)
                                                                                    @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="image" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @else 
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4">
                                                        <div class="flex items-center justify-end assignedUserWrap" id="assignedUserWrap_{{ $task->id }}">
                                                            <div class="font-medium text-base mr-5 ml-auto">Assigned To:</div>
                                                            @if(isset($task->task->users) && !empty($task->task->users))
                                                                <div class="flex taskUserLoader" data-taskid="{{ $task->task->id }}">
                                                                    @foreach($task->task->users as $usr)
                                                                        @if($loop->index > 2) 
                                                                            @break 
                                                                        @endif
                                                                        <div class="w-10 h-10 image-fit zoom-in {{ ($loop->first ? '' : ' -ml-5') }}">
                                                                            <img alt="{{ (isset($usr->user->employee->full_name) ? $usr->user->employee->full_name : 'Unknown Employee') }}" class="rounded-full" src="{{ (isset($usr->user->employee->photo_url) && !empty($usr->user->employee->photo_url) ? $usr->user->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else 
                                                                <div class="ml-0">
                                                                    <div class="font-medium assignedUserName">Not Found</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4 text-right">
                                                        <div class="flex justify-end">
                                                            <div class="dropdown">
                                                                <button class="dropdown-toggle btn btn-warning text-white" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Update  <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                                                                <div class="dropdown-menu w-64">
                                                                    <ul class="dropdown-content">
                                                                        <li>
                                                                        <a href="javascript:void(0);" data-interview="{{ $task->task->interview == 'Yes' ? 1 : 0 }}" data-studentid="{{ $student->id }}" data-studenttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item">
                                                                                <i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Log
                                                                            </a>
                                                                        </li>
                                                                        @if(isset($task->task->status) && $task->task->status == 'Yes')
                                                                        <li>
                                                                            <a data-studenttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskOutcomeModal" class="updateTaskOutcome dropdown-item">
                                                                                <i data-lucide="award" class="w-4 h-4 mr-2"></i> Update Outcome
                                                                            </a>
                                                                        </li>
                                                                        @endif
                                                                        @if(isset($task->task->upload) && $task->task->upload == 'Yes')
                                                                        <li>
                                                                            <a data-studenttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#uploadTaskDocumentModal" class="uploadTaskDoc dropdown-item">
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
                                                            <button type="button" data-taskid="{{ $task->id }}" class="deletestudentTask btn btn-danger ml-2">
                                                                <i data-lucide="Trash2" class="w-5 h-5"></i>
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else 
                                            <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! There are no "In Progress" task found for this student.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-2" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-2-tab">
                                        @if($proGroup['completedTask']->count() > 0)
                                            @foreach($proGroup['completedTask'] as $task)
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
                                                                        {{--<div class="text-xs text-slate-500 ml-auto">{{ date('h:i a', strtotime($task->created_at)) }}</div>--}}
                                                                    </div>
                                                                    <div class="text-slate-500">
                                                                        @if(isset($task->task->short_description) && !empty($task->task->short_description))
                                                                        <div class="mt-1">{{ $task->task->short_description }}</div>
                                                                        @endif
                                                                        @if(isset($task->documents) && !empty($task->documents))
                                                                            <div class="flex mt-2">
                                                                                @foreach($task->documents as $tdoc)
                                                                                    @if($tdoc->doc_type == 'jpg' || $tdoc->doc_type == 'jpeg' || $tdoc->doc_type == 'png' || $tdoc->doc_type == 'gif')
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="image" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @else 
                                                                                        @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                                                                                            <a data-id="{{ $tdoc->id }}" class="downloadDoc w-8 h-8 mr-1 zoom-in inline-flex rounded-md btn-primary-soft justify-center items-center" href="javascript:void(0);">
                                                                                                <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                                                                            </a>
                                                                                        @endif
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4">
                                                        <div class="flex items-center justify-end assignedUserWrap completedUserWrap" id="assignedUserWrap_{{ $task->id }}">
                                                            <div class="font-medium text-base mr-5 ml-auto">Completed By:</div>
                                                            @if(isset($task->updatedBy->employee) && !empty($task->updatedBy->employee))
                                                                <div class="flex items-center justify-start">
                                                                    <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                                                        <img class="assignedUserPhoto" alt="Assign To" src="{{ (isset($task->updatedBy->employee->photo_url) && !empty($task->updatedBy->employee->photo_url) ? $task->updatedBy->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                                    </div>
                                                                    <div class="ml-4">
                                                                        <div class="font-medium assignedUserName">{{ (isset($task->updatedBy->employee->full_name) ? $task->updatedBy->employee->full_name : 'Unknown Employee') }}</div>
                                                                        @if(isset($task->updated_at) && !empty($task->updated_at))
                                                                        <div class="text-slate-500 text-xs whitespace-nowrap">{{ date('jS M, Y', strtotime($task->updated_at)) }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @else 
                                                                <div class="ml-0">
                                                                    <div class="font-medium assignedUserName">Not Found</div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-span-3 sm:col-span-4 text-right">
                                                        <div class="flex justify-end">
                                                            <div class="dropdown">
                                                                <button class="dropdown-toggle btn btn-success text-white" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-5 h-5 mr-2"></i> Actions  <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                                                                <div class="dropdown-menu w-64">
                                                                    <ul class="dropdown-content">
                                                                        <li>
                                                                        <a href="javascript:void(0);" data-interview="{{ $task->task->interview == 'Yes' ? 1 : 0 }}" data-studentid="{{ $student->id }}" data-studenttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item">
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
                                                <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! There are no completed process found for this student.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-3" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-3-tab">
                                        <div class="overflow-x-auto scrollbar-hidden">
                                            <div id="processTaskArchiveListTable_{{ $proGroup['id'] }}" data-process="{{ $proGroup['id'] }}" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator processTaskArchiveListTable"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else 
            <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Oops! No task found under this process.
            </div>
        @endif
    </div>

    
    <!-- BEGIN: View Log Modal -->
    <div id="viewTaskLogModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Task Change Log</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="processTaskLogTable" data-interview="0" data-studentid="{{ $student->id }}" data-studenttaskid="0" class="mt-0 table-report table-report--tabulator"></div>
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
    <div id="updateTaskOutcomeModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="updateTaskOutcomeForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Outcome</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="student_task_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Outcome Modal -->

    <!-- BEGIN: Import Modal -->
    <div id="uploadTaskDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.upload.task.documents') }}" class="dropzone" id="uploadTaskDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="student_task_id" value="0"/>
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

    <!-- BEGIN: Task User Modal -->
    <div id="taskUserModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Task Assigned Users</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="taskUserModalLoader text-center flex justify-center">
                        <i data-loading-icon="rings" class="w-20 h-20"></i>
                    </div>
                    <div class="taskUserModalContent" style="display: none;">
                        <table class="table table-report">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">NAME</th>
                                    <th class="whitespace-nowrap">Department</th>
                                    <th class="whitespace-nowrap">Work Type</th>
                                    <th class="whitespace-nowrap">Work No.</th>
                                    <th class="whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Task User Modal -->

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
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-process.js')
@endsection