@extends('../layout/main')

@section('head')
    @yield('subhead')
@endsection

@section('content')
    @include('../layout/components/mobile-menu')
    @include('../layout/components/top-bar', ['class' => 'top-bar-boxed--top-menu'])
    <!-- BEGIN: Top Menu -->
    <nav class="top-nav flex">
        <ul>
            @foreach ($top_menu as $menuKey => $menu)
            
                <li>
                    <a href="{{ isset($menu['route_name']) ? route($menu['route_name'], $menu['params']) : 'javascript:;' }}" class="{{ $first_level_active_index == $menuKey ? 'top-menu top-menu--active' : 'top-menu' }}">
                        <div class="top-menu__icon">
                            <i data-lucide="{{ $menu['icon'] }}"></i>
                        </div>
                        <div class="top-menu__title">
                            {{ $menu['title'] }}
                            @if (isset($menu['sub_menu']))
                                <i data-lucide="chevron-down" class="top-menu__sub-icon"></i>
                            @endif
                        </div>
                    </a>
                    @if (isset($menu['sub_menu']))
                        <ul class="{{ $first_level_active_index == $menuKey ? 'top-menu__sub-open' : '' }}">
                            @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                <li>
                                    <a href="{{ isset($subMenu['route_name']) ? route($subMenu['route_name'], $subMenu['params']) : 'javascript:;' }}" class="top-menu">
                                        <div class="top-menu__icon">
                                            <i data-lucide="activity"></i>
                                        </div>
                                        <div class="top-menu__title">
                                            {{ $subMenu['title'] }}
                                            @if (isset($subMenu['sub_menu']))
                                                <i data-lucide="chevron-down" class="top-menu__sub-icon"></i>
                                            @endif
                                        </div>
                                    </a>
                                    @if (isset($subMenu['sub_menu']))
                                        <ul class="{{ $second_level_active_index == $subMenuKey ? 'top-menu__sub-open' : '' }}">
                                            @foreach ($subMenu['sub_menu'] as $lastSubMenuKey => $lastSubMenu)
                                                <li>
                                                    <a href="{{ isset($lastSubMenu['route_name']) ? route($lastSubMenu['route_name'], $lastSubMenu['params']) : 'javascript:;' }}" class="top-menu">
                                                        <div class="top-menu__icon">
                                                            <i data-lucide="zap"></i>
                                                        </div>
                                                        <div class="top-menu__title">{{ $lastSubMenu['title'] }}</div>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul> 
        @if(Auth::user() && Route::currentRouteName() == 'dashboard' && !empty($home_work_statistics) && ((!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work) || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)))
            {!! $home_work_statistics !!}
            {{--<div class="clockinStatistics inline-flex justify-end items-start ml-auto">
                <div class="statusArea">
                    <div class="text-slate-500 text-xs whitespace-nowrap uppercase">Status</div>
                    <div class="font-medium whitespace-nowrap uppercase">Working</div>
                </div>
                <div class="sinceArea">
                    <div class="text-slate-500 text-xs whitespace-nowrap uppercase">since</div>
                    <div class="font-medium whitespace-nowrap uppercase">09:00 AM</div>
                    <div class="text-slate-500 text-xs whitespace-nowrap">7 hours 5 mins</div>
                </div>
            </div>--}}
            {{--<div class="clockinArea inline-flex justify-end items-center">
                {!! $home_work_history !!}
            </div>--}}
        @endif
    </nav>
    <!-- END: Top Menu -->
    <!-- BEGIN: Content -->
    <div class="content content--top-nav">
        @yield('subcontent')
    </div>
    <!-- END: Content -->
@endsection
