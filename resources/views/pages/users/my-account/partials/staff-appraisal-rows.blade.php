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

@forelse($appraisal as $appraisalItem)
    @php
        $today = date('Y-m-d');
        $dueOn = date('Y-m-d', strtotime($appraisalItem->due_on));
        $label = ($dueOn < $today ? 'Overdue' : 'Due');
        $appraisalEmployeeName = trim((optional($appraisalItem->employee)->first_name ?? '').' '.(optional($appraisalItem->employee)->last_name ?? ''));
    @endphp

    <a href="{{ route('employee.appraisal.documents', [$appraisalItem->employee_id, $appraisalItem->id]) }}" class="myhr-staff-row myhr-staff-row--link">
        <span class="myhr-staff-avatar" style="background: {{ $myStaffAvatarColorFor($appraisalEmployeeName) }}">{{ $myStaffInitialsFor($appraisalEmployeeName) }}</span>
        <span class="myhr-staff-row__copy">
            <strong>{{ $appraisalEmployeeName }}</strong>
            <span>{{ date('jS F, Y', strtotime($appraisalItem->due_on)) }}</span>
        </span>
        <span class="myhr-staff-time {{ $dueOn < $today ? 'myhr-staff-time--danger' : 'myhr-staff-time--warning' }}">{{ $label }}</span>
    </a>
@empty
    @if(($showEmpty ?? true))
        <div class="myhr-staff-empty">
            <span><i data-lucide="check-square"></i></span>
            <p>No upcoming appraisals</p>
        </div>
    @endif
@endforelse
