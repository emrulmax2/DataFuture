{{--
    "Student attendance" card.

    Rendered on first paint and again by the class-info XHR, so the figures
    follow the date / course / module / group filters rather than being frozen
    at whatever was true on page load.

    The numbers are term-level and cumulative — every fed register in the term
    to date, not just the selected day — which is why the class-status filters
    (Scheduled / Ongoing / quick views) deliberately do not narrow them.
--}}
@php
    // The ring is a Chart.js doughnut with one segment per term, as designed.
    // Its data comes straight off the term rows below (they already carry name,
    // rate, expected, present and colour), so there is one source of truth.
    $pgdActiveTerm = (!empty($termAttendanceRates) ? $termAttendanceRates[0] : null);
@endphp

<div class="pgd-card__head">
    <h2>Student attendance</h2>
    @if(isset(auth()->user()->priv()['reports']) && auth()->user()->priv()['reports'] == 1)
        <a href="{{ route('reports') }}" class="pgd-btn pgd-btn--ghost pgd-btn--xs">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v5h5"></path><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2Z"></path><path d="M9 13h6"></path><path d="M9 17h4"></path></svg>
            Reports
        </a>
    @endif
</div>

@if(empty($termAttendanceRates))
    <div class="pgd-note pgd-note--warn">
        <span class="pgd-note__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9 1.9 18a2 2 0 0 0 1.7 3h16.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"></path><path d="M12 9v4"></path><path d="M12 17h.01"></path></svg>
        </span>
        <span>
            <strong>No attendance to show</strong>
            Nothing has been fed against the selected terms and filters.
        </span>
    </div>
@else
    <div class="pgd-attendance__top">
        <div class="pgd-donut">
            <canvas data-pgd-donut></canvas>
            <div class="pgd-donut__tip" data-pgd-tip hidden>
                <strong data-pgd-tip-name></strong>
                <span><i data-pgd-tip-dot></i><span data-pgd-tip-value></span></span>
            </div>
        </div>

        <div class="pgd-attendance__figures">
            <div>
                <div class="pgd-attendance__rate"><strong data-pgd-term-rate>{{ number_format((float) $pgdActiveTerm['rate'], 1) }}%</strong><span>attended</span></div>
                <div class="pgd-attendance__name" data-pgd-term-name>{{ $pgdActiveTerm['name'] }}</div>
                <div class="pgd-attendance__dates" data-pgd-term-dates>{{ $pgdActiveTerm['dates'] }}</div>
            </div>
            <div class="pgd-attendance__stats">
                <div><span>Expected</span><strong data-pgd-term-expected>{{ number_format($pgdActiveTerm['expected']) }}</strong></div>
                <div><span>Present</span><strong class="is-green" data-pgd-term-present>{{ number_format($pgdActiveTerm['present']) }}</strong></div>
            </div>
        </div>
    </div>

    <div class="pgd-attendance__list">
        <div class="pgd-attendance__listhead">Terms — attendance rate</div>
        @foreach($termAttendanceRates as $index => $tar)
            <button type="button" class="pgd-termrow {{ $index === 0 ? 'is-on' : '' }}"
                    data-pgd-termrow="{{ $tar['id'] }}"
                    data-name="{{ $tar['name'] }}"
                    data-rate="{{ number_format((float) $tar['rate'], 1) }}%"
                    data-rate-value="{{ min(100, max(0, (float) $tar['rate'])) }}"
                    data-dates="{{ $tar['dates'] }}"
                    data-expected="{{ number_format($tar['expected']) }}"
                    data-present="{{ number_format($tar['present']) }}"
                    data-color="{{ $tar['color'] }}">
                <span class="pgd-termrow__copy">
                    <span class="pgd-termrow__dot" style="background: {{ $tar['color'] }};"></span>
                    <span>
                        <span class="pgd-termrow__name">{{ $tar['name'] }}</span>
                        <span class="pgd-termrow__meta">{{ number_format($tar['expected']) }} expected · {{ $tar['tutors'] }} tutors</span>
                    </span>
                </span>
                <span class="pgd-termrow__bar"><span style="width: {{ min(100, (float) $tar['rate']) }}%; background: {{ $tar['color'] }};"></span></span>
                <span class="pgd-termrow__figs">
                    <span class="pgd-termrow__rate">{{ number_format((float) $tar['rate'], 1) }}%</span>
                    @if($tar['delta'] != 0)
                        <span class="pgd-termrow__delta {{ $tar['delta'] >= 0 ? 'is-up' : 'is-down' }}">{{ $tar['delta'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($tar['delta']), 1) }}</span>
                    @endif
                </span>
            </button>
        @endforeach
    </div>
@endif
