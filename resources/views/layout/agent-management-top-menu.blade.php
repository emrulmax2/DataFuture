{{--
    Layout for the redesigned Agent Management module.

    Shared by redesigned Agent Management screens as they are converted one at
    a time.
--}}
@extends('../layout/base')

@section('head')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('styles')
    @vite('resources/css/agent-management-redesign.css')
@endsection

@section('body')
    <body class="agm-body">
        @include('../layout/components/preloader')

        @include('../layout/components/agent-management-top-bar')

        <main class="agm-main">
            @yield('subcontent')
        </main>

        @vite('resources/js/app.js')
        @vite('resources/js/agent-management-redesign.js')

        @yield('script')
    </body>
@endsection
