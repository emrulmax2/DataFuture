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

@forelse($pendingLeaves as $leave)
    @php
        $leaveMinute = 0;
        foreach($leave->leaveDays as $leaveDay):
            if($leaveDay->status == 'Active'):
                $leaveMinute += (int) $leaveDay->hour;
            endif;
        endforeach;

        $employeeName = trim((optional($leave->employee)->first_name ?? '').' '.(optional($leave->employee)->last_name ?? ''));
    @endphp

    <button type="button" class="myhr-staff-row myhr-staff-row--button actPendingHoliday" data-leave="{{ $leave->id }}">
        <span class="myhr-staff-avatar" style="background: {{ $myStaffAvatarColorFor($employeeName) }}">{{ $myStaffInitialsFor($employeeName) }}</span>
        <span class="myhr-staff-row__copy">
            <strong>{{ $employeeName }}</strong>
            <span>{{ date('jS M, Y', strtotime($leave->from_date)).' - '.date('jS M, Y', strtotime($leave->to_date)) }}</span>
        </span>
        <span class="myhr-staff-time myhr-staff-time--danger">{{ $myStaffFormatMinutes($leaveMinute) }}</span>
    </button>
@empty
    @if(($showEmpty ?? true))
        <div class="myhr-staff-empty">
            <span><i data-lucide="check-square"></i></span>
            <p>No pending leave requests</p>
        </div>
    @endif
@endforelse
