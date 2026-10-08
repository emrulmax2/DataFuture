{{--
    Refund Request Form - the third "Do it online" form served from the portal.

    The bank section is the part to be careful with: the account holder is not
    a field, because the College only refunds the student, and nothing the
    student types here is echoed back to them afterwards beyond the last four
    digits of the account.
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
        $accountHolder = trim($student->first_name.' '.$student->last_name);
    @endphp

    <div class="sfm-head">
        <div>
            <div class="sfm-crumb">
                <a href="{{ route('students.dashboard.forms') }}">Do it online</a> &middot; Forms
            </div>
            <h1 class="sfm-title">Refund request</h1>
            <p class="sfm-sub">Use this form to request a refund of tuition fees you have paid to the College.</p>
        </div>
        <div class="sfm-counter" data-sfm-counter>0 of 5 answered</div>
    </div>

    <div class="sfm">
        <div>
            <div class="sfm-notice">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>
                    Before you complete this form, please read the London Churchill College
                    <a href="https://lcc.ac.uk/policies/tuition-fee-and-refund-policy" target="_blank" rel="noopener">Tuition Fee and Refund Policy</a>.
                </span>
            </div>

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
                  action="{{ route('students.doitonline.form.refund.store', $form->id) }}"
                  enctype="multipart/form-data"
                  autocomplete="off"
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
                                <div class="sfm-record__label">LCC student ID</div>
                                <div class="sfm-record__value">{{ $student->registration_no ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Course name</div>
                                <div class="sfm-record__value">{{ $currentCourseName ?: 'Course not set' }}</div>
                            </div>
                            <div>
                                <div class="sfm-record__label">Intake semester</div>
                                <div class="sfm-record__value">{{ $currentIntake ?: '—' }}</div>
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

                    {{-- 2 - how much, and why --}}
                    <section class="sfm-card" data-sfm-section="refund" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">2</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Refund details</div>
                                <div class="sfm-card__sub">How much you are asking for, and why.</div>
                            </div>
                        </div>

                        <div class="sfm-grid">
                            <div>
                                <label for="refund_amount" class="sfm-label">
                                    Refund amount (£) <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-money">
                                    <span class="sfm-money__sign" aria-hidden="true">£</span>
                                    <input type="text"
                                           name="refund_amount"
                                           id="refund_amount"
                                           class="sfm-input"
                                           inputmode="decimal"
                                           value="{{ old('refund_amount') }}"
                                           data-sfm-money
                                           data-sfm-field="refund">
                                </div>
                            </div>

                            <div class="sfm-field--full">
                                <label for="reason" class="sfm-label">
                                    Reasons for refund <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">Briefly describe why you are requesting this refund.</div>
                                <textarea name="reason"
                                          id="reason"
                                          class="sfm-textarea"
                                          data-sfm-field="refund"
                                          data-sfm-counted="reason">{{ old('reason') }}</textarea>
                                <div class="sfm-count" data-sfm-countfor="reason"></div>
                            </div>
                        </div>
                    </section>

                    {{-- 3 - where the money goes --}}
                    <section class="sfm-card" data-sfm-section="bank" data-sfm-done="Completed">
                        <div class="sfm-card__head">
                            <div class="sfm-card__num">3</div>
                            <div class="sfm-card__headtext">
                                <div class="sfm-card__title">Your bank details</div>
                                <div class="sfm-card__sub">UK bank account only. Make sure every detail is correct.</div>
                            </div>
                        </div>

                        <div class="sfm-notice">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <span>
                                <strong>We do not transfer money to any third party.</strong>
                                Refunds are paid only into a bank account in your own name.
                            </span>
                        </div>

                        <div class="sfm-record">
                            <div>
                                <div class="sfm-record__label">Account holder name</div>
                                <div class="sfm-record__value">{{ $accountHolder ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="sfm-note">
                            The account holder name is taken from your student record and can't be changed.
                        </div>

                        <div class="sfm-grid">
                            <div>
                                <label for="account_number" class="sfm-label">
                                    Account number <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">8 digits, for example 01234567.</div>
                                <input type="text"
                                       name="account_number"
                                       id="account_number"
                                       class="sfm-input"
                                       inputmode="numeric"
                                       maxlength="8"
                                       autocomplete="off"
                                       data-sfm-digits="8"
                                       data-sfm-field="bank">
                            </div>

                            <div>
                                <label for="sort_code" class="sfm-label">
                                    Sort code <span class="sfm-req">*</span>
                                </label>
                                <div class="sfm-hint">6 digits, for example 12-34-56.</div>
                                <input type="text"
                                       name="sort_code"
                                       id="sort_code"
                                       class="sfm-input"
                                       inputmode="numeric"
                                       maxlength="8"
                                       autocomplete="off"
                                       data-sfm-sortcode
                                       data-sfm-field="bank">
                            </div>
                        </div>
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
                                I confirm that the above information is correct and accurate to the best of my
                                knowledge, and that the bank account given is in my own name.
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
                <div class="sfm-prog" data-sfm-step="refund">
                    <div class="sfm-prog__name">Refund details</div>
                    <div class="sfm-prog__state" data-sfm-state="refund">0 of 2 answered</div>
                </div>
                <div class="sfm-prog" data-sfm-step="bank">
                    <div class="sfm-prog__name">Your bank details</div>
                    <div class="sfm-prog__state" data-sfm-state="bank">0 of 2 answered</div>
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
                    <div class="sfm-panel__version">Form version LCC1.16.1 REV2, May 2024</div>
                </div>
            </div>
        </aside>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-form.js')
@endsection
