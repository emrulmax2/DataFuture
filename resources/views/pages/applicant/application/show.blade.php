@extends('../layout/' . $layout)

{{--
    The applicant's own application, read back (user, 29 Oct 2026): the same layout as the form's
    "Review & apply" step — step head, white sheet, the components.applicant.review sections and
    the declaration — on the applicant portal shell. Read-only: it says what can be done next for
    the application's status. The page before this redesign is show-legacy.blade.php.
--}}

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $ref = $applicant->application_no ?: $applicant->id;
        $statusId = (int) $applicant->status_id;
        $statusName = optional($applicant->status)->name ?: 'Unknown';
        $tone = [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'][$statusId] ?? 'muted';
        $rawSubmitted = $applicant->getRawOriginal('submission_date');
        $submitted = $rawSubmitted ? \Carbon\Carbon::parse($rawSubmitted)->format('j F Y') : null;
        $isDraft = !$submitted;
        $isClosed = in_array($statusId, [8, 9, 46], true);
        $course = optional(optional(optional($applicant->course)->creation)->course)->name;
        $intake = optional(optional($applicant->course)->semester)->name;
        $admissions = (string) config('services.docuseal.contact_email', 'admission@lcc.ac.uk');
    @endphp

    <div class="apd-form apd-appview">
        <div class="apd-step-head">
            <span class="apd-eyebrow">Application &middot; Ref {{ $ref }}</span>
            <h1 class="apd-step-title">Your application</h1>
            <div class="apd-appview__meta">
                <span class="apd-pill apd-pill--{{ $tone }}"><i></i>{{ $statusName }}</span>
                <span>
                    {{ $course ?: 'Course not chosen yet' }}@if($intake) &middot; {{ $intake }}@endif
                    &middot; {{ $submitted ? 'submitted '.$submitted : 'not submitted yet' }}
                </span>
            </div>
        </div>

        <div class="apd-sheet">
            <div class="apd-review__wrap">
                @if($isDraft)
                    <div class="apd-notice apd-notice--warn">
                        <b>This application has not been submitted yet.</b>
                        Finish the remaining steps and apply when you are ready; until then the admissions team cannot see it.
                    </div>
                @elseif($statusId === 7)
                    <div class="apd-notice apd-notice--ok">
                        <b>Congratulations, your place is confirmed.</b>
                        You can now manage your studies in the student portal.
                        <div class="apd-notice__actions">
                            <a href="{{ route('students.login') }}" target="_blank" rel="noopener" class="apd-btn apd-btn--primary apd-btn--sm">Log in to the student portal</a>
                        </div>
                    </div>
                @elseif($isClosed)
                    <div class="apd-notice apd-notice--bad">
                        <b>This application is closed ({{ $statusName }}).</b>
                        Below is what you sent. If you have a question about it, email
                        <a href="mailto:{{ $admissions }}">{{ $admissions }}</a> quoting reference {{ $ref }}.
                    </div>
                @else
                    <div class="apd-notice">
                        <b>This is what you sent on {{ $submitted }}.</b>
                        It is with the admissions team and can no longer be changed. If something here is wrong, email
                        <a href="mailto:{{ $admissions }}">{{ $admissions }}</a> quoting reference {{ $ref }}.
                    </div>
                @endif

                @include('components.applicant.review', ['applicant' => $applicant])

                <div class="apd-sub apd-declaration">
                    <h4 class="apd-sub__title">Declaration</h4>
                    <p class="apd-prose">
                        I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my
                        knowledge. It is my responsibility to stay informed about the terms and conditions as well as the policies
                        of the college, and I commit to comply with them. I have thoroughly reviewed the college's terms and
                        conditions and student privacy policy and pledge to adhere to them throughout my entire course of study.
                    </p>
                    @if($submitted)
                        <label class="apd-opt chk apd-appview__agreed">
                            <input class="sr" type="checkbox" checked disabled>
                            <span class="box"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span>
                            <span>You agreed to the declaration when you applied on {{ $submitted }}.</span>
                        </label>
                    @else
                        <p class="apd-note">You agree to the declaration when you apply.</p>
                    @endif
                </div>
            </div>
        </div><!-- /.apd-sheet -->

        <div class="apd-nav-row">
            <a href="{{ route('applicant.dashboard') }}" class="apd-btn apd-btn--ghost">&larr; Back to dashboard</a>
            @if($isDraft)
                <a href="{{ route('applicant.application') }}" class="apd-btn apd-btn--primary">Continue application &rarr;</a>
            @endif
        </div>
    </div>
@endsection
