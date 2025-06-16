<!-- BEGIN: Add Work Placement Modal -->
<div id="workplacementAddModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addWorkPlacementForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Work Placement</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="course_id" class="form-label">Course<span class="text-danger">*</span></label>
                            <select id="course_id" data-placeholder="Select Course" class="tom-select form-control w-full" name="course_id">
                                <option value="">Select Course</option>
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-course_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="start_date" class="form-label">Start Date<span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="start_date" class="form-control datepicker" name="start_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="end_date" class="form-control datepicker" name="end_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="insertWorkPlacement" class="btn btn-primary w-auto"> 
                        Add Work Placement
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
<!-- END: Add Work Placement Modal -->

<!-- BEGIN: Add Level Hours Modal -->
<div id="addLevelHoursModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addLevelHoursForm">
            <input type="hidden" name="workplacement_id" id="workplacement_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Level Hours</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="insertLevelHours" class="btn btn-primary w-auto"> 
                        Add Level Hours
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
<!-- END: Add Level Hours Modal -->

<!-- BEGIN: Edit Level Hours Modal -->
<div id="levelHoursEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editLevelHoursForm">
            <input type="hidden" name="workplacement_id" id="workplacement_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Level Hours</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateLevelHours" class="btn btn-primary w-auto"> 
                        Update Level Hours
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
<!-- END: Edit Level Hours Modal -->
<!-- BEGIN: Add Learning Hours Modal -->
<div id="addLearningHoursModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addLearningHoursForm">
            <input type="hidden" name="level_hours_id" id="level_hours_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Learning Hours</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <div class="form-check form-switch">
                                <label class="form-check-label ml-0 mr-5" for="module_required_add">Module Required?</label>
                                <input id="module_required_add" class="form-check-input mr-0" type="checkbox" name="module_required" value="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="insertLearningHours" class="btn btn-primary w-auto"> 
                        Add Learning Hours
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
<!-- END: Add Learning Hours Modal -->
<!-- BEGIN: Add Learning Hours Modal -->
<div id="editLearningHoursModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editLearningHoursForm">
            <input type="hidden" name="level_hours_id" id="level_hours_id" value="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Learning Hours</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <div class="form-check form-switch">
                                <label class="form-check-label ml-0 mr-5" for="module_required_edit">Module Required?</label>
                                <input id="module_required_edit" class="form-check-input mr-0" type="checkbox" name="module_required" value="1">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateLearningHours" class="btn btn-primary w-auto"> 
                        Update Learning Hours
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
<!-- END: Add Learning Hours Modal -->

<!-- BEGIN: Edit Work Placement Modal -->
<div id="workplacementEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="editWorkPlacementForm" data-form-id="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Work Placement</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12">
                            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
                            <input type="text" placeholder="Enter Name" id="name" class="form-control w-full" name="name">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="hours" class="form-label">Hours<span class="text-danger">*</span></label>
                            <input step="any" type="number" placeholder="Enter Hours" id="hours" class="form-control w-full" name="hours">
                            <div class="acc__input-error error-hours text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="course_id" class="form-label">Course<span class="text-danger">*</span></label>
                            <select id="course_id" data-placeholder="Select Course" class="form-control w-full" name="course_id">
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-course_id text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="start_date" class="form-label">Start Date<span class="text-danger">*</span></label>
                            <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">  
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateWorkPlacement" class="btn btn-primary w-auto"> 
                        Update Work Placement
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
<!-- END: Edit Work Placement Modal -->


<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successModalTitle"></div>
                    <div class="text-slate-500 mt-2 successModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 warningModalTitle"></div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->

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
                    <button type="button" data-tw-dismiss="modal" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-recordid="0" data-status="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->