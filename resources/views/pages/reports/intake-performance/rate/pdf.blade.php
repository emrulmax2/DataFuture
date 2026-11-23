@php
    $fontDir = resource_path('fonts/audit-certificate');
    $t = $report['totals'];
    $pct = function ($rate) { return number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $day = function ($date) { return date('j M Y', strtotime($date)); };
    $say = function ($text) use ($t, $num) { return strtr($text, [':kept' => $num($t['kept']), ':registered' => $num($t['registered']), ':dropped' => $num($t['dropped_out'])]); };
    $flags = function ($row) use ($copy, $day) {
        return array_values(array_filter([
            (!$row['due'] ? 'not due until '.$day($row['due_date']) : null),
            ($row['due'] && $row['rate'] === null ? 'no registered students' : null),
            (!empty($copy['show_running']) && $row['due'] && $row['running'] ? 'still running · ends '.$day($row['end_date']) : null),
            ($row['small'] ? 'small cohort' : null),
        ]));
    };

    // One bar per intake, 443px: kept, dropped out, never registered, with 2px between.
    $bar = function ($it) use ($copy, $num) {
        $parts = array_values(array_filter([
            ['n' => $it['kept'], 'colour' => '#0e5a54', 'label' => $num($it['kept']).' '.$copy['kept_lower'], 'min' => 110],
            ['n' => $it['dropped_out'], 'colour' => '#c8601a', 'label' => $num($it['dropped_out']), 'min' => 28],
            ['n' => $it['never_registered'], 'colour' => '#c3c9d1', 'label' => '', 'min' => 999],
        ], function ($p) { return $p['n'] > 0; }));
        $room = 443 - 2 * max(0, count($parts) - 1);
        $used = 0;
        foreach ($parts as $i => $p) {
            $px = ($i === count($parts) - 1 ? $room - $used : (int) round($p['n'] / max(1, $it['intake']) * $room));
            $parts[$i]['px'] = $px;
            $parts[$i]['text'] = ($px >= $p['min'] ? $p['label'] : '');
            $used += $px;
        }
        return $parts;
    };
    $legend = [
        [$t['kept'], $copy['kept'], $copy['kept_note'], '#0e5a54'],
        [$t['dropped_out'], 'Dropped out', $copy['dropped_note'], '#c8601a'],
        [$t['never_registered'], 'Never registered', 'Not counted in the rate', '#c3c9d1'],
    ];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $copy['title'] }} Report · {{ $report['intake'] }}</title>
<style>
    /*
     * Continuation / Retention Rate Report (one view, $copy holds the wording)
     * for one or more intakes, landscape A4 (1123 × 794 px at DomPDF's 96 dpi,
     * the design's own size, so its px values carry over). Hanken Grotesk and
     * Source Serif 4 stand in for the design's Inter and Georgia. Columns are
     * px on auto-layout tables (DomPDF splits `table-layout: fixed` tables
     * evenly), and widths are content boxes: padding adds on.
     */
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 110px 44px 62px 44px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 13px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* --- running header / footer --- */
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
    .pagenum:after { content: "Page " counter(page) " of {{ (int) $pages }}"; }

    /* --- top: rate on the left, what happened in each intake on the right --- */
    .top { width: 1035px; }
    .top > tr > td, .top > tbody > tr > td { vertical-align: middle; }
    .top__left { width: 340px; }
    .top__gap { width: 48px; }
    .top__right { width: 647px; }
    .eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .hero { font-family: 'Source Serif 4', Georgia, serif; font-size: 60px; font-weight: 700; line-height: 0.82; margin-top: 8px; height: 58px; }
    .hero--small { font-size: 40px; height: 42px; }
    .sentence { font-size: 14px; line-height: 1.12; margin-top: 12px; }
    .h2 { font-size: 15px; font-weight: 700; }

    .bars { margin-top: 6px; width: 647px; }
    .bars > tr > td, .bars > tbody > tr > td { padding: 4px 0; vertical-align: middle; }
    .bars .b-label { font-size: 13px; font-weight: 600; width: 130px; }
    .bars .b-bar { width: 443px; }
    .bars .b-rate { font-size: 16px; font-weight: 700; text-align: right; width: 74px; }
    .bars .b-none { color: #55616b; font-size: 13px; }
    .stack { height: 26px; width: 443px; }
    .stack td { border-left: 2px solid #ffffff; color: #ffffff; font-size: 13px; font-weight: 600; height: 26px; vertical-align: middle; white-space: nowrap; }
    .stack td.first { border-left: 0; padding-left: 12px; }
    .stack td.centre { text-align: center; }
    .legend { margin-top: 8px; width: 647px; }
    .legend > tr > td, .legend > tbody > tr > td { width: 215px; }
    .legend__swatch { padding-top: 2px; width: 18px; }
    .legend__swatch span { display: block; height: 10px; width: 10px; }
    .legend__title { font-weight: 700; }
    .legend__sub { color: #55616b; font-size: 12px; margin-top: 1px; }

    /* --- by intake and course --- */
    .sechead { margin-top: 16px; }
    .sechead td { vertical-align: bottom; }
    .sechead__meta { color: #55616b; font-size: 12px; text-align: right; }
    .courses { margin-top: 12px; width: 1035px; }
    .courses th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 500; padding: 0 0 9px; text-align: right; white-space: nowrap; }
    .courses td { border-bottom: 1px solid #e3e7ea; font-size: 13px; padding: 7px 0; text-align: right; vertical-align: middle; }
    .courses th.c-course { padding-left: 8px; text-align: left; width: 329px; }
    .courses td.c-course { padding-left: 20px; text-align: left; }
    .courses .c-bar { padding-left: 16px; text-align: left; width: 190px; }
    .courses .c-rate { font-size: 15px; font-weight: 700; width: 68px; }
    .courses .c-rate.low { color: #8a3d0c; }
    .courses .c-rate.none { color: #55616b; font-size: 12px; font-weight: 400; }
    .courses .c-num { width: 92px; }
    .courses .c-num-wide { width: 110px; }
    .courses .c-last { padding-right: 8px; width: 122px; }
    .courses tr.intake td { background: #e6ecef; border-bottom-color: #c3c9d1; font-weight: 700; }
    .courses tr.intake td.c-course { padding-left: 8px; }
    .courses tr.course td.c-course { font-weight: 600; }
    .courses tr.all td { border-bottom-color: #16242b; border-top: 1px solid #16242b; font-weight: 700; }
    .courses tr.all td.c-course { padding-left: 8px; }
    .flag { color: #55616b; font-size: 12px; font-weight: 400; }
    .track { background: #e9edf0; height: 12px; }
    .track div { background: #0e5a54; height: 12px; }
    .track div.low { background: #c8601a; }
    .track div.total { background: #16242b; }

    /* --- notes --- */
    .notes { margin-top: 14px; width: 1035px; page-break-inside: avoid; }
    .notes td { color: #55616b; font-size: 12px; line-height: 1.02; }
    .notes td.n1 { width: 512px; }
    .notes td.gap { width: 30px; }
    .notes b { color: #16242b; }
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
                        <div class="runhead__title">{{ $copy['title'] }} Report</div>
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
                <td>London Churchill College · {{ $copy['title'] }} Report · {{ $report['intake'] }}@if($pages > 1) · <span class="pagenum"></span>@endif</td>
                <td class="right">Confidential, internal use only</td>
            </tr>
        </table>
    </div>

    <table class="top">
        <tr>
            <td class="top__left">
                <div class="eyebrow">{{ $copy['rate'] }}{{ $report['many'] ? ' · All intakes' : '' }}</div>
                @if($report['has_due'] && $t['rate'] !== null)
                    <div class="hero">{{ $pct($t['rate']) }}</div>
                    <div class="sentence">{{ $say($copy['sentence']) }}</div>
                @elseif($report['has_due'])
                    <div class="hero hero--small">No rate</div>
                    <div class="sentence">None of the {{ $num($t['intake']) }} students registered.</div>
                @elseif(!empty($report['next_due']))
                    <div class="hero hero--small">Not due yet</div>
                    <div class="sentence">{{ $copy['not_due'] }} {{ $day($report['next_due']) }}.</div>
                @else
                    <div class="hero hero--small">No data</div>
                    <div class="sentence">No courses with students were found for {{ $report['many'] ? 'these intakes' : 'this intake' }}.</div>
                @endif
            </td>
            <td class="top__gap"></td>
            <td class="top__right">
                @if($report['has_courses'] && $t['intake'] > 0)
                    <div class="h2">What happened to the {{ $num($t['intake']) }} students{{ $report['many'] ? ' in each intake' : '' }}</div>
                    <table class="bars">
                        @foreach($report['intakes'] as $intake)
                            @php $it = $intake['totals']; @endphp
                            <tr>
                                <td class="b-label">{{ preg_replace('/ intake$/', '', $intake['intake']) }}</td>
                                @if(!$intake['has_due'] || $it['intake'] < 1)
                                    <td class="b-bar b-none">{{ !$intake['has_due'] && $intake['next_due'] ? 'Not due until '.$day($intake['next_due']) : 'No students' }}</td>
                                    <td class="b-rate"></td>
                                @else
                                    <td class="b-bar">
                                        <table class="stack">
                                            <tr>
                                                @foreach($bar($it) as $i => $seg)
                                                    <td class="{{ $i === 0 ? 'first' : 'centre' }}" style="width: {{ $i === 0 ? max(0, $seg['px'] - 12) : $seg['px'] }}px; background: {{ $seg['colour'] }};">{{ $seg['text'] }}</td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                    <td class="b-rate">{{ $it['rate'] !== null ? $pct($it['rate']) : '–' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                    <table class="legend">
                        <tr>
                            @foreach($legend as [$n, $title, $sub, $colour])
                                <td>
                                    <table>
                                        <tr>
                                            <td class="legend__swatch"><span style="background: {{ $colour }};"></span></td>
                                            <td>
                                                <div class="legend__title">{{ $num($n) }} {{ $title }}</div>
                                                <div class="legend__sub">{{ $sub }}</div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            @endforeach
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>

    @if($report['has_courses'])
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
                    <th class="c-bar">{{ $copy['rate'] }}</th>
                    <th class="c-rate"></th>
                    <th class="c-num">Registered</th>
                    <th class="c-num">{{ $copy['kept'] }}</th>
                    <th class="c-num-wide">Dropped out</th>
                    <th class="c-last">Never registered</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['intakes'] as $intake)
                    @php $it = $intake['totals']; @endphp
                    <tr class="intake">
                        <td class="c-course">{{ $intake['intake'] }}</td>
                        @if(empty($intake['courses']))
                            <td class="c-bar"></td>
                            <td class="c-rate none" colspan="5">No courses with students</td>
                        @elseif(!$intake['has_due'])
                            <td class="c-bar"><div class="track"></div></td>
                            <td class="c-rate none">Not due</td>
                            <td class="c-num">–</td><td class="c-num">–</td><td class="c-num-wide">–</td><td class="c-last">–</td>
                        @else
                            <td class="c-bar"><div class="track">@if($it['rate'] !== null)<div class="total" style="width: {{ $it['rate'] }}%;"></div>@endif</div></td>
                            <td class="c-rate{{ $it['rate'] === null ? ' none' : '' }}">{{ $it['rate'] !== null ? $pct($it['rate']) : 'No rate' }}</td>
                            <td class="c-num">{{ $num($it['registered']) }}</td>
                            <td class="c-num">{{ $num($it['kept']) }}</td>
                            <td class="c-num-wide">{{ $num($it['dropped_out']) }}</td>
                            <td class="c-last">{{ $num($it['never_registered']) }}</td>
                        @endif
                    </tr>
                    @foreach($intake['courses'] as $row)
                        @php $notes = $flags($row); @endphp
                        <tr class="course">
                            <td class="c-course">{{ $row['name'] }}@if($notes) <span class="flag">· {{ implode(' · ', $notes) }}</span>@endif</td>
                            <td class="c-bar"><div class="track">@if($row['rate'] !== null)<div class="{{ $row['low'] ? 'low' : '' }}" style="width: {{ $row['rate'] }}%;"></div>@endif</div></td>
                            @if(!$row['due'])
                                <td class="c-rate none">Not due</td>
                                <td class="c-num">–</td><td class="c-num">–</td><td class="c-num-wide">–</td><td class="c-last">–</td>
                            @else
                                <td class="c-rate{{ $row['rate'] === null ? ' none' : ($row['low'] ? ' low' : '') }}">{{ $row['rate'] !== null ? $pct($row['rate']) : 'No rate' }}</td>
                                <td class="c-num">{{ $num($row['registered']) }}</td>
                                <td class="c-num">{{ $num($row['kept']) }}</td>
                                <td class="c-num-wide">{{ $num($row['dropped_out']) }}</td>
                                <td class="c-last">{{ $num($row['never_registered']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
                @if($report['many'] && $report['has_due'])
                    <tr class="all">
                        <td class="c-course">All intakes</td>
                        <td class="c-bar"><div class="track">@if($t['rate'] !== null)<div class="total" style="width: {{ $t['rate'] }}%;"></div>@endif</div></td>
                        <td class="c-rate{{ $t['rate'] === null ? ' none' : '' }}">{{ $t['rate'] !== null ? $pct($t['rate']) : 'No rate' }}</td>
                        <td class="c-num">{{ $num($t['registered']) }}</td>
                        <td class="c-num">{{ $num($t['kept']) }}</td>
                        <td class="c-num-wide">{{ $num($t['dropped_out']) }}</td>
                        <td class="c-last">{{ $num($t['never_registered']) }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="notes">
            <tr>
                <td class="n1"><b>How the rate is worked out.</b> {{ $copy['how'] }}</td>
                <td class="gap"></td>
                <td>@if($report['small_note'])<b>Small cohort.</b> {{ $report['small_note'] }}@endif</td>
            </tr>
        </table>
    @endif
</body>
</html>
