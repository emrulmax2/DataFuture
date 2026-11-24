{{--
    Header of one report card on Reports › Term Performance, as on Intake Performance
    (intake-performance/partials/report-head.blade.php; styles in term-attendance-report.css,
    `.ipr-*`). Keeps the theme's collapse hooks (accordion-button, data-tw-target, ids):
    term-performance-reports.js opens the card named in the URL hash through them.

    $n        accordion number
    $title    report name
    $desc     one line on what it measures
    $icon     Lucide icon name
    $formats  downloads the report offers, e.g. ['PDF', 'Excel']
--}}
<div id="termPerformanceReportAccordion-{{ $n }}" class="accordion-header">
    <button id="studentSearchBtn" class="accordion-button collapsed ipr-head" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-{{ $n }}" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-{{ $n }}">
        <span class="ipr-icon" aria-hidden="true"><i data-lucide="{{ $icon }}"></i></span>
        <span class="ipr-text">
            <span class="ipr-title">{{ $title }}</span>
            <span class="ipr-desc">{{ $desc }}</span>
        </span>
        @if(!empty($formats))
            <span class="ipr-formats">
                @foreach($formats as $format)<span>{{ $format }}</span>@endforeach
            </span>
        @endif
        <span class="ipr-chevron" aria-hidden="true"><i data-lucide="chevron-down"></i></span>
    </button>
</div>
