@extends('../layout/' . $layout)

@section('subhead')
    <title>Form Data Types List</title>
@endsection
<script src="https://unpkg.com/imask"></script>
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Form Data Types List</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('formdatatypes.create') }}" class="btn btn-primary shadow-md mr-2">Add New Form Data</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
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
                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                </a>
                            </li>
                            {{-- <li>
                                <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                </a>
                            </li> --}}
                            <li>
                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                            {{-- <li>
                                <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                </a>
                            </li> --}}
                        </ul>
                    </div>                  
                </div>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="formDataListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Edit Modal -->
    <div id="editFormDataModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editFormDataType" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Form Data Types</h2>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="editFormdataTypeText" class="form-label">Text</label>
                            <input id="editFormdataTypeText" type="text" name="textInput" class="regexp-mask form-control w-full" placeholder="Input text">
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeNumber" class="form-label">Currency</label>
                            <input id="editFormdataTypeNumber" type="text" name="numberInput" class="form-control w-full" placeholder="Currency">
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeSelect" class="form-label">Select Option</label>
                            <select id="editFormdataTypeSelect" name="selectOption" class="form-control">
                                <option value="">Please Select</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeCheckbox" class="cursor-pointer select-none">Checkbox</label>
                            <input id="editFormdataTypeCheckbox" type="checkbox" name="checkboxInput" class="form-check-input border ml-3">                   
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeSwitch">Switch On/Off</label>
                            <div class="form-switch mt-2">
                                <input id="editFormdataTypeSwitch" type="checkbox" name="switchInput" class="form-check-input">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeRadio">Radio Button</label>
                            <div class="mt-3 form-check mr-4">
                                <input id="condition-new" class="form-check-input" type="radio" name="horizontal_radio_button" value="first">
                                <label class="form-check-label" for="condition-new">First</label>
                            </div>
                            <div class="mt-3 form-check mr-4 mt-2 sm:mt-0">
                                <input id="condition-second" class="form-check-input" type="radio" name="horizontal_radio_button" value="second">
                                <label class="form-check-label" for="condition-second">Second</label>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypePhone" class="form-label">Phone</label>
                            <input id="editFormdataTypePhone" type="text" name="phone" class="form-control w-full" placeholder="+(000)0000000000">
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeEmail" class="form-label">Email</label>
                            <input id="editFormdataTypeEmail" type="text" name="email" class="intro-x datatype__input form-control py-3 px-4 block" placeholder="Email">
                            <div id="error-email" class="datatype__input-error text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeDate" class="form-label">Date Format</label>
                            <div class="absolute rounded-l w-10 h-10 flex items-center justify-center bg-slate-100 border text-slate-500 dark:bg-darkmode-700 dark:border-darkmode-800 dark:text-slate-400">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <input id="editFormdataTypeDate" type="text" name="dateformat" class="datepicker form-control pl-12" data-single-mode="true">
                        </div>
                        <div class="mt-3">
                            <label for="editFormdataTypeDaterange" class="form-label">Date Range Picker</label>
                            <input id="editFormdataTypeDaterange" type="text" name="daterange" data-daterange="true" class="datepicker form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateFormData" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->
    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle">Good job!</div>
                        <div class="text-slate-500 mt-2 successModalDesc">You clicked the button!</div>
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
    <div id="confirmFormdataDelModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc">Do you really want to delete these records? <br>This process cannot be undone.</div>
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
    @vite('resources/js/ckeditor-classic.js')
    <script>
        IMask(
            document.getElementById('formdataTypeNumber'),
            {
            mask: /^[1-9]\d{0,100}$/
            },
            document.getElementById('formdataTypePhone'),
            {
            mask: /^[1-9]\d{0,15}$/
            }
        );

        var emailField = document.getElementById('formdataTypeEmail');
        //document.querySelector("#formdataTypeEmail").addEventListener('input', e => {
        emailField.addEventListener('blur', function() {
            var reg = /^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-](?!\.)){0,61}[a-zA-Z0-9]?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9\-](?!$)){0,61}[a-zA-Z0-9]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/;
            if (reg.test(emailField.value) == false) {
            $('#datatypeForm').find('.datatype__input').addClass('border-danger')
            $('#datatypeForm').find('.datatype__input-error').html('Please enter a valid email')
            return false;
            }  
            return true;                        
        });
    </script>
@endsection