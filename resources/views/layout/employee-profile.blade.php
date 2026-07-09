@extends('../layout/main')

@section('head')
    @yield('subhead')
@endsection

@section('body_class', 'employee-profile-body')

@section('content')
    @include('../layout/components/mobile-menu')
    <div class="ep-page">
        @include('pages.employee.profile.partials.app-bar')

        <!-- BEGIN: Content -->
        <div class="ep-shell">
            @yield('subcontent')
        </div>
    </div>
    <!-- END: Content -->
@endsection
