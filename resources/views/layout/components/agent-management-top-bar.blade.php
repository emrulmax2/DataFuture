@php
    $agmMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    $agmMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $agmMenuIcon = function ($key, $fallback = 'circle') {
        return [
            'dashboard' => 'layout-grid',
            'course.management' => 'book-open',
            'students' => 'users',
            'site.setting' => 'settings',
        ][$key] ?? $fallback;
    };

    $agmSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications and offers',
            'student' => 'Enrolled and active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    $agmSubMenuIcon = function ($key) {
        return [
            'admission' => 'plus',
            'student' => 'circle-dot',
            'agent_management' => 'users',
        ][$key] ?? 'circle';
    };

    $agmEmployee = null;
    if (Auth::guard('agent')->check()) {
        $agmUserName = auth('agent')->user()->email;
        $agmUserEmail = auth('agent')->user()->email;
        $agmUserRole = 'Agent User';
        $agmProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $agmLogoutUrl = route('agent.logout');
        $agmDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $agmUserName = auth('applicant')->user()->email;
        $agmUserEmail = auth('applicant')->user()->email;
        $agmUserRole = 'Applicant User';
        $agmProfileUrl = route('applicant.dashboard');
        $agmLogoutUrl = route('applicant.logout');
        $agmDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $agmUserName = auth('student')->user()->email;
        $agmUserEmail = auth('student')->user()->email;
        $agmUserRole = 'Student User';
        $agmProfileUrl = route('students.dashboard.profile');
        $agmLogoutUrl = route('students.logout');
        $agmDashboardUrl = route('students.dashboard');
    } else {
        $agmEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $agmEmployee = $agmEmployeeUser?->employee;
        $agmUserName = trim((isset($agmEmployee?->title?->name) ? $agmEmployee->title->name.' ' : '').($agmEmployee?->first_name ?? '').' '.($agmEmployee?->last_name ?? ''));
        $agmUserName = $agmUserName !== '' ? $agmUserName : (Auth::user()->name ?? 'London Churchill College');
        $agmUserEmail = Auth::user()->email ?? '';
        $agmUserRole = $agmEmployee?->employment?->employeeJobTitle?->name ?? 'Staff';
        $agmProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $agmLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $agmDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    $agmFirst = trim((string) ($agmEmployee?->first_name ?? ''));
    $agmLast = trim((string) ($agmEmployee?->last_name ?? ''));
    if ($agmFirst !== '' && $agmLast !== '') {
        $agmInitials = strtoupper(mb_substr($agmFirst, 0, 1).mb_substr($agmLast, 0, 1));
    } else {
        $agmWords = preg_split('/\s+/', trim($agmUserName));
        $agmInitials = strtoupper(mb_substr($agmWords[0] ?? 'L', 0, 1).mb_substr(count($agmWords) > 1 ? end($agmWords) : ($agmWords[0] ?? 'C'), 0, 1));
    }
    $agmInitials = $agmInitials !== '' ? $agmInitials : 'LC';

    $agmAvatarUrl = null;
    if (isset($agmEmployee) && $agmEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$agmEmployee->id.'/'.$agmEmployee->photo)) {
        $agmAvatarUrl = Storage::disk('local')->url('public/employees/'.$agmEmployee->id.'/'.$agmEmployee->photo);
    }

    $agmLogoOptions = App\Models\Option::where('category', 'SITE_SETTINGS')
        ->whereIn('name', ['site_logo', 'site_dark_logo'])
        ->pluck('value', 'name')
        ->toArray();

    $agmHeaderLogo = null;
    foreach (['site_logo', 'site_dark_logo'] as $agmLogoKey) {
        $agmLogoValue = $agmLogoOptions[$agmLogoKey] ?? null;
        if (!empty($agmLogoValue) && Storage::disk('local')->exists('public/'.$agmLogoValue)) {
            $agmHeaderLogo = Storage::disk('local')->url('public/'.$agmLogoValue);
            break;
        }
    }

    $agmSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $agmCanSearchApplicants = $agmSearchConfig['applicants'];
    $agmCanSearchStudents = $agmSearchConfig['students'];
    $agmCanSearchEmployees = $agmSearchConfig['employees'];
    $agmShowSearch = $agmSearchConfig['show'] && Route::has('global.search');
    $agmSearchPlaceholder = $agmSearchConfig['placeholder'];

    $agmRoute = Route::currentRouteName() ?? '';
    $agmInModule = request()->is('agent-management*')
        || request()->is('agent-user*')
        || request()->is('agent-profile*')
        || in_array($agmRoute, ['agent.management', 'agent-user.index', 'agent-user.show'], true);
    $agmCrumbs = [];
    if (isset($breadcrumbs) && !empty($breadcrumbs)) {
        foreach ($breadcrumbs as $crumb) {
            $agmCrumbs[] = [
                'label' => $crumb['label'] ?? '',
                'href' => $crumb['href'] ?? 'javascript:void(0);',
            ];
        }
    }
@endphp

<div class="agm-header no-print" data-global-header>
    <div class="agm-header__rule"></div>

    <header class="agm-header__bar">
        <a href="{{ $agmDashboardUrl }}" class="agm-header__logo{{ $agmHeaderLogo ? '' : ' agm-header__logo--fallback' }}" aria-label="London Churchill College">
            @if($agmHeaderLogo)
                <img src="{{ $agmHeaderLogo }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>

        <span class="agm-header__divider" aria-hidden="true"></span>

        <nav class="agm-nav" aria-label="Primary navigation">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $agmHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $agmActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'students' && $agmInModule);
                        $agmLabel = $agmMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($agmHasSub)
                        <div class="agm-nav__group" data-header-menu>
                            <button type="button" class="agm-nav__item {{ $agmActive ? 'agm-nav__item--active' : '' }}" data-header-menu-toggle>
                                <i data-lucide="{{ $agmMenuIcon($menuKey, $menu['icon'] ?? 'circle') }}"></i>
                                <span>{{ $agmLabel }}</span>
                                <i data-lucide="chevron-down" class="agm-nav__caret"></i>
                            </button>
                            <div class="agm-nav__menu">
                                <div class="agm-nav__menu-title">{{ $menu['title'] }}</div>
                                @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                    @php
                                        $agmSubActive = isset($subMenu['route_name'])
                                            && ($subMenu['route_name'] == $agmRoute
                                                || ($subKey == 'agent_management' && $agmInModule));
                                    @endphp
                                    <a href="{{ $agmMenuUrl($subMenu) }}" class="agm-nav__menu-item {{ $agmSubActive ? 'agm-nav__menu-item--active' : '' }}">
                                        <span class="agm-nav__menu-icon">
                                            <i data-lucide="{{ $agmSubMenuIcon($subKey) }}"></i>
                                        </span>
                                        <span class="agm-nav__menu-copy">
                                            <span>{{ $subMenu['title'] }}</span>
                                            <small>{{ $agmSubMenuDescription($subKey) }}</small>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $agmMenuUrl($menu) }}" class="agm-nav__item {{ $agmActive ? 'agm-nav__item--active' : '' }}">
                            <i data-lucide="{{ $agmMenuIcon($menuKey, $menu['icon'] ?? 'circle') }}"></i>
                            <span>{{ $agmLabel }}</span>
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="agm-header__spacer">
            @if($agmShowSearch)
                <div class="agm-search" data-agm-search data-global-search
                     data-search-url="{{ route('global.search') }}"
                     data-search-applicants="{{ $agmCanSearchApplicants ? '1' : '0' }}"
                     data-search-students="{{ $agmCanSearchStudents ? '1' : '0' }}"
                     data-search-employees="{{ $agmCanSearchEmployees ? '1' : '0' }}">
                    <button type="button" class="agm-search__toggle" data-agm-search-toggle aria-label="Open search">
                        <i data-lucide="search"></i>
                    </button>
                    <label class="agm-search__field">
                        <i data-lucide="search"></i>
                        <input type="search" autocomplete="off" placeholder="{{ $agmSearchPlaceholder }}" data-agm-search-input data-global-search-input>
                        <button type="button" class="agm-search__close" data-agm-search-close aria-label="Close search">
                            <i data-lucide="x"></i>
                        </button>
                    </label>
                    <div class="agm-search__results" data-global-search-results></div>
                </div>
            @endif

            <span class="agm-header__divider agm-header__divider--compact" aria-hidden="true"></span>

            <div class="agm-profile" data-header-menu>
                <button type="button" class="agm-profile__toggle" data-header-menu-toggle>
                    <span class="agm-profile__avatar">
                        @if($agmAvatarUrl)
                            <img src="{{ $agmAvatarUrl }}" alt="{{ $agmUserName }}">
                        @else
                            {{ $agmInitials }}
                        @endif
                    </span>
                    <span class="agm-profile__copy">
                        <strong>{{ $agmUserName }}</strong>
                        <small>{{ $agmUserRole }}</small>
                    </span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="agm-profile__menu">
                    <div class="agm-profile__summary">
                        <strong>{{ $agmUserName }}</strong>
                        <small>{{ $agmUserEmail }}</small>
                    </div>
                    <a href="{{ $agmProfileUrl }}" class="agm-profile__link">
                        <i data-lucide="user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="{{ $agmLogoutUrl }}" class="agm-profile__link agm-profile__link--danger">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="agm-crumbs">
        <a href="{{ $agmDashboardUrl }}" class="agm-crumbs__item">
            <i data-lucide="home"></i>
            <span>Dashboard</span>
        </a>
        @foreach($agmCrumbs as $crumb)
            @php
                $agmIsLastCrumb = $loop->last;
                $agmHasCrumbLink = !empty($crumb['href']) && $crumb['href'] !== 'javascript:void(0);' && $crumb['href'] !== 'javascript:;';
            @endphp
            <i data-lucide="chevron-right" class="agm-crumbs__sep"></i>
            @if($agmIsLastCrumb)
                <span class="agm-crumbs__current" aria-current="page">{{ $crumb['label'] }}</span>
            @elseif($agmHasCrumbLink)
                <a href="{{ $crumb['href'] }}" class="agm-crumbs__item">{{ $crumb['label'] }}</a>
            @else
                <span class="agm-crumbs__item">{{ $crumb['label'] }}</span>
            @endif
        @endforeach
    </div>
</div>
