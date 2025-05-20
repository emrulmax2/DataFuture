 <!-- BEGIN: Add Hour Modal -->
 <style>
    #workplacementDocumentsModal.modal .modal-dialog {
        width: 90% !important;
    }
 </style>
<div id="workplacementDocumentsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="intro-y">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm-UP" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                    <input id="query-UP" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                    <select id="status-UP" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option selected value="1">Active</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go-UP" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset-UP" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div>
                                <button class=" btn btn-primary" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal"><i data-lucide="activity" class="w-4 h-4 mr-2"></i>  Add Document</button>
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="studentWorkPlacementDocumentsTable" data-student="{{ $student->id }}" data-row="0" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
    <!-- END: Add Hour Modal -->

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
                    <form method="post"  action="{{ route('student.workplacement.documents.store') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
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
                        <input type="hidden" name="student_workplacement_id" value="0"/>
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