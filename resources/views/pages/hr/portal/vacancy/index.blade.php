@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Vacancy List</h2>
            <p class="text-sm text-slate-400 mt-1">Open roles &amp; recruitment &middot; London Churchill College</p>
        </div>
        <button data-tw-toggle="modal" data-tw-target="#addVacancyModal" type="button" class="btn btn-primary text-white h-[42px] text-sm"><i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i> Add Vacancy</button>
    </div>
    <!-- END: Page Header -->

    <div class="intro-y box mt-5">
        <!-- Toolbar -->
        <div class="flex flex-col xl:flex-row xl:items-end gap-4 px-5 py-4 border-b border-slate-100 dark:border-darkmode-400">
            <form id="tabulatorFilterForm" class="flex flex-wrap xl:flex-nowrap gap-3 items-end mr-auto">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Query</label>
                    <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 dark:bg-darkmode-800 dark:border-darkmode-400 rounded-lg px-3 h-[42px] w-60 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 flex-none"></i>
                        <input id="query" name="query" type="text" class="bg-transparent border-0 outline-none text-sm text-slate-700 dark:text-slate-300 w-full placeholder:text-slate-400" placeholder="Search...">
                    </div>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Status</label>
                    <select id="status" name="status" class="form-select h-[42px] rounded-lg border-slate-200 dark:border-darkmode-400 bg-slate-50 dark:bg-darkmode-800 text-sm font-semibold w-36">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                        <option value="2">Archived</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary h-[42px] px-5 text-sm">Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-outline-secondary h-[42px] px-5 text-sm">Reset</button>
                </div>
            </form>
            <div class="flex flex-wrap gap-2">
                <button id="tabulator-print" class="btn btn-outline-secondary h-[42px] text-sm">
                    <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i> Print
                </button>
                <div class="dropdown">
                    <button class="dropdown-toggle btn btn-outline-secondary h-[42px] text-sm" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="download" class="w-4 h-4 mr-1.5"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-1.5"></i>
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
        <div class="overflow-x-auto scrollbar-hidden px-5 pb-5">
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
