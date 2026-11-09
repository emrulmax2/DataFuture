{{--
    Signature audit certificate (ApplicanESignatureController@download), on the
    approved "Audit_<ref>.html" design: A4, page 1 = request details, captured
    signature and the two signers with their verification details and maps;
    page 2+ = the audit trail. Rendered by DomPDF, so the layout is tables, the
    fonts are embedded from resources/fonts/audit-certificate and the running
    footer ("Audit of … · Page n of m") is drawn by the controller on the canvas.
--}}
@php
    $dash = '—';
    $fontDir = resource_path('fonts/audit-certificate');
    $carbon = function ($value) {
        if (empty($value)) return null;
        try { return \Carbon\Carbon::parse($value); } catch (\Throwable $e) { return null; }
    };
    $fmtDT = fn ($value) => ($d = $carbon($value)) ? $d->format('M d, Y · h:i A T') : $dash;
    $fmtDTplain = fn ($value) => ($d = $carbon($value)) ? $d->format('M d, Y h:i A T') : $dash;
    $fmtDate = fn ($value) => ($d = $carbon($value)) ? $d->format('M d, Y') : $dash;
    $fmtTime = fn ($value) => ($d = $carbon($value)) ? $d->format('h:i A') : $dash;
    $dms = function ($decimal, $isLat = true) {
        if ($decimal === null || $decimal === '') return null;
        $direction = $decimal >= 0 ? ($isLat ? 'N' : 'E') : ($isLat ? 'S' : 'W');
        $decimal = abs((float) $decimal);
        $degrees = floor($decimal);
        $minutesDecimal = ($decimal - $degrees) * 60;
        $minutes = floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;
        return sprintf("%d° %d' %.5f\" %s", $degrees, $minutes, $seconds, $direction);
    };
    $initials = function ($name, $email) {
        $clean = trim(preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Prof)\.?\s+/i', '', trim(preg_replace('/\s+/', ' ', (string) $name))));
        if ($clean === '' || $clean === 'N/A') $clean = (string) strtok((string) $email, '@');
        $parts = preg_split('/[\s._-]+/', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $first = $parts ? mb_substr($parts[0], 0, 1) : 'L';
        $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : (mb_strlen($parts[0] ?? '') > 1 ? mb_substr($parts[0], 1, 1) : 'C');
        return mb_strtoupper($first.$last);
    };
    $device = fn ($browser, $os) => implode(' · ', array_filter([$browser && $browser !== 'System' ? $browser : null, $os && $os !== 'System' ? $os : null])) ?: $dash;

    $isFinalised = (bool) ($isFinalised ?? (!empty($applicantEsign?->signature) || !empty($finalizedEvent)));
    $statusLabel = $statusLabel ?? ($isFinalised ? 'Finalised' : 'Awaiting signature');
    $initialisedAt = $initialisedAt ?? ($adminEsign?->created_at ?? $applicantEsign?->created_at);
    $signedAt = $signedAt ?? ($finalizedEvent?->created_at ?? $applicantEsign?->signed_date);
    $eventCount = $applicantEsignEvents->count();
    $duration = $duration ?? null;
    $trigger = $trigger ?? null;
    $requestVia = $requestVia ?? '';
    $documentName = $documentName ?? 'Signed Application Form';
    $signedCount = collect($signers)->where('status', 'Signed')->count();

    $eventLabel = [
        'sign_request_created' => 'Sign request created', 'email_sent' => 'Email sent', 'email_read' => 'Email read', 'viewed' => 'Viewed',
        'location_verified' => 'Location verified', 'consented_to_esign' => 'Consented to eSign', 'finalized' => 'Finalised', 'modified' => 'Modified',
        'sign_request_finalized' => 'Sign request finalised', 'renamed' => 'Renamed',
    ];
    // "Opened …" for a sent email: the matching read event, when there is one.
    $openedAt = function ($event) use ($applicantEsignEvents) {
        if (!isset($event->extra_field['opened']) || $event->extra_field['opened'] !== true) return null;
        $who = (string) strtok((string) $event->event_description, ' ');
        $read = $applicantEsignEvents->first(fn ($e) => $e->event_type === 'email_read' && $e->id > $event->id && str_starts_with((string) $e->event_description, $who));
        return $read ? $read->created_at : true;
    };
    $eventMeta = function ($event) use ($dms, $device, $openedAt, $fmtDTplain, $dash) {
        $parts = [];
        $opened = $openedAt($event);
        if ($opened === true) $parts[] = 'Opened';
        elseif ($opened) $parts[] = 'Opened '.$fmtDTplain($opened);
        if (!empty($event->ip_address)) $parts[] = 'IP '.$event->ip_address;
        $dev = $device($event->browser ?? null, $event->os ?? null);
        if ($dev !== $dash) $parts[] = str_replace(' · ', ', ', $dev);
        if ($event->latitude !== null && $event->latitude !== '' && $event->longitude !== null && $event->longitude !== '') $parts[] = $dms($event->latitude, true).', '.$dms($event->longitude, false);
        return implode(' · ', $parts);
    };
    // The design's wording (British spelling, the spelled-out consent, a typo in the stored text); display only.
    $describe = fn ($text) => str_replace(
        ['initialized', 'finalized', 'consented to esign', 'notificaiton', 'Finalized'],
        ['initialised', 'finalised', 'consented to electronic signing', 'notification', 'Finalised'],
        (string) $text
    );
    $firstEvent = $applicantEsignEvents->first();
    $lastEvent = $applicantEsignEvents->last();
    $eventSpan = $firstEvent ? ($fmtDate($firstEvent->created_at) === $fmtDate($lastEvent->created_at) ? $fmtDate($firstEvent->created_at) : $fmtDate($firstEvent->created_at).' – '.$fmtDate($lastEvent->created_at)) : '';
    $tz = $firstEvent && $carbon($firstEvent->created_at) ? $carbon($firstEvent->created_at)->format('T') : '';
@endphp
<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8">
<title>Signature audit certificate — {{ $reference }}</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-500.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-500.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-500.ttf") format('truetype'); }
    @font-face { font-family: 'Source Serif 4'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/SourceSerif4-600.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-500.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 500; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-500.ttf") format('truetype'); }
    @font-face { font-family: 'JetBrains Mono'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/JetBrainsMono-600.ttf") format('truetype'); }

    @page { size: A4; margin: 12mm 14mm 12mm 14mm; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 0; }
    .pg { position: relative; height: 273mm; }
    .pg2 { position: relative; min-height: 273mm; }
    .pin-bottom { position: absolute; left: 0; right: 0; top: 256mm; }
    /* DomPDF makes a line box line-height × (ascender − descender): ×1.434 for Hanken Grotesk, ×1.508 for
       Source Serif 4, ×1.452 for JetBrains Mono. Values here are the design's line-heights divided by those. */
    body { font-family: 'Hanken Grotesk', 'DejaVu Sans', sans-serif; font-size: 9.4pt; line-height: 0.976; color: #15201e; background: #fff; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }
    p { margin: 0; }
    .mono { font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-weight: 500; line-height: 0.964; }
    .serif { font-family: 'Source Serif 4', Georgia, serif; line-height: 0.93; }
    .lbl { font-size: 7pt; letter-spacing: 1.4pt; text-transform: uppercase; color: #7a847f; font-weight: 600; line-height: 0.976; }
    .muted { color: #7a847f; }
    .ink2 { color: #3d4a46; }

    /* header */
    .head { border-bottom: 1.5pt solid #15201e; }
    .head td { padding-bottom: 5mm; }
    .head td { vertical-align: top; }
    .head .logo { width: 37mm; padding-right: 5mm; vertical-align: middle; }
    .head .logo table { width: auto; }
    .head .logo img { width: 13mm; height: auto; display: block; }
    .head .logo b { font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 10pt; line-height: 0.7; letter-spacing: 0.8pt; text-transform: uppercase; padding-left: 3mm; vertical-align: middle; display: block; }
    .head .title { border-left: 0.75pt solid #d9d9d4; padding-left: 6mm; }
    .head h1 { font-family: 'Source Serif 4', Georgia, serif; font-weight: 500; font-size: 17pt; margin: 1.5mm 0 0; line-height: 0.73; }
    .head .title p { margin-top: 1.5mm; font-size: 8.8pt; line-height: 0.976; color: #3d4a46; }
    .head .id { width: 38mm; text-align: right; }
    .head .ref { font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-size: 16pt; font-weight: 600; letter-spacing: 1pt; margin: 1.5mm 0 1.5mm; line-height: 0.76; }
    .st { display: inline-block; font-size: 7pt; font-weight: 700; letter-spacing: 1pt; text-transform: uppercase; border: 0.75pt solid #15201e; border-radius: 99pt; padding: 0.9mm 3mm 0.8mm; line-height: 0.84; white-space: nowrap; }
    .st .dot { display: inline-block; width: 1.8mm; height: 1.8mm; border-radius: 50%; background: #15201e; margin-right: 1.5mm; vertical-align: middle; }
    .st.pending { border-color: #a9842d; color: #a9842d; }
    .st.pending .dot { background: #a9842d; }

    /* strip */
    .strip td { padding: 3.2mm 0 3.2mm 4mm; border-left: 0.75pt solid #d9d9d4; border-bottom: 0.75pt solid #d9d9d4; width: 33.33%; }
    .strip td.first { border-left: 0; padding-left: 0; }
    .strip b { display: block; font-size: 10pt; font-weight: 600; margin-top: 0.8mm; }

    h2 { font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 11pt; line-height: 0.93; margin: 6mm 0 2mm; padding-bottom: 1.2mm; border-bottom: 0.75pt solid #15201e; page-break-after: avoid; }
    .h2row { margin: 6mm 0 2mm; }
    .h2row td { border-bottom: 0.75pt solid #15201e; padding-bottom: 1.2mm; vertical-align: bottom; }
    .h2row td.h2 { font-family: 'Source Serif 4', Georgia, serif; font-weight: 600; font-size: 11pt; line-height: 0.93; }
    .h2row td.sub { font-family: 'JetBrains Mono', 'DejaVu Sans Mono', monospace; font-size: 8pt; color: #7a847f; font-weight: 500; letter-spacing: 0.6pt; text-align: right; }

    /* two columns */
    .two > tbody > tr > td { width: 50%; }
    .two td.gap { width: 8mm; }
    .kv td { padding: 1.4mm 0; border-bottom: 0.75pt solid #ebebe6; vertical-align: baseline; }
    .kv small { display: block; color: #7a847f; font-size: 8pt; font-weight: 400; }
    .kv td.k { width: 30mm; color: #7a847f; font-size: 8.5pt; padding-right: 3mm; }
    .kv td.v { font-weight: 500; }
    .kv ol { margin: 0; padding-left: 4mm; }
    .kv ol li { margin: 0; }

    .sigbox { border: 0.75pt solid #d9d9d4; padding: 4mm; text-align: center; }
    .sigbox .img { height: 28mm; vertical-align: middle; }
    .sigbox img { max-height: 26mm; max-width: 60mm; }
    .sigbox .empty { height: 28mm; color: #7a847f; font-size: 8.8pt; }
    .sigbox .cap { border-top: 0.75pt solid #15201e; padding-top: 1.5mm; margin-top: 2mm; text-align: center; }
    .sigbox .cap small { display: block; color: #3d4a46; font-size: 8.3pt; margin-top: 0.5mm; }

    /* signers */
    .sgs > tbody > tr > td { width: 50%; vertical-align: top; }
    .sgs td.gap { width: 6mm; }
    .sg { border: 0.75pt solid #d9d9d4; padding: 3.5mm 4mm 1.5mm; page-break-inside: avoid; }
    .sg-top td { vertical-align: middle; padding-bottom: 2.5mm; border-bottom: 0.75pt solid #15201e; }
    .sg-top td.av, .sg-top table td { border-bottom: 0; padding-bottom: 0; }
    .av { width: 9mm; height: 9mm; border-radius: 50%; background: #15201e; color: #fff; font-weight: 700; font-size: 8.5pt; letter-spacing: 0.3pt; text-align: center; vertical-align: middle; }
    .av img { width: 9mm; height: 9mm; border-radius: 50%; display: block; }
    .sg-id { padding-left: 3mm; }
    .sg-id b { display: block; font-weight: 600; font-size: 9.4pt; line-height: 0.84; }
    .sg-id small { color: #7a847f; font-size: 7.8pt; }
    .sg-st { width: 20mm; text-align: right; }
    .map { position: relative; height: 26mm; margin: 2.5mm 0 1mm; overflow: hidden; border: 0.75pt solid #d9d9d4; background: #ebebe6; }
    .map .pin { position: absolute; left: 50%; top: 50%; width: 5.4mm; height: 5.4mm; margin: -2.7mm 0 0 -2.7mm; border-radius: 50%; background: rgba(21,32,30,.25); }
    .map .pin i { position: absolute; left: 1.2mm; top: 1.2mm; width: 3mm; height: 3mm; border-radius: 50%; background: #15201e; border: 0.5mm solid #fff; }
    .map img { width: 100%; display: block; }
    .map .geo { position: absolute; left: 2mm; bottom: 1.6mm; background: #fff; border: 0.75pt solid #15201e; border-radius: 99pt; padding: 0.4mm 2mm; font-size: 6.5pt; letter-spacing: 1pt; text-transform: uppercase; font-weight: 700; }
    .map .geo.off { border-color: #a9842d; color: #a9842d; }
    .sg .kv td.k { width: 20mm; }
    .sg .kv td { padding: 1.2mm 0; }
    .sg .kv tr.last td { border-bottom: 0; }
    .sg .kv td.v.mono { font-size: 8.3pt; }

    .note { padding-top: 5mm; font-size: 8pt; line-height: 0.976; color: #3d4a46; border-top: 0.75pt solid #d9d9d4; }
    .note.flow { margin-top: 6mm; }
    .note td { vertical-align: top; }
    .note td.right { width: 40mm; text-align: right; }

    /* audit trail */
    .trail th { text-align: left; font-size: 7pt; letter-spacing: 1.2pt; text-transform: uppercase; color: #7a847f; font-weight: 600; padding: 0 0 1.6mm; border-bottom: 0.75pt solid #15201e; }
    .trail td { padding: 2.3mm 3mm 2.3mm 0; border-bottom: 0.75pt solid #ebebe6; vertical-align: top; page-break-inside: avoid; }
    .trail td.t { width: 20mm; color: #3d4a46; font-size: 8.3pt; white-space: nowrap; padding-top: 2.6mm; }
    .trail td.ev { width: 42mm; font-weight: 600; }
    .trail td.ev .dot { display: inline-block; width: 1.8mm; height: 1.8mm; border-radius: 50%; background: #15201e; margin-right: 2mm; vertical-align: -0.2mm; }
    .trail small { display: block; color: #7a847f; font-size: 8pt; margin-top: 0.4mm; }
    .page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ============================ page 1 ============================ --}}
<div class="pg">
<table class="head">
    <tr>
        <td class="logo">
            <table><tr>
                @if(!empty($logoImage) && file_exists($logoImage))
                    <td style="width: 14mm; vertical-align: middle;"><img src="{{ $logoImage }}" alt=""></td>
                @endif
                <td style="vertical-align: middle;"><b>London<br>Churchill<br>College</b></td>
            </tr></table>
        </td>
        <td class="title">
            <span class="lbl">Signature audit certificate</span>
            <h1>Electronic sign request</h1>
            <p>This document records {{ $isFinalised ? 'a finalised' : 'an open' }} electronic sign request. Signer identities, consent and locations {{ $isFinalised ? 'were verified at the time of signing' : 'are recorded as each step is completed' }}.</p>
        </td>
        <td class="id">
            <span class="lbl">Reference</span>
            <div class="ref">{{ $reference }}</div>
            <span class="st {{ $isFinalised ? '' : 'pending' }}"><span class="dot"></span>{{ $statusLabel }}</span>
        </td>
    </tr>
</table>

<table class="strip">
    <tr>
        <td class="first"><span class="lbl">Document</span><b>{{ $documentName }}</b></td>
        <td><span class="lbl">Signature ID</span><b class="mono">{{ $signatureId }}</b></td>
        <td><span class="lbl">Integrity</span><b>{{ $isFinalised ? 'Tamper-proof' : 'Pending' }}</b></td>
    </tr>
</table>

<table class="two" style="margin-top: 2mm;">
    <tr>
        <td>
            <h2>Request details</h2>
            <table class="kv">
                <tr><td class="k">From</td><td class="v">London Churchill College<br><span class="muted" style="font-weight: 400;">{{ $fromEmail }}</span></td></tr>
                <tr><td class="k">File owner</td><td class="v">London Churchill College</td></tr>
                <tr><td class="k">Signing order</td><td class="v"><ol><li>{{ $adminEmail }}</li><li>{{ $applicantEmail }}</li></ol></td></tr>
                <tr><td class="k">Initialised</td><td class="v">{{ $fmtDT($initialisedAt) }}</td></tr>
                <tr><td class="k">Finalised</td><td class="v">@if($isFinalised)<b>{{ $fmtDT($signedAt) }}</b>@else<span class="muted">Waiting for the applicant</span>@endif</td></tr>
                <tr><td class="k">Duration</td><td class="v">{{ $duration ? $duration.' · ' : '' }}{{ $eventCount }} audit {{ $eventCount === 1 ? 'event' : 'events' }}</td></tr>
            </table>
        </td>
        <td class="gap"></td>
        <td>
            <h2>Captured signature</h2>
            <div class="sigbox">
                @if($isFinalised && !empty($signatureImage))
                    <div class="img"><img src="{{ $signatureImage }}" alt="Applicant signature"></div>
                @elseif($isFinalised)
                    <div class="empty" style="padding-top: 10mm;">Signature image not available</div>
                @else
                    <div class="empty" style="padding-top: 10mm;">Not signed yet</div>
                @endif
                <div class="cap">
                    <span class="lbl">Applicant signature</span>
                    <small>{{ $isFinalised ? 'Signed '.$fmtDT($signedAt).' · '.$signatureId : 'Awaiting the applicant · '.$signatureId }}</small>
                </div>
            </div>
        </td>
    </tr>
</table>

<table class="h2row"><tr><td class="h2">Signers</td><td class="sub">{{ count($signers) }} signers · {{ $signedCount === count($signers) ? 'all verified' : $signedCount.' of '.count($signers).' signed' }}</td></tr></table>
<table class="sgs">
    <tr>
        @foreach($signers as $i => $signer)
            @if($i > 0)<td class="gap"></td>@endif
            @php
                $hasCoords = $signer['latitude'] !== null && $signer['latitude'] !== '' && $signer['longitude'] !== null && $signer['longitude'] !== '';
                $signed = ($signer['status'] ?? 'Signed') === 'Signed';
            @endphp
            <td>
                <div class="sg">
                    <table class="sg-top">
                        <tr>
                            <td style="width: 9mm;">
                                @if(!empty($signer['photo']) && file_exists($signer['photo']))
                                    <img src="{{ $signer['photo'] }}" alt="" style="width: 9mm; height: 9mm; border-radius: 50%; display: block;">
                                @else
                                    <table style="width: 9mm;"><tr><td class="av">{{ $initials($signer['name'], $signer['email']) }}</td></tr></table>
                                @endif
                            </td>
                            <td class="sg-id"><b>{{ $signer['email'] }}</b><small>{{ $signer['label'] }} · {{ $signatureId }}</small></td>
                            <td class="sg-st"><span class="st {{ $signed ? '' : 'pending' }}">{{ $signed ? 'Signed' : 'Pending' }}</span></td>
                        </tr>
                    </table>
                    <div class="map">
                        @if(!empty($signer['map']) && file_exists($signer['map']))
                            <img src="{{ $signer['map'] }}" alt="">
                        @endif
                        @if($hasCoords)<span class="pin"><i></i></span>@endif
                        <span class="geo {{ $hasCoords ? '' : 'off' }}">{{ $hasCoords ? 'Geolocation verified' : 'Location not available' }}</span>
                    </div>
                    <table class="kv">
                        <tr><td class="k">Email</td><td class="v">{{ $signer['email_status'] ?? 'Verified' }}</td></tr>
                        <tr><td class="k">Consent</td><td class="v">{{ $signer['consent'] ?? 'Agreed to eSign' }}</td></tr>
                        <tr><td class="k">IP address</td><td class="v mono">{{ $signer['ip_address'] ?: $dash }}</td></tr>
                        <tr><td class="k">Device</td><td class="v">{{ $device($signer['browser'] ?? null, $signer['os'] ?? null) }}</td></tr>
                        <tr><td class="k">Location</td><td class="v mono">@if($hasCoords){{ $dms($signer['latitude'], true) }}<br>{{ $dms($signer['longitude'], false) }}@else{{ $dash }}@endif</td></tr>
                        <tr class="last"><td class="k">{{ $signer['time_label'] ?? 'Signed' }}</td><td class="v">{{ $fmtDT($signer['signed_at']) }}</td></tr>
                    </table>
                </div>
            </td>
        @endforeach
    </tr>
</table>

<table class="note pin-bottom">
    <tr>
        <td>
            @if($isFinalised)
                <p><b>Verification.</b> Each signer confirmed their email address, accepted the electronic signature consent and had their IP address, device and geolocation recorded at the moment of signing. The signed document is sealed and any later modification would invalidate this certificate.</p>
            @else
                <p><b>Status.</b> The request has been issued and the audit trail is being recorded. This certificate becomes final once the applicant has consented and signed; until then the signature and the applicant's verification details may be incomplete.</p>
            @endif
        </td>
    </tr>
</table>

</div>
<div class="page-break"></div>

{{-- ============================ page 2+ : audit trail ============================ --}}
@php $pinTrailNote = $eventCount <= 16; @endphp
<div class="{{ $pinTrailNote ? 'pg2' : '' }}">
<table class="h2row" style="margin-top: 0;"><tr><td class="h2">Audit trail</td><td class="sub">{{ $eventCount }} {{ $eventCount === 1 ? 'event' : 'events' }}{{ $eventSpan ? ' · '.$eventSpan : '' }}{{ $tz ? ' · times in '.$tz : '' }}</td></tr></table>
<table class="trail">
    <thead><tr><th style="width: 20mm;">Time</th><th style="width: 42mm;">Event</th><th>Detail</th></tr></thead>
    <tbody>
        @forelse($applicantEsignEvents as $event)
            @php $meta = $eventMeta($event); $sameDay = $firstEvent && $fmtDate($event->created_at) === $fmtDate($firstEvent->created_at); @endphp
            <tr>
                <td class="t mono">{{ $fmtTime($event->created_at) }}@if(!$sameDay)<small style="color: #7a847f;">{{ $fmtDate($event->created_at) }}</small>@endif</td>
                <td class="ev"><span class="dot"></span>{{ $eventLabel[$event->event_type] ?? \Illuminate\Support\Str::headline((string) $event->event_type) }}</td>
                <td>{{ $describe($event->event_description ?: 'No description recorded.') }}@if($meta !== '')<small>{{ $meta }}</small>@endif</td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align: center; color: #7a847f;">No e-signature audit events have been recorded yet.</td></tr>
        @endforelse
    </tbody>
</table>

<table class="note {{ $pinTrailNote ? 'pin-bottom' : 'flow' }}">
    <tr>
        <td><p>Generated by the LCC admission system{{ $generatedAt ?? false ? ' on '.$fmtDT($generatedAt) : '' }}. This certificate accompanies the signed document and should be retained with the applicant's record.</p></td>
        <td class="right mono">{{ $signatureId }}</td>
    </tr>
</table>
</div>

</body>
</html>
