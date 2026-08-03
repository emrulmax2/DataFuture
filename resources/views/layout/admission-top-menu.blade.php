{{--
    Layout for the redesigned admission module.

    Selected by AdmissionController passing `'layout' => 'admission-top-menu'`
    (MenuComposer::layout() honours a view-supplied `layout` before falling
    back to 'top-menu'), so every other page in the app is untouched.

    The `--adm-*` custom properties are written onto <body> rather than an
    inner wrapper because the theme's modals are re-parented to <body> when
    shown; scoping them any deeper would strip the palette from every popup.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/admission-redesign.css')
@endsection

@section('body')
    @php
        // `$admissionThemeApplicant` is set by the detail/sub pages. The list
        // page leaves it unset, which resolves to the default LCC Teal.
        $admThemeStyle = \App\Support\CourseTheme::admissionCssVariables($admissionThemeApplicant ?? null);
    @endphp
    <body class="adm-body" style="{{ $admThemeStyle }}">
        @include('../layout/components/preloader')

        @include('../layout/components/admission-top-bar')

        <main class="adm-main">
            @yield('subcontent')
        </main>

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        @vite('resources/js/admission-redesign.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
