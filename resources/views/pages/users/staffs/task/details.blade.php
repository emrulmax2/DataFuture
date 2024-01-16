@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student list for <u>{{ $task->name }}</u></h2>
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
                    @if($task->id == 5)
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="exportTaskStudentsBtn" style="display: none;">
                            <i data-lucide="sheet" class="w-4 h-4 mr-2"></i> Export Students Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-1/2 sm:w-auto ml-2" id="completeEmailTaskStudentsBtn" style="display: none;">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Complete & Send Email
                        </button>
                    @else 
                        <div class="dropdown w-1/2 sm:w-auto" id="commonActionBtns">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="settings-2" class="w-4 h-4 mr-2"></i> Update Task Status <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-80">
                                <ul class="dropdown-content">
                                    <li>
                                        <a data-phase="{{ $task->processlist->phase }}" data-taskid="{{ $task->id }}" data-status="Completed" href="javascript:void(0);" class="dropdown-item updateSelectedStudentTaskStatusBtn">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i> Mark As Completed 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 theLoaderSvg">
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
                                        <a data-phase="{{ $task->processlist->phase }}" data-taskid="{{ $task->id }}" data-status="Canceled" href="javascript:void(0);" class="dropdown-item updateSelectedStudentTaskStatusBtn">
                                            <i data-lucide="x-circle" class="w-4 h-4 mr-2 text-danger"></i> Mark As Canceled 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 theLoaderSvg">
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
            <div id="taskAssignedStudentTable" data-taskid="{{ $task->id }}" data-phase={{ (isset($task->processlist->phase) && !empty($task->processlist->phase) ? $task->processlist->phase : 'Live') }} class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

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