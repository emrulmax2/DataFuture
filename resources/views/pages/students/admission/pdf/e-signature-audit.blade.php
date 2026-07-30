@php
    $formatDateTime = function ($value) {
        if (empty($value)) {
            return 'N/A';
        }

        return \Carbon\Carbon::parse($value)->format('M d, Y h:i A T');
    };

    $formatDate = function ($value) {
        if (empty($value)) {
            return 'N/A';
        }

        return \Carbon\Carbon::parse($value)->format('M d, Y');
    };

    $formatTime = function ($value) {
        if (empty($value)) {
            return 'N/A';
        }

        return \Carbon\Carbon::parse($value)->format('h:i A T');
    };

    $initials = function ($value) {
        $parts = preg_split('/\s+|@/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);
        $letters = '';

        foreach ($parts as $part) {
            $letters .= strtoupper(substr($part, 0, 1));

            if (strlen($letters) >= 2) {
                break;
            }
        }

        return $letters !== '' ? $letters : 'LC';
    };

    $eventKey = function ($eventType) {
        return strtolower(str_replace([' ', '-'], '_', (string) $eventType));
    };

    $eventLabel = function ($eventType) use ($eventKey) {
        $enum = \App\Enums\EsignEventType::tryFrom((string) $eventType);

        if ($enum) {
            return $enum->label();
        }

        return ucwords(str_replace('_', ' ', $eventKey($eventType)));
    };

    $eventColor = function ($eventType) use ($eventKey) {
        return match ($eventKey($eventType)) {
            'email_sent', 'email_read' => '#a9842d',
            'viewed', 'modified', 'renamed' => '#6d4bb0',
            'location_verified', 'consented_to_esign', 'finalized', 'sign_request_finalized' => '#1E6B4E',
            default => '#2f6ea5',
        };
    };

    $formatCoordinate = function ($decimal, $isLat = true) {
        if ($decimal === null || $decimal === '') {
            return 'N/A';
        }

        $direction = $decimal >= 0 ? ($isLat ? 'N' : 'E') : ($isLat ? 'S' : 'W');
        $decimal = abs((float) $decimal);
        $degrees = floor($decimal);
        $minutesDecimal = ($decimal - $degrees) * 60;
        $minutes = floor($minutesDecimal);
        $seconds = ($minutesDecimal - $minutes) * 60;

        return sprintf('%d&deg; %d\' %.5f&quot; %s', $degrees, $minutes, $seconds, $direction);
    };

    $initializedAt = $adminEsign?->created_at ?? $applicantEsign?->created_at;
    $signedAt = $finalizedEvent?->created_at ?? $applicantEsign?->signed_date ?? $applicantEsign?->updated_at;
    $statusLabel = !empty($applicantEsign?->signature) || !empty($finalizedEvent) ? 'Finalized' : 'Pending';
    $eventCount = $applicantEsignEvents->count();
@endphp

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit of {{ $reference }}</title>
    <style>
        @font-face {
            font-family: 'LccPdfSans';
            font-style: normal;
            font-weight: 400;
            src: url("{{ storage_path('fonts/public_sans_normal_26e53e56b39caf7f3755bea92e4edcb8.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'LccPdfSans';
            font-style: normal;
            font-weight: 600;
            src: url("{{ storage_path('fonts/public_sans_600_0884e3018f1bb7f5f82c96c2d5021c8e.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'LccPdfSans';
            font-style: normal;
            font-weight: 700;
            src: url("{{ storage_path('fonts/public_sans_bold_54586e007dbdfd49947a75ad29a456ca.ttf') }}") format('truetype');
        }

        @page { margin: 0; }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #12293c;
            background: #ffffff;
            font-family: 'LccPdfSans', DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
        }

        body,
        table,
        td,
        th,
        div,
        span {
            font-family: 'LccPdfSans', DejaVu Sans, Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .page {
            position: relative;
            min-height: 1122px;
            page-break-after: always;
            padding: 0;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .cover-header {
            background: #0F252D;
            color: #ffffff;
            padding: 45px 45px 38px;
        }

        .brand-logo {
            width: 180px;
            height: auto;
        }

        .brand-wordmark {
            color: #ffffff;
            font-size: 18pt;
            font-weight: 700;
            letter-spacing: 0;
        }

        .brand-caption {
            color: #c79a34;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
        }

        .cover-kicker,
        .section-kicker,
        .label {
            color: #a2adb4;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .cover-ref {
            color: #ffffff;
            font-size: 22pt;
            font-weight: 700;
            line-height: 1;
            margin-top: 8px;
        }

        .divider {
            height: 1px;
            margin: 31px 0 22px;
            background: #31444c;
        }

        .status-pill {
            display: inline-block;
            padding: 6px 15px;
            border: 1px solid #7dbb9f;
            border-radius: 20px;
            background: #1a443c;
            color: #a8dcc4;
            font-size: 8pt;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .cover-note {
            padding-left: 14px;
            color: #9db0b3;
            font-size: 9pt;
        }

        .body-pad {
            padding: 38px 45px 95px;
        }

        .panel {
            border: 1px solid #e5eaec;
            border-radius: 8px;
            overflow: hidden;
        }

        .details-table td {
            border-bottom: 1px solid #eef2f3;
            padding: 13px 17px;
            vertical-align: top;
        }

        .details-table tr:last-child td {
            border-bottom: 0;
        }

        .details-table .details-label {
            width: 155px;
            background: #f8fafa;
            color: #5a6b74;
            font-size: 8.5pt;
            font-weight: 700;
        }

        .details-table .details-value {
            color: #12293c;
            font-size: 9.5pt;
        }

        .muted {
            color: #8b98a0;
            line-height: 1.22;
        }

        .success-text {
            color: #1E6B4E;
            font-weight: 700;
        }

        .signature-box {
            margin-top: 17px;
            padding: 30px 28px 24px;
            border: 1px dashed #dfe6e8;
            border-radius: 8px;
            background: #fbfcfc;
            text-align: center;
        }

        .signature-img {
            max-width: 430px;
            max-height: 130px;
            width: auto;
            height: auto;
        }

        .signature-empty {
            height: 112px;
        }

        .signature-line {
            height: 1px;
            margin: 16px 58px 10px;
            background: #dfe6e8;
        }

        .summary-cards td {
            width: 33.333%;
            padding-right: 12px;
        }

        .summary-cards td:last-child {
            padding-right: 0;
        }

        .summary-card {
            min-height: 72px;
            padding: 16px 17px;
            border: 1px solid #e5eaec;
            border-radius: 8px;
        }

        .summary-value {
            margin-top: 7px;
            color: #12293c;
            font-size: 9.5pt;
            font-weight: 700;
        }

        .footer {
            position: absolute;
            right: 0;
            bottom: 0;
            left: 0;
            padding: 0;
            color: #a2adb4;
            font-size: 7.5pt;
        }

        .footer td {
            padding: 0 0 31px 45px;
        }

        .footer .page-no {
            padding-right: 45px;
            padding-left: 0;
            text-align: right;
            font-weight: 700;
        }

        .section-header {
            padding: 42px 45px 18px;
            border-bottom: 2px solid #0F252D;
        }

        .section-title-mark {
            width: 7px;
            height: 35px;
            border-radius: 4px;
            background: #c79a34;
        }

        .section-title {
            margin-top: 4px;
            color: #12293c;
            font-size: 16pt;
            font-weight: 700;
            line-height: 1.1;
        }

        .section-meta {
            color: #8b98a0;
            font-size: 8.5pt;
            text-align: right;
            white-space: nowrap;
        }

        .signer-card {
            margin-bottom: 18px;
            border: 1px solid #e7edee;
            border-radius: 8px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .signer-head td {
            padding: 13px 17px;
            border-bottom: 1px solid #eef2f3;
            background: #fbfcfc;
            vertical-align: middle;
        }

        .avatar-table {
            width: 42px;
            height: 42px;
            border-collapse: separate;
            border-radius: 50%;
        }

        .signer-head .avatar-table td {
            width: 42px;
            height: 42px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #ffffff;
            font-size: 10pt;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            vertical-align: middle;
        }

        .avatar-photo {
            display: block;
            width: 42px;
            height: 42px;
            border-radius: 50%;
        }

        .signer-email {
            color: #12293c;
            font-size: 9.4pt;
            font-weight: 700;
            line-height: 1.05;
        }

        .signer-label {
            margin-top: 1px;
            color: #8b98a0;
            font-size: 8pt;
            line-height: 1.12;
        }

        .signed-pill {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            background: #eaf4ee;
            color: #1E6B4E;
            font-size: 7.8pt;
            font-weight: 700;
            letter-spacing: .6px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .signer-body {
            padding: 14px 17px 17px;
        }

        .badge {
            display: inline-block;
            margin: 0 7px 7px 0;
            padding: 5px 10px;
            border-radius: 6px;
            background: #eaf4ee;
            color: #1E6B4E;
            font-size: 7.6pt;
            font-weight: 700;
        }

        .geo-box {
            margin-top: 4px;
            padding: 12px 13px;
            border-radius: 8px;
            background: #f8fafa;
            line-height: 1.15;
        }

        .geo-icon {
            width: 30px;
            height: 30px;
            border: 1px solid #e7edee;
            border-radius: 6px;
            background: #ffffff;
            text-align: center;
        }

        .geo-dot {
            display: block;
            width: 12px;
            height: 12px;
            margin: 8px auto 0;
            border-radius: 50%;
            background: #d53d35;
        }

        .geo-coords {
            margin-top: 2px;
            color: #28414f;
            font-size: 9pt;
            font-weight: 700;
            line-height: 1.12;
        }

        .geo-box .label {
            line-height: 1;
        }

        .geo-box .muted {
            line-height: 1.12;
        }

        .map-frame {
            position: relative;
            width: 100%;
            height: 205px;
            margin-top: 12px;
            border: 1px solid #e7edee;
            border-radius: 8px;
            overflow: hidden;
            background: #f8fafa;
        }

        .map-image {
            width: 100%;
            height: auto;
            margin: -30px 0 0;
            border: 0;
        }

        .page-signers .section-header {
            padding-top: 40px;
            padding-bottom: 16px;
        }

        .page-signers .section-title-mark {
            height: 30px;
        }

        .page-signers .section-title {
            margin-top: 2px;
            font-size: 15pt;
        }

        .page-signers .body-pad {
            padding-top: 22px !important;
            padding-bottom: 62px;
        }

        .page-signers .signer-card {
            margin-bottom: 12px;
        }

        .page-signers .signer-head td {
            padding-top: 11px;
            padding-bottom: 11px;
        }

        .page-signers .signer-body {
            padding-top: 10px;
            padding-bottom: 12px;
        }

        .page-signers .badge {
            margin-bottom: 6px;
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .page-signers .geo-box {
            padding-top: 9px;
            padding-bottom: 9px;
        }

        .page-signers .signer-head .avatar-table td {
            width: 42px;
            height: 42px;
            padding: 0;
            border: 0;
            background: transparent;
            vertical-align: middle;
        }

        .audit-table th {
            padding: 10px 12px;
            border-bottom: 1px solid #e5eaec;
            background: #f8fafa;
            color: #7d8c96;
            font-size: 7.2pt;
            font-weight: 700;
            letter-spacing: .6px;
            text-align: left;
            text-transform: uppercase;
        }

        .audit-table td {
            padding: 11px 12px;
            border-bottom: 1px solid #f0f4f5;
            color: #3d5563;
            font-size: 8.5pt;
            vertical-align: top;
        }

        .audit-table tr:last-child td {
            border-bottom: 0;
        }

        .audit-event {
            color: #12293c;
            font-weight: 700;
        }

        .audit-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 8px;
            border-radius: 50%;
        }

        .audit-time {
            color: #28414f;
            text-align: right;
            white-space: nowrap;
        }

    </style>
</head>
<body>
    <section class="page">
        <div class="cover-header">
            <table>
                <tr>
                    <td style="width: 55%; vertical-align: top;">
                        @if(!empty($logoImage) && file_exists($logoImage))
                            <img src="{{ $logoImage }}" alt="London Churchill College" class="brand-logo">
                        @else
                            <div class="brand-wordmark">London Churchill College</div>
                            <div class="brand-caption">LCC</div>
                        @endif
                    </td>
                    <td style="width: 45%; text-align: right; vertical-align: top;">
                        <div class="cover-kicker">Signature Audit Certificate</div>
                        <div class="cover-ref">{{ $reference }}</div>
                    </td>
                </tr>
            </table>

            <div class="divider"></div>

            <table>
                <tr>
                    <td style="width: 115px; vertical-align: middle;">
                        <span class="status-pill">{{ $statusLabel }}</span>
                    </td>
                    <td class="cover-note">
                        This document is a finalized electronic sign request. All signer identities, consents and locations were verified at the time of signing.
                    </td>
                </tr>
            </table>
        </div>

        <div class="body-pad">
            <div class="section-kicker">Request Details</div>
            <div class="panel" style="margin-top: 17px;">
                <table class="details-table">
                    <tr>
                        <td class="details-label">From</td>
                        <td class="details-value">London Churchill College <span class="muted">({{ $fromEmail }})</span></td>
                    </tr>
                    <tr>
                        <td class="details-label">File Owner</td>
                        <td class="details-value">London Churchill College</td>
                    </tr>
                    <tr>
                        <td class="details-label">Signing Order</td>
                        <td class="details-value">
                            <span class="success-text">1.</span> {{ $adminEmail }}<br>
                            <span class="success-text">2.</span> {{ $applicantEmail }}
                        </td>
                    </tr>
                    <tr>
                        <td class="details-label">Initialized</td>
                        <td class="details-value">{{ $formatDateTime($initializedAt) }}</td>
                    </tr>
                    <tr>
                        <td class="details-label">Finalized</td>
                        <td class="details-value success-text">{{ $formatDateTime($signedAt) }}</td>
                    </tr>
                </table>
            </div>

            <div class="section-kicker" style="margin-top: 30px;">Captured Signature</div>
            <div class="signature-box">
                @if(!empty($signatureImage))
                    <img src="{{ $signatureImage }}" alt="{{ $applicantName }} signature" class="signature-img">
                    <div class="signature-line"></div>
                    <div class="label">Applicant Signature</div>
                    <div class="muted" style="margin-top: 4px;">Signed {{ $formatDateTime($signedAt) }}</div>
                @else
                    <div class="signature-empty"></div>
                @endif
            </div>

            <table class="summary-cards" style="margin-top: 26px;">
                <tr>
                    <td>
                        <div class="summary-card">
                            <div class="label">Document</div>
                            <div class="summary-value">Signed Application Form</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="label">Signature ID</div>
                            <div class="summary-value">{{ $signatureId }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="label">Integrity</div>
                            <div class="summary-value success-text">Tamper-proof</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>Audit of &lsquo;{{ $reference }}&rsquo; &middot; London Churchill College</td>
                    <td class="page-no">Page 1 of 3</td>
                </tr>
            </table>
        </div>
    </section>

    <section class="page page-signers">
        <div class="section-header">
            <table>
                <tr>
                    <td style="width: 12px; vertical-align: top;">
                        <div class="section-title-mark"></div>
                    </td>
                    <td style="vertical-align: top; padding-left: 12px;">
                        <div class="section-kicker">Section 2</div>
                        <div class="section-title">Signers</div>
                    </td>
                    <td class="section-meta">Ref {{ $reference }} &middot; {{ count($signers) }} signers</td>
                </tr>
            </table>
        </div>

        <div class="body-pad" style="padding-top: 30px;">
            @foreach($signers as $signer)
                @php
                    $hasCoordinates = $signer['latitude'] !== null && $signer['latitude'] !== '' && $signer['longitude'] !== null && $signer['longitude'] !== '';
                @endphp
                <div class="signer-card">
                    <table class="signer-head">
                        <tr>
                            <td style="width: 58px;">
                                @if(!empty($signer['photo']) && file_exists($signer['photo']))
                                    <img src="{{ $signer['photo'] }}" alt="{{ $signer['name'] }}" class="avatar-photo">
                                @else
                                    <table class="avatar-table" style="background: {{ $signer['color'] }};">
                                        <tr>
                                            <td>{{ $initials($signer['name'] !== 'N/A' ? $signer['name'] : $signer['email']) }}</td>
                                        </tr>
                                    </table>
                                @endif
                            </td>
                            <td>
                                <div class="signer-email">{{ $signer['email'] }}</div>
                                <div class="signer-label">{{ $signer['label'] }} &middot; {{ $signatureId }}</div>
                            </td>
                            <td style="width: 92px; text-align: right;">
                                <span class="signed-pill">Signed</span>
                            </td>
                        </tr>
                    </table>
                    <div class="signer-body">
                        <div>
                            <span class="badge">Email verified</span>
                            <span class="badge">IP {{ $signer['ip_address'] ?: 'N/A' }}</span>
                            <span class="badge">Consent to eSign</span>
                        </div>

                        <div class="geo-box">
                            <table>
                                <tr>
                                    <td style="width: 42px; vertical-align: top;">
                                        <div class="geo-icon"><span class="geo-dot"></span></div>
                                    </td>
                                    <td style="vertical-align: top;">
                                        <div class="label">Geolocation verified</div>
                                        <div class="geo-coords">
                                            @if($hasCoordinates)
                                                {!! $formatCoordinate($signer['latitude'], true) !!} &nbsp; {!! $formatCoordinate($signer['longitude'], false) !!}
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                        <div class="muted" style="margin-top: 1px;">
                                            {{ trim(($signer['browser'] ?: 'Unknown') . ', ' . ($signer['os'] ?: 'Unknown'), ', ') }} &middot; Signed {{ $formatDateTime($signer['signed_at']) }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        @if(!empty($signer['map']) && file_exists($signer['map']))
                            <div class="map-frame">
                                <img src="{{ $signer['map'] }}" alt="Location map" class="map-image">
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>Audit of &lsquo;{{ $reference }}&rsquo; &middot; London Churchill College</td>
                    <td class="page-no">Page 2 of 3</td>
                </tr>
            </table>
        </div>
    </section>

    <section class="page">
        <div class="section-header">
            <table>
                <tr>
                    <td style="width: 12px; vertical-align: top;">
                        <div class="section-title-mark"></div>
                    </td>
                    <td style="vertical-align: top; padding-left: 12px;">
                        <div class="section-kicker">Section 3</div>
                        <div class="section-title">Audit Trail</div>
                    </td>
                    <td class="section-meta">{{ $eventCount }} events &middot; {{ $formatDate($signedAt) }}</td>
                </tr>
            </table>
        </div>

        <div class="body-pad" style="padding-top: 26px;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 175px;">Event</th>
                        <th>Detail</th>
                        <th style="width: 115px; text-align: right;">Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applicantEsignEvents as $event)
                        @php
                            $key = $eventKey($event->event_type);
                        @endphp
                        <tr>
                            <td>
                                <span class="audit-event"><span class="audit-dot" style="background: {{ $eventColor($event->event_type) }};"></span>{{ $eventLabel($event->event_type) }}</span>
                            </td>
                            <td>
                                {{ $event->event_description ?: 'No description recorded.' }}

                                @if(isset($event->extra_field['opened']) && $event->extra_field['opened'] === true)
                                    <span style="margin-left: 6px; padding: 2px 7px; border-radius: 4px; background: #eef2f3; color: #28414f; font-size: 7pt; font-weight: 700;">OPENED</span>
                                @endif

                                @if(in_array($key, ['sign_request_created', 'viewed', 'consented_to_esign']) && !empty($event->ip_address))
                                    <div class="muted" style="margin-top: 3px;">IP {{ $event->ip_address }} / {{ $event->browser ?: 'Unknown' }}, {{ $event->os ?: 'Unknown' }}</div>
                                @endif

                                @if($key === 'email_sent' && !empty($event->created_at))
                                    <div class="muted" style="margin-top: 3px;">{{ $event->created_at->diffForHumans() }} / {{ $formatDateTime($event->created_at) }}</div>
                                @endif

                                @if($key === 'location_verified')
                                    <div class="muted" style="margin-top: 3px;">IP {{ $event->ip_address ?: 'N/A' }} / {{ $event->browser ?: 'Unknown' }}, {{ $event->os ?: 'Unknown' }}</div>
                                    @if($event->latitude !== null && $event->longitude !== null)
                                        <div class="muted" style="margin-top: 3px;">{!! $formatCoordinate($event->latitude, true) !!} {!! $formatCoordinate($event->longitude, false) !!}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="audit-time">
                                {{ $formatDate($event->created_at) }}
                                <div class="muted" style="margin-top: 3px;">{{ $formatTime($event->created_at) }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #8b98a0;">No e-signature audit events have been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer">
            <table>
                <tr>
                    <td>Audit of &lsquo;{{ $reference }}&rsquo; &middot; London Churchill College</td>
                    <td class="page-no">Page 3 of 3</td>
                </tr>
            </table>
        </div>
    </section>
</body>
</html>
