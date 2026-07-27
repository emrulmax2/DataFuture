<!-- BEGIN: Add Workplacement Setting Modal -->
<div id="addWpSettingModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addWpSettingForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Workplacement Setting</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="wp_setting_add_name">Setting Name <span>*</span></label>
                        <input id="wp_setting_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter setting name">
                        <div class="acc__input-error error-name"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="wpSettingInsertBtn" class="ss-btn ss-btn--primary">
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
                        <i data-lucide="check"></i>
                        Save
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Workplacement Setting Modal -->

<!-- BEGIN: Edit Workplacement Setting Modal -->
<div id="editWpSettingModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="editWpSettingForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Workplacement Setting</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="wp_setting_edit_name">Setting Name <span>*</span></label>
                        <input id="wp_setting_edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter setting name">
                        <div class="acc__input-error error-name"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateWpSettingBtn" class="ss-btn ss-btn--primary">
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
                        <i data-lucide="check"></i>
                        Update
                    </button>
                    <input type="hidden" name="setting_id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Workplacement Setting Modal -->

<!-- BEGIN: Add Workplacement Setting Type Modal -->
<div id="addWpSettingTypeModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addWpSettingTypeForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Workplacement Setting Type</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="wp_setting_type_add">Setting Type <span>*</span></label>
                        <input id="wp_setting_type_add" type="text" name="type" class="ss-modal-input type" placeholder="Enter setting type">
                        <div class="acc__input-error error-type"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="wpSettingTypeInsertBtn" class="ss-btn ss-btn--primary">
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
                        <i data-lucide="check"></i>
                        Save
                    </button>
                    <input type="hidden" name="workplacement_setting_id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Workplacement Setting Type Modal -->

<!-- BEGIN: Edit Workplacement Setting Type Modal -->
<div id="editWpSettingTypeModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="editWpSettingTypeForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Workplacement Setting Type</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="wp_setting_type_edit">Setting Type <span>*</span></label>
                        <input id="wp_setting_type_edit" type="text" name="type" class="ss-modal-input type" placeholder="Enter setting type">
                        <div class="acc__input-error error-type"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateWpSettingTypeBtn" class="ss-btn ss-btn--primary">
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
                        <i data-lucide="check"></i>
                        Update
                    </button>
                    <input type="hidden" name="setting_type_id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Workplacement Setting Type Modal -->

<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                    <div class="successModalTitle"></div>
                    <p class="successModalDesc"></p>
                </div>
                <div class="ss-success-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-confirm-modal__dialog">
        <div class="modal-content ss-confirm-modal">
            <div class="ss-confirm-modal__hero">
                <span><i data-lucide="alert-triangle"></i></span>
                <h2 class="confModTitle">Are you sure?</h2>
            </div>
            <div class="ss-confirm-modal__body">
                <p class="confModDesc"></p>
            </div>
            <div class="ss-confirm-modal__footer">
                <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                    <i data-lucide="x"></i>
                    No, Cancel
                </button>
                <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                    <i data-lucide="check"></i>
                    Yes, I agree
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
