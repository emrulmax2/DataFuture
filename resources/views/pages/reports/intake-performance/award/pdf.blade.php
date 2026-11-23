@php
    $fontDir = resource_path('fonts/audit-certificate');
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

    // One bar per intake, 443px: completed, did not complete, with 2px between.
    $bar = function ($it) use ($num) {
        $parts = array_values(array_filter([
            ['n' => $it['completed'], 'colour' => '#0e5a54', 'label' => $num($it['completed']).' completed', 'min' => 110],
            ['n' => $it['not_completed'], 'colour' => '#c8601a', 'label' => $num($it['not_completed']), 'min' => 30],
        ], function ($p) { return $p['n'] > 0; }));
        $room = 443 - 2 * max(0, count($parts) - 1);
        $used = 0;
        foreach ($parts as $i => $p) {
            $px = ($i === count($parts) - 1 ? $room - $used : (int) round($p['n'] / max(1, $it['registered']) * $room));
            $parts[$i]['px'] = $px;
            $parts[$i]['text'] = ($px >= $p['min'] ? $p['label'] : '');
            $used += $px;
        }
        return $parts;
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Award Rate Report · {{ $report['intake'] }}</title>
<style>
    /*
     * Award Rate Report for one or more intakes, landscape A4 (1123 × 794 px at
     * DomPDF's 96 dpi, the design's own size): the rate, one bar per intake,
     * then each intake's courses with one dot per student, 28 dots to a line.
     * Columns are px on auto-layout tables and widths are content boxes:
     * padding adds on. DomPDF ignores border-radius on cells, so each dot is a
     * rounded div inside its cell, and it gives a 13-14px line about 20px
     * whatever the line-height, hence the small paddings.
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
    .pagenum:after { content: "Page " counter(page) " of {{ (int) $pages }}"; }

    /* --- top: the rate on the left, what happened in each intake on the right --- */
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
    .stack td { border-left: 2px solid #ffffff; color: #ffffff; font-size: 13px; font-weight: 600; height: 26px; padding-left: 12px; vertical-align: middle; white-space: nowrap; }
    .stack td.first { border-left: 0; }
    .legend { margin-top: 8px; width: 647px; }
    .legend > tr > td, .legend > tbody > tr > td { width: 323px; }
    .legend__swatch { padding-top: 2px; width: 18px; }
    .legend__swatch span { display: block; height: 10px; width: 10px; }
    .legend__title { font-weight: 700; }
    .legend__sub { color: #55616b; font-size: 12px; margin-top: 1px; }

    /* --- by intake and course --- */
    .sechead { margin-top: 18px; }
    .sechead > tr > td, .sechead > tbody > tr > td { border-bottom: 1px solid #16242b; padding-bottom: 9px; vertical-align: bottom; }
    .dotkey { width: auto; }
    .dotkey td { color: #55616b; font-size: 12px; vertical-align: middle; white-space: nowrap; }
    .dotkey .key { padding: 0 6px 0 22px; }
    .dotkey .key div { border-radius: 5px; height: 10px; width: 10px; }

    .intake { background: #e6ecef; border-bottom: 1px solid #c3c9d1; width: 1035px; page-break-after: avoid; }
    .intake td { font-weight: 700; padding: 8px 8px; vertical-align: middle; }
    .intake td.right { text-align: right; }
    .course { border-bottom: 1px solid #e3e7ea; page-break-inside: avoid; width: 1035px; }
    .course > tr > td, .course > tbody > tr > td { padding: 14px 0; vertical-align: middle; }
    .course__info { padding-left: 8px !important; padding-right: 20px !important; width: 352px; }
    .course__dots { width: 647px; }
    .course__title { font-size: 14px; font-weight: 700; }
    .course__sub { color: #55616b; font-size: 12px; }
    .course__rate { font-size: 30px; font-weight: 700; height: 40px; margin-top: 2px; }
    .course__rate.low { color: #8a3d0c; }
    .course__rate.none { color: #55616b; }
    .course__counts { font-size: 13px; }
    .course__counts b { font-weight: 700; }
    .dots { width: auto; }
    .dots td { height: 15px; padding: 0 5px 5px 0; width: 15px; }
    .dots tr.last td { padding-bottom: 0; }
    .dots td div { border-radius: 7.5px; height: 15px; width: 15px; }
    .done { background: #0e5a54; }
    .not { background: #c8601a; }

    .footnote { color: #55616b; font-size: 12px; margin-top: 20px; }
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
                        <div class="runhead__title">Award Rate Report</div>
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
                <td>London Churchill College · Award Rate Report · {{ $report['intake'] }}@if($pages > 1) · <span class="pagenum"></span>@endif</td>
                <td class="right">Confidential, internal use only</td>
            </tr>
        </table>
    </div>

    @if($t['registered'] === 0)
        <div class="eyebrow">Award rate</div>
        <div class="hero hero--small">No rate</div>
        <div class="sentence" style="margin-top: 16px;">{{ !$report['has_courses'] ? 'No courses with students were found for '.($report['many'] ? 'these intakes.' : 'this intake.') : 'None of the students has an awarding body reference yet, so there is no award rate.' }}</div>
    @else
    <table class="top">
        <tr>
            <td class="top__left">
                <div class="eyebrow">Award rate{{ $report['many'] ? ' · All intakes' : '' }}</div>
                <div class="hero">{{ $pct($t['rate']) }}</div>
                <div class="sentence">{{ $num($t['completed']) }} of {{ $num($t['registered']) }} registered students completed their award. {{ $num($t['not_completed']) }} did not.</div>
            </td>
            <td class="top__gap"></td>
            <td class="top__right">
                <div class="h2">What happened to the {{ $num($t['registered']) }} students{{ $report['many'] ? ' in each intake' : '' }}</div>
                <table class="bars">
                    @foreach($report['intakes'] as $intake)
                        @php $it = $intake['totals']; @endphp
                        <tr>
                            <td class="b-label">{{ preg_replace('/ intake$/', '', $intake['intake']) }}</td>
                            @if($it['registered'] < 1)
                                <td class="b-bar b-none">No registered students</td>
                                <td class="b-rate"></td>
                            @else
                                <td class="b-bar">
                                    <table class="stack">
                                        <tr>
                                            @foreach($bar($it) as $i => $seg)
                                                <td class="{{ $i === 0 ? 'first' : '' }}" style="width: {{ max(0, $seg['px'] - 12) }}px; background: {{ $seg['colour'] }};">{{ $seg['text'] }}</td>
                                            @endforeach
                                        </tr>
                                    </table>
                                </td>
                                <td class="b-rate">{{ $pct($it['rate']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                </table>
                <table class="legend">
                    <tr>
                        @foreach([[$t['completed'], 'Completed', 'Completed their award', '#0e5a54'], [$t['not_completed'], 'Did not complete', 'Registered but not completed', '#c8601a']] as [$n, $title, $sub, $colour])
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
            </td>
        </tr>
    </table>

    <table class="sechead">
        <tr>
            <td class="h2">By intake and course</td>
            <td style="text-align: right;">
                <table class="dotkey" align="right">
                    <tr>
                        <td>Each dot is one student</td>
                        <td class="key"><div class="done"></div></td>
                        <td>Completed</td>
                        <td class="key"><div class="not"></div></td>
                        <td>Did not complete</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @foreach($report['intakes'] as $intake)
        @php $it = $intake['totals']; @endphp
        <table class="intake">
            <tr>
                <td>{{ $intake['intake'] }}</td>
                <td class="right">
                    @if(empty($intake['courses']))
                        No courses with students
                    @elseif($it['registered'] === 0)
                        No registered students
                    @else
                        {{ $pct($it['rate']) }} · {{ $num($it['completed']) }} of {{ $num($it['registered']) }} completed
                    @endif
                </td>
            </tr>
        </table>
        @foreach($intake['courses'] as $row)
            @php
                $flags = array_filter([
                    ($row['registered'] === 0 ? 'No registered students, so no rate' : null),
                    ($row['running'] && $row['registered'] > 0 ? 'Still running · ends '.$day($row['end_date']) : null),
                    ($row['small'] ? 'Small cohort' : null),
                ]);
                $dots = array_merge(array_fill(0, $row['completed'], 'done'), array_fill(0, $row['not_completed'], 'not'));
                $lines = array_chunk($dots, 28);
            @endphp
            <table class="course">
                <tr>
                    <td class="course__info">
                        <div class="course__title">{{ $row['name'] }}</div>
                        @if(!empty($flags))
                            <div class="course__sub">{{ implode(' · ', $flags) }}</div>
                        @endif
                        <div class="course__rate{{ $row['low'] ? ' low' : '' }}{{ $row['rate'] === null ? ' none' : '' }}">{{ $pct($row['rate']) }}</div>
                        <div class="course__counts"><b>{{ $num($row['completed']) }}</b> completed · <b>{{ $num($row['not_completed']) }}</b> did not · {{ $num($row['registered']) }} registered</div>
                    </td>
                    <td class="course__dots">
                        @if(!empty($lines))
                            <table class="dots">
                                @foreach($lines as $line)
                                    <tr class="{{ $loop->last ? 'last' : '' }}">
                                        @foreach($line as $kind)<td><div class="{{ $kind }}"></div></td>@endforeach
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                </tr>
            </table>
        @endforeach
    @endforeach

    <div class="footnote">
        Award rate = Completed ÷ Registered students.
        @if($report['small_note']) {{ $report['small_note'] }} @endif
        @if($anyRunning) Still running = the course has not ended yet, so students still studying count as not completed. @endif
    </div>
    @endif
</body>
</html>
