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
                    <div class="font-medium text-base">Employee Appraisal Document</div>
                </div>
                <div class="col-span-6 text-right">
                    <a href="{{ route('employee.appraisal', $employee->id) }}" class="add_btn btn btn-success shadow-md ml-auto mr-2 text-white">Back to Appraisal</a>
                    <button data-tw-toggle="modal" data-tw-target="#addAppraisalDocModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Upload Document</button>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
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
                        <div id="employeeAppraisalDocListTable" data-employee="{{ $employee->id }}" data-appraisal="{{ $appraisal->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Edit New Request Modal -->
    <div id="addAppraisalDocModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Apprisal Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="block mb-1">Document Name <span class="text-danger">*</span></label>
                        <input type="text" name="document_name" value="" class="form-control w-full"/>
                    </div>
                    <form method="post"  action="{{ route('employee.appraisal.upload.documents') }}" class="dropzone" id="addAppraisalDocForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 1.
                            </div>
                        </div>
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="employee_appraisal_id" value="{{ $appraisal->id }}"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="mt-3">
                        <label>Hard Copy Checked?</label>
                        <div class="form-check mt-2">
                            <input id="hard_copy_check-1" class="form-check-input" type="radio" value="1" name="hard_copy_check_status" value="vertical-radio-chris-evans">
                            <label class="form-check-label" for="hard_copy_check-1">Yes</label>
                        </div>
                        <div class="form-check mt-2">
                            <input checked id="hard_copy_check-2" class="form-check-input" type="radio" value="0" name="hard_copy_check_status" value="vertical-radio-liam-neeson">
                            <label class="form-check-label" for="hard_copy_check-2">No</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadDocBtn" class="btn btn-primary w-auto">     
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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
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
                        <button type="button" data-action="NONE" class="warningCloser btn btn-primary w-24">Ok</button>
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
                        <button type="button" data-recordid="0" data-status="none" data-apprisal="{{ $appraisal->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->


@endsection

@section('script')
    @vite('resources/js/employee-appraisal-documents.js')
@endsection