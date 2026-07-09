{{--
    Global quick Send-Email / Send-SMS popups for student profile pages.
    Rendered wherever the student profile header (show-info) appears, so the
    header email/phone can trigger these popups from any student/* profile page.

    NOTE: This partial is intentionally NOT included on the Communication page,
    which has its own #sendEmailModal / #smsSMSModal and JS. The marker element
    #quickCommModals below is what student-quick-communication.js keys off, so the
    module stays a no-op on pages that don't render this partial.

    Data ($smtps, $emailTemplates, $smsTemplates) is supplied by a View Composer
    (see AppServiceProvider) so every profile controller need not pass it.
--}}
@isset($student)
<div id="quickCommModals">
    <!-- BEGIN: Send SMS Modal -->
    <div id="smsSMSModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="smsSMSForm" enctype="multipart/form-data">
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
                            <select id="sms_template_id" name="sms_template_id" class="w-full qc-select">
                                <option value="">Please Select</option>
                                @if(isset($smsTemplates) && $smsTemplates->count() > 0)
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
                        <div class="form-check form-switch" style="margin-right: auto; display: flex; align-items: center;">
                            <label class="form-check-label mr-3 ml-0" for="show_as_news">Show As News</label>
                            <input id="show_as_news" class="form-check-input m-0" name="show_as_news" value="1" type="checkbox">
                        </div>
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send SMS Modal -->

    <!-- BEGIN: Send Email Modal -->
    <div id="sendEmailModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="sendEmailForm" enctype="multipart/form-data">
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
                                @if(!empty($smtps))
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
                        <div class="mt-3">
                            <label for="bcc_email" class="form-label">BCC</label>
                            <input id="bcc_emails" type="text" name="bcc_emails" class="form-control w-full" placeholder="exmp@domain.com,exmp@domain.com....">
                            <div class="text-xs text-slate-500 mt-1">Please use comma (,) seperator for multiple emails.</div>
                        </div>
                        <div class="mt-3 mb-4">
                            <label for="email_template_id" class="form-label">Template</label>
                            <select id="email_template_id" name="email_template_id" class="w-full qc-select">
                                <option value="">Please Select</option>
                                @if(!empty($emailTemplates))
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Email Modal -->

    <!-- BEGIN: Quick-comm Success Modal -->
    <div id="qcSuccessModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 qcSuccessTitle"></div>
                        <div class="text-slate-500 mt-2 qcSuccessDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="qcSuccessCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Quick-comm Success Modal -->

    <!-- BEGIN: Quick-comm Warning Modal -->
    <div id="qcWarningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 qcWarningTitle"></div>
                        <div class="text-slate-500 mt-2 qcWarningDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="qcWarningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Quick-comm Warning Modal -->
</div>
@endisset
