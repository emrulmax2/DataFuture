@php
    $siteOptions = $siteOpt ?? [];
    // Fall back to reading the option directly so the header logo stays in sync with
    // Site Settings > Company Logo even on pages whose controller does not pass $siteOpt.
    $siteLogoFile = (isset($siteOptions['site_logo']) && !empty($siteOptions['site_logo']))
        ? $siteOptions['site_logo']
        : \App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    $siteLogo = (!empty($siteLogoFile) && Storage::disk('local')->exists('public/'.$siteLogoFile))
        ? Storage::disk('local')->url('public/'.$siteLogoFile).'?v='.Storage::disk('local')->lastModified('public/'.$siteLogoFile)
        : asset('build/assets/images/logo_white.svg');
    $authUser = auth()->user();
    $searchConfig = \App\Support\GlobalSearch::forCurrentUser();
    $canSearchApplicants = $searchConfig['applicants'];
    $canSearchStudents = $searchConfig['students'];
    $canSearchEmployees = $searchConfig['employees'];
    $canShowGlobalSearch = $searchConfig['show'];
    $searchPlaceholder = $searchConfig['placeholder'];
    $employeeUser = Auth::check() ? (cache()->get('employeeCache' . Auth::id()) ?? Auth::user()->load('employee')) : null;
    $employee = $employeeUser?->employee;
    $userName = trim((isset($employee?->title?->name) ? $employee->title->name.' ' : '').($employee?->first_name ?? '').' '.($employee?->last_name ?? ''));
    $userName = $userName !== '' ? $userName : ($authUser->name ?? 'User');
    $userEmail = $authUser->email ?? '';
    $userRole = $employee?->employment?->employeeJobTitle?->name ?? 'Staff';
    $profileUrl = Route::has('user.account') ? route('user.account') : 'javascript:;';
    $logoutUrl = Route::has('logout') ? route('logout') : 'javascript:;';
    $initialsFromName = function ($name) {
        $words = preg_split('/\s+/', trim((string) $name));
        $first = $words[0] ?? 'U';
        $last = count($words) > 1 ? $words[count($words) - 1] : ($words[0] ?? 'U');
        $initials = strtoupper(substr($first, 0, 1).substr($last, 0, 1));

        return $initials !== '' ? $initials : 'U';
    };
    $employeeFirstName = trim((string) ($employee?->first_name ?? ''));
    $employeeLastName = trim((string) ($employee?->last_name ?? ''));
    $userInitials = ($employeeFirstName !== '' && $employeeLastName !== '')
        ? strtoupper(substr($employeeFirstName, 0, 1).substr($employeeLastName, 0, 1))
        : $initialsFromName(trim($employeeFirstName.' '.$employeeLastName) ?: $userName);
    $userAvatarUrl = null;

    if (isset($employee) && $employee?->photo && Storage::disk('local')->exists('public/employees/'.$employee->id.'/'.$employee->photo)) {
        $userAvatarUrl = Storage::disk('local')->url('public/employees/'.$employee->id.'/'.$employee->photo);
    }
@endphp

<header class="ss-header" data-global-header>
    <div class="ss-header__inner">
        <a href="{{ route('dashboard') }}" class="ss-brand" aria-label="London Churchill College dashboard">
            <img src="{{ $siteLogo }}" alt="London Churchill College">
        </a>
        <div class="ss-header__divider"></div>
        <nav class="ss-mainnav" aria-label="Primary">
            <a href="{{ route('dashboard') }}" class="ss-mainnav__link">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('courses') }}" class="ss-mainnav__link">
                <i data-lucide="book-open"></i>
                <span>Courses</span>
            </a>
            <div class="ss-dropdown" data-ss-dropdown>
                <button type="button" class="ss-mainnav__link ss-mainnav__button" data-ss-dropdown-toggle aria-expanded="false">
                    <i data-lucide="users"></i>
                    <span>Students</span>
                    <i data-lucide="chevron-down" class="ss-chevron"></i>
                </button>
                <div class="ss-dropdown__menu ss-dropdown__menu--students" data-ss-dropdown-menu>
                    <a href="{{ route('admission') }}" class="ss-dropdown__item">
                        <span><i data-lucide="user-plus"></i></span>
                        Admission
                    </a>
                    <a href="{{ route('student') }}" class="ss-dropdown__item">
                        <span><i data-lucide="graduation-cap"></i></span>
                        Student Records
                    </a>
                    <a href="{{ route('agent.management') }}" class="ss-dropdown__item">
                        <span><i data-lucide="briefcase-business"></i></span>
                        Agent Management
                    </a>
                </div>
            </div>
            <a href="{{ route('site.setting') }}" class="ss-mainnav__link ss-mainnav__link--active">
                <i data-lucide="settings"></i>
                <span>Settings</span>
            </a>
        </nav>
        <div class="ss-header__spacer"></div>
        @if ($canShowGlobalSearch && Route::has('global.search'))
            <div class="ss-search" data-global-search data-search-url="{{ route('global.search') }}" data-search-applicants="{{ $canSearchApplicants ? '1' : '0' }}" data-search-students="{{ $canSearchStudents ? '1' : '0' }}" data-search-employees="{{ $canSearchEmployees ? '1' : '0' }}">
                <label class="ss-search__box">
                    <i data-lucide="search"></i>
                    <input type="search" autocomplete="off" placeholder="{{ $searchPlaceholder }}" data-global-search-input>
                </label>
                <div class="ss-search__results" data-global-search-results></div>
            </div>
        @endif
        <div class="ss-dropdown ss-user" data-ss-dropdown>
            <button type="button" class="ss-user__toggle" data-ss-dropdown-toggle aria-expanded="false">
                <span class="ss-user__who">
                    <span>{{ $userName }}</span>
                    <small>{{ $userRole }}</small>
                </span>
                <span class="ss-avatar">
                    @if($userAvatarUrl)
                        <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}">
                    @else
                        {{ $userInitials }}
                    @endif
                </span>
                <i data-lucide="chevron-down" class="ss-chevron"></i>
            </button>
            <div class="ss-dropdown__menu ss-dropdown__menu--user" data-ss-dropdown-menu>
                <div class="ss-user-card">
                    <span class="ss-avatar ss-avatar--large">
                        @if($userAvatarUrl)
                            <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}">
                        @else
                            {{ $userInitials }}
                        @endif
                    </span>
                    <span>
                        <strong>{{ $userName }}</strong>
                        <small>{{ $userEmail }}</small>
                        <em>{{ $userRole }}</em>
                    </span>
                </div>
                <div class="ss-user-menu">
                    <a href="{{ $profileUrl }}">
                        <span><i data-lucide="user"></i></span>
                        <span>
                            <strong>Profile</strong>
                            <small>View & edit your details</small>
                        </span>
                        <i data-lucide="chevron-right"></i>
                    </a>
                    <a href="{{ $logoutUrl }}" class="ss-user-menu__danger">
                        <span><i data-lucide="log-out"></i></span>
                        <span>
                            <strong>Logout</strong>
                            <small>End this session</small>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
