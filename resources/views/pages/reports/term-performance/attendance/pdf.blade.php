@php
    /*
     * Term Attendance PDF (user's attendance-term-report-pdf design, 24 Nov 2026): one level of the
     * term — the term itself, a course or a group — on A4 portrait (794 × 1123 px at DomPDF's 96
     * dpi, 45px sides, so 704px of content). Data: TermAttendanceTree; drawing:
     * TermAttendanceChart. DomPDF gives a 13px line about 19px whatever the line-height, hence the
     * pinned heights and small paddings.
     */
    use App\Services\Reports\TermAttendanceChart as Draw;

    $fontDir = resource_path('fonts/audit-certificate');
    $levels = [['one' => 'course', 'many' => 'Courses'], ['one' => 'group', 'many' => 'Groups'], ['one' => 'module', 'many' => 'Modules']];
    $level = $levels[min($depth, 2)];
    $pc = fn ($v) => $v === null ? '–' : number_format((float) $v, 1).'%';
    $kids = $node['children'];
    $rated = array_values(array_filter($kids, fn ($k) => $k['rate'] !== null));
    usort($rated, fn ($a, $b) => $a['rate'] <=> $b['rate']);
    $unrated = array_values(array_filter($kids, fn ($k) => $k['rate'] === null));
    $avg = $node['rate'];

    // Weekly grid: the overall row, then the rows lowest rate first; only weeks with any figure.
    $grid = [];
    if (Draw::has($node['weekly'])) {
        $grid[] = ['name' => $depth === 0 ? 'All courses' : 'Overall', 'data' => $node['weekly'], 'main' => true];
    }
    foreach (array_merge($rated, $unrated) as $k) {
        if (Draw::has($k['weekly'])) {
            $grid[] = ['name' => $k['name'], 'data' => $k['weekly'], 'main' => false];
        }
    }
    $shown = array_values(array_filter(array_keys($weeks), fn ($i) => count(array_filter($grid, fn ($r) => $r['data'][$i] !== null)) > 0));
    $nameW = 220;
    $cellW = count($shown) ? max(26, (int) floor((704 - $nameW) / count($shown)) - 3) : 40;
    $cellFont = $cellW >= 38 ? 12 : ($cellW >= 31 ? 11 : 10);
    $title = $depth === 0 ? $term['name'].' term' : $node['name'];
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Attendance Report · {{ $title }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 104px 45px 66px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 13px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* Running header and footer (every page). */
    .runhead { position: fixed; top: -80px; left: 0; right: 0; border-bottom: 2px solid #16242b; }
    .runhead td { padding-bottom: 10px; vertical-align: bottom; }
    .runhead__logo { display: block; height: 42px; width: auto; }
    .runhead__eyebrow { color: #55616b; font-size: 12px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; }
    .runhead__title { font-size: 17px; font-weight: 700; }
    .runhead td.runhead__mid { vertical-align: middle; } /* the title (and its divider) sit in the middle of the logo's height */
    .runhead__divider { border-left: 1px solid #c3c9d1; height: 30px; margin: 0 14px; }
    .runhead__meta { text-align: right; }
    .runhead__term { color: #0e5a54; font-size: 13px; font-weight: 700; }
    .runhead__by { color: #55616b; font-size: 12px; margin-top: 4px; }
    .runfoot { position: fixed; bottom: -40px; left: 0; right: 0; }
    .runfoot td { border-top: 1px solid #dfe3e7; color: #55616b; font-size: 11px; padding-top: 8px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    /* The one number, and lowest / highest. */
    .hero td { vertical-align: middle; }
    .hero__title { font-size: 18px; font-weight: 700; }
    .hero__big { font-size: 52px; font-weight: 700; height: 60px; line-height: 1; margin-top: 4px; letter-spacing: -0.02em; }
    .hero__sub { color: #55616b; font-size: 13px; margin-top: 4px; }
    .fact { border: 1px solid #dfe3e7; border-radius: 6px; margin-bottom: 8px; }
    .fact td { padding: 7px 12px 6px; }
    .fact__label { color: #55616b; font-size: 13px; width: 100px; }
    .fact__name { font-size: 13px; font-weight: 600; }
    .fact__rate { font-size: 17px; font-weight: 700; text-align: right; width: 56px; }
    .low { color: #8a3d0c; }

    h2 { font-size: 16px; font-weight: 700; margin: 0; }
    .section { margin-top: 12px; }

    /* Week by week. */
    .legend-series { margin-top: 8px; }
    .legend-series span { display: inline-block; font-size: 12px; font-weight: 600; margin: 0 16px 0 0; white-space: nowrap; }
    .legend-series i { display: inline-block; width: 10px; height: 10px; border-radius: 5px; margin-right: 6px; }
    .chart { display: block; margin-top: 2px; width: 704px; height: 196px; }
    .note { color: #55616b; font-size: 12px; margin-top: 8px; }

    /* Courses / groups / modules. */
    .listhead td { padding-bottom: 6px; vertical-align: bottom; }
    .legend-bands { color: #55616b; font-size: 12px; text-align: right; }
    .legend-bands i { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin: 0 5px 0 12px; }
    .rows { border-top: 2px solid #16242b; }
    .rows td { border-bottom: 1px solid #e3e7ea; padding: 7px 6px 6px; vertical-align: middle; }
    .rows__name { font-size: 13px; font-weight: 600; width: 300px; }
    .track { background: #e9edf0; border-radius: 5px; height: 10px; width: 286px; }
    .track div { border-radius: 5px; height: 10px; }
    .rows__rate { font-size: 16px; font-weight: 700; text-align: right; width: 64px; }

    /* Weekly figures (heat grid). */
    .weekly { border-top: 1px solid #dfe3e7; margin-top: 12px; padding-top: 10px; page-break-inside: avoid; }
    .weekly h3 { font-size: 13px; font-weight: 700; margin: 0; }
    .weekly__desc { color: #55616b; font-size: 12px; margin-top: 6px; }
    .scale { margin-top: 8px; width: auto; }
    .scale td { color: #55616b; font-size: 11px; vertical-align: middle; }
    .scale .bar td { height: 8px; width: 5px; padding: 0; }
    .heat { border-collapse: separate; border-spacing: 3px; margin-top: 8px; width: auto; }
    .heat th { color: #55616b; font-size: 11px; font-weight: 600; padding: 0 0 2px; text-align: center; }
    .heat th.name { text-align: left; }
    .heat td { border-radius: 4px; font-size: {{ $cellFont }}px; font-weight: 600; height: 19px; padding: 0 0 2px; text-align: center; vertical-align: middle; width: {{ $cellW }}px; }
    /* 2px at the bottom, not the top: the font's line box sits its digits ~2px low; this centres them in the box. */
    .heat td.name { border-radius: 0; font-size: 11px; font-weight: 600; height: auto; padding: 0 10px 0 0; text-align: left; width: {{ $nameW - 10 }}px; }
    .heat tr.main td.name { font-weight: 700; }
    .heat tr.main td.cell { border: 1.5px solid #3a4850; }
</style>
</head>
<body>
    <div class="runhead">
        <table>
            <tr>
                <td style="width: 1%; white-space: nowrap;">
                    @if(!empty($logo))
                        <img class="runhead__logo" src="{{ $logo }}" alt="London Churchill College">
                    @else
                        <div class="runhead__eyebrow">London Churchill College</div>
                    @endif
                </td>
                @if(!empty($logo))
                    <td class="runhead__mid" style="width: 1%;"><div class="runhead__divider"></div></td>
                @endif
                <td class="runhead__mid"><div class="runhead__title">Attendance Report</div></td>
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
                <td>London Churchill College · Attendance Report · Confidential, internal use only</td>
                <td class="right"><span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    {{-- The one number --}}
    <table class="hero">
        <tr>
            <td style="width: 280px;">
                <div class="hero__title">{{ $title }}</div>
                @if($depth > 0)
                    <div class="hero__sub" style="margin-top: 3px;">{{ implode(' › ', array_map(fn ($n) => $n['name'], array_slice($trail, 1, -1))) ?: $term['name'].' term' }}</div>
                @endif
                <div class="hero__big">{{ $pc($avg) }}</div>
                <div class="hero__sub">Attendance this term</div>
            </td>
            <td>
                @if(count($rated) > 1)
                    @foreach([['Lowest', $rated[0], true], ['Highest', $rated[count($rated) - 1], false]] as [$label, $k, $isLow])
                        <table class="fact">
                            <tr>
                                <td class="fact__label">{{ $label }} {{ $level['one'] }}</td>
                                <td class="fact__name">{{ $k['name'] }}</td>
                                <td class="fact__rate{{ $isLow && $avg !== null && $k['rate'] < $avg ? ' low' : '' }}">{{ $pc($k['rate']) }}</td>
                            </tr>
                        </table>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>

    {{-- Week by week --}}
    <div class="section">
        <h2>Week by week</h2>
        @if($chart['uri'])
            <div class="legend-series">
                @foreach($chart['series'] as $s)
                    <span><i style="background: {{ $s['colour'] }};"></i>{{ $s['name'] }}</span>
                @endforeach
            </div>
            <img class="chart" src="{{ $chart['uri'] }}" alt="Weekly attendance">
        @else
            <div class="note">No weekly figures for this {{ $depth === 0 ? 'term' : $levels[$depth - 1]['one'] }}.</div>
        @endif
    </div>

    {{-- The list --}}
    @if($depth < 3 && count($kids))
        <div class="section">
            <table class="listhead">
                <tr>
                    <td><h2>{{ $level['many'] }}</h2></td>
                    @if($avg !== null)
                        <td class="legend-bands"><i style="background: #0e5a54;"></i>At or above {{ $pc($avg) }}<i style="background: #c8601a;"></i>Below</td>
                    @endif
                </tr>
            </table>
            <table class="rows">
                @foreach(array_merge($rated, $unrated) as $k)
                    @php $below = $k['rate'] !== null && $avg !== null && $k['rate'] < $avg; @endphp
                    <tr>
                        <td class="rows__name">{{ $k['name'] }}</td>
                        <td>
                            <div class="track">
                                @if($k['rate'] !== null)
                                    <div style="width: {{ max(1, (int) round(min(100, $k['rate']) * 2.86)) }}px; background: {{ $below ? '#c8601a' : '#0e5a54' }};"></div>
                                @endif
                            </div>
                        </td>
                        <td class="rows__rate{{ $below ? ' low' : '' }}">{{ $pc($k['rate']) }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- Weekly figures --}}
    @if(count($grid) && count($shown))
        <div class="weekly">
            <h3>Weekly figures</h3>
            <div class="weekly__desc">Each cell is one week's attendance (%). Colour shows how far it is from the term average{{ $avg !== null ? ' of '.$pc($avg) : '' }}.</div>
            <table class="scale">
                <tr>
                    <td style="padding-right: 8px;">Below</td>
                    <td>
                        <table class="bar"><tr>
                            @foreach(Draw::scale() as $colour)
                                <td style="background: {{ $colour }};"></td>
                            @endforeach
                        </tr></table>
                    </td>
                    <td style="padding-left: 8px;">Above</td>
                </tr>
            </table>
            <table class="heat">
                <thead>
                    <tr>
                        <th class="name">{{ $depth < 3 ? rtrim($level['many'], 's') : '' }}</th>
                        @foreach($shown as $i)
                            <th>{{ date('j M', strtotime($weeks[$i])) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($grid as $row)
                        <tr class="{{ $row['main'] ? 'main' : '' }}">
                            <td class="name">{{ $row['name'] }}</td>
                            @foreach($shown as $i)
                                @php [$bg, $fg] = Draw::heat($row['data'][$i], $avg); @endphp
                                <td class="cell" style="background: {{ $bg }}; color: {{ $fg }};">{{ $row['data'][$i] === null ? '–' : number_format($row['data'][$i], 1) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>
