{{--
    Course Change Request Form - the first "Do it online" form served from the
    portal rather than Google Forms. Reached through the dynamic form route
    (students.doitonline.form.show) so the "Do it online" list needs no special
    case beyond StudentFormController::internalPage().

    The portal rail is hidden ($hideRail) and the width used for the progress
    panel on the right, which `student-form.js` keeps in step with the fields.
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
        $justSubmitted = session('course_change_submitted');
        $fullName = trim(($student->title->name ?? '').' '.$student->first_name.' '.$student->last_name);
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">{{ $form->form_name }}</h1>
            <p class="sfm-sub">Use this form to request a move from your current course to a different one.</p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 4 answered</div>
    </div>

    <div class="sfm">
        <div>
            @if($justSubmitted)
                @php
                    /* The reference comes from the Service Desk ticket, so it
                       only exists once Operations has taken the request. */
                    $raised = $submitted && $submitted->id == $justSubmitted ? $submitted : null;
                @endphp
                <div class="sfm-flash">
                    <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    <div>
                        <div class="sfm-flash__title">Request submitted</div>
                        <div class="sfm-flash__text">
                            @if($raised && $raised->ticket_ref)
                                Your request is with the College Registry under reference
                                <strong>{{ $raised->ticket_ref }}</strong>. We have emailed you a copy of what you
                                submitted — please quote the reference if you contact us about it.
                            @else
                                Your request has been received and is being passed to the College Registry.
                                We will email you the reference shortly.
                            @endif
                        </div>
                    </div>
                </div>
            @endif

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
                  action="{{ route('students.doitonline.form.course-change.store', $form->id) }}"
                  enctype="multipart/form-data"
                  data-sfm-form>
                @csrf

                <div class="sfm-steps">
                    {{-- 1 - from the student record, nothing to answer --}}
                    <section class="sfm-card"
                             data-sfm-section="details"
                             data-sfm-count="false">
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
                                <div class="sfm-record__label">Current course</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Current course start date</div>
                                <div class="sfm-record__value">
                                    @if($currentCourseStart && !empty((string) $currentCourseStart))
                                        {{ date('j F Y', strtotime((string) $currentCourseStart)) }}
                                    @elseif($currentIntake)
                                        {{ $currentIntake }} intake
                                    @else
                                        —
                                    @endif
                                </div>
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

                    {{-- 2 - the request itself --}}
                    <section class="sfm-card"
                             data-sfm-section="course"
                             data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">New course details</div>
                                <div class="sfm-card__sub">The course you would like to move to.</div>
                            </div>
                        </div>

                        <div class="sfm-grid">
                            <div>
                                <label for="proposed_course_id" class="sfm-label">
                                    Proposed course name <span class="sfm-req">*</span>
                                </label>
                                <select name="proposed_course_id"
                                        id="proposed_course_id"
                                        class="sfm-select"
                                        data-sfm-field="course">
                                    <option value="">Select a course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                                {{ old('proposed_course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="proposed_start_date" class="sfm-label">
                                    Course start date <span class="sfm-req">*</span>
                                </label>
                                {{-- Litepicker, like every other date field in
                                     the app; it posts DD-MM-YYYY. --}}
                                <input type="text"
                                       name="proposed_start_date"
                                       id="proposed_start_date"
                                       class="sfm-input"
                                       value="{{ old('proposed_start_date') }}"
                                       placeholder="DD-MM-YYYY"
                                       autocomplete="off"
                                       data-sfm-datepicker
                                       data-sfm-field="course">
                            </div>

                            <div class="sfm-field--full">
                                <label for="reason" class="sfm-label">
                                    Reasons for change <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">Briefly explain why you want to change course.</div>
                                <textarea name="reason"
                                          id="reason"
                                          class="sfm-textarea"
                                          data-sfm-field="course"
                                          data-sfm-counted="reason">{{ old('reason') }}</textarea>
                                <div class="sfm-count" data-sfm-countfor="reason"></div>
                            </div>
                        </div>
                    </section>

                    {{-- 3 - optional, so it never holds the progress back --}}
                    <section class="sfm-card"
                             data-sfm-section="documents"
                             data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Supporting documents</div>
                                <div class="sfm-card__sub">Anything that helps us understand your request.</div>
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
                            <div class="sfm-drop__hint">PDF, Word or image files, up to 10MB each.</div>
                        </label>

                        <div class="sfm-files" data-sfm-filelist></div>
                    </section>

                    {{-- 4 - declaration --}}
                    <section class="sfm-card"
                             data-sfm-section="declaration"
                             data-sfm-pending="Not yet confirmed"
                             data-sfm-done="Confirmed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">4</div>
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
                                I confirm that the information I have given is true and complete. I understand that
                                a course change is not final until the College Registry has confirmed it, and that it
                                may affect my fees, funding and timetable.
                            </span>
                        </label>

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
                <div class="sfm-prog" data-sfm-step="course">
                    <div class="sfm-prog__name">New course details</div>
                    <div class="sfm-prog__state" data-sfm-state="course">0 of 3 answered</div>
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
                    @if($submitted && !$justSubmitted)
                        <div style="margin-top:8px">
                            Last request: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->status }}
                        </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
