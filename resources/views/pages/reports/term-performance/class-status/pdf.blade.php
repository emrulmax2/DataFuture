@php
    /*
     * Class Status PDF (user's class-status-report design, 25 Nov 2026): A4 landscape (1123 × 794 px
     * at DomPDF's 96 dpi, 45px sides, so 1033px of content), every course with all its groups.
     * Data: ClassStatusSummary. Segment bars are floated divs (DomPDF has no flex and does not
     * clip to a rounded box), with px widths worked out here.
     */
    use App\Services\Reports\ClassStatusSummary as Sum;

    $fontDir = resource_path('fonts/audit-certificate');
    $T = $totals;
    $n = fn ($v) => number_format((int) $v);
    $pc = fn ($a, $b) => $b > 0 ? number_format($a * 100 / $b, 1).'%' : '–';
    $avg = Sum::rate($T);
    $own = fn ($r) => max(0, $r['held'] - $r['proxy']);

    // Columns that only show when the term has any (the design's note says which are left out).
    $extra = array_values(array_filter([
        ['key' => 'cancelled', 'label' => 'Cancelled', 'none' => 'cancelled'],
        ['key' => 'unknown', 'label' => 'Unknown', 'none' => 'unknown-status'],
        ['key' => 'future', 'label' => 'Still to come', 'none' => 'future-scheduled'],
    ], fn ($c) => $T[$c['key']] > 0));
    $absent = array_values(array_filter(['cancelled' => 'cancelled', 'unknown' => 'unknown-status', 'future' => 'future-scheduled'], fn ($label, $key) => $T[$key] === 0, ARRAY_FILTER_USE_BOTH));
    $absentText = match (count($absent)) {
        0 => '',
        1 => 'No '.$absent[0].' classes are recorded for this term, so that column is left out.',
        2 => 'No '.$absent[0].' or '.$absent[1].' classes are recorded for this term, so those columns are left out.',
        default => 'No '.$absent[0].', '.$absent[1].' or '.$absent[2].' classes are recorded for this term, so those columns are left out.',
    };

    // Table widths (design: 318 course · 268 bar · 86 · 101 · 80 · 88 · 89 = 1030); each extra column takes 74px from the bar.
    $barW = max(120, 262 - 74 * count($extra));

    /** Floated segments: scheduled tutor, proxy, not held; 2px gaps; rounded ends. */
    $segments = function (array $r, int $width, int $height, bool $labels = false) use ($own, $n) {
        $parts = array_values(array_filter([
            ['v' => $own($r), 'c' => '#0e5a54', 'label' => 'Held by scheduled tutor '.$n($own($r)), 'short' => $n($own($r))],
            ['v' => $r['proxy'], 'c' => '#5fa39b', 'label' => 'By proxy '.$n($r['proxy']), 'short' => $n($r['proxy'])],
            ['v' => $r['not_held'], 'c' => '#c8601a', 'label' => $n($r['not_held']), 'short' => $n($r['not_held'])],
        ], fn ($p) => $p['v'] > 0));
        $total = array_sum(array_column($parts, 'v'));
        if ($total === 0) {
            return '<div class="seg" style="width:'.$width.'px;height:'.$height.'px;background:#e9edf0;border-radius:'.($height / 2).'px;"></div>';
        }
        $avail = $width - 2 * (count($parts) - 1);
        $textW = fn ($s) => (int) ceil(mb_strlen($s) * 6.6 + 16); // 12px label + 10px inset
        // With labels, a small part (e.g. 44 of 5,016) is at least as wide as its number; the widest part gives up the room.
        $min = fn ($p) => $labels ? max(4, $textW($p['short'])) : 4;
        $widths = array_map(fn ($p) => max($min($p), (int) floor($p['v'] / $total * $avail)), $parts);
        $widest = array_keys($widths, max($widths))[0];
        $widths[$widest] += $avail - array_sum($widths); // the widest takes the rounding and any extra room
        $radius = $height >= 20 ? 6 : $height / 2;
        $html = '<div class="seg" style="width:'.$width.'px;height:'.$height.'px;">';
        foreach ($parts as $i => $p) {
            $first = $i === 0;
            $last = $i === count($parts) - 1;
            $style = 'width:'.$widths[$i].'px;height:'.$height.'px;background:'.$p['c'].';'
                .($last ? '' : 'margin-right:2px;')
                .($first ? 'border-top-left-radius:'.$radius.'px;border-bottom-left-radius:'.$radius.'px;' : '')
                .($last ? 'border-top-right-radius:'.$radius.'px;border-bottom-right-radius:'.$radius.'px;' : '');
            // The full label when it fits (about 6.6px a character at 12px, plus the 10px inset), else the number.
            $text = '';
            if ($labels) {
                $fits = fn ($s) => mb_strlen($s) * 6.6 + 16 <= $widths[$i];
                $text = $fits($p['label']) ? $p['label'] : ($fits($p['short']) ? $p['short'] : '');
            }
            $html .= '<div class="seg__part" style="'.$style.'">'.($text !== '' ? '<span>'.e($text).'</span>' : '').'</div>';
        }

        return $html.'</div>';
    };

    $cells = function (array $r, bool $isTotal = false) use ($pc, $n, $avg, $extra) {
        $rate = Sum::rate($r);
        $low = !$isTotal && $rate !== null && $avg !== null && $rate < $avg;
        $html = '<td class="c-pct'.($low ? ' low' : '').'">'.$pc($r['held'], $r['scheduled']).'</td>'
            .'<td class="c-n">'.$n($r['scheduled']).'</td>'
            .'<td class="c-n">'.$n($r['held']).'</td>'
            .'<td class="c-n '.($r['not_held'] ? 'miss' : 'zero').'">'.$n($r['not_held']).'</td>'
            .'<td class="c-n">'.$n($r['proxy']).'</td>';
        foreach ($extra as $c) {
            $html .= '<td class="c-n'.($r[$c['key']] ? '' : ' zero').'">'.$n($r[$c['key']]).'</td>';
        }

        return $html;
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Class Status Report · {{ $term['name'] }} term</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 100px 45px 60px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 13px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* Running header and footer (as the Attendance Report PDF). */
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

    /* Hero: the held % and the four figures. */
    .hero__title { font-size: 16px; font-weight: 700; }
    .hero__big { font-size: 46px; font-weight: 700; height: 50px; line-height: 1; margin-top: 4px; letter-spacing: -0.02em; }
    .hero__sub { color: #55616b; font-size: 12px; margin-top: 2px; }
    .stats td.stat-gap { width: 12px; }
    .stat { border: 1px solid #dfe3e7; border-radius: 6px; padding: 9px 12px 8px; height: 74px; }
    .stat__k { color: #55616b; font-size: 12px; }
    .stat__k i { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin-right: 6px; }
    .stat__v { font-size: 22px; font-weight: 700; margin-top: 4px; }
    .stat__s { color: #55616b; font-size: 11px; margin-top: 2px; }
    .low, .stat__v.low { color: #8a3d0c; }

    h2 { font-size: 15px; font-weight: 700; margin: 0; }
    .section { margin-top: 14px; }
    .note { color: #55616b; font-size: 11px; margin-top: 6px; }

    .seg { overflow: hidden; }
    .seg__part { float: left; }
    .seg__part span { color: #ffffff; display: block; font-size: 12px; font-weight: 600; padding: 5px 0 0 10px; white-space: nowrap; }
    .legend { color: #55616b; font-size: 12px; text-align: right; }
    .legend i { display: inline-block; width: 9px; height: 9px; border-radius: 2px; margin: 0 5px 0 14px; }

    /* By course and group. */
    .grid th { border-bottom: 2px solid #16242b; color: #55616b; font-size: 11px; font-weight: 600; padding: 0 6px 5px; vertical-align: bottom; }
    .grid th.r { text-align: right; }
    .grid td { border-bottom: 1px solid #e3e7ea; padding: 6px 6px 5px; vertical-align: middle; }
    .grid .c-name { width: 306px; font-size: 12px; }
    .grid .c-bar { width: {{ $barW }}px; }
    .grid .c-pct { font-size: 14px; font-weight: 700; text-align: right; width: 74px; }
    .grid .c-n { font-size: 12px; text-align: right; width: 70px; }
    .grid .miss { color: #8a3d0c; }
    .grid .zero { color: #9aa3ab; }
    .grid tr.course td { font-weight: 700; }
    .grid tr.group td { background: #f6f8f9; }
    .grid tr.group td.c-name { padding-left: 32px; font-weight: 600; }
    .grid tr.group td.c-pct { font-size: 13px; }
    .grid tr.total td { background: #e6ecef; border-bottom: 2px solid #16242b; font-weight: 700; }
    .grid tr.total td.c-name { padding-left: 32px; }
    .grid tr { page-break-inside: avoid; }
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
                <td class="runhead__mid"><div class="runhead__title">Class Status Report</div></td>
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
                <td>London Churchill College · Class Status Report · Confidential, internal use only</td>
                <td class="right"><span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    @if($T['scheduled'] === 0 && $T['future'] === 0)
        <div class="hero__title">{{ $term['name'] }} term</div>
        <div class="note" style="font-size: 13px;">No classes are scheduled for this term.</div>
    @else
        {{-- Hero --}}
        <table>
            <tr>
                <td style="width: 290px; vertical-align: bottom;">
                    <div class="hero__title">{{ $term['name'] }} term</div>
                    <div class="hero__big">{{ $pc($T['held'], $T['scheduled']) }}</div>
                    <div class="hero__sub">of scheduled classes were held</div>
                </td>
                <td>
                    <table class="stats">
                        <tr>
                            <td><div class="stat">
                                <div class="stat__k">Scheduled</div>
                                <div class="stat__v">{{ $n($T['scheduled']) }}</div>
                                <div class="stat__s">Classes due so far{{ $T['future'] ? ' · '.$n($T['future']).' still to come' : '' }}</div>
                            </div></td>
                            <td class="stat-gap"></td>
                            <td><div class="stat">
                                <div class="stat__k"><i style="background: #0e5a54;"></i>Held</div>
                                <div class="stat__v">{{ $n($T['held']) }}</div>
                                <div class="stat__s">{{ $n($own($T)) }} by the scheduled tutor</div>
                            </div></td>
                            <td class="stat-gap"></td>
                            <td><div class="stat">
                                <div class="stat__k"><i style="background: #5fa39b;"></i>Held by proxy</div>
                                <div class="stat__v">{{ $n($T['proxy']) }}</div>
                                <div class="stat__s">{{ $pc($T['proxy'], $T['held']) }} of classes held</div>
                            </div></td>
                            <td class="stat-gap"></td>
                            <td><div class="stat">
                                <div class="stat__k"><i style="background: #c8601a;"></i>Not held</div>
                                <div class="stat__v{{ $T['not_held'] ? ' low' : '' }}">{{ $n($T['not_held']) }}</div>
                                <div class="stat__s">{{ $pc($T['not_held'], $T['scheduled']) }} of scheduled classes</div>
                            </div></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- What happened --}}
        <div class="section">
            <h2>What happened to the {{ $n($T['scheduled']) }} scheduled classes</h2>
            <div style="margin-top: 8px;">{!! $segments($T, 1033, 24, true) !!}</div>
            @if($absentText !== '')
                <div class="note">{{ $absentText }}</div>
            @endif
        </div>

        {{-- By course and group --}}
        <div class="section">
            <table>
                <tr>
                    <td><h2>By course and group</h2></td>
                    <td class="legend"><i style="background: #0e5a54;"></i>Scheduled tutor<i style="background: #5fa39b;"></i>Proxy<i style="background: #c8601a;"></i>Not held</td>
                </tr>
            </table>
            <table class="grid" style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Classes</th>
                        <th class="r">Held %</th>
                        <th class="r">Scheduled</th>
                        <th class="r">Held</th>
                        <th class="r">Not held</th>
                        <th class="r">By proxy</th>
                        @foreach($extra as $c)
                            <th class="r">{{ $c['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($courses as $course)
                        <tr class="course">
                            <td class="c-name">{{ $course['name'] }}</td>
                            <td class="c-bar">{!! $segments($course, $barW, 14) !!}</td>
                            {!! $cells($course) !!}
                        </tr>
                        @foreach($course['groups'] as $group)
                            <tr class="group">
                                <td class="c-name">{{ $group['name'] }}</td>
                                <td class="c-bar">{!! $segments($group, $barW, 14) !!}</td>
                                {!! $cells($group) !!}
                            </tr>
                        @endforeach
                    @endforeach
                    <tr class="total">
                        <td class="c-name">All courses shown</td>
                        <td class="c-bar">{!! $segments($T, $barW, 14) !!}</td>
                        {!! $cells($T, true) !!}
                    </tr>
                </tbody>
            </table>
            <div class="note">Held % = classes held ÷ classes scheduled (due so far). Lowest first; figures in orange are below the term average of {{ $pc($T['held'], $T['scheduled']) }}.</div>
        </div>
    @endif
</body>
</html>
