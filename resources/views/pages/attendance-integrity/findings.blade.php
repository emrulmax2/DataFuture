@extends('pages.attendance-integrity.shell')

@section('aim_page', 'findings')
@section('aim_eyebrow', $pageKey === 'findings' ? 'Detection' : 'Detection rule')

@section('aim_sub'){{ $lead }} Points rank what to look at first; they are not proof.@endsection

@section('aim_actions')
    @include('pages.attendance-integrity.partials.export', ['report' => ['retrospective' => 'retrospective', 'bulk' => 'bulk', 'patterns' => 'patterns', 'timetable' => 'timetable'][$pageKey] ?? 'findings'])
@endsection

@section('aim_extra_filters')
    <div class="aim-field">
        <label for="aimType">Finding</label>
        <select id="aimType" name="type" class="aim-input">
            <option value="">All findings</option>
            @foreach($types as $value => $label)
                <option value="{{ $value }}" @selected($filters->type === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="aim-field">
        <label for="aimBasis">Basis</label>
        <select id="aimBasis" name="basis" class="aim-input">
            <option value="">Any basis</option>
            @foreach(\App\Models\AttendanceIntegrity\AimFinding::BASIS as $value => $label)
                <option value="{{ $value }}" @selected($filters->basis === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="aim-field">
        <label for="aimFindingStatus">Status</label>
        <select id="aimFindingStatus" name="finding_status" class="aim-input">
            @foreach(['open' => 'Open', 'case' => 'In a case', 'dismissed' => 'Dismissed', 'all' => 'All'] as $value => $label)
                <option value="{{ $value }}" @selected($filters->findingStatus === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="aim-field">
        <label for="aimSearch">Search summaries</label>
        <input id="aimSearch" type="search" name="search" class="aim-input" value="{{ $filters->search }}" placeholder="Name, module, group…">
    </div>
@endsection

@section('aim_content')
    <div class="aim-panel">
        <div class="aim-panel__head">
            <div><h3>Findings</h3><p>Click a finding to see its evidence.@if($can['manage_investigations']) Tick findings to open a case, or dismiss one with a reason.@endif</p></div>
            <div class="aim-count" id="aimCount"></div>
        </div>
        @include('pages.attendance-integrity.partials.findings-table', ['dataUrl' => route('attendance.integrity.findings.data', $query)])
    </div>
@endsection
