{{-- Assign policies (HR admin pages). Needs $assignEmployees, $roles, $hasRoles, $policyGroups, $levelMix.
     HR picks a role and the policy picker is filled with that role's policies; policies can
     still be added or removed, and what is left in the picker is exactly what is posted
     (policy_ids[]) together with the roles (role_ids[]).
     Each level card shows its exam's question pattern, and each policy option carries, per exam
     level, whether that exam can be drawn in full (data-exam), so the page JS can flag the
     policies whose exam at the chosen level is not ready yet. --}}
<div id="assignModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog pa-assign-modal-dialog">
        <form method="POST" action="#" id="assignForm" autocomplete="off">
            {{-- The policy picker is exactly what is assigned: an emptied picker is an error, never "the whole role". --}}
            <input type="hidden" name="policies_listed" value="1">
            <div class="modal-content ss-settings-modal ss-compact-settings-modal pa-assign-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Assign policies</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="modal-body ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="pa_assign_employees">Staff <span>*</span></label>
                        <select id="pa_assign_employees" name="employee_ids[]" multiple class="employee_ids" placeholder="Search staff by name...">
                            @foreach($assignEmployees as $assignEmployee)
                                @php $assignJob = (isset($assignEmployee->employment->employeeJobTitle->name) ? $assignEmployee->employment->employeeJobTitle->name : ''); @endphp
                                <option value="{{ $assignEmployee->id }}">{{ $assignEmployee->full_name }}{{ $assignJob !== '' ? ' — '.$assignJob : '' }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-employee_ids"></div>
                    </div>

                    @include('pages.hr.policy-assessment.partials.results-level-choice', ['levelChoiceId' => 'pa_assign_level'])

                    <div class="ss-modal-field">
                        <label id="pa_assign_role_label">Role</label>
                        @include('pages.hr.policy-assessment.partials.results-role-picker', ['rolePickerId' => 'pa_assign_role'])
                        <div class="acc__input-error error-role_ids"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="pa_assign_policies">Policies <small class="pa-assign-label-note">(filled from the role — add or remove if needed)</small></label>
                        <select id="pa_assign_policies" name="policy_ids[]" multiple class="policy_ids" placeholder="Search policies...">
                            @include('pages.hr.policy-assessment.partials.results-policy-options')
                        </select>
                        <small class="pa-assign-count" data-policy-count hidden></small>
                        <div class="acc__input-error error-policy_ids"></div>
                        <p class="pa-assign-level-note" data-level-note hidden>
                            <i data-lucide="alert-triangle"></i>
                            <span></span>
                        </p>
                    </div>

                    <div class="ss-modal-grid">
                        <div class="ss-modal-field">
                            <label for="pa_assign_due">Due date <span>*</span></label>
                            <input id="pa_assign_due" type="text" name="due_date" class="ss-modal-input datepicker due_date" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true" autocomplete="off">
                            <div class="acc__input-error error-due_date"></div>
                        </div>
                        <div class="ss-modal-field pa-assign-email-field">
                            <label>Notification</label>
                            <label class="ss-status-toggle" for="pa_assign_email">
                                <input id="pa_assign_email" name="send_email" value="1" type="checkbox" checked autocomplete="off">
                                <span class="ss-status-toggle__control">
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                </span>
                                <span class="ss-status-toggle__copy">
                                    <strong>Email the staff member(s)</strong>
                                    <small>One email each, listing new policies</small>
                                </span>
                            </label>
                        </div>
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="pa_assign_note">Note</label>
                            <textarea id="pa_assign_note" name="note" rows="2" maxlength="1000" class="ss-modal-input ss-modal-textarea note" placeholder="Optional, for HR only"></textarea>
                            <div class="acc__input-error error-note"></div>
                        </div>
                    </div>
                    <p class="pa-assign-hint pa-assign-hint--foot">
                        <i data-lucide="info"></i>
                        The policies listed above are what gets assigned. Nobody is given the same policy twice at the same level. If someone already has it at this level, a new due date replaces the old one unless they have already met the target. Another level is a separate exam.
                    </p>
                </div>
                <div class="modal-footer ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="assignSave" class="ss-btn ss-btn--primary">
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                        <i data-lucide="send"></i>
                        Assign
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
