{{--
    Dialogs of the process page: add tasks (#addTasksModal / #studentProcessListForm),
    update outcome (#updateTaskOutcomeModal / #updateTaskOutcomeForm, body filled by
    admission.show.task.outmoce.statuses), upload documents (#uploadTaskDocumentModal /
    #uploadTaskDocumentForm, Dropzone), task change log (#viewTaskLogModal /
    #processTaskLogTable) and assigned staff (#taskUserModal). Needs $process,
    $adpExisting, $applicant, $adpIcon, $adpCheck, $adpX, $adpSpin, $adpClose.
--}}
<div id="addTasksModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="#" id="studentProcessListForm">
            <div class="modal-content">
                <div class="modal-header"><h3>Add tasks</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    {{-- without Delete Task (user, 13 Nov 2026) the tasks already on the board stay ticked; the save ignores removals too --}}
                    @php $adpCanRemove = (int) (auth()->user()->priv()['applicant_delete_task'] ?? 0) === 1; @endphp
                    <p class="adp-copy">{{ $adpCanRemove ? 'Ticked tasks are on the board. Untick one to archive it; tick it again later to restore it.' : 'Ticked tasks are on the board. Tick a task to add it. Removing a task needs the Delete Task permission.' }}</p>
                    @foreach($process as $adpPro)
                        @if(isset($adpPro->tasks) && $adpPro->tasks->count() > 0)
                            <div class="adp-task-pick__group">
                                <small class="adp-task-pick__label">{{ $adpPro->name }}</small>
                                <div class="adp-task-pick">
                                    @foreach($adpPro->tasks as $adpTl)
                                        @php $adpInputId = 'process_task_'.$adpPro->id.'_'.$adpTl->id; @endphp
                                        <label class="adp-opt adp-opt--chk" for="{{ $adpInputId }}">
                                            <input id="{{ $adpInputId }}" class="adp-opt__input task_list_id" name="task_list_ids[]" type="checkbox" value="{{ $adpTl->id }}" {{ in_array($adpTl->id, $adpExisting) ? 'checked' : '' }} {{ !$adpCanRemove && in_array($adpTl->id, $adpExisting) ? 'disabled' : '' }}>
                                            <span class="adp-opt__box">{!! $adpCheck !!}</span>
                                            <span>{{ $adpTl->name }}@if(!empty($adpTl->short_description) && trim($adpTl->short_description) !== trim($adpTl->name))<span class="adp-opt__hint">{{ $adpTl->short_description }}</span>@endif</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    <div class="adp-err" data-adp-pick-error></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="closeProcessDropdown" class="adp-btn adp-btn--ghost" data-tw-dismiss="modal">Cancel</button>
                    <button type="submit" id="addProcessItemsAdd" class="adp-btn adp-btn--primary">Save tasks {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="updateTaskOutcomeModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="updateTaskOutcomeForm">
            <div class="modal-content">
                <div class="modal-header"><h3>Update outcome <span class="adp-modal__sub" data-adp-outcome-task></span></h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div data-adp-outcome-body>
                        <div class="adp-arch-loading"><span class="adp-spin"></span>Loading outcomes…</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="submit" id="updateOutcomeBtn" class="adp-btn adp-btn--primary">Save outcome {!! $adpSpin !!}</button>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    <input type="hidden" name="applicant_task_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="uploadTaskDocumentModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h3>Upload documents</h3>{!! $adpClose !!}</div>
            <div class="modal-body">
                <div class="adp-doc-picked">
                    <span class="adp-doc-picked__ico">{!! $adpIcon('upload', 18) !!}</span>
                    <span class="adp-doc-picked__t"><b data-adp-upload-task>Task</b><small data-adp-upload-sub>Evidence for this task.</small></span>
                </div>
                <form method="post" action="{{ route('admission.upload.task.documents') }}" class="dropzone adp-dropzone" id="uploadTaskDocumentForm" enctype="multipart/form-data">
                    <div class="fallback"><input name="documents[]" multiple type="file" /></div>
                    <div class="dz-message" data-dz-message>
                        <span class="adp-dz-ico">{!! $adpIcon('upload', 22) !!}</span>
                        <b>Drop files here or click to browse</b>
                        <small>PDF, Office, image or text · up to 5 MB each · 10 files per upload</small>
                    </div>
                    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                    <input type="hidden" name="applicant_task_id" value="0"/>
                    <input type="hidden" name="display_file_name" value=""/>
                    <input type="hidden" name="hard_copy_check" value="0"/>
                </form>
                <ul class="adp-dz-list" data-adp-dz-list></ul>
                <div class="adp-notice adp-notice--bad" data-adp-upload-error hidden></div>
                <div class="adp-grid-2">
                    <div class="adp-field">
                        <label for="process_doc_name">Document name</label>
                        <input type="text" id="process_doc_name" class="adp-ctl" name="process_doc_name" autocomplete="off">
                        <span class="adp-opt__hint">Shown instead of the file name.</span>
                    </div>
                    <div class="adp-field">
                        <label for="hard_copy_check_status">Hard copy checked?</label>
                        <div>
                            <label class="adp-sw" for="hard_copy_check_status"><input id="hard_copy_check_status" class="adp-sw__input" name="hard_copy_check_status" value="1" type="checkbox"><span class="adp-sw__dot"><span class="adp-sw__on">{!! $adpCheck !!}</span><span class="adp-sw__off">{!! $adpX !!}</span></span><span class="adp-sw__yes">Yes</span><span class="adp-sw__no">No</span></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                <button type="button" id="uploadProcessDoc" class="adp-btn adp-btn--primary">{!! $adpIcon('upload', 14) !!}<span data-adp-upload-label>Upload</span> {!! $adpSpin !!}</button>
            </div>
        </div>
    </div>
</div>

<div id="viewTaskLogModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h3>Task change log</h3>{!! $adpClose !!}</div>
            <div class="modal-body">
                <div class="adp-doc-picked">
                    <span class="adp-doc-picked__ico">{!! $adpIcon('clock', 18) !!}</span>
                    <span class="adp-doc-picked__t"><b data-adp-log-task>Task</b><small data-adp-log-status></small></span>
                    <span class="adp-pill adp-pill--muted" data-adp-log-count hidden><i></i></span>
                </div>
                <div class="adp-dtable">
                    <div id="processTaskLogTable" data-interview="0" data-applicantid="{{ $applicant->id }}" data-applicanttaskid="0" class="adp-table"></div>
                </div>
                {{-- Interview tasks also list their interview sessions (admission.applicant.interview.log). --}}
                <div data-adp-log-interviews hidden>
                    <h4 class="adp-subhead adp-log-subhead">Interviews</h4>
                    <div class="adp-dtable">
                        <div id="processInterviewLogTable" class="adp-table"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Close</button>
            </div>
        </div>
    </div>
</div>

<div id="taskUserModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h3>Assigned staff <span class="adp-modal__sub" data-adp-users-task></span></h3>{!! $adpClose !!}</div>
            <div class="modal-body">
                <div class="adp-arch-loading taskUserModalLoader"><span class="adp-spin"></span>Loading staff…</div>
                <div class="taskUserModalContent adp-dtable" style="display: none;">
                    <table class="adp-plain-table">
                        <thead>
                            <tr><th>Name</th><th>Department</th><th>Work type</th><th>Work no.</th><th>Status</th></tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Close</button>
            </div>
        </div>
    </div>
</div>
