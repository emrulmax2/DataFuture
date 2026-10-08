{{--
    Academic Appeal Form, Stage 1 - the fourth "Do it online" form in the
    portal, and the longest. Two of its answers change what the form shows:
    saying results have not been notified warns that the appeal cannot be
    considered yet, and the evidence choice decides whether the upload panel
    or the email instruction appears (data-sfm-when, in student-form.js).
--}}
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/student-form.css')
@endsection

@section('subcontent')
    @php
        $fullName = trim(($student->title->name ?? '').' '.$student->first_name.' '.$student->last_name);
        $registryEmail = config('services.registry.email');
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">Academic appeal: Stage 1</h1>
            <p class="sfm-sub">
                Use this form to make a formal appeal against an academic decision taken by the College.
                Grading decisions are excluded.
            </p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 5 answered</div>
    </div>

    <div class="sfm">
        <div>
            <section class="sfm-card sfm-before">
                <div class="sfm-eyebrow">Before you start</div>
                <ul class="sfm-guidance">
                    <li>
                        Read the
                        <a href="https://lcc.ac.uk/policies/academic-appeals-policy-and-procedure/" target="_blank" rel="noopener">Academic Appeals Policy</a>
                        before completing this form.
                    </li>
                    <li>If you need help completing the form, contact your Personal Tutor.</li>
                    <li>
                        You need to appeal within <strong>15 working days</strong> of receiving formal notification
                        of your results. If your appeal is submitted late, it will not normally be considered.
                    </li>
                    <li>
                        In exceptional cases, the College may accept a late appeal if you can demonstrate
                        circumstances that prevented an on-time submission. Any such case must be supported with
                        relevant documentary evidence, for example a medical certificate.
                    </li>
                    <li>
                        The College will only consider appeals against decisions of the Assessment and Progress
                        Board. If you have not received formal notification of your results, please wait for them
                        before appealing.
                    </li>
                </ul>
            </section>

            @if($errors->any())
                <div class="sfm-alert">
                    <strong>We could not submit your appeal.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('students.doitonline.form.appeal.store', $form->id) }}"
                  enctype="multipart/form-data"
                  data-sfm-form>
                @csrf

                <div class="sfm-steps">
                    {{-- 1 - from the student record, nothing to answer --}}
                    <section class="sfm-card" data-sfm-section="details" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">1</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Your personal details</div>
                                <div class="sfm-card__sub">Filled in from your student record.</div>
                            </div>
                            <div class="sfm-tag"><i data-lucide="lock" class="w-3 h-3"></i> Read only</div>
                        </div>

                        <div class="sfm-record">
                            <div>
                                <div class="sfm-record__label">Name</div>
                                <div class="sfm-record__value">{{ $fullName ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Student ID number</div>
                                <div class="sfm-record__value">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Programme / course</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Mobile number</div>
                                <div class="sfm-record__value">{{ $studentMobile ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Email</div>
                                <div class="sfm-record__value">{{ $studentEmail ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="sfm-note">
                            You can't change these details here. If anything is wrong, contact the College Registry.
                        </div>
                    </section>

                    {{-- 2 - whether they can appeal, and on what basis --}}
                    <section class="sfm-card" data-sfm-section="grounds" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Grounds for your appeal</div>
                                <div class="sfm-card__sub">Whether you can appeal, and on what basis.</div>
                            </div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">
                                Have you received formal notification of your results? <span class="sfm-req">*</span>
                            </legend>
                            <div class="sfm-choices">
                                <label class="sfm-choice">
                                    <input type="radio" name="notified" value="yes"
                                           data-sfm-field="grounds"
                                           {{ old('notified') === 'yes' ? 'checked' : '' }}> Yes
                                </label>
                                <label class="sfm-choice">
                                    <input type="radio" name="notified" value="no"
                                           data-sfm-field="grounds"
                                           {{ old('notified') === 'no' ? 'checked' : '' }}> No
                                </label>
                            </div>

            {{-- An appeal made before the results were notified cannot be
                 considered, so the form stops here: data-sfm-blocks disables
                 the submit button while this is on screen, and the server
                 refuses it as well. --}}
                            <div class="sfm-notice sfm-notice--warning"
                                 data-sfm-when="notified:no"
                                 data-sfm-blocks="You can only appeal once you have received formal notification of your results."
                                 hidden>
                                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                <span>
                                    The College will only consider appeals against decisions of the Assessment and
                                    Progress Board. Please wait for your results before appealing — this form cannot
                                    be submitted until then.
                                </span>
                            </div>
                        </fieldset>

                        <hr class="sfm-divider">

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">
                                What are the grounds for your appeal? <span class="sfm-req">*</span>
                            </legend>
                            <div class="sfm-options">
                                <label class="sfm-option">
                                    <input type="radio" name="grounds" value="mitigating_circumstances"
                                           data-sfm-field="grounds"
                                           {{ old('grounds') === 'mitigating_circumstances' ? 'checked' : '' }}>
                                    <span>
                                        <span class="sfm-option__title">Mitigating circumstances</span>
                                        <span class="sfm-option__desc">
                                            Circumstances affecting your performance of which the assessors were not
                                            aware when their decision was taken, and which could not reasonably have
                                            been presented to the assessors, or there was an administrative error.
                                        </span>
                                    </span>
                                </label>
                                <label class="sfm-option">
                                    <input type="radio" name="grounds" value="procedural_irregularity"
                                           data-sfm-field="grounds"
                                           {{ old('grounds') === 'procedural_irregularity' ? 'checked' : '' }}>
                                    <span>
                                        <span class="sfm-option__title">Procedural irregularity / unfair conduct of assessment</span>
                                        <span class="sfm-option__desc">
                                            Of a nature as to cause doubt as to whether the result might have been
                                            different had there not been such an error.
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <p class="sfm-strong-note">
                                These are the only grounds for appeal the College will accept. Any appeal that is
                                based solely on a request to be given another opportunity to change a grade, or any
                                attempt to alter the outcomes of academic judgement reached through due academic
                                process, will not be considered.
                            </p>
                        </fieldset>

                        <hr class="sfm-divider">

                        <div>
                            <label for="early_resolution" class="sfm-label">
                                What attempts have you made at an early resolution? <span class="sfm-req">*</span>
                            </label>
                            <div class="sfm-hint">The actions that were taken to try to resolve the appeal informally.</div>
                            <textarea name="early_resolution"
                                      id="early_resolution"
                                      class="sfm-textarea"
                                      data-sfm-field="grounds">{{ old('early_resolution') }}</textarea>
                        </div>
                    </section>

                    {{-- 3 - the appeal itself --}}
                    <section class="sfm-card" data-sfm-section="appeal" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Details of your appeal</div>
                                <div class="sfm-card__sub">What happened, and the outcome you are seeking.</div>
                            </div>
                        </div>

                        <div>
                            <label for="decision" class="sfm-label">
                                Explain the decision you wish to appeal against <span class="sfm-req">*</span>
                            </label>
                            <div class="sfm-hint">
                                Please ensure you include the following:
                                <ul class="sfm-helplist">
                                    <li>Details of the problem that occurred</li>
                                    <li>The date(s) it affected your performance</li>
                                    <li>The unit(s) affected</li>
                                    <li>When and how you were informed of the academic results</li>
                                </ul>
                            </div>
                            <textarea name="decision"
                                      id="decision"
                                      class="sfm-textarea sfm-textarea--tall"
                                      data-sfm-field="appeal"
                                      data-sfm-counted="decision">{{ old('decision') }}</textarea>
                            <div class="sfm-count" data-sfm-countfor="decision"></div>
                        </div>

                        <div>
                            <label for="outcome" class="sfm-label">
                                How would you like your appeal to be resolved? <span class="sfm-req">*</span>
                            </label>
                            <div class="sfm-hint">The outcome you are seeking.</div>
                            <textarea name="outcome"
                                      id="outcome"
                                      class="sfm-textarea"
                                      data-sfm-field="appeal">{{ old('outcome') }}</textarea>
                        </div>
                    </section>

                    {{-- 4 - evidence, however they are sending it --}}
                    <section class="sfm-card" data-sfm-section="evidence" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">4</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Supporting evidence</div>
                                <div class="sfm-card__sub">
                                    You must support your appeal with documentary evidence, for example medical evidence.
                                </div>
                            </div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">
                                How will you provide your documents? <span class="sfm-req">*</span>
                            </legend>
                            <div class="sfm-options">
                                <label class="sfm-option">
                                    <input type="radio" name="evidence_method" value="upload"
                                           data-sfm-field="evidence"
                                           {{ old('evidence_method') === 'upload' ? 'checked' : '' }}>
                                    <span><span class="sfm-option__title">I will upload my documents with this form</span></span>
                                </label>
                                <label class="sfm-option">
                                    <input type="radio" name="evidence_method" value="none"
                                           data-sfm-field="evidence"
                                           {{ old('evidence_method') === 'none' ? 'checked' : '' }}>
                                    <span><span class="sfm-option__title">I do not have any documents to send</span></span>
                                </label>
                            </div>
                        </fieldset>

                        {{-- Hidden until chosen, and the file input disabled with
                             it, so nothing is posted from a panel not in play. --}}
                        <div data-sfm-when="evidence_method:upload" hidden>
                            <label class="sfm-drop" data-sfm-drop>
                                <input type="file"
                                       name="documents[]"
                                       class="hidden"
                                       multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       data-sfm-upload>
                                <div class="sfm-drop__title">Choose files or drop them here</div>
                                <div class="sfm-drop__hint">PDF, Word, JPG or PNG. Up to 5 files, 5MB each.</div>
                            </label>
                            <div class="sfm-files" data-sfm-filelist></div>
                        </div>

                        {{-- Only asked for when there is nothing to send: with
                             documents attached, the documents are the answer. --}}
                        <div data-sfm-when="evidence_method:none" hidden>
                            <label for="evidence_statement" class="sfm-label">
                                Describe your supporting information <span class="sfm-req">*</span>
                            </label>
                            <div class="sfm-hint">
                                State what supporting information you are providing. If you are unable to provide
                                documentary evidence, please explain why.
                            </div>
                            <textarea name="evidence_statement"
                                      id="evidence_statement"
                                      class="sfm-textarea"
                                      disabled>{{ old('evidence_statement') }}</textarea>
                            <p class="sfm-strong-note">
                                All documentation must be in English or accompanied by an official English translation.
                            </p>
                        </div>
                    </section>

                    {{-- 5 - declaration --}}
                    <section class="sfm-card"
                             data-sfm-section="declaration"
                             data-sfm-pending="Not yet confirmed"
                             data-sfm-done="Confirmed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">5</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Declaration</div>
                                <div class="sfm-card__sub">Please read before you submit.</div>
                            </div>
                        </div>

                        <div class="sfm-declaration">
                            <p>By submitting this form I confirm that:</p>
                            <ul class="sfm-guidance">
                                <li>
                                    I have read the Academic Appeals Policy and understand that my appeal will only be
                                    considered within the terms of the Academic Appeals Policy and the College Regulations.
                                </li>
                                <li>The information I have provided on this form is true to the best of my knowledge.</li>
                                <li>
                                    I understand that if any information I have provided is found to be false I may be
                                    subject to disciplinary proceedings.
                                </li>
                                <li>
                                    For the purposes of investigating my appeal, I give my permission for the College
                                    Registrar and Academic Appeals Panel to review any information about me held in other
                                    College departments including but not limited to my academic department, Finance and
                                    Student Support.
                                </li>
                                <li>
                                    If I choose to appoint a representative to act on my behalf, I provide London Churchill
                                    College with my explicit permission to receive information on my behalf and provide
                                    appropriate responses to my Nominated Representative.
                                </li>
                                <li>
                                    In giving this permission, I agree to the sharing of information which, if relevant,
                                    may be of a personal or private nature, including sensitive data as defined by the
                                    General Data Protection Regulations 2018.
                                </li>
                                <li>
                                    Cancellation of this authority to act can be made by the Appellant in writing to the
                                    Registrar on email
                                    <a href="mailto:registry.Support@lcc.ac.uk">registry.Support@lcc.ac.uk</a>.
                                </li>
                            </ul>
                        </div>

                        <label class="sfm-declare">
                            <input type="checkbox"
                                   name="declaration"
                                   value="1"
                                   data-sfm-field="declaration"
                                   {{ old('declaration') ? 'checked' : '' }}>
                            <span class="sfm-declare__text">I confirm the above declaration.</span>
                        </label>

                        {{-- The script fills these; the IP and user agent are read
                             from the request itself, where they cannot be edited. --}}
                        <input type="hidden" name="device_type" data-sfm-device="type">
                        <input type="hidden" name="device_os" data-sfm-device="os">
                        <input type="hidden" name="device_browser" data-sfm-device="browser">
                        <input type="hidden" name="device_screen" data-sfm-device="screen">

                        <div class="sfm-note">
                            When you submit, your IP address and the type of device you are using are recorded
                            with your appeal.
                        </div>

                        <div class="sfm-actions">
                            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Cancel</a>
                            <button type="submit" class="sfm-btn">Submit appeal</button>
                        </div>
                    </section>
                </div>
            </form>
        </div>

        <aside class="sfm-aside">
            <div class="sfm-panel">
                <div class="sfm-panel__title">Form progress</div>

                <div class="sfm-prog sfm-prog--done" data-sfm-step="details">
                    <div class="sfm-prog__name">Your personal details</div>
                    <div class="sfm-prog__state" data-sfm-state="details">From your record</div>
                </div>
                <div class="sfm-prog" data-sfm-step="grounds">
                    <div class="sfm-prog__name">Grounds for your appeal</div>
                    <div class="sfm-prog__state" data-sfm-state="grounds">0 of 3 answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="appeal">
                    <div class="sfm-prog__name">Details of your appeal</div>
                    <div class="sfm-prog__state" data-sfm-state="appeal">0 of 2 answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="evidence">
                    <div class="sfm-prog__name">Supporting evidence</div>
                    <div class="sfm-prog__state" data-sfm-state="evidence">0 of 1 answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="declaration">
                    <div class="sfm-prog__name">Declaration</div>
                    <div class="sfm-prog__state" data-sfm-state="declaration">Not yet confirmed</div>
                </div>

                <div class="sfm-panel__foot">
                    Fields marked <span class="sfm-req">*</span> are required.
                    @if($submitted)
                        <div style="margin-top:8px">
                            Last appeal: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->ticket_ref ?: $submitted->status }}
                        </div>
                    @endif
                    <div class="sfm-panel__version">
                        LCC Academic Appeal Form ST1 v2.0 &middot; Version LCC1.31.1 REV2, May 2024
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
