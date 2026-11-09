{{--
    Confirm / success / error dialogs.

    The centred form of the design: a tinted round icon, a title that names the
    outcome, one line of copy and full-width actions. No header bar and no
    close cross - the actions are the only way out, which is what a confirm
    should be. The record chip in the design is deliberately left out.

    The skin rides on the modal element because tw-starter moves a modal to
    <body> when it opens, so page-level scopes do not reach it.

    Hooks kept: the ids, `successModalTitle`/`successModalDesc`,
    `errorModalTitle`/`errorModalDesc`, `confModTitle`/`confModDesc`, and the
    `agreeWith` button with its `data-id` / `data-action`.
--}}
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body apd-dlg">
                    <span class="apd-dlg__icon apd-dlg__icon--green">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.2 2.4 2.4 4.6-4.9"/></svg>
                    </span>
                    <h3 class="apd-dlg__title successModalTitle"></h3>
                    <p class="apd-dlg__copy successModalDesc"></p>
                    <div class="apd-dlg__actions apd-dlg__actions--one">
                        <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--green">Done</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body apd-dlg">
                    <span class="apd-dlg__icon apd-dlg__icon--red">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7.6v5M12 16.1h.01"/></svg>
                    </span>
                    <h3 class="apd-dlg__title errorModalTitle"></h3>
                    <p class="apd-dlg__copy errorModalDesc"></p>
                    <div class="apd-dlg__actions apd-dlg__actions--one">
                        <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Error Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal apd-modal-skin apd-modal-skin--center" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body apd-dlg">
                    <span class="apd-dlg__icon apd-dlg__icon--red">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 7V5.5A1.5 1.5 0 0 1 11.5 4h1A1.5 1.5 0 0 1 14 5.5V7M6.5 7l.8 11.2A2 2 0 0 0 9.3 20h5.4a2 2 0 0 0 2-1.8L17.5 7"/><path d="M10.5 11v5M13.5 11v5"/></svg>
                    </span>
                    <h3 class="apd-dlg__title confModTitle">Are you sure?</h3>
                    <p class="apd-dlg__copy confModDesc"></p>
                    <div class="apd-dlg__actions">
                        <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith apd-btn apd-btn--red">Yes, remove</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
