@extends('../layout/base')

@section('body')
    <body class="login">
        @yield('content')
        {{-- Color-scheme / dark-mode switchers intentionally omitted: the LCC auth design is a fixed full-bleed light theme. --}}

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
