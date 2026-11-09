{{--
    The email verification code an applicant receives when their education
    agent adds them (or presses Resend) on the agent dashboard.

    Sent by App\Mail\ApplicantAgentBasisEmailVerification with $name, $email
    and $code. Table-based with inline styles so it draws the same in Gmail,
    Outlook and Apple Mail; the logo is the Site Settings logo embedded in the
    message (App\Support\EmailLogo), falling back to the bundled artwork.
--}}
@php
    /* The Site Settings logo is white lettering on a clear ground, so it sits
       on the card's navy header. In the message it is embedded (cid:); in a
       browser preview the hosted copy is linked instead. */
    $siteLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $logoUrl = ($siteLogo && Storage::disk('local')->exists('public/'.$siteLogo)) ? url('storage/'.$siteLogo) : null;
    $emailLogoPath = App\Support\EmailLogo::path();
    $logoSrc = (isset($message) && $emailLogoPath) ? $message->embed($emailLogoPath) : $logoUrl;
    $firstName = trim(explode(' ', trim((string) $name))[0] ?? '') ?: 'there';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>Your verification code</title>
    <style>
        html, body { margin: 0 auto !important; padding: 0 !important; height: 100% !important; width: 100% !important; background: #eef1f6; }
        * { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt !important; mso-table-rspace: 0pt !important; }
        table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important; margin: 0 auto !important; }
        img { -ms-interpolation-mode: bicubic; }
        a { text-decoration: none; }
        *[x-apple-data-detectors], .unstyle-auto-detected-links * { border-bottom: 0 !important; cursor: default !important; color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .card-pad { padding: 28px 22px !important; }
            .code { font-size: 34px !important; letter-spacing: .22em !important; }
        }
    </style>
</head>
<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #eef1f6;">
    <center style="width: 100%; background-color: #eef1f6;">
        {{-- preheader: the inbox preview line --}}
        <div style="display: none; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
            Your London Churchill College verification code is {{ $code }}.&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
        </div>

        <div style="max-width: 600px; margin: 0 auto;" class="email-container">
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: auto;">
                <tr><td style="height: 28px; font-size: 0; line-height: 0;">&nbsp;</td></tr>

                {{-- the card: navy header with the college logo, then the message --}}
                <tr>
                    <td style="padding: 0 16px;">
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background: #ffffff; border: 1px solid #e1e7f0; border-radius: 16px; overflow: hidden;">
                            <tr>
                                <td style="background: #0b1228; padding: 30px 24px; text-align: center; border-radius: 16px 16px 0 0;">
                                    <a href="https://lcc.ac.uk" style="display: inline-block;">
                                        @if($logoSrc)
                                            <img src="{{ $logoSrc }}" alt="London Churchill College" width="180" style="display: block; width: 180px; max-width: 180px; height: auto; border: 0; margin: 0 auto;">
                                        @else
                                            <span style="color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 700; letter-spacing: .02em;">London Churchill College</span>
                                        @endif
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="card-pad" style="padding: 36px 40px 34px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0d1a30;">
                                    <p style="margin: 0 0 10px; font-size: 12px; letter-spacing: .12em; text-transform: uppercase; color: #2f4fe8; font-weight: 700;">Email verification</p>
                                    <h1 style="margin: 0 0 14px; font-size: 26px; line-height: 1.2; font-weight: 700; color: #0d1a30; letter-spacing: -.01em;">Your verification code</h1>
                                    <p style="margin: 0 0 22px; font-size: 15px; line-height: 1.6; color: #3a4860;">
                                        Hi {{ $firstName }}, your education agent is setting up your application to London Churchill College.
                                        Give them this code, or enter it yourself, to confirm that <b style="color: #0d1a30; font-weight: 600;">{{ $email }}</b> is your email address.
                                    </p>

                                    {{-- the code: one plain run of digits, so it selects and copies as one --}}
                                    <p class="code" style="margin: 0 0 6px; text-align: center; font-family: 'Courier New', Courier, monospace; font-size: 40px; line-height: 1.2; font-weight: 700; color: #2f4fe8; letter-spacing: .28em; text-indent: .28em;">{{ $code }}</p>
                                    <p style="margin: 0 0 24px; text-align: center; font-size: 13px; color: #66758f;">Your four-digit code.</p>

                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background: #fbf3e6; border: 1px solid #f1dcb8; border-radius: 12px;">
                                        <tr>
                                            <td style="padding: 14px 16px; font-size: 13.5px; line-height: 1.55; color: #5a4a2e;">
                                                <b style="color: #0d1a30; font-weight: 600;">Did not expect this?</b>
                                                The code was requested through the agent portal. If that was not you or your agent, ignore this email &mdash; nothing changes without the code.
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 22px 0 0; font-size: 13.5px; line-height: 1.6; color: #66758f;">
                                        Need help? Admissions are at <a href="mailto:info@lcc.ac.uk" style="color: #2f4fe8; font-weight: 600;">info@lcc.ac.uk</a> or <a href="tel:02073771077" style="color: #2f4fe8; font-weight: 600;">020 7377 1077</a>.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- footer --}}
                <tr>
                    <td style="padding: 26px 32px 36px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: center;">
                        <p style="margin: 0 0 6px; font-size: 13px; line-height: 1.6; color: #66758f;">
                            <b style="color: #3a4860; font-weight: 600;">London Churchill College</b><br>
                            Barclay Hall, 156B Green Street, London, E7 8JQ<br>
                            <a href="tel:02073771077" style="color: #66758f;">020 7377 1077</a> &middot; <a href="mailto:info@lcc.ac.uk" style="color: #66758f;">info@lcc.ac.uk</a>
                        </p>
                        <p style="margin: 12px 0 0;">
                            <a href="https://www.facebook.com/londonchurchillcollege" style="display: inline-block; margin: 0 3px;"><img src="http://churchill.ac/img/facebook_new.png" alt="Facebook" height="26" style="height: 26px; width: auto; border: 0; display: block;"></a>
                            <a href="https://instagram.com/londonchurchillcollege" style="display: inline-block; margin: 0 3px;"><img src="http://churchill.ac/img/instagram.png" alt="Instagram" height="26" style="height: 26px; width: auto; border: 0; display: block;"></a>
                            <a href="https://www.linkedin.com/company/london-churchill-college-ltd" style="display: inline-block; margin: 0 3px;"><img src="http://churchill.ac/img/linkedin_new.png" alt="LinkedIn" height="26" style="height: 26px; width: auto; border: 0; display: block;"></a>
                            <a href="https://www.youtube.com/user/LCCUK1" style="display: inline-block; margin: 0 3px;"><img src="http://churchill.ac/img/youtube.png" alt="YouTube" height="26" style="height: 26px; width: auto; border: 0; display: block;"></a>
                            <a href="https://twitter.com/LCC_Welfare" style="display: inline-block; margin: 0 3px;"><img src="http://churchill.ac/img/twitter_new.png" alt="Twitter" height="26" style="height: 26px; width: auto; border: 0; display: block;"></a>
                        </p>
                        <p style="margin: 18px 0 0; font-size: 11px; line-height: 1.55; color: #98a3b8; text-align: left;">
                            <b style="font-weight: 600;">Disclaimer:</b> This electronic message contains information which may be privileged or confidential. The information is intended to be for the use of the individual or entity named above. If you are not the intended recipient be aware that any disclosure, copying, distribution or use of the contents of this information is prohibited. If you have received this electronic message in error, please notify us by telephone or email immediately and delete it from your system. Internet e-mails are not necessarily secure. You are advised to scan this message for viruses and cannot accept liability for any loss or damage which may be caused as a result of any computer virus.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </center>
</body>
</html>
