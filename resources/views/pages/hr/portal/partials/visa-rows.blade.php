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
@forelse($visaExpiry as $pass)
    @php
        $name = $pass->employee->first_name.' '.$pass->employee->last_name;
        $expiryDate = date('Y-m-d', strtotime($pass->workpermit_expire));
        $isExpired = date('Y-m-d') > $expiryDate;
        $diffDays = Carbon::parse($expiryDate)->diffInDays(Carbon::now());
    @endphp
    <div class="hrd-person-row">
        <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvatarColor($name) }}">{{ $hrdInitials($name) }}</span>
        <span class="hrd-person-row__copy">
            <strong>{{ $name }}</strong>
            <small>{{ date('jS F, Y', strtotime($pass->workpermit_expire)) }}</small>
        </span>
        <span class="hrd-pill {{ $isExpired ? 'hrd-pill--critical' : 'hrd-pill--warning' }}">{{ $diffDays }} Days</span>
    </div>
@empty
    @if(($showEmpty ?? true))
        <div class="hrd-empty">No data found.</div>
    @endif
@endforelse
