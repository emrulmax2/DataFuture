{{--
    The student-facing portal shell.

    A dedicated layout — the student frontend used to borrow the staff
    `top-menu` layout (and with it the staff global header, which students
    have no business seeing). This gives them their own three-column shell:
    fixed navigation, editorial main column and a news / library / timetable
    rail, styled by `css/components/_student-portal.css`.

    Pages render into `@section('subcontent')`. The rail is part of the shell
    and is populated by `App\Support\StudentPortalRail`, so no controller has
    to hand it anything.
--}}
@extends('../layout/base')

@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&display=swap" rel="stylesheet">
    @yield('subhead')
@endsection

@section('body')
    @php
        /* Most screens hand the shell a `$student`; the rest (module detail,
           orders, IT reports) get the same one resolved from the session. */
        $portalStudent = isset($student) && $student ? $student : App\Support\StudentPortalRail::current();
    @endphp
    <body class="student-portal-body">
        @include('../layout/components/preloader')

        @include('../layout/components/student-portal-topbar', ['student' => $portalStudent])

        <div class="spf">
            @include('../layout/components/student-portal-sidebar', ['student' => $portalStudent])

            <main class="spf-main">
                @yield('subcontent')
            </main>

            @if($portalStudent)
                @include('../layout/components/student-portal-rail', ['student' => $portalStudent])
            @endif
        </div>

        <div id="spfBackdrop" class="spf-backdrop"></div>

        <!-- BEGIN: JS Assets-->
        @vite('resources/js/app.js')
        @vite('resources/js/student-portal.js')
        @vite('resources/js/student-frontend-global.js')
        <!-- END: JS Assets-->

        @yield('script')
    </body>
@endsection
