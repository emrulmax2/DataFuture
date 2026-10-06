{{-- Category add/edit fields. Needs $prefix (add|edit) so ids stay unique across the two modals. --}}
<div class="ss-modal-field">
    <label for="{{ $prefix }}_category_name">Name <span>*</span></label>
    <input id="{{ $prefix }}_category_name" type="text" name="name" maxlength="191" class="ss-modal-input name" placeholder="e.g. Corporate Policies">
    <div class="acc__input-error error-name"></div>
</div>

<div class="ss-modal-field">
    <label for="{{ $prefix }}_category_description">Description</label>
    <textarea id="{{ $prefix }}_category_description" name="description" rows="3" maxlength="2000" class="ss-modal-input ss-modal-textarea description" placeholder="Optional. A line about what this group covers."></textarea>
    <div class="acc__input-error error-description"></div>
</div>

<div class="ss-modal-grid">
    <div class="ss-modal-field">
        <label for="{{ $prefix }}_category_sort">Sort order</label>
        <input id="{{ $prefix }}_category_sort" type="number" min="0" step="1" name="sort_order" class="ss-modal-input sort_order" placeholder="{{ $prefix === 'add' ? 'Next in list' : '0' }}">
        <div class="acc__input-error error-sort_order"></div>
    </div>
    <div class="ss-modal-field pa-modal-toggle-field">
        <label class="ss-status-toggle pa-active-toggle" for="{{ $prefix }}_category_active">
            <input id="{{ $prefix }}_category_active" name="is_active" value="1" type="checkbox" {{ $prefix === 'add' ? 'checked' : '' }} autocomplete="off">
            <span class="ss-status-toggle__control">
                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
            </span>
            <span class="ss-status-toggle__copy">
                <strong data-on="Active" data-off="Inactive">Active</strong>
                <small data-on="Shown when assigning policies" data-off="Hidden when assigning policies">Shown when assigning policies</small>
            </span>
        </label>
        <div class="acc__input-error error-is_active"></div>
    </div>
</div>
