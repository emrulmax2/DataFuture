@extends('pages.attendance-integrity.shell')

@section('aim_page', 'student-show')
@section('aim_eyebrow', 'Student Analysis')

@section('aim_sub')
    Every class date under the filters, oldest first: what is recorded, who entered and changed it, the change history (where recorded), excuses and findings.
@endsection

@section('aim_actions')
    <a href="{{ route('attendance.integrity.students', array_diff_key($query, ['student' => 1])) }}" class="aim-btn"><i data-lucide="arrow-left"></i> All students</a>
    <a href="{{ route('student.show', $subjectId) }}" class="aim-btn" target="_blank"><i data-lucide="external-link"></i> Student record</a>
    @if($can['manage_investigations'])
        <button type="button" class="aim-btn aim-btn--primary" data-aim-open-case data-subject-type="student" data-subject-id="{{ $subjectId }}" data-subject-label="{{ trim($student['registration_no'].' '.$student['name']) }}"><i data-lucide="folder-plus"></i> Open a case</button>
    @endif
@endsection

@section('aim_content')
    @php $r = $row; $n = fn ($v) => $v === null ? '—' : number_format($v); @endphp
    @if($r)
        <div class="aim-panel">
            <div class="aim-panel__head">
                <div><h3>Summary</h3><p>Under the filters above.</p></div>
                @if($score)<span class="aim-badge aim-badge--{{ $score['band'] }}">{{ ucfirst($score['band']) }} risk · {{ number_format($score['score']) }} points</span>@endif
            </div>
            <div class="aim-panel__body">
                <div class="aim-facts">
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['scheduled']) }}</div><div class="aim-fact__label">Scheduled classes</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $r['rate'] !== null ? $r['rate'].'%' : '—' }}</div><div class="aim-fact__label">Attendance rate</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['present']) }}</div><div class="aim-fact__label">Present (P / O)</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['absent']) }}</div><div class="aim-fact__label">Absent</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['late']) }}</div><div class="aim-fact__label">Late</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['excused']) }}</div><div class="aim-fact__label">Excused (E / M / H)</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['late_entered']) }}</div><div class="aim-fact__label">Entered over 24h after the class</div></div>
                    <div class="aim-fact {{ $r['positive'] ? 'aim-fact--alert' : '' }}"><div class="aim-fact__value">{{ $n($r['positive']) }}</div><div class="aim-fact__label">Absent changed to Present</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['negative']) }}</div><div class="aim-fact__label">Present changed to Absent</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['staff_entering']) }} / {{ $n($r['staff_correcting']) }}</div><div class="aim-fact__label">Staff entering / correcting</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['excuses_n']) }}</div><div class="aim-fact__label">Excuses sent{{ $r['excuses_detail'] ? ' ('.$r['excuses_detail'].')' : '' }}</div></div>
                    <div class="aim-fact"><div class="aim-fact__value">{{ $n($r['flagged']) }}</div><div class="aim-fact__label">Scored findings</div></div>
                </div>
            </div>
        </div>
    @endif

    <div class="aim-panel" style="margin-top: 18px;">
        <div class="aim-panel__head"><div><h3>Timeline</h3><p>Highlighted class dates have at least one scored finding.</p></div><div class="aim-count">{{ number_format(count($timeline)) }} class dates</div></div>
        @if(!$timeline)
            <div class="aim-empty">No attendance under these filters.</div>
        @else
            <div class="aim-timeline">
                @foreach($timeline as $t)
                    @php
                        $flagged = collect($t['findings'])->where('weight', '>', 0)->isNotEmpty();
                        $code = strtok($t['status'], ' ');
                    @endphp
                    <div class="aim-tl {{ $flagged ? 'is-flagged' : '' }}">
                        <div class="aim-tl__when"><strong>{{ $t['class_day'] }} {{ $t['class_date'] }}</strong>{{ $t['class_time'] }}</div>
                        <div class="aim-tl__body">
                            <div class="aim-tl__title">
                                <span class="{{ in_array($code, ['P', 'O', 'L']) ? 'aim-status--in' : ($code === 'A' ? 'aim-status--out' : '') }}">{{ $t['status'] }}</span>
                                <span>{{ $t['module'] }}</span>
                                @if($t['deleted_at'])<span class="aim-badge aim-badge--dismissed">Deleted {{ $t['deleted_at'] }}</span>@endif
                                @if($t['session_status'] && $t['session_status'] !== 'Completed')<span class="aim-badge">Class {{ strtolower($t['session_status']) }}</span>@endif
                            </div>
                            <div class="aim-tl__meta">{{ trim($t['group'].' · '.$t['class_type'].' · '.$t['teacher'], ' ·') }}</div>
                            <div class="aim-tl__meta">
                                First entered by {{ $t['first_entered_by'] ?: 'not recorded' }} {{ $t['first_entered_at'] }}{{ $t['entered_after'] ? ' ('.$t['entered_after'].' the class)' : '' }}
                                @if($t['last_changed_by']) · last changed by {{ $t['last_changed_by'] }} {{ $t['last_changed_at'] }}@endif
                                @if($t['absence_notice']) · absence notice recorded {{ $t['absence_notice'] }}@endif
                                @if($t['note']) · note: {{ $t['note'] }}@endif
                            </div>
                            @if($t['history'] || $t['excuse'] || $t['findings'])
                                <div class="aim-tl__lines">
                                    @foreach($t['history'] as $h)
                                        <div>{{ $h['at'] }} · {{ ucfirst($h['action']) }} {{ $h['action'] === 'updated' ? $h['change'] : '' }} by {{ $h['by'] }} ({{ $h['screen'] }}){{ $h['late'] ? ' · late correction' : '' }}{{ $h['reason'] ? ' · reason: "'.$h['reason'].'"' : '' }}</div>
                                    @endforeach
                                    @if($t['excuse'])
                                        <div><strong>Excuse {{ strtolower($t['excuse']['status']) }}</strong> ({{ $t['excuse']['type'] }}){{ $t['excuse']['by'] !== 'not recorded' ? ' by '.$t['excuse']['by'].' '.$t['excuse']['at'] : '' }}</div>
                                        @if($t['excuse']['reason'])<div>Student: “{{ $t['excuse']['reason'] }}”</div>@endif
                                        @if($t['excuse']['reply'])<div>Reply: “{{ $t['excuse']['reply'] }}”</div>@endif
                                    @endif
                                    @foreach($t['findings'] as $x)
                                        <div><span class="aim-points {{ $x['weight'] >= 25 ? 'aim-points--hi' : ($x['weight'] >= 10 ? 'aim-points--md' : '') }}">{{ $x['weight'] }}</span> {{ $x['summary'] }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="aim-panel" style="margin-top: 18px;">
        <div class="aim-panel__head">
            <div><h3>All findings for this student</h3><p>Including patterns that are not about one class date.</p></div>
            <div class="aim-count" id="aimCount"></div>
        </div>
        @include('pages.attendance-integrity.partials.findings-table', ['dataUrl' => route('attendance.integrity.findings.data', ['student' => $subjectId] + $query)])
    </div>
@endsection
