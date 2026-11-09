@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        /* The status machine, as the applicant sees it. 1-7 is the main path;
           8, 9 and 46 are branches off the step they can happen at. */
        $apdMainPath = [1, 2, 3, 4, 5, 6, 7];
        $apdTone = [1 => 'muted', 2 => 'muted', 3 => 'amber', 4 => 'blue', 5 => 'green', 6 => 'blue', 7 => 'green', 8 => 'red', 9 => 'red', 46 => 'red'];
        $apdBranchAt = [8 => 4, 9 => 6, 46 => 3];
        $apdStatusName = function ($id) use ($applicantStatuses) {
            return $applicantStatuses[$id] ?? 'Unknown';
        };

        $apdVerified = !empty($user->email_verified_at);
        $apdDraft = $applications->first(function ($a) { return (int) $a->status_id === 1 && empty($a->submission_date); });
        $apdLatest = $applications->first();
        $apdSubmitted = $applications->filter(function ($a) { return !empty($a->submission_date); })->count();
    @endphp

    @if(!$apdVerified)
        <div class="apd-notice apd-notice--warn">
            <b>Verify your email address to start an application</b>
            <div>
                A verification link has been sent to <b style="display:inline">{{ $user->email }}</b>. Your reference number,
                offer letter and acceptance form are all sent to this address, so we need to know it is yours before an
                application can be started or submitted. If it is not in your inbox, please check your Junk/Spam folder.
            </div>
            <div class="apd-notice__actions">
                <form id="resendverification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                    <button id="emailverification" type="submit" class="apd-btn apd-btn--ghost apd-btn--sm">
                        Resend verification email
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if (session('applicantSubmission'))
        <div class="apd-notice apd-notice--ok">{{ session('applicantSubmission') }}</div>
        @php session()->forget('applicantSubmission'); @endphp
    @endif

    {{-- ---------------------------------------------------------------- hero --}}
    <section class="apd-hero" aria-label="Applications overview">
        <div class="apd-hero__top">
            <div style="display:grid;gap:8px">
                <span class="apd-eyebrow">Dashboard</span>
                <h1 class="apd-h1">Your <em>applications</em></h1>
                <p class="apd-hero__sub">
                    {{ $user->email }}
                    @if($apdVerified)
                        <span class="apd-pill apd-pill--green" style="margin-left:6px"><i></i>verified</span>
                    @else
                        <span class="apd-pill apd-pill--amber" style="margin-left:6px"><i></i>unverified</span>
                    @endif
                </p>
            </div>

            @if($apdDraft)
                <a href="{{ route('applicant.application') }}" class="apd-btn apd-btn--primary">
                    Continue draft{{ $apdDraft->first_name ? ' · '.$apdDraft->first_name : '' }}
                </a>
            @elseif($apdVerified)
                <a href="{{ route('applicant.application') }}" class="apd-btn apd-btn--primary">
                    {{ $applications->count() ? 'Start a new application' : 'Start your application' }}
                </a>
            @else
                <div class="apd-locked">
                    <span class="apd-btn apd-btn--primary is-disabled" aria-disabled="true" title="Verify your email first">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        {{ $applications->count() ? 'Start a new application' : 'Start your application' }}
                    </span>
                    <small>Verify your email address first — see the notice above.</small>
                </div>
            @endif
        </div>

        <div class="apd-hero__stats">
            <div class="apd-hero__stat"><small>Applications</small><b>{{ $applications->count() }}</b></div>
            <div class="apd-hero__stat"><small>Submitted</small><b>{{ $apdSubmitted }}</b></div>
            <div class="apd-hero__stat">
                <small>Latest</small>
                <b class="apd-mono">{{ $apdLatest ? ($apdLatest->application_no ?: 'draft') : '—' }}</b>
            </div>
        </div>
    </section>

    {{-- --------------------------------------------------------- empty state --}}
    @if($applications->isEmpty())
        <div class="apd-card" style="padding:26px 28px;display:grid;gap:18px">
            <div style="display:grid;gap:6px">
                <h2 class="apd-h1" style="font-size:26px">Nothing on file yet.</h2>
                <p class="apd-lede" style="font-size:14px;margin:0">
                    Have these to hand before you begin. Everything saves as you go, and your reference is issued after
                    the first step.
                </p>
            </div>
            <div class="apd-checklist">
                <div><b>01</b>Passport or UK ID / BRP</div>
                <div><b>02</b>Proof of your current address</div>
                <div><b>03</b>Highest qualification details</div>
                <div><b>04</b>Funding plan — Student Finance England, sponsor or self</div>
            </div>
            <div>
                @if($apdVerified)
                    <a href="{{ route('applicant.application') }}" class="apd-btn apd-btn--primary">Start your application</a>
                @else
                    <div class="apd-locked" style="justify-items:start;text-align:left">
                        <span class="apd-btn apd-btn--primary is-disabled" aria-disabled="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                            Start your application
                        </span>
                        <small>Verify your email address first — see the notice above.</small>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ---------------------------------------------------- application cards --}}
    @if($applications->isNotEmpty())
        <div style="display:grid;gap:14px">
            @foreach($applications as $apdApp)
                @php
                    $apdStatusId = (int) $apdApp->status_id;
                    $apdIsDraft = $apdStatusId === 1 && empty($apdApp->submission_date);
                    $apdCourse = $apdApp->course->creation->course->name ?? null;
                    $apdSemester = $apdApp->course->semester->name ?? null;
                    $apdVenue = $apdApp->course->venue->name ?? null;
                    $apdIdx = array_search($apdStatusId, $apdMainPath, true);
                    $apdBranch = $apdBranchAt[$apdStatusId] ?? null;
                @endphp
                <article class="apd-dossier">
                    <div class="apd-dossier__in">
                        <div class="apd-dossier__head">
                            <div class="apd-dossier__id">
                                <span class="apd-stamp">
                                    <small>{{ $apdIsDraft ? 'Draft' : 'Reference' }}</small>
                                    <b>{{ $apdApp->application_no ?: '— — — — —' }}</b>
                                </span>
                                <div style="display:grid;gap:4px">
                                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                                        <span class="apd-pill apd-pill--{{ $apdTone[$apdStatusId] ?? 'muted' }}">
                                            <i></i>{{ $apdStatusName($apdStatusId) }}
                                        </span>
                                        @if($apdIsDraft)
                                            <span style="font-size:12.5px;color:var(--muted)">not submitted</span>
                                        @endif
                                    </div>
                                    <div class="apd-dossier__title">
                                        @if($apdCourse)
                                            {{ $apdCourse }}@if($apdSemester)<span> · {{ $apdSemester }}</span>@endif
                                        @else
                                            <span>Course not chosen yet</span>
                                        @endif
                                    </div>
                                    <div class="apd-dossier__meta">
                                        @if($apdVenue){{ $apdVenue }} · @endif
                                        @if($apdIsDraft)
                                            started {{ $apdApp->created_at ? $apdApp->created_at->format('d M Y') : '—' }}
                                        @else
                                            submitted {{ $apdApp->submission_date ?: '—' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="apd-dossier__actions">
                                @if($apdIsDraft)
                                    <a href="{{ route('applicant.application') }}" class="apd-btn apd-btn--primary apd-btn--sm">Continue</a>
                                    <button type="button" data-id="{{ $apdApp->id }}" class="delete_btn apd-btn apd-btn--danger apd-btn--sm">Delete draft</button>
                                @else
                                    <a href="{{ route('applicant.application.show', $apdApp->id) }}" class="apd-btn apd-btn--ghost apd-btn--sm">View application</a>
                                @endif
                            </div>
                        </div>

                        {{-- progress track --}}
                        <div class="apd-journey" aria-label="Application progress">
                            @foreach($apdMainPath as $apdI => $apdStep)
                                @php
                                    $apdCls = 'apd-journey__node';
                                    if ($apdIdx !== false) {
                                        if ($apdI < $apdIdx) { $apdCls .= ' done'; }
                                        if ($apdI === $apdIdx) { $apdCls .= ' now'; }
                                        if ($apdI === $apdIdx && $apdStep === 7) { $apdCls .= ' done'; }
                                    } elseif ($apdBranch !== null) {
                                        $apdBi = array_search($apdBranch, $apdMainPath, true);
                                        if ($apdI < $apdBi) { $apdCls .= ' done'; }
                                        if ($apdI === $apdBi) { $apdCls .= ' branch now'; }
                                    }
                                    $apdDone = str_contains($apdCls, ' done') && !str_contains($apdCls, 'now');
                                    $apdIsBranch = str_contains($apdCls, 'branch');
                                @endphp
                                <div class="{{ $apdCls }}">
                                    <div class="apd-journey__dot">
                                        @if($apdDone)
                                            <svg width="11" height="11" viewBox="0 0 10 10" aria-hidden="true"><path d="M1.5 5.2l2.3 2.3 4.7-4.8" stroke="#fff" stroke-width="1.8" fill="none"/></svg>
                                        @else
                                            {{ $apdIsBranch ? '!' : $apdI + 1 }}
                                        @endif
                                    </div>
                                    <span>{{ $apdIsBranch ? $apdStatusName($apdStatusId) : $apdStatusName($apdStep) }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($apdStatusId === 8 && !empty($apdApp->application_rejected_reason_id))
                            <div style="font-size:13.5px">
                                <span class="apd-eyebrow" style="margin-right:8px">Reason</span>
                                {{ optional($apdApp->reason)->name ?? 'Recorded by admissions' }}
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    {{-- ------------------------------------------------------------- legend --}}
    <div style="display:grid;gap:10px;padding-top:8px">
        <h3 class="apd-h3">What the statuses mean</h3>
        <dl class="apd-kv" style="font-size:13.5px;grid-template-columns:minmax(150px,190px) 1fr">
            @foreach([1 => 'Draft you are still filling in. Only you can see it; you can delete it.',
                      2 => 'Submitted and received. Reference number issued.',
                      3 => 'Admissions are running checks (documents, interview, English test).',
                      4 => 'Checks complete; with the admissions panel.',
                      5 => 'Conditional Offer Letter emailed.',
                      6 => 'Offer acceptance form emailed for signature.',
                      7 => 'Signed and confirmed; you become a student.',
                      8 => 'Unsuccessful; rejection letter emailed. You may apply again.',
                      9 => 'Offer declined.'] as $apdSid => $apdMeaning)
                <dt><span class="apd-pill apd-pill--{{ $apdTone[$apdSid] ?? 'muted' }}"><i></i>{{ $apdStatusName($apdSid) }}</span></dt>
                <dd>{{ $apdMeaning }}</dd>
            @endforeach
        </dl>
    </div>

    @if (session('verifymessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Email Sent!</div>
                <div class="text-slate-500 mt-1">{{ session('verifymessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc">Do you really want to delete this incomplete application? This action cannot be undone.</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" class="agreeWith btn btn-danger w-auto">Yes, I agree
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    <script type="module">
        (function () {
            if ($('#success-notification-toggle').length > 0) {
                $("#success-notification-toggle").trigger('click')
            }
        })()
    </script>
    @vite('resources/js/applicant-dahsboard.js')
@endsection
