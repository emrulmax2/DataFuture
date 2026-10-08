{{--
    Complaint (Stage 1) receipt.

    The complaint is repeated back in full. It is investigated by people the
    student has not met, so their copy is the record of what they said.

    Table-based and inline-styled on purpose: this is email, where a stylesheet
    is a suggestion and flexbox does not exist. The logo is embedded rather than
    linked so it draws without the student having to "load remote images", and
    because a link would point at a host they are not signed in to.

    Everything merged here is escaped by Blade; the two free-text values go
    through nl2br after escaping so the student's own line breaks survive.
--}}
@php
    $documents = $request->documents->pluck('display_file_name')->filter()->values();

    $device = collect([$request->device_type, $request->device_os, $request->device_browser])
        ->filter()->implode(' · ');

    $submittedAt = $request->created_at
        ? $request->created_at->format('j F Y, g:ia')
        : date('j F Y, g:ia');

    $label = 'width: 40%; padding: 11px 12px 11px 0; border-bottom: 1px solid #E4E2DA; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 21px; color: #5B606C;';
    $value = 'padding: 11px 0; border-bottom: 1px solid #E4E2DA; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 21px; font-weight: bold; color: #1B1E27;';
    $valueSoft = 'padding: 11px 0; border-bottom: 1px solid #E4E2DA; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 22px; color: #1B1E27;';
@endphp
<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting">
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<title>Complaint received</title>
<style>
  body { margin: 0; padding: 0; }
  table { border-collapse: collapse; }
  a { color: #1A2030; }
  @media (max-width: 620px) {
    .container { width: 100% !important; }
    .pad { padding-left: 22px !important; padding-right: 22px !important; }
    .label, .value { display: block !important; width: 100% !important; }
    .label { padding-bottom: 2px !important; border-bottom: none !important; }
    .value { padding-top: 0 !important; }
    .brand-mark, .brand-side { display: block !important; width: 100% !important; text-align: left !important; }
    .brand-side { padding-top: 14px !important; }
  }
</style>
</head>
<body style="margin: 0; padding: 0; background-color: #F4F3EE;">

<div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #F4F3EE;">
  We have received your request. Reference {{ $request->ticket_ref }}. Someone will contact you within 5 working days.
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
                  @if($logoPath)
                    <img src="{{ $message->embed($logoPath) }}" width="208" alt="London Churchill College" style="display: block; width: 208px; max-width: 208px; height: auto; border: 0; outline: none; text-decoration: none;">
                  @else
                    <div style="font-family: Georgia, 'Times New Roman', serif; font-size: 15px; line-height: 20px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #FFFFFF;">London Churchill College</div>
                  @endif
                </td>
                <td class="brand-side" valign="middle" align="right" style="font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 16px; letter-spacing: 1.6px; text-transform: uppercase; color: #C7B27A;">
                  College Registry
                  <div style="margin-top: 3px; letter-spacing: 1.2px; color: #9AA0AE;">Online form receipt</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Intro -->
        <tr>
          <td class="pad" style="padding: 36px 40px 8px;">
            <h1 style="margin: 0 0 20px; font-family: Georgia, 'Times New Roman', serif; font-size: 26px; line-height: 32px; font-weight: normal; color: #1B1E27;">Complaint received</h1>
            <p style="margin: 0 0 14px; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Dear {{ $studentName }},</p>
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Thank you for submitting your complaint. We have received it, and it will be handled under the Complaints Policy and Procedure.</p>
          </td>
        </tr>

        <!-- Reference number -->
        <tr>
          <td class="pad" style="padding: 22px 40px 6px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F6F2E4" style="background-color: #F6F2E4; border: 1px solid #E3D9B5;">
              <tr>
                <td style="padding: 20px 24px;">
                  <div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 16px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; color: #5A4A12;">Your reference number</div>
                  <div style="margin-top: 6px; font-family: Georgia, 'Times New Roman', serif; font-size: 28px; line-height: 34px; color: #1A2030;">{{ $request->ticket_ref }}</div>
                  <div style="margin-top: 8px; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 21px; color: #4A4636;">Please quote this reference if you contact us about your request.</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- What happens next -->
        <tr>
          <td class="pad" style="padding: 24px 40px 4px;">
            <h2 style="margin: 0 0 8px; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; line-height: 26px; font-weight: normal; color: #1B1E27;">What happens next</h2>
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">A member of staff will contact you within <strong>5 working days</strong>. You do not need to do anything else in the meantime.</p>
          </td>
        </tr>

        <!-- Submitted details -->
        <tr>
          <td class="pad" style="padding: 28px 40px 4px;">
            <h2 style="margin: 0 0 10px; font-family: Georgia, 'Times New Roman', serif; font-size: 19px; line-height: 26px; font-weight: normal; color: #1B1E27;">Details you submitted</h2>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top: 1px solid #E4E2DA;">
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Submitted on</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $submittedAt }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Full name</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $studentName }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Student ID</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $registrationNo ?: '—' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Email</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $studentEmail ?: '—' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Mobile number</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $studentMobile ?: '—' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Relationship to LCC</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $request->relationship ?: 'Student' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Programme / course</td>
                <td class="value" valign="top" style="{{ $value }}">{{ $request->course_name ?: '—' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Your complaint</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">{!! nl2br(e($request->complaint_details)) !!}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Spoken to anyone</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">{{ $request->spokenToLabel() }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Attempts to resolve</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">{!! nl2br(e($request->attempts)) !!}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Outcome wanted</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">{!! nl2br(e($request->desired_outcome)) !!}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Supporting documents</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">
                  @if($documents->count())
                    {!! nl2br(e($documents->implode("\n"))) !!}
                  @else
                    None attached
                  @endif
                </td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Submitted from</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">{{ $device ?: 'Not recorded' }}</td>
              </tr>
              <tr>
                <td class="label" width="40%" valign="top" style="{{ $label }}">Declaration</td>
                <td class="value" valign="top" style="{{ $valueSoft }}">Confirmed, including your agreement that the complaint may be investigated within the College</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Received by mistake -->
        <tr>
          <td class="pad" style="padding: 28px 40px 8px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F8ECEC" style="background-color: #F8ECEC; border: 1px solid #E3C3C8;">
              <tr>
                <td style="padding: 18px 24px;">
                  <div style="font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 22px; font-weight: bold; color: #6A1522;">Did not make this request?</div>
                  <div style="margin-top: 4px; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 23px; color: #1B1E27;">If you have received this email by mistake, or you did not submit this request, please contact the College Registry immediately at <a href="mailto:{{ $registryEmail }}" style="color: #1A2030; font-weight: bold;">{{ $registryEmail }}</a> or on <span style="font-weight: bold; white-space: nowrap;">{{ $registryPhone }}</span>.</div>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Sign-off -->
        <tr>
          <td class="pad" style="padding: 20px 40px 36px;">
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 25px; color: #1B1E27;">Kind regards,<br><strong>London Churchill College</strong></p>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td class="pad" bgcolor="#FAF9F5" style="background-color: #FAF9F5; border-top: 1px solid #E4E2DA; padding: 20px 40px;">
            <p style="margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 20px; color: #5B606C;">This is an automated confirmation of your online form submission. Reference: {{ $request->ticket_ref }}</p>
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>
