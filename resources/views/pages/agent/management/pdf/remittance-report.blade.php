@php
    $agent = $comission->agent;
    $agentName = trim((string) (optional($agent)->organization ?: optional($agent)->full_name));
    $agentName = ($agentName !== '' ? $agentName : 'Agent not assigned');
    $agentCode = trim((string) optional($agent)->code);

    $address = optional($agent)->address;
    $addressLines = [];
    $streetLine = [];
    if(isset($address->address_line_1) && !empty($address->address_line_1)):
        $streetLine[] = $address->address_line_1;
    endif;
    if(isset($address->address_line_2) && !empty($address->address_line_2)):
        $streetLine[] = $address->address_line_2;
    endif;
    if(!empty($streetLine)):
        $addressLines[] = implode(', ', $streetLine);
    endif;
    $cityLine = [];
    if(isset($address->city) && !empty($address->city)):
        $cityLine[] = $address->city;
    endif;
    if(isset($address->post_code) && !empty($address->post_code)):
        $cityLine[] = $address->post_code;
    endif;
    if(!empty($cityLine)):
        $addressLines[] = implode(', ', $cityLine);
    endif;
    if(isset($address->country) && !empty($address->country)):
        $addressLines[] = $address->country;
    endif;

    $statusMap = [
        'paid' => ['bg' => '#eaf4ee', 'border' => '#cbe2d3', 'text' => '#1e6b4e'],
        'scheduled' => ['bg' => '#eef6ff', 'border' => '#cfe5ff', 'text' => '#2563a8'],
        'canceled' => ['bg' => '#fdeced', 'border' => '#f8cfd4', 'text' => '#b4232d'],
        'pending' => ['bg' => '#fff6df', 'border' => '#ecd39a', 'text' => '#a87816'],
    ];
    $status = $statusMap[$statusTone] ?? $statusMap['pending'];
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 400;
            src: url("{{ resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-Regular.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 600;
            src: url("{{ resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-SemiBold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 700;
            src: url("{{ resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-Bold.ttf') }}") format('truetype');
        }

        @font-face {
            font-family: 'Plus Jakarta Sans';
            font-style: normal;
            font-weight: 800;
            src: url("{{ resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-ExtraBold.ttf') }}") format('truetype');
        }

        @page {
            margin: 19mm 20mm 24mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #12293c;
            font-family: "Plus Jakarta Sans", "DejaVu Sans", Arial, sans-serif;
            font-size: 8.8pt;
            line-height: 1.45;
        }

        body,
        table,
        td,
        th,
        div,
        span,
        p {
            font-family: "Plus Jakarta Sans", "DejaVu Sans", Arial, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tr,
        td,
        th {
            page-break-inside: avoid;
        }

        .muted {
            color: #8b98a0;
        }

        .soft {
            color: #a2adb4;
        }

        .text-right {
            text-align: right;
        }

        .report-header td {
            vertical-align: top;
            padding: 0;
        }

        .logo {
            height: 17mm;
            width: auto;
            display: block;
        }

        .college-copy {
            margin-top: 4.2mm;
            color: #a2adb4;
            font-size: 7.8pt;
            line-height: 1.6;
        }

        .report-title {
            color: #12293c;
            font-size: 22pt;
            font-weight: 800;
            letter-spacing: .02em;
            line-height: 1;
            text-align: right;
        }

        .report-subtitle {
            margin-top: 2.5mm;
            color: #a2adb4;
            font-size: 9pt;
            font-weight: 800;
            letter-spacing: .14em;
            text-align: right;
            text-transform: uppercase;
        }

        .status-pill {
            border-radius: 999px;
            display: inline-block;
            font-size: 8pt;
            font-weight: 800;
            letter-spacing: .1em;
            margin-top: 4mm;
            min-width: 23mm;
            padding: 1.45mm 4.6mm;
            text-align: center;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status-bullet {
            font-size: 9pt;
            line-height: 1;
            margin-right: 2mm;
            vertical-align: .1mm;
        }

        .kpi-shell {
            border: 1px solid #e5eaec;
            border-radius: 9px;
            margin-top: 7mm;
            overflow: hidden;
        }

        .kpi-table td {
            border-right: 1px solid #f0f4f5;
            padding: 4mm 5mm;
            vertical-align: top;
            width: 25%;
        }

        .kpi-table td:last-child {
            border-right: 0;
        }

        .kpi-label,
        .section-eyebrow,
        .table-eyebrow {
            color: #a2adb4;
            font-size: 7.2pt;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .kpi-value {
            color: #12293c;
            font-size: 11pt;
            font-weight: 800;
            margin-top: 1.2mm;
        }

        .kpi-total {
            background: #0f252d;
        }

        .kpi-total .kpi-label {
            color: #8fa8ab;
        }

        .kpi-total .kpi-value {
            color: #e2bb6a;
            font-size: 15pt;
        }

        .details-table {
            margin-top: 6.5mm;
        }

        .details-table > tbody > tr > td {
            vertical-align: top;
            width: 50%;
        }

        .details-table > tbody > tr > td:first-child {
            padding-right: 4mm;
        }

        .details-table > tbody > tr > td:last-child {
            padding-left: 4mm;
        }

        .remit-card {
            border-left: 3px solid #0b6b66;
            margin-top: 3mm;
            padding-left: 5mm;
        }

        .remit-name {
            color: #12293c;
            font-size: 12.5pt;
            font-weight: 800;
            line-height: 1.2;
        }

        .remit-meta {
            color: #8b98a0;
            font-size: 9pt;
            margin-top: 1mm;
        }

        .remit-copy {
            color: #3d5563;
            font-size: 9pt;
            line-height: 1.65;
            margin-top: 3mm;
        }

        .pay-card {
            background: #fbfcfc;
            border: 1px solid #e5eaec;
            border-radius: 9px;
            margin-top: 3mm;
            padding: 4mm 5mm;
        }

        .pay-card td {
            border-top: 1px solid #eef2f3;
            padding: 1.8mm 0;
            vertical-align: top;
        }

        .pay-card tr:first-child td {
            border-top: 0;
        }

        .pay-label {
            color: #8b98a0;
            font-size: 8.5pt;
            width: 25mm;
        }

        .pay-value {
            color: #12293c;
            font-size: 9.5pt;
            font-weight: 800;
        }

        .pay-ref {
            color: #0b6b66;
        }

        .items-block {
            margin-top: 6mm;
        }

        .table-eyebrow {
            margin-bottom: 3mm;
        }

        .items-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .items-table th {
            background: #0f252d;
            color: #9db0b3;
            font-size: 7.5pt;
            font-weight: 800;
            letter-spacing: .1em;
            padding: 3.1mm 5mm;
            text-align: left;
            text-transform: uppercase;
        }

        .items-table th:first-child {
            border-radius: 7px 0 0 0;
            width: 13mm;
        }

        .items-table th:nth-child(2) {
            width: 32mm;
        }

        .items-table th:last-child {
            border-radius: 0 7px 0 0;
            text-align: right;
            width: 24mm;
        }

        .items-table td {
            border-bottom: 1px solid #eef2f3;
            padding: 2.9mm 5mm;
            vertical-align: top;
        }

        .items-table tbody tr.is-even td {
            background: #fbfcfc;
        }

        .student-ref {
            color: #12293c;
            font-size: 9.6pt;
            font-weight: 800;
        }

        .student-reg {
            color: #a2adb4;
            font-size: 7.6pt;
            margin-top: .8mm;
        }

        .student-name {
            color: #12293c;
            font-size: 9.5pt;
            font-weight: 700;
        }

        .course-pill {
            border-radius: 999px;
            display: inline-block;
            font-size: 7.2pt;
            font-weight: 800;
            line-height: 1.2;
            margin-top: 1.2mm;
            max-width: 84mm;
            padding: .75mm 2.6mm;
        }

        .course-pill .dot {
            display: inline-block;
            height: 1.5mm;
            margin-right: 1.8mm;
            vertical-align: 1px;
            width: 1.5mm;
        }

        .course-teal {
            background: #e5f2f0;
            color: #0b6b66;
        }

        .course-red {
            background: #f6e9ec;
            color: #8e2a3c;
        }

        .amount {
            color: #12293c;
            font-size: 10.2pt;
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }

        .amount.negative {
            color: #b4232d;
        }

        .total-row td {
            background: #f8fafa;
            border-bottom: 0;
            border-top: 2px solid #e5eaec;
            padding: 4mm 5mm;
        }

        .total-label {
            color: #5a6b74;
            font-size: 8.4pt;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .total-amount {
            color: #0b6b66;
            font-size: 14pt;
            font-weight: 800;
            text-align: right;
            white-space: nowrap;
        }

        .terms {
            margin-top: 5.5mm;
            page-break-inside: avoid;
        }

        .terms-copy {
            color: #5a6b74;
            font-size: 8.1pt;
            line-height: 1.6;
            margin-top: 2mm;
        }

        .footer {
            border-top: 1px solid #e5eaec;
            bottom: 0;
            left: 0;
            margin-top: 0;
            padding-top: 3mm;
            position: fixed;
            right: 0;
        }

        .footer td {
            color: #a2adb4;
            font-size: 8pt;
            line-height: 1.6;
            vertical-align: top;
        }

        .footer b {
            color: #5a6b74;
            font-weight: 800;
        }

        .footer-spacer {
            height: 4mm;
        }

        .empty-row td {
            color: #8b98a0;
            font-size: 9pt;
            padding: 7mm 4mm;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="footer">
        <tr>
            <td>
                <b>London Churchill College</b> &middot; Admissions &amp; Partnerships<br>
                Queries: finance@lcc.ac.uk
            </td>
            <td class="text-right">
                Remit Report &middot; Reference {{ !empty($remittanceRef) ? '#'.$remittanceRef : 'N/A' }}
            </td>
        </tr>
    </table>

    <table class="report-header">
        <tr>
            <td>
                @if(!empty($logoSrc))
                    <img class="logo" src="{{ $logoSrc }}" alt="London Churchill College">
                @else
                    <div class="report-title" style="text-align:left;font-size:15pt;">London Churchill College</div>
                @endif
                <div class="college-copy">
                    86-90 Paul Street, London EC2A 4NE<br>
                    United Kingdom &middot; info@lcc.ac.uk<br>
                    VAT Reg. GB 421 9987 54
                </div>
            </td>
            <td class="text-right">
                <div class="report-title">REMIT REPORT</div>
                <div class="report-subtitle">Agent Commission &middot; {{ $semesterName }}</div>
                <div class="status-pill" style="background: {{ $status['bg'] }}; border: 1px solid {{ $status['border'] }}; color: {{ $status['text'] }};">
                    <span class="status-bullet">&bull;</span><span>{{ $statusLabel }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="kpi-shell">
        <table class="kpi-table">
            <tr>
                <td>
                    <div class="kpi-label">Reference</div>
                    <div class="kpi-value">{{ !empty($remittanceRef) ? '#'.$remittanceRef : 'N/A' }}</div>
                </td>
                <td>
                    <div class="kpi-label">Generate Date</div>
                    <div class="kpi-value">{{ $generatedDate }}</div>
                </td>
                <td>
                    <div class="kpi-label">No of Student</div>
                    <div class="kpi-value">{{ $comissionDetails->count() }}</div>
                </td>
                <td class="kpi-total">
                    <div class="kpi-label">Remittance Total</div>
                    <div class="kpi-value">{{ \Illuminate\Support\Number::currency((float) $totalAmount, in: 'GBP') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="details-table">
        <tr>
            <td>
                <div class="section-eyebrow">Remit To</div>
                <div class="remit-card">
                    <div class="remit-name">{{ $agentName }}</div>
                    <div class="remit-meta">
                        @if($agentCode !== '')
                            Referral code {{ $agentCode }} &middot;
                        @endif
                        Recruitment partner
                    </div>
                    <div class="remit-copy">
                        @if(!empty($addressLines))
                            @foreach($addressLines as $line)
                                {{ $line }}@if(!$loop->last)<br>@endif
                            @endforeach
                        @else
                            Address not set
                        @endif
                    </div>
                    <div class="remit-copy" style="margin-top:2.5mm;">
                        {{ optional($agent)->email ?: 'Email not set' }}
                    </div>
                </div>
            </td>
            <td>
                <div class="section-eyebrow">Payable To</div>
                <div class="pay-card">
                    <table>
                        <tr>
                            <td class="pay-label">Beneficiary</td>
                            <td class="pay-value">{{ optional($agentBank)->beneficiary ?: 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td class="pay-label">Sort Code</td>
                            <td class="pay-value">{{ optional($agentBank)->sort_code ?: 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td class="pay-label">Account No</td>
                            <td class="pay-value">{{ optional($agentBank)->ac_no ?: 'Not set' }}</td>
                        </tr>
                        <tr>
                            <td class="pay-label">Reference</td>
                            <td class="pay-value pay-ref">{{ !empty($remittanceRef) ? $remittanceRef : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="items-block">
        <div class="table-eyebrow">Line Items &middot; {{ $semesterName }} intake</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Reference</th>
                    <th>Name</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($comissionDetails as $detail)
                    @php
                        $student = $detail->student;
                        $courseName = (isset($student->activeCR->creation->course->name) && !empty($student->activeCR->creation->course->name) ? $student->activeCR->creation->course->name : 'Course not assigned');
                        $courseLower = strtolower($courseName);
                        $courseClass = (strpos($courseLower, 'hospitality') !== false ? 'course-red' : 'course-teal');
                        $amount = (float) ($detail->amount ?? 0);
                    @endphp
                    <tr class="{{ $loop->even ? 'is-even' : '' }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="student-ref">{{ optional($student)->application_no ?: 'N/A' }}</div>
                            <div class="student-reg">{{ optional($student)->registration_no ?: 'No registration' }}</div>
                        </td>
                        <td>
                            <div class="student-name">{{ optional($student)->full_name ?: 'Student not assigned' }}</div>
                            <div class="course-pill {{ $courseClass }}">
                                <span class="dot" style="background: {{ $courseClass === 'course-red' ? '#8e2a3c' : '#0b6b66' }};"></span>{{ $courseName }}
                            </div>
                        </td>
                        <td class="amount {{ $amount < 0 ? 'negative' : '' }}">{{ \Illuminate\Support\Number::currency($amount, in: 'GBP') }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="4">No student remittance items found.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="total-label">Total remittance</td>
                    <td class="total-amount">{{ \Illuminate\Support\Number::currency((float) $totalAmount, in: 'GBP') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="terms">
        <div class="section-eyebrow">Remittance Terms</div>
        <div class="terms-copy">
            Commission is payable at {{ $ruleValueLabel }} for eligible enrolled students in the {{ $semesterName }} intake, per the agreed agent rule
            ({{ $ruleModeLabel }}, {{ $rulePeriodLabel }}, {{ $rulePaymentLabel }}). Settled by bank transfer quoting reference
            <b style="color:#12293c;">{{ !empty($remittanceRef) ? $remittanceRef : 'N/A' }}</b>. Commission is reversible where a student withdraws within the cooling-off period.
        </div>
    </div>

    <div class="footer-spacer"></div>
</body>
</html>
