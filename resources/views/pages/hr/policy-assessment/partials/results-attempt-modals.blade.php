{{-- Attempts list + attempt review (HR admin pages). Filled by the page JS.
     Pass ['withAttemptsList' => false] to leave out the attempts list modal. --}}
@if(!isset($withAttemptsList) || $withAttemptsList)
<div id="attemptsModal" class="modal ss-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog pa-wide-modal-dialog">
        <div class="modal-content ss-settings-modal pa-detail-modal">
            <div class="ss-settings-modal__header">
                <div>
                    <span></span>
                    <div class="pa-detail-modal__heading">
                        <h2 class="attemptsModalTitle">Attempts</h2>
                        <small class="attemptsModalSubtitle"></small>
                    </div>
                </div>
                <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body ss-settings-modal__body">
                <div class="attemptsModalBody pa-detail-modal__body"></div>
            </div>
            <div class="modal-footer ss-settings-modal__footer">
                <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                    <i data-lucide="x"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<div id="attemptReviewModal" class="modal ss-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog pa-wide-modal-dialog">
        <div class="modal-content ss-settings-modal pa-detail-modal">
            <div class="ss-settings-modal__header">
                <div>
                    <span></span>
                    <div class="pa-detail-modal__heading">
                        <h2 class="attemptReviewTitle">Attempt review</h2>
                        <small class="attemptReviewSubtitle"></small>
                    </div>
                </div>
                <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body ss-settings-modal__body">
                <div class="attemptReviewBody pa-detail-modal__body"></div>
            </div>
            <div class="modal-footer ss-settings-modal__footer">
                <button type="button" class="ss-btn ss-btn--light attemptReviewBack">
                    <i data-lucide="arrow-left"></i>
                    Back
                </button>
                <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
