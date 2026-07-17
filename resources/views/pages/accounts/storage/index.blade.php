@extends('../layout/' . $layout)

@section('body_class', 'accounts-shell-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        // Resolved once here and handed to the sidebar partial: reading
        // AccBank::$balance costs 4 aggregate queries per bank and is uncached.
        $accBankBalances = [];
        if(!empty($banks)):
            foreach($banks as $bnk):
                $accBankBalances[$bnk->id] = $bnk->balance;
            endforeach;
        endif;
        $accCurrentBalance = (array_key_exists($bank->id, $accBankBalances) ? $accBankBalances[$bank->id] : $bank->balance);
        $accCanManage = (auth()->user()->remote_access && isset(auth()->user()->priv()['access_account_type']) && in_array(auth()->user()->priv()['access_account_type'], [1, 3]));
    @endphp

    <div class="accounts-shell accounts-storage-redesign">
        <div class="acc-shell__layout">
            <!-- BEGIN: Accounts Menu -->
            <aside class="acc-shell__aside">
                @include('pages.accounts.sidebar', ['redesign' => true, 'bankBalances' => $accBankBalances])
            </aside>
            <!-- END: Accounts Menu -->

            <div class="acc-shell__main">
                @if(Session::has('csv_error'))
                    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> {!! Session::get('csv_error') !!}
                    </div>
                @endif

                <!-- BEGIN: Toolbar -->
                <div class="acc-st__toolbar">
                    @if($accCanManage)
                    <button data-tw-toggle="modal" data-tw-target="#uploadCSVModal" title="Upload CSV" class="acc-st__action">
                        <i data-lucide="hard-drive-upload"></i>
                    </button>
                    <button id="addTransactionToggle" title="Add Transaction" class="acc-st__action acc-st__action--primary addTransactionToggle">
                        <i data-lucide="plus" class="thePlus"></i>
                        <i data-lucide="minus" class="theMinus"></i>
                    </button>
                    @endif
                    <div class="acc-st__identity">
                        @if(!$bank->hasImage())
                            <div class="acc-st__mono" style="background: {{ $bank->monogram_color }};">{{ $bank->monogram }}</div>
                        @else
                            <div class="acc-st__logo"><img alt="{{ $bank->bank_name }}" src="{{ $bank->image_url }}"></div>
                        @endif
                        <div>
                            <h2 class="acc-st__name">
                                {{ $bank->bank_name }}
                                @if($accCanManage)
                                {!! ($csf_trans > 0 && isset($csv_file->id) && $csv_file->id > 0 ? '<a href="'.route('accounts.csv.transactions', [$bank->id, $csv_file->id]).'">('.$csf_trans.')</a>' : '') !!}
                                @endif
                            </h2>
                            <div class="acc-st__balance {{ ($accCurrentBalance < 0 ? 'acc-st__balance--negative' : '') }}">
                                Balance <strong>{{ ($accCurrentBalance >= 0 ? '£'.number_format($accCurrentBalance, 2) : '-£'.number_format(abs($accCurrentBalance), 2)) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="acc-st__tools">
                        <div class="acc-st__search">
                            <i data-lucide="search" class="acc-st__search-icon"></i>
                            <input id="searchTransaction" name="query" type="text" class="form-control" placeholder="Search transactions...">
                        </div>
                        <button style="display: none;" id="storageExportBtn" type="button" class="acc-st__export">Export</button>
                        <input type="hidden" id="export_storage_id" name="export_storage_id" value="{{ $bank->id }}"/>
                    </div>
                </div>
                <!-- END: Toolbar -->

                <form method="post" action="#" id="storageTransactionForm" class="acc-st__form" enctype="multipart/form-data" style="display: none;">
                <div class="acc-st__form-card">
                    <div class="acc-st__form-head">
                        <span class="acc-st__form-badge"><i data-lucide="plus"></i></span>
                        <div class="acc-st__form-title">New transaction</div>
                        <span class="acc-st__form-sub">· {{ $bank->bank_name }}</span>
                        <button type="button" class="acc-st__form-dismiss acc-st__form-x" title="Close">
                            <i data-lucide="x"></i>
                        </button>
                    </div>

                    <div class="acc-st__form-body">
                        <div class="acc-st__form-row acc-st__form-row--top">
                            <div class="acc-st__field">
                                <label class="acc-st__label" for="transaction_date">Date</label>
                                <input type="text" placeholder="DD-MM-YYYY" data-today="{{ date('d-m-Y') }}" value="{{ date('d-m-Y') }}" class="w-full form-control datepicker" id="transaction_date" name="transaction_date" data-format="DD-MM-YYYY" data-single-mode="true" />
                            </div>
                            <div class="acc-st__field">
                                <label class="acc-st__label" for="detail">Details</label>
                                <input type="text" placeholder="Payee, reference or note..." class="w-full form-control" id="detail" name="detail" />
                            </div>
                            <div class="acc-st__amounts">
                                <div class="acc-st__field">
                                    <label class="acc-st__label acc-st__label--out" for="expense">Withdrawal</label>
                                    <div class="acc-st__money">
                                        <span class="acc-st__money-sign">£</span>
                                        <input type="number" step="any" placeholder="0.00" id="expense" name="expense" class="form-control w-full text-right"/>
                                    </div>
                                </div>
                                <div class="acc-st__field">
                                    <label class="acc-st__label acc-st__label--in" for="income">Deposit</label>
                                    <div class="acc-st__money">
                                        <span class="acc-st__money-sign">£</span>
                                        <input type="number" step="any" placeholder="0.00" id="income" name="income" class="form-control w-full text-right"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="acc-st__form-row acc-st__form-row--mid">
                            <div class="acc-st__field">
                                <label class="acc-st__label" for="trans_type">Type</label>
                                <select class="w-full form-control" id="trans_type" name="trans_type">
                                    <option value="0">Income</option>
                                    <option value="1">Expense</option>
                                    <option value="2">Transfer</option>
                                </select>
                            </div>
                            <div class="acc-st__field">
                                <label class="acc-st__label">Category</label>
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
                            <div class="acc-st__field">
                                <label class="acc-st__label" for="invoice_no">Invoice no.</label>
                                <input type="text" placeholder="INV0001" class="w-full form-control" id="invoice_no" name="invoice_no" />
                            </div>
                            <div class="acc-st__field">
                                <label class="acc-st__label" for="invoice_date">Invoice date</label>
                                <input type="text" placeholder="DD-MM-YYYY" class="w-full form-control datepicker" id="invoice_date" name="invoice_date" data-format="DD-MM-YYYY" data-single-mode="true" />
                            </div>
                        </div>

                        <div class="acc-st__field" @if($is_auditor) style="display: none;" @endif>
                            <label class="acc-st__label" for="description">Description</label>
                            <input type="{{ ($is_auditor ? 'hidden' : 'text') }}" class="w-full form-control" id="description" name="description" placeholder="Optional notes about this transaction..."/>
                        </div>

                        <div class="acc-st__form-actions">
                            <div class="acc-st__check" style="{{ ($is_auditor ? 'opacity: 0; visibility: hidden;' : '') }}">
                                <input id="audit_status" checked class="form-check-input" name="audit_status" type="checkbox" value="1">
                                <label for="audit_status" class="acc-st__check-label">Audited</label>
                            </div>
                            <input type="hidden" name="storage_id" value="{{ $bank->id }}"/>

                            <div class="acc-st__form-actions-end">
                                <div class="acc-st__icon-wrap">
                                    <input type="checkbox" id="is_assets" name="is_assets" value="1" class="absolute l-0 t-0 w-0 h-0 opacity-0 invisible" />
                                    <label for="is_assets" class="assetsChecker acc-st__icon-btn" title="Register as asset">
                                        <i data-lucide="package-plus" class="unCheckedIcon"></i>
                                        <i data-lucide="package-check" class="checkedIcon"></i>
                                    </label>
                                </div>
                                <input type="file" name="document" id="transaction_document" value="" style="opacity: 0; visibility: hidden; width: 0; height: 0; position: absolute;"/>
                                <label for="transaction_document" class="acc-st__icon-btn" title="Upload receipt"><i data-lucide="hard-drive-upload"></i></label>
                                <button type="button" class="acc-st__form-dismiss acc-st__btn-cancel">Cancel</button>
                                <button type="submit" id="storeTransaction" class="acc-st__btn-save">
                                    <i data-lucide="check"></i>
                                    Save
                                    <svg class="acc-st__spinner" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
                                <button data-id="0" style="display: none;" type="button" id="deleteTransaction" class="acc-st__btn-delete">
                                    <i data-lucide="trash-2"></i>
                                </button>
                                @endif
                                <input type="hidden" id="transaction_id" name="transaction_id" value="0"/>
                            </div>
                        </div>
                    </div>
                </div>
                </form>

                <!-- BEGIN: Transactions -->
                <div class="acc-st__card">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="storageTransList" data-auditor="{{ $is_auditor }}" data-storage="{{ $bank->id }}" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
                <!-- END: Transactions -->
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
    <div id="uploadCSVModal" class="modal acc-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('accounts.csv.store') }}" id="uploadCSVForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="acc-modal__title mr-auto">Upload CSV</h2>
                        <a data-tw-dismiss="modal" class="acc-modal__x" href="javascript:;">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="relative">
                            <label for="csv_doc" class="acc-modal__label">CSV <span class="text-danger">*</span></label>
                            <div class="acc-modal__drop">
                                <span class="acc-modal__drop-icon"><i data-lucide="file-down"></i></span>
                                <div class="acc-modal__drop-body">
                                    <input type="file" id="csv_doc" name="csv_doc" value="" accept=".csv">
                                    <div class="acc-modal__hint">Accepts .csv exported from your bank</div>
                                </div>
                            </div>
                            <div class="acc__input-error error-csv_doc text-danger mt-2"></div>
                        </div>
                        <div class="acc-modal__switch">
                            <div class="form-check form-switch">
                                <input id="has_cto_receipts" name="has_cto_receipts" class="form-check-input" type="checkbox" value="1">
                                <label class="form-check-label" for="has_cto_receipts">COT Receipts Upload</label>
                            </div>
                            <div class="acc-modal__hint">Attach Capital On Tap receipt files alongside the CSV</div>
                        </div>
                        <div class="mt-3 relative cto_receipts_wrap" style="display: none;">
                            <label for="cto_receipts" class="acc-modal__label">Receipts</label>
                            <div class="acc-modal__drop">
                                <span class="acc-modal__drop-icon"><i data-lucide="paperclip"></i></span>
                                <div class="acc-modal__drop-body">
                                    <input type="file" id="cto_receipts" name="cto_receipts[]" multiple value="" accept=".pdf">
                                </div>
                            </div>
                            <div class="acc__input-error error-cto_receipts text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="acc-modal__btn-cancel">Cancel</button>
                        <button type="submit" id="editEmailSet" class="acc-modal__btn-submit">
                            <i data-lucide="hard-drive-upload"></i>
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
