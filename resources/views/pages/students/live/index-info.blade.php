@php
    if(Auth::guard('agent')->check()):
        $profileUserName = auth('agent')->user()->email;
        $profileUserEmail = auth('agent')->user()->email;
        $profileUserRole = 'Agent User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = null;
        $logoutUrl = Route::has('agent.logout') ? route('agent.logout') : url('/agent/logout');
    elseif(Auth::guard('applicant')->check()):
        $profileUserName = auth('applicant')->user()->email;
        $profileUserEmail = auth('applicant')->user()->email;
        $profileUserRole = 'Applicant User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = null;
        $logoutUrl = Route::has('applicant.logout') ? route('applicant.logout') : url('/applicant/logout');
    elseif(Auth::guard('student')->check()):
        $profileUserName = auth('student')->user()->email;
        $profileUserEmail = auth('student')->user()->email;
        $profileUserRole = 'Student User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = Route::has('students.dashboard.profile') ? route('students.dashboard.profile') : null;
        $logoutUrl = Route::has('students.logout') ? route('students.logout') : url('/students/logout');
    else:
        $employee = auth()->user()->employee ?? null;
        $profileUserName = $employee ? trim(($employee->title->name ?? '').' '.$employee->first_name.' '.$employee->last_name) : (auth()->user()->name ?? auth()->user()->email);
        $profileUserEmail = auth()->user()->email ?? $profileUserName;
        $profileUserRole = '';
        $profileUserInitials = $employee ? strtoupper(substr($employee->first_name ?? '', 0, 1).substr($employee->last_name ?? '', 0, 1)) : strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = Route::has('user.account') ? route('user.account') : null;
        $logoutUrl = Route::has('logout') ? route('logout') : url('/logout');
    endif;
    $studentHeaderLogo = cache()->get('site_logo');
    if(empty($studentHeaderLogo)):
        $studentHeaderLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    endif;
    $studentHeaderLogoUrl = (!empty($studentHeaderLogo) && Storage::disk('local')->exists('public/'.$studentHeaderLogo))
        ? Storage::disk('local')->url('public/'.$studentHeaderLogo)
        : null;

    $studentNavMeta = [
        'admission' => [
            'description' => 'Applicants & enrolment pipeline',
            'icon' => 'user-plus',
            'tone' => 'teal',
        ],
        'student' => [
            'badge' => 'CURRENT',
            'description' => 'Active, enrolled students',
            'icon' => 'clock',
            'tone' => 'green',
        ],
        'agent_management' => [
            'description' => 'Recruitment partners & referrals',
            'icon' => 'landmark',
            'tone' => 'gold',
        ],
    ];
@endphp

<div class="student-profile-header student-live-header no-print">
    <div class="student-profile-appbar">
        <a href="{{ url('/') }}" class="student-profile-brand {{ $studentHeaderLogoUrl ? 'student-profile-brand--logo' : 'student-profile-brand--fallback' }}">
            @if($studentHeaderLogoUrl)
                <img src="{{ $studentHeaderLogoUrl }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>
        <nav class="student-profile-appnav" aria-label="Main navigation">
            @if(isset($top_menu))
                @foreach ($top_menu as $menuKey => $menu)
                    @php
                        $hasSubMenu = isset($menu['sub_menu']);
                        $isActiveMenu = (($first_level_active_index ?? null) == $menuKey || ($menuKey == 'students' && str_starts_with(Route::currentRouteName(), 'student')));
                    @endphp
                    <div class="student-profile-appnav-item {{ $hasSubMenu ? 'has-submenu' : '' }}">
                        <a href="{{ isset($menu['route_name']) ? route($menu['route_name'], $menu['params']) : 'javascript:;' }}" class="student-profile-appnav-link {{ $isActiveMenu ? 'active' : '' }}">
                            {{ $menu['title'] }}
                            @if ($hasSubMenu)
                                <i data-lucide="chevron-down" class="student-profile-appnav-chevron w-3 h-3"></i>
                            @endif
                        </a>
                        @if ($hasSubMenu)
                            <div class="student-profile-appnav-submenu">
                                <span class="student-profile-appnav-submenu-arrow"></span>
                                <div class="student-profile-appnav-submenu-inner">
                                    @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                        @php
                                            $meta = $studentNavMeta[$subMenuKey] ?? ['description' => '', 'icon' => 'circle', 'tone' => 'teal'];
                                            $isActiveSubMenu = isset($subMenu['route_name']) && ($subMenu['route_name'] == Route::currentRouteName() || ($subMenuKey == 'student' && str_starts_with(Route::currentRouteName(), 'student')));
                                        @endphp
                                        <a href="{{ isset($subMenu['route_name']) ? route($subMenu['route_name'], $subMenu['params']) : 'javascript:;' }}" class="student-profile-appnav-submenu-link {{ $isActiveSubMenu ? 'active' : '' }}">
                                            <span class="student-profile-appnav-submenu-icon {{ $meta['tone'] }}">
                                                <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
                                            </span>
                                            <span class="student-profile-appnav-submenu-copy">
                                                <span class="student-profile-appnav-submenu-title">
                                                    {{ $subMenu['title'] }}
                                                    @if(isset($meta['badge']))
                                                        <span class="student-profile-appnav-submenu-badge">{{ $meta['badge'] }}</span>
                                                    @endif
                                                </span>
                                                @if(!empty($meta['description']))
                                                    <span class="student-profile-appnav-submenu-description">{{ $meta['description'] }}</span>
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </nav>
        <div class="student-profile-user student-profile-user-dropdown">
            <button type="button" class="student-profile-user-trigger" aria-label="Open user menu">
                <span class="student-profile-user-copy">
                    <span class="student-profile-user-name">{{ $profileUserName }}</span>
                    @if(!empty($profileUserRole))
                        <span class="student-profile-user-role">{{ $profileUserRole }}</span>
                    @endif
                </span>
                <span class="student-profile-user-avatar">{{ $profileUserInitials }}</span>
                <i data-lucide="chevron-down" class="student-profile-user-chevron w-3 h-3"></i>
            </button>
            <div class="student-profile-user-menu">
                <span class="student-profile-user-menu-arrow"></span>
                <div class="student-profile-user-menu-card">
                    <div class="student-profile-user-menu-head">
                        <span class="student-profile-user-menu-avatar">{{ $profileUserInitials }}</span>
                        <span class="student-profile-user-menu-copy">
                            <span class="student-profile-user-menu-name">{{ $profileUserName }}</span>
                            <span class="student-profile-user-menu-email">{{ $profileUserEmail }}</span>
                        </span>
                    </div>
                    @if($profileUrl)
                        <a href="{{ $profileUrl }}" class="student-profile-user-menu-link">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Profile</span>
                        </a>
                    @endif
                    <a href="{{ $logoutUrl }}" class="student-profile-user-menu-link">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Logout</span>
                    </a>
                </div>
                <div class="student-profile-user-menu-accent"></div>
            </div>
        </div>
    </div>

    <div class="student-profile-breadcrumb">
        <a href="{{ Route::has('staff.dashboard') ? route('staff.dashboard') : route('dashboard') }}">Dashboard</a>
        <span class="student-profile-crumb-separator">/</span>
        <strong>Students Live</strong>
    </div>

    <div class="student-live-hero">
        <div>
            <h1 class="student-live-hero-title">Live Students</h1>
            <p class="student-live-hero-copy">Search, filter and message enrolled students</p>
        </div>
        <a href="{{ route('dashboard') }}" class="student-live-back-btn">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>
</div>
