@php
    /*
     * Birthday List PDF, new version (user's birthday-list-report.pdf design, 26 Nov 2026): A4 portrait
     * (794 × 1123 px at DomPDF's 96 dpi, 45px sides, so 704px of content). Page 1 opens with the
     * 12-month wall; the list follows, January to December. Data: App\Services\HR\BirthdayList::build.
     * DomPDF notes: no box-shadow (rings are borders), band colours on the <tr>, widths are content boxes.
     */
    use App\Services\HR\BirthdayList;

    $fontDir = resource_path('fonts/audit-certificate');
    $today = $data['today'];
    $MN = BirthdayList::MONTHS;
    $list = $data['list'];
    $bands = [];
    foreach ($list as $p) {
        $bands[$p['m']] = ($bands[$p['m']] ?? 0) + 1;
    }
    $rows = array_chunk($data['wall'], 4);
@endphp
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Birthday List</title>
<style>
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 400; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-400.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 600; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-600.ttf") format('truetype'); }
    @font-face { font-family: 'Hanken Grotesk'; font-weight: 700; font-style: normal; src: url("{{ $fontDir }}/HankenGrotesk-700.ttf") format('truetype'); }

    @page { size: A4 portrait; margin: 50px 45px 66px 45px; }
    body { margin: 0; color: #16242b; font-family: 'Hanken Grotesk', Arial, sans-serif; font-size: 11px; line-height: 1.2; }
    table { border-collapse: collapse; }
    td, th { padding: 0; text-align: left; vertical-align: top; }
    .muted { color: #55616b; }

    .runfoot { position: fixed; bottom: -42px; left: 0; right: 0; }
    .runfoot td { color: #55616b; font-size: 9px; }
    .runfoot .right { text-align: right; }
    .pagenum:after { content: "Page " counter(page){!! !empty($pages) ? ' " of '.(int) $pages.'"' : '' !!}; }

    .head { width: 704px; border-bottom: 2px solid #16242b; }
    .head td { padding-bottom: 8px; vertical-align: bottom; }
    .head__title { font-size: 19px; font-weight: 700; }
    .head__sub { font-size: 11px; color: #55616b; padding-top: 2px; }
    .head__meta { text-align: right; font-size: 10.5px; color: #55616b; line-height: 1.45; }

    h2 { margin: 0; font-size: 14px; font-weight: 700; }
    .sec { margin-top: 16px; }

    /* The year ahead: 4 cards a row */
    .wall { width: 704px; margin-top: 8px; }
    .wall td.slot { width: 170px; padding: 0 8px 8px 0; }
    .wall td.slot.last { padding-right: 0; }
    .card { border: 1px solid #d9dee3; border-radius: 8px; padding: 7px 7px 6px; height: 166px; }
    .card__head { width: 154px; margin-bottom: 3px; }
    .card__head td { vertical-align: middle; }
    .card__month { font-size: 11px; font-weight: 700; }
    .card__year { font-size: 10.5px; color: #55616b; }
    .card__cnt { text-align: right; }
    .pill { display: inline-block; min-width: 14px; padding: 1px 6px; border-radius: 8px; background: #e6ecef; font-size: 10px; font-weight: 700; text-align: center; }
    .cal { width: 154px; }
    .cal td { width: 22px; height: 21px; text-align: center; vertical-align: middle; font-size: 8.5px; color: #7b858d; }
    .cal tr.dow td { height: 12px; font-size: 7.5px; font-weight: 600; color: #8b949c; }
    .cal td.past { color: #c0c6cb; }
    /* Block circles with top padding: DomPDF sets text low in an inline-block (tuned at 500 dpi). */
    .dc { display: block; width: 17px; height: 15px; padding-top: 2px; margin: 0 auto; border-radius: 9px; line-height: 1; text-align: center; font-weight: 700; }
    .dc.b1 { background: #cfe8e3; color: #0b4a45; }
    .dc.b2 { background: #7fbdb5; color: #04302c; }
    .dc.b3 { background: #0e5a54; color: #ffffff; }
    .dc.tod { width: 14px; height: 13px; padding-top: 1px; border: 1.5px solid #c8601a; color: #16242b; }

    /* The list */
    .listhead { width: 704px; margin-top: 18px; }
    .list { width: 704px; margin-top: 8px; font-size: 10.5px; }
    .list th { padding: 4px 6px; font-size: 10px; font-weight: 600; color: #55616b; border-bottom: 1px solid #16242b; white-space: nowrap; }
    .list td { padding: 4px 6px; vertical-align: middle; border-top: 1px solid #eceff2; }
    .list tr.band { background: #e6ecef; }
    .list tr.band td { font-weight: 700; padding: 6px; border-top: 0; }
    .list tr.band span { font-weight: 400; color: #55616b; }
    .list tr.alt { background: #f4f6f8; }
    .list tr.today { background: #e2f1ee; }
    .list .day b { font-size: 11.5px; }
    .list .r { text-align: right; }
    .list .nm { font-weight: 700; }
    .miss { color: #8a3d0c; font-weight: 700; }
    .tag { padding: 1px 6px; border-radius: 8px; background: #fbe9d8; color: #7a3409; font-size: 9px; font-weight: 700; }
    .tag.today { background: #0e5a54; color: #ffffff; }
    .nodob { margin-top: 10px; font-size: 10px; color: #55616b; }
    .nodob b { color: #8a3d0c; }
    .empty { margin-top: 10px; padding: 16px; border: 1px dashed #c3c9d1; text-align: center; color: #55616b; }
</style>
</head>
<body>
    <div class="runfoot">
        <table style="width: 704px;"><tr>
            <td>London Churchill College · Birthday List</td>
            <td class="right"><span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table class="head"><tr>
        <td>
            <div class="head__title">Birthday List</div>
            <div class="head__sub">London Churchill College</div>
        </td>
        <td class="head__meta">{{ $scope }}<br>Printed {{ $today->format('j M Y') }}</td>
    </tr></table>

    <div class="sec">
        <h2>The year ahead</h2>
        <table class="wall">
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $i => $w)
                        @php
                            $first = $w['first'];
                            $dim = $first->daysInMonth;
                            $lead = ($first->dayOfWeek + 6) % 7;
                            $cells = array_merge(array_fill(0, $lead, null), range(1, $dim));
                            $weeks = array_chunk($cells, 7);
                        @endphp
                        <td class="slot{{ $i === 3 ? ' last' : '' }}">
                            <div class="card">
                                <table class="card__head"><tr>
                                    <td><span class="card__month">{{ $MN[$first->month - 1] }}</span> <span class="card__year">{{ $first->year }}</span></td>
                                    <td class="card__cnt"><span class="pill">{{ $w['count'] }}</span></td>
                                </tr></table>
                                <table class="cal">
                                    <tr class="dow"><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td><td>S</td></tr>
                                    @foreach($weeks as $week)
                                        <tr>
                                            @for($c = 0; $c < 7; $c++)
                                                @php $d = $week[$c] ?? null; @endphp
                                                @if($d === null)
                                                    <td></td>
                                                @else
                                                    @php
                                                        $n = $w['days'][$d] ?? 0;
                                                        $isToday = $first->year === $today->year && $first->month === $today->month && $d === $today->day;
                                                        $past = $first->year === $today->year && $first->month === $today->month && $d < $today->day;
                                                    @endphp
                                                    @if($n || $isToday)
                                                        <td><div class="dc{{ $n ? ' b'.min(3, $n) : '' }}{{ $isToday ? ' tod' : '' }}">{{ $d }}</div></td>
                                                    @else
                                                        <td class="{{ $past ? 'past' : '' }}">{{ $d }}</td>
                                                    @endif
                                                @endif
                                            @endfor
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    </div>

    <table class="listhead"><tr><td>
        <h2>{{ $data['filters']['month'] ? $MN[$data['filters']['month'] - 1].' birthdays' : 'All birthdays' }}</h2>
        <div class="muted" style="padding-top: 2px;">{{ count($list) }} staff{{ $data['filters']['q'] !== '' ? ' matching “'.$data['filters']['q'].'”' : '' }}</div>
    </td></tr></table>

    @if(count($list))
        <table class="list">
            <thead>
                <tr>
                    <th style="width: 52px;">Day</th>
                    <th style="width: 196px;">Staff member</th>
                    <th style="width: 46px;">Gender</th>
                    <th style="width: 50px;">Works no.</th>
                    <th style="width: 68px;">Date of birth</th>
                    <th class="r" style="width: 26px;">Age</th>
                    <th>Next birthday</th>
                </tr>
            </thead>
            <tbody>
                @php $band = null; $n = 0; @endphp
                @foreach($list as $p)
                    @if($p['m'] !== $band)
                        @php $band = $p['m']; $n = 0; @endphp
                        <tr class="band"><td colspan="7">{{ $MN[$p['m'] - 1] }} &nbsp;<span>{{ $bands[$p['m']] }} birthday{{ $bands[$p['m']] === 1 ? '' : 's' }}</span></td></tr>
                    @endif
                    @php $n++; @endphp
                    <tr class="{{ $p['days'] === 0 ? 'today' : ($n % 2 ? '' : 'alt') }}">
                        <td class="day"><b>{{ $p['next']->day }}</b>&nbsp; <span class="muted">{{ $p['next']->format('D') }}</span></td>
                        <td class="nm">{{ $p['name'] }}</td>
                        <td>@if($p['gender'] !== ''){{ $p['gender'] }}@else<span class="miss">Missing</span>@endif</td>
                        <td>@if($p['works'] !== ''){{ $p['works'] }}@else<span class="miss">Missing</span>@endif</td>
                        <td>{{ $p['d'] }} {{ substr($MN[$p['m'] - 1], 0, 3) }} {{ $p['y'] }}</td>
                        <td class="r">{{ $p['age'] }}</td>
                        <td>
                            <b>{{ $p['days'] === 0 ? 'Today' : ($p['days'] === 1 ? 'Tomorrow' : 'in '.$p['days'].' days') }}</b>
                            <span class="muted">turns {{ $p['turns'] }}</span>
                            @if($p['days'] === 0)&nbsp; <span class="tag today">Today</span>@elseif($p['milestone'])&nbsp; <span class="tag">Milestone</span>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No staff match.</div>
    @endif

    @if(count($data['noDob']))
        <div class="nodob"><b>No date of birth on file:</b> {{ collect($data['noDob'])->pluck('name')->join(', ') }}</div>
    @endif
</body>
</html>
