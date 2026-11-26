@php
    /*
     * Employee Starter Information PDF, new version (user's employee-starter-information.pdf design,
     * 26 Nov 2026): A4 portrait at DomPDF's 96 dpi, 45px sides (704px of content). Page 1: the figures and
     * the year × month grid; the starters follow from page 2, newest first. Data: StarterReport::build.
     */
    use App\Services\HR\StarterReport as R;

    $fontDir = resource_path('fonts/audit-certificate');
    $N = $d['n'];
    $pc = fn ($a, $b) => $b ? number_format(100 * $a / $b, 1).'%' : '–';
    $MN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $lvl = fn ($c) => $c >= 7 ? '#0e5a54' : ($c >= 4 ? '#5fada3' : ($c >= 2 ? '#9fd0c8' : '#cfe8e3'));
    $years = array_keys($d['grid']);
    $parts = [['1' => 'Active staff', '0' => 'Inactive staff', '2' => 'Active and inactive staff'][$f['status']]];
    $title = 'All starters';
    if ($f['year']) $title = 'Started in '.($f['month'] >= 0 ? R::MONTHS[$f['month']].' ' : '').$f['year'];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Employee Starter Information</title>
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
    .stat.warn { border-left-color: #c8601a; }
    .stat b { font-size: 20px; font-weight: 700; }
    .stat.warn b { color: #8a3d0c; }
    .stat div { font-size: 10px; color: #55616b; margin-top: 2px; }

    .grid { width: 704px; margin-top: 8px; }
    .grid td { padding: 1px; vertical-align: middle; font-size: 9.5px; }
    .grid th { padding: 0 1px 3px; font-size: 9.5px; font-weight: 600; color: #55616b; text-align: center; }
    .grid .y { font-weight: 700; width: 40px; }
    .grid .t { font-weight: 700; text-align: right; width: 36px; padding-right: 4px; }
    .cell { height: 16px; padding-top: 2px; border-radius: 4px; background: #f4f6f8; text-align: center; font-weight: 700; color: #04302c; }
    .gapy td { text-align: center; color: #55616b; font-size: 9px; padding: 3px 0; }
    .tot td { border-top: 1px solid #16242b; font-weight: 700; text-align: center; padding-top: 4px; }

    .list { width: 704px; margin-top: 8px; font-size: 10px; }
    .list td { padding: 4px 6px; border-bottom: 1px solid #eef1f3; vertical-align: middle; }
    .list tr.yh td { background: #e6ecef; font-weight: 700; padding: 5px 8px; border-bottom: 0; }
    .list tr.yh span { font-weight: 400; color: #55616b; }
    .list .d b { font-size: 11.5px; }
    .list .d span { font-size: 9px; font-weight: 600; color: #55616b; }
    .list .nm { font-weight: 700; }
    .new { padding: 1px 6px; border-radius: 8px; background: #dcefec; color: #0b4a45; font-size: 8.5px; font-weight: 700; }
    .miss { color: #8a3d0c; font-weight: 700; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Employee Starter Information</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Employee Starter Information</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ implode(' · ', $parts) }} · {{ $N }} starters<br>As at {{ $today->format('j M Y') }}</td>
    </tr></table>

    @if(!$N)
        <p class="muted" style="margin-top: 20px;">No starters match these filters.</p>
    @else
    <table class="stats"><tr>
        <td><div class="stat"><b>{{ $d['thisYear'] }}</b><div>starters in {{ $today->year }} · {{ $pc($d['thisYear'], $N) }} of current staff</div></div></td>
        <td><div class="stat"><b>{{ $d['last90'] }}</b><div>started in the last 90 days</div></div></td>
        <td><div class="stat"><b>{{ R::MONTHS[$d['busiest']] }}</b><div>busiest month to start · {{ $d['byMonth'][$d['busiest']] }} starters over the years</div></div></td>
        <td><div class="stat{{ $d['noWorks'] ? ' warn' : '' }}"><b>{{ $d['noWorks'] }}</b><div>starters with no works number</div></div></td>
    </tr></table>

    <table style="width: 704px; margin-top: 22px;"><tr>
        <td><h2>When staff started</h2></td>
        <td class="r muted" style="font-size: 10px;">1 &nbsp;<span style="background: #cfe8e3;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <span style="background: #9fd0c8;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <span style="background: #5fada3;">&nbsp;&nbsp;&nbsp;&nbsp;</span> <span style="background: #0e5a54;">&nbsp;&nbsp;&nbsp;&nbsp;</span>&nbsp; 7 or more</td>
    </tr></table>
    <table class="grid">
        <tr><th></th>@foreach($MN as $m)<th>{{ $m }}</th>@endforeach<th class="r">Total</th></tr>
        @foreach($years as $k => $y)
            @if($k && $years[$k - 1] - $y > 1)
                <tr class="gapy"><td colspan="14">No starters still here from {{ $y + 1 }}{{ $years[$k - 1] - $y > 2 ? ' to '.($years[$k - 1] - 1) : '' }}</td></tr>
            @endif
            <tr>
                <td class="y">{{ $y }}</td>
                @foreach($d['grid'][$y] as $m => $c)
                    <td><div class="cell" style="{{ $c ? 'background: '.$lvl($c).';'.($c >= 7 ? ' color: #ffffff;' : '') : '' }}">{{ $c ?: '' }}</div></td>
                @endforeach
                <td class="t">{{ array_sum($d['grid'][$y]) }}</td>
            </tr>
        @endforeach
        <tr class="tot"><td style="border-top: 0;"></td>@foreach($d['byMonth'] as $c)<td>{{ $c }}</td>@endforeach<td class="r" style="padding-right: 4px;">{{ $N }}</td></tr>
    </table>

    @if(count($d['noStart']))
        <p class="muted" style="margin-top: 16px; font-size: 10px;">No start date on file (left out of the figures): {{ collect($d['noStart'])->pluck('name')->join(', ') }}</p>
    @endif

    <div style="page-break-before: always;"></div>
    <h2 style="font-size: 14px;">{{ $title }}</h2>
    <div class="muted" style="margin-top: 2px;">{{ count($d['shown']) }} staff · newest first</div>
    <table class="list">
        @php $key = null; @endphp
        @foreach($d['shown'] as $p)
            @php $y = (int) substr($p['started'], 0, 4); @endphp
            @if($y !== $key)
                @php $key = $y; $c = count(array_filter($d['shown'], fn ($x) => (int) substr($x['started'], 0, 4) === $y)); @endphp
                <tr class="yh"><td colspan="4">{{ $y }} &nbsp;<span>{{ $c }} starter{{ $c === 1 ? '' : 's' }}</span></td></tr>
            @endif
            <tr>
                <td class="d" style="width: 58px;"><b>{{ (int) substr($p['started'], 8, 2) }}</b> <span>{{ strtoupper($MN[(int) substr($p['started'], 5, 2) - 1]) }}</span></td>
                <td class="nm" style="width: 250px;">{{ $p['name'] }}@if($p['days'] !== null && $p['days'] >= 0 && $p['days'] <= 90)&nbsp; <span class="new">New</span>@endif</td>
                <td style="width: 150px;">@if($p['works'] !== '')<span class="muted">Works no. {{ $p['works'] }}</span>@else<span class="miss">No works number</span>@endif</td>
                <td class="r muted">{{ $p['ago'] }}</td>
            </tr>
        @endforeach
    </table>
    @endif
</body>
</html>
