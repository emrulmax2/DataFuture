<!-- BEGIN: Add Module Modal -->
<div id="moduleAssesmentAddModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="moduleAssesmentAddForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Assesment</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="assesment_code" class="form-label">Assesment Code <span class="text-danger">*</span></label>
                            <input id="assesment_code" type="text" name="assesment_code" class="form-control w-full">
                            <div class="acc__input-error error-assesment_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="assesment_name" class="form-label">Assesment Name <span class="text-danger">*</span></label>
                            <input id="assesment_name" type="text" name="assesment_name" class="form-control w-full">
                            <div class="acc__input-error error-assesment_name text-danger mt-2"></div>
                        </div>     
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveModuleAssesment" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_module_id" value="{{ $module->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Module Modal -->

    <!-- BEGIN: Edit Module Modal -->
    <div id="moduleAssesmentEditModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="moduleAssesmentEditForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Assesment</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_assesment_code" class="form-label">Assesment Code <span class="text-danger">*</span></label>
                            <input id="edit_assesment_code" type="text" name="assesment_code" class="form-control w-full">
                            <div class="acc__input-error error-assesment_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_assesment_name" class="form-label">Assesment Name <span class="text-danger">*</span></label>
                            <input id="edit_assesment_name" type="text" name="assesment_name" class="form-control w-full">
                            <div class="acc__input-error error-assesment_name text-danger mt-2"></div>
                        </div>     
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateModuleAssesment" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_module_id" value="{{ $module->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Module Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCMA" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCMA">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCMA"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCMA btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->