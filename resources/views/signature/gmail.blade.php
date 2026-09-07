{{--
    Gmail / modern-client variant of the staff email signature.

    Three panels split by hairline rules: the ringed portrait, the contact
    details, and the crest with the social badges. The reference design builds
    this with absolute positioning and flexbox; neither survives an email
    client, so it is rebuilt here as nested tables with inline styles only —
    Gmail throws away <style> blocks and classes when a signature is pasted in.
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
    $sans = "'Helvetica Neue', Helvetica, Arial, sans-serif";

    /* The five contact rows, assembled once so they cannot drift apart. */
    $rows = [];
    if (filled($sig['telephone'] ?? null)) {
        $ext = filled($sig['extension'] ?? null)
            ? '<span style="color:#9AA3AE"> &nbsp;&middot;&nbsp; </span><span style="color:#5A6069">ext. '.e($sig['extension']).'</span>'
            : '';
        $rows[] = ['phone', 'Telephone', '<a href="'.$telHref.'" style="color:#12294A; text-decoration:none">'.e($sig['telephone']).'</a>'.$ext];
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
        $rows[] = ['address', 'Address', '<span style="color:#5A6069">'.$addressLines->map(fn ($line) => e($line))->implode('<br>').'</span>'];
    }
@endphp
<table cellpadding="0" cellspacing="0" border="0" width="830" bgcolor="#FFFFFF" style="border-collapse:collapse; width:830px; background:#FFFFFF; color-scheme:only light; box-shadow:0 16px 44px rgba(18,41,74,.34), 0 3px 10px rgba(18,41,74,.14)">
  <tr>
    {{-- The crimson rule that anchors the whole card --}}
    <td width="5" bgcolor="#C8102E" style="width:5px; background:#C8102E; font-size:1px; line-height:1px">&nbsp;</td>

    @if($showPhoto)
      <td width="218" bgcolor="#FFFFFF" style="width:218px; padding:0; vertical-align:top; background:#FFFFFF">
        <img src="{{ $sig['photo_url'] }}" width="218" height="320" alt="{{ $sig['display_name'] }}" style="display:block; border:0; width:218px; height:320px">
      </td>
      <td width="1" bgcolor="#D8E0EA" style="width:1px; background:#D8E0EA; font-size:1px; line-height:1px">&nbsp;</td>
    @endif

    <td width="340" bgcolor="#FFFFFF" style="width:340px; padding:32px 24px 32px 30px; vertical-align:middle; background:#FFFFFF">
      <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse">
        <tr>
          <td style="font:700 32px/1.05 {{ $sans }}; letter-spacing:-.026em; color:#12294A">{{ $sig['display_name'] }}</td>
        </tr>
        @if(filled($sig['qualifications'] ?? null))
          <tr>
            <td style="font:500 12px/1.4 {{ $sans }}; letter-spacing:.02em; color:#1B3A6B; padding-top:8px">{{ $sig['qualifications'] }}</td>
          </tr>
        @endif
        @if(filled($roleLine))
          <tr>
            {{-- Uppercased in PHP: Word ignores text-transform outright --}}
            <td style="font:600 11px/1.3 {{ $sans }}; letter-spacing:.17em; color:#8A93A0; padding-top:10px">{{ mb_strtoupper($roleLine) }}</td>
          </tr>
        @endif
        <tr>
          <td style="padding-top:16px">
            <table cellpadding="0" cellspacing="0" border="0" width="54" style="border-collapse:collapse; width:54px">
              <tr><td height="3" bgcolor="#C8102E" style="height:3px; background:#C8102E; font-size:1px; line-height:1px">&nbsp;</td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding-top:20px">
            <table cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse">
              @foreach($rows as $item)
                <tr>
                  <td width="19" style="width:19px; padding:0 13px {{ $loop->last ? '0' : '11px' }} 0; vertical-align:top">
                    <img src="{{ $icon($item[0]) }}" width="19" height="19" alt="{{ $item[1] }}" style="display:block; border:0; width:19px; height:19px; margin-top:1px">
                  </td>
                  <td style="font:400 13.5px/1.4 {{ $sans }}; color:#2A3440; padding:0 0 {{ $loop->last ? '0' : '11px' }} 0">{!! $item[2] !!}</td>
                </tr>
              @endforeach
            </table>
          </td>
        </tr>
      </table>
    </td>

    <td width="1" bgcolor="#D8E0EA" style="width:1px; background:#D8E0EA; font-size:1px; line-height:1px">&nbsp;</td>
    <td width="156" align="right" bgcolor="#FFFFFF" background="{{ $icon('corner') }}" style="width:156px; padding:32px 30px 32px 25px; vertical-align:middle; background-color:#FFFFFF; background-image:url({{ $icon('corner') }}); background-repeat:no-repeat; background-position:right bottom">
      <table cellpadding="0" cellspacing="0" border="0" align="right" style="border-collapse:collapse">
        <tr>
          <td align="right" style="text-align:right">
            <a href="{{ $sig['website_url'] }}" style="text-decoration:none"><img src="{{ $icon('lcc-logo') }}" width="156" height="74" alt="{{ config('emailsignature.organisation') }}" style="display:block; border:0; width:156px; height:74px"></a>
          </td>
        </tr>
        @if(!empty($sig['socials']))
          <tr>
            <td align="right" style="padding-top:26px; text-align:right">
              <table cellpadding="0" cellspacing="0" border="0" align="right" style="border-collapse:collapse">
                <tr>
                  @foreach($sig['socials'] as $network => $url)
                    @continue(blank($icon($network)))
                    <td style="padding-left:{{ $loop->first ? '0' : '9' }}px"><a href="{{ $url }}" style="text-decoration:none"><img src="{{ $icon($network) }}" width="22" height="22" alt="{{ ucfirst($network) }}" style="display:block; border:0; width:22px; height:22px"></a></td>
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
  <table cellpadding="0" cellspacing="0" border="0" width="830" style="border-collapse:collapse; width:830px">
    <tr>
      <td style="font:400 10.5px/1.6 {{ $sans }}; color:#6B727C; padding:16px 0 0 0">{{ $sig['disclaimer'] }}</td>
    </tr>
  </table>
@endif
