@php
    /*
     * Submission Performance PDF (user's submission-performance-report design, 25 Nov 2026):
     * A4 landscape (1123 × 794 px at DomPDF's 96 dpi, 45px sides, so 1033px of content). Page 1:
     * the rates, first submission against final result, by course; page 2: the full figures.
     * Data: SubmissionPerformance. Bars are floated divs with px widths (no flex in DomPDF); the
     * hatched "not accounted for" is a PNG tile ($hatch).
     */
    use App\Services\Reports\SubmissionPerformance as SP;

    $fontDir = resource_path('fonts/audit-certificate');
    $T = $totals;
    $F = $T['first'];
    $A = $T['final'];
    $n = fn ($v) => number_format((int) $v);
    $pc = fn ($a, $b, $dp = 1) => $b > 0 ? number_format($a * 100 / $b, $dp).'%' : '–';
    $pts = function ($now, $then) {
        $d = $now - $then;

        return ($d >= 0 ? '+' : '−').number_format(abs($d), 1).' pts';
    };
    $CATS = [
        'D' => ['Distinction', '#0b3f3b', '#ffffff'], 'M' => ['Merit', '#2f8f84', '#ffffff'], 'P' => ['Pass', '#9fd0c8', '#16242b'],
        'R' => ['Referred', '#e0a030', '#16242b'], 'C' => ['Plagiarised', '#b3261e', '#ffffff'], 'A' => ['Not submitted', '#c3c9d1', '#16242b'],
    ];
    $gap = fn ($f) => max(0, $f['expected'] - ($f['D'] + $f['M'] + $f['P'] + $f['R'] + $f['C'] + $f['A']));
    $hasGap = $gap($F) > 0 || $gap($A) > 0;
    $gapFill = $hatch ? "background: #ffffff url('".$hatch."') repeat;" : 'background: #eef1f3;';

    /** A bar of every expected assignment by outcome; numbers inside parts over 4.5% that fit. */
    $bar = function (array $f, int $width, int $height, bool $labels) use ($CATS, $gap, $n, $gapFill) {
        $parts = [];
        foreach ($CATS as $key => [$label, $bg, $fg]) {
            if ($f[$key] > 0) {
                $parts[] = ['v' => $f[$key], 'style' => 'background:'.$bg.';color:'.$fg.';'];
            }
        }
        if ($gap($f) > 0) {
            $parts[] = ['v' => $gap($f), 'style' => $gapFill];
        }
        $total = $f['expected'];
        if ($total <= 0 || empty($parts)) {
            return '<div class="bar" style="width:'.$width.'px;height:'.$height.'px;background:#e9edf0;border-radius:4px;"></div>';
        }
        $avail = $width - 2 * (count($parts) - 1);
        $widths = array_map(fn ($p) => max(2, (int) floor($p['v'] / $total * $avail)), $parts);
        $widest = array_keys($widths, max($widths))[0];
        $widths[$widest] += $avail - array_sum($widths);
        $radius = $height >= 18 ? 4 : $height / 2;
        $html = '<div class="bar" style="width:'.$width.'px;height:'.$height.'px;">';
        foreach ($parts as $i => $p) {
            $last = $i === count($parts) - 1;
            $text = ($labels && $p['v'] / $total > 0.045 && mb_strlen($n($p['v'])) * 6.4 + 10 <= $widths[$i]) ? $n($p['v']) : '';
            $pad = $labels ? (int) floor(($height - 16) / 2) : 0;
            $html .= '<div class="bar__part" style="width:'.$widths[$i].'px;height:'.($height - $pad).'px;padding-top:'.$pad.'px;'.$p['style']
                .($last ? '' : 'margin-right:2px;')
                .($i === 0 ? 'border-top-left-radius:'.$radius.'px;border-bottom-left-radius:'.$radius.'px;' : '')
                .($last ? 'border-top-right-radius:'.$radius.'px;border-bottom-right-radius:'.$radius.'px;' : '').'">'.$text.'</div>';
        }

        return $html.'</div>';
    };

    // The full-figures table: "All courses", then each course.
    $cols = array_merge([['name' => 'All courses', 'students' => $T['students'], 'first' => $F, 'final' => $A, 'main' => true]], $courses);
    $colW = count($cols) ? max(34, (int) floor((1033 - 150 - count($cols) * 2 * 17) / (count($cols) * 2))) : 60; // 16px padding + 1px border a cell
    $otherText = function (array $other) use ($grades, $n) {
        arsort($other);
        return implode(', ', array_map(fn ($g, $c) => $n($c).' '.(($grades[$g] ?? '') !== '' ? $grades[$g] : 'with no grade name'), array_keys($other), $other));
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Submission Performance Report · {{ $term['name'] }} term</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 100px 45px 60px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 12px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* Running header and footer (as the other Term Performance PDFs). */
    .runhead { position: fixed; top: -76px; left: 0; right: 0; border-bottom: 2px solid #16242b; }
    .runhead td { padding-bottom: 9px; vertical-align: bottom; }
    .runhead td.runhead__mid { vertical-align: middle; }
    .runhead__logo { display: block; height: 40px; width: auto; }
    .runhead__eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .runhead__title { font-size: 17px; font-weight: 700; }
    .runhead__divider { border-left: 1px solid #c3c9d1; height: 30px; margin: 0 14px; }
    .runhead__meta { text-align: right; }
    .runhead__term { color: #0e5a54; font-size: 13px; font-weight: 700; }
    .runhead__by { color: #55616b; font-size: 12px; margin-top: 4px; }
    .runfoot { position: fixed; bottom: -36px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 11px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    h1 { font-size: 15px; font-weight: 700; margin: 0; }
    h2 { font-size: 14px; font-weight: 700; margin: 0; }
    .lede { color: #55616b; font-size: 11px; margin-top: 3px; }
    .hint { color: #55616b; font-size: 10.5px; }
    .section { margin-top: 14px; }

    /* Rates. */
    .kpi { border: 1px solid #dfe3e7; border-radius: 8px; padding: 7px 14px 6px; }
    .kpi__k { color: #55616b; font-size: 12px; font-weight: 600; }
    .kpi__v { font-size: 32px; font-weight: 700; height: 36px; letter-spacing: -0.02em; }
    .pill { background: #e2f1ee; border-radius: 12px; color: #0e5a54; font-size: 11.5px; font-weight: 600; padding: 4px 9px 3px; }
    .kpi__s { color: #55616b; font-size: 11px; margin-top: 2px; }

    /* Bars. */
    .bar { overflow: hidden; }
    .bar__part { float: left; font-size: 11px; font-weight: 600; text-align: center; white-space: nowrap; overflow: hidden; }
    .pair td { padding-bottom: 8px; vertical-align: middle; }
    .pair__lab { font-size: 12px; font-weight: 700; width: 130px; }
    .pair__lab small { color: #55616b; display: block; font-size: 10.5px; font-weight: 400; margin-top: 2px; }
    .key { color: #55616b; font-size: 11px; }
    .key span { display: inline-block; margin-right: 14px; white-space: nowrap; }
    .sw { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 5px; }
    .change th { border-bottom: 2px solid #16242b; color: #55616b; font-size: 10.5px; font-weight: 600; padding: 0 8px 4px; text-align: right; }
    .change th:first-child, .change td:first-child { text-align: left; }
    .change td { border-bottom: 1px solid #e3e7ea; font-size: 11px; padding: 2px 8px 1px; text-align: right; }
    .change td:first-child { font-weight: 600; }
    .change td.final { font-weight: 700; }
    .up { color: #0e5a54; font-weight: 600; }
    .down { color: #8a3d0c; font-weight: 600; }
    .same { color: #55616b; }

    /* By course. */
    .courses { border-top: 2px solid #16242b; margin-top: 6px; }
    .courses > tbody > tr > td { border-bottom: 1px solid #e3e7ea; padding: 6px 6px; vertical-align: middle; }
    .c-name { font-size: 12px; font-weight: 700; width: 280px; }
    .c-meta { color: #55616b; font-size: 11px; font-weight: 400; margin-top: 3px; }
    .c-bars td { color: #55616b; font-size: 10.5px; padding: 2px 0; vertical-align: middle; }
    .trio td { width: 33%; }
    .trio .k { color: #55616b; font-size: 10.5px; }
    .trio .v { font-size: 14px; font-weight: 700; margin-top: 1px; }
    .trio .w { color: #55616b; font-size: 10.5px; margin-top: 1px; }

    /* Full figures. */
    .page2 { page-break-before: always; }
    .mx { margin-top: 8px; }
    .mx th, .mx td { border-bottom: 1px solid #e3e7ea; font-size: 10.5px; padding: 2px 8px 1px; text-align: right; }
    .mx thead th { border-bottom: 0; color: #55616b; font-weight: 500; vertical-align: bottom; }
    .mx thead th.cg { color: #16242b; font-size: 10.5px; font-weight: 700; text-align: center; }
    .mx thead tr.sub th { border-bottom: 2px solid #16242b; font-size: 10.5px; }
    .mx td.lab { font-size: 11.5px; font-weight: 600; text-align: left; width: 134px; }
    .mx td.f { color: #55616b; }
    .mx td.l { font-weight: 700; }
    .mx td.fl, .mx th.fl { border-left: 1px solid #dfe3e7; }
    .mx td.span { text-align: center; }
    .mx td small { color: #55616b; display: block; font-size: 9px; font-weight: 400; }
    .mx .mainc { background: #f4f6f8; }
    .mx tr.band td { background: #e6ecef; border-bottom: 1px solid #c3c9d1; font-size: 9.5px; font-weight: 700; letter-spacing: 0.06em; padding: 2px 8px 1px; text-align: left; text-transform: uppercase; }
    .mx tr.rate td { border-bottom: 1px solid #c3c9d1; }
    .mx td.up { color: #0e5a54; }
    .mx td.down { color: #8a3d0c; }
    .mx tr { page-break-inside: avoid; }
    .notes { color: #55616b; font-size: 10.5px; margin: 10px 0 0; padding-left: 16px; }
    .notes li { margin-bottom: 4px; }
</style>
</head>
<body>
    <div class="runhead">
        <table>
            <tr>
                <td class="runhead__mid" style="width: 1%; white-space: nowrap;">
                    @if(!empty($logo))
                        <img class="runhead__logo" src="{{ $logo }}" alt="London Churchill College">
                    @else
                        <div class="runhead__eyebrow">London Churchill College</div>
                    @endif
                </td>
                @if(!empty($logo))
                    <td class="runhead__mid" style="width: 1%;"><div class="runhead__divider"></div></td>
                @endif
                <td class="runhead__mid"><div class="runhead__title">Submission Performance Report</div></td>
                <td class="runhead__meta">
                    <div class="runhead__term">{{ $term['name'] }} term</div>
                    <div class="runhead__by">Generated {{ $generated }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="runfoot">
        <table>
            <tr>
                <td>London Churchill College · Submission Performance Report · Confidential, internal use only</td>
                <td class="right"><span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    <h1>{{ $term['name'] }} term</h1>
    @if(empty($courses) || $F['expected'] === 0)
        <div class="lede" style="font-size: 13px; margin-top: 10px;">No results are recorded for this term yet.</div>
    @else
        <div class="lede">{{ $n($T['students']) }} students · {{ $n($A['expected']) }} assignments expected</div>

        {{-- Rates --}}
        <table class="section">
            <tr>
                @foreach([
                    ['Submission rate', $A['sub'], $A['expected'], $F['sub'], $F['expected'], $n($A['sub']).' of '.$n($A['expected']).' expected assignments handed in'],
                    ['Pass rate', SP::passed($A), $A['sub'], SP::passed($F), $F['sub'], $n(SP::passed($A)).' of '.$n($A['sub']).' submitted assignments passed'],
                ] as $i => [$label, $now, $of, $then, $thenOf, $line])
                    @if($i === 1)<td style="width: 12px;"></td>@endif
                    <td style="width: 50%;"><div class="kpi">
                        <div class="kpi__k">{{ $label }}</div>
                        @php $r = $of > 0 ? $now * 100 / $of : 0; $rt = $thenOf > 0 ? $then * 100 / $thenOf : 0; @endphp
                        <table><tr>
                            <td style="width: 1%; white-space: nowrap; vertical-align: middle;"><div class="kpi__v">{{ number_format($r, 1) }}%</div></td>
                            <td style="vertical-align: middle; padding-left: 12px;"><span class="pill">First submission {{ number_format($rt, 1) }}% · {{ $pts($r, $rt) }}</span></td>
                        </tr></table>
                        <div class="kpi__s">{{ $line }}</div>
                    </div></td>
                @endforeach
            </tr>
        </table>

        {{-- First submission to final result --}}
        <div class="section">
            <table>
                <tr>
                    <td><h2>From first submission to final result</h2></td>
                    <td class="hint" style="text-align: right; vertical-align: bottom;">Each bar is all {{ $n($A['expected']) }} expected assignments</td>
                </tr>
            </table>
            <table style="margin-top: 8px;">
                <tr>
                    <td style="width: 640px; padding-right: 22px;">
                        <table class="pair">
                            <tr><td class="pair__lab">First submission<small>Before any resubmission</small></td><td>{!! $bar($F, 498, 22, true) !!}</td></tr>
                            <tr><td class="pair__lab">Final result<small>After resubmission</small></td><td>{!! $bar($A, 498, 22, true) !!}</td></tr>
                        </table>
                        <div class="key">
                            @foreach($CATS as [$label, $bg])
                                <span><i class="sw" style="background: {{ $bg }};"></i>{{ $label }}</span>
                            @endforeach
                            @if($hasGap)
                                <span><i class="sw" style="{{ $gapFill }} border: 1px solid #9aa3ab;"></i>Not accounted for</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <table class="change">
                            <thead><tr><th>Outcome</th><th>First</th><th>Final</th><th>Change</th></tr></thead>
                            <tbody>
                                @foreach($CATS as $key => [$label, $bg])
                                    @php $d = $A[$key] - $F[$key]; $good = in_array($key, ['R', 'C', 'A'], true) ? $d < 0 : $d > 0; @endphp
                                    <tr>
                                        <td><i class="sw" style="background: {{ $bg }};"></i>{{ $label }}</td>
                                        <td>{{ $n($F[$key]) }}</td>
                                        <td class="final">{{ $n($A[$key]) }}</td>
                                        <td class="{{ $d === 0 ? 'same' : ($good ? 'up' : 'down') }}">{{ $d === 0 ? 'No change' : ($d > 0 ? '+' : '−').$n(abs($d)) }}</td>
                                    </tr>
                                @endforeach
                                @if($hasGap)
                                    @php $d = $gap($A) - $gap($F); @endphp
                                    <tr>
                                        <td><i class="sw" style="{{ $gapFill }} border: 1px solid #9aa3ab;"></i>Not accounted for</td>
                                        <td>{{ $n($gap($F)) }}</td>
                                        <td class="final">{{ $n($gap($A)) }}</td>
                                        <td class="{{ $d === 0 ? 'same' : ($d < 0 ? 'up' : 'down') }}">{{ $d === 0 ? 'No change' : ($d > 0 ? '+' : '−').$n(abs($d)) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- By course --}}
        <div class="section">
            <table>
                <tr>
                    <td><h2>By course</h2></td>
                    <td class="hint" style="text-align: right; vertical-align: bottom;">Top bar = first submission · Bottom bar = final result</td>
                </tr>
            </table>
            <table class="courses">
                <tbody>
                    @foreach($courses as $c)
                        @php $cf = $c['first']; $ca = $c['final']; $md = $ca['M'] + $ca['D']; @endphp
                        <tr>
                            <td class="c-name">{{ $c['name'] }}<div class="c-meta">{{ $n($c['students']) }} students · {{ $n($ca['expected']) }} expected{{ $ca['expected'] < 30 ? ' · small cohort' : '' }}</div></td>
                            <td style="width: 400px;">
                                <table class="c-bars">
                                    <tr><td style="width: 38px;">First</td><td>{!! $bar($cf, 350, 9, false) !!}</td></tr>
                                    <tr><td>Final</td><td>{!! $bar($ca, 350, 9, false) !!}</td></tr>
                                </table>
                            </td>
                            <td style="width: 300px;">
                                <table class="trio">
                                    <tr>
                                        <td><div class="k">Submitted</div><div class="v">{{ $pc($ca['sub'], $ca['expected']) }}</div><div class="w">first {{ $pc($cf['sub'], $cf['expected']) }}</div></td>
                                        <td><div class="k">Passed</div><div class="v">{{ $pc(SP::passed($ca), $ca['sub']) }}</div><div class="w">first {{ $pc(SP::passed($cf), $cf['sub']) }}</div></td>
                                        <td><div class="k">Higher grades</div><div class="v">{{ $pc($md, $ca['sub']) }}</div><div class="w">{{ $n($md) }} Merit or Distinction</div></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Full figures --}}
        <div class="page2">
            <h2 style="font-size: 13px;">Full figures: first submission and final result</h2>
            <table class="mx">
                <thead>
                    <tr>
                        <th></th>
                        @foreach($cols as $col)
                            <th colspan="2" class="cg fl{{ !empty($col['main']) ? ' mainc' : '' }}">{{ $col['name'] }}</th>
                        @endforeach
                    </tr>
                    <tr class="sub">
                        <th></th>
                        @foreach($cols as $col)
                            <th class="fl{{ !empty($col['main']) ? ' mainc' : '' }}" style="width: {{ $colW }}px;">First</th>
                            <th class="{{ !empty($col['main']) ? 'mainc' : '' }}" style="width: {{ $colW }}px;">Final</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $band = fn ($t) => '<tr class="band"><td colspan="'.(1 + count($cols) * 2).'">'.$t.'</td></tr>';
                        // A row: $get(figures) => [value, text, small]; $better: +1 higher is good, -1 lower is good, 0 neutral.
                        $row = function ($label, $get, $better = 0, $cls = '') use ($cols) {
                            $html = '<tr class="'.$cls.'"><td class="lab">'.$label.'</td>';
                            foreach ($cols as $col) {
                                [$fv, $ft, $fs] = $get($col['first'], $col);
                                [$av, $at, $as] = $get($col['final'], $col);
                                $dir = '';
                                if ($better && $av != $fv) {
                                    $dir = ($av - $fv) * $better > 0 ? ' up' : ' down';
                                }
                                $main = !empty($col['main']) ? ' mainc' : '';
                                $html .= '<td class="f fl'.$main.'">'.$ft.($fs !== null ? '<small>'.$fs.'</small>' : '').'</td>'
                                    .'<td class="l'.$dir.$main.'">'.$at.($as !== null ? '<small>'.$as.'</small>' : '').'</td>';
                            }

                            return $html.'</tr>';
                        };
                        $both = function ($label, $get) use ($cols) {
                            $html = '<tr><td class="lab">'.$label.'</td>';
                            foreach ($cols as $col) {
                                $html .= '<td colspan="2" class="span fl'.(!empty($col['main']) ? ' mainc' : '').'">'.$get($col).'</td>';
                            }

                            return $html.'</tr>';
                        };
                        $count = fn ($k) => fn ($f) => [$f[$k], $n($f[$k]), $pc($f[$k], $f['sub'])];
                        $sw = fn ($k) => '<i class="sw" style="background:'.$CATS[$k][1].';"></i>';
                        $sameExpected = collect($cols)->every(fn ($c) => $c['first']['expected'] === $c['final']['expected']);
                    @endphp
                    {!! $band('Assignments') !!}
                    {!! $both('Students', fn ($c) => $n($c['students'])) !!}
                    {!! $sameExpected ? $both('Expected', fn ($c) => $n($c['final']['expected'])) : $row('Expected', fn ($f) => [$f['expected'], $n($f['expected']), null]) !!}
                    {!! $row('Submitted', fn ($f) => [$f['sub'], $n($f['sub']), null], 1) !!}
                    {!! $row('Submission rate', fn ($f) => [$f['expected'] ? $f['sub'] / $f['expected'] : 0, $pc($f['sub'], $f['expected'], 2), null], 1, 'rate') !!}
                    {!! $band('Passed') !!}
                    {!! $row($sw('D').'Distinction', $count('D'), 1) !!}
                    {!! $row($sw('M').'Merit', $count('M'), 1) !!}
                    {!! $row($sw('P').'Pass', $count('P'), 1) !!}
                    {!! $row('Total passed', fn ($f) => [SP::passed($f), $n(SP::passed($f)), null], 1) !!}
                    {!! $row('Pass rate', fn ($f) => [$f['sub'] ? SP::passed($f) / $f['sub'] : 0, $pc(SP::passed($f), $f['sub'], 2), null], 1, 'rate') !!}
                    {!! $band('Not passed') !!}
                    {!! $row($sw('R').'Referred', $count('R'), -1) !!}
                    {!! $row($sw('C').'Plagiarised', $count('C'), -1) !!}
                    {!! $row($sw('A').'Not submitted', fn ($f) => [$f['A'], $n($f['A']), $pc($f['A'], $f['expected'])], -1) !!}
                    @if($hasGap)
                        {!! $row('Not accounted for', fn ($f) => [$gap($f), $n($gap($f)), null], -1) !!}
                    @endif
                </tbody>
            </table>
            <div class="hint" style="margin-top: 8px;">Small figures are the share of submitted work (for Not submitted, the share of expected). Final figures in green improved on the first submission; orange got worse.</div>
            <ul class="notes">
                <li>Submission rate = submitted ÷ expected. Pass rate = Pass, Merit and Distinction ÷ submitted.</li>
                @if($A['M'] === $F['M'] && $A['D'] === $F['D'] && $A['P'] > $F['P'])
                    <li>Merit and Distinction counts are the same at first submission and after resubmission; every resubmitted assignment that passed did so at Pass grade.</li>
                @endif
                @if($gap($A) > 0)
                    <li>{{ $n($gap($A)) }} expected assignments in the final figures have a grade outside these outcomes: {{ $otherText($A['other']) }}.</li>
                @endif
                @if($gap($F) > 0)
                    <li>At first submission, {{ $n($gap($F)) }} had a grade outside these outcomes: {{ $otherText($F['other']) }}.</li>
                @endif
            </ul>
        </div>
    @endif
</body>
</html>
