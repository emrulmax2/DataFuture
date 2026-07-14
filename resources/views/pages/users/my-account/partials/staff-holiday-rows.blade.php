@php
    $myStaffAvatarPalette = ['#D98324', '#D6338A', '#3B5BB5', '#1E9E8F', '#B07E14', '#0F7B76'];
    $myStaffInitialsFor = function ($name) {
        $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        return empty($words) ? 'NA' : strtoupper(substr($words[0], 0, 1).substr($words[count($words) - 1], 0, 1));
    };
    $myStaffAvatarColorFor = function ($name) use ($myStaffAvatarPalette) {
        return $myStaffAvatarPalette[abs(crc32((string) $name)) % count($myStaffAvatarPalette)];
    };
    $myStaffFormatMinutes = function ($minutes) {
        $minutes = (int) $minutes;
        return str_pad((string) intdiv($minutes, 60), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT);
    };
@endphp

@forelse($holidays as $holiday)
    @php
        $holidayEmployeeName = trim((optional(optional($holiday->leave)->employee)->first_name ?? '').' '.(optional(optional($holiday->leave)->employee)->last_name ?? ''));
    @endphp

    <div class="myhr-staff-row">
        <span class="myhr-staff-avatar" style="background: {{ $myStaffAvatarColorFor($holidayEmployeeName) }}">{{ $myStaffInitialsFor($holidayEmployeeName) }}</span>
        <span class="myhr-staff-row__copy">
            <strong>{{ $holidayEmployeeName }}</strong>
            <span>{{ date('jS M, Y', strtotime($holiday->leave_date)) }}</span>
        </span>
        <span class="myhr-staff-time myhr-staff-time--warning">{{ $myStaffFormatMinutes($holiday->hour) }}</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="myhr-staff-empty">
            <span><i data-lucide="calendar"></i></span>
            <p>No holidays scheduled today</p>
        </div>
    @endif
@endforelse
