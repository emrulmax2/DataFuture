{{--
    Induction invitation to a newly converted student (user's lcc_induction_invitation_template.html,
    30 Oct 2026). App\Mail\StudentInductionInvitation; data from the Gather event behind the course's
    booking link (App\Services\StudentEmail\InductionEventDetails):
      $firstName, $term, $event[title, booking_url, venue_name, venue_address, date_label, same_day, sessions[date, time, courses, sold_out]]
    "Confirm my attendance" opens the event's booking page. The dark-lettered Site Settings logo is
    embedded (the header is light); outside a mail (a preview) the hosted file is linked.
--}}
@php
    $darkLogo = App\Support\EmailLogo::darkPath();
    $logoUrl = is_file(storage_path('app/public/company_dark_logo.png')) ? url('storage/company_dark_logo.png') : url('storage/company_logo.png');
    $logoSrc = (isset($message) && $darkLogo) ? $message->embed($darkLogo) : $logoUrl;

    $sessions = $event['sessions'] ?? [];
    $count = count($sessions);
    $words = [2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five'];
    $bookingUrl = $event['booking_url'] ?? '#';
    $floor = trim((string) config('tasks.induction.registration_floor', '5th floor'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">
  <title>Action required: Confirm your LCC induction attendance</title>
  <style>
    @media screen and (max-width: 620px) {
      .outer-pad { padding: 14px 10px !important; }
      .content-pad { padding-left: 22px !important; padding-right: 22px !important; }
      .hero-title { font-size: 31px !important; line-height: 1.15 !important; }
      .section-title { font-size: 21px !important; }
      .mobile-full { width: 100% !important; }
      .mobile-stack, .mobile-stack tbody, .mobile-stack tr, .mobile-stack td { display: block !important; width: 100% !important; box-sizing: border-box !important; }
      .mobile-stack .card-gap { padding: 0 0 12px 0 !important; }
      .mobile-stack .card-top { padding: 0 !important; } /* stacked: no desktop gutter on the second card */
      .footer-pad { padding-left: 22px !important; padding-right: 22px !important; }
    }
  </style>
</head>
<body style="margin:0;padding:0;width:100%;background-color:#F3F5F9;font-family:Arial,Helvetica,sans-serif;color:#21324B;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
  <!-- Hidden inbox preview text -->
  <div style="display:none!important;visibility:hidden;opacity:0;height:0;width:0;overflow:hidden;color:transparent;mso-hide:all;">
    Action required: {{ $count > 1 ? 'choose your induction session and ' : '' }}complete the short registration form. See your date, {{ $count > 1 ? 'times' : 'time' }}, venue and what to bring.
  </div>
  <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;background:#F3F5F9;">
    <tr><td align="center" class="outer-pad" style="padding:32px 16px 44px;">
      <!--[if mso]><table role="presentation" width="640" align="center" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
      <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:separate;max-width:640px;width:100%;">
        <tr><td style="padding:0 0 14px 0;" align="left">
          <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"><tr>
            <td valign="middle" style="width:158px;padding-right:14px;">
              <img src="{{ $logoSrc }}" alt="London Churchill College" width="148" style="display:block;width:148px;max-width:100%;height:auto;border:0;outline:none;text-decoration:none;" />
            </td>
            <td valign="middle" align="right" style="font-size:11px;line-height:17px;letter-spacing:1.2px;color:#53627C;font-weight:bold;">STUDENT ENGAGEMENT @if($term)<br>{{ strtoupper($term) }}@endif</td>
          </tr></table>
        </td></tr>
        <tr>
          <td style="background:#FFFFFF;border:1px solid #E5EAF1;border-radius:18px;overflow:hidden;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
              <tr><td style="height:7px;line-height:7px;font-size:1px;background:#A41F4C;">&nbsp;</td></tr>
              <tr><td class="content-pad" style="padding:34px 40px 30px;background:#142B49;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="background:#FCE9EF;border-radius:5px;padding:7px 11px;color:#9E1B47;font-size:11px;font-weight:bold;letter-spacing:1.4px;line-height:14px;">ACTION REQUIRED</td></tr></table>
                <h1 class="hero-title" style="margin:18px 0 13px;font-size:39px;line-height:1.13;letter-spacing:-1.25px;color:#FFFFFF;font-weight:700;">Your college journey<br>starts here.</h1>
                <p style="margin:0;color:#DEE7F2;font-size:16px;line-height:25px;">You're invited to your London Churchill College induction. Please complete the short registration form to confirm your attendance.</p>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:separate;margin-top:22px;"><tr><td bgcolor="#A41F4C" style="border-radius:9px;background-color:#A41F4C;">
                  <a href="{{ $bookingUrl }}" target="_blank" style="display:inline-block;border:1px solid #B92B5B;border-radius:9px;padding:15px 21px;color:#FFFFFF;font-size:15px;font-weight:bold;line-height:20px;text-decoration:none;text-align:center;">Confirm my attendance &rarr;</a>
                </td></tr></table>
              </td></tr>
              <tr><td class="content-pad" style="padding:30px 40px 0;">
                <p style="font-size:16px;line-height:25px;margin:0 0 12px;color:#223552;">Dear {{ $firstName }},</p>
                <p style="font-size:15px;line-height:24px;margin:0 0 20px;color:#53627A;">We look forward to welcoming you to LCC! Your induction will help you get ready for your course, meet our team and get familiar with college life.</p>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:separate;background:#F5F8FC;border:1px solid #E4EAF2;border-radius:12px;">
                  <tr><td style="padding:18px 20px;">
                    <p style="margin:0 0 6px;font-size:11px;font-weight:bold;letter-spacing:1.2px;color:#748199;">{{ ($event['same_day'] ?? true) ? 'YOUR INDUCTION DAY' : 'YOUR INDUCTION DAYS' }}</p>
                    <p style="margin:0 0 7px;font-size:21px;line-height:27px;font-weight:bold;color:#142B49;">{{ $event['date_label'] ?? '' }}</p>
                    @if(!empty($event['venue_name']))
                      <p style="margin:0;font-size:14px;line-height:22px;color:#42536F;"><strong>{{ $event['venue_name'] }}</strong>@if(!empty($event['venue_address']))<br>{{ $event['venue_address'] }}@endif @if($floor !== '')<br>Registration: <strong>{{ $floor }}</strong>@endif</p>
                    @endif
                  </td></tr>
                </table>
              </td></tr>
              @if($count > 0)
              <tr><td class="content-pad" style="padding:26px 40px 0;">
                <h2 class="section-title" style="font-size:23px;line-height:29px;margin:0 0 8px;color:#142B49;">{{ $count > 1 ? 'Choose your session' : 'Your session' }}</h2>
                <p style="font-size:14px;line-height:22px;margin:0 0 18px;color:#576681;">
                  @if($count === 1)
                    Please attend the session below and confirm it on the registration form.
                  @elseif($event['same_day'] ?? true)
                    There are {{ $words[$count] ?? $count }} sessions on the same day. Attend the one scheduled for your course, or choose {{ $count === 2 ? 'the other session' : 'another session' }} if {{ $count === 2 ? 'the first' : 'it' }} is inconvenient.
                  @else
                    There are {{ $words[$count] ?? $count }} sessions. Attend the one scheduled for your course, or choose another one that suits you.
                  @endif
                </p>
                @foreach(array_chunk($sessions, 2, true) as $pair)
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="mobile-stack" style="border-collapse:collapse;{{ $loop->last ? '' : 'margin-bottom:16px;' }}">
                  <tr>
                    @foreach($pair as $index => $session)
                    @php $single = count($pair) === 1; @endphp
                    <td valign="top" width="{{ $single ? '100%' : '50%' }}" class="{{ $loop->first && !$single ? 'card-gap' : 'card-top' }}" style="width:{{ $single ? '100%' : '50%' }};padding:{{ $single ? '0' : ($loop->first ? '0 8px 0 0' : '0 0 0 8px') }};">
                      <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:separate;border:1px solid #DEE6F0;border-radius:12px;background:#FFFFFF;">
                        <tr><td style="padding:17px 17px 19px;">
                          <p style="margin:0 0 8px;color:#A41F4C;font-size:11px;font-weight:bold;letter-spacing:1.15px;">SESSION {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}@if(!empty($session['sold_out'])) <span style="color:#7A8698;">&middot; FULLY BOOKED</span>@endif</p>
                          @unless($event['same_day'] ?? true)
                            <p style="margin:0 0 4px;color:#53627C;font-size:13px;font-weight:bold;line-height:19px;">{{ $session['date'] }}</p>
                          @endunless
                          <p style="margin:0 0 {{ !empty($session['courses']) ? '12px' : '0' }};color:#172F4C;font-size:17px;font-weight:bold;line-height:23px;">{{ $session['time'] }}</p>
                          @if(!empty($session['courses']))
                            <p style="margin:0 0 5px;color:#7A8698;font-size:11px;letter-spacing:.6px;font-weight:bold;">COURSES</p>
                            <p style="margin:0;font-size:13px;color:#495A74;line-height:20px;">{!! collect($session['courses'])->map(fn ($course) => e($course))->implode('<br>') !!}</p>
                          @endif
                        </td></tr>
                      </table>
                    </td>
                    @endforeach
                  </tr>
                </table>
                @endforeach
              </td></tr>
              @endif
              <tr><td class="content-pad" style="padding:26px 40px 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:separate;background:#FFF5F7;border:1px solid #F7DDE6;border-radius:12px;">
                  <tr><td align="center" style="padding:23px 18px 24px;">
                    <p style="margin:0 0 7px;color:#8B1740;font-size:13px;font-weight:bold;letter-spacing:.3px;">YOUR NEXT STEP</p>
                    <p style="margin:0 0 18px;color:#2A3852;font-size:15px;line-height:23px;">Complete the registration form to confirm {{ $count > 1 ? 'which session you\'ll attend' : 'your attendance' }}.</p>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" class="mobile-full" style="border-collapse:separate;"><tr><td bgcolor="#A41F4C" align="center" style="border-radius:9px;background-color:#A41F4C;">
                      <a href="{{ $bookingUrl }}" target="_blank" style="display:inline-block;border:1px solid #A41F4C;border-radius:9px;padding:16px 23px;color:#FFFFFF;font-size:16px;font-weight:bold;line-height:20px;text-decoration:none;text-align:center;">Confirm my induction attendance &rarr;</a>
                    </td></tr></table>
                    <p style="margin:14px 0 0;font-size:12px;line-height:18px;color:#6F6271;">The registration form only takes a short time to complete.</p>
                  </td></tr>
                </table>
              </td></tr>
              <tr><td class="content-pad" style="padding:29px 40px 0;">
                <h2 class="section-title" style="font-size:21px;line-height:28px;margin:0 0 15px;color:#142B49;">Before you arrive</h2>
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse;">
                  <tr><td valign="top" width="30" style="padding:0 7px 14px 0;font-size:19px;">&#10003;</td><td style="padding:0 0 14px;color:#445673;font-size:14px;line-height:21px;"><strong style="color:#213451;">Bring a valid photo ID</strong><br>Passport, driving licence or other official photo ID.</td></tr>
                  <tr><td valign="top" style="padding:0 7px 14px 0;font-size:19px;">&#10003;</td><td style="padding:0 0 14px;color:#445673;font-size:14px;line-height:21px;"><strong style="color:#213451;">Arrive on time</strong><br>{{ $floor !== '' ? 'Please go to the '.$floor.' for registration and allow extra travel time.' : 'Please allow extra travel time.' }}</td></tr>
                  <tr><td valign="top" style="padding:0 7px 6px 0;font-size:19px;">&#10003;</td><td style="padding:0 0 6px;color:#445673;font-size:14px;line-height:21px;"><strong style="color:#213451;">Plan your journey</strong><br><a href="https://tfl.gov.uk/" target="_blank" style="color:#A41F4C;font-weight:bold;text-decoration:underline;">Check TfL travel updates</a> before travelling.</td></tr>
                </table>
              </td></tr>
              <tr><td class="content-pad" style="padding:26px 40px 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:separate;background:#F5F8FC;border-radius:12px;"><tr><td style="padding:19px 20px;">
                  <p style="margin:0 0 7px;color:#142B49;font-size:15px;font-weight:bold;">What will you learn?</p>
                  <p style="margin:0;color:#50617B;font-size:13px;line-height:21px;">How to read your timetable, collect your student ID card, access online learning platforms and library facilities, use student support services, and get answers to your questions.</p>
                </td></tr></table>
              </td></tr>
              <tr><td class="content-pad" style="padding:24px 40px 32px;">
                <p style="margin:0 0 10px;font-size:13px;line-height:21px;color:#556680;"><strong style="color:#263B59;">Can't attend or need help?</strong> @if($count > 1)You can attend {{ $count === 2 ? 'the other session' : 'another session' }}{{ ($event['same_day'] ?? true) ? ' on the same day' : '' }}. For further assistance, email @else Email @endif<a href="mailto:studentengagement@lcc.ac.uk" style="color:#A41F4C;text-decoration:underline;font-weight:bold;">studentengagement@lcc.ac.uk</a>.</p>
                <p style="margin:0;font-size:12px;line-height:20px;color:#66758D;">Induction is an important part of getting started. Missing it may cause difficulties accessing college services and may delay Student Finance payments if required enrolment steps remain incomplete.</p>
              </td></tr>
            </table>
          </td>
        </tr>
        <tr><td class="footer-pad" style="padding:24px 40px 0;text-align:center;">
          <p style="margin:0 0 6px;color:#253952;font-size:13px;line-height:18px;font-weight:bold;">London Churchill College</p>
          <p style="margin:0;color:#77849A;font-size:12px;line-height:19px;">Student Engagement Team &nbsp;|&nbsp; <a style="color:#5B708D;text-decoration:underline;" href="mailto:studentengagement@lcc.ac.uk">studentengagement@lcc.ac.uk</a></p>
          <p style="margin:12px 0 0;color:#8591A5;font-size:11px;line-height:17px;">This invitation has been sent to you in connection with your studies at LCC.</p>
        </td></tr>
      </table>
      <!--[if mso]></td></tr></table><![endif]-->
    </td></tr>
  </table>
</body>
</html>
