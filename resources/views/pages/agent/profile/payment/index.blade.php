@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->full_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.agent.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Bank Details</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#addBankDetailsModal" type="button" class="btn btn-primary shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Bank</button>
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        
        <div class="intro-y p-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
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
                    <button id="tabulator-print-ED" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="agentBankListTable" data-agent="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

    
    <!-- BEGIN: Add Bank Modal -->
    <div id="addBankDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addBankDetailsForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Bank Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                            <input type="text" value="" id="beneficiary" name="beneficiary" class="form-control w-full">
                            <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                            <input type="text" value="" id="sort_code" name="sort_code" class="form-control w-full">
                            <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" value="" id="ac_no" minlength="8" maxlength="8" name="ac_no" class="form-control w-full">
                            <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                            <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveABNK" class="btn btn-primary w-auto">     
                            Add Bank                   
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
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Bank Modal -->
    
    <!-- BEGIN: Edit Bank Modal -->
    <div id="editBankDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editBankDetailsForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Bank Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_beneficiary" class="form-label">Beneficiary Name <span class="text-danger">*</span></label>
                            <input type="text" value="" id="edit_beneficiary" name="beneficiary" class="form-control w-full">
                            <div class="acc__input-error error-beneficiary text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_sort_code" class="form-label">Sort Code <span class="text-danger">*</span></label>
                            <input type="text" value="" id="edit_sort_code" name="sort_code" class="form-control w-full">
                            <div class="acc__input-error error-sort_code text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_ac_no" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" value="" id="edit_ac_no" minlength="8" maxlength="8" name="ac_no" class="form-control w-full">
                            <div class="acc__input-error error-ac_no text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                            <input id="edit_active" class="form-check-input m-0" name="active" value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateABNK" class="btn btn-primary w-auto">     
                            Update Bank                   
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
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Bank Modal -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
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

    <!-- BEGIN: Confirm Confirm Modal Content -->
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
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-employee="{{ $employee->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/agent-payment-settings.js')
@endsection