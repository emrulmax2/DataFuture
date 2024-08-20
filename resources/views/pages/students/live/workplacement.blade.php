@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <ul class="nav workplacementtab" role="tablist">
            <li id="std_work_placement_item" class="nav-item" role="presentation">
                <button class="nav-link active btn btn-outline-secondary active-bg-white hover-bg-white hover:text-primary rounded-0" data-tw-toggle="pill" data-tw-target="#std_work_placement" type="button" role="tab" aria-controls="std_work_placement" aria-selected="true">
                    Work Placement
                </button>
            </li>
            <li id="std_wbl_profile_item" class="nav-item" role="presentation">
                <button class="nav-link btn btn-outline-secondary rounded-0 active-bg-white hover-bg-white hover:text-primary" data-tw-toggle="pill" data-tw-target="#std_wbl_profile" type="button" role="tab" aria-controls="std_wbl_profile" aria-selected="false" >
                    Student WBL Profile
                </button>
            </li>
        </ul>
    </div>
    <div class="tab-content workplacementtabcontent">
        <div id="std_work_placement" class="tab-pane active" role="tabpanel" aria-labelledby="example-3-tab">
            <div class="intro-y box p-5">
                <div class="absolute thebtnarea">
                    <button data-tw-toggle="modal" data-tw-target="#addHourModal" type="button" class="btn btn-success rounded-0 text-white"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Hours</button>
                    <button type="button" class="btn btn-primary rounded-0"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF</button>
                </div>
                <div class="intro-y">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium">Hours Required</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-danger px-2 py-0 ml-2 text-white rounded-0">
                                        {{ $student->crel->creation->required_hours.' Hours' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="grid grid-cols-12 gap-3 items-center">
                                <div class="col-span-4 text-slate-500 font-medium">Hours Completed</div>
                                <div class="col-span-8">
                                    <span class="btn inline-flex btn-success px-2 py-0 ml-2 text-white rounded-0">
                                        {{ (isset($work_hours) && $work_hours > 0 ? $work_hours.' Hours' : '0 Hours') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="intro-y pt-5 pb-5">
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
                        <div id="studentWorkPlacementTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
        <div id="std_wbl_profile" class="tab-pane" role="tabpanel" aria-labelledby="example-4-tab">
            <div class="intro-y box p-5">
                <div class="absolute thebtnarea">
                    <button style="display: none;" data-tw-toggle="modal" data-tw-target="#addWBLProfileModal" type="button" class="btn addWBLProfileBtn btn-success rounded-0 text-white"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add WBL Profile</button>
                    <button type="button" class="btn btn-primary rounded-0"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF</button>
                </div>
                <div class="intro-y">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-12 text-right">
                            <select name="student_work_placement_id" id="student_work_placement_id" class="form-control w-auto" style="max-width: 270px;">
                                <option value="">Please Select a Company</option>
                                @if($placement->count() > 0)
                                    @foreach($placement as $plc)
                                        <option value="{{ $plc->id }}">{{ (isset($plc->company->name) ? $plc->company->name : 'Unknown Company') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="intro-y pt-5 pb-5">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm-WBL" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                    <select id="status-WBL" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go-WBL" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset-WBL" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div class="flex mt-5 sm:mt-0">
                                <button id="tabulator-print-WBL" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                </button>
                                <div class="dropdown w-1/2 sm:w-auto">
                                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                    </button>
                                    <div class="dropdown-menu w-40">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv-WBL" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-WBL" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="studentWBLProfileTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  

    <!-- BEGIN: Add Hour Modal -->
    <div id="addHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addHourForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Work Placement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select id="company_id" class="form-control w-full" name="company_id">
                                <option value="">Please Select</option>
                                @if($company->count() > 0)
                                    @foreach($company as $com)
                                        <option value="{{ $com->id }}">{{ $com->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 supervisorWrap">
                            <label for="company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                            <select id="company_supervisor_id" class="form-control w-full" name="company_supervisor_id">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-3">
                            <label for="hours" class="form-label">Hours <span class="text-danger">*</span></label>
                            <input type="number" value="" id="hours" class="form-control" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select id="contract_type" class="form-control w-full" name="contract_type">
                                <option value="">Please Select</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Contract Base">Contact Base</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveWP" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Hour Modal -->

    <!-- BEGIN: Edit Hour Modal -->
    <div id="editHourModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editHourForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Work Placement Hours</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_company_id" class="form-label">Company <span class="text-danger">*</span></label>
                            <select id="edit_company_id" class="form-control w-full" name="company_id">
                                <option value="">Please Select</option>
                                @if($company->count() > 0)
                                    @foreach($company as $com)
                                        <option value="{{ $com->id }}">{{ $com->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-company_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 supervisorWrap">
                            <label for="edit_company_supervisor_id" class="form-label">Supervisor <span class="text-danger">*</span></label>
                            <select id="edit_company_supervisor_id" class="form-control w-full" name="company_supervisor_id">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-company_supervisor_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_end_date" class="form-label">End Date</label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-3">
                            <label for="edit_hours" class="form-label">Hours <span class="text-danger">*</span></label>
                            <input type="number" value="" id="edit_hours" class="form-control" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_contract_type" class="form-label">Contract Type <span class="text-danger">*</span></label>
                            <select id="edit_contract_type" class="form-control w-full" name="contract_type">
                                <option value="">Please Select</option>
                                <option value="Permanent">Permanent</option>
                                <option value="Temporary">Temporary</option>
                                <option value="Contract Base">Contact Base</option>
                                <option value="Part-time">Part-time</option>
                            </select>
                            <div class="acc__input-error error-contract_type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateWP" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Hour Modal -->

    <!-- BEGIN: Add WBL Profile Modal -->
    <div id="addWBLProfileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addWBLProfileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add WBL Profile</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">WEIF form provided</label>
                            </div>
                            <div class="col-span-4">
                                <input name="weif_form_provided_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_1_1" class="form-check-input" type="radio" name="weif_form_provided_status" value="1">
                                        <label class="form-check-label" for="R_1_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_1_0" class="form-check-input" type="radio" name="weif_form_provided_status" value="0">
                                        <label class="form-check-label" for="R_1_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Received completed WEIF form</label>
                            </div>
                            <div class="col-span-4">
                                <input name="received_completed_weif_form_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_2_1" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="1">
                                        <label class="form-check-label" for="R_2_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_2_0" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="0">
                                        <label class="form-check-label" for="R_2_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work hours update by terms</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_hour_update_term_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_3_1" class="form-check-input" type="radio" name="work_hour_update_term_status" value="1">
                                        <label class="form-check-label" for="R_3_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_3_0" class="form-check-input" type="radio" name="work_hour_update_term_status" value="0">
                                        <label class="form-check-label" for="R_3_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook completed</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_complete_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_4_1" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="1">
                                        <label class="form-check-label" for="R_4_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_4_0" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="0">
                                        <label class="form-check-label" for="R_4_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook checked</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_checked_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_5_1" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="1">
                                        <label class="form-check-label" for="R_5_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_5_0" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="0">
                                        <label class="form-check-label" for="R_5_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer handbook sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_handbook_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_6_1" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="1">
                                        <label class="form-check-label" for="R_6_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_6_0" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="0">
                                        <label class="form-check-label" for="R_6_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employers letter sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_letter_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_7_1" class="form-check-input" type="radio" name="emp_letter_sent_status" value="1">
                                        <label class="form-check-label" for="R_7_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_7_0" class="form-check-input" type="radio" name="emp_letter_sent_status" value="0">
                                        <label class="form-check-label" for="R_7_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer Confirmation Received</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_confirm_rec_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_8_1" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="1">
                                        <label class="form-check-label" for="R_8_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_8_0" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="0">
                                        <label class="form-check-label" for="R_8_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Company visit</label>
                            </div>
                            <div class="col-span-4">
                                <input name="company_visit_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_9_1" class="form-check-input" type="radio" name="company_visit_status" value="1">
                                        <label class="form-check-label" for="R_9_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_9_0" class="form-check-input" type="radio" name="company_visit_status" value="0">
                                        <label class="form-check-label" for="R_9_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of student meetings</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_std_meeting_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_10_1" class="form-check-input" type="radio" name="record_std_meeting_status" value="1">
                                        <label class="form-check-label" for="R_10_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_10_0" class="form-check-input" type="radio" name="record_std_meeting_status" value="0">
                                        <label class="form-check-label" for="R_10_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of all contacts to student (Calls, Class visit, text, letter)</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_all_contact_student_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_11_1" class="form-check-input" type="radio" name="record_all_contact_student_status" value="1">
                                        <label class="form-check-label" for="R_11_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_11_0" class="form-check-input" type="radio" name="record_all_contact_student_status" value="0">
                                        <label class="form-check-label" for="R_11_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Email sent to employer</label>
                            </div>
                            <div class="col-span-4">
                                <input name="email_sent_emp_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="R_12_1" class="form-check-input" type="radio" name="email_sent_emp_status" value="1">
                                        <label class="form-check-label" for="R_12_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="R_12_0" class="form-check-input" type="radio" name="email_sent_emp_status" value="0">
                                        <label class="form-check-label" for="R_12_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addWBL" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="student_work_placement_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add WBL Profile Modal -->

    <!-- BEGIN: Edit WBL Profile Modal -->
    <div id="editWBLProfileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editWBLProfileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit WBL Profile</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">WEIF form provided</label>
                            </div>
                            <div class="col-span-4">
                                <input name="weif_form_provided_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_1_1" class="form-check-input" type="radio" name="weif_form_provided_status" value="1">
                                        <label class="form-check-label" for="E_R_1_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_1_0" class="form-check-input" type="radio" name="weif_form_provided_status" value="0">
                                        <label class="form-check-label" for="E_R_1_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Received completed WEIF form</label>
                            </div>
                            <div class="col-span-4">
                                <input name="received_completed_weif_form_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_2_1" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="1">
                                        <label class="form-check-label" for="E_R_2_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_2_0" class="form-check-input" type="radio" name="received_completed_weif_form_status" value="0">
                                        <label class="form-check-label" for="E_R_2_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work hours update by terms</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_hour_update_term_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_3_1" class="form-check-input" type="radio" name="work_hour_update_term_status" value="1">
                                        <label class="form-check-label" for="E_R_3_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_3_0" class="form-check-input" type="radio" name="work_hour_update_term_status" value="0">
                                        <label class="form-check-label" for="E_R_3_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook completed</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_complete_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_4_1" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="1">
                                        <label class="form-check-label" for="E_R_4_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_4_0" class="form-check-input" type="radio" name="work_exp_handbook_complete_status" value="0">
                                        <label class="form-check-label" for="E_R_4_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Work experience handbook checked</label>
                            </div>
                            <div class="col-span-4">
                                <input name="work_exp_handbook_checked_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_5_1" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="1">
                                        <label class="form-check-label" for="E_R_5_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_5_0" class="form-check-input" type="radio" name="work_exp_handbook_checked_status" value="0">
                                        <label class="form-check-label" for="E_R_5_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer handbook sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_handbook_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_6_1" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="1">
                                        <label class="form-check-label" for="E_R_6_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_6_0" class="form-check-input" type="radio" name="emp_handbook_sent_status" value="0">
                                        <label class="form-check-label" for="E_R_6_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employers letter sent</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_letter_sent_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_7_1" class="form-check-input" type="radio" name="emp_letter_sent_status" value="1">
                                        <label class="form-check-label" for="E_R_7_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_7_0" class="form-check-input" type="radio" name="emp_letter_sent_status" value="0">
                                        <label class="form-check-label" for="E_R_7_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Employer Confirmation Received</label>
                            </div>
                            <div class="col-span-4">
                                <input name="emp_confirm_rec_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_8_1" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="1">
                                        <label class="form-check-label" for="E_R_8_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_8_0" class="form-check-input" type="radio" name="emp_confirm_rec_status" value="0">
                                        <label class="form-check-label" for="E_R_8_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Company visit</label>
                            </div>
                            <div class="col-span-4">
                                <input name="company_visit_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_9_1" class="form-check-input" type="radio" name="company_visit_status" value="1">
                                        <label class="form-check-label" for="E_R_9_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_9_0" class="form-check-input" type="radio" name="company_visit_status" value="0">
                                        <label class="form-check-label" for="E_R_9_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of student meetings</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_std_meeting_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_10_1" class="form-check-input" type="radio" name="record_std_meeting_status" value="1">
                                        <label class="form-check-label" for="E_R_10_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_10_0" class="form-check-input" type="radio" name="record_std_meeting_status" value="0">
                                        <label class="form-check-label" for="E_R_10_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 mb-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Record of all contacts to student (Calls, Class visit, text, letter)</label>
                            </div>
                            <div class="col-span-4">
                                <input name="record_all_contact_student_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_11_1" class="form-check-input" type="radio" name="record_all_contact_student_status" value="1">
                                        <label class="form-check-label" for="E_R_11_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_11_0" class="form-check-input" type="radio" name="record_all_contact_student_status" value="0">
                                        <label class="form-check-label" for="E_R_11_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-12 gap-0 gap-x-4 items-center">
                            <div class="col-span-5">
                                <label class="form-label m-0">Email sent to employer</label>
                            </div>
                            <div class="col-span-4">
                                <input name="email_sent_emp_date" class="form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true" type="text" value="" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-span-3">
                                <div class="flex flex-col sm:flex-row">
                                    <div class="form-check mr-4">
                                        <input id="E_R_12_1" class="form-check-input" type="radio" name="email_sent_emp_status" value="1">
                                        <label class="form-check-label" for="E_R_12_1">Yes</label>
                                    </div>
                                    <div class="form-check mt-2 sm:mt-0">
                                        <input id="E_R_12_0" class="form-check-input" type="radio" name="email_sent_emp_status" value="0">
                                        <label class="form-check-label" for="E_R_12_0">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateWBL" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit WBL Profile Modal -->

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
    @vite('resources/js/student-workplacement.js')
    @vite('resources/js/student-wbl-profile.js')
@endsection