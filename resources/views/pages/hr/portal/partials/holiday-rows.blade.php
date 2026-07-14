@php
    /*
     | Holiday Today rows — shared by the initial dashboard render and the
     | infinite-scroll AJAX endpoint (EmployeePortalController@holidayRows).
     |
     | Params:
     |   $holidays   Collection  page of EmployeeLeaveDay (today, Active, id DESC)
     |   $showEmpty  bool        render the empty-state message (default true).
     |                           AJAX pages pass false so no message appears mid-list.
     */
    $hrdFormatMinutes = function ($minutes) {
        $minutes = (int) $minutes;
        return str_pad((string) intdiv($minutes, 60), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
    };
@endphp
@forelse($holidays as $hol)
    @php
        $name = $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name;
        $hourMins = $hrdFormatMinutes($hol->hour);
    @endphp
    <div class="hrd-activity-row">
        @include('pages.hr.portal.partials.avatar', ['name' => $name, 'photoUrl' => optional(optional($hol->leave)->employee)->photo_url])
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS M, Y', strtotime($hol->leave_date)) }} &middot; {{ $hourMins }}</small>
        </span>
        <span class="hrd-pill hrd-pill--success">On Leave</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No Holiday / Vacation found for today.</div>
    @endif
@endforelse
