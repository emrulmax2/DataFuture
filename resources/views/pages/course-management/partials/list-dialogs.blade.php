{{--
    The success and confirm dialogs shared by every Course Management list.
    Copy is set at runtime by `js/course-table-kit.js` (showSuccess /
    openConfirm), so the ids and hooks here are fixed.
--}}

<!-- BEGIN: Success Modal -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-confirm__dialog">
        <div class="modal-content cm-success">
            <div class="cm-success__body">
                {{-- Both glyphs ship inline and are toggled by `showSuccess`,
                     so the same dialog can report a failure without a green
                     tick. Swapping a lucide placeholder would only work once. --}}
                <span class="cm-success__icon">
                    <svg data-cm-notice-icon="ok" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                    <svg data-cm-notice-icon="warn" style="display:none;" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                </span>
                <div class="cm-success__title cm-serif successModalTitle"></div>
                <p class="cm-success__text successModalDesc"></p>
            </div>
            <div class="cm-success__foot">
                <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--save">Done</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal -->

<!-- BEGIN: Confirm Modal -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-confirm__dialog">
        <div class="modal-content cm-confirm">
            <div class="cm-confirm__body">
                {{-- Both glyphs ship inline and are toggled by JS; a lucide
                     placeholder could only be swapped once, because createIcons
                     replaces the <i> with an <svg>. --}}
                <span class="cm-confirm__icon">
                    <svg data-cm-confirm-icon="DELETE" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                    <svg data-cm-confirm-icon="RESTORE" style="display:none;" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                </span>
                <div>
                    <div class="cm-confirm__title cm-serif confModTitle">Are you sure?</div>
                    <p class="cm-confirm__text confModDesc"></p>
                </div>
            </div>
            <div class="cm-confirm__foot">
                <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--keep">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                    Keep it
                </button>
                <button type="button" data-id="0" data-action="none" class="agreeWith cm-btn cm-btn--danger">
                    <svg data-cm-confirm-glyph="DELETE" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path></svg>
                    <svg data-cm-confirm-glyph="RESTORE" style="display:none;" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                    <span data-cm-confirm-label>Delete</span>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Confirm Modal -->
