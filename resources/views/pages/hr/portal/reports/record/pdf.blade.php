@php
    /*
     * Employee Record Card PDF, new version (user's employee-record-card.pdf design, 26 Nov 2026): one A4
     * portrait page per person (794 × 1123 px at DomPDF's 96 dpi, 56px sides, so 682px of content).
     * Data: RecordCardReport::staff. Key details that are missing show in orange; optional ones in grey.
     */
    use App\Services\HR\RecordCardReport as R;

    $fontDir = resource_path('fonts/audit-certificate');
    $total = count($list);
    $date = fn ($iso) => $iso ? date('j F Y', strtotime($iso)) : '';
    $row = function ($label, $v, $kind = 'key') {
        if ($v !== '' && $v !== null) return '<tr><td class="k">'.e($label).'</td><td class="v">'.e($v).'</td></tr>';
        $text = !in_array($kind, ['key', 'opt'], true) ? $kind : 'Not recorded';

        return '<tr><td class="k">'.e($label).'</td><td class="v '.($kind === 'key' ? 'miss' : 'none').'">'.e($text).'</td></tr>';
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Employee Record Card</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 48px 56px 40px 56px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 12px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td { padding: 0; vertical-align: top; text-align: left; }
    .page { position: relative; height: 1030px; page-break-after: always; }
    .page.last { page-break-after: auto; }

    .top { width: 682px; border-bottom: 2px solid #16242b; }
    .top td { padding-bottom: 12px; vertical-align: bottom; }
    .org { font-size: 10px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #0e5a54; }
    .top h1 { margin: 4px 0 0; font-size: 21px; font-weight: 700; }
    .meta { text-align: right; font-size: 10.5px; color: #55616b; line-height: 1.5; }
    .conf { padding: 1px 7px; border: 1px solid #8a3d0c; border-radius: 3px; color: #8a3d0c; font-weight: 700; letter-spacing: 0.06em; font-size: 9.5px; }

    .hero { width: 682px; margin-top: 22px; background: #f4f6f8; border-radius: 14px; }
    .hero td { padding: 22px 0; vertical-align: middle; }
    .av { width: 72px; height: 50px; padding-top: 22px; border-radius: 36px; background: #0e5a54; color: #ffffff; font-size: 22px; font-weight: 700; text-align: center; line-height: 1; }
    .photo { width: 72px; height: 72px; border-radius: 36px; }
    .name { font-size: 22px; font-weight: 700; }
    .pill { padding: 1px 8px; border-radius: 9px; background: #0e5a54; color: #ffffff; font-size: 10.5px; font-weight: 700; }
    .pill.off { background: #8a3d0c; }
    .job { font-size: 12.5px; color: #55616b; margin-top: 4px; }
    .fact { font-size: 9.5px; color: #55616b; text-transform: uppercase; letter-spacing: 0.05em; }
    .fact b { display: block; margin-top: 3px; font-size: 13px; color: #16242b; letter-spacing: 0; text-transform: none; }

    .comp { width: 682px; margin-top: 18px; }
    .comp td { vertical-align: middle; font-size: 11px; color: #55616b; }
    .meter { height: 5px; background: #e9edf0; border-radius: 3px; }
    .meter div { height: 5px; background: #0e5a54; border-radius: 3px; }

    .grid { width: 682px; margin-top: 18px; }
    .grid > tbody > tr > td { width: 321px; padding-bottom: 20px; }
    .grid > tbody > tr > td.gap { width: 40px; padding: 0; }
    h3 { margin: 0 0 4px; padding-bottom: 5px; border-bottom: 1px solid #16242b; font-size: 10.5px; font-weight: 700; letter-spacing: 0.07em; text-transform: uppercase; }
    h3.em { color: #8a3d0c; border-bottom-color: #c8601a; }
    .dl { width: 100%; }
    .dl td { padding: 6px 0; border-bottom: 1px solid #eceff2; font-size: 11.5px; }
    .dl tr:last-child td { border-bottom: 0; }
    .dl td.k { width: 104px; color: #55616b; }
    .dl td.v { font-weight: 600; }
    .dl td.none { font-weight: 400; color: #8b949c; }
    .dl td.miss { font-weight: 400; color: #c8601a; }

    .foot { position: absolute; left: 0; right: 0; bottom: 0; border-top: 1px solid #d9dee3; padding-top: 8px; font-size: 9.5px; color: #55616b; }
    .foot td { width: 341px; }
</style>
</head>
<body>
    @forelse($list as $i => $r)
        @php
            $name = trim(implode(' ', array_filter([$r['title'], $r['forename'], $r['surname']]))) ?: 'Unnamed';
            $init = strtoupper(mb_substr($r['forename'] ?: '?', 0, 1).mb_substr($r['surname'], 0, 1));
            $active = $r['status'] === 1;
            $done = count(R::KEY) - count($r['missing']);
        @endphp
        <div class="page{{ $i === $total - 1 ? ' last' : '' }}">
            <table class="top"><tr>
                <td><div class="org">{{ R::COMPANY }}</div><h1>Employee Record Card</h1></td>
                <td class="meta"><span class="conf">CONFIDENTIAL</span><br>Printed {{ $printed }}</td>
            </tr></table>

            <table class="hero"><tr>
                <td style="width: 96px; padding-left: 22px;">@if(!empty($r['photoPath']))<img class="photo" src="{{ $r['photoPath'] }}" alt="">@else<div class="av">{{ $init }}</div>@endif</td>
                <td>
                    <span class="name">{{ $name }}</span>&nbsp; <span class="pill{{ $active ? '' : ' off' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
                    <div class="job">{{ $r['jobTitle'] !== '' ? $r['jobTitle'].($r['department'] !== '' ? ' · '.$r['department'] : '') : 'Job title not recorded' }}</div>
                </td>
                <td style="width: 90px;" class="fact">Works no.<b>{{ $r['works'] !== '' ? $r['works'] : '–' }}</b></td>
                <td style="width: 130px; padding-right: 22px;" class="fact">Service<b>{{ $r['service'] ?: '–' }}</b></td>
            </tr></table>

            <table class="comp"><tr>
                <td style="width: 170px;"><b style="color: #16242b;">{{ $done }} of {{ count(R::KEY) }}</b> key details recorded</td>
                <td><div class="meter"><div style="width: {{ round(100 * $done / count(R::KEY)) }}%;"></div></div></td>
            </tr></table>

            <table class="grid"><tbody>
                <tr>
                    <td>
                        <h3>Personal details</h3>
                        <table class="dl">
                            {!! $row('Title', $r['title']) !!}
                            {!! $row('Forename', $r['forename']) !!}
                            {!! $row('Surname', $r['surname']) !!}
                            {!! $row('Date of birth', $r['dob'] ? $date($r['dob']).' (age '.$r['age'].')' : '') !!}
                            {!! $row('Gender', $r['gender']) !!}
                            {!! $row('Ethnic origin', $r['ethnicity']) !!}
                            {!! $row('Nationality', $r['nationality']) !!}
                            {!! $row('NI number', $r['ni']) !!}
                        </table>
                    </td>
                    <td class="gap"></td>
                    <td>
                        <h3>Employment details</h3>
                        <table class="dl">
                            {!! $row('Company', R::COMPANY) !!}
                            {!! $row('Works number', $r['works']) !!}
                            {!! $row('Job title', $r['jobTitle']) !!}
                            {!! $row('Department', $r['department']) !!}
                            {!! $row('Started on', $date($r['started'])) !!}
                            {!! $row('Ended on', $date($r['ended']), $active ? 'Still employed' : 'opt') !!}
                            {!! $row('Current status', $active ? 'Active' : 'Inactive') !!}
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3>Contact information</h3>
                        <table class="dl">
                            {!! $row('Address', $r['address']) !!}
                            {!! $row('Telephone', $r['telephone'], 'opt') !!}
                            {!! $row('Mobile', $r['mobile']) !!}
                            {!! $row('Email', $r['email']) !!}
                        </table>
                    </td>
                    <td class="gap"></td>
                    <td>
                        <h3 class="em">Emergency contact</h3>
                        <table class="dl">
                            {!! $row('Name', $r['eName']) !!}
                            {!! $row('Relationship', $r['eRel'], 'opt') !!}
                            {!! $row('Telephone', $r['eTel'], 'opt') !!}
                            {!! $row('Mobile', $r['eMob']) !!}
                            {!! $row('Email', $r['eEmail'], 'opt') !!}
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <h3>Other details</h3>
                        <table style="width: 682px;"><tr>
                            <td style="width: 321px;"><table class="dl">{!! $row('Disabled', $r['disability']) !!}</table></td>
                            <td style="width: 40px;"></td>
                            <td style="width: 321px;"><table class="dl">{!! $row('Car registration', $r['carReg'], 'opt') !!}</table></td>
                        </tr></table>
                    </td>
                </tr>
            </tbody></table>

            <table class="foot"><tr>
                <td>{{ R::COMPANY }} · HR</td>
                <td style="text-align: right;">Record {{ $i + 1 }} of {{ $total }}</td>
            </tr></table>
        </div>
    @empty
        <p>No employees match these filters.</p>
    @endforelse
</body>
</html>
