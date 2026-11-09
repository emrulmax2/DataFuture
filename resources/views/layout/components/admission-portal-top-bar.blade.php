{{--
    Admission portal top bar: brand, the four top-level sections (Students
    opens a menu), global search, the signed-in user's menu, then the
    breadcrumb strip. Menu entries, privileges and guard handling follow the
    app's TopMenu, so nothing a user could reach before disappears here.

    Dropdown state is the shared `data-header-menu` / `data-menu-open`
    contract from js/search.js; the search box is one of those menus too, so
    Escape and outside clicks close it like everything else.
--}}
@php
    $adpMenuUrl = function ($menu) {
        return isset($menu['route_name']) && Route::has($menu['route_name'])
            ? route($menu['route_name'], $menu['params'] ?? [])
            : 'javascript:;';
    };

    // The design labels the four entries Dashboard / Courses / Students /
    // Settings; TopMenu's own titles are longer.
    $adpMenuLabel = function ($key, $title) {
        return [
            'dashboard' => 'Dashboard',
            'course.management' => 'Courses',
            'students' => 'Students',
            'site.setting' => 'Settings',
        ][$key] ?? $title;
    };

    $adpSubMenuDescription = function ($key) {
        return [
            'admission' => 'New applications & offers',
            'student' => 'Enrolled & active students',
            'agent_management' => 'Recruitment partners',
        ][$key] ?? 'Open section';
    };

    $adpEmployee = null;
    if (Auth::guard('agent')->check()) {
        $adpUserName = auth('agent')->user()->email;
        $adpUserEmail = auth('agent')->user()->email;
        $adpUserRole = 'Agent User';
        $adpProfileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
        $adpLogoutUrl = route('agent.logout');
        $adpDashboardUrl = route('agent.dashboard');
    } elseif (Auth::guard('applicant')->check()) {
        $adpUserName = auth('applicant')->user()->email;
        $adpUserEmail = auth('applicant')->user()->email;
        $adpUserRole = 'Applicant User';
        $adpProfileUrl = route('applicant.dashboard');
        $adpLogoutUrl = route('applicant.logout');
        $adpDashboardUrl = route('applicant.dashboard');
    } elseif (Auth::guard('student')->check()) {
        $adpUserName = auth('student')->user()->email;
        $adpUserEmail = auth('student')->user()->email;
        $adpUserRole = 'Student User';
        $adpProfileUrl = route('students.dashboard.profile');
        $adpLogoutUrl = route('students.logout');
        $adpDashboardUrl = route('students.dashboard');
    } else {
        $adpEmployeeUser = Auth::check() ? (cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee')) : null;
        $adpEmployee = $adpEmployeeUser?->employee;
        $adpUserName = trim((isset($adpEmployee?->title?->name) ? $adpEmployee->title->name.' ' : '').($adpEmployee?->first_name ?? '').' '.($adpEmployee?->last_name ?? ''));
        $adpUserName = $adpUserName !== '' ? $adpUserName : (Auth::user()->name ?? 'London Churchill College');
        $adpUserEmail = Auth::user()->email ?? '';
        $adpUserRole = $adpEmployee?->employment?->employeeJobTitle?->name ?? 'Staff';
        $adpProfileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
        $adpLogoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
        $adpDashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
    }

    // Initials from first/last name rather than the display name, which is
    // title-prefixed ("Mr Nazmus Sakib" would otherwise read "MS").
    $adpFirst = trim((string) ($adpEmployee?->first_name ?? ''));
    $adpLast = trim((string) ($adpEmployee?->last_name ?? ''));
    if ($adpFirst !== '' && $adpLast !== '') {
        $adpInitials = strtoupper(mb_substr($adpFirst, 0, 1).mb_substr($adpLast, 0, 1));
    } else {
        $adpWords = preg_split('/\s+/', trim($adpUserName));
        $adpInitials = strtoupper(mb_substr($adpWords[0] ?? 'L', 0, 1).mb_substr(count($adpWords) > 1 ? end($adpWords) : ($adpWords[0] ?? 'C'), 0, 1));
    }
    $adpInitials = $adpInitials !== '' ? $adpInitials : 'LC';

    $adpAvatarUrl = null;
    if (isset($adpEmployee) && $adpEmployee?->photo && Storage::disk('local')->exists('public/employees/'.$adpEmployee->id.'/'.$adpEmployee->photo)) {
        $adpAvatarUrl = Storage::disk('local')->url('public/employees/'.$adpEmployee->id.'/'.$adpEmployee->photo);
    }

    // Settings → Branding. `site_logo` is the white crest-and-wordmark lockup,
    // which is the one that reads on this dark bar.
    $adpLogoFile = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $adpBrandLogo = ($adpLogoFile && Storage::disk('local')->exists('public/'.$adpLogoFile))
        ? Storage::disk('local')->url('public/'.$adpLogoFile)
        : asset('build/assets/images/red_and_white_logo.png');

    $adpSearchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $adpShowSearch = $adpSearchConfig['show'] && Route::has('global.search');

    // Pages that belong to the admission section but do not carry an
    // `admission.*` route name still light up the Students entry.
    $adpRoute = Route::currentRouteName() ?? '';
    $adpInAdmission = str_starts_with($adpRoute, 'admission')
        || str_starts_with($adpRoute, 'reports.applicant.analysis');

    $adpCrumbs = [];
    if (isset($breadcrumbs) && !empty($breadcrumbs)) {
        foreach ($breadcrumbs as $crumb) {
            $adpCrumbs[] = [
                'label' => $crumb['label'] ?? '',
                'href' => $crumb['href'] ?? 'javascript:void(0);',
            ];
        }
    }
@endphp

<header class="adp-topbar no-print" data-global-header>
    <div class="adp-topbar__in">
        <a href="{{ $adpDashboardUrl }}" class="adp-brand" aria-label="London Churchill College">
            <img src="{{ $adpBrandLogo }}" alt="London Churchill College">
        </a>

        <nav class="adp-topnav" aria-label="Main">
            @if(isset($top_menu))
                @foreach($top_menu as $menuKey => $menu)
                    @php
                        $adpHasSub = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                        $adpActive = ($first_level_active_index ?? '') == $menuKey || ($menuKey == 'students' && $adpInAdmission);
                        $adpLabel = $adpMenuLabel($menuKey, $menu['title']);
                    @endphp

                    @if($adpHasSub)
                        <span class="adp-tn-wrap" data-header-menu data-menu-open="false">
                            <button type="button" class="{{ $adpActive ? 'on' : '' }}" data-header-menu-toggle aria-expanded="false" aria-haspopup="menu">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                                {{ $adpLabel }}
                                <svg class="adp-tn-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            <div class="adp-tn-menu" role="menu">
                                <small>{{ $menu['title'] }}</small>
                                @foreach($menu['sub_menu'] as $subKey => $subMenu)
                                    @php
                                        $adpSubActive = isset($subMenu['route_name'])
                                            && ($subMenu['route_name'] == $adpRoute
                                                || ($subKey == 'admission' && $adpInAdmission));
                                    @endphp
                                    <a role="menuitem" href="{{ $adpMenuUrl($subMenu) }}" class="{{ $adpSubActive ? 'on' : '' }}">
                                        <span class="adp-tn-ico">
                                            @if($subKey == 'admission')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                            @elseif($subKey == 'student')
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                                            @else
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/></svg>
                                            @endif
                                        </span>
                                        <span>
                                            <b>{{ $subMenu['title'] }}</b>
                                            <em>{{ $adpSubMenuDescription($subKey) }}</em>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </span>
                    @else
                        <a href="{{ $adpMenuUrl($menu) }}" class="{{ $adpActive ? 'on' : '' }}">
                            @if($menuKey == 'dashboard')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7.5" height="9" rx="2"/><rect x="13.5" y="3" width="7.5" height="5" rx="2"/><rect x="13.5" y="11" width="7.5" height="10" rx="2"/><rect x="3" y="15" width="7.5" height="6" rx="2"/></svg>
                            @elseif($menuKey == 'course.management')
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                            @endif
                            {{ $adpLabel }}
                        </a>
                    @endif
                @endforeach
            @endif
        </nav>

        <div class="adp-tb-right">
            @if(Auth::guard('agent')->check())
                @impersonating($guard='agent')
                    <a href="{{ route('impersonate.leave') }}" class="adp-tb-leave">Leave impersonate</a>
                @endImpersonating
            @elseif(Auth::guard('applicant')->check())
                @impersonating($guard='applicant')
                    <a href="{{ route('applicant.impersonate.leave') }}" class="adp-tb-leave">Leave impersonate</a>
                @endImpersonating
            @elseif(Auth::guard('student')->check())
                @impersonating($guard='student')
                    <a href="{{ route('impersonate.leave') }}" class="adp-tb-leave">Leave impersonate</a>
                @endImpersonating
            @else
                @impersonating($guard=null)
                    <a href="{{ route('impersonate.leave') }}" class="adp-tb-leave">Leave impersonate</a>
                @endImpersonating
            @endif

            @if($adpShowSearch)
                <span class="adp-tn-wrap adp-gs-wrap" data-header-menu data-menu-open="false">
                    <button type="button" class="adp-tb-icon" data-header-menu-toggle data-adp-search-toggle data-tip="Search" aria-label="Search" aria-expanded="false">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    </button>
                    <div class="adp-gs" data-global-search data-search-open="false"
                         data-search-url="{{ route('global.search') }}"
                         data-search-applicants="{{ $adpSearchConfig['applicants'] ? '1' : '0' }}"
                         data-search-students="{{ $adpSearchConfig['students'] ? '1' : '0' }}"
                         data-search-employees="{{ $adpSearchConfig['employees'] ? '1' : '0' }}">
                        <div class="adp-gs-box">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            <input class="adp-gs-in" type="search" autocomplete="off" spellcheck="false" placeholder="Search applicants, students, staff…" aria-label="Search" data-global-search-input>
                            <button type="button" class="adp-tag-x" aria-label="Clear" data-global-search-clear hidden>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                            <kbd>Esc</kbd>
                        </div>
                        <div class="adp-gs-menu" role="listbox" data-global-search-results></div>
                    </div>
                </span>
            @endif

            <span class="adp-tn-wrap" data-header-menu data-menu-open="false">
                <button type="button" class="adp-tb-me" data-header-menu-toggle aria-expanded="false" aria-haspopup="menu" aria-label="Open user menu">
                    <span class="adp-topme-t"><b>{{ $adpUserName }}</b><small>{{ $adpUserRole }}</small></span>
                    <span class="adp-avatar adp-avatar--ring" style="width:36px;height:36px;font-size:13px;">
                        @if($adpAvatarUrl)
                            <img src="{{ $adpAvatarUrl }}" alt="">
                        @else
                            {{ $adpInitials }}
                        @endif
                    </span>
                    <svg class="adp-tn-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <div class="adp-tn-menu adp-tn-menu--right" role="menu">
                    <div class="adp-tn-id">
                        <span class="adp-avatar adp-avatar--ring" style="width:40px;height:40px;font-size:14px;">
                            @if($adpAvatarUrl)
                                <img src="{{ $adpAvatarUrl }}" alt="">
                            @else
                                {{ $adpInitials }}
                            @endif
                        </span>
                        <span><b>{{ $adpUserName }}</b><em>{{ $adpUserEmail }}</em></span>
                    </div>
                    <a role="menuitem" href="{{ $adpProfileUrl }}">
                        <span class="adp-tn-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg></span>
                        <span><b>Profile</b><em>View &amp; edit your details</em></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    @if(\App\Services\StaffDocumentVaultService::showsMenu())
                        <a role="menuitem" href="{{ route('user.account.document.pin') }}">
                            <span class="adp-tn-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg></span>
                            <span><b>PIN</b><em>For encrypted staff documents</em></span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @endif
                    <div class="adp-tn-sep"></div>
                    <a role="menuitem" class="danger" href="{{ $adpLogoutUrl }}">
                        <span class="adp-tn-ico"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/></svg></span>
                        <span><b>Logout</b><em>End this session</em></span>
                    </a>
                </div>
            </span>
        </div>
    </div>

    <div class="adp-crumbs">
        <nav class="adp-crumbs__in" aria-label="breadcrumb">
            <a class="adp-crumb" href="{{ $adpDashboardUrl }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                Dashboard
            </a>
            @foreach($adpCrumbs as $crumbIndex => $crumb)
                <span class="adp-crumb-sep"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></span>
                @if($crumbIndex === count($adpCrumbs) - 1)
                    <span class="adp-crumb on">{{ $crumb['label'] }}</span>
                @else
                    <a class="adp-crumb" href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
                @endif
            @endforeach
        </nav>
    </div>
</header>
