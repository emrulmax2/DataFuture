@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-3 2xl:col-span-3 relative z-10">
            <!-- BEGIN: Profile Info -->
            @include('pages.accounts.sidebar')
            <!-- END: Profile Info -->
        </div>
        <div class="col-span-12 xl:col-span-9 2xl:col-span-9 z-10 pt-6">
            <div class="intro-y box mt-2">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto"><strong><u>{{ $category->category_name }}</u></strong> Report from <strong><u>{{ date('jS F, Y', strtotime($startDate)) }}</u></strong> to <strong><u>{{ date('jS F, Y', strtotime($endDate)) }}</u></strong></h2>
                    <div class="ml-auto inline-flex justify-end">
                        <a href="{{ route('accounts.management.report', [$startDate, $endDate]) }}" class="add_btn btn btn-primary shadow-md">Back To Report</a>
                        <div class="dropdown w-auto ml-2">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export or Print <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-48">
                                <ul class="dropdown-content">
                                    <li>
                                        <a href="{{ route('accounts.management.report.export.details', [$startDate, $endDate, $category->id]) }}" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('accounts.management.report.print.details', [$startDate, $endDate, $category->id]) }}" class="dropdown-item">
                                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box mt-5 p-5 editTransactionFormWrap" id="editTransactionFormWrap" style="display: none;">
                <form method="post" action="#" id="storageTransactionForm" enctype="multipart/form-data">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                            <input type="text" placeholder="DD-MM-YYYY" data-today="{{ date('d-m-Y') }}" value="{{ date('d-m-Y') }}" class="w-full form-control datepicker" id="transaction_date" name="transaction_date" data-format="DD-MM-YYYY" data-single-mode="true" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 lg:col-span-6">
                            <input type="text" placeholder="Details" class="w-full form-control" id="detail" name="detail" />
                        </div>
                        <div class="col-span-12 sm:col-span-3 lg:col-span-4">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 sm:col-span-12 lg:col-span-6 text-right">
                                    <input type="number" step="any" placeholder="Withdrawl" id="expense" name="expense" class="form-control w-full text-right"/>
                                </div>
                                <div class="col-span-12 sm:col-span-12 lg:col-span-6 text-right">
                                    <input type="number" step="any" placeholder="Deposit" id="income" name="income" class="form-control w-full text-right"/>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                            <select class="w-full form-control" id="trans_type" name="trans_type">
                                <option value="0">Income</option>
                                <option value="1">Expense</option>
                                <option value="2">Transfer</option>
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3 lg:col-span-3">
                            <div id="acc_category_id_in_wrap">
                                <select class="w-full tom-selects" id="acc_category_id_in" name="acc_category_id_in">
                                    <option value="">Please Select Category</option>
                                    @if(!empty($in_categories))
                                        @foreach($in_categories as $cat)
                                            <option {{ (isset($cat['disabled']) && $cat['disabled'] == 1 ? 'disabled' : '') }} value="{{ $cat['id'] }}">{!! $cat['category_name'] !!}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div id="acc_category_id_out_wrap" style="display: none;">
                                <select class="w-full  tom-selects" id="acc_category_id_out" name="acc_category_id_out">
                                    <option value="">Please Select Category</option>
                                    @if(!empty($out_categories))
                                        @foreach($out_categories as $cat)
                                            <option {{ (isset($cat['disabled']) && $cat['disabled'] == 1 ? 'disabled' : '') }} value="{{ $cat['id'] }}">{!! $cat['category_name'] !!}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div id="acc_bank_id_wrap" style="display: none;">
                                <select class="w-full tom-selects" id="acc_bank_id" name="acc_bank_id">
                                    <option value="">Please Select Storage</option>
                                    @if(!empty($banks))
                                        @foreach($banks as $bnk)
                                            <option value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3 lg:col-span-3">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 sm:col-span-6">
                                    <input type="text" placeholder="INV0001" class="w-full form-control" id="invoice_no" name="invoice_no" />
                                </div>
                                <div class="col-span-12 sm:col-span-6">
                                    <input type="text" placeholder="DD-MM-YYYY" class="w-full form-control datepicker" id="invoice_date" name="invoice_date" data-format="DD-MM-YYYY" data-single-mode="true" />
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3 lg:col-span-4"></div>

                        <div class="col-span-12 sm:col-span-3 lg:col-span-2"></div>
                        <div class="col-span-12 sm:col-span-6 lg:col-span-6">
                            <input type="{{ ($is_auditor ? 'hidden' : 'text') }}" class="w-full form-control" id="description" name="description" placeholder="Descriptions"/>
                        </div>
                        
                        
                        <div class="col-span-12 sm:col-span-3 lg:col-span-4 text-right flex items-center justify-end">
                            <div class="form-check inline-flex mr-3" style="{{ ($is_auditor ? 'opacity: 0; visibility: hidden;' : '') }}">
                                <input id="audit_status" checked class="form-check-input" name="audit_status" type="checkbox" value="1">
                            </div>
                            <input type="hidden" id="storage_id" name="storage_id" value="0"/>

                            <div class="inline-flex mr-2 relative">
                                <input type="checkbox" id="is_assets" name="is_assets" value="1" class="absolute l-0 t-0 w-0 h-0 opacity-0 invisible" />
                                <label for="is_assets" class="assetsChecker cursor-pointer btn btn-outline-secondary h-[38px] text-success">
                                    <i data-lucide="package-plus" class="w-5 h-5 unCheckedIcon"></i>
                                    <i data-lucide="package-check" class="w-5 h-5 checkedIcon"></i>
                                </label>
                            </div>
                            <input type="file" name="document" id="transaction_document" value="" style="opacity: 0; visibility: hidden; width: 0; height: 0; position: absolute;"/>
                            <label for="transaction_document" class="btn btn-primary text-white w-auto mr-2"><i data-lucide="hard-drive-upload" class="w-4 h-4"></i></label>
                            <button type="submit" id="storeTransaction" class="btn btn-success text-white w-auto px-4">     
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
                            <button type="button" id="cancelEdit" class="btn btn-warning text-white w-auto ml-2"> 
                                <i class="w-4 h-4 mr-2" data-lucide="x-circle"></i> Cancel
                            </button>
                            <input type="hidden" id="transaction_id" name="transaction_id" value="0"/>
                        </div>
                    </div>
                </form>
            </div>

            <div class="intro-y box mt-5 p-5">
                @if($transactions->count() > 0)
                    <table class="table table-bordered table-striped table-sm" id="transactionListTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>TC</th>
                                <th>Invoice</th>
                                <th>Storage</th>
                                <th>Details</th>
                                @if(!$is_auditor)
                                <th>Description</th>
                                @endif
                                <th class="text-right">Withdrawl</th>
                                <th class="text-right">Deposit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $subTotal = 0;
                            @endphp
                            @foreach($transactions as $trns)
                                <tr>
                                    <td class="font-medium {{ $trns->audit_status != 1 ? 'text-danger' : 'text-success' }}">
                                        @if($can_edit) <a data-id="{{ $trns->id }}" href="javascript:void(0);" class="editTransaction underline"> @endif
                                            {{ date('jS F, Y', strtotime($trns->transaction_date_2)) }}
                                        @if($can_edit) </a> @endif
                                    </td>
                                    <td>
                                        <div class="flex justify-start items-center">
                                            @if(isset($trns->transaction_doc_name) && $trns->transaction_doc_name != '')
                                                <a data-id="{{ $trns->id }}" href="javascript:void(0);" target="_blank" class="downloadTransDoc text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>
                                            @endif
                                            @if(isset($trns->assets->id) && $trns->assets->id > 0)
                                                <span class="text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="package-check" class="w-4 h-4"></i></span>
                                            @endif
                                            {{ $trns->transaction_code }}
                                            @if(isset($trns->receipts) && $trns->receipts->count() > 0)
                                                <a target="_blank" href="{{ route('reports.accounts.transaction.connection', $trns->id) }}" class="text-success ml-2" style="position: relative; top: -1px;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg></a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{!! $trns->invoice_no.(!empty($trns->invoice_date) ? '<br/>'.date('jS M, Y', strtotime($trns->invoice_date)) : '') !!}</td>
                                    <td>{{ (isset($trns->bank->bank_name) ? $trns->bank->bank_name : '') }}</td>
                                    <td>{{ $trns->detail }}</td>
                                    @if(!$is_auditor)
                                    <td>{{ $trns->description }}</td>
                                    @endif
                                    <td class="text-right">{{ ($trns->flow == 1 ? '£'.number_format($trns->transaction_amount, 2) : '') }}</td>
                                    <td class="text-right">{{ ($trns->flow != 1 ? '£'.number_format($trns->transaction_amount, 2) : '') }}</td>
                                </tr>
                                @php 
                                    if($trns->flow == 1):
                                        $subTotal -= $trns->transaction_amount;
                                    else:
                                        $subTotal += $trns->transaction_amount;
                                    endif;
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="{{ ($is_auditor ? 5 : 6) }}">Total</th>
                                <th colspan="2" class="text-right">{{ ($subTotal >= 0 ? '£'.number_format($subTotal, 2) : '-£'.number_format(str_replace('-', '', $subTotal), 2)) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Transactions not found
                    </div>
                @endif
            </div>
        </div>
    </div>

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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
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
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-management-report-details.js')
@endsection
