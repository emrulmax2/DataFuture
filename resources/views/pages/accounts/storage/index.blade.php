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
            @if(Session::has('csv_error'))
                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> {!! Session::get('csv_error') !!}
                </div>
            @endif
            <div class="flex items-center">
                <div class="mr-auto inline-flex justify-start items-center">
                    @if(auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3]))
                    <button data-tw-toggle="modal" data-tw-target="#uploadCSVModal" title="Upload CSV" class="btn btn-facebook px-2 py-2 rounded-0 mr-2 text-white">
                        <i data-lucide="hard-drive-upload" class="w-4 h-4"></i>
                    </button>
                    <button id="addTransactionToggle" title="Add Transaction" class="btn btn-linkedin px-2 py-2 rounded-0 mr-3 text-white addTransactionToggle">
                        <i data-lucide="plus" class="w-4 h-4 thePlus"></i>
                        <i data-lucide="minus" class="w-4 h-4 theMinus"></i>
                    </button>
                    @endif
                    <h2 class="font-medium text-lg mr-auto">
                        <strong><u>{{ $bank->bank_name }}</u></strong> 
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3]))
                        {!! ($csf_trans > 0 && isset($csv_file->id) && $csv_file->id > 0 ? '<a href="'.route('accounts.csv.transactions', [$bank->id, $csv_file->id]).'" class="text-primary underline">('.$csf_trans.')</a>' : '') !!}
                        @endif
                    </h2>
                </div>
                <div class="ml-auto flex justify-end">
                    <input id="searchTransaction" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    <button style="display: none;" id="storageExportBtn" type="button" class="btn btn-primary text-white ml-2">Export</button>
                    <input type="hidden" id="export_storage_id" name="export_storage_id" value="{{ $bank->id }}"/>
                </div>
            </div>
            <form method="post" action="#" id="storageTransactionForm" enctype="multipart/form-data" style="display: none;">
                <div class="box p-5 mt-5">
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
                                            @if($bnk->id != $bank->id)
                                            <option value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                            @endif
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
                            <input type="text" class="w-full form-control" id="description" name="description" placeholder="Descriptions"/>
                        </div>
                        
                        
                        <div class="col-span-12 sm:col-span-3 lg:col-span-4 text-right flex items-center justify-end">
                            <div class="form-check inline-flex mr-3">
                                <input id="audit_status" checked class="form-check-input" name="audit_status" type="checkbox" value="1">
                            </div>
                            <input type="hidden" name="storage_id" value="{{ $bank->id }}"/>

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
                            @if(auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3]))
                            <button data-id="0" style="display: none;" type="button" id="deleteTransaction" class="btn btn-danger text-white w-auto ml-2"> 
                                <i class="w-4 h-4" data-lucide="trash-2"></i>
                            </button>
                            @endif
                            <input type="hidden" id="transaction_id" name="transaction_id" value="0"/>
                        </div>
                    </div>
                </div>
            </form>
            <div class="intro-y box mt-5">
                <div class="p-5">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="storageTransList" data-storage="{{ $bank->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

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
     
    <!-- BEGIN: Edit Modal -->
    <div id="uploadCSVModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('accounts.csv.store') }}" id="uploadCSVForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Upload CSV</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="relative">
                            <label for="csv_doc" class="form-label block">CSV <span class="text-danger">*</span></label>
                            <input type="file" id="csv_doc" name="csv_doc" id="csv_doc" value="" accept=".csv">
                            <div class="acc__input-error error-csv_doc text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="editEmailSet" class="btn btn-primary w-auto">     
                            Upload                      
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
                        <input type="hidden" name="acc_bank_id" value="{{ $bank->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

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
    @vite('resources/js/accounts-storage.js')
@endsection
