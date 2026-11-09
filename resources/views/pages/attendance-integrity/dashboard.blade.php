@extends('pages.attendance-integrity.shell')

@section('aim_page', 'dashboard')

@section('aim_sub')
    How attendance is being entered and changed, what the detection rules found, and where investigations stand. Findings are patterns that need an explanation, not proof of wrongdoing.
@endsection

@section('aim_actions')
    @if($can['export'])
        <a href="{{ route('attendance.integrity.reports.export', ['report' => 'term_summary'] + $query + ['format' => 'xlsx']) }}" class="aim-btn"><i data-lucide="download"></i> Export summary</a>
    @endif
    @if($can['view_investigations'])
        <a href="{{ route('attendance.integrity.cases') }}" class="aim-btn aim-btn--primary"><i data-lucide="folder-search"></i> Investigations</a>
    @endif
@endsection

@section('aim_content')
    @php
        $c = $data['cards'];
        $n = fn ($v) => $v === null ? '—' : number_format($v);
        $rec = fn ($metric) => route('attendance.integrity.records', ['metric' => $metric] + $query);
    @endphp

    <div class="aim-callout aim-callout--neutral">
        <i data-lucide="info"></i>
        <div>
            @if($lastScan)
                Findings last updated <strong>{{ \Carbon\Carbon::parse($lastScan->finished_at)->format('d M Y, H:i') }}</strong>.
            @else
                No scan has run yet. Anomaly figures appear after the first scan (Settings, or the nightly scan).
            @endif
            @if($historySince)
                The full change history has been recorded since {{ \Carbon\Carbon::parse($historySince)->format('d M Y, H:i') }}; for earlier changes only the last editor is known.
            @else
                The full change history starts with the first attendance change after deployment; for earlier changes only the last editor is known.
            @endif
        </div>
    </div>

    <div class="aim-section">
        <div class="aim-section__head"><h2>Attendance on record</h2><span>Read directly from the attendance records</span></div>
        <div class="aim-kpis">
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Scheduled attendance', 'icon' => 'calendar-days', 'value' => $n($c['opportunities']), 'hint' => 'Class dates held so far × students assigned', 'basis' => 'fact'])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Recorded attendance', 'icon' => 'clipboard-list', 'value' => $n($c['recorded']), 'hint' => 'Attendance rate '.($c['rate'] !== null ? $c['rate'].'%' : '—'), 'basis' => 'fact', 'href' => $rec('recorded')])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Recorded as Late', 'icon' => 'timer', 'value' => $n($c['late_status']), 'hint' => 'Status L chosen in the register', 'basis' => 'fact', 'href' => $rec('late_status')])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Entered retrospectively', 'icon' => 'alarm-clock', 'value' => $n($c['retrospective']), 'hint' => 'First entered over 24 hours after the class ended', 'basis' => 'fact', 'tone' => $c['retrospective'] ? 'warn' : null, 'href' => $rec('retrospective')])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Changed after first entry', 'icon' => 'pencil-line', 'value' => $n($c['modified']), 'hint' => $n($c['with_history']).' with full history; the rest show only the last editor', 'basis' => 'fact', 'href' => $rec('modified')])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Deleted / restored', 'icon' => 'trash-2', 'html' => e($n($c['deleted'])).' <small>/ '.e($n($c['restored'])).'</small>', 'hint' => 'Who deleted is known only from the history', 'basis' => 'fact', 'href' => $rec('deleted')])
        </div>
    </div>

    <div class="aim-section">
        <div class="aim-section__head"><h2>Detected anomalies</h2><span>From the detection rules, under these filters</span></div>
        <div class="aim-kpis">
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Absent changed to Present', 'icon' => 'repeat', 'value' => $n($c['absent_to_present']), 'hint' => 'Proven by the history or the absence notice', 'basis' => 'fact', 'tone' => $c['absent_to_present'] ? 'alert' : null, 'href' => $rec('absent_to_present')])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Suspicious bulk entries', 'icon' => 'layers', 'value' => $n($c['bulk']), 'hint' => 'Bursts of entries across different classes', 'basis' => 'anomaly', 'tone' => $c['bulk'] ? 'warn' : null, 'href' => route('attendance.integrity.bulk', $query)])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Students with unusual patterns', 'icon' => 'graduation-cap', 'value' => $n($c['students_flagged']), 'hint' => 'With at least one scored finding', 'basis' => 'anomaly', 'href' => $can['view_students'] ? route('attendance.integrity.students', $query) : null])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Staff with high change rates', 'icon' => 'user-cog', 'value' => $n($c['high_amendment_staff']), 'hint' => 'Twice the median changes per 100 records', 'basis' => 'anomaly', 'tone' => $c['high_amendment_staff'] ? 'warn' : null, 'href' => $can['view_staff'] ? route('attendance.integrity.staff', $query) : null])
        </div>
    </div>

    <div class="aim-section">
        <div class="aim-section__head"><h2>Risk and investigations</h2><span>Scores rank what to look at first; they are not proof</span></div>
        <div class="aim-kpis">
            @php $sb = $data['staff_bands']; $tb = $data['student_bands']; @endphp
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Staff by risk level', 'icon' => 'users', 'html' => '<div class="aim-split"><span class="is-high">High<b>'.$sb['high'].'</b></span><span class="is-med">Medium<b>'.$sb['medium'].'</b></span><span>Low<b>'.$sb['low'].'</b></span></div>', 'basis' => 'anomaly', 'href' => $can['view_staff'] ? route('attendance.integrity.staff', ['risk' => 'high'] + $query) : null])
            @include('pages.attendance-integrity.partials.kpi', ['label' => 'Students by risk level', 'icon' => 'graduation-cap', 'html' => '<div class="aim-split"><span class="is-high">High<b>'.$tb['high'].'</b></span><span class="is-med">Medium<b>'.$tb['medium'].'</b></span><span>Low<b>'.$tb['low'].'</b></span></div>', 'basis' => 'anomaly', 'href' => $can['view_students'] ? route('attendance.integrity.students', ['risk' => 'high'] + $query) : null])
            @if($can['view_investigations'])
                @include('pages.attendance-integrity.partials.kpi', ['label' => 'Investigations', 'icon' => 'folder-search', 'html' => '<div class="aim-split"><span>Open<b>'.$data['cases']['open'].'</b></span><span class="is-high">Substantiated<b>'.$data['cases']['substantiated'].'</b></span><span>No issue<b>'.$data['cases']['no_issue'].'</b></span></div>', 'hint' => $data['cases']['closed'].' closed', 'href' => route('attendance.integrity.cases')])
            @endif
        </div>
    </div>

    <div class="aim-grid">
        <div class="aim-panel aim-col-8">
            <div class="aim-panel__head"><div><h3>Week by week</h3><p>By class week: records, records entered over 24 hours late, records changed, and findings.</p></div></div>
            <div class="aim-panel__body"><div class="aim-chart"><canvas id="aimTrendChart" aria-label="Week by week chart"></canvas></div></div>
        </div>
        <div class="aim-panel aim-col-4">
            <div class="aim-panel__head"><div><h3>Attendance rate by term</h3><p>Statuses set in Attendance Settings.</p></div></div>
            <div class="aim-panel__body"><div class="aim-chart"><canvas id="aimTermChart" aria-label="Attendance rate by term chart"></canvas></div></div>
        </div>

        <div class="aim-panel aim-col-6">
            <div class="aim-panel__head"><div><h3>Findings by rule</h3><p>Open and in a case; dismissed findings are left out.</p></div></div>
            <div class="aim-table-wrap">
                <table class="aim-table">
                    <thead><tr><th>Rule</th><th class="num">Findings</th><th class="num">Scored</th><th class="num">Open</th></tr></thead>
                    <tbody>
                        @forelse($data['by_rule'] as $r)
                            @php
                                $page = ['retrospective' => 'retrospective', 'bulk_entry' => 'bulk', 'staff_student' => 'patterns', 'timetable' => 'timetable'][$r['rule']] ?? null;
                                $link = $page ? route('attendance.integrity.'.$page, $query) : route('attendance.integrity.findings', ['rule' => $r['rule']] + $query);
                                if ($r['rule'] === 'amendment' && $can['view_audit_logs']) { $link = route('attendance.integrity.amendments', $query); }
                            @endphp
                            <tr>
                                <td><a href="{{ $link }}" class="aim-link">{{ $r['label'] }}</a></td>
                                <td class="num">{{ number_format($r['findings']) }}</td>
                                <td class="num">{{ number_format($r['scored']) }}</td>
                                <td class="num">{{ number_format($r['open']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="aim-empty">No findings for these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aim-panel aim-col-6">
            <div class="aim-panel__head"><div><h3>Highest staff scores</h3><p>Sum of each event's highest points.</p></div>
                @if($can['view_staff'])<a href="{{ route('attendance.integrity.staff', $query) }}" class="aim-btn aim-btn--sm">All staff</a>@endif</div>
            <div class="aim-table-wrap">
                <table class="aim-table">
                    <thead><tr><th>Staff</th><th class="num">Findings</th><th class="num">Score</th><th>Level</th></tr></thead>
                    <tbody>
                        @forelse($data['top_staff'] as $s)
                            <tr>
                                <td>
                                    @if($can['view_staff'])<a href="{{ route('attendance.integrity.staff.show', ['user' => $s['id']] + $query) }}" class="aim-link">{{ $s['name'] }}</a>@else{{ $s['name'] }}@endif
                                    <span class="aim-sub">{{ $s['meta'] }}</span>
                                </td>
                                <td class="num">{{ number_format($s['findings']) }}</td>
                                <td class="num"><strong>{{ number_format($s['score']) }}</strong></td>
                                <td><span class="aim-badge aim-badge--{{ $s['band'] }}">{{ ucfirst($s['band']) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="aim-empty">No scored findings.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aim-panel aim-col-6">
            <div class="aim-panel__head"><div><h3>Highest student scores</h3><p>Students whose records are most flagged.</p></div>
                @if($can['view_students'])<a href="{{ route('attendance.integrity.students', $query) }}" class="aim-btn aim-btn--sm">All students</a>@endif</div>
            <div class="aim-table-wrap">
                <table class="aim-table">
                    <thead><tr><th>Student</th><th class="num">Findings</th><th class="num">Score</th><th>Level</th></tr></thead>
                    <tbody>
                        @forelse($data['top_students'] as $s)
                            <tr>
                                <td>
                                    @if($can['view_students'])<a href="{{ route('attendance.integrity.students.show', ['student' => $s['id']] + $query) }}" class="aim-link">{{ $s['name'] }}</a>@else{{ $s['name'] }}@endif
                                    <span class="aim-sub">{{ $s['meta'] }}</span>
                                </td>
                                <td class="num">{{ number_format($s['findings']) }}</td>
                                <td class="num"><strong>{{ number_format($s['score']) }}</strong></td>
                                <td><span class="aim-badge aim-badge--{{ $s['band'] }}">{{ ucfirst($s['band']) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="aim-empty">No scored findings.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aim-panel aim-col-6">
            <div class="aim-panel__head"><div><h3>Reading these figures</h3><p>What the records can and cannot show.</p></div>
                <a href="{{ route('attendance.integrity.guide') }}" class="aim-btn aim-btn--sm"><i data-lucide="book-open"></i> Guide</a></div>
            <div class="aim-panel__body">
                <ul class="aim-small" style="list-style: disc; padding-left: 18px; display: grid; gap: 6px;">
                    <li>Each record keeps only its first and last editor; the full history exists from the day it was switched on.</li>
                    <li>Before the history, Absent &gt; Present is known only where the register's absence notice is still on the record.</li>
                    <li>There is no card swipe, QR or learning-platform record. A missing record is never treated as proof of absence.</li>
                    <li>"Late" is chosen by the tutor; nothing compares it with the class start time.</li>
                    <li>Scores rank what to look at first. Opening a case is not a finding of misconduct.</li>
                </ul>
            </div>
        </div>
    </div>

    <script type="application/json" id="aimDashboardData">@json(['trend' => $data['trend'], 'by_term' => $data['by_term']])</script>
@endsection
