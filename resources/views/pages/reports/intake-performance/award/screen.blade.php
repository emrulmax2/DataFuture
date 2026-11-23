{{--
    Award Rate, new version: the result for one or more intakes, injected under
    the form in the "New version" tab (resources/js/intake-rate-reports.js).
    Styles: rate/styles.blade.php (`.crn-*`, `.crn-awd-*`).

    $report  App\Services\Reports\AwardRate::buildMany()
--}}
@php
    $t = $report['totals'];
    $pct = function ($rate) { return $rate === null ? '–' : number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $day = function ($date) { return date('j M Y', strtotime($date)); };
    $anyRunning = false;
    foreach ($report['intakes'] as $intake) {
        foreach ($intake['courses'] as $row) {
            $anyRunning = $anyRunning || ($row['running'] && $row['registered'] > 0);
        }
    }
@endphp

<div class="crn-result">
    @if(!$report['has_courses'])
        <div class="crn-notice">No courses with students were found for {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @elseif($t['registered'] === 0)
        <div class="crn-notice"><strong>No registered students.</strong> None of the students has an awarding body reference yet, so there is no award rate.</div>
    @else
        <div class="crn-kpis">
            <div class="crn-kpi crn-kpi--main">
                <div class="crn-kpi__label">Award rate</div>
                <div class="crn-kpi__value">{{ $pct($t['rate']) }}</div>
                <div class="crn-kpi__note">{{ $num($t['completed']) }} of {{ $num($t['registered']) }} registered students</div>
            </div>
            <div class="crn-kpi">
                <div class="crn-kpi__label">Completed</div>
                <div class="crn-kpi__value">{{ $num($t['completed']) }}</div>
                <div class="crn-kpi__note">Completed their award</div>
            </div>
            <div class="crn-kpi">
                <div class="crn-kpi__label">Did not complete</div>
                <div class="crn-kpi__value crn-kpi__value--down">{{ $num($t['not_completed']) }}</div>
                <div class="crn-kpi__note">Registered but not completed</div>
            </div>
            <div class="crn-kpi">
                <div class="crn-kpi__label">Registered students</div>
                <div class="crn-kpi__value">{{ $num($t['registered']) }}</div>
                <div class="crn-kpi__note">Across {{ $t['intakes'] }} {{ $t['intakes'] === 1 ? 'intake' : 'intakes' }}</div>
            </div>
        </div>

        <div class="crn-awd-section">
            <div class="crn-awd-head">
                <h2 class="crn-att-title">By intake and course</h2>
                <div class="crn-awd-legend">
                    <span>Each dot is one student</span>
                    <span class="crn-awd-key"><span class="crn-awd-swatch is-done" aria-hidden="true"></span>Completed</span>
                    <span class="crn-awd-key"><span class="crn-awd-swatch is-not" aria-hidden="true"></span>Did not complete</span>
                </div>
            </div>
            @foreach($report['intakes'] as $intake)
                @php $it = $intake['totals']; $group = 'awd-intake-'.$loop->index; @endphp
                <div class="crn-awd-grp">
                    <div>
                        @if(!empty($intake['courses']))
                            @include('pages.reports.intake-performance.rate.group-toggle', ['key' => $group, 'label' => $intake['intake']])
                        @else
                            {{ $intake['intake'] }}
                        @endif
                    </div>
                    <div>
                        @if(empty($intake['courses']))
                            No courses with students
                        @elseif($it['registered'] === 0)
                            No registered students
                        @else
                            {{ $pct($it['rate']) }} · {{ $num($it['completed']) }} of {{ $num($it['registered']) }} completed
                        @endif
                    </div>
                </div>
                @foreach($intake['courses'] as $row)
                    @php
                        $flags = array_filter([
                            ($row['registered'] === 0 ? 'No registered students, so no rate' : null),
                            ($row['running'] && $row['registered'] > 0 ? 'Still running · ends '.$day($row['end_date']) : null),
                            ($row['small'] ? 'Small cohort' : null),
                        ]);
                    @endphp
                    <div class="crn-awd-row" data-crn-group="{{ $group }}">
                        <div class="crn-awd-info">
                            <div>
                                <div class="crn-awd-name">{{ $row['name'] }}</div>
                                @if(!empty($flags))
                                    <div class="crn-awd-flag">{{ implode(' · ', $flags) }}</div>
                                @endif
                            </div>
                            <div class="crn-awd-rate{{ $row['low'] ? ' is-low' : '' }}{{ $row['rate'] === null ? ' is-none' : '' }}">{{ $pct($row['rate']) }}</div>
                            <div class="crn-awd-counts">
                                <b>{{ $num($row['completed']) }}</b> completed · <b>{{ $num($row['not_completed']) }}</b> did not · {{ $num($row['registered']) }} registered
                            </div>
                        </div>
                        @if($row['registered'] > 0)
                            <div class="crn-awd-dotwrap">
                                <div class="crn-awd-dots" role="img" aria-label="{{ $row['registered'] }} students: {{ $row['completed'] }} completed, {{ $row['not_completed'] }} did not complete">
                                    {!! str_repeat('<span class="is-done"></span>', $row['completed']).str_repeat('<span class="is-not"></span>', $row['not_completed']) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>

        <div class="crn-footnote">
            Award rate = Completed ÷ Registered students.
            @if($report['small_note']) {{ $report['small_note'] }} @endif
            @if($anyRunning) Still running = the course has not ended yet, so students still studying count as not completed. @endif
        </div>
    @endif
</div>
