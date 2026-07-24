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
            <span>Course Parameters</span>
            <i data-lucide="chevron-right"></i>
            <span>Assessment Type</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="clipboard-check"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage term types, assessments, awarding bodies, academic years and qualifications.</p>
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
                    @if(isset(auth()->user()->priv()['course_parameters']) && auth()->user()->priv()['course_parameters'] == 1)
                        <div class="ss-table-card ss-assessmenttype-card">
                            <div class="ss-table-card__header">
                                <h2>Assessment Type List</h2>
                                <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Add New Assessment Type
                                </button>
                            </div>

                            <div class="ss-table-tools">
                                <form id="tabulatorFilterForm" class="ss-table-filter">
                                    <div class="ss-filter-field">
                                        <span>Query</span>
                                        <label class="ss-filter-input" for="query">
                                            <i data-lucide="search"></i>
                                            <input id="query" name="query" type="text" placeholder="Search...">
                                        </label>
                                    </div>
                                    <div class="ss-filter-field">
                                        <span>Status</span>
                                        <label class="ss-filter-select" for="status">
                                            <select id="status" name="status">
                                                <option value="1">Active</option>
                                                <option value="2">Archived</option>
                                            </select>
                                            <i data-lucide="chevron-down"></i>
                                        </label>
                                    </div>
                                    <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                    <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                                </form>

                                <div class="ss-table-actions">
                                    <button id="tabulator-print" type="button" class="ss-btn ss-btn--light ss-btn--tool">
                                        <i data-lucide="printer"></i>
                                        Print
                                    </button>
                                    <div class="dropdown ss-export-dropdown">
                                        <button type="button" class="dropdown-toggle ss-btn ss-btn--light ss-btn--tool" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="download"></i>
                                            Export
                                            <i data-lucide="chevron-down"></i>
                                        </button>
                                        <div class="dropdown-menu ss-export-menu">
                                            <ul class="dropdown-content">
                                                <li>
                                                    <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-text"></i>
                                                        Export CSV
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-spreadsheet"></i>
                                                        Export XLSX
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="ss-tabulator-wrap">
                                <div id="assessmentTypeTableId" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>
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

        <div id="addModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="addForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add New Assessment Type</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="add_name">Assessment Type <span>*</span></label>
                                <input id="add_name" type="text" name="name" class="ss-modal-input name" placeholder="Assignment">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="add_code">Code <span>*</span></label>
                                <input id="add_code" type="text" name="code" class="ss-modal-input code" placeholder="A1">
                                <div class="acc__input-error error-code"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="add_is_active">Active Status</label>
                                <label class="ss-status-toggle" for="add_is_active">
                                    <input id="add_is_active" name="is_active" value="1" type="checkbox" autocomplete="off">
                                    <span class="ss-status-toggle__control">
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                    </span>
                                    <span class="ss-status-toggle__copy">
                                        <strong>Inactive</strong>
                                        <small>Not available for new assessment setup</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="save" class="ss-btn ss-btn--primary">
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
                                <i data-lucide="check"></i>
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="editModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="editForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Assessment Type</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="edit_name">Assessment Type <span>*</span></label>
                                <input id="edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Assignment">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_code">Code <span>*</span></label>
                                <input id="edit_code" type="text" name="code" class="ss-modal-input code" placeholder="A1">
                                <div class="acc__input-error error-code"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_is_active">Active Status</label>
                                <label class="ss-status-toggle" for="edit_is_active">
                                    <input id="edit_is_active" name="is_active" value="1" type="checkbox" autocomplete="off">
                                    <span class="ss-status-toggle__control">
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                    </span>
                                    <span class="ss-status-toggle__copy">
                                        <strong>Inactive</strong>
                                        <small>Not available for new assessment setup</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="update" class="ss-btn ss-btn--primary">
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
                                <i data-lucide="check"></i>
                                Update
                            </button>
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>

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

        <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-confirm-modal__dialog">
                <div class="modal-content ss-confirm-modal">
                    <div class="ss-confirm-modal__hero">
                        <span><i data-lucide="alert-triangle"></i></span>
                        <h2 class="confModTitle">Are you sure?</h2>
                    </div>
                    <div class="ss-confirm-modal__body">
                        <p class="confModDesc"></p>
                    </div>
                    <div class="ss-confirm-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/assessmenttype.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
