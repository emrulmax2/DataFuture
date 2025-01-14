@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.course-management.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">Term Declaration List</h2>
                    <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add New Term</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="termTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="addModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add New Term</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-4">       
                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                
                                <select id="academic_year_id" name="academic_year_id" class="form-control w-full academic_year_id">
                                    <option value="">Please Select</option>
                                    @foreach($academicYears as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach    
                                </select>
                                <div id="error-academic_year_id" class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="name" class="form-label"><span class="text-danger">*</span>Term Name</label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>      
                            <div class="col-span-12 sm:col-span-4">       
                                <label for="term_type_id" class="form-label">Term Type <span class="text-danger">*</span></label>
                                
                                <select id="term_type_id" name="term_type_id" class="form-control w-full term_type_id">
                                    <option value="">Please Select</option>
                                    @foreach($termTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach    
                                </select>
                                <div id="error-term_type_id" class="acc__input-error error-term_type_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="total_teaching_weeks" class="form-label">Total Teaching Weeks <span class="text-danger">*</span></label>
                                <input id="total_teaching_weeks" type="number" name="total_teaching_weeks" class="form-control w-full">
                                <div class="acc__input-error error-total_teaching_weeks text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="teaching_start_date" class="form-label">Teaching Start Date <span class="text-danger">*</span></label>
                                <input id="teaching_start_date" name="teaching_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-teaching_start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="teaching_end_date" class="form-label">Teaching End Date <span class="text-danger">*</span></label>
                                <input id="teaching_end_date" name="teaching_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-teaching_end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="revision_start_date" class="form-label">Revision Start Date <span class="text-danger">*</span></label>
                                <input id="revision_start_date" name="revision_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-revision_start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="revision_end_date" class="form-label">Revision End Date <span class="text-danger">*</span></label>
                                <input id="revision_end_date" name="revision_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-revision_end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_publish_date" class="form-label">Exam Publish Date </label>
                                <input id="exam_publish_date" name="exam_publish_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-exam_publish_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_publish_time" class="form-label">Exam Publish Time </label>
                                <input id="exam_publish_time" name="exam_publish_time" type="text" class="form-control theTimeField" placeholder="HH:MM">    
                                <div class="acc__input-error error-exam_publish_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_resubmission_publish_date" class="form-label">Resubmission Publish Date </label>
                                <input id="exam_resubmission_publish_date" name="exam_resubmission_publish_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-exam_resubmission_publish_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_resubmission_publish_time" class="form-label">Resubmission Publish Time </label>
                                <input id="exam_resubmission_publish_time" name="exam_resubmission_publish_time" type="text" class="form-control theTimeField" placeholder="HH:MM" >    
                                <div class="acc__input-error error-exam_resubmission_publish_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="stuload" class="form-label">Term Stuload </label>
                                <input id="stuload" name="stuload" max="100" type="number" class="form-control" placeholder="33" >    
                                <div class="acc__input-error error-stuload text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="save" class="btn btn-primary w-auto">     
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
    <div id="editModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Term</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-4">       
                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                
                                <select id="academic_year_id" name="academic_year_id" class="form-control w-full academic_year_id">
                                    <option value="">Please Select</option>
                                    @foreach($academicYears as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach    
                                </select>
                                <div id="error-academic_year_id" class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="name" class="form-label"><span class="text-danger">*</span>Term Name</label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>      
                            <div class="col-span-12 sm:col-span-4">       
                                <label for="term_type_id" class="form-label">Term Type <span class="text-danger">*</span></label>
                                
                                <select id="term_type_id" name="term_type_id" class="form-control w-full term_type_id">
                                    <option value="">Please Select</option>
                                    @foreach($termTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach    
                                </select>
                                <div id="error-term_type_id" class="acc__input-error error-term_type_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input id="start_date" name="start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                <input id="end_date" name="end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="total_teaching_weeks" class="form-label">Total Teaching Weeks <span class="text-danger">*</span></label>
                                <input id="total_teaching_weeks" type="number" name="total_teaching_weeks" class="form-control w-full">
                                <div class="acc__input-error error-total_teaching_weeks text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="teaching_start_date" class="form-label">Teaching Start Date <span class="text-danger">*</span></label>
                                <input id="teaching_start_date" name="teaching_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-teaching_start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="teaching_end_date" class="form-label">Teaching End Date <span class="text-danger">*</span></label>
                                <input id="teaching_end_date" name="teaching_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-teaching_end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="revision_start_date" class="form-label">Revision Start Date <span class="text-danger">*</span></label>
                                <input id="revision_start_date" name="revision_start_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-revision_start_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="revision_end_date" class="form-label">Revision End Date <span class="text-danger">*</span></label>
                                <input id="revision_end_date" name="revision_end_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-revision_end_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_publish_date" class="form-label">Exam Publish Date </label>
                                <input id="exam_publish_date" name="exam_publish_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-exam_publish_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_publish_time" class="form-label">Exam Publish Time </label>
                                <input id="exam_publish_time" name="exam_publish_time" type="text" class="form-control theTimeField" placeholder="HH:MM">    
                                <div class="acc__input-error error-exam_publish_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_resubmission_publish_date" class="form-label">Resubmission Publish Date </label>
                                <input id="exam_resubmission_publish_date" name="exam_resubmission_publish_date" type="text" class="form-control datepicker itdp" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                <div class="acc__input-error error-exam_resubmission_publish_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="exam_resubmission_publish_time" class="form-label">Resubmission Publish Time </label>
                                <input id="exam_resubmission_publish_time" name="exam_resubmission_publish_time" type="text" class="form-control theTimeField" placeholder="HH:MM" >    
                                <div class="acc__input-error error-exam_resubmission_publish_time text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="edit_stuload" class="form-label">Term Stuload </label>
                                <input id="edit_stuload" name="stuload" max="100" type="number" class="form-control" placeholder="33" >    
                                <div class="acc__input-error error-stuload text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="update" class="btn btn-primary w-auto">
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
@endsection

@section('script')
    @vite('resources/js/course-management.js')
    @vite('resources/js/term-declaration.js')
@endsection