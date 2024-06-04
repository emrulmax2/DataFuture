@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student list for <u class="theTaskName">{{ $task->name }}</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('task.manager') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Task Manager</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select name="status" id="status" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <div class="taskActionBtnGroup">
                    @if($task->org_email == 'Yes')
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="exportTaskStudentsBtn" style="display: none;">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Export Students Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="completeEmailTaskStudentsBtn" style="display: none;">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Complete & Send Email
                        </button>
                    @else 
                        <button data-phase="{{ $task->processlist->phase }}" data-taskid="{{ $task->id }}" type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="exportTaskStudentListBtn" style="display: none;">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Export Student List 
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="rgb(100, 116, 139)" class="w-4 h-4 ml-2 theLoaderSvg">
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
                        <div class="dropdown w-1/2 sm:w-auto ml-2 inline-flex" id="commonActionBtns">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i> Update Task Status <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-80">
                                <ul class="dropdown-content">
                                    <li>
                                        <a data-phase="{{ $task->processlist->phase }}" data-taskid="{{ $task->id }}" data-status="Completed" href="javascript:void(0);" class="dropdown-item updateSelectedStudentTaskStatusBtn">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i> Mark As Completed 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="rgb(100, 116, 139)" class="w-4 h-4 ml-2 theLoaderSvg">
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
                                        </a>
                                    </li>
                                    <li>
                                        <a data-phase="{{ $task->processlist->phase }}" data-taskid="{{ $task->id }}" href="javascript:void(0);" class="dropdown-item markAsCanceled">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2 text-danger"></i> Mark As Canceled 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="rgb(100, 116, 139)" class="w-4 h-4 ml-2 theLoaderSvg">
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
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="taskAssignedStudentTable" data-email="{{ $task->org_email }}" data-idcard="{{ $task->id_card }}" data-interview="{{ $task->interview }}" data-taskid="{{ $task->id }}" data-phase={{ (isset($task->processlist->phase) && !empty($task->processlist->phase) ? $task->processlist->phase : 'Live') }} class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

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
                        <input type="hidden" name="student_id" value="0"/>
                        <input type="hidden" name="task_id" value="0"/>
                        <input type="hidden" name="phase" value=""/>
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
                    <form method="post"  action="{{ route('task.manager.upload.document') }}" class="dropzone" id="uploadTaskDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="0"/>
                        <input type="hidden" name="task_id" value="0"/>
                        <input type="hidden" name="phase" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                    </form>
                    <div class="mt-3">
                        <label for="process_doc_name" class="form-label">Document Name</label>
                        <input type="text" id="process_doc_name" class="form-control w-full" name="process_doc_name">
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Hard Copy Checked?</label>
                        <div class="form-check mt-2">
                            <input id="hard_copy_check-1" class="form-check-input" type="radio" value="1" name="hard_copy_check_status" value="1">
                            <label class="form-check-label" for="hard_copy_check-1">Yes</label>
                        </div>
                        <div class="form-check mt-2">
                            <input checked id="hard_copy_check-2" class="form-check-input" type="radio" value="0" name="hard_copy_check_status" value="0">
                            <label class="form-check-label" for="hard_copy_check-2">No</label>
                        </div>
                    </div>
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

    <div id="downloadIDCard" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="idLoader flex justify-center items-center p-10"><i data-loading-icon="rings" class="w-20 h-20"></i></div>
                    <div class="idContent" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Canceled Reason Modal -->
    <div id="canceledReasonModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="canceledReasonForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Canceled Reason</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="canceled_reason" class="form-label">Canceled Reason <span class="text-danger">*</span></label>
                            <textarea id="canceled_reason" name="canceled_reason" rows="5" class="form-control w-full"></textarea>
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <input type="hidden" name="phase" value=""/>
                        <input type="hidden" name="task_id" value="0"/>
                        <input type="hidden" name="ids" value=""/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateReason" class="btn btn-primary w-auto">     
                            Submit                      
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
            </form>
        </div>
    </div>
    <!-- END: Canceled Reason Modal -->

    <!-- BEGIN: Student Profile Lock Modal -->
    <div id="callLockModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="callLockModalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Unlock Profile for Interview</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="dob" class="form-label">Please provide applicant date of birth to unlock profile <span class="text-danger">*</span></label>
                            <input id="dob" type="text" name="dob" class="datepicker date-picker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                            <div class="dob__input-error error-name text-danger mt-2"></div>
                            <input type="hidden" id="applicantId" name="applicantId" value="">
                            <input type="hidden" id="taskListId" name="taskListId" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="unlock" class="btn btn-primary w-auto">     
                            <i data-lucide="unlock" class="stroke-1.5 h-5 w-5 mr-1"></i> Unlock                      
                            <svg class="loading" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
            </form>
        </div>
    </div>
    <!-- END: Student Profile Lock Modal -->

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/task-manager.js')
@endsection