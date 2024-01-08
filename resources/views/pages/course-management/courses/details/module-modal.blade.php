<!-- BEGIN: Add Module Modal -->
<div id="courseModuleAddModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="courseModuleAddForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Module</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-6">
                                <div>
                                    <label for="name" class="form-label">Module Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="modul_level" class="form-label">Module Level</label>
                                    <select id="modul_level" name="module_level_id" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($levels))
                                            @foreach($levels as $lvl)
                                                <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="code" class="form-label">Module Code <span class="text-danger">*</span></label>
                                    <input id="code" type="text" name="code" class="form-control w-full">
                                    <div class="acc__input-error error-code text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="credit_value" class="form-label">Credit Value <span class="text-danger">*</span></label>
                                    <input id="credit_value" type="text" name="credit_value" class="form-control w-full">
                                    <div class="acc__input-error error-credit_value text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="unit_value" class="form-label">Unit Value <span class="text-danger">*</span></label>
                                    <input id="unit_value" type="text" name="unit_value" class="form-control w-full">
                                    <div class="acc__input-error error-unit_value text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="module_status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="module_status" name="status" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        <option value="core">Core</option>
                                        <option value="specialist">Specialist</option>
                                    </select>
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>      
                    </div>
                    <div class="modal-footer">
                        <div style="float:left" class="mt-2">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-4" for="checkbox-switch-7">Is Active?</label>
                                <input id="checkbox-switch-7" checked name="active" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveModule" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_id" value="{{ $course->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Module Modal -->

    <!-- BEGIN: Edit Module Modal -->
    <div id="courseModuleEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="courseModuleEditForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Module</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-6">
                                <div>
                                    <label for="name" class="form-label">Module Name <span class="text-danger">*</span></label>
                                    <input id="name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="modul_level" class="form-label">Module Level</label>
                                    <select id="modul_level" name="module_level_id" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        @if(!empty($levels))
                                            @foreach($levels as $lvl)
                                                <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="code" class="form-label">Module Code <span class="text-danger">*</span></label>
                                    <input id="code" type="text" name="code" class="form-control w-full">
                                    <div class="acc__input-error error-code text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="credit_value" class="form-label">Credit Value <span class="text-danger">*</span></label>
                                    <input id="credit_value" type="text" name="credit_value" class="form-control w-full">
                                    <div class="acc__input-error error-credit_value text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="unit_value" class="form-label">Unit Value <span class="text-danger">*</span></label>
                                    <input id="unit_value" type="text" name="unit_value" class="form-control w-full">
                                    <div class="acc__input-error error-unit_value text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div>
                                    <label for="module_status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select id="module_status" name="status" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        <option value="core">Core</option>
                                        <option value="specialist">Specialist</option>
                                    </select>
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>      
                    </div>
                    <div class="modal-footer">
                        <div style="float:left" class="mt-2">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-4" for="checkbox-switch-7">Is Active?</label>
                                <input id="checkbox-switch-7" checked name="active" value="1" class="form-check-input" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateModule" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_id" value="{{ $course->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Module Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalMD" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleMD">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescMD"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithMD btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->