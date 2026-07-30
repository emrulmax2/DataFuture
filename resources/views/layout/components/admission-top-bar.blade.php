{{--
    Admission-only top bar.

    A dedicated header for the redesigned admission module — the shared
    `components/top-bar` / `layout/top-menu` header stays untouched for the
    rest of the app. Nav items, privileges and guard handling mirror the
    global header so nothing a user could previously reach disappears here.
--}}
@php
    $admMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    // The mock labels the four top-level entries Dashboard / Courses /
    // Students / Settings; TopMenu's own titles are longer.
    $admMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $admSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications & offers',
            'student' => 'Enrolled & active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    if (Auth::guard('agent')->check()) {
        $admUserName = auth('agent')->user()->email;
        $admUserEmail = auth('agent')->user()->email;
        $admUserRole = 'Agent User';
        $admProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $admLogoutUrl = route('agent.logout');
        $admDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $admUserName = auth('applicant')->user()->email;
        $admUserEmail = auth('applicant')->user()->email;
        $admUserRole = 'Applicant User';
        $admProfileUrl = route('applicant.dashboard');
        $admLogoutUrl = route('applicant.logout');
        $admDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $admUserName = auth('student')->user()->email;
        $admUserEmail = auth('student')->user()->email;
        $admUserRole = 'Student User';
        $admProfileUrl = route('students.dashboard.profile');
        $admLogoutUrl = route('students.logout');
        $admDashboardUrl = route('students.dashboard');
    } else {
        $admEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $admEmployee = $admEmployeeUser?->employee;
        $admUserName = trim((isset($admEmployee?->title?->name) ? $admEmployee->title->name.' ' : '').($admEmployee?->first_name ?? '').' '.($admEmployee?->last_name ?? ''));
        $admUserName = $admUserName !== '' ? $admUserName : (Auth::user()->name ?? 'London Churchill College');
        $admUserEmail = Auth::user()->email ?? '';
        $admUserRole = $admEmployee?->employment?->employeeJobTitle?->name ?? 'Staff';
        $admProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $admLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $admDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    // Initials read from first/last name rather than the display name: the
    // latter is title-prefixed, so "Mr Nazmus Sakib" would render as "MS".
    $admFirst = trim((string) ($admEmployee?->first_name ?? ''));
    $admLast = trim((string) ($admEmployee?->last_name ?? ''));
    if ($admFirst !== '' && $admLast !== '') {
        $admInitials = strtoupper(mb_substr($admFirst, 0, 1).mb_substr($admLast, 0, 1));
    } else {
        $admWords = preg_split('/\s+/', trim($admUserName));
        $admInitials = strtoupper(mb_substr($admWords[0] ?? 'L', 0, 1).mb_substr(count($admWords) > 1 ? end($admWords) : ($admWords[0] ?? 'C'), 0, 1));
    }
    $admInitials = $admInitials !== '' ? $admInitials : 'LC';

    $admAvatarUrl = null;
    if (isset($admEmployee) && $admEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$admEmployee->id.'/'.$admEmployee->photo)) {
        $admAvatarUrl = Storage::disk('local')->url('public/employees/'.$admEmployee->id.'/'.$admEmployee->photo);
    }

    // Logo comes from Settings → Branding. `site_logo` is the white-wordmark
    // lockup, which is the one that reads on this dark bar; `site_dark_logo`
    // (navy wordmark) is only a fallback for when it hasn't been uploaded.
    $admLogoOptions = App\Models\Option::where('category', 'SITE_SETTINGS')
        ->whereIn('name', ['site_logo', 'site_dark_logo'])
        ->pluck('value', 'name')
        ->toArray();

    $admHeaderLogo = null;
    foreach (['site_logo', 'site_dark_logo'] as $admLogoKey) {
        $admLogoValue = $admLogoOptions[$admLogoKey] ?? null;
        if (!empty($admLogoValue) && Storage::disk('local')->exists('public/'.$admLogoValue)) {
            $admHeaderLogo = Storage::disk('local')->url('public/'.$admLogoValue);
            break;
        }
    }

    $admSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $admCanSearchApplicants = $admSearchConfig['applicants'];
    $admCanSearchStudents = $admSearchConfig['students'];
    $admCanSearchEmployees = $admSearchConfig['employees'];
    $admShowSearch = $admSearchConfig['show'] && Route::has('global.search');
    $admSearchPlaceholder = $admSearchConfig['placeholder'];

    // Pages that belong to the admission section but do not carry an
    // `admission.*` route name — they still light up the Students nav.
    $admRoute = Route::currentRouteName() ?? '';
    $admInAdmission = str_starts_with($admRoute, 'admission')
        || str_starts_with($admRoute, 'reports.applicant.analysis');

    $admCrumbs = [];
    if (isset($breadcrumbs) && !empty($breadcrumbs)) {
        foreach ($breadcrumbs as $crumb) {
            $admCrumbs[] = [
                'label' => $crumb['label'] ?? '',
                'href' => $crumb['href'] ?? 'javascript:void(0);',
            ];
        }
    }
@endphp

<div class="adm-header no-print" data-global-header>
    <div class="adm-header__rule"></div>

    <header class="adm-header__bar">
        <a href="{{ $admDashboardUrl }}" class="adm-header__logo{{ $admHeaderLogo ? '' : ' adm-header__logo--fallback' }}" aria-label="London Churchill College">
            @if($admHeaderLogo)
                <img src="{{ $admHeaderLogo }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>

        <span class="adm-header__divider" aria-hidden="true"></span>

        <nav class="adm-nav" aria-label="Primary navigation">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $admHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $admActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'students' && $admInAdmission);
                        $admLabel = $admMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($admHasSub)
                        <div class="adm-nav__group" data-adm-nav-group>
                            <button type="button" class="adm-nav__item {{ $admActive ? 'adm-nav__item--active' : '' }}" data-adm-nav-toggle aria-expanded="false">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="7" r="3.2"></circle><path d="M2 21c0-3.9 3.1-6 7-6s7 2.1 7 6"></path><path d="M17 7h5M19.5 4.5v5"></path></svg>
                                {{ $admLabel }}
                                <svg class="adm-nav__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="adm-nav__menu">
                                <div class="adm-nav__menu-title">{{ $menu['title'] }}</div>
                                @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                    @php
                                        $admSubActive = isset($subMenu['route_name'])
                                            && ($subMenu['route_name'] == $admRoute
                                                || ($subKey == 'admission' && $admInAdmission));
                                    @endphp
                                    <a href="{{ $admMenuUrl($subMenu) }}" class="adm-nav__menu-item {{ $admSubActive ? 'adm-nav__menu-item--active' : '' }}">
                                        <span class="adm-nav__menu-icon">
                                            @if($subKey == 'admission')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6M9 14l2 2 4-4"></path></svg>
                                            @elseif($subKey == 'student')
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke-width="2"><circle cx="9" cy="7" r="3.5"></circle><path d="M2 21c0-3.9 3.1-6 7-6s7 2.1 7 6"></path><path d="M17 7h5M19.5 4.5v5"></path></svg>
                                            @else
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            @endif
                                        </span>
                                        <span class="adm-nav__menu-copy">
                                            <span class="adm-nav__menu-name">{{ $subMenu['title'] }}</span>
                                            <span class="adm-nav__menu-desc">{{ $admSubMenuDescription($subKey) }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $admMenuUrl($menu) }}" class="adm-nav__item {{ $admActive ? 'adm-nav__item--active' : '' }}">
                            @if($menuKey == 'dashboard')
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect></svg>
                            @elseif($menuKey == 'course.management')
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 6h9a3 3 0 013 3v11a2.5 2.5 0 00-2.5-2.5H2z"></path><path d="M22 6h-9a3 3 0 00-3 3v11a2.5 2.5 0 012.5-2.5H22z"></path></svg>
                            @else
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"></path></svg>
                            @endif
                            {{ $admLabel }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="adm-header__spacer">
            @if($admShowSearch)
                <div class="adm-search" data-adm-search data-global-search
                     data-search-url="{{ route('global.search') }}"
                     data-search-applicants="{{ $admCanSearchApplicants ? '1' : '0' }}"
                     data-search-students="{{ $admCanSearchStudents ? '1' : '0' }}"
                     data-search-employees="{{ $admCanSearchEmployees ? '1' : '0' }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8fa2b8" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                    <input class="adm-search__input" type="search" autocomplete="off" placeholder="{{ $admSearchPlaceholder }}" data-adm-search-input data-global-search-input>
                    <svg class="adm-search__close" data-adm-search-close width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9db0c6" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    <div class="adm-search__results" data-adm-search-results data-global-search-results></div>
                </div>
                <button type="button" class="adm-search__toggle" data-adm-search-toggle aria-label="Open search">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                </button>
            @endif

            <span class="adm-header__divider" aria-hidden="true"></span>

            @if(Auth::guard('agent')->check())
                @impersonating($guard='agent')
                    <a href="{{ route('impersonate.leave') }}" class="adm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('applicant')->check())
                @impersonating($guard='applicant')
                    <a href="{{ route('applicant.impersonate.leave') }}" class="adm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('student')->check())
                @impersonating($guard='student')
                    <a href="{{ route('impersonate.leave') }}" class="adm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @else
                @impersonating($guard=null)
                    <a href="{{ route('impersonate.leave') }}" class="adm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @endif

            <div class="adm-profile" data-adm-profile>
                <button type="button" class="adm-profile__trigger" data-adm-profile-toggle aria-label="Open user menu">
                    <span class="adm-profile__copy">
                        <span class="adm-profile__name">{{ $admUserName }}</span>
                        <span class="adm-profile__role">{{ $admUserRole }}</span>
                    </span>
                    <span class="adm-profile__avatar">
                        @if($admAvatarUrl)
                            <img src="{{ $admAvatarUrl }}" alt="{{ $admUserName }}">
                        @else
                            {{ $admInitials }}
                        @endif
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9db0c6" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
                </button>

                <div class="adm-profile__menu">
                    <div class="adm-profile__menu-head">
                        <span class="adm-profile__menu-avatar">
                            @if($admAvatarUrl)
                                <img src="{{ $admAvatarUrl }}" alt="{{ $admUserName }}">
                            @else
                                {{ $admInitials }}
                            @endif
                        </span>
                        <span style="min-width:0;">
                            <span class="adm-profile__menu-name">{{ $admUserName }}</span>
                            <span class="adm-profile__menu-email">{{ $admUserEmail }}</span>
                        </span>
                    </div>
                    <div class="adm-profile__menu-body">
                        <a href="{{ $admProfileUrl }}" class="adm-profile__menu-link">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"></path></svg>
                            <span style="flex:1;">
                                <span class="adm-profile__menu-title">Profile</span>
                                <span class="adm-profile__menu-desc">View &amp; edit your details</span>
                            </span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#cdd8dc" stroke-width="2.2"><path d="M9 6l6 6-6 6"></path></svg>
                        </a>
                        <div class="adm-profile__menu-sep"></div>
                        <a href="{{ $admLogoutUrl }}" class="adm-profile__menu-link adm-profile__menu-link--danger">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                            <span style="flex:1;">
                                <span class="adm-profile__menu-title">Logout</span>
                                <span class="adm-profile__menu-desc">End this session</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="adm-crumbs">
        <nav class="adm-crumbs__inner" aria-label="breadcrumb">
            <svg class="adm-crumbs__home" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M3 11l9-8 9 8M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"></path></svg>
            <a href="{{ $admDashboardUrl }}">Dashboard</a>
            @foreach($admCrumbs as $crumbIndex => $crumb)
                <svg class="adm-crumbs__sep" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke-width="2.5"><path d="M9 6l6 6-6 6"></path></svg>
                @if($crumbIndex === count($admCrumbs) - 1)
                    <strong>{{ $crumb['label'] }}</strong>
                @else
                    <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
                @endif
            @endforeach
        </nav>
    </div>
</div>
