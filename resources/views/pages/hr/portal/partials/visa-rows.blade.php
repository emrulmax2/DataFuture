@php
    use Illuminate\Support\Carbon;

    /*
     | Visa Expiry rows — shared by the initial dashboard render and the
     | infinite-scroll AJAX endpoint (EmployeePortalController@visaRows).
     |
     | Params:
     |   $visaExpiry  Collection  page of EmployeeEligibilites (workpermit_expire ASC)
     |   $showEmpty   bool        render the empty-state message (default true).
     |                            AJAX pages pass false so no message appears mid-list.
     */
@endphp
@forelse($visaExpiry as $pass)
    @php
        $name = $pass->employee->first_name.' '.$pass->employee->last_name;
        $expiryDate = date('Y-m-d', strtotime($pass->workpermit_expire));
        $isExpired = date('Y-m-d') > $expiryDate;
        $diffDays = Carbon::parse($expiryDate)->diffInDays(Carbon::now());
    @endphp
    <a href="{{ route('profile.employee.view', $pass->employee_id) }}" class="hrd-person-row">
        @include('pages.hr.portal.partials.avatar', ['name' => $name, 'photoUrl' => optional($pass->employee)->photo_url])
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS F, Y', strtotime($pass->workpermit_expire)) }}</small>
        </span>
        <span class="hrd-pill {{ $isExpired ? 'hrd-pill--critical' : 'hrd-pill--warning' }}">{{ $diffDays }} Days</span>
    </a>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No data found.</div>
    @endif
@endforelse
