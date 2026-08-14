{{--
    Programme Dashboard-only header.

    Two bands, matching the design handoff: the navy nav bar (logo, primary
    nav, global search, user menu) and the darker sub-bar underneath carrying
    the breadcrumb trail, the open-term pills, the date and the live clock.

    The shared `components/top-bar` / `layout/top-menu` header is left alone for
    the rest of the app. Nav entries, privileges and guard handling mirror the
    global header so nothing a user could previously reach disappears here.

    View data this component reads (all optional):
      $pgdCrumbMid      string  middle breadcrumb label
      $pgdCrumbMidUrl   string  where that middle crumb points
      $pgdCrumbCurrent  string  the current page label
      $termChips        array   [['id','name','dot','tint','rate','today'], …]
                                — interactive filters on the dashboard
      $termDeclarations collection + $termDeclaration + $pgdTermRoute
                                — term switcher on the tutor screens
--}}
@php
    $pgdMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    // The design labels the four top-level entries Dashboard / Courses /
    // Students / Settings; TopMenu's own titles are longer.
    $pgdMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $pgdSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications & offers',
            'student' => 'Enrolled & active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    if (Auth::guard('agent')->check()) {
        $pgdUserName = auth('agent')->user()->email;
        $pgdUserEmail = auth('agent')->user()->email;
        $pgdUserRole = 'Agent User';
        $pgdProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $pgdLogoutUrl = route('agent.logout');
        $pgdDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $pgdUserName = auth('applicant')->user()->email;
        $pgdUserEmail = auth('applicant')->user()->email;
        $pgdUserRole = 'Applicant User';
        $pgdProfileUrl = route('applicant.dashboard');
        $pgdLogoutUrl = route('applicant.logout');
        $pgdDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $pgdUserName = auth('student')->user()->email;
        $pgdUserEmail = auth('student')->user()->email;
        $pgdUserRole = 'Student User';
        $pgdProfileUrl = route('students.dashboard.profile');
        $pgdLogoutUrl = route('students.logout');
        $pgdDashboardUrl = route('students.dashboard');
    } else {
        $pgdEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $pgdEmployee = $pgdEmployeeUser?->employee;
        $pgdUserName = trim((isset($pgdEmployee?->title?->name) ? $pgdEmployee->title->name.' ' : '').($pgdEmployee?->first_name ?? '').' '.($pgdEmployee?->last_name ?? ''));
        $pgdUserName = $pgdUserName !== '' ? $pgdUserName : (Auth::user()->name ?? 'London Churchill College');
        $pgdUserEmail = Auth::user()->email ?? '';
        // Designation only. Where no job title is on record fall back to the
        // work type, and failing that show nothing — the literal word "Staff"
        // told the reader less than the empty space does.
        $pgdUserRole = $pgdEmployee?->employment?->employeeJobTitle?->name
            ?? $pgdEmployee?->employment?->employeeWorkType?->name
            ?? '';
        $pgdProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $pgdLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $pgdDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    // Initials read from first/last name rather than the display name: the
    // latter is title-prefixed, so "Mr Nazmus Sakib" would render as "MS".
    $pgdFirst = trim((string) ($pgdEmployee?->first_name ?? ''));
    $pgdLast = trim((string) ($pgdEmployee?->last_name ?? ''));
    if ($pgdFirst !== '' && $pgdLast !== '') {
        $pgdInitials = strtoupper(mb_substr($pgdFirst, 0, 1).mb_substr($pgdLast, 0, 1));
    } else {
        $pgdWords = preg_split('/\s+/', trim($pgdUserName));
        $pgdInitials = strtoupper(mb_substr($pgdWords[0] ?? 'L', 0, 1).mb_substr(count($pgdWords) > 1 ? end($pgdWords) : ($pgdWords[0] ?? 'C'), 0, 1));
    }
    $pgdInitials = $pgdInitials !== '' ? $pgdInitials : 'LC';

    $pgdAvatarUrl = null;
    if (isset($pgdEmployee) && $pgdEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$pgdEmployee->id.'/'.$pgdEmployee->photo)) {
        $pgdAvatarUrl = Storage::disk('local')->url('public/employees/'.$pgdEmployee->id.'/'.$pgdEmployee->photo);
    }

    // Logo comes from Settings → Branding. `site_logo` is the white-wordmark
    // lockup, which is the one that reads on this dark bar; `site_dark_logo`
    // (navy wordmark) is only a fallback for when it hasn't been uploaded.
    $pgdLogoOptions = App\Models\Option::where('category', 'SITE_SETTINGS')
        ->whereIn('name', ['site_logo', 'site_dark_logo'])
        ->pluck('value', 'name')
        ->toArray();

    $pgdHeaderLogo = null;
    foreach (['site_logo', 'site_dark_logo'] as $pgdLogoKey) {
        $pgdLogoValue = $pgdLogoOptions[$pgdLogoKey] ?? null;
        if (!empty($pgdLogoValue) && Storage::disk('local')->exists('public/'.$pgdLogoValue)) {
            $pgdHeaderLogo = Storage::disk('local')->url('public/'.$pgdLogoValue);
            break;
        }
    }

    $pgdSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $pgdShowSearch = $pgdSearchConfig['show'] && Route::has('global.search');

    $pgdDashboardHome = Route::has('programme.dashboard') ? route('programme.dashboard') : $pgdDashboardUrl;
    $pgdInModule = request()->is('programme-dashboard*');
@endphp

<div class="pgd-header no-print" data-global-header>
    <header class="pgd-header__bar">
        <a href="{{ $pgdDashboardUrl }}" class="pgd-header__logo{{ $pgdHeaderLogo ? '' : ' pgd-header__logo--fallback' }}" aria-label="London Churchill College">
            @if($pgdHeaderLogo)
                <img src="{{ $pgdHeaderLogo }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>

        <nav class="pgd-nav" aria-label="Primary navigation">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $pgdHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $pgdActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'dashboard' && $pgdInModule);
                        $pgdLabel = $pgdMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($pgdHasSub)
                        <div class="pgd-nav__group" data-pgd-nav-group>
                            <button type="button" class="pgd-nav__item {{ $pgdActive ? 'pgd-nav__item--active' : '' }}" data-pgd-nav-toggle aria-expanded="false">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20v-1.6a3.4 3.4 0 0 0-3.4-3.4H6.4A3.4 3.4 0 0 0 3 18.4V20"></path><circle cx="9.5" cy="8" r="3.4"></circle><path d="M21 20v-1.6a3.4 3.4 0 0 0-2.6-3.3"></path><path d="M15.8 4.7a3.4 3.4 0 0 1 0 6.6"></path></svg>
                                {{ $pgdLabel }}
                                <svg class="pgd-nav__caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                            </button>
                            <div class="pgd-nav__menu">
                                <div class="pgd-nav__menu-title">{{ $menu['title'] }}</div>
                                @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                    @php
                                        $pgdSubActive = isset($subMenu['route_name']) && $subMenu['route_name'] == (Route::currentRouteName() ?? '');
                                    @endphp
                                    <a href="{{ $pgdMenuUrl($subMenu) }}" class="pgd-nav__menu-item {{ $pgdSubActive ? 'pgd-nav__menu-item--active' : '' }}">
                                        <span class="pgd-nav__menu-icon pgd-nav__menu-icon--{{ $subKey }}">
                                            @if($subKey == 'admission')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M12 5v14"></path><path d="M5 12h14"></path></svg>
                                            @elseif($subKey == 'student')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><circle cx="12" cy="12" r="8"></circle><circle cx="12" cy="12" r="3"></circle></svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"></circle><path d="M3.5 20v-1.4A3.4 3.4 0 0 1 6.9 15h4.2a3.4 3.4 0 0 1 3.4 3.6V20"></path><path d="M17 9.5h3.5"></path><path d="M18.75 7.75v3.5"></path></svg>
                                            @endif
                                        </span>
                                        <span class="pgd-nav__menu-copy">
                                            <span class="pgd-nav__menu-name">{{ $subMenu['title'] }}</span>
                                            <span class="pgd-nav__menu-desc">{{ $pgdSubMenuDescription($subKey) }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $pgdMenuUrl($menu) }}" class="pgd-nav__item {{ $pgdActive ? 'pgd-nav__item--active' : '' }}">
                            @if($menuKey == 'dashboard')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="{{ $pgdActive ? '#6FD3D0' : 'currentColor' }}" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>
                            @elseif($menuKey == 'course.management')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H10a2 2 0 0 1 2 2v14a1.6 1.6 0 0 0-1.6-1.6H4Z"></path><path d="M20 5.5A1.5 1.5 0 0 0 18.5 4H14a2 2 0 0 0-2 2v14a1.6 1.6 0 0 1 1.6-1.6H20Z"></path></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3.1"></circle><path d="M19.1 14.6a1.6 1.6 0 0 0 .32 1.77l.06.05a2 2 0 1 1-2.83 2.83l-.06-.06a1.6 1.6 0 0 0-1.77-.32 1.6 1.6 0 0 0-1 1.47V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-1.05-1.47 1.6 1.6 0 0 0-1.77.32l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.6 1.6 0 0 0 .32-1.77 1.6 1.6 0 0 0-1.47-1H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.47-1.05 1.6 1.6 0 0 0-.32-1.77l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.6 1.6 0 0 0 1.77.32H9a1.6 1.6 0 0 0 1-1.47V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.47 1.6 1.6 0 0 0 1.77-.32l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.6 1.6 0 0 0-.32 1.77V9a1.6 1.6 0 0 0 1.47 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.47 1Z"></path></svg>
                            @endif
                            {{ $pgdLabel }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="pgd-header__tail">
            @if($pgdShowSearch)
                <div class="pgd-search" data-pgd-search data-global-search
                     data-search-url="{{ route('global.search') }}"
                     data-search-applicants="{{ $pgdSearchConfig['applicants'] ? '1' : '0' }}"
                     data-search-students="{{ $pgdSearchConfig['students'] ? '1' : '0' }}"
                     data-search-employees="{{ $pgdSearchConfig['employees'] ? '1' : '0' }}">
                    <label class="pgd-search__field">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#93A7C4" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="6.5"></circle><path d="M16 16l4 4"></path></svg>
                        <input class="pgd-search__input" type="search" autocomplete="off" placeholder="{{ $pgdSearchConfig['placeholder'] }}" data-global-search-input aria-label="{{ $pgdSearchConfig['placeholder'] }}">
                        <button type="button" class="pgd-search__clear" data-global-search-clear aria-label="Clear search" hidden>
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 6l12 12"></path><path d="M18 6L6 18"></path></svg>
                        </button>
                    </label>
                    <div class="pgd-search__results" data-global-search-results></div>
                </div>
            @endif

            @if(Auth::guard('agent')->check())
                @impersonating($guard='agent')
                    <a href="{{ route('impersonate.leave') }}" class="pgd-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('applicant')->check())
                @impersonating($guard='applicant')
                    <a href="{{ route('applicant.impersonate.leave') }}" class="pgd-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @elseif(Auth::guard('student')->check())
                @impersonating($guard='student')
                    <a href="{{ route('impersonate.leave') }}" class="pgd-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @else
                @impersonating($guard=null)
                    <a href="{{ route('impersonate.leave') }}" class="pgd-nav__item">Leave Impersonate...</a>
                @endImpersonating
            @endif

            <div class="pgd-profile" data-pgd-profile>
                <button type="button" class="pgd-profile__trigger" data-pgd-profile-toggle aria-label="Open user menu">
                    <span class="pgd-profile__copy">
                        <span class="pgd-profile__name">{{ $pgdUserName }}</span>
                        @if($pgdUserRole !== '')
                            <span class="pgd-profile__role">{{ $pgdUserRole }}</span>
                        @endif
                    </span>
                    <span class="pgd-profile__avatar">
                        @if($pgdAvatarUrl)
                            <img src="{{ $pgdAvatarUrl }}" alt="{{ $pgdUserName }}">
                        @else
                            {{ $pgdInitials }}
                        @endif
                    </span>
                </button>

                <div class="pgd-profile__menu">
                    <div class="pgd-profile__menu-head">
                        <span class="pgd-profile__menu-avatar">
                            @if($pgdAvatarUrl)
                                <img src="{{ $pgdAvatarUrl }}" alt="{{ $pgdUserName }}">
                            @else
                                {{ $pgdInitials }}
                            @endif
                        </span>
                        <span class="pgd-profile__menu-copy">
                            <span class="pgd-profile__menu-name">{{ $pgdUserName }}</span>
                            <span class="pgd-profile__menu-email">{{ $pgdUserEmail }}</span>
                            @if($pgdUserRole !== '')
                                <span class="pgd-profile__menu-badge"><span></span>{{ $pgdUserRole }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="pgd-profile__menu-body">
                        <a href="{{ $pgdProfileUrl }}" class="pgd-profile__menu-link">
                            <span class="pgd-profile__menu-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#E3C878" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8.5" r="3.4"></circle><path d="M5 20v-1.2A4 4 0 0 1 9 15h6a4 4 0 0 1 4 3.8V20"></path></svg>
                            </span>
                            <span>
                                <span class="pgd-profile__menu-label">Profile</span>
                                <span class="pgd-profile__menu-desc">View &amp; edit your details</span>
                            </span>
                        </a>
                        <a href="{{ $pgdLogoutUrl }}" class="pgd-profile__menu-link pgd-profile__menu-link--danger">
                            <span class="pgd-profile__menu-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F09A93" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h4.5A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5H14"></path><path d="M9 8l-5 4 5 4"></path><path d="M4 12h11"></path></svg>
                            </span>
                            <span>
                                <span class="pgd-profile__menu-label">Logout</span>
                                <span class="pgd-profile__menu-desc">End this session</span>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="pgd-subbar">
        <div class="pgd-crumbs">
            <a href="{{ $pgdDashboardHome }}">Dashboard</a>
            @if(!empty($pgdCrumbMid ?? null))
                <span class="pgd-crumbs__sep">/</span>
                <a href="{{ $pgdCrumbMidUrl ?? 'javascript:void(0);' }}">{{ $pgdCrumbMid }}</a>
            @endif
            <span class="pgd-crumbs__sep">/</span>
            <strong aria-current="page">{{ $pgdCrumbCurrent ?? 'Daily class information' }}</strong>
        </div>

        <div class="pgd-subbar__tail">
            @if(!empty($termChips ?? null))
                <div class="pgd-terms" data-pgd-terms>
                    @foreach($termChips as $chip)
                        <button type="button" class="pgd-term is-on" data-pgd-term="{{ $chip['id'] }}" style="--pgd-term-dot: {{ $chip['dot'] }};">
                            <span class="pgd-term__dot"></span>
                            <span class="pgd-term__name">{{ $chip['name'] }}</span>
                            <span class="pgd-term__rate">{{ $chip['rate'] }}%</span>
                            <span class="pgd-term__count">· {{ $chip['today'] }} today</span>
                        </button>
                    @endforeach
                </div>
                <span class="pgd-subbar__rule"></span>
            @elseif(!empty($termDeclarations ?? null) && !empty($pgdTermRoute ?? null) && !in_array(null, $pgdTermRouteExtra ?? [], true))
                {{-- Tutor screens can switch to any term ever declared — dozens
                     of them — so this is a picker, not a row of pills. The
                     dashboard's pills stay pills because only the open terms
                     appear there and they toggle rather than navigate. --}}
                <div class="pgd-termpicker" data-pgd-termpicker>
                    <button type="button" class="pgd-termpicker__trigger" data-pgd-termpicker-toggle aria-expanded="false">
                        <span class="pgd-termpicker__dot"></span>
                        <span class="pgd-termpicker__name">{{ $termDeclaration->name ?? 'Select term' }}</span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                    </button>
                    <div class="pgd-termpicker__panel">
                        <input type="text" class="pgd-termpicker__search" placeholder="Search terms…" data-pgd-termpicker-search aria-label="Search terms">
                        <div class="pgd-termpicker__list" data-pgd-termpicker-list>
                            @foreach($termDeclarations as $tds)
                                <a href="{{ route($pgdTermRoute, array_merge([$tds->id], $pgdTermRouteExtra ?? [])) }}"
                                   class="pgd-termpicker__opt {{ (isset($termDeclaration->id) && $termDeclaration->id == $tds->id) ? 'is-on' : '' }}">{{ $tds->name }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <span class="pgd-subbar__rule"></span>
            @endif

            <span class="pgd-subbar__date">{{ date('D j F Y') }}</span>
            <span class="pgd-subbar__rule"></span>
            <span class="pgd-subbar__clock"><span class="pgd-subbar__pulse"></span><span id="theClock">{{ date('H:i:s') }}</span></span>
        </div>
    </div>
</div>
