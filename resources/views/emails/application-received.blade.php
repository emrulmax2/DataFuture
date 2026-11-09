{{--
    "Apply now" acknowledgement: the application reference and, when the task
    engine raised an e-signature request during the submission, the link to
    sign - one email instead of two. Sent by App\Mail\ApplicationReceivedMail
    with $applicantName, $reference, $siteName, $signUrl, $trackingUrl.

    Table-based with inline styles for Gmail / Outlook / Apple Mail. The logo is
    the Site Settings logo (white lettering) embedded in the message on the
    navy-teal header; a browser preview links the hosted copy instead.
--}}
@php
    $siteLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $logoUrl = ($siteLogo && Storage::disk('local')->exists('public/'.$siteLogo)) ? url('storage/'.$siteLogo) : null;
    $emailLogoPath = App\Support\EmailLogo::path();
    $logoSrc = (isset($message) && $emailLogoPath) ? $message->embed($emailLogoPath) : $logoUrl;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Application received | {{ $siteName }}</title>
    <style>
        body { margin: 0; padding: 0; background: #eef1f4; font-family: Arial, Helvetica, sans-serif; color: #243440; }
        table { border-spacing: 0; border-collapse: collapse; }
        img { border: 0; display: block; }
        a { color: #17628c; }
        @media only screen and (max-width: 620px) {
            .outer { width: 100% !important; }
            .content { padding: 28px 22px !important; }
            .header { padding: 27px 16px !important; }
            .head-logo { width: 235px !important; height: auto !important; }
            .ref { font-size: 32px !important; }
            .mobile { padding-left: 18px !important; padding-right: 18px !important; }
        }
    </style>
</head>
<body>
    <div style="display:none;max-height:0;overflow:hidden;opacity:0">Your application has been received. Reference: {{ $reference }}.</div>
    @if($trackingUrl)
        <img src="{{ $trackingUrl }}" width="1" height="1" style="display:none;" alt="">
    @endif
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f4">
        <tr>
            <td align="center" style="padding:34px 12px">
                <table class="outer" role="presentation" width="600" style="max-width:600px;width:100%;background:#fff;border-radius:8px;overflow:hidden;border:1px solid #e0e6ea">
                    <tr>
                        <td class="header" align="center" style="background:#174e64;padding:30px 20px 28px;border-bottom:4px solid #cba45b">
                            @if($logoSrc)
                                <img class="head-logo" src="{{ $logoSrc }}" width="290" alt="{{ $siteName }}" style="width:290px;max-width:100%;height:auto;margin:0 auto">
                            @else
                                <span style="color:#fff;font-size:22px;font-weight:700;letter-spacing:.02em">{{ $siteName }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="content" style="padding:38px 44px 22px">
                            <div style="font-size:11px;font-weight:700;letter-spacing:1.7px;text-transform:uppercase;color:#607d8b;margin-bottom:10px">Admissions &bull; Application acknowledgement</div>
                            <h1 style="font-size:26px;line-height:33px;color:#173e53;letter-spacing:-.5px;margin:0 0 23px;font-weight:700">Application received</h1>
                            <p style="font-size:14px;line-height:23px;margin:0 0 15px">Dear {{ $applicantName }},</p>
                            <p style="font-size:14px;line-height:23px;margin:0 0 23px">Thank you for applying to study at {{ $siteName }}. We confirm that your application has been successfully submitted and received.</p>
                            <table role="presentation" width="100%" style="border:1px solid #dbe8ef;background:#f5f9fb;border-radius:6px">
                                <tr>
                                    <td style="padding:23px 25px">
                                        <div style="font-size:11px;letter-spacing:1.2px;font-weight:700;color:#526c7b;text-transform:uppercase;margin-bottom:8px">Your application reference</div>
                                        <div class="ref" style="font-size:37px;font-weight:700;line-height:43px;color:#153f56;letter-spacing:1px">{{ $reference }}</div>
                                        <div style="font-size:12px;line-height:19px;color:#576c78;margin-top:9px">Please keep this number and quote it in future correspondence with our Admissions Team.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @if($signUrl)
                        <tr>
                            <td class="content" style="padding:0 44px 29px">
                                <table role="presentation" width="100%" style="background:#f2f7fa;border:1px solid #d6e6ee;border-radius:6px">
                                    <tr>
                                        <td class="mobile" style="padding:24px 26px">
                                            <div style="color:#164d66;font-weight:700;font-size:16px;line-height:22px;margin-bottom:9px">Action required: E-signature</div>
                                            <div style="font-size:14px;line-height:22px;color:#3a4a55;margin-bottom:21px">Please review and electronically sign your application document using the secure link below.</div>
                                            <table role="presentation" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td bgcolor="#16546e" style="border-radius:5px">
                                                        <a href="{{ $signUrl }}" style="display:inline-block;background:#16546e;border-radius:5px;color:#fff;font-size:14px;font-weight:700;padding:14px 23px;text-decoration:none">Complete E-Signature &nbsp; &rarr;</a>
                                                    </td>
                                                </tr>
                                            </table>
                                            <div style="font-size:12px;line-height:19px;color:#61717d;margin-top:18px">If the button does not work, copy this link into your browser:</div>
                                            <div style="font-size:12px;line-height:19px;overflow-wrap:anywhere;word-break:break-all;margin-top:5px"><a href="{{ $signUrl }}">{{ $signUrl }}</a></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="content" style="padding:0 44px 35px">
                            <p style="font-size:14px;line-height:23px;margin:0 0 20px">Our Admissions Team will contact you if further information or supporting documents are required.</p>
                            <p style="font-size:14px;line-height:23px;margin:0">Kind regards,<br><strong style="color:#163e53">Admissions Team</strong><br>{{ $siteName }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:21px 38px;border-top:1px solid #e2e8ed;background:#fafbfc;text-align:center">
                            <div style="color:#566976;font-size:12px;line-height:20px">Barclay Hall, 156B Green Street, London, E7 8JQ<br><a href="tel:+442073771077" style="color:#17628c;text-decoration:none">+44 (0) 20 7377 1077</a></div>
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="600" class="outer" style="width:100%;max-width:600px">
                    <tr>
                        <td style="text-align:center;padding:17px 14px;color:#798891;font-size:11px;line-height:17px">This message is intended for the named recipient. If you received it in error, please notify the sender and delete it.</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
