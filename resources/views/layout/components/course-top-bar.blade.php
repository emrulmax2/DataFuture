{{--
    Course Management-only top bar.

    A dedicated header for the redesigned course management module — the
    shared `components/top-bar` / `layout/top-menu` header stays untouched for
    the rest of the app. Nav items, privileges and guard handling mirror the
    global header so nothing a user could previously reach disappears here.
--}}
@php
    $cmMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    // The design labels the four top-level entries Dashboard / Courses /
    // Students / Settings; TopMenu's own titles are longer.
    $cmMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $cmSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications & offers',
            'student' => 'Enrolled & active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    if (Auth::guard('agent')->check()) {
        $cmUserName = auth('agent')->user()->email;
        $cmUserEmail = auth('agent')->user()->email;
        $cmUserRole = 'Agent User';
        $cmProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $cmLogoutUrl = route('agent.logout');
        $cmDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $cmUserName = auth('applicant')->user()->email;
        $cmUserEmail = auth('applicant')->user()->email;
        $cmUserRole = 'Applicant User';
        $cmProfileUrl = route('applicant.dashboard');
        $cmLogoutUrl = route('applicant.logout');
        $cmDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $cmUserName = auth('student')->user()->email;
        $cmUserEmail = auth('student')->user()->email;
        $cmUserRole = 'Student User';
        $cmProfileUrl = route('students.dashboard.profile');
        $cmLogoutUrl = route('students.logout');
        $cmDashboardUrl = route('students.dashboard');
    } else {
        $cmEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $cmEmployee = $cmEmployeeUser?->employee;
        $cmUserName = trim((isset($cmEmployee?->title?->name) ? $cmEmployee->title->name.' ' : '').($cmEmployee?->first_name ?? '').' '.($cmEmployee?->last_name ?? ''));
        $cmUserName = $cmUserName !== '' ? $cmUserName : (Auth::user()->name ?? 'London Churchill College');
        $cmUserEmail = Auth::user()->email ?? '';
        $cmUserRole = $cmEmployee?->employment?->employeeJobTitle?->name ?? 'Staff';
        $cmProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $cmLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $cmDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    // Initials read from first/last name rather than the display name: the
    // latter is title-prefixed, so "Mr Nazmus Sakib" would render as "MS".
    $cmFirst = trim((string) ($cmEmployee?->first_name ?? ''));
    $cmLast = trim((string) ($cmEmployee?->last_name ?? ''));
    if ($cmFirst !== '' && $cmLast !== '') {
        $cmInitials = strtoupper(mb_substr($cmFirst, 0, 1).mb_substr($cmLast, 0, 1));
    } else {
        $cmWords = preg_split('/\s+/', trim($cmUserName));
        $cmInitials = strtoupper(mb_substr($cmWords[0] ?? 'L', 0, 1).mb_substr(count($cmWords) > 1 ? end($cmWords) : ($cmWords[0] ?? 'C'), 0, 1));
    }
    $cmInitials = $cmInitials !== '' ? $cmInitials : 'LC';

    $cmAvatarUrl = null;
    if (isset($cmEmployee) && $cmEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$cmEmployee->id.'/'.$cmEmployee->photo)) {
        $cmAvatarUrl = Storage::disk('local')->url('public/employees/'.$cmEmployee->id.'/'.$cmEmployee->photo);
    }

    // Logo comes from Settings → Branding. `site_logo` is the white-wordmark
    // lockup, which is the one that reads on this dark bar; `site_dark_logo`
    // (navy wordmark) is only a fallback for when it hasn't been uploaded.
    $cmLogoOptions = App\Models\Option::where('category', 'SITE_SETTINGS')
        ->whereIn('name', ['site_logo', 'site_dark_logo'])
        ->pluck('value', 'name')
        ->toArray();

    $cmHeaderLogo = null;
    foreach (['site_logo', 'site_dark_logo'] as $cmLogoKey) {
        $cmLogoValue = $cmLogoOptions[$cmLogoKey] ?? null;
        if (!empty($cmLogoValue) && Storage::disk('local')->exists('public/'.$cmLogoValue)) {
            $cmHeaderLogo = Storage::disk('local')->url('public/'.$cmLogoValue);
            break;
        }
    }

    $cmSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $cmCanSearchApplicants = $cmSearchConfig['applicants'];
    $cmCanSearchStudents = $cmSearchConfig['students'];
    $cmCanSearchEmployees = $cmSearchConfig['employees'];
    $cmShowSearch = $cmSearchConfig['show'] && Route::has('global.search');
    $cmSearchPlaceholder = $cmSearchConfig['placeholder'];

    // Every screen under `course-management/*` belongs to this module, but only
    // the landing page carries the `course.management` route name — the
    // sub-pages (semester, courses, plans, …) have their own. Match on the URI
    // so the Courses nav entry stays lit throughout. A few screens in the
    // module sit outside that prefix (Term Declarations is at
    // `/term-declaration`), hence the second pattern and the route-name list.
    $cmInModule = request()->is('course-management*')
        || request()->is('term-declaration*')
        || in_array(Route::currentRouteName(), ['course.management', 'semester', 'modulelevels', 'courses', 'courses.show', 'course.module.show'], true);
@endphp

<div class="cm-header no-print" data-global-header>
    <header class="cm-header__bar">
        <a href="{{ $cmDashboardUrl }}" class="cm-header__logo{{ $cmHeaderLogo ? '' : ' cm-header__logo--fallback' }}" aria-label="London Churchill College">
            @if($cmHeaderLogo)
                <img src="{{ $cmHeaderLogo }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>

        <span class="cm-header__divider" aria-hidden="true"></span>

        <nav class="cm-nav" aria-label="Primary navigation">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $cmHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $cmActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'course.management' && $cmInModule);
                        $cmLabel = $cmMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($cmHasSub)
                        <div class="cm-nav__group" data-cm-nav-group>
                            <button type="button" class="cm-nav__item {{ $cmActive ? 'cm-nav__item--active' : '' }}" data-cm-nav-toggle aria-expanded="false">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                {{ $cmLabel }}
                                <svg class="cm-nav__caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="cm-nav__menu">
                                <div class="cm-nav__menu-title">{{ $menu['title'] }}</div>
                                @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                    @php
                                        $cmSubActive = isset($subMenu['route_name']) && $subMenu['route_name'] == (Route::currentRouteName() ?? '');
                                    @endphp
                                    <a href="{{ $cmMenuUrl($subMenu) }}" class="cm-nav__menu-item {{ $cmSubActive ? 'cm-nav__menu-item--active' : '' }}">
                                        <span class="cm-nav__menu-icon">
                                            @if($subKey == 'admission')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                            @elseif($subKey == 'student')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            @endif
                                        </span>
                                        <span class="cm-nav__menu-copy">
                                            <span class="cm-nav__menu-name">{{ $subMenu['title'] }}</span>
                                            <span class="cm-nav__menu-desc">{{ $cmSubMenuDescription($subKey) }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $cmMenuUrl($menu) }}" class="cm-nav__item {{ $cmActive ? 'cm-nav__item--active' : '' }}">
                            @if($menuKey == 'dashboard')
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect></svg>
                            @elseif($menuKey == 'course.management')
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                            @else
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"></path></svg>
                            @endif
                            {{ $cmLabel }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="cm-header__spacer">
            @if($cmShowSearch)
                <div class="cm-search" data-cm-search data-global-search
                     data-search-url="{{ route('global.search') }}"
                     data-search-applicants="{{ $cmCanSearchApplicants ? '1' : '0' }}"
                     data-search-students="{{ $cmCanSearchStudents ? '1' : '0' }}"
                     data-search-employees="{{ $cmCanSearchEmployees ? '1' : '0' }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                    <input class="cm-search__input" type="search" autocomplete="off" placeholder="{{ $cmSearchPlaceholder }}" data-cm-search-input data-global-search-input aria-label="{{ $cmSearchPlaceholder }}">
                    <svg class="cm-search__close" data-cm-search-close width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.55)" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    <div class="cm-search__results" data-cm-search-results data-global-search-results></div>
                </div>
                <button type="button" class="cm-search__toggle" data-cm-search-toggle aria-label="Open search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                </button>
            @endif

            @if(Auth::guard('agent')->check())
                @impersonating($guard='agent')
                    <a href="{{ route('impersonate.leave') }}" class="cm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('applicant')->check())
                @impersonating($guard='applicant')
                    <a href="{{ route('applicant.impersonate.leave') }}" class="cm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('student')->check())
                @impersonating($guard='student')
                    <a href="{{ route('impersonate.leave') }}" class="cm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @else
                @impersonating($guard=null)
                    <a href="{{ route('impersonate.leave') }}" class="cm-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @endif

            <div class="cm-profile" data-cm-profile>
                <button type="button" class="cm-profile__trigger" data-cm-profile-toggle aria-label="Open user menu">
                    <span class="cm-profile__copy">
                        <span class="cm-profile__name">{{ $cmUserName }}</span>
                        <span class="cm-profile__role">{{ $cmUserRole }}</span>
                    </span>
                    <span class="cm-profile__avatar">
                        @if($cmAvatarUrl)
                            <img src="{{ $cmAvatarUrl }}" alt="{{ $cmUserName }}">
                        @else
                            {{ $cmInitials }}
                        @endif
                    </span>
                    <svg class="cm-profile__caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                </button>

                <div class="cm-profile__menu">
                    <div class="cm-profile__menu-head">
                        <span class="cm-profile__menu-name">{{ $cmUserName }}</span>
                        <span class="cm-profile__menu-email">{{ $cmUserEmail }}</span>
                    </div>
                    <div class="cm-profile__menu-body">
                        <a href="{{ $cmProfileUrl }}" class="cm-profile__menu-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span>Profile</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.35)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
                        </a>
                        <a href="{{ $cmLogoutUrl }}" class="cm-profile__menu-link cm-profile__menu-link--danger">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"></path></svg>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
</div>
