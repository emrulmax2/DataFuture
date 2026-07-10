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
    $hrdInitials = function ($name) {
        $name = trim(preg_replace('/^(Mrs|Mr|Miss|Ms|Dr)\.?\s+/i', '', (string) $name));
        $parts = preg_split('/\s+/', $name);
        return strtoupper(substr($parts[0] ?? 'L', 0, 1).substr($parts[1] ?? 'C', 0, 1));
    };
    $hrdAvatarColor = function ($name) {
        $palette = ['#0F7B76', '#3B5BB5', '#7A3FB0', '#C4432F', '#187A45', '#B07E14', '#2A6FA8', '#B0357E', '#0E7C86', '#8A5A2B'];
        return $palette[abs(crc32((string) $name)) % count($palette)];
    };
@endphp
@forelse($holidays as $hol)
    @php
        $name = $hol->leave->employee->first_name.' '.$hol->leave->employee->last_name;
        $hourMins = $hrdFormatMinutes($hol->hour);
    @endphp
    <div class="hrd-activity-row">
        <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvatarColor($name) }}">{{ $hrdInitials($name) }}</span>
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
