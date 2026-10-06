{{-- Success / warning / confirm modals shared by the Question Bank pages (categories, policies, questions). --}}
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

<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal ss-success-modal--warning">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="alert-octagon" class="ss-success-modal__icon"></i>
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
