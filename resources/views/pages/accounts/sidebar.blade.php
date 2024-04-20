<div class="2xl:border-r h-full pb-10 intro-y 2xl:pr-6 pt-6">
    <ul class="accountsMenu">
        <li class="mb-2">
            <a href="{{ route('accounts') }}" class="{{ Route::currentRouteName() == 'accounts' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center"><i data-lucide="sliders-horizontal" class="w-5 h-5 mr-4"></i> Summary</a>
        </li>
        <li class="mb-2 hasDropdown">
            <a href="javascript:void(0);" class="{{ Route::currentRouteName() == 'accounts.storage' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center"><i data-lucide="landmark" class="w-5 h-5 mr-4"></i> Bank / Storages</a>
            <div class="mt-3 accDropDown" style="padding-left: 37px; display: {{ Route::currentRouteName() == 'accounts.storage' ? 'block' : 'none' }};">
                @if(!empty($banks))
                    @foreach ($banks as $bank)
                        <a href="{{ route('accounts.storage', $bank->id) }}" class="{{ Route::currentRouteName() == 'accounts.storage' && Route::current()->parameter('id') == $bank->id ? 'active text-primary' : '' }} bankItem box px-4 py-4 mb-3 flex items-center zoom-in">
                            <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                <img alt="{{ $bank->bank_name }}" src="{{ $bank->image_url }}">
                            </div>
                            <div class="ml-4 mr-auto">
                                <div class="font-medium">{{ $bank->bank_name }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ '£'.number_format($bank->opening_balance, 2) }}</div>
                            </div>
                            <div class="py-1 px-2 rounded-full text-xs bg-success text-white cursor-pointer font-medium">{{ '£'.number_format($bank->opening_balance, 2) }}</div>
                        </a>
                    @endforeach
                @endif
            </div>
        </li>
    </ul>
</div>