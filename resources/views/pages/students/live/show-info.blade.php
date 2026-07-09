@php
    $filledProfileIcon = static function (string $icon): string {
        $svgMap = [
            'alert' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2 1.9 6v6.35c0 6.02 4.28 9.95 10.1 11.65 5.82-1.7 10.1-5.63 10.1-11.65V6L12 2Zm0 5.1a1.15 1.15 0 0 1 1.15 1.15v5.3a1.15 1.15 0 0 1-2.3 0v-5.3A1.15 1.15 0 0 1 12 7.1Zm0 10.1a1.4 1.4 0 1 1 0-2.8 1.4 1.4 0 0 1 0 2.8Z"/></svg>',
            'flag' => '<svg viewBox="0 0 256 256" aria-hidden="true" focusable="false"><g transform="translate(0,256) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M150 1280 l0 -1280 80 0 80 0 0 452 c0 249 0 460 0 469 0 40 241 117 396 126 180 11 317 -23 534 -132 231 -115 375 -155 567 -155 118 0 262 25 367 65 101 38 223 102 230 121 9 24 7 1502 -2 1508 -5 3 -39 -14 -77 -36 -256 -149 -521 -191 -765 -119 -89 26 -101 31 -273 116 -172 84 -279 118 -425 135 -155 18 -358 -10 -497 -70 -25 -11 -49 -20 -51 -20 -2 0 -4 23 -4 50 l0 50 -80 0 -80 0 0 -1280z"/></g></svg>',
            'money' => '<svg viewBox="0 0 256 256" aria-hidden="true" focusable="false"><g transform="translate(0,256) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M1160 2489 c-189 -19 -382 -87 -546 -194 -116 -75 -274 -233 -349 -349 -265 -408 -265 -924 0 -1332 75 -117 233 -274 350 -350 406 -264 924 -264 1331 1 116 75 274 233 349 349 265 407 265 925 0 1332 -105 163 -303 338 -480 427 -183 91 -444 138 -655 116z m392 -521 c105 -49 178 -147 200 -269 8 -47 7 -63 -7 -92 -20 -43 -73 -71 -115 -62 -37 8 -77 49 -84 86 -3 16 -9 42 -12 59 -6 33 -62 86 -99 96 -68 17 -144 -31 -164 -102 -6 -21 -11 -96 -11 -166 l0 -128 130 0 c158 0 193 -11 217 -67 13 -33 14 -43 2 -78 -22 -63 -56 -75 -215 -75 l-132 0 -5 -117 c-2 -65 -8 -128 -12 -140 l-7 -23 224 0 c211 0 227 -1 251 -20 38 -30 52 -80 33 -123 -27 -67 -30 -67 -466 -67 -435 0 -439 1 -466 65 -29 70 14 136 98 149 101 15 138 67 138 195 l0 81 -76 0 c-92 0 -136 18 -158 65 -20 41 -20 49 0 90 22 47 66 65 158 65 l76 0 0 153 c0 173 11 226 63 303 39 58 113 116 176 138 72 25 192 17 263 -16z"/></g></svg>',
            'sun' => '<svg viewBox="0 0 256 256" aria-hidden="true" focusable="false"><g transform="translate(0,256) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M1224 2544 c-57 -28 -69 -63 -69 -209 0 -111 3 -135 20 -162 20 -34 68 -63 105 -63 37 0 85 29 105 63 17 27 20 51 20 162 0 111 -3 135 -20 162 -34 57 -101 77 -161 47z"/><path d="M415 2211 c-11 -5 -31 -21 -45 -36 -20 -22 -25 -37 -25 -81 l0 -55 100 -98 c79 -78 108 -101 137 -106 82 -15 158 61 143 143 -5 29 -28 58 -104 135 -90 92 -101 99 -142 103 -24 2 -53 0 -64 -5z"/><path d="M2045 2211 c-11 -5 -61 -51 -112 -103 -70 -72 -93 -102 -98 -130 -15 -82 61 -158 143 -143 29 5 58 28 137 106 l100 98 0 55 c0 49 -4 59 -31 87 -34 33 -99 48 -139 30z"/><path d="M1140 1904 c-234 -50 -436 -254 -485 -489 -71 -338 136 -670 471 -755 397 -102 794 208 794 620 0 406 -380 710 -780 624z"/><path d="M105 1401 c-63 -16 -105 -65 -105 -121 0 -37 29 -85 63 -105 27 -17 51 -20 162 -20 110 0 135 3 161 19 84 51 85 162 0 212 -24 14 -55 18 -141 21 -60 1 -123 -1 -140 -6z"/><path d="M2215 1401 c-119 -31 -143 -165 -41 -227 26 -16 51 -19 161 -19 111 0 135 3 162 20 34 20 63 68 63 105 0 37 -29 85 -62 105 -26 15 -56 19 -143 22 -60 1 -123 -1 -140 -6z"/><path d="M555 716 c-16 -8 -70 -55 -120 -105 l-90 -91 0 -55 c0 -48 4 -58 33 -87 29 -29 39 -33 88 -33 l55 0 98 100 c110 111 125 142 98 208 -26 62 -102 91 -162 63z"/><path d="M1900 712 c-46 -23 -75 -79 -65 -130 5 -29 28 -58 106 -137 l98 -100 55 0 c49 0 59 4 88 33 29 29 33 39 33 87 l0 55 -90 91 c-118 119 -155 136 -225 101z"/><path d="M1224 434 c-57 -28 -69 -63 -69 -209 0 -110 3 -135 20 -162 20 -34 68 -63 105 -63 37 0 85 29 105 63 17 27 20 52 20 162 0 112 -3 135 -20 162 -34 57 -101 77 -161 47z"/></g></svg>',
            'moon' => '<svg viewBox="0 0 256 256" aria-hidden="true" focusable="false"><g transform="translate(0,256) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M890 2531 c-178 -57 -362 -173 -510 -321 -191 -191 -304 -402 -357 -668 -24 -120 -24 -366 0 -482 115 -545 524 -945 1062 -1036 131 -22 382 -15 500 15 255 63 444 169 625 351 155 156 253 314 320 521 36 110 37 133 5 164 -39 40 -79 34 -160 -26 -85 -63 -223 -130 -330 -161 -69 -20 -102 -23 -245 -22 -197 0 -270 16 -425 93 -259 129 -438 352 -511 641 -29 113 -26 344 5 460 28 105 94 247 149 320 55 71 59 109 16 148 -30 27 -68 28 -144 3z"/></g></svg>',
            'accessibility' => '<svg viewBox="0 0 256 256" aria-hidden="true" focusable="false"><g transform="translate(0,256) scale(0.1,-0.1)" fill="currentColor" stroke="none"><path d="M1015 2463 c-138 -50 -225 -185 -212 -328 11 -116 80 -215 184 -262 l53 -24 0 -380 0 -380 25 -24 24 -25 274 0 274 0 224 -337 c123 -186 235 -348 248 -360 13 -13 36 -23 50 -23 26 0 229 127 284 178 46 43 34 111 -23 131 -39 13 -47 10 -151 -60 l-87 -58 -223 335 c-123 185 -231 340 -240 345 -10 5 -130 9 -268 9 l-251 0 0 160 0 160 255 0 256 0 24 25 c33 32 33 78 0 110 l-24 25 -256 0 -255 0 0 84 0 85 53 24 c69 31 123 86 156 156 36 78 37 193 1 266 -32 64 -98 131 -158 157 -65 29 -172 34 -237 11z"/><path d="M700 1801 c-297 -98 -511 -323 -593 -626 -29 -107 -30 -316 -3 -419 88 -332 326 -568 656 -652 109 -28 306 -26 415 3 159 43 294 120 406 232 63 63 169 211 169 235 -1 6 -44 76 -98 156 l-97 145 -250 5 c-218 4 -255 7 -288 24 -49 24 -110 95 -125 144 -8 27 -12 149 -12 384 l0 345 -23 21 c-29 27 -80 28 -157 3z"/></g></svg>',
        ];

        return $svgMap[$icon] ?? '';
    };

    $flagIndicatorHtml = '';
    if(isset($student->flag_html) && !empty($student->flag_html)):
        $flagIndicatorHtml = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is', $filledProfileIcon('flag'), $student->flag_html, 1) ?? $student->flag_html;
    endif;

    if(Auth::guard('agent')->check()):
        $profileUserName = auth('agent')->user()->email;
        $profileUserEmail = auth('agent')->user()->email;
        $profileUserRole = 'Agent User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = null;
        $logoutUrl = Route::has('agent.logout') ? route('agent.logout') : url('/agent/logout');
    elseif(Auth::guard('applicant')->check()):
        $profileUserName = auth('applicant')->user()->email;
        $profileUserEmail = auth('applicant')->user()->email;
        $profileUserRole = 'Applicant User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = null;
        $logoutUrl = Route::has('applicant.logout') ? route('applicant.logout') : url('/applicant/logout');
    elseif(Auth::guard('student')->check()):
        $profileUserName = auth('student')->user()->email;
        $profileUserEmail = auth('student')->user()->email;
        $profileUserRole = 'Student User';
        $profileUserInitials = strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = Route::has('students.dashboard.profile') ? route('students.dashboard.profile') : null;
        $logoutUrl = Route::has('students.logout') ? route('students.logout') : url('/students/logout');
    else:
        $employee = auth()->user()->employee ?? null;
        $profileUserName = $employee ? trim(($employee->title->name ?? '').' '.$employee->first_name.' '.$employee->last_name) : (auth()->user()->name ?? auth()->user()->email);
        $profileUserEmail = auth()->user()->email ?? $profileUserName;
        $profileUserRole = '';
        $profileUserInitials = $employee ? strtoupper(substr($employee->first_name ?? '', 0, 1).substr($employee->last_name ?? '', 0, 1)) : strtoupper(substr($profileUserName, 0, 2));
        $profileUrl = Route::has('user.account') ? route('user.account') : null;
        $logoutUrl = Route::has('logout') ? route('logout') : url('/logout');
    endif;
    $studentHeaderLogo = cache()->get('site_logo');
    if(empty($studentHeaderLogo)):
        $studentHeaderLogo = App\Models\Option::where('category', 'SITE_SETTINGS')->where('name', 'site_logo')->value('value');
    endif;
    $studentHeaderLogoUrl = (!empty($studentHeaderLogo) && Storage::disk('local')->exists('public/'.$studentHeaderLogo))
        ? Storage::disk('local')->url('public/'.$studentHeaderLogo)
        : null;
    $studentFullName = trim(($student->title->name ?? '').' '.$student->first_name.' '.$student->last_name);
    $studentInitials = strtoupper(substr($student->first_name ?? '', 0, 1).substr($student->last_name ?? '', 0, 1));
    $studentInitials = $studentInitials != '' ? $studentInitials : 'ST';
    $courseName = isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '';
    $semesterName = isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '';
    $studyMode = isset($student->crel->creation->available->type) ? $student->crel->creation->available->type : '';
    $termAddress = '';
    if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0):
        $addressParts = [];
        if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1)): $addressParts[] = \Illuminate\Support\Str::lower($student->contact->termaddress->address_line_1); endif;
        if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2)): $addressParts[] = \Illuminate\Support\Str::lower($student->contact->termaddress->address_line_2); endif;
        if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city)): $addressParts[] = \Illuminate\Support\Str::lower($student->contact->termaddress->city); endif;
        if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state)): $addressParts[] = \Illuminate\Support\Str::lower($student->contact->termaddress->state); endif;
        if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code)): $addressParts[] = \Illuminate\Support\Str::upper($student->contact->termaddress->post_code); endif;
        if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country)): $addressParts[] = \Illuminate\Support\Str::lower($student->contact->termaddress->country); endif;
        $termAddress = implode(', ', $addressParts);
    endif;

    $studentIndicators = '<div class="student-profile-indicators">';
        if(isset($student->multi_agreement_status) && $student->multi_agreement_status > 1):
            $studentIndicators .= '<span class="student-profile-icon student-profile-icon--filled text-warning">'.$filledProfileIcon('alert').'</span>';
        endif;
        $studentIndicators .= $flagIndicatorHtml;
        if(isset($student->due) && $student->due > 1):
            $studentIndicators .= '<span class="student-profile-icon student-profile-icon--filled '.($student->due == 2 ? 'text-success' : ($student->due == 3 ? 'text-warning' : 'text-danger')).'">'.$filledProfileIcon('money').'</span>';
        endif;
        $studentIndicators .= '<span class="student-profile-icon student-profile-icon--filled '.(isset($student->course->full_time) && $student->course->full_time == 1 ? 'text-warning' : 'text-pending').'">';
            $studentIndicators .= $filledProfileIcon(isset($student->course->full_time) && $student->course->full_time == 1 ? 'moon' : 'sun');
        $studentIndicators .= '</span>';
        if(isset($student->other->disability_status) && $student->other->disability_status == 1):
            $studentIndicators .= '<span class="student-profile-icon student-profile-icon--filled text-danger">'.$filledProfileIcon('accessibility').'</span>';
        endif;
    $studentIndicators .= '</div>';

    $studentNavMeta = [
        'admission' => [
            'description' => 'Applicants & enrolment pipeline',
            'icon' => 'user-plus',
            'tone' => 'teal',
        ],
        'student' => [
            'badge' => 'CURRENT',
            'description' => 'Active, enrolled students',
            'icon' => 'clock',
            'tone' => 'green',
        ],
        'agent_management' => [
            'description' => 'Recruitment partners & referrals',
            'icon' => 'landmark',
            'tone' => 'gold',
        ],
    ];
@endphp

<div class="student-profile-header no-print">
    <div class="student-profile-appbar">
        <a href="{{ url('/') }}" class="student-profile-brand {{ $studentHeaderLogoUrl ? 'student-profile-brand--logo' : 'student-profile-brand--fallback' }}">
            @if($studentHeaderLogoUrl)
                <img src="{{ $studentHeaderLogoUrl }}" alt="London Churchill College">
            @else
                <span>LCC</span>
            @endif
        </a>
        <nav class="student-profile-appnav" aria-label="Main navigation">
            @if(isset($top_menu))
                @foreach ($top_menu as $menuKey => $menu)
                    @php
                        $hasSubMenu = isset($menu['sub_menu']);
                        $isActiveMenu = ($first_level_active_index == $menuKey || ($menuKey == 'students' && str_starts_with(Route::currentRouteName(), 'student')));
                    @endphp
                    <div class="student-profile-appnav-item {{ $hasSubMenu ? 'has-submenu' : '' }}">
                        <a href="{{ isset($menu['route_name']) ? route($menu['route_name'], $menu['params']) : 'javascript:;' }}" class="student-profile-appnav-link {{ $isActiveMenu ? 'active' : '' }}">
                            {{ $menu['title'] }}
                            @if ($hasSubMenu)
                                <i data-lucide="chevron-down" class="student-profile-appnav-chevron w-3 h-3"></i>
                            @endif
                        </a>
                        @if ($hasSubMenu)
                            <div class="student-profile-appnav-submenu">
                                <span class="student-profile-appnav-submenu-arrow"></span>
                                <div class="student-profile-appnav-submenu-inner">
                                    @foreach ($menu['sub_menu'] as $subMenuKey => $subMenu)
                                        @php
                                            $meta = $studentNavMeta[$subMenuKey] ?? ['description' => '', 'icon' => 'circle', 'tone' => 'teal'];
                                            $isActiveSubMenu = isset($subMenu['route_name']) && ($subMenu['route_name'] == Route::currentRouteName() || ($subMenuKey == 'student' && str_starts_with(Route::currentRouteName(), 'student')));
                                        @endphp
                                        <a href="{{ isset($subMenu['route_name']) ? route($subMenu['route_name'], $subMenu['params']) : 'javascript:;' }}" class="student-profile-appnav-submenu-link {{ $isActiveSubMenu ? 'active' : '' }}">
                                            <span class="student-profile-appnav-submenu-icon {{ $meta['tone'] }}">
                                                <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
                                            </span>
                                            <span class="student-profile-appnav-submenu-copy">
                                                <span class="student-profile-appnav-submenu-title">
                                                    {{ $subMenu['title'] }}
                                                    @if(isset($meta['badge']))
                                                        <span class="student-profile-appnav-submenu-badge">{{ $meta['badge'] }}</span>
                                                    @endif
                                                </span>
                                                @if(!empty($meta['description']))
                                                    <span class="student-profile-appnav-submenu-description">{{ $meta['description'] }}</span>
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </nav>
        <div class="student-profile-user student-profile-user-dropdown">
            <button type="button" class="student-profile-user-trigger" aria-label="Open user menu">
                <span class="student-profile-user-copy">
                    <span class="student-profile-user-name">{{ $profileUserName }}</span>
                    @if(!empty($profileUserRole))
                        <span class="student-profile-user-role">{{ $profileUserRole }}</span>
                    @endif
                </span>
                <span class="student-profile-user-avatar">{{ $profileUserInitials }}</span>
                <i data-lucide="chevron-down" class="student-profile-user-chevron w-3 h-3"></i>
            </button>
            <div class="student-profile-user-menu">
                <span class="student-profile-user-menu-arrow"></span>
                <div class="student-profile-user-menu-card">
                    <div class="student-profile-user-menu-head">
                        <span class="student-profile-user-menu-avatar">{{ $profileUserInitials }}</span>
                        <span class="student-profile-user-menu-copy">
                            <span class="student-profile-user-menu-name">{{ $profileUserName }}</span>
                            <span class="student-profile-user-menu-email">{{ $profileUserEmail }}</span>
                        </span>
                    </div>
                    @if($profileUrl)
                        <a href="{{ $profileUrl }}" class="student-profile-user-menu-link">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span>Profile</span>
                        </a>
                    @endif
                    <a href="{{ $logoutUrl }}" class="student-profile-user-menu-link">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Logout</span>
                    </a>
                </div>
                <div class="student-profile-user-menu-accent"></div>
            </div>
        </div>
    </div>

    <div class="student-profile-breadcrumb">
        <a href="{{ route('staff.dashboard') }}">Dashboard</a>
        <span class="student-profile-crumb-separator">/</span>
        <a href="{{ route('student') }}">Live Student</a>
        <span class="student-profile-crumb-separator">/</span>
        <strong>Student Details</strong>
    </div>

    <div class="student-profile-hero intro-y">
        <div class="student-profile-hero-main">
            <div class="student-profile-avatar">
                @if(isset($student->photo_url) && !empty($student->photo_url))
                    <img alt="{{ $studentFullName }}" src="{{ $student->photo_url }}">
                @else
                    <span>{{ $studentInitials }}</span>
                @endif
                <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" type="button" class="student-profile-photo-btn">
                    <i class="w-4 h-4" data-lucide="camera"></i>
                </button>
            </div>
            <div class="student-profile-summary">
                <div class="student-profile-meta-row">
                    <span class="student-profile-reg">{{ !empty($student->registration_no) ? $student->registration_no : $student->application_no }}</span>
                    <button
                        type="button"
                        class="btn btn-success text-white tooltip student-profile-status-badge"
                        data-tooltip-content="#student-status-tooltip" data-theme="light" data-placement="top">
                        {{ $student->status->name ?? '--' }}
                    </button>
                    @if(!empty($studyMode))
                        <span class="student-profile-country">{{ $studyMode }}</span>
                    @endif
                </div>
                <div class="tooltip-content">
                    <div id="student-status-tooltip">
                        <div class="text-sm font-medium">{{ $student->termStatusLatest->term->name ?? '--' }}</div>
                        <div class="text-xs text-slate-500">{{ $student->termStatusLatest->status_change_reason ?? '--' }}</div>
                        <div class="text-xs font-medium">Changed By</div>
                        <div class="text-xs text-slate-500">{{ isset($student->termStatusLatest->updatedBy->employee) ? $student->termStatusLatest->updatedBy->employee->full_name : (isset($student->termStatusLatest->user) ? $student->termStatusLatest->user->employee->full_name : "--") }}</div>
                        <div class="text-xs text-slate-500">{{ $student->termStatusLatest->status_change_date ?? '--' }}</div>
                    </div>
                </div>
                <h1>{{ $studentFullName }}
                    @if(isset($student->hesa_status) && $student->hesa_status == 1)
                        <span class="student-profile-verified tooltip" title="Added To Hesa"><i data-lucide="check" class="w-3 h-3"></i></span>
                    @endif
                </h1>
                <div class="student-profile-course">
                    @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) <span class="bg-danger text-white inline pl-1 pr-1"> @endif
                        {{ $courseName }}{{ !empty($semesterName) ? ' - '.$semesterName : '' }}
                    @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) </span> @endif
                    @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0)
                        <a href="{{ route('student.set.default.course', $student->id) }}" class="inline ml-1 bg-success px-1 text-white">Reset</a>
                    @endif
                </div>
                <div class="student-profile-contact-line">
                    @if(isset($student->contact->institutional_email) && !empty($student->contact->institutional_email))
                        <span class="student-profile-email student-profile-contact-trigger" data-tw-toggle="modal" data-tw-target="#sendEmailModal" role="button" title="Send email"><i data-lucide="mail" class="w-3.5 h-3.5"></i>{{ $student->contact->institutional_email }}</span>
                    @elseif(isset($student->users->email))
                        <span class="student-profile-email student-profile-contact-trigger" data-tw-toggle="modal" data-tw-target="#sendEmailModal" role="button" title="Send email"><i data-lucide="mail" class="w-3.5 h-3.5"></i>{{ $student->users->email }}</span>
                    @endif
                    @if(isset($student->contact->mobile) && !empty($student->contact->mobile))
                        <span class="student-profile-contact-trigger" data-tw-toggle="modal" data-tw-target="#smsSMSModal" role="button" title="Send SMS"><i data-lucide="phone" class="w-3.5 h-3.5"></i>{{ $student->contact->mobile }}</span>
                    @endif
                    @if(!empty($termAddress))
                        <span class="student-profile-contactline-address"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i>{{ $termAddress }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="student-profile-actions">
            {!! $studentIndicators !!}
            @if(isset(auth()->user()->priv()['edit_student_print']) && auth()->user()->priv()['edit_student_print'] == 1 && isset($student->applicant->id) && !empty($student->applicant->id))
                <a href="{{ route('studentapplication.print',$student->id) }}" data-id="{{ $student->id }}" class="btn btn-outline-pending flex-1 sm:flex-none student-profile-print-btn">
                    Print PDF
                </a>
            @endif
            @if(isset(auth()->user()->priv()['login_as_student']) && auth()->user()->priv()['login_as_student'] == 1)
                <a target="__blank" href="{{ route('impersonate', ['id' =>$student->student_user_id,'guardName' =>'student']) }}" class="btn btn-warning min-w-max student-profile-login-btn">
                    Login As Student <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </a>
            @endif
            @if(isset(auth()->user()->priv()['edit_student_status']) && auth()->user()->priv()['edit_student_status'] == 1)
                <button data-tw-toggle="modal" data-tw-target="#changeStudentModal" type="button" class="btn btn-primary text-white tooltip student-profile-icon-btn student-profile-statuschange-btn" title="Change Status">
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            @endif
            <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
            <div class="dropdown">
                <button class="dropdown-toggle btn px-2 btn-outline-success student-profile-icon-btn" aria-expanded="false" data-tw-toggle="dropdown">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <i class="w-4 h-5" data-lucide="users"></i>
                    </span>
                </button>
                <div class="dropdown-menu w-52">
                    <ul class="dropdown-content">
                        @if(isset($student->children) && count($student->children) > 0)
                            @if(isset($student->descendants))
                                @foreach($student->descendants as $descendant)
                                    <li>
                                        <a href="{{ route('student.show', $descendant->id) }}" class="dropdown-item">
                                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $descendant->course->semester->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                @foreach($student->children as $child)
                                    <li>
                                        <a href="{{ route('student.show', $child->id) }}" class="dropdown-item">
                                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $child->course->semester->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        @elseif(isset($student->parent)  && is_object($student->parent))
                            @if($student->ancestors->count())
                                @foreach($student->ancestors as $ancestor)
                                    <li>
                                        <a href="{{ route('student.show', $ancestor->id) }}" class="dropdown-item">
                                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> View {{ $ancestor->course->semester->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li>
                                    <span class="dropdown-item">
                                        <i data-lucide="circle-slash-2" class="w-4 h-4 mr-2"></i> No Record
                                    </span>
                                </li>
                            @endif
                        @else
                            <li>
                                <span class="dropdown-item">
                                    <i data-lucide="circle-slash-2" class="w-4 h-4 mr-2"></i> No Record
                                </span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="student-profile-tabs-wrap intro-y box no-print">
    @include('pages.students.live.show-menu')
</div>

    <!-- BEGIN: Import Modal -->
    <div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Profile Photo</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.upload.photo') }}" class="dropzone" id="addStudentPhotoForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop file here or click to upload.</div>
                            <div class="text-slate-500">
                                Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $student->applicant_id }}"/>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadStudentPhotoBtn" class="btn btn-primary w-auto">     
                        Upload                      
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Import Modal -->

    <!-- BEGIN: Status Change Modal -->
    <div id="changeStudentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="changeStudentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Change Student Status</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="change_status_id" class="form-label">
                                Status <span class="text-danger">*</span>
                                <i data-loading-icon="three-dots" class="w-6 h-3 ml-3 inline-flex dotLoader"></i>
                            </label>
                            <select id="change_status_id" name="status_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(isset($statuses))
                                    @foreach($statuses as $stst)
                                        <option value="{{ $stst->id }}">{{ $stst->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <input type="hidden" name="is_assigned" value="{{ isset($student->is_assigned) && $student->is_assigned ? 1 : 0}}"/>
                            <label for="term_declaration_id" class="form-label">
                                Term <span class="text-danger">{{ isset($student->is_assigned) && $student->is_assigned ? '*' : '' }}</span>
                                <i data-loading-icon="three-dots" class="w-6 h-3 ml-3 inline-flex dotLoader"></i>
                            </label>
                            <select id="term_declaration_id" name="term_declaration_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($student->assigned_terms && !empty($student->assigned_terms) && $student->assigned_terms->count() > 0)
                                    @foreach($student->assigned_terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mt-3 attenIndicatorWrap" style="display: none;">
                            <label for="status_change_reason" class="form-label">Attendance Indicator</label>
                            <div class="form-check form-switch">
                                <input id="attendance_indicator" class="form-check-input" name="attendance_indicator" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_reason" class="form-label">Change Reason</label>
                            <textarea name="status_change_reason" id="status_change_reason" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_date" class="form-label">Change Date <span class="text-danger">*</span></label>
                            <input type="text" name="status_change_date" id="status_change_date" value="<?php echo date('d-m-Y') ?>" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                        @php 
                            $endStatuses = [21, 26, 27, 31, 42, 22, 45];
                            $studentStatusId = (isset($student->termStatus->status_id) && !empty($student->termStatus->status_id) ? $student->termStatus->status_id : '');
                        @endphp
                        <div class="mt-3 studyEndDateWrap" style="display: {{ in_array($studentStatusId, $endStatuses) ? 'block' : 'none' }};">
                            <label for="status_end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                            <input value="{{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->status_end_date) && !empty($student->termStatus->status_end_date)) ? date('d-m-Y', strtotime($student->termStatus->status_end_date)) : '' }}" type="text" name="status_end_date" id="status_end_date" value="" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                            <div class="acc__input-error error-status_end_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 reasonIdWrap" style="display: {{ in_array($studentStatusId, $endStatuses) ? 'block' : 'none' }};">
                            <label for="reason_for_ending_id" class="form-label">End Reason <span class="text-danger">*</span></label>
                            <select id="reason_for_ending_id" name="reason_for_engagement_ending_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($reasonEndings->count() > 0)
                                    @foreach($reasonEndings as $ersn)
                                        <option {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == $ersn->id) ? 'Selected' : '' }} value="{{ $ersn->id }}">{{ $ersn->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mt-3 qualAwardTypeWrap" style="display: {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) ? 'block' : 'none' }};">
                            <label for="qual_award_type" class="form-label">Qualification Award Type</label>
                            <select id="qual_award_type" name="qual_award_type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <!-- <option {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) && (isset($student->termStatus->qual_award_type) && $student->termStatus->qual_award_type == 'HND') ? 'Selected' : '' }} value="HND">HND</option>
                                <option {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) && (isset($student->termStatus->qual_award_type) && $student->termStatus->qual_award_type == 'HNC') ? 'Selected' : '' }} value="HNC">HNC</option> -->
                                @if(isset($student->crel->course->dfQual) && $student->crel->course->dfQual->count() > 0)
                                    @foreach($student->crel->course->dfQual as $dffileds)
                                        @if(isset($dffileds->field->name) && $dffileds->field->name == 'QUALAWARDID' && !empty($dffileds->field_value))
                                            <option {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) && (isset($student->termStatus->qual_award_type) && $student->termStatus->qual_award_type == trim($dffileds->field_value)) ? 'Selected' : '' }} value="{{ trim($dffileds->field_value) }}">{{ trim($dffileds->field_value) }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mt-3 qualIdQrap" style="display: {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) ? 'block' : 'none' }};">
                            <label for="other_academic_qualification_id" class="form-label">Qualification Award Result</label>
                            <select id="other_academic_qualification_id" name="qual_award_result_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($qualAwards->count() > 0)
                                    @foreach($qualAwards as $oaq)
                                        <option {{ in_array($studentStatusId, $endStatuses) && (isset($student->termStatus->reason_for_engagement_ending_id) && $student->termStatus->reason_for_engagement_ending_id == 1) && (isset($student->termStatus->qual_award_result_id) && $student->termStatus->qual_award_result_id == $oaq->id) ? 'Selected' : '' }} value="{{ $oaq->id }}">{{ $oaq->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <!-- <button disabled type="submit" id="updateStatusBtn" class="btn btn-primary w-auto"> -->
                        <button type="submit" id="updateStatusBtn" class="btn btn-primary w-auto">
                            Update Status
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Status Change Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModalInfo" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalInfoTitle"></div>
                        <div class="text-slate-500 mt-2 successModalInfoDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    {{-- Global quick Send-Email / Send-SMS popups (header email/phone triggers).
         Skipped on the Communication page, which ships its own copies + JS. --}}
    @if(empty($skipQuickComm))
        @include('pages.students.live.partials.quick-communication-modals')
    @endif
