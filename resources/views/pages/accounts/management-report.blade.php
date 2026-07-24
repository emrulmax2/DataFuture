@extends('../layout/' . $layout)

@section('body_class', 'accounts-shell-body')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        // Resolved once here and handed to the sidebar partial: reading
        // AccBank::$balance costs 4 aggregate queries per bank and is uncached.
        $accBankBalances = [];
        if(!empty($banks)):
            foreach($banks as $bnk):
                $accBankBalances[$bnk->id] = $bnk->balance;
            endforeach;
        endif;
    @endphp

    <div class="accounts-shell accounts-management-report-redesign">
        <div class="acc-shell__layout">
            <!-- BEGIN: Accounts Menu -->
            <aside class="acc-shell__aside">
                @include('pages.accounts.sidebar', ['redesign' => true, 'bankBalances' => $accBankBalances])
            </aside>
            <!-- END: Accounts Menu -->

            <div class="acc-shell__main">
                <!-- BEGIN: Report Header -->
                <div class="acc-mr__head">
                    <div class="acc-mr__head-titles">
                        <div class="acc-mr__eyebrow">Management Report</div>
                        <h1 class="acc-mr__title">
                            Report from <span>{{ date('jS M, Y', strtotime($startDate)) }}</span>
                            to <span>{{ date('jS M, Y', strtotime($endDate)) }}</span>
                        </h1>
                    </div>
                    <div class="acc-mr__head-actions">
                        <div class="acc-mr__date">
                            <i data-lucide="calendar" class="acc-mr__date-icon"></i>
                            <input type="text" id="reportPicker" class="form-control" placeholder="Report range">
                        </div>
                        <div class="dropdown acc-mr__export">
                            <button class="dropdown-toggle acc-mr__export-btn" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="file-text" class="w-4 h-4"></i> Export or Print
                                <i data-lucide="chevron-down" class="w-4 h-4 acc-mr__export-chevron"></i>
                            </button>
                            <div class="dropdown-menu acc-mr__export-menu w-52">
                                <ul class="dropdown-content">
                                    <li>
                                        <a href="{{ route('accounts.management.report.export.incomes', [$startDate, $endDate]) }}" class="dropdown-item">
                                            <i data-lucide="file-down" class="w-4 h-4 mr-2"></i> Export Incomes
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('accounts.management.report.export.expenses', [$startDate, $endDate]) }}" class="dropdown-item">
                                            <i data-lucide="file-down" class="w-4 h-4 mr-2"></i> Export Expenditure
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('accounts.management.report.print', [$startDate, $endDate]) }}" class="dropdown-item">
                                            <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print PDF
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: Report Header -->

                <!-- BEGIN: Profit & Loss Statement -->
                <div class="acc-mr__card">
                    <table class="acc-mr__table managementReportTable" id="managementReportTable">
                        @php
                            $PROFIT = $all_sales['total_sale'];
                            $COS_TOTAL = 0;
                            $GROSS_PROFIT = 0;
                            $EXPENDITURE_TOTAL = 0;
                        @endphp
                        <tbody>
                            <tr class="acc-mr__lead-row">
                                <td colspan="3">
                                    <a href="javascript:void(0);" class="cursor-pointer toggleSalesParentRows acc-mr__toggle"><i data-lucide="arrow-up-down" class="w-3.5 h-3.5"></i> Sales</a>
                                </td>
                                <td class="w-[180px] text-right acc-mr__amount">
                                    {{ number_format($all_sales['total_sale'], 2) }}
                                </td>
                            </tr>
                            @if(!empty($all_sales['incomes']))
                                @foreach($all_sales['incomes'] as $perent_id => $sale)
                                    <tr class="sales_parent_row acc-mr__item-row" data-id="{{ $perent_id }}" style="display: none;">
                                        <td colspan="2">
                                            <a href="{{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'javascript:void(0);' : route('accounts.management.report.show', [$startDate, $endDate, $perent_id]))}}" data-parent="{{ $perent_id }}" class="cursor-pointer {{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'toggleSalesChildRows' : '')}} acc-mr__link">
                                                @if(isset($sale['has_children']) && $sale['has_children'] == 1)
                                                <i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i>
                                                @endif
                                                {{ $sale['name'] }}
                                            </a>
                                        </td>
                                        <td class="w-[180px] text-right acc-mr__amount">{{ number_format($sale['amount'], 2) }}</td>
                                        <td class="w-[180px] text-right"></td>
                                    </tr>
                                    @if(isset($sale['childs']) && !empty($sale['childs']))
                                        @foreach($sale['childs'] as $sale_id => $child)
                                            <tr class="sales_child_row sales_child_of_{{ $perent_id }} acc-mr__item-row acc-mr__item-row--sub" style="display: none;">
                                                <td><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $sale_id]) }}" class="acc-mr__link">{{ $child['name'] }}</a></td>
                                                <td class="w-[180px] text-right acc-mr__amount">{{ number_format($child['amount'], 2) }}</td>
                                                <td class="w-[180px] text-right"></td>
                                                <td class="w-[180px] text-right"></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            @if(!empty($cos))
                                <tr class="cosHeadingRow acc-mr__section-row">
                                    <td colspan="3" class="acc-mr__section">Cost Of Sales</td>
                                    <td></td>
                                </tr>
                                @foreach($cos as $cs_id => $cs)
                                    @php $COS_TOTAL += $cs['amount']; @endphp
                                    <tr class="acc-mr__item-row">
                                        <td colspan="3"><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $cs_id]) }}" class="acc-mr__link">{{ $cs['name'] }}</a></td>
                                        <td class="w-[180px] text-right acc-mr__amount">{{ number_format($cs['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @php
                                $GROSS_PROFIT = ($PROFIT - $COS_TOTAL);
                            @endphp
                            <tr class="gpHeadingRow acc-mr__total-row">
                                <td colspan="3" class="acc-mr__total-label">Gross Profit</td>
                                <td class="w-[180px] text-right acc-mr__amount acc-mr__amount--total">{{ number_format($GROSS_PROFIT, 2) }}</td>
                            </tr>

                            @if(!empty($all_other_income['incomes']))
                                @foreach($all_other_income['incomes'] as $perent_id => $sale)
                                    <tr class="other_income_parent_row acc-mr__lead-row {{ ($loop->first ? 'oiFirstHeadingRow' : '') }}" data-id="{{ $perent_id }}">
                                        <td colspan="3">
                                            <a href="{{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'javascript:void(0);' : route('accounts.management.report.show', [$startDate, $endDate, $perent_id]))}}" data-parent="{{ $perent_id }}" class="cursor-pointer {{ (isset($sale['has_children']) && $sale['has_children'] == 1 ? 'toggleOtherChildRows' : '')}} acc-mr__toggle">
                                                @if(isset($sale['has_children']) && $sale['has_children'] == 1)
                                                <i data-lucide="arrow-up-down" class="w-3.5 h-3.5"></i>
                                                @endif
                                                {{ $sale['name'] }}
                                            </a>
                                        </td>
                                        <td class="w-[180px] text-right acc-mr__amount">{{ number_format($sale['amount'], 2) }}</td>
                                    </tr>
                                    @if(isset($sale['childs']) && !empty($sale['childs']))
                                        @foreach($sale['childs'] as $sale_id => $child)
                                            <tr class="other_child_row other_child_of_{{ $perent_id }} acc-mr__item-row acc-mr__item-row--sub" style="display: none;">
                                                <td colspan="2"><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $sale_id]) }}" class="acc-mr__link">{{ $child['name'] }}</a></td>
                                                <td class="w-[180px] text-right acc-mr__amount">{{ number_format($child['amount'], 2) }}</td>
                                                <td class="w-[180px] text-right"></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            @php
                                $GROSS_PROFIT += $all_other_income['total_sale'];
                            @endphp
                            <tr class="aoiHeadingRow acc-mr__total-row">
                                <td colspan="3" class="acc-mr__total-label"></td>
                                <td class="w-[180px] text-right acc-mr__amount acc-mr__amount--total">{{ number_format($GROSS_PROFIT, 2) }}</td>
                            </tr>

                            @if(!empty($expenditure))
                            <tr class="expdHeadingRow acc-mr__section-row">
                                <td colspan="3" class="acc-mr__section">Expenditure</td>
                                <td class="w-[180px] text-right"></td>
                            </tr>
                                @foreach($expenditure as $perent_id => $expd)
                                    @php $EXPENDITURE_TOTAL += $expd['amount']; @endphp
                                    <tr class="parent_row acc-mr__item-row" data-id="{{ $perent_id }}">
                                        <td colspan="2"><a href="javascript:void(0);" data-parent="{{ $perent_id }}" class="cursor-pointer toggleChildRows acc-mr__link"><i data-lucide="arrow-up-down" class="w-3 h-3 mr-1"></i> {{ $expd['name'] }}</a></td>
                                        <td class="w-[180px] text-right acc-mr__amount">{{ number_format($expd['amount'], 2) }}</td>
                                        <td class="w-[180px] text-right"></td>
                                    </tr>
                                    @if($expd['childs'] && !empty($expd['childs']))
                                        @foreach($expd['childs'] as $exped_id => $child)
                                            <tr class="child_row child_of_{{ $perent_id }} acc-mr__item-row acc-mr__item-row--sub" style="display: none;">
                                                <td><a target="_blank" href="{{ route('accounts.management.report.show', [$startDate, $endDate, $exped_id]) }}" class="acc-mr__link">{{ $child['name'] }}</a></td>
                                                <td class="w-[180px] text-right acc-mr__amount">{{ number_format($child['amount'], 2) }}</td>
                                                <td class="w-[180px] text-right"></td>
                                                <td class="w-[180px] text-right"></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                @php
                                    $GROSS_PROFIT -= $EXPENDITURE_TOTAL;
                                @endphp
                                <tr class="texpdHeadingRow acc-mr__total-row">
                                    <td colspan="3" class="acc-mr__total-label"></td>
                                    <td class="w-[180px] text-right acc-mr__amount acc-mr__amount--total">{{ number_format($EXPENDITURE_TOTAL, 2) }}</td>
                                </tr>
                                <tr class="npHeadingRow acc-mr__net-row">
                                    <td colspan="3" class="acc-mr__net-label">Net Profit</td>
                                    <td class="w-[180px] text-right acc-mr__amount acc-mr__amount--net">{{ number_format($GROSS_PROFIT, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <!-- END: Profit & Loss Statement -->
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
