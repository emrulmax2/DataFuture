@extends('pages.attendance-integrity.shell')

@section('aim_page', 'staff-show')
@section('aim_eyebrow', 'Staff Analysis')

@section('aim_sub')
    {{ trim($person['job_title'].' · '.$person['department'], ' ·') ?: 'Member of staff' }}. Counts come straight from the records (minimums: each record keeps only its first and last editor). Findings are patterns to explain, not proof.
@endsection

@section('aim_actions')
    <a href="{{ route('attendance.integrity.staff', array_diff_key($query, ['staff' => 1])) }}" class="aim-btn"><i data-lucide="arrow-left"></i> All staff</a>
    @if($can['manage_investigations'])
        <button type="button" class="aim-btn aim-btn--primary" data-aim-open-case data-subject-type="staff" data-subject-id="{{ $subjectId }}" data-subject-label="{{ $person['name'] }}"><i data-lucide="folder-plus"></i> Open a case</button>
    @endif
@endsection

@section('aim_content')
    @php $r = $row; $n = fn ($v) => $v === null ? '—' : number_format($v); @endphp
    @if(!$r)
        <div class="aim-callout aim-callout--neutral"><i data-lucide="info"></i><div>No attendance entered or changed by {{ $person['name'] }} under these filters.</div></div>
    @else
        <div class="aim-grid">
            <div class="aim-panel aim-col-8">
                <div class="aim-panel__head">
                    <div><h3>Key facts</h3><p>Under the filters above.</p></div>
                    @if($r['band'])<span class="aim-badge aim-badge--{{ $r['band'] }}">{{ ucfirst($r['band']) }} risk · {{ number_format($r['score']) }} points</span>@endif
                </div>
                <div class="aim-panel__body">
                    <div class="aim-facts">
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['created']) }}</div><div class="aim-fact__label">Records entered</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['changed']) }}</div><div class="aim-fact__label">Records changed (last editor)</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['late_entered']) }}</div><div class="aim-fact__label">Entered over 24h after the class</div></div>
                        <div class="aim-fact {{ $r['positive'] ? 'aim-fact--alert' : '' }}"><div class="aim-fact__value">{{ $n($r['positive']) }}</div><div class="aim-fact__label">Absent changed to Present</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['negative']) }}</div><div class="aim-fact__label">Present changed to Absent</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['deleted']) }}</div><div class="aim-fact__label">Deleted (in the history)</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['students']) }}</div><div class="aim-fact__label">Students affected</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['classes']) }}</div><div class="aim-fact__label">Classes affected</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['bulk']) }}</div><div class="aim-fact__label">Bulk entry bursts</div></div>
                        <div class="aim-fact {{ $r['unauthorised'] ? 'aim-fact--alert' : '' }}"><div class="aim-fact__value">{{ $n($r['unauthorised']) }}</div><div class="aim-fact__label">Records in classes they do not teach</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['pairs']) }}</div><div class="aim-fact__label">Recurring staff–student pairs</div></div>
                        <div class="aim-fact {{ $r['rejected_excuse'] ? 'aim-fact--alert' : '' }}"><div class="aim-fact__value">{{ $n($r['rejected_excuse']) }}</div><div class="aim-fact__label">Present after a rejected excuse</div></div>
                    </div>
                </div>
            </div>
            <div class="aim-panel aim-col-4">
                <div class="aim-panel__head"><div><h3>Compared with other staff</h3><p>Per 100 records they entered or changed. Peer = median of all staff under these filters.</p></div></div>
                <table class="aim-table">
                    <thead><tr><th>Measure</th><th class="num">This person</th><th class="num">Peers</th></tr></thead>
                    <tbody>
                        <tr><td>Changes per 100</td><td class="num"><strong>{{ $r['changes_per_100'] ?? '—' }}</strong></td><td class="num aim-muted">{{ $r['peer_changes_per_100'] ?? '—' }}</td></tr>
                        <tr><td>Absent &gt; Present per 100</td><td class="num"><strong>{{ $r['positive_per_100'] ?? '—' }}</strong></td><td class="num aim-muted">{{ $r['peer_positive_per_100'] ?? '—' }}</td></tr>
                    </tbody>
                </table>
                <div class="aim-panel__head" style="border-top: 1px solid var(--aim-line-2);"><div><h3>Points by rule</h3></div></div>
                <table class="aim-table">
                    <tbody>
                        @forelse($r['rules'] as $rule => $points)
                            <tr><td>{{ \App\Services\AttendanceIntegrity\Detection\Scanner::ruleLabels()[$rule] ?? $rule }}</td><td class="num"><strong>{{ number_format($points) }}</strong></td></tr>
                        @empty
                            <tr><td class="aim-empty">No scored findings.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="aim-panel__body" style="border-top: 1px solid var(--aim-line-2); display: flex; flex-wrap: wrap; gap: 8px;">
                    <a class="aim-btn aim-btn--sm" href="{{ route('attendance.integrity.records', ['metric' => 'modified', 'staff' => $subjectId] + $query) }}">Records they changed</a>
                    @if($can['view_audit_logs'])
                        <a class="aim-btn aim-btn--sm" href="{{ route('attendance.integrity.amendments', ['staff' => $subjectId] + $query) }}">Their change history</a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="aim-panel" style="margin-top: 18px;">
        <div class="aim-panel__head">
            <div><h3>Findings</h3><p>Click a finding to see its evidence.@if($can['manage_investigations']) Tick findings to open a case.@endif</p></div>
            <div class="aim-count" id="aimCount"></div>
        </div>
        @include('pages.attendance-integrity.partials.findings-table', ['dataUrl' => route('attendance.integrity.findings.data', ['staff' => $subjectId] + $query)])
    </div>

    <div class="aim-panel" style="margin-top: 18px;">
        <div class="aim-panel__head"><div><h3>Students affected</h3><p>Students whose attendance {{ $person['name'] }} entered or last changed, most flagged first.</p></div></div>
        <div class="aim-table-wrap">
            <table class="aim-table">
                <thead><tr><th>Student</th><th class="num">Entered</th><th class="num">Last changed</th><th class="num">Classes</th><th class="num">Absent &gt; Present</th><th class="num">Findings</th><th class="num">Points</th></tr></thead>
                <tbody>
                    @forelse(array_slice($students, 0, 300) as $s)
                        <tr class="{{ $s['positive'] ? 'is-flagged' : '' }}">
                            <td>
                                @if($can['view_students'])<a class="aim-link" href="{{ route('attendance.integrity.students.show', ['student' => $s['student_id']] + $query) }}">{{ $s['registration_no'] }}</a>@else{{ $s['registration_no'] }}@endif
                                <span class="aim-sub">{{ $s['name'] }}</span>
                            </td>
                            <td class="num">{{ number_format($s['entered']) }}</td>
                            <td class="num">{{ number_format($s['changed']) }}</td>
                            <td class="num">{{ number_format($s['classes']) }}</td>
                            <td class="num">{{ number_format($s['positive']) }}</td>
                            <td class="num">{{ number_format($s['findings']) }}</td>
                            <td class="num"><strong>{{ number_format($s['points']) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="aim-empty">None.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($students) > 300)<div class="aim-panel__body aim-small">Showing the first 300 of {{ number_format(count($students)) }}.</div>@endif
    </div>
@endsection
