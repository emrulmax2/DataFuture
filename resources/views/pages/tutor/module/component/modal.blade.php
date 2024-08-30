
<!-- BEGIN: Import Modal -->
<div id="addActivityModal" class="modal" size="xl" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class=" modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">SELECT AN ACTIVITY</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <div id="activit-contentlist" class="grid grid-cols-12 gap-5 mt-5 pt-5"></div>
            </div>
        </div>
    </div>
</div>
<!-- END: Import Modal -->
<!-- BEGIN: Import Modal -->
<div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('plan-taskupload.store') }}" class="dropzone" id="addStudentPhotoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input type="hidden" name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop file here or click to upload.</div>
                        <div class="text-slate-500">
                            Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                        </div>
                    </div>
                    <input type="hidden" name="plan_task_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button id="uploadStudentPhotoBtn" type="button" class="btn btn-outline-success w-20 mr-1">Upload
                    <span class="ml-2 h-4 w-4" style="display: none">
                        <svg class="w-full h-full" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform type="rotate" attributeName="transform" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </span>
                </button>
                <button type="button" data-tw-dismiss="modal" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
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
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 warningModalTitle">Oops!</div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">OK, Got it</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->

<!-- BEGIN: Send Mail Modal -->
<div id="sendBulkMailModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="sendBulkMailForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Send Email</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="comon_smtp_id" class="form-label">SMTP <span class="text-danger">*</span></label>
                        <select id="comon_smtp_id" name="comon_smtp_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if($smtps->count() > 0)
                                @foreach($smtps as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-comon_smtp_id text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input id="subject" type="text" name="subject" class="form-control w-full">
                        <div class="acc__input-error error-subject text-danger mt-2"></div>
                    </div>
                    <div class="mt-3 mb-4">
                        <label for="email_template_id" class="form-label">Template</label>
                        <select id="email_template_id" name="email_template_id" class="w-full tom-selects">
                            <option value="">Please Select</option>
                            @if($emailTemplates->count() > 0)
                                @foreach($emailTemplates as $et)
                                    <option value="{{ $et->id }}">{{ $et->email_title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <div class="editor document-editor">
                            <div class="document-editor__toolbar"></div>
                            <div class="document-editor__editable-container">
                                <div class="document-editor__editable" id="mailEditor"></div>
                            </div>
                        </div>
                        <div class="acc__input-error error-body text-danger mt-2"></div>
                    </div>
                    <div class="mt-3 flex justify-start items-center relative">
                        <label for="sendMailsDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                            <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Attachments
                        </label>
                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" class="absolute w-0 h-0 overflow-hidden opacity-0" id="sendMailsDocument"/>
                    </div>
                    <div id="sendMailsDocumentNames" class="sendMailsDocumentNames mt-3" style="display: none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="sendEmailBtn" class="btn btn-primary w-auto">     
                        Send Mail                      
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
                    <input type="hidden" name="student_ids" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Send Mail Modal -->

<!-- BEGIN: Send SMS Modal -->
<div id="sendBulkSmsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="sendBulkSmsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Send SMS</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="sms_template_id" class="form-label">Template</label>
                        <select id="sms_template_id" name="sms_template_id" class="w-full tom-selects">
                            <option value="">Please Select</option>
                            @if($smsTemplates->count() > 0)
                                @foreach($smsTemplates as $st)
                                    <option value="{{ $st->id }}">{{ $st->sms_title }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="sms_subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input id="sms_subject" type="text" name="subject" class="form-control w-full">
                        <div class="acc__input-error error-subject text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <div class="flex justify-between items-center">
                            <label for="smsTextArea" class="form-label">SMS <span class="text-danger">*</span></label>
                            <span class="sms_countr font-bold">160 / 1</span>
                        </div>
                        <textarea maxlength rows="7" id="smsTextArea" name="sms" class="form-control w-full"></textarea>
                        <div class="acc__input-error error-sms text-danger mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="sendSMSBtn" class="btn btn-primary w-auto">     
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
                    <input type="hidden" name="student_ids" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Send SMS Modal -->