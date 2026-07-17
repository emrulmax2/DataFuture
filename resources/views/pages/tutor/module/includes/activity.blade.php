@php
    $documentPalettes = [
        ['#0b5f58', '#12867b', '#0d7c73', 'rgba(13,124,115,.10)'],
        ['#8a6420', '#c69233', '#b98a2e', 'rgba(185,138,46,.12)'],
        ['#25578c', '#3b7fc4', '#2f6fb0', 'rgba(47,111,176,.10)'],
        ['#5f3d78', '#8a5aa8', '#7a4f97', 'rgba(122,79,151,.10)'],
    ];
@endphp

<div class="tm-section-head no-border">
    <h2 class="tm-section-title">Module Documents</h2>
    <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#confirmModalPlanTask" data-planid="{{ $plan->id }}" data-moduleCretionId="{{ $plan->module_creation_id }}" class="callModalPlanTask tm-btn tm-btn-primary">
        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Synchronize Documents
    </button>
</div>

<div class="grid grid-cols-12 gap-5">
    @forelse ($planTasks as $key => $task)
        @php
            $palette = $documentPalettes[$key % count($documentPalettes)];
            $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
            $lastUpdate = ($task->task->updated_at) ?? $task->task->created_at;
            $required_date = '';
            $days_reminder = (isset($task->task->days_reminder) && $task->task->days_reminder > 0 ? $task->task->days_reminder : 0);
            $class_start = (isset($plan->attenTerm->start_date) && !empty($plan->attenTerm->start_date) ? date('Y-m-d', strtotime($plan->attenTerm->start_date)) : '');

            if(!empty($class_start)):
                $required_date = date('jS F, Y', strtotime('+'.$days_reminder.' days', strtotime($class_start)));
            endif;

            $document = [];
            $uploadedBy = '';
            $uploadedAt = '';
        @endphp

        @if($task->taskUploads->isNotEmpty())
            @foreach($task->taskUploads as $upload)
                @php
                    $document['type'] = $upload->doc_type;
                    $document['url'] = Storage::disk('s3')->temporaryUrl('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name, now()->addMinutes(120));
                    $uploadedBy = $upload->createdBy->employee->full_name ?? '';
                    $uploadedAt = (isset($upload->created_at) && !empty($upload->created_at) ? date('jS F, Y', strtotime($upload->created_at)) : '');
                @endphp
            @endforeach
        @endif

        <div class="col-span-12 md:col-span-6 2xl:col-span-3">
            <div class="tm-doc-card">
                <div class="tm-doc-banner" style="background: linear-gradient(135deg, {{ $palette[0] }}, {{ $palette[1] }});">
                    <span class="tm-doc-corner"><i data-lucide="file-text" class="w-4 h-4"></i></span>
                    <span class="tm-doc-ext">{{ !empty($document['type'] ?? '') ? strtoupper($document['type']) : 'DOC' }}</span>
                    <div class="tm-doc-title">{{ $task->task->name }}</div>
                </div>
                <div class="tm-doc-body">
                    <div class="tm-doc-meta">
                        <span class="tm-doc-chip" style="color: {{ $palette[2] }}; background: {{ $palette[3] }};">{{ $task->task->category ?? 'Document' }}</span>
                        <span>{{ !empty($document) ? 'Available' : 'Pending upload' }}</span>
                    </div>

                    <div class="tm-doc-info">
                        <div>
                            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                            <span>Required by</span>
                            <strong>{{ !empty($required_date) ? $required_date : 'Not set' }}</strong>
                        </div>
                        @if(!empty($uploadedBy))
                            <div>
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                <span>{{ $uploadedBy }}</span>
                                <strong>{{ $uploadedAt }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="tm-doc-actions">
                        @if(!empty($document) && count($document) > 0 && isset($document['url']) && !empty($document['url']))
                            <a target="_blank" href="{{ $document['url'] }}" class="tm-btn tm-btn-primary">
                                <i data-lucide="{{ ($document['type'] !="pdf" && $document['type']!="xls" && $document['type']!="doc" && $document['type']!="docx") ? 'file-down' : 'download' }}" class="w-4 h-4"></i>
                                Download
                            </a>
                        @else
                            <span class="tm-btn tm-btn-secondary text-slate-400">
                                <i data-lucide="x-circle" class="w-4 h-4"></i> Not Available
                            </span>
                        @endif
                        <a data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" data-plantaskid="{{ $task->task->id }}" class="task-upload__Button tm-btn tm-btn-primary" href="javascript:void(0);">
                            <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-12">
            <div class="tm-empty">No module documents found.</div>
        </div>
    @endforelse
</div>

<style>
    #tutorModuleDetails .tm-doc-card {
        background: #fff;
        border: 1px solid #ecebe2;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(16,49,46,.05);
        height: 100%;
        overflow: hidden;
        transition: box-shadow .16s ease, transform .16s ease;
    }

    #tutorModuleDetails .tm-doc-card:hover {
        box-shadow: 0 10px 28px rgba(16,49,46,.10);
        transform: translateY(-2px);
    }

    #tutorModuleDetails .tm-doc-banner {
        display: flex;
        flex-direction: column;
        height: 180px;
        justify-content: flex-end;
        padding: 18px 20px;
        position: relative;
    }

    #tutorModuleDetails .tm-doc-corner {
        align-items: center;
        background: rgba(255,255,255,.22);
        border-radius: 9px;
        color: #fff;
        display: flex;
        height: 34px;
        justify-content: center;
        position: absolute;
        right: 14px;
        top: 14px;
        width: 34px;
    }

    #tutorModuleDetails .tm-doc-ext {
        color: rgba(255,255,255,.82);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .7px;
    }

    #tutorModuleDetails .tm-doc-title {
        color: #fff;
        font-family: "IBM Plex Serif", Georgia, serif;
        font-size: 16px;
        font-weight: 600;
        line-height: 1.22;
        margin-top: 3px;
        text-shadow: 0 1px 2px rgba(0,0,0,.12);
    }

    #tutorModuleDetails .tm-doc-body {
        padding: 16px 20px 18px;
    }

    #tutorModuleDetails .tm-doc-meta {
        align-items: center;
        display: flex;
        gap: 8px;
        margin-bottom: 14px;
    }

    #tutorModuleDetails .tm-doc-meta > span:last-child {
        color: #9aa7a4;
        font-size: 11px;
    }

    #tutorModuleDetails .tm-doc-chip {
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        padding: 3px 9px;
        text-transform: uppercase;
    }

    #tutorModuleDetails .tm-doc-info {
        display: flex;
        flex-direction: column;
        gap: 9px;
        margin-bottom: 16px;
    }

    #tutorModuleDetails .tm-doc-info div {
        align-items: center;
        color: #39514d;
        display: flex;
        gap: 8px;
        min-width: 0;
    }

    #tutorModuleDetails .tm-doc-info i {
        color: #a1802f;
        flex: none;
    }

    #tutorModuleDetails .tm-doc-info span {
        color: #39514d;
        font-size: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-doc-info strong {
        color: #12312e;
        font-size: 12.5px;
        font-weight: 600;
        margin-left: auto;
        white-space: nowrap;
    }

    #tutorModuleDetails .tm-doc-actions {
        display: grid;
        gap: 9px;
        grid-template-columns: 1fr 1fr;
    }
</style>

<!-- BEGIN: Plan Task Confirm Modal Content -->
<div id="confirmModalPlanTask" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="info" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 title">Are you sure?</div>
                    <div class="text-slate-500 mt-2 description"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWithPlanTask btn btn-primary w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Plan Task Confirm Modal Content -->
