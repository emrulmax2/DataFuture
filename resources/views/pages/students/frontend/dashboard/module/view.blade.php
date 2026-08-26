@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Dashboard &middot; My modules</div>
            <h1 class="spf-h1">Module details</h1>
        </div>
        <div class="spf-spacer"></div>
        <a href="{{ route('students.dashboard') }}" class="spf-btn spf-btn--sm">&larr; Back to dashboard</a>
    </div>

    <div class="spf-hero" style="grid-template-columns:minmax(260px,1.3fr) auto auto;gap:36px">
        <div style="display:flex;flex-direction:column;gap:10px;min-width:0">
            <h2 class="spf-h3" style="font-size:22px;line-height:1.3">{{ $data->module }}</h2>
            <div class="spf-hero__name" style="line-height:1.6">{{ $data->course }}@if(!empty($data->term_name)) &middot; {{ $data->term_name }}@endif</div>
        </div>

        <div class="spf-hero__aside" style="gap:16px">
            <div>
                <div class="spf-label" style="margin-bottom:7px">Group</div>
                <span class="spf-chip spf-chip--cream">{{ !empty($data->group) ? strtoupper($data->group) : '—' }}</span>
            </div>
            <div>
                <div class="spf-label" style="margin-bottom:7px">Class type</div>
                <div class="spf-hero__course">{{ !empty($data->classType) ? $data->classType : 'Unknown' }}</div>
            </div>
        </div>

        <div class="spf-hero__aside" style="gap:14px">
            @if(isset($data->tutor) && $data->tutor != null)
                <div style="display:flex;align-items:center;gap:12px">
                    <img src="{{ $data->tutor->photo_url }}" alt="{{ $data->tutor->full_name }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                    <div style="line-height:1.35">
                        <div style="font-size:13px;font-weight:600">{{ $data->tutor->full_name }}</div>
                        <div class="spf-hero__name">Tutor</div>
                    </div>
                </div>
            @endif
            @if(isset($data->personalTutor) && $data->personalTutor != null)
                <div style="display:flex;align-items:center;gap:12px">
                    <img src="{{ $data->personalTutor->photo_url }}" alt="{{ $data->personalTutor->full_name }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                    <div style="line-height:1.35">
                        <div style="font-size:13px;font-weight:600">{{ $data->personalTutor->full_name }}</div>
                        <div class="spf-hero__name">Personal tutor</div>
                    </div>
                </div>
            @endif
            @if((!isset($data->tutor) || $data->tutor == null) && (!isset($data->personalTutor) || $data->personalTutor == null))
                <div class="spf-hero__name">No tutor assigned yet.</div>
            @endif
        </div>
    </div>

    {{-- Tabs are driven by `student-portal.js` (data-spf-tab), not tw-starter. --}}
    <nav class="spf-tabs" data-spf-tabs>
        <button type="button" class="spf-tabs__btn is-active" data-spf-tab="mtab-content">Course content</button>
        <button type="button" class="spf-tabs__btn" data-spf-tab="mtab-teams">Microsoft Teams</button>
        <button type="button" class="spf-tabs__btn" data-spf-tab="mtab-dates">Class dates</button>
    </nav>

    <div id="mtab-content" class="spf-tabpanel">
        @include('pages.students.frontend.dashboard.module.includes.activity')
    </div>

    <div id="mtab-teams" class="spf-tabpanel hidden">
        <div class="spf-panel" style="max-width:560px;display:flex;flex-direction:column;align-items:flex-start;gap:10px">
            <h2 class="spf-h2">Your class team</h2>
            <p class="spf-section__note" style="font-size:12.5px;line-height:1.7;margin:0">
                Lectures, announcements and group work for this module run through Microsoft Teams.
                Sign in with your college email to join the sessions.
            </p>
            <a href="https://teams.microsoft.com/v2/" target="_blank" rel="noopener" class="spf-btn spf-btn--dark" style="margin-top:6px">
                Open Microsoft Teams &nearr;
            </a>
        </div>
    </div>

    <div id="mtab-dates" class="spf-tabpanel hidden">
        @include('pages.students.frontend.dashboard.module.includes.dates')
    </div>

    @include('pages.students.frontend.dashboard.module.component.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks-students.js')
@endsection
