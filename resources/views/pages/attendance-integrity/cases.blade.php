@extends('pages.attendance-integrity.shell')

@section('aim_page', 'cases')
@section('aim_eyebrow', 'Casework')

@section('aim_sub')
    Cases opened from findings. A case records a concern to investigate; it is not a finding of misconduct, and nothing here triggers disciplinary action or student sanctions.
@endsection

@section('aim_actions')
    @include('pages.attendance-integrity.partials.export', ['report' => 'cases'])
    @if($can['manage_investigations'])
        <button type="button" class="aim-btn aim-btn--primary" data-aim-open-case data-subject-type="other"><i data-lucide="folder-plus"></i> New case</button>
    @endif
@endsection

@section('aim_content')
    <div class="aim-panel">
        <div class="aim-panel__head">
            <div class="aim-seg">
                <a href="{{ route('attendance.integrity.cases') }}" class="aim-btn aim-btn--sm {{ !request('case_status') && !request('mine') ? 'is-on' : '' }}">All · {{ $statusCounts->sum() }}</a>
                <a href="{{ route('attendance.integrity.cases', ['mine' => 1]) }}" class="aim-btn aim-btn--sm {{ request('mine') ? 'is-on' : '' }}">Assigned to me</a>
                @foreach(\App\Models\AttendanceIntegrity\AimCase::STATUSES as $status)
                    @if(($statusCounts[$status] ?? 0) > 0)
                        <a href="{{ route('attendance.integrity.cases', ['case_status' => $status]) }}" class="aim-btn aim-btn--sm {{ request('case_status') === $status ? 'is-on' : '' }}">{{ $status }} · {{ $statusCounts[$status] }}</a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="aim-table-wrap">
            <table class="aim-table">
                <thead><tr><th>Case</th><th>Opened</th><th>Subject</th><th>Summary</th><th class="num">Score</th><th>Status</th><th>Investigator</th></tr></thead>
                <tbody>
                    @forelse($cases as $case)
                        <tr>
                            <td><a href="{{ route('attendance.integrity.cases.show', $case) }}" class="aim-link">{{ $case->case_number }}</a></td>
                            <td class="num">{{ \Carbon\Carbon::parse($case->created_at)->format('d M Y') }}</td>
                            <td>{{ $case->subject_label ?: '—' }}<span class="aim-sub">{{ \App\Models\AttendanceIntegrity\AimCase::SUBJECT_TYPES[$case->subject_type] ?? '' }}</span></td>
                            <td>{{ \Illuminate\Support\Str::limit($case->summary, 140) }}</td>
                            <td class="num"><strong>{{ number_format($case->risk_score) }}</strong></td>
                            <td><span class="aim-badge {{ in_array($case->investigation_status, \App\Models\AttendanceIntegrity\AimCase::OPEN_STATUSES) ? 'aim-badge--open' : 'aim-badge--done' }}">{{ $case->investigation_status }}</span></td>
                            <td>{{ $investigators[$case->assigned_investigator]['name'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="aim-empty">No cases yet. Tick findings on any detection page and choose <strong>Open a case</strong>, or use <strong>New case</strong>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cases->hasPages())<div class="aim-panel__body">{{ $cases->links() }}</div>@endif
    </div>
@endsection
