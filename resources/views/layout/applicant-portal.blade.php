{{--
    The applicant-facing portal shell.

    A dedicated layout — the applicant area used to borrow the staff `top-menu`
    layout, and with it the staff global header, which an applicant has no
    business seeing. This gives them their own two-column shell: a fixed
    navigation rail and an editorial main column, styled by
    `css/applicant-portal.css`.

    Pages render into `@section('subcontent')`. Controllers opt in by passing
    `'layout' => 'applicant-portal'`, which MenuComposer honours.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,650;12..96,700&family=Hanken+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

{{-- `styles` is yielded after app.css, so the portal sheet always lands last. --}}
@section('styles')
    @vite('resources/css/applicant-portal.css')
@endsection

@section('body')
    @php
        $apdUser = Auth::guard('applicant')->user();
        $apdRoute = optional(request()->route())->getName();
        /* lab404/laravel-impersonate: a staff member can be signed in as this
           applicant. Say so, and offer the way back. */
        $apdImpersonating = $apdUser && method_exists($apdUser, 'isImpersonated') && $apdUser->isImpersonated();

        /* Same mark as the sign-in screens: Site settings -> Logos & favicon.
           The fallback is a tracked source image, never anything written into
           `public/build`, which `vite build` empties. */
        $apdLogoFile = \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
        $apdBrandLogo = ($apdLogoFile && Storage::disk('local')->exists('public/'.$apdLogoFile))
            ? url('storage/'.$apdLogoFile)
            : asset('build/assets/images/red_and_white_logo.png');

        $apdNav = [
            ['route' => 'applicant.dashboard',   'label' => 'Dashboard',   'match' => ['applicant.dashboard']],
            ['route' => 'applicant.application', 'label' => 'Application', 'match' => ['applicant.application', 'applicant.application.show']],
            ['route' => 'applicant.account',     'label' => 'My account',  'match' => ['applicant.account']],
        ];
    @endphp
    <body class="applicant-portal-body">
        @if($apdImpersonating)
            <div class="apd-imp apd-gutter">
                <div style="max-width:1100px;margin:0 auto;padding:7px 0;display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
                    <span>Staff view: you are signed in as this applicant (impersonation).</span>
                    <a href="{{ route('applicant.impersonate.leave') }}">Leave impersonation</a>
                </div>
            </div>
        @endif

        <div class="apd-shell">
            <aside class="apd-side">
                <a href="{{ route('applicant.dashboard') }}" class="apd-brand">
                    {{-- The site logo is the full lockup and stands on its own: no
                         college name beside it, no portal label beneath it. --}}
                    <img src="{{ $apdBrandLogo }}" alt="London Churchill College" class="apd-brand__logo">
                </a>

                <nav class="apd-nav" aria-label="Main">
                    <div class="apd-nav__label">Menu</div>
                    @foreach($apdNav as $apdItem)
                        @php $apdOn = in_array($apdRoute, $apdItem['match'], true); @endphp
                        <a href="{{ route($apdItem['route']) }}" class="{{ $apdOn ? 'on' : '' }}" @if($apdOn) aria-current="page" @endif>
                            @if($apdItem['label'] === 'Dashboard')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7.5" height="9" rx="2"/><rect x="13.5" y="3" width="7.5" height="5" rx="2"/><rect x="13.5" y="11" width="7.5" height="10" rx="2"/><rect x="3" y="15" width="7.5" height="6" rx="2"/></svg>
                            @elseif($apdItem['label'] === 'My account')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>
                            @endif
                            {{ $apdItem['label'] }}
                        </a>
                    @endforeach
                    {{-- The rail collapses to a scrolling strip on narrow screens, where
                         the account block below is hidden, so sign out joins the strip. --}}
                    <a href="{{ route('applicant.logout') }}" class="apd-only-m">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Sign out
                    </a>
                </nav>

                <div class="apd-me">
                    <div class="apd-me__em">{{ $apdUser->email ?? '' }}</div>
                    <span class="apd-me__vf {{ optional($apdUser)->email_verified_at ? '' : 'no' }}">
                        <i></i>{{ optional($apdUser)->email_verified_at ? 'Email verified' : 'Email not verified' }}
                    </span>
                    <a href="{{ route('applicant.logout') }}">Sign out</a>
                </div>
            </aside>

            <div class="apd-main-col">
                <main class="apd-main apd-gutter">
                    @yield('subcontent')
                </main>
            </div>
        </div>

        <!-- BEGIN: Vendor JS Assets-->
        @vite('resources/js/app.js')
        <!-- END: Vendor JS Assets-->

        @yield('script')
    </body>
@endsection
