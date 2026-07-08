@php
    $epLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->pluck('value', 'name')->toArray();
    $epLogoUrl = (isset($epLogo['site_logo']) && !empty($epLogo['site_logo']) && Storage::disk('local')->exists('public/'.$epLogo['site_logo']))
        ? Storage::disk('local')->url('public/'.$epLogo['site_logo'])
        : null;

    $epUser = Auth::user();
    $epEmp  = optional($epUser)->employee ?? (optional($epUser)->load('employee')->employee ?? null);
    $epName = $epEmp && isset($epEmp->title->name)
        ? trim($epEmp->title->name.' '.$epEmp->first_name.' '.$epEmp->last_name)
        : (optional($epUser)->email ?? 'Staff User');
    $epJobTitle = $epEmp?->employment?->employeeJobTitle?->name ?? '';
    $epInitials = '';
    if($epEmp) {
        $epInitials = strtoupper(substr($epEmp->first_name ?? '', 0, 1).substr($epEmp->last_name ?? '', 0, 1));
    }
    if($epInitials === '') { $epInitials = strtoupper(substr($epName, 0, 2)); }
    $epActiveIdx = $first_level_active_index ?? null;
@endphp

<header class="ep-appbar">
    <div class="ep-appbar__inner">

        {{-- Brand --}}
        <a href="{{ route('staff.dashboard') }}" class="ep-brand">
            @if($epLogoUrl)
                <img src="{{ $epLogoUrl }}" alt="London Churchill College" class="ep-brand__img">
            @else
                <span class="ep-brand__mark">LC</span>
            @endif
        </a>

        {{-- Primary nav (real, privilege-driven $top_menu) --}}
        <nav class="ep-nav">
            @foreach($top_menu as $menuKey => $menu)
                @php $hasSub = isset($menu['sub_menu']); $isActive = ($epActiveIdx !== null && $epActiveIdx === $menuKey); @endphp
                <div class="ep-nav__item {{ $hasSub ? 'has-sub' : '' }}">
                    <a href="{{ isset($menu['route_name']) ? route($menu['route_name'], $menu['params'] ?? []) : 'javascript:;' }}"
                       class="ep-nav__link {{ $isActive ? 'is-active' : '' }}">
                        <i data-lucide="{{ $menu['icon'] ?? 'circle' }}" class="w-[15px] h-[15px]"></i>
                        <span>{{ $menu['title'] }}</span>
                        @if($hasSub)<i data-lucide="chevron-down" class="w-3 h-3 opacity-70"></i>@endif
                    </a>
                    @if($hasSub)
                        <div class="ep-nav__drop">
                            @foreach($menu['sub_menu'] as $subMenu)
                                <a href="{{ isset($subMenu['route_name']) ? route($subMenu['route_name'], $subMenu['params'] ?? []) : 'javascript:;' }}" class="ep-nav__dropitem">
                                    <i data-lucide="{{ $subMenu['icon'] ?? 'activity' }}" class="w-[14px] h-[14px]"></i>
                                    <span>{{ $subMenu['title'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        {{-- Right cluster: impersonate-leave + account --}}
        <div class="ep-appbar__right">
            @impersonating($guard=null)
                <a href="{{ route('impersonate.leave') }}" class="ep-leave">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="hidden lg:inline">Leave impersonating</span>
                </a>
            @endImpersonating

            <div class="ep-account has-sub">
                <button type="button" class="ep-account__btn">
                    <span class="ep-account__who">
                        <span class="ep-account__name">{{ $epName }}</span>
                        <span class="ep-account__role">{{ $epJobTitle }}</span>
                    </span>
                    <span class="ep-account__avatar">
                        @if($epEmp && isset($epEmp->photo_url))
                            <img src="{{ $epEmp->photo_url }}" alt="{{ $epName }}">
                        @else
                            {{ $epInitials }}
                        @endif
                    </span>
                </button>
                <div class="ep-nav__drop ep-account__drop">
                    <div class="ep-account__head">
                        <div class="ep-account__head-name">{{ $epName }}</div>
                        <div class="ep-account__head-mail">{{ optional($epUser)->email }}</div>
                    </div>
                    <a href="{{ route('user.account') }}" class="ep-nav__dropitem"><i data-lucide="user" class="w-[14px] h-[14px]"></i><span>Profile</span></a>
                    <a href="{{ route('logout') }}" class="ep-nav__dropitem"><i data-lucide="toggle-right" class="w-[14px] h-[14px]"></i><span>Logout</span></a>
                </div>
            </div>
        </div>
    </div>
</header>
