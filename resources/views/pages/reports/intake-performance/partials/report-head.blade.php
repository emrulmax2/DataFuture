{{--
    Header of one report card on Reports › Intake Performance (styles:
    rate/styles.blade.php, `.ipr-*`). Keeps the theme's collapse hooks
    (accordion-button, data-tw-target, ids): intake-performance-reports.js
    opens the card named in the URL hash through them.

    $n        accordion number (1–6)
    $title    report name
    $desc     one line on what it measures
    $icon     Lucide icon name
    $formats  downloads the report offers, e.g. ['PDF', 'Excel']
--}}
<div id="intakePerformanceReportAccordion-{{ $n }}" class="accordion-header">
    <button id="studentSearchBtn" class="accordion-button collapsed ipr-head" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-{{ $n }}" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-{{ $n }}">
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
