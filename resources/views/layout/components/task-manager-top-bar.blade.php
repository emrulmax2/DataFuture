{{--
    Task Manager-only header: the cream nav bar (logo, primary nav, global
    search, user menu) plus the breadcrumb strip beneath it.

    Nav entries, guard handling and the search widget mirror the global header
    so nothing a user could previously reach disappears here; only the skin
    changes. The shared `components/top-bar` is left alone for the rest of the
    app.

    View data this component reads (all optional):
      $tkmCrumbMid      string  middle breadcrumb label
      $tkmCrumbMidUrl   string  where that middle crumb points
      $tkmCrumbCurrent  string  the current page label; omit it on the task
                                list, where Task Manager is itself the page
--}}
@php
    $tkmMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    // The design labels the four top-level entries Dashboard / Courses /
    // Students / Settings; TopMenu's own titles are longer.
    $tkmMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $tkmSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications & offers',
            'student' => 'Enrolled & active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    if (Auth::guard('agent')->check()) {
        $tkmUserName = auth('agent')->user()->email;
        $tkmUserEmail = auth('agent')->user()->email;
        $tkmUserRole = 'Agent User';
        $tkmProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $tkmLogoutUrl = route('agent.logout');
        $tkmDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $tkmUserName = auth('applicant')->user()->email;
        $tkmUserEmail = auth('applicant')->user()->email;
        $tkmUserRole = 'Applicant User';
        $tkmProfileUrl = route('applicant.dashboard');
        $tkmLogoutUrl = route('applicant.logout');
        $tkmDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $tkmUserName = auth('student')->user()->email;
        $tkmUserEmail = auth('student')->user()->email;
        $tkmUserRole = 'Student User';
        $tkmProfileUrl = route('students.dashboard.profile');
        $tkmLogoutUrl = route('students.logout');
        $tkmDashboardUrl = route('students.dashboard');
    } else {
        $tkmEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $tkmEmployee = $tkmEmployeeUser?->employee;
        $tkmUserName = trim((isset($tkmEmployee?->title?->name) ? $tkmEmployee->title->name.' ' : '').($tkmEmployee?->first_name ?? '').' '.($tkmEmployee?->last_name ?? ''));
        $tkmUserName = $tkmUserName !== '' ? $tkmUserName : (Auth::user()->name ?? 'London Churchill College');
        $tkmUserEmail = Auth::user()->email ?? '';
        $tkmUserRole = $tkmEmployee?->employment?->employeeJobTitle?->name
            ?? $tkmEmployee?->employment?->employeeWorkType?->name
            ?? '';
        $tkmProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $tkmLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $tkmDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    // Initials read from first/last name rather than the display name: the
    // latter is title-prefixed, so "Mr Nazmus Sakib" would render as "MS".
    $tkmFirst = trim((string) ($tkmEmployee?->first_name ?? ''));
    $tkmLast = trim((string) ($tkmEmployee?->last_name ?? ''));
    if ($tkmFirst !== '' && $tkmLast !== '') {
        $tkmInitials = strtoupper(mb_substr($tkmFirst, 0, 1).mb_substr($tkmLast, 0, 1));
    } else {
        $tkmWords = preg_split('/\s+/', trim($tkmUserName));
        $tkmInitials = strtoupper(mb_substr($tkmWords[0] ?? 'L', 0, 1).mb_substr(count($tkmWords) > 1 ? end($tkmWords) : ($tkmWords[0] ?? 'C'), 0, 1));
    }
    $tkmInitials = $tkmInitials !== '' ? $tkmInitials : 'LC';

    $tkmAvatarUrl = null;
    if (isset($tkmEmployee) && $tkmEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$tkmEmployee->id.'/'.$tkmEmployee->photo)) {
        $tkmAvatarUrl = Storage::disk('local')->url('public/employees/'.$tkmEmployee->id.'/'.$tkmEmployee->photo);
    }

    // Logo comes from Settings → Branding. This bar is cream, so the navy
    // wordmark reads best; the white lockup is the fallback.
    $tkmLogoOptions = App\Models\Option::where('category', 'SITE_SETTINGS')
        ->whereIn('name', ['site_logo', 'site_dark_logo'])
        ->pluck('value', 'name')
        ->toArray();

    $tkmHeaderLogo = null;
    foreach (['site_dark_logo', 'site_logo'] as $tkmLogoKey) {
        $tkmLogoValue = $tkmLogoOptions[$tkmLogoKey] ?? null;
        if (!empty($tkmLogoValue) && Storage::disk('local')->exists('public/'.$tkmLogoValue)) {
            $tkmHeaderLogo = Storage::disk('local')->url('public/'.$tkmLogoValue);
            break;
        }
    }

    $tkmSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $tkmShowSearch = $tkmSearchConfig['show'] && Route::has('global.search');

    $tkmInModule = request()->is('task-manager*');
@endphp

<div class="tkm-header no-print" data-global-header>
    <header class="tkm-header__bar">
        <a href="{{ $tkmDashboardUrl }}" class="tkm-header__logo{{ $tkmHeaderLogo ? '' : ' tkm-header__logo--fallback' }}" aria-label="London Churchill College">
            @if($tkmHeaderLogo)
                <img src="{{ $tkmHeaderLogo }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
            <span class="tkm-header__word">LONDON<br>CHURCHILL<br>COLLEGE</span>
        </a>

        <nav class="tkm-nav" aria-label="Primary navigation">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $tkmHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $tkmActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'students' && $tkmInModule);
                        $tkmLabel = $tkmMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($tkmHasSub)
                        <div class="tkm-nav__group" data-tkm-navgroup>
                            <button type="button" class="tkm-nav__item {{ $tkmActive ? 'tkm-nav__item--active' : '' }}" data-tkm-act="nav-toggle" aria-expanded="false">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"></circle><path d="M2.5 19.5c0-3 2.7-4.8 6.5-4.8s6.5 1.8 6.5 4.8"></path><path d="M15.8 5.2a3.2 3.2 0 0 1 0 5.9M18.3 15.3c1.9.7 3.2 2.1 3.2 4.2"></path></svg>
                                {{ $tkmLabel }}
                                <svg class="tkm-nav__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                            </button>
                            <div class="tkm-nav__menu">
                                <div class="tkm-nav__menu-title">{{ $menu['title'] }}</div>
                                <div class="tkm-nav__menu-body">
                                    @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                        @php
                                            $tkmSubActive = isset($subMenu['route_name']) && $subMenu['route_name'] == (Route::currentRouteName() ?? '');
                                        @endphp
                                        <a href="{{ $tkmMenuUrl($subMenu) }}" class="tkm-nav__menu-item {{ $tkmSubActive ? 'tkm-nav__menu-item--active' : '' }}">
                                            <span class="tkm-nav__menu-icon">
                                                @if($subKey == 'admission')
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M12 5.5v13"></path><path d="M5.5 12h13"></path></svg>
                                                @elseif($subKey == 'student')
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="8.2"></circle><circle cx="12" cy="12" r="3.4" fill="currentColor" stroke="none"></circle></svg>
                                                @else
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11.2a3.1 3.1 0 1 0 0-6.2 3.1 3.1 0 0 0 0 6.2z"></path><path d="M2.6 19.4c0-3 2.8-4.9 6.4-4.9s6.4 1.9 6.4 4.9"></path><path d="M16.4 6.1a3.1 3.1 0 0 1 0 5.8M18.6 15.2c1.9.7 3.2 2 3.2 4.2"></path></svg>
                                                @endif
                                            </span>
                                            <span class="tkm-nav__menu-copy">
                                                <span class="tkm-nav__menu-name">{{ $subMenu['title'] }}</span>
                                                <span class="tkm-nav__menu-desc">{{ $tkmSubMenuDescription($subKey) }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $tkmMenuUrl($menu) }}" class="tkm-nav__item {{ $tkmActive ? 'tkm-nav__item--active' : '' }}">
                            @if($menuKey == 'dashboard')
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.4"></rect><rect x="14" y="3" width="7" height="7" rx="1.4"></rect><rect x="3" y="14" width="7" height="7" rx="1.4"></rect><rect x="14" y="14" width="7" height="7" rx="1.4"></rect></svg>
                            @elseif($menuKey == 'course.management')
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5A2 2 0 0 1 6 4h6v15H6a2 2 0 0 0-2 1.5V5.5z"></path><path d="M20 5.5A2 2 0 0 0 18 4h-6v15h6a2 2 0 0 1 2 1.5V5.5z"></path></svg>
                            @else
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3.1"></circle><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1.03 1.56V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.9 19.3a1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.56-1.03H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.9a1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1.03-1.56V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15 4.6a1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9v.09a1.7 1.7 0 0 0 1.56 1.03H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15z"></path></svg>
                            @endif
                            {{ $tkmLabel }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="tkm-header__tail">
            @if($tkmShowSearch)
                <div class="tkm-search" data-global-search
                     data-search-url="{{ route('global.search') }}"
                     data-search-applicants="{{ $tkmSearchConfig['applicants'] ? '1' : '0' }}"
                     data-search-students="{{ $tkmSearchConfig['students'] ? '1' : '0' }}"
                     data-search-employees="{{ $tkmSearchConfig['employees'] ? '1' : '0' }}">
                    <label class="tkm-search__field">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" style="flex:none"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4.5 4.5"></path></svg>
                        <input class="tkm-search__input" type="search" autocomplete="off" placeholder="{{ $tkmSearchConfig['placeholder'] }}" data-global-search-input aria-label="{{ $tkmSearchConfig['placeholder'] }}">
                        <button type="button" class="tkm-search__clear" data-global-search-clear aria-label="Clear search" hidden>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg>
                        </button>
                    </label>
                    <div class="tkm-search__results" data-global-search-results></div>
                </div>
            @endif

            @if(Auth::guard('agent')->check())
                @impersonating($guard='agent')
                    <a href="{{ route('impersonate.leave') }}" class="tkm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('applicant')->check())
                @impersonating($guard='applicant')
                    <a href="{{ route('applicant.impersonate.leave') }}" class="tkm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('student')->check())
                @impersonating($guard='student')
                    <a href="{{ route('impersonate.leave') }}" class="tkm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @else
                @impersonating($guard=null)
                    <a href="{{ route('impersonate.leave') }}" class="tkm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @endif

            <div class="tkm-profile" data-tkm-profile>
                <button type="button" class="tkm-profile__trigger" data-tkm-act="profile-toggle" aria-label="Open user menu">
                    <span class="tkm-profile__copy">
                        <span class="tkm-profile__name">{{ $tkmUserName }}</span>
                        @if($tkmUserRole !== '')
                            <span class="tkm-profile__role">{{ $tkmUserRole }}</span>
                        @endif
                    </span>
                    <span class="tkm-profile__avatar">
                        @if($tkmAvatarUrl)
                            <img src="{{ $tkmAvatarUrl }}" alt="{{ $tkmUserName }}">
                        @else
                            {{ $tkmInitials }}
                        @endif
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6E6754" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                </button>

                <div class="tkm-profile__menu">
                    <div class="tkm-profile__menu-head">
                        <span class="tkm-profile__menu-avatar">
                            @if($tkmAvatarUrl)
                                <img src="{{ $tkmAvatarUrl }}" alt="{{ $tkmUserName }}">
                            @else
                                {{ $tkmInitials }}
                            @endif
                        </span>
                        <span class="tkm-profile__menu-copy">
                            <span class="tkm-profile__menu-name">{{ $tkmUserName }}</span>
                            <span class="tkm-profile__menu-email">{{ $tkmUserEmail }}</span>
                            @if($tkmUserRole !== '')
                                <span class="tkm-profile__menu-badge"><span></span>{{ $tkmUserRole }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="tkm-profile__menu-body">
                        <a href="{{ $tkmProfileUrl }}" class="tkm-profile__menu-link">
                            <span class="tkm-profile__menu-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"></circle><path d="M5 20c0-3.5 3-5.5 7-5.5s7 2 7 5.5"></path></svg>
                            </span>
                            <span>
                                <span class="tkm-profile__menu-label">Profile</span>
                                <span class="tkm-profile__menu-desc">View &amp; edit your details</span>
                            </span>
                        </a>
                        <a href="{{ $tkmLogoutUrl }}" class="tkm-profile__menu-link tkm-profile__menu-link--danger">
                            <span class="tkm-profile__menu-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 4H18a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4.5"></path><path d="M9 8l-4 4 4 4M5 12h10"></path></svg>
                            </span>
                            <span>
                                <span class="tkm-profile__menu-label">Logout</span>
                                <span class="tkm-profile__menu-desc">End this session</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="tkm-subbar">
        <nav class="tkm-crumbs" aria-label="Breadcrumb">
            <a href="{{ $tkmDashboardUrl }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" style="flex:none"><path d="m3.5 10.5 8.5-7 8.5 7"></path><path d="M6 9.2V20h12V9.2"></path><path d="M10 20v-5h4v5"></path></svg>
                Dashboard
            </a>
            <span class="tkm-crumbs__sep"></span>
            @if(empty($tkmCrumbCurrent ?? null))
                {{-- Module root (the task list): nothing sits below Task Manager. --}}
                <strong aria-current="page">Task Manager</strong>
            @else
                <a href="{{ Route::has('task.manager') ? route('task.manager') : 'javascript:;' }}">Task Manager</a>
                @if(!empty($tkmCrumbMid ?? null))
                    <span class="tkm-crumbs__sep"></span>
                    <a href="{{ $tkmCrumbMidUrl ?? 'javascript:void(0);' }}">{{ $tkmCrumbMid }}</a>
                @endif
                <span class="tkm-crumbs__sep"></span>
                <strong aria-current="page">{{ $tkmCrumbCurrent }}</strong>
            @endif
        </nav>

        <div class="tkm-subbar__tail">
            <span>{{ date('D j F Y') }}</span>
            <span class="tkm-subbar__rule"></span>
            <span id="theClock">{{ date('H:i:s') }}</span>
        </div>
    </div>
</div>
