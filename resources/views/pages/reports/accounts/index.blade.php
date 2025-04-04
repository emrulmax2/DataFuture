@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Accounts Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button type="button" data-tw-target="#transConnectionModal" data-tw-toggle="modal" class="add_btn btn btn-success text-white shadow-md mr-2"><i data-lucide="arrow-right-left" class="w-4 h-4 mr-2"></i> Connect TC</button>
            <a href="{{ route('accounts') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Accounts</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="accountsReportsAccordion" class="accordion accordion-boxed pt-2">
            <div class="accordion-item">
                <div id="accountsReportsAccordion-1" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-1" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-1">
                        Collection Reports
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-1" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <form action="{{ route('reports.account.collection.export') }}" method="post" id="collectionReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                <div class="col-span-12 sm:col-span-2">
                                    <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                                    <div class="relative w-full mx-auto">
                                        <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500">
                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                        </div>
                                        <input type="text" name="date_range" class="datepicker form-control pl-12" data-format="DD-MM-YYYY" data-daterange="true">
                                    </div>
                                    <div class="acc__input-error error-date_range text-danger mt-2">{{ ($errors->has('date_range') ? $errors->first('date_range') : '')}}</div>
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <div class="flex flex-col sm:flex-row pt-10">
                                        <div class="form-check mr-5">
                                            <input id="date_type_1" checked class="form-check-input" type="radio" name="date_type" value="entry_date">
                                            <label class="form-check-label" for="date_type_1">Created Date</label>
                                        </div>
                                        <div class="form-check mr-2 mt-2 sm:mt-0">
                                            <input id="date_type_2" class="form-check-input" type="radio" name="date_type" value="payment_date">
                                            <label class="form-check-label" for="date_type_2">Invoice Date</label>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-date_type text-danger mt-2">{{ ($errors->has('date_type') ? $errors->first('date_type') : '')}}</div>
                                </div>
                                <div class="col-span-12 sm:col-span-7 ml-auto mt-auto text-right">
                                    <button type="submit" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="file-text"></i> Export Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="accountsReportsAccordion-2" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-2" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-2">
                        Due Reports
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-2" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <form method="post" action="{{ route('reports.account.due.export') }}" id="accountDueReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="due_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="due_semester_id[]" multiple class="tom-selects w-full" id="due_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label for="due_course_id" class="form-label courseLabel inline-flex items-center">Course</label>
                                    <select name="due_course_id[]" multiple class="tom-selects w-full" id="due_course_id">
                                        <option value="">Please Select</option>
                                        @if($courses->count() > 0)
                                            @foreach($courses as $crs)
                                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label for="due_status_id" class="form-label">Status</label>
                                    <select name="due_status_id[]" multiple class="tom-selects w-full" id="due_status_id">
                                        <option value="">Please Select</option>
                                        @if($courses->count() > 0)
                                            @foreach($status as $sts)
                                                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label for="due_date" class="form-label">Due Until</label>
                                    <input type="text" name="due_date" class="form-control w-full datepicker" id="due_date" value="" data-date-format="DD-MM-YYYY" data-single-mode="true"/>
                                </div>
                                <div class="col-span-2 text-right" style="padding-top: 31px;">
                                    <button type="submit" id="accDueSubmitBtn" class="btn btn-success text-white w-auto ml-2"><i class="w-4 h-4 mr-2" data-lucide="file-text"></i> Export Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="accountsReportsAccordion-3" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-3" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-3">
                        Payments Upload
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-3" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-3" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <form action="#" method="post" id="slcPaymentHistorySearchForm">
                                    @csrf
                                    <div class="flex justify-start">
                                        <div class="relative w-72">
                                            <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                                            <div class="relative w-full mx-auto">
                                                <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                </div>
                                                <input type="text" id="payment_history_date_range" name="date_range" class="datepicker form-control pl-12" data-format="DD-MM-YYYY" data-daterange="true">
                                            </div>
                                            <div class="acc__input-error error-date_range text-danger mt-2">{{ ($errors->has('date_range') ? $errors->first('date_range') : '')}}</div>
                                        </div>
                                        <button type="button" id="slcPaymentHistorySearchBtn" class="btn btn-success text-white ml-2 w-auto h-10" style="margin-top: 31px;"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-span-12 sm:col-span-6 text-right">
                                <form method="POST" action="#" id="slcPaymentDocUploadForm" class="text-right" enctype="multipart/form-data">
                                    @csrf
                                    <label class="btn btn-success w-auto relative text-white" style="margin-top: 31px;">
                                        <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Upload Payment File (.csv)
                                        <input type="file" accept=".csv" id="payment_file_csv" name="payment_file_csv" style="width: 0; height: 0; opacity: 0; visibility: hidden; position: absolute;">
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                            stroke="white" class="w-4 h-4 ml-2 loaders">
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
                                    </label>
                                    <input type="hidden" name="submited" value="1"/>
                                </form>
                            </div>
                        </div>


                        <div class="overflow-x-auto scrollbar-hidden pt-5" id="slcPaymentHistoryListWrap" style="display: none;">
                            <div class="slcPaymentHistoryListBtnWrap flex justify-end">
                                <button type="button" class="btn btn-primary text-white btn-sm" id="recheck_errors" style="display: none;">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Re Check Errors 
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2 loaders">
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
                                <button type="button" class="btn btn-sm btn-success text-white" id="make_payments" style="display: none;">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Insert Payment
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2 loaders">
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
                            </div>
                            <div id="slcPaymentHistoryListTable" class="mt-5 table-report table-report--tabulator"></div>
                        </div>

                        <div class="overflow-x-auto scrollbar-hidden pt-5" id="slcPaymentUploadListWrap" style="display: none;">
                            <form method="post" action="#" id="slcPaymentUploadListForm">
                                
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="accountsReportsAccordion-4" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#accountsReportsAccordion-collapse-4" aria-expanded="false" aria-controls="accountsReportsAccordion-collapse-4">
                        Marketing Report
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="accountsReportsAccordion-collapse-4" class="accordion-collapse collapse" aria-labelledby="accountsReportsAccordion-4" data-tw-parent="#accountsReportsAccordion">
                    <div class="accordion-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <form action="#" method="post" id="accountMarketingReportForm">
                                    @csrf
                                    <div class="flex justify-start">
                                        <div class="relative w-72">
                                            <label for="marketing_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                            <select name="marketing_semester_id" class="tom-selects w-full" id="marketing_semester_id">
                                                <option value="">Please Select</option>
                                                @if($semester->count() > 0)
                                                    @foreach($semester as $sem)
                                                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-marketing_semester_id text-danger mt-2"></div>
                                        </div>
                                        <button type="submit" id="markRepBtn" class="btn btn-success text-white ml-2 w-auto h-10" style="margin-top: 31px;">
                                            <i class="w-4 h-4 mr-2" data-lucide="search"></i> 
                                            Search 
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                                stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                                    </div>
                                </form>
                            </div>
                            <div class="col-span-12 sm:col-span-6 text-right">
                                <button type="button" class="btn btn-success text-white ml-2 w-auto" data-tw-target="#semesterComissionRatModal" data-tw-toggle="modal" style="margin-top: 31px;"><i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Comission Rates</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto scrollbar-hidden pt-5" id="accountsMarketingReportWrap" style="display: none;">
                            
                        </div>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Force Insert Modal Start -->
    <div id="forceInsertFixModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="forceInsertFixForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Force Insert</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="slc_agreement_id" class="form-label">Agreement <span class="text-danger">*</span></label>
                            <select id="slc_agreement_id" name="slc_agreement_id" class="form-control w-full">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-slc_agreement_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="forceSubmitBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="0"/>
                        <input type="hidden" name="history_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- BEGIN: Transaction Connection Modal Modal Start -->
    <div id="transConnectionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="transConnectionForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Connect Transaction</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label class="col-span-12 sm:col-span-4 form-label pt-2">Search Transaction</label>
                            <div class="col-span-12 sm:col-span-8">
                                <div class="autoCompleteField" data-table="acc_transactions">
                                    <input type="text" autocomplete="off" id="transaction_code" name="transaction_code" class="form-control transaction_code" value="" placeholder="TC00000"/>
                                    <ul class="autoFillDropdown"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Transaction Connection Modal Modal End -->

    <!-- BEGIN: Semester Comission Modal Start -->
    <div id="semesterComissionRatModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Semester Comission Rates</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm-SCR" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Semester</label>
                                <select name="semester-SCR" class="tom-selects mt-2 sm:mt-0 w-52" id="semester-SCR">
                                    <option value="">Please Select</option>
                                    @if($semester->count() > 0)
                                        @foreach($semester as $sem)
                                            <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status-SCR" name="statusSCR" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go-SCR" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset-SCR" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button data-tw-toggle="modal" data-tw-target="#addComissionRateModal" type="button" class="btn btn-success text-white shadow-md ml-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i> Add Comission Rate</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="semesterComissionRateTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Transaction Connection Modal Modal End -->
    
    <!-- BEGIN: Force Insert Modal Start -->
    <div id="addComissionRateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addComissionRateForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Semester Comission Rate</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="comr_semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                            <select name="comr_semester_id" class="tom-selects w-full" id="comr_semester_id">
                                <option value="">Please Select</option>
                                @if($semester->count() > 0)
                                    @foreach($semester as $sem)
                                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-comr_semester_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="comission_rate" class="form-label">Rate <span class="text-danger">*</span></label>
                            <input type="number" step="any" value="" name="comission_rate" id="comission_rate" class="form-control w-full"/>
                            <div class="acc__input-error error-comission_rate text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveComRateBtn" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>


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
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/slc-accounts-reports.js')
    @vite('resources/js/slc-accounts-due-reports.js')
    @vite('resources/js/marketing-reports.js')
@endsection