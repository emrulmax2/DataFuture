@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->

    @include('pages.students.admission.show-info')

    <!-- END: Profile Info -->
    <div class="adm-sections adm-comm adm-uploads">
    <div class="adm-section">
        <div class="adm-section__head">
            <div class="adm-section__title">Documents</div>
            <div class="adm-tabletools__actions">
                <div class="dropdown adm-docmenu" id="uploadsDropdown" data-tw-placement="bottom-end">
                    <button type="button" class="dropdown-toggle adm-btn adm-btn--primary adm-docmenu__trigger" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                        Add Document List
                    </button>
                    <div class="dropdown-menu adm-docmenu__menu">
                        <ul class="dropdown-content adm-docmenu__panel">
                            <li class="adm-docmenu__head">Document List</li>
                            @if(isset($docSettings) && !empty($docSettings))
                                @foreach($docSettings as $ds)
                                    <li>
                                        <div class="form-check dropdown-item adm-docmenu__item">
                                            <input id="applicant_doc_{{ $ds->id }}" name="applicant_doc_ids[]" class="form-check-input applicant_doc_ids adm-docmenu__radio" type="radio" value="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                            <label class="adm-docmenu__label" for="applicant_doc_{{ $ds->id }}">
                                                <span class="adm-docmenu__icon"><i data-lucide="activity" class="w-4 h-4"></i></span>
                                                <span class="adm-docmenu__text">{{ $ds->name }}</span>
                                                <span class="adm-docmenu__chip">
                                                    <i data-lucide="x" class="adm-docmenu__chip-x w-3 h-3"></i>
                                                    <i data-lucide="check" class="adm-docmenu__chip-check w-3 h-3"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                            <li class="adm-docmenu__foot">
                                <div>
                                    <button type="button" id="applicantDocumentUploaders" class="adm-btn adm-btn--primary">
                                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        Upload Documents
                                    </button>
                                    <button type="button" id="closeUploadsDropdown" class="adm-btn adm-btn--soft">Close</button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="adm-sectionbody">
            <div class="adm-tabletools">
                <form id="tabulatorFilterForm-UP" class="adm-tabletools__filters">
                    <span class="adm-filter-text">Query</span>
                    <input id="query-UP" name="query" type="text" class="adm-input" placeholder="Search...">
                    <span class="adm-filter-text">Status</span>
                    <div class="adm-field adm-field--narrow">
                        <select id="status-UP" name="status" class="adm-select">
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                        <svg class="adm-field__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                    </div>
                    <button id="tabulator-html-filter-go-UP" type="button" class="adm-btn adm-btn--primary">Go</button>
                    <button id="tabulator-html-filter-reset-UP" type="button" class="adm-btn adm-btn--soft">Reset</button>
                </form>
                <div class="adm-tabletools__actions">
                    <button type="button" id="tabulator-print-UP" class="adm-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>Print</button>
                    <div class="dropdown adm-dropdown">
                        <button type="button" class="dropdown-toggle adm-btn" aria-expanded="false" data-tw-toggle="dropdown"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>Export<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg></button>
                        <div class="dropdown-menu">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-UP" href="javascript:;" class="dropdown-item">Export CSV</a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-UP" href="javascript:;" class="dropdown-item">Export XLSX</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="adm-table-wrap">
                <div id="applicantUploadListTable" data-applicant="{{ $applicant->id }}" class="table-report table-report--tabulator"></div>
            </div>
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
                    <form method="post" action="{{ route('admission.upload.documents') }}" class="dropzone adm-upload-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <span class="adm-upload-dropzone__icon"><i data-lucide="upload-cloud" class="w-6 h-6"></i></span>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500 adm-upload-dropzone__hint">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="adm-upload-name mt-3">
                        <label class="form-label">Document Name</label>
                        <span id="documentNameDisplay" class="block mb-1"></span>
                        <input type="text" name="display_name" value="" class="displayNameInput form-control w-full"/>
                    </div>
                    <div class="adm-upload-hardcopy mt-3">
                        <label class="form-label">Hard Copy Checked?</label>
                        <div class="adm-upload-choice-group">
                            <label class="adm-upload-choice" for="hard_copy_check-1">
                                <input id="hard_copy_check-1" class="form-check-input" type="radio" value="1" name="hard_copy_check_status">
                                <span class="adm-upload-choice__chip"><i data-lucide="check" class="w-4 h-4"></i></span>
                                <span>Yes</span>
                            </label>
                            <label class="adm-upload-choice" for="hard_copy_check-2">
                                <input checked id="hard_copy_check-2" class="form-check-input" type="radio" value="0" name="hard_copy_check_status">
                                <span class="adm-upload-choice__chip"><i data-lucide="x" class="w-4 h-4"></i></span>
                                <span>No</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadDocBtn" class="btn btn-primary w-auto">
                        Upload
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="adm-btn-loader w-4 h-4 ml-2">
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
                        <button type="button" data-recordid="0" data-status="none" data-applicant="{{ $applicant->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/admission-uploads.js')
    @vite('resources/js/admission-vue.js')
@endsection
