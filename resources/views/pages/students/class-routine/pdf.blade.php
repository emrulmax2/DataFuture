{{--
    NOT USED since 30 Oct 2026: replaced by the detailed term timetable, term-pdf.blade.php
    (ClassRoutineGroupPdf). Kept for reference.

    Class routine (ICP) PDF, on the "Timetable_<group>.html" design the user sent (28 Oct 2026).
    Data: App\Services\ClassRoutine\ClassRoutineBuilder. Rendered by DomPDF, so the design's
    CSS grids are fixed-width tables (widths add up to the 186mm content width), the break
    hatching is a flat tint and the fonts are the ones embedded for the audit certificate.
--}}
@php
    $fontDir = resource_path('fonts/audit-certificate');
    $e = fn ($value) => e((string) $value);
    $date = fn ($value, $format) => $value ? \Carbon\Carbon::parse($value)->format($format) : '';
    $tick = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><circle cx="12" cy="12" r="12" fill="#e6f0ec"/><path d="M17.5 8.5 10.4 15.6 6.8 12" fill="none" stroke="#1d6b5f" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>');

    $slots = $routine['slots'];
    $slotCount = max(1, count($slots));
    // day 22mm, then slot | break 6mm | slot …, with a 1.6mm gap between columns
    $slotWidth = round((186 - 22 - 1.6 - ($slotCount - 1) * 9.2) / $slotCount, 2);
    // each slot is the 2.2mm coloured bar (its own cell) and the card; a card's width is its content box:
    // the slot less the bar, its 0.75pt right border and 2.6mm padding each side
    $cardWidth = $slotWidth - 2.2 - 0.27 - 5.2;
    $slotCols = 2 * $slotCount + 3 * ($slotCount - 1);   // bar + card per slot, gap | break | gap between
    // the design's 19pt title, a little smaller when the course name would not fit on one line
    $titleSize = mb_strlen($routine['course']) > 34 ? 14 : (mb_strlen($routine['course']) > 24 ? 16.5 : 19);

    $supportHours = (string) config('class_routine.support_hours', '10:00 – 16:00');
    $supportDays = $routine['support_days'];
    $plural = fn ($days) => implode(' and ', array_filter([implode(', ', array_map(fn ($d) => $d.'s', array_slice($days, 0, -1))), ($days ? end($days).'s' : '')]));
    $termLine = implode(' · ', array_filter([
        $routine['term']['code'] ? preg_replace('/^Y(\d+)T(\d+)$/', 'Year $1 · Term $2', $routine['term']['code']) : null,
        'Weekly teaching pattern, teaching weeks and assessment deadlines',
    ]));
    $teaching = $routine['term']['teaching_start'] && $routine['term']['teaching_end']
        ? $date($routine['term']['teaching_start'], 'j M').' – '.$date($routine['term']['teaching_end'], 'j M Y') : '';
    $phone = (string) config('class_routine.phone', '020 7377 1077');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Timetable — {{ $routine['course'] }} · {{ $routine['group']['name'] }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-500.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-500.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-600.ttf") format('truetype'); }

    @page { size: A4; margin: 11mm 12mm 16mm 12mm; }
    * { box-sizing: border-box; }
    /* DomPDF line boxes are line-height × (ascender − descender): ×1.434 Hanken Grotesk, ×1.508 Source Serif 4,
       ×1.452 JetBrains Mono, so the design's line-heights are divided by those. */
    body { margin: 0; font-family: 'Hanken Grotesk', 'DejaVu Sans', sans-serif; font-size: 8.6pt; line-height: 0.94; color: #15201e; background: #fff; }
    table { border-collapse: separate; border-spacing: 0; }
    td, th { vertical-align: top; padding: 0; }
    p { margin: 0; }
    .mono { font-family: 'JetBrains Mono', monospace; }
    .lbl { font-size: 6.8pt; letter-spacing: 1.2pt; text-transform: uppercase; color: #7a847f; font-weight: 700; }
    .block { margin-top: 4mm; }

    /* header */
    .hd { width: 186mm; border-bottom: 1.1pt solid #15201e; }
    .hd td { padding-bottom: 3.5mm; }
    .hd .logo { width: 40mm; }
    .hd .logo img { width: 12.5mm; height: auto; }
    .hd .logo b { font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 9.5pt; line-height: 0.7; letter-spacing: 0.6pt; text-transform: uppercase; }
    .hd .ttl { width: 95mm; border-left: 0.75pt solid #d9d9d4; padding-left: 6mm; padding-right: 3mm; }
    .hd .ttl h1 { font-family: 'Source Serif 4', Georgia, serif; font-weight: 500; font-size: 19pt; line-height: 0.7; margin: 1.2mm 0 0; }
    .hd .ttl p { margin-top: 1.4mm; color: #3d4a46; font-size: 9pt; }
    .hd .grp { width: 42mm; text-align: right; }
    .hd .grp .g { display: block; font-family: 'JetBrains Mono', monospace; font-size: 13pt; font-weight: 600; letter-spacing: 0.5pt; margin-top: 1mm; }
    .hd .grp .sz { display: block; font-size: 8pt; color: #3d4a46; margin-top: 1mm; }

    /* meta strip */
    .meta { border: 0.75pt solid #d9d9d4; border-radius: 2.5mm; }
    .meta table { width: 185mm; }
    .meta td { width: 39.25mm; padding: 2.2mm 3.5mm; border-left: 0.75pt solid #d9d9d4; }
    .meta td.first { border-left: 0; }
    .meta b { display: block; font-size: 9.6pt; font-weight: 600; margin-top: 0.6mm; }

    h2 { font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 11pt; line-height: 0.75; margin: 0 0 2mm; }
    .h2row { width: 186mm; margin-bottom: 2mm; }
    .h2row h2 { margin: 0; }
    .key { font-size: 7.4pt; font-weight: 600; color: #3d4a46; text-align: right; }
    .key i { display: inline-block; width: 2.6mm; height: 2.6mm; border-radius: 0.7mm; margin: 0 1.2mm 0 3mm; }

    /* week grid */
    .wk { width: 186mm; }
    .wk .tm { font-family: 'JetBrains Mono', monospace; font-size: 7.6pt; font-weight: 600; color: #3d4a46; padding: 0 0 0.8mm 1mm; }
    .wk .day { width: 20.4mm; padding-left: 1mm; border-left: 1.5pt solid #15201e; vertical-align: middle; }
    .wk .day b { font-size: 9.4pt; font-weight: 700; }
    .wk .gap { width: 1.6mm; }
    .wk .brk { width: 6mm; }
    .wk .sp td { height: 1.6mm; }
    /* The design's thick left rule is a cell of its own: a 2.2mm border-left on a bordered box makes
       DomPDF mitre the top and bottom corners. */
    .wk td.bar { width: 2.2mm; background: #1d6b5f; }
    .wk td.bar.online { background: #b0821f; }
    .wk td.bar.empty { width: 1.93mm; background: #fff; border: 0.75pt dashed #d9d9d4; border-right: 0; }
    .wk td.s { height: 21mm; border: 0.75pt solid #b8cbc5; border-left: 0; padding: 1.6mm 2.6mm; background: #fff; }
    .wk td.s.online { border-color: #e2d4b2; }
    .wk td.s.empty { border: 0.75pt dashed #d9d9d4; border-left: 0; }
    .wk td.brk { background: #efefea; text-align: center; vertical-align: middle; color: #a3aaa6; font-size: 5.6pt; font-weight: 700; line-height: 0.95; }
    .more { border-top: 0.75pt dashed #d9d9d4; margin-top: 1.6mm; padding-top: 1.6mm; }
    .s-top { width: 100%; }
    .kind { font-size: 6.6pt; letter-spacing: 0.9pt; text-transform: uppercase; font-weight: 700; color: #1d6b5f; }
    .online .kind { color: #b0821f; }
    .mode { font-size: 6.6pt; font-weight: 700; padding: 0.3mm 1.6mm; border-radius: 3mm; background: #e6f0ec; color: #164f47; }
    .online .mode { background: #f8efd9; color: #84621a; }
    .s-t { display: block; font-size: 9.2pt; font-weight: 600; line-height: 0.84; margin-top: 0.8mm; }
    .ln { display: block; font-size: 7.8pt; color: #3d4a46; margin-top: 0.7mm; }
    /* label | value rows: the 7pt label sits on the first line of the 7.8pt value (its extra 0.3mm top
       makes up the smaller font's higher baseline), so a name that wraps keeps its label beside line one */
    .kv { margin-top: 0.3mm; }
    .kv td { padding-top: 0.45mm; vertical-align: top; font-size: 7.8pt; color: #3d4a46; }
    .kv td.k { width: 11mm; padding-top: 0.75mm; font-size: 7pt; color: #7a847f; white-space: nowrap; }
    .kv td.link { padding-top: 0.2mm; white-space: nowrap; }
    .kv a { color: #15201e; text-decoration: none; font-family: 'JetBrains Mono', monospace; font-size: 6.4pt; }
    .flexrow { width: 162.4mm; }
    .flexrow td.fbar { width: 2.2mm; background: #3d4a46; }
    .flexrow td.fbar.quiet { background: #d9d9d4; }
    .flexrow td.flex { width: 153.9mm; border: 0.75pt solid #d9d9d4; border-left: 0; background: #f7f5f0; padding: 2.2mm 3mm; }
    .flexrow td.flex.quiet { background: #fff; }
    .flex b { font-size: 8.8pt; font-weight: 600; }
    .flex span { font-size: 7.8pt; color: #3d4a46; padding-left: 4mm; }
    .flex.quiet b { color: #3d4a46; }
    sup { font-size: 6.5pt; color: #b0821f; font-weight: 700; }

    /* bottom */
    .btm { width: 186mm; }
    .weeks { width: 90mm; }
    .weeks th { text-align: left; font-size: 6pt; letter-spacing: 0.45pt; text-transform: uppercase; color: #7a847f; font-weight: 700; padding: 0 1.5mm 1.2mm; border-bottom: 0.75pt solid #15201e; }
    .weeks td { padding: 0.35mm 1.5mm; border-bottom: 0.75pt solid #ebebe6; font-size: 8.2pt; vertical-align: middle; }
    .weeks .n { width: 6mm; color: #7a847f; font-size: 7.6pt; }
    .weeks .d { width: 31mm; }
    .weeks .c { width: 12.6mm; text-align: center; }
    .weeks .c img { width: 3.4mm; height: 3.4mm; }
    .weeks .no { color: #d9d9d4; }
    .rev { font-size: 6.2pt; font-weight: 700; color: #84621a; background: #f8efd9; border-radius: 2mm; padding: 0.2mm 1.3mm; margin-left: 1mm; }
    .as { border: 0.75pt solid #d9d9d4; border-radius: 1.8mm; padding: 1.3mm 2.5mm; margin-bottom: 1.3mm; }
    .as table { width: 84.8mm; }
    .as td { vertical-align: middle; }
    .as .u { width: 9mm; }
    .unit { height: 8mm; padding-top: 2.4mm; background: #0f2d2a; color: #fff; text-align: center; border-radius: 1.4mm; font-family: 'JetBrains Mono', monospace; font-size: 8pt; font-weight: 600; }
    .as .g { width: 2.5mm; }
    .as .t { width: 36mm; }
    .as .t b { display: block; font-size: 8.8pt; font-weight: 600; line-height: 0.84; }
    .as .t small { display: block; font-size: 7.4pt; color: #7a847f; margin-top: 0.6mm; }
    .as .cl { width: 10mm; }
    .cal { border: 0.75pt solid #15201e; border-radius: 1.4mm; text-align: center; }
    .cal small { display: block; background: #15201e; color: #fff; font-size: 5.8pt; font-weight: 700; letter-spacing: 0.7pt; text-transform: uppercase; padding: 0.4mm 0; }
    .cal b { display: block; font-family: 'Source Serif 4', Georgia, serif; font-size: 11pt; font-weight: 600; line-height: 0.7; padding: 0.8mm 0 0.6mm; }
    .cal-d { width: 22mm; font-size: 8pt; font-weight: 600; }
    .cal-d small { display: block; font-size: 7.2pt; color: #7a847f; font-weight: 500; }
    .note { background: #f8efd9; border-radius: 1.8mm; padding: 2.2mm 3mm; font-size: 8pt; color: #3d4a46; margin-top: 1mm; }
    .note b { color: #15201e; font-weight: 600; }

    .ft { position: fixed; left: 0; right: 0; bottom: -9mm; border-top: 0.75pt solid #d9d9d4; padding-top: 2.5mm; font-size: 7.4pt; color: #7a847f; }
    .ft table { width: 186mm; }
    .ft b { color: #15201e; font-weight: 600; }
</style>
</head>
<body>

<div class="ft">
    <table><tr>
        <td><b>London Churchill College</b>{!! $routine['venue']['address'] ? ' · '.$e($routine['venue']['address']) : '' !!}</td>
        <td style="text-align: right;">{{ $phone }} · Ref {{ $routine['ref'] }}</td>
    </tr></table>
</div>

<table class="hd"><tr>
    <td class="logo">
        <table><tr>
            @if($logo)<td style="vertical-align: middle; width: 12.5mm;"><img src="{{ $logo }}" alt=""></td>@endif
            <td style="vertical-align: middle; padding-left: 3mm;"><b>London<br>Churchill<br>College</b></td>
        </tr></table>
    </td>
    <td class="ttl">
        <span class="lbl">Class timetable</span>
        <h1 style="font-size: {{ $titleSize }}pt;">{{ $routine['course'] }}</h1>
        <p>{{ $termLine }}</p>
    </td>
    <td class="grp">
        <span class="lbl">Group</span>
        <span class="g">{{ $routine['group']['name'] }}</span>
        <span class="sz">{{ $routine['group']['size'] }} {{ $routine['group']['size'] === 1 ? 'student' : 'students' }}</span>
        @if($routine['group']['leaders'])
            <span class="sz">Group leader · {{ implode(', ', $routine['group']['leaders']) }}</span>
        @endif
    </td>
</tr></table>

<div class="meta block">
    <table><tr>
        <td class="first"><span class="lbl">Term</span><b>{{ $routine['term']['name'] }}{{ $routine['term']['code'] ? ' ('.$routine['term']['code'].')' : '' }}</b></td>
        <td><span class="lbl">Level</span><b>{{ $routine['level'] ?: '—' }}</b></td>
        <td><span class="lbl">Awarding body</span><b>{{ $routine['awarding_body'] ?: '—' }}</b></td>
        <td><span class="lbl">Teaching weeks</span><b>{{ $teaching ?: '—' }}</b></td>
    </tr></table>
</div>

<div class="block">
    <table class="h2row"><tr>
        <td><h2>Weekly schedule</h2></td>
        <td class="key">
            @if($routine['has_campus'])<i style="background:#1d6b5f"></i>On campus{{ $routine['venue']['name'] ? ' · '.$routine['venue']['name'] : '' }}@endif
            @if($routine['has_online'])<i style="background:#b0821f"></i>Online{{ $routine['online_provider'] ? ' · '.$routine['online_provider'] : '' }}@endif
        </td>
    </tr></table>

    <table class="wk">
        <tr>
            <td style="width: 22mm;"></td><td class="gap"></td>
            @foreach($slots as $i => $slot)
                @if($i > 0)<td class="gap"></td><td style="width: 6mm;"></td><td class="gap"></td>@endif
                <td style="width: 2.2mm;"></td><td class="tm" style="width: {{ $slotWidth - 2.2 - 1 }}mm;">{{ $slot['start'] }} – {{ $slot['end'] }}</td>
            @endforeach
        </tr>
        @foreach($routine['days'] as $dayName => $day)
            <tr class="sp"><td colspan="{{ 2 + $slotCols }}"></td></tr>
            @if($day['type'] === 'classes')
                <tr>
                    <td class="day"><b>{{ $dayName }}</b></td><td class="gap"></td>
                    @foreach($slots as $i => $slot)
                        @if($i > 0)
                            <td class="gap"></td><td class="brk">B<br>R<br>E<br>A<br>K</td><td class="gap"></td>
                        @endif
                        @php $cell = $day['cells'][$i]; $first = $cell[0] ?? null; @endphp
                        @if(!$first)
                            <td class="bar empty"></td><td class="s empty" style="width: {{ $cardWidth }}mm;"></td>
                        @else
                            <td class="bar {{ $first['mode'] }}"></td>
                            <td class="s {{ $first['mode'] }}" style="width: {{ $cardWidth }}mm;">
                                @foreach($cell as $k => $s)
                                    <div class="{{ $k > 0 ? 'more' : '' }}">
                                        <table class="s-top"><tr>
                                            <td><span class="kind">{{ $s['kind'] }}</span></td>
                                            <td style="text-align: right;"><span class="mode">{{ $s['mode'] === 'online' ? 'Online' : 'Campus' }}</span></td>
                                        </tr></table>
                                        <span class="s-t">{{ $s['title'] }}</span>
                                        @php
                                            $shown = $s['link'] ? preg_replace('#^https?://(www\.)?#i', '', $s['link']) : '';
                                            $shown = mb_strlen($shown) > 34 ? mb_substr($shown, 0, 33).'…' : $shown;
                                        @endphp
                                        <table class="kv" style="width: {{ $cardWidth }}mm;">
                                            @if($s['mode'] === 'online' && $s['link'])
                                                {{-- the link gets the card's full width, on one line --}}
                                                <tr><td class="k" colspan="2">Class link</td></tr>
                                                <tr><td class="link" colspan="2"><a href="{{ $s['link'] }}">{{ $shown }}</a></td></tr>
                                            @elseif($s['room'])
                                                <tr><td class="k">Room</td><td>{{ $s['room'] }}</td></tr>
                                            @endif
                                            @if($s['person'])
                                                <tr><td class="k">{{ $s['person_label'] }}</td><td>{{ $s['person'] }}</td></tr>
                                            @endif
                                        </table>
                                    </div>
                                @endforeach
                            </td>
                        @endif
                    @endforeach
                </tr>
            @else
                <tr>
                    <td class="day"><b>{{ $dayName }}</b></td><td class="gap"></td>
                    <td colspan="{{ $slotCols }}">
                        @if($day['type'] === 'support')
                            <table class="flexrow"><tr><td class="fbar"></td><td class="flex"><b>Independent study · flexible tutor-led academic support</b><span>{{ $supportHours }} · pre-book your weekly support hours<sup>*</sup></span></td></tr></table>
                        @else
                            <table class="flexrow"><tr><td class="fbar quiet"></td><td class="flex quiet"><b>Independent study</b></td></tr></table>
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </table>
</div>

<table class="btm block"><tr>
    <td style="width: 90mm;">
        <h2>Teaching weeks</h2>
        <table class="weeks">
            <tr><th class="n">Wk</th><th class="d">Week commencing</th><th class="c">Lecture</th><th class="c">Workshop</th><th class="c">Tutorial</th></tr>
            @foreach($routine['weeks'] as $week)
                <tr>
                    <td class="n mono">{{ str_pad($week['n'], 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="d">{{ $date($week['commencing'], 'j M Y') }}@if($week['revision'])<span class="rev">Revision</span>@endif</td>
                    @foreach(['lecture', 'workshop', 'tutorial'] as $kind)
                        <td class="c">@if($week[$kind])<img src="{{ $tick }}" alt="Yes">@else<span class="no">–</span>@endif</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </td>
    <td style="width: 5mm;"></td>
    <td style="width: 91mm;">
        <h2>Assessment deadlines</h2>
        @forelse($routine['assessments'] as $n => $as)
            <div class="as">
                <table><tr>
                    <td class="u"><div class="unit">{{ $n + 1 }}</div></td><td class="g"></td>
                    <td class="t"><b>{{ $as['module'] }}</b><small>{{ implode(' · ', array_filter([$as['lecturer'], $as['code']])) }}</small></td><td class="g"></td>
                    <td class="cl"><div class="cal">@if($as['date'])<small>{{ $date($as['date'], 'M') }}</small><b>{{ $date($as['date'], 'j') }}</b>@else<small>TBC</small><b>–</b>@endif</div></td><td class="g"></td>
                    <td class="cal-d">@if($as['date']){{ $date($as['date'], 'l') }}<small>{{ $date($as['date'], 'j F Y') }}</small>@else To be confirmed<small>Your tutor will confirm</small>@endif</td>
                </tr></table>
            </div>
        @empty
            <div class="as"><span class="ln">No assessment deadlines are set for this term yet.</span></div>
        @endforelse
        <div class="note"><sup>*</sup> <b>Mandatory {{ config('class_routine.support_hours_per_week', 4) }} hours of weekly academic support</b> are available{{ $supportDays ? ' on '.$plural($supportDays) : '' }} ({{ $supportHours }}), subject to pre-booking. Times are flexible.</div>
    </td>
</tr></table>

</body>
</html>
