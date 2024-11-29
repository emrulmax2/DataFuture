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
            <div class="intro-y box mt-2">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto"><strong><u>{{ $category->category_name }}</u></strong> Report from <strong><u>{{ date('jS F, Y', strtotime($startDate)) }}</u></strong> to <strong><u>{{ date('jS F, Y', strtotime($endDate)) }}</u></strong></h2>
                    <a href="{{ route('accounts.management.report', [$startDate, $endDate]) }}" class="add_btn btn btn-primary shadow-md ml-auto">Back To Report</a>
                </div>
            </div>

            <div class="intro-y box mt-5 p-5">
                @if($transactions->count() > 0)
                    <table class="table table-bordered table-striped table-sm" id="transactionListTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>TC</th>
                                <th>Invoice</th>
                                <th>Storage</th>
                                <th>Details</th>
                                @if(!$is_auditor)
                                <th>Description</th>
                                @endif
                                <th class="text-right">Withdrawl</th>
                                <th class="text-right">Deposit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $subTotal = 0;
                            @endphp
                            @foreach($transactions as $trns)
                                <tr>
                                    <td>{{ date('jS F, Y', strtotime($trns->transaction_date_2)) }}</td>
                                    <td>
                                        <div class="flex justify-start items-center">
                                            @if(isset($trns->transaction_doc_name) && $trns->transaction_doc_name != '')
                                                <a data-id="{{ $trns->id }}" href="javascript:void(0);" target="_blank" class="downloadTransDoc text-success mr-2" style="position: relative; top: -1px;"><i data-lucide="hard-drive-download" class="w-4 h-4"></i></a>
                                            @endif
                                            {{ $trns->transaction_code }}
                                            @if(isset($trns->receipts) && $trns->receipts->count() > 0)
                                                <a target="_blank" href="{{ route('reports.accounts.transaction.connection', $trns->id) }}" class="text-success ml-2" style="position: relative; top: -1px;"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right-left" class="lucide lucide-arrow-right-left w-4 h-4"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg></a>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{!! $trns->invoice_no.(!empty($trns->invoice_date) ? '<br/>'.date('jS M, Y', strtotime($trns->invoice_date)) : '') !!}</td>
                                    <td>{{ (isset($trns->bank->bank_name) ? $trns->bank->bank_name : '') }}</td>
                                    <td>{{ $trns->detail }}</td>
                                    @if(!$is_auditor)
                                    <td>{{ $trns->description }}</td>
                                    @endif
                                    <td class="text-right">{{ ($trns->flow == 1 ? '£'.number_format($trns->transaction_amount, 2) : '') }}</td>
                                    <td class="text-right">{{ ($trns->flow != 1 ? '£'.number_format($trns->transaction_amount, 2) : '') }}</td>
                                </tr>
                                @php 
                                    if($trns->flow == 1):
                                        $subTotal -= $trns->transaction_amount;
                                    else:
                                        $subTotal += $trns->transaction_amount;
                                    endif;
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="{{ ($is_auditor ? 5 : 6) }}">Sub Total</th>
                                <th colspan="2" class="text-right">{{ ($subTotal >= 0 ? '£'.number_format($subTotal, 2) : '-£'.number_format(str_replace('-', '', $subTotal), 2)) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                        <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Transactions not found
                    </div>
                @endif
            </div>
        </div>
    </div>

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
    @vite('resources/js/accounts-management-report-details.js')
@endsection
