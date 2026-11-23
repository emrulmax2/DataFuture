{{--
    Result of a new intake rate report (Continuation Rate, Retention Rate) for
    one or more intakes, injected under its form by
    resources/js/intake-rate-reports.js. Styles: rate/styles.blade.php.

    $report  App\Services\Reports\IntakeRateReport::buildMany()
    $copy    the report's wording (IntakeRateController::copy())
--}}
@php
    $t = $report['totals'];
    $pct = function ($rate) { return number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $day = function ($date) { return date('j M Y', strtotime($date)); };
    $small = \App\Services\Reports\IntakeRateReport::SMALL_COHORT;
    // Shown after a course name, as the design's "· small cohort".
    $flags = function ($row) use ($copy, $day) {
        return array_values(array_filter([
            (!$row['due'] ? 'not due until '.$day($row['due_date']) : null),
            ($row['due'] && $row['rate'] === null ? 'no registered students' : null),
            (!empty($copy['show_running']) && $row['due'] && $row['running'] ? 'still running · ends '.$day($row['end_date']) : null),
            ($row['small'] ? 'small cohort' : null),
        ]));
    };
@endphp

<div class="crn-result">
    @if(!$report['has_courses'])
        <div class="crn-notice">No courses with students were found for {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
    @else
        @if(!$report['has_due'])
            <div class="crn-notice">
                <strong>Not due yet.</strong>
                @if($report['next_due'])
                    {{ $copy['not_due'] }} {{ $day($report['next_due']) }}, so the {{ strtolower($copy['rate']) }} can be worked out from then.
                @endif
            </div>
        @else
            <div class="crn-kpis">
                <div class="crn-kpi crn-kpi--main">
                    <div class="crn-kpi__label">{{ $copy['rate'] }}</div>
                    <div class="crn-kpi__value">{{ $t['rate'] !== null ? $pct($t['rate']) : 'No rate' }}</div>
                    <div class="crn-kpi__note">{{ $num($t['kept']) }} of {{ $num($t['registered']) }} registered students</div>
                </div>
                <div class="crn-kpi">
                    <div class="crn-kpi__label">{{ $copy['kept'] }}</div>
                    <div class="crn-kpi__value">{{ $num($t['kept']) }}</div>
                    <div class="crn-kpi__note">{{ $copy['kept_note'] }}</div>
                </div>
                <div class="crn-kpi">
                    <div class="crn-kpi__label">Dropped out</div>
                    <div class="crn-kpi__value crn-kpi__value--down">{{ $num($t['dropped_out']) }}</div>
                    <div class="crn-kpi__note">{{ $copy['dropped_note'] }}</div>
                </div>
                <div class="crn-kpi">
                    <div class="crn-kpi__label">Never registered</div>
                    <div class="crn-kpi__value">{{ $num($t['never_registered']) }}</div>
                    <div class="crn-kpi__note">Of {{ $num($t['intake']) }} students · not in the rate</div>
                </div>
            </div>
        @endif

        <div class="crn-att-section">
            <div class="crn-att-head">
                <h2 class="crn-att-title">By intake and course</h2>
                <div class="crn-att-meta">Lowest rate first within each intake</div>
            </div>
            <div class="crn-tablewrap">
                <table class="crn-table crn-gtable">
                    <thead>
                        <tr>
                            <th class="crn-table__course">Intake and course</th>
                            <th class="crn-table__bar">{{ $copy['rate'] }}</th>
                            <th class="crn-g-rate"></th>
                            <th class="crn-table__num">Registered</th>
                            <th class="crn-table__num">{{ $copy['kept'] }}</th>
                            <th class="crn-table__num crn-table__num--wide">Dropped out</th>
                            <th class="crn-table__num crn-table__num--wider">Never registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['intakes'] as $intake)
                            @php
                                $it = $intake['totals'];
                                $group = $copy['key'].'-intake-'.$loop->index;
                            @endphp
                            <tr class="crn-grp">
                                <td class="crn-table__course">
                                    @if(!empty($intake['courses']))
                                        @include('pages.reports.intake-performance.rate.group-toggle', ['key' => $group, 'label' => $intake['intake']])
                                    @else
                                        {{ $intake['intake'] }}
                                    @endif
                                </td>
                                @if(empty($intake['courses']))
                                    <td class="crn-table__bar"></td>
                                    <td class="crn-g-rate is-none" colspan="5">No courses with students</td>
                                @elseif(!$intake['has_due'])
                                    <td class="crn-table__bar"><div class="crn-track" aria-hidden="true"></div></td>
                                    <td class="crn-g-rate is-none">Not due</td>
                                    <td class="crn-table__num">–</td><td class="crn-table__num">–</td><td class="crn-table__num">–</td><td class="crn-table__num">–</td>
                                @else
                                    <td class="crn-table__bar">
                                        <div class="crn-track" @if($it['rate'] !== null) role="img" aria-label="{{ $pct($it['rate']) }}" @else aria-hidden="true" @endif>
                                            @if($it['rate'] !== null)<div class="crn-track__fill is-total" style="width: {{ $it['rate'] }}%;"></div>@endif
                                        </div>
                                    </td>
                                    <td class="crn-g-rate{{ $it['rate'] === null ? ' is-none' : '' }}">{{ $it['rate'] !== null ? $pct($it['rate']) : 'No rate' }}</td>
                                    <td class="crn-table__num">{{ $num($it['registered']) }}</td>
                                    <td class="crn-table__num">{{ $num($it['kept']) }}</td>
                                    <td class="crn-table__num">{{ $num($it['dropped_out']) }}</td>
                                    <td class="crn-table__num">{{ $num($it['never_registered']) }}</td>
                                @endif
                            </tr>
                            @foreach($intake['courses'] as $row)
                                @php $notes = $flags($row); @endphp
                                <tr class="crn-in" data-crn-group="{{ $group }}">
                                    <td class="crn-table__course">{{ $row['name'] }}@if($notes) <span class="crn-flag">· {{ implode(' · ', $notes) }}</span>@endif</td>
                                    <td class="crn-table__bar">
                                        <div class="crn-track" @if($row['rate'] !== null) role="img" aria-label="{{ $pct($row['rate']) }}" @else aria-hidden="true" @endif>
                                            @if($row['rate'] !== null)<div class="crn-track__fill{{ $row['low'] ? ' is-low' : '' }}" style="width: {{ $row['rate'] }}%;"></div>@endif
                                        </div>
                                    </td>
                                    @if(!$row['due'])
                                        <td class="crn-g-rate is-none">Not due</td>
                                        <td class="crn-table__num">–</td><td class="crn-table__num">–</td><td class="crn-table__num">–</td><td class="crn-table__num">–</td>
                                    @else
                                        <td class="crn-g-rate{{ $row['rate'] === null ? ' is-none' : ($row['low'] ? ' is-low' : '') }}">{{ $row['rate'] !== null ? $pct($row['rate']) : 'No rate' }}</td>
                                        <td class="crn-table__num">{{ $num($row['registered']) }}</td>
                                        <td class="crn-table__num">{{ $num($row['kept']) }}</td>
                                        <td class="crn-table__num">{{ $num($row['dropped_out']) }}</td>
                                        <td class="crn-table__num">{{ $num($row['never_registered']) }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                        @if($report['many'] && $report['has_due'])
                            <tr class="crn-all">
                                <td class="crn-table__course">All intakes</td>
                                <td class="crn-table__bar">
                                    <div class="crn-track" @if($t['rate'] !== null) role="img" aria-label="{{ $pct($t['rate']) }}" @endif>
                                        @if($t['rate'] !== null)<div class="crn-track__fill is-total" style="width: {{ $t['rate'] }}%;"></div>@endif
                                    </div>
                                </td>
                                <td class="crn-g-rate{{ $t['rate'] === null ? ' is-none' : '' }}">{{ $t['rate'] !== null ? $pct($t['rate']) : 'No rate' }}</td>
                                <td class="crn-table__num">{{ $num($t['registered']) }}</td>
                                <td class="crn-table__num">{{ $num($t['kept']) }}</td>
                                <td class="crn-table__num">{{ $num($t['dropped_out']) }}</td>
                                <td class="crn-table__num">{{ $num($t['never_registered']) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="crn-footnote">{{ $copy['how'] }} Small cohort = fewer than {{ $small }} registered students.</div>
</div>
