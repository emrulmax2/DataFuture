<div class="intro-y box p-5 mt-5">
    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
        <form id="tabulatorFilterForm-TMC" class="xl:flex sm:mr-auto" >
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                <input id="query-TMC" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
            </div>
            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                <select id="status-TMC" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                    <option value="1">Active</option>
                    <option value="2">Archived</option>
                </select>
            </div>
            <div class="mt-2 xl:mt-0">
                <button id="tabulator-html-filter-go-TMC" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                <button id="tabulator-html-filter-reset-TMC" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
            </div>
        </form>
        <div class="flex mt-5 sm:mt-0">
            <button class="btn btn-primary w-auto" data-tw-toggle="modal" data-tw-target="#addModuleCreationModal">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Module
            </button>
        </div>
    </div>
    <div class="overflow-x-auto scrollbar-hidden">
        <div id="termModuleListTable" data-terminstanceid="{{ $term->id }}" class="mt-5 table-report table-report--tabulator"></div>
    </div>
</div>

<!-- BEGIN: Add Module Creation Modal -->
<div id="addModuleCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addModuleCreationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Module Creation</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="creation_module_id" class="form-label">Module <span class="text-danger">*</span></label>
                        <select id="creation_module_id" name="course_module_id" class="tom-selects w-full">
                            <option value="">Please Select</option>
                            @if($modules->count() > 0)
                                @foreach($modules as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-course_module_id text-danger mt-2"></div>
                    </div> 
                    <div class="mt-3 moduleAssessMentWrap" style="display: none;">
                        <table class="table  table-striped border-t table-sm">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">#</th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Code</th>
                                    <th class="whitespace-nowrap">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveModuleCreation" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="instance_term_id" value="{{ $term->id }}"/>
                    <input type="hidden" name="course_id" value="{{ $course->id }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Module Creation Modal -->

<!-- BEGIN: Edit or View Assessment Modal -->
<div id="viewModuleAssessmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="viewModuleAssessmentForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Assessments list of <u class="moduleName">Module Name</u></h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="theLoader flex flex-col justify-end items-center py-10">
                        <i data-loading-icon="oval" class="w-20 h-20"></i>
                    </div>
                    <div class="theContent" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateAssessments" class="btn btn-primary w-auto">
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
                    <input type="hidden" name="module_creation_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit or View Assessment Modal -->

<!-- BEGIN: Add Assessment Modal -->
<div id="addModuleAssessmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addModuleAssessmentForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Assessments list of <u class="moduleName">Module Name</u></h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="theLoader flex flex-col justify-end items-center py-10">
                        <i data-loading-icon="oval" class="w-20 h-20"></i>
                    </div>
                    <div class="theContent" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="addMAssessments" class="btn btn-primary w-auto">
                        Add
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
                    <input type="hidden" name="module_creation_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Add Assessment Modal -->

<!-- BEGIN: Edit Modal -->
<div id="editModuleCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editModuleCreationForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Module Creation</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="module_name" class="form-label">Module Name <span class="text-danger">*</span></label>
                        <input id="module_name" type="text" name="module_name" class="form-control w-full">
                        <div class="acc__input-error error-module_name text-danger mt-2"></div>
                    </div> 
                    <div class="mt-3">
                        <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                        <input id="code" type="text" name="code" class="form-control w-full">
                        <div class="acc__input-error error-code text-danger mt-2"></div>
                    </div>       
                    <div class="mt-3">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control w-full">
                            <option value="">Please Select</option>
                            <option value="core">Core</option>
                            <option value="specialist">Specialist</option>
                            <option value="optional">Optional</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="credit_value" class="form-label">Credit Value <span class="text-danger">*</span></label>
                        <input id="credit_value" type="text" name="credit_value" class="form-control w-full">
                        <div class="acc__input-error error-credit_value text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="unit_value" class="form-label">Unit Value <span class="text-danger">*</span></label>
                        <input id="unit_value" type="text" name="unit_value" class="form-control w-full">
                        <div class="acc__input-error error-unit_value text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="moodle_enrollment_key" class="form-label">Enrollment Key</label>
                        <input id="moodle_enrollment_key" type="text" name="moodle_enrollment_key" class="form-control w-full">
                    </div>
                    <div class="mt-3">
                        <label for="class_type" class="form-label">Class Type</label>
                        <select id="class_type" name="class_type" class="form-control w-full">
                            <option value="">Please Select</option>
                            <option value="Theory">Theory</option>
                            <option value="Practical">Practical</option>
                            <option value="Tutorial">Tutorial</option>
                            <option value="Seminar">Seminar</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label for="submission_date" class="form-label">Submission Date</label>
                        <input id="submission_date" type="text" name="submission_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateModuleCreation" class="btn btn-primary w-auto">
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

<!-- BEGIN: Success Modal Content -->
<div id="successModalMCAS" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successModalTitleMCAS"></div>
                    <div class="text-slate-500 mt-2 successModalDescMCAS"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->