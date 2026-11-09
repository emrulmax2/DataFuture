@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Communication on the admission portal shell: Email / SMS / Letter tabs,
        each with its compose form on the left and the sent list on the right
        (deleted items in an accordion with Restore). Behaviour lives in
        resources/js/admission-communication.js; the endpoints are the ones the
        previous page used (admission.communication.*). Ids kept from the
        previous page: #sendEmailForm (#comon_smtp_id, #subject,
        #email_template_id, #mailEditor, #sendMailsDocument, #sendEmailBtn),
        #smsSMSForm (#sms_template_id, #sms_subject, #smsTextArea, .sms_countr,
        #sendSMSBtn), #addLetterForm (#issued_date, #letter_set_id,
        .letterEditorArea, #letterEditor, #signatory_id, #sendLetterBtn),
        #viewCommunicationModal, .view_btn / .delete_btn / .restore_btn /
        .downloadDoc, #confirmModal (.agreeWith data-recordid / data-status /
        data-applicant).
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'communication';

        $adpPaths = [
            'send' => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
            'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
            'undo' => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'print' => '<path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>',
            'export' => '<path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',
            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'flag' => '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
            'expand' => '<path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
        $adpCaret = '<span class="adp-acc-caret" aria-hidden="true">▾</span>';
        $adpAccept = '.jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx';
        // The design's small rich-text editor (bold / italic / underline / lists / clear); rows set the height.
        $adpEditor = fn ($id, $placeholder, $rows = 8) => '<div class="adp-editor" data-adp-editor><div class="adp-ed-bar" role="toolbar" aria-label="Formatting">'
            .'<button type="button" data-cmd="bold" title="Bold" aria-label="Bold"><b>B</b></button>'
            .'<button type="button" data-cmd="italic" title="Italic" aria-label="Italic"><i>I</i></button>'
            .'<button type="button" data-cmd="underline" title="Underline" aria-label="Underline"><u>U</u></button>'
            .'<span class="adp-ed-sep"></span>'
            .'<button type="button" data-cmd="insertUnorderedList" title="Bulleted list" aria-label="Bulleted list">'.$adpIcon('list', 14).'</button>'
            .'<button type="button" data-cmd="insertOrderedList" title="Numbered list" aria-label="Numbered list">1.</button>'
            .'<span class="adp-ed-sep"></span>'
            .'<button type="button" data-cmd="removeFormat" title="Clear formatting" aria-label="Clear formatting">Tx</button>'
            .'</div><div id="'.$id.'" class="adp-ed-body" contenteditable="true" data-adp-ed-body data-placeholder="'.e($placeholder).'" aria-label="'.e($placeholder).'" style="min-height: '.($rows * 22).'px;"></div></div>';

        $adpEmail = $applicant->users->email ?? '';
        $adpMobile = $applicant->contact->mobile ?? '';
        $adpCourseName = $applicant->course->creation->course->name ?? '';
        $adpDeleted = array_merge(['email' => 0, 'sms' => 0, 'letter' => 0], $deletedCounts ?? []);
        $adpToday = date('Y-m-d');

        // Merge tags the letter generator understands (GenerateApplicantLetterTrait), grouped as in
        // pages/settings/letter/letter-tags; student tags apply to student letters and merge empty here.
        $adpTagGroups = [
            'Applicant tags' => ['[DATA=titles]name[/DATA]', '[DATA=applicants]full_name[/DATA]', '[DATA=applicants]first_name[/DATA]', '[DATA=applicants]last_name[/DATA]', '[DATA=applicants]application_no[/DATA]', '[DATA=applicants]date_of_birth[/DATA]', '[DATA=applicant_contacts]full_address[/DATA]', '[DATA=applicant_contacts]address_line_1[/DATA]', '[DATA=applicant_contacts]address_line_2[/DATA]', '[DATA=applicant_contacts]city[/DATA]', '[DATA=applicant_contacts]post_code[/DATA]', '[DATA=applicant_contacts]country[/DATA]', '[DATA=courses]name[/DATA]', '[DATA=courses]degree_offered[/DATA]', '[DATA=applicant_proposed_courses]full_time[/DATA]', '[DATA=applicant_proposed_courses]course_start_date[/DATA]', '[DATA=applicant_proposed_courses]course_end_date[/DATA]', '[DATA=applicant_proposed_courses]fees[/DATA]', '[DATA=applicant_proposed_courses]venue_name[/DATA]'],
            'Common tags' => ['[DATA=signatories]sign_url[/DATA]', '[DATA=signatories]name[/DATA]', '[DATA=signatories]post[/DATA]', '[DATA=letter_issuing]issued_date[/DATA]', '[DATA=today_date]today_date[/DATA]', '[DATA=page_break]page_break[/DATA]'],
            'Student tags' => ['[DATA=students]full_name[/DATA]', '[DATA=students]last_name[/DATA]', '[DATA=students]first_name[/DATA]', '[DATA=students]registration_no[/DATA]', '[DATA=students]date_of_birth[/DATA]', '[DATA=student_contacts]term_address[/DATA]', '[DATA=student_contacts]term_address_line_1[/DATA]', '[DATA=student_contacts]term_address_line_2[/DATA]', '[DATA=student_contacts]term_city[/DATA]', '[DATA=student_contacts]term_post_code[/DATA]', '[DATA=student_contacts]term_country[/DATA]', '[DATA=student_contacts]permanent_address[/DATA]', '[DATA=student_contacts]permanent_address_line_1[/DATA]', '[DATA=student_contacts]permanent_address_line_2[/DATA]', '[DATA=student_contacts]permanent_city[/DATA]', '[DATA=student_contacts]permanent_post_code[/DATA]', '[DATA=student_contacts]permanent_country[/DATA]', '[DATA=student_contacts]mobile[/DATA]', '[DATA=student_contacts]personal_email[/DATA]', '[DATA=student_kins]name[/DATA]', '[DATA=student_kins]mobile[/DATA]', '[DATA=semesters]name[/DATA]', '[DATA=student_proposed_courses]awarding_body[/DATA]', '[DATA=student_proposed_courses]evening_and_weekends[/DATA]', '[DATA=student_proposed_courses]class_startdate[/DATA]', '[DATA=student_proposed_courses]class_enddate[/DATA]', '[DATA=student_proposed_courses]fees[/DATA]', '[DATA=other_details]mode[/DATA]', '[DATA=result]W,P,M,D,R,C,U,A[/DATA]'],
        ];
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-comms" data-adp-comms data-applicant="{{ $applicant->id }}" data-adp-deleted-counts='@json($adpDeleted)'>
        <div class="adp-panel__head adp-panel__head--top">
            <div class="adp-proc__copy">
                <h3 class="adp-h3">Communication</h3>
                <p class="adp-proc__desc">Email, text or write to the applicant; everything sent is kept here, per channel.</p>
            </div>
            <div class="adp-tabletools__actions">
                <button type="button" id="tabulator-print-CM" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('print', 13) !!}Print</button>
                <button type="button" id="tabulator-export-csv-CM" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export CSV</button>
                <button type="button" id="tabulator-export-xlsx-CM" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export XLSX</button>
            </div>
        </div>

        {{-- Channel tabs. aria-pressed, not role="tab": the theme's app.js binds every [role="tab"] and expects data-tw-target. --}}
        <div class="adp-tabs adp-comms__tabs" data-adp-channels>
            <button type="button" class="on" data-adp-channel="email" aria-pressed="true">Email</button>
            <button type="button" data-adp-channel="sms" aria-pressed="false">SMS</button>
            <button type="button" data-adp-channel="letter" aria-pressed="false">Letter</button>
        </div>

        <div class="adp-comms-grid">
            <div class="adp-comms-compose">
                {{-- Email: admission.communication.send.mail (comon_smtp_id, subject, email_template_id, body, documents[]) --}}
                <form method="POST" action="#" id="sendEmailForm" class="adp-compose" enctype="multipart/form-data" data-adp-compose="email" data-adp-fill-name="{{ $applicant->first_name }}" data-adp-fill-ref="{{ $applicant->application_no }}" data-adp-fill-course="{{ $adpCourseName }}" data-adp-to="{{ $adpEmail }}">
                    <h3 class="adp-h3">Send email</h3>
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="comon_smtp_id">SMTP <span class="adp-req">*</span></label>
                            <select id="comon_smtp_id" name="comon_smtp_id" class="adp-ctl adp-select comon_smtp_id">
                                @foreach($smtps as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                @endforeach
                            </select>
                            <div class="adp-err acc__input-error error-comon_smtp_id"></div>
                        </div>
                        <div class="adp-field">
                            <label for="subject">Subject <span class="adp-req">*</span></label>
                            <input id="subject" type="text" name="subject" class="adp-ctl subject" autocomplete="off">
                            <div class="adp-err acc__input-error error-subject"></div>
                        </div>
                    </div>
                    <div class="adp-field">
                        <label for="email_template_id">Template</label>
                        <select id="email_template_id" name="email_template_id" class="adp-ctl adp-select">
                            <option value="">Blank email</option>
                            @foreach($emailTemplates as $et)
                                <option value="{{ $et->id }}">{{ $et->email_title }}</option>
                            @endforeach
                        </select>
                        <span class="adp-hint">Placeholders {name}, {ref} and {course} are filled from the record.</span>
                    </div>
                    <div class="adp-field">
                        <label for="email_to">To</label>
                        <input id="email_to" type="text" class="adp-ctl" value="{{ $adpEmail }}" readonly>
                    </div>
                    <div class="adp-field">
                        <label for="mailEditor">Message <span class="adp-req">*</span></label>
                        {!! $adpEditor('mailEditor', 'Write your message…', 8) !!}
                        <div class="adp-err acc__input-error error-body"></div>
                    </div>
                    <div class="adp-attach-row">
                        <label for="sendMailsDocument" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('upload', 13) !!}Upload attachments</label>
                        <input type="file" id="sendMailsDocument" name="documents[]" class="adp-sr-only" multiple accept="{{ $adpAccept }}">
                        <span class="adp-hint">PDF, DOC, image, spreadsheet</span>
                        <span class="adp-attach-list sendMailsDocumentNames" id="sendMailsDocumentNames" data-adp-files hidden></span>
                        <div class="adp-err acc__input-error error-documents"></div>
                    </div>
                    <div class="adp-compose__foot">
                        <button type="button" class="adp-btn adp-btn--ghost" data-adp-compose-reset>Cancel</button>
                        <button type="submit" id="sendEmailBtn" class="adp-btn adp-btn--primary">{!! $adpIcon('send', 14) !!}Send mail {!! $adpSpin !!}</button>
                    </div>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </form>

                {{-- SMS: admission.communication.send.sms (sms_template_id, subject, sms) --}}
                <form method="POST" action="#" id="smsSMSForm" class="adp-compose" data-adp-compose="sms" data-adp-to="{{ $adpMobile }}" hidden>
                    <h3 class="adp-h3">Send SMS</h3>
                    <div class="adp-field">
                        <label for="sms_template_id">Template</label>
                        <select id="sms_template_id" name="sms_template_id" class="adp-ctl adp-select">
                            <option value="">Blank message</option>
                            @foreach($smsTemplates as $st)
                                <option value="{{ $st->id }}">{{ $st->sms_title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="adp-field">
                        <label for="sms_subject">Subject <span class="adp-req">*</span></label>
                        <input id="sms_subject" type="text" name="subject" class="adp-ctl subject" autocomplete="off">
                        <span class="adp-hint">Internal label; not sent to the applicant.</span>
                        <div class="adp-err acc__input-error error-subject"></div>
                    </div>
                    <div class="adp-field">
                        <label for="sms_to">To</label>
                        <input id="sms_to" type="text" class="adp-ctl" value="{{ $adpMobile }}" readonly>
                        @if($adpMobile === '')
                            <span class="adp-hint adp-hint--warn">No mobile number on the record: the SMS is stored but not sent.</span>
                        @endif
                    </div>
                    <div class="adp-field">
                        <div class="adp-lbl-row">
                            <label for="smsTextArea">SMS <span class="adp-req">*</span></label>
                            <span class="sms_countr adp-sms-count" data-adp-sms-count>0 / 1</span>
                        </div>
                        <textarea id="smsTextArea" name="sms" class="adp-ctl sms" rows="5"></textarea>
                        <div class="adp-err acc__input-error error-sms"></div>
                        <span class="adp-hint" data-adp-sms-hint>160 characters per segment; longer messages are billed as multiple segments.</span>
                    </div>
                    <div class="adp-compose__foot">
                        <button type="button" class="adp-btn adp-btn--ghost" data-adp-compose-reset>Cancel</button>
                        <button type="submit" id="sendSMSBtn" class="adp-btn adp-btn--primary">{!! $adpIcon('send', 14) !!}Send SMS {!! $adpSpin !!}</button>
                    </div>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </form>

                {{-- Letter: admission.communication.send.letter (issued_date, comon_smtp_id, letter_set_id, signatory_id, letter_body) --}}
                <form method="POST" action="#" id="addLetterForm" class="adp-compose" data-adp-compose="letter" data-adp-today="{{ $adpToday }}" hidden>
                    <div class="adp-compose__head">
                        <h3 class="adp-h3">Send letter</h3>
                        {{-- Full width: the form takes the whole row and the sent letters move below it (remembered per browser). --}}
                        <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-wide-toggle aria-pressed="false" title="Give the letter the full width">{!! $adpIcon('expand', 13) !!}<span data-adp-wide-label>Full width</span></button>
                    </div>
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="issued_date">Issued date <span class="adp-req">*</span></label>
                            <input id="issued_date" type="date" name="issued_date" class="adp-ctl issued_date" value="{{ $adpToday }}">
                            <div class="adp-err acc__input-error error-issued_date"></div>
                        </div>
                        <div class="adp-field">
                            <label for="letter_comon_smtp_id">SMTP <span class="adp-req">*</span></label>
                            <select id="letter_comon_smtp_id" name="comon_smtp_id" class="adp-ctl adp-select comon_smtp_id">
                                @foreach($smtps as $sm)
                                    <option value="{{ $sm->id }}">{{ $sm->smtp_user }}</option>
                                @endforeach
                            </select>
                            <div class="adp-err acc__input-error error-comon_smtp_id"></div>
                        </div>
                    </div>
                    <div class="adp-field">
                        <label for="letter_set_id">Letter <span class="adp-req">*</span></label>
                        <select id="letter_set_id" name="letter_set_id" class="adp-ctl adp-select letter_set_id">
                            <option value="">Choose a letter set</option>
                            @foreach($letterSet as $ls)
                                <option value="{{ $ls->id }}">{{ $ls->letter_type.' - '.$ls->letter_title }}</option>
                            @endforeach
                        </select>
                        <div class="adp-err acc__input-error error-letter_set_id"></div>
                    </div>
                    <div class="adp-field">
                        <label for="signatory_id">Signatory</label>
                        <select id="signatory_id" name="signatory_id" class="adp-ctl adp-select">
                            <option value="">Please select</option>
                            @foreach($signatory as $sg)
                                <option value="{{ $sg->id }}">{{ $sg->signatory_name }}{{ $sg->signatory_post ? ' · '.$sg->signatory_post : '' }}</option>
                            @endforeach
                        </select>
                        <div class="adp-err acc__input-error error-signatory_id"></div>
                    </div>
                    {{-- Shown once a letter set is chosen (admission.communication.get.letter.set fills the body). --}}
                    <div class="adp-field letterEditorArea" data-adp-letter-body hidden>
                        <div class="adp-lbl-row">
                            <label for="letterEditor">Letter body <span class="adp-hint">— tags merge on send</span></label>
                            <span class="adp-pop-wrap">
                                <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-tags-toggle aria-expanded="false">{!! $adpIcon('flag', 13) !!}<span data-adp-tags-label>Tags</span></button>
                                <div class="adp-pop adp-pop--wide adp-pop--right" data-adp-tags-pop role="dialog" aria-label="Merge tags" hidden>
                                    <div class="adp-pop-h"><b>Merge tags</b><span class="adp-hint">Click a tag to insert it at the cursor</span></div>
                                    <div class="adp-tag-groups">
                                        @foreach($adpTagGroups as $group => $tags)
                                            <div>
                                                <small>{{ $group }}</small>
                                                @foreach($tags as $tag)
                                                    <button type="button" class="adp-tag-item adp-mono" data-adp-tag="{{ $tag }}">{{ $tag }}</button>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </span>
                        </div>
                        {!! $adpEditor('letterEditor', 'The letter set fills this in…', 12) !!}
                        <div class="adp-err acc__input-error error-letter_body"></div>
                    </div>
                    <div class="adp-compose__foot">
                        <button type="button" class="adp-btn adp-btn--ghost" data-adp-compose-reset>Cancel</button>
                        <button type="submit" id="sendLetterBtn" class="adp-btn adp-btn--primary">{!! $adpIcon('send', 14) !!}Send letter {!! $adpSpin !!}</button>
                    </div>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </form>
            </div>

            {{-- Sent list of the active channel (admission.communication.{mail,sms,letter}.list). --}}
            <div class="adp-comms-sent" data-adp-sent>
                <div class="adp-comms-sent__head">
                    <h3 class="adp-h3" data-adp-sent-title>Sent emails</h3>
                    <form id="tabulatorFilterForm-CM" class="adp-comms-search">
                        <input id="query-CM" name="query" type="text" class="adp-ctl" placeholder="Search sent emails…" autocomplete="off">
                        <button id="tabulator-html-filter-go-CM" type="button" class="adp-btn adp-btn--primary adp-btn--sm" aria-label="Search">{!! $adpIcon('search', 13) !!}</button>
                        <button id="tabulator-html-filter-reset-CM" type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-search-reset hidden>Reset</button>
                    </form>
                </div>
                <span class="adp-note-count" data-adp-sent-count></span>
                <div class="adp-arch-loading" data-adp-sent-loading><span class="adp-spin"></span>Loading…</div>
                <div class="adp-empty adp-empty--small" data-adp-sent-empty hidden><span>Nothing sent yet.</span></div>
                <div class="adp-vrows" data-adp-sent-rows></div>
                <div class="adp-timeline__more" data-adp-sent-more hidden><button type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Load more</button></div>

                <details class="adp-accordion adp-accordion--deleted" data-adp-deleted hidden>
                    <summary><span>Deleted <span class="adp-cnt adp-cnt--muted" data-adp-deleted-count>0</span></span>{!! $adpCaret !!}</summary>
                    <div class="adp-acc-body">
                        <div class="adp-arch-loading" data-adp-deleted-loading hidden><span class="adp-spin"></span>Loading deleted items…</div>
                        <div class="adp-vrows" data-adp-deleted-rows></div>
                    </div>
                </details>
            </div>
        </div>
    </section>

    {{-- View dialog: meta line, attachment tags, body. --}}
    <div id="viewCommunicationModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h3 class="adp-trunc-title" data-adp-view-title>View communication</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-view-comm">
                        <span class="adp-view-comm__meta" data-adp-view-meta></span>
                        <div class="adp-attach-list" data-adp-view-files hidden></div>
                        <div class="adp-view-comm__body mailContent" data-adp-view-body></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="footerBtns adp-view-comm__acts" data-adp-view-actions></div>
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Close</button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs', ['adpConfirmAttrs' => 'data-recordid="0" data-status="none" data-applicant="'.$applicant->id.'"'])
@endsection

@section('script')
    @vite('resources/js/admission-communication.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
