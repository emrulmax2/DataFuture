<!-- BEGIN: Add Work Placement Modal -->
<div id="workplacementAddModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
        <form method="POST" action="#" id="addWorkPlacementForm" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Work Placement</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="wp_add_name">Name <span>*</span></label>
                            <input id="wp_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                            <div class="acc__input-error error-name"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_add_hours">Hours <span>*</span></label>
                            <input id="wp_add_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                            <div class="acc__input-error error-hours"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_add_course_id">Course <span>*</span></label>
                            <select id="wp_add_course_id" name="course_id" data-placeholder="Select Course" class="tom-select course_id">
                                <option value="">Select Course</option>
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-course_id"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_add_start_date">Start Date <span>*</span></label>
                            <input id="wp_add_start_date" type="text" name="start_date" class="ss-modal-input datepicker start_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_add_end_date">End Date</label>
                            <input id="wp_add_end_date" type="text" name="end_date" class="ss-modal-input datepicker end_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date"></div>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="insertWorkPlacement" class="ss-btn ss-btn--primary">
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
                        Add Work Placement
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Work Placement Modal -->

<!-- BEGIN: Edit Work Placement Modal -->
<div id="workplacementEditModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
        <form method="POST" action="#" id="editWorkPlacementForm" data-form-id="" autocomplete="off">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Work Placement</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-grid">
                        <div class="ss-modal-field ss-modal-field--full">
                            <label for="wp_edit_name">Name <span>*</span></label>
                            <input id="wp_edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                            <div class="acc__input-error error-name"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_edit_hours">Hours <span>*</span></label>
                            <input id="wp_edit_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                            <div class="acc__input-error error-hours"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_edit_course_id">Course <span>*</span></label>
                            <label class="ss-modal-select" for="wp_edit_course_id">
                                <select id="wp_edit_course_id" name="course_id" class="course_id">
                                    @foreach($courses as $crs)
                                        <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                    @endforeach
                                </select>
                                <i data-lucide="chevron-down"></i>
                            </label>
                            <div class="acc__input-error error-course_id"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_edit_start_date">Start Date <span>*</span></label>
                            <input id="wp_edit_start_date" type="text" name="start_date" class="ss-modal-input datepicker start_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date"></div>
                        </div>

                        <div class="ss-modal-field">
                            <label for="wp_edit_end_date">End Date</label>
                            <input id="wp_edit_end_date" type="text" name="end_date" class="ss-modal-input datepicker end_date" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date"></div>
                        </div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateWorkPlacement" class="ss-btn ss-btn--primary">
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
                        Update Work Placement
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Work Placement Modal -->

<!-- BEGIN: Add Level Hours Modal -->
<div id="addLevelHoursModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addLevelHoursForm" autocomplete="off">
            <input type="hidden" name="workplacement_id" id="add_level_workplacement_id" value="">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Level Hours</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="level_add_name">Name <span>*</span></label>
                        <input id="level_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="level_add_hours">Hours <span>*</span></label>
                        <input id="level_add_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                        <div class="acc__input-error error-hours"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="insertLevelHours" class="ss-btn ss-btn--primary">
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
                        Add Level Hours
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Level Hours Modal -->

<!-- BEGIN: Edit Level Hours Modal -->
<div id="levelHoursEditModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="editLevelHoursForm" autocomplete="off">
            <input type="hidden" name="workplacement_id" id="edit_level_workplacement_id" value="">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Level Hours</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="level_edit_name">Name <span>*</span></label>
                        <input id="level_edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="level_edit_hours">Hours <span>*</span></label>
                        <input id="level_edit_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                        <div class="acc__input-error error-hours"></div>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateLevelHours" class="ss-btn ss-btn--primary">
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
                        Update Level Hours
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Level Hours Modal -->

<!-- BEGIN: Add Learning Hours Modal -->
<div id="addLearningHoursModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="addLearningHoursForm" autocomplete="off">
            <input type="hidden" name="level_hours_id" id="add_learning_level_hours_id" value="">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Add Learning Hours</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="learning_add_name">Name <span>*</span></label>
                        <input id="learning_add_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="learning_add_hours">Hours <span>*</span></label>
                        <input id="learning_add_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                        <div class="acc__input-error error-hours"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="module_required_add">Module Required?</label>
                        <label class="ss-status-toggle" for="module_required_add">
                            <input id="module_required_add" name="module_required" value="1" type="checkbox" autocomplete="off">
                            <span class="ss-status-toggle__control">
                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                            </span>
                            <span class="ss-status-toggle__copy">
                                <strong>Not required</strong>
                                <small>These hours are logged without a linked module</small>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="insertLearningHours" class="ss-btn ss-btn--primary">
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
                        Add Learning Hours
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Learning Hours Modal -->

<!-- BEGIN: Edit Learning Hours Modal -->
<div id="editLearningHoursModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog ss-settings-modal__dialog">
        <form method="POST" action="#" id="editLearningHoursForm" autocomplete="off">
            <input type="hidden" name="level_hours_id" id="edit_learning_level_hours_id" value="">
            <div class="modal-content ss-settings-modal ss-workplacement-modal">
                <div class="ss-settings-modal__header">
                    <div>
                        <span></span>
                        <h2>Edit Learning Hours</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="ss-settings-modal__body">
                    <div class="ss-modal-field">
                        <label for="learning_edit_name">Name <span>*</span></label>
                        <input id="learning_edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Enter name">
                        <div class="acc__input-error error-name"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="learning_edit_hours">Hours <span>*</span></label>
                        <input id="learning_edit_hours" step="any" type="number" name="hours" class="ss-modal-input hours" placeholder="Enter hours">
                        <div class="acc__input-error error-hours"></div>
                    </div>

                    <div class="ss-modal-field">
                        <label for="module_required_edit">Module Required?</label>
                        <label class="ss-status-toggle" for="module_required_edit">
                            <input id="module_required_edit" name="module_required" value="1" type="checkbox" autocomplete="off">
                            <span class="ss-status-toggle__control">
                                <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                            </span>
                            <span class="ss-status-toggle__copy">
                                <strong>Not required</strong>
                                <small>These hours are logged without a linked module</small>
                            </span>
                        </label>
                    </div>
                </div>
                <div class="ss-settings-modal__footer">
                    <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="submit" id="updateLearningHours" class="ss-btn ss-btn--primary">
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
                        Update Learning Hours
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Learning Hours Modal -->

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
                    <button type="button" data-action="NONE" data-tw-dismiss="modal" class="successCloser ss-btn ss-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ss-success-modal ss-success-modal--warning">
            <div class="modal-body p-0">
                <div class="ss-success-modal__body">
                    <i data-lucide="alert-octagon" class="ss-success-modal__icon"></i>
                    <div class="warningModalTitle"></div>
                    <p class="warningModalDesc"></p>
                </div>
                <div class="ss-success-modal__footer">
                    <button type="button" data-action="DISMISS" data-tw-dismiss="modal" class="warningCloser ss-btn ss-btn--primary">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->

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
                <button type="button" data-tw-dismiss="modal" class="disAgreeWith ss-btn ss-btn--light">
                    <i data-lucide="x"></i>
                    No, Cancel
                </button>
                <button type="button" data-id="0" data-recordid="0" data-status="none" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                    <i data-lucide="check"></i>
                    Yes, I agree
                </button>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
