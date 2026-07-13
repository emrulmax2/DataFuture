@php
    /*
     | Absent Today rows — shared by the initial dashboard render and the
     | infinite-scroll AJAX endpoint (EmployeePortalController@absentRows).
     |
     | Params:
     |   $absentToday  array  page of absent entries keyed by employee_id
     |   $showEmpty    bool   render the empty-state message (default true).
     |                        AJAX pages pass false so no message appears mid-list.
     |
     | Rows carry no data-tw-toggle: the modal is opened by hr-portal.js so that
     | rows appended on scroll behave the same as the initial ones.
     */
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
@forelse($absentToday as $employee_id => $absent)
    <div class="hrd-activity-row absentToday" data-emloyee="{{ $employee_id }}" data-date="{{ $absent['the_date'] }}" data-minute="{{ $absent['minute'] }}" data-hour-min="{{ $absent['hourMinute'] }}">
        <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvatarColor($absent['full_name']) }}">{{ $hrdInitials($absent['full_name']) }}</span>
        <span class="hrd-person-row__copy">
            <strong>{{ $absent['full_name'] }}</strong>
            <small>{{ $absent['start'].' - '.$absent['end'] }}</small>
        </span>
        <b>{{ $absent['hourMinute'] }}</b>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No absent attendance found for today.</div>
    @endif
@endforelse
