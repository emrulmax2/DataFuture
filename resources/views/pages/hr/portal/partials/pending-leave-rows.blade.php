@php
    /*
     | Pending Holiday Request rows — shared by the initial dashboard render and
     | the infinite-scroll AJAX endpoint (EmployeePortalController@pendingLeaveRows).
     |
     | Params:
     |   $pendingLeaves  Collection  page of EmployeeLeave (status Pending, id DESC)
     |   $showEmpty      bool        render the empty-state message (default true).
     |                               AJAX pages pass false so no message appears mid-list.
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
@forelse($pendingLeaves as $leave)
    @php
        $leaveMinute = 0;
        foreach($leave->leaveDays as $ld):
            if($ld->status == 'Active'):
                $leaveMinute += $ld->hour;
            endif;
        endforeach;
        $hourMins = $hrdFormatMinutes($leaveMinute);
        $name = $leave->employee->first_name.' '.$leave->employee->last_name;
        $authUsers = false;
        foreach(optional($leave->employee)->approvers ?? [] as $hau):
            if($hau->user_id == auth()->user()->id):
                $authUsers = true;
            endif;
        endforeach;
    @endphp
    <div class="hrd-activity-row {{ $authUsers ? 'actPendingHoliday' : '' }}" data-leave="{{ $leave->id }}">
        <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvatarColor($name) }}">{{ $hrdInitials($name) }}</span>
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS M, Y', strtotime($leave->from_date)) }} - {{ date('jS M, Y', strtotime($leave->to_date)) }}</small>
        </span>
        @if(isset($leave->supervisedDays) && $leave->supervisedDays->count() > 0)
            <i data-lucide="shield-check" class="hrd-inline-good"></i>
        @endif
        <b>{{ $hourMins }}</b>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No pending leave available.</div>
    @endif
@endforelse
