<div class="intro-y block sm:flex items-center h-10">
    <h2 class="text-lg font-medium truncate mr-5">Module Assessment</h2>
    <div class="ml-auto w-full sm:w-auto flex mt-4 sm:mt-0">
        <button data-tw-merge data-tw-toggle="modal" data-tw-target="#addAssessmentModal" class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1"><i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add An Assessment
            <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden"></i>
        </button>
    </div>
</div>

<div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
    <form id="tabulatorFilterForm-CLTML" class="xl:flex sm:mr-auto" >
        
        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
            <select id="status-CLTML" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                <option value="1">Active</option>
                <option value="2">Archived</option>
            </select>
        </div>
        <div class="mt-2 xl:mt-0">
            <button id="tabulator-html-filter-go-CLTML" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
            <button id="tabulator-html-filter-reset-CLTML" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
        </div>
    </form>
    <div class="flex mt-5 sm:mt-0">
        <button id="tabulator-print-CLTML" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
        </button>
        <div class="dropdown w-1/2 sm:w-auto">
            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
            </button>
            <div class="dropdown-menu w-40">
                <ul class="dropdown-content">
                    <li>
                        <a id="tabulator-export-csv-CLTML" href="javascript:;" class="dropdown-item">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                        </a>
                    </li>
                    <li>
                        <a id="tabulator-export-xlsx-CLTML" href="javascript:;" class="dropdown-item">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="overflow-x-auto scrollbar-hidden">
    <div id="classPlanAssessmentModuleTable" data-planid="{{ $plan->id }}" class="mt-5 table-report table-report--tabulator"></div>
</div>

<!-- BEGIN: Import Modal -->
<div id="addAssessmentModal" class="modal" size="xl" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    
        <div class="modal-dialog modal-xl">
            <div class=" modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add an assessment</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <form method="post" id="saveModuleAssesment">
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5">
                        <div class="col-span-12">
                            @if(!$assessmentlist->isEmpty())
                            <div class="col-span-12">
                                <label for="course_module_base_assesment_id" class="form-label">Assesment <span class="text-danger">*</span></label>
                                <select id="course_module_base_assesment_id" class="assementlccTom lcc-tom-select w-full" name="course_module_base_assesment_id">
                                    <option value="" selected>Please Select</option>
                                    
                                        @foreach($assessmentlist as $t)
                                            <option value="{{ $t->id }}">{{ $t->type->name }} - {{ $t->type->code }}</option>
                                        @endforeach 
                                
                                </select>
                                <div class="acc__input-error error-course_module_base_assesment_id text-danger mt-2"></div>
                            </div> 
                            @else 
                                <div class="alert alert-pending-soft show flex items-center col-span-12" role="alert"><i data-lucide="alert-triangle" class="w-4 h-4 mr-1"></i> No Assessment found!</div>
                            @endif 
                        </div>
                        <div class="col-span-12 mt-3">
                            <label for="publish_at" class="form-label">Publish Date<span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="publish_at" class="form-control datepicker" name="publish_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-publish_at text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 mt-3">
                            <label for="visible_at" class="form-label">Visible Publish Date<span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="visible_at" class="form-control datepicker" name="visible_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-visible_at text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 mt-3">
                            <label for="resubmission_date" class="form-label">Resubmission Date<span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="resubmission_at" class="form-control datepicker" name="resubmission_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-resubmission_date text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 mt-3">
                            <label for="resubmission_visible_at" class="form-label">Visible Resubmission Date<span class="text-danger">*</span></label>
                            <input type="text" value="" placeholder="DD-MM-YYYY" id="resubmission_visible_at" class="form-control datepicker" name="resubmission_visible_at" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-resubmission_visible_at text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                @if(!$assessmentlist->isEmpty())
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="save" class="btn btn-primary w-auto">
                        Add Now
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
                @endif
                <input type="hidden" value="{{ $plan->id }}" name="plan_id"/>
                </form>
            </div>
        </div>
    
</div>
<!-- END: Import Modal -->

<!-- BEGIN: Import Modal -->
{{-- #bankholidayImportModal --}}
<div id="resultImportModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Import Result</h2>
                    <a data-tw-dismiss="modal" href="javascript:void(0);">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"   action="{{ route('result.upload-excel') }}" class="[&.dropzone]:border-2 [&.dropzone]:border-dashed dropzone [&.dropzone]:border-darkmode-200/60 [&.dropzone]:dark:bg-darkmode-600 [&.dropzone]:dark:border-white/5 dropzone" id="bankholidayImportForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input name="import_holiday_file" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop an excel file here with a selected option.</div>
                            <div class="text-gray-600">
                                Please Choose
                                <span class="font-medium">an option</span> below to
                                upload result as new records or update the old records.
                            </div>
                        </div>
                        <input type="hidden" name="assessment_plan_id" value=""/>
                        <input type="hidden" name="upload_type" value="add"/>
                    </form>

                    
                    <div data-tw-merge class="flex items-center mt-5"><input  id="radio-switch-1" name="upload_type_select" value="add" checked data-tw-merge type="radio" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                        <label data-tw-merge for="radio-switch-1" class="cursor-pointer ml-2 inline-flex"><i data-lucide="plus-square" class="w-4 h-4 mr-1 ml-1"></i>Add Results</label>
                    </div>
                    <div data-tw-merge class="flex items-center mt-2"><input id="radio-switch-2" name="upload_type_select" value="update" data-tw-merge type="radio" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                        <label data-tw-merge for="radio-switch-2" class="cursor-pointer ml-2 inline-flex"><i data-lucide="check-square" class="w-4 h-4 mr-1 ml-1"></i> Update Results</label>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- <a style="float: left;" href="{{ route('bankholidays.export') }}" id="downloadSample" class="btn btn-success text-white w-auto">Download Sample Excel</a> --}}
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button id="saveImportResult" class="btn btn-primary w-auto">Upload <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                </div>
            </div>
        
    </div>
</div>
<!-- END: Import Modal -->
