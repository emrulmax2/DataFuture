<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $copy['subject'] }}</title>
    {{-- App\Mail\OfferDeclinedNotice. Same look as the DocuSeal offer acceptance request email
         (resources/docuseal/offer-acceptance-request-email.html); inlined, table-based. --}}
</head>
<body style="margin:0;padding:0;background-color:#f2f0ed;font-family:Arial,Helvetica,sans-serif;color:#303b49;">
    <div style="display:none;font-size:1px;color:#f2f0ed;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">{{ $copy['title'] }}: application {{ $applicant->application_no }}.</div>
    @php $logoSrc = (isset($message) && !empty($logoPath)) ? $message->embed($logoPath) : $logoUrl; @endphp

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f2f0ed;">
        <tr>
            <td align="center" style="padding:30px 14px 35px;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;background-color:#ffffff;border-collapse:separate;border-radius:14px;">

                    <tr>
                        <td align="center" bgcolor="#102d50" style="padding:30px 25px 26px;background-color:#102d50;border-radius:14px 14px 0 0;">
                            @if(!empty($logoSrc))
                                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" width="340" style="display:block;width:100%;max-width:340px;height:auto;border:0;outline:none;text-decoration:none;">
                            @else
                                <p style="margin:0;color:#ffffff;font-size:20px;font-weight:bold;">{{ $companyName }}</p>
                            @endif
                            <p style="margin:20px 0 0;color:#e4eaf2;font-size:11px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">Admissions Office</p>
                        </td>
                    </tr>
                    <tr>
                        <td height="4" bgcolor="#c9a257" style="height:4px;line-height:4px;font-size:0;background-color:#c9a257;">&nbsp;</td>
                    </tr>

                    <tr>
                        <td style="padding:38px 32px 25px;">
                            <p style="margin:0 0 12px;color:#98783e;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;">{{ $copy['eyebrow'] }}</p>
                            <h1 style="margin:0 0 25px;color:#102d50;font-size:27px;font-weight:bold;line-height:1.35;">{{ $copy['title'] }}</h1>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#303b49;">{{ $copy['greeting'] }}</p>
                            @foreach($copy['paragraphs'] as $paragraph)
                                <p style="margin:0 0 20px;font-size:15px;line-height:1.8;color:#475569;">{{ $paragraph }}</p>
                            @endforeach

                            @if(count($rows))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f5f7fa;border:1px solid #e3e8ef;border-radius:8px;margin:5px 0 25px;">
                                    @foreach($rows as $label => $value)
                                        <tr>
                                            <td valign="top" style="padding:12px 20px;{{ !$loop->last ? 'border-bottom:1px solid #e3e8ef;' : '' }}font-size:11px;font-weight:bold;letter-spacing:1px;color:#6b7280;text-transform:uppercase;width:150px;">{{ $label }}</td>
                                            <td valign="top" style="padding:12px 20px 12px 0;{{ !$loop->last ? 'border-bottom:1px solid #e3e8ef;' : '' }}font-size:14px;font-weight:bold;color:#102d50;line-height:1.5;word-break:break-word;">{{ $value }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding:7px 0 12px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#123c70" style="border-radius:6px;background-color:#123c70;">
                                                    <a href="mailto:{{ $contactEmail }}?subject={{ rawurlencode('Offer declined - application '.$applicant->application_no) }}" target="_blank" style="display:inline-block;padding:17px 32px;border:1px solid #123c70;border-radius:6px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;line-height:20px;">Contact Admissions</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:5px 0 28px;text-align:center;color:#7c8796;font-size:12px;line-height:1.6;">
                                <a href="mailto:{{ $contactEmail }}" style="color:#1257a5;text-decoration:none;">{{ $contactEmail }}</a> &bull; <a href="tel:{{ $companyPhoneTel }}" style="color:#1257a5;text-decoration:none;">{{ $companyPhone }}</a>
                            </p>

                            <p style="margin:30px 0 4px;color:#475569;font-size:14px;line-height:1.7;">Kind regards,</p>
                            <p style="margin:0 0 4px;color:#102d50;font-size:15px;font-weight:bold;line-height:1.6;">Admissions Team</p>
                            <p style="margin:0;color:#475569;font-size:14px;line-height:1.6;">{{ $companyName }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:15px 32px 18px;border-top:1px dotted #d8d8d8;">
                            <p style="margin:0;color:#777777;font-size:12px;line-height:1.55;text-align:justify;">
                                This e-mail and its attachments are intended for the above named only and may be confidential. If they have come to you in error you must take no action based on them, nor must you copy or show them to anyone; please reply to this e-mail and highlight the error. Although this email and any attachments are believed to be free of any virus or another defect which might affect any computer or IT system onto which it is received and opened, it is the responsibility of the recipient to ensure that it is virus free, and no responsibility is accepted by London Churchill College for any loss or damage arising from its use.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:16px 20px 20px;border-top:1px dotted #d8d8d8;border-radius:0 0 14px 14px;">
                            <p style="margin:0 0 5px;color:#737373;font-size:12px;line-height:1.6;">{{ $companyAddress }}</p>
                            <p style="margin:0;color:#1257a5;font-size:13px;line-height:1.6;"><a href="tel:{{ $companyPhoneTel }}" style="color:#1257a5;text-decoration:none;">{{ $companyPhone }}</a></p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding:17px 15px 0;">
                            <p style="margin:0;color:#7a7a7a;font-size:11px;line-height:1.6;text-align:center;">Sent automatically when the offer was declined.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
