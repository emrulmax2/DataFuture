@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col md:flex-row items-center mt-1 md:mt-8">
        <div class="flex flex-row justify-center md:justify-normal items-center gap-2 flex-wrap mb-4 md:mb-0 w-full">
            <h2 class="text-lg font-medium text-center md:text-left">Invoice Details</h2>
        </div>
        <div class="md:ml-auto md:w-full flex flex-wrap sm:flex-row gap-2 justify-end">
            <a href="{{ route('university.claims.invoices') }}" class="btn btn-facebook text-white shadow-md ml-auto"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>Bank to Invoices</a>
        </div>
    </div>
    <form method="post" action="#" id="uniPayInvoicedForm">
        <input type="hidden" name="university_payment_claim_id" value="{{ $claim->id }}" />
        <div class="grid grid-cols-12 gap-6 mt-5">
            <div class="col-span-12">
                <div class="intro-y box">
                    <div class="p-5">
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-2">
                                <h3 class="font-medium mb-3">Proforma No</h3>
                                <div class="font-medium">{{ $claim->proforma_no }}</div>
                            </div>
                            @if($claim->status == 2)
                            <div class="col-span-2">
                                <h3 class="font-medium mb-3">Invoice No</h3>
                                <div class="font-medium">{{ $claim->invoice_no }}</div>
                            </div>
                            @endif
                            <div class="col-span-2">
                                <h3 class="font-medium mb-3">Claim Date</h3>
                                <div class="font-medium">{{ $claim->claim_date }}</div>
                            </div>
                            <div class="col-span-2">
                                <h3 class="font-medium mb-3">Invoice Amount</h3>
                                <div class="font-medium">
                                    <span class="mr-2 {{ $claim->status == 2 ? 'text-danger line-through' : '' }}">{{ Number::currency($claim->proforma_total, 'GBP') }}</span>
                                    @if($claim->status == 2)
                                        <br/><span class="text-success">{{ Number::currency($claim->invoice_total, 'GBP') }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($claim->status == 2)
                            <div class="col-span-2">
                                <h3 class="font-medium mb-3">Invoice Date</h3>
                                <div class="font-medium">
                                    {{ isset($claim->invoiced->employee->full_name) && !empty($claim->invoiced->employee->full_name) ? 'By '.$claim->invoiced->employee->full_name : (isset($claim->invoiced->name) && !empty($claim->invoiced->name ? 'By '.$claim->invoiced->name : '')) }}
                                    {!! !empty($claim->invoiced_at) ? '<br/><span class="text-xs font-normal text-slate-400">'.date('F d, Y', strtotime($claim->invoiced_at)).'</span>' : '' !!}
                                </div>
                            </div>
                            @endif
                            @if($claim->vendor_id > 0)
                                <div class="col-span-3">
                                    <h3 class="font-medium mb-3">Invoice To</h3>
                                    <div class="grid grid-cols-12 gap-0 mb-2">
                                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                                        <div class="col-span-8 font-medium">{{ $claim->vendor->name }}</div>
                                    </div>
                                    @if(isset($claim->vendor->email) && !empty($claim->vendor->email))
                                        <div class="grid grid-cols-12 gap-0 mb-2">
                                            <div class="col-span-4 text-slate-500 font-medium">Email</div>
                                            <div class="col-span-8 font-medium">{{ $claim->vendor->email }}</div>
                                        </div>
                                    @endif
                                    @if(isset($claim->vendor->phone) && !empty($claim->vendor->phone))
                                        <div class="grid grid-cols-12 gap-0 mb-2">
                                            <div class="col-span-4 text-slate-500 font-medium">Phone</div>
                                            <div class="col-span-8 font-medium">{{ $claim->vendor->phone }}</div>
                                        </div>
                                    @endif
                                    @if(isset($claim->vendor->address) && !empty($claim->vendor->address))
                                        <div class="grid grid-cols-12 gap-0">
                                            <div class="col-span-4 text-slate-500 font-medium">Address</div>
                                            <div class="col-span-8 font-medium">{{ $claim->vendor->address }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @if($claim->acc_bank_id > 0)
                                <div class="col-span-3">
                                    <h3 class="font-medium mb-3">Remit To</h3>
                                    <div class="grid grid-cols-12 gap-0 mb-2">
                                        <div class="col-span-4 text-slate-500 font-medium">Bank Name</div>
                                        <div class="col-span-8 font-medium">{{ $claim->bank->bank_name }}</div>
                                    </div>
                                    @if(isset($claim->bank->ac_name) && !empty($claim->bank->ac_name))
                                        <div class="grid grid-cols-12 gap-0 mb-2">
                                            <div class="col-span-4 text-slate-500 font-medium">AC. Name</div>
                                            <div class="col-span-8 font-medium">{{ $claim->bank->ac_name }}</div>
                                        </div>
                                    @endif
                                    @if(isset($claim->bank->sort_code) && !empty($claim->bank->sort_code))
                                        <div class="grid grid-cols-12 gap-0 mb-2">
                                            <div class="col-span-4 text-slate-500 font-medium">Sortcode</div>
                                            <div class="col-span-8 font-medium">{{ $claim->bank->sort_code }}</div>
                                        </div>
                                    @endif
                                    @if(isset($claim->bank->ac_number) && !empty($claim->bank->ac_number))
                                        <div class="grid grid-cols-12 gap-0">
                                            <div class="col-span-4 text-slate-500 font-medium">AC. Number</div>
                                            <div class="col-span-8 font-medium">{{ $claim->bank->ac_number }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @if($claim->semester_id > 0 || $claim->course_id > 0)
                                <div class="col-span-3">
                                    <h3 class="font-medium mb-3">Course & Semester</h3>
                                    @if($claim->course_id)
                                    <div class="grid grid-cols-12 gap-0 mb-2">
                                        <div class="col-span-4 text-slate-500 font-medium">Course</div>
                                        <div class="col-span-8 font-medium">{{ $claim->course->name }}</div>
                                    </div>
                                    @endif
                                    @if($claim->semester_id)
                                    <div class="grid grid-cols-12 gap-0 mb-2">
                                        <div class="col-span-4 text-slate-500 font-medium">Semester</div>
                                        <div class="col-span-8 font-medium">{{ $claim->semester->name }}</div>
                                    </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="intro-y box mt-5">
                    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <h2 class="font-medium text-base mr-auto">Students</h2>
                        <div class="sm:ml-auto mt-3 sm:mt-0 flex items-center justify-end">
                            <div class="selectedInstStats font-medium inline-flex items-center gap-5" style="display: {{ $claim->status == 2 ? 'inline-flex' : 'none' }};">
                                <span class="text-primary noOfStd">No of Student: {{ isset($claim->paid_installments_count) && $claim->paid_installments_count > 0 ? $claim->paid_installments_count : 0 }}</span>
                                <span class="text-success totalAmnt">No of Student: {{ isset($claim->invoice_total) && $claim->invoice_total > 0 ? Number::currency($claim->invoice_total, 'GBP') : Number::currency(0, 'GBP') }}</span>
                            </div>
                            @if($claim->status != 2)
                            <button type="submit" id="createInvoice" class="btn btn-success w-auto text-white ml-10">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i> Create Invoice                      
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
                            @endif
                        </div>
                    </div>
                    <div class="p-5">
                        <div class="overflow-x-auto">
                            <table class="table table-sm" id="universityClaimStudentsTable">
                                <thead>
                                    <tr>
                                        <th class="whitespace-nowrap">
                                            <div class="form-check">
                                                <input class="form-check-input m-0 checkUncheckAll" name="checkUncheckAll" type="checkbox" value="1">
                                            </div>
                                        </th>
                                        <th class="whitespace-nowrap">Name</th>
                                        <th class="whitespace-nowrap">Course</th>
                                        <th class="whitespace-nowrap">Amount</th>
                                        <th class="whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($claim->installments) && $claim->installments->count() > 0)
                                        @foreach($claim->installments as $inst)
                                            <tr class="claimRow row_{{$inst->id}}" id="row_{{ $inst->id }}">
                                                <td>
                                                    <div class="form-check">
                                                        <input {{ ($inst->status == 2 ? 'Checked' : '') }} class="form-check-input m-0 installMentCheck" name="ids[]" type="checkbox" value="{{ $inst->id }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <a href="{{ route('student.accounts', $inst->student_id) }}" class="block relative">
                                                        <div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">{{ $inst->student->registration_no }}</div>
                                                        <div class="font-medium whitespace-nowrap uppercase">{{ $inst->student->full_name }}</div>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="inline-block relative">
                                                        <div class="text-xs font-medium text-slate-400 whitespace-nowrap uppercase">{{ isset($inst->student->activeCR->course->name) ? $inst->student->activeCR->course->name : '' }}</div>
                                                        <div class="font-medium whitespace-normal uppercase">{{ isset($inst->student->activeCR->semester->name) ? $inst->student->activeCR->semester->name : '' }}</div>
                                                    </div>
                                                </td>
                                                <td>
                                                    {{ (isset($inst->installment->amount) && $inst->installment->amount > 0 ? Number::currency($inst->installment->amount, 'GBP') : Number::currency(0, 'GBP')) }}
                                                    <input type="hidden" name="installmentAmount[]" class="installmentAmount" value="{{ (isset($inst->installment->amount) && $inst->installment->amount > 0 ? $inst->installment->amount : 0) }}"/>
                                                </td>
                                                <td>
                                                    @if($inst->status == 2)
                                                        <span class="btn btn-success text-white text-xs px-2 py-0.5 font-medium  w-auto">Received</span>
                                                    @elseif($inst->status == 3)
                                                        <span class="btn btn-danger text-white btn-sm px-2 py-0.5 font-medium w-auto">Cancelled</span>
                                                    @else 
                                                        <span class="btn btn-primary text-white text-xs font-medium px-2 py-0.5 w-auto">Claimed</span>
                                                    @endif
                                                </td>
                                            </tr>

                                        @endforeach
                                    @else 
                                        <td colspan="100%" class="text-center">No data found!</td>
                                    @endif
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </form>


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
    @vite('resources/js/accounts-university-invoice-details.js')
@endsection
