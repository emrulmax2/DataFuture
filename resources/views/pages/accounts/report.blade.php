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
                    <h2 class="font-medium text-base mr-auto">Report from <strong><u>{{ date('jS F, Y', strtotime($startDate)) }}</u></strong> to <strong><u>{{ date('jS F, Y', strtotime($endDate)) }}</u></strong></h2>
                    {{--<a href="#" class="add_btn btn btn-primary shadow-md ml-auto">Add New SMTP</a>--}}
                    <div class="sm:ml-auto mt-3 sm:mt-0 border relative text-slate-500">
                        <i data-lucide="calendar" class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>
                        <input type="text" id="reportPicker" class="form-control sm:w-56 box pl-10">
                    </div>
                </div>
            </div>
            @php 
                $inflowTotal = 0;
                $outflowTotal = 0;
            @endphp
            @if(!empty($inflows))
            <div class="intro-y box mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto inline-flex justify-start items-center"><i data-lucide="arrow-left" class="w-4 h-4 mr-3"></i>Inflows</h2>
                </div>
                <div class="p-5">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Inflow</th>
                                <th class="w-44 text-left">No of Transactions</th>
                                <th class="text-right">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inflows as $category_id => $inf)
                                @php
                                    if($inf['sub_total'] >= 0):
                                        $inflowTotal += $inf['sub_total'];
                                    else:
                                        $inflowTotal += $inf['sub_total'];
                                    endif;
                                @endphp
                                <tr class="tr_{{ $category_id }}">
                                    <td class="text-left">
                                        <a data-id="{{ $category_id }}" data-start="{{ date('Y-m-d', strtotime($startDate)) }}" data-end="{{ date('Y-m-d', strtotime($endDate)) }}" href="javascript:void(0);" class="categoryToggler text-success underline font-medium inline-flex justify-start items-center">
                                            {!! $inf['name'] !!}
                                            <svg style="display: none;"  width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(30, 41, 59)" class="w-4 h-4 ml-3">
                                                <g fill="none" fill-rule="evenodd">
                                                    <g transform="translate(1 1)" stroke-width="4">
                                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                        </path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </a>
                                    </td>
                                    <td class="w-44 text-left">{{ $inf['no_of'] }}</td>
                                    <td class="w-52 text-right"><strong>{{ ($inf['sub_total'] >= 0 ? '£'.number_format($inf['sub_total'], 2) : '-£'.number_format(str_replace('-', '', $inf['sub_total']), 2) ) }}</strong></td>
                                </tr>
                                <tr class="dt_{{ $category_id }}" style="display: none;">
                                    <td class="data_td" colspan="100%">

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-left">Sub Total</th>
                                <th class="w-52 text-right">{{ ($inflowTotal >= 0 ? '£'.number_format($inflowTotal, 2) : '-£'.number_format(str_replace('-', '', $inflowTotal), 2) ) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($outflows))
                @foreach($outflows as $pcat_id => $category)
                    @php 
                        $outflowCatTotal = 0;
                    @endphp
                    <div class="intro-y box mt-5">
                        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                            <h2 class="font-medium text-base mr-auto inline-flex justify-start items-center"><i data-lucide="arrow-right" class="w-4 h-4 mr-3"></i>{{ $category['name'] }}</h2>
                        </div>
                        <div class="p-5">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th class="text-left">Outflow</th>
                                        <th class="w-44 text-left">No of Transactions</th>
                                        <th class="text-right">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($category['childs'] as $category_id => $inf)
                                        @php
                                            $outflowCatTotal += $inf['sub_total'];
                                        @endphp
                                        <tr class="tr_{{ $category_id }}">
                                            <td class="text-left">
                                                <a data-id="{{ $category_id }}" data-start="{{ date('Y-m-d', strtotime($startDate)) }}" data-end="{{ date('Y-m-d', strtotime($endDate)) }}" href="javascript:void(0);" class="categoryToggler text-success underline font-medium inline-flex justify-start items-center">
                                                    {!! $inf['name'] !!}
                                                    <svg style="display: none;"  width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(30, 41, 59)" class="w-4 h-4 ml-3">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <g transform="translate(1 1)" stroke-width="4">
                                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                                </path>
                                                            </g>
                                                        </g>
                                                    </svg>
                                                </a>
                                            </td>
                                            <td class="w-44 text-left">{{ $inf['no_of'] }}</td>
                                            <td class="w-52 text-right"><strong>{{ ($inf['sub_total'] >= 0 ? '£'.number_format($inf['sub_total'], 2) : '-£'.number_format(str_replace('-', '', $inf['sub_total']), 2) ) }}</strong></td>
                                        </tr>
                                        <tr class="dt_{{ $category_id }}" style="display: none;">
                                            <td class="data_td" colspan="100%">

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-left">Sub Total</th>
                                        <th class="w-52 text-right">{{ ($outflowCatTotal >= 0 ? '£'.number_format($outflowCatTotal, 2) : '-£'.number_format(str_replace('-', '', $outflowCatTotal), 2) ) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    @php 
                        $outflowTotal += $outflowCatTotal;
                    @endphp
                @endforeach
            @endif

            <div class="intro-y box mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto inline-flex justify-start items-center"><i data-lucide="calculator" class="w-4 h-4 mr-3"></i>Operating Income</h2>
                    <h2 class="font-medium text-{{ $outflowTotal > $inflowTotal ? 'danger' : 'success' }} ml-auto">
                        @if($inflowTotal > $outflowTotal)
                            £{{ number_format(($inflowTotal - $outflowTotal), 2) }}
                        @elseif($outflowTotal > $inflowTotal)
                            -£{{ number_format(str_replace('-', '', ($outflowTotal - $inflowTotal)), 2) }}
                        @else 
                            £{{ number_format(($inflowTotal - $outflowTotal), 2) }}
                        @endif
                    </h2>
                </div>
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
    @vite('resources/js/accounts-report.js')
@endsection
