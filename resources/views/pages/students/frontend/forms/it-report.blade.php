{{--
    Report an IT issue on campus - the eighth "Do it online" form in the portal.

    The one form where the student chooses the issue type. The options come from
    Operations' own Service Desk settings (IT & Monitoring, types opened to
    students), so the dropdown always matches what that end will accept; when
    the list cannot be fetched the form says so and will not submit.

    The older in-house page at /students/report-any-it-student is untouched and
    still works; this one raises a Service Desk ticket instead.
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
        $typesAvailable = count($issueTypes) > 0;
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">Report an IT issue</h1>
            <p class="sfm-sub">
                Tell IT &amp; Monitoring about a problem on campus — a room, a computer, your account or your access.
            </p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 3 answered</div>
    </div>

    <div class="sfm">
        <div>
            @if($errors->any())
                <div class="sfm-alert">
                    <strong>We could not submit your report.</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('students.doitonline.form.it-report.store', $form->id) }}"
                  enctype="multipart/form-data"
                  data-sfm-form>
                @csrf

                {{-- Nothing can be raised without an issue type, so the form
                     says so and blocks rather than failing on submit. --}}
                @unless($typesAvailable)
                    <div class="sfm-notice sfm-notice--warning"
                         data-sfm-blocks="The IT service desk is unavailable at the moment.">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>
                            We cannot reach the IT service desk at the moment, so this form cannot be submitted.
                            Please try again shortly.
                        </span>
                    </div>
                @endunless

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
                            IT will use these details to get back to you. If anything is wrong, contact the College Registry.
                        </div>
                    </section>

                    {{-- 2 - the issue --}}
                    <section class="sfm-card" data-sfm-section="issue" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">About the issue</div>
                                <div class="sfm-card__sub">What is wrong, and where.</div>
                            </div>
                        </div>

                        <div class="sfm-grid">
                            <div class="sfm-field--full">
                                <label for="issue_type_id" class="sfm-label">
                                    What is the issue about? <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">
                                    The list is set by IT &amp; Monitoring. If nothing fits, choose the closest and
                                    explain below.
                                </div>
                                <select name="issue_type_id"
                                        id="issue_type_id"
                                        class="sfm-select"
                                        data-sfm-field="issue"
                                        {{ $typesAvailable ? '' : 'disabled' }}>
                                    <option value="">Please select</option>
                                    @foreach($issueTypes as $type)
                                        <option value="{{ $type['id'] }}"
                                                {{ (string) old('issue_type_id') === (string) $type['id'] ? 'selected' : '' }}>
                                            {{ $type['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="venue_id" class="sfm-label">
                                    Which campus has the issue? <span class="sfm-req">*</span>
                                </label>
                                <select name="venue_id" id="venue_id" class="sfm-select" data-sfm-field="issue">
                                    <option value="">Please select</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}"
                                                {{ (string) old('venue_id') === (string) $venue->id ? 'selected' : '' }}>
                                            {{ $venue->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="location" class="sfm-label">Location of issue</label>
                                <input type="text"
                                       name="location"
                                       id="location"
                                       class="sfm-input"
                                       placeholder="e.g. Room 101, Library"
                                       value="{{ old('location') }}">
                                {{-- Below the field: it is an example of what to
                                     type, not something to read beforehand. --}}
                                <div class="sfm-hint sfm-hint--after">Room, area or desk number.</div>
                            </div>

                            <div class="sfm-field--full">
                                <label for="description" class="sfm-label">
                                    Description of issue <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">Please provide as much detail as possible.</div>
                                <textarea name="description"
                                          id="description"
                                          class="sfm-textarea"
                                          data-sfm-field="issue"
                                          data-sfm-counted="description">{{ old('description') }}</textarea>
                                <div class="sfm-count" data-sfm-countfor="description"></div>
                            </div>
                        </div>
                    </section>

                    {{-- 3 - optional attachments --}}
                    <section class="sfm-card" data-sfm-section="documents" data-sfm-count="false">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Attachments</div>
                                <div class="sfm-card__sub">A photo of the problem often says more than a paragraph.</div>
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

                        {{-- The script fills these; the IP and user agent are read
                             from the request itself, where they cannot be edited. --}}
                        <input type="hidden" name="device_type" data-sfm-device="type">
                        <input type="hidden" name="device_os" data-sfm-device="os">
                        <input type="hidden" name="device_browser" data-sfm-device="browser">
                        <input type="hidden" name="device_screen" data-sfm-device="screen">

                        <div class="sfm-note">
                            When you submit, your IP address and the type of device you are using are recorded
                            with your report.
                        </div>

                        <div class="sfm-actions">
                            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Cancel</a>
                            <button type="submit" class="sfm-btn">Submit report</button>
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
                <div class="sfm-prog" data-sfm-step="issue">
                    <div class="sfm-prog__name">About the issue</div>
                    <div class="sfm-prog__state" data-sfm-state="issue">0 of 3 answered</div>
                </div>
                <div class="sfm-prog sfm-prog--done" data-sfm-step="documents">
                    <div class="sfm-prog__name">Attachments</div>
                    <div class="sfm-prog__state" data-sfm-state="documents">Optional</div>
                </div>

                <div class="sfm-panel__foot">
                    Fields marked <span class="sfm-req">*</span> are required.
                    @if($submitted)
                        <div style="margin-top:8px">
                            Last report: {{ date('j M Y', strtotime($submitted->created_at)) }}
                            &middot; {{ $submitted->ticket_ref ?: $submitted->status }}
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
