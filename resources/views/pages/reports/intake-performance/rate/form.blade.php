{{--
    Form + result area of a new intake report (Continuation, Retention,
    Attendance, Submission & Pass, Award) in its "New version" tab: one or
    more intake semesters. Driven by resources/js/intake-rate-reports.js; the
    result is the report's screen view, grouped by intake.

    $copy      the report's wording and route names (its controller's copy())
    $semester  intake semesters for the select
--}}
@php $selectId = $copy['key'].'RateSemester'; @endphp
<div class="crn">
    <form method="post" action="#" class="crn-form" novalidate data-rate-form
          data-generate="{{ $copy['routes']['generate'] }}" data-pdf="{{ $copy['routes']['pdf'] }}" data-excel="{{ $copy['routes']['excel'] ?? '' }}">
        <div class="crn-field">
            <label for="{{ $selectId }}" class="crn-label">Intake semesters <span class="text-danger">*</span></label>
            <select id="{{ $selectId }}" name="semester_ids[]" multiple class="w-full" data-rate-semester>
                @foreach($semester as $sem)
                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                @endforeach
            </select>
            <div class="crn-error" role="alert" data-rate-error></div>
        </div>
        <div class="crn-actions">
            <button type="submit" class="crn-btn crn-btn--primary" data-rate-generate>
                <i data-lucide="bar-chart-3" class="crn-btn__icon"></i>
                Generate Report
                <svg style="display: none;" width="16" height="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="crn-spinner" aria-hidden="true">
                    <g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g>
                </svg>
            </button>
            <a href="javascript:void(0);" class="crn-btn crn-btn--blue" style="display: none;" data-rate-pdf><i data-lucide="file-down" class="crn-btn__icon"></i> Download PDF</a>
            @if(!empty($copy['routes']['excel']))
                <a href="javascript:void(0);" class="crn-btn crn-btn--soft" style="display: none;" data-rate-excel><i data-lucide="file-spreadsheet" class="crn-btn__icon"></i> Export Excel</a>
            @endif
        </div>
    </form>

    <div aria-live="polite" data-rate-result></div>
</div>
