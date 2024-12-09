@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium truncate mr-5">Requisition Details</h2>
        <div class="ml-auto inline-flex justify-end">
            <a href="{{ route('budget.management') }}" class="btn btn-primary w-auto">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Budget Management
            </a>      
            @if(($requisition->first_approver == auth()->user()->id && $requisition->active == 1) || ($requisition->final_approver == auth()->user()->id && $requisition->active == 2) || $requisition->active == 0)
            <div class="dropdown ml-2">
                <button class="dropdown-toggle btn btn-success text-white" aria-expanded="false" data-tw-toggle="dropdown">
                    <i data-lucide="settings" class="w-4 h-4 mr-2"></i> 
                    Actions
                    <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                </button>
                <div class="dropdown-menu w-48">
                    <ul class="dropdown-content">
                        @if($requisition->active == 1 && $requisition->first_approver == auth()->user()->id)
                        <li>
                            <a href="javascript:void(0);" data-active="2" data-id="{{ $requisition->id }}" class="statusUpdater dropdown-item text-success">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Approved
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-active="0" data-id="{{ $requisition->id }}" class="statusUpdater dropdown-item text-danger">
                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Cancelled
                            </a>
                        </li>
                        @elseif($requisition->active == 2 && $requisition->final_approver == auth()->user()->id)
                        <li>
                            <a href="javascript:void(0);" data-active="3" data-id="{{ $requisition->id }}" class="statusUpdater dropdown-item text-success">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Approved
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0);" data-active="0" data-id="{{ $requisition->id }}" class="statusUpdater dropdown-item text-danger">
                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Cancelled
                            </a>
                        </li>
                        @elseif($requisition->active == 0)
                        <li>
                            <a href="javascript:void(0);" data-active="1" data-id="{{ $requisition->id }}" class="statusUpdater dropdown-item text-success">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Active
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif
            @if($requisition->active == 3)
                <button data-tw-toggle="modal" data-tw-target="#markRequisitionModal" type="button" class="ml-2 btn btn-linkedin text-white">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Mark as Complete
                </button>
            @endif
            @if($requisition->active == 4)
                <button type="button" class="ml-2 btn btn-success text-white">
                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Completed
                </button>
            @endif
        </div>
    </div>

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Requisition</div>
            </div>
            <div class="col-span-6 text-right">
                
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Budget Year</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->year->title) && !empty($requisition->year->title) ? $requisition->year->title : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Budget Source</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->budget->names->name) && !empty($requisition->budget->names->name) ? $requisition->budget->names->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Requisitioner</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->requisitioners->employee->full_name) && !empty($requisition->requisitioners->employee->full_name) ? $requisition->requisitioners->employee->full_name : $requisition->requisitioners->name) }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Date</div>
                        <div class="col-span-8 font-medium">{{ (!empty($requisition->date) ? date('jS M, Y', strtotime($requisition->date)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Required By</div>
                        <div class="col-span-8 font-medium">{{ (!empty($requisition->required_by) ? date('jS M, Y', strtotime($requisition->required_by)) : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">First Approver</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->fapprover->employee->full_name) && !empty($requisition->fapprover->employee->full_name) ? $requisition->fapprover->employee->full_name : $requisition->fapprover->name) }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Final Approver</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->lapprover->employee->full_name) && !empty($requisition->lapprover->employee->full_name) ? $requisition->lapprover->employee->full_name : $requisition->lapprover->name) }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Delivery Location</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->venue->name) && !empty($requisition->venue->name) ? $requisition->venue->name : '') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Vendor Details</div>
            </div>
            <div class="col-span-6 text-right">
                
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->vendor->name) && !empty($requisition->vendor->name) ? $requisition->vendor->name : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->vendor->email) && !empty($requisition->vendor->email) ? $requisition->vendor->email : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Phone</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->vendor->phone) && !empty($requisition->vendor->phone) ? $requisition->vendor->phone : '---') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Address</div>
                        <div class="col-span-8 font-medium">{{ (isset($requisition->vendor->address) && !empty($requisition->vendor->address) ? $requisition->vendor->address : '---') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Items</div>
            </div>
            <div class="col-span-6 text-right">
                <button data-tw-toggle="modal" data-tw-target="#addRequisitionItemModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Add Item
                </button>
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <div id="tabulatorFilterForm-RI" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-RI" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-RI" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-RI" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-RI" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </div>
                <div class="flex mt-5 sm:mt-0">
                    
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="requisitionItemListTable" data-requisition="{{ $requisition->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

    @if($requisition->active == 4 && (isset($requisition->transactions) && $requisition->transactions->count() > 0))
    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Transactions</div>
            </div>
            <div class="col-span-6 text-right">
                {{--<button data-tw-toggle="modal" data-tw-target="#addRequisitionItemModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Add Item
                </button>--}}
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="requisitionTransListTable" data-requisition="{{ $requisition->id }}" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    @endif

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Documents</div>
            </div>
            <div class="col-span-6 text-right">
                <button data-tw-toggle="modal" data-tw-target="#addRequisitionDocModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Add Document
                </button>
            </div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <div id="tabulatorFilterForm-RD" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-RD" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-RD" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-RD" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-RD" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </div>
                <div class="flex mt-5 sm:mt-0">
                    
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="requisitionDocListTable" data-requisition="{{ $requisition->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>


    <!-- BEGIN: Mark as Complete Modal -->
    <div id="markRequisitionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="markRequisitionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Item</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <div class="autoCompleteField" data-table="students">
                                <input type="text" autocomplete="off" id="transaction_no" name="transaction_no" class="form-control" value="" placeholder="TC000001"/>
                                <ul class="autoFillDropdown"></ul>
                            </div>
                        </div>
                        <div class="mt-5">
                            <table class="table table-sm table-bordered transactionsTable">
                                <thead>
                                    <tr>
                                        <th>TC No.</th>
                                        <th>Details</th>
                                        <th>Category</th>
                                        <th>Storage</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="initRow">
                                        <td colspan="5">
                                            <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Transaction not found!
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="markCompBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="budget_requisition_id" value="{{ $requisition->id }}"/>
                        <input type="hidden" name="total_balance" value="{{ (isset($requisition->items) && $requisition->items->count() > 0 ? $requisition->items->sum('total') : '0') }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Mark as Complete Modal -->

    <!-- BEGIN: Description Show Modal -->
    <div id="descriptionShowHideModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Description Show Modal -->

    <!-- BEGIN: Edit Requisition Modal -->
    <div id="editRequisitionItemModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editRequisitionItemForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Item</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_description" class="form-label">Description <span class="text-danger">*</span></label>
                            <input id="edit_description" type="text" name="description" class="form-control w-full">
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input id="edit_quantity" type="number" step="1" name="quantity" class="form-control w-full">
                            <div class="acc__input-error error-quantity text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input id="edit_price" type="number" step="any" name="price" class="form-control w-full">
                            <div class="acc__input-error error-price text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_total" class="form-label">total <span class="text-danger">*</span></label>
                            <input readonly id="edit_Total" type="number" step="any" name="total" class="form-control w-full">
                            <div class="acc__input-error error-address text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateItemBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="budget_requisition_id" value="{{ $requisition->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Requisition Modal -->

    <!-- BEGIN: Add Requisition Modal -->
    <div id="addRequisitionItemModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addRequisitionItemForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Item</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                            <input id="description" type="text" name="description" class="form-control w-full">
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input id="quantity" type="number" step="1" name="quantity" class="form-control w-full">
                            <div class="acc__input-error error-quantity text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="price" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input id="price" type="number" step="any" name="price" class="form-control w-full">
                            <div class="acc__input-error error-price text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="total" class="form-label">total <span class="text-danger">*</span></label>
                            <input readonly id="Total" type="number" step="any" name="total" class="form-control w-full">
                            <div class="acc__input-error error-address text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveItemBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="budget_requisition_id" value="{{ $requisition->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Requisition Modal -->

    <!-- BEGIN: Add Document Modal -->
    <div id="addRequisitionDocModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addRequisitionDocForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Document</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="display_file_name" class="form-label">Name</label>
                            <input id="display_file_name" type="text" name="display_file_name" class="form-control w-full">
                            <div class="acc__input-error error-display_file_name text-danger mt-2"></div>
                        </div>
                        <div class="flex justify-start items-start relative mt-5">
                            <label for="addRequiDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="addRequiDocument"/>
                            <div id="addRequiDocumentName" class="documentNoteName ml-5"></div>
                        </div>
                        <div class="acc__input-error error-document text-danger mt-2"></div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveDocBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="budget_requisition_id" value="{{ $requisition->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Document Modal -->

    <!-- BEGIN: Success Reloader Modal Content -->
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
                        <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
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
                        <i data-lucide="octagon-alert" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-danger w-24">Ok</button>
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
                        <button data-phase="" type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/budget-requisition-show.js')
    @vite('resources/js/budget-requisition-item.js')
    @vite('resources/js/budget-requisition-document.js')
@endsection
