{{--
    Shell for the redesigned Task Manager detail screen.

    Extends `layout/base` directly, skipping `layout/main` + `layout/top-menu`,
    so none of the legacy theme chrome loads on this page. Controllers opt in
    with `'layout' => 'task-manager-top-menu'`; the MenuComposer passes that
    through and the page view does `@extends('../layout/' . $layout)`.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/task-manager-detail.css')
@endsection

@section('body')
    <body class="tkm-body">
        @include('../layout/components/preloader')

        @include('../layout/components/task-manager-top-bar')

        @yield('subcontent')

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        @vite('resources/js/task-manager-detail.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
