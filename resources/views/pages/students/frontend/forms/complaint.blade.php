{{--
    Complaint Form Stage 1 - the seventh "Do it online" form in the portal.

    "Relationship to LCC" is read-only: the paper form asks it because staff and
    visitors use it too, and only a signed-in student can reach this page.
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
            <h1 class="sfm-title">Complaint: Stage 1</h1>
            <p class="sfm-sub">Use this form to make a formal complaint to the College.</p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 5 answered</div>
    </div>

    <div class="sfm">
        <div>
            <section class="sfm-card sfm-before">
                <div class="sfm-eyebrow">Before you start</div>
                <ul class="sfm-guidance">
                    <li>
                        Please read the
                        <a href="https://lcc.ac.uk/policies/complaints-policy-and-procedure/" target="_blank" rel="noopener">Complaints Policy and Procedure</a>
                        before completing this form.
                    </li>
                    <li>If you need help completing the form, you should contact your Personal Tutor at LCC.</li>
                </ul>
            </section>

            @if($errors->any())
                <div class="sfm-alert">
                    <strong>We could not submit your complaint.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('students.doitonline.form.complaint.store', $form->id) }}"
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
                                <div class="sfm-record__label">Relationship to LCC</div>
                                <div class="sfm-record__value">Student</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Programme / course</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Student ID number</div>
                                <div class="sfm-record__value">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Email</div>
                                <div class="sfm-record__value">{{ $studentEmail ?: '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Contact number</div>
                                <div class="sfm-record__value">{{ $studentMobile ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="sfm-note">
                            You can't change these details here. If anything is wrong, contact the College Registry.
                        </div>
                    </section>

                    {{-- 2 - the complaint itself --}}
                    <section class="sfm-card" data-sfm-section="complaint" data-sfm-pending="Not yet answered" data-sfm-done="Complete">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Your complaint</div>
                                <div class="sfm-card__sub">What happened, in your own words.</div>
                            </div>
                        </div>

                        <div>
                            <label for="complaint_details" class="sfm-label">
                                Please explain your complaint in detail <span class="sfm-req">*</span>
                            </label>
                            <textarea name="complaint_details"
                                      id="complaint_details"
                                      class="sfm-textarea sfm-textarea--tall"
                                      data-sfm-field="complaint"
                                      data-sfm-counted="complaint">{{ old('complaint_details') }}</textarea>
                            <div class="sfm-count" data-sfm-countfor="complaint"></div>
                        </div>
                    </section>

                    {{-- 3 - what has been tried --}}
                    <section class="sfm-card" data-sfm-section="resolution" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Resolving your complaint</div>
                                <div class="sfm-card__sub">What has been tried so far, and what you would like to happen.</div>
                            </div>
                        </div>

                        <fieldset class="sfm-question">
                            <legend class="sfm-label">Have you spoken to anyone about your complaint?</legend>
                            <div class="sfm-hint">
                                For example your Personal Tutor or a Lecturer. This question is optional.
                            </div>
                            <div class="sfm-choices">
                                <label class="sfm-choice">
                                    <input type="radio" name="spoken_to_anyone" value="yes"
                                           {{ old('spoken_to_anyone') === 'yes' ? 'checked' : '' }}> Yes
                                </label>
                                <label class="sfm-choice">
                                    <input type="radio" name="spoken_to_anyone" value="no"
                                           {{ old('spoken_to_anyone') === 'no' ? 'checked' : '' }}> No
                                </label>
                            </div>

                            {{-- Shown only on "yes"; disabled while hidden so a
                                 changed mind cannot post a stale answer. --}}
                            <div class="sfm-followup" data-sfm-when="spoken_to_anyone:yes" hidden>
                                <label for="spoken_to_details" class="sfm-label">Please provide their details</label>
                                <div class="sfm-hint">Their name, and their role if you know it.</div>
                                <input type="text"
                                       name="spoken_to_details"
                                       id="spoken_to_details"
                                       class="sfm-input"
                                       value="{{ old('spoken_to_details') }}"
                                       disabled>
                            </div>
                        </fieldset>

                        <hr class="sfm-divider">

                        <div>
                            <label for="attempts" class="sfm-label">
                                Please explain how you have attempted to resolve your complaint so far and why you
                                remain dissatisfied <span class="sfm-req">*</span>
                            </label>
                            <textarea name="attempts"
                                      id="attempts"
                                      class="sfm-textarea"
                                      data-sfm-field="resolution">{{ old('attempts') }}</textarea>
                        </div>

                        <div>
                            <label for="desired_outcome" class="sfm-label">
                                Please explain what you would like to happen to resolve your complaint
                                <span class="sfm-req">*</span>
                            </label>
                            <textarea name="desired_outcome"
                                      id="desired_outcome"
                                      class="sfm-textarea"
                                      data-sfm-field="resolution">{{ old('desired_outcome') }}</textarea>
                        </div>
                    </section>

                    {{-- 4 - optional uploads --}}
                    <section class="sfm-card" data-sfm-section="documents" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">4</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Supporting documents</div>
                                <div class="sfm-card__sub">Attach anything that supports your complaint.</div>
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

                        <div class="sfm-declaration">
                            <ul class="sfm-guidance">
                                <li>
                                    I agree that information about my complaint may be gathered from within the College
                                    by members of the staff as directed by the Registrar or those investigating the
                                    complaint.
                                </li>
                                <li>
                                    Experience has demonstrated that in order to investigate complaints properly and to
                                    balance fairness with the rights of the person about whom the complaint is made,
                                    disclosure is needed, and accordingly I agree that my name and other necessary
                                    information about the complaint may be disclosed in order to investigate it.
                                </li>
                                <li>
                                    If I choose to appoint a representative to act on my behalf, I provide London
                                    Churchill College with my explicit permission to receive information on my behalf
                                    and provide appropriate responses to my Nominated Representative.
                                </li>
                                <li>
                                    In giving this permission, I agree to the sharing of information which, if relevant,
                                    may be of a personal or private nature, including sensitive data as defined by the
                                    General Data Protection Regulations 2018.
                                </li>
                                <li>
                                    Cancellation of this authority to act can be made at any time and must be made by
                                    the Complainant in writing to the Registrar on email
                                    <a href="mailto:registry.Support@lcc.ac.uk">registry.Support@lcc.ac.uk</a>
                                    or/and
                                    <a href="mailto:registrar@londonchurchillcollege.ac.uk">registrar@londonchurchillcollege.ac.uk</a>.
                                </li>
                                <li>
                                    I also understand and accept that the outcome of formal complaints must be recorded
                                    for the purposes of monitoring and analysing complaints generally, and for reporting
                                    to Academic Board for monitoring and evaluation in terms of quality assurance in
                                    line with QAA UK Quality Code.
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
                            with your complaint.
                        </div>

                        <div class="sfm-actions">
                            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Cancel</a>
                            <button type="submit" class="sfm-btn">Submit complaint</button>
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
                <div class="sfm-prog" data-sfm-step="complaint">
                    <div class="sfm-prog__name">Your complaint</div>
                    <div class="sfm-prog__state" data-sfm-state="complaint">Not yet answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="resolution">
                    <div class="sfm-prog__name">Resolving your complaint</div>
                    <div class="sfm-prog__state" data-sfm-state="resolution">0 of 2 answered</div>
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
                            Last complaint: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->ticket_ref ?: $submitted->status }}
                        </div>
                    @endif
                    <div class="sfm-panel__version">
                        LCC Complaint Form ST1 v2.0 &middot; Version LCC1.6.1 REV1, May 2024
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
