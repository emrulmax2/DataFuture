@extends('../layout/base')

@section('body')
    <body class="py-5 md:py-0">
        <div class="sitePreLoader">
            <div class="la-ball-scale-multiple la-2x">
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
        
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
