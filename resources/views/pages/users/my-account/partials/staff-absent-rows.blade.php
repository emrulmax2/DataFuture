@php
    $myStaffAvatarPalette = ['#D98324', '#D6338A', '#3B5BB5', '#1E9E8F', '#B07E14', '#0F7B76'];
    $myStaffInitialsFor = function ($name) {
        $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);
        return empty($words) ? 'NA' : strtoupper(substr($words[0], 0, 1).substr($words[count($words) - 1], 0, 1));
    };
    $myStaffAvatarColorFor = function ($name) use ($myStaffAvatarPalette) {
        return $myStaffAvatarPalette[abs(crc32((string) $name)) % count($myStaffAvatarPalette)];
    };
@endphp

@forelse($absentToday as $absent)
    <div class="myhr-staff-row">
        <span class="myhr-staff-avatar" style="background: {{ $myStaffAvatarColorFor($absent['full_name'] ?? '') }}">{{ $myStaffInitialsFor($absent['full_name'] ?? '') }}</span>
        <span class="myhr-staff-row__copy">
            <strong>{{ $absent['full_name'] }}</strong>
            <span>{{ $absent['date'] }}</span>
        </span>
        <span class="myhr-staff-time myhr-staff-time--danger">{{ $absent['hourMinute'] }}</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="myhr-staff-empty">
            <span><i data-lucide="check-square"></i></span>
            <p>No absent attendance found for today</p>
        </div>
    @endif
@endforelse
