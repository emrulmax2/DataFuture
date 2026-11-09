{{--
    Admission portal shell — the staff admission screens (/admission/*).

    Controllers opt in with `'layout' => 'admission-portal'` (MenuComposer
    honours a view-supplied `layout`). The page is the approved admission
    portal design: a sticky dark top bar with the breadcrumb strip under it,
    then a 1280px column of panels. Styling lives in css/admission-portal.css
    under the `adp-` namespace, scoped to body.adp-body so nothing leaks into
    the rest of the app and nothing of Midone's leaks in.

    The header's dropdowns and the global search reuse the app-wide hooks in
    js/search.js (`data-global-header`, `data-header-menu`,
    `data-global-search`), so they behave exactly as the other headers do.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,650;12..96,700&family=Hanken+Grotesk:ital,wght@0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@500;600&family=Source+Serif+4:ital,opsz,wght@0,8..60,500;0,8..60,600;1,8..60,500&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/admission-portal.css')
@endsection

@section('body')
    <body class="adp-body">
        <div class="adp-topshell">
            @include('../layout/components/admission-portal-top-bar')

            <main class="adp-main">
                @yield('subcontent')
            </main>
        </div>

        @vite('resources/js/app.js')
        @vite('resources/js/admission-portal.js')

        @php $adpPresenceApplicant = $applicant ?? $admissionThemeApplicant ?? null; @endphp
        @if(auth()->check() && $adpPresenceApplicant && isset($adpPresenceApplicant->id))
            {{-- staff time on this applicant's record → applicant_view_sessions (admission-presence.js) --}}
            <div data-adp-presence hidden data-applicant="{{ $adpPresenceApplicant->id }}" data-route="{{ Route::currentRouteName() }}" data-section="{{ \App\Models\ApplicantViewSession::sectionFor(Route::currentRouteName()) }}" data-start-url="{{ route('admission.presence.start') }}" data-beat-url="{{ route('admission.presence.beat') }}" data-end-url="{{ route('admission.presence.end') }}"></div>
            @vite('resources/js/admission-presence.js')
        @endif
        @yield('script')
    </body>
@endsection
