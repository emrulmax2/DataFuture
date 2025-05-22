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
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">REG/REF</label>
                    <input id="reg_or_ref" name="reg_or_ref" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Reg No or Ref No">
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select name="status" id="status" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                        <option value="Canceled">Canceled</option>
                    </select>
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Courses</label>
                    <select name="courses" id="courses" class="form-control sm:w-40 2xl:w-60 mt-2 sm:mt-0">
                        <option value="">All Courses</option>
                        @if($courses->count() > 0)
                            @foreach($courses as $crs)
                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <div class="taskActionBtnGroup">
                    @if($task->pearson_reg == 'Yes')
                        <button type="button" data-tw-toggle="modal" data-tw-target="#uploadPearsonRegConfModal" class="btn btn-facebook w-1/2 sm:w-auto ml-2" id="uploadPearsonRegConfBtn">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Upload Pearson Reg. Confirmation
                        </button>
                    @endif
                    @if($task->org_email == 'Yes')
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="exportTaskStudentsBtn" style="display: none;">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Export Students Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="completeEmailTaskStudentsBtn" style="display: none;">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Complete & Send Email
                        </button>
                    @else 
                        @if($task->pearson_reg == 'Yes')
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="exportPearsonRegStudentList" style="display: none;">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Export Pearson Registration
                        </button>
                        @endif
                        
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
            <div id="taskAssignedStudentTable" data-pearsonreg="{{ $task->pearson_reg }}" data-excuse="{{ $task->attendance_excuses }}" data-email="{{ $task->org_email }}" data-idcard="{{ $task->id_card }}" data-interview="{{ $task->interview }}" data-taskid="{{ $task->id }}" data-phase={{ (isset($task->processlist->phase) && !empty($task->processlist->phase) ? $task->processlist->phase : 'Live') }} class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Upload Prearson Reg Modal -->
    <div id="uploadPearsonRegConfModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="uploadPearsonRegConfForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Upload Pearson Registration Confirmations</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="change_status_id" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="change_status_id" name="status_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(isset($statuses))
                                    @foreach($statuses as $stst)
                                        <option value="{{ $stst->id }}">{{ $stst->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="term_declaration_id" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="term_declaration_id" name="term_declaration_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_reason" class="form-label">Change Reason</label>
                            <textarea name="status_change_reason" id="status_change_reason" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <label for="upload_pearson_doc" class="form-label">Upload Pearson Registration Excel</label>
                            <div class="flex justify-start items-center relative w-full">
                                <label for="editPearRegDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                    <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Excel
                                </label>
                                <input type="file" accept=".xlsx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editPearRegDocument"/>
                                <span id="editPearRegDocumentName" class="documentPearRegName ml-5"></span>
                            </div>
                            <div class="acc__input-error error-document text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer flex justify-between">
                        <a href="{{ url('storage/BTECRT_Sample.xlsx') }}" class="btn btn-success text-white w-auto mr-auto">Download Sample</a>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="upPRegConfBtn" class="btn btn-primary w-auto">
                            Save
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
                        <input type="hidden" name="task_list_id" value="{{ $task->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Upload Prearson Reg Modal -->

    <!-- BEGIN: View Excuse Modal -->
    <div id="viewAttendanceExcuseModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="viewAttendanceExcuseForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Attendance Excuse</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="loaderWrap flex justify-center items-center py-5">
                            <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(30, 41, 59)" class="w-8 h-8">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>              
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAttnExcuseBtn" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="student_task_id" value="0"/>
                        <input type="hidden" name="attendance_excuse_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: View Excuse Modal -->

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

    

    <!-- BEGIN: Update Outcome Modal -->
    <div id="updateTaskDocumentRequestOutcomeModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="updateTaskDocumentRequestOutcomeForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Document Request</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        
                        <div id="informative-divmark" role="alert" class="alert relative border rounded-md px-5 py-4 bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 mb-2">
                            <div class="flex items-center">
                                <div  class="text-lg font-medium letter-title justify-start">
                                    Awesome alert with additional info
                                </div>
                                
                                <div class="justify-between  ml-auto rounded-md bg-orange-500 px-1 text-xs text-white letter-status">
                                    Pending
                                </div>
                            </div>
                            <div class="mt-3 letter-description">
                                Lorem Ipsum is simply dummy text of the printing and
                                typesetting industry. Lorem Ipsum has been the
                                industry's standard dummy text ever since the 1500s.
                            </div>
                            
                            <div class="mt-3 flex items-center">
                                <div class="flex font-medium text-sm justify-start">Service requested : <span class="letter-service-type"></span></div>
                                
                                <div class="flex justify-between ml-auto rounded-md bg-slate-500 px-1 text-xs text-white letter-request-time">
                                    New
                                </div>
                                
                            </div>
                        </div>
                        <div>
                            <div class="font-medium text-base">Status <span class="text-danger">*</span></div>
{{--                             
                                <div class="form-check mr-2  my-3">
                                    <input id="status1" class="form-check-input" type="radio" name="status" value="In Progress">
                                    <label class="form-check-label" for="status1">In Progress</label>
                                </div> --}}
                                
                                <div class="form-check mr-2  my-3">
                                    <input id="status2" class="form-check-input" type="radio" name="status" value="Approved">
                                    <label class="form-check-label" for="status2">Approved</label>
                                </div>
                                
                                <div class="form-check mr-2 my-3 sm:mt-0">
                                    <input id="status3" class="form-check-input" type="radio" name="status" value="Rejected">
                                    <label class="form-check-label" for="status3">Rejected</label>
                                </div>
                            <div class="acc__input-error error-status text-danger mt-2"></div>
                        </div>  
                        <div class="mt-3 description">
                            <label for="description" class="form-label">Remarks/ Comments <span class="text-danger">*</span></label>
                            {{-- <textarea id="description" name="description" class="form-control w-full h-52 border border-slate-200 px-3 py-3 rounded-md" rows="20" style="white-space: pre-wrap;">
                            </textarea> --}}
                            
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="emailEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!--implement a email sending checkbox here-->
                        <div class="flex justify-between">
                            <div class="form-check mr-2 my-3 sm:mt-0 flex justify-start items-center">
                                <input id="email_sent" class="form-check-input" type="checkbox" checked name="email_sent" value="1">
                                <label class="form-check-label" for="email_sent">Send Confirmation Email</label>
                            </div>
                            <div class="form-check mr-2 my-3 sm:mt-0 flex justify-end items-center">
                                <button type="submit" id="updateRequestBtn" data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&:hover:not(:disabled)]:bg-success/10 mb-2 mr-1 w-52">
                                    <i data-lucide="check-circle"   class="w-4 h-4 mr-2 text-success "></i> Update Status
                                    <i data-loading-icon="oval" class="w-4 h-4 loading ml-1 hidden"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="0"/>
                        <input type="hidden" name="task_id" value="0"/>
                        <input type="hidden" name="phase" value=""/>
                        <input id="#request_id" type="hidden" name="student_task_id" value="" />
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
        <a data-tw-dismiss="modal" class="hideIDCardModalBtn btn btn-linkedin text-white btn-rounded m-0 p-0 w-9 h-9" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-white"></i></a>
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

    <!-- BEGIN: Send Letter Modal -->
    <div id="addLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addLetterForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Send Letter</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div id="informative-div" role="alert" class="alert relative border rounded-md px-5 py-4 bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 mb-2">
                            <div class="flex items-center">
                                <div  class="text-lg font-medium letter-title justify-start">
                                    Awesome alert with additional info
                                </div>
                                
                                <div class="justify-between  ml-auto rounded-md bg-orange-500 px-1 text-xs text-white letter-status">
                                    Pending
                                </div>
                            </div>
                            <div class="mt-3 letter-description">
                                Lorem Ipsum is simply dummy text of the printing and
                                typesetting industry. Lorem Ipsum has been the
                                industry's standard dummy text ever since the 1500s.
                            </div>
                            
                            <div class="mt-3 flex items-center">
                                <div class="flex font-medium text-sm justify-start">Service requested : <span class="letter-service-type"></span></div>
                                
                                <div class="flex justify-between ml-auto rounded-md bg-slate-500 px-1 text-xs text-white letter-request-time">
                                    New
                                </div>
                                
                            </div>
                        </div>
                        <div>
                            <label for="issued_date" class="form-label">Issued Date <span class="text-danger">*</span></label>
                            <input id="issued_date" type="text" value="{{ date('Y-m-d') }}" name="issued_date" class="datepicker form-control w-full" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-issued_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="letter_set_id" class="form-label">Letter <span class="text-danger">*</span></label>
                            <select id="letter_set_id" name="letter_set_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if(!empty($letterSet))
                                    @foreach($letterSet as $ls)
                                        <option value="{{ $ls->id }}">{{ $ls->letter_type.' - '.$ls->letter_title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-letter_set_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-5 letterEditorArea" style="display: none;">
                            <div class="flex justify-between mb-2">
                                <div class="ml-auto">@include('pages.settings.letter.letter-tags')</div>
                            </div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="letterEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-letter_body text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="signatory_id" class="form-label">Signatory</label>
                            <select id="signatory_id" name="signatory_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($signatory))
                                    @foreach($signatory as $sg)
                                        <option value="{{ $sg->id }}">{{ $sg->signatory_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-signatory_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch items-center">
                                <label class="form-check-label ml-0 mr-5" for="checkbox-switch-7">Send Email</label>
                                <input id="send_in_email" class="form-check-input" name="send_in_email" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 commonSmtpWrap" style="display: none;">
                            <label for="comon_smtp_id" class="form-label">SMTP <span class="text-danger">*</span></label>
                            <select id="comon_smtp_id" name="comon_smtp_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($smtps))
                                    @foreach($smtps as $sm)
                                        <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-comon_smtp_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="sendLetterBtn" class="btn btn-primary w-auto">     
                            Send Letter                      
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
                        <input type="hidden" name="student_id" value=""/>
                        <input type="hidden" name="student_task_id" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Letter Modal -->
    
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc">Do you want to delete the uploaded file.</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="" data-action="DELETE" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: View Modal -->
    <div id="viewCommunicationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Vew Communication</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <div class="footerBtns" style="float: left"></div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Modal -->
@endsection

@section('script')
    
    @vite('resources/js/task-manager.js')
    @vite('resources/js/student-task-letter-manager.js')
@endsection