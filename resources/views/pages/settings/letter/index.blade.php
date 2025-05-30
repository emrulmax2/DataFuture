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
                    <h2 class="font-medium text-base mr-auto">Letter Set</h2>
                    <button data-tw-toggle="modal" data-tw-target="#addLetterModal" type="button" class="add_btn btn btn-primary shadow-md mr-2">Add New Letter</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-LS" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-LS" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Phase</label>
                                <select id="phase-LS" name="phase" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="">All</option>
                                    <option value="admission">Admission</option>
                                    <option value="live">Live Student</option>
                                    <option value="hr">Human Resource</option>
                                    <option value="document_request">Document Request / ID replacement</option>
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-LS" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="3">All</option>
                                    <option selected value="1">Active</option>
                                    <option value="0">Inactive</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-LS" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-LS" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
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
                                            <a id="tabulator-export-csv-LS" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-LS" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="letterSettingsListTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Edit Modal -->
    <div id="editLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editLetterForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Letter</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="phase" class="form-label">Phase <span class="text-danger">*</span></label>
                            <div class="flex flex-col sm:flex-row">
                                <div class="form-check mr-4">
                                    <input id="edit_phase_admission" class="form-check-input phaseCheckboxs" name="phase[admission]" type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_phase_admission">Admission</label>
                                </div>
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="edit_phase_live" class="form-check-input phaseCheckboxs"  name="phase[live]" type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_phase_live">Live Student</label>
                                </div>
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="edit_phase_hr" class="form-check-input phaseCheckboxs" name="phase[hr]" type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_phase_hr">Human Resource</label>
                                </div>
                                
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="edit_phase_document_request" class="form-check-input phaseCheckboxs" name="phase[document_request]" type="checkbox" value="1">
                                    <label class="form-check-label" for="edit_phase_document_request">Document Request/ID Replacement</label>
                                </div>
                            </div>
                            <div class="acc__input-error error-phase text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_letter_type" class="form-label">Letter Type <span class="text-danger">*</span></label>
                            <input id="edit_letter_type" type="text" name="letter_type" class="form-control w-full">
                            <div class="acc__input-error error-letter_type text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_letter_title" class="form-label">Letter Title <span class="text-danger">*</span></label>
                            <input id="edit_letter_title" type="text" name="letter_title" class="form-control w-full">
                            <div class="acc__input-error error-letter_title text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between">
                                <label for="editEditor" class="form-label">Description <span class="text-danger">*</span></label>
                                @include('pages.settings.letter.letter-tags')
                            </div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="editEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="edit_status">Active</label>
                            <input id="edit_status" class="form-check-input m-0" name="status" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="editLetterSet" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

    <!-- BEGIN: Add Modal -->
    <div id="addLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addLetterForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Letter</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="phase" class="form-label">Phase <span class="text-danger">*</span></label>
                            <div class="flex flex-col sm:flex-row">
                                <div class="form-check mr-4">
                                    <input id="phase_admission" class="form-check-input phaseCheckboxs" name="phase[admission]" type="checkbox" value="1">
                                    <label class="form-check-label" for="phase_admission">Admission</label>
                                </div>
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="phase_live" class="form-check-input phaseCheckboxs"  name="phase[live]" type="checkbox" value="1">
                                    <label class="form-check-label" for="phase_live">Live Student</label>
                                </div>
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="phase_hr" class="form-check-input phaseCheckboxs" name="phase[hr]" type="checkbox" value="1">
                                    <label class="form-check-label" for="phase_hr">Human Resource</label>
                                </div>
                                <div class="form-check mr-4 mt-2 sm:mt-0">
                                    <input id="phase_document_request" class="form-check-input phaseCheckboxs" name="phase[document_request]" type="checkbox" value="1">
                                    <label class="form-check-label" for="phase_document_request">Document Request/ID Replacement From Student</label>
                                </div>
                            </div>
                            <div class="acc__input-error error-phase text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="letter_type" class="form-label">Letter Type <span class="text-danger">*</span></label>
                            <input id="letter_type" type="text" name="letter_type" class="form-control w-full">
                            <div class="acc__input-error error-letter_type text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="letter_title" class="form-label">Letter Title <span class="text-danger">*</span></label>
                            <input id="letter_title" type="text" name="letter_title" class="form-control w-full">
                            <div class="acc__input-error error-letter_title text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between">
                                <label for="addEditor" class="form-label">Description <span class="text-danger">*</span></label>
                                @include('pages.settings.letter.letter-tags')
                            </div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="addEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="status">Active</label>
                            <input id="status" class="form-check-input m-0" name="status" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveLetterSet" class="btn btn-primary w-auto">     
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
                        <button type="button" data-phase="" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/letter-set.js')
@endsection