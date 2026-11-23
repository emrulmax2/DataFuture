@php
    $fontDir = resource_path('fonts/audit-certificate');
    $t = $report['totals'];
    $pct = function ($rate) { return $rate === null ? '–' : number_format((float) $rate, 1).'%'; };
    $num = function ($n) { return number_format((int) $n); };
    $share = function ($n) use ($t) { return ($t['expected'] > 0 ? number_format($n / $t['expected'] * 100, 1).'%' : '0%'); };
    [$wPassed, $wNotPassed, $wNotSubmitted] = $report['waffle'];
    $squares = array_merge(array_fill(0, $wPassed, '#0e5a54'), array_fill(0, $wNotPassed, '#e0a030'), array_fill(0, $wNotSubmitted, '#c8601a'));
    $colours = ['passed' => '#0e5a54', 'notpassed' => '#e0a030', 'missing' => '#c8601a'];

    // Stacked bar segments in px: 357px less 2px between segments.
    $segments = function ($row) {
        $parts = array_values(array_filter([
            ['passed', $row['passed']], ['notpassed', $row['submitted_not_passed']], ['missing', $row['not_submitted']],
        ], function ($p) { return $p[1] > 0; }));
        $room = 357 - 2 * max(0, count($parts) - 1);
        $used = 0;
        foreach ($parts as $i => $part) {
            $px = ($i === count($parts) - 1 ? $room - $used : (int) round($part[1] / max(1, $row['expected']) * $room));
            $parts[$i][2] = $px;
            $used += $px;
        }
        return $parts;
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Submission and Pass Rate Report · {{ $report['intake'] }}</title>
<style>
    /*
     * Submission and Pass Rate Report, landscape A4 (1123 × 794 px at DomPDF's
     * 96 dpi, the design's own size), for one or more intakes: the summary, then the detail from a new page.
     * Columns are px on auto-layout tables (DomPDF splits `table-layout: fixed`
     * tables evenly) and widths are content boxes: padding adds on.
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

    /* --- page 1: the two rates, the waffle --- */
    .top { width: 1035px; }
    .top__left { padding-top: 30px; width: 480px; }
    .top__gap { width: 35px; }
    .top__right { width: 520px; }
    .rates { width: 480px; }
    .rates td { width: 221px; }
    .eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .hero { font-family: 'Source Serif 4', Georgia, serif; font-size: 60px; font-weight: 700; line-height: 0.82; margin-top: 10px; }
    .hero-sub { color: #55616b; font-size: 13px; margin-top: 10px; }
    .sentence { font-size: 15px; line-height: 1.05; margin-top: 16px; width: 470px; }
    .h2 { font-size: 15px; font-weight: 700; }
    .waffle-wrap { margin-top: 14px; }
    .waffle-wrap > tr > td, .waffle-wrap > tbody > tr > td { vertical-align: middle; }
    .waffle { border-collapse: separate; border-spacing: 3px; width: auto; }
    .waffle td { height: 16px; width: 16px; }
    .legend td { padding-bottom: 12px; vertical-align: top; }
    .legend__swatch { padding-top: 6px; width: 20px; }
    .legend__swatch span { display: block; height: 12px; width: 12px; }
    .legend__big { font-size: 22px; font-weight: 700; }
    .legend__big span { font-size: 15px; font-weight: 600; }
    .legend__sub { color: #55616b; font-size: 12px; margin-top: 3px; }

    /* --- page 1: by course --- */
    .sechead { margin-top: 12px; }
    .sechead td { vertical-align: bottom; }
    .sechead__meta { color: #55616b; font-size: 12px; text-align: right; }
    .courses { margin-top: 14px; width: 1035px; }
    .courses th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 0 0 8px; text-align: right; white-space: nowrap; }
    .courses td { border-bottom: 1px solid #e3e7ea; font-size: 14px; padding: 8px 0; text-align: right; vertical-align: middle; }
    .courses th.c-course { padding-left: 8px; text-align: left; width: 364px; }
    .courses td.c-course { font-weight: 600; padding-left: 20px; text-align: left; }
    .courses th:last-child, .courses td:last-child { padding-right: 8px; }
    .courses tr.intake td { background: #e6ecef; border-bottom-color: #c3c9d1; font-weight: 700; }
    .courses tr.intake td.c-course, .courses tr.all td.c-course { padding-left: 8px; }
    .courses tr.all td { border-bottom-color: #16242b; border-top: 1px solid #16242b; font-weight: 700; }
    .courses .c-bar { padding-left: 16px; text-align: left; width: 357px; }
    .courses .c-expected { width: 90px; }
    .courses .c-rate { width: 100px; }
    .courses .c-pass { font-size: 15px; font-weight: 700; }
    .courses .none { color: #55616b; font-weight: 400; }
    .course__title { font-weight: 700; }
    .course__sub { color: #55616b; font-size: 12px; margin-top: 3px; }
    .stack { height: 16px; width: 357px; }
    .stack td { height: 16px; padding: 0 !important; border-bottom: 0 !important; }
    .stack td + td { border-left: 2px solid #ffffff; }
    .stack-empty { background: #e9edf0; height: 16px; }
    .footnote { color: #55616b; font-size: 12px; margin-top: 14px; }

    /* --- page 2: detail --- */
    .page2 { page-break-before: always; }
    .crn-att-detail { margin-top: 14px; width: 1035px; }
    .crn-att-detail th { border-bottom: 1px solid #16242b; color: #55616b; font-size: 12px; font-weight: 600; padding: 0 0 6px 10px; text-align: right; }
    .crn-att-detail td { border-bottom: 1px solid #e3e7ea; font-size: 12px; line-height: 0.95; padding: 5px 0 5px 10px; text-align: right; }
    .crn-att-detail .d-name { padding-left: 8px; text-align: left; }
    .crn-att-detail .d-rate { padding-right: 8px; width: 56px; }
    .crn-att-detail tr.d-intake td { background: #d3dce2; border-bottom-color: #16242b; font-weight: 700; }
    .crn-att-detail tr.d-course td, .crn-att-detail tr.d-all td { background: #e6ecef; font-weight: 700; }
    .crn-att-detail tr.d-course td { border-bottom-color: #c3c9d1; }
    .crn-att-detail tr.d-course .d-name { padding-left: 22px; }
    .crn-att-detail tr.d-term .d-name { padding-left: 36px; }
    .crn-att-detail tr.d-even td { background: #f4f6f8; }
    .crn-att-detail tr.d-all td { border-bottom-color: #16242b; }
    .crn-att-detail .d-students { width: 56px; }
    .crn-att-detail .d-num { width: 64px; }
    .crn-att-detail .d-wide { width: 96px; }
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
                        <div class="runhead__title">Submission and Pass Rate Report</div>
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
                <td>London Churchill College · Submission and Pass Rate Report · {{ $report['intake'] }} · <span class="pagenum"></span></td>
                <td class="right">Confidential, internal use only</td>
            </tr>
        </table>
    </div>

    @if(!$report['has_results'])
        <div class="eyebrow">Submission and pass rate</div>
        <div class="hero" style="font-size: 40px;">Nothing due yet</div>
        <div class="sentence">{{ !$report['has_courses'] ? 'No courses with students were found for '.($report['many'] ? 'these intakes.' : 'this intake.') : 'No assignment results have been recorded for the registered students of '.($report['many'] ? 'these intakes.' : 'this intake.') }}</div>
    @else
    <table class="top">
        <tr>
            <td class="top__left">
                <table class="rates">
                    <tr>
                        <td>
                            <div class="eyebrow">Submission rate</div>
                            <div class="hero">{{ $pct($t['submission_rate']) }}</div>
                            <div class="hero-sub">{{ $num($t['submitted']) }} of {{ $num($t['expected']) }} submitted</div>
                        </td>
                        <td style="width: 38px;"></td>
                        <td>
                            <div class="eyebrow">Pass rate</div>
                            <div class="hero">{{ $pct($t['pass_rate']) }}</div>
                            <div class="hero-sub">{{ $num($t['passed']) }} of {{ $num($t['expected']) }} passed</div>
                        </td>
                    </tr>
                </table>
                <div class="sentence">{{ $num($t['expected']) }} assignments were expected from {{ $report['many'] ? 'these intakes' : 'this intake' }}. {{ $num($t['not_submitted']) }} {{ $t['not_submitted'] === 1 ? 'was' : 'were' }} never submitted.</div>
            </td>
            <td class="top__gap"></td>
            <td class="top__right">
                <div class="h2">Out of every 100 assignments expected</div>
                <table class="waffle-wrap">
                    <tr>
                        <td style="width: 210px;">
                            <table class="waffle">
                                @foreach(array_chunk($squares, 10) as $line)
                                    <tr>
                                        @foreach($line as $colour)
                                            <td style="background: {{ $colour }};"></td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                        <td>
                            <table class="legend">
                                @foreach([
                                    ['passed', $wPassed, 'passed', $t['passed']],
                                    ['notpassed', $wNotPassed, 'submitted, not passed', $t['submitted_not_passed']],
                                    ['missing', $wNotSubmitted, 'not submitted', $t['not_submitted']],
                                ] as [$kind, $n, $label, $count])
                                    <tr>
                                        <td class="legend__swatch"><span style="background: {{ $colours[$kind] }};"></span></td>
                                        <td>
                                            <div class="legend__big">{{ $n }} <span>{{ $label }}</span></div>
                                            <div class="legend__sub">{{ $num($count) }} assignments · {{ $share($count) }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sechead">
        <tr>
            <td class="h2">By intake and course</td>
            <td class="sechead__meta">Lowest pass rate first within each intake</td>
        </tr>
    </table>
    <table class="courses">
        <thead>
            <tr>
                <th class="c-course">Intake and course</th>
                <th class="c-bar">Passed · Submitted, not passed · Not submitted</th>
                <th class="c-expected">Expected</th>
                <th class="c-rate">Submitted</th>
                <th class="c-rate">Passed</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rows = [];
                foreach ($report['intakes'] as $intake) {
                    $rows[] = ['kind' => 'intake', 'label' => $intake['intake']] + $intake['totals'];
                    foreach ($intake['courses'] as $course) {
                        $rows[] = ['kind' => 'course', 'label' => $course['name']] + $course;
                    }
                }
                if ($report['many']) {
                    $rows[] = ['kind' => 'all', 'label' => 'All intakes'] + $t;
                }
            @endphp
            @foreach($rows as $row)
                <tr class="{{ $row['kind'] }}">
                    <td class="c-course">{{ $row['label'] }}</td>
                    <td class="c-bar">
                        @if($row['expected'] > 0)
                            <table class="stack">
                                <tr>
                                    @foreach($segments($row) as [$kind, $n, $px])
                                        <td style="width: {{ $px }}px; background: {{ $colours[$kind] }};"></td>
                                    @endforeach
                                </tr>
                            </table>
                        @else
                            <div class="stack-empty"></div>
                        @endif
                    </td>
                    <td class="c-expected">{{ $num($row['expected']) }}</td>
                    @if($row['expected'] > 0)
                        <td class="c-rate">{{ $pct($row['submission_rate']) }}</td>
                        <td class="c-rate c-pass">{{ $pct($row['pass_rate']) }}</td>
                    @else
                        <td class="c-rate none" colspan="2">Nothing due yet</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footnote">
        Both rates are out of assignments expected.
        @if($t['pass_of_submitted'] !== null)
            Of the {{ $num($t['submitted']) }} assignments actually submitted, {{ $pct($t['pass_of_submitted']) }} passed.
        @endif
        The term-by-term figures start on the next page.
    </div>

    <div class="page2">
        <table>
            <tr>
                <td class="h2">Detail: by intake, course and term</td>
                <td class="sechead__meta">Rates are out of assignments expected</td>
            </tr>
        </table>
        @include('pages.reports.intake-performance.submission.detail', ['report' => $report])
    </div>
    @endif
</body>
</html>
