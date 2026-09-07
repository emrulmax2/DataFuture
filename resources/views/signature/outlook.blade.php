{{--
    Outlook / Hotmail variant of the staff email signature.

    Same three-panel design as the Gmail file, retuned for Word: Outlook for
    Windows draws mail with the Word engine, which ignores letter-spacing,
    text-transform, box-shadow and CSS margins, and sizes images from their
    HTML attributes rather than CSS. So every dimension is declared as an
    attribute, spacing is carried by table cells and spacer rows rather than
    padding shorthands, tracking is faked with real spaces, and the font stack
    starts at Arial. It also renders correctly in Outlook.com / Hotmail on the
    web and in Apple Mail.
--}}
@php
    $icon = fn ($name) => ($sig['icons'][$name] ?? '');
    $telHref = 'tel:'.preg_replace('/[^0-9+]/', '', (string) ($sig['telephone'] ?? ''));
    $mobileHref = 'tel:'.preg_replace('/[^0-9+]/', '', (string) ($sig['mobile'] ?? ''));
    $showMobile = filled($sig['mobile'] ?? null);
    $showPhoto = filled($sig['photo_url'] ?? null);
    $roleLine = trim((string) ($sig['job_title'] ?? ''));
    $addressLines = collect([$sig['address_line_1'] ?? null, $sig['address_line_2'] ?? null])
        ->filter(fn ($item) => filled($item))->values();
    $body = 'font-family:Arial, Helvetica, sans-serif; mso-line-height-rule:exactly;';

    /* Word drops letter-spacing, so the designation's tracking is spelled out
       with thin spaces instead of being asked for in CSS. */
    $designation = mb_strtoupper($roleLine);
    $designation = implode("\u{2009}", preg_split('//u', $designation, -1, PREG_SPLIT_NO_EMPTY));

    $rows = [];
    if (filled($sig['telephone'] ?? null)) {
        $ext = filled($sig['extension'] ?? null)
            ? '<span style="color:#9AA3AE;">&nbsp;&middot;&nbsp;</span><span style="color:#5A6069;">ext. '.e($sig['extension']).'</span>'
            : '';
        $rows[] = ['phone', 'Telephone', '<a href="'.$telHref.'" style="color:#12294A; text-decoration:none;">'.e($sig['telephone']).'</a>'.$ext];
    }
    if ($showMobile) {
        $rows[] = ['mobile', 'Mobile', '<a href="'.$mobileHref.'" style="color:#12294A; text-decoration:none;">'.e($sig['mobile']).'</a>'];
    }
    if (filled($sig['email'] ?? null)) {
        $rows[] = ['email', 'Email', '<a href="mailto:'.e($sig['email']).'" style="color:#12294A; text-decoration:none;">'.e($sig['email']).'</a>'];
    }
    if (filled($sig['website'] ?? null)) {
        $rows[] = ['website', 'Website', '<a href="'.e($sig['website_url']).'" style="color:#12294A; text-decoration:none;">'.e($sig['website']).'</a>'];
    }
    if ($addressLines->isNotEmpty()) {
        $rows[] = ['address', 'Address', '<span style="color:#5A6069;">'.$addressLines->map(fn ($line) => e($line))->implode('<br>').'</span>'];
    }
@endphp
<table cellpadding="0" cellspacing="0" border="0" width="830" bgcolor="#FFFFFF" style="border-collapse:collapse; width:830px; background-color:#FFFFFF; color-scheme:only light; box-shadow:0 16px 44px rgba(18,41,74,.34), 0 3px 10px rgba(18,41,74,.14);">
  <tr>
    <td width="5" bgcolor="#C8102E" style="width:5px; background-color:#C8102E; font-size:1px; line-height:1px;">&nbsp;</td>

    @if($showPhoto)
      <td width="218" bgcolor="#FFFFFF" style="width:218px; padding:0; vertical-align:top; background-color:#FFFFFF;">
        <img src="{{ $sig['photo_url'] }}" width="218" height="320" alt="{{ $sig['display_name'] }}" style="display:block; border:0; width:218px; height:320px;">
      </td>
      <td width="1" bgcolor="#D8E0EA" style="width:1px; background-color:#D8E0EA; font-size:1px; line-height:1px;">&nbsp;</td>
    @endif

    <td width="340" bgcolor="#FFFFFF" style="width:340px; padding:32px 24px 32px 30px; vertical-align:middle; background-color:#FFFFFF;">
      <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
        <tr>
          <td style="{{ $body }} font-size:30px; font-weight:bold; line-height:34px; color:#12294A;">{{ $sig['display_name'] }}</td>
        </tr>
        @if(filled($sig['qualifications'] ?? null))
          <tr>
            <td style="{{ $body }} font-size:12px; line-height:17px; color:#1B3A6B; padding:8px 0 0 0;">{{ $sig['qualifications'] }}</td>
          </tr>
        @endif
        @if(filled($roleLine))
          <tr>
            <td style="{{ $body }} font-size:11px; font-weight:bold; line-height:15px; color:#8A93A0; padding:10px 0 0 0;">{{ $designation }}</td>
          </tr>
        @endif
        <tr>
          <td style="padding:16px 0 0 0;">
            <table cellpadding="0" cellspacing="0" border="0" width="54" style="border-collapse:collapse; width:54px;">
              <tr><td height="3" bgcolor="#C8102E" style="height:3px; background-color:#C8102E; font-size:1px; line-height:1px;">&nbsp;</td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:20px 0 0 0;">
            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
              @foreach($rows as $item)
                <tr>
                  <td width="19" style="width:19px; padding:1px 13px {{ $loop->last ? '0' : '11px' }} 0; vertical-align:top;">
                    <img src="{{ $icon($item[0]) }}" width="19" height="19" alt="{{ $item[1] }}" style="display:block; border:0; width:19px; height:19px;">
                  </td>
                  <td style="{{ $body }} font-size:13px; line-height:19px; color:#2A3440; padding:0 0 {{ $loop->last ? '0' : '11px' }} 0;">{!! $item[2] !!}</td>
                </tr>
              @endforeach
            </table>
          </td>
        </tr>
      </table>
    </td>

    <td width="1" bgcolor="#D8E0EA" style="width:1px; background-color:#D8E0EA; font-size:1px; line-height:1px;">&nbsp;</td>
    <td width="156" align="right" bgcolor="#FFFFFF" background="{{ $icon('corner') }}" style="width:156px; padding:32px 30px 32px 25px; vertical-align:middle; background-color:#FFFFFF; background-image:url({{ $icon('corner') }}); background-repeat:no-repeat; background-position:right bottom;">
      <table cellpadding="0" cellspacing="0" border="0" align="right" style="border-collapse:collapse;">
        <tr>
          <td align="right" style="text-align:right;">
            <a href="{{ $sig['website_url'] }}" style="text-decoration:none;"><img src="{{ $icon('lcc-logo') }}" width="156" height="74" alt="{{ config('emailsignature.organisation') }}" style="display:block; border:0; width:156px; height:74px;"></a>
          </td>
        </tr>
        @if(!empty($sig['socials']))
          <tr>
            <td align="right" style="padding:26px 0 0 0; text-align:right;">
              <table cellpadding="0" cellspacing="0" border="0" align="right" style="border-collapse:collapse;">
                <tr>
                  @foreach($sig['socials'] as $network => $url)
                    @continue(blank($icon($network)))
                    <td style="padding:0 0 0 {{ $loop->first ? '0' : '9' }}px;"><a href="{{ $url }}" style="text-decoration:none;"><img src="{{ $icon($network) }}" width="22" height="22" alt="{{ ucfirst($network) }}" style="display:block; border:0; width:22px; height:22px;"></a></td>
                  @endforeach
                </tr>
              </table>
            </td>
          </tr>
        @endif
      </table>
    </td>
  </tr>
</table>
@if(filled($sig['disclaimer'] ?? null))
  <table cellpadding="0" cellspacing="0" border="0" width="830" style="border-collapse:collapse; width:830px;">
    <tr>
      <td style="{{ $body }} font-size:10.5px; line-height:17px; color:#6B727C; padding:16px 0 0 0;">{{ $sig['disclaimer'] }}</td>
    </tr>
  </table>
@endif
