@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }} </title>
@endsection

@section('subcontent')

    @include('pages.employee.profile.title-info')

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5 pb-7">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employee Appraisals</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#addAppraisalModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add Appraisal</button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
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
                        <div id="employeeAppraisalListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="intro-y mt-5">
        <div class="intro-y box p-5 pb-7">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employee Trainings</div>
                </div>
                <div class="col-span-6 text-right">
                    <button data-tw-toggle="modal" data-tw-target="#addTraininglModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add Training</button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-ET" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-ET" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-ET" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-ET" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-ET" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-ET" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-ET" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="employeeTrainingListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: ADD Training Modal -->
    <div id="addTraininglModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addTraininglForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Training</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-4 gap-y-1">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="name" class="form-label">Training Name <span class="text-danger">*</span></label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="provider" class="form-label">Provider <span class="text-danger">*</span></label>
                                <input id="provider" type="text" name="provider" class="form-control w-full">
                                <div class="acc__input-error error-provider text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input id="location" type="text" name="location" class="form-control w-full">
                                <div class="acc__input-error error-location text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="training_date" class="form-label">Training Date <span class="text-danger">*</span></label>
                                <input id="training_date" type="text" name="training_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY - DD-MM-YYYY" data-format="DD-MM-YYYY">
                                <div class="acc__input-error error-training_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="cost" class="form-label">Cost</label>
                                <input id="cost" type="number" step="any" name="cost" class="form-control w-full">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="expire_date" class="form-label">Expire Date</label>
                                <input id="expire_date" type="text" name="expire_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-12 flex justify-start items-center relative pt-3">
                                <div class="flex justify-start items-center relative">
                                    <label for="addTraiDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addTraiDocument"/>
                                    <span id="addTraiDocumentName" class="addTraiDocumentName ml-5"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveTraining" class="btn btn-primary w-auto">     
                            Save Training                  
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
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: ADD Training Modal -->

    <!-- BEGIN: Edit Training Modal -->
    <div id="editTraininglModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editTraininglForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Training</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-4 gap-y-1">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_name" class="form-label">Training Name <span class="text-danger">*</span></label>
                                <input id="edit_name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_provider" class="form-label">Provider <span class="text-danger">*</span></label>
                                <input id="edit_provider" type="text" name="provider" class="form-control w-full">
                                <div class="acc__input-error error-provider text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input id="edit_location" type="text" name="location" class="form-control w-full">
                                <div class="acc__input-error error-location text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_training_date" class="form-label">Training Date <span class="text-danger">*</span></label>
                                <input id="edit_training_date" type="text" name="training_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY - DD-MM-YYYY" data-format="DD-MM-YYYY">
                                <div class="acc__input-error error-training_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_cost" class="form-label">Cost</label>
                                <input id="edit_cost" type="number" step="any" name="cost" class="form-control w-full">
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_expire_date" class="form-label">Expire Date</label>
                                <input id="edit_expire_date" type="text" name="expire_date" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-12 flex justify-start items-center relative pt-3">
                                <div class="flex justify-start items-center relative">
                                    <label for="editTraiDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                        <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editTraiDocument"/>
                                    <span id="editTraiDocumentName" class="editTraiDocumentName ml-5"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateTraining" class="btn btn-primary w-auto">     
                            Update Training                  
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
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: ADD Training Modal -->

    <!-- BEGIN: Edit New Request Modal -->
    <div id="addAppraisalModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"> {{-- modal-lg --}}
            <form method="POST" action="#" id="addAppraisalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add New Appraisal</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="due_on" class="form-label">Due On <span class="text-danger">*</span></label>
                            <input id="due_on" type="text" name="due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-due_on text-danger mt-2"></div>
                        </div>
                        {{--<div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6">
                                <label for="completed_on" class="form-label">Completed On</label>
                                <input id="completed_on" type="text" name="completed_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="next_due_on" class="form-label">Next Due On</label>
                                <input id="next_due_on" type="text" name="next_due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="appraised_by" class="form-label">Appraised By</label>
                                <select id="appraised_by" name="appraised_by" class="tom-selects w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="reviewed_by" class="form-label">Reviewed By</label>
                                <select id="reviewed_by" name="reviewed_by" class="tom-selects w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="total_score" class="form-label">Total Score</label>
                                <input id="total_score" type="number" step="any" name="total_score" class="form-control w-full">
                            </div>
                            <div class="col-span-6">
                                <label for="promotion_consideration" class="form-label">Consider for Promotion</label>
                                <div class="form-check form-switch m-0">
                                    <input id="promotion_consideration" class="form-check-input" name="promotion_consideration" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-12">
                                <label for="notes" class="form-label">Note</label>
                                <textarea id="notes" name="notes" rows="3" class="form-control w-full"></textarea>
                            </div>
                        </div>--}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveAppraisal" class="btn btn-primary w-auto">     
                            Save Appraisal                  
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
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div id="editAppraisalModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editAppraisalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Appraisal</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6">
                                <label for="edit_due_on" class="form-label">Due On <span class="text-danger">*</span></label>
                                <input id="edit_due_on" readonly type="text" name="due_on" class="form-control w-full">
                                <div class="acc__input-error error-due_on text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_completed_on" class="form-label">Completed On</label>
                                <input id="edit_completed_on" type="text" name="completed_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_next_due_on" class="form-label">Next Due On</label>
                                <input id="edit_next_due_on" type="text" name="next_due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_appraised_by" class="form-label">Appraised By</label>
                                <select id="edit_appraised_by" name="appraised_by" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_reviewed_by" class="form-label">Reviewed By</label>
                                <select id="edit_reviewed_by" name="reviewed_by" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->first_name.' '.$aemp->last_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_total_score" class="form-label">Total Score</label>
                                <input id="edit_total_score" type="number" step="any" name="total_score" class="form-control w-full">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_promotion_consideration" class="form-label">Consider for Promotion</label>
                                <div class="form-check form-switch m-0">
                                    <input id="edit_promotion_consideration" class="form-check-input" name="promotion_consideration" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-12">
                                <label for="edit_notes" class="form-label">Note</label>
                                <textarea id="edit_notes" name="notes" rows="3" class="form-control w-full"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAppraisal" class="btn btn-primary w-auto">     
                            Update Apprisal                  
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
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="viewAppraisalNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Appraisal Note</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-0">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Edit New Request Modal -->


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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary successCloser w-24">Ok</button>
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
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
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
    @vite('resources/js/employee-appraisal.js')
    @vite('resources/js/employee-training.js')
@endsection