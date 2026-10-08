{{--
    What a student sees once an in-portal form is in.

    A page of its own, reached by URL, so the reference survives a refresh or a
    screenshot. It returns to the dashboard by itself after a few seconds -
    through a meta refresh as well as the countdown, so it still happens if the
    script never runs.
--}}
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    <meta http-equiv="refresh" content="{{ $redirectAfter }};url={{ route('students.dashboard') }}">
@endsection

@section('styles')
    @vite('resources/css/student-form.css')
@endsection

@section('subcontent')
    @php
        $raised = (bool) $request->ticket_ref;
        $documents = $request->documents->pluck('display_file_name')->filter()->values();
    @endphp

    <div class="sfm-done">
        <div class="sfm-done__icon">
            <i data-lucide="check" class="w-7 h-7"></i>
        </div>

        <h1 class="sfm-done__title">Request submitted</h1>

        <p class="sfm-done__lead">
            @if($raised)
                {{ $lead }} We have emailed you a copy of what you submitted.
            @else
                Your request has been received and is being passed to the College Registry.
                We will email you the reference shortly.
            @endif
        </p>

        @if($raised)
            <div class="sfm-done__ref">
                <div class="sfm-done__ref-label">Your reference number</div>
                <div class="sfm-done__ref-value">{{ $request->ticket_ref }}</div>
                <div class="sfm-done__ref-note">Please quote this reference if you contact us about your request.</div>
            </div>
        @endif

        <div class="sfm-done__summary">
            @foreach($summary as $label => $value)
                <div class="sfm-done__row">
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </div>
            @endforeach
        </div>

        <p class="sfm-done__next">
            A member of staff will contact you within <strong>5 working days</strong>. You do not need to do
            anything else in the meantime.
        </p>

        <div class="sfm-done__actions">
            <a href="{{ route('students.dashboard') }}" class="sfm-btn">Go to my dashboard</a>
            <a href="{{ route('students.dashboard.forms') }}" class="sfm-btn sfm-btn--ghost">Back to Do it online</a>
        </div>

        <div class="sfm-done__timer">
            Taking you to your dashboard in <span data-sfm-countdown>{{ $redirectAfter }}</span> seconds.
        </div>
    </div>
@endsection

@section('script')
    <script>
        /* The meta refresh above is the real redirect; this only counts it down
           on screen, and takes over if the browser ignores the meta tag. */
        (function () {
            var readout = document.querySelector('[data-sfm-countdown]');
            var left = {{ $redirectAfter }};

            if (!readout) return;

            var tick = setInterval(function () {
                left -= 1;
                readout.textContent = left > 0 ? left : 0;

                if (left <= 0) {
                    clearInterval(tick);
                    window.location.href = @json(route('students.dashboard'));
                }
            }, 1000);
        })();
    </script>
@endsection
