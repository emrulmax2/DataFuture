@extends('pages.attendance-integrity.shell')

@section('aim_page', 'amendments')
@section('aim_eyebrow', 'Detection')

@section('aim_sub')
    Status changes with their before and after, from the attendance history (recording since {{ $counts['since'] ? \Carbon\Carbon::parse($counts['since'])->format('d M Y, H:i') : 'deployment' }}), older Absent &gt; Present changes known from the absence notice, and how many older edits have no recorded previous value.
@endsection

@section('aim_actions')
    @include('pages.attendance-integrity.partials.export', ['report' => 'amendments'])
@endsection

@section('aim_content')
    <div class="aim-section">
        <div class="aim-kpis">
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Status changes recorded', 'icon' => 'history', 'value' => number_format($counts['history_changes']), 'hint' => 'With before and after', 'basis' => 'fact'])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Late corrections', 'icon' => 'alarm-clock', 'value' => number_format($counts['late']), 'hint' => number_format($counts['with_reason']).' with a reason given', 'basis' => 'fact', 'tone' => $counts['late'] ? 'warn' : null])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Changed, previous value unknown', 'icon' => 'help-circle', 'value' => number_format($counts['unknown']), 'hint' => 'Changed before the history existed; only the last editor is known', 'basis' => 'unavailable', 'href' => route('attendance.integrity.records', ['metric' => 'modified'] + $query)])
        </div>
    </div>

    <div class="aim-panel">
        <div class="aim-panel__head">
            <div class="aim-seg" id="aimHistoryKinds">
                <button type="button" class="aim-btn aim-btn--sm is-on" data-kind="status">Status changes, deletions, restores</button>
                <button type="button" class="aim-btn aim-btn--sm" data-kind="late">Late corrections</button>
                <button type="button" class="aim-btn aim-btn--sm" data-kind="all">Every recorded change</button>
                <button type="button" class="aim-btn aim-btn--sm" data-kind="notice">Before the history: absence notice</button>
            </div>
            <div class="aim-count" id="aimCount"></div>
        </div>
        <div id="aimHistoryTable" data-url="{{ route('attendance.integrity.amendments.history', $query) }}"
            data-student-url="{{ $can['view_students'] ? route('attendance.integrity.students.show', ['student' => '__ID__']) : '' }}"
            data-staff-url="{{ $can['view_staff'] ? route('attendance.integrity.staff.show', ['user' => '__ID__']) : '' }}"></div>
        <div id="aimNoticeWrap" style="display: none;">
            <div class="aim-panel__body aim-small" style="border-bottom: 1px solid var(--aim-line-2);">The class register writes an absence notice when it saves a student Absent and clears it on any other register save; the single-change screen leaves it. A record that is Present now with the notice still on it was therefore saved Absent, then changed to Present by its last editor.</div>
            @include('pages.attendance-integrity.partials.findings-table', ['dataUrl' => route('attendance.integrity.findings.data', ['rule' => 'amendment'] + $query)])
        </div>
    </div>
@endsection
