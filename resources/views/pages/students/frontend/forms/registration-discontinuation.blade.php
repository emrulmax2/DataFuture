{{--
    Registration Discontinuation Form - the second "Do it online" form served
    from the portal. Same shell as the course change form (`sfm-`), so the two
    read as one family; what differs is the two yes/no questions, their
    follow-ups, and the device note under the declaration.
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
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">Registration discontinuation</h1>
            <p class="sfm-sub">Use this form to tell the College that you want to discontinue your registration.</p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 5 answered</div>
    </div>

    <div class="sfm">
        <div>
            @if($errors->any())
                <div class="sfm-alert">
                    <strong>We could not submit the form.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('students.doitonline.form.discontinuation.store', $form->id) }}"
                  enctype="multipart/form-data"
                  data-sfm-form>
                @csrf

                <div class="sfm-steps">
                    {{-- 1 - from the student record, nothing to answer --}}
                    <section class="sfm-card" data-sfm-section="details" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">1</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Student's details</div>
                                <div class="sfm-card__sub">Filled in from your student record.</div>
                            </div>
                            <div class="sfm-tag"><i data-lucide="lock" class="w-3 h-3"></i> Read only</div>
                        </div>

                        <div class="sfm-record">
                            <div>
                                <div class="sfm-record__label">Full name</div>
                                <div class="sfm-record__value">{{ $fullName ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Student ID</div>
                                <div class="sfm-record__value">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Course</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Email</div>
                                <div class="sfm-record__value">{{ $studentEmail ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Mobile number</div>
                                <div class="sfm-record__value">{{ $studentMobile ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="sfm-note">
                            You can't change these details here. If anything is wrong, contact the College Registry.
                        </div>
                    </section>

                    {{-- 2 - when and why --}}
                    <section class="sfm-card" data-sfm-section="discontinuation" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Discontinuation details</div>
                                <div class="sfm-card__sub">When you want to stop, and why.</div>
                            </div>
                        </div>

                        <div class="sfm-grid">
                            <div>
                                <label for="effective_from" class="sfm-label">
                                    Effective from <span class="sfm-req">*</span>
                                </label>
                                {{-- Litepicker, like every other date field in the app. --}}
                                <input type="text"
                                       name="effective_from"
                                       id="effective_from"
                                       class="sfm-input"
                                       value="{{ old('effective_from') }}"
                                       placeholder="DD-MM-YYYY"
                                       autocomplete="off"
                                       data-sfm-datepicker
                                       data-sfm-field="discontinuation">
                            </div>

                            <div class="sfm-field--full">
                                <label for="reason" class="sfm-label">
                                    Reasons for discontinuation <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">Explain why you want to discontinue your registration.</div>
                                <textarea name="reason"
                                          id="reason"
                                          class="sfm-textarea"
                                          data-sfm-field="discontinuation"
                                          data-sfm-counted="reason">{{ old('reason') }}</textarea>
                                <div class="sfm-count" data-sfm-countfor="reason"></div>
                            </div>
                        </div>
                    </section>

                    {{-- 3 - optional, so it never holds the progress back --}}
                    <section class="sfm-card" data-sfm-section="further" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Further information</div>
                                <div class="sfm-card__sub">Two short questions to help us understand your decision.</div>
                            </div>
                            <div class="sfm-tag">Optional</div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">Did you discuss your withdrawal with a member of staff at LCC?</legend>
                            <div class="sfm-choices">
                                <label class="sfm-choice">
                                    <input type="radio" name="discussed_with_staff" value="yes"
                                           {{ old('discussed_with_staff') === 'yes' ? 'checked' : '' }}> Yes
                                </label>
                                <label class="sfm-choice">
                                    <input type="radio" name="discussed_with_staff" value="no"
                                           {{ old('discussed_with_staff') === 'no' ? 'checked' : '' }}> No
                                </label>
                            </div>

                            {{-- Shown only on "yes"; its fields are disabled while
                                 hidden so a stale answer cannot be posted. --}}
                            <div class="sfm-followup" data-sfm-when="discussed_with_staff:yes" hidden>
                                <label for="staff_details" class="sfm-label">Staff member's details</label>
                                <div class="sfm-hint">Their name, and their role or department if you know it.</div>
                                <input type="text"
                                       name="staff_details"
                                       id="staff_details"
                                       class="sfm-input"
                                       value="{{ old('staff_details') }}"
                                       disabled>
                            </div>
                        </fieldset>

                        <hr class="sfm-divider">

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">Did you get an offer from another institute?</legend>
                            <div class="sfm-choices">
                                <label class="sfm-choice">
                                    <input type="radio" name="offer_from_other_institute" value="yes"
                                           {{ old('offer_from_other_institute') === 'yes' ? 'checked' : '' }}> Yes
                                </label>
                                <label class="sfm-choice">
                                    <input type="radio" name="offer_from_other_institute" value="no"
                                           {{ old('offer_from_other_institute') === 'no' ? 'checked' : '' }}> No
                                </label>
                            </div>

                            <div class="sfm-followup" data-sfm-when="offer_from_other_institute:yes" hidden>
                                <div class="sfm-grid">
                                    <div>
                                        <label for="institute_name" class="sfm-label">Institute name</label>
                                        <input type="text"
                                               name="institute_name"
                                               id="institute_name"
                                               class="sfm-input"
                                               value="{{ old('institute_name') }}"
                                               disabled>
                                    </div>
                                    <div>
                                        <label for="institute_start_date" class="sfm-label">Start date</label>
                                        <input type="text"
                                               name="institute_start_date"
                                               id="institute_start_date"
                                               class="sfm-input"
                                               value="{{ old('institute_start_date') }}"
                                               placeholder="DD-MM-YYYY"
                                               autocomplete="off"
                                               data-sfm-datepicker
                                               disabled>
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    </section>

                    {{-- 4 - optional uploads --}}
                    <section class="sfm-card" data-sfm-section="documents" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">4</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Supporting documents</div>
                                <div class="sfm-card__sub">Attach anything that supports your request.</div>
                            </div>
                            <div class="sfm-tag">Optional</div>
                        </div>

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

                        <label class="sfm-declare">
                            <input type="checkbox"
                                   name="declaration"
                                   value="1"
                                   data-sfm-field="declaration"
                                   {{ old('declaration') ? 'checked' : '' }}>
                            <span class="sfm-declare__text">
                                I confirm that, to the best of my knowledge, the information in this form is correct.
                                I understand that discontinuing my registration may affect my fees, funding and visa
                                status, and that the College will contact me before it is finalised.
                            </span>
                        </label>

                        {{-- The script fills these; the IP and user agent are read
                             from the request itself, where they cannot be edited. --}}
                        <input type="hidden" name="device_type" data-sfm-device="type">
                        <input type="hidden" name="device_os" data-sfm-device="os">
                        <input type="hidden" name="device_browser" data-sfm-device="browser">
                        <input type="hidden" name="device_screen" data-sfm-device="screen">

                        <div class="sfm-note">
                            When you submit, your IP address and the type of device you are using are recorded
                            with your request.
                        </div>

                        <div class="sfm-actions">
                            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Cancel</a>
                            <button type="submit" class="sfm-btn">Submit request</button>
                        </div>
                    </section>
                </div>
            </form>
        </div>

        <aside class="sfm-aside">
            <div class="sfm-panel">
                <div class="sfm-panel__title">Form progress</div>

                <div class="sfm-prog sfm-prog--done" data-sfm-step="details">
                    <div class="sfm-prog__name">Student's details</div>
                    <div class="sfm-prog__state" data-sfm-state="details">From your record</div>
                </div>
                <div class="sfm-prog" data-sfm-step="discontinuation">
                    <div class="sfm-prog__name">Discontinuation details</div>
                    <div class="sfm-prog__state" data-sfm-state="discontinuation">0 of 2 answered</div>
                </div>
                <div class="sfm-prog sfm-prog--done" data-sfm-step="further">
                    <div class="sfm-prog__name">Further information</div>
                    <div class="sfm-prog__state" data-sfm-state="further">Optional</div>
                </div>
                <div class="sfm-prog sfm-prog--done" data-sfm-step="documents">
                    <div class="sfm-prog__name">Supporting documents</div>
                    <div class="sfm-prog__state" data-sfm-state="documents">Optional</div>
                </div>
                <div class="sfm-prog" data-sfm-step="declaration">
                    <div class="sfm-prog__name">Declaration</div>
                    <div class="sfm-prog__state" data-sfm-state="declaration">Not yet confirmed</div>
                </div>

                <div class="sfm-panel__foot">
                    Fields marked <span class="sfm-req">*</span> are required.
                    @if($submitted)
                        <div style="margin-top:8px">
                            Last request: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->ticket_ref ?: $submitted->status }}
                        </div>
                    @endif
                    <div class="sfm-panel__version">Form version LCC1.26.1 REV4, May 2024</div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
