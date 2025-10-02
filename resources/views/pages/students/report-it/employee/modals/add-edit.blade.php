    <!-- BEGIN: New Order Modal -->
    <div id="addModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            
            <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Create New Report IT</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label for="add_issue_type_id" class="form-label">What Type of Issue you are facing? <span class="text-danger">*</span></label>
                        
                        @foreach($issueList as $issue)
                        <div class="flex flex-col sm:flex-row mt-2 {{ $issue->availability }}_class">
                            <div class="form-check mr-3">
                                <input  id="add_issue_type_id{{ $issue->id }}" class="form-check-input" type="radio" name="issue_type_id" value="{{ $issue->id }}">
                                <label class="form-check-label" for="add_issue_type_id{{ $issue->id }}">{{ $issue->name }} </label>
                            </div>
                        </div>
                        @endforeach

                        <div class="acc__input-error error-issue_type_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12">
                        <label for="add_venue_id" class="form-label">Which Campus has the issue?<span class="text-danger">*</span></label>
                        <select id="add_venue_id" class="lcc-tom-select w-full" name="venue_id">
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                    </div>

                    <div class="col-span-12">
                        <label for="add_location" class="form-label">Location of Issue (Room/Area/Desk Number)<span class="text-danger">*</span></label>
                        <input type="text" id="add_location" class="form-control" name="location" placeholder="e.g. Room 101, Library, etc.">
                        <div class="acc__input-error error-location text-danger mt-2"></div>
                    </div>

                    <div class="col-span-12">
                        <label for="add_description" class="form-label">Description of Issue <span class="text-danger">*</span></label>
                        <textarea id="add_description" name="description" class="form-control" placeholder="Please provide as much detail as possible" rows="5"></textarea>
                        <div class="acc__input-error error-description text-danger mt-2"></div>
                    </div>

                    <div class="col-span-12">
                        <a href="javascript:void(0)" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal" class="w-full flex text-cyan-700 font-medium"><i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Attachments</a>
                        <div class="acc__input-error error-documents text-danger mt-2"></div>
                    </div>
                    <div id="addDocumenthiddenInput">
                        
                    </div>
                    <div id="addItems" class="hidden col-span-12 w-full mt-3 xl:mt-0 flex-1 border-2 border-dashed dark:border-darkmode-400 rounded-md py-4">
                        <div id="AddItemBox" class="grid grid-cols-10 gap-5 pl-4 pr-5"></div>
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
                    </button>
                    <input type="hidden" id="student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                    <input type="hidden" id="employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <input type="hidden" id="created_by" name="created_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                    <input type="hidden" name="status" value="Pending" />

                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- END: New Order Modal -->

    <div id="uploadDocumentModal" class="modal" tabindex="-1" aria-hidden="true">
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
    </div>
    <!-- END: New Upload Document Modal -->

    <!-- BEGIN: New Edit Modal -->
    <div id="editModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Report IT</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 mb-3">
                        <label for="edit_issue_type_id" class="form-label">What Type of Issue you are facing?</label>
                        

                        @foreach($issueList as $issue)
                        <div class="flex flex-col sm:flex-row mt-2 {{ $issue->availability }}_class">
                            <div class="form-check mr-3">
                                <input  id="edit_issue_type_id_{{ $issue->id }}" class="form-check-input" type="radio" name="issue_type_id" value="{{ $issue->id }}">
                                <label class="form-check-label" for="edit_issue_type_id_{{ $issue->id }}">{{ $issue->name }} </label>
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="acc__input-error error-issue_type_id text-danger mt-2"></div>
                    </div>

                    <div class="col-span-12 mb-3">
                        <label for="edit_venue_id" class="form-label">Which Campus has the issue?</label>
                        <select id="edit_venue_id" class="lcc-tom-select w-full" name="venue_id">
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                            @endforeach
                        </select>

                        <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 mb-3">
                        <label for="edit_location" class="form-label">Location of Issue (Room/Area/Desk Number)</label>
                        <input type="text" id="edit_location" class="form-control" name="location" placeholder="e.g. Room 101, Library, etc.">

                        <div class="acc__input-error error-location text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12 mb-3">
                        <label for="edit_description" class="form-label">Description of Issue</label>
                        <textarea id="edit_description" name="description" class="form-control" placeholder="Please , Provide details of the issue you are experiencing" rows="5"></textarea>

                        <div class="acc__input-error error-description text-danger mt-2"></div>
                    </div>
                    <div class="col-span-12">
                        <a href="javascript:void(0)" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal" class="w-full flex text-cyan-700 font-medium"><i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Attachments</a>
                        <div class="acc__input-error error-documents text-danger mt-2"></div>
                    </div>

                    <div id="editDocumenthiddenInput">
                        
                    </div>
                    <div id="editItems" class="hidden col-span-12 w-full mt-3 xl:mt-0 flex-1 border-2 border-dashed dark:border-darkmode-400 rounded-md py-4">
                        <div id="editItemBox" class="grid grid-cols-10 gap-5 pl-4 pr-5"></div>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <input type="hidden" id="edit_student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                    <input type="hidden" id="edit_employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <input type="hidden" id="edit_updated_by" name="updated_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
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