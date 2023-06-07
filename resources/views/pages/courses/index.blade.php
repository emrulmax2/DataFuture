@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Courses List</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="add_btn btn btn-primary shadow-md mr-2">Add New Course</button>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
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
                                <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                </a>
                            </li>
                            <li>
                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                            <li>
                                <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="courseTableId" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Add Modal -->
    <div id="addModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Course</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="degree_offered" class="form-label">Degree Offered <span class="text-danger">*</span></label>
                            <input id="degree_offered" type="text" name="degree_offered" class="form-control w-full">
                            <div class="acc__input-error error-degree_offered text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="pre_qualification" class="form-label">Pre Qualification <span class="text-danger">*</span></label>
                            <input id="pre_qualification" type="text" name="pre_qualification" class="form-control w-full">
                            <div class="acc__input-error error-pre_qualification text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="awarding_body" class="form-label">Awarding Body <span class="text-danger">*</span></label>
                            <select id="awarding_body" name="awarding_body_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($bodies))
                                    @foreach($bodies as $bds)
                                        <option value="{{ $bds->id }}">{{ $bds->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-awarding_body_id text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="source_tution_fee" class="form-label">Source of Tution Fee <span class="text-danger">*</span></label>
                            <select id="source_tution_fee" name="source_tuition_fee_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($fees))
                                    @foreach($fees as $fs)
                                        <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-source_tuition_fee_id text-danger mt-2"></div>
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
    <div id="editModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Course</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_name" class="form-label">Course Name <span class="text-danger">*</span></label>
                            <input id="edit_name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_degree_offered" class="form-label">Degree Offered <span class="text-danger">*</span></label>
                            <input id="edit_degree_offered" type="text" name="degree_offered" class="form-control w-full">
                            <div class="acc__input-error error-degree_offered text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_pre_qualification" class="form-label">Pre Qualification <span class="text-danger">*</span></label>
                            <input id="edit_pre_qualification" type="text" name="pre_qualification" class="form-control w-full">
                            <div class="acc__input-error error-pre_qualification text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_awarding_body" class="form-label">Awarding Body <span class="text-danger">*</span></label>
                            <select id="edit_awarding_body" name="awarding_body_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($bodies))
                                    @foreach($bodies as $bds)
                                        <option value="{{ $bds->id }}">{{ $bds->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-awarding_body_id text-danger mt-2"></div>
                        </div> 
                        <div class="mt-3">
                            <label for="edit_source_tution_fee" class="form-label">Source of Tution Fee <span class="text-danger">*</span></label>
                            <select id="edit_source_tution_fee" name="source_tuition_fee_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if(!empty($fees))
                                    @foreach($fees as $fs)
                                        <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-source_tuition_fee_id text-danger mt-2"></div>
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
    <!-- BEGIN: Warning Modal Content -->
    {{-- <div id="coursesWarningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5">Oops...</div>
                        <div class="text-slate-500 mt-2">Duplicate Course Name!</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn w-24 btn-warning">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
    @endsection