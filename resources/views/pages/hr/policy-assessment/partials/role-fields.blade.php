{{-- Role add/edit fields. Needs $prefix (add|edit) so ids stay unique across the two modals,
     plus $policyGroups and $policyTotal for the policy tick list. --}}
<div class="pa-role-fields">
    <div class="ss-modal-field pa-role-fields__name">
        <label for="{{ $prefix }}_role_name">Role name <span>*</span></label>
        <input id="{{ $prefix }}_role_name" type="text" name="name" maxlength="191" class="ss-modal-input name" placeholder="e.g. Lecturer">
        <div class="acc__input-error error-name"></div>
    </div>

    <div class="ss-modal-field pa-role-fields__sort">
        <label for="{{ $prefix }}_role_sort">Sort order</label>
        <input id="{{ $prefix }}_role_sort" type="number" min="0" step="1" name="sort_order" class="ss-modal-input sort_order" placeholder="{{ $prefix === 'add' ? 'Next in list' : '0' }}">
        <div class="acc__input-error error-sort_order"></div>
    </div>

    <div class="ss-modal-field pa-role-fields__description">
        <label for="{{ $prefix }}_role_description">Description</label>
        <textarea id="{{ $prefix }}_role_description" name="description" rows="2" maxlength="2000" class="ss-modal-input ss-modal-textarea description" placeholder="Optional. Who this role covers."></textarea>
        <div class="acc__input-error error-description"></div>
    </div>

    <div class="ss-modal-field pa-modal-toggle-field pa-role-fields__active">
        <label class="ss-status-toggle pa-active-toggle" for="{{ $prefix }}_role_active">
            <input id="{{ $prefix }}_role_active" name="is_active" value="1" type="checkbox" {{ $prefix === 'add' ? 'checked' : '' }} autocomplete="off">
            <span class="ss-status-toggle__control">
                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
            </span>
            <span class="ss-status-toggle__copy">
                <strong data-on="Active" data-off="Inactive">Active</strong>
                <small data-on="Can be picked when assigning" data-off="Hidden when assigning">Can be picked when assigning</small>
            </span>
        </label>
        <div class="acc__input-error error-is_active"></div>
    </div>
</div>

@include('pages.hr.policy-assessment.partials.role-policy-picker', ['prefix' => $prefix])
