{{--
    Bulk Communication.

    Reached from the class plan tree with a "-" separated list of plan ids in
    the URL. Every endpoint, field name and hidden input below is the one
    `BulkCommunicationController` already expects — `student_ids` as a comma
    string, `letter_body` / `body` appended from the editors, `documents[]` for
    the attachments — so only the chrome is new.
--}}
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-card cm-tablecard">
                <div class="cm-tablecard__head cm-tablecard__head--divided">
                    <div class="cm-tablecard__titles" style="display:block;">
                        <h2 class="cm-tablecard__title cm-serif">Student List</h2>
                        <p class="cm-commhead__sub">
                            {{ $planCount }} {{ $planCount === 1 ? 'class plan' : 'class plans' }}
                            @if(!empty($planNames))
                                <span class="cm-commhead__dot">&middot;</span>{{ implode(', ', $planNames) }}
                            @endif
                        </p>
                    </div>

                    {{-- Revealed by `rowSelectionChanged` — there is nothing to
                         send to until somebody is ticked. --}}
                    <div class="cm-tablecard__actions" id="communicationBtnsArea" hidden>
                        <span class="cm-commcount" data-cm-selected></span>
                        <button type="button" class="sendBulkSmsBtn cm-btn cm-btn--pill cm-btn--sms">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"></rect><path d="M12 18h.01"></path></svg>
                            Send SMS
                        </button>
                        <button type="button" class="sendBulkMailBtn cm-btn cm-btn--pill cm-btn--mail">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path></svg>
                            Send Email
                        </button>
                        <button type="button" class="generateBulkLetterBtn cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H5.2L4 17.2z"></path><path d="M8 9h8M8 12h5"></path></svg>
                            Generate Letter
                        </button>
                    </div>
                </div>

                <div class="cm-commbar">
                    <button type="button" class="cm-btn cm-btn--ghost" data-cm-select-all>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                        Select all on page
                    </button>
                    <button type="button" class="cm-btn cm-btn--ghost" data-cm-select-none>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Clear selection
                    </button>

                    <span class="cm-commbar__spacer"></span>
                    <span class="cm-tablecard__count" data-cm-count="student"></span>
                </div>

                <div class="cm-tabulator-wrap">
                    <div id="communicationStudentListTable" class="cm-tabulator" data-plans="{{ $plans }}"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Send SMS                                                          --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="sendBulkSmsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
            <form method="POST" action="#" id="sendBulkSmsForm" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span data-cm-sms-eyebrow>Bulk message</span></div>
                            <h2 class="cm-modal__title cm-serif">Send SMS</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body cm-modal__body--grid2">
                        <div class="cm-field cm-field--span2">
                            <label for="sms_template_id">Template</label>
                            <select id="sms_template_id" name="sms_template_id" class="cm-select sms_template_id">
                                <option value="">Please Select</option>
                                @foreach($smsTemplates as $st)
                                    <option value="{{ $st->id }}">{{ $st->sms_title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label for="sms_subject">Subject <span>*</span></label>
                            <input id="sms_subject" type="text" name="subject" class="cm-input subject" placeholder="What is this message about?">
                            <div class="acc__input-error error-subject"></div>
                        </div>

                        <div class="cm-field cm-field--span2">
                            {{-- The counter is a sibling of the caption, not a
                                 child of it: `.cm-field > label span` is the
                                 required-marker rule and would paint it red. --}}
                            <div class="cm-labelrow">
                                <label for="smsTextArea">SMS <span>*</span></label>
                                {{-- Characters left / how many 160-char parts
                                     the message will be billed as. --}}
                                <span class="cm-smscount sms_countr">160 / 1</span>
                            </div>
                            <textarea id="smsTextArea" rows="6" name="sms" class="cm-input cm-textarea sms" placeholder="Type the message…"></textarea>
                            <div class="acc__input-error error-sms"></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="sendSMSBtn" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Send SMS
                        </button>
                        <input type="hidden" name="student_ids" value="">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Send Email                                                        --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="sendBulkMailModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
            <form method="POST" action="#" id="sendBulkMailForm" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span data-cm-mail-eyebrow>Bulk message</span></div>
                            <h2 class="cm-modal__title cm-serif">Send Email</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body cm-modal__body--grid2">
                        <div class="cm-field">
                            <label for="mail_comon_smtp_id">SMTP <span>*</span></label>
                            <select id="mail_comon_smtp_id" name="comon_smtp_id" class="cm-select comon_smtp_id">
                                <option value="">Please Select</option>
                                @foreach($smtps as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-comon_smtp_id"></div>
                        </div>

                        <div class="cm-field">
                            <label for="email_template_id">Template</label>
                            <select id="email_template_id" name="email_template_id" class="cm-select email_template_id">
                                <option value="">Please Select</option>
                                @foreach($emailTemplates as $et)
                                    <option value="{{ $et->id }}">{{ $et->email_title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label for="subject">Subject <span>*</span></label>
                            <input id="subject" type="text" name="subject" class="cm-input subject" placeholder="Email subject line">
                            <div class="acc__input-error error-subject"></div>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label>Message <span>*</span></label>
                            <div class="editor document-editor cm-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="mailEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-body"></div>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label>Attachments</label>
                            <div class="cm-upload">
                                <label for="sendMailsDocument" class="cm-btn cm-btn--ghost cm-upload__button">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M7 9l5-5 5 5M12 4v12"></path></svg>
                                    Choose files
                                </label>
                                <span class="cm-upload__hint">Images, PDF, Word, Excel or PowerPoint</span>
                                <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" id="sendMailsDocument" class="cm-upload__input">
                            </div>
                            <div id="sendMailsDocumentNames" class="cm-upload__files sendMailsDocumentNames" hidden></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="sendEmailBtn" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Send Email
                        </button>
                        <input type="hidden" name="student_ids" value="">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Generate Letter                                                   --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="generateBulkLetterModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
            <form method="POST" action="#" id="generateBulkLetterForm" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span data-cm-letter-eyebrow>Bulk letter</span></div>
                            <h2 class="cm-modal__title cm-serif">Generate Letter</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body cm-modal__body--grid2">
                        <div class="cm-field">
                            <label for="issued_date">Issued Date <span>*</span></label>
                            <input id="issued_date" type="text" name="issued_date" value="{{ date('d-m-Y') }}" class="cm-input datepicker issued_date" data-format="DD-MM-YYYY" data-single-mode="true" readonly>
                            <div class="acc__input-error error-issued_date"></div>
                        </div>

                        <div class="cm-field">
                            <label for="letter_set_id">Letter <span>*</span></label>
                            <select id="letter_set_id" name="letter_set_id" class="cm-select letter_set_id">
                                <option value="">Please Select</option>
                                @foreach($letterSet as $ls)
                                    <option value="{{ $ls->id }}">{{ $ls->letter_type.' - '.$ls->letter_title }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-letter_set_id"></div>
                        </div>

                        {{-- Revealed once a letter is picked, because the body
                             is the chosen letter's template. --}}
                        <div class="cm-field cm-field--span2 letterEditorArea" hidden>
                            <div class="cm-labelrow">
                                <label>Letter Body <span>*</span></label>
                                @include('pages.settings.letter.letter-tags')
                            </div>
                            <div class="editor document-editor cm-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="letterEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-letter_body"></div>
                        </div>

                        <div class="cm-field">
                            <label for="signatory_id">Signatory</label>
                            <select id="signatory_id" name="signatory_id" class="cm-select signatory_id">
                                <option value="">Please Select</option>
                                @foreach($signatory as $sg)
                                    <option value="{{ $sg->id }}">{{ $sg->signatory_name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-signatory_id"></div>
                        </div>

                        <div class="cm-field">
                            <label>Delivery</label>
                            {{-- The toggle sits in a wrapper so its own <label>
                                 is not a direct child of `.cm-field`, whose
                                 caption rules would uppercase it and paint its
                                 spans in the required-marker red. --}}
                            <div class="cm-togglewrap">
                                <label class="cm-togglecard" for="send_in_email">
                                    <input id="send_in_email" class="cm-togglecard__input" type="checkbox" name="send_in_email" value="1">
                                    <span class="cm-togglecard__card">
                                        <span class="cm-togglecard__mark">
                                            {{-- Inline rather than lucide: createIcons
                                                 replaces the placeholder once, and both
                                                 glyphs have to survive every toggle. --}}
                                            <svg class="cm-togglecard__glyph cm-togglecard__glyph--off" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                            <svg class="cm-togglecard__glyph cm-togglecard__glyph--on" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        </span>
                                        <span class="cm-togglecard__text">
                                            <strong>Send by email</strong>
                                            <small class="cm-togglecard__hint cm-togglecard__hint--on">The letter will be emailed too</small>
                                            <small class="cm-togglecard__hint cm-togglecard__hint--off">Generate the PDF only</small>
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="cm-field cm-field--span2 commonSmtpWrap" hidden>
                            <label for="comon_smtp_id">SMTP <span>*</span></label>
                            <select id="comon_smtp_id" name="comon_smtp_id" class="cm-select comon_smtp_id">
                                <option value="">Please Select</option>
                                @foreach($smtps as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-comon_smtp_id"></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="sendLetterBtn" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Generate Letter
                        </button>
                        <input type="hidden" name="student_ids" value="">
                        {{-- The response carries a pdf url that is opened in a
                             new tab when this is 1. --}}
                        <input type="hidden" name="print_pdf" value="1">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-bulk-communication.js')
@endsection
