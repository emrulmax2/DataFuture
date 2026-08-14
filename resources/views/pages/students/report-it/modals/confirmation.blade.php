{{-- The delete / restore confirmation. The page JS retitles it and stamps the
     record id + action onto `.agreeWith` before showing it. --}}
<div id="confirmModal" class="modal rit-modal rit-modal--confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="rit-notice">
                <div class="rit-notice__icon rit-notice__icon--danger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 7h15M9.5 7V5h5v2M6.5 7l1 13h9l1-13"></path></svg>
                </div>
                <h2 class="rit-notice__title confModTitle">Are you sure?</h2>
                <p class="rit-notice__desc confModDesc"></p>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">No, cancel</button>
                <button type="button" data-id="0" data-action="none" class="agreeWith rit-btn rit-btn--danger">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

<div id="confirmSecondaryModal" class="modal rit-modal rit-modal--confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="rit-notice">
                <div class="rit-notice__icon rit-notice__icon--gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.6"></circle><path d="M12 8.2v4.6M12 15.6v.1"></path></svg>
                </div>
                <h2 class="rit-notice__title confModTitle">Are you sure?</h2>
                <p class="rit-notice__desc confModDesc"></p>
            </div>
            <div class="modal-footer">
                <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">No, cancel</button>
                <button type="button" data-id="0" data-action="none" class="agreeWith rit-btn rit-btn--solid">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>
