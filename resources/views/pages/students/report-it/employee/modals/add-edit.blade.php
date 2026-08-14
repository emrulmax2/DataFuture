{{-- The Midone modal JS re-parents a modal to <body> when it opens, so the
     `rit-modal` scope has to ride on the modal element itself. --}}

<!-- BEGIN: Create Report Modal -->
<div id="addModal" class="modal rit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Create new report</h2>
                    <button type="button" data-tw-dismiss="modal" aria-label="Close" class="rit-modal__close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="rit-field__label">What type of issue are you facing? <span class="rit-field__req">*</span></label>
                        <div class="rit-radios">
                            @foreach($issueList as $issue)
                                <label class="rit-radio {{ $issue->availability }}_class" for="add_issue_type_id{{ $issue->id }}">
                                    <input id="add_issue_type_id{{ $issue->id }}" class="rit-radio__input" type="radio" name="issue_type_id" value="{{ $issue->id }}">
                                    <span class="rit-radio__dot"></span>
                                    <span class="rit-radio__text">{{ $issue->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="acc__input-error error-issue_type_id rit-error"></div>
                    </div>

                    <div>
                        <label for="add_venue_id" class="rit-field__label">Which campus has the issue? <span class="rit-field__req">*</span></label>
                        <select id="add_venue_id" class="rit-select w-full" name="venue_id">
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-venue_id rit-error"></div>
                    </div>

                    <div>
                        <label for="add_location" class="rit-field__label">Location of issue (room / area / desk) <span class="rit-field__req">*</span></label>
                        <input type="text" id="add_location" class="rit-input" name="location" placeholder="e.g. Room 101, Library">
                        <div class="acc__input-error error-location rit-error"></div>
                    </div>

                    <div>
                        <label for="add_description" class="rit-field__label">Description of issue <span class="rit-field__req">*</span></label>
                        <textarea id="add_description" name="description" class="rit-textarea" placeholder="Please provide as much detail as possible"></textarea>
                        <div class="acc__input-error error-description rit-error"></div>
                    </div>

                    <div>
                        <a href="javascript:void(0)" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal" class="rit-linkbtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5.5v13M5.5 12h13"></path></svg>
                            Add attachments
                        </a>
                        <div class="acc__input-error error-documents rit-error"></div>
                    </div>

                    <div id="addDocumenthiddenInput"></div>

                    {{-- `#AddItemBox` keeps its capitalisation and its children keep
                         `.col-span-5`: the page JS appends previews here and finds
                         them by that class when removing an attachment. --}}
                    <div id="addItems" class="rit-thumbs hidden">
                        <div id="AddItemBox" class="rit-thumbs__grid"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                    <input type="hidden" id="employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <input type="hidden" id="created_by" name="created_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                    <input type="hidden" name="status" value="Pending" />
                    <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">Cancel</button>
                    <button type="submit" id="save" class="rit-btn rit-btn--solid">
                        Save
                        <svg style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
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
<!-- END: Create Report Modal -->

<!-- BEGIN: Upload Attachments Modal -->
<div id="uploadDocumentModal" class="modal rit-modal rit-modal--wide" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add attachments</h2>
                <button type="button" data-tw-dismiss="modal" aria-label="Close" class="rit-modal__close">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('report.it.all.upload') }}" class="dropzone rit-dropzone" id="addReportITUploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input type="file" name="documents[]" multiple>
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="rit-dropzone__title">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"></rect><circle cx="8.5" cy="9.5" r="1.8"></circle><path d="m4 17 5-5 4 4 3-2.5 4 3.5"></path></svg>
                            <em>Upload images</em> or drag and drop
                        </div>
                        <div class="rit-dropzone__hint">JPEG or PNG &middot; max 20&nbsp;MB each &middot; up to 10 files</div>
                    </div>
                    <input type="hidden" id="student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                    <input type="hidden" id="employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <input type="hidden" id="uploaded_by" name="uploaded_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">Cancel</button>
                <button type="button" id="uploadBtn" class="rit-btn rit-btn--solid">
                    Upload
                    <svg style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
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
<!-- END: Upload Attachments Modal -->

<!-- BEGIN: Update Report Modal -->
<div id="editModal" class="modal rit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Update report</h2>
                    <span class="editLoading rit-loading">
                        <svg viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>Loading&hellip;
                    </span>
                    <button type="button" data-tw-dismiss="modal" aria-label="Close" class="rit-modal__close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label class="rit-field__label">What type of issue are you facing?</label>
                        <div class="rit-radios">
                            @foreach($issueList as $issue)
                                <label class="rit-radio {{ $issue->availability }}_class" for="edit_issue_type_id_{{ $issue->id }}">
                                    <input id="edit_issue_type_id_{{ $issue->id }}" class="rit-radio__input" type="radio" name="issue_type_id" value="{{ $issue->id }}">
                                    <span class="rit-radio__dot"></span>
                                    <span class="rit-radio__text">{{ $issue->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="acc__input-error error-issue_type_id rit-error"></div>
                    </div>

                    <div>
                        <label for="edit_venue_id" class="rit-field__label">Which campus has the issue? <span class="rit-field__req">*</span></label>
                        <select id="edit_venue_id" class="rit-select w-full" name="venue_id">
                            <option value="">Please Select</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-venue_id rit-error"></div>
                    </div>

                    <div>
                        <label for="edit_location" class="rit-field__label">Location of issue (room / area / desk) <span class="rit-field__req">*</span></label>
                        <input type="text" id="edit_location" class="rit-input" name="location" placeholder="e.g. Room 101, Library">
                        <div class="acc__input-error error-location rit-error"></div>
                    </div>

                    <div>
                        <label for="edit_description" class="rit-field__label">Description of issue <span class="rit-field__req">*</span></label>
                        <textarea id="edit_description" name="description" class="rit-textarea" placeholder="Please provide as much detail as possible"></textarea>
                        <div class="acc__input-error error-description rit-error"></div>
                    </div>

                    <div>
                        <a href="javascript:void(0)" data-tw-toggle="modal" data-tw-target="#uploadDocumentModal" class="rit-linkbtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5.5v13M5.5 12h13"></path></svg>
                            Add attachments
                        </a>
                        <div class="acc__input-error error-documents rit-error"></div>
                    </div>

                    <div id="editDocumenthiddenInput"></div>

                    <div id="editItems" class="rit-thumbs hidden">
                        <div id="editItemBox" class="rit-thumbs__grid"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="edit_student_id" name="student_id" value="{{ isset($student) ? $student->id : '' }}">
                    <input type="hidden" id="edit_employee_id" name="employee_id" value="{{ isset($employee) ? $employee->id : '' }}">
                    <input type="hidden" id="edit_updated_by" name="updated_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                    <input type="hidden" name="id" value="0" />
                    <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">Cancel</button>
                    <button type="submit" id="update" class="rit-btn rit-btn--solid">
                        Update
                        <svg style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
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
<!-- END: Update Report Modal -->
