<!-- BEGIN: Add Modal -->
<div id="instancetermAddModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
        <form method="POST" action="#" id="instancetermAddForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Instance Term</h2>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6">
                            <label for="name" class="form-label">Term Name <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control" placeholder="Term Name">    
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="term" name="term" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="Autumn Term">Autumn Term</option>
                                <option value="Spring Term">Spring Term</option>
                                <option value="Summer Term">Summer Term</option>
                                <option value="Winter Term">Winter Term</option>
                            </select>
                            <div class="acc__input-error error-term text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                            <select id="session_term" name="session_term" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
                                <option value="4">Term 4</option>
                            </select>
                            <div class="acc__input-error error-session_term text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="total_teaching_weeks" class="form-label">Total Teaching Weeks <span class="text-danger">*</span></label>
                            <input id="total_teaching_weeks" type="number" name="total_teaching_weeks" class="form-control w-full">
                            <div class="acc__input-error error-total_teaching_weeks text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="teaching_start_date" class="form-label">Teaching Start Date <span class="text-danger">*</span></label>
                            <input id="teaching_start_date" name="teaching_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-teaching_start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="teaching_end_date" class="form-label">Teaching End Date <span class="text-danger">*</span></label>
                            <input id="teaching_end_date" name="teaching_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-teaching_end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="revision_start_date" class="form-label">Revision Start Date <span class="text-danger">*</span></label>
                            <input id="revision_start_date" name="revision_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-revision_start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="revision_end_date" class="form-label">Revision End Date <span class="text-danger">*</span></label>
                            <input id="revision_end_date" name="revision_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-revision_end_date text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveInstanceTerm" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="course_creation_instance_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->
<!-- BEGIN: Edit Modal -->
<div id="instancetermEditModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="instancetermEditForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Instance Term</h2>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-6">
                            <label for="name" class="form-label">Term Name <span class="text-danger">*</span></label>
                            <input id="name" name="name" type="text" class="form-control" placeholder="Term Name">    
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="term" name="term" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="Autumn Term">Autumn Term</option>
                                <option value="Spring Term">Spring Term</option>
                                <option value="Summer Term">Summer Term</option>
                                <option value="Winter Term">Winter Term</option>
                            </select>
                            <div class="acc__input-error error-term text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                            <select id="session_term" name="session_term" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
                                <option value="4">Term 4</option>
                            </select>
                            <div class="acc__input-error error-session_term text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="total_teaching_weeks" class="form-label">Total Teaching Weeks <span class="text-danger">*</span></label>
                            <input id="total_teaching_weeks" type="number" name="total_teaching_weeks" class="form-control w-full">
                            <div class="acc__input-error error-total_teaching_weeks text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="teaching_start_date" class="form-label">Teaching Start Date <span class="text-danger">*</span></label>
                            <input id="teaching_start_date" name="teaching_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-teaching_start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="teaching_end_date" class="form-label">Teaching End Date <span class="text-danger">*</span></label>
                            <input id="teaching_end_date" name="teaching_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-teaching_end_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="revision_start_date" class="form-label">Revision Start Date <span class="text-danger">*</span></label>
                            <input id="revision_start_date" name="revision_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-revision_start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="revision_end_date" class="form-label">Revision End Date <span class="text-danger">*</span></label>
                            <input id="revision_end_date" name="revision_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-revision_end_date text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateInstanceTerm" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="course_creation_instance_id" value="0"/>
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="instancetermConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 instancetermConfModTitle">Are you sure?</div>
                    <div class="text-slate-500 mt-2 instancetermConfModDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="instancetermAgreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->