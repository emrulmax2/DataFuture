@php
    /*
     * Retention PDF (user's term-retention-report design, 25 Nov 2026): A4 landscape (1123 × 794 px
     * at DomPDF's 96 dpi, 45px sides, so 1033px of content). The rates per term, retention by term
     * of study, then every term and intake. Data: RetentionByIntakeController::data(). Bars are
     * divs with px widths (no flex in DomPDF); the chart is an SVG image (TermRetentionChart).
     */
    use App\Services\Reports\TermRetention;

    $fontDir = resource_path('fonts/audit-certificate');
    $n = fn ($v) => number_format((int) $v);
    $pc = fn ($r) => $r === null ? '–' : number_format($r, 1).'%';
    $labels = array_map(fn ($t) => $t['label'], $terms);
    $termList = count($labels) > 1 ? implode(', ', array_slice($labels, 0, -1)).' and '.end($labels) : ($labels[0] ?? '');
    $perRow = min(3, max(1, count($terms)));
    $cardW = (int) floor((1033 - ($perRow - 1) * 14) / $perRow);

    /** A rounded track filled to the rate. */
    $track = function (?float $rate, string $colour, int $width) {
        $fill = $rate === null ? 0 : max(0, min($width, (int) round($rate / 100 * $width)));
        return '<div class="track" style="width:'.$width.'px;">'.($fill > 0 ? '<div class="track__fill" style="width:'.$fill.'px;background:'.$colour.';"></div>' : '').'</div>';
    };
    /** Stayed against dropped out, as the cards' split bar. */
    $split = function (array $t, int $width) {
        $total = $t['stayed'] + $t['dropped'];
        if ($total === 0) {
            return '<div class="split" style="width:'.$width.'px;background:#e9edf0;"></div>';
        }
        $gap = $t['stayed'] > 0 && $t['dropped'] > 0 ? 2 : 0;
        $stay = $t['dropped'] > 0 ? max(2, (int) floor($t['stayed'] / $total * ($width - $gap))) : $width;
        $drop = $width - $gap - $stay;
        $html = '<div class="split" style="width:'.$width.'px;">';
        if ($t['stayed'] > 0) {
            $html .= '<div class="split__part" style="width:'.$stay.'px;background:'.$t['colour'].';'.($gap ? 'margin-right:2px;border-radius:5px 0 0 5px;' : 'border-radius:5px;').'"></div>';
        }
        if ($t['dropped'] > 0 && $drop > 0) {
            $html .= '<div class="split__part" style="width:'.$drop.'px;background:#c8601a;'.($gap ? 'border-radius:0 5px 5px 0;' : 'border-radius:5px;').'"></div>';
        }
        return $html.'</div>';
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Retention Report · {{ $termList }}</title>
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
    h2 { font-size: 13px; font-weight: 700; margin: 0; }
    .lede { color: #55616b; font-size: 11px; margin-top: 3px; }
    .section { margin-top: 12px; }

    /* Rates per term. */
    .kpis td { padding-bottom: 10px; }
    .kpi { border: 1px solid #dfe3e7; border-radius: 8px; padding: 8px 16px 7px; }
    .kpi__k { font-size: 12px; font-weight: 700; }
    .dot { display: inline-block; width: 10px; height: 10px; border-radius: 5px; margin-right: 6px; }
    .kpi__v { font-size: 32px; font-weight: 700; height: 38px; letter-spacing: -0.02em; margin-top: 2px; }
    .split { height: 9px; overflow: hidden; border-radius: 5px; margin: 4px 0 6px; }
    .split__part { float: left; height: 9px; }
    .kpi__s { color: #55616b; font-size: 11px; }
    .kpi__s b { color: #16242b; }
    .low { color: #8a3d0c !important; }

    /* Retention by term of study. */
    .head td { vertical-align: bottom; }
    .legend { color: #55616b; font-size: 11px; text-align: right; }
    .legend span { font-weight: 700; }
    .legend i { display: inline-block; width: 8px; height: 8px; border-radius: 4px; margin-right: 5px; }
    .chart { border: 1px solid #dfe3e7; border-radius: 8px; padding: 8px 6px 2px; margin-top: 6px; text-align: center; }
    .chart img { display: block; margin: 0 auto; }
    .takeaway { background: #f4f6f8; border-radius: 6px; font-size: 11.5px; margin-top: 8px; padding: 7px 14px 6px; }

    /* By term and intake. */
    .rt { margin-top: 6px; }
    .rt th { border-bottom: 2px solid #16242b; color: #55616b; font-size: 10.5px; font-weight: 600; padding: 0 8px 4px; }
    .rt td { border-bottom: 1px solid #e3e7ea; font-size: 11.5px; padding: 3px 8px 2px; vertical-align: middle; }
    .rt .r { text-align: right; }
    .rt td.pct { font-size: 12.5px; font-weight: 700; }
    /* The band colour on the row, not the cells: cell backgrounds leave hairline seams in DomPDF. */
    .rt tr.term { background: #e6ecef; }
    .rt tr.term td { border-bottom: 1px solid #c3c9d1; font-weight: 700; }
    .rt td.name { padding-left: 26px; font-weight: 600; }
    .rt tr.term td.name { padding-left: 8px; font-weight: 700; }
    .rt td.age { color: #55616b; font-size: 10.5px; }
    .rt td.empty { color: #55616b; font-size: 10.5px; padding-left: 26px; }
    .rt tr { page-break-inside: avoid; }
    .track { height: 10px; overflow: hidden; border-radius: 5px; background: #e9edf0; }
    .track__fill { height: 10px; border-radius: 5px; }
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
                <td class="runhead__mid"><div class="runhead__title">Retention Report</div></td>
                <td class="runhead__meta">
                    <div class="runhead__term">{{ $termList }} {{ count($terms) > 1 ? 'terms' : 'term' }}</div>
                    <div class="runhead__by">Generated {{ $generated }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="runfoot">
        <table>
            <tr>
                <td>London Churchill College · Retention Report · Confidential, internal use only</td>
                <td class="right"><span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    <h1>Retention</h1>
    <div class="lede">Registered students who stayed through the term, by intake</div>

    <table class="kpis section">
        @foreach(array_chunk($terms, $perRow) as $row)
            <tr>
                @foreach($row as $i => $t)
                    <td style="width: {{ $cardW }}px;{{ $i < $perRow - 1 ? ' padding-right: 14px;' : '' }}">
                        <div class="kpi">
                            <div class="kpi__k"><span class="dot" style="background: {{ $t['colour'] }};"></span>{{ $t['label'] }} term</div>
                            <div class="kpi__v">{{ $pc($t['rate']) }}</div>
                            {!! $split($t, $cardW - 34 - ($i < $perRow - 1 ? 14 : 0)) !!}
                            <div class="kpi__s"><b>{{ $n($t['stayed']) }}</b> of {{ $n($t['registered']) }} stayed · <b class="{{ $t['dropped'] ? 'low' : '' }}">{{ $n($t['dropped']) }}</b> dropped out</div>
                        </div>
                    </td>
                @endforeach
                @for($k = count($row); $k < $perRow; $k++)
                    <td style="width: {{ $cardW }}px;"></td>
                @endfor
            </tr>
        @endforeach
    </table>

    @if(!empty($chart))
        <table class="head">
            <tr>
                <td><h2>Retention by term of study</h2></td>
                <td class="legend">
                    {{-- DomPDF drops an inline span's margin, so the gap is spaces. --}}
                    @foreach($series as $s){!! $loop->first ? '' : '&nbsp;&nbsp;&nbsp;&nbsp;' !!}<span style="color: {{ $s['colour'] }};"><i style="background: {{ $s['colour'] }};"></i>{{ $s['label'] }}</span>@endforeach
                </td>
            </tr>
        </table>
        <div class="chart"><img src="{{ $chart }}" width="1000" height="200" alt=""></div>
        @if($takeaway)
            <div class="takeaway">@if($takeaway['lead'])<b>{{ $takeaway['lead'] }}</b> @endif{{ $takeaway['text'] }}</div>
        @endif
    @endif

    <div class="section">
        <h2>By term and intake</h2>
        <table class="rt">
            <thead>
                <tr>
                    {{-- Widths are content only: each cell adds 16px of padding (8 × 16 + 905 = 1033). --}}
                    <th style="width: 228px;">Term and intake</th>
                    <th style="width: 82px;">Term of study</th>
                    <th style="width: 250px;">Retention rate</th>
                    <th style="width: 56px;"></th>
                    <th class="r" style="width: 70px;">Registered</th>
                    <th class="r" style="width: 60px;">Stayed</th>
                    <th class="r" style="width: 78px;">Dropped out</th>
                    <th class="r" style="width: 81px;">Total students</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terms as $t)
                    <tr class="term">
                        <td class="name">{{ $t['label'] }} term</td>
                        <td></td>
                        <td>{!! $track($t['rate'], '#16242b', 246) !!}</td>
                        <td class="r pct">{{ $pc($t['rate']) }}</td>
                        <td class="r">{{ $n($t['registered']) }}</td>
                        <td class="r">{{ $n($t['stayed']) }}</td>
                        <td class="r">{{ $n($t['dropped']) }}</td>
                        <td class="r">{{ $n($t['total']) }}</td>
                    </tr>
                    @forelse($t['intakes'] as $in)
                        @php $low = $in['rate'] !== null && $t['rate'] !== null && $in['rate'] < $t['rate']; @endphp
                        <tr>
                            <td class="name">{{ $in['label'] }} intake</td>
                            <td class="age">{{ $in['study'] ? TermRetention::ordinal($in['study']).' term' : '–' }}</td>
                            <td>{!! $track($in['rate'], $low ? '#c8601a' : '#0e5a54', 246) !!}</td>
                            <td class="r pct{{ $low ? ' low' : '' }}">{{ $pc($in['rate']) }}</td>
                            <td class="r">{{ $n($in['registered']) }}</td>
                            <td class="r">{{ $n($in['stayed']) }}</td>
                            <td class="r{{ $in['dropped'] ? ' low' : '' }}">{{ $n($in['dropped']) }}</td>
                            <td class="r">{{ $n($in['total']) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="8">No students are recorded for this term.</td></tr>
                    @endforelse
                @endforeach
            </tbody>
        </table>
    </div>

    <ul class="notes">
        <li>Retention rate = (Registered − Dropped out) ÷ Registered. Orange = below that term's overall rate.</li>
        <li>Term of study counts from the intake's first term: an intake that joined this term is in its 1st term.</li>
        <li>Total students = students on an intake's courses who are assigned to classes this term. Registered = those with an awarding body registration.</li>
        <li>Dropped out = registered students who have left (Discarded, Left, Suspended or Terminated, SLC and non-progression included) with their leaving recorded against this term; each counts once, in that term. Students who came back are not counted; suspended students who have not returned are. Leaving with the HNC is not a dropout.</li>
        @php $gone = array_filter($terms, fn ($t) => $t['left_before'] > 0); @endphp
        @if(!empty($gone))
            <li>Not counted in a term: students who had left in an earlier term or within 28 days of their course start ({{ implode(', ', array_map(fn ($t) => $t['label'].' term '.$n($t['left_before']), $gone)) }}).</li>
        @endif
    </ul>
</body>
</html>
