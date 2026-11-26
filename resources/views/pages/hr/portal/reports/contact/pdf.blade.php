@php
    /*
     * Employee Contact Details PDF, new version (user's employee-contact-details.pdf design, 26 Nov 2026):
     * A4 landscape at DomPDF's 96 dpi (1123 × 794 px), 40px sides, so 1043px of content. One table: the
     * person's own contact details, then the emergency contact on a tinted block. Data: ContactDetailsReport.
     */
    $fontDir = resource_path('fonts/audit-certificate');
    $dash = '<span class="miss">–</span>';
    $v = fn ($s) => $s !== '' ? e($s) : $dash;
    $parts = [['1' => 'Active staff', '0' => 'Inactive staff', '2' => 'Active and inactive staff'][$f['status']]];
    if ($f['q'] !== '') $parts[] = 'matching “'.$f['q'].'”';
    if ($f['gap'] !== '') $parts[] = $f['gap'] === 'ok' ? 'complete records' : mb_strtolower(\App\Services\HR\ContactDetailsReport::GAPS[$f['gap']]);
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Employee Contact Details</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 landscape; margin: 42px 40px 58px 40px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 9px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td, th { padding: 0; text-align: left; vertical-align: top; }
    .muted { color: #55616b; }

    .runfoot { position: fixed; bottom: -38px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 8.5px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .head { width: 1043px; border-bottom: 2px solid #16242b; }
    .head td { padding-bottom: 6px; vertical-align: bottom; }
    .head__title { font-size: 18px; font-weight: 700; }
    .head__sub { font-size: 10px; color: #55616b; padding-top: 2px; }
    .head__meta { text-align: right; font-size: 10px; color: #55616b; line-height: 1.45; }

    .list { width: 1043px; margin-top: 10px; }
    .list th { padding: 3px 5px; font-size: 8.5px; font-weight: 600; color: #55616b; vertical-align: bottom; }
    .list tr.grp th { color: #16242b; font-weight: 700; padding-bottom: 0; }
    .list tr.cols th { border-bottom: 1px solid #16242b; }
    .list td { padding: 4px 5px; border-top: 1px solid #eceff2; vertical-align: top; }
    .list tr.alt td { background: #f4f6f8; }
    .list .e { background: #fdf3ea; }
    .list tr.alt td.e { background: #fbeee2; }
    .list .e1 { border-left: 1px solid #f0d5bd; }
    .list .nm { font-weight: 700; }
    .list .rel { color: #55616b; font-size: 8.5px; }
    .miss { color: #c8601a; font-weight: 700; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 1043px;"><tr>
            <td>London Churchill College · Employee Contact Details · Confidential</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Employee Contact Details</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ implode(' · ', $parts) }} · {{ count($list) }} staff<br>Printed {{ $printed }}</td>
    </tr></table>

    @if(!count($list))
        <p class="muted" style="margin-top: 20px;">No staff match these filters.</p>
    @else
    <table class="list">
        <thead>
            <tr class="grp">
                <th></th><th colspan="4">Contact</th><th colspan="4" class="e e1">In an emergency</th>
            </tr>
            <tr class="cols">
                <th style="width: 124px;">Staff member</th>
                <th style="width: 70px;">Mobile</th>
                <th style="width: 70px;">Telephone</th>
                <th style="width: 132px;">Email</th>
                <th style="width: 170px;">Address</th>
                <th class="e e1" style="width: 108px;">Contact</th>
                <th class="e" style="width: 70px;">Mobile</th>
                <th class="e" style="width: 70px;">Telephone</th>
                <th class="e" style="width: 129px;">Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $i => $p)
                <tr class="{{ $i % 2 ? 'alt' : '' }}">
                    <td class="nm">{{ $p['name'] }}</td>
                    <td>{!! $v($p['mob']) !!}</td>
                    <td>{!! $v($p['tel']) !!}</td>
                    <td>{!! $v($p['email']) !!}</td>
                    <td>{!! $v($p['address']) !!}</td>
                    <td class="e e1">@if($p['eName'] !== ''){{ $p['eName'] }}@if($p['eRel'] !== '')<br><span class="rel">{{ $p['eRel'] }}</span>@endif @else{!! $dash !!}@endif</td>
                    <td class="e">{!! $v($p['eMob']) !!}</td>
                    <td class="e">{!! $v($p['eTel']) !!}</td>
                    <td class="e">{!! $v($p['eEmail']) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>
