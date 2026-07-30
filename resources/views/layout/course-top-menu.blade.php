{{--
    Layout for the redesigned Course Management module.

    Selected by passing `'layout' => 'course-top-menu'` from the controller
    (MenuComposer::layout() honours a view-supplied `layout` before falling
    back to 'top-menu'), so every other page in the app is untouched. Screens
    in this module that have not been redesigned yet keep their old layout
    until they are converted, one menu at a time.

    The `--cm-*` custom properties live on <body> rather than an inner wrapper
    because the theme's modals are re-parented to <body> when shown; scoping
    them any deeper would strip the palette from every popup.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@400;500;600;700&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/course-management-redesign.css')
@endsection

@section('body')
    <body class="cm-body">
        @include('../layout/components/preloader')

        @include('../layout/components/course-top-bar')

        @include('../layout/components/course-page-head')

        <main class="cm-main">
            @yield('subcontent')
        </main>

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        @vite('resources/js/course-management-redesign.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
