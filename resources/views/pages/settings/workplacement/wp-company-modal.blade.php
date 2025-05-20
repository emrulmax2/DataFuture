<!-- BEGIN: Add Modal -->
<div id="addWPCompanyModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addWPCompanyForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Company</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-2">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="fax" class="form-label">FAX</label>
                            <input id="fax" type="text" name="fax" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="website" class="form-label">Website</label>
                            <input id="website" type="text" name="website" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="other_info" class="form-label">Other Info</label>
                            <textarea id="other_info" name="other_info" class="form-control w-full" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                        <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveCompany" class="btn btn-primary w-auto">     
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
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Modal -->

<!-- BEGIN: Edit Modal -->
<div id="editWPCompanyModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editWPCompanyForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Company</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                <div class="grid grid-cols-12 gap-4 gap-y-2">
                        <div class="col-span-12">
                            <label for="edit_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input id="edit_name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input id="edit_email" type="email" name="email" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input id="edit_phone" type="text" name="phone" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_fax" class="form-label">FAX</label>
                            <input id="edit_fax" type="text" name="fax" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_website" class="form-label">Website</label>
                            <input id="edit_website" type="text" name="website" class="form-control w-full">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea id="edit_address" name="address" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="edit_other_info" class="form-label">Other Info</label>
                            <textarea id="edit_other_info" name="other_info" class="form-control w-full" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                        <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                        <input id="edit_active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateCompany" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Modal -->

<!-- BEGIN: Add Supervisor Modal -->
<div id="editCompanySupervisorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editCompanySupervisorForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Supervisor</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                <div class="grid grid-cols-12 gap-4 gap-y-2">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" class="form-control w-full">
                        </div>
                        <div class="col-span-12">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control w-full">
                        </div>
                        <div class="col-span-12">
                            <label for="other_info" class="form-label">Other Info</label>
                            <textarea id="other_info" name="other_info" class="form-control w-full" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="editSupervisor" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Supervisor Modal -->

<!-- BEGIN: Add Supervisor Modal -->
<div id="addCompanySupervisorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="addCompanySupervisorForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Supervisor</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                <div class="grid grid-cols-12 gap-4 gap-y-2">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" name="email" class="form-control w-full">
                        </div>
                        <div class="col-span-12">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control w-full">
                        </div>
                        <div class="col-span-12">
                            <label for="other_info" class="form-label">Other Info</label>
                            <textarea id="other_info" name="other_info" class="form-control w-full" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="addSupervisor" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="company_id" value="0" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Supervisor Modal -->


    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle">Success</div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->