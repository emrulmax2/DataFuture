@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 2xl:col-span-12"> 
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Budget Management</h2>
                        <div class="ml-auto inline-flex justify-end items-center">
                            @if(auth()->user()->remote_access && isset(auth()->user()->priv()['budget_settings']) && auth()->user()->priv()['budget_settings'] == 1)
                            <a href="{{ route('budget.settings.year') }}" class="btn btn-primary w-auto">
                                <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Budget Settings
                            </a>
                            @endif
                            @if(auth()->user()->remote_access && isset(auth()->user()->priv()['budget_reports']) && auth()->user()->priv()['budget_reports'] == 1)
                            <a href="{{ route('budget.management.reports') }}" class="ml-2 btn btn-linkedin w-auto">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Budget Reports
                            </a>
                            @endif
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12">
                    <div class="intro-y box p-5 mt-5">
                        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Date</label>
                                    <input id="date_range" name="date_range" type="text" class="form-control sm:w-36 2xl:w-full mt-2 sm:mt-0"  placeholder="DD-MM-YYYY - DD-MM-YYYY">
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Year</label>
                                    <select id="budget_year_ids" name="budget_year_ids" class="tom-selects w-full mt-2 sm:mt-0 sm:w-36" >
                                        <option value="">Please Select</option>
                                        @if($years->count() > 0)
                                            @foreach($years as $yr)
                                                <option value="{{ $yr->id }}">{{ $yr->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Budgets</label>
                                    <select id="budget_name_ids" name="budget_name_ids" class="tom-selects w-full mt-2 sm:mt-0 sm:w-48" >
                                        <option value="">Please Select</option>
                                        @if($names->count() > 0)
                                            @foreach($names as $bnm)
                                                <option value="{{ $bnm->id }}">{{ $bnm->name.(isset($bnm->code) && !empty($bnm->code) ? ' ('.$bnm->code.')' : '') }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                    <select id="req_active" name="req_active" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                        <option value="6">All</option>
                                        <option value="3">Finaly Approved</option>
                                        <option value="2">Approved</option>
                                        <option value="1">Active</option>
                                        <option value="0">Cancelled</option>
                                        <option value="4">Completed</option>
                                        <option value="5">Archived</option>
                                    </select>
                                </div>
                                <div class="mt-2 xl:mt-0">
                                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                                </div>
                            </form>
                            <div class="flex mt-5 sm:mt-0">
                                <button type="button" data-tw-toggle="modal" data-tw-target="#addRequisitionModal" class="btn btn-success text-white w-auto"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Requisition</button>
                                
                            </div>
                        </div>
                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="requisitionListTable" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{--<div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l 2xl:h-full -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6 relative">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Upcoming...</h2>
                            <a href="#" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No data found!.
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div> --}}
    </div>


    <!-- BEGIN: Add Requisition Modal -->
    <div id="addRequisitionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addRequisitionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Requisition</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-6 gap-y-3">
                            <div class="col-span-12 sm:col-span-5">
                                <h3 class="font-medium mb-4 flex justify-between items-center">Vendor <a href="javascript:void(0);" data-modal="editRequisitionModal" data-tw-toggle="modal" data-tw-target="#addBudgetVendorModal" class="add_vendor ml-auto font-medium underline inline-flex items-center text-success"><i class="w-3 h-3 mr-1" data-lucide="plus"></i> Add New</a></h3>
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
                                <div class="mt-3 vendorDetailsWrap" style="display: none;"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-7">
                                <h3 class="font-medium mb-4">Requisitioner Informations</h3>
                                <div class="grid grid-cols-12 gap-x-6 gap-y-1">
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="budget_set_detail_id" class="form-label">Budget Source <span class="text-danger">*</span></label>
                                        <select name="budget_set_detail_id" class="w-full tom-selects" id="budget_set_detail_id">
                                            <option value="">Please Select</option>
                                            @if(isset($budgets->details) && $budgets->details->count() > 0)
                                                @foreach($budgets->details as $det)
                                                    <option value="{{ $det->id }}">{{ (isset($det->names->name) && !empty($det->names->name) ? $det->names->name : 'Undefined').(isset($det->names->code) && !empty($det->names->code) ? ' ('.$det->names->code.')' : '') }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-budget_set_detail_id text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="required_by" class="form-label">Required By <span class="text-danger">*</span></label>
                                        <input id="required_by" type="text" name="required_by" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-required_by text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-12">
                                        <label for="venue_id" class="form-label">Delivery Location</label>
                                        <select name="venue_id" class="w-full tom-selects" id="venue_id">
                                            <option value="">Please Select</option>
                                            @if($venues->count() > 0)
                                                @foreach($venues as $vn)
                                                    <option value="{{ $vn->id }}">{{ $vn->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="itemsWrap mt-3">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-medium">Item Information</h3>
                                <button type="button" class="btn btn-facebook btn-sm ml-auto addReqItem"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Item</button>
                            </div>
                            <table class="table table-sm table-bordered padding-less requisitionItemsTable">
                                <thead>
                                    <tr>
                                        <th>Item Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="requisition_item_row">
                                        <td><input type="text" name="items[description][]" class="description form-control w-full"/></td>
                                        <td class="w-[160px]"><input type="number" step="1" name="items[quantity][]" class="quantity form-control w-full"/></td>
                                        <td class="w-[160px]"><input type="number" step="any" name="items[price][]" class="price form-control w-full"/></td>
                                        <td class="w-[160px] relative">
                                            <input type="number" readonly step="any" name="items[total][]" class="total form-control w-full"/>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="font-medium">Total</td>
                                        <td><input readonly type="number" step="any" name="requisition_total" class="requisition_total form-control w-full"/></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="acc__input-error error-requisition_ietems text-danger mt-2"></div>
                        </div>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-2 mt-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="first_approver" class="form-label">First Approver <span class="text-danger">*</span></label>
                                <select name="first_approver" class="w-full tom-selects" id="first_approver">
                                    <option value="">Please Select</option>
                                    @if($approvers->count() > 0)
                                        @foreach($approvers as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employe->full_name) && !empty($usr->employe->full_name) ? $usr->employe->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-first_approver text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="final_approver" class="form-label">Final Approver <span class="text-danger">*</span></label>
                                <select name="final_approver" class="w-full tom-selects" id="final_approver">
                                    <option value="">Please Select</option>
                                    @if($approvers->count() > 0)
                                        @foreach($approvers as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employe->full_name) && !empty($usr->employe->full_name) ? $usr->employe->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-final_approver text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="final_approver" class="form-label">Note</label>
                                <textarea name="note" class="form-control w-full" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-start items-start relative mt-3">
                            <label for="addRequiDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="addRequiDocument"/>
                            <div id="addRequiDocumentName" class="documentNoteName ml-5"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveReqBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="budget_set_id" value="{{ (isset($budgets->id) && $budgets->id > 0 ? $budgets->id : 0) }}"/>
                        <input type="hidden" name="budget_year_id" value="{{ (isset($budgets->budget_year_id) && $budgets->budget_year_id > 0 ? $budgets->budget_year_id : 0) }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Requisition Modal -->

    <!-- BEGIN: Edit Requisition Modal -->
    <div id="editRequisitionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editRequisitionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Requisition</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-6 gap-y-3">
                            <div class="col-span-12 sm:col-span-5">
                                <h3 class="font-medium mb-4 flex justify-between items-center">Vendor <a href="javascript:void(0);" data-modal="editRequisitionModal" data-tw-toggle="modal" data-tw-target="#addBudgetVendorModal" class="add_vendor ml-auto font-medium underline inline-flex items-center text-success"><i class="w-3 h-3 mr-1" data-lucide="plus"></i> Add New</a></h3>
                                <div>
                                    <select name="vendor_id" class="w-full tom-selects" id="edit_vendor_id">
                                        <option value="">Please Select</option>
                                        @if($vendors->count() > 0)
                                            @foreach($vendors as $ven)
                                                <option value="{{ $ven->id }}">{{ $ven->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-vendor_id text-danger mt-2"></div>
                                </div>
                                <div class="mt-3 vendorDetailsWrap" style="display: none;"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-7">
                                <h3 class="font-medium mb-4">Requisitioner Informations</h3>
                                <div class="grid grid-cols-12 gap-x-6 gap-y-1">
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="edit_budget_set_detail_id" class="form-label">Budget Source <span class="text-danger">*</span></label>
                                        <select name="budget_set_detail_id" class="w-full tom-selects" id="edit_budget_set_detail_id">
                                            <option value="">Please Select</option>
                                            
                                        </select>
                                        <div class="acc__input-error error-budget_set_detail_id text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-6">
                                        <label for="edit_required_by" class="form-label">Required By <span class="text-danger">*</span></label>
                                        <input id="edit_required_by" type="text" name="required_by" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-required_by text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-12">
                                        <label for="edit_venue_id" class="form-label">Delivery Location</label>
                                        <select name="venue_id" class="w-full tom-selects" id="edit_venue_id">
                                            <option value="">Please Select</option>
                                            @if($venues->count() > 0)
                                                @foreach($venues as $vn)
                                                    <option value="{{ $vn->id }}">{{ $vn->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="itemsWrap mt-3">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="font-medium">Item Information</h3>
                                <button type="button" class="btn btn-facebook btn-sm ml-auto addReqItem"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Item</button>
                            </div>
                            <table class="table table-sm table-bordered padding-less requisitionItemsTable">
                                <thead>
                                    <tr>
                                        <th>Item Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{--<tr class="requisition_item_row">
                                        <td><input type="text" name="items[description][]" class="description form-control w-full"/></td>
                                        <td class="w-[160px]"><input type="number" step="1" name="items[quantity][]" class="quantity form-control w-full"/></td>
                                        <td class="w-[160px]"><input type="number" step="any" name="items[price][]" class="price form-control w-full"/></td>
                                        <td class="w-[160px] relative">
                                            <input type="number" step="any" name="items[total][]" class="total form-control w-full"/>
                                        </td>
                                    </tr>--}}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="font-medium">Total</td>
                                        <td><input readonly type="number" step="any" name="requisition_total" class="requisition_total form-control w-full"/></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="acc__input-error error-requisition_ietems text-danger mt-2"></div>
                        </div>
                        <div class="grid grid-cols-12 gap-x-6 gap-y-2 mt-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_first_approver" class="form-label">First Approver <span class="text-danger">*</span></label>
                                <select name="first_approver" class="w-full tom-selects" id="edit_first_approver">
                                    <option value="">Please Select</option>
                                    @if($approvers->count() > 0)
                                        @foreach($approvers as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employe->full_name) && !empty($usr->employe->full_name) ? $usr->employe->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-first_approver text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="edit_final_approver" class="form-label">Final Approver <span class="text-danger">*</span></label>
                                <select name="final_approver" class="w-full tom-selects" id="edit_final_approver">
                                    <option value="">Please Select</option>
                                    @if($approvers->count() > 0)
                                        @foreach($approvers as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employe->full_name) && !empty($usr->employe->full_name) ? $usr->employe->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-final_approver text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="final_approver" class="form-label">Note</label>
                                <textarea name="note" class="form-control w-full" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-start items-start relative mt-3">
                            <label for="editRequiDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="editRequiDocument"/>
                            <div id="editRequiDocumentName" class="documentNoteName ml-5"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateReqBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Requisition Modal -->

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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Vendor Modal -->
    
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
                        <button data-phase="" type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/budget-management.js')
@endsection
