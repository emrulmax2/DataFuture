{{--
    Application form PDF (ApplicantProfilePrintController@buildPdf: /applicantprofilepdf/{id},
    and the "Signed Application Form" stored in Uploads after the e-signature), on the
    approved "Application_<ref>_<name>.html" design (27 Oct 2026). Rendered by DomPDF, so
    the design's CSS grids are tables, the ticks are small SVGs and the fonts are the ones
    embedded for the audit certificate (resources/fonts/audit-certificate).
--}}
@php
    $fontDir = resource_path('fonts/audit-certificate');
    $e = fn ($value) => e((string) $value);
    $filled = fn ($value) => !($value === null || $value === '' || $value === '---');
    // the design's ticks: an outlined box with a check or a cross
    $svg = fn ($path, $stroke) => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="'.$path.'" fill="none" stroke="'.$stroke.'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>');
    $tick = $svg('M20 6 9 17l-5-5', '#15201e');
    $cross = $svg('M18 6 6 18M6 6l12 12', '#7a847f');

    /** A value cell: the text, or a muted dash. */
    $val = fn ($value) => $filled($value) ? '<td class="v">'.$e($value).'</td>' : '<td class="v empty">&mdash;</td>';
    /** A Yes / No cell; null = not answered. */
    $yn = function ($answer) use ($tick, $cross) {
        if ($answer === null || $answer === '') return '<td class="v empty">&mdash;</td>';
        return $answer
            ? '<td class="v"><span class="yn"><img class="bx" src="'.$tick.'">Yes</span></td>'
            : '<td class="v"><span class="yn no"><img class="bx no" src="'.$cross.'">No</span></td>';
    };
    /** Rows of [label, cell html] laid out two per line, like the design's two-column grid. */
    $grid = function (array $rows, string $class = '') {
        $html = '<table class="grid '.$class.'">';
        foreach (array_chunk($rows, 2) as $pair) {
            $html .= '<tr><td class="k">'.e($pair[0][0]).'</td>'.$pair[0][1].'<td class="gap"></td>';
            $html .= isset($pair[1]) ? '<td class="k">'.e($pair[1][0]).'</td>'.$pair[1][1] : '<td class="none"></td><td class="none"></td>';
            $html .= '</tr>';
        }
        return $html.'</table>';
    };
    /** Rows of [question, cell html], one per line (the design's single-column grid). */
    $list = function (array $rows) {
        $html = '<table class="grid one">';
        foreach ($rows as $row) {
            $html .= '<tr><td class="k">'.e($row[0]).'</td>'.$row[1].'</tr>';
        }
        return $html.'</table>';
    };
    $sec = fn ($n, $title) => '<div class="h2"><span class="n">'.$n.'</span>'.e($title).'</div>';
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Application {{ $ref }} — {{ $fullName }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-500.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-500.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-600.ttf") format('truetype'); }

    @page { size: A4; margin: 8mm 14mm 12mm 14mm; }
    * { box-sizing: border-box; }
    /* DomPDF makes a line box line-height × (ascender − descender): ×1.434 for Hanken Grotesk, ×1.508 for
       Source Serif 4, ×1.452 for JetBrains Mono. Values here are the design's line-heights divided by those. */
    body { margin: 0; font-family: 'Hanken Grotesk', 'DejaVu Sans', sans-serif; font-size: 9.6pt; line-height: 0.976; color: #15201e; background: #fff; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; padding: 0; }
    p { margin: 0; }

    /* header */
    .head { border-bottom: 1.1pt solid #15201e; }
    .head td.pad { height: 4mm; }
    .head td.logo { width: 42mm; padding-right: 6mm; }
    .head td.logo table { width: auto; }
    .head td.logo td { vertical-align: middle; }
    .head td.logo img { width: 14mm; height: auto; display: block; }
    .head td.logo b { display: block; padding-left: 3.5mm; font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 10.5pt; line-height: 0.7; letter-spacing: 0.63pt; text-transform: uppercase; }
    .head td.title { border-left: 0.75pt solid #d9d9d4; padding-left: 6mm; padding-right: 6mm; }
    .head td.title small { display: block; font-size: 7.5pt; letter-spacing: 1.65pt; text-transform: uppercase; color: #7a847f; font-weight: 600; margin-bottom: 1.5mm; }
    .head td.title h1 { margin: 0; font-family: 'Source Serif 4', Georgia, serif; font-weight: 500; font-size: 17pt; line-height: 0.73; }
    .head td.title p { margin-top: 1.5mm; font-size: 9pt; color: #3d4a46; }
    .head td.id { width: 24.5mm; padding-right: 6mm; text-align: right; }
    .head td.id small { display: block; white-space: nowrap; font-size: 6.5pt; letter-spacing: 1.17pt; text-transform: uppercase; color: #7a847f; font-weight: 600; margin-bottom: 0.6mm; }
    .head td.id .ref { font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-size: 14pt; font-weight: 600; letter-spacing: 1.12pt; line-height: 0.76; }
    .head td.id .st { display: inline-block; margin-top: 1.5mm; font-size: 8pt; font-weight: 600; border: 0.75pt solid #15201e; border-radius: 99pt; padding: 0.8mm 3mm; }
    .head td.photo { width: 20mm; }
    .photo div { width: 20mm; height: 25mm; border: 0.75pt solid #d9d9d4; text-align: center; color: #7a847f; font-size: 7pt; overflow: hidden; }
    .photo div span { display: block; padding-top: 10.5mm; }
    .photo img { width: 20mm; height: 25mm; }

    /* sections */
    .body { padding-top: 5mm; }
    /* the design's print values: 3.5mm between sections, 1mm row padding */
    .sec { margin-top: 3.5mm; page-break-inside: avoid; }
    .sec.first { margin-top: 0; }
    /* number + title on one line, so they share a baseline as in the design */
    .h2 { padding-bottom: 1.2mm; border-bottom: 0.75pt solid #15201e; font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 11pt; line-height: 0.93; page-break-after: avoid; }
    .h2 .n { padding-right: 3mm; font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-size: 8pt; color: #7a847f; font-weight: 500; letter-spacing: 0.64pt; }

    /* the design's <dl> keeps the browser's 1em margin: 3.4mm between a heading (or card title) and its rows */
    .grid { margin-top: 3.4mm; }
    .grid tr { page-break-inside: avoid; }
    .grid td { padding: 1mm 0; border-bottom: 0.75pt solid #ebebe6; vertical-align: baseline; }
    /* the smaller label sits 0.3mm lower so its baseline meets the value's */
    .grid td.k { width: 32mm; padding-top: 1.3mm; padding-right: 3mm; color: #7a847f; font-size: 8.5pt; }
    .grid td.v { width: 52mm; font-weight: 500; }
    .grid td.gap, .grid td.none { border-bottom: 0; }
    .grid td.gap { width: 8mm; }
    .grid.one td.k { width: 92mm; }
    .grid.one td.v { width: 87mm; }
    .grid td.empty { color: #7a847f; font-weight: 400; }
    .yn { font-size: 9pt; font-weight: 600; }
    .yn.no { color: #3d4a46; font-weight: 500; }
    .yn img.bx { width: 2.4mm; height: 2.4mm; padding: 0.25mm; border: 0.75pt solid #15201e; border-radius: 0.8mm; margin-right: 1.5mm; vertical-align: -0.6mm; }
    .yn img.bx.no { border-color: #d9d9d4; }

    /* qualifications */
    .quals { margin-top: 3.4mm; font-size: 9.5pt; }
    .quals th { text-align: left; font-size: 7.5pt; letter-spacing: 0.9pt; text-transform: uppercase; color: #7a847f; font-weight: 600; padding: 1.5mm 2mm; border-bottom: 0.75pt solid #15201e; }
    .quals td { padding: 2mm; border-bottom: 0.75pt solid #ebebe6; }
    .quals tr { page-break-inside: avoid; }

    /* employment cards */
    .card { margin-top: 3mm; border: 0.75pt solid #d9d9d4; padding: 2.5mm 3.5mm 5.9mm; page-break-inside: avoid; }
    .card.first { margin-top: 3.4mm; }
    .card .ct { margin-bottom: 0; }
    .card .ct td { vertical-align: baseline; }
    .card .ct b { font-size: 10pt; font-weight: 600; }
    .card .ct td.when { text-align: right; font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-size: 8.5pt; color: #7a847f; font-weight: 500; line-height: 0.964; }
    .card .grid td { padding: 1mm 0; }
    .card .grid td.k { width: 28mm; }
    .card .grid td.v { width: 52.2mm; }
    .card .grid.wide td.k { width: 28mm; }
    .card .grid.wide td.v { width: 143.5mm; }
    .card .grid.wide { margin-top: 0; }
    .card .grid.wide td { border-bottom: 0; }

    /* consents + signature */
    .decl { margin-top: 2.5mm; font-size: 8.8pt; page-break-inside: avoid; }
    .decl td.box { width: 10mm; }
    .decl .bx { width: 4.7mm; height: 4.7mm; border: 1.1pt solid #15201e; }
    .decl .bx img { width: 3.6mm; height: 3.6mm; margin: 0.55mm 0 0 0.55mm; }
    .decl b { font-weight: 600; }
    .sign { width: 120mm; margin-top: 15mm; page-break-inside: avoid; }
    .sign td { width: 54mm; border-top: 0.75pt solid #15201e; padding-top: 2mm; font-size: 8.5pt; color: #7a847f; position: relative; }
    .sign td.gap { width: 12mm; border-top: 0; }
    .sign td b { display: block; color: #15201e; font-size: 10pt; font-weight: 600; height: 5mm; }
    .sign td img { position: absolute; left: 0; top: -13mm; max-height: 12mm; max-width: 50mm; }

    .foot { margin-top: 6mm; border-top: 0.75pt solid #d9d9d4; font-size: 7.5pt; color: #7a847f; page-break-inside: avoid; }
    .foot td { padding-top: 2mm; }
    .foot td.r { text-align: right; }
    .foot b { color: #3d4a46; font-weight: 600; }
</style>
</head>
<body>

<table class="head">
    <tr>
        <td class="logo">
            <table><tr>
                <td>@if($logo)<img src="{{ $logo }}" alt="">@endif</td>
                <td><b>London<br>Churchill<br>College</b></td>
            </tr></table>
        </td>
        <td class="title">
            <small>{{ $formTitle }}</small>
            <h1>{{ $fullName }}</h1>
            <p>{{ $headline }}</p>
        </td>
        <td class="id">
            <small>Application no.</small>
            <div class="ref">{{ $ref }}</div>
            @if($status)<span class="st">{{ $status }}</span>@endif
        </td>
        <td class="photo">
            <div>@if($photo)<img src="{{ $photo }}" alt="">@else<span>Photo</span>@endif</div>
        </td>
    </tr>
    <tr><td class="pad" colspan="4"></td></tr>
</table>

<div class="body">
    <div class="sec first">
        {!! $sec('01', 'Personal details') !!}
        @php
            $personalRows = [
                ['Name', $val($fullName)], ['Date of birth', $val($personal['dob'])],
                ['Gender', $val($personal['gender'])], ['Nationality', $val($personal['nationality'])],
                ['Country of birth', $val($personal['country_of_birth'])], ['Ethnicity', $val($personal['ethnicity'])],
                ['Care leaver', $val($personal['care_leaver'])], ['Disability status', $yn($personal['disability'])],
            ];
            if ($personal['disability']) {
                $personalRows[] = ['Allowance claimed', $yn($personal['allowance'])];
                $personalRows[] = ['Disabilities', $val($personal['disabilities'])];
            }
        @endphp
        {!! $grid($personalRows) !!}
    </div>

    <div class="sec">
        {!! $sec('02', 'Contact details') !!}
        {!! $grid([['Email', $val($contact['email'])], ['Home phone', $val($contact['home'])], ['Mobile', $val($contact['mobile'])], ['Address', $val($contact['address'])]]) !!}
    </div>

    <div class="sec">
        {!! $sec('03', 'Next of kin') !!}
        {!! $grid([['Name', $val($kin['name'])], ['Relation', $val($kin['relation'])], ['Mobile', $val($kin['mobile'])], ['Email', $val($kin['email'])], ['Address', $val($kin['address'])]]) !!}
    </div>

    <div class="sec">
        {!! $sec('04', 'Proposed course') !!}
        @php
            $courseRows = [
                ['When would you like to start your course?', $val($course['start'])],
                ['Which course do you propose to take?', $val($course['course'])],
            ];
            if ($filled($course['venue'])) $courseRows[] = ['Which venue do you want to study?', $val($course['venue'])];
            $courseRows[] = ['How are you funding your education at London Churchill College?', $val($course['funding'])];
            if ($course['funding'] === 'Student Loan') {
                $courseRows[] = ['If your funding is through Student Finance England: have you applied for the proposed course?', $yn($course['sfe_applied'])];
                if ($course['sfe_applied']) $courseRows[] = ['Are you already in receipt of funds?', $yn($course['fund_receipt'])];
                $courseRows[] = ['Have you ever applied for / received any fund or loan from SLC / government for another programme or institution?', $yn($course['applied_received_fund'])];
            } elseif ($course['funding'] === 'Others') {
                $courseRows[] = ['Other funding', $val($course['other_funding'])];
            }
            if ($course['evening_weekend']) $courseRows[] = ['Are you applying for evening and weekend classes (full time)?', $yn($course['full_time'])];
            $courseRows[] = ['Fee eligibility', $val($course['fee_eligibility'])];
        @endphp
        {!! $list($courseRows) !!}
    </div>

    <div class="sec">
        {!! $sec('05', 'Educational qualification') !!}
        {!! $list([['Do you have any formal academic qualification?', $yn($hasQualification)]]) !!}
        @if($hasQualification)
            <table class="quals">
                <thead><tr><th style="width: 20%;">Qualification</th><th style="width: 22%;">Awarding body</th><th style="width: 28%;">Subjects</th><th style="width: 11%;">Result</th><th>Awarded</th></tr></thead>
                <tbody>
                    @forelse($qualifications as $q)
                        <tr><td>{{ $q['qualification'] ?: '—' }}</td><td>{{ $q['awarding_body'] ?: '—' }}</td><td>{{ $q['subjects'] ?: '—' }}</td><td>{{ $q['result'] ?: '—' }}</td><td>{{ $q['awarded'] ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" style="color: #7a847f;">No qualifications recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>

    <div class="sec">
        {!! $sec('06', 'Employment history') !!}
        {!! $list([['What is your current employment status?', $val($employmentStatus)]]) !!}
        @foreach($employments as $i => $job)
            <div class="card {{ $i === 0 ? 'first' : '' }}">
                <table class="ct"><tr><td><b>{{ $job['org'] ?: '—' }}</b></td><td class="when">{{ $job['when'] }}</td></tr></table>
                {!! $grid([['Position', $val($job['position'])], ['Phone', $val($job['phone'])], ['Contact person', $val($job['ref_name'])], ['Contact position', $val($job['ref_position'])], ['Contact phone', $val($job['ref_phone'])]]) !!}
                <table class="grid wide"><tr><td class="k">Address</td>{!! $val($job['address']) !!}</tr></table>
            </div>
        @endforeach
    </div>

    <div class="sec">
        {!! $sec('07', 'Residency status & criminal convictions') !!}
        @php
            $residencyRows = [['Residency status', $val($residency['status'])], ['Have you been convicted?', $yn($residency['convicted'])]];
            if ($residency['convicted']) {
                $residencyRows[] = ['Criminal declaration', $yn($residency['declaration'])];
                $residencyRows[] = ['Details', $val($residency['details'])];
            }
        @endphp
        {!! $grid($residencyRows) !!}
    </div>

    <div class="sec">
        {!! $sec('08', 'Others') !!}
        {!! $list([['Referral code (agent or student)', $val($referralCode)], ['How did you hear about the college?', $val($collegeIntroduction)]]) !!}

        <table class="decl" style="margin-top: 3.4mm;"><tr>
            <td class="box"><div class="bx">@if($videoConsent)<img src="{{ $tick }}">@endif</div></td>
            <td><b>Video consent.</b> I hereby authorise the filming and utilisation of recordings featuring my person, conducted by members or staff of London Churchill College, exclusively for admission purposes.</td>
        </tr></table>
        <table class="decl"><tr>
            <td class="box"><div class="bx">@if($declared)<img src="{{ $tick }}">@endif</div></td>
            <td><b>Declaration.</b> I hereby verify the accuracy and truthfulness of the information provided in this form to the best of my knowledge. It is my responsibility to stay informed about the terms and conditions as well as the policies of the college, and I commit to comply with them. I have thoroughly reviewed the college’s terms and conditions and student privacy policy and pledge to adhere to them throughout my entire course of study.</td>
        </tr></table>

        <table class="sign"><tr>
            <td>@if($signature)<img src="{{ $signature }}" alt="Signature">@else<b></b>@endif Student’s signature</td>
            <td class="gap"></td>
            <td><b>{{ $signedOn }}</b>Date</td>
        </tr></table>
    </div>

    <table class="foot"><tr>
        <td><b>London Churchill College</b> · Admissions · admission@lcc.ac.uk · 020 7377 1077</td>
        <td class="r">Application <b>{{ $ref }}</b> · generated {{ $generated }}</td>
    </tr></table>
</div>

</body>
</html>
