
<div id="editPunchNumberDeteilsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editPunchNumberDeteilsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Do you want to start the class now ?</div>
                    </div>
                    <input id="employee_punch_number" type="hidden" value="" class="form-control rounded  form-control-lg" name="punch_number" aria-label="default input example">
                    <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-danger text-white w-auto mr-1">No</button>
                    <button type="submit" id="savePD" class="btn btn-success text-white w-20 save">     
                        Yes                      
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
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.startClass') }}" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Punch Modal -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="confirmModalForm" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Different Tutor ?</div>
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">Please Put a note Below, why are you taking this class?</div>
                    
                    <div class="relative w-full min-w-[200px]">
                        <textarea id="note" class="peer h-full focus:ring-0 focus:ring-offset-0 min-h-[100px] w-full resize-none border-b border-0 border-blue-gray-200 bg-transparent pt-4 pb-1.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border-blue-gray-200 focus:border-pink-500 focus:outline-0 disabled:resize-none disabled:border-0 disabled:bg-blue-gray-50"
                          placeholder=" "name = "note"></textarea>
                        <label id="note" class="after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[14px] font-normal leading-tight text-blue-gray-500 transition-all after:absolute after:-bottom-0 after:block after:w-full after:scale-x-0 after:border-b-2 after:border-pink-500 after:transition-transform after:duration-300 peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[4.25] peer-placeholder-shown:text-blue-gray-500 peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-pink-500 peer-focus:after:scale-x-100 peer-focus:after:border-pink-500 peer-disabled:text-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
                          Type Here
                        </label>
                        
                    </div>
                    <div class="acc__input-error error-note text-danger mt-2"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                    <input type="hidden" name="start_class" value="1" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                    <input type="hidden" name="type" value="1"/>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>

                    <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                        Yes, I confirm
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
        </form>
    </div>
</div>

<div id="startClassConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="startClassConfirmModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Confirmed Class Teacher</div>
                        <div class="text-slate-500 mt-2 mb-2 confModDesc">Do you want to start this class?</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                        <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                        <input type="hidden" value="Class Started By {{ $employee->full_name }}" name="note"/>
                        
                        <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                        <input type="hidden" name="start_class" value="1" />
                        <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <input type="hidden" name="type" value="1"/>

                        <button type="submit" data-id="0" data-action="none" class="save btn btn-success w-auto text-white">
                            Yes, Start Class
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
        </form>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->


<!-- BEGIN: Delete Confirm Modal Content -->
<div id="endClassModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="endClassModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">End Now ?</div>
                        <div class="text-slate-500 mt-2 mb-2 confModDesc">Do you want to end this class?</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                        <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                        
                        <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                        <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>

                        <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                            Yes, I do
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
        </form>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<!-- BEGIN: Error Modal Content -->
<div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 errorModalTitle"></div>
                    <div class="text-slate-500 mt-2 errorModalDesc"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Error Modal Content -->
<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true" data-tw-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successModalTitle"></div>
                    <div class="text-slate-500 mt-2 successModalDesc"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Add Note Drawer -->
<div id="addNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addNoteForm" enctype="multipart/form-data">
            <div class="modal-content pt-note-drawer">
                <div class="pt-note-header relative">
                    <div class="pt-note-eyebrow">Tutor Note</div>
                    <a class="pt-note-close" data-tw-dismiss="modal" href="javascript:;" aria-label="Close note drawer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                    <div class="pt-note-student">
                        <span class="pt-note-avatar pt-note-student-initials">ST</span>
                        <div class="min-w-0">
                            <div class="pt-note-student-name">Selected student</div>
                            <div class="pt-note-student-meta">
                                <span class="pt-note-student-reg">Student ID</span>
                                <span> &middot; </span>
                                <span class="pt-note-student-attendance">attendance</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-body pt-note-body">
                    <div class="pt-note-section">
                        <div class="pt-note-grid">
                            <div>
                                <label for="note_term_declaration_id" class="pt-note-label">Term</label>
                                <select id="note_term_declaration_id" class="w-full tom-selects" name="term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if($termdeclarations->count() > 0)
                                        @foreach($termdeclarations as $trm)
                                            <option {{ isset($current_term->id) && $current_term->id == $trm->id ? 'Selected' : '' }} value="{{ $trm->id }}">{{ $trm->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div>
                                <label for="opening_date" class="pt-note-label">Date</label>
                                <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="opening_date" class="pt-note-field datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-note-section">
                        <label for="content" class="pt-note-label">Note</label>
                        <div class="editor document-editor pt-note-editor">
                            <div class="document-editor__toolbar"></div>
                            <div class="document-editor__editable-container">
                                <div class="document-editor__editable" id="addEditor"></div>
                            </div>
                        </div>
                        <div class="acc__input-error error-content text-danger mt-2"></div>
                    </div>

                    <div class="pt-note-section">
                        <span class="pt-note-label">Attachment</span>
                        <label for="addNoteDocument" class="pt-note-upload">
                            <i data-lucide="paperclip" class="w-4 h-4"></i>
                            <span>Upload supporting document</span>
                        </label>
                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addNoteDocument"/>
                        <span id="addNoteDocumentName" class="documentNoteName pt-note-document-name"></span>
                    </div>

                    <label class="pt-note-check">
                        <input type="checkbox" checked>
                        <span>Notify student by email</span>
                    </label>
                </div>

                <div class="modal-footer pt-note-footer">
                    <button type="submit" id="saveNote" class="pt-note-save">
                        Save note
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
                    <button type="button" data-tw-dismiss="modal" class="pt-note-cancel">Cancel</button>
                    <input type="hidden" name="student_id" value="0"/>
                    <input type="hidden" name="attendance_ids" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Note Drawer -->

<!-- BEGIN: SMS Drawer -->
<div id="smsSMSModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="smsSMSForm" enctype="multipart/form-data">
            <div class="modal-content pt-note-drawer">
                <div class="pt-note-header relative">
                    <div class="pt-note-eyebrow">Tutor SMS</div>
                    <a class="pt-note-close" data-tw-dismiss="modal" href="javascript:;" aria-label="Close SMS drawer">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                    <div class="pt-note-student">
                        <span class="pt-note-avatar pt-sms-student-initials">ST</span>
                        <div class="min-w-0">
                            <div class="pt-note-student-name pt-sms-student-name">Selected student</div>
                            <div class="pt-note-student-meta">
                                <span class="pt-sms-student-reg">Student ID</span>
                                <span> &middot; </span>
                                <span class="pt-sms-student-attendance">attendance</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-body pt-note-body">
                    <div class="pt-note-section">
                        <label for="sms_template_id" class="pt-note-label">Template</label>
                        <select id="sms_template_id" name="sms_template_id" class="w-full tom-selects">
                            <option value="">Please Select</option>
                            @if($smsTemplates->count() > 0)
                                @foreach($smsTemplates as $st)
                                    <option value="{{ $st->id }}">{{ $st->sms_title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="pt-note-section">
                        <label for="sms_subject" class="pt-note-label">Subject</label>
                        <input id="sms_subject" type="text" name="subject" class="pt-note-field" placeholder="Message subject">
                        <div class="acc__input-error error-subject text-danger mt-2"></div>
                    </div>

                    <div class="pt-note-section">
                        <div class="flex justify-between items-center">
                            <label for="smsTextArea" class="pt-note-label mb-0">Message</label>
                            <span class="sms_countr pt-sms-counter">160 / 1</span>
                        </div>
                        <textarea maxlength rows="7" id="smsTextArea" name="sms" class="pt-note-field mt-2" placeholder="Write your SMS message..."></textarea>
                        <div class="acc__input-error error-sms text-danger mt-2"></div>
                    </div>

                    <label class="pt-note-check">
                        <input type="checkbox" checked disabled>
                        <span>Send via SMS gateway</span>
                    </label>
                </div>

                <div class="modal-footer pt-note-footer">
                    <button type="submit" id="sendSMSBtn" class="pt-note-save">
                        Send SMS
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
                    <button type="button" data-tw-dismiss="modal" class="pt-note-cancel">Cancel</button>
                    <input type="hidden" name="student_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: SMS Drawer -->
