@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Tasks List</h2>
                    <button data-tw-toggle="modal" data-tw-target="#addTaskModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add New Task</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Process</label>
                                <select id="processlists-01" name="processlists" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="">Please Select</option>
                                    @if(!empty($processlists))
                                        @foreach($processlists as $pro)
                                            <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="taskTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Add Modal -->
    <div id="addTaskModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addTaskForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Task</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-40 h-40 flex-none image-fit relative">
                                    <img alt="User Photo" class="rounded-full processImageAddShow" id="processImageAddShow" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="processImageAdd" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="processImageAdd"/>
                                </div>
                            </div>
                            <div class="col-span-8">
                                <div>
                                    <label for="process_list_id" class="form-label">Permission <span class="text-danger">*</span></label>
                                    <select id="process_list_id" name="process_list_id" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($processlists))
                                            @foreach($processlists as $pro)
                                                <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-process_list_id text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="short_description" class="form-label">Short Description</label>
                                    <input id="short_description" type="text" name="short_description" class="form-control w-full">
                                    <div class="acc__input-error error-short_description text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="assigned_users" class="form-label">Assigned Users <span class="text-danger">*</span></label>
                                    <select id="assigned_users" name="assigned_users[]" class="w-full tom-selects" multiple>
                                        @if(!empty($employees))
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->user_id }}">{{ $emp->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-assigned_users text-danger mt-2"></div>
                                </div>
                                <div class="mt-3 grid grid-cols-12 gap-4">
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="interview" class="form-label">Interview <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="interview-yes" class="form-check-input" type="radio" name="interview" value="Yes">
                                                    <label class="form-check-label" for="interview-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="interview-no" class="form-check-input" type="radio" name="interview" value="No">
                                                    <label class="form-check-label" for="interview-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="upload" class="form-label">Upload <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="upload-yes" class="form-check-input" type="radio" name="upload" value="Yes">
                                                    <label class="form-check-label" for="upload-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="upload-no" class="form-check-input" type="radio" name="upload" value="No">
                                                    <label class="form-check-label" for="upload-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="org_email" class="form-label">Organization Email</label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="org_email-yes" class="form-check-input" type="radio" name="org_email" value="Yes">
                                                    <label class="form-check-label" for="org_email-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="org_email-no" class="form-check-input" type="radio" name="org_email" value="No">
                                                    <label class="form-check-label" for="org_email-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="id_card" class="form-label">ID Card</label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="id_card-yes" class="form-check-input" type="radio" name="id_card" value="Yes">
                                                    <label class="form-check-label" for="id_card-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="id_card-no" class="form-check-input" type="radio" name="id_card" value="No">
                                                    <label class="form-check-label" for="id_card-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <div class="form-check form-switch">
                                                <label class="form-check-label mr-3 ml-0" for="is_df">External Link</label>
                                                <input id="external_link" class="form-check-input" name="external_link" value="1" type="checkbox">
                                            </div> 
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <div class="extarnalUrlWrap" style="display: none;">
                                            <label for="external_link_ref" class="form-label">External URL <span class="text-danger">*</span></label>
                                            <input id="external_link_ref" type="text" name="external_link_ref" class="form-control w-full">
                                            <div class="acc__input-error error-external_link_ref text-danger mt-2"></div>
                                        </div>  
                                    </div>
                                    <div class="col-span-12">
                                        <div>
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="status-yes" class="form-check-input" type="radio" name="status" value="Yes">
                                                    <label class="form-check-label" for="status-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="status-no" class="form-check-input" type="radio" name="status" value="No">
                                                    <label class="form-check-label" for="status-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <div class="taskStatusesWrap" style="display: none;">
                                            <label for="upload" class="form-label">Task Statuses <span class="text-danger">*</span></label>
                                            @if($taskStatus->count() > 0)
                                                <div>
                                                    @foreach($taskStatus as $ts)
                                                        <div class="form-check mt-2">
                                                            <input id="task-status-{{ $ts->id }}" class="form-check-input" type="checkbox" name="task_statuses[]" value="{{ $ts->id }}">
                                                            <label class="form-check-label" for="task-status-{{ $ts->id }}">{{ $ts->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="acc__input-error error-task_statuses text-danger mt-2"></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="save" class="btn btn-primary w-auto">     
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editTaskModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editTaskForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Task</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-40 h-40 flex-none image-fit relative">
                                    <img alt="User Photo" class="rounded-full processImageEditShow" id="processImageEditShow" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="processImageEdit" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="processImageEdit"/>
                                </div>
                            </div>
                            <div class="col-span-8">
                                <div>
                                    <label for="process_list_id" class="form-label">Permission <span class="text-danger">*</span></label>
                                    <select id="process_list_id" name="process_list_id" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($processlists))
                                            @foreach($processlists as $pro)
                                                <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-process_list_id text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="edit_short_description" class="form-label">Short Description</label>
                                    <input id="edit_short_description" type="text" name="short_description" class="form-control w-full">
                                    <div class="acc__input-error error-short_description text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="edit_assigned_users" class="form-label">Assigned Users <span class="text-danger">*</span></label>
                                    <select id="edit_assigned_users" name="assigned_users[]" class="w-full tom-selects" multiple>
                                        @if(!empty($employees))
                                            @foreach($employees as $emp)
                                                <option value="{{ $emp->user_id }}">{{ $emp->full_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-edit_assigned_users text-danger mt-2"></div>
                                </div>
                                <div class="mt-3 grid grid-cols-12 gap-4">
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="edit_interview" class="form-label">Interview <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="edit_interview-yes" class="form-check-input" type="radio" name="interview" value="Yes">
                                                    <label class="form-check-label" for="edit_interview-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="edit_interview-no" class="form-check-input" type="radio" name="interview" value="No">
                                                    <label class="form-check-label" for="edit_interview-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="upload" class="form-label">Upload <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="edit_upload-yes" class="form-check-input" type="radio" name="upload" value="Yes">
                                                    <label class="form-check-label" for="edit_upload-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="edit_upload-no" class="form-check-input" type="radio" name="upload" value="No">
                                                    <label class="form-check-label" for="edit_upload-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="org_email" class="form-label">Organization Email</label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="edit_org_email-yes" class="form-check-input" type="radio" name="org_email" value="Yes">
                                                    <label class="form-check-label" for="edit_org_email-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="edit_org_email-no" class="form-check-input" type="radio" name="org_email" value="No">
                                                    <label class="form-check-label" for="edit_org_email-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <label for="id_card" class="form-label">ID Card</label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="edit_id_card-yes" class="form-check-input" type="radio" name="id_card" value="Yes">
                                                    <label class="form-check-label" for="edit_id_card-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="edit_id_card-no" class="form-check-input" type="radio" name="id_card" value="No">
                                                    <label class="form-check-label" for="edit_id_card-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div>
                                            <div class="form-check form-switch">
                                                <label class="form-check-label mr-3 ml-0" for="edit_external_link">External Link</label>
                                                <input id="edit_external_link" class="form-check-input" name="external_link" value="1" type="checkbox">
                                            </div> 
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <div class="extarnalUrlWrap" style="display: none;">
                                            <label for="edit_external_link_ref" class="form-label">External URL <span class="text-danger">*</span></label>
                                            <input id="edit_external_link_ref" type="text" name="external_link_ref" class="form-control w-full">
                                            <div class="acc__input-error error-external_link_ref text-danger mt-2"></div>
                                        </div>  
                                    </div>
                                    <div class="col-span-12">
                                        <div>
                                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                            <div class="flex flex-col sm:flex-row">
                                                <div class="form-check mr-3">
                                                    <input id="edit_status-yes" class="form-check-input" type="radio" name="status" value="Yes">
                                                    <label class="form-check-label" for="edit_status-yes">Yes</label>
                                                </div>
                                                <div class="form-check mr-2">
                                                    <input checked id="edit_status-no" class="form-check-input" type="radio" name="status" value="No">
                                                    <label class="form-check-label" for="edit_status-no">No</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-span-12">
                                        <div class="taskStatusesWrap" style="display: none;">
                                            <label for="upload" class="form-label">Task Statuses <span class="text-danger">*</span></label>
                                            @if($taskStatus->count() > 0)
                                                <div>
                                                    @foreach($taskStatus as $ts)
                                                        <div class="form-check mt-2">
                                                            <input id="edit_task-status-{{ $ts->id }}" class="form-check-input" type="checkbox" name="task_statuses[]" value="{{ $ts->id }}">
                                                            <label class="form-check-label" for="edit_task-status-{{ $ts->id }}">{{ $ts->name }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="acc__input-error error-task_statuses text-danger mt-2"></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="update" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/tasklist.js')
@endsection