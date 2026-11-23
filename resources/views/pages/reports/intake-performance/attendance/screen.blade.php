{{--
    Attendance Rate, new version: the result for one or more intakes, injected
    under the form in the "New version" tab (resources/js/intake-rate-reports.js).
    Styles: rate/styles.blade.php (`.crn-*`, attendance parts `.crn-att-*`,
    intake groups `.crn-gtable`).

    $report  App\Services\Reports\AttendanceRate::buildMany()
--}}
@php
    $t = $report['totals'];
    $slots = $report['slots'];
    $pct = function ($rate) { return number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $termCells = function ($row) use ($slots, $pct) {
        $out = '';
        foreach ($slots as $key => $slot) {
            $cell = $row['cells'][$key] ?? null;
            $has = ($cell && $cell['rate'] !== null);
            $out .= '<td class="crn-att-term'.($slot['heading'] !== $slot['short'] ? ' is-wide' : '').($has ? '' : ' is-none').'">'.($has ? $pct($cell['rate']) : 'No term').'</td>';
        }
        return $out;
    };
    $track = function ($rate, $class) use ($pct) {
        if ($rate === null) {
            return '<div class="crn-track crn-att-track" aria-hidden="true"></div>';
        }
        return '<div class="crn-track crn-att-track" role="img" aria-label="'.$pct($rate).'"><div class="crn-track__fill '.$class.'" style="width: '.$rate.'%;"></div></div>';
    };
@endphp

<div class="crn-result">
    @if(!$report['has_courses'])
        <div class="crn-notice">No courses with students were found for {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @elseif(!$report['has_marks'])
        <div class="crn-notice"><strong>No attendance yet.</strong> No attendance has been taken for the registered students of {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @else
        <div class="crn-kpis">
            <div class="crn-kpi crn-kpi--main">
                <div class="crn-kpi__label">Attendance rate</div>
                <div class="crn-kpi__value">{{ $pct($t['rate']) }}</div>
                <div class="crn-kpi__note">{{ $num($t['present']) }} of {{ $num($t['total']) }} sessions attended</div>
            </div>
            @php $firstShown = false; @endphp
            @foreach($slots as $key => $slot)
                @php $cell = $t['cells'][$key] ?? null; @endphp
                @if($cell && $cell['rate'] !== null)
                    <div class="crn-kpi">
                        <div class="crn-kpi__label">{{ $slot['heading'] }} term</div>
                        <div class="crn-kpi__value">{{ $pct($cell['rate']) }}</div>
                        <div class="crn-kpi__note">
                            @if(!$firstShown)
                                First term
                            @elseif($cell['vs_first'] < 0)
                                Down {{ number_format(abs($cell['vs_first']), 1) }} points on the first term
                            @elseif($cell['vs_first'] > 0)
                                Up {{ number_format($cell['vs_first'], 1) }} points on the first term
                            @else
                                Same as the first term
                            @endif
                        </div>
                    </div>
                    @php $firstShown = true; @endphp
                @endif
            @endforeach
        </div>

        <div class="crn-att-section">
            <div class="crn-att-head">
                <h2 class="crn-att-title">By intake and course</h2>
                <div class="crn-att-meta">Lowest rate first within each intake</div>
            </div>
            <div class="crn-tablewrap">
                <table class="crn-table crn-att-table crn-gtable">
                    <thead>
                        <tr>
                            <th class="crn-table__course">Intake and course</th>
                            <th class="crn-att-bar">Attendance rate</th>
                            <th class="crn-g-rate"></th>
                            <th class="crn-att-num">Students</th>
                            @foreach($slots as $slot)
                                <th class="crn-att-term{{ $slot['heading'] !== $slot['short'] ? ' is-wide' : '' }}">{{ $slot['heading'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['intakes'] as $intake)
                            @php $it = $intake['totals']; $group = 'atn-intake-'.$loop->index; @endphp
                            <tr class="crn-grp">
                                <td class="crn-table__course">
                                    @if(!empty($intake['courses']))
                                        @include('pages.reports.intake-performance.rate.group-toggle', ['key' => $group, 'label' => $intake['intake']])
                                    @else
                                        {{ $intake['intake'] }}
                                    @endif
                                </td>
                                <td class="crn-att-bar">{!! $track($it['rate'], 'is-total') !!}</td>
                                <td class="crn-g-rate{{ $it['rate'] === null ? ' is-none' : '' }}">{{ $it['rate'] !== null ? $pct($it['rate']) : '–' }}</td>
                                <td class="crn-att-num">{{ $num($it['registered']) }}</td>
                                {!! $termCells($it) !!}
                            </tr>
                            @foreach($intake['courses'] as $row)
                                <tr class="crn-in" data-crn-group="{{ $group }}">
                                    <td class="crn-table__course">{{ $row['name'] }}</td>
                                    <td class="crn-att-bar">{!! $track($row['rate'], $row['low'] ? 'is-low' : '') !!}</td>
                                    <td class="crn-g-rate{{ $row['rate'] === null ? ' is-none' : ($row['low'] ? ' is-low' : '') }}">{{ $row['rate'] !== null ? $pct($row['rate']) : '–' }}</td>
                                    <td class="crn-att-num">{{ $num($row['registered']) }}</td>
                                    {!! $termCells($row) !!}
                                </tr>
                            @endforeach
                        @endforeach
                        @if($report['many'])
                            <tr class="crn-all">
                                <td class="crn-table__course">All intakes</td>
                                <td class="crn-att-bar">{!! $track($t['rate'], 'is-total') !!}</td>
                                <td class="crn-g-rate">{{ $t['rate'] !== null ? $pct($t['rate']) : '–' }}</td>
                                <td class="crn-att-num">{{ $num($t['registered']) }}</td>
                                {!! $termCells($t) !!}
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="crn-footnote">Attendance rate = sessions attended ÷ sessions recorded.</div>

        <details class="crn-att-details">
            <summary>Show sessions by attendance code and term</summary>
            <div class="crn-tablewrap">
                @include('pages.reports.intake-performance.attendance.detail', ['report' => $report, 'withCodesNote' => true, 'collapsible' => true])
            </div>
        </details>
    @endif
</div>
