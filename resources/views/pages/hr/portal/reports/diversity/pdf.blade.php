@php
    /*
     * Diversity Information PDF, new version (user's diversity-information-report.pdf design, 26 Nov 2026):
     * A4 portrait at DomPDF's 96 dpi, 45px sides (704px of content). Page 1: the dots, the three cards and
     * gender within each ethnic group; the staff list starts on page 2. Data: DiversityReport::rows and
     * ::dimension (as on screen). DomPDF: no flex or box-shadow, so bars are tables and divs.
     */
    use App\Services\HR\DiversityReport as R;

    $fontDir = resource_path('fonts/audit-certificate');
    $C = ['#0e5a54', '#c8601a', '#3d6fb4', '#8a5aa8', '#b8922a', '#8b949c'];
    $N = count($list);
    $pc = fn ($a, $b) => $b ? number_format(100 * $a / $b, 1).'%' : '–';
    $tally = function ($rows, $f) { $t = []; foreach ($rows as $p) { $k = $f($p); $t[$k] = ($t[$k] ?? 0) + 1; } return $t; };
    $colours = function ($dim, $keys) use ($C) {
        $col = []; $j = 0;
        foreach ($keys as $k) {
            if (preg_match('/^Not |^Other nationalities$/', $k)) $col[$k] = $C[5];
            elseif ($dim === 'ethnic') $col[$k] = $C[array_search($k, R::GROUPS, true) === false ? 4 : array_search($k, R::GROUPS, true)];
            else $col[$k] = $C[min($j++, 4)];
        }
        return $col;
    };

    $D = R::dimension($f['dim'], $list);
    $D['col'] = $colours($f['dim'], $D['keys']);
    $pick = ($f['pick'] !== '' && isset($D['counts'][$f['pick']])) ? $f['pick'] : null;
    $dots = $list;
    usort($dots, fn ($a, $b) => array_search(($D['of'])($a), $D['keys'], true) <=> array_search(($D['of'])($b), $D['keys'], true));
    $cols = $N > 200 ? 25 : 17;
    $dotRows = array_chunk($dots, $cols);

    $G = R::dimension('gender', $list);
    $G['col'] = $colours('gender', $G['keys']);
    $yes = count(array_filter($list, fn ($p) => $p['disability'] === 'Yes'));
    $tgr = $tally($list, fn ($p) => $p['ethGroup']);
    $sub = [];
    foreach (R::GROUPS as $g) {
        $td = $tally(array_filter($list, fn ($p) => $p['ethGroup'] === $g), fn ($p) => $p['ethDetail']);
        uksort($td, fn ($a, $b) => [$td[$b], $a] <=> [$td[$a], $b]);
        if (count($td) > 1 || (count($td) === 1 && array_key_first($td) !== $g && $g !== 'Not known')) $sub[$g] = $td;
    }
    $tn = $tally($list, fn ($p) => $p['nationality'] !== '' ? $p['nationality'] : 'Not recorded');
    uksort($tn, fn ($a, $b) => [$tn[$b], $a] <=> [$tn[$a], $b]);
    $natTop = array_filter($tn, fn ($c, $k) => $c > 1 && $k !== 'Not recorded', ARRAY_FILTER_USE_BOTH);
    $natOne = array_keys(array_filter($tn, fn ($c, $k) => $c === 1 && $k !== 'Not recorded', ARRAY_FILTER_USE_BOTH));
    $shown = $pick ? array_values(array_filter($list, fn ($p) => ($D['of'])($p) === $pick)) : $list;
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Diversity Information</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 50px 45px 66px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 11px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td, th { padding: 0; text-align: left; vertical-align: top; }
    .muted { color: #55616b; }
    h2 { margin: 0; font-size: 13px; font-weight: 700; }

    .runfoot { position: fixed; bottom: -42px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 9px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .head { width: 704px; border-bottom: 2px solid #16242b; }
    .head td { padding-bottom: 8px; vertical-align: bottom; }
    .head__title { font-size: 19px; font-weight: 700; }
    .head__sub { font-size: 11px; color: #55616b; padding-top: 2px; }
    .head__meta { text-align: right; font-size: 10.5px; color: #55616b; line-height: 1.45; }

    .count { margin-top: 14px; }
    .count b { font-size: 26px; font-weight: 700; }
    .glance { width: 704px; margin-top: 10px; }
    .dots td { padding: 0 3px 3px 0; }
    .dot { width: 14px; height: 14px; border-radius: 7px; }
    .dots.many .dot { width: 10px; height: 10px; border-radius: 5px; }
    .dim .dot { opacity: 0.18; }
    .lg td { padding: 4px 0; vertical-align: middle; font-size: 11px; }
    .sw { width: 11px; height: 11px; border-radius: 6px; }
    .r { text-align: right; }

    .cards { width: 704px; margin-top: 18px; }
    .cards td.slot { width: 220px; padding-right: 12px; }
    .cards td.slot.last { padding-right: 0; }
    .card { border: 1px solid #d9dee3; border-radius: 8px; padding: 10px; }
    .split { width: 100%; height: 20px; }
    .split td { height: 20px; color: #ffffff; font-size: 10px; font-weight: 700; text-align: center; vertical-align: middle; }
    .kv { width: 100%; margin-top: 6px; }
    .kv td { padding: 2px 0; font-size: 10.5px; }
    .big2 { font-size: 20px; font-weight: 700; }
    .bar { margin-top: 6px; }
    .bar table { width: 100%; }
    .bar td { font-size: 10.5px; }
    .meter { height: 6px; background: #e9edf0; border-radius: 3px; margin-top: 2px; }
    .meter div { height: 6px; background: #0e5a54; border-radius: 3px; }
    .subl { margin: 4px 0 0 0; padding-left: 8px; border-left: 2px solid #e6ecef; }
    .subl td { font-size: 10px; color: #55616b; padding: 1px 0; }
    .subl td.n { color: #16242b; font-weight: 700; text-align: right; }
    .hint { font-size: 10px; color: #55616b; margin-top: 6px; line-height: 1.35; }

    .cross { width: 704px; margin-top: 8px; }
    .cross td { padding: 3px 0; vertical-align: middle; font-size: 10.5px; }
    .legend i { display: inline-block; width: 9px; height: 9px; border-radius: 2px; }

    .list { width: 704px; margin-top: 8px; font-size: 10px; }
    .list th { padding: 4px 6px; font-size: 9.5px; font-weight: 600; color: #55616b; border-bottom: 1px solid #16242b; }
    .list td { padding: 4px 6px; vertical-align: middle; border-top: 1px solid #eceff2; }
    .list tr.alt { background: #f4f6f8; }
    .list .nm { font-weight: 700; }
    .miss { color: #8a3d0c; font-weight: 700; }
    .yes { padding: 1px 6px; border-radius: 8px; background: #0e5a54; color: #ffffff; font-size: 9px; font-weight: 700; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Diversity Information</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Diversity Information</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ $scope }} · {{ $N }} staff<br>Printed {{ $printed }}</td>
    </tr></table>

    @if(!$N)
        <p class="muted" style="margin-top: 20px;">No staff match these filters.</p>
    @else
    <div class="count"><b>{{ $N }}</b> <span class="muted">staff · each dot is one person</span></div>
    <table class="glance"><tr>
        <td style="width: 380px;">
            <table class="dots{{ $cols === 25 ? ' many' : '' }}">
                @foreach($dotRows as $row)
                    <tr>
                        @foreach($row as $p)
                            @php $k = ($D['of'])($p); @endphp
                            <td class="{{ $pick && $pick !== $k ? 'dim' : '' }}"><div class="dot" style="background: {{ $D['col'][$k] }};"></div></td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </td>
        <td style="padding-left: 24px;">
            <div class="muted" style="margin-bottom: 4px;">{{ $D['label'] }}</div>
            <table class="lg" style="width: 100%;">
                @foreach($D['keys'] as $k)
                    <tr>
                        <td style="width: 18px;"><div class="sw" style="background: {{ $D['col'][$k] }};"></div></td>
                        <td>{{ $k }}@if($pick === $k) <span class="muted">· listed</span>@endif</td>
                        <td class="r" style="width: 34px;"><b>{{ $D['counts'][$k] }}</b></td>
                        <td class="r muted" style="width: 50px;">{{ $pc($D['counts'][$k], $N) }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr></table>

    <table class="cards"><tr>
        <td class="slot"><div class="card">
            <h2>Gender</h2>
            <table class="split" style="margin-top: 8px;"><tr>
                @foreach($G['keys'] as $k)
                    <td style="width: {{ 100 * $G['counts'][$k] / $N }}%; background: {{ $G['col'][$k] }};">{{ $G['counts'][$k] / $N > 0.08 ? $G['counts'][$k] : '' }}</td>
                @endforeach
            </tr></table>
            <table class="kv">
                @foreach($G['keys'] as $k)
                    <tr><td>{{ $k }} <span class="muted">{{ $pc($G['counts'][$k], $N) }}</span></td><td class="r"><b>{{ $G['counts'][$k] }}</b></td></tr>
                @endforeach
            </table>
            <h2 style="margin-top: 12px;">Disability</h2>
            <div style="margin-top: 4px;"><span class="big2">{{ $yes }}</span> <span class="muted">marked Yes · {{ $pc($yes, $N) }}</span></div>
            <div class="hint">From each person's disability status, the old report's "Status" column.</div>
        </div></td>
        <td class="slot"><div class="card">
            <h2>Ethnic group</h2>
            @foreach(R::GROUPS as $g)
                @continue(empty($tgr[$g]))
                <div class="bar">
                    <table><tr><td>{{ $g }} <span class="muted">{{ $pc($tgr[$g], $N) }}</span></td><td class="r"><b>{{ $tgr[$g] }}</b></td></tr></table>
                    <div class="meter"><div style="width: {{ 100 * $tgr[$g] / $N }}%;"></div></div>
                    @if(isset($sub[$g]))
                        <table class="subl" style="width: 96%;">
                            @foreach($sub[$g] as $k => $c)
                                <tr><td>{{ $k }}</td><td class="n">{{ $c }}</td></tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            @endforeach
        </div></td>
        <td class="slot last"><div class="card">
            <h2>Nationality</h2>
            @foreach($natTop as $k => $c)
                <div class="bar">
                    <table><tr><td>{{ $k }} <span class="muted">{{ $pc($c, $N) }}</span></td><td class="r"><b>{{ $c }}</b></td></tr></table>
                    <div class="meter"><div style="width: {{ 100 * $c / $N }}%;"></div></div>
                </div>
            @endforeach
            @if(count($natOne))
                <div class="hint"><b style="color: #16242b;">One each ({{ count($natOne) }}):</b> {{ implode(', ', $natOne) }}</div>
            @endif
            @if(!empty($tn['Not recorded']))
                <div class="hint">{{ $tn['Not recorded'] }} not recorded</div>
            @endif
        </div></td>
    </tr></table>

    <table style="width: 704px; margin-top: 18px;"><tr>
        <td><h2>Gender within each ethnic group</h2></td>
        <td class="r legend muted" style="font-size: 10px;"><i style="background: {{ $C[0] }};"></i> Male &nbsp; <i style="background: {{ $C[1] }};"></i> Female &nbsp; <i style="background: {{ $C[5] }};"></i> Other or not recorded</td>
    </tr></table>
    <table class="cross">
        @foreach(R::GROUPS as $g)
            @continue(empty($tgr[$g]))
            @php
                $l = array_filter($list, fn ($p) => $p['ethGroup'] === $g);
                $m = count(array_filter($l, fn ($p) => $p['gender'] === 'Male'));
                $fe = count(array_filter($l, fn ($p) => $p['gender'] === 'Female'));
                $o = count($l) - $m - $fe;
                $w = max(4, round(330 * count($l) / $N));
            @endphp
            <tr>
                <td style="width: 130px;">{{ $g }} <span class="muted">{{ count($l) }}</span></td>
                <td style="width: 340px;">
                    <table style="width: {{ $w }}px; height: 12px;"><tr>
                        @if($m)<td style="width: {{ 100 * $m / count($l) }}%; height: 12px; background: {{ $C[0] }};"></td>@endif
                        @if($fe)<td style="width: {{ 100 * $fe / count($l) }}%; height: 12px; background: {{ $C[1] }};"></td>@endif
                        @if($o)<td style="width: {{ 100 * $o / count($l) }}%; height: 12px; background: {{ $C[5] }};"></td>@endif
                    </tr></table>
                </td>
                <td class="r muted">{{ $m }} male · {{ $fe }} female{{ $o ? ' · '.$o.' other' : '' }}</td>
            </tr>
        @endforeach
    </table>

    <div style="page-break-before: always;"></div>
    <h2 style="font-size: 14px;">Staff list</h2>
    <div class="muted" style="margin-top: 2px;">{{ count($shown) }} of {{ $N }} staff{{ $pick ? ' · '.$D['label'].': '.$pick : '' }}</div>
    <table class="list">
        <thead><tr>
            <th style="width: 150px;">Staff member</th><th style="width: 48px;">Works no.</th><th style="width: 52px;">Gender</th>
            <th style="width: 210px;">Ethnicity</th><th style="width: 110px;">Nationality</th><th>Disability</th>
        </tr></thead>
        <tbody>
            @foreach($shown as $i => $p)
                <tr class="{{ $i % 2 ? '' : 'alt' }}">
                    <td class="nm">{{ $p['name'] }}</td>
                    <td>@if($p['works'] !== ''){{ $p['works'] }}@else<span class="miss">Missing</span>@endif</td>
                    <td>@if($p['gender'] !== ''){{ $p['gender'] }}@else<span class="miss">Missing</span>@endif</td>
                    <td>@if($p['ethnicity'] !== ''){{ $p['ethnicity'] }}@else<span class="miss">Missing</span>@endif</td>
                    <td>@if($p['nationality'] !== ''){{ $p['nationality'] }}@else<span class="miss">Missing</span>@endif</td>
                    <td>@if($p['disability'] === 'Yes')<span class="yes">Yes</span>@elseif($p['disability'] === 'No'){{ 'No' }}@else<span class="miss">Missing</span>@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
