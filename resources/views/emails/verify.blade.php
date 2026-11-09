{{--
    Applicant account: verify your email address.

    Same build as the other college emails (the online form receipts): a navy
    header carrying the Site Settings logo, Georgia headings on Arial text, and
    everything in tables with inline styles, because this is email and a
    stylesheet is only a suggestion there. The logo is embedded rather than
    linked so it shows without the reader having to load remote images.

    $url is a signed link that lasts an hour (VerifyEmailForApplicant).
--}}
@php
    $email = $user->email ?? '';
    $contactEmail = 'info@lcc.ac.uk';
    $contactPhone = '020 7377 1077';
@endphp
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Verify your email address</title>
<style>
  body { margin: 0; padding: 0; }
  table { border-collapse: collapse; }
  a { color: #1A2030; }
  @media (max-width: 620px) {
    .container { width: 100% !important; }
    .pad { padding-left: 22px !important; padding-right: 22px !important; }
    .brand-mark, .brand-side { display: block !important; width: 100% !important; text-align: left !important; }
    .brand-side { padding-top: 14px !important; }
    .button a { display: block !important; }
  }
</style>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F3EE;">

<div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #F4F3EE;">
  Confirm your email address to continue with your application to London Churchill College.
</div>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F4F3EE" style="background-color: #F4F3EE;">
  <tr>
    <td align="center" style="padding: 32px 12px;">

      <table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px; max-width: 600px; background-color: #FFFFFF; border: 1px solid #E4E2DA;">

        <!-- Header: the crest, then the office this came from -->
        <tr>
          <td class="pad" bgcolor="#1A2030" style="background-color: #1A2030; border-bottom: 4px solid #8A6A1F; padding: 26px 40px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td class="brand-mark" valign="middle" align="left" style="width: 58%;">
                  @if(!empty($logoPath) && isset($message))
                    <img src="{{ $message->embed($logoPath) }}" width="208" alt="London Churchill College" style="display: block; width: 208px; max-width: 208px; height: auto; border: 0; outline: none; text-decoration: none;">
                  @else
                    <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 15px; line-height: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #FFFFFF;">London Churchill College</div>
                  @endif
                </td>
                <td class="brand-side" valign="middle" align="right" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 16px; letter-spacing: 1.6px; text-transform: uppercase; color: #C7B27A;">
                  Admissions
                  <div style="margin-top: 3px; letter-spacing: 1.2px; color: #9AA0AE;">Applicant account</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td class="pad" style="padding: 36px 40px 8px;">
            <h1 style="margin: 0 0 20px; font-family: Georgia, 'Times New Roman', serif; font-size: 26px; line-height: 32px; font-weight: normal; color: #1B1E27;">Verify your email address</h1>
            <p style="margin: 0 0 14px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Hello,</p>
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Thank you for creating an applicant account with London Churchill College. Please confirm that @if($email)<strong style="white-space: nowrap;">{{ $email }}</strong>@else this @endif is your email address. Your reference number, offer letter and acceptance form are all sent here, so we need to know it is yours before you can start an application.</p>
          </td>
        </tr>

        <!-- The button -->
        <tr>
          <td class="pad" align="left" style="padding: 28px 40px 8px;">
            <table role="presentation" class="button" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td bgcolor="#1A2030" style="background-color: #1A2030; border-bottom: 3px solid #8A6A1F;">
                  <a href="{{ $url }}" target="_blank" style="display: inline-block; padding: 15px 34px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 20px; font-weight: bold; color: #FFFFFF; text-decoration: none; text-align: center;">Verify email address</a>
                </td>
              </tr>
            </table>
            <p style="margin: 16px 0 0; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 21px; color: #5B606C;">This link works for <strong style="color: #1B1E27;">60 minutes</strong>. If it has expired, sign in to your applicant account and choose <strong style="color: #1B1E27;">Resend verification email</strong>.</p>
          </td>
        </tr>

        <!-- Link to copy, for when the button does not work -->
        <tr>
          <td class="pad" style="padding: 22px 40px 6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F6F2E4" style="background-color: #F6F2E4; border: 1px solid #E3D9B5;">
              <tr>
                <td style="padding: 18px 24px;">
                  <div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 16px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #5A4A12;">Button not working?</div>
                  <div style="margin-top: 6px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 21px; color: #4A4636;">Copy this link into your browser:</div>
                  <div style="margin-top: 6px; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 20px; word-break: break-all;"><a href="{{ $url }}" target="_blank" style="color: #1A2030;">{{ $url }}</a></div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Received by mistake -->
        <tr>
          <td class="pad" style="padding: 22px 40px 8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F8ECEC" style="background-color: #F8ECEC; border: 1px solid #E3C3C8;">
              <tr>
                <td style="padding: 18px 24px;">
                  <div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 22px; font-weight: bold; color: #6A1522;">Did not create an account?</div>
                  <div style="margin-top: 4px; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 23px; color: #1B1E27;">You can ignore this email; the address will not be confirmed unless the link is used. If you are worried someone is using your email address, contact us at <a href="mailto:{{ $contactEmail }}" style="color: #1A2030; font-weight: bold;">{{ $contactEmail }}</a> or on <span style="font-weight: bold; white-space: nowrap;">{{ $contactPhone }}</span>.</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Sign-off -->
        <tr>
          <td class="pad" style="padding: 20px 40px 36px;">
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Kind regards,<br><strong>Admissions, London Churchill College</strong></p>
          </td>
        </tr>

        <!-- Footer: where we are, then the disclaimer the old email carried -->
        <tr>
          <td class="pad" bgcolor="#FAF9F5" style="background-color: #FAF9F5; border-top: 1px solid #E4E2DA; padding: 20px 40px;">
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 20px; color: #5B606C;">
              London Churchill College &middot; Barclay Hall, 156B Green Street, London, E7 8JQ<br>
              <span style="white-space: nowrap;">{{ $contactPhone }}</span> &middot; <a href="mailto:{{ $contactEmail }}" style="color: #5B606C;">{{ $contactEmail }}</a> &middot; <a href="https://lcc.ac.uk" style="color: #5B606C;">lcc.ac.uk</a>
            </p>
            <p style="margin: 12px 0 0; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 17px; color: #8A8E99;">This is an automated email; please do not reply to it. It may contain confidential information intended only for the person named above. If you have received it in error, please tell us and delete it.</p>
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>
