@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new version, as in its design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/attendance-report.css')
@endsection

@section('subcontent')
    {{-- New version (user's design, 24 Nov 2026) beside the old one until the old report is retired.
         Toggle buttons, not role="tab": the theme's tab script grabs every [role=tab]. --}}
    <div class="atr" data-atr>
        {{-- Page title as on Intake Performance Reports (user, 24 Nov 2026): icon tile, title, one line under it
             (a description, then the search's summary), actions on the right with the version switch. --}}
        <div class="atr-head">
            <div class="atr-head__lead">
                <span class="atr-head__icon" aria-hidden="true"><i data-lucide="calendar-check"></i></span>
                <div class="atr-head__text">
                    <h1 class="atr-head__title">Attendance Report</h1>
                    <p class="atr-head__sub" data-atr-summary data-default="Each student's attendance for the intakes and semesters you choose. Pick a semester and search.">Each student's attendance for the intakes and semesters you choose. Pick a semester and search.</p>
                </div>
            </div>
            <div class="atr-actions">
                <div class="atr-versions" role="group" aria-label="Report version">
                    <button type="button" class="is-active" aria-pressed="true" data-atr-version="new">New version</button>
                    <button type="button" aria-pressed="false" data-atr-version="old">Old version</button>
                </div>
                <a class="atr-back" href="{{ route('dashboard') }}">
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Back to Dashboard
                </a>
                <button class="atr-btn atr-btn--navy" type="button" disabled data-atr-export data-atr-new-only>
                    <svg class="atr-btn__icon" aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>
                    <span class="atr-spinner" aria-hidden="true"></span>
                    Export Excel
                </button>
            </div>
        </div>

        <div data-atr-pane="new">
            @include('pages.reports.attendance.new-version')
        </div>

        <div data-atr-pane="old" hidden>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5">
        <form action="#" method="post" id="studentGroupSearchForm">
            @csrf
            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                <div class="col-span-12 sm:col-span-3">
                    <label for="intake_semester" class="form-label">Intake Semester <span class="text-danger">*</span></label>
                    <select id="intake_semester" class="w-full tom-selects" multiple name="params[intake_semester][]">
                        <option value="">Please Select</option>
                        @if(!empty($semesters))
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-intake_semester text-danger mt-2"></div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="attendance_semester" class="form-label">Attendance Semester <span class="text-danger">*</span></label>
                    <select id="attendance_semester" class="w-full tom-selects" multiple name="params[attendance_semester][]">
                        <option value="">Please Select</option>
                        @if($terms->count() > 0)
                            @foreach($terms as $trm)
                                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="course" class="form-label">Course </label>
                    <select id="course" class="w-full tom-selects" multiple name="params[course][]">
                        <option value="">Please Select</option>
                        @if(!empty($courses))
                            @foreach($courses as $crs)
                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-course text-danger mt-2"></div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="group" class="form-label">Master Group</label>
                    <select id="group" class="w-full tom-selects" multiple name="params[group][]">
                        <option value="">Please Select</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="student_status" class="form-label">Student Status</label>
                    <select id="group_student_status" class="w-full tom-selects" name="params[group_student_status][]" multiple>
                        <option value="">Please Select</option>
                        @if(!empty($allStatuses))
                            @foreach($allStatuses as $sts)
                                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="attendance_percentage" class="form-label">Percentage</label>
                    <select class="form-control w-full" name="params[attendance_percentage]" id="attendance_percentage">
                        <option value="">Please Select</option>
                        <option value="0.00">less than or equal 0%</option>
                        <option value="10.00">less than 10%</option>
                        <option value="20.00">less than 20%</option>
                        <option value="30.00">less than 30%</option>
                        <option value="40.00">less than 40%</option>
                        <option value="50.00">less than 50%</option>
                        <option value="60.00">less than 60%</option>
                        <option value="70.00">less than 70%</option>
                        <option value="80.00">less than 80%</option>
                        <option value="90.00">less than 90%</option>
                        <option value="100.00">less than 100%</option>       
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 ml-auto mt-auto text-right">
                    <button type="button" id="studentGroupSearchBtn" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden pt-5 attendanceReportListTableWrap" style="display: none;">
            <div class="grid grid-cols-12 items-center" id="reportRowCountWrap">
                <div class="col-span-12 sm:col-span-6 items-center text-left reportTotalRowCount font-medium"></div>
                <div class="col-span-12 sm:col-span-6 text-right">
                    <div class="ml-auto inline-flex" id="communicationBtnsArea" style="display: none;">
                        <button type="button" class="sendBulkSmsBtn btn btn-pending shadow-md text-white mr-2"><i data-lucide="smartphone" class="w-4 h-4 mr-2"></i>Send SMS</button>
                        <button type="button" class="sendBulkMailBtn btn btn-success shadow-md text-white mr-2"><i data-lucide="mail" class="w-4 h-4 mr-2"></i>Send Email</button>
                        <button type="button" class="generateBulkLetterBtn btn btn-primary shadow-md text-white mr-1"><i data-lucide="mailbox" class="w-4 h-4 mr-2"></i>Generate Letter</button>
                    </div>
                    <button type="button" id="attendanceReportExcelBtn" class="btn btn-facebook w-auto">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 loading">
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
            <div id="attendanceReportListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
        </div>
    </div>

    {{--
        Page dialogs in the report's design (user, 24 Nov 2026), shared by both versions. The old
        script (student-attendance-reports.js) still drives them: every id, name and class it reads
        is kept (#sendSMSBtn svg = the first svg = the loader, .acc__input-error .error-*, .modal-body
        inputs it clears, .letterEditorArea, .commonSmtpWrap, .successModalTitle ...). Styles ride on
        .atr-modal because the theme moves a modal to <body> when it opens.
    --}}
    @php
        $atrLoader = '<svg class="atr-mbtn__spin" style="display: none;" width="16" height="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
    @endphp

    <!-- BEGIN: Send Letter Modal -->
    <div id="generateBulkLetterModal" class="modal atr-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog atr-modal__dialog--lg">
            <form method="POST" action="#" id="generateBulkLetterForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header atr-modal__head">
                        <span class="atr-modal__icon" aria-hidden="true"><i data-lucide="mailbox"></i></span>
                        <div class="atr-modal__titles">
                            <h2 class="atr-modal__title">Generate Letter</h2>
                            <p class="atr-modal__sub" data-atr-modal-to>For the selected students</p>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="atr-modal__close" aria-label="Close"><i data-lucide="x"></i></a>
                    </div>
                    <div class="modal-body atr-modal__body">
                        <div class="atr-mgrid">
                            <div class="atr-mfield">
                                <label for="issued_date">Issued Date <i>*</i></label>
                                <input id="issued_date" type="text" name="issued_date" value="<?php echo date('d-m-Y') ?>" class="datepicker form-control w-full atr-minput" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                                <div class="acc__input-error error-issued_date"></div>
                            </div>
                            <div class="atr-mfield">
                                <label for="signatory_id">Signatory</label>
                                <select id="signatory_id" name="signatory_id" class="form-control w-full atr-minput atr-mselect">
                                    <option value="">Please Select</option>
                                    @if(!empty($signatory))
                                        @foreach($signatory as $sg)
                                            <option value="{{ $sg->id }}">{{ $sg->signatory_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-signatory_id"></div>
                            </div>
                        </div>
                        <div class="atr-mfield">
                            <label for="letter_set_id">Letter <i>*</i></label>
                            <select id="letter_set_id" name="letter_set_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if(!empty($letterSet))
                                    @foreach($letterSet as $ls)
                                        <option value="{{ $ls->id }}">{{ $ls->letter_type.' - '.$ls->letter_title }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-letter_set_id"></div>
                        </div>
                        <div class="atr-mfield letterEditorArea" style="display: none;">
                            <div class="atr-mfield__row">
                                <label>Letter text</label>
                                <div class="atr-mtags">@include('pages.settings.letter.letter-tags')</div>
                            </div>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="letterEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-letter_body"></div>
                        </div>
                        <div class="atr-mswitch">
                            <div class="atr-mswitch__text">
                                <b>Send Email</b>
                                <span>Also email the letter to each student.</span>
                            </div>
                            <div class="form-check form-switch">
                                <input id="send_in_email" class="form-check-input" name="send_in_email" value="1" type="checkbox" aria-label="Send Email">
                            </div>
                        </div>
                        <div class="atr-mfield commonSmtpWrap" style="display: none;">
                            <label for="comon_smtp_id">SMTP <i>*</i></label>
                            <select id="comon_smtp_id" name="comon_smtp_id" class="form-control w-full atr-minput atr-mselect">
                                <option value="">Please Select</option>
                                @if(!empty($smtps))
                                    @foreach($smtps as $sm)
                                        <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-comon_smtp_id"></div>
                        </div>
                    </div>
                    <div class="modal-footer atr-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--ghost">Cancel</button>
                        <button type="submit" id="sendLetterBtn" class="atr-mbtn atr-mbtn--primary">
                            {!! $atrLoader !!}<i data-lucide="file-text" class="atr-mbtn__lead"></i>
                            Generate Letter
                        </button>
                        <input type="hidden" name="student_ids" value=""/>
                        <input type="hidden" name="print_pdf" value="1"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Letter Modal -->

    <!-- BEGIN: Send Mail Modal -->
    <div id="sendBulkMailModal" class="modal atr-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog atr-modal__dialog--lg">
            <form method="POST" action="#" id="sendBulkMailForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header atr-modal__head">
                        <span class="atr-modal__icon" aria-hidden="true"><i data-lucide="mail"></i></span>
                        <div class="atr-modal__titles">
                            <h2 class="atr-modal__title">Send Email</h2>
                            <p class="atr-modal__sub" data-atr-modal-to>To the selected students</p>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="atr-modal__close" aria-label="Close"><i data-lucide="x"></i></a>
                    </div>
                    <div class="modal-body atr-modal__body">
                        <div class="atr-mgrid">
                            <div class="atr-mfield">
                                <label for="comon_smtp_id">SMTP <i>*</i></label>
                                <select id="comon_smtp_id" name="comon_smtp_id" class="form-control w-full atr-minput atr-mselect">
                                    <option value="">Please Select</option>
                                    @if($smtps->count() > 0)
                                        @foreach($smtps as $sm)
                                            <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-comon_smtp_id"></div>
                            </div>
                            <div class="atr-mfield">
                                <label for="email_template_id">Template</label>
                                <select id="email_template_id" name="email_template_id" class="w-full tom-selects">
                                    <option value="">Please Select</option>
                                    @if($emailTemplates->count() > 0)
                                        @foreach($emailTemplates as $et)
                                            <option value="{{ $et->id }}">{{ $et->email_title }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="atr-mfield">
                            <label for="subject">Subject <i>*</i></label>
                            <input id="subject" type="text" name="subject" class="form-control w-full atr-minput">
                            <div class="acc__input-error error-subject"></div>
                        </div>
                        <div class="atr-mfield">
                            <label>Message <i>*</i></label>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="mailEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-body"></div>
                        </div>
                        <div class="atr-mfield">
                            <label for="sendMailsDocument" class="atr-mattach">
                                <i data-lucide="paperclip"></i>
                                <span><b>Add attachments</b> PDF, Word, Excel, PowerPoint, images or text</span>
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" class="atr-sr" id="sendMailsDocument"/>
                            <div id="sendMailsDocumentNames" class="sendMailsDocumentNames atr-mfiles" style="display: none"></div>
                        </div>
                    </div>
                    <div class="modal-footer atr-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--ghost">Cancel</button>
                        <button type="submit" id="sendEmailBtn" class="atr-mbtn atr-mbtn--primary">
                            {!! $atrLoader !!}<i data-lucide="send" class="atr-mbtn__lead"></i>
                            Send Email
                        </button>
                        <input type="hidden" name="student_ids" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Mail Modal -->

    <!-- BEGIN: Send SMS Modal -->
    <div id="sendBulkSmsModal" class="modal atr-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog">
            <form method="POST" action="#" id="sendBulkSmsForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header atr-modal__head">
                        <span class="atr-modal__icon" aria-hidden="true"><i data-lucide="smartphone"></i></span>
                        <div class="atr-modal__titles">
                            <h2 class="atr-modal__title">Send SMS</h2>
                            <p class="atr-modal__sub" data-atr-modal-to>To the selected students</p>
                        </div>
                        <a data-tw-dismiss="modal" href="javascript:;" class="atr-modal__close" aria-label="Close"><i data-lucide="x"></i></a>
                    </div>
                    <div class="modal-body atr-modal__body">
                        <div class="atr-mfield">
                            <label for="sms_template_id">Template</label>
                            <select id="sms_template_id" name="sms_template_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($smsTemplates->count() > 0)
                                    @foreach($smsTemplates as $st)
                                        <option value="{{ $st->id }}">{{ $st->sms_title }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="atr-mfield">
                            <label for="sms_subject">Subject <i>*</i></label>
                            <input id="sms_subject" type="text" name="subject" class="form-control w-full atr-minput">
                            <div class="acc__input-error error-subject"></div>
                        </div>
                        <div class="atr-mfield">
                            <div class="atr-mfield__row">
                                <label for="smsTextArea">SMS <i>*</i></label>
                                <span class="sms_countr atr-mcount" title="Characters left / messages">160 / 1</span>
                            </div>
                            <textarea maxlength rows="7" id="smsTextArea" name="sms" class="form-control w-full atr-minput"></textarea>
                            <div class="acc__input-error error-sms"></div>
                        </div>
                    </div>
                    <div class="modal-footer atr-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--ghost">Cancel</button>
                        <button type="submit" id="sendSMSBtn" class="atr-mbtn atr-mbtn--primary">
                            {!! $atrLoader !!}<i data-lucide="send" class="atr-mbtn__lead"></i>
                            Send SMS
                        </button>
                        <input type="hidden" name="student_ids" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send SMS Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal atr-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog atr-modal__dialog--alert">
            <div class="modal-content">
                <div class="modal-body atr-alert">
                    <span class="atr-alert__icon atr-alert__icon--ok" aria-hidden="true"><i data-lucide="check"></i></span>
                    <div class="atr-alert__title successModalTitle"></div>
                    <div class="atr-alert__desc successModalDesc"></div>
                </div>
                <div class="atr-alert__foot">
                    <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--primary">OK</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal atr-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog atr-modal__dialog--alert">
            <div class="modal-content">
                <div class="modal-body atr-alert">
                    <span class="atr-alert__icon atr-alert__icon--danger" aria-hidden="true"><i data-lucide="alert-triangle"></i></span>
                    <div class="atr-alert__title confModTitle">Are you sure?</div>
                    <div class="atr-alert__desc confModDesc"></div>
                </div>
                <div class="atr-alert__foot">
                    <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--ghost">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWith atr-mbtn atr-mbtn--danger">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal atr-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog atr-modal__dialog atr-modal__dialog--alert">
            <div class="modal-content">
                <div class="modal-body atr-alert">
                    <span class="atr-alert__icon atr-alert__icon--warn" aria-hidden="true"><i data-lucide="alert-circle"></i></span>
                    <div class="atr-alert__title warningModalTitle">Oops!</div>
                    <div class="atr-alert__desc warningModalDesc"></div>
                </div>
                <div class="atr-alert__foot">
                    <button type="button" data-tw-dismiss="modal" class="atr-mbtn atr-mbtn--ghost">OK, Got it</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-attendance-reports.js')
    @vite('resources/js/attendance-report-new.js')
@endsection