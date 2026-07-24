<!-- BEGIN: Add Company Modal -->
<div id="addWPCompanyModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
        <form method="POST" action="#" id="addWPCompanyForm" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Company</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="company_add_name">Company Name <span>*</span></label>
                            <input id="company_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter company name">
                            <div class="acc__input-error error-name"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_email">Email</label>
                            <input id="company_add_email" type="email" name="email" class="ss-modal-input email" placeholder="name@company.co.uk">
                            <div class="acc__input-error error-email"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_phone">Phone</label>
                            <input id="company_add_phone" type="text" name="phone" class="ss-modal-input phone" placeholder="Enter phone number">
                            <div class="acc__input-error error-phone"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_fax">FAX</label>
                            <input id="company_add_fax" type="text" name="fax" class="ss-modal-input fax" placeholder="Enter fax number">
                            <div class="acc__input-error error-fax"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_website">Website</label>
                            <input id="company_add_website" type="text" name="website" class="ss-modal-input website" placeholder="https://example.co.uk">
                            <div class="acc__input-error error-website"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_address">Address</label>
                            <textarea id="company_add_address" name="address" rows="3" class="ss-modal-input ss-modal-textarea address" placeholder="Enter address"></textarea>
                            <div class="acc__input-error error-address"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="company_add_other_info">Other Info</label>
                            <textarea id="company_add_other_info" name="other_info" rows="3" class="ss-modal-input ss-modal-textarea other_info" placeholder="Anything else worth recording"></textarea>
                            <div class="acc__input-error error-other_info"></div>
                        </div>

                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="company_add_active">Active Status</label>
                            <label class="ss-status-toggle" for="company_add_active">
                                <input id="company_add_active" name="active" value="1" type="checkbox" checked autocomplete="off">
                                <span class="ss-status-toggle__control">
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                </span>
                                <span class="ss-status-toggle__copy">
                                    <strong>Active</strong>
                                    <small>Available when placing students</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="saveCompany" class="ss-btn ss-btn--primary">
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
<!-- END: Add Company Modal -->

<!-- BEGIN: Edit Company Modal -->
<div id="editWPCompanyModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
        <form method="POST" action="#" id="editWPCompanyForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Company</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="edit_name">Company Name <span>*</span></label>
                            <input id="edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter company name">
                            <div class="acc__input-error error-name"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_email">Email</label>
                            <input id="edit_email" type="email" name="email" class="ss-modal-input email" placeholder="name@company.co.uk">
                            <div class="acc__input-error error-email"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_phone">Phone</label>
                            <input id="edit_phone" type="text" name="phone" class="ss-modal-input phone" placeholder="Enter phone number">
                            <div class="acc__input-error error-phone"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_fax">FAX</label>
                            <input id="edit_fax" type="text" name="fax" class="ss-modal-input fax" placeholder="Enter fax number">
                            <div class="acc__input-error error-fax"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_website">Website</label>
                            <input id="edit_website" type="text" name="website" class="ss-modal-input website" placeholder="https://example.co.uk">
                            <div class="acc__input-error error-website"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_address">Address</label>
                            <textarea id="edit_address" name="address" rows="3" class="ss-modal-input ss-modal-textarea address" placeholder="Enter address"></textarea>
                            <div class="acc__input-error error-address"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="edit_other_info">Other Info</label>
                            <textarea id="edit_other_info" name="other_info" rows="3" class="ss-modal-input ss-modal-textarea other_info" placeholder="Anything else worth recording"></textarea>
                            <div class="acc__input-error error-other_info"></div>
                        </div>

                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="edit_active">Active Status</label>
                            <label class="ss-status-toggle" for="edit_active">
                                <input id="edit_active" name="active" value="1" type="checkbox" autocomplete="off">
                                <span class="ss-status-toggle__control">
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                </span>
                                <span class="ss-status-toggle__copy">
                                    <strong>Inactive</strong>
                                    <small>Not available when placing students</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateCompany" class="ss-btn ss-btn--primary">
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
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Company Modal -->

<!-- BEGIN: Add Supervisor Modal -->
<div id="addCompanySupervisorModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addCompanySupervisorForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Supervisor</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="supervisor_add_name">Full Name <span>*</span></label>
                        <input id="supervisor_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter full name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_add_email">Email</label>
                        <input id="supervisor_add_email" type="email" name="email" class="ss-modal-input email" placeholder="name@company.co.uk">
                        <div class="acc__input-error error-email"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_add_phone">Phone</label>
                        <input id="supervisor_add_phone" type="text" name="phone" class="ss-modal-input phone" placeholder="Enter phone number">
                        <div class="acc__input-error error-phone"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_add_other_info">Other Info</label>
                        <textarea id="supervisor_add_other_info" name="other_info" rows="3" class="ss-modal-input ss-modal-textarea other_info" placeholder="Job title, notes, availability"></textarea>
                        <div class="acc__input-error error-other_info"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="addSupervisor" class="ss-btn ss-btn--primary">
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
                    <input type="hidden" name="company_id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Supervisor Modal -->

<!-- BEGIN: Edit Supervisor Modal -->
<div id="editCompanySupervisorModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="editCompanySupervisorForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Supervisor</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="supervisor_edit_name">Full Name <span>*</span></label>
                        <input id="supervisor_edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter full name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_edit_email">Email</label>
                        <input id="supervisor_edit_email" type="email" name="email" class="ss-modal-input email" placeholder="name@company.co.uk">
                        <div class="acc__input-error error-email"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_edit_phone">Phone</label>
                        <input id="supervisor_edit_phone" type="text" name="phone" class="ss-modal-input phone" placeholder="Enter phone number">
                        <div class="acc__input-error error-phone"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="supervisor_edit_other_info">Other Info</label>
                        <textarea id="supervisor_edit_other_info" name="other_info" rows="3" class="ss-modal-input ss-modal-textarea other_info" placeholder="Job title, notes, availability"></textarea>
                        <div class="acc__input-error error-other_info"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="editSupervisor" class="ss-btn ss-btn--primary">
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
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Supervisor Modal -->

<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                    <div class="successModalTitle">Success</div>
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
