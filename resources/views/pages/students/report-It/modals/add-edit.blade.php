    <!-- BEGIN: New Order Modal -->
    <div id="addModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Log</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    
                    <div class="col-span-12">
                        <label for="add_description" class="form-label">Description of Issue <span class="text-danger">*</span></label>
                        <textarea id="add_description" name="description" class="form-control" placeholder="Please provide as much detail as possible" rows="5"></textarea>
                        <div class="acc__input-error error-description text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-32 mr-1">Cancel</button>
                    <button type="submit" id="save"  class="btn btn-primary w-32"> Save
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
                    <input type="hidden" id="created_by" name="created_by" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="status" value="In Progress" />
                    <input type="hidden" id="report_it_all_id" name="report_it_all_id" value="{{ $reportItAll->id ?? '' }}">
                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- END: New Order Modal -->

    {{-- <div id="uploadDocumentModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg ">
            <div class="modal-content">
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                <form method="post"  action="{{ route('report.it.all.upload') }}" class="dropzone col-span-12 rounded-md mt-3" id="addReportITUploadForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input type="file" name="documents[]" multiple>
                        </div>
                        <div class="dz-message py-5" data-dz-message>
                            <div class="text-lg font-medium flex justify-center"><i data-lucide="image" class="w-4 h-4 mr-2 mt-2"></i> <span class="text-primary mr-1">Upload images</span> or drag and drop</div>
                            <div class="text-slate-500">
                                Max file size 10MB & max file limit 5.
                            </div>
                        </div>
                        <input type="hidden" id="student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                        <input type="hidden" id="employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                        
                        <input type="hidden" id="uploaded_by" name="uploaded_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                </form>
            
                </div>
                <div class="modal-footer text-right flex border-none pb-8">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary btn-sm  w-full mr-2">Cancel</button>
                    <button type="button" id="uploadBtn" class="btn btn-success w-full  text-white"> Upload
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
    </div> --}}

    <!-- END: New Upload Document Modal -->

    <!-- BEGIN: New Edit Modal -->

    <div id="editModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Log</h2>
                    </div>
                    <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                        
                        <div class="col-span-12 mb-3">
                            <label for="edit_description" class="form-label">Description of Issue</label>
                            <textarea id="edit_description" name="description" class="form-control" placeholder="Please , Provide details of the issue you are experiencing" rows="5"></textarea>

                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer text-right">
                        <input type="hidden" id="edit_updated_by" name="updated_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                        
                        <input type="hidden" id="edit_report_it_all_id" name="report_it_all_id" value="{{ $reportItAll->id ?? '' }}">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-32 mr-1">Cancel</button>
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

    <!-- END: New Edit Modal -->