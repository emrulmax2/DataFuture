@extends('../layout/base')

@section('head')
    @yield('subhead')
@endsection

@section('body')
    <body class="site-settings-redesign-body @yield('body_class')">
        @include('../layout/components/preloader')
        @yield('content')

        @vite('resources/js/app.js')
        @yield('script')
    </body>
@endsection
