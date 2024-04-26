<div class="2xl:border-r h-full pb-10 intro-y 2xl:pr-6 pt-6">
    <ul class="accountsMenu">
        <li class="mb-2">
            <a href="{{ route('accounts') }}" class="{{ Route::currentRouteName() == 'accounts' ? 'active text-primary' : '' }} text-lg font-medium truncate flex justify-start items-center"><i data-lucide="lamp-desk" class="w-5 h-5 mr-4"></i> Summary</a>
        </li>
        <li class="mb-2 hasDropdown">
            <a href="javascript:void(0);" class="active text-primary text-lg font-medium truncate flex justify-start items-center"><i data-lucide="landmark" class="w-5 h-5 mr-4"></i> Bank / Storages</a>
            <div class="mt-3 accDropDown" style="display: block;">
                @if(!empty($banks))
                    @foreach ($banks as $bnk)
                        <a href="{{ route('accounts.storage', $bnk->id) }}" class="{{ (Route::currentRouteName() == 'accounts.csv.transactions' && (isset($bank->id) && $bank->id == $bnk->id)) || (Route::currentRouteName() == 'accounts.storage' && Route::current()->parameter('id') == $bnk->id) ? 'active text-primary' : '' }} bankItem box px-2 py-2 mb-2 flex items-center zoom-in">
                            <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden">
                                <img alt="{{ $bnk->bank_name }}" src="{{ $bnk->image_url }}">
                            </div>
                            <div class="ml-4 mr-auto">
                                <div class="font-medium">{{ $bnk->bank_name }}</div>
                            </div>
                            <div class="py-1 px-2 rounded-full text-xs bg-{{ ($bnk->balance < 0 ? 'danger' : 'success') }} text-white cursor-pointer font-medium">{{ '£'.number_format($bnk->balance, 2) }}</div>
                        </a>
                    @endforeach
                @endif
            </div>
        </li>
    </ul>
</div>