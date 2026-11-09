{{--
    Outcome dialogs shared by the admission portal pages: #confirmModal (its
    Yes button carries the page's own data attributes, passed in as
    $adpConfirmAttrs; the page script may repaint tone, icon and copy),
    #successModal, #errorModal and #warningModal. Buttons carry adp-btn, never
    .btn, so admissionDecorateModalButtons() leaves them alone.
--}}
@php
    $adpConfirmAttrs = $adpConfirmAttrs ?? 'data-id="0" data-action="none"';
@endphp
<div id="confirmModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--danger">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg></span>
            <h3 class="confModTitle">Are you sure?</h3>
            <p class="confModDesc"></p>
            <div class="adp-dlg-item" data-adp-confirm-item hidden></div>
            <div class="adp-dialog__actions">
                <button type="button" data-tw-dismiss="modal" class="disAgreeWith adp-btn adp-btn--ghost">No, cancel</button>
                <button type="button" {!! $adpConfirmAttrs !!} class="agreeWith adp-btn adp-btn--danger">Yes, I agree</button>
            </div>
        </div>
    </div>
</div>

<div id="successModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--success">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h3 class="successModalTitle"></h3>
            <p class="successModalDesc"></p>
            <div class="adp-dialog__actions adp-dialog__actions--single">
                <button type="button" data-action="DISMISS" class="successCloser adp-btn adp-btn--success">Done</button>
            </div>
        </div>
    </div>
</div>

<div id="errorModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--danger">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 2h8.2L22 7.9v8.2L16.1 22H7.9L2 16.1V7.9z"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></span>
            <h3 class="errorModalTitle"></h3>
            <p class="errorModalDesc"></p>
            <div class="adp-dialog__actions adp-dialog__actions--single">
                <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--primary">Done</button>
            </div>
        </div>
    </div>
</div>

<div id="warningModal" class="modal adp-modal adp-modal--dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content adp-dialog adp-dialog--warn">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg></span>
            <h3 class="warningModalTitle"></h3>
            <p class="warningModalDesc"></p>
            <div class="adp-dialog__actions adp-dialog__actions--single">
                <button type="button" data-tw-dismiss="modal" data-action="NONE" class="warningCloser adp-btn adp-btn--primary">Ok</button>
            </div>
        </div>
    </div>
</div>
