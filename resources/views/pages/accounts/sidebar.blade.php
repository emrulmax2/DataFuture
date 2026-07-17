@php
    $accRedesign = (isset($redesign) && $redesign ? true : false);
    // AccBank::$balance is an uncached accessor costing 4 aggregate queries per
    // read, so resolve each bank once. The caller may hand the balances in to
    // avoid paying for them twice.
    $accBalances = (isset($bankBalances) && is_array($bankBalances) ? $bankBalances : []);
    if(empty($accBalances) && !empty($banks)):
        foreach($banks as $bnk):
            $accBalances[$bnk->id] = $bnk->balance;
        endforeach;
    endif;
    $accSidebarTotal = abs(array_sum($accBalances));
@endphp
<div class="2xl:border-r h-full pb-10 intro-y 2xl:pr-6 pt-6">
    <ul class="accountsMenu">
        <li class="mb-2">
            <a href="{{ route('accounts') }}" class="{{ Route::currentRouteName() == 'accounts' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center"><svg class="accountsMenu__icon mr-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 5h16M4 12h16M4 19h10"/></svg> Summary</a>
        </li>
        <li class="mb-2 hasDropdown">
            <a href="javascript:void(0);" class="active text-primary text-lg font-medium truncate flex justify-start items-center"><svg class="accountsMenu__icon mr-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6"/></svg> Bank / Storages</a>
            <div class="mt-3 accDropDown" style="display: block;">
                @if(!empty($banks))
                    @foreach ($banks as $bnk)
                        @php $accBal = (array_key_exists($bnk->id, $accBalances) ? $accBalances[$bnk->id] : $bnk->balance); @endphp
                        <a href="{{ route('accounts.storage', $bnk->id) }}" class="{{ (Route::currentRouteName() == 'accounts.csv.transactions' && (isset($bank->id) && $bank->id == $bnk->id)) || (Route::currentRouteName() == 'accounts.storage' && Route::current()->parameter('id') == $bnk->id) ? 'active text-primary' : '' }} bankItem box px-2 py-2 mb-2 flex items-center zoom-in">
                            @if($accRedesign && !$bnk->hasImage())
                                <div class="bankItem__mono" style="background: {{ $bnk->monogram_color }};">{{ $bnk->monogram }}</div>
                            @else
                                <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden bankItem__logo">
                                    <img alt="{{ $bnk->bank_name }}" src="{{ $bnk->image_url }}">
                                </div>
                            @endif
                            <div class="ml-4 mr-auto">
                                <div class="font-medium bankItem__name">{{ $bnk->bank_name }}</div>
                                @if($accRedesign)
                                    <div class="bankItem__meta">{{ ($accSidebarTotal > 0 ? number_format((abs($accBal) / $accSidebarTotal) * 100, 1) : '0.0') }}% of total</div>
                                @endif
                            </div>
                            <div class="py-1 px-2 rounded-full text-xs bg-{{ ($accBal < 0 ? 'danger' : 'success') }} text-white cursor-pointer font-medium bankItem__balance {{ ($accBal < 0 ? 'bankItem__balance--negative' : '') }}">{{ ($accBal >= 0 ? '£'.number_format($accBal, 2) : '-£'.number_format(str_replace('-', '', $accBal), 2)) }}</div>
                        </a>
                    @endforeach
                @endif
            </div>
        </li>
        @if($accRedesign)
            <li class="acc-shell__aside-divider"></li>
        @endif
        <li class="mb-2 pt-1">
            <a href="{{ route('reports.accounts') }}" class="{{ Route::currentRouteName() == 'reports.accounts' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center"><span class="accountsMenu__swatch mr-4"></span> Student Accounts</a>
        </li>
        <li class="mb-2 pt-1">
            <a href="{{ route('accounts.assets.register') }}" class="{{ Route::currentRouteName() == 'accounts.assets.register.new' || Route::currentRouteName() == 'accounts.assets.register' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center">
                <span class="accountsMenu__swatch mr-4"></span> Assets Register
                {!! ($openedAssets > 0 ? '<span data-count="'.$openedAssets.'" class="py-1 px-2 assetsRegCounter rounded-full text-xs bg-danger text-white cursor-pointer font-medium ml-3">'.$openedAssets.'</span>' : '') !!}
            </a>
        </li>
        <li class="mb-2 pt-1">
            <a href="{{ route('budget.management') }}" class="text-lg font-medium truncate flex justify-start items-center">
                <span class="accountsMenu__swatch mr-4"></span> Budget Management
            </a>
        </li>
        <li class="mb-2 pt-1">
            <a href="{{ route('university.claims') }}" class="text-lg font-medium truncate flex justify-start items-center">
                <span class="accountsMenu__swatch mr-4"></span> Invoice
            </a>
        </li>
    </ul>


    {{--<a href="{{ route('reports.accounts') }}" class="box px-2 py-2 mb-2 flex items-center zoom-in">
        <div class="w-10 h-10 flex-none bg-slate-500 rounded-full inline-flex justify-center items-center overflow-hidden">
            <i class="w-6 h-6 text-success" data-lucide="badge-pound-sterling"></i>
        </div>
        <div class="ml-4 mr-auto">
            <div class="font-medium">Student Accounts</div>
        </div>
        <div class="py-1 px-2 rounded-full text-xs bg-success text-white cursor-pointer font-medium">0</div>
    </a>--}}
</div>