@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    
    @include('pages.employee.profile.title-info')
    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

        <div class="intro-y box p-5 mt-5">
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
            <h2 class="text-lg font-medium mr-auto">Login Log</h2>
        </div>

        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start flex-wrap gap-2">
                <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto flex-wrap gap-2">
                    {{-- Logout reason --}}
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="logout_reason" name="logout_reason" class="form-select w-full mt-2 sm:mt-0 sm:w-auto">
                            <option value="">All</option>
                            <option value="active">Active (logged in)</option>
                            <option value="manual_logout">Manual Logout</option>
                            <option value="session_timeout">Session Timeout</option>
                            <option value="session_invalidated">Session Invalidated</option>
                        </select>
                    </div>
                    {{-- Date from --}}
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">From</label>
                        <input id="date_from" name="date_from" type="date"
                            class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                    </div>
                    {{-- Date to --}}
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">To</label>
                        <input id="date_to" name="date_to" type="date"
                            class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                    </div>
                    {{-- Buttons --}}
                    <div class="mt-2 xl:mt-0 flex gap-1">
                        <button id="tabulator-html-filter-go" type="button"
                                class="btn btn-primary w-full sm:w-16">Go</button>
                        <button id="tabulator-html-filter-reset" type="button"
                                class="btn btn-secondary w-full sm:w-16 sm:ml-1">Reset</button>
                    </div>
                </form>

                {{-- Export --}}
                <div class="flex mt-5 sm:mt-0">
                    <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown w-1/2 sm:w-auto">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto"
                                aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto scrollbar-hidden">
                <div id="loginLogTable" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
        <!-- END: HTML Table Data -->
 
        <input type="hidden" id="actor_id" value="{{ $employee->user_id }}"/>
        <input type="hidden" id="actor_type" value="user"/>


<!-- BEGIN: Address Modal -->
<div id="addressModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addressForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Address</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div id="addressStart" class="grid grid-cols-12 gap-4 theAddressWrap">
                        <div class="col-span-12">
                            <label for="address_lookup" class="form-label">Address Lookup</label>
                            <input type="text" placeholder="Search address here..." id="address_lookup" class="form-control w-full theAddressLookup" name="address_lookup">
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Address Line 1" id="student_address_address_line_1" class="address_line_1 form-control w-full uppercase inputUppercase" name="address_line_1">
                            <div class="acc__input-error error-student_address_address_line_1 text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="student_address_address_line_2" class="form-label">Address Line 2</label>
                            <input type="text" placeholder="Address Line 2 (Optional)" id="student_address_address_line_2" class="address_line_2 form-control w-full uppercase inputUppercase" name="address_line_2">
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_city" class="form-label">City / Town <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_city" class="city form-control w-full uppercase inputUppercase" name="city">
                            <div class="acc__input-error error-student_address_city text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_postal_zip_code" class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input type="text" placeholder="City / Town" id="student_address_postal_zip_code" class="postal_code form-control w-full uppercase inputUppercase" name="post_code">
                            <div class="acc__input-error error-student_address_postal_zip_code text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <label for="student_address_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Country" id="student_address_country" class="fcountry orm-control w-full uppercase inputUppercase" name="country">
                            <div class="acc__input-error error-student_address_country text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="insertAddress" class="btn btn-primary w-auto">     
                        Add Address                      
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
                    <input type="hidden" name="place" value=""/>
                    <input type="hidden" name="address_id" value="0"/>
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                    <input type="hidden" name="type" value=""/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Address Modal -->

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
                </div>
            </div>
        </div>
    </div>
<!-- END: Success Modal Content -->

@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/login-log_users.js')
@endsection