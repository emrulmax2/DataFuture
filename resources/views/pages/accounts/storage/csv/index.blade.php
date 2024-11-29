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
            <div class="intro-y box mt-3">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">CSV Upload {!! (isset($bank->bank_name) && !empty($bank->bank_name) ? '<u>('.$bank->bank_name.')</u>' : '') !!}</h2>
                    <div class="dropdown" id="processDropdown">
                        <button class="dropdown-toggle btn btn-primary" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i>  {{ isset($csv_file->name) && !empty($csv_file->name) ? $csv_file->name : 'Select CSV File' }} <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                        <div class="dropdown-menu w-72">
                            <ul class="dropdown-content">
                                <li><h6 class="dropdown-header">Available Files</h6></li>
                                <li><hr class="dropdown-divider mt-0"></li>
                                @if($csv_files->count() > 0)
                                    @foreach($csv_files as $file)
                                    <li>
                                        <a href="{{ route('accounts.csv.transactions', [$bank->id, $file->id] ) }}" class="dropdown-item">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> {{ $file->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                @else 
                                    <li>
                                        <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                                            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Files not available.
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="p-5">
                    @if($csv_file_id > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-bordered table-xsm" id="csvTransTable">
                            <thead>
                                <tr>
                                    <th colspan="10" class="text-right">{{ (!empty($csv_transactions) && $csv_transactions->count() > 0) ? $csv_transactions->count().' Rows Found' : '0 Rows Found' }}</th>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Inv. Date</th>
                                    <th>Details</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th class="text-right">Withdrawl</th>
                                    <th class="text-right">Deposit</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($csv_transactions) && $csv_transactions->count() > 0)
                                    @foreach($csv_transactions as $trns)
                                        <tr data-fileid="{{ $csv_file_id }}" data-transid="{{ $trns->id }}" class="transaction_row {{ $trns->transaction_type == 1 ? 'bg-danger-soft' : 'bg-success-soft' }}" id="transaction_row_{{ $trns->id }}">
                                            <td>
                                                <select name="trans_{{ $csv_file_id }}_{{ $trns->id }}_transactiontype" class="w-28 form-control transaction_type">
                                                    <option {{ ($trns->transaction_type == 0 ? 'selected' : '') }} value="0">Income</option>
                                                    <option {{ ($trns->transaction_type == 1 ? 'selected' : '') }} value="1">Expense</option>
                                                    <option {{ ($trns->transaction_type == 2 ? 'selected' : '') }} value="2">Transfer</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_transdate" class="form-control w-24 datepicker" value="{{ (!empty($trns->trans_date) ? date('d-m-Y', strtotime($trns->trans_date)) : '') }}" data-date-format="DD-MM-YYYY" data-single-mode="true"/></td>
                                            <td><input type="text" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_invoiceno" class="form-control w-24" value=""/></td>
                                            <td><input type="text" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_invoicedate" class="form-control w-24 datepicker" value=""  data-date-format="DD-MM-YYYY" data-single-mode="true"/></td>
                                            <td><textarea name="trans_{{ $csv_file_id }}_{{ $trns->id }}_detail" class="form-control w-full" rows="1">{{ $trns->description }}</textarea></td>
                                            <td><textarea name="trans_{{ $csv_file_id }}_{{ $trns->id }}_description" class="form-control w-full" rows="1"></textarea></td>
                                            <td>
                                                <div class="inTomWrap" style="display: {{ $trns->transaction_type == 0 ? 'block' : 'none' }};">
                                                    <select name="trans_{{ $csv_file_id }}_{{ $trns->id }}_inccategory" class="w-48 csvInToms tom-selects inc_category" >
                                                        <option value="">Category</option>
                                                        @if(!empty($inCategories))
                                                            @foreach($inCategories as $cat)
                                                                <option {{ (isset($cat['disabled']) && $cat['disabled'] == 1 ? 'disabled' : '') }} value="{{ $cat['id'] }}">{!! $cat['category_name'] !!}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="outTomWrap" style="display: {{ $trns->transaction_type == 1 ? 'block' : 'none' }};">
                                                    <select name="trans_{{ $csv_file_id }}_{{ $trns->id }}_expcategory" class="w-48 csvOutToms tom-selects exp_category" >
                                                        <option value="">Category</option>
                                                        @if(!empty($outCategories))
                                                            @foreach($outCategories as $cat)
                                                                <option {{ (isset($cat['disabled']) && $cat['disabled'] == 1 ? 'disabled' : '') }} value="{{ $cat['id'] }}">{!! $cat['category_name'] !!}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div class="strgTomWrap" style="display: {{ $trns->transaction_type == 2 ? 'block' : 'none' }};">
                                                    <select name="trans_{{ $csv_file_id }}_{{ $trns->id }}_transstorage" class="w-48 csvStrToms tom-selects trans_storage" >
                                                        <option value="">Storage</option>
                                                        @if(!empty($banks))
                                                            @foreach($banks as $bnk)
                                                                <option {{ ($bank->id == $bnk->id ? 'disabled' : '') }} value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-right"><input type="number" step="any" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_expense" class="w-24 form-control rowExpense text-right" value="{{ ($trns->flow == 1 ? $trns->amount : '') }}"/></td>
                                            <td class="text-right"><input type="number" step="any" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_income" class="w-24 form-control rowIncome text-right" value="{{ ($trns->flow == 0 ? $trns->amount : '') }}"/></td>
                                            <td>
                                                <div class="flex justify-end items-center relative">
                                                    <div class="form-check inline-flex mr-2">
                                                        <input checked class="form-check-input audit_status" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_auditstatus" type="checkbox" value="1">
                                                    </div>
                                                    <label for="trans_up_{{$trns->id}}" class="btn btn-linkedin btn-sm"><i data-lucide="hard-drive-upload" class="w-4 h-4"></i></label>
                                                    <input type="file" id="trans_up_{{$trns->id}}" name="trans_{{ $csv_file_id }}_{{ $trns->id }}_doument" style="opacity: 0; visibility: hidden; width: 0; height: 0; position: absolute;"/>
                                                    <button data-file-id="{{ $trns->acc_csv_file_id }}" data-id="{{ $trns->id }}" style="display: none;" type="button" class="btn btn-success saveCsvTransRow text-white btn-sm ml-1"><i data-lucide="save" class="w-4 h-4"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @else 
                        <div class="alert alert-warning-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> Please select a file from dropdown.
                        </div>
                    @endif
                </div>
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
                        <button type="button" data-action="NONE" data-redirect="NONE" class="btn btn-primary successCloser w-24">Ok</button>
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
    @vite('resources/js/accounts-csv.js')
@endsection
