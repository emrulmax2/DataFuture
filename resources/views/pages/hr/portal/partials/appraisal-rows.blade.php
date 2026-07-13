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
@forelse($appraisal as $apr)
    @php
        $name = $apr->employee->first_name.' '.$apr->employee->last_name;
        $dueDate = date('Y-m-d', strtotime($apr->due_on));
        $isOverdue = date('Y-m-d') > $dueDate;
        $diffDays = Carbon::parse($dueDate)->diffInDays(Carbon::now());
    @endphp
    <a href="{{ route('employee.appraisal', $apr->employee_id) }}" class="hrd-person-row">
        <span class="hrd-avatar hrd-avatar--sm" style="background: {{ $hrdAvatarColor($name) }}">{{ $hrdInitials($name) }}</span>
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
