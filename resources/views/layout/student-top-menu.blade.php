@extends('../layout/student-base')

@section('head')
    @yield('subhead')
@endsection

@section('body')
    @php
        $studentProfileThemeStyle = \App\Support\CourseTheme::inlineCssVariables(isset($student) ? $student : null);
        $isStudentAttendancePage = request()->routeIs('student.attendance*');
    @endphp
    <body class="student-profile-body{{ $isStudentAttendancePage ? ' student-profile-attendance-page' : '' }}" @if($studentProfileThemeStyle !== '') style="{{ $studentProfileThemeStyle }}" @endif>
        @include('../layout/components/preloader')

        <main class="student-profile-redesign" @if($studentProfileThemeStyle !== '') style="{{ $studentProfileThemeStyle }}" @endif>
            @yield('subcontent')
        </main>

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        <!-- END: JS Assets-->

        {{-- Global quick Send-Email / Send-SMS popups. No-ops unless the page renders
             the shared #quickCommModals partial (i.e. skips the Communication page). --}}
        @vite('resources/js/student-quick-communication.js')

        @yield('script')
    </body>
@endsection
