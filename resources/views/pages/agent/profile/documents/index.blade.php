@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->full_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.agent.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Documents</div>
            </div>
            <div class="col-span-6 text-right relative">
                <div class="dropdown" id="uploadsDropdown">
                    <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="activity" class="w-4 h-4 mr-2"></i>  Add Document List <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                    <div class="dropdown-menu w-72">
                        <ul class="dropdown-content">
                            <li><h6 class="dropdown-header">Document List</h6></li>
                            <li><hr class="dropdown-divider mt-0"></li>
                            @if(isset($docSettings) && !empty($docSettings) && $docSettings->count() > 0)
                                @foreach($docSettings as $ds)
                                    <li>
                                        <div class="form-check dropdown-item">
                                            <label class="inline-flex items-center cursor-pointer" for="employee_doc_{{ $ds->id }}"><i data-lucide="activity" class="w-4 h-4 mr-2"></i> {{ $ds->name }}</label>
                                            <input id="employee_doc_{{ $ds->id }}" name="employee_doc_ids[]" class="form-check-input employee_doc_ids ml-auto" type="radio" value="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                        </div>
                                    </li>
                                @endforeach
                            @else 
                                <li>
                                    <div class="alert alert-pending-soft show flex items-top mb-1 mt-1" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are no settings found!
                                    </div>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div class="flex p-1">
                                    <button type="button" id="employeeDocumentUploaders" class="btn btn-primary py-1 px-2 w-auto">     
                                        Upload Documents
                                    </button>
                                    <button type="button" id="closeUploadsDropdown" class="btn btn-secondary py-1 px-2 ml-auto">Close</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-UP" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-ED" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-ED" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-ED" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-ED" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
                <div class="flex mt-5 sm:mt-0">
                    <button id="tabulator-print-ED" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown w-1/2 sm:w-auto">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-ED" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                {{-- <li>
                                    <a id="tabulator-export-json-UP" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                    </a>
                                </li> --}}
                                <li>
                                    <a id="tabulator-export-xlsx-ED" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                                {{-- <li>
                                    <a id="tabulator-export-html-UP" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                    </a>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="employeeDocumentListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

    {{-- <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Communications</div>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-UP" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-EDC" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-EDC" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-EDC" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-EDC" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
                <div class="flex mt-5 sm:mt-0">
                    <button id="tabulator-print-EDC" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown w-1/2 sm:w-auto">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-EDC" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-EDC" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="employeeCommunicationDocumentListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div> --}}

    <!-- BEGIN: Import Modal -->
    <div id="uploadEmployeeDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('agent-user.documents.upload.documents') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="mt-3">
                        <label class="block mb-1">Document Name</label>
                        <span id="documentNameDisplay" class="block mb-1"></span>
                        <input type="text" name="doc_name" class="displayNameInput form-control w-full"/>
                    </div>
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
                    <button type="button" id="uploadEmpDocBtn" class="btn btn-primary w-auto">     
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

    <!-- BEGIN: Confirm Confirm Modal Content -->
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
                        <button type="button" data-recordid="0" data-status="none" data-employee="{{ $employee->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('pages.agent.profile.show-modals')
@endsection
@section('script')
    @vite('resources/js/agent-global.js')
    @vite('resources/js/agent-upload.js')
@endsection