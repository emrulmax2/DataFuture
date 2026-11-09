{{--
    Applicant record header on the admission portal shell: identity card,
    actions, the status journey, contextual notices and the section tabs.

    Status changes keep going through admission-global.js: the anchors it
    binds (.changeApplicantStatus / .rejectApplicationBtn) are rendered
    hidden, and the "Change status" dialog (admission-show.js) clicks the one
    for the chosen status. #rejected_reason lives in that dialog; the confirm
    step reads it by id.
--}}
@php
    $adpFullName = trim(($applicant->title->name ?? '').' '.$applicant->first_name.' '.$applicant->last_name);
    $adpPhotoUrl = (string) $applicant->photo_url;
    $adpHasPhoto = !\App\Support\Avatar::isGenerated($adpPhotoUrl);
    $adpInitials = \App\Support\Avatar::initialsOnly($applicant->first_name.' '.$applicant->last_name);
    $adpStatusId = (int) ($applicant->status_id ?? 0);
    $adpStatusName = $applicant->status->name ?? '—';
    $adpStatusName = $adpStatusName === 'Awating Decision' ? 'Awaiting Decision' : $adpStatusName;
    $adpTone = [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'][$adpStatusId] ?? 'muted';
    $adpReason = ($adpStatusId == 8 && isset($applicant->reason->name) && !empty($applicant->reason->name)) ? $applicant->reason->name : '';
    $adpCourse = trim(($applicant->course->creation->course->name ?? '').(isset($applicant->course->semester->name) ? ' · '.$applicant->course->semester->name : ''));
    $adpEmail = $applicant->users->email ?? '';
    $adpMobile = $applicant->contact->mobile ?? '';
    $adpMobileVerified = isset($applicant->contact->mobile_verification) && $applicant->contact->mobile_verification == 1;
    $adpPending = $applicant->pendingTasks->count() + $applicant->inProgressTasks->count();
    $adpPriv = auth()->user()->priv();

    $adpPath = [1 => 'Submitting', 2 => 'New', 3 => 'In Progress', 4 => 'Awaiting Decision', 5 => 'Accepted', 6 => 'Offer Placed', 7 => 'Offer Accepted'];
    $adpIdx = array_search($adpStatusId, array_keys($adpPath), true);
    $adpBranchFrom = $adpStatusId == 8 ? 4 : ($adpStatusId == 9 ? 6 : ($adpStatusId == 46 ? 3 : null));
    $adpBranchIdx = $adpBranchFrom ? array_search($adpBranchFrom, array_keys($adpPath), true) : null;

    $adpLogs = $applicant->conversionLogs;
    $adpCanEsign = isset($adpPriv['e_signature_request']) && $adpPriv['e_signature_request'] == 1;
    $adpSigned = isset($esignature) && !empty($esignature->signature);
    $adpEsignPending = isset($esignature) && empty($esignature->signature);
    // Login as applicant: the privilege, and only for user 1 (user, 23 Oct 2026).
    $adpCanLoginAs = isset($adpPriv['login_as_applicant']) && $adpPriv['login_as_applicant'] == 1 && (int) auth()->id() === 1;
    // Offer Placed with DocuSeal: the signing moves the status on, so no manual Change status
    // (AdmissionStatusFlow::next). Offer Accepted: the student record it became.
    $adpAutoAccept = \App\Support\AdmissionStatusFlow::autoAccepts();
    $adpOfferForm = in_array($adpStatusId, [6, 9], true) ? \App\Support\AdmissionStatusFlow::latestOfferForm($applicant) : null;
    $adpHeld = $adpStatusId == 6 && $adpOfferForm && $adpOfferForm->outcome === \App\Models\ApplicantDocusealSubmission::OUTCOME_HELD;
    $adpStudent = $adpStatusId == 7 ? \App\Support\AdmissionStatusFlow::studentFor($applicant) : null;
    // View Student opens the student profile only for staff with Dashboard Privileges › Live Student
    // (employee_permissions dashboard_live_student, "live" in priv()); otherwise it is a # link (user, 23 Oct 2026).
    $adpCanViewStudent = isset($adpPriv['live']) && $adpPriv['live'] == 1;
    // Which section tab is current; the page sets $adpTab (details by default).
    $adpTab = $adpTab ?? 'details';
    $adpTabAttr = fn ($key) => $adpTab === $key ? 'class="on" aria-current="page"' : '';
@endphp

@if($adpStatusId == 4)
    <div class="adp-notice adp-notice--ok"><b>Ready for a decision</b><div>Every assigned task is complete, so the status moved to Awaiting Decision. Accept or reject from <b>Change status</b>.</div></div>
@elseif($adpStatusId == 2)
    <div class="adp-notice"><b>New application</b><div>Nobody has started on this yet. The admission tasks were generated on submission; the status moves to In Progress when the first one is completed, and to Awaiting Decision when they are all done.</div></div>
@elseif($adpStatusId == 6 && $adpAutoAccept && $adpHeld)
    <div class="adp-notice adp-notice--warn"><b>Signed — proof of ID needed</b><div>The applicant signed the offer acceptance form{{ $adpOfferForm->completed_at ? ' on '.$adpOfferForm->completed_at->format('d M Y, H:i') : '' }}, but the proof of ID is incomplete, so they have not been moved to Students. Add the missing details, then choose <b>Change status &rsaquo; Offer Accepted</b>.</div></div>
@elseif($adpStatusId == 6 && $adpAutoAccept)
    <div class="adp-notice adp-notice--warn"><b>Waiting for the applicant to sign</b><div>The offer acceptance form was sent with DocuSeal{{ $adpOfferForm && $adpOfferForm->sent_at ? ' on '.$adpOfferForm->sent_at->format('d M Y, H:i') : '' }}. When the applicant signs, the status moves to Offer Accepted and they are moved to Students automatically; if they decline, it moves to Offer Rejected.</div></div>
@elseif($adpStatusId == 6 && $adpEsignPending)
    <div class="adp-notice adp-notice--warn"><b>Waiting for the applicant</b><div>The e-signature request is open; the applicant accepts the offer from their portal.</div></div>
@elseif($adpStatusId == 9)
    <div class="adp-notice adp-notice--bad"><b>Offer rejected</b><div>The applicant declined the offer{{ $adpOfferForm && $adpOfferForm->decline_reason ? ': “'.$adpOfferForm->decline_reason.'”' : '.' }} To make the offer again, choose <b>Change status &rsaquo; Offer Placed</b>; a new offer acceptance form is sent.</div></div>
@elseif($adpStatusId == 7 && !$adpStudent)
    <div class="adp-notice adp-notice--warn"><b>Student record not created yet</b><div>The offer was accepted, but moving the applicant to Students has not finished. See the <a href="{{ route('admission.conversion.log', $applicant->id) }}">conversion log</a>.</div></div>
@endif

<section class="adp-rec-head">
    <div class="adp-rec-id">
        <div class="adp-rec-avatar">
            <span class="adp-avatar">
                @if($adpHasPhoto)
                    <img src="{{ $adpPhotoUrl }}" alt="">
                @else
                    {{ $adpInitials }}
                @endif
            </span>
            <button type="button" class="adp-photo-btn" title="Upload photo" aria-label="Upload photo" data-tw-toggle="modal" data-tw-target="#addApplicantPhotoModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </button>
        </div>
        <div class="adp-rec-copy">
            <div class="adp-rec-title">
                <h1 class="adp-h1 adp-rec-name">{{ $adpFullName }}</h1>
                <span class="adp-pill adp-pill--{{ $adpTone }}"><i></i>{{ $adpStatusName }}</span>
                @if($adpReason !== '')
                    <span class="adp-pill adp-pill--red">{{ $adpReason }}</span>
                @endif
            </div>
            <div class="adp-rec-meta">
                <span><b class="adp-mono">{{ (isset($applicant->application_no) && !empty($applicant->application_no) ? $applicant->application_no : '—') }}</b></span>
                <span class="adp-rec-course">{{ $adpCourse !== '' ? $adpCourse : '—' }}</span>
                <span>Submitted {{ !empty($applicant->submission_date) ? \Carbon\Carbon::parse($applicant->submission_date)->format('d M Y') : '—' }}</span>
            </div>
            <div class="adp-rec-meta">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    {{ $adpEmail !== '' ? $adpEmail : '—' }}
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
                    {{ $adpMobile !== '' ? $adpMobile : '—' }}
                    @if($adpMobile !== '')
                        @if($adpMobileVerified)
                            <span class="adp-flag adp-flag--ok"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>verified</span>
                        @else
                            <span class="adp-flag adp-flag--no"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg>unverified</span>
                        @endif
                    @endif
                </span>
            </div>
        </div>
    </div>
    <div class="adp-rec-actions">
        @if($adpStudent)
            {{-- Offer Accepted: the student record this applicant became (student profile). --}}
            @if($adpCanViewStudent)
            <a href="{{ route('student.show', $adpStudent->id) }}" target="_blank" rel="noopener" class="adp-btn adp-btn--primary" data-adp-view-student title="Open {{ $adpStudent->registration_no ?: 'the student record' }}">
            @else
            <a href="#" class="adp-btn adp-btn--primary" data-adp-view-student aria-disabled="true" title="You need the Live Student dashboard privilege to open the student record">
            @endif
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                View Student
                @if($adpStudent->registration_no)<span class="adp-btn__meta adp-mono">{{ $adpStudent->registration_no }}</span>@endif
            </a>
        @endif
        @if(count($adpNext) > 0)
            <button type="button" class="adp-btn adp-btn--primary" data-adp-status-open>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/></svg>
                Change status
            </button>
        @endif
        <a href="{{ route('admission.communication', $applicant->id) }}" class="adp-btn adp-btn--ghost adp-btn--tint adp-btn--tint-steel">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
            Email
        </a>
        <a href="{{ route('admission.communication', $applicant->id) }}#sms" class="adp-btn adp-btn--ghost adp-btn--tint adp-btn--tint-sage">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>
            SMS
        </a>
        <a href="{{ route('admission.process', $applicant->id) }}" class="adp-btn adp-btn--ghost adp-btn--tint adp-btn--tint-ochre">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
            Tasks
            @if($adpPending > 0)<span class="adp-cnt">{{ $adpPending }}</span>@endif
        </a>
        <a href="{{ route('applicantprofile.print', $applicant->id) }}" class="adp-btn adp-btn--ghost adp-btn--tint adp-btn--tint-plum" title="Print the application form">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            Print
        </a>
        @if($adpCanLoginAs)
            <a target="_blank" rel="noopener" href="{{ route('impersonate', ['id' => $applicant->applicant_user_id, 'guardName' => 'applicant']) }}" class="adp-btn adp-btn--ghost adp-btn--tint adp-btn--tint-gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/></svg>
                Login as applicant
            </a>
        @endif
    </div>

    {{-- The anchors admission-global.js binds; the status dialog clicks them. --}}
    <div hidden aria-hidden="true">
        @foreach($adpNext as $adpN)
            <a href="javascript:void(0);" data-statusid="{{ $adpN['id'] }}" data-applicantid="{{ $applicant->id }}" class="{{ $adpN['via'] }}" data-adp-status-anchor="{{ $adpN['id'] }}">{{ $adpN['name'] }}</a>
        @endforeach
    </div>
</section>

<div class="adp-journey" aria-label="Application progress">
    @foreach($adpPath as $adpS => $adpLabel)
        @php
            $adpI = $loop->index;
            $adpCls = 'adp-journey__node';
            $adpInner = $adpI + 1;
            $adpLabelOut = $adpLabel;
            if ($adpIdx !== false) {
                if ($adpI < $adpIdx) $adpCls .= ' done';
                if ($adpI === $adpIdx) $adpCls .= ' now';
                if ($adpI === $adpIdx && $adpS === 7) $adpCls .= ' done';
            } elseif ($adpBranchIdx !== null) {
                if ($adpI < $adpBranchIdx) $adpCls .= ' done';
                if ($adpI === $adpBranchIdx) { $adpCls .= ' branch now'; $adpLabelOut = $adpStatusName; $adpInner = '!'; }
            }
            $adpIsDone = str_contains($adpCls, ' done') && !str_contains($adpCls, 'now');
        @endphp
        <div class="{{ $adpCls }}">
            <div class="adp-journey__dot">
                @if($adpIsDone)
                    <svg width="11" height="11" viewBox="0 0 10 10" aria-hidden="true"><path d="M1.5 5.2l2.3 2.3 4.7-4.8" stroke="#fff" stroke-width="1.8" fill="none"/></svg>
                @else
                    {{ $adpInner }}
                @endif
            </div>
            <span>{{ $adpLabelOut }}</span>
        </div>
    @endforeach
</div>


<div class="adp-tabs" role="navigation" aria-label="Applicant sections">
    <a href="{{ route('admission.show', $applicant->id) }}" {!! $adpTabAttr('details') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
        Details
    </a>
    <a href="{{ route('admission.process', $applicant->id) }}" {!! $adpTabAttr('process') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>
        Processes
        @if($adpPending > 0)<span class="adp-cnt">{{ $adpPending }}</span>@endif
    </a>
    <a href="{{ route('admission.uploads', $applicant->id) }}" {!! $adpTabAttr('uploads') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        Uploaded files
        @if($applicant->docses->count() > 0)<span class="adp-cnt adp-cnt--muted">{{ $applicant->docses->count() }}</span>@endif
    </a>
    <a href="{{ route('admission.notes', $applicant->id) }}" {!! $adpTabAttr('notes') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        Notes
        @if($applicant->notes->count() > 0)<span class="adp-cnt adp-cnt--muted">{{ $applicant->notes->count() }}</span>@endif
    </a>
    <a href="{{ route('admission.communication', $applicant->id) }}" {!! $adpTabAttr('communication') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        Communication
    </a>
    @if($adpLogs->count() > 0)
        <a href="{{ route('admission.conversion.log', $applicant->id) }}" {!! $adpTabAttr('log') !!}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>
            Conversion log
        </a>
    @endif
    @if($adpCanEsign)
        {{-- The tab opens the e-signature overview; Send request / Resend live there (the prototype's flow). --}}
        <a href="{{ route('admission.show.e.signature', $applicant->id) }}" {!! $adpTabAttr('esign') !!}>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/></svg>
            E-signature
            @if($adpEsignPending)<span class="adp-tab-dot" title="Awaiting signature" aria-label="Awaiting signature"></span>@endif
        </a>
    @endif
    {{-- Everything that happened on the application, oldest first (ApplicantTimeline). --}}
    <a href="{{ route('admission.timeline', $applicant->id) }}" {!! $adpTabAttr('timeline') !!}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Time Line
    </a>
</div>
