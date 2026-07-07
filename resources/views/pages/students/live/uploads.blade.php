@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box mt-5 student-profile-documents">
        <div class="student-profile-secthead">
            <div class="student-profile-secthead-title">
                <div class="font-medium text-base">Documents</div>
            </div>
            <div class="student-profile-secthead-actions student-profile-documents-actions">
                <button data-studentid="{{ $student->id }}" class="btn btn-outline-secondary" id="downloadIDCardBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2 lucide lucide-id-card-lanyard-icon lucide-id-card-lanyard"><path d="M13.5 8h-3"/><path d="m15 2-1 2h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h3"/><path d="M16.899 22A5 5 0 0 0 7.1 22"/><path d="m9 2 3 6"/><circle cx="12" cy="15" r="3"/></svg>
                    Print ID Card
                </button>
                <div class="dropdown inline-flex student-profile-documents-dropdown {{ isset(auth()->user()->priv()['document_add']) && auth()->user()->priv()['document_add'] == 1 ? '' : 'hidden' }}" id="uploadsDropdown">
                    <button class="dropdown-toggle btn student-profile-addbtn" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>  Add Document <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                    <div class="dropdown-menu w-72 student-profile-documents-menu">
                        <ul class="dropdown-content">
                            <li><h6 class="dropdown-header">Document List</h6></li>
                            <li><hr class="dropdown-divider mt-0"></li>
                            @if(isset($docSettings) && !empty($docSettings) && $docSettings->count() > 0)
                                @foreach($docSettings as $ds)
                                    <li>
                                        <div class="form-check dropdown-item">
                                            <label class="inline-flex items-center cursor-pointer" for="student_doc_{{ $ds->id }}"><i data-lucide="file-plus-2" class="w-4 h-4 mr-2"></i> {{ $ds->name }}</label>
                                            <input id="student_doc_{{ $ds->id }}" name="student_doc_ids[]" class="form-check-input student_doc_ids ml-auto" type="radio" value="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                        </div>
                                    </li>
                                @endforeach
                            @else 
                                <li>
                                    <div class="alert alert-pending-soft show flex items-top mb-1 mt-1" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are not setting found!
                                    </div>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <div class="flex p-1">
                                    <button type="button" id="studentDocumentUploaders" class="btn btn-primary py-1 px-2 w-auto">     
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
        <div class="intro-y">
            <div class="student-profile-tablefilter student-profile-documents-filter">
                <form id="tabulatorFilterForm-UP" class="student-profile-tablefilter-form" >
                    <input id="query-UP" name="query" type="text" class="form-control student-profile-tablefilter-search"  placeholder="Search documents...">
                    <select id="status-UP" name="status" class="form-select student-profile-tablefilter-status" >
                        <option selected value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                    <button id="tabulator-html-filter-go-UP" type="button" class="btn btn-primary" >Go</button>
                    <button id="tabulator-html-filter-reset-UP" type="button" class="btn btn-outline-secondary student-profile-tablefilter-reset" >Reset</button>
                </form>
                <div class="student-profile-tablefilter-actions hidden md:flex">
                    <button id="tabulator-print-UP" class="btn btn-outline-secondary">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-UP" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                {{-- <li>
                                    <a id="tabulator-export-json-UP" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                    </a>
                                </li> --}}
                                <li>
                                    <a id="tabulator-export-xlsx-UP" href="javascript:;" class="dropdown-item">
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
            <div class="student-profile-tablebody">
                <div id="studentUploadListTable" data-student="{{ $student->id }}" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>


    <!-- BEGIN: Import Modal -->
    <div id="uploadDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.upload.documents') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="mt-3">
                        <label class="form-label">Document Name</label>
                        <span id="documentNameDisplay" class="block mb-1"></span>
                        <input type="text" name="display_name" value="" class="displayNameInput form-control w-full"/>
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
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-upload.js')
@endsection
