@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
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
                    <label for="group_student_status" class="form-label">Student Status</label>
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

    <!-- BEGIN: Send Letter Modal -->
    <div id="generateBulkLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="generateBulkLetterForm" enctype="multipart/form-data">
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
                        <input type="hidden" name="student_ids" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Letter Modal -->

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
@endsection

@section('script')
    @vite('resources/js/student-group-search-form.js')
    @vite('resources/js/student-attendance-reports.js')
@endsection