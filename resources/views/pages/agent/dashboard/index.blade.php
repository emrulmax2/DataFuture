@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $tone = function ($status) {
            return match ($status) {
                'In Progress' => 'amber',
                'Awating Decision', 'Awaiting Decision', 'Offer Placed' => 'blue',
                'Accepted', 'Offer Accepted' => 'green',
                'Rejected', 'Offer Rejected', 'Canceled' => 'red',
                default => 'muted',
            };
        };
    @endphp

    {{-- The pending verification records, for the side column. One renderer
         (agent-dahsboard.js) draws them on load and after every add, verify,
         resend and remove, from the list those endpoints return. --}}
    @php
        /* @json splits its argument on commas (the second part is the flags),
           so the shape is built here and the directive gets one variable. */
        $agpChecks = $recentData->map(fn($c) => ['id' => $c->id, 'first_name' => $c->first_name, 'last_name' => $c->last_name, 'email' => $c->email, 'mobile' => $c->mobile, 'email_verified_at' => $c->email_verified_at, 'mobile_verified_at' => $c->mobile_verified_at])->values();
    @endphp
    <script type="application/json" id="agpChecks">@json($agpChecks)</script>

    @if(!$verified)
        <div class="apd-notice apd-notice--warn">
            <b>Verify your email address to add applicants</b>
            <div>We sent a verification link to <b style="display:inline">{{ $user->email }}</b>. Check your Junk or Spam folder if you do not see it. Adding applicants and starting applications unlocks once the address is verified.</div>
            <div class="apd-notice__actions">
                <form id="resendverification" method="post" action="{{ route('agent.verification.send') }}">
                    @csrf
                    <button type="submit" class="apd-btn apd-btn--ghost apd-btn--sm">Resend verification email</button>
                </form>
            </div>
        </div>
    @endif

    @if(session('verifymessage'))
        <div class="apd-notice apd-notice--ok"><b>Email sent</b><div>{{ session('verifymessage') }}</div></div>
    @endif
    @if(session('verifySuccessMessage') || session('status'))
        <div class="apd-notice apd-notice--ok"><div>{{ session('verifySuccessMessage') ?: session('status') }}</div></div>
    @endif
    @if(session('errors') && is_string(session('errors')))
        <div class="apd-notice apd-notice--bad"><div>{{ session('errors') }}</div></div>
    @endif

    <section class="apd-hero" aria-label="Agent overview">
        <div class="apd-hero__top">
            <div style="display:grid;gap:8px">
                <span class="apd-eyebrow" style="color:#9fb0ff">Agent dashboard</span>
                <h1 class="apd-h1">My <em>applicants</em></h1>
                <p class="apd-hero__sub">{{ $agentName }}@if($referralCode) &middot; referral code <span class="apd-mono" style="color:#fff">{{ $referralCode }}</span>@endif</p>
            </div>
            @if($verified)
                <button type="button" class="apd-btn apd-btn--primary" data-tw-toggle="modal" data-tw-target="#addDeteilsModal">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Add new applicant
                </button>
            @else
                <div class="apd-locked">
                    <button type="button" class="apd-btn apd-btn--primary" disabled aria-disabled="true" title="Verify your email first">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        Add new applicant
                    </button>
                    <small>Verify your email address first &mdash; see the notice above.</small>
                </div>
            @endif
        </div>
        <div class="apd-hero__stats">
            <div class="apd-hero__stat"><small>Submitted</small><b>{{ $stats['submitted'] }}</b></div>
            <div class="apd-hero__stat"><small>Being processed</small><b>{{ $stats['processing'] }}</b></div>
            <div class="apd-hero__stat"><small>Offers &amp; students</small><b>{{ $stats['offers'] }}</b></div>
            <div class="apd-hero__stat"><small>Awaiting verification</small><b id="awaiting-count">{{ $stats['awaiting'] }}</b></div>
            <div class="apd-hero__stat"><small>Total active applications</small><b>{{ $stats['active'] }}</b></div>
        </div>
    </section>

    <div class="agp-cols">
        <section class="agp-panel" style="min-width:0">
            <div class="agp-panel__head">
                <h3 class="apd-h3">My applicants</h3>
                <button type="button" id="agpFilterToggle" class="apd-btn apd-btn--ghost apd-btn--sm" aria-expanded="false">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <span>Search &amp; filter</span>
                </button>
            </div>

            {{-- Closed until asked for: the search box and the list both wait for the toggle / Search. --}}
            <div id="agpSearchbox" class="agp-searchbox apd-form" style="display:none">
                <div class="agp-grid-4">
                    <div class="apd-field"><label for="application_no">Reference no</label><input id="application_no" type="text" class="apd-ctl" placeholder="e.g. 203412" autocomplete="off"></div>
                    <div class="apd-field"><label for="query-CNTR">Name</label><input id="query-CNTR" type="text" class="apd-ctl" autocomplete="off"></div>
                    <div class="apd-field"><label for="applicantEmail">Applicant email</label><input id="applicantEmail" type="text" class="apd-ctl" autocomplete="off"></div>
                    <div class="apd-field"><label for="applicantPhone">Applicant phone</label><input id="applicantPhone" type="text" class="apd-ctl" autocomplete="off"></div>
                    <div class="apd-field">
                        <label for="semesters">Intake semester</label>
                        <select id="semesters" class="lcc-tom-select agp-select" data-placeholder="All semesters">
                            <option value="">All semesters</option>
                            @foreach($semesters as $sem)<option value="{{ $sem->id }}">{{ $sem->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="apd-field">
                        <label for="courses">Course</label>
                        <select id="courses" class="lcc-tom-select agp-select" data-placeholder="All courses">
                            <option value="">All courses</option>
                            @foreach($courses as $crs)<option value="{{ $crs->id }}">{{ $crs->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="apd-grid-2">
                    <div class="apd-field">
                        <label>Status</label>
                        <div class="apd-opts" id="statusChips">
                            @foreach($statuses as $st)<button type="button" class="agp-chip" data-value="{{ $st->id }}">{{ $st->name === 'Awating Decision' ? 'Awaiting Decision' : $st->name }}</button>@endforeach
                        </div>
                        <span class="apd-hint">By default, only submitted applications are listed. Pick Submitting to see drafts.</span>
                    </div>
                    <div class="apd-field">
                        <label for="agents">Agent / sub-agents</label>
                        <select id="agents" multiple class="lcc-tom-select agp-select agp-tags" data-placeholder="All agents and sub-agents">
                            @foreach($agentOptions as $ag)<option value="{{ $ag['value'] }}">{{ $ag['label'] }}@if($ag['code']) [{{ $ag['code'] }}]@endif</option>@endforeach
                        </select>
                        <span class="apd-hint">Pick one or more. Leave empty for all of them.</span>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button type="button" id="studentGroupSearchSubmitBtn" class="apd-btn apd-btn--primary apd-btn--sm">Search</button>
                    <button type="button" id="agpResetBtn" class="apd-btn apd-btn--ghost apd-btn--sm">Reset</button>
                </div>
            </div>

            <div id="agpListIntro" class="agp-empty agp-empty--small">
                <span>Your applications are listed after a search. Open <b>Search &amp; filter</b>, set what you need and press <b>Search</b> &mdash; with nothing set, it lists every submitted application.</span>
                <button type="button" id="agpIntroSearch" class="apd-btn apd-btn--ghost apd-btn--sm">Search &amp; filter</button>
            </div>
            <div class="agp-dtable" style="display:none">
                <div id="applicantApplicantionList" class="agp-table"></div>
            </div>
        </section>

        <aside class="agp-side-col">
            <div class="agp-side-head">
                <h3 class="apd-h3">New applicants</h3>
                @if($verified)
                    <button type="button" class="apd-btn apd-btn--primary apd-btn--sm" data-tw-toggle="modal" data-tw-target="#addDeteilsModal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        Add new account
                    </button>
                @else
                    <button type="button" class="apd-btn apd-btn--primary apd-btn--sm" disabled aria-disabled="true" title="Verify your email first">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
                        Add new account
                    </button>
                @endif
            </div>

            <section class="agp-panel">
                <p style="margin:0;font-size:13.5px">Verified applicants can be applied for now. Others need their SMS and email codes first.</p>
                <input type="search" id="agpChecksSearch" class="apd-ctl agp-feed-search" placeholder="Search by name, phone or email" autocomplete="off" aria-label="Search new applicants">
                <div id="applicant-list" class="agp-chk-list agp-chk-list--feed" data-apply-url="{{ route('agent.application', '__ID__') }}"></div>
            </section>

            @if($drafts->count())
                <section class="agp-panel">
                    <h3 class="apd-h3">Drafts in progress</h3>
                    {{-- Five tall; the rest page in over AJAX as it is scrolled (agent-dahsboard.js). --}}
                    <input type="search" id="agpDraftsSearch" class="apd-ctl agp-feed-search" placeholder="Search by name, phone or email" autocomplete="off" aria-label="Search drafts">
                    <div id="agpDrafts" class="agp-chk-list agp-chk-list--feed" data-url="{{ route('agent.dashboard.drafts') }}" data-offset="{{ $draftsOffset }}" data-more="{{ $draftsMore ? 1 : 0 }}" data-continue-url="{{ route('agent.application', '__ID__') }}">
                        @foreach($drafts as $d)
                            <div class="agp-chk-card">
                                <div style="display:grid;gap:4px"><b>{{ $d['name'] }}</b><span style="font-size:12.5px;color:var(--muted)">Not yet submitted</span></div>
                                <a href="{{ route('agent.application', $d['check_id']) }}" class="apd-btn apd-btn--primary apd-btn--sm">Continue</a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>

    @include('pages.agent.dashboard.modals')
@endsection

@section('script')
    @vite('resources/js/agent-dahsboard.js')
@endsection
