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
        $accTotalBalance = array_sum($accBankBalances);
        $accAccountCount = (!empty($banks) ? $banks->count() : 0);
        $accIncomes = (isset($chartData['totalIncRaw']) ? $chartData['totalIncRaw'] : 0);
        $accExpenses = (isset($chartData['totalExpRaw']) ? $chartData['totalExpRaw'] : 0);
        $accNet = $accIncomes - $accExpenses;
    @endphp

    <div class="accounts-shell accounts-summary-redesign">
        <div class="acc-shell__layout">
            <!-- BEGIN: Accounts Menu -->
            <aside class="acc-shell__aside">
                @include('pages.accounts.sidebar', ['redesign' => true, 'bankBalances' => $accBankBalances])
            </aside>
            <!-- END: Accounts Menu -->

            <div class="acc-shell__main">
                <!-- BEGIN: Headline Figures (hidden for now — keep for later) -->
                {{--
                <div class="acc-sum__stats">
                    <div class="acc-sum__stat acc-sum__stat--accent">
                        <div class="acc-sum__stat-label">Net position &middot; 12 months</div>
                        <div class="acc-sum__stat-value tnum">{{ ($accNet >= 0 ? '£'.number_format($accNet, 2) : '-£'.number_format(abs($accNet), 2)) }}</div>
                        <div class="acc-sum__stat-note">Incomes &minus; Expenses</div>
                    </div>
                    <div class="acc-sum__stat">
                        <div class="acc-sum__stat-label">Total balance</div>
                        <div class="acc-sum__stat-value tnum">{{ ($accTotalBalance >= 0 ? '£'.number_format($accTotalBalance, 2) : '-£'.number_format(abs($accTotalBalance), 2)) }}</div>
                        <div class="acc-sum__stat-note">Across {{ $accAccountCount }} {{ ($accAccountCount == 1 ? 'account' : 'accounts') }}</div>
                    </div>
                    <div class="acc-sum__stat">
                        <div class="acc-sum__stat-label">Incomes</div>
                        <div class="acc-sum__stat-value acc-sum__stat-value--income tnum">{{ $chartData['totalInc'] }}</div>
                        <div class="acc-sum__stat-note">12-month total</div>
                    </div>
                    <div class="acc-sum__stat">
                        <div class="acc-sum__stat-label">Expenses</div>
                        <div class="acc-sum__stat-value acc-sum__stat-value--expense tnum">{{ $chartData['totalExp'] }}</div>
                        <div class="acc-sum__stat-note">{{ ($accIncomes > 0 ? number_format(($accExpenses / $accIncomes) * 100, 1).'% of incomes' : '12-month total') }}</div>
                    </div>
                </div>
                --}}
                <!-- END: Headline Figures -->

                <!-- BEGIN: Search -->
                <form method="post" action="#" id="summarySearchForm">
                    <div class="acc-sum__filter-row">
                        <div class="acc-sum__field acc-sum__field--date">
                            <i data-lucide="calendar" class="acc-sum__field-icon"></i>
                            <input type="text" placeholder="DD-MM-YYYY - DD-MM-YYYY" class="form-control" id="summary_date" name="summary_date">
                        </div>
                        <div class="acc-sum__field acc-sum__field--grow">
                            <input type="text" placeholder="Search transactions..." class="form-control" id="summary_search_query" name="summary_search_query"/>
                        </div>
                        <div class="acc-sum__field acc-sum__field--amount">
                            <input type="number" step="any" placeholder="Min amount" class="form-control" id="summary_min_amount" name="summary_min_amount"/>
                        </div>
                        <div class="acc-sum__field acc-sum__field--amount">
                            <input type="number" step="any" placeholder="Max amount" class="form-control" id="summary_max_amount" name="summary_max_amount"/>
                        </div>
                        <button type="button" id="advanceSearchToggle" class="acc-sum__adv-btn">
                            Advanced search
                            <span class="acc-sum__adv-chevron"><i data-lucide="chevron-down"></i></span>
                        </button>
                    </div>
                    <div class="advanceSearchGroup" style="display: none;">
                        <div class="acc-sum__adv-grid">
                            <div class="acc-sum__adv-field">
                                <label class="acc-sum__adv-label" for="summary_categories">Category</label>
                                <select id="summary_categories" name="summary_categories[]" multiple class="w-full tom-selects">
                                    <option value="">Select Category</option>
                                    @if($categories->count() > 0)
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="acc-sum__adv-field">
                                <label class="acc-sum__adv-label" for="summary_storages">Bank / Storage</label>
                                <select id="summary_storages" name="summary_storages[]" multiple class="w-full tom-selects">
                                    <option value="">Select Storage</option>
                                    @if($banks->count() > 0)
                                        @foreach($banks as $bnk)
                                            <option value="{{ $bnk->id }}">{{ $bnk->bank_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END: Search -->

                <div class="summarySearchResultWrap"></div>

                <!-- BEGIN: Cash Flow Report -->
                <div class="acc-sum__card">
                    <div class="acc-sum__card-head">
                        <h2 class="acc-sum__card-title">Cash flow report</h2>
                        <div class="acc-sum__legend">
                            <span class="acc-sum__legend-item acc-sum__legend-item--inc"><span class="acc-sum__legend-swatch"></span>Incomes</span>
                            <span class="acc-sum__legend-item acc-sum__legend-item--exp"><span class="acc-sum__legend-swatch"></span>Expenses</span>
                        </div>
                        <div class="acc-sum__field acc-sum__field--report">
                            <i data-lucide="calendar" class="acc-sum__field-icon"></i>
                            <input type="text" id="reportPicker" class="form-control" placeholder="Report range">
                        </div>
                    </div>
                    <div class="report-chart">
                        <div class="acc-sum__chart-canvas">
                            <canvas
                                id="report-line-chart"
                                data-months="{{ (isset($chartData['months']) && !empty($chartData['months']) ? json_encode($chartData['months']) : '') }}"
                                data-incomes="{{ (isset($chartData['incomes']) && !empty($chartData['incomes']) ? json_encode($chartData['incomes']) : '') }}"
                                data-expense="{{ (isset($chartData['expense']) && !empty($chartData['expense']) ? json_encode($chartData['expense']) : '') }}">
                            </canvas>
                        </div>
                    </div>
                </div>
                <!-- END: Cash Flow Report -->
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
    @vite('resources/js/accounts-summary.js')
@endsection
