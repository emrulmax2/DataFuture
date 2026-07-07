@extends('../layout/base')

@section('body')
    <body class="py-5 md:py-0">
        @include('../layout/components/preloader')
        
        @yield('content')
        {{--
            @include('../layout/components/dark-mode-switcher')
            @include('../layout/components/main-color-switcher')
        --}}

        <!-- BEGIN: JS Assets-->
        
        
        @vite('resources/js/app.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
