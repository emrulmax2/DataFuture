{{--
    Submission & Pass Rate, new version: the result for one or more intakes,
    injected under the form in the "New version" tab
    (resources/js/intake-rate-reports.js). Styles: rate/styles.blade.php
    (`.crn-*`, `.crn-sub-*`, intake groups `.crn-gtable`).

    $report  App\Services\Reports\SubmissionPassRate::buildMany()
--}}
@php
    $t = $report['totals'];
    $pct = function ($rate) { return $rate === null ? '–' : number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $share = function ($n) use ($t) { return ($t['expected'] > 0 ? number_format($n / $t['expected'] * 100, 1).'%' : '0%'); };
    [$wPassed, $wNotPassed, $wNotSubmitted] = $report['waffle'];
    $squares = array_merge(array_fill(0, $wPassed, 'passed'), array_fill(0, $wNotPassed, 'notpassed'), array_fill(0, $wNotSubmitted, 'missing'));
    $stack = function ($row) {
        if ($row['expected'] < 1) {
            return '<div class="crn-sub-stack is-empty" aria-hidden="true"></div>';
        }
        $out = '<div class="crn-sub-stack" role="img" aria-label="'.$row['passed'].' passed, '.$row['submitted_not_passed'].' submitted but not passed, '.$row['not_submitted'].' not submitted">';
        foreach ([['passed', $row['passed']], ['notpassed', $row['submitted_not_passed']], ['missing', $row['not_submitted']]] as [$kind, $n]) {
            if ($n > 0) {
                $out .= '<span class="is-'.$kind.'" style="flex: '.$n.' 1 0;"></span>';
            }
        }
        return $out.'</div>';
    };
    $figures = function ($row, $passClass = '') use ($num, $pct) {
        if ($row['expected'] < 1) {
            return '<td class="crn-sub-num">'.$num($row['expected']).'</td><td class="crn-sub-num crn-sub-none" colspan="2">Nothing due yet</td>';
        }
        return '<td class="crn-sub-num">'.$num($row['expected']).'</td><td class="crn-sub-num">'.$pct($row['submission_rate']).'</td><td class="crn-sub-num crn-g-rate'.$passClass.'">'.$pct($row['pass_rate']).'</td>';
    };
@endphp

<div class="crn-result">
    @if(!$report['has_courses'])
        <div class="crn-notice">No courses with students were found for {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @elseif(!$report['has_results'])
        <div class="crn-notice"><strong>Nothing due yet.</strong> No assignment results have been recorded for the registered students of {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @else
        <div class="crn-sub-top">
            <div class="crn-sub-cards">
                <div class="crn-kpi crn-kpi--main">
                    <div class="crn-kpi__label">Submission rate</div>
                    <div class="crn-kpi__value">{{ $pct($t['submission_rate']) }}</div>
                    <div class="crn-kpi__note">{{ $num($t['submitted']) }} of {{ $num($t['expected']) }} submitted</div>
                </div>
                <div class="crn-kpi crn-kpi--main">
                    <div class="crn-kpi__label">Pass rate</div>
                    <div class="crn-kpi__value">{{ $pct($t['pass_rate']) }}</div>
                    <div class="crn-kpi__note">{{ $num($t['passed']) }} of {{ $num($t['expected']) }} passed</div>
                </div>
                <div class="crn-kpi">
                    <div class="crn-kpi__label">Not submitted</div>
                    <div class="crn-kpi__value">{{ $num($t['not_submitted']) }}</div>
                    <div class="crn-kpi__note">Assignments never handed in</div>
                </div>
                <div class="crn-kpi">
                    <div class="crn-kpi__label">Submitted, not passed</div>
                    <div class="crn-kpi__value">{{ $num($t['submitted_not_passed']) }}</div>
                    <div class="crn-kpi__note">Handed in but not passed</div>
                </div>
            </div>
            <div class="crn-sub-panel">
                <h2 class="crn-att-title">Out of every 100 assignments expected</h2>
                <div class="crn-sub-panel__body">
                    <div class="crn-sub-waffle" role="img" aria-label="Out of every 100 assignments expected: {{ $wPassed }} passed, {{ $wNotPassed }} submitted but not passed, {{ $wNotSubmitted }} not submitted">
                        @foreach($squares as $kind)<span class="is-{{ $kind }}"></span>@endforeach
                    </div>
                    <div class="crn-sub-legend">
                        @foreach([
                            ['passed', $wPassed, 'passed', $t['passed']],
                            ['notpassed', $wNotPassed, 'submitted, not passed', $t['submitted_not_passed']],
                            ['missing', $wNotSubmitted, 'not submitted', $t['not_submitted']],
                        ] as [$kind, $n, $label, $count])
                            <div class="crn-sub-legend__item">
                                <span class="crn-sub-legend__swatch is-{{ $kind }}" aria-hidden="true"></span>
                                <div>
                                    <div class="crn-sub-legend__big">{{ $n }} <span>{{ $label }}</span></div>
                                    <div class="crn-sub-legend__sub">{{ $num($count) }} assignments · {{ $share($count) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="crn-att-section">
            <div class="crn-att-head">
                <h2 class="crn-att-title">By intake and course</h2>
                <div class="crn-att-meta">Lowest pass rate first within each intake</div>
            </div>
            <div class="crn-tablewrap">
                <table class="crn-table crn-sub-table crn-gtable">
                    <thead>
                        <tr>
                            <th class="crn-table__course">Intake and course</th>
                            <th class="crn-sub-barcell">Passed · Submitted, not passed · Not submitted</th>
                            <th class="crn-sub-num">Expected</th>
                            <th class="crn-sub-num crn-sub-num--wide">Submitted</th>
                            <th class="crn-sub-num crn-sub-num--wide">Passed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['intakes'] as $intake)
                            @php $group = 'sub-intake-'.$loop->index; @endphp
                            <tr class="crn-grp">
                                <td class="crn-table__course">
                                    @if(!empty($intake['courses']))
                                        @include('pages.reports.intake-performance.rate.group-toggle', ['key' => $group, 'label' => $intake['intake']])
                                    @else
                                        {{ $intake['intake'] }}
                                    @endif
                                </td>
                                <td class="crn-sub-barcell">{!! $stack($intake['totals']) !!}</td>
                                {!! $figures($intake['totals']) !!}
                            </tr>
                            @foreach($intake['courses'] as $row)
                                <tr class="crn-in" data-crn-group="{{ $group }}">
                                    <td class="crn-table__course">{{ $row['name'] }}</td>
                                    <td class="crn-sub-barcell">{!! $stack($row) !!}</td>
                                    {!! $figures($row) !!}
                                </tr>
                            @endforeach
                        @endforeach
                        @if($report['many'])
                            <tr class="crn-all">
                                <td class="crn-table__course">All intakes</td>
                                <td class="crn-sub-barcell">{!! $stack($t) !!}</td>
                                {!! $figures($t) !!}
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="crn-footnote">
            Both rates are out of assignments expected.
            @if($t['pass_of_submitted'] !== null)
                Of the {{ $num($t['submitted']) }} assignments actually submitted, {{ $pct($t['pass_of_submitted']) }} passed.
            @endif
        </div>

        <details class="crn-att-details">
            <summary>Show figures by intake, course and term</summary>
            <div class="crn-tablewrap">
                @include('pages.reports.intake-performance.submission.detail', ['report' => $report, 'collapsible' => true])
            </div>
        </details>
    @endif
</div>
