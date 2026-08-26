{{--
    Class dates for this module, rendered from `$planDateList`. The session
    time, room and venue are fixed on the plan, so only the date and status
    vary row to row.
--}}
@php
    $sessions = collect($planDateList)->sortBy(function ($row) {
        return strtotime($row->date);
    })->values();

    $statusTone = [
        'Completed' => 'spf-chip--green',
        'Ongoing'   => 'spf-chip--cream',
        'Scheduled' => 'spf-chip--grey',
        'Canceled'  => 'spf-chip--rust',
    ];

    $clock = function ($time, $withMeridiem = true) {
        if (empty($time)) {
            return null;
        }

        return date($withMeridiem ? 'g:i a' : 'g:i', strtotime('2000-01-01 ' . $time));
    };

    $start = $plan->start_time ?? null;
    $end = $plan->end_time ?? null;

    if ($start && $end) {
        /* Match the design: one meridiem when both ends share it. */
        $sameHalf = date('a', strtotime('2000-01-01 ' . $start)) === date('a', strtotime('2000-01-01 ' . $end));
        $timeLabel = $clock($start, !$sameHalf) . ' – ' . $clock($end);
    } else {
        $timeLabel = '—';
    }

    /* "Every Tuesday" only holds when every session falls on the same day. */
    $weekdays = $sessions->map(function ($row) {
        return date('l', strtotime($row->date));
    })->unique();

    $cadence = [];

    if ($weekdays->count() === 1) {
        $cadence[] = 'Every ' . $weekdays->first();
    }

    if ($sessions->count() > 0) {
        $cadence[] = $sessions->count() . ' ' . ($sessions->count() === 1 ? 'session' : 'sessions');
    }
@endphp

<div class="spf-page-head spf-page-head--baseline" style="margin-bottom:16px">
    <h2 class="spf-h2">Class dates</h2>
    @if(count($cadence) > 0)
        <span class="spf-eyebrow">{{ implode(' · ', $cadence) }}</span>
    @endif
</div>

@if($sessions->count() > 0)
    <div class="spf-rtable-wrap">
        <div class="spf-dtable">
            <div class="spf-dtable__head">
                <div>Date</div>
                <div>Room</div>
                <div>Time</div>
                <div class="spf-dtable__cell--right">Status</div>
            </div>

            @foreach($sessions as $session)
                @php
                    $stamp = strtotime($session->date);
                    $status = !empty($session->status) ? $session->status : 'Scheduled';
                    $tone = $statusTone[$status] ?? 'spf-chip--grey';
                @endphp
                <div class="spf-dtable__row">
                    <div class="spf-datecell">
                        <span class="spf-datechip">
                            <span class="spf-datechip__d">{{ date('j', $stamp) }}</span>
                            <span class="spf-datechip__m">{{ date('M', $stamp) }}</span>
                        </span>
                        <span>
                            <span style="font-weight:600">{{ date('jS M, Y', $stamp) }}</span>
                            <span class="spf-dtable__sub">{{ date('l', $stamp) }}</span>
                        </span>
                    </div>
                    <div>
                        <span style="font-weight:500">{{ !empty($data->venue) ? $data->venue : '—' }}</span>
                        @if(!empty($data->room))
                            <span class="spf-dtable__sub">{{ $data->room }}</span>
                        @endif
                    </div>
                    <div class="spf-cell--muted">{{ $timeLabel }}</div>
                    <div class="spf-dtable__cell--right">
                        <span class="spf-chip {{ $tone }}">{{ $status }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="spf-empty">No class dates have been scheduled for this module yet.</div>
@endif
