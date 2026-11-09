{{--
    One task card on the process board. Expects $task (ApplicantTask with
    task, applicatnTaskStatus, documents, updatedBy loaded), $lane
    (pending | in-progress | completed) and, from the page, $applicant,
    $adpUserId, $adpIcon, $adpWho, $interviews, $taskNotes.

    Only staff assigned to the task type (task_lists → users) may act on it,
    as before; everyone can read the staff list, the uploads and the log.
--}}
@php
    $taskEntity = $task->task ?? null;
    $taskName = $taskEntity->name ?? 'Task';
    $taskDesc = trim((string) ($taskEntity->short_description ?? ''));
    $needsOutcome = ($taskEntity->status ?? 'No') === 'Yes';
    $needsUpload = ($taskEntity->upload ?? 'No') === 'Yes';
    $isInterview = ($taskEntity->interview ?? 'No') === 'Yes';
    $outcomeFull = ($task->task_status_id > 0 && !empty($task->applicatnTaskStatus->name)) ? $task->applicatnTaskStatus->name : '';
    $outcome = trim(preg_replace('/\s*\(.*$/s', '', $outcomeFull));
    $outcomeTone = strtolower($outcome) === 'pass' ? 'green' : (strtolower($outcome) === 'fail' ? 'red' : 'muted');
    $documents = $task->documents ?? collect();
    $docCount = $documents->count();
    $assigned = $taskEntity->users ?? collect();
    $canManage = in_array($adpUserId, $assigned->pluck('user_id')->all());
    $isCompleted = $task->status === 'Completed';
    $canComplete = (!$needsOutcome || $task->task_status_id > 0) && (!$needsUpload || $docCount > 0);
    $canDelete = (!$needsOutcome || empty($task->task_status_id)) && (!$needsUpload || $docCount === 0);
    $interview = isset($interviews[$task->id]) ? $interviews[$task->id]->first() : null;
    $interviewWhen = '';
    if ($interview && !empty($interview->interview_date)) {
        $ts = strtotime((string) $interview->interview_date);
        $interviewWhen = $ts ? date('d M Y', $ts) : (string) $interview->interview_date;
        $times = array_filter([$interview->start_time ? substr((string) $interview->start_time, 0, 5) : '', $interview->end_time ? substr((string) $interview->end_time, 0, 5) : '']);
        if (count($times)) $interviewWhen .= ' '.implode('–', $times);
        if (!empty($interview->interview_result) && $interview->interview_result !== 'N/A') $interviewWhen .= ' · '.$interview->interview_result;
    }
    $note = isset($taskNotes[$task->id]) ? (string) $taskNotes[$task->id]->first()->current_field_value : '';
    $completedBy = $isCompleted ? trim((string) ($task->updatedBy->employee->full_name ?? ($task->updatedBy->name ?? ''))) : '';
    $completedAt = ($isCompleted && !empty($task->updated_at)) ? date('d M Y', strtotime($task->updated_at)) : '';
    $autoAssigned = ($task->assigned_via ?? null) === 'auto';
    $hasMeta = ($taskDesc !== '' && $taskDesc !== $taskName) || $interviewWhen !== '' || $note !== '' || $completedBy !== '';
@endphp
<div class="adp-task adp-task--{{ $lane }}" data-adp-task="{{ $task->id }}">
    <div class="adp-task__top">
        <b>{{ $taskName }}@if($autoAssigned) <span class="adp-task__auto" title="Assigned automatically by Task Automation rule #{{ $task->rule_id }}">Auto</span>@endif</b>
        @if($outcome !== '')
            <span class="adp-pill adp-pill--{{ $outcomeTone }}"><i></i>{{ $outcome }}</span>
        @endif
    </div>
    @if($hasMeta)
        <div class="adp-task__meta">
            @if($taskDesc !== '' && $taskDesc !== $taskName)<span class="adp-task__desc">{{ $taskDesc }}</span>@endif
            @if($interviewWhen !== '')<span class="adp-flag adp-flag--ink">{!! $adpIcon('clock', 13) !!}{{ $interviewWhen }}</span>@endif
            @if($completedBy !== '')<span class="adp-flag adp-flag--ink">{!! $adpIcon('ok', 13) !!}Completed by {{ $completedBy }}@if($completedAt !== '') · {{ $completedAt }}@endif</span>@endif
            @if($note !== '')<span class="adp-task__note">{{ $note }}</span>@endif
        </div>
    @endif
    <div class="adp-task__acts">
        @if($canManage && !$isCompleted && $canComplete)
            <button type="button" class="adp-btn adp-btn--primary adp-btn--sm markAsCompleted" data-recordid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}">{!! $adpIcon('check', 13) !!}Mark as complete</button>
        @endif
        @if($canManage && $isCompleted)
            <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm markAsPending" data-recordid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}">{!! $adpIcon('undo', 13) !!}Reopen</button>
        @endif
        <span class="adp-task__icons">
            @if($canManage && !$isCompleted && $needsOutcome)
                <button type="button" class="adp-iconbtn updateTaskOutcome{{ $outcome === '' ? ' adp-iconbtn--need' : '' }}" data-applicanttaskid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}" data-tip="{{ $outcome === '' ? 'Set outcome' : 'Update outcome' }}" aria-label="{{ $outcome === '' ? 'Set outcome' : 'Update outcome' }}">{!! $adpIcon('flag', 15) !!}</button>
            @endif
            <span class="adp-pop-wrap">
                <button type="button" class="adp-iconbtn" data-adp-pop="staff" data-tip="Assigned staff" aria-label="Assigned staff" aria-expanded="false">{!! $adpIcon('users', 15) !!}@if($assigned->count() > 0)<b class="adp-badge">{{ $assigned->count() }}</b>@endif</button>
                <div class="adp-pop adp-pop--right" hidden>
                    <div class="adp-pop__h"><b>Assigned staff</b><button type="button" class="adp-btn-link taskUserLoader" data-taskid="{{ $taskEntity->id ?? 0 }}" data-adp-task-name="{{ $taskName }}">Details</button></div>
                    @if($assigned->isEmpty())
                        <div class="adp-pop__empty">Nobody assigned yet.</div>
                    @else
                        <ul class="adp-pop__list">
                            @foreach($assigned as $usr)
                                @php
                                    $staffEmployee = $usr->user->employee ?? null;
                                    $staffName = trim((string) ($staffEmployee->full_name ?? ($usr->user->name ?? 'Unknown employee')));
                                    $staffJob = $staffEmployee->employment->employeeJobTitle->name ?? '';
                                @endphp
                                <li>{!! $adpWho($staffEmployee, $staffName) !!}<span><b>{{ $staffName }}</b>@if($staffJob !== '')<small>{{ $staffJob }}</small>@endif</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </span>
            @if($needsUpload)
                <span class="adp-pop-wrap">
                    <button type="button" class="adp-iconbtn{{ !$isCompleted && $docCount === 0 ? ' adp-iconbtn--need' : '' }}" data-adp-pop="files" data-tip="{{ $docCount > 0 ? 'Uploads' : 'Upload required' }}" aria-label="Uploads" aria-expanded="false">{!! $adpIcon('upload', 15) !!}@if($docCount > 0)<b class="adp-badge">{{ $docCount }}</b>@endif</button>
                    <div class="adp-pop adp-pop--right adp-pop--wide" hidden>
                        <div class="adp-pop__h"><b>Uploads</b>@if($canManage && !$isCompleted)<button type="button" class="adp-btn-link uploadTaskDoc" data-applicanttaskid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}" data-adp-count="{{ $docCount }}">{!! $adpIcon('upload', 12) !!} Upload</button>@endif</div>
                        @if($docCount === 0)
                            <div class="adp-pop__empty">No files on this task yet.</div>
                        @else
                            <ul class="adp-pop__list adp-pop__list--files">
                                @foreach($documents as $doc)
                                    @php
                                        $docLabel = !empty($doc->display_file_name) ? $doc->display_file_name : (!empty($doc->current_file_name) ? $doc->current_file_name : 'Document');
                                        $docBits = array_filter([
                                            (!empty($doc->current_file_name) && $doc->current_file_name !== $docLabel) ? $doc->current_file_name : '',
                                            !empty($doc->created_at) ? date('d M Y', strtotime($doc->created_at)) : '',
                                            trim((string) ($doc->user->employee->full_name ?? ($doc->user->name ?? ''))),
                                            (isset($doc->hard_copy_check) && (int) $doc->hard_copy_check === 1) ? 'hard copy ✓' : '',
                                        ]);
                                    @endphp
                                    <li>
                                        <span class="adp-dz-fico">{!! $adpIcon('file', 14) !!}</span>
                                        <span class="adp-pop__file"><b class="adp-trunc">{{ $docLabel }}</b><small class="adp-trunc">{{ implode(' · ', $docBits) }}</small></span>
                                        <button type="button" class="adp-iconbtn adp-iconbtn--xs downloadDoc" data-id="{{ $doc->id }}" title="Download" aria-label="Download {{ $docLabel }}">{!! $adpIcon('download', 13) !!}</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </span>
            @endif
            <button type="button" class="adp-iconbtn viewTaskLogBtn" data-interview="{{ $isInterview ? 1 : 0 }}" data-applicantid="{{ $applicant->id }}" data-applicanttaskid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}" data-adp-task-status="{{ $task->status }}{{ $outcome !== '' ? ' · '.$outcome : '' }}" data-tip="View log" aria-label="View log">{!! $adpIcon('clock', 15) !!}</button>
            @if($canManage && !$isCompleted && $canDelete)
                <button type="button" class="adp-iconbtn adp-iconbtn--danger deleteApplicantTask" data-taskid="{{ $task->id }}" data-adp-task-name="{{ $taskName }}" data-tip="Archive task" aria-label="Archive task">{!! $adpIcon('trash', 15) !!}</button>
            @endif
        </span>
    </div>
</div>
