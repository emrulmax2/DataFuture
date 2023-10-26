@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5 pb-7">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Payment Settings</div>
                </div>
                <div class="col-span-6 text-right">
                    @if(isset($employee->payment->id) && $employee->payment->id > 0)
                        <button data-applicant="" data-tw-toggle="modal" data-tw-target="#editEmployeePaymentSettingModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                            <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Payment Settings
                        </button>
                    @else
                        <button data-applicant="" data-tw-toggle="modal" data-tw-target="#addEmployeePaymentSettingModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                            <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Add Payment Settings
                        </button>
                    @endif
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Pay Frequency</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->payment->pay_frequency) ? $employee->payment->pay_frequency : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Tax Code</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->payment->tax_code) ? $employee->payment->tax_code : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Payment Method</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->payment->payment_method) ? $employee->payment->payment_method : '') }}</div>
                    </div>
                </div>
                @if(isset($employee->payment->payment_method) && $employee->payment->payment_method == 'Bank Transfer')
                <div class="col-span-12">
                    <div class="pt-2 mb-5 border-b border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-BNK" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-BNK" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-BNK" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="0">In Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-BNK" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-BNK" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-BNK" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-BNK" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-BNK" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="employeeBankListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                    <div class="pt-5 mb-2 border-b border-slate-200/60 dark:border-darkmode-400"></div>
                </div>
                @endif

                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Subject To Clockin</div>
                        <div class="col-span-8 font-medium">{!! (isset($employee->payment->subject_to_clockin) ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                <div class="col-span-6 sm:col-span-8">
                    @if(isset($employee->payment->subject_to_clockin) && $employee->payment->subject_to_clockin == 'Yes')
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-3 text-slate-500 font-medium">Hour Authorised By</div>
                        <div class="col-span-9 font-medium">
                            @if(isset($employee->hourauth) && $employee->hourauth->count() > 0)
                                @foreach($employee->hourauth as $ha)
                                    <span class="btn inline-flex btn-secondary w-auto text-left px-1 ml-0 mr-1 py-0 mb-1 rounded-0">{{ $ha->user->name }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div class="col-span-12">
                    <div class="pt-2 mb-2 border-b border-slate-200/60 dark:border-darkmode-400"></div>
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Holiday Entitled</div>
                        <div class="col-span-8 font-medium">{!! (isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes' ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                @if(isset($employee->payment->holiday_entitled) && $employee->payment->holiday_entitled == 'Yes')
                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Holiday Base</div>
                        <div class="col-span-8 font-medium">{{ (isset($employee->payment->holiday_base) && !empty($employee->payment->holiday_base) ? $employee->payment->holiday_base : '') }}</div>
                    </div>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Bank Holiday Auto Book</div>
                        <div class="col-span-8 font-medium">{!! (isset($employee->payment->bank_holiday_auto_book) && $employee->payment->bank_holiday_auto_book == 'Yes' ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Holiday Authorised By</div>
                        <div class="col-span-8 font-medium">
                            @if(isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0)
                                @foreach($employee->holidayAuth as $ha)
                                    <span class="btn inline-flex btn-secondary w-auto text-left px-1 ml-0 mr-1 py-0 mb-1 rounded-0">{{ $ha->user->name }}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-span-12">
                    <div class="pt-2 mb-2 border-b border-slate-200/60 dark:border-darkmode-400"></div>
                </div>
                @endif

                <div class="col-span-6 sm:col-span-4">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Pension Enrolled</div>
                        <div class="col-span-8 font-medium">{!! (isset($employee->payment->pension_enrolled) && $employee->payment->pension_enrolled == 'Yes' ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>') !!}</div>
                    </div>
                </div>
                @if(isset($employee->payment->pension_enrolled) && $employee->payment->pension_enrolled == 'Yes')
                <div class="col-span-12">
                    <div class="pt-2 mb-5 border-b border-slate-200/60 dark:border-darkmode-400"></div>
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-PNS" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query-PNS" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-PNS" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-PNS" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-PNS" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print-PNS" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-PNS" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-PNS" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="employeePenssionListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
    

    <!-- BEGIN: Payment Modals -->
    @include('pages.employee.profile.payment-modal')
    <!-- END: Payment Modals -->
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/employee-payment-setting.js')
    @vite('resources/js/employee-banks.js')
    @vite('resources/js/employee-penssion-scheem.js')
@endsection