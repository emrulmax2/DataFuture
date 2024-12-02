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
                    <div class="sm:ml-auto mt-3 sm:mt-0 flex items-center justify-end">
                        <div class="btn btn-outline-secondary flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 ml-3">
                            <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i>
                            <input type="text" id="reportPicker" class="w-full form-control border-0">
                        </div>
                        <div class="dropdown w-1/2 sm:w-auto ml-2">
                            <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Exports <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                            </button>
                            <div class="dropdown-menu w-48">
                                <ul class="dropdown-content">
                                    <li>
                                        <a href="{{ route('accounts.management.report.export.incomes', [$startDate, $endDate]) }}" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Incomes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('accounts.management.report.export.expenses', [$startDate, $endDate]) }}" class="dropdown-item">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Expenditure
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="intro-y box mt-5 p-5">
                <table class="table table-borderless table-sm managementReportTable" id="managementReportTable">
                    @php 
                        $PROFIT = $all_sales['total_sale'];
                        $COS_TOTAL = 0;
                        $GROSS_PROFIT = 0;
                        $EXPENDITURE_TOTAL = 0;
                    @endphp
                    <tbody>
                        <tr>
                            <td colspan="3">
                                <a href="javascript:void(0);" class="cursor-pointer toggleSalesParentRows font-medium text-primary underline inline-flex items-center"><i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i> Sales</a>
                            </td>
                            <td class="w-[180px] text-right">
                                {{ number_format($all_sales['total_sale'], 2) }}
                            </td>
                        </tr>
                        @if(!empty($all_sales['incomes']))
                            @foreach($all_sales['incomes'] as $perent_id => $sale)
                                <tr class="sales_parent_row" data-id="{{ $perent_id }}" style="display: none;">
                                    <td colspan="2">
                                        <a href="{{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'javascript:void(0);' : route('accounts.management.report.show', [$startDate, $endDate, $perent_id]))}}" data-parent="{{ $perent_id }}" class="cursor-pointer {{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'toggleSalesChildRows' : '')}} text-primary underline inline-flex items-center">
                                            @if(isset($sale['has_children']) && $sale['has_children'] == 1)
                                            <i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i> 
                                            @endif
                                            {{ $sale['name'] }}
                                        </a>
                                    </td>
                                    <td class="w-[180px] text-right">{{ number_format($sale['amount'], 2) }}</td>
                                    <td class="w-[180px] text-right"></td>
                                </tr>
                                @if(isset($sale['childs']) && !empty($sale['childs']))
                                    @foreach($sale['childs'] as $sale_id => $child)
                                        <tr class="sales_child_row sales_child_of_{{ $perent_id }}" style="display: none;">
                                            <td><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $sale_id]) }}" class="text-primary underline">{{ $child['name'] }}</a></td>
                                            <td class="w-[180px] text-right">{{ number_format($child['amount'], 2) }}</td>
                                            <td class="w-[180px] text-right"></td>
                                            <td class="w-[180px] text-right"></td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif

                        @if(!empty($cos))
                            <tr class="cosHeadingRow">
                                <td colspan="3" class="font-medium">Cose Of Sales</td>
                                <td></td>
                            </tr>
                            @foreach($cos as $cs_id => $cs)
                                @php $COS_TOTAL += $cs['amount']; @endphp
                                <tr>
                                    <td colspan="3"><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $cs_id]) }}" class="text-primary underline">{{ $cs['name'] }}</a></td>
                                    <td class="w-[180px] text-right">{{ number_format($cs['amount'], 2) }}</td>
                                </tr>
                            @endforeach
                        @endif
                        @php 
                            $GROSS_PROFIT = ($PROFIT - $COS_TOTAL);
                        @endphp
                        <tr class="gpHeadingRow">
                            <td colspan="3" class="font-medium uppercase">Gross Profit</td>
                            <td class="w-[180px] text-right">{{ number_format($GROSS_PROFIT, 2) }}</td>
                        </tr>

                        @if(!empty($all_other_income['incomes']))
                            @foreach($all_other_income['incomes'] as $perent_id => $sale)
                                <tr class="other_income_parent_row {{ ($loop->first ? 'oiFirstHeadingRow' : '') }}" data-id="{{ $perent_id }}">
                                    <td colspan="3">
                                        <a href="{{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'javascript:void(0);' : route('accounts.management.report.show', [$startDate, $endDate, $perent_id]))}}" data-parent="{{ $perent_id }}" class="cursor-pointer {{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'toggleOtherChildRows' : '')}} text-primary font-medium underline inline-flex items-center">
                                            @if(isset($sale['has_children']) && $sale['has_children'] == 1)
                                            <i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i> 
                                            @endif
                                            {{ $sale['name'] }}
                                        </a>
                                    </td>
                                    <td class="w-[180px] text-right">{{ number_format($sale['amount'], 2) }}</td>
                                </tr>
                                @if(isset($sale['childs']) && !empty($sale['childs']))
                                    @foreach($sale['childs'] as $sale_id => $child)
                                        <tr class="other_child_row other_child_of_{{ $perent_id }}" style="display: none;">
                                            <td colspan="2"><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $sale_id]) }}" class="text-primary underline">{{ $child['name'] }}</a></td>
                                            <td class="w-[180px] text-right">{{ number_format($child['amount'], 2) }}</td>
                                            <td class="w-[180px] text-right"></td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @endif

                        @php 
                            $GROSS_PROFIT += $all_other_income['total_sale'];
                        @endphp
                        <tr class="aoiHeadingRow">
                            <td colspan="3" class="font-medium"></td>
                            <td class="w-[180px] text-right">{{ number_format($GROSS_PROFIT, 2) }}</td>
                        </tr>

                        @if(!empty($expenditure))
                        <tr class="expdHeadingRow">
                            <td colspan="3" class="font-medium">Expenditure</td>
                            <td class="w-[180px] text-right"></td>
                        </tr>
                            @foreach($expenditure as $perent_id => $expd)
                                @php $EXPENDITURE_TOTAL += $expd['amount']; @endphp
                                <tr class="parent_row" data-id="{{ $perent_id }}">
                                    <td colspan="2"><a href="javascript:void(0);" data-parent="{{ $perent_id }}" class="cursor-pointer toggleChildRows text-primary underline inline-flex items-center"><i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i> {{ $expd['name'] }}</a></td>
                                    <td class="w-[180px] text-right">{{ number_format($expd['amount'], 2) }}</td>
                                    <td class="w-[180px] text-right"></td>
                                </tr>
                                @if($expd['childs'] && !empty($expd['childs']))
                                    @foreach($expd['childs'] as $exped_id => $child)
                                        <tr class="child_row child_of_{{ $perent_id }}" style="display: none;">
                                            <td><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $exped_id]) }}" class="text-primary underline">{{ $child['name'] }}</a></td>
                                            <td class="w-[180px] text-right">{{ number_format($child['amount'], 2) }}</td>
                                            <td class="w-[180px] text-right"></td>
                                            <td class="w-[180px] text-right"></td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            @php 
                                $GROSS_PROFIT -= $EXPENDITURE_TOTAL;
                            @endphp
                            <tr class="texpdHeadingRow">
                                <td colspan="3" class="font-medium"></td>
                                <td class="w-[180px] text-right">{{ number_format($EXPENDITURE_TOTAL, 2) }}</td>
                            </tr>
                            <tr class="npHeadingRow">
                                <td colspan="3" class="font-medium">NET PROFIT</td>
                                <td class="w-[180px] text-right">{{ number_format($GROSS_PROFIT, 2) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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
    @vite('resources/js/accounts-management-report.js')
@endsection
