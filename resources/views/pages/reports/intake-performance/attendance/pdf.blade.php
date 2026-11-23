@php
    $fontDir = resource_path('fonts/audit-certificate');
    $t = $report['totals'];
    $slots = $report['slots'];
    $trend = $report['trend'];
    $pct = function ($rate) { return number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $count = max(1, count($slots));

    // Attended / Absent bar: 591px less the 2px gap; each labelled cell has 12px padding.
    $attendedPx = ($t['total'] > 0 ? (int) round($t['present'] / $t['total'] * 589) : 0);
    $absentPx = 589 - $attendedPx;

    // Term chart: one column per term across 380px; bars 1.1px per point (110px at 100%).
    $chartCol = (int) floor(380 / $count);
    $chartBase = min(96, $chartCol - 8);
    $chartBar = min(64, $chartBase - 16);
    $chartValueSize = ($count <= 4 ? 20 : 14);

    // Course table, as the design: 315 course + bar + 67 rate + 84 students + 118 per term (narrower past three) + 8 = 1035px.
    $slotWidth = ($count <= 3 ? 118 : ($count == 4 ? 100 : ($count == 5 ? 88 : 76)));
    $barWidth = max(60, 1035 - 315 - 16 - 67 - 84 - $count * $slotWidth - 8);
    $termCells = function ($row) use ($slots, $pct) {
        $out = '';
        foreach ($slots as $key => $slot) {
            $cell = $row['cells'][$key] ?? null;
            $has = ($cell && $cell['rate'] !== null);
            $out .= '<td class="c-term'.($has ? '' : ' none').'">'.($has ? $pct($cell['rate']) : 'No term').'</td>';
        }
        return $out;
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Attendance Rate Report · {{ $report['intake'] }}</title>
<style>
    /*
     * Attendance Rate Report for one or more intakes, landscape A4 (1123 × 794
     * px at DomPDF's 96 dpi, the design's own size): the summary, then the
     * detail from a new page. DomPDF gives a 13-14px line about 20px whatever
     * the line-height, hence the small cell paddings. Columns
     * are px on auto-layout tables (DomPDF splits `table-layout: fixed` tables
     * evenly) and widths are content boxes: padding adds on.
     */
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 110px 44px 62px 44px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 13px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    .runhead { border-bottom: 2px solid #16242b; left: 0; position: fixed; right: 0; top: -76px; }
    .runhead td { padding-bottom: 10px; vertical-align: bottom; }
    .runhead__logo { display: block; height: 44px; width: auto; }
    .runhead__eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .runhead__title { font-size: 17px; font-weight: 700; margin-top: 3px; }
    .runhead__meta { text-align: right; }
    .runhead__intake { color: #0e5a54; font-size: 13px; font-weight: 700; }
    .runhead__by { color: #55616b; font-size: 12px; margin-top: 3px; }
    .runfoot { bottom: -36px; left: 0; position: fixed; right: 0; }
    .runfoot td { border-top: 1px solid #c3c9d1; color: #55616b; font-size: 12px; padding-top: 8px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    /* --- page 1: rate on the left, term chart on the right --- */
    .top { width: 1035px; }
    .top__left { vertical-align: bottom; width: 591px; }
    .top__gap { width: 64px; }
    .top__right { width: 380px; }
    .eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .hero { font-family: 'Source Serif 4', Georgia, serif; font-size: 60px; font-weight: 700; line-height: 0.82; margin-top: 8px; height: 58px; }
    .sentence { font-size: 14px; margin-top: 12px; }
    .split { height: 28px; margin-top: 12px; width: 591px; }
    .split td { color: #ffffff; font-size: 13px; font-weight: 600; height: 28px; padding-left: 12px; vertical-align: middle; white-space: nowrap; }
    .split td + td { border-left: 2px solid #ffffff; }
    .h2 { font-size: 15px; font-weight: 700; }
    .chart { margin-top: 12px; width: 380px; }
    .chart > tr > td, .chart > tbody > tr > td { vertical-align: bottom; width: {{ $chartCol }}px; }
    .chart__inner { width: {{ $chartBase }}px; }
    .chart__value { font-size: {{ $chartValueSize }}px; font-weight: 700; padding-bottom: 6px; text-align: center; }
    .chart__bar { background: #0e5a54; margin-left: {{ (int) floor(($chartBase - $chartBar) / 2) }}px; width: {{ $chartBar }}px; }
    .chart__base { border-top: 1px solid #16242b; margin-top: 6px; width: {{ $chartBase }}px; }
    .chart__label { color: #55616b; font-size: {{ $count <= 4 ? 13 : 11 }}px; padding-top: 5px; text-align: center; white-space: nowrap; }
    .trend { font-size: 13px; font-weight: 700; margin-top: 10px; }
    .trend.down { color: #8a3d0c; }
    .trend.up { color: #0e5a54; }

    /* --- page 1: by intake and course --- */
    .sechead { margin-top: 14px; }
    .sechead td { vertical-align: bottom; }
    .sechead__meta { color: #55616b; font-size: 12px; text-align: right; }
    .courses { margin-top: 14px; width: 1035px; }
    .courses th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 0 0 9px; text-align: right; white-space: nowrap; }
    .courses td { border-bottom: 1px solid #e3e7ea; font-size: 14px; padding: 8px 0; text-align: right; vertical-align: middle; }
    .courses th.c-course { padding-left: 8px; text-align: left; width: 307px; }
    .courses td.c-course { padding-left: 20px; text-align: left; font-weight: 600; }
    .courses .c-bar { padding-left: 16px; text-align: left; width: {{ $barWidth }}px; }
    .courses .c-rate { font-size: 15px; font-weight: 700; width: 67px; }
    .courses .c-rate.low { color: #8a3d0c; }
    .courses .c-students { width: 84px; }
    .courses .c-term { width: {{ $slotWidth }}px; }
    .courses th:last-child, .courses td:last-child { padding-right: 8px; }
    .courses .none { color: #55616b; font-weight: 400; }
    .courses tr.intake td { background: #e6ecef; border-bottom-color: #c3c9d1; font-weight: 700; }
    .courses tr.intake td.c-course, .courses tr.all td.c-course { padding-left: 8px; }
    .courses tr.all td { border-bottom-color: #16242b; border-top: 1px solid #16242b; font-weight: 700; }
    .track { background: #e9edf0; height: 12px; }
    .track div { background: #0e5a54; height: 12px; }
    .track div.low { background: #c8601a; }
    .track div.total { background: #16242b; }
    .footnote { color: #55616b; font-size: 12px; margin-top: 16px; }

    /* --- page 2: detail --- */
    .page2 { page-break-before: always; }
    .crn-att-detail { margin-top: 14px; width: 1035px; }
    .crn-att-detail th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 0 0 6px 10px; text-align: right; }
    .crn-att-detail td { border-bottom: 1px solid #e3e7ea; font-size: 12px; line-height: 0.95; padding: 3px 0 3px 10px; text-align: right; }
    .crn-att-detail .d-name { padding-left: 8px; text-align: left; }
    .crn-att-detail .d-rate { padding-right: 8px; width: 38px; }
    .crn-att-detail tr.d-intake td { background: #d3dce2; border-bottom-color: #16242b; font-weight: 700; }
    .crn-att-detail tr.d-course td, .crn-att-detail tr.d-all td { background: #e6ecef; font-weight: 700; }
    .crn-att-detail tr.d-course td { border-bottom-color: #c3c9d1; }
    .crn-att-detail tr.d-course .d-name { padding-left: 22px; }
    .crn-att-detail tr.d-term .d-name { padding-left: 36px; }
    .crn-att-detail tr.d-even td { background: #f4f6f8; }
    .crn-att-detail tr.d-all td { border-bottom-color: #16242b; }
    .crn-att-detail .d-reg { width: 60px; }
    .crn-att-detail .d-code { width: 40px; }
    .crn-att-detail .d-absent { width: 76px; }
    .crn-att-detail .d-total { width: 60px; }
    .crn-att-early { color: #55616b; font-size: 12px; margin-top: 18px; }
</style>
</head>
<body>
    <div class="runhead">
        <table>
            <tr>
                <td>
                    @if(!empty($logo))
                        <img class="runhead__logo" src="{{ $logo }}" alt="London Churchill College">
                    @else
                        <div class="runhead__eyebrow">London Churchill College</div>
                        <div class="runhead__title">Attendance Rate Report</div>
                    @endif
                </td>
                <td class="runhead__meta">
                    <div class="runhead__intake">{{ $report['intake'] }}</div>
                    <div class="runhead__by">Created by {{ $author }} · {{ $created }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="runfoot">
        <table>
            <tr>
                <td>London Churchill College · Attendance Rate Report · {{ $report['intake'] }} · <span class="pagenum"></span></td>
                <td class="right">Confidential, internal use only</td>
            </tr>
        </table>
    </div>

    @if(!$report['has_marks'])
        <div class="eyebrow">Attendance rate</div>
        <div class="hero" style="font-size: 40px;">No attendance yet</div>
        <div class="sentence">{{ !$report['has_courses'] ? 'No courses with students were found for '.($report['many'] ? 'these intakes.' : 'this intake.') : 'No attendance has been taken for the registered students of '.($report['many'] ? 'these intakes.' : 'this intake.') }}</div>
    @else
    <table class="top">
        <tr>
            <td class="top__left">
                <div class="eyebrow">Attendance rate{{ $report['many'] ? ' · All intakes' : '' }}</div>
                <div class="hero">{{ $pct($t['rate']) }}</div>
                <div class="sentence">Students attended {{ $num($t['present']) }} of {{ $num($t['total']) }} recorded sessions.</div>
                <table class="split">
                    <tr>
                        @if($attendedPx > 0)
                            <td style="width: {{ max(0, $attendedPx - 12) }}px; background: #0e5a54;">{{ $attendedPx >= 130 ? 'Attended '.$num($t['present']) : '' }}</td>
                        @endif
                        @if($absentPx > 0 && $t['codes']['A'] > 0)
                            <td style="width: {{ max(0, $absentPx - 12) }}px; background: #c8601a;">{{ $absentPx >= 120 ? 'Absent '.$num($t['codes']['A']) : '' }}</td>
                        @endif
                    </tr>
                </table>
            </td>
            <td class="top__gap"></td>
            <td class="top__right">
                <div class="h2">Attendance by term</div>
                <table class="chart">
                    <tr>
                        @foreach($slots as $key => $slot)
                            @php $cell = $t['cells'][$key] ?? null; @endphp
                            <td>
                                <table class="chart__inner">
                                    @if($cell && $cell['rate'] !== null)
                                        <tr><td class="chart__value">{{ $pct($cell['rate']) }}</td></tr>
                                        <tr><td><div class="chart__bar" style="height: {{ max(1, (int) round($cell['rate'] * 1.1)) }}px;"></div></td></tr>
                                    @endif
                                    <tr><td><div class="chart__base"></div></td></tr>
                                    <tr><td class="chart__label">{{ $slot['heading'] }}</td></tr>
                                </table>
                            </td>
                        @endforeach
                    </tr>
                </table>
                @if($trend && !$trend['single'])
                    <div class="trend {{ $trend['change'] < 0 ? 'down' : ($trend['change'] > 0 ? 'up' : '') }}">
                        @if($trend['change'] < 0)
                            Down {{ number_format(abs($trend['change']), 1) }} points since the first term.
                        @elseif($trend['change'] > 0)
                            Up {{ number_format($trend['change'], 1) }} points since the first term.
                        @else
                            Level with the first term.
                        @endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="sechead">
        <tr>
            <td class="h2">By intake and course</td>
            <td class="sechead__meta">Lowest rate first within each intake</td>
        </tr>
    </table>
    <table class="courses">
        <thead>
            <tr>
                <th class="c-course">Intake and course</th>
                <th class="c-bar" style="text-align: left;">Attendance rate</th>
                <th class="c-rate"></th>
                <th class="c-students">Students</th>
                @foreach($slots as $slot)
                    <th class="c-term{{ $loop->last ? ' c-last' : '' }}">{{ $slot['heading'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($report['intakes'] as $intake)
                @php $it = $intake['totals']; @endphp
                <tr class="intake">
                    <td class="c-course">{{ $intake['intake'] }}</td>
                    <td class="c-bar"><div class="track">@if($it['rate'] !== null)<div class="total" style="width: {{ $it['rate'] }}%;"></div>@endif</div></td>
                    <td class="c-rate">{{ $it['rate'] !== null ? $pct($it['rate']) : '–' }}</td>
                    <td class="c-students">{{ $num($it['registered']) }}</td>
                    {!! $termCells($it) !!}
                </tr>
                @foreach($intake['courses'] as $row)
                    <tr class="course">
                        <td class="c-course">{{ $row['name'] }}</td>
                        <td class="c-bar"><div class="track">@if($row['rate'] !== null)<div class="{{ $row['low'] ? 'low' : '' }}" style="width: {{ $row['rate'] }}%;"></div>@endif</div></td>
                        <td class="c-rate{{ $row['low'] ? ' low' : '' }}">{{ $row['rate'] !== null ? $pct($row['rate']) : '–' }}</td>
                        <td class="c-students">{{ $num($row['registered']) }}</td>
                        {!! $termCells($row) !!}
                    </tr>
                @endforeach
            @endforeach
            @if($report['many'])
                <tr class="all">
                    <td class="c-course">All intakes</td>
                    <td class="c-bar"><div class="track">@if($t['rate'] !== null)<div class="total" style="width: {{ $t['rate'] }}%;"></div>@endif</div></td>
                    <td class="c-rate">{{ $t['rate'] !== null ? $pct($t['rate']) : '–' }}</td>
                    <td class="c-students">{{ $num($t['registered']) }}</td>
                    {!! $termCells($t) !!}
                </tr>
            @endif
        </tbody>
    </table>
    <div class="footnote">Attendance rate = sessions attended ÷ sessions recorded. The breakdown by attendance code and term starts on the next page.</div>

    <div class="page2">
        <table>
            <tr>
                <td class="h2">Detail: sessions by attendance code and term</td>
                <td class="sechead__meta">Every code except A counts as attended</td>
            </tr>
        </table>
        @include('pages.reports.intake-performance.attendance.detail', ['report' => $report, 'withCodesNote' => false])
    </div>
    @endif
</body>
</html>
