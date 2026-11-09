<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $copy['subject'] }}</title>
    {{-- Inlined and table-based on purpose (Gmail / Outlook), like the library emails. --}}
</head>
<body style="margin:0; padding:0; background:#f2f0ec; font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#232528; -webkit-font-smoothing:antialiased;">
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent; height:0; width:0;">{{ $copy['eyebrow'] }}: {{ $name }} ({{ $applicant->application_no }}).</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f2f0ec;">
        <tr>
            <td align="center" style="padding:28px 14px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:100%; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #e8e6e1;">
                    @php
                        // The Site Settings logo (white wordmark, so it sits on the dark band):
                        // attached to the mail when sending, the hosted copy otherwise.
                        $logoSrc = (isset($message) && !empty($logoPath)) ? $message->embed($logoPath) : $logoUrl;
                    @endphp
                    <tr>
                        <td style="background:#123c38; padding:20px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td valign="middle">
                                        @if(!empty($logoSrc))
                                            <a href="https://lcc.ac.uk" style="text-decoration:none;"><img src="{{ $logoSrc }}" alt="{{ $companyName }}" width="140" style="display:block; width:140px; max-width:140px; height:auto; border:0; outline:none;"></a>
                                        @else
                                            <span style="font-size:15px; font-weight:600; color:#ffffff;">{{ $companyName }}</span>
                                        @endif
                                    </td>
                                    <td align="right" valign="middle" style="font-size:11px; font-weight:600; color:#cda468; letter-spacing:0.14em; text-transform:uppercase;">Admissions</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 32px 18px;">
                            <div style="font-size:11.5px; font-weight:700; color:{{ $copy['tone'] }}; letter-spacing:0.12em; text-transform:uppercase; margin-bottom:10px;">{{ $copy['eyebrow'] }}</div>
                            <div style="font-family:Georgia,'Times New Roman',serif; font-size:24px; line-height:1.25; color:#232528;">{{ $name }}</div>
                            <div style="font-size:14px; color:#53565b; margin-top:5px;">{{ $applicant->application_no }}@if($course) · {{ $course }}@endif @if($semester) · {{ $semester }}@endif</div>
                            @foreach($copy['paragraphs'] as $paragraph)
                                <div style="margin-top:{{ $loop->first ? '18px' : '12px' }}; font-size:14px; line-height:1.6; color:#3c3f44;">{{ $paragraph }}</div>
                            @endforeach
                        </td>
                    </tr>

                    @php
                        $rows = array_filter([
                            'Signed' => !empty($details['signed_at']) ? \Illuminate\Support\Carbon::parse($details['signed_at'])->format('D j M Y, H:i') : null,
                            'Declined' => $kind === 'rejected' && !empty($details['declined_at']) ? \Illuminate\Support\Carbon::parse($details['declined_at'])->format('D j M Y, H:i') : null,
                            'Reason given' => $kind === 'rejected' ? ($details['reason'] ?: 'No reason given') : null,
                            'Declined from' => $kind === 'rejected' ? (($details['via'] ?? 'docuseal') === 'email_link' ? 'The Decline button in the offer email' : 'The DocuSeal form') : null,
                            'DocuSeal form' => $kind === 'rejected' && array_key_exists('withdrawn', $details) ? ($details['withdrawn'] ? 'Withdrawn (the signing link no longer works)' : 'Still open on DocuSeal: please archive it there') : null,
                            'Student ID' => $details['student_id'] ?? null,
                            'Registration no.' => $details['registration_no'] ?? null,
                            'Student record' => !empty($details['student_url']) ? ['url' => $details['student_url']] : null,
                            'Missing' => !empty($details['missing']) ? ucfirst(implode(', ', $details['missing'])) : null,
                            'Fee eligibility' => $details['fee_note'] ?? null,
                            'Failed step' => $details['failed_job'] ?? null,
                            'Error' => !empty($details['error']) ? \Illuminate\Support\Str::limit((string) $details['error'], 400) : null,
                        ]);
                    @endphp
                    @if(count($rows))
                        <tr>
                            <td style="padding:0 32px 8px;">
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f7f5f1; border-radius:10px;">
                                    @foreach($rows as $label => $value)
                                        <tr>
                                            <td valign="top" style="padding:10px 14px; font-size:12.5px; font-weight:600; color:#6b6b66; width:130px; {{ !$loop->last ? 'border-bottom:1px solid #ebe8e2;' : '' }}">{{ $label }}</td>
                                            <td valign="top" style="padding:10px 14px; font-size:13.5px; color:#232528; word-break:break-word; {{ !$loop->last ? 'border-bottom:1px solid #ebe8e2;' : '' }}">@if(is_array($value))<a href="{{ $value['url'] }}" style="color:#1a5fb4; text-decoration:none;">{{ $value['url'] }}</a>@else{{ $value }}@endif</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if($kind === 'rejected' && count($applicantRows))
                        <tr>
                            <td style="padding:16px 32px 8px;">
                                <div style="font-size:11.5px; font-weight:700; color:#6b6b66; letter-spacing:0.12em; text-transform:uppercase; margin:0 0 8px;">Applicant details</div>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #ebe8e2; border-radius:10px;">
                                    @foreach($applicantRows as $label => $value)
                                        <tr>
                                            <td valign="top" style="padding:9px 14px; font-size:12.5px; font-weight:600; color:#6b6b66; width:130px; {{ !$loop->last ? 'border-bottom:1px solid #ebe8e2;' : '' }}">{{ $label }}</td>
                                            <td valign="top" style="padding:9px 14px; font-size:13.5px; color:#232528; word-break:break-word; {{ !$loop->last ? 'border-bottom:1px solid #ebe8e2;' : '' }}">@if($label === 'Email')<a href="mailto:{{ $value }}" style="color:#1a5fb4; text-decoration:none;">{{ $value }}</a>@elseif($label === 'Mobile')<a href="tel:{{ preg_replace('/[^0-9+]/', '', $value) }}" style="color:#1a5fb4; text-decoration:none;">{{ $value }}</a>@else{{ $value }}@endif</td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:18px 32px 30px;">
                            @if($kind === 'converted' && !empty($details['student_url']))
                                <a href="{{ $details['student_url'] }}" style="display:inline-block; background:#123c38; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:11px 18px; border-radius:9px;">Open student record</a>
                                <a href="{{ $recordUrl }}" style="display:inline-block; margin-left:8px; color:#123c38; text-decoration:none; font-size:14px; font-weight:600; padding:10px 16px; border-radius:9px; border:1px solid #cfd8d5;">Applicant record</a>
                            @else
                                <a href="{{ $recordUrl }}" style="display:inline-block; background:#123c38; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:11px 18px; border-radius:9px;">Open applicant record</a>
                            @endif
                            @if(in_array($kind, ['converted', 'conversion_failed'], true))
                                <a href="{{ $logUrl }}" style="display:inline-block; margin-left:8px; color:#123c38; text-decoration:none; font-size:14px; font-weight:600; padding:10px 16px; border-radius:9px; border:1px solid #cfd8d5;">Conversion log</a>
                            @elseif($kind === 'held')
                                <a href="{{ $uploadsUrl }}" style="display:inline-block; margin-left:8px; color:#123c38; text-decoration:none; font-size:14px; font-weight:600; padding:10px 16px; border-radius:9px; border:1px solid #cfd8d5;">Signed form</a>
                            @endif
                        </td>
                    </tr>

                    {{-- The college footer, as on the communication emails. --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px dotted #dddddd; border-bottom:1px dotted #dddddd;">
                            <p style="text-align:justify; color:#888888; font-size:12px; line-height:1.5; margin:0;">
                                This e-mail and its attachments are intended for the above named only and may be confidential. If they have
                                come to you in error you must take no action based on them, nor must you copy or show them to anyone; please
                                reply to this e-mail and highlight the error. Although this email and any attachments are believed to be free of any
                                virus or another defect which might affect any computer or IT system onto which it is received and opened, it is
                                the responsibility of the recipient to ensure that it is virus free, and no responsibility is accepted by London
                                Churchill College for any loss or damage arising from its use.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center; padding:14px 32px 18px;">
                            <p style="margin:0; font-size:13px; line-height:20px; color:#777777;">
                                {{ $companyAddress }}<br>
                                <a href="tel:{{ $companyPhoneTel }}" style="color:#1a5fb4; text-decoration:none;">{{ $companyPhone }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <div style="font-size:11.5px; color:#8a8a85; margin-top:14px;">Sent automatically when the applicant acted on the DocuSeal offer acceptance form.</div>
            </td>
        </tr>
    </table>
</body>
</html>
