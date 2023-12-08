@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Student Accounts</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#addAgreementModal" type="button" class="btn btn-primary shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Agreement</button>
            </div>
        </div>
    </div>

    @if(!empty($agreements) && $agreements->count() > 0)
        @foreach($agreements as $agr)
            <div class="intro-y box p-5 mt-5">
                <div class="grid grid-cols-12 gap-0 items-center">
                    <div class="col-span-6">
                        <div class="font-medium text-base">Agreements Details for <u class="text-success">Year {{ $agr->year }}</u></div>
                    </div>
                    <div class="col-span-6 text-right relative">
                        <button data-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#editAgreementModal" type="button" class="edit_agreement_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button>
                        <button data-agr-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#addInstallmentModal" type="button" class="add_attendance_btn btn btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Installment</button>
                    </div>
                </div>
                <div class="intro-y mt-5">
                    <div class="grid grid-cols-12 gap-2">
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Date</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->date) ? date('jS M, Y', strtotime($agr->date)) : '---') }}
                                    {!! (isset($agr->user->employee->full_name) && !empty($agr->user->employee->full_name) ? 'by '.$agr->user->employee->full_name : '') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">SLC Course Code</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->slc_coursecode) ? $agr->slc_coursecode : '---') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Self Funded</div>
                                <div class="col-span-8 font-medium">
                                    {!! (isset($agr->is_self_funded) && $agr->is_self_funded == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Fees</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->fees) ? '£'.number_format($agr->fees, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Discount</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->discount) ? '£'.number_format($agr->discount, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Total</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->total) ? '£'.number_format($agr->total, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($agr->note))
                        <div class="col-span-12"></div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Total</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->note) ? $agr->note : '') }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="installmentAndPaymentWrap mt-7">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-5">
                                <div class="intro-y box p-5 bg-success-soft-2">
                                    <div class="grid grid-cols-12 gap-0 items-center">
                                        <div class="col-span-6">
                                            <div class="font-medium text-base">Installments</div>
                                        </div>
                                    </div>
                                    <div class="intro-y mt-5 bg-white">
                                        @if(!empty($agr->installments) && $agr->installments->count() > 0)
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th class="whitespace-nowrap">#</th>
                                                        <th class="whitespace-nowrap">Date</th>
                                                        <th class="whitespace-nowrap">Year</th>
                                                        <th class="whitespace-nowrap">Term Name</th>
                                                        <th class="whitespace-nowrap">Term</th>
                                                        <th class="whitespace-nowrap">Amount</th>
                                                        <th class="whitespace-nowrap">Course Code</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($agr->installments as $inst)
                                                        <tr class="cursor-pointer installmentRow" data-id="{{ $inst->id }}">
                                                            <td>{{ $inst->id }}</td>
                                                            <td>{{ !empty($inst->installment_date) ? date('jS M, Y', strtotime($inst->installment_date)) : '' }}</td>
                                                            <td>{{ isset($inst->agreement->year) && !empty($inst->agreement->year) ? $inst->agreement->year : ''  }}</td>
                                                            <td>{{ '---' }}</td>
                                                            <td>{{ $inst->term }}</td>
                                                            <td class="font-medium">{{ ($inst->amount > 0 ? '£'.number_format($inst->amount, 2) : '£0.00') }}</td>
                                                            <td>{{ (isset($inst->agreement->slc_coursecode) && !empty($inst->agreement->slc_coursecode) ? $inst->agreement->slc_coursecode : '') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-7">
                                <div class="intro-y box p-5 bg-danger-soft-2">
                                    <div class="grid grid-cols-12 gap-0 items-center">
                                        <div class="col-span-6">
                                            <div class="font-medium text-base">Invoices</div>
                                        </div>
                                    </div>
                                    <div class="intro-y mt-5">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    

    <!-- BEGIN: Edit Agreement Modal -->
    <div id="editAgreementModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editAgreementForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Agreement</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-6">
                                <label for="agr_edit_slc_coursecode" class="form-label">SLC Course Code <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="agr_edit_slc_coursecode" class="form-control" name="slc_coursecode">
                                <div class="acc__input-error error-slc_coursecode text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="agr_edit_isf" class="form-label">Self Funded?</label>
                                <div class="form-check form-switch">
                                    <input id="agr_edit_isf" name="is_self_funded" value="1" class="form-check-input" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_edit_date" class="form-label">Agreement Date <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="agr_edit_date" class="form-control datepicker" name="date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_edit_year" class="form-label">Year <span class="text-danger">*</span></label>
                                <select id="agr_edit_year" class="form-control w-full" name="year">
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="agr_edit_fees" class="form-label">Fees <span class="text-danger">*</span></label>
                                <input id="agr_edit_fees" class="form-control w-full" name="fees" type="number" step="any">
                                <div class="acc__input-error error-fees text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="agr_edit_discount" class="form-label">Discount <span class="text-danger">*</span></label>
                                <input id="agr_edit_discount" class="form-control w-full" name="discount" type="number" step="any">
                            </div>
                            <div class="col-span-12">
                                <label for="agr_edit_note" class="form-label">Note</label>
                                <textarea id="agr_edit_note" rows="2" class="form-control w-full" name="note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateAgre" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="slc_agreement_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Agreement Modal -->

    <!-- BEGIN: Edit Installment Modal -->
    <div id="editInstallmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editInstallmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Installment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-3">
                            <div class="col-span-12 sm:col-span-6">
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Total Amount</div>
                                    <div class="col-span-8 font-medium totalAmount"></div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <div class="grid grid-cols-12 gap-0 mb-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Remaining Amount</div>
                                    <div class="col-span-8 font-medium remainingAmount"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-0 mb-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="installment_date" class="form-label">Installment Date <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="installment_date" class="form-control datepicker" name="installment_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-installment_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control w-full" name="amount" type="number" step="any">
                                <div class="acc__input-error error-amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="attendance_term" class="form-label">Attendance Term <span class="text-danger">*</span></label>
                                <select id="attendance_term" class="form-control w-full" name="attendance_term">
                                    <option value="">Please Select</option>
                                </select>
                                <div class="acc__input-error error-attendance_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                                <select id="session_term" class="form-control w-full" name="session_term">
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="term" class="form-label">Term Name <span class="text-danger">*</span></label>
                                <select id="term" class="form-control w-full" name="term">
                                    <option value="Autumn Term">Autumn Term</option>
                                    <option value="Spring Term">Spring Term</option>
                                    <option value="Summer Term">Summer Term</option>
                                    <option value="Winter Term">Winter Term</option>
                                </select>
                                <div class="acc__input-error error-term text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateInst" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="slc_installment_id" value="0"/>
                        <input type="hidden" name="total_amount" value="0"/>
                        <input type="hidden" name="remaining_amount" value="0"/>
                        <input type="hidden" name="amount_org" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Installment Modal -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

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
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-slc-agreement.js')
    @vite('resources/js/student-slc-installment.js')
@endsection