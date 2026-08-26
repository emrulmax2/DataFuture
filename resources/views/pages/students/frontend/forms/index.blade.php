@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $liveForms = collect($doItOnline)->filter(function ($form) {
            return empty($form->end_to) || $form->end_to === '0000-00-00'
                || strtotime($form->end_to) > strtotime(date('Y-m-d'));
        })->values();

        $formLink = function ($form) use ($reportItAll) {
            if ($form->form_name == 'Document / ID Card Replacement request / Printer Balance Top up') {
                return route('students.document-request-form.products');
            }

            if ($form->form_name == 'Report any IT issues on campus' && isset($reportItAll) && count($reportItAll) > 0) {
                return route('students.report-any-it-issues');
            }

            return $form->form_link;
        };
    @endphp

    <div class="spf-page-head spf-page-head--baseline">
        <h1 class="spf-h1">Do it online</h1>
        <span class="spf-eyebrow">Requests &amp; forms</span>
    </div>

    @if($liveForms->count() > 0)
        <div class="spf-formgrid">
            @foreach($liveForms as $form)
                <a href="{{ $formLink($form) }}" class="spf-formcard">
                    <div class="spf-formcard__title">{{ $form->form_name }}</div>
                    @if(!empty($form->form_description))
                        <div class="spf-formcard__desc">{{ $form->form_description }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <div class="spf-empty">No online forms are available at the moment.</div>
    @endif

    <section class="spf-section" style="margin-top:30px">
        <div class="spf-section__head">
            <h2 class="spf-h2">Other services</h2>
        </div>
        <div class="spf-quicklinks">
            <a href="{{ route('students.dashboard.attendance.excuse') }}" class="spf-quicklink">
                <i data-lucide="calendar-check" class="w-4 h-4"></i>Attendance excuse
            </a>
            <a href="{{ route('students.document-request-form.products') }}" class="spf-quicklink">
                <i data-lucide="id-card" class="w-4 h-4"></i>Document &amp; ID card requests
            </a>
            <a href="{{ route('students.document-request-form.index') }}" class="spf-quicklink">
                <i data-lucide="receipt" class="w-4 h-4"></i>My orders
            </a>
            <a href="{{ route('students.report-any-it-issues') }}" class="spf-quicklink">
                <i data-lucide="life-buoy" class="w-4 h-4"></i>Report an IT issue
            </a>
        </div>
    </section>
@endsection
