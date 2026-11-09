{{--
    Class routine (ICP) PDF, the detailed term timetable the user sent (SEPT26-HM-C_Timetable-6.pdf,
    30 Oct 2026): page 1 the week at a glance, then one page per week of the term with its real
    class dates, the revision week and the hand-in deadlines. Nothing on it is about one student,
    so a group shares one PDF (ClassRoutineGroupPdf). Data: App\Services\ClassRoutine\ClassRoutineBuilder.
    DomPDF: fixed-width tables (186mm content width), no border-radius on cells, fonts embedded
    from resources/fonts/audit-certificate. The one-page design before it is pdf.blade.php.
--}}
@php
    $fontDir = resource_path('fonts/audit-certificate');
    $e = fn ($value) => e((string) $value);
    $date = fn ($value, $format) => $value ? \Carbon\Carbon::parse($value)->format($format) : '';

    $slots = $routine['slots'];
    $slotCount = max(1, count($slots));
    // day label 21mm, then a 2mm gap before each slot column
    $slotWidth = round((186 - 21 - 2 * $slotCount) / $slotCount, 2);

    $termShort = preg_replace('/^(\w{3})\w*\s+(\d{4})$/', '$1 $2', $routine['term']['name']) ?: $routine['term']['name'];
    $supportHours = str_replace(' ', '', (string) config('class_routine.support_hours', '10:00 – 16:00'));
    $supportHoursSpaced = (string) config('class_routine.support_hours', '10:00 – 16:00');
    $weeks = $routine['calendar'] ?? [];
    $weekCount = count($weeks);
    $assessments = $routine['assessments'];
    $dated = collect($assessments)->pluck('date')->filter()->sort()->values();
    $deadlineRange = $dated->isEmpty() ? null : ($dated->first() === $dated->last()
        ? $date($dated->first(), 'j M')
        : ($date($dated->first(), 'M') === $date($dated->last(), 'M') ? $date($dated->first(), 'j').'–'.$date($dated->last(), 'j M') : $date($dated->first(), 'j M').' – '.$date($dated->last(), 'j M')));
    $revisionWeeks = collect($weeks)->where('revision', true)->pluck('n')->values();
    $lastRevision = $revisionWeeks->last();
    $address = $routine['venue']['address'] ?? '';

    // what a class is called on a card, and its colour
    $category = fn ($c) => $c['kind'] === 'Workshop' ? 'ws' : ($c['mode'] === 'online' ? 'on' : 'ca');
    $cardLabel = fn ($c) => $c['kind'] === 'Workshop' ? ($c['mode'] === 'online' ? 'Online workshop' : 'Workshop')
        : (($c['mode'] === 'online' ? 'Online ' : 'Campus ').strtolower($c['kind']));
    $provider = fn ($c) => str_contains((string) $c['link'], 'meet.google.com') ? 'Google Meet' : (str_contains((string) $c['link'], 'zoom') ? 'Zoom' : (str_contains((string) $c['link'], 'teams') ? 'Microsoft Teams' : 'Online'));
    $bareLink = fn ($link) => preg_replace('#^https?://(www\.)?#i', '', rtrim((string) $link, '/'));
    $dayMode = function ($classes) {
        $modes = collect($classes)->pluck('mode')->unique();

        return $modes->count() > 1 ? 'mixed' : ($modes->first() ?: 'campus');
    };
    $plainDays = ['Monday' => 'Mon', 'Tuesday' => 'Tue', 'Wednesday' => 'Wed', 'Thursday' => 'Thu', 'Friday' => 'Fri', 'Saturday' => 'Sat', 'Sunday' => 'Sun'];
    $onlineDays = $routine['online_days'] ?? [];
    $onlineDaysText = $onlineDays ? implode(' & ', $onlineDays) : '';
    $campusLine = trim(implode(', ', array_filter([implode(' & ', $routine['campus_rooms'] ?? []), $routine['campus_place'] ?? null])));
    $camera = 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><rect x="2.5" y="6.5" width="13" height="11" rx="2" fill="none" stroke="#ffffff" stroke-width="1.8"/><path d="M15.5 10.5 21 7.5v9l-5.5-3" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linejoin="round"/></svg>');
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Term timetable — {{ $routine['course'] }} · {{ $routine['group']['name'] }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }

    @page { size: A4; margin: 11mm 12mm 15mm 12mm; }
    * { box-sizing: border-box; }
    /* DomPDF line boxes are line-height × 1.434 for Hanken Grotesk, so the design's ~1.3 is 0.9 here */
    body { margin: 0; font-family: 'Hanken Grotesk', 'DejaVu Sans', sans-serif; font-size: 8.6pt; line-height: 0.9; color: #1f2533; background: #fff; }
    table { border-collapse: separate; border-spacing: 0; }
    td { vertical-align: top; padding: 0; }
    p { margin: 0; }
    .page { page-break-before: always; }
    .navy { color: #1f3a6e; }
    .muted { color: #5b6472; }

    /* header */
    .hd { width: 186mm; }
    .hd td { vertical-align: middle; }
    .brand { width: 60mm; }
    .brand img { width: 8.6mm; height: auto; vertical-align: middle; }
    .brand span { display: inline-block; vertical-align: middle; margin-left: 1.6mm; font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 6.6pt; line-height: 0.66; letter-spacing: 0.3pt; color: #b4142e; text-transform: uppercase; }
    .hd .right { text-align: right; }
    .eyebrow { color: #b4142e; font-size: 7.6pt; font-weight: 700; letter-spacing: 0.9pt; text-transform: uppercase; }
    .rule { height: 0.5pt; background: #d7dbe2; margin: 3mm 0 3.6mm; }
    .rule-navy { height: 1.4pt; background: #1f3a6e; margin: 2.4mm 0 3.6mm; }

    /* page 1 */
    h1 { margin: 0 0 2.6mm; font-size: 19.5pt; font-weight: 700; color: #1f3a6e; letter-spacing: -0.3pt; line-height: 0.82; }
    .chips td { padding-right: 2mm; }
    .chip { background: #f1f3f6; border-radius: 1.6mm; padding: 1.6mm 2.6mm; font-size: 8pt; color: #5b6472; white-space: nowrap; }
    .chip b { color: #1f2533; font-weight: 700; margin-left: 0.8mm; }
    .h2 { width: 186mm; margin: 4.2mm 0 1.8mm; }
    .h2 td { vertical-align: bottom; }
    .h2 .t { font-size: 10.5pt; font-weight: 700; color: #1f2533; }
    .key { text-align: right; font-size: 7.4pt; color: #3b4352; }
    .key i { display: inline-block; width: 2.2mm; height: 2.2mm; border-radius: 1.1mm; margin: 0 1mm 0 3.2mm; vertical-align: middle; }

    .grid { width: 186mm; }
    .grid .dl { width: 21mm; vertical-align: middle; }
    .grid .dl b { display: block; font-size: 10pt; font-weight: 700; color: #1f2533; }
    .grid .dl span { display: block; font-size: 7pt; font-weight: 600; margin-top: 0.3mm; }
    .grid .g { width: 2mm; }
    .grid .sp td { height: 1.8mm; }
    .tpill { background: #1f3a6e; color: #fff; border-radius: 1.6mm; text-align: center; font-weight: 700; font-size: 8.4pt; padding: 1.6mm 0; }
    .card { height: 17.2mm; border-radius: 1.8mm; padding: 1.7mm 2.4mm 0; overflow: hidden; }
    .card .lb { font-size: 6.6pt; font-weight: 700; letter-spacing: 0.6pt; text-transform: uppercase; }
    .card .tt { font-size: 8.7pt; font-weight: 700; color: #1f2533; margin-top: 0.7mm; line-height: 0.86; }
    .card .mt { font-size: 7.3pt; color: #5b6472; margin-top: 1mm; }
    .card + .card { margin-top: 1.2mm; }
    .on { background: #eef3fb; border: 0.6pt solid #c9d7ef; } .on .lb { color: #2d5ba8; }
    .ca { background: #fdeef1; border: 0.6pt solid #f2c5ce; } .ca .lb { color: #b4142e; }
    .ws { background: #fff7e2; border: 0.6pt solid #f0d99c; } .ws .lb { color: #9a6a00; }
    .none { height: 11.4mm; border-radius: 1.8mm; border: 0.7pt dashed #c9ced6; text-align: center; color: #8a92a0; font-size: 7.6pt; padding-top: 7.5mm; }
    .supp { background: #f3f4f6; border: 0.6pt solid #e2e5ea; border-radius: 1.8mm; padding: 2mm 2.6mm; }
    .supp td { vertical-align: middle; }
    .supp .tx { font-size: 8.3pt; color: #3b4352; }
    .pre { display: inline-block; border: 0.6pt solid #e7a2af; color: #b4142e; background: #fff; border-radius: 2mm; padding: 0.4mm 1.6mm; font-size: 7.2pt; font-weight: 600; }
    .hrs { display: inline-block; background: #fff; border: 0.6pt solid #d7dbe2; border-radius: 2.4mm; padding: 0.8mm 2.4mm; font-size: 7.8pt; font-weight: 700; color: #1f2533; white-space: nowrap; }
    .banner { width: 186mm; margin-top: 2.6mm; background: #1f3a6e; border-radius: 1.8mm; }
    .banner td { padding: 2.2mm 3.2mm; color: #fff; vertical-align: middle; font-size: 8.3pt; }
    .banner img { width: 4.2mm; height: auto; vertical-align: middle; margin-right: 1.8mm; }
    .banner b { font-weight: 700; }
    .banner .rt { text-align: right; color: #c8d3ea; font-size: 7.6pt; }

    .panels { width: 186mm; margin-top: 3.4mm; }
    /* a div's width here is its content box: 91mm less 2 × 3.4mm padding and the 0.6pt borders */
    .panel { width: 83.8mm; border: 0.6pt solid #e2e5ea; border-radius: 2mm; padding: 3mm 3.4mm; }
    .panel .pt { font-size: 9.8pt; font-weight: 700; color: #1f2533; }
    .panel .ps { font-size: 7.3pt; color: #5b6472; margin: 0.6mm 0 2.4mm; }
    .tiles td { padding: 0 1.6mm 1.6mm 0; }
    .tile { width: 23.2mm; background: #eef3fb; border: 0.6pt solid #d3def0; border-radius: 1.4mm; padding: 1.2mm 1.8mm; }
    .tile span { display: block; font-size: 6.8pt; color: #2d5ba8; font-weight: 600; }
    .tile b { display: block; font-size: 8.6pt; color: #1f2533; font-weight: 700; margin-top: 0.3mm; }
    .tile.rev { background: #fff7e2; border-color: #f0d99c; } .tile.rev span { color: #9a6a00; }
    .tile.dl { background: #b4142e; border-color: #b4142e; } .tile.dl span, .tile.dl b { color: #fff; }
    .revnote { font-size: 7.2pt; color: #3b4352; margin-top: 0.6mm; }
    .revnote i { display: inline-block; width: 2.6mm; height: 2.6mm; background: #fff7e2; border: 0.6pt solid #f0d99c; border-radius: 0.6mm; vertical-align: middle; margin-right: 1.2mm; }
    .due td { vertical-align: middle; padding-bottom: 2.4mm; }
    .badge { width: 10mm; border: 0.6pt solid #f2c5ce; border-radius: 1.4mm; text-align: center; overflow: hidden; }
    .badge .m { background: #b4142e; color: #fff; font-size: 6.4pt; font-weight: 700; letter-spacing: 0.5pt; padding: 0.5mm 0; }
    .badge .d { font-size: 11pt; font-weight: 700; color: #1f2533; padding: 0.7mm 0 0.8mm; line-height: 0.8; }
    .due .mod { padding-left: 3mm; font-size: 8.5pt; font-weight: 700; color: #1f2533; }
    .due .mod span { display: block; font-size: 7.3pt; font-weight: 400; color: #5b6472; margin-top: 0.4mm; }

    /* week pages */
    .wtitle { width: 186mm; }
    .wtitle td { vertical-align: bottom; }
    .wtitle .big { font-size: 26pt; font-weight: 700; color: #1f3a6e; letter-spacing: -0.6pt; line-height: 0.8; }
    .pill { display: inline-block; border-radius: 2.4mm; padding: 0.8mm 2.4mm; font-size: 7.6pt; font-weight: 700; vertical-align: middle; margin-left: 2mm; }
    .pill.teach { background: #e6edf8; color: #1f3a6e; }
    .pill.rev { background: #fdeccb; color: #8a5a00; }
    .wtitle .of { text-align: right; font-size: 8pt; font-weight: 700; color: #1f2533; padding-bottom: 1.4mm; }
    .wstart { display: inline-block; background: #1f3a6e; color: #fff; border-radius: 1.6mm; padding: 1.4mm 3mm; font-size: 8.6pt; font-weight: 700; }
    .legend { width: 186mm; margin-bottom: 3.2mm; }
    .lg { border-radius: 1.8mm; padding: 2mm 2.6mm; font-size: 8pt; color: #3b4352; }
    .lg td { vertical-align: middle; }
    .lg.on { background: #eef3fb; border: 0; } .lg.ca { background: #fdeef1; border: 0; }
    .tag { display: inline-block; border-radius: 1mm; padding: 0.7mm 1.6mm; color: #fff; font-size: 6.9pt; font-weight: 700; letter-spacing: 0.4pt; white-space: nowrap; }
    .tag.on { background: #2d5ba8; border: 0; } .tag.ca { background: #b4142e; border: 0; }
    .dcard { width: 186mm; border-radius: 2mm; margin-bottom: 2.6mm; overflow: hidden; }
    .dcard.on { background: #fff; border: 0.7pt solid #c9d7ef; }
    .dcard.ca, .dcard.mixed { background: #fff; border: 0.7pt solid #f2c5ce; }
    .dhead { padding: 2mm 3.2mm; }
    .dcard.on .dhead { background: #eef3fb; }
    .dcard.ca .dhead, .dcard.mixed .dhead { background: #fdeef1; }
    .dhead td { vertical-align: middle; }
    .dhead .dn { font-size: 10.6pt; font-weight: 700; color: #1f2533; }
    .dhead .dd { font-size: 8.6pt; font-weight: 600; color: #5b6472; margin-left: 1.4mm; margin-right: 2mm; }
    .dhead .note { text-align: right; font-size: 7.6pt; color: #5b6472; }
    .crow { width: 100%; }
    .crow td { padding: 2.1mm 3.2mm; border-top: 0.6pt solid #e7eaef; vertical-align: top; }
    .crow .tm { width: 25mm; }
    .crow .tm b { display: block; font-size: 10.4pt; font-weight: 700; color: #1f2533; }
    .crow .tm span { display: block; font-size: 7.4pt; color: #5b6472; margin-top: 0.2mm; }
    .crow .wh b { display: block; font-size: 9pt; font-weight: 700; color: #1f2533; }
    .crow .wh span { display: block; font-size: 7.6pt; color: #5b6472; margin-top: 0.5mm; }
    .crow .pl { width: 62mm; }
    .crow .pl b { display: block; font-size: 8pt; font-weight: 700; }
    .crow .pl span { display: block; font-size: 7.6pt; color: #3b4352; margin-top: 0.4mm; }
    .crow .pl a { color: #2d5ba8; text-decoration: underline; font-size: 8pt; font-weight: 600; }
    .crow.off .tm b, .crow.off .wh b { color: #9aa1ad; text-decoration: line-through; }
    .crow .pl .off-tag { display: inline-block; margin-top: 0; }
    .off-tag { display: inline-block; border: 0.6pt solid #e7a2af; color: #b4142e; border-radius: 1mm; padding: 0.5mm 1.4mm; font-size: 6.8pt; font-weight: 700; letter-spacing: 0.4pt; }
    .nocls { width: 186mm; background: #f3f4f6; border: 0.6pt solid #e2e5ea; border-radius: 2mm; margin-bottom: 2.6mm; }
    .nocls td { padding: 2.4mm 3.2mm; vertical-align: middle; }
    .nocls .dn { width: 26mm; }
    .nocls .dn b { display: block; font-size: 10pt; font-weight: 700; color: #1f2533; }
    .nocls .dn span { display: block; font-size: 8pt; font-weight: 600; color: #5b6472; margin-top: 0.3mm; }
    .nocls .tx { font-size: 8.2pt; color: #3b4352; }
    .nocls .tx b { color: #1f2533; }
    .nocls .ncp { width: 27mm; text-align: right; }
    .ncp span { display: inline-block; background: #fff; border: 0.6pt solid #d7dbe2; border-radius: 1mm; padding: 0.7mm 1.8mm; font-size: 7pt; font-weight: 700; color: #3b4352; letter-spacing: 0.3pt; }

    /* revision + deadlines */
    .revbox { width: 177.3mm; background: #fff8e6; border: 0.7pt solid #f0d99c; border-radius: 2.2mm; padding: 3.4mm 4mm; margin-bottom: 4mm; }
    .revbox .ey { font-size: 7.4pt; font-weight: 700; letter-spacing: 0.8pt; color: #8a5a00; text-transform: uppercase; }
    .revbox .rt { font-size: 13pt; font-weight: 700; color: #1f2533; margin: 1mm 0 1.4mm; }
    .revbox .rx { font-size: 8.8pt; color: #3b4352; line-height: 0.95; }
    .revbox .tbc { background: #fff; border: 0.6pt solid #f0d99c; border-radius: 1.6mm; padding: 2mm 3mm; margin-top: 2.6mm; font-size: 8.4pt; color: #3b4352; }
    .revbox .tbc b { color: #1f2533; margin-right: 2mm; }
    .h3 { font-size: 13pt; font-weight: 700; color: #1f3a6e; margin: 1mm 0 0.6mm; }
    .h3s { font-size: 8pt; color: #5b6472; margin-bottom: 2.4mm; }
    .hand { width: 186mm; border: 0.7pt solid #f2c5ce; border-radius: 2mm; margin-bottom: 2.4mm; }
    .hand td { padding: 2.6mm 3.4mm; vertical-align: middle; }
    .hand .badge { width: 12.5mm; }
    .hand .badge .d { font-size: 14pt; padding: 0.9mm 0 0.2mm; }
    .hand .badge .w { font-size: 6.8pt; color: #5b6472; font-weight: 600; padding-bottom: 0.8mm; }
    .hand .mod { padding-left: 4mm; font-size: 11pt; font-weight: 700; color: #1f2533; }
    .hand .mod span { display: block; font-size: 8.2pt; font-weight: 400; color: #5b6472; margin-top: 0.6mm; }
    .help { width: 178.6mm; background: #f3f4f6; border: 0.6pt solid #e2e5ea; border-radius: 2mm; padding: 2.8mm 3.4mm; font-size: 8.4pt; color: #3b4352; margin-top: 1.6mm; }
    .help b { color: #1f2533; }
    .foot { position: fixed; bottom: -8.5mm; left: 0; width: 186mm; border-top: 0.5pt solid #d7dbe2; padding-top: 2mm; font-size: 7.4pt; color: #5b6472; }
</style>
</head>
<body>

{{-- ═════ page 1 · the week at a glance ═════ --}}
@include('pages.students.class-routine.partials.term-pdf-header', ['right' => '<span class="eyebrow">Term timetable</span>&nbsp;&nbsp;<span style="font-size:10.5pt;font-weight:600;color:#1f2533">'.$e($termShort).'</span>'])
<div class="rule"></div>

<h1>{{ $routine['course'] }}</h1>
<table class="chips"><tr>
    <td><div class="chip">Group<b>{{ $routine['group']['name'] }}</b></div></td>
    @if($routine['level'])<td><div class="chip">{{ preg_replace('/\s*\d+$/', '', $routine['level']) }}<b>{{ preg_replace('/^\D+/', '', $routine['level']) }}</b></div></td>@endif
    @if($routine['awarding_body'])<td><div class="chip">Awarding body<b>{{ $routine['awarding_body'] }}</b></div></td>@endif
    @if($routine['group']['leaders'])<td><div class="chip">Group leader<b>{{ implode(', ', $routine['group']['leaders']) }}</b></div></td>@endif
</tr></table>

<table class="h2"><tr>
    <td class="t">Your week</td>
    <td class="key">
        @if($routine['has_online'])<i style="background:#2d5ba8"></i>Online tutorial @endif
        @if($routine['has_campus'])<i style="background:#b4142e"></i>Campus lecture @endif
        <i style="background:#c08a00"></i>Workshop
        <i style="background:#9aa1ad"></i>Support / study
    </td>
</tr></table>

<table class="grid">
    <tr>
        <td class="dl"></td>
        @foreach($slots as $slot)
            <td class="g"></td>
            <td style="width: {{ $slotWidth }}mm"><div class="tpill">{{ $slot['start'] }} – {{ $slot['end'] }}</div></td>
        @endforeach
    </tr>
    @foreach($routine['days'] as $dayName => $day)
        <tr class="sp"><td colspan="{{ 1 + 2 * $slotCount }}"></td></tr>
        @php
            $all = collect($day['cells'] ?? [])->flatten(1);
            $mode = $day['type'] === 'classes' ? $dayMode($all) : $day['type'];
            $sub = ['online' => ['Online', '#2d5ba8'], 'campus' => ['Campus', '#b4142e'], 'mixed' => ['Online & campus', '#1f3a6e'], 'support' => ['Support', '#6b7380'], 'study' => ['Study', '#6b7380']][$mode] ?? ['', '#6b7380'];
        @endphp
        <tr>
            <td class="dl"><b>{{ $plainDays[$dayName] ?? $dayName }}</b><span style="color: {{ $sub[1] }}">{{ $sub[0] }}</span></td>
            @if($day['type'] === 'classes')
                @foreach($day['cells'] as $cell)
                    <td class="g"></td>
                    <td style="width: {{ $slotWidth }}mm">
                        @forelse($cell as $c)
                            <div class="card {{ $category($c) }}">
                                <div class="lb">{{ $cardLabel($c) }}</div>
                                <div class="tt">{{ $c['title'] }}</div>
                                <div class="mt">{{ $c['mode'] === 'online' ? $provider($c) : ($c['room_label'] ?: 'On campus') }}@if($c['person']) · {{ $c['person'] }}@endif</div>
                            </div>
                        @empty
                            <div class="none">No class</div>
                        @endforelse
                    </td>
                @endforeach
            @else
                <td class="g"></td>
                <td colspan="{{ 2 * $slotCount - 1 }}">
                    <div class="supp"><table style="width:100%"><tr>
                        <td class="tx">
                            @if($day['type'] === 'support')
                                Tutor-led academic support <span class="pre">must be pre-booked</span> (in person or online) or independent study
                            @else
                                Independent study
                            @endif
                        </td>
                        <td style="text-align:right;width:26mm"><span class="hrs">{{ $day['type'] === 'support' ? $supportHoursSpaced : 'All day' }}</span></td>
                    </tr></table></div>
                </td>
            @endif
        </tr>
    @endforeach
</table>

@if($routine['shared_link'] || $routine['break_minutes'])
    <table class="banner"><tr>
        <td>
            @if($routine['shared_link'])
                <img src="{{ $camera }}" alt="">{{ $onlineDaysText }} online classes · join with one link: <b>{{ $bareLink($routine['shared_link']) }}</b>
            @elseif($routine['has_online'])
                <img src="{{ $camera }}" alt="">Online classes · join with the link shown for each class
            @endif
        </td>
        @if($routine['break_minutes'])<td class="rt">{{ $routine['break_minutes'] }}-min breaks between sessions</td>@endif
    </tr></table>
@endif

@php
    $tiles = collect($weeks)->map(fn ($w) => ['n' => $w['n'], 'date' => $w['commencing'], 'rev' => $w['revision']])->all();
    if ($deadlineRange) { $tiles[] = ['n' => null, 'date' => null, 'rev' => false]; }
    $tileRows = array_chunk($tiles, 3);
    $leftHeight = 15 + count($tileRows) * 12.4 + ($revisionWeeks->isNotEmpty() ? 5 : 0);
    $rightHeight = 15 + max(1, count($assessments)) * 12.6;
    $panelHeight = max($leftHeight, $rightHeight);
@endphp
<table class="panels"><tr>
    <td style="width: 91mm">
        <div class="panel" style="height: {{ $panelHeight }}mm">
            <div class="pt">Term at a glance</div>
            <div class="ps">Each week starts on a Monday</div>
            <table class="tiles">
                @foreach($tileRows as $row)
                    <tr>
                        @foreach($row as $tile)
                            <td>
                                @if($tile['n'] === null)
                                    <div class="tile dl"><span>Deadlines</span><b>{{ $deadlineRange }}</b></div>
                                @else
                                    <div class="tile{{ $tile['rev'] ? ' rev' : '' }}"><span>Week {{ $tile['n'] }}</span><b>{{ $date($tile['date'], 'D j M') }}</b></div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </table>
            @if($revisionWeeks->isNotEmpty())
                <div class="revnote"><i></i><b>Week {{ $revisionWeeks->implode(', ') }}</b> {{ $revisionWeeks->count() > 1 ? 'are' : 'is' }} revision &amp; formative feedback</div>
            @endif
        </div>
    </td>
    <td style="width: 4mm"></td>
    <td style="width: 91mm">
        <div class="panel" style="height: {{ $panelHeight }}mm">
            <div class="pt">Assessment deadlines</div>
            <div class="ps">Submit by the date shown</div>
            <table class="due" style="width:100%">
                @forelse($assessments as $a)
                    <tr>
                        <td style="width: 10mm">
                            <div class="badge"><div class="m">{{ $a['date'] ? strtoupper($date($a['date'], 'M')) : 'TBC' }}</div><div class="d">{{ $a['date'] ? $date($a['date'], 'j') : '–' }}</div></div>
                        </td>
                        <td class="mod">{{ $a['module'] }}<span>{{ $a['date'] ? $date($a['date'], 'l') : 'Date to be confirmed' }}</span></td>
                    </tr>
                @empty
                    <tr><td class="mod" style="padding-left:0">No assessment deadlines yet.</td></tr>
                @endforelse
            </table>
        </div>
    </td>
</tr></table>

{{-- ═════ one page per week of the term ═════ --}}
@foreach($weeks as $week)
    @php
        $isRevision = $week['revision'];
        $weekModes = collect($week['days'])->flatMap(fn ($d) => $d['classes'])->where('cancelled', false)->pluck('mode')->unique();
        $isLast = $week['n'] === ($lastRevision ?? null);
    @endphp
    <div class="page">
        @include('pages.students.class-routine.partials.term-pdf-header', ['right' => '<b style="font-size:9pt;color:#1f2533">'.$e($routine['course']).'</b><br><span class="muted" style="font-size:8pt">Group '.$e($routine['group']['name']).' · '.$e($termShort).'</span>'])
        <div style="height: 4mm"></div>
        <table class="wtitle"><tr>
            <td><span class="big">Week {{ $week['n'] }}</span><span class="pill {{ $isRevision ? 'rev' : 'teach' }}">{{ $isRevision ? 'Revision week' : 'Teaching week' }}</span></td>
            <td style="text-align:right">
                <div class="of">Week {{ $week['n'] }} of {{ $weekCount }}</div>
                <span class="wstart">Week starting: {{ $date($week['commencing'], 'D j M Y') }}</span>
            </td>
        </tr></table>
        <div class="rule-navy"></div>

        @if($isRevision)
            <div class="revbox">
                <div class="ey">What happens this week</div>
                <div class="rt">Revision &amp; formative feedback</div>
                <div class="rx">
                    @if($week['live_classes'] > 0)
                        This week is for going over what you have learned and getting feedback on your work before you hand in your assessments. Your sessions this week are below.
                    @else
                        No new teaching. This week is for going over what you have learned and getting feedback on your work before you hand in your assessments.
                    @endif
                </div>
                @if($week['live_classes'] === 0)
                    <div class="tbc"><b>Session times &amp; place:</b> [to be confirmed]</div>
                @endif
            </div>
        @endif

        @if(!$isRevision || $week['live_classes'] > 0)
            @if($weekModes->isNotEmpty())
                <table class="legend"><tr>
                    @if($weekModes->contains('online'))
                        <td style="width: {{ $weekModes->count() > 1 ? '92mm' : '186mm' }}">
                            <div class="lg on"><table><tr><td style="width:17mm"><span class="tag on">ONLINE</span></td><td>Join from home on {{ $routine['online_provider'] ?: 'the online platform' }} using the link shown</td></tr></table></div>
                        </td>
                    @endif
                    @if($weekModes->count() > 1)<td style="width: 2mm"></td>@endif
                    @if($weekModes->contains('campus'))
                        <td style="width: {{ $weekModes->count() > 1 ? '92mm' : '186mm' }}">
                            <div class="lg ca"><table><tr><td style="width:22mm"><span class="tag ca">ON CAMPUS</span></td><td>Come to college{{ $campusLine !== '' ? ': '.$campusLine : '' }}</td></tr></table></div>
                        </td>
                    @endif
                </tr></table>
            @endif

            @foreach($week['days'] as $day)
                @if($day['type'] === 'classes')
                    @php
                        $live = collect($day['classes'])->where('cancelled', false);
                        $mode = $dayMode($live->isNotEmpty() ? $live : $day['classes']);
                        $sameLink = $live->count() > 1 && $live->every(fn ($c) => $c['mode'] === 'online') && $live->pluck('link')->filter()->unique()->count() === 1;
                        $note = $live->isEmpty() ? 'Cancelled this week'
                            : ($mode === 'online' ? $live->count().' '.($live->count() === 1 ? 'class' : 'classes').($sameLink ? ' · same link for all' : '')
                            : ($live->count() === 1 ? 'Class at '.$live->first()['start'] : 'First class at '.$live->first()['start']));
                    @endphp
                    <div class="dcard {{ $mode === 'online' ? 'on' : ($mode === 'mixed' ? 'mixed' : 'ca') }}">
                        <div class="dhead"><table style="width:100%"><tr>
                            <td>
                                <span class="dn">{{ $day['name'] }}</span><span class="dd">{{ $date($day['date'], 'j M') }}</span>
                                @if(in_array($mode, ['online', 'mixed'], true))<span class="tag on">ONLINE</span>@endif
                                @if(in_array($mode, ['campus', 'mixed'], true))<span class="tag ca">ON CAMPUS</span>@endif
                            </td>
                            <td class="note">{{ $note }}</td>
                        </tr></table></div>
                        @foreach($day['classes'] as $c)
                            <table class="crow{{ $c['cancelled'] ? ' off' : '' }}"><tr>
                                <td class="tm"><b>{{ $c['start'] }}</b><span>to {{ $c['end'] }}</span></td>
                                <td class="wh"><b>{{ $c['title'] === 'All units' ? 'Workshop: all units' : $c['title'] }}</b><span>{{ $c['kind'] === 'Tutorial' && $c['mode'] === 'online' ? 'Online tutorial' : $c['kind'] }}@if($c['person']) · {{ $c['person_label'] }}: {{ $c['person'] }}@endif</span></td>
                                <td class="pl">
                                    @if($c['cancelled'])
                                        <span class="off-tag">CANCELLED</span><span>This class will not run.</span>
                                    @elseif($c['mode'] === 'online')
                                        <b style="color:#2d5ba8">Join on {{ $provider($c) }}</b>
                                        @if($c['link'])<a href="{{ $c['link'] }}">{{ $bareLink($c['link']) }}</a>@else<span>Link to follow</span>@endif
                                    @else
                                        <b style="color:#b4142e">{{ $c['room_label'] ?: 'On campus' }}</b>
                                        @if($c['address'])<span>{{ $c['address'] }}</span>@endif
                                    @endif
                                </td>
                            </tr></table>
                        @endforeach
                    </div>
                @else
                    <table class="nocls"><tr>
                        <td class="dn"><b>{{ $day['name'] }}</b><span>{{ $date($day['date'], 'j M') }}</span></td>
                        <td class="tx">
                            @if($day['type'] === 'support')
                                <b>No classes.</b> Optional tutor support {{ $supportHours }}, in person or online (book in advance). Otherwise, study on your own.
                            @else
                                <b>No classes.</b> Independent study.
                            @endif
                        </td>
                        <td class="ncp"><span>NO CLASSES</span></td>
                    </tr></table>
                @endif
            @endforeach
        @endif

        @php $ownPage = ($isLast && $week['live_classes'] > 0) || ($loop->last && $lastRevision === null); @endphp
        @if($isLast || ($loop->last && $lastRevision === null))
            @if($ownPage)
                </div><div class="page">
                @include('pages.students.class-routine.partials.term-pdf-header', ['right' => '<b style="font-size:9pt;color:#1f2533">'.$e($routine['course']).'</b><br><span class="muted" style="font-size:8pt">Group '.$e($routine['group']['name']).' · '.$e($termShort).'</span>'])
                <div style="height: 4mm"></div>
                <table class="wtitle"><tr><td><span class="big">Deadlines</span><span class="pill rev">Hand-in</span></td></tr></table>
                <div class="rule-navy"></div>
            @endif
            <div class="h3">Hand-in deadlines</div>
            <div class="h3s">Submit each assessment by the date below.</div>
            @forelse($assessments as $a)
                <table class="hand"><tr>
                    <td style="width: 12.5mm">
                        <div class="badge"><div class="m">{{ $a['date'] ? strtoupper($date($a['date'], 'M')) : 'TBC' }}</div><div class="d">{{ $a['date'] ? $date($a['date'], 'j') : '–' }}</div><div class="w">{{ $a['date'] ? $date($a['date'], 'D') : '' }}</div></div>
                    </td>
                    <td class="mod">{{ $a['module'] }}<span>Deadline: {{ $a['date'] ? $date($a['date'], 'l j F Y') : 'to be confirmed' }}</span></td>
                </tr></table>
            @empty
                <div class="help">No assessment deadlines have been set yet.</div>
            @endforelse
            <div class="help"><b>Need help?</b> Tutor support is available on {{ implode(' and ', array_map(fn ($d) => $d.'s', $routine['support_days'])) ?: 'weekdays' }}, {{ $supportHours }}, in person or online. You must book in advance.</div>
        @endif
    </div>
@endforeach

@if($address !== '')
    <div class="foot">{{ $address }}</div>
@endif
</body>
</html>
