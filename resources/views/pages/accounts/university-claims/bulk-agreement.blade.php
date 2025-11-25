@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col md:flex-row items-center mt-1 md:mt-8">
        <div class="flex flex-row justify-center md:justify-normal items-center gap-2 flex-wrap mb-4 md:mb-0 w-full">
            <h2 class="text-lg font-medium text-center md:text-left">Create Bulk Agreement</h2>
        </div>
        <div class="md:ml-auto md:w-full flex flex-wrap sm:flex-row gap-2 justify-end">
            <a href="{{ route('university.claims') }}" class="btn btn-success text-white shadow-md ml-auto"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>Back To Claim</a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12">
            <div class="intro-y box">
                <div class="p-5">
                    <div class="grid grid-cols-12 gap-6">
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label flex items-center">Intake Semester <i data-loading-icon="three-dots" class="w-6 h-6 ml-3 theLoaders hidden"></i></label>
                            <select id="intakeSemester" name="intakeSemester" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if(!empty($semesters))
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-intakeSemester text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label flex items-center">Course</label>
                            <select id="course" name="course" class="w-full tom-selects">
                                <option value="">Please Select</option>
                            </select>
                            <div class="acc__input-error error-course text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label flex items-center">Student Status</label>
                            <select id="studentStatus" name="studentStatus[]" class="w-full tom-selects" multiple>
                                <option value="">Please Select</option>
                                @if(!empty($statuses))
                                    @foreach($statuses as $sts)
                                        <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-studentStatus text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12 sm:col-span-3 text-right mt-7">
                            <button id="generateInvoiceList" class="btn btn-primary w-auto text-white">
                                <i data-lucide="search" class="w-4 h-4 mr-2"></i> Search
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
                            <button id="createBulkAgreements" style="display: none;" class="btn btn-success ml-1 w-auto text-white">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Create Agreements
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box mt-5 p-5 invoiceStudentListWrap" style="display: none;">
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="invoiceStudentListTable" class="table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Add Agreement Modal -->
    <div id="addAgreementModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addAgreementForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Agreement</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_date" class="form-label">Agreement Date <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="agr_add_date" class="require form-control datepicker" name="date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_year" class="form-label">Year <span class="text-danger">*</span></label>
                                <select id="agr_add_year" class="require form-control w-full" name="year">
                                    <option value="">Please Select</option>
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_course_creation_instance_id" class="form-label">Instance Year <span class="text-danger">*</span></label>
                                <select id="agr_add_course_creation_instance_id" class="require form-control w-full" name="course_creation_instance_id">
                                    <option value="">Please Select</option>
                                </select>
                                <div class="acc__input-error error-course_creation_instance_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="agr_add_fees" class="form-label">Fees <span class="text-danger">*</span></label>
                                <input id="agr_add_fees" class="require form-control w-full" name="fees" type="number" step="any">
                                <div class="acc__input-error error-fees text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3 universityCommissionWrap" style="display: none;">
                                <label for="commission_amount" class="form-label">University Commission<span class="percntage text-danger font-medium ml-2"></span></label>
                                <input id="commission_amount" readonly class="form-control w-full" name="commission_amount" type="number" step="any">
                                <div class="acc__input-error error-commission_amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 installMentWraper" style="display: none;">
                                <h3 class="font-medium flex items-center justify-between mb-4">
                                    <span>Installments</span>
                                    <button type="button" id="addInstallmentRow" class="btn btn-primary btn-sm">Add Installment</button>
                                </h3>
                                <table id="installmentTable" class="installmentTable table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Term declaration</th>
                                            <th>Session Term</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="defaultRow installmentRow relative">
                                            <td>
                                                <input type="text" name="installment_date[]" placeholder="DD-MM-YYYY" class="rowRequire form-control datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                            </td>
                                            <td>
                                                <select class="rowRequire form-control w-full" name="term_declaration_id[]">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                                        @foreach($term_declarations as $td)
                                                            <option value="{{ $td->id }}">{{ $td->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <select class="rowRequire form-control w-full" name="session_term[]">
                                                    <option value="">Please Select</option>
                                                    <option value="1">Term 01</option>
                                                    <option value="2">Term 02</option>
                                                    <option value="3">Term 03</option>
                                                    <option value="4">Term 04</option>
                                                    <option value="5">N/A</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input class="rowRequire form-control w-full installmentAmount" name="amounts[]" type="number" step="any">
                                                <button class="delete_btn hidden btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px] absolute"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="trash-2" class="lucide lucide-trash-2 w-4 h-4"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path><line x1="10" x2="10" y1="11" y2="17"></line><line x1="14" x2="14" y1="11" y2="17"></line></svg></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-span-12 hidden feesError text-danger"></div>
                            <div class="col-span-12">
                                <label for="agr_add_note" class="form-label">Note</label>
                                <textarea id="agr_add_note" rows="2" class="form-control w-full" name="note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addAgre" class="btn btn-primary w-auto">     
                            Create Agreement                      
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
                        <input type="hidden" name="student_ids" value=""/>
                        <input type="hidden" name="semester_id" value="0"/>
                        <input type="hidden" name="course_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Agreement Modal -->

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

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle">Oops!</div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">OK, Got it</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

@endsection

@section('script')
    @vite('resources/js/accounts.js')
    @vite('resources/js/accounts-university-invoice-bulk-agreement.js')
@endsection