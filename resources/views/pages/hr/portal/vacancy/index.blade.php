@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-5">
        <h2 class="text-lg font-medium mr-auto">Vacancy List</h2>
        <div class="ml-auto flex justify-end">
            <button data-tw-toggle="modal" data-tw-target="#addVacancyModal" type="button" class="btn btn-primary text-white w-auto ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Vacancy</button>
        </div>
    </div>
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
                        <option value="0">Inactive</option>
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
                <div class="dropdown w-1/2 sm:w-auto mr-2">
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
            <div id="vacancyListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="addVacancyModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addVacancyForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Vacancy</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control w-full">
                            <div class="acc__input-error error-title text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="hr_vacancy_type_id" class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="hr_vacancy_type_id" name="hr_vacancy_type_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($types->count() > 0)
                                    @foreach($types as $typ)
                                        <option value="{{ $typ->id }}">{{ $typ->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-hr_vacancy_type_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="link" class="form-label">Link</label>
                            <input type="text" id="link" name="link" class="form-control w-full">
                        </div>
                        <div class="mt-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="text" id="date" name="date" class="form-control w-full datepicker"  placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-5 flex justify-start items-center relative">
                            <label for="addVacanDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addVacanDocument"/>
                            <span id="addVacanDocumentName" class="documentVacanName ml-5"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                            <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addVacancyBtn" class="btn btn-primary w-auto">
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

    <!-- BEGIN: Add Modal -->
    <div id="editVacancyModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editVacancyForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Vacancy</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" id="edit_title" name="title" class="form-control w-full">
                            <div class="acc__input-error error-title text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_hr_vacancy_type_id" class="form-label">Type <span class="text-danger">*</span></label>
                            <select id="edit_hr_vacancy_type_id" name="hr_vacancy_type_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($types->count() > 0)
                                    @foreach($types as $typ)
                                        <option value="{{ $typ->id }}">{{ $typ->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-hr_vacancy_type_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_link" class="form-label">Link</label>
                            <input type="text" id="edit_link" name="link" class="form-control w-full">
                        </div>
                        <div class="mt-3">
                            <label for="edit_date" class="form-label">Date</label>
                            <input type="text" id="edit_date" name="date" class="form-control w-full datepicker"  placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                        </div>
                        <div class="mt-5 flex justify-start items-center relative">
                            <label for="editVacanDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editVacanDocument"/>
                            <span id="editVacanDocumentName" class="documentVacanName ml-5"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                            <input id="edit_active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="editVacancyBtn" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-orange-400 mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary text-white w-24 mr-1">Ok, Got it</button>
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
    @vite('resources/js/hr-portal-vacancy.js')
@endsection
