{{--
    The global header — brand lock-up, primary nav, global search, account
    menu and the breadcrumb context strip.

    Extracted from `layout/top-menu` so redesigned module shells that extend
    `layout/base` directly (Task Manager, …) render the *same* header rather
    than a look-alike copy. Everything it needs comes from view data that is
    already global: `$top_menu` / `$first_level_active_index` (MenuComposer),
    the auth guards, and the optional `$breadcrumbs` array a controller passes.

    Any page including this should also include `components/mobile-menu`
    unless it is deliberately desktop-only — the header hides below 768px.
--}}
    @php
        $menuUrl = function ($menu) {
            return isset($menu['route_name']) && Route::has($menu['route_name'])
                ? route($menu['route_name'], $menu['params'] ?? [])
                : 'javascript:;';
        };

        $menuLabel = function ($key, $title) {
            return [
                'dashboard' => 'Dashboard',
                'course.management' => 'Courses',
                'students' => 'Students',
                'site.setting' => 'Settings',
            ][$key] ?? $title;
        };

        $menuIcon = function ($key, $fallback) {
            return [
                'dashboard' => 'layout-dashboard',
                'course.management' => 'book-open',
                'students' => 'users',
                'site.setting' => 'settings',
            ][$key] ?? $fallback;
        };

        $subMenuIcon = function ($key) {
            return [
                'admission' => 'file-plus',
                'student' => 'radio',
                'agent_management' => 'contact',
            ][$key] ?? 'activity';
        };

        $subMenuDescription = function ($key) {
            return [
                'admission' => 'New applications & offers',
                'student' => 'Enrolled & active students',
                'agent_management' => 'Recruitment partners',
            ][$key] ?? 'Open section';
        };

        $initialsFromName = function ($name) {
            $words = preg_split('/\s+/', trim((string) $name));
            $first = $words[0] ?? 'L';
            $last = count($words) > 1 ? $words[count($words) - 1] : ($words[0] ?? 'C');
            $initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

            return $initials !== '' ? $initials : 'LC';
        };

        $staticHeaderLogo = asset('build/assets/images/lcc-header-sample-logo.png');
        $headerLogo = $staticHeaderLogo;
        $darkHeaderLogo = cache()->get('site_dark_logo');

        if (!$darkHeaderLogo) {
            $darkHeaderLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_dark_logo')->value('value');

            if ($darkHeaderLogo) {
                cache()->forever('site_dark_logo', $darkHeaderLogo);
            }
        }

        if (!empty($darkHeaderLogo) && Storage::disk('local')->exists('public/'.$darkHeaderLogo)) {
            $headerLogo = Storage::disk('local')->url('public/'.$darkHeaderLogo);
        }

        $hideGlobalHeader = request()->routeIs('hr.portal') || request()->is('hr/portal');
        $searchConfig = \App\Support\GlobalSearch::forCurrentUser();
        $canSearchApplicants = $searchConfig['applicants'];
        $canSearchStudents = $searchConfig['students'];
        $canSearchEmployees = $searchConfig['employees'];
        $canShowGlobalSearch = $searchConfig['show'];
        $searchPlaceholder = $searchConfig['placeholder'];

        if (Auth::guard('agent')->check()) {
            $currentUserName = auth('agent')->user()->email;
            $currentUserEmail = auth('agent')->user()->email;
            $currentUserRole = 'Agent User';
            $profileUrl = Route::has('agent.account') ? route('agent.account') : route('agent.dashboard');
            $logoutUrl = route('agent.logout');
            $dashboardUrl = route('agent.dashboard');
            $guardLabel = 'Agent';
        } elseif (Auth::guard('applicant')->check()) {
            $currentUserName = auth('applicant')->user()->email;
            $currentUserEmail = auth('applicant')->user()->email;
            $currentUserRole = 'Applicant User';
            $profileUrl = route('applicant.dashboard');
            $logoutUrl = route('applicant.logout');
            $dashboardUrl = route('applicant.dashboard');
            $guardLabel = 'Applicant';
        } elseif (Auth::guard('student')->check()) {
            $studentUser = cache()->get('studentCache' . Auth::guard('student')->id()) ?? Auth::guard('student')->user()->load('student');
            $currentUserName = trim(($studentUser->student?->full_name ?? '') ?: auth('student')->user()->email);
            $currentUserEmail = auth('student')->user()->email;
            $currentUserRole = 'Student User';
            $profileUrl = route('students.dashboard.profile');
            $logoutUrl = route('students.logout');
            $dashboardUrl = route('students.dashboard');
            $guardLabel = 'Student';
        } else {
            $employeeUser = Auth::check() ? (cache()->get('employeeCache' . Auth::id()) ?? Auth::user()->load('employee')) : null;
            $employee = $employeeUser?->employee;
            $currentUserName = trim((isset($employee?->title?->name) ? $employee->title->name . ' ' : '') . ($employee?->first_name ?? '') . ' ' . ($employee?->last_name ?? ''));
            $currentUserName = $currentUserName !== '' ? $currentUserName : (Auth::user()->name ?? 'London Churchill College');
            $currentUserEmail = Auth::user()->email ?? '';
            $currentUserRole = $employee?->employment?->employeeJobTitle?->name ?? 'Staff';
            $profileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
            $logoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
            $dashboardUrl = Route::has('staff.dashboard') ? route('staff.dashboard') : (Route::has('dashboard') ? route('dashboard') : 'javascript:;');
            $guardLabel = 'User';
        }

        // Initials come from the employee's first/last name, not the display
        // name: that string is prefixed with the title and can carry middle
        // names, so "Mr Bhuiyan A M Nazmus Sakib" would read as "MS" (Mr +
        // Sakib) rather than "BS". Guards without an employee (agent /
        // applicant / student) keep parsing the display name.
        $initialsEmployee = $employee ?? null;
        $initialsFirst = trim((string) ($initialsEmployee?->first_name ?? ''));
        $initialsLast = trim((string) ($initialsEmployee?->last_name ?? ''));

        $currentInitials = ($initialsFirst !== '' && $initialsLast !== '')
            ? strtoupper(mb_substr($initialsFirst, 0, 1).mb_substr($initialsLast, 0, 1))
            : $initialsFromName(trim($initialsFirst.' '.$initialsLast) ?: $currentUserName);

        // Use the employee's (or student's) uploaded photo when there is one.
        // `photo_url` falls back to a generated `data:` initials avatar, which we
        // skip so the header keeps its own gold-ringed initials treatment.
        $currentAvatarUrl = null;
        $currentPhotoUrl = $employee->photo_url ?? ($studentUser->student->photo_url ?? null);

        if (!empty($currentPhotoUrl) && !str_starts_with($currentPhotoUrl, 'data:')) {
            $currentAvatarUrl = $currentPhotoUrl;
        }

        $breadcrumbsList = [
            ['label' => 'Dashboard', 'href' => $dashboardUrl],
        ];

        if (isset($breadcrumbs) && !empty($breadcrumbs)) {
            foreach ($breadcrumbs as $crumb) {
                $breadcrumbsList[] = [
                    'label' => $crumb['label'] ?? '',
                    'href' => $crumb['href'] ?? 'javascript:void(0);',
                ];
            }
        }

        $showClockinStatistics = Auth::user()
            && Route::currentRouteName() == 'dashboard'
            && !empty($home_work_statistics)
            && (
                (!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work)
                || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)
            );
    @endphp

    @unless ($hideGlobalHeader)
        @include('../layout/components/mobile-menu')

        <header class="lcc-global-header" data-global-header>
            <div class="lcc-global-header__frame">
                <div class="lcc-global-header__main">
                    {{-- Brand lock-up: the Site Settings "dark logo" already pairs the
                         crest with the three-line college wordmark, which is exactly the
                         brand block in the design. --}}
                    <a href="{{ $dashboardUrl }}" class="lcc-global-header__brand" aria-label="London Churchill College dashboard">
                        <img src="{{ $headerLogo }}" alt="London Churchill College">
                    </a>

                <nav class="lcc-global-header__nav" aria-label="Primary navigation">
                    @foreach ($top_menu as $menuKey => $menu)
                        @php
                            $hasSubMenu = isset($menu['sub_menu']) && !empty($menu['sub_menu']);
                            $isActive = $first_level_active_index == $menuKey;
                            $label = $menuLabel($menuKey, $menu['title']);
                            $icon = $menuIcon($menuKey, $menu['icon']);
                        @endphp

                        @if ($hasSubMenu)
                            <div class="lcc-global-header__nav-group" data-header-menu>
                                <button type="button" class="lcc-global-header__nav-item {{ $isActive ? 'lcc-global-header__nav-item--active' : '' }}" data-header-menu-toggle>
                                    <i data-lucide="{{ $icon }}"></i>
                                    <span>{{ $label }}</span>
                                    <i data-lucide="chevron-down" class="lcc-global-header__chevron"></i>
                                </button>
                                <div class="lcc-global-header__menu lcc-global-header__menu--nav">
                                    <div class="lcc-global-header__menu-title">{{ $menu['title'] }}</div>
                                    @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                        <a href="{{ $menuUrl($subMenu) }}" class="lcc-global-header__menu-link">
                                            <span class="lcc-global-header__menu-icon">
                                                <i data-lucide="{{ $subMenuIcon($subMenuKey) }}"></i>
                                            </span>
                                            <span>
                                                <span>{{ $subMenu['title'] }}</span>
                                                <small>{{ $subMenuDescription($subMenuKey) }}</small>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $menuUrl($menu) }}" class="lcc-global-header__nav-item {{ $isActive ? 'lcc-global-header__nav-item--active' : '' }}">
                                <i data-lucide="{{ $icon }}"></i>
                                <span>{{ $label }}</span>
                            </a>
                        @endif
                    @endforeach
                </nav>

                <div class="lcc-global-header__end">
                @if ($canShowGlobalSearch && Route::has('global.search'))
                    <div class="lcc-global-header__search" data-global-search data-search-url="{{ route('global.search') }}" data-search-applicants="{{ $canSearchApplicants ? '1' : '0' }}" data-search-students="{{ $canSearchStudents ? '1' : '0' }}" data-search-employees="{{ $canSearchEmployees ? '1' : '0' }}">
                        <label class="lcc-global-header__search-box">
                            <i data-lucide="search"></i>
                            <input type="text" autocomplete="off" placeholder="{{ $searchPlaceholder }}" data-global-search-input>
                            <button type="button" class="lcc-global-header__search-clear" data-global-search-clear aria-label="Clear search" hidden>
                                <i data-lucide="x"></i>
                            </button>
                        </label>
                        <div class="lcc-global-header__search-results" data-global-search-results></div>
                    </div>
                @endif

                @if(Auth::guard('agent')->check())
                    @impersonating($guard='agent')
                        <a href="{{ route('impersonate.leave') }}" class="lcc-global-header__impersonate">
                            <i data-lucide="log-out"></i>
                            <span>Leave Impersonate...</span>
                        </a>
                    @endImpersonating
                @elseif(Auth::guard('applicant')->check())
                    @impersonating($guard='applicant')
                        <a href="{{ route('applicant.impersonate.leave') }}" class="lcc-global-header__impersonate">
                            <i data-lucide="log-out"></i>
                            <span>Leave Impersonate...</span>
                        </a>
                    @endImpersonating
                @elseif(Auth::guard('student')->check())
                    @impersonating($guard='student')
                        <a href="{{ route('impersonate.leave') }}" class="lcc-global-header__impersonate">
                            <i data-lucide="log-out"></i>
                            <span>Leave Impersonate...</span>
                        </a>
                    @endImpersonating
                @else
                    @impersonating($guard=null)
                        <a href="{{ route('impersonate.leave') }}" class="lcc-global-header__impersonate">
                            <i data-lucide="log-out"></i>
                            <span>Leave Impersonate...</span>
                        </a>
                    @endImpersonating
                @endif

                <div class="lcc-global-header__account" data-header-menu>
                    <button type="button" class="lcc-global-header__account-toggle" data-header-menu-toggle>
                        <span class="lcc-global-header__account-copy">
                            <strong>{{ $currentUserName }}</strong>
                            <small>{{ $currentUserRole }}</small>
                        </span>
                        <span class="lcc-global-header__avatar">
                            @if($currentAvatarUrl)
                                <img src="{{ $currentAvatarUrl }}" alt="{{ $currentUserName }}">
                            @else
                                {{ $currentInitials }}
                            @endif
                        </span>
                        <i data-lucide="chevron-down" class="lcc-global-header__chevron"></i>
                    </button>
                    <div class="lcc-global-header__menu lcc-global-header__menu--account">
                        <div class="lcc-global-header__account-card">
                            <span class="lcc-global-header__avatar lcc-global-header__avatar--large">
                                @if($currentAvatarUrl)
                                    <img src="{{ $currentAvatarUrl }}" alt="{{ $currentUserName }}">
                                @else
                                    {{ $currentInitials }}
                                @endif
                            </span>
                            <span>
                                <strong>{{ $currentUserName }}</strong>
                                <small>{{ $currentUserEmail }}</small>
                                <em><span></span>{{ $currentUserRole }}</em>
                            </span>
                        </div>
                        <div class="lcc-global-header__account-actions">
                            <a href="{{ $profileUrl }}" class="lcc-global-header__account-link">
                                <span><i data-lucide="user"></i></span>
                                <strong>Profile</strong>
                                <small>View & edit your details</small>
                                <i data-lucide="chevron-right"></i>
                            </a>
                            <a href="{{ $logoutUrl }}" class="lcc-global-header__account-link lcc-global-header__account-link--danger">
                                <span><i data-lucide="log-out"></i></span>
                                <strong>Logout</strong>
                                <small>End this session</small>
                            </a>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <div class="lcc-global-header__context">
                <nav aria-label="breadcrumb">
                    @foreach ($breadcrumbsList as $crumbIndex => $crumb)
                        @php $isLastCrumb = $crumbIndex === count($breadcrumbsList) - 1; @endphp
                        @if ($crumbIndex > 0)
                            <span class="lcc-global-header__crumb-dot" aria-hidden="true"></span>
                        @endif

                        @if ($crumbIndex === 0 && $isLastCrumb)
                            <strong class="lcc-global-header__crumb-home"><i data-lucide="home"></i>{{ $crumb['label'] }}</strong>
                        @elseif ($crumbIndex === 0)
                            <a href="{{ $crumb['href'] }}" class="lcc-global-header__crumb-home"><i data-lucide="home"></i>{{ $crumb['label'] }}</a>
                        @elseif ($isLastCrumb)
                            <strong>{{ $crumb['label'] }}</strong>
                        @else
                            <a href="{{ $crumb['href'] }}">{{ $crumb['label'] }}</a>
                        @endif
                    @endforeach
                </nav>

                @if($showClockinStatistics)
                    <div class="lcc-global-header__status" aria-label="Work status summary">
                        {!! $home_work_statistics !!}
                    </div>
                @endif
            </div>
            </div>
        </header>
    @endunless
