@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <!-- BEGIN: Page Header -->
    <div class="intro-y flex flex-wrap items-center justify-between gap-3 mt-8 mb-2">
        <div>
            <h2 class="font-display text-2xl font-semibold text-slate-800 dark:text-white leading-tight tracking-tight">Upcoming Appraisal</h2>
            <p class="text-sm text-slate-400 mt-1">Performance reviews due &middot; London Churchill College</p>
        </div>
        <a href="{{ route('hr.portal') }}" class="flex items-center gap-2 text-sm font-semibold text-primary hover:text-primary/80 px-3 py-2 rounded-lg hover:bg-primary/10 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Portal
        </a>
    </div>
    <!-- END: Page Header -->

    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box mt-5">
        <!-- Toolbar -->
        <div class="flex flex-col xl:flex-row xl:items-end gap-4 px-5 py-4 border-b border-slate-100 dark:border-darkmode-400">
            <form id="tabulatorFilterForm" class="flex flex-wrap xl:flex-nowrap gap-3 items-end mr-auto">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-400">Status</label>
                    <select id="status" name="status" class="form-select h-[42px] rounded-lg border-slate-200 dark:border-darkmode-400 bg-slate-50 dark:bg-darkmode-800 text-sm font-semibold w-36">
                        <option value="1">Active</option>
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
            <div id="upcomingAppraisalListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    
    <div id="editAppraisalModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editAppraisalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Appraisal</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6">
                                <label for="edit_due_on" class="form-label">Due On <span class="text-danger">*</span></label>
                                <input id="edit_due_on" readonly type="text" name="due_on" class="form-control w-full">
                                <div class="acc__input-error error-due_on text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_completed_on" class="form-label">Completed On</label>
                                <input id="edit_completed_on" type="text" name="completed_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_next_due_on" class="form-label">Next Due On</label>
                                <input id="edit_next_due_on" type="text" name="next_due_on" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_appraised_by" class="form-label">Appraised By</label>
                                <select id="edit_appraised_by" name="appraised_by" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_reviewed_by" class="form-label">Reviewed By</label>
                                <select id="edit_reviewed_by" name="reviewed_by" class="form-control w-full">
                                    <option value="">Please Select</option>
                                    @if(!empty($activeEmployees))
                                        @foreach($activeEmployees as $aemp)
                                            <option value="{{ $aemp->id }}">{{ $aemp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label for="edit_total_score" class="form-label">Total Score</label>
                                <input id="edit_total_score" type="number" step="any" name="total_score" class="form-control w-full">
                            </div>
                            <div class="col-span-6">
                                <label for="edit_promotion_consideration" class="form-label">Consider for Promotion</label>
                                <div class="form-check form-switch m-0">
                                    <input id="edit_promotion_consideration" class="form-check-input" name="promotion_consideration" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-12">
                                <label for="edit_notes" class="form-label">Note</label>
                                <textarea id="edit_notes" name="notes" rows="3" class="form-control w-full"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAppraisal" class="btn btn-primary w-auto">     
                            Update Apprisal                  
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

    <div id="viewAppraisalNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Appraisal Note</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-0">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Edit New Request Modal -->


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
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
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
    @vite('resources/js/hr-upcoming-appraisal.js')
@endsection