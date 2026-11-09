@extends('pages.attendance-integrity.shell')

@section('aim_page', 'case-show')
@section('aim_eyebrow', 'Investigation')

@section('aim_sub')
    {{ \App\Models\AttendanceIntegrity\AimCase::SUBJECT_TYPES[$case->subject_type] ?? 'Case' }}: {{ $subject['label'] }}{{ $term ? ' · '.$term : '' }}. A case records a concern to investigate; it is not a finding of misconduct.
@endsection

@section('aim_actions')
    <a href="{{ route('attendance.integrity.cases') }}" class="aim-btn"><i data-lucide="arrow-left"></i> All cases</a>
    @if($subject['url'])<a href="{{ $subject['url'] }}" class="aim-btn"><i data-lucide="user"></i> {{ $case->subject_type === 'staff' ? 'Staff analysis' : 'Student analysis' }}</a>@endif
@endsection

@section('aim_content')
    @php
        $manage = $can['manage_investigations'];
        $name = fn ($id) => $id ? ($people[$id]['name'] ?? 'User #'.$id) : '—';
        $isOpen = in_array($case->investigation_status, \App\Models\AttendanceIntegrity\AimCase::OPEN_STATUSES);
    @endphp
    <div class="aim-grid">
        <div class="aim-col-8" style="display: grid; gap: 18px; align-content: start;">
            <div class="aim-panel">
                <div class="aim-panel__head">
                    <div><h3>{{ $case->case_number }}</h3><p>Opened {{ \Carbon\Carbon::parse($case->created_at)->format('d M Y, H:i') }} by {{ $name($case->created_by) }}</p></div>
                    <span class="aim-badge {{ $isOpen ? 'aim-badge--open' : 'aim-badge--done' }}">{{ $case->investigation_status }}</span>
                </div>
                <div class="aim-panel__body">
                    <p style="margin: 0 0 14px; font-size: 14px; color: var(--aim-ink);">{{ $case->summary }}</p>
                    @if($case->evidence)<div class="aim-callout aim-callout--neutral" style="white-space: pre-line;"><i data-lucide="file-text"></i><div>{{ $case->evidence }}</div></div>@endif
                    <div class="aim-facts">
                        <div class="aim-fact"><div class="aim-fact__value">{{ number_format($case->risk_score) }}</div><div class="aim-fact__label">Score of linked findings</div></div>
                        <div class="aim-fact"><div class="aim-fact__value">{{ count($findings) }}</div><div class="aim-fact__label">Linked findings</div></div>
                        <div class="aim-fact"><div class="aim-fact__value" style="font-size: 14px;">{{ $name($case->assigned_investigator) }}</div><div class="aim-fact__label">Investigator</div></div>
                        <div class="aim-fact"><div class="aim-fact__value" style="font-size: 14px;">{{ $case->reviewed_by ? $name($case->reviewed_by) : '—' }}</div><div class="aim-fact__label">Reviewed{{ $case->reviewed_at ? ' '.\Carbon\Carbon::parse($case->reviewed_at)->format('d M Y') : '' }}</div></div>
                    </div>
                    @if($case->outcome)
                        <div class="aim-callout aim-callout--ok" style="margin: 14px 0 0; white-space: pre-line;"><i data-lucide="check-circle-2"></i><div><strong>Outcome.</strong> {{ $case->outcome }}</div></div>
                    @endif
                </div>
            </div>

            <div class="aim-panel">
                <div class="aim-panel__head"><div><h3>History</h3><p>Every step, oldest first. Entries cannot be changed or deleted.</p></div></div>
                @if($manage)
                    <form method="post" action="{{ route('attendance.integrity.cases.note', $case) }}" class="aim-panel__body" style="border-bottom: 1px solid var(--aim-line-2);">
                        @csrf
                        <textarea name="body" rows="3" class="aim-input" placeholder="Investigator note, or an explanation received">{{ old('body') }}</textarea>
                        <div class="aim-form__actions">
                            <button type="submit" name="kind" value="note" class="aim-btn aim-btn--sm aim-btn--primary">Add note</button>
                            <button type="submit" name="kind" value="explanation" class="aim-btn aim-btn--sm">Add as explanation received</button>
                        </div>
                    </form>
                @endif
                <div class="aim-timeline">
                    @foreach($activities as $a)
                        <div class="aim-tl">
                            <div class="aim-tl__when"><strong>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y') }}</strong>{{ \Carbon\Carbon::parse($a->created_at)->format('H:i') }}</div>
                            <div class="aim-tl__body">
                                <div class="aim-tl__title">{{ ['created' => 'Case opened', 'status' => 'Status changed', 'assigned' => 'Investigator', 'note' => 'Note', 'explanation' => 'Explanation received', 'evidence' => 'Evidence added', 'findings' => 'Findings added', 'outcome' => 'Outcome recorded'][$a->type] ?? ucfirst($a->type) }}
                                    <span class="aim-tl__meta" style="font-weight: 400;">· {{ $a->created_by_name }}</span></div>
                                <div style="white-space: pre-line;">{{ $a->body }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="aim-col-4" style="display: grid; gap: 18px; align-content: start;">
            <div class="aim-panel">
                <div class="aim-panel__head"><div><h3>Manage</h3><p>{{ $manage ? 'Every change is added to the history.' : 'Changing a case needs Manage investigations.' }}</p></div></div>
                @if($manage)
                    <div class="aim-panel__body">
                        <form method="post" action="{{ route('attendance.integrity.cases.status', $case) }}" class="aim-form">
                            @csrf
                            <h4>Status</h4>
                            <select name="investigation_status" class="aim-input">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($case->investigation_status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <textarea name="note" rows="2" class="aim-input" placeholder="Why (required when resolving or closing)">{{ old('note') }}</textarea>
                            <div class="aim-form__actions"><button type="submit" class="aim-btn aim-btn--sm aim-btn--primary">Change status</button></div>
                        </form>
                        <form method="post" action="{{ route('attendance.integrity.cases.assign', $case) }}" class="aim-form">
                            @csrf
                            <h4>Investigator</h4>
                            <div class="aim-field">
                                <select name="assigned_investigator" data-aim-remote="staff">
                                    @if($case->assigned_investigator)<option value="{{ $case->assigned_investigator }}" selected>{{ $name($case->assigned_investigator) }}</option>@endif
                                </select>
                            </div>
                            <p class="aim-small" style="margin: 6px 0 0;">Must hold View investigations.</p>
                            <div class="aim-form__actions"><button type="submit" class="aim-btn aim-btn--sm">Save investigator</button></div>
                        </form>
                        <form method="post" action="{{ route('attendance.integrity.cases.outcome', $case) }}" class="aim-form">
                            @csrf
                            <h4>Outcome</h4>
                            <textarea name="outcome" rows="3" class="aim-input" placeholder="What was found and decided">{{ old('outcome', $case->outcome) }}</textarea>
                            <div class="aim-form__actions"><button type="submit" class="aim-btn aim-btn--sm">Record outcome</button></div>
                        </form>
                    </div>
                @endif
            </div>

            <div class="aim-panel">
                <div class="aim-panel__head"><div><h3>Evidence</h3><p>Kept privately; downloads are logged; cannot be removed.</p></div></div>
                @if($manage)
                    <form method="post" action="{{ route('attendance.integrity.cases.evidence', $case) }}" enctype="multipart/form-data" class="aim-panel__body aim-form" style="border-bottom: 1px solid var(--aim-line-2);">
                        @csrf
                        <input type="text" name="title" class="aim-input" placeholder="Title (e.g. Email from the tutor)" required maxlength="255">
                        <textarea name="description" rows="2" class="aim-input" placeholder="What it shows"></textarea>
                        <input type="file" name="file" class="aim-input" style="padding-top: 7px;">
                        <p class="aim-small" style="margin: 6px 0 0;">PDF, images, Office documents, CSV, text or email; up to 20 MB.</p>
                        <div class="aim-form__actions"><button type="submit" class="aim-btn aim-btn--sm">Add evidence</button></div>
                    </form>
                @endif
                <table class="aim-table">
                    <tbody>
                        @forelse($evidence as $e)
                            <tr>
                                <td>
                                    <strong>{{ $e->title }}</strong>
                                    <span class="aim-sub">{{ \Carbon\Carbon::parse($e->created_at)->format('d M Y, H:i') }} · {{ $name($e->uploaded_by) }}</span>
                                    @if($e->description)<span class="aim-sub">{{ $e->description }}</span>@endif
                                </td>
                                <td class="num">@if($e->file_path)<a class="aim-btn aim-btn--sm" href="{{ route('attendance.integrity.cases.evidence.download', [$case, $e]) }}"><i data-lucide="download"></i></a>@endif</td>
                            </tr>
                        @empty
                            <tr><td class="aim-empty">No evidence added yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="aim-panel aim-col-12">
            <div class="aim-panel__head"><div><h3>Linked findings</h3><p>Click a finding to see its evidence.</p></div></div>
            <div id="aimCaseFindings" data-rows='@json($findings)'
                data-staff-url="{{ $can['view_staff'] ? route('attendance.integrity.staff.show', ['user' => '__ID__']) : '' }}"
                data-student-url="{{ $can['view_students'] ? route('attendance.integrity.students.show', ['student' => '__ID__']) : '' }}"></div>
        </div>
    </div>
@endsection
