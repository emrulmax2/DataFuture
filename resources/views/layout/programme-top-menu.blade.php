{{--
    Layout for the redesigned Programme Dashboard module.

    Selected by passing `'layout' => 'programme-top-menu'` from the controller
    (MenuComposer::layout() honours a view-supplied `layout` before falling back
    to 'top-menu'), so every other screen in the app is untouched.

    The `--pgd-*` custom properties live on <body> rather than an inner wrapper
    because the theme re-parents modals to <body> when they open; scoping the
    palette any deeper would strip it from every popup.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/programme-dashboard.css')
@endsection

@section('body')
    <body class="pgd-body">
        @include('../layout/components/preloader')

        @include('../layout/components/programme-top-bar')

        @yield('subcontent')

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        @vite('resources/js/programme-dashboard.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
