@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->

    @include('pages.students.admission.show-info')
    
    <!-- END: Profile Info -->
    <div class="adm-process-page">
    <div class="adm-section adm-section--row adm-process-toolbar">
        <div class="adm-section__head" style="margin-bottom:0;">
            <div class="adm-section__title">My Task</div>
            <div class="adm-tabletools__actions">
                <div class="dropdown adm-process-taskmenu" id="processDropdown" data-tw-placement="bottom-end">
                    <button type="button" class="dropdown-toggle adm-btn adm-btn--primary adm-process-taskmenu__trigger" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-4 h-4"></i>Add Task<i data-lucide="chevron-down" class="w-3 h-3"></i></button>
                    <div class="dropdown-menu w-72 adm-process-taskmenu__menu">
                        <form method="post" action="#" id="studentProcessListForm" class="adm-process-taskmenu__form">
                            <ul class="dropdown-content adm-process-taskmenu__panel">
                                <li><h6 class="dropdown-header adm-process-taskmenu__head">Task List</h6></li>
                                <li><hr class="dropdown-divider mt-0 adm-process-taskmenu__rule"></li>
                                <li class="processAccrodionWrap adm-process-taskmenu__body">
                                    @if(isset($process) && !empty($process))
                                        <div id="processListAccordion" class="accordion adm-process-taskmenu__accordion">
                                            @foreach($process as $pro)
                                                @php
                                                    $exists = 0;
                                                    if(isset($pro->tasks) && !empty($pro->tasks)):
                                                        foreach($pro->tasks as $task):
                                                            $exists += (in_array($task->id, $existingTask) ? 1 : 0);
                                                        endforeach;
                                                    endif;
                                                    $processListOpen = $loop->first;
                                                    $processCollapseId = 'process-list-collapse-'.$pro->id;
                                                @endphp
                                                <div class="accordion-item adm-process-taskmenu__group">
                                                    <div id="process-list-heading-{{ $pro->id }}" class="accordion-header">
                                                        <button class="accordion-button adm-process-taskmenu__group-head {{ $processListOpen ? '' : 'collapsed' }}" type="button" data-tw-toggle="collapse" data-tw-target="#{{ $processCollapseId }}" aria-expanded="{{ $processListOpen ? 'true' : 'false' }}" aria-controls="{{ $processCollapseId }}">
                                                            <i data-lucide="{{ $exists ? 'check-circle' : 'circle' }}"></i>
                                                            <span>{{ $pro->name }}</span>
                                                            <i data-lucide="chevron-down" class="adm-process-taskmenu__group-caret"></i>
                                                        </button>
                                                    </div>
                                                    <div id="{{ $processCollapseId }}" class="accordion-collapse collapse {{ $processListOpen ? 'show' : '' }}" aria-labelledby="process-list-heading-{{ $pro->id }}" data-tw-parent="#processListAccordion">
                                                        @if(isset($pro->tasks) && !empty($pro->tasks))
                                                            <div class="accordion-body adm-process-taskmenu__rows">
                                                                @foreach($pro->tasks as $task)
                                                                    @php
                                                                        $taskInputId = 'process_task_'.$pro->id.'_'.$task->id;
                                                                    @endphp
                                                                    <label class="adm-process-taskmenu__row {{ in_array($task->id, $existingTask) ? 'is-checked' : '' }}" for="{{ $taskInputId }}" data-adm-task-row>
                                                                        <span class="adm-process-taskmenu__row-title">
                                                                            <i data-lucide="activity"></i>
                                                                            <span>{{ $task->name }}</span>
                                                                        </span>
                                                                        <input {{ (in_array($task->id, $existingTask) ? 'checked' : '') }} id="{{ $taskInputId }}" name="task_list_ids[]" class="task_list_id adm-process-taskmenu__input" type="checkbox" value="{{ $task->id }}" style="display:none !important;">
                                                                        <span class="adm-process-taskmenu__check" aria-hidden="true"></span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                                <li><hr class="dropdown-divider adm-process-taskmenu__rule"></li>
                                <li>
                                    <div class="adm-process-taskmenu__foot">
                                        <button type="submit" id="addProcessItemsAdd" class="btn btn-primary py-1 px-2 w-auto">
                                            <i data-lucide="plus-circle" class="adm-btn-icon"></i> Add Items
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 theLoader adm-btn-loader">
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
                                        <button type="button" id="closeProcessDropdown" class="btn btn-secondary py-1 px-2 ml-auto">
                                            <i data-lucide="x" class="adm-btn-icon"></i> Close
                                        </button>
                                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                                    </div>
                                </li>
                            </ul>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="adm-process-list">
        @if(!empty($processGroup))
            <div id="studentProcessAccordion" class="accordion">
                @foreach($processGroup as $proGroup)
                    <div class="accordion-item">
                        <div id="studentProcessAccordion-{{ $loop->index }}" class="accordion-header">
                            <button class="processListAccordionBtn accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentProcessAccordion-collapse-{{ $loop->index }}" aria-expanded="false" aria-controls="studentProcessAccordion-collapse-{{ $loop->index }}">
                                <span class="adm-process-acc-icon"><i data-lucide="clipboard-check"></i></span>
                                <span class="adm-process-acc-copy">
                                    <span class="adm-process-acc-title">{{ $proGroup['name'] }}</span>
                                    @php
                                        $processTaskCount = $proGroup['pendingTask']->count() + $proGroup['inProgressTask']->count() + $proGroup['completedTask']->count();
                                        $processCreated = !empty($proGroup['created_at']) ? date('j M, Y', strtotime($proGroup['created_at'])) : '';
                                    @endphp
                                    <span class="adm-process-acc-meta">{{ $processCreated != '' ? 'Created '.$processCreated.' · ' : '' }}{{ $processTaskCount }} {{ $processTaskCount == 1 ? 'task' : 'tasks' }}</span>
                                </span>
                                @if($proGroup['pendingTask']->count() > 0)
                                    <span class="py-1 px-4 inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-2 relative">{{ $proGroup['pendingTask']->count() }} Pendings</span>
                                @endif
                                <span class="accordionCollaps" aria-hidden="true">
                                    <i data-lucide="plus" class="accordionCollaps__plus"></i>
                                    <i data-lucide="minus" class="accordionCollaps__minus"></i>
                                </span>
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
                                                @include('pages.students.admission.partials.process-task-card', ['task' => $task, 'applicant' => $applicant, 'variant' => 'pending'])
                                            @endforeach
                                        @else 
                                            <div class="adm-alert adm-alert--warning" role="alert">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg> Oops! There are no pending process found for this applicant.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-4" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-4-tab">
                                        @if($proGroup['inProgressTask']->count() > 0)
                                            @foreach($proGroup['inProgressTask'] as $task)
                                                @include('pages.students.admission.partials.process-task-card', ['task' => $task, 'applicant' => $applicant, 'variant' => 'progress'])
                                            @endforeach
                                        @else 
                                            <div class="adm-alert adm-alert--warning" role="alert">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg> Oops! There are no "In Progress" task found for this applicant.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-2" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-2-tab">
                                        @if($proGroup['completedTask']->count() > 0)
                                            @foreach($proGroup['completedTask'] as $task)
                                            @include('pages.students.admission.partials.process-task-card', ['task' => $task, 'applicant' => $applicant, 'variant' => 'completed'])
                                            @endforeach
                                        @else 
                                            <div class="adm-alert adm-alert--warning" role="alert">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg> Oops! There are no completed process found for this applicant.
                                            </div>
                                        @endif
                                    </div>
                                    <div id="process-tab-{{ $loop->index }}-3" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-{{ $loop->index }}-3-tab">
                                        <div class="overflow-x-auto scrollbar-hidden">
                                            <div id="processTaskArchiveListTable_{{ $proGroup['id'] }}" data-process="{{ $proGroup['id'] }}" data-applicant="{{ $applicant->id }}" class="mt-5 table-report table-report--tabulator processTaskArchiveListTable"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else 
            <div class="adm-alert adm-alert--warning" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v5M12 16h.01"></path></svg> Oops! No task found under this process.
            </div>
        @endif
    </div>
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
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="processTaskLogTable" data-interview="0" data-applicantid="{{ $applicant->id }}" data-applicanttaskid="0" class="mt-0 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-0"><i data-lucide="x" class="adm-btn-icon"></i> Cancel</button>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1"><i data-lucide="x" class="adm-btn-icon"></i> Cancel</button>
                        <button type="submit" id="updateOutcomeBtn" class="btn btn-primary w-auto">
                            <i data-lucide="check" class="adm-btn-icon"></i> Update
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2 adm-btn-loader">
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
                    <form method="post" action="{{ route('admission.upload.task.documents') }}" class="dropzone adm-upload-dropzone adm-process-dropzone" id="uploadTaskDocumentForm" enctype="multipart/form-data">
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <span class="adm-upload-dropzone__icon"><i data-lucide="upload-cloud" class="w-5 h-5"></i></span>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500 adm-upload-dropzone__hint">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="applicant_task_id" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                    </form>
                    <div class="mt-4 adm-upload-name">
                        <label for="process_doc_name" class="form-label">Document Name</label>
                        <input type="text" id="process_doc_name" class="form-control w-full" name="process_doc_name">
                    </div>
                    <div class="mt-4 adm-upload-hardcopy">
                        <label class="form-label">Hard Copy Checked?</label>
                        <div class="adm-upload-choice-group">
                            <label class="adm-upload-choice" for="hard_copy_check-1">
                                <input id="hard_copy_check-1" type="radio" value="1" name="hard_copy_check_status">
                                <span class="adm-upload-choice__chip"><i data-lucide="check" class="w-4 h-4"></i></span>
                                <span>Yes</span>
                            </label>
                            <label class="adm-upload-choice" for="hard_copy_check-2">
                                <input checked id="hard_copy_check-2" type="radio" value="0" name="hard_copy_check_status">
                                <span class="adm-upload-choice__chip"><i data-lucide="x" class="w-4 h-4"></i></span>
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1"><i data-lucide="x" class="adm-btn-icon"></i> Cancel</button>
                    <button type="submit" id="uploadProcessDoc" class="btn btn-primary w-auto">     
                        <i data-lucide="upload-cloud" class="adm-btn-icon"></i> Upload
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 adm-btn-loader">
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
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1"><i data-lucide="x" class="adm-btn-icon"></i> Cancel</button>
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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24"><i data-lucide="check" class="adm-btn-icon"></i> Ok</button>
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
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24"><i data-lucide="check" class="adm-btn-icon"></i> Ok</button>
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
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1"><i data-lucide="x" class="adm-btn-icon"></i> No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto"><i data-lucide="check" class="adm-btn-icon"></i> Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/admission-process.js')
    @vite('resources/js/admission-vue.js')
@endsection
