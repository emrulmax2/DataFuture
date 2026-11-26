@php
    /*
     * Employee Service Lengths PDF, new version (user's employee-service-lengths.pdf design, 26 Nov 2026):
     * A4 portrait at DomPDF's 96 dpi, 45px sides (704px of content). Page 1: the figures, staff by start
     * year and length of service; the service bars start on page 2. Data: ServiceLengthsReport::build.
     * DomPDF: no gradients, so ended service is a plain grey bar; bars are tables and divs.
     */
    use App\Services\HR\ServiceLengthsReport as R;

    $fontDir = resource_path('fonts/audit-certificate');
    $B = ['#cfe8e3', '#9fd0c8', '#5fada3', '#2f8a80', '#0e5a54'];
    $N = $d['n'];
    $pc = fn ($a, $b) => $b ? number_format(100 * $a / $b, 1).'%' : '–';
    $fmt = fn ($iso) => date('j M Y', strtotime($iso));
    $years = $d['byYear'];
    $y0 = $years ? min(array_keys($years)) : $today->year;
    $ymax = $years ? max($years) : 1;
    $cols = $today->year - $y0 + 1;
    $colW = $cols ? floor(370 / $cols) : 20;
    $top = max(5, (int) ceil(max(array_merge([0], array_column($d['shown'], 'years'))) / 5) * 5);
    $step = $top > 10 ? 5 : 1;
    $trackW = 300;
    $parts = [['1' => 'Active staff', '0' => 'Inactive staff', '2' => 'Active and inactive staff'][$f['status']]];
    if ($f['band'] >= 0) $parts[] = R::BANDS[$f['band']][0];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Employee Service Lengths</title>
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
    .r { text-align: right; }

    .runfoot { position: fixed; bottom: -42px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 9px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .head { width: 704px; border-bottom: 2px solid #16242b; }
    .head td { padding-bottom: 8px; vertical-align: bottom; }
    .head__title { font-size: 19px; font-weight: 700; }
    .head__sub { font-size: 11px; color: #55616b; padding-top: 2px; }
    .head__meta { text-align: right; font-size: 10.5px; color: #55616b; line-height: 1.45; }

    .stats { width: 704px; margin-top: 12px; }
    .stats td { width: 176px; padding-right: 10px; }
    .stat { border-left: 3px solid #0e5a54; padding: 2px 0 2px 10px; }
    .stat b { font-size: 20px; font-weight: 700; }
    .stat small { font-size: 11px; font-weight: 600; }
    .stat div { font-size: 10px; color: #55616b; margin-top: 2px; }

    .two { width: 704px; margin-top: 22px; }
    .yrs td { vertical-align: bottom; text-align: center; padding: 0 1px; }
    .yrs .n { font-size: 9px; font-weight: 600; }
    .yrs .bar { background: #5fada3; }
    .yrs tr.yx td { border-top: 1px solid #16242b; font-size: 8px; color: #55616b; text-align: center; vertical-align: top; padding-top: 2px; height: auto; }
    .split { width: 100%; height: 22px; }
    .split td { height: 22px; font-size: 10px; font-weight: 700; text-align: center; vertical-align: middle; }
    .bands td { padding: 4px 0; vertical-align: middle; font-size: 10.5px; }
    .sw { width: 11px; height: 11px; border-radius: 2px; }

    .listhead { margin-top: 0; }
    .list { width: 704px; margin-top: 8px; font-size: 9.5px; }
    .list th { padding: 3px 4px; font-size: 9px; font-weight: 600; color: #55616b; border-bottom: 1px solid #16242b; vertical-align: bottom; }
    .list td { padding: 3px 4px; border-bottom: 1px solid #eef1f3; vertical-align: middle; }
    .list tr.band td { background: #e6ecef; font-weight: 700; padding: 5px 6px; border-bottom: 0; }
    .list tr.band span { font-weight: 400; color: #55616b; }
    .list .nm { font-weight: 700; }
    .track { position: relative; width: {{ $trackW }}px; height: 8px; }
    .track div { height: 8px; }
    .ended { color: #8a3d0c; font-size: 8.5px; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Employee Service Lengths</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Employee Service Lengths</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ implode(' · ', $parts) }} · {{ $N }} staff<br>As at {{ $today->format('j M Y') }}</td>
    </tr></table>

    @if(!$N)
        <p class="muted" style="margin-top: 20px;">No staff match these filters.</p>
    @else
    <table class="stats"><tr>
        <td><div class="stat"><b>{{ number_format($d['avg'], 1) }}</b> <small>years</small><div>average service</div></div></td>
        <td><div class="stat"><b>{{ number_format($d['median'], 1) }}</b> <small>years</small><div>median: half of staff have less</div></div></td>
        <td><div class="stat"><b>{{ $d['longest']['y'] }}</b> <small>years</small><div>longest service, since {{ $fmt($d['longest']['started']) }}</div></div></td>
        <td><div class="stat"><b>{{ $d['thisYear'] }}</b><div>joined in {{ $today->year }} · {{ $pc($d['thisYear'], $N) }} of staff</div></div></td>
    </tr></table>

    <table class="two"><tr>
        <td style="width: 380px; padding-right: 24px;">
            <table style="width: 370px;"><tr><td><h2>When staff joined</h2></td><td class="r muted" style="font-size: 10px;">Staff by year they started</td></tr></table>
            <table class="yrs" style="margin-top: 10px; width: {{ $colW * $cols }}px;">
                <tr>
                    @for($y = $y0; $y <= $today->year; $y++)
                        @php $c = $years[$y] ?? 0; @endphp
                        <td style="width: {{ $colW - 2 }}px; height: 112px;">
                            @if($c)<div class="n">{{ $c }}</div><div class="bar" style="height: {{ max(2, round(95 * $c / $ymax)) }}px;"></div>@endif
                        </td>
                    @endfor
                </tr>
                <tr class="yx">
                    @for($y = $y0; $y <= $today->year; $y++)<td>'{{ substr((string) $y, 2) }}</td>@endfor
                </tr>
            </table>
        </td>
        <td>
            <h2>Length of service</h2>
            <table class="split" style="margin-top: 10px;"><tr>
                @foreach($d['bands'] as $k => $c)
                    @if($c)<td style="width: {{ 100 * $c / $N }}%; background: {{ $B[$k] }}; color: {{ $k > 2 ? '#ffffff' : '#04302c' }};">{{ $c / $N > 0.06 ? $c : '' }}</td>@endif
                @endforeach
            </tr></table>
            <table class="bands" style="width: 100%; margin-top: 6px;">
                @foreach(R::BANDS as $k => $b)
                    <tr>
                        <td style="width: 18px;"><div class="sw" style="background: {{ $B[$k] }};"></div></td>
                        <td>{{ $b[0] }}</td>
                        <td class="r" style="width: 30px;"><b>{{ $d['bands'][$k] }}</b></td>
                        <td class="r muted" style="width: 46px;">{{ $pc($d['bands'][$k], $N) }}</td>
                    </tr>
                @endforeach
            </table>
            <div class="muted" style="margin-top: 8px; font-size: 10.5px;">{{ $pc($d['bands'][0] + $d['bands'][1], $N) }} of staff have under 2 years of service.</div>
        </td>
    </tr></table>

    @if(count($d['noStart']))
        <p class="muted" style="margin-top: 18px; font-size: 10px;">No start date on file (left out of the figures): {{ collect($d['noStart'])->pluck('name')->join(', ') }}</p>
    @endif

    <div style="page-break-before: always;"></div>
    <h2 style="font-size: 14px;">{{ $f['band'] >= 0 ? R::BANDS[$f['band']][0] : 'All staff' }}</h2>
    <div class="muted" style="margin-top: 2px;">{{ count($d['shown']) }} staff</div>
    <table class="list">
        <thead><tr>
            <th style="width: 150px;">Staff member</th>
            <th style="width: 64px;">Started</th>
            <th style="width: {{ $trackW }}px;">
                <div style="position: relative; width: {{ $trackW }}px; height: 11px;">
                    @for($t = 0; $t < $top; $t += $step)
                        <span style="position: absolute; top: 0; left: {{ round($trackW * $t / $top) }}px; font-size: 8.5px;">{{ $t }}{{ $t === 0 ? ' years' : '' }}</span>
                    @endfor
                </div>
            </th>
            <th class="r" style="width: 158px;">Service</th>
        </tr></thead>
        <tbody>
            @php $key = -1; @endphp
            @foreach($d['shown'] as $p)
                @if($p['band'] !== $key)
                    @php $key = $p['band']; $cnt = count(array_filter($d['shown'], fn ($x) => $x['band'] === $key)); @endphp
                    <tr class="band"><td colspan="4">{{ R::BANDS[$key][0] }} &nbsp;<span>{{ $cnt }} staff</span></td></tr>
                @endif
                <tr>
                    <td class="nm">{{ $p['name'] }}</td>
                    <td class="muted">{{ $fmt($p['started']) }}</td>
                    <td><div class="track"><div style="width: {{ max(3, round($trackW * min($p['years'], $top) / $top)) }}px; background: {{ $p['left'] ? '#b7bfc5' : $B[$p['band']] }};"></div></div></td>
                    <td class="r"><b>{{ $p['words'] }}</b>@if($p['left'])<br><span class="ended">Ended {{ $fmt($p['end']) }}</span>@elseif($p['future'])<br><span class="ended">Ends {{ $fmt($p['end']) }}</span>@endif</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
