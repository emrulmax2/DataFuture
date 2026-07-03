@extends('../layout/main')

@section('head')
    @yield('subhead')
@endsection

@section('content')
    @include('../layout/components/mobile-menu')
    @include('pages.employee.profile.partials.app-bar')

    <!-- BEGIN: Content -->
    <div class="ep-shell">
        @yield('subcontent')
    </div>
    <!-- END: Content -->
@endsection
