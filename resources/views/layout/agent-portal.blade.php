{{--
    The agent-facing portal shell: the applicant portal's two-column Atlas
    shell (`css/applicant-portal.css`) with the agent's navigation and
    identity block, plus `css/agent-portal.css` for the pieces only the agent
    dashboard has. Pages render into `@section('subcontent')`; controllers opt
    in with `'layout' => 'agent-portal'`, which MenuComposer honours.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,650;12..96,700&family=Hanken+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/applicant-portal.css')
    @vite('resources/css/agent-portal.css')
@endsection

@section('body')
    @php
        $agpUser = Auth::guard('agent')->user();
        $agpAgent = optional($agpUser)->agent;
        $agpName = $agpAgent ? ($agpAgent->organization ?: trim($agpAgent->first_name.' '.$agpAgent->last_name)) : optional($agpUser)->email;
        $agpCode = $agpUser ? \App\Models\ReferralCode::where('agent_user_id', $agpUser->id)->whereNull('deleted_at')->value('code') : null;
        $agpVerified = (bool) optional($agpUser)->email_verified_at;
        $agpRoute = optional(request()->route())->getName();
        /* lab404/laravel-impersonate: a staff member can be signed in as this
           agent. The account block says so and offers the way back. */
        $agpImpersonating = $agpUser && method_exists($agpUser, 'isImpersonated') && $agpUser->isImpersonated();

        $agpLogoFile = \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
        $agpBrandLogo = ($agpLogoFile && Storage::disk('local')->exists('public/'.$agpLogoFile))
            ? url('storage/'.$agpLogoFile)
            : asset('build/assets/images/red_and_white_logo.png');

        $agpNav = [
            ['route' => 'agent.dashboard', 'label' => 'Dashboard',  'match' => ['agent.dashboard', 'agent.application', 'agent.application.create', 'agent.application.show']],
            ['route' => 'agent.account',   'label' => 'My account', 'match' => ['agent.account']],
        ];
    @endphp
    <body class="applicant-portal-body agent-portal-body">
        <div class="apd-shell">
            <aside class="apd-side">
                <a href="{{ route('agent.dashboard') }}" class="apd-brand">
                    <img src="{{ $agpBrandLogo }}" alt="London Churchill College" class="apd-brand__logo">
                </a>

                <nav class="apd-nav" aria-label="Main">
                    <div class="apd-nav__label">Menu</div>
                    @foreach($agpNav as $agpItem)
                        @php $agpOn = in_array($agpRoute, $agpItem['match'], true); @endphp
                        <a href="{{ route($agpItem['route']) }}" class="{{ $agpOn ? 'on' : '' }}" @if($agpOn) aria-current="page" @endif>
                            @if($agpItem['label'] === 'Dashboard')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7.5" height="9" rx="2"/><rect x="13.5" y="3" width="7.5" height="5" rx="2"/><rect x="13.5" y="11" width="7.5" height="10" rx="2"/><rect x="3" y="15" width="7.5" height="6" rx="2"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                            @endif
                            {{ $agpItem['label'] }}
                        </a>
                    @endforeach
                    <a href="{{ route('agent.logout') }}" class="apd-only-m">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Sign out
                    </a>
                </nav>

                <div class="apd-me">
                    <div class="apd-me__em">{{ $agpName }}</div>
                    <div class="apd-me__vf apd-mono">{{ $agpCode ?: 'Code pending' }}</div>
                    <span class="apd-me__vf {{ $agpVerified ? '' : 'no' }}">
                        <i></i>{{ $agpVerified ? 'Email verified' : 'Email not verified' }}
                    </span>
                    <a href="{{ route('agent.logout') }}">Sign out</a>
                    @if($agpImpersonating)
                        <div class="apd-me__imp">
                            <span>Staff view &mdash; signed in as this agent.</span>
                            <a href="{{ route('agent.impersonate.leave') }}">Leave impersonation</a>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="apd-main-col">
                <main class="apd-main apd-gutter">
                    @yield('subcontent')
                </main>
            </div>
        </div>

        @vite('resources/js/app.js')
        @yield('script')
    </body>
@endsection
