@php
    /*
     * Eligibility Expiry PDF, new version (user's eligibility-expiry-report.pdf design, 26 Nov 2026): A4
     * portrait at DomPDF's 96 dpi, 45px sides (704px of content). Page 1: where every permit stands and the
     * next 12 months; the list (needs attention, a group or a month) follows from page 2.
     * Data: EligibilityExpiry::build.
     */
    use App\Services\HR\EligibilityExpiry as R;

    $fontDir = resource_path('fonts/audit-certificate');
    $k = $f['kind'];
    $unit = $k === 'doc' ? 'document' : 'permit';
    $C = ['x' => '#8a3d0c', 's30' => '#c8601a', 's90' => '#e0934f', 's180' => '#5fada3', 'ok' => '#0e5a54', 'nd' => '#8b949c'];
    $N = count($d['rows']);
    $filtered = collect(['q', 'type', 'department', 'from', 'to', 'ethnicity', 'nationality', 'gender', 'status'])->contains(fn ($x) => $f[$x] !== '');
    $inYear = array_sum(array_map(fn ($m) => count($m['people']), $d['months']));
    $tall = max(1, ...array_map(fn ($m) => count($m['people']), $d['months']));
    $cap = 18; // dots in one month before "+N"
    $shown = $d['shown'];
    $onlyNd = count($shown) && collect($shown)->every(fn ($r) => $r[$k]['band'] === 'nd');
    $sub = $f['band'] || $f['ym'] ? '' : ' · expiring within 90 days, then expired';
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Eligibility Expiry Report</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 50px 45px 66px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 11px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td, th { padding: 0; text-align: left; vertical-align: top; }
    .muted { color: #55616b; }
    h2 { margin: 0; font-size: 14px; font-weight: 700; }
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

    .tiles { width: 704px; margin-top: 10px; }
    .tiles td { width: 109px; padding-right: 8px; }
    .tiles td.last { padding-right: 0; }
    .bar { height: 4px; border-radius: 4px 4px 0 0; }
    .tile { border: 1px solid #e3e8eb; border-top: 0; border-radius: 0 0 6px 6px; padding: 6px 9px 9px; }
    .tile b { display: block; font-size: 20px; font-weight: 700; }
    .tile .l { font-size: 10px; font-weight: 700; margin-top: 3px; }
    .tile .s { font-size: 8.5px; color: #55616b; margin-top: 3px; }

    .legend { margin-top: 8px; font-size: 10.5px; color: #55616b; }
    .legend span.dot { display: inline-block; width: 9px; height: 9px; border-radius: 5px; margin: 0 3px 0 10px; }

    .run { width: 704px; margin-top: 10px; }
    .run td { width: 52px; padding: 0 3px; vertical-align: bottom; text-align: center; }
    .run .dot { width: 9px; height: 9px; border-radius: 5px; margin: 3px auto 0; }
    .run .more { font-size: 8.5px; color: #55616b; }
    .run .base { border-top: 2px solid #16242b; margin-top: 4px; padding-top: 4px; font-size: 11px; font-weight: 700; height: 13px; }
    .run .mo { padding-top: 5px; font-size: 10px; font-weight: 600; color: #55616b; }

    .list { width: 704px; margin-top: 12px; font-size: 10px; }
    .list th { padding: 0 6px 5px; font-size: 9.5px; font-weight: 600; color: #55616b; border-bottom: 1.5px solid #16242b; }
    .list td { padding: 5px 6px; border-bottom: 1px solid #eef1f3; vertical-align: middle; }
    .list tr.gh td { background: #e6ecef; font-weight: 700; padding: 5px 8px; border-bottom: 0; }
    .list tr.gh span.sq { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 6px; }
    .list tr.gh span.n { font-weight: 400; color: #55616b; margin-left: 4px; }
    .list .nm { font-weight: 700; }
    .miss { color: #8a3d0c; font-weight: 700; }
    .track { width: 116px; height: 6px; border-radius: 3px; background: #e9edf0; }
    .track div { height: 6px; border-radius: 3px; }
    .pill { padding: 2px 7px; border-radius: 8px; color: #ffffff; font-size: 9px; font-weight: 700; }
    .cols td { width: 168px; padding: 4px 8px 4px 0; font-size: 10px; border-bottom: 1px solid #eef1f3; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Eligibility Expiry Report · Confidential</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Eligibility Expiry Report</div>
            <div class="head__sub">London Churchill College · {{ $k === 'doc' ? 'passport and ID document' : 'visa and work permit' }} expiry</div>
        </td>
        <td class="head__meta">{{ $N }} {{ $N === 1 ? 'person' : 'people' }}{{ $filtered ? ' · filtered' : '' }}<br>As at {{ $today->format('j M Y') }}</td>
    </tr></table>

    @if(!$N)
        <p class="muted" style="margin-top: 20px;">Nobody matches these filters.</p>
    @else
    <h2 style="margin-top: 16px;">Where every {{ $unit }} stands</h2>
    <table class="tiles"><tr>
        @foreach(R::BANDS as $b => [$label, $hint])
            <td class="{{ $loop->last ? 'last' : '' }}"><div class="bar" style="background: {{ $C[$b] }};"></div><div class="tile">
                <b>{{ $d['counts'][$b] }}</b>
                <div class="l">{{ $label }}</div>
                <div class="s">{{ $hint }}</div>
            </div></td>
        @endforeach
    </tr></table>

    <h2 style="margin-top: 26px;">The next 12 months</h2>
    <div class="legend">Each dot is one {{ $unit }} expiring that month
        @foreach(['s30', 's90', 's180', 'ok'] as $b)<span class="dot" style="background: {{ $C[$b] }};"></span>{{ R::BANDS[$b][0] }}@endforeach
    </div>
    <table class="run"><tr>
        @foreach($d['months'] as $m)
            @php
                $ps = $m['people'];
                usort($ps, fn ($a, $b) => $b[$k]['days'] <=> $a[$k]['days']); // soonest at the bottom
                $over = max(0, count($ps) - $cap);
                $ps = array_slice($ps, $over);
            @endphp
            <td>
                @if($over)<div class="more">+{{ $over }}</div>@endif
                @foreach($ps as $p)<div class="dot" style="background: {{ $C[$p[$k]['band']] }};"></div>@endforeach
                <div class="base">{{ count($m['people']) ?: '' }}</div>
                <div class="mo">{{ $m['first']->format('M') }} '{{ $m['first']->format('y') }}</div>
            </td>
        @endforeach
    </tr></table>
    <p class="muted" style="margin-top: 14px; font-size: 11px;">{{ $inYear }} {{ $unit }}{{ $inYear === 1 ? '' : 's' }} expire{{ $inYear === 1 ? 's' : '' }} in the next 12 months.</p>
    @if($k === 'visa' && $d['counts']['nd'])
        <p class="muted" style="margin-top: 6px; font-size: 10px;">{{ $d['counts']['nd'] }} {{ $d['counts']['nd'] === 1 ? 'person has' : 'people have' }} no {{ $unit }} expiry date. The old report prints an empty date as expired on 1 January 1970; here they sit under No date recorded.</p>
    @endif

    <div style="page-break-before: always;"></div>
    <h2 style="font-size: 15px;">{{ $d['title'] }}</h2>
    <div class="muted" style="margin-top: 3px; font-size: 11px;">{{ count($shown) }} {{ $unit }}{{ count($shown) === 1 ? '' : 's' }}{{ $sub }}</div>
    @if(!count($shown))
        <p class="muted" style="margin-top: 12px;">Nothing in this group.</p>
    @elseif($onlyNd)
        <table class="cols" style="width: 704px; margin-top: 12px;">
            @foreach(array_chunk($shown, 4) as $line)
                <tr>@foreach($line as $p)<td>{{ $p['name'] }}</td>@endforeach @for($i = count($line); $i < 4; $i++)<td></td>@endfor</tr>
            @endforeach
        </table>
    @else
        <table class="list">
            <tr>
                <th style="width: 168px;">Employee</th>
                <th style="width: 150px;">{{ $k === 'doc' ? 'Document' : 'Permit number' }}</th>
                <th style="width: 74px;">Expiry date</th>
                <th style="width: 128px;">Time left</th>
                <th class="r">Status</th>
            </tr>
            @php $key = null; @endphp
            @foreach($shown as $p)
                @php $x = $p[$k]; @endphp
                @if($x['band'] !== $key)
                    @php $key = $x['band']; $c = count(array_filter($shown, fn ($y) => $y[$k]['band'] === $key)); @endphp
                    <tr class="gh"><td colspan="5"><span class="sq" style="background: {{ $C[$key] }};"></span>{{ R::BANDS[$key][0] }}<span class="n">{{ $c }}</span></td></tr>
                @endif
                @php $num = $k === 'doc' ? implode(' · ', array_filter([$x['what'], $x['num']])) : $x['num']; @endphp
                <tr>
                    <td class="nm">{{ $p['name'] }}</td>
                    <td>@if($num !== ''){{ $num }}@else<span class="miss">Not recorded</span>@endif</td>
                    <td>{{ $x['date'] ? \Carbon\Carbon::parse($x['date'])->format('j M Y') : '–' }}</td>
                    <td>@if($x['days'] !== null && $x['days'] >= 0 && $x['days'] <= 180)<div class="track"><div style="width: {{ max(3, round(100 * $x['days'] / 180)) }}%; background: {{ $C[$x['band']] }};"></div></div>@endif</td>
                    <td class="r">
                        @if($x['band'] === 'nd')
                            <span class="pill" style="background: #e9edf0; color: #55616b;">No date</span>
                        @else
                            <span class="pill" style="background: {{ $C[$x['band']] }};{{ $x['band'] === 's90' ? ' color: #3d1d05;' : '' }}">{{ R::when($x['days']) }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
    @endif
</body>
</html>
