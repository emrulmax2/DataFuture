@php
    /*
     * SLC Record Report PDF (user's slc-record-report PDF design, 25 Nov 2026): A4 landscape (1123 ×
     * 794 px at DomPDF's 96 dpi, 45px sides, so 1033px of content). The journey, the exits, then
     * every intake and course of the chosen record set. Data: SlcRecord::build(). Bars are divs with
     * px widths (no flex in DomPDF); the journey's arrows are border triangles.
     */
    use App\Services\Reports\SlcRecord;

    $fontDir = resource_path('fonts/audit-certificate');
    $n = fn ($v) => number_format((int) $v);
    $z = fn ($v) => (int) $v === 0 ? '<span class="zero">0</span>' : number_format((int) $v);
    $labels = array_map(fn ($i) => SlcRecord::label($i['name']), $intakes);
    $intakeList = count($labels) > 1 ? implode(', ', array_slice($labels, 0, -1)).' and '.end($labels).' intakes' : ($labels[0] ?? '').' intake';
    $T = array_fill(0, 8, 0);
    foreach ($intakes as $i) {
        foreach ($i['sets'][$set]['v'] as $k => $x) {
            $T[$k] += $x;
        }
    }
    $pc = fn ($a, $b) => $b ? number_format($a * 100 / $b, 1).'%' : '';
    $track = function (array $v, string $colour) {
        $w = 132;
        $fill = $v[0] ? max(0, min($w, (int) round($v[2] / $v[0] * $w))) : 0;

        return '<div class="track" style="width:'.$w.'px;">'.($fill ? '<div class="track__fill" style="width:'.$fill.'px;background:'.$colour.';"></div>' : '').'</div>';
    };
    $cells = function (array $v, string $colour) use ($z, $n, $pc, $track) {
        return '<td class="r">'.$z($v[0]).'</td><td class="r">'.$z($v[1]).'</td><td>'.$track($v, $colour).'</td><td class="r b">'.$z($v[2]).'</td>'
            .'<td class="r b">'.$pc($v[2], $v[0]).'</td><td class="r'.($v[7] ? ' low b' : '').'">'.$z($v[7]).'</td><td class="r">'.$z($v[3]).'</td>'
            .'<td class="r">'.$z($v[4]).'</td><td class="r">'.$z($v[5]).'</td><td class="r">'.$z($v[6]).'</td>';
    };
    $step2 = $T[1] === $T[0] ? 'Same as step 1' : ($T[1] > $T[0] ? $n($T[1] - $T[0]).' more than step 1' : '<b>'.$n($T[0] - $T[1]).' fewer</b> than step 1');
    $step3 = ($T[0] ? $pc($T[2], $T[0]).' of LCC registrations' : 'No LCC registrations in this set').($T[7] ? ' · <b>'.$n($T[7]).' not yet</b>' : '');
    $step4 = !$T[2] && !$T[3] ? 'No SLC-registered students' : ($T[3] === $T[2] ? 'Every SLC-registered student' : $pc($T[3], $T[2]).' of SLC registered'.($T[2] > $T[3] ? ' · <b>'.$n($T[2] - $T[3]).' not yet</b>' : ''));
    $odd = array_values(array_filter(array_map(fn ($i) => $i['sets'][$set]['v'][1] > $i['sets'][$set]['v'][0] ? SlcRecord::label($i['name']).' ('.$i['sets'][$set]['v'][1].' against '.$i['sets'][$set]['v'][0].')' : null, $intakes)));
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>SLC Record Report · {{ $intakeList }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 100px 45px 60px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 12px; line-height: 1.02; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 0; text-align: left; vertical-align: top; }

    /* Running header and footer (as the other report PDFs). */
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
    .head td { vertical-align: bottom; }
    .hint { color: #55616b; font-size: 11px; text-align: right; }
    .section { margin-top: 10px; }

    /* The journey: four steps, a triangle between them. */
    .journey { margin-top: 6px; }
    /* 4 steps × (218 + 28 padding + 2 border) + 3 arrows × 13 = 1031 of 1033. */
    .journey td.step { width: 218px; padding: 8px 14px 7px; border: 1px solid #dfe3e7; background: #f4f6f8; vertical-align: top; }
    .journey td.step.end { border-color: #cfe6e1; background: #e2f1ee; }
    .journey td.arrow { width: 13px; vertical-align: middle; }
    .tri { width: 0; height: 0; border-top: 11px solid transparent; border-bottom: 11px solid transparent; border-left: 9px solid #c3c9d1; margin-left: 2px; }
    .step__n { color: #55616b; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; }
    .step__k { font-size: 12px; font-weight: 600; margin-top: 3px; }
    .step__v { font-size: 26px; font-weight: 700; height: 31px; margin-top: 2px; }
    .step__s { color: #55616b; font-size: 10.5px; }
    .step__s b { color: #8a3d0c; }
    .end .step__n, .end .step__v { color: #0e5a54; }

    /* The exits. */
    .exits { margin-top: 8px; }
    .exits td { width: 25%; padding-right: 10px; }
    .exits td:last-child { padding-right: 0; }
    .exit { border: 1px solid #dfe3e7; border-radius: 8px; padding: 6px 12px 5px; }
    .exit.alert { border-color: #f1d9c2; background: #fbf3ea; }
    .exit__k { color: #55616b; font-size: 11px; font-weight: 600; }
    .exit__v { font-size: 18px; font-weight: 700; height: 22px; margin-top: 2px; }
    .exit__s { color: #55616b; font-size: 10px; }
    .exit.alert .exit__k, .exit.alert .exit__v { color: #8a3d0c; }

    /* By intake and course. Widths are content only: each cell adds 12px of padding (10 × 12 + 913 = 1033). */
    .rt { margin-top: 6px; }
    .rt th { border-bottom: 2px solid #16242b; color: #55616b; font-size: 10px; font-weight: 600; padding: 0 6px 4px; vertical-align: bottom; text-align: right; }
    .rt th:first-child { text-align: left; }
    .rt td { border-bottom: 1px solid #e3e7ea; font-size: 11px; padding: 4px 6px 3px; vertical-align: middle; }
    .rt .r { text-align: right; }
    .rt .b { font-weight: 700; }
    .rt tr.intake { background: #e6ecef; }
    .rt tr.intake td { border-bottom: 1px solid #c3c9d1; font-weight: 700; }
    .rt tr.total td { border-top: 2px solid #16242b; border-bottom: 2px solid #16242b; font-weight: 700; }
    .rt td.name { padding-left: 22px; font-weight: 600; }
    .rt tr.intake td.name, .rt tr.total td.name { padding-left: 6px; font-weight: 700; }
    .rt td.name small { display: block; color: #55616b; font-size: 9.5px; font-weight: 400; margin-top: 2px; }
    .rt td.empty { color: #55616b; font-size: 10.5px; padding-left: 22px; }
    .rt tr { page-break-inside: avoid; }
    .track { height: 10px; overflow: hidden; border-radius: 5px; background: #e9edf0; }
    .track__fill { height: 10px; border-radius: 5px; }
    .zero { color: #9aa3ab; font-weight: 400; }
    .low { color: #8a3d0c; }
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
                <td class="runhead__mid"><div class="runhead__title">SLC Record Report</div></td>
                <td class="runhead__meta">
                    <div class="runhead__term">{{ $intakeList }} · {{ $sets[$set] }}</div>
                    <div class="runhead__by">Generated {{ $generated }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="runfoot">
        <table>
            <tr>
                <td>London Churchill College · SLC Record Report · Confidential, internal use only</td>
                <td class="right"><span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

    <h1>SLC Record</h1>

    <table class="head section">
        <tr>
            <td><h2>The journey{{ count($intakes) > 1 ? ', all selected intakes' : '' }}</h2></td>
            <td class="hint">Students at each step</td>
        </tr>
    </table>
    <table class="journey">
        <tr>
            <td class="step"><div class="step__n">Step 1</div><div class="step__k">Registered at LCC</div><div class="step__v">{{ $n($T[0]) }}</div><div class="step__s">First registration on the LCC system, excluding discarded</div></td>
            <td class="arrow"><div class="tri"></div></td>
            <td class="step"><div class="step__n">Step 2</div><div class="step__k">Registered with awarding body</div><div class="step__v">{{ $n($T[1]) }}</div><div class="step__s">{!! $step2 !!}</div></td>
            <td class="arrow"><div class="tri"></div></td>
            <td class="step"><div class="step__n">Step 3</div><div class="step__k">SLC registered</div><div class="step__v">{{ $n($T[2]) }}</div><div class="step__s">{!! $step3 !!}</div></td>
            <td class="arrow"><div class="tri"></div></td>
            <td class="step end"><div class="step__n">Step 4</div><div class="step__k">SLC attendance confirmed</div><div class="step__v">{{ $n($T[3]) }}</div><div class="step__s">{!! $step4 !!}</div></td>
        </tr>
    </table>
    <table class="exits">
        <tr>
            <td><div class="exit{{ $T[7] ? ' alert' : '' }}"><div class="exit__k">Not yet SLC registered</div><div class="exit__v">{{ $n($T[7]) }}</div><div class="exit__s">Registered at LCC but not with SLC</div></div></td>
            <td><div class="exit"><div class="exit__k">SLC withdrawn</div><div class="exit__v">{{ $n($T[4]) }}</div><div class="exit__s">Withdrawn on the SLC record</div></div></td>
            <td><div class="exit"><div class="exit__k">Withdrawn or intermittent</div><div class="exit__v">{{ $n($T[5]) }}</div><div class="exit__s">Student status at the college</div></div></td>
            <td><div class="exit"><div class="exit__k">Self funded</div><div class="exit__v">{{ $n($T[6]) }}</div><div class="exit__s">Not funded through SLC</div></div></td>
        </tr>
    </table>

    <div class="section">
        <h2>By intake and course</h2>
        <table class="rt">
            <thead>
                <tr>
                    <th style="width: 235px;">Intake and course</th>
                    {{-- Line breaks set by hand: DomPDF drops the spaces of a wrapped heading when it repeats the header on a new page. --}}
                    <th style="width: 58px;">Registered<br>at LCC</th>
                    <th style="width: 60px;">With<br>awarding<br>body</th>
                    <th style="width: 132px; text-align: left;">SLC registered</th>
                    <th style="width: 38px;"></th>
                    <th style="width: 50px;">% of LCC</th>
                    <th style="width: 68px;">Not yet SLC<br>registered</th>
                    <th style="width: 68px;">Attendance<br>confirmed</th>
                    <th style="width: 50px;">SLC<br>withdrawn</th>
                    <th style="width: 76px;">Withdrawn or<br>intermittent</th>
                    <th style="width: 48px;">Self<br>funded</th>
                </tr>
            </thead>
            <tbody>
                @foreach($intakes as $intake)
                    @php $s = $intake['sets'][$set]; @endphp
                    <tr class="intake">
                        <td class="name">{{ SlcRecord::label($intake['name']) }} intake</td>
                        {!! $cells($s['v'], '#16242b') !!}
                    </tr>
                    @forelse($s['courses'] as $c)
                        @php preg_match('/^(.*?)\s*\((.*)\)\s*$/', $c['name'], $m); @endphp
                        <tr>
                            <td class="name">{{ $m[1] ?? $c['name'] }}@if(!empty($m[2]))<small>{{ $m[2] }}</small>@endif</td>
                            {!! $cells($c['v'], '#0e5a54') !!}
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="11">No {{ $set === 'franchise' ? 'franchise' : 'LCC' }} courses in this intake.</td></tr>
                    @endforelse
                @endforeach
                @if(count($intakes) > 1)
                    <tr class="total"><td class="name">All selected intakes</td>{!! $cells($T, '#16242b') !!}</tr>
                @endif
            </tbody>
        </table>
    </div>

    <ul class="notes">
        <li>% of LCC = SLC registered ÷ Registered at LCC. Not yet SLC registered = students registered at LCC who are not SLC registered.</li>
        @if(!empty($odd))
            <li><b>Check:</b> more students are registered with the awarding body than at LCC for {{ implode(' and ', $odd) }}. Registered at LCC excludes discarded students, which may be why.</li>
        @endif
        <li>{{ $sets[$set] }}: {{ $set === 'franchise' ? 'courses run by a franchise partner' : 'courses run by the college, franchise courses left out' }}.</li>
    </ul>
</body>
</html>
