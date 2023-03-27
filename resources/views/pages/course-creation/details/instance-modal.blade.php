<!-- BEGIN: Add Base Data Future Modal -->
<div id="addCourseCreationInstModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addCourseCreationInstForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Instance</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select id="academic_year_id" name="academic_year_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($academic))
                                    @foreach($academic as $ac)
                                        <option value="{{ $ac->id }}">{{ $ac->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input id="start_date" name="start_date" type="text" class="form-control datepicker ccin" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input id="end_date" name="end_date" type="text" class="form-control datepicker ccin" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="total_teaching_week" class="form-label">Total Teaching Week <span class="text-danger">*</span></label>
                            <input id="total_teaching_week" type="number" name="total_teaching_week" class="form-control w-full">
                            <div class="acc__input-error error-total_teaching_week text-danger mt-2"></div>
                        </div>    
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveCCIN" class="btn btn-primary w-auto">
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
    <!-- END: Add  Base Data Future Modal -->

<!-- BEGIN: Add Base Data Future Modal -->
<div id="editCourseCreationInstModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editCourseCreationInstForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Instance</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select id="edit_academic_year_id" name="academic_year_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($academic))
                                    @foreach($academic as $ac)
                                        <option value="{{ $ac->id }}">{{ $ac->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input id="edit_start_date" name="start_date" type="text" class="form-control datepicker ccin" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-start_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input id="edit_end_date" name="end_date" type="text" class="form-control datepicker ccin" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                            <div class="acc__input-error error-end_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_total_teaching_week" class="form-label">Total Teaching Week <span class="text-danger">*</span></label>
                            <input id="edit_total_teaching_week" type="number" name="total_teaching_week" class="form-control w-full">
                            <div class="acc__input-error error-total_teaching_week text-danger mt-2"></div>
                        </div>     
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateCCIN" class="btn btn-primary w-auto">
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
    <!-- END: Add  Base Data Future Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCCIN" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCCIN">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCCIN"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCCIN btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->