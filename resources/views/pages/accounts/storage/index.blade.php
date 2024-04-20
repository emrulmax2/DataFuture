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
            <div class="flex items-center">
                <h2 class="font-medium text-lg mr-auto">Transactions</h2>
                <button type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add Transaction</button>
            </div>
            <div class="intro-y box p-5 mt-5">
                <div class="grid grid-cols-12 gap-4">

                    <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                        <input type="text" placeholder="DD-MM-YYYY" value="{{ date('d-m-Y') }}" class="w-full form-control datepicker" name="transaction_date" data-format="DD-MM-YYYY" data-single-mode="true" />
                    </div>
                    <div class="col-span-12 sm:col-span-3 lg:col-span-6">
                        <input type="text" placeholder="Details" class="w-full form-control" name="detail" />
                    </div>
                    <div class="col-span-12 sm:col-span-3 lg:col-span-4">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-12 lg:col-span-6 text-right">
                                <input type="number" step="any" placeholder="Withdrawl" name="expense" class="form-control w-full text-right"/>
                            </div>
                            <div class="col-span-12 sm:col-span-12 lg:col-span-6 text-right">
                                <input type="number" step="any" placeholder="Deposit" name="income" class="form-control w-full text-right"/>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                        <select class="w-full form-control" name="trans_type">
                            <option value="0">Income</option>
                            <option value="1">Expense</option>
                            <option value="2">Transfer</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 lg:col-span-4">
                        <select class="w-full form-control" name="acc_category_id">
                            <option value="">Please Select Category</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-3 lg:col-span-2">
                        <input type="text" placeholder="INV0001" class="w-full form-control" name="invoice_no" />
                    </div>
                    <div class="col-span-12 sm:col-span-3 lg:col-span-4"></div>

                    <div class="col-span-12 sm:col-span-3 lg:col-span-2"></div>
                    <div class="col-span-12 sm:col-span-6 lg:col-span-6">
                        <input type="text" class="w-full form-control" name="description" placeholder="Descriptions"/>
                    </div>
                    
                    
                    <div class="col-span-12 sm:col-span-3 lg:col-span-4 text-right">
                        <div class="form-check inline-flex mr-5">
                            <input id="checkbox-switch-1" class="form-check-input" type="checkbox" value="">
                        </div>
                        <button type="submit" class="btn btn-success text-white w-auto">Save</button>
                    </div>
                </div>
            </div>
            <div class="intro-y box mt-5">
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-LS" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-EMAIL" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-EMAIL" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-EMAIL" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-EMAIL" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-EMAIL" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json-EMAIL" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx-EMAIL" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html-EMAIL" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="emailTemplateListTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-storage.js')
@endsection
