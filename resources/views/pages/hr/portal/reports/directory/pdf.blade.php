@php
    /*
     * Employee Telephone Directory PDF, new version (user's employee-telephone-directory.pdf design,
     * 26 Nov 2026): A4 portrait at DomPDF's 96 dpi, 45px sides (704px of content). One block per letter:
     * the letter and its count, then the people in two columns (down the left, then the right), each
     * with a dotted leader to the number. Data: TelephoneDirectory::byLetter.
     */
    $fontDir = resource_path('fonts/audit-certificate');
    $colW = 312;
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Employee Telephone Directory</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 50px 45px 66px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 10px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td { padding: 0; text-align: left; vertical-align: top; }
    .muted { color: #55616b; }

    .runfoot { position: fixed; bottom: -42px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 9px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .head { width: 704px; border-bottom: 2px solid #16242b; }
    .head td { padding-bottom: 8px; vertical-align: bottom; }
    .head__title { font-size: 20px; font-weight: 700; }
    .head__sub { font-size: 11px; color: #55616b; padding-top: 2px; }
    .head__meta { text-align: right; font-size: 10.5px; color: #55616b; line-height: 1.45; }

    .letter { width: 704px; border-bottom: 1px solid #d9dee3; page-break-inside: avoid; }
    .letter > tbody > tr > td { padding: 10px 0 8px; }
    .lt { width: 40px; font-size: 26px; font-weight: 700; color: #0e5a54; line-height: 1; }
    .lt small { display: block; font-size: 8.5px; font-weight: 600; color: #55616b; margin-top: 3px; }
    .e { width: {{ $colW }}px; margin-bottom: 5px; }
    .e td { vertical-align: bottom; }
    .e .n { font-weight: 700; font-size: 10px; white-space: nowrap; }
    .e .dots div { margin: 0 6px 3px; border-bottom: 1px dotted #b7bfc5; height: 1px; }
    .e .num { font-weight: 700; font-size: 11px; white-space: nowrap; text-align: right; }
    .e .l2 td { font-size: 8.5px; color: #55616b; padding-top: 1px; vertical-align: top; }
    .nof { color: #8a3d0c; font-weight: 700; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Employee Telephone Directory · Confidential</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Employee Telephone Directory</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ $n }} staff<br>Printed {{ $printed }}</td>
    </tr></table>

    @if(!$n)
        <p class="muted" style="margin-top: 20px;">Nobody matches these filters.</p>
    @endif
    @foreach($groups as $letter => $people)
        @php $cols = array_chunk($people, (int) ceil(count($people) / 2)); @endphp
        <table class="letter"><tbody><tr>
            <td class="lt">{{ $letter }}<small>{{ count($people) }}</small></td>
            @foreach([0, 1] as $c)
                <td style="width: {{ $colW }}px; {{ $c ? 'padding-left: 20px;' : '' }}">
                    @foreach($cols[$c] ?? [] as $p)
                        @php $main = $p['mob'] !== '' ? $p['mob'] : $p['tel']; @endphp
                        <table class="e">
                            <tr>
                                <td class="n">{{ $p['name'] }}</td>
                                <td class="dots" style="width: 100%;"><div></div></td>
                                <td class="num">@if($main !== ''){{ $main }}@else<span class="nof">No number on file</span>@endif</td>
                            </tr>
                            <tr class="l2">
                                <td colspan="2">@if($p['email'] !== ''){{ $p['email'] }}@else<span class="nof">No email on file</span>@endif</td>
                                <td style="text-align: right; white-space: nowrap;">@if($p['tel'] !== '' && $p['mob'] !== ''){{ $p['land'] ? 'Landline' : 'Also' }} {{ $p['tel'] }}@endif</td>
                            </tr>
                        </table>
                    @endforeach
                </td>
            @endforeach
        </tr></tbody></table>
    @endforeach
</body>
</html>
