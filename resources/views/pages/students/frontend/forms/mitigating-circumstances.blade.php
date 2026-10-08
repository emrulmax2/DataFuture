{{--
    Mitigating Circumstances Claim Form (Assignment) - the fifth "Do it online"
    form in the portal, and the first with repeating rows: one claim can cover
    several assignments, each with its own deadline, because the panel decides
    them one by one.
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
        $reasons = App\Models\StudentMitigatingCircumstance::REASONS;
        $oldReasons = (array) old('reasons', []);
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">Mitigating circumstances: Assignment</h1>
            <p class="sfm-sub">Use this form to apply for an extension on one or more of your assignments.</p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 5 answered</div>
    </div>

    <div class="sfm">
        <div>
            <section class="sfm-card sfm-before">
                <div class="sfm-eyebrow">Before you start</div>
                <ul class="sfm-guidance">
                    <li>
                        Please ensure you read the
                        <a href="https://lcc.ac.uk/policies/mitigating-circumstance-policy-and-procedure/" target="_blank" rel="noopener">Mitigating Circumstances Policy</a>
                        before completing this form.
                    </li>
                    <li>
                        To be considered for an extension on one or more of your assignments, you must accurately
                        complete this form as soon as you realise you will be unable to meet the submission deadline.
                    </li>
                    <li>
                        All details of the mitigating circumstances that have prevented you from submitting your
                        assignment on time <strong>must</strong> be listed, and all supporting documentation must be
                        provided.
                    </li>
                    <li>
                        Please refer to the 'Mitigating Circumstances Guidance' to help you decide if you have a
                        valid claim.
                    </li>
                </ul>
            </section>

            @if($errors->any())
                <div class="sfm-alert">
                    <strong>We could not submit your claim.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('students.doitonline.form.mitigating.store', $form->id) }}"
                  enctype="multipart/form-data"
                  data-sfm-form>
                @csrf

                <div class="sfm-steps">
                    {{-- 1 - from the student record, nothing to answer --}}
                    <section class="sfm-card" data-sfm-section="details" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">1</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Your details</div>
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
                                <div class="sfm-record__label">LCC student ID</div>
                                <div class="sfm-record__value">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Course</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Telephone / mobile</div>
                                <div class="sfm-record__value">{{ $studentMobile ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Email</div>
                                <div class="sfm-record__value">{{ $studentEmail ?: '—' }}</div>
                            </div>
                            <div class="sfm-record__full">
                                <div class="sfm-record__label">Address</div>
                                <div class="sfm-record__value">{{ $correspondenceAddress ?: 'No address on your record' }}</div>
                            </div>
                        </div>

                        <div class="sfm-note">
                            You can't change these details here. If anything is wrong, contact the College Registry.
                        </div>
                    </section>

                    {{-- 2 - one row per assignment --}}
                    <section class="sfm-card" data-sfm-section="assignments" data-sfm-count="rows">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Assignment details</div>
                                <div class="sfm-card__sub">Add each assignment you are requesting an extension for.</div>
                            </div>
                        </div>

                        {{-- Rows are numbered by the script, so they post as
                             assignments[0][title], assignments[1][title]… --}}
                        <div class="sfm-rows" data-sfm-rows></div>

                        <button type="button" class="sfm-btn sfm-btn--ghost sfm-btn--add" data-sfm-addrow>
                            <i data-lucide="plus" class="w-4 h-4"></i> Add another assignment
                        </button>

                        <template data-sfm-rowtemplate>
                            <div class="sfm-row">
                                <div class="sfm-row__field sfm-row__field--wide">
                                    <label class="sfm-label" data-sfm-rowlabel="title">
                                        Assignment title <span class="sfm-req">*</span>
                                    </label>
                                    <input type="text" class="sfm-input" data-sfm-rowkey="title" data-sfm-field="assignments">
                                </div>
                                <div class="sfm-row__field">
                                    <label class="sfm-label" data-sfm-rowlabel="submission_date">
                                        Submission date <span class="sfm-req">*</span>
                                    </label>
                                    <input type="text"
                                           class="sfm-input"
                                           placeholder="DD-MM-YYYY"
                                           autocomplete="off"
                                           data-sfm-rowkey="submission_date"
                                           data-sfm-datepicker
                                           data-sfm-field="assignments">
                                </div>
                                <button type="button" class="sfm-row__remove" data-sfm-removerow aria-label="Remove this assignment">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </section>

                    {{-- 3 - why --}}
                    <section class="sfm-card" data-sfm-section="circumstances" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Mitigating circumstances</div>
                                <div class="sfm-card__sub">
                                    Explain the circumstances you want the Mitigating Circumstances Panel to consider.
                                </div>
                            </div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">Possible reasons <span class="sfm-req">*</span></legend>
                            <div class="sfm-hint">Tick all that apply.</div>
                            <div class="sfm-options sfm-options--grid">
                                @foreach($reasons as $key => $label)
                                    <label class="sfm-option">
                                        <input type="checkbox" name="reasons[]" value="{{ $key }}"
                                               data-sfm-field="circumstances"
                                               {{ in_array($key, $oldReasons, true) ? 'checked' : '' }}>
                                        <span><span class="sfm-option__title">{{ $label }}</span></span>
                                    </label>
                                @endforeach
                            </div>

                            {{-- "Other" is only an answer once it is described. --}}
                            <div class="sfm-followup" data-sfm-when="reasons[]:other" hidden>
                                <label for="other_reason" class="sfm-label">
                                    Your other reason <span class="sfm-req">*</span>
                                </label>
                                <input type="text"
                                       name="other_reason"
                                       id="other_reason"
                                       class="sfm-input"
                                       value="{{ old('other_reason') }}"
                                       disabled>
                            </div>
                        </fieldset>

                        <div>
                            <label for="claim_details" class="sfm-label">
                                Provide details of your claim <span class="sfm-req">*</span>
                            </label>
                            <div class="sfm-hint">
                                Explain how these circumstances have prevented you from submitting your assignment on time.
                            </div>
                            <textarea name="claim_details"
                                      id="claim_details"
                                      class="sfm-textarea sfm-textarea--tall"
                                      data-sfm-field="circumstances"
                                      data-sfm-counted="claim">{{ old('claim_details') }}</textarea>
                            <div class="sfm-count" data-sfm-countfor="claim"></div>
                        </div>
                    </section>

                    {{-- 4 - evidence --}}
                    <section class="sfm-card" data-sfm-section="evidence" data-sfm-done="Answered" data-sfm-pending="Not yet answered">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">4</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Supporting evidence</div>
                                <div class="sfm-card__sub">
                                    Documentation that supports your claim, such as a medical certificate.
                                </div>
                            </div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">
                                How will you provide your supporting documents? <span class="sfm-req">*</span>
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
                                    <span><span class="sfm-option__title">I do not have any document to send</span></span>
                                </label>
                            </div>
                        </fieldset>

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
                    </section>

                    {{-- 5 - declaration --}}
                    <section class="sfm-card"
                             data-sfm-section="declaration"
                             data-sfm-pending="Not yet confirmed"
                             data-sfm-done="Confirmed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">5</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Declaration and outcome</div>
                                <div class="sfm-card__sub">
                                    Students will receive an outcome approximately within <strong>14 working days</strong>
                                    after the submission of their claim.
                                </div>
                            </div>
                        </div>

                        <label class="sfm-declare">
                            <input type="checkbox"
                                   name="declaration"
                                   value="1"
                                   data-sfm-field="declaration"
                                   {{ old('declaration') ? 'checked' : '' }}>
                            <span class="sfm-declare__text">
                                I confirm that, to the best of my knowledge, the information in this form is true
                                and accurate.
                            </span>
                        </label>

                        <p class="sfm-strong-note">
                            NB: Making false claims or falsifying evidence could lead to disciplinary procedures
                            against you.
                        </p>

                        {{-- The script fills these; the IP and user agent are read
                             from the request itself, where they cannot be edited. --}}
                        <input type="hidden" name="device_type" data-sfm-device="type">
                        <input type="hidden" name="device_os" data-sfm-device="os">
                        <input type="hidden" name="device_browser" data-sfm-device="browser">
                        <input type="hidden" name="device_screen" data-sfm-device="screen">

                        <div class="sfm-note">
                            When you submit, your IP address and the type of device you are using are recorded
                            with your claim.
                        </div>

                        <div class="sfm-actions">
                            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Cancel</a>
                            <button type="submit" class="sfm-btn">Submit claim</button>
                        </div>
                    </section>
                </div>
            </form>
        </div>

        <aside class="sfm-aside">
            <div class="sfm-panel">
                <div class="sfm-panel__title">Form progress</div>

                <div class="sfm-prog sfm-prog--done" data-sfm-step="details">
                    <div class="sfm-prog__name">Your details</div>
                    <div class="sfm-prog__state" data-sfm-state="details">From your record</div>
                </div>
                <div class="sfm-prog" data-sfm-step="assignments">
                    <div class="sfm-prog__name">Assignment details</div>
                    <div class="sfm-prog__state" data-sfm-state="assignments">Not yet added</div>
                </div>
                <div class="sfm-prog" data-sfm-step="circumstances">
                    <div class="sfm-prog__name">Mitigating circumstances</div>
                    <div class="sfm-prog__state" data-sfm-state="circumstances">0 of 2 answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="evidence">
                    <div class="sfm-prog__name">Supporting evidence</div>
                    <div class="sfm-prog__state" data-sfm-state="evidence">Not yet answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="declaration">
                    <div class="sfm-prog__name">Declaration and outcome</div>
                    <div class="sfm-prog__state" data-sfm-state="declaration">Not yet confirmed</div>
                </div>

                <div class="sfm-panel__foot">
                    Fields marked <span class="sfm-req">*</span> are required.
                    @if($submitted)
                        <div style="margin-top:8px">
                            Last claim: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->ticket_ref ?: $submitted->status }}
                        </div>
                    @endif
                    <div class="sfm-panel__version">Form version LCC1.0 REV1, May 2024</div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
