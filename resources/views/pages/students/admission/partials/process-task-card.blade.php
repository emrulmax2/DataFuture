@php
    $variant = $variant ?? 'pending';
    $taskEntity = $task->task ?? null;
    $taskName = $taskEntity->name ?? 'Task';
    $taskDescription = $taskEntity->short_description ?? '';
    $taskOutcome = ($task->task_status_id > 0 && isset($task->applicatnTaskStatus->name) && !empty($task->applicatnTaskStatus->name))
        ? $task->applicatnTaskStatus->name
        : '';
    $documents = (isset($task->documents) && !empty($task->documents)) ? $task->documents : collect();
    $documentCount = method_exists($documents, 'count') ? $documents->count() : count($documents);
    $assignedUsers = (isset($taskEntity->users) && !empty($taskEntity->users)) ? $taskEntity->users : collect();
    $userIds = [];
    foreach($assignedUsers as $usr):
        $userIds[] = $usr->user_id;
    endforeach;
    $canManageTask = !empty($userIds) && in_array(auth()->user()->id, $userIds);
    $taskStatusRequirement = $taskEntity->status ?? 'No';
    $taskUploadRequirement = $taskEntity->upload ?? 'No';
    $canCompleteTask = (
        ($taskStatusRequirement == 'No' || ($taskStatusRequirement == 'Yes' && $task->task_status_id > 0))
        && ($taskUploadRequirement == 'No' || ($taskUploadRequirement == 'Yes' && $documentCount > 0))
    );
    $canDeleteTask = (
        ($taskStatusRequirement == 'No' || ($taskStatusRequirement == 'Yes' && $task->task_status_id == ''))
        && ($taskUploadRequirement == 'No' || ($taskUploadRequirement == 'Yes' && $documentCount == 0))
    );
    $employeePhotoUrl = function($employee) {
        if(empty($employee) || empty($employee->id) || empty($employee->photo)):
            return '';
        endif;

        $photoPath = 'public/employees/'.$employee->id.'/'.$employee->photo;
        return \Illuminate\Support\Facades\Storage::disk('local')->exists($photoPath)
            ? \Illuminate\Support\Facades\Storage::disk('local')->url($photoPath)
            : '';
    };
    $initials = function($name) {
        $parts = preg_split('/\s+/', trim($name));
        $first = isset($parts[0]) && $parts[0] !== '' ? substr($parts[0], 0, 1) : 'U';
        $second = isset($parts[1]) && $parts[1] !== '' ? substr($parts[1], 0, 1) : '';
        return strtoupper($first . $second);
    };
    $uploadedBy = [];
    if($variant == 'completed' && $documentCount > 0):
        foreach($documents as $tdoc):
            $uploadedEmployee = $tdoc->user->employee ?? null;
            if(isset($uploadedEmployee->full_name) && !empty($uploadedEmployee->full_name)):
                $uploadedBy[$tdoc->created_by] = [
                    'photo' => $employeePhotoUrl($uploadedEmployee),
                    'name' => $uploadedEmployee->full_name,
                    'date' => (isset($tdoc->created_at) && !empty($tdoc->created_at) ? date('jS M, Y', strtotime($tdoc->created_at)) : ''),
                ];
            endif;
        endforeach;
    endif;
    $isCompleted = $variant == 'completed';
@endphp

<div class="adm-process-task-card adm-process-task-card--{{ $variant }}">
    <div class="adm-process-task-card__main">
        <span class="adm-process-task-card__icon">
            <i data-lucide="{{ $isCompleted ? 'check' : 'clock' }}"></i>
        </span>
        <div class="adm-process-task-card__copy">
            <div class="adm-process-task-card__title">
                {{ $taskName }}
                @if($taskOutcome != '')
                    <span class="adm-process-task-card__outcome">Outcome: {{ $taskOutcome }}</span>
                @endif
            </div>
            @if(!empty($taskDescription))
                <div class="adm-process-task-card__desc">{{ $taskDescription }}</div>
            @endif
        </div>

        @if($canManageTask)
            <div class="adm-process-task-card__actions">
                <div class="dropdown">
                    <button class="dropdown-toggle btn {{ $isCompleted ? 'btn-success' : 'btn-warning' }} text-white adm-process-action-btn" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="activity"></i> {{ $isCompleted ? 'Actions' : 'Update' }} <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="dropdown-menu w-64">
                        <ul class="dropdown-content adm-process-action-menu">
                            <li>
                                <a href="javascript:void(0);" data-interview="{{ $taskEntity && $taskEntity->interview == 'Yes' ? 1 : 0 }}" data-applicantid="{{ $applicant->id }}" data-applicanttaskid="{{ $task->id }}" data-tw-toggle="modal" data-tw-target="#viewTaskLogModal" class="viewTaskLogBtn dropdown-item adm-process-action-item">
                                    <span class="adm-process-action-item__icon"><i data-lucide="eye-off"></i></span>
                                    <span>View Log</span>
                                </a>
                            </li>
                            @if(!$isCompleted && $taskStatusRequirement == 'Yes')
                                <li>
                                    <a data-applicanttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#updateTaskOutcomeModal" class="updateTaskOutcome dropdown-item adm-process-action-item">
                                        <span class="adm-process-action-item__icon"><i data-lucide="settings"></i></span>
                                        <span>Update Outcome</span>
                                    </a>
                                </li>
                            @endif
                            @if(!$isCompleted && $taskUploadRequirement == 'Yes')
                                <li>
                                    <a data-applicanttaskid="{{ $task->id }}" href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#uploadTaskDocumentModal" class="uploadTaskDoc dropdown-item adm-process-action-item">
                                        <span class="adm-process-action-item__icon"><i data-lucide="upload-cloud"></i></span>
                                        <span>Upload Documents</span>
                                    </a>
                                </li>
                            @endif
                            @if(!$isCompleted && $canCompleteTask)
                                <li>
                                    <a data-recordid="{{ $task->id }}" href="javascript:void(0);" class="markAsCompleted dropdown-item adm-process-action-item">
                                        <span class="adm-process-action-item__icon adm-process-action-item__icon--success"><i data-lucide="check-circle"></i></span>
                                        <span>Mark as Complete</span>
                                    </a>
                                </li>
                            @endif
                            @if($isCompleted)
                                <li>
                                    <a data-recordid="{{ $task->id }}" href="javascript:void(0);" class="markAsPending dropdown-item adm-process-action-item">
                                        <span class="adm-process-action-item__icon adm-process-action-item__icon--warning"><i data-lucide="clock"></i></span>
                                        <span>Mark as Pending</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                @if(!$isCompleted && $canDeleteTask)
                    <button type="button" data-taskid="{{ $task->id }}" class="deleteApplicantTask btn btn-danger adm-process-delete-btn" title="Delete">
                        <i data-lucide="trash-2"></i>
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="adm-process-task-card__foot">
        <div class="adm-process-task-card__files">
            <span class="adm-process-task-card__foot-label">Files</span>
            @if($documentCount > 0)
                @foreach($documents as $tdoc)
                    @if(isset($tdoc->current_file_name) && !empty($tdoc->current_file_name) && isset($tdoc->id) && $tdoc->id > 0)
                        @php
                            $docType = strtolower($tdoc->doc_type ?? '');
                            $isImage = in_array($docType, ['jpg', 'jpeg', 'png', 'gif']);
                            $docLabel = !empty($tdoc->display_file_name)
                                ? $tdoc->display_file_name
                                : (!empty($tdoc->current_file_name) ? $tdoc->current_file_name : 'Document');
                        @endphp
                        <a data-id="{{ $tdoc->id }}" class="downloadDoc adm-process-file-chip" title="{{ $docLabel }}" href="javascript:void(0);">
                            <span class="adm-process-file-chip__icon"><i data-lucide="{{ $isImage ? 'image' : 'file-text' }}"></i></span>
                            <span class="adm-process-file-chip__name">{{ $docLabel }}</span>
                        </a>
                    @endif
                @endforeach
            @else
                <span class="adm-process-task-card__empty">No files</span>
            @endif
        </div>

        @if($isCompleted)
            <div class="adm-process-task-card__people adm-process-task-card__people--completed">
                <div class="adm-process-person">
                    @php
                        $completedEmployee = $task->updatedBy->employee ?? null;
                        $completedName = isset($completedEmployee->full_name) ? $completedEmployee->full_name : 'Unknown Employee';
                        $completedPhoto = $employeePhotoUrl($completedEmployee);
                    @endphp
                    <span class="adm-process-avatar">
                        @if(!empty($completedPhoto))
                            <img alt="{{ $completedName }}" src="{{ $completedPhoto }}">
                        @else
                            {{ $initials($completedName) }}
                        @endif
                    </span>
                    <span>
                        <span class="adm-process-task-card__foot-label">Completed By</span>
                        <strong>{{ $completedName }}</strong>
                        @if(isset($task->updated_at) && !empty($task->updated_at))
                            <em>{{ date('jS M, Y', strtotime($task->updated_at)) }}</em>
                        @endif
                    </span>
                </div>
                @foreach($uploadedBy as $upby)
                    <div class="adm-process-person">
                        <span class="adm-process-avatar adm-process-avatar--alt">
                            @if(!empty($upby['photo']))
                                <img alt="{{ $upby['name'] }}" src="{{ $upby['photo'] }}">
                            @else
                                {{ $initials($upby['name']) }}
                            @endif
                        </span>
                        <span>
                            <span class="adm-process-task-card__foot-label">Uploaded By</span>
                            <strong>{{ $upby['name'] }}</strong>
                            @if(!empty($upby['date']))
                                <em>{{ $upby['date'] }}</em>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="adm-process-task-card__people">
                <span class="adm-process-task-card__foot-label">Assigned To</span>
                @if(method_exists($assignedUsers, 'count') && $assignedUsers->count() > 0)
                    <button type="button" class="adm-process-avatar-stack taskUserLoader" data-taskid="{{ $taskEntity->id ?? 0 }}">
                        @foreach($assignedUsers as $usr)
                            @if($loop->index > 2)
                                @break
                            @endif
                            @php
                                $assigneeEmployee = $usr->user->employee ?? null;
                                $assigneeName = isset($assigneeEmployee->full_name) ? $assigneeEmployee->full_name : 'Unknown Employee';
                                $assigneePhoto = $employeePhotoUrl($assigneeEmployee);
                            @endphp
                            <span class="adm-process-avatar" title="{{ $assigneeName }}">
                                @if(!empty($assigneePhoto))
                                    <img alt="{{ $assigneeName }}" src="{{ $assigneePhoto }}">
                                @else
                                    {{ $initials($assigneeName) }}
                                @endif
                            </span>
                        @endforeach
                    </button>
                @else
                    <span class="adm-process-task-card__empty">Not Found</span>
                @endif
            </div>
        @endif
    </div>
</div>
