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
@endphp
@forelse($absentToday as $employee_id => $absent)
    <div class="hrd-person-row absentToday" data-emloyee="{{ $employee_id }}" data-date="{{ $absent['the_date'] }}" data-minute="{{ $absent['minute'] }}" data-hour-min="{{ $absent['hourMinute'] }}">
        @include('pages.hr.portal.partials.avatar', ['name' => $absent['full_name'], 'photoUrl' => ($absent['photo_url'] ?? null)])
        <span class="hrd-person-row__copy">
            <strong>{{ $absent['full_name'] }}</strong>
            <small>{{ $absent['start'].' - '.$absent['end'] }}</small>
        </span>
        <span class="hrd-pill hrd-pill--critical">{{ $absent['hourMinute'] }}</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No absent attendance found for today.</div>
    @endif
@endforelse
