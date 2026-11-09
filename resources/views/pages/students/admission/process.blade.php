@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Applicant processes on the admission portal shell: one board per
        Applicant-phase process (Pending / In Progress / Completed lanes), an
        archived view per process and the task dialogs. Behaviour lives in
        resources/js/admission-process.js, whose only markup is this page;
        the record header's status, photo and e-signature actions keep going
        through admission-global.js and admission-vue.js (partials/
        portal-record-modals). Hook classes kept from the previous page:
        .markAsCompleted, .markAsPending, .deleteApplicantTask,
        .updateTaskOutcome, .uploadTaskDoc, .viewTaskLogBtn, .taskUserLoader,
        .downloadDoc, .restore_btn, .task_list_id, #studentProcessListForm,
        #addProcessItemsAdd, #updateTaskOutcomeForm, #uploadTaskDocumentForm,
        #processTaskLogTable, .processTaskArchiveListTable, #confirmModal
        (.agreeWith data-recordid/data-status/data-applicant).
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'process';
        $adpUserId = (int) auth()->user()->id;
        $adpExisting = $existingTask ?? [];

        $adpPaths = [
            'check' => '<path d="M20 6 9 17l-5-5"/>',
            'undo' => '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
            'flag' => '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
            'users' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.5 3-6 7-6s7 2.5 7 6M17 4.5a4 4 0 0 1 0 7M22 21c0-2.8-1.8-4.8-4.5-5.6"/>',
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'trash' => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'archive' => '<rect x="2" y="4" width="20" height="5" rx="1"/><path d="M4 9v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"/><path d="M10 13h4"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'ok' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpCheck = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
        $adpX = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
        // Staff avatar (photo or initials) for the popovers.
        $adpWho = function ($employee, $fallback = '') {
            $name = trim((string) ($employee->full_name ?? $fallback));
            $photo = $employee ? (string) $employee->photo_url : '';
            $inner = ($photo !== '' && !\App\Support\Avatar::isGenerated($photo)) ? '<img src="'.e($photo).'" alt="">' : e(\App\Support\Avatar::initialsOnly($name !== '' ? $name : 'Unknown'));
            return '<span class="adp-who">'.$inner.'</span>';
        };
    @endphp

    @include('pages.students.admission.partials.portal-record')

    @if(empty($processGroup))
        <section class="adp-panel adp-proc">
            <h3 class="adp-h3">Processes</h3>
            <div class="adp-empty"><b>No processes yet.</b><span>No Applicant-phase process has tasks configured in Process settings.</span></div>
        </section>
    @else
        @foreach($processGroup as $adpProc)
            @php
                $adpLive = $adpProc['pendingTask']->count() + $adpProc['inProgressTask']->count() + $adpProc['completedTask']->count();
                $adpDone = $adpProc['completedTask']->count();
                $adpPct = $adpLive > 0 ? (int) round($adpDone / $adpLive * 100) : 0;
                $adpLanes = [
                    ['Pending', $adpProc['pendingTask'], 'pending'],
                    ['In Progress', $adpProc['inProgressTask'], 'in-progress'],
                    ['Completed', $adpProc['completedTask'], 'completed'],
                ];
            @endphp
            <section class="adp-panel adp-proc" data-adp-proc="{{ $adpProc['id'] }}">
                <div class="adp-proc__head">
                    <div class="adp-proc__copy">
                        <div class="adp-proc__title">
                            <h3 class="adp-h3">{{ $adpProc['name'] }}</h3>
                            <span class="adp-pill adp-pill--{{ $adpProc['phase'] === 'Applicant' ? 'blue' : 'muted' }} adp-pill--tight"><i></i>{{ $adpProc['phase'] }} phase</span>
                        </div>
                        <p class="adp-proc__desc">Tasks {{ $adpProc['auto'] ? 'are generated on submission' : 'are assigned by the admissions team' }}. Tasks with an outcome or upload stay Pending until both are in, then move to In Progress, where they can be marked complete; tasks needing neither are completed from Pending. Archive any that do not apply.</p>
                    </div>
                    <div class="adp-proc__tools">
                        {{-- Add tasks: hidden for now at the user's request (22 Oct 2026); the dialog and its script stay in place.
                        <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-tw-toggle="modal" data-tw-target="#addTasksModal">{!! $adpIcon('plus', 13) !!}Add tasks</button>
                        --}}
                        <span class="adp-tprog" title="{{ $adpDone }} of {{ $adpLive }} tasks complete"><span class="adp-tprog__bar"><i style="width:{{ $adpPct }}%"></i></span><small>{{ $adpDone }} of {{ $adpLive }} complete</small></span>
                        <div class="adp-seg" role="group" aria-label="{{ $adpProc['name'] }} view">
                            <button type="button" class="on" aria-pressed="true" data-adp-view="board">{!! $adpIcon('list', 13) !!}Board<b>{{ $adpLive }}</b></button>
                            <button type="button" aria-pressed="false" data-adp-view="archive">{!! $adpIcon('archive', 13) !!}Archived<b>{{ $adpProc['archivedCount'] }}</b></button>
                        </div>
                    </div>
                </div>

                <div data-adp-board>
                    @if($adpLive === 0)
                        <div class="adp-empty"><b>No open tasks in this process.</b><span>Open <b>Archived</b> to restore one.</span></div>
                    @else
                        <div class="adp-board">
                            @foreach($adpLanes as [$adpLaneName, $adpLaneTasks, $adpLaneKey])
                                <div class="adp-lane">
                                    <div class="adp-lane__h"><span>{{ $adpLaneName }}</span><b>{{ $adpLaneTasks->count() }}</b></div>
                                    @forelse($adpLaneTasks as $task)
                                        @include('pages.students.admission.partials.portal-task-card', ['task' => $task, 'lane' => $adpLaneKey])
                                    @empty
                                        <div class="adp-lane__empty">—</div>
                                    @endforelse
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div data-adp-archive hidden>
                    <div class="adp-arch-loading" role="status" aria-live="polite" data-adp-archive-loading><span class="adp-spin"></span>Loading archived tasks…</div>
                    <div class="adp-empty" data-adp-archive-empty hidden><b>Nothing archived.</b><span>Tasks you archive from the board appear here and can be restored.</span></div>
                    <div class="adp-dtable" data-adp-archive-table hidden>
                        <div id="processTaskArchiveListTable_{{ $adpProc['id'] }}" data-process="{{ $adpProc['id'] }}" data-applicant="{{ $applicant->id }}" class="processTaskArchiveListTable adp-table"></div>
                    </div>
                </div>
            </section>
        @endforeach
    @endif

    @include('pages.students.admission.partials.portal-process-modals')
    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs', ['adpConfirmAttrs' => 'data-recordid="0" data-status="none" data-applicant="'.$applicant->id.'"'])
@endsection

@section('script')
    @vite('resources/js/admission-process.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other, details-only blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
