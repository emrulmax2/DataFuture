@php
    use Illuminate\Support\Carbon;

    /*
     | Appraisal (60 days) rows — shared by the initial dashboard render and the
     | infinite-scroll AJAX endpoint (EmployeePortalController@appraisalRows).
     |
     | Params:
     |   $appraisal  Collection  page of EmployeeAppraisal (due within 60 days, due_on ASC)
     |   $showEmpty  bool        render the empty-state message (default true).
     |                           AJAX pages pass false so no message appears mid-list.
     */
@endphp
@forelse($appraisal as $apr)
    @php
        $name = $apr->employee->first_name.' '.$apr->employee->last_name;
        $dueDate = date('Y-m-d', strtotime($apr->due_on));
        $isOverdue = date('Y-m-d') > $dueDate;
        $diffDays = Carbon::parse($dueDate)->diffInDays(Carbon::now());
    @endphp
    <a href="{{ route('employee.appraisal', $apr->employee_id) }}" class="hrd-person-row">
        @include('pages.hr.portal.partials.avatar', ['name' => $name, 'photoUrl' => optional($apr->employee)->photo_url])
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS M, Y', strtotime($apr->due_on)) }} &middot; <b>{{ $isOverdue ? 'Overdue' : 'Due to Complete' }}</b></small>
        </span>
        <span class="hrd-pill {{ $isOverdue ? 'hrd-pill--critical' : 'hrd-pill--warning' }}">{{ $isOverdue ? 'by ' : 'in ' }}{{ $diffDays }} days</span>
    </a>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No data found.</div>
    @endif
@endforelse
