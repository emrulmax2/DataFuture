
    <!-- BEGIN: Add Employement History Modal -->
    <div id="addEmployementHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addEmployementHistoryForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Employment Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <label for="company_name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Company Name" id="company_name" class="form-control w-full" name="company_name">
                                <div class="acc__input-error error-company_name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="company_phone" class="form-label">Organization Phone <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Company Phone" id="company_phone" class="form-control w-full applicationPhoneMask" name="company_phone">
                                <div class="acc__input-error error-company_phone text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Position" id="position" class="form-control w-full" name="position">
                                <div class="acc__input-error error-position text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-5">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="text" placeholder="MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-2 text-center">
                                <label for="continuing" class="form-label">Continuing</label>
                                <div class="form-check form-switch mt-2 justify-center">
                                    <input id="continuing" class="form-check-input" type="checkbox" name="continuing" value="1">
                                    <label class="form-check-label" for="continuing">&nbsp;</label>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-5">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="text" placeholder="MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-12">
                                <label for="company_address" class="form-label">Organization Address <span class="text-danger">*</span></label>
                                <div class="addressWrap mb-2" id="empHistoryAddress" style="display: none;"></div>
                                <div>
                                    <button type="button" data-tw-toggle="modal" data-prefix="employment" data-address-wrap="#empHistoryAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                    </button>
                                </div>
                                <div class="acc__input-error error-employment_address text-danger mt-2"></div>
                            </div>

                            <div class="col-span-12">
                                <div class="pt-2 mb-2 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                                <div class="font-medium text-base">Reference</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Name" id="contact_name" class="form-control w-full" name="contact_name">
                                <div class="acc__input-error error-contact_name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="contact_position" class="form-label">Contact Position <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Position" id="contact_position" class="form-control w-full" name="contact_position">
                                <div class="acc__input-error error-contact_position text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Phone" id="contact_phone" class="form-control w-full applicationPhoneMask" name="contact_phone">
                                <div class="acc__input-error error-contact_phone text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" placeholder="Contact Email" id="contact_email" class="form-control w-full" name="contact_email">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveEmpHistory" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Employement History Modal -->

    <!-- BEGIN: Edit Employement History Modal -->
    <div id="editEmployementHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editEmployementHistoryForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Employment Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <label for="edit_company_name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Company Name" id="edit_company_name" class="form-control w-full" name="company_name">
                                <div class="acc__input-error error-company_name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_company_phone" class="form-label">Organization Phone <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Company Phone" id="edit_company_phone" class="form-control w-full applicationPhoneMask" name="company_phone">
                                <div class="acc__input-error error-company_phone text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_position" class="form-label">Position <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Position" id="edit_position" class="form-control w-full" name="position">
                                <div class="acc__input-error error-position text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-5">
                                <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="text" placeholder="MM-YYYY" id="edit_start_date" class="form-control datepicker" name="start_date" data-format="MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-2 text-center">
                                <label for="edit_continuing" class="form-label">Continuing</label>
                                <div class="form-check form-switch mt-2 justify-center">
                                    <input id="edit_continuing" class="form-check-input" type="checkbox" name="continuing" value="1">
                                    <label class="form-check-label" for="continuing">&nbsp;</label>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-5">
                                <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="text" placeholder="MM-YYYY" id="edit_end_date" class="form-control datepicker" name="end_date" data-format="MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-12">
                                <label for="company_address" class="form-label">Organization Address <span class="text-danger">*</span></label>
                                <div class="addressWrap mb-2" id="editEmpHistoryAddress" style="display: none;"></div>
                                <div>
                                    <button type="button" data-tw-toggle="modal" data-prefix="employment" data-address-wrap="#editEmpHistoryAddress" data-tw-target="#addressModal" class="addressPopupToggler btn btn-linkedin w-auto">
                                        <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> <span>Add Address</span>
                                    </button>
                                </div>
                                <div class="acc__input-error error-employment_address text-danger mt-2"></div>
                            </div>

                            <div class="col-span-12">
                                <div class="pt-2 mb-2 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                                <div class="font-medium text-base">Reference</div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_contact_name" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Name" id="edit_contact_name" class="form-control w-full" name="contact_name">
                                <div class="acc__input-error error-contact_name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_contact_position" class="form-label">Contact Position <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Position" id="edit_contact_position" class="form-control w-full" name="contact_position">
                                <div class="acc__input-error error-contact_position text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_contact_phone" class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" placeholder="Contact Phone" id="edit_contact_phone" class="form-control w-full applicationPhoneMask" name="contact_phone">
                                <div class="acc__input-error error-contact_phone text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_contact_email" class="form-label">Contact Email</label>
                                <input type="email" placeholder="Contact Email" id="edit_contact_email" class="form-control w-full" name="contact_email">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateEmpHistory" class="btn btn-primary w-auto">     
                            update                      
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                        <input type="hidden" name="id" value="0"/>
                        <input type="hidden" name="ref_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Employement History Modal -->