{{--
    Phone variant of the staff email signature — one column, 340px wide.

    The desktop card is 830px across three panels, which a phone can only
    shrink until nothing is legible, so this is a genuine re-layout rather than
    a scaled copy: the crimson rule moves from the left edge to the top, the
    portrait becomes a round badge beside the name, and the three panels stack.

    Everything the Apple variant does for data detectors applies here twice
    over, because this is read on exactly the devices that run them: iOS Mail
    and Gmail on Android both linkify loose phone numbers and addresses in
    their own blue. Anything detectable is given a link of its own, and the
    badge is drawn at 3x because phones run two to three device pixels to the
    CSS pixel.
--}}
@php
    $icon = fn ($name) => ($sig['icons'][$name] ?? '');
    $badge = (string) ($sig['badge_url'] ?? '');
    $showBadge = filled($badge);
    $showMobile = filled($sig['mobile'] ?? null);
    $roleLine = trim((string) ($sig['job_title'] ?? ''));
    $addressLines = collect([$sig['address_line_1'] ?? null, $sig['address_line_2'] ?? null])
        ->filter(fn ($item) => filled($item))->values();
    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    $telHref = 'tel:'.preg_replace('/[^0-9+]/', '', (string) ($sig['telephone'] ?? ''))
        .(filled($sig['extension'] ?? null) ? ','.preg_replace('/\D/', '', (string) $sig['extension']) : '');
    $mobileHref = 'tel:'.preg_replace('/[^0-9+]/', '', (string) ($sig['mobile'] ?? ''));
    $mapHref = 'https://maps.apple.com/?q='.rawurlencode(trim(rtrim($addressLines->implode(' '), ' ,')));

    $rows = [];
    if (filled($sig['telephone'] ?? null)) {
        $ext = filled($sig['extension'] ?? null)
            ? '<span style="color:#9AA3AE"> &middot; </span><span style="color:#5A6069">ext. '.e($sig['extension']).'</span>'
            : '';
        $rows[] = ['phone', 'Telephone', '<a href="'.$telHref.'" style="color:#12294A; text-decoration:none">'.e($sig['telephone']).$ext.'</a>'];
    }
    if ($showMobile) {
        $rows[] = ['mobile', 'Mobile', '<a href="'.$mobileHref.'" style="color:#12294A; text-decoration:none">'.e($sig['mobile']).'</a>'];
    }
    if (filled($sig['email'] ?? null)) {
        $rows[] = ['email', 'Email', '<a href="mailto:'.e($sig['email']).'" style="color:#12294A; text-decoration:none">'.e($sig['email']).'</a>'];
    }
    if (filled($sig['website'] ?? null)) {
        $rows[] = ['website', 'Website', '<a href="'.e($sig['website_url']).'" style="color:#12294A; text-decoration:none">'.e($sig['website']).'</a>'];
    }
    if ($addressLines->isNotEmpty()) {
        $rows[] = ['address', 'Address', '<a href="'.e($mapHref).'" style="color:#5A6069; text-decoration:none">'
            .$addressLines->map(fn ($line) => e($line))->implode('<br>').'</a>'];
    }
@endphp
<table cellpadding="0" cellspacing="0" border="0" width="340" bgcolor="#FFFFFF" style="border-collapse:collapse; width:340px; max-width:100%; background:#FFFFFF; color-scheme:only light; -webkit-text-size-adjust:100%; box-shadow:0 10px 28px rgba(18,41,74,.28), 0 2px 7px rgba(18,41,74,.12)">
  {{-- The crimson rule turns horizontal: a vertical one would eat width the
       phone cannot spare, and there is no tall panel left to anchor it to --}}
  <tr>
    <td height="4" bgcolor="#C8102E" style="height:4px; background:#C8102E; font-size:1px; line-height:1px">&nbsp;</td>
  </tr>

  {{-- Header: badge and identity, on the pale tint the badge is drawn onto --}}
  <tr>
    <td bgcolor="#F4F7FB" style="background:#F4F7FB; padding:18px 16px 16px 16px">
      <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse; width:100%">
        <tr>
          @if($showBadge)
            <td width="88" style="width:88px; padding:0 12px 0 0; vertical-align:middle">
              <img src="{{ $badge }}" width="88" height="88" alt="{{ $sig['display_name'] }}" style="display:block; border:0; width:88px; height:88px">
            </td>
          @endif
          <td style="vertical-align:middle">
            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse">
              <tr>
                <td style="font:700 20px/1.15 {{ $sans }}; letter-spacing:-.018em; color:#12294A">{{ $sig['display_name'] }}</td>
              </tr>
              @if(filled($roleLine))
                <tr>
                  {{-- Uppercased in PHP so all four variants read identically --}}
                  <td style="font:600 9.5px/1.35 {{ $sans }}; letter-spacing:.13em; color:#8A93A0; padding-top:7px">{{ mb_strtoupper($roleLine) }}</td>
                </tr>
              @endif
              @if(filled($sig['qualifications'] ?? null))
                <tr>
                  <td style="font:600 10px/1.45 {{ $sans }}; letter-spacing:.01em; color:#1B3A6B; padding-top:6px">{{ $sig['qualifications'] }}</td>
                </tr>
              @endif
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  {{-- Contact rows: same glyphs as the desktop card, spaced for a thumb --}}
  <tr>
    <td style="padding:18px 16px 4px 16px">
      <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse">
        @foreach($rows as $item)
          <tr>
            <td width="19" style="width:19px; padding:0 12px {{ $loop->last ? '0' : '14px' }} 0; vertical-align:top">
              <img src="{{ $icon($item[0]) }}" width="19" height="19" alt="{{ $item[1] }}" style="display:block; border:0; width:19px; height:19px; margin-top:1px">
            </td>
            <td style="font:400 14px/1.45 {{ $sans }}; color:#2A3440; padding:0 0 {{ $loop->last ? '0' : '14px' }} 0">{!! $item[2] !!}</td>
          </tr>
        @endforeach
      </table>
    </td>
  </tr>

  {{-- Crest left, socials right, on one line at this width --}}
  <tr>
    <td style="padding:20px 16px 20px 16px">
      <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse:collapse; width:100%">
        <tr>
          <td align="left" style="text-align:left; vertical-align:middle">
            <a href="{{ $sig['website_url'] }}" style="text-decoration:none"><img src="{{ $icon('lcc-logo') }}" width="120" height="57" alt="{{ config('emailsignature.organisation') }}" style="display:block; border:0; width:120px; height:57px"></a>
          </td>
          @if(!empty($sig['socials']))
            <td align="right" style="text-align:right; vertical-align:middle">
              <table cellpadding="0" cellspacing="0" border="0" align="right" style="border-collapse:collapse">
                <tr>
                  @foreach($sig['socials'] as $network => $url)
                    @continue(blank($icon($network)))
                    <td style="padding-left:{{ $loop->first ? '0' : '7' }}px"><a href="{{ $url }}" style="text-decoration:none"><img src="{{ $icon($network) }}" width="24" height="24" alt="{{ ucfirst($network) }}" style="display:block; border:0; width:24px; height:24px"></a></td>
                  @endforeach
                </tr>
              </table>
            </td>
          @endif
        </tr>
      </table>
    </td>
  </tr>

  @if(filled($sig['disclaimer'] ?? null))
    {{-- Banded rather than loose: at this width the disclaimer is the tallest
         thing in the signature, and the tint keeps it from reading as part of
         the message --}}
    <tr>
      <td bgcolor="#EDEFF2" style="background:#EDEFF2; padding:14px 16px 14px 16px; font:400 10px/1.6 {{ $sans }}; color:#6B727C">{{ $sig['disclaimer'] }}</td>
    </tr>
  @endif
</table>
