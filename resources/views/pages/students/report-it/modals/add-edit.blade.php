{{-- Log dialogs for the all-issues screen. The Midone modal JS re-parents a
     modal to <body> when it opens, so the `rit-modal` scope rides on the modal
     element itself. --}}

<!-- BEGIN: Create Log Modal -->
<div id="addModal" class="modal rit-modal rit-modal--narrow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Create new log</h2>
                    <button type="button" data-tw-dismiss="modal" aria-label="Close" class="rit-modal__close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="add_description" class="rit-field__label">Description of issue <span class="rit-field__req">*</span></label>
                        <textarea id="add_description" name="description" class="rit-textarea rit-textarea--tall" placeholder="Please provide as much detail as possible"></textarea>
                        <div class="acc__input-error error-description rit-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="created_by" name="created_by" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="status" value="In Progress" />
                    <input type="hidden" id="report_it_all_id" name="report_it_all_id" value="{{ $reportItAll->id ?? '' }}">
                    <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">Cancel</button>
                    <button type="submit" id="save" class="rit-btn rit-btn--solid">
                        Save
                        <svg style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Create Log Modal -->

<!-- BEGIN: Update Log Modal -->
<div id="editModal" class="modal rit-modal rit-modal--narrow" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Update log</h2>
                    <span class="editLoading rit-loading">
                        <svg viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>Loading&hellip;
                    </span>
                    <button type="button" data-tw-dismiss="modal" aria-label="Close" class="rit-modal__close">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="edit_description" class="rit-field__label">Description of issue <span class="rit-field__req">*</span></label>
                        <textarea id="edit_description" name="description" class="rit-textarea rit-textarea--tall" placeholder="Please provide details of the issue you are experiencing"></textarea>
                        <div class="acc__input-error error-description rit-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="edit_updated_by" name="updated_by" value="{{ isset(auth('student')->user()->id) ? auth('student')->user()->id : auth()->user()->id }}">
                    <input type="hidden" id="edit_report_it_all_id" name="report_it_all_id" value="{{ $reportItAll->id ?? '' }}">
                    <input type="hidden" name="id" value="0" />
                    <button type="button" data-tw-dismiss="modal" class="rit-btn rit-btn--ghost">Cancel</button>
                    <button type="submit" id="update" class="rit-btn rit-btn--solid">
                        Update
                        <svg style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="rit-spinner">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Update Log Modal -->
