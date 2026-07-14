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
    <div class="hrd-person-row {{ $authUsers ? 'actPendingHoliday' : '' }}" data-leave="{{ $leave->id }}">
        @include('pages.hr.portal.partials.avatar', ['name' => $name, 'photoUrl' => optional($leave->employee)->photo_url])
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS M, Y', strtotime($leave->from_date)) }} - {{ date('jS M, Y', strtotime($leave->to_date)) }}</small>
        </span>
        @if(isset($leave->supervisedDays) && $leave->supervisedDays->count() > 0)
            <i data-lucide="shield-check" class="hrd-inline-good"></i>
        @endif
        <span class="hrd-pill hrd-pill--warning">{{ $hourMins }}</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No pending leave available.</div>
    @endif
@endforelse
