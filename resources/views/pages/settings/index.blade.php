@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
    @php
        $siteLogo = (isset($opt['site_logo']) && !empty($opt['site_logo']) && Storage::disk('local')->exists('public/'.$opt['site_logo']))
            ? Storage::disk('local')->url('public/'.$opt['site_logo'])
            : asset('build/assets/images/logo_white.svg');
        $siteDarkLogo = (isset($opt['site_dark_logo']) && !empty($opt['site_dark_logo']) && Storage::disk('local')->exists('public/'.$opt['site_dark_logo']))
            ? Storage::disk('local')->url('public/'.$opt['site_dark_logo'])
            : asset('build/assets/images/lcc-header-sample-logo.png');
        $siteFavicon = (isset($opt['site_favicon']) && !empty($opt['site_favicon']) && Storage::disk('local')->exists('public/'.$opt['site_favicon']))
            ? Storage::disk('local')->url('public/'.$opt['site_favicon'])
            : asset('build/assets/images/placeholders/200x200.jpg');
        $authUser = auth()->user();
        $isStaffGuard = Auth::check() && !Auth::guard('agent')->check() && !Auth::guard('applicant')->check() && !Auth::guard('student')->check();
        $staffPrivileges = $isStaffGuard ? Auth::user()->priv() : [];
        $canSearchStudents = $isStaffGuard && !empty($staffPrivileges['live']) && $staffPrivileges['live'] != '0';
        $canSearchEmployees = $isStaffGuard && !empty($staffPrivileges['hr_porta']) && $staffPrivileges['hr_porta'] != '0';
        $canShowGlobalSearch = $canSearchStudents || $canSearchEmployees;
        $searchPlaceholder = $canSearchStudents && $canSearchEmployees
            ? 'Search Student, Staff...'
            : ($canSearchStudents ? 'Search Student...' : 'Search Staff...');
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
        $companyFields = [
            ['id' => 'company_name', 'label' => 'Company Name', 'placeholder' => 'Company Name', 'icon' => 'building-2'],
            ['id' => 'company_address', 'label' => 'Address', 'placeholder' => 'Address', 'icon' => 'map-pin'],
            ['id' => 'company_phone', 'label' => 'Phone', 'placeholder' => 'Phone', 'icon' => 'phone'],
            ['id' => 'company_email', 'label' => 'Email Address', 'placeholder' => 'Email Address', 'icon' => 'mail'],
            ['id' => 'company_term_condition_url', 'label' => 'Terms and Condition URL', 'placeholder' => 'Terms and condition URL', 'icon' => 'link'],
            ['id' => 'company_e_learning_url', 'label' => 'E-Learning URL', 'placeholder' => 'E-Learning URL', 'icon' => 'link'],
            ['id' => 'company_doc_req_url', 'label' => 'Document Request URL', 'placeholder' => 'Document Request URL', 'icon' => 'link'],
            ['id' => 'company_hcuci', 'label' => 'HESA College Unique Code Identifier', 'placeholder' => 'Unique Code Identifier', 'icon' => 'hash'],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page">
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
                    <div class="ss-search" data-global-search data-search-url="{{ route('global.search') }}" data-search-students="{{ $canSearchStudents ? '1' : '0' }}" data-search-employees="{{ $canSearchEmployees ? '1' : '0' }}">
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

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Site Settings</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="settings"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Configure your institution's global preferences and branding.</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Dashboard
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @if(isset(auth()->user()->priv()['site_settings']) && auth()->user()->priv()['site_settings'] == 1)
                        <form method="post" action="#" id="companySettingsForm" enctype="multipart/form-data" class="ss-company-form">
                            <input type="hidden" name="category" value="SITE_SETTINGS">

                            <div class="ss-form-card">
                                <div class="ss-card-heading">
                                    <span></span>
                                    <div>
                                        <h2>Update Company Information</h2>
                                        <p>These details appear across student-facing documents and communications.</p>
                                    </div>
                                </div>

                                <div class="ss-field-grid">
                                    @foreach($companyFields as $field)
                                        <div class="ss-field">
                                            <label for="{{ $field['id'] }}">{{ $field['label'] }}</label>
                                            <div class="ss-input-wrap">
                                                <i data-lucide="{{ $field['icon'] }}"></i>
                                                <input
                                                    id="{{ $field['id'] }}"
                                                    type="text"
                                                    name="{{ $field['id'] }}"
                                                    class="ss-input"
                                                    placeholder="{{ $field['placeholder'] }}"
                                                    value="{{ old($field['id'], $opt[$field['id']] ?? '') }}"
                                                >
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="ss-field-stack">
                                    <div class="ss-field">
                                        <label for="company_registration">Company Registration Details</label>
                                        <textarea rows="3" id="company_registration" name="company_registration" class="ss-textarea" placeholder="Company Reg. No. 5995926, Companies House, England and Wales">{{ old('company_registration', $opt['company_registration'] ?? '') }}</textarea>
                                    </div>
                                    <div class="ss-field">
                                        <label for="company_right">Copyright Info</label>
                                        <textarea rows="3" id="company_right" name="company_right" class="ss-textarea" placeholder="Right reserved by LCC @ 2023">{{ old('company_right', $opt['company_right'] ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="ss-form-actions">
                                    <button type="reset" class="ss-btn ss-btn--danger-soft">
                                        <i data-lucide="x"></i>
                                        Cancel
                                    </button>
                                    <button type="submit" id="updateCINF" class="ss-btn ss-btn--primary">
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                                            <g fill="none" fill-rule="evenodd">
                                                <g transform="translate(1 1)" stroke-width="4">
                                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                    </path>
                                                </g>
                                            </g>
                                        </svg>
                                        <i data-lucide="save"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </div>

                            <aside class="ss-branding-card">
                                <div class="ss-branding-card__heading">
                                    <h3>Branding</h3>
                                    <p>Logos & favicon</p>
                                </div>

                                <div class="ss-upload">
                                    <span class="ss-upload__label">Company Logo</span>
                                    <div class="ss-upload__preview ss-upload__preview--dark">
                                        <img class="siteLogoImg" id="siteLogoImg" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" alt="Site Logo" src="{{ $siteLogo }}">
                                    </div>
                                    <label class="ss-upload__button" for="siteLogoUpload">
                                        <i data-lucide="upload"></i>
                                        Select Logo
                                    </label>
                                    <span class="ss-upload__filename" data-ss-upload-name>No file selected</span>
                                    <input type="file" accept=".jpg, .jpeg, .png, .gif, .svg" id="siteLogoUpload" name="site_logo">
                                </div>

                                <div class="ss-branding-card__rule"></div>

                                <div class="ss-upload">
                                    <span class="ss-upload__label">Dark Logo</span>
                                    <div class="ss-upload__preview ss-upload__preview--light">
                                        <img class="siteDarkLogoImg" id="siteDarkLogoImg" data-placeholder="{{ asset('build/assets/images/lcc-header-sample-logo.png') }}" alt="Dark Logo" src="{{ $siteDarkLogo }}">
                                    </div>
                                    <label class="ss-upload__button" for="siteDarkLogoUpload">
                                        <i data-lucide="upload"></i>
                                        Select Dark Logo
                                    </label>
                                    <span class="ss-upload__filename" data-ss-upload-name>No file selected</span>
                                    <input type="file" accept=".jpg, .jpeg, .png, .gif, .svg" id="siteDarkLogoUpload" name="site_dark_logo">
                                </div>

                                <div class="ss-branding-card__rule"></div>

                                <div class="ss-upload">
                                    <span class="ss-upload__label">Favicon</span>
                                    <div class="ss-upload__preview ss-upload__preview--favicon">
                                        <img alt="Site Favicon" id="siteFaviconImg" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ $siteFavicon }}">
                                    </div>
                                    <label class="ss-upload__button" for="siteFaviconUpload">
                                        <i data-lucide="upload"></i>
                                        Select Favicon
                                    </label>
                                    <span class="ss-upload__filename" data-ss-upload-name>No file selected</span>
                                    <input accept=".ico, .png, .svg" type="file" name="site_favicon" id="siteFaviconUpload">
                                </div>
                            </aside>
                        </form>
                    @else
                        <div class="ss-empty-state" role="alert">
                            <span><i data-lucide="alert-triangle"></i></span>
                            <div>
                                <h2>Permission Required</h2>
                                <p>You do not have enough permission to view this page's content. Please navigate to the menus on the left.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>

        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                            <div class="successModalTitle"></div>
                            <p class="successModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
