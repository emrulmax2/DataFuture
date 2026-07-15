{{--
    Where the punch came from. $loc is ['suc' => 0|1|2, 'ip' => '', 'venue' => ''],
    resolved in bulk by EmployeeAttendanceController::punchVenues().

    The old screen rendered clock_in_location in BOTH the Clock In and the Clock Out
    cell, so every clock-out reported the venue the person arrived at.
--}}
@php $loc = $loc ?? ['suc' => 2, 'ip' => '', 'venue' => '']; @endphp

@if((int) $loc['suc'] === 1)
    <span class="att-venue att-venue--ok">
        <i data-lucide="map-pin" class="w-3 h-3"></i>{{ $loc['venue'] }}
    </span>
@elseif((int) $loc['suc'] === 0)
    <span class="att-venue att-venue--away">
        <i data-lucide="map-pin-off" class="w-3 h-3"></i>Away{{ !empty($loc['ip']) ? ' ('.$loc['ip'].')' : '' }}
    </span>
@else
    <span class="att-venue att-venue--none">
        <i data-lucide="help-circle" class="w-3 h-3"></i>Punch not found
    </span>
@endif
