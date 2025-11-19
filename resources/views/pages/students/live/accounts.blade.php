@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Student Accounts</div>
            </div>
            <div class="col-span-6 text-right relative">
                @if($can_add) <button data-tw-toggle="modal" data-tw-target="#addAgreementModal" type="button" class="btn btn-primary shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Agreement</button> @endif
            </div>
        </div>
    </div>

    @if(!empty($agreements) && $agreements->count() > 0)
        @foreach($agreements as $agr)
            @php 
                $discount = (isset($agr->discount) && $agr->discount > 0 ? $agr->discount : 0);
                $claimAmount = (isset($agr->claim_amount) && $agr->claim_amount > 0 ? $agr->claim_amount : 0);
                $onlyReceivedAmount = (isset($agr->only_received_amount) && $agr->only_received_amount > 0 ? $agr->only_received_amount : 0);
                $refundAmount = (isset($agr->refund_amount) && $agr->refund_amount > 0 ? $agr->refund_amount : 0);
                $balance = $onlyReceivedAmount - ($claimAmount + $refundAmount);

                $fees = (isset($agr->fees) && $agr->fees > 0 ? $agr->fees : 0);
                $commission = (isset($agr->commission_amount) && $agr->commission_amount > 0 ? $agr->commission_amount : 0);
            @endphp
            <div class="intro-y box p-5 mt-5 {{ (isset($agr->student_course_relation_id) && $agr->student_course_relation_id > 0 ? '' : 'bg-danger-soft') }}">
                <div class="grid grid-cols-12 gap-0 items-center">
                    <div class="col-span-6">
                        <div class="font-medium text-base">
                            Agreements Details for <u class="text-success">Year {{ $agr->year }}</u><br/>
                            {{ '('.$agr->id.'-'.$agr->slc_registration_id.')' }}
                        </div>
                    </div>
                    <div class="col-span-6 text-right relative">
                        @if($can_edit) <button data-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#editAgreementModal" type="button" class="edit_agreement_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1"><i data-lucide="Pencil" class="w-4 h-4"></i></button> @endif
                        @if($can_delete) <button data-id="{{ $agr->id }}" type="button" class="deleteAgreementBtn btn-rounded btn btn-danger text-white p-0 w-9 h-9"><i data-lucide="trash-2" class="w-4 h-4"></i></button> @endif
                        @if(!empty($registrations) && $registrations->count() > 0 && (empty($agr->slc_registration_id) || $agr->slc_registration_id == 0) && $can_add)
                            <div class="dropdown inline-block ml-1" data-tw-placement="bottom-end">
                                <button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-9 h-9 mr-1" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="arrow-right-left" class="w-4 h-4"></i></button>
                                <div class="dropdown-menu w-64">
                                    <ul class="dropdown-content">
                                        @foreach($registrations as $regs)
                                            <li><a href="javascript:void(0);" data-reg="{{ $regs->id }}" data-agr="{{ $agr->id }}" class="dropdown-item assignAgreementToReg text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>ID: {{ $regs->id }} - Year {{ $regs->registration_year }} {{ (isset($regs->year->name) && !empty($regs->year->name) ? ' - '.$regs->year->name : '') }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="intro-y mt-5">
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Date</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->date) ? date('jS M, Y', strtotime($agr->date)) : '---') }}
                                    {!! (isset($agr->user->employee->full_name) && !empty($agr->user->employee->full_name) ? 'by '.$agr->user->employee->full_name : '') !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">SLC Course Code</div>
                                <div class="col-span-8 font-medium">
                                    {{ (!empty($agr->slc_coursecode) ? $agr->slc_coursecode : '---') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Self Funded</div>
                                <div class="col-span-8 font-medium">
                                    {!! (isset($agr->is_self_funded) && $agr->is_self_funded == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}
                                </div>
                            </div>
                        </div>
                        @if($discount > 0)
                            <div class="col-span-12 sm:col-span-3">
                                <div class="grid grid-cols-12 gap-0 gap-x-3">
                                    <div class="col-span-4 text-slate-500 font-medium">Discount</div>
                                    <div class="col-span-8 font-medium text-pending">
                                        {{ (!empty($agr->discount) ? '£'.number_format($agr->discount, 2) : '£0.00') }}
                                    </div>
                                </div>
                            </div>
                        @else 
                            <div class="col-span-12 sm:col-span-3"></div>
                        @endif
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Fees</div>
                                <div class="col-span-8 font-medium">
                                    @if($discount > 0)
                                        <del class="text-slate-400 mr-2">{{ (!empty($fees) ? '£'.number_format($fees, 2) : '£0.00') }}</del>
                                        {{ (!empty($fees) ? '£'.number_format(($fees - $discount), 2) : '£0.00') }}
                                    @elseif($commission > 0)
                                        <del class="text-slate-400 mr-2">{{ (!empty($fees) ? '£'.number_format($fees, 2) : '£0.00') }}</del>
                                        {{ (!empty($fees) ? '£'.number_format(($fees - $commission), 2) : '£0.00') }}
                                    @else
                                        {{ (!empty($fees) ? '£'.number_format($fees, 2) : '£0.00') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">No of Claim</div>
                                <div class="col-span-8 font-medium">
                                    {{ (isset($agr->installments) ? $agr->installments->count() : '0') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Claim Amount</div>
                                <div class="col-span-8 font-medium">
                                    {{ ($claimAmount > 0 ? '£'.number_format($claimAmount, 2) : '£0.00') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Balance</div>
                                <div class="col-span-8 font-medium">
                                    <!--{{ (!empty($agr->total) ? '£'.number_format($agr->total, 2) : '£0.00') }}-->
                                    {!! ($balance >= 0 ? '<span class="text-success">£'.number_format($balance, 2).'</span>' : '<span class="text-danger">'.($balance < 0 ? '- £'.number_format(str_replace('-', '', $balance), 2) : '£'.number_format($balance, 2)).'</span>') !!}
                                </div>
                            </div>
                        </div>
                        
                        @if(!empty($agr->note))
                        <div class="col-span-12"></div>
                        <div class="col-span-12 sm:col-span-4">
                            <div class="grid grid-cols-12 gap-0 gap-x-3">
                                <div class="col-span-4 text-slate-500 font-medium">Note</div>
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
                                <div class="intro-y md:box md:p-5 md:bg-success-soft-2">
                                    <div class="grid grid-cols-12 gap-0 items-center">
                                        <div class="col-span-6">
                                            <div class="font-medium text-base">Installments</div>
                                        </div>
                                        <div class="col-span-6 text-right">
                                            @if($can_add) <button data-agr-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#addInstallmentModal" type="button" class="add_installment_btn btn btn-sm btn-linkedin shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i>Add Instalment</button> @endif
                                        </div>
                                    </div>
                                    <div class="intro-y mt-5 bg-white overflow-x-auto">
                                        <table class="table table-bordered table-sm padding-less">
                                            <thead>
                                                <tr>
                                                    <th class="whitespace-nowrap">#</th>
                                                    <th class="whitespace-nowrap">Date</th>
                                                    <th class="whitespace-nowrap">Term</th>
                                                    <th class="whitespace-nowrap">Ses. Term</th>
                                                    <th class="whitespace-nowrap">Amount</th>
                                                    <th class="whitespace-nowrap">Course Code</th>
                                                    <th class="whitespace-nowrap">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(!empty($agr->installments) && $agr->installments->count() > 0)
                                                    @foreach($agr->installments as $inst)
                                                        <tr class="cursor-pointer installmentRow {{ isset($inst->slc_money_receipt_id) && $inst->slc_money_receipt_id > 0 ? 'paidInstllment' : '' }}" data-id="{{ $inst->id }}">
                                                            <td>{{ $inst->id.'-'.$inst->slc_attendance_id }}</td>
                                                            <td>{{ !empty($inst->installment_date) ? date('jS M, Y', strtotime($inst->installment_date)) : '' }}</td>
                                                            <td>
                                                                {{ isset($inst->declaraton->name) && !empty($inst->declaraton->name) ? $inst->declaraton->name : '' }}
                                                                {!! isset($inst->declaraton->termType->code) && !empty($inst->declaraton->termType->code) ? '<br/>'.$inst->declaraton->termType->code : '' !!}
                                                            </td>
                                                            <td>{{ isset($inst->session_term) && $inst->session_term > 0 ? 'Term '.$inst->session_term : '' }}</td>
                                                            <td class="font-medium">{{ ($inst->amount > 0 ? '£'.number_format($inst->amount, 2) : '£0.00') }}</td>
                                                            <td>{{ (isset($inst->agreement->slc_coursecode) && !empty($inst->agreement->slc_coursecode) ? $inst->agreement->slc_coursecode : '') }}</td>
                                                            <td>
                                                                @if($can_edit) <button data-id="{{ $inst->id }}" data-tw-toggle="modal" data-tw-target="#editInstallmentModal" type="button" class="editInstallmentBtn btn-rounded btn btn-success text-white p-0 w-6 h-6"><i data-lucide="Pencil" class="w-3 h-3"></i></button> @endif
                                                                @if($can_delete) <button data-id="{{ $inst->id }}" type="button" class="deleteInstallmentBtn btn-rounded btn btn-danger text-white p-0 w-6 h-6"><i data-lucide="trash-2" class="w-3 h-3"></i></button> @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="7" class="text-center">Installments not found for this agreement.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 sm:col-span-7">
                                <div class="intro-y md:box md:p-5 md:bg-danger-soft-2">
                                    <div class="grid grid-cols-12 gap-0 items-center">
                                        <div class="col-span-6">
                                            <div class="font-medium text-base">Invoices</div>
                                        </div>
                                        <div class="col-span-6 text-right">
                                            @if($can_add) <button data-agr-id="{{ $agr->id }}" data-tw-toggle="modal" data-tw-target="#addPaymentModal" type="button" class="addPaymentBtn btn btn-sm btn-twitter shadow-md"><i data-lucide="plus-circle" class="w-4 h-4 mr-1"></i>Add Payment</button> @endif
                                        </div>
                                    </div>
                                    <div class="intro-y mt-5 bg-white overflow-x-auto">
                                        <table class="table table-bordered table-sm padding-less">
                                            <thead>
                                                <tr>
                                                    <th class="whitespace-nowrap">Inv.</th>
                                                    <th class="whitespace-nowrap">Date</th>
                                                    <th class="whitespace-nowrap">Term</th>
                                                    <th class="whitespace-nowrap">Ses. Term</th>
                                                    <th class="whitespace-nowrap">Method</th>
                                                    <th class="whitespace-nowrap">Rec. By</th>
                                                    <th class="whitespace-nowrap">Type</th>
                                                    <th class="whitespace-nowrap">Amount</th>
                                                    <th class="whitespace-nowrap">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if(isset($agr->payments) && $agr->payments->count() > 0)
                                                    @foreach($agr->payments as $payment)
                                                        <tr title="{!! $payment->remarks !!}" class="payment_row {{ !empty($payment->remarks) ? 'tooltip' : '' }} payment_row_{{ isset($payment->payment_type) && !empty($payment->payment_type) ? str_replace(' ', '_', strtolower($payment->payment_type)) : '' }}">
                                                            <td><span class="flex">{{ $payment->invoice_no }}
                                                                @if($payment->mailed_pdf_file!=null || $payment->mailed_pdf_file!='')
                                                                    <i data-lucide="send" class="w-4 h-4 text-orange-500 ml-auto"></i></a>
                                                                @endif
                                                                </span>
                                                            </td>
                                                            <td>{{ (!empty($payment->payment_date) ? date('jS M, Y', strtotime($payment->payment_date)) : '') }}</td>
                                                            <td>
                                                                {{ isset($payment->declaraton->name) && !empty($payment->declaraton->name) ? $payment->declaraton->name : '' }}
                                                                {!! isset($payment->declaraton->termType->code) && !empty($payment->declaraton->termType->code) ? '<br/>'.$payment->declaraton->termType->code : '' !!}
                                                            </td>
                                                            <td>{{ isset($payment->session_term) && $payment->session_term > 0 ? 'Term '.$payment->session_term : '' }}</td>
                                                            <td>{{ isset($payment->method->name) && $payment->slc_payment_method_id > 0 ? $payment->method->name : '' }}</td>
                                                            <td>{{ isset($payment->received->employee->full_name) && !empty($payment->received->employee->full_name) ? $payment->received->employee->full_name : '' }}</td>
                                                            <td>{{ isset($payment->payment_type) && !empty($payment->payment_type) ? $payment->payment_type : '' }}</td>
                                                            <td>{{ isset($payment->amount) && $payment->amount > 0 ? '£'.number_format($payment->amount, 2) : '£0.00' }}</td>
                                                            <td>
                                                                <a data-id="{{ $payment->id }}" href="{{ route('student.accounts.print',[$student->id, $payment->id]) }}" class="printBtn btn-rounded btn btn-primary text-white p-0 w-6 h-6"><i data-lucide="printer" class="w-3 h-3"></i></a>
                                                                <a data-id="{{ $payment->id }}" data-student="{{ $student->id }}" href="{{ route('student.accounts.send_mail',[$student->id, $payment->id]) }}" data-tw-toggle="modal" data-tw-target="#sendMailModal" class="sendAccountMailBtn btn-rounded btn btn-warning text-white p-0 w-6 h-6"><i data-lucide="send" class="w-3 h-3"></i></a>
                                                                @if($can_edit) <button data-id="{{ $payment->id }}" data-tw-toggle="modal" data-tw-target="#editPaymentModal" type="button" class="editPaymentBtn btn-rounded btn btn-success text-white p-0 w-6 h-6"><i data-lucide="Pencil" class="w-3 h-3"></i></button> @endif
                                                                @if($can_delete) <button data-id="{{ $payment->id }}" type="button" class="deletePaymentBtn btn-rounded btn btn-danger text-white p-0 w-6 h-6"><i data-lucide="trash-2" class="w-3 h-3"></i></button> @endif
                                                                @if(!empty($agreements) && $agreements->count() > 0 && $can_add)
                                                                    <div class="dropdown inline-flex" data-tw-placement="bottom-end">
                                                                        <button class="dropdown-toggle btn-rounded btn btn-success text-white p-0 w-6 h-6" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="arrow-right-left" class="w-3 h-3"></i></button>
                                                                        <div class="dropdown-menu w-64">
                                                                            <ul class="dropdown-content">
                                                                                @foreach($agreements as $sagr)
                                                                                    @if($sagr->id != $agr->id)
                                                                                        <li><a href="javascript:void(0);" data-agr="{{ $sagr->id }}" data-pay="{{ $payment->id }}" class="dropdown-item assignPaymentToAgr text-success"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>Agreement ID: {{ $sagr->id }} - Year {{ $sagr->year }}</a></li>
                                                                                    @endif
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="10" class="text-center">Payments not found for this agreement.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
    

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
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="agr_add_date" class="form-control datepicker" name="date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_slc_coursecode" class="form-label">SLC Course Code <span class="text-danger">*</span></label>
                                <input data-code="{{ $slcCode }}" type="text" value="{{ $slcCode }}" id="agr_add_slc_coursecode" class="form-control" name="slc_coursecode">
                                <div class="acc__input-error error-slc_coursecode text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="agr_add_isf" class="form-label">Self Funded?</label>
                                <div class="form-check form-switch">
                                    <input id="agr_add_isf" name="is_self_funded" value="1" class="form-check-input" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_year" class="form-label">Year <span class="text-danger">*</span></label>
                                <select id="agr_add_year" class="form-control w-full" name="year">
                                    <option value="">Please Select</option>
                                    <option value="1">Year 1</option>
                                    <option value="2">Year 2</option>
                                    <option value="3">Year 3</option>
                                </select>
                                <div class="acc__input-error error-year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-3">
                                <label for="agr_add_course_creation_instance_id" class="form-label">Instance Year <span class="text-danger">*</span></label>
                                <select id="agr_add_course_creation_instance_id" class="form-control w-full" name="course_creation_instance_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($instances) && $instances->count())
                                        @foreach($instances as $inst)
                                            <option value="{{ $inst->id }}">{{ $inst->year->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-instance_year text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="agr_add_fees" class="form-label">Fees <span class="text-danger">*</span></label>
                                <input id="agr_add_fees" class="form-control w-full" name="fees" type="number" step="any">
                                <div class="acc__input-error error-fees text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-3 universityCommissionWrap" style="display: none;">
                                <label for="commission_amount" class="form-label">University Commission<span class="percntage text-danger font-medium ml-2"></span></label>
                                <input id="commission_amount" class="form-control w-full" name="commission_amount" type="number" step="any">
                                <div class="acc__input-error error-commission_amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="agr_add_note" class="form-label">Note</label>
                                <textarea id="agr_add_note" rows="2" class="form-control w-full" name="note"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addAgre" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Agreement Modal -->

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
                                <label for="agr_edit_discount" class="form-label">Discount</label>
                                <input id="agr_edit_discount" class="form-control w-full" name="discount" type="number" step="any">
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <label for="edit_commission_amount" class="form-label">University Commission</label>
                                <input id="edit_commission_amount" class="form-control w-full" name="commission_amount" type="number" step="any">
                                <div class="acc__input-error error-commission_amount text-danger mt-2"></div>
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

    <!-- BEGIN: Add Installment Modal -->
    <div id="addInstallmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addInstallmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Instalment</h2>
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
                            <div class="col-span-6 sm:col-span-4">
                                <label for="term_declaration_id" class="form-label">Attendance Term <span class="text-danger">*</span></label>
                                <select id="term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                                <select id="session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 installmentAmountWrap" style="display: none;">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control w-full" name="amount" type="number" step="any">
                                <div class="acc__input-error error-amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 installmentAmountNotice" style="display: none;">
                                <div class="alert alert-warning-soft show flex items-center px-2 py-1 mt-0" role="alert">
                                    Opps! Installment already exist under this session term.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="addInst" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="total_amount" value="0"/>
                        <input type="hidden" name="remaining_amount" value="0"/>
                        <input type="hidden" name="slc_agreement_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Installment Modal -->

    <!-- BEGIN: Edit Installment Modal -->
    <div id="editInstallmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editInstallmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Instalment</h2>
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
                            <div class="col-span-6 sm:col-span-4">
                                <label for="term_declaration_id" class="form-label">Attendance Term <span class="text-danger">*</span></label>
                                <select id="term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                                <select id="session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 installmentAmountWrap" style="display: none;">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control w-full" name="amount" type="number" step="any">
                                <div class="acc__input-error error-amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4 installmentAmountNotice" style="display: none;">
                                <div class="alert alert-warning-soft show flex items-center px-2 py-1 mt-5" role="alert">
                                    Opps! Installment already exist under this session term.
                                </div>
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

    <!-- BEGIN: Add Payment Modal -->
    <div id="addPaymentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addPaymentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Payment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="invoice_no" class="form-label">Invoice No <span class="text-danger">*</span></label>
                                <input type="text" readonly value="" id="invoice_no" class="form-control" name="invoice_no">
                                <div class="acc__input-error error-invoice_no text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="payment_date" class="form-control datepicker" name="payment_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-payment_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="slc_payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select id="slc_payment_method_id" class="form-control w-full" name="slc_payment_method_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($paymentMethods) && $paymentMethods->count() > 0)
                                        @foreach($paymentMethods as $pm)
                                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-slc_payment_method_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="term_declaration_id" class="form-label">Payment Term <span class="text-danger">*</span></label>
                                <select id="term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                                <select id="session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control w-full" name="amount" type="number" step="any">
                                <div class="acc__input-error error-amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                                <select id="payment_type" class="form-control w-full" name="payment_type">
                                    <option value="">Please Select</option>
                                    <option value="Course Fee">Course Fee</option>
                                    <option value="Exam Fee">Exam Fee</option>
                                    <option value="ID Card Fee">ID Card Fee</option>
                                    <option value="Photocopy Card Fee">Photocopy Card Fee</option>
                                    <option value="Late Fee">Late Fee</option>
                                    <option value="Refund">Refund</option>
                                    <option value="Letter Request">Letter Request</option>
                                    <option value="Others">Others</option>
                                </select>
                                <div class="acc__input-error error-payment_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea id="remarks" rows="2" class="form-control w-full" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="savePayment" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="studen_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="slc_agreement_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Payment Modal -->

    <!-- BEGIN: Add Payment Modal -->
    <div id="editPaymentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editPaymentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Payment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="invoice_no" class="form-label">Invoice No <span class="text-danger">*</span></label>
                                <input type="text" readonly value="" id="invoice_no" class="form-control" name="invoice_no">
                                <div class="acc__input-error error-invoice_no text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="text" value="" placeholder="DD-MM-YYYY" id="payment_date" class="form-control datepicker" name="payment_date" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-payment_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="slc_payment_method_id" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select id="slc_payment_method_id" class="form-control w-full" name="slc_payment_method_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($paymentMethods) && $paymentMethods->count() > 0)
                                        @foreach($paymentMethods as $pm)
                                            <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-slc_payment_method_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="term_declaration_id" class="form-label">Payment Term <span class="text-danger">*</span></label>
                                <select id="term_declaration_id" class="form-control w-full" name="term_declaration_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($term_declarations) && $term_declarations->count() > 0)
                                        @foreach($term_declarations as $td)
                                            <option {{ (isset($lastAssigns->plan->term_declaration_id) && $lastAssigns->plan->term_declaration_id == $td->id ? 'Selected' : '')}} value="{{ $td->id }}">{{ $td->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="session_term" class="form-label">Session Term <span class="text-danger">*</span></label>
                                <select id="session_term" class="form-control w-full" name="session_term">
                                    <option value="">Please Select</option>
                                    <option value="1">Term 01</option>
                                    <option value="2">Term 02</option>
                                    <option value="3">Term 03</option>
                                    <option value="4">Term 04</option>
                                    <option value="5">N/A</option>
                                </select>
                                <div class="acc__input-error error-session_term text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-4">
                                <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                <input id="amount" class="form-control w-full" name="amount" type="number" step="any">
                                <div class="acc__input-error error-amount text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6 sm:col-span-4">
                                <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                                <select id="payment_type" class="form-control w-full" name="payment_type">
                                    <option value="">Please Select</option>
                                    <option value="Course Fee">Course Fee</option>
                                    <option value="Exam Fee">Exam Fee</option>
                                    <option value="ID Card Fee">ID Card Fee</option>
                                    <option value="Photocopy Card Fee">Photocopy Card Fee</option>
                                    <option value="Late Fee">Late Fee</option>
                                    <option value="Refund">Refund</option>
                                    <option value="Letter Request">Letter Request</option>
                                    <option value="Others">Others</option>
                                </select>
                                <div class="acc__input-error error-payment_type text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea id="remarks" rows="2" class="form-control w-full" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updatePayment" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Payment Modal -->

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

    <div id="sendMailModal" class="modal " tabindex="-1" aria-hidden="true" >
        <div  class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div id="sendMailLoadingConfirm">
                        <div class="p-5 text-center">
                            <i data-loading-icon="rings" data-color="oklch(70.5% 0.213 47.604)" class="ring-loading w-20 h-20 mx-auto mt-3 hidden"></i>
                            
                            <i data-lucide="send" class="success-on w-16 h-16 text-success mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 sendMailModalTitle transition">Email the money receipt?</div>
                            <div class="text-slate-500 mt-2 sendMailModalDesc">Money receipt will send to student e-mail</div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <button type="button" data-tw-dismiss="modal" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                            <button type="button" data-payment_id="" data-student="{{ $student->id }}" class="agreeWith btn btn-success text-white w-auto">Send Now<i data-lucide="send" class="w-4 h-4 ml-1"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                        <button type="button" data-action="NONE" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
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
    @vite('resources/js/student-slc-payment.js')
@endsection