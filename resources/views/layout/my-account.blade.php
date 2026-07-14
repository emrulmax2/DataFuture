@extends('../layout/base')

@section('body')
    <body class="my-account-body @yield('body_class')">
        @include('../layout/components/preloader')
        @include('../layout/components/my-account-topbar')

        <main class="my-account-page">
            @yield('subcontent')
        </main>

        @vite('resources/js/app.js')
        @yield('script')
    </body>
@endsection
