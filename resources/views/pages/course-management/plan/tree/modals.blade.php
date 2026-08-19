{{--
    The five dialogs the tree view drives. Ids and field names are the ones the
    endpoints already expect — `plans.tree.update`, `plans.tree.store.tutorial`,
    `plans.tree.sync.tutorial`, `plans.tree.assigned.list` and
    `plans.assign.participants` — so only the chrome is new.

    Required: $users  Active users, for the tutor pickers
              $room   Rooms, for the room pickers
--}}
@php
    $treeDays = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
    $treeTypes = ['Theory', 'Practical', 'Tutorial', 'Seminar'];
@endphp

{{-- ---------------------------------------------------------------- --}}
{{-- Edit plan                                                         --}}
{{-- ---------------------------------------------------------------- --}}
<div id="editPlanModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
        <form method="POST" action="#" id="editPlanForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span>Class plan</span></div>
                        <h2 class="cm-modal__title cm-serif">Edit Plan</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="cm-modal__body cm-modal__body--grid3">
                    <div class="cm-readout"><span class="cm-readout__label">Term</span><span class="cm-readout__value termName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Course</span><span class="cm-readout__value courseName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Group</span><span class="cm-readout__value groupName">&mdash;</span></div>

                    <div class="cm-field">
                        <label for="tp_module_creation_id">Module <span>*</span></label>
                        <select id="tp_module_creation_id" name="module_creation_id" class="cm-select module_creation_id"><option value="">Please Select</option></select>
                        <div class="acc__input-error error-module_creation_id"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tp_rooms_id">Room <span>*</span></label>
                        <select id="tp_rooms_id" name="rooms_id" class="cm-select rooms_id">
                            <option value="">Please Select</option>
                            @foreach($room as $rm)
                                <option value="{{ $rm->id }}">{{ $rm->name }} - {{ $rm->venue->name ?? '' }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-rooms_id"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tp_class_type">Class Type <span>*</span></label>
                        <select id="tp_class_type" name="class_type" class="cm-select class_type">
                            <option value="">Please Select</option>
                            @foreach($treeTypes as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-class_type"></div>
                    </div>

                    {{-- Theory is led by the tutor, everything else by the
                         personal tutor. Both stay in the DOM so a saved value on
                         the hidden one is never cleared. --}}
                    <div class="cm-field tpTutorWrap">
                        <label for="tp_tutor_id">Tutor</label>
                        <select id="tp_tutor_id" name="tutor_id" class="cm-select tutor_id">
                            <option value="">Please Select</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->full_name }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-tutor_id"></div>
                    </div>

                    <div class="cm-field tpPTutorWrap">
                        <label for="tp_personal_tutor_id">Personal Tutor</label>
                        <select id="tp_personal_tutor_id" name="personal_tutor_id" class="cm-select personal_tutor_id">
                            <option value="">Please Select</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->full_name }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-personal_tutor_id"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tp_start_time">Start Time <span>*</span></label>
                        <input id="tp_start_time" type="text" name="start_time" class="cm-input treeTimeField start_time" placeholder="14:30">
                        <div class="acc__input-error error-start_time"></div>
                    </div>
                    <div class="cm-field">
                        <label for="tp_end_time">End Time <span>*</span></label>
                        <input id="tp_end_time" type="text" name="end_time" class="cm-input treeTimeField end_time" placeholder="16:30">
                        <div class="acc__input-error error-end_time"></div>
                    </div>
                    <div class="cm-field">
                        <label for="tp_submission_date">Submission Date</label>
                        <input id="tp_submission_date" type="text" name="submission_date" class="cm-input treeDateField submission_date" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY" readonly>
                        <div class="acc__input-error error-submission_date"></div>
                    </div>

                    <div class="cm-field cm-field--span3">
                        <label>Class Day <span>*</span></label>
                        <div class="cm-optpick" data-cm-optpick="tp_class_day">
                            @foreach($treeDays as $v => $l)
                                <button type="button" class="cm-optpick__btn" data-cm-opt="{{ $v }}">
                                    <span class="cm-optpick__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg></span>
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="tp_class_day" name="class_day" value="">
                        <div class="acc__input-error error-class_day"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tp_virtual_room">Virtual Room</label>
                        <textarea id="tp_virtual_room" name="virtual_room" class="cm-input cm-textarea virtual_room"></textarea>
                    </div>
                    <div class="cm-field cm-field--span2">
                        <label for="tp_note">Note</label>
                        <textarea id="tp_note" name="note" class="cm-input cm-textarea note"></textarea>
                    </div>
                </div>

                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="updateTreePlan" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Update
                    </button>
                    <input type="hidden" name="id" value="0">
                    <input type="hidden" name="group_id" value="">
                    <input type="hidden" name="module_enrollment_key" value="">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ---------------------------------------------------------------- --}}
{{-- Tutorial details                                                  --}}
{{-- ---------------------------------------------------------------- --}}
<div id="tutorialDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
        <form method="POST" action="#" id="tutorialDetailsForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span>Tutorial</span></div>
                        {{-- The one dialog does both; the JS names it on open. --}}
                        <h2 class="cm-modal__title cm-serif" data-cm-tutorial-title>Edit Tutorial</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="cm-modal__body cm-modal__body--grid3">
                    <div class="cm-readout"><span class="cm-readout__label">Term</span><span class="cm-readout__value tuTermName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Course</span><span class="cm-readout__value tuCourseName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Group</span><span class="cm-readout__value tuGroupName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Module</span><span class="cm-readout__value tuModuleName">&mdash;</span></div>
                    <div class="cm-readout"><span class="cm-readout__label">Venue</span><span class="cm-readout__value tuVenueName">&mdash;</span></div>

                    <div class="cm-field">
                        <label for="tu_rooms_id">Room <span>*</span></label>
                        <select id="tu_rooms_id" name="rooms_id" class="cm-select rooms_id">
                            <option value="">Please Select</option>
                            @foreach($room as $rm)
                                <option value="{{ $rm->id }}">{{ $rm->name }} - {{ $rm->venue->name ?? '' }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-rooms_id"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tu_personal_tutor_id">Personal Tutor <span>*</span></label>
                        <select id="tu_personal_tutor_id" name="personal_tutor_id" class="cm-select">
                            <option value="">Please Select</option>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->full_name }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-personal_tutor_id"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tu_start_time">Start Time <span>*</span></label>
                        <input id="tu_start_time" type="text" name="start_time" class="cm-input treeTimeField start_time" placeholder="14:30">
                        <div class="acc__input-error error-start_time"></div>
                    </div>
                    <div class="cm-field">
                        <label for="tu_end_time">End Time <span>*</span></label>
                        <input id="tu_end_time" type="text" name="end_time" class="cm-input treeTimeField end_time" placeholder="16:30">
                        <div class="acc__input-error error-end_time"></div>
                    </div>

                    <div class="cm-field cm-field--span3">
                        <label>Class Day <span>*</span></label>
                        <div class="cm-optpick" data-cm-optpick="tu_class_day">
                            @foreach($treeDays as $v => $l)
                                <button type="button" class="cm-optpick__btn" data-cm-opt="{{ $v }}">
                                    <span class="cm-optpick__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg></span>
                                    {{ $l }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="tu_class_day" name="class_day" value="">
                        <div class="acc__input-error error-class_day"></div>
                    </div>

                    <div class="cm-field">
                        <label for="tu_virtual_room">Virtual Room</label>
                        <textarea id="tu_virtual_room" name="virtual_room" class="cm-input cm-textarea"></textarea>
                    </div>
                    <div class="cm-field cm-field--span2">
                        <label for="tu_note">Note</label>
                        <textarea id="tu_note" name="note" class="cm-input cm-textarea"></textarea>
                    </div>
                </div>

                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="storeTreeTutorial" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Save
                    </button>
                    {{-- `class_type` is fixed for a tutorial; the endpoint reads it. --}}
                    <input type="hidden" name="class_type" value="Tutorial">
                    <input type="hidden" name="theory_id" value="0">
                    <input type="hidden" name="tutorial_id" value="0">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ---------------------------------------------------------------- --}}
{{-- Sync tutorial                                                     --}}
{{-- ---------------------------------------------------------------- --}}
<div id="syncTutorialModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--sm">
        <form method="POST" action="#" id="syncTutorialForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span>Tutorial</span></div>
                        <h2 class="cm-modal__title cm-serif">Sync with a theory plan</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="cm-modal__body">
                    <div class="cm-field">
                        <label for="sync_plan_id">Theory plan <span>*</span></label>
                        <select id="sync_plan_id" name="sync_plan_id" class="cm-select"><option value="">Please Select</option></select>
                        <div class="acc__input-error error-sync_plan_id"></div>
                    </div>
                </div>
                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="syncTutorialBtn" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Sync
                    </button>
                    <input type="hidden" name="id" value="0">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ---------------------------------------------------------------- --}}
{{-- Assigned students                                                 --}}
{{-- ---------------------------------------------------------------- --}}
<div id="viewAssignedStudentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
        <div class="modal-content cm-modal">
            <div class="cm-modal__head">
                <div>
                    <div class="cm-modal__eyebrow"><span>Students</span></div>
                    <h2 class="cm-modal__title cm-serif">Assigned Student List</h2>
                </div>
                <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="cm-modal__body">
                <div class="cm-tabulator-wrap">
                    <div id="assignedStudentModalListTable" class="cm-tabulator"></div>
                </div>
            </div>
            <div class="cm-modal__foot">
                <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--save">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ---------------------------------------------------------------- --}}
{{-- Assign manager / audit user                                       --}}
{{-- ---------------------------------------------------------------- --}}
<div id="assignManagerOrCoOrdinatorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
        <form method="POST" action="#" id="assignManagerOrCoOrdinatorForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span data-cm-assign-eyebrow>Group</span></div>
                        <h2 class="cm-modal__title cm-serif" data-cm-assign-title>Assign Manager</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="cm-modal__body">
                    <div class="cm-field">
                        <label for="assigned_user_ids">People <span>*</span></label>
                        <select id="assigned_user_ids" name="assigned_user_ids[]" class="cm-select assigned_user_ids" multiple>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->full_name }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-assigned_user_ids"></div>
                    </div>
                </div>
                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="assignParticipantsBtn" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Save
                    </button>
                    {{-- `plan_participants.type` is written verbatim; the JS
                         swaps this for "Auditor" on the audit-user flow. --}}
                    <input type="hidden" name="type" value="Manager">
                    <input type="hidden" name="plan_ids" value="">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Group leader. Kept apart from the Manager / Audit User modal because it
     writes to `group_leaders` (group-scoped) rather than `plan_participants`
     (plan-scoped), and so it carries the group coordinates, not plan ids. --}}
<div id="assignGroupLeaderModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
        <form method="POST" action="#" id="assignGroupLeaderForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span data-cm-leader-eyebrow>Group</span></div>
                        <h2 class="cm-modal__title cm-serif">Assign Group Leader</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="cm-modal__body">
                    <div class="cm-field">
                        <label for="group_leader_ids">Group leader</label>
                        <select id="group_leader_ids" name="group_leader_ids[]" class="cm-select group_leader_ids" multiple>
                            @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->full_name }}</option>@endforeach
                        </select>
                        <div class="acc__input-error error-group_leader_ids"></div>
                        <p class="cm-field__hint">
                            A group leader sees this group, its class plans and its attendance on their
                            Group Leader dashboard. Saving with nobody selected clears the group's leaders.
                        </p>
                    </div>
                </div>
                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button type="submit" id="assignGroupLeaderBtn" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Save
                    </button>
                    <input type="hidden" name="yearid" value="">
                    <input type="hidden" name="termid" value="">
                    <input type="hidden" name="courseid" value="">
                    <input type="hidden" name="groupid" value="">
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Group leader assignment history. Read-only: the rows come from
     `group_leader_logs`, which nothing edits or deletes. --}}
<div id="groupLeaderLogModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
        <div class="modal-content cm-modal">
            <div class="cm-modal__head">
                <div>
                    <div class="cm-modal__eyebrow"><span data-cm-leaderlog-eyebrow>Group</span></div>
                    <h2 class="cm-modal__title cm-serif">Group Leader History</h2>
                </div>
                <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="cm-modal__body cm-modal__body--scroll" data-cm-leaderlog-body></div>
            <div class="cm-modal__foot">
                <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
