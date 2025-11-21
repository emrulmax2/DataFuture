@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col md:flex-row items-center mt-1 md:mt-8">
        <div class="flex flex-row justify-center md:justify-normal items-center gap-2 flex-wrap mb-4 md:mb-0 w-full">
            <h2 class="text-lg font-medium text-center md:text-left">Create Invoice</h2>
        </div>
        <div class="md:ml-auto md:w-full flex flex-wrap sm:flex-row gap-1 justify-end">
            <a href="{{ route('university.claims.invoices') }}" class="btn btn-linkedin text-white shadow-md"><i data-lucide="list" class="w-4 h-4 mr-2"></i>Invoices</a>
            <a href="{{ route('university.claims.bulk.agreement') }}" class="btn btn-facebook text-white shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Create Bulk Agreement</a>
        </div>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12">
            <div class="intro-y box">
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 sm:col-span-4">
                            <label class="form-label flex items-center">Intake Semester <i data-loading-icon="three-dots" class="w-6 h-6 ml-3 theLoaders hidden"></i></label>
                            <select id="intakeSemester" name="intakeSemester" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if(!empty($semesters))
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-intakeSemester text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label class="form-label flex items-center">Course</label>
                            <select id="course" name="course" class="w-full tom-selects">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-course text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-4 text-right mt-7">
                            <button id="generateInvoiceList" class="btn btn-primary w-auto text-white">
                                <i data-lucide="search" class="w-4 h-4 mr-2"></i> Search
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                            <button id="claimInvoiceAmount" style="display: none;" class="btn btn-success ml-1 w-auto text-white">
                                <i data-lucide="badge-pound-sterling" class="w-4 h-4 mr-2"></i> Claim
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box mt-5 p-5 invoiceStudentListWrap" style="display: none;">
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="invoiceStudentListTable" class="table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Add Modal -->
    <div id="claimInvoiceAmountModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="claimInvoiceAmountForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Submit Claim</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-6 gap-y-3">
                            <div class="col-span-12 sm:col-span-6">
                                <h3 class="font-medium mb-3 flex justify-between items-center"><span>Invoice To <span class="text-danger">*</span></span> <a href="javascript:void(0);" data-modal="claimInvoiceAmountModal" data-tw-toggle="modal" data-tw-target="#addBudgetVendorModal" class="add_vendor ml-auto font-medium underline inline-flex items-center text-success"><i class="w-3 h-3 mr-1" data-lucide="plus"></i> Add New</a></h3>
                                <div>
                                    <select name="vendor_id" class="w-full tom-selects" id="vendor_id">
                                        <option value="">Please Select</option>
                                        @if($vendors->count() > 0)
                                            @foreach($vendors as $ven)
                                                <option value="{{ $ven->id }}">{{ $ven->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-vendor_id text-danger mt-2"></div>
                                </div>
                                <div class="mt-3 mb-3 vendorDetailsWrap" style="display: none;"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div>
                                    <label for="claim_date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input id="claim_date" type="text" name="claim_date" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-claim_date text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="claim_date" class="form-label">Remit To <span class="text-danger">*</span></label>
                                    <select name="acc_bank_id" class="w-full tom-selects" id="acc_bank_id">
                                        <option value="">Please Select</option>
                                        @if($banks->count() > 0)
                                            @foreach($banks as $bnk)
                                                <option value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-acc_bank_id text-danger mt-2"></div>
                                </div>
                                <div class="mt-3 bankDetailsWrap" style="display: none;"></div>
                            </div>
                            <div class="col-span-12">
                                <table class="table table-sm table-bordered padding-less requisitionItemsTable">
                                    <tbody>
                                        <tr class="row">
                                            <td>Number of Students</td>
                                            <td class="w-[160px] relative text-right studentsCounts font-medium">
                                                
                                            </td>
                                        </tr>
                                        <tr class="row">
                                            <td>Claim Amount</td>
                                            <td class="w-[160px] relative text-right totalAmount font-medium">
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="acc__input-error error-claim_amount text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addClaim" class="btn btn-primary w-auto">     
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
                        <input type="hidden" id="student_ids" name="student_ids" value=""/>
                        <input type="hidden" id="slc_installment_ids" name="slc_installment_ids" value=""/>
                        <input type="hidden" id="semester_id" name="semester_id" value=""/>
                        <input type="hidden" id="course_id" name="course_id" value=""/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

    <!-- BEGIN: Add Vendor Modal -->
    <div id="addBudgetVendorModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addBudgetVendorForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Vendor</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="text" name="email" class="form-control w-full">
                            <div class="acc__input-error error-email text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input id="phone" type="text" name="phone" class="form-control w-full">
                            <div class="acc__input-error error-phone text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control w-full" rows="3"></textarea>
                            <div class="acc__input-error error-address text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                            <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveVenBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="modal_id" value=""/>
                        <input type="hidden" name="vendor_for" value="2"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Vendor Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" data-redirect="NONE" class="btn btn-primary successCloser w-24">Ok</button>
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
                        <button data-phase="" type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle">Oops!</div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">OK, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-university-invoice.js')
@endsection
