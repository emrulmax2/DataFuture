<!-- BEGIN: Add Module Modal -->
<div id="cretionAvailabilityAddModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="cretionAvailabilityAddForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Availability</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="admission_date" class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input id="admission_date" type="text" name="admission_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-admission_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="admission_end_date" class="form-label">Admission End Date <span class="text-danger">*</span></label>
                            <input id="admission_end_date" type="text" name="admission_end_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-admission_end_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="course_start_date" class="form-label">Course Start Date <span class="text-danger">*</span></label>
                            <input id="course_start_date" type="text" name="course_start_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-course_start_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="course_end_date" class="form-label">Course End Date <span class="text-danger">*</span></label>
                            <input id="course_end_date" type="text" name="course_end_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-course_end_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="last_joinning_date" class="form-label">Last Joinning Date <span class="text-danger">*</span></label>
                            <input id="last_joinning_date" type="text" name="last_joinning_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-last_joinning_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="UK">UK</option>
                                <option value="OVERSEAS">OVERSEAS</option>
                                <option value="BOTH">BOTH</option>
                            </select>
                            <div class="acc__input-error error-type text-danger mt-2"></div>
                        </div>     
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="crationAvailabilitySave" class="btn btn-primary w-auto">
                            Save
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
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
                        <input type="hidden" name="course_creation_id" value="{{ $creation->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Module Modal -->

    <!-- BEGIN: Edit Module Modal -->
    <div id="cretionAvailabilityEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="cretionAvailabilityEditForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Availability</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                    <div>
                            <label for="admission_date" class="form-label">Admission Date <span class="text-danger">*</span></label>
                            <input id="admission_date" type="text" name="admission_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-admission_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="admission_end_date" class="form-label">Admission End Date <span class="text-danger">*</span></label>
                            <input id="admission_end_date" type="text" name="admission_end_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-admission_end_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="course_start_date" class="form-label">Course Start Date <span class="text-danger">*</span></label>
                            <input id="course_start_date" type="text" name="course_start_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-course_start_date text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="course_end_date" class="form-label">Course End Date <span class="text-danger">*</span></label>
                            <input id="course_end_date" type="text" name="course_end_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-course_end_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="last_joinning_date" class="form-label">Last Joinning Date <span class="text-danger">*</span></label>
                            <input id="last_joinning_date" type="text" name="last_joinning_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY"  data-single-mode="true">
                            <div class="acc__input-error error-last_joinning_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="UK">UK</option>
                                <option value="OVERSEAS">OVERSEAS</option>
                                <option value="BOTH">BOTH</option>
                            </select>
                            <div class="acc__input-error error-type text-danger mt-2"></div>
                        </div>    
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="crationAvailabilityUpdate" class="btn btn-primary w-auto">
                            Update
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
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
                        <input type="hidden" name="course_creation_id" value="{{ $creation->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Module Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCCA" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCCA">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCCA"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCCA btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->