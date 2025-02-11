@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Transaction Connection</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports.accounts') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Accounts</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <form method="post" action="#" id="transConnectionForm">
        <div class="intro-y box mt-5">
            <div class="grid grid-cols-12 gap-0 items-center p-5">
                <div class="col-span-6">
                    <div class="font-medium text-base">Transaction Details</div>
                </div>

                <div class="col-span-6 text-right">
                    <button style="display: none;" type="submit" id="saveConnectionBtn" class="btn btn-success text-white w-auto mr-0 mb-0">
                        <i data-lucide="arrow-right-left" class="w-4 h-4 mr-2"></i> Save Connection 
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
                    <input type="hidden" name="acc_transaction_id" value="{{ $transaction->id }}"/>
                    <a href="{{ route('reports.accounts.transaction.connection.print', $transaction->id) }}" class="btn btn-linkedin text-white w-auto ml-1"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                    <a href="{{ route('reports.accounts.transaction.connection.export', $transaction->id) }}" class="btn btn-facebook text-white w-auto ml-1"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                </div>
            </div>
            <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="p-5">
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-3">
                        <div class="text-slate-500 font-medium mb-1">Transaction of {{ (!empty($transaction->transaction_date_2) ? '('.date('d-m-Y', strtotime($transaction->transaction_date_2)).')' : '') }}</div>
                        <div class="font-medium">{{ $transaction->transaction_code }}</div>
                    </div>
                    <div class="col-span-3">
                        <div class="text-slate-500 font-medium mb-1">Amount</div>
                        <div class="font-medium" id="transactionAmount">{{ '£'.number_format($transaction->transaction_amount, 2) }}</div>
                        <input type="hidden" id="transaction_amount" name="transaction_amount" value="{{ $transaction->transaction_amount }}"/>
                    </div>
                    <div class="col-span-6">
                        <div class="grid grid-cols-12 gap-0 mb-1">
                            <div class="col-span-6 text-slate-500 font-medium">Course Fees Received</div>
                            <div class="col-span-6 font-medium" id="totalCourseFees">£0.00</div>
                        </div>
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-6 text-slate-500 font-medium">Refund</div>
                            <div class="col-span-6 font-medium" id="totalRefunds">£0.00</div>
                        </div>
                    </div>
                </div>

                <div class="col-span-12 pt-5">
                    @if($moneyReceipt->count() > 0)
                        <table class="table table-bordered table-sm" id="connectionListTable">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <div class="form-check justify-center">
                                            <input id="checkAll" name="checkAll" class="form-check-input checkAll" type="checkbox" value="1">
                                        </div>
                                    </th>
                                    <th>Date</th>
                                    <th>Invoice No</th>
                                    <th>Student ID</th>
                                    <th>SSN</th>
                                    <th>Name</th>
                                    <th>Payment Type</th>
                                    <th>Amount</th>
                                    <th>Indicator</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($moneyReceipt as $rec)
                                    <tr class="receiptRow {{ ($transaction->id == $rec->acc_transaction_id ? 'selectedRow' : '' )}} {{ ($rec->payment_type == 'Refund' ? 'bg-danger-soft' : '') }}">
                                        <td class="text-enter">
                                            <div class="form-check justify-center">
                                                <input {{ ($transaction->id == $rec->acc_transaction_id ? 'Checked' : '' )}} id="slc_money_receipt_id_{{ $rec->id }}" name="slc_money_receipt_ids[]" class="form-check-input slc_money_receipt_id" type="checkbox" value="{{ $rec->id }}">
                                            </div>
                                        </td>
                                        <td>
                                            {{ (isset($rec->payment_date) && !empty($rec->payment_date) ? date('d-m-Y', strtotime($rec->payment_date)) : '')}}
                                        </td>
                                        <td>
                                            {{ (isset($rec->invoice_no) && !empty($rec->invoice_no) ? $rec->invoice_no : '')}}
                                        </td>
                                        <td>
                                            {!! (isset($rec->student->registration_no) && !empty($rec->student->registration_no) ? '<a href="'.route('student.accounts', $rec->student_id).'" class="font-medium text-primary">'.$rec->student->registration_no.'</a>' : '') !!}
                                        </td>
                                        <td>
                                            {{ (isset($rec->student->ssn_no) && !empty($rec->student->ssn_no) ? $rec->student->ssn_no : '') }}
                                        </td>
                                        <td>
                                            {{ (isset($rec->student->full_name) && !empty($rec->student->full_name) ? $rec->student->full_name : '')}}
                                        </td>
                                        <td>
                                            {{ $rec->payment_type }}
                                            <input type="hidden" class="payment_type" name="payment_type[]" value="{{ $rec->payment_type }}"/>
                                        </td>
                                        <td>
                                            £{{ number_format($rec->amount, 2); }}
                                            <input type="hidden" class="amount" name="amount[]" value="{{ $rec->amount }}"/>
                                        </td>
                                        <td>
                                            {{ (isset($rec->agreement->is_self_funded) && $rec->agreement->is_self_funded == 1 ? 'Yes' : '') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else 
                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Money receipts not found for the day {{ (!empty($transaction->transaction_date_2) ? date('d-m-Y', strtotime($transaction->transaction_date_2)) : '') }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

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
                        <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
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
@endsection

@section('script')
    @vite('resources/js/slc-connection-reports.js')
@endsection