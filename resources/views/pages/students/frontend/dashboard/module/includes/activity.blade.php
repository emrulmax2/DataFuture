{{--
    Course content: the assignment brief and supporting documents the tutor
    has attached to this module's plan.
--}}
@php
    $initials = function ($name) {
        $letters = '';

        foreach (preg_split('/\s+/', trim((string) $name)) as $word) {
            if ($word !== '' && ctype_alpha($word[0])) {
                $letters .= strtoupper($word[0]);
            }

            if (strlen($letters) === 2) {
                break;
            }
        }

        return $letters !== '' ? $letters : 'DOC';
    };

    $tile = function ($name) {
        $variants = ['', ' spf-module__icon--v1', ' spf-module__icon--v2', ' spf-module__icon--v3', ' spf-module__icon--v4'];

        return 'spf-module__icon' . $variants[crc32((string) $name) % count($variants)];
    };

    $classStart = (isset($plan->attenTerm->start_date) && !empty($plan->attenTerm->start_date))
        ? date('Y-m-d', strtotime($plan->attenTerm->start_date))
        : '';
@endphp

<div class="spf-page-head spf-page-head--baseline" style="margin-bottom:16px">
    <h2 class="spf-h2">Assignment brief &amp; important documents</h2>
    <span class="spf-eyebrow">{{ count($planTasks) }} {{ count($planTasks) === 1 ? 'file' : 'files' }}</span>
</div>

@if(count($planTasks) > 0)
    <div class="spf-cardgrid">
        @foreach ($planTasks as $task)
            @php
                $daysReminder = (isset($task->task->days_reminder) && $task->task->days_reminder > 0) ? $task->task->days_reminder : 0;
                $requiredDate = !empty($classStart)
                    ? date('jS F, Y', strtotime('+' . $daysReminder . ' days', strtotime($classStart)))
                    : '';

                $document = [];
                $uploadedBy = '';
                $uploadedAt = '';

                if ($task->taskUploads->isNotEmpty()) {
                    foreach ($task->taskUploads as $upload) {
                        $document['type'] = $upload->doc_type;
                        $document['url'] = Storage::disk('s3')->temporaryUrl(
                            'public/plans/plan_task/' . $task->task->id . '/' . $upload->current_file_name,
                            now()->addMinutes(120)
                        );
                        $uploadedBy = $upload->createdBy->employee->full_name ?? '';
                        $uploadedAt = !empty($upload->created_at) ? date('jS F, Y', strtotime($upload->created_at)) : '';
                    }
                }
            @endphp
            <div class="spf-doccard">
                <div class="spf-doccard__head">
                    <span class="{{ $tile($task->task->name) }}">{{ $initials($task->task->name) }}</span>
                    <div class="spf-doccard__title">{{ $task->task->name }}</div>
                </div>
                <div class="spf-doccard__body">
                    @if(!empty($requiredDate))
                        <div class="spf-doccard__meta"><span>Upload required by</span> <strong>{{ $requiredDate }}</strong></div>
                    @endif
                    @if(!empty($uploadedBy))
                        <div class="spf-doccard__meta"><span>Uploaded by</span> <strong>{{ $uploadedBy }}</strong></div>
                    @endif
                    @if(!empty($uploadedAt))
                        <div class="spf-doccard__meta"><span>Uploaded at</span> <strong>{{ $uploadedAt }}</strong></div>
                    @endif
                </div>
                @if(!empty($document['url']))
                    <a target="_blank" rel="noopener" href="{{ $document['url'] }}" class="spf-doccard__foot">
                        <i data-lucide="download" class="w-4 h-4"></i> Download file
                    </a>
                @else
                    <div class="spf-doccard__foot spf-doccard__foot--off">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> File not available
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="spf-empty">No course documents have been uploaded for this module yet.</div>
@endif
