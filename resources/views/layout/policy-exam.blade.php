@extends('../layout/base')

{{--
    The exam shell of My HR › Policy Assessments: a focused page with no My HR
    top bar, profile card or tab strip. It carries its own sticky header — the
    brand mark, the four steps of a test (Briefing, Questions, Review, Result)
    and, on a timed test in progress, the countdown — over a thin progress bar.

    Pages pass $examStep (1 to 4). The test page moves between steps 2 and 3
    without a reload, so its script keeps the stepper and the bar up to date.
    Yields: 'exam_timer' (the countdown, in the header), 'subcontent', 'script'.
    Styles: resources/css/policy-assessment-staff.css (pexam-*).

    No preloader here: on a timed test the clock is already running when the
    questions page opens, so nothing should sit over it.
--}}
@php
    $pexamStep = (isset($examStep) ? (int) $examStep : 1);
    $pexamSteps = [1 => 'Briefing', 2 => 'Questions', 3 => 'Review', 4 => 'Result'];
@endphp

@section('head')
    @yield('subhead')
@endsection

@section('body')
    <body class="my-account-body pexam-body @yield('body_class')">
        <a href="#pexamMain" class="pexam-skip">Skip to the content</a>

        <header class="pexam-top">
            <div class="pexam-top__in">
                <div class="pexam-brand">
                    <span class="pexam-brand__mark" aria-hidden="true">LC</span>
                    <span class="pexam-brand__text">Policy assessment</span>
                </div>

                <ol class="pexam-steps" id="pexamSteps" aria-label="Test progress">
                    @foreach($pexamSteps as $number => $label)
                        <li class="pexam-step {{ $number < $pexamStep ? 'is-done' : ($number == $pexamStep ? 'is-now' : 'is-todo') }}" data-step="{{ $number }}" @if($number == $pexamStep) aria-current="step" @endif>
                            <span class="pexam-step__dot" aria-hidden="true">{{ $number < $pexamStep ? '✓' : $number }}</span>
                            <span class="pexam-step__label"><span class="pexam-sr">Step {{ $number }} of {{ count($pexamSteps) }}{{ $number < $pexamStep ? ', done' : '' }}: </span>{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>

                @yield('exam_timer')
            </div>
            <div class="pexam-bar" aria-hidden="true">
                <i id="pexamBar" style="width: {{ $pexamStep >= 4 ? 100 : 0 }}%;"></i>
            </div>
        </header>

        {{-- Filled by the test page's script when the member of staff comes back after leaving the tab or window. --}}
        <div class="pexam-notice" id="pexamNotice" role="alert" hidden></div>

        <main class="pexam-wrap" id="pexamMain">
            @yield('subcontent')
        </main>

        {{-- Announcements for screen readers: the step or question reached, time marks, flags. --}}
        <div class="pexam-sr" id="pexamLive" role="status" aria-live="polite"></div>

        @vite('resources/js/app.js')
        @yield('script')
    </body>
@endsection
