@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Letters -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Letters</div>
            </div>
            <div class="col-span-6 text-right relative">
                <a href="{{ route('student.print.communications', [$student->id, 'all'] )}}" class="btn btn-success text-white shadow-md mr-2">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print All
                </a>
                <button data-tw-toggle="modal" data-tw-target="#addLetterModal" type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Send New Letter</button>
                <a href="{{ route('student.print.communications', [$student->id, 'letter'] )}}" class="btn btn-outline-secondary w-auto">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Letters
                </a>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-CML" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-CML" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-CML" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-CML" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-CML" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentCommLetterListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Letters -->

    <!-- BEGIN: Email -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Email</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#sendEmailModal" type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Send Email</button>
                <a href="{{ route('student.print.communications', [$student->id, 'email'] )}}" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print Emails
                </a>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-CME" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-CME" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-CME" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-CME" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-CME" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentCommEmailListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Email -->

    <!-- BEGIN: SMS -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">SMS</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#smsSMSModal" type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Send SMS</button>
                <a href="{{ route('student.print.communications', [$student->id, 'sms'] )}}" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print SMS
                </a>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-CMS" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-CMS" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-CMS" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-CMS" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-CMS" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentCommSMSListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: SMS -->

    <!-- BEGIN: Send Letter Modal -->
    <div id="addLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addLetterForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Send Letter</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="issued_date" class="form-label">Issued Date <span class="text-danger">*</span></label>
                            <input id="issued_date" type="text" name="issued_date" class="datepicker form-control w-full" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-issued_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="letter_set_id" class="form-label">Letter <span class="text-danger">*</span></label>
                            <select id="letter_set_id" name="letter_set_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if(!empty($letterSet))
                                    @foreach($letterSet as $ls)
                                        <option value="{{ $ls->id }}">{{ $ls->letter_title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-letter_set_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-5 letterEditorArea" style="display: none;">
                            <div class="flex justify-between mb-2">
                                <div class="ml-auto">@include('pages.settings.letter.letter-tags')</div>
                            </div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="letterEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-letter_body text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="signatory_id" class="form-label">Signatory</label>
                            <select id="signatory_id" name="signatory_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($signatory))
                                    @foreach($signatory as $sg)
                                        <option value="{{ $sg->id }}">{{ $sg->signatory_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-signatory_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <div class="form-check form-switch items-center">
                                <label class="form-check-label ml-0 mr-5" for="checkbox-switch-7">Send Email</label>
                                <input id="send_in_email" class="form-check-input" name="send_in_email" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 commonSmtpWrap" style="display: none;">
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
                    </div>
                    <div class="modal-footer">
                        {{--<div class="modal-footer-left pt-2" style="float: left;">
                            <div class="flex flex-col sm:flex-row">
                                <div class="form-check mr-5">
                                    <input checked id="is_send_email" name="is_email_or_attachment" class="form-check-input" type="radio" value="1">
                                    <label class="form-check-label" for="is_send_email">Send Email</label>
                                </div>
                                <div class="form-check mr-0">
                                    <input id="is_send_attachment" name="is_email_or_attachment" class="form-check-input" type="radio" value="2">
                                    <label class="form-check-label" for="is_send_attachment">Send Attachment</label>
                                </div>
                            </div>
                        </div>--}}
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="sendLetterBtn" class="btn btn-primary w-auto">     
                            Send Letter                      
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
    <!-- END: Send Letter Modal -->

    <!-- BEGIN: View Modal -->
    <div id="viewCommunicationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Vew Communication</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <div class="footerBtns" style="float: left"></div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Modal -->

    <!-- BEGIN: Add Modal -->
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

    <!-- BEGIN: Add Modal -->
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
                        <div class="mt-3 mb-4">
                            <label for="email_template_id" class="form-label">Template</label>
                            <select id="email_template_id" name="email_template_id" class="w-full tom-selects">
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-letter.js')
    @vite('resources/js/student-email.js')
    @vite('resources/js/student-sms.js')
@endsection