<div id="bankholidayAddModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="bankholidayAddForm" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Bank Holiday</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field">
                            <label for="add_bankholiday_start_date">Start Date <span>*</span></label>
                            <input id="add_bankholiday_start_date" name="start_date" type="text" class="ss-modal-input datepicker start_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="add_bankholiday_end_date">End Date <span>*</span></label>
                            <input id="add_bankholiday_end_date" name="end_date" type="text" class="ss-modal-input datepicker end_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="add_bankholiday_duration">Duration <span>*</span></label>
                            <input id="add_bankholiday_duration" type="number" name="duration" class="ss-modal-input duration" min="1" placeholder="1">
                            <div class="acc__input-error error-duration"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="add_bankholiday_type">Type <span>*</span></label>
                            <label class="ss-modal-select" for="add_bankholiday_type">
                                <select id="add_bankholiday_type" name="type" class="type">
                                    <option value="">Please Select</option>
                                    <option value="Bank Holiday">Bank Holiday</option>
                                    <option value="Public Holiday">Public Holiday</option>
                                    <option value="College Closure">College Closure</option>
                                </select>
                                <i data-lucide="chevron-down"></i>
                            </label>
                            <div class="acc__input-error error-type"></div>
                        </div>
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="add_bankholiday_title">Title <span>*</span></label>
                            <input id="add_bankholiday_title" type="text" name="title" class="ss-modal-input title" placeholder="Spring bank holiday">
                            <div class="acc__input-error error-title"></div>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="saveBankholiday" class="ss-btn ss-btn--primary">
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
                    <input type="hidden" name="academic_year_id" value="{{ $academicyear->id }}">
                </div>
            </div>
        </form>
    </div>
</div>

<div id="bankholidayEditModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="bankholidayEditForm" enctype="multipart/form-data" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Bank Holiday</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field">
                            <label for="edit_bankholiday_start_date">Start Date <span>*</span></label>
                            <input id="edit_bankholiday_start_date" name="start_date" type="text" class="ss-modal-input datepicker start_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="edit_bankholiday_end_date">End Date <span>*</span></label>
                            <input id="edit_bankholiday_end_date" name="end_date" type="text" class="ss-modal-input datepicker end_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="edit_bankholiday_duration">Duration <span>*</span></label>
                            <input id="edit_bankholiday_duration" type="number" name="duration" class="ss-modal-input duration" min="1" placeholder="1">
                            <div class="acc__input-error error-duration"></div>
                        </div>
                        <div class="ss-modal-field">
                            <label for="edit_bankholiday_type">Type <span>*</span></label>
                            <label class="ss-modal-select" for="edit_bankholiday_type">
                                <select id="edit_bankholiday_type" name="type" class="type">
                                    <option value="">Please Select</option>
                                    <option value="Bank Holiday">Bank Holiday</option>
                                    <option value="Public Holiday">Public Holiday</option>
                                    <option value="College Closure">College Closure</option>
                                </select>
                                <i data-lucide="chevron-down"></i>
                            </label>
                            <div class="acc__input-error error-type"></div>
                        </div>
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="edit_bankholiday_title">Title <span>*</span></label>
                            <input id="edit_bankholiday_title" type="text" name="title" class="ss-modal-input title" placeholder="Spring bank holiday">
                            <div class="acc__input-error error-title"></div>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateBankholiday" class="ss-btn ss-btn--primary">
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
                    <input type="hidden" name="id" value="0">
                </div>
            </div>
        </form>
    </div>
</div>

<div id="bankholidayImportModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--import">
        <div class="modal-content ss-settings-modal">
            <div class="ss-settings-modal__header">
                <div>
                    <span></span>
                    <h2>Import Holiday</h2>
                </div>
                <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="ss-settings-modal__body">
                <form method="post" action="{{ route('bankholidays.import') }}" class="dropzone ss-dropzone" id="bankholidayImportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input name="import_holiday_file" type="file">
                    </div>
                    <div class="dz-message" data-dz-message>
                        <i data-lucide="upload-cloud"></i>
                        <strong>Drop files here or click to upload.</strong>
                    </div>
                    <input type="hidden" name="academic_year_id" value="{{ $academicyear->id }}">
                </form>
            </div>
            <div class="ss-settings-modal__footer ss-settings-modal__footer--split">
                <a href="{{ route('bankholidays.export') }}" id="downloadSample" class="ss-btn ss-btn--success">
                    <i data-lucide="download"></i>
                    Sample XL
                </a>
                <div class="ss-modal-footer-actions">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                        Cancel
                    </button>
                    <button id="saveImportholiday" type="button" class="ss-btn ss-btn--primary">
                        <i data-lucide="upload"></i>
                        Upload
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                    <div class="successModalTitle">Congratulations!</div>
                    <p class="successModalDesc">Holidays data successfully uploaded.</p>
                </div>
                <div class="ss-success-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="bankholidayConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-confirm-modal__dialog">
        <div class="modal-content ss-confirm-modal">
            <div class="ss-confirm-modal__hero">
                <span><i data-lucide="alert-triangle"></i></span>
                <h2 class="bankholidayConfModTitle">Are you sure?</h2>
            </div>
            <div class="ss-confirm-modal__body">
                <p class="bankholidayConfModDesc"></p>
            </div>
            <div class="ss-confirm-modal__footer">
                <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                    <i data-lucide="x"></i>
                    No, Cancel
                </button>
                <button type="button" data-id="0" data-action="none" class="bankholidayAgreeWith ss-btn ss-btn--danger">
                    <i data-lucide="check"></i>
                    Yes, I agree
                </button>
            </div>
        </div>
    </div>
</div>
