@extends('../layout/' . $layout)

@section('body_class', 'tutor-dashboard-body')

@section('subhead')
    <title>Dashboard - London Churchill College</title>
    <style>
        body.tutor-dashboard-body {
            background: #f4f5f3;
        }

        body.tutor-dashboard-body .content--top-nav {
            background: #f4f5f3;
            padding: 0;
        }

        #tutorDashboard {
            --td-ink: #12312e;
            --td-muted: #8b9995;
            --td-faint: #9aa8a5;
            --td-line: #e6e8e3;
            --td-soft: #faf9f5;
            --td-cream: #efe9da;
            --td-green: #0d7c73;
            --td-green-dark: #0a655d;
            --td-gold: #c6a44e;
            --td-gold-dark: #a1802f;
            --td-danger: #b3261e;
            color: var(--td-ink);
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            margin: 0 auto;
            max-width: 1440px;
            padding: 28px 40px 60px;
        }

        #tutorDashboard *,
        #tutorDashboard *::before,
        #tutorDashboard *::after {
            box-sizing: border-box;
        }

        #tutorDashboard a {
            color: inherit;
            text-decoration: none;
        }

        #tutorDashboard .td-card {
            background: #fff;
            border: 1px solid var(--td-line);
            border-radius: 16px;
        }

        #tutorDashboard .td-welcome {
            align-items: center;
            border-radius: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
            margin-bottom: 24px;
            padding: 22px 26px;
        }

        #tutorDashboard .td-avatar {
            align-items: center;
            background: radial-gradient(circle at 50% 35%, #1c4d47, #0c2b28);
            border: 3px solid #e8d59a;
            border-radius: 16px;
            box-shadow: 0 0 0 2px rgba(198, 164, 78, .28);
            color: #e8d59a;
            display: inline-flex;
            flex: 0 0 64px;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 24px;
            font-weight: 700;
            height: 64px;
            justify-content: center;
            width: 64px;
        }

        #tutorDashboard .td-kicker {
            color: #a1926b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .12em;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        #tutorDashboard .td-title {
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 24px;
            font-weight: 650;
            letter-spacing: 0;
            line-height: 1.2;
            margin: 0;
        }

        #tutorDashboard .td-subtitle {
            color: var(--td-muted);
            font-size: 13px;
            margin-top: 3px;
        }

        #tutorDashboard .td-hero-stats {
            align-items: stretch;
            border: 1px solid var(--td-line);
            border-radius: 14px;
            display: flex;
            margin-left: auto;
            overflow: hidden;
        }

        #tutorDashboard .td-hero-stat {
            min-width: 80px;
            padding: 12px 20px;
            text-align: center;
        }

        #tutorDashboard .td-hero-stat + .td-hero-stat {
            border-left: 1px solid #eef0ec;
        }

        #tutorDashboard .td-hero-value {
            color: var(--td-green);
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 24px;
            font-weight: 700;
            line-height: 1;
        }

        #tutorDashboard .td-hero-value.is-gold {
            color: var(--td-gold-dark);
        }

        #tutorDashboard .td-hero-value.is-ink {
            color: #0f2d2a;
        }

        #tutorDashboard .td-hero-label {
            color: var(--td-faint);
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .05em;
            margin-top: 5px;
            text-transform: uppercase;
        }

        #tutorDashboard .td-grid {
            align-items: start;
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1.55fr) minmax(390px, 1fr);
        }

        #tutorDashboard .td-section-head {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 16px;
            min-height: 40px;
        }

        #tutorDashboard .td-section-title-wrap {
            align-items: center;
            display: flex;
            gap: 11px;
            min-width: 0;
        }

        #tutorDashboard .td-section-title {
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 20px;
            font-weight: 650;
            line-height: 1.2;
            margin: 0;
        }

        #tutorDashboard .td-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 10px;
            white-space: nowrap;
        }

        #tutorDashboard .td-pill.is-green {
            background: #e4f1ee;
            border: 1px solid #c4e2da;
            color: var(--td-green);
        }

        #tutorDashboard .td-pill.is-gold {
            background: #f6efdc;
            border: 1px solid #e9dcbc;
            color: var(--td-gold-dark);
        }

        #tutorDashboard .td-date-shell,
        #tutorDashboard .td-term-button {
            align-items: center;
            background: #fff;
            border: 1px solid #ded7c6;
            border-radius: 11px;
            color: #0f2d2a;
            display: inline-flex;
            gap: 9px;
            min-height: 40px;
            padding: 0 13px;
        }

        #tutorDashboard .td-date-shell svg,
        #tutorDashboard .td-term-button svg {
            color: var(--td-green);
            flex: 0 0 auto;
        }

        #tutorDashboard .td-date-input {
            background: transparent;
            border: 0;
            color: #0f2d2a;
            font-size: 13.5px;
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            height: 38px;
            outline: 0;
            padding: 0;
            width: 112px;
        }

        #tutorDashboard .td-class-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        #tutorDashboard .td-class-card {
            border-left: 4px solid var(--td-green);
            display: flex;
            gap: 20px;
            padding: 20px 22px;
        }

        #tutorDashboard .td-class-card.is-gold {
            border-left-color: var(--td-gold);
        }

        #tutorDashboard .td-class-time {
            flex: 0 0 78px;
            padding-top: 2px;
            text-align: center;
        }

        #tutorDashboard .td-time-main {
            color: #0f2d2a;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 22px;
            font-weight: 700;
            line-height: 1;
        }

        #tutorDashboard .td-time-ampm {
            color: #a1926b;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-top: 4px;
        }

        #tutorDashboard .td-time-rule {
            background: #eef0ec;
            height: 1px;
            margin-top: 12px;
        }

        #tutorDashboard .td-time-status {
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .05em;
            margin-top: 12px;
            text-transform: uppercase;
        }

        #tutorDashboard .td-class-divider {
            background: #eef0ec;
            flex: 0 0 1px;
        }

        #tutorDashboard .td-class-body {
            flex: 1;
            min-width: 0;
        }

        #tutorDashboard .td-class-meta {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 9px;
        }

        #tutorDashboard .td-code {
            background: #e4f1ee;
            border: 1px solid #c4e2da;
            border-radius: 7px;
            color: var(--td-green);
            display: inline-flex;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            padding: 5px 9px;
        }

        #tutorDashboard .td-status-pill {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            gap: 6px;
            padding: 5px 11px;
            white-space: nowrap;
        }

        #tutorDashboard .td-status-dot {
            border-radius: 999px;
            height: 6px;
            width: 6px;
        }

        #tutorDashboard .td-class-title {
            color: #12312e;
            font-size: 16.5px;
            font-weight: 750;
            line-height: 1.3;
        }

        #tutorDashboard .td-class-sub {
            align-items: center;
            color: #5a6f6c;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 10px;
        }

        #tutorDashboard .td-class-sub span {
            align-items: center;
            display: inline-flex;
            font-size: 12.5px;
            gap: 7px;
        }

        #tutorDashboard .td-class-sub svg {
            color: #a1926b;
            flex: 0 0 auto;
        }

        #tutorDashboard .td-action-row {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 16px;
        }

        #tutorDashboard .td-action {
            align-items: center;
            background: var(--td-green);
            border: 1px solid var(--td-green);
            border-radius: 11px;
            box-shadow: 0 4px 12px -4px rgba(13, 124, 115, .7);
            color: #fff;
            display: inline-flex;
            font-size: 13px;
            font-weight: 800;
            gap: 8px;
            min-height: 38px;
            padding: 9px 16px;
        }

        #tutorDashboard .td-action:hover {
            background: var(--td-green-dark);
        }

        #tutorDashboard .td-action.is-danger {
            background: var(--td-danger);
            border-color: var(--td-danger);
            box-shadow: 0 4px 12px -4px rgba(179, 38, 30, .55);
        }

        #tutorDashboard .td-alert {
            align-items: center;
            background: #fbf4e2;
            border: 1px solid #eeddb4;
            border-radius: 11px;
            color: #94711f;
            display: flex;
            font-size: 12px;
            font-weight: 600;
            gap: 10px;
            line-height: 1.4;
            margin-top: 16px;
            padding: 11px 13px;
        }

        #tutorDashboard .td-alert strong {
            color: #7a5c15;
        }

        #tutorDashboard .td-term-button {
            background: #fffdf7;
            border-color: #d8c087;
            cursor: pointer;
            font-size: 13px;
            font-weight: 800;
            justify-content: space-between;
            min-width: 156px;
            padding: 0 13px;
        }

        #tutorDashboard .td-term-label {
            min-width: 78px;
            text-align: left;
        }

        #tutorDashboard .td-term-spinner {
            display: none;
        }

        #tutorDashboard .td-term-menu {
            background: #fffdf7 !important;
            border: 1px solid #d8c087 !important;
            border-radius: 13px !important;
            box-shadow: 0 20px 42px -18px rgba(16, 49, 46, .42) !important;
            min-width: 170px !important;
            padding: 8px !important;
            width: 170px !important;
        }

        #tutorDashboard .td-term-menu .dropdown-item.term-select {
            align-items: center;
            background: transparent !important;
            border-radius: 7px !important;
            color: #263936 !important;
            display: flex !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            gap: 12px !important;
            justify-content: space-between !important;
            line-height: 1.2 !important;
            min-height: 39px !important;
            padding: 0 10px !important;
            width: 100% !important;
        }

        #tutorDashboard .td-term-menu .dropdown-item.term-select span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #tutorDashboard .td-term-menu .dropdown-item.term-select svg {
            color: var(--td-green);
            flex: 0 0 auto;
            margin-left: auto;
        }

        #tutorDashboard .td-term-menu .dropdown-item.term-select:hover,
        #tutorDashboard .td-term-menu .dropdown-item.term-select.dropdown-active {
            background: #e5f4ef !important;
            color: var(--td-green) !important;
            font-weight: 800 !important;
        }

        #tutorDashboard .td-module-panel {
            background: #fff;
            border: 1px solid var(--td-line);
            border-radius: 16px;
            max-height: 632px;
            overflow-y: auto;
            padding: 8px;
            scrollbar-gutter: stable;
        }

        #tutorDashboard .td-module-panel::-webkit-scrollbar {
            width: 9px;
        }

        #tutorDashboard .td-module-panel::-webkit-scrollbar-track {
            background: #f4f2eb;
            border-radius: 999px;
        }

        #tutorDashboard .td-module-panel::-webkit-scrollbar-thumb {
            background: #c9c4b7;
            border-radius: 999px;
        }

        #tutorDashboard .td-module-card {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 12px;
            display: flex;
            gap: 13px;
            padding: 12px;
        }

        #tutorDashboard .td-module-card:hover {
            background: #f8faf8;
            border-color: var(--td-line);
        }

        #tutorDashboard .td-module-badge {
            align-items: center;
            border-radius: 11px;
            display: inline-flex;
            flex: 0 0 42px;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            font-weight: 800;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        #tutorDashboard .td-module-code {
            color: var(--td-muted);
            display: block;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 10.5px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        #tutorDashboard .td-module-title {
            color: #12312e;
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #tutorDashboard .td-module-course {
            color: var(--td-muted);
            display: block;
            font-size: 11.5px;
            margin-top: 1px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #tutorDashboard .td-empty {
            color: #96876a;
            font-size: 13px;
            padding: 24px;
            text-align: center;
        }

        @media (max-width: 1120px) {
            #tutorDashboard .td-grid {
                grid-template-columns: 1fr;
            }

            #tutorDashboard .td-hero-stats {
                margin-left: 0;
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            #tutorDashboard {
                padding: 18px 14px 40px;
            }

            #tutorDashboard .td-welcome,
            #tutorDashboard .td-class-card {
                padding: 16px;
            }

            #tutorDashboard .td-class-card {
                gap: 14px;
            }

            #tutorDashboard .td-class-time {
                flex-basis: 62px;
            }

            #tutorDashboard .td-time-main {
                font-size: 18px;
            }

            #tutorDashboard .td-class-divider {
                display: none;
            }

            #tutorDashboard .td-section-head {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
@endsection

@php
    $employeeName = trim(($employee->title->name ?? '') . ' ' . ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
    $employeeName = $employeeName !== '' ? $employeeName : ($employee->full_name ?? $user->name ?? 'Tutor');
    $employeeEmail = $employee->user->email ?? $user->email ?? '';
    $initialsFor = function ($name) {
        $clean = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
        return strtoupper(substr($parts[0] ?? 'L', 0, 1) . substr($parts[count($parts) - 1] ?? 'C', 0, 1));
    };
    $badgeFor = function ($group) {
        $parts = preg_split('/[-\s]+/', (string) $group);
        $candidate = $parts[1] ?? $parts[0] ?? 'MOD';
        return strtoupper(substr($candidate, 0, 3));
    };
    $toneFor = function ($seed) {
        $palettes = [
            ['#e4f1ee', '#0d7c73', '#c4e2da'],
            ['#f3ecd8', '#a1802f', '#e9dcbc'],
            ['#e6ecf5', '#2f5fa1', '#cdd9ee'],
            ['#f4e6ec', '#a13f6b', '#eccdda'],
            ['#e9f0e4', '#4a7a2f', '#d3e4c7'],
            ['#ece4f5', '#7a4fa3', '#ddd0ec'],
        ];
        return $palettes[abs(crc32((string) $seed)) % count($palettes)];
    };
    $termLabelFor = function ($name) {
        $name = trim((string) $name);

        if (preg_match('/^(\d{4})\s+([A-Za-z]+)$/', $name, $match)) {
            return $match[2] . ' ' . $match[1];
        }

        return $name;
    };
    $currentTerm = $termList[$currenTerm] ?? null;
    $currentTermName = $currentTerm->name ?? 'Current term';
    $currentTermLabel = $termLabelFor($currentTermName);
    $currentModules = $data[$currenTerm] ?? [];
    $moduleCount = is_countable($currentModules) ? count($currentModules) : 0;
    $classCount = is_countable($todaysClassList) ? count($todaysClassList) : 0;
    $campusLabel = 'C';
    if (!empty($todaysClassList[0]['venue']) && preg_match('/Campus\s*([A-Z0-9]+)/i', $todaysClassList[0]['venue'], $campusMatch)) {
        $campusLabel = strtoupper($campusMatch[1]);
    }
@endphp

@section('subcontent')
    <div id="tutorDashboard">
        <div class="td-card td-welcome">
            <span class="td-avatar">{{ $initialsFor($employeeName) }}</span>
            <div class="min-w-0 flex-1">
                <div class="td-kicker">{{ date('l, d F Y') }}</div>
                <h1 class="td-title">Welcome back, {{ $employeeName }}</h1>
                <div class="td-subtitle">Term Time Lecturer · {{ $employeeEmail }}</div>
            </div>
            <div class="td-hero-stats">
                <div class="td-hero-stat">
                    <div class="td-hero-value" id="tdHeroModuleCount">{{ $moduleCount }}</div>
                    <div class="td-hero-label">Modules</div>
                </div>
                <div class="td-hero-stat">
                    <div class="td-hero-value is-gold" id="tdHeroClassCount">{{ $classCount }}</div>
                    <div class="td-hero-label">Today</div>
                </div>
                <div class="td-hero-stat">
                    <div class="td-hero-value is-ink">{{ $campusLabel }}</div>
                    <div class="td-hero-label">Campus</div>
                </div>
            </div>
        </div>

        <div class="td-grid">
            <div>
                <div class="td-section-head">
                    <div class="td-section-title-wrap">
                        <h2 class="td-section-title">Today's Classes</h2>
                        <span class="td-pill is-green" id="tdClassCount">{{ $classCount }} sessions</span>
                    </div>
                    <label class="td-date-shell">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                        <input id="tutor-calendar-date" value="{{ date('d/m/Y') }}" type="text" class="td-date-input" placeholder="DD/MM/YYYY" data-format="DD/MM/YYYY" data-single-mode="true">
                        <input name="tutor_id" value="{{ $user->id }}" type="hidden">
                    </label>
                </div>

                <div id="todays-classlist" class="td-class-list">
                    @forelse($todaysClassList as $list)
                        @php
                            $classStart = date('H:i:s', strtotime('-15 minutes', strtotime($list['start_time'])));
                            $startTimestamp = strtotime($list['start_time']);
                            $timeMain = date('h:i', $startTimestamp);
                            $timeAmPm = date('A', $startTimestamp);
                            $attendanceInfo = $list['attendance_information'] ?? null;
                            $showClassReady = ($list['showClass'] === true || (int) ($list['showClass'] ?? 0) === 1);
                            $showClassPending = ((int) ($list['showClass'] ?? 0) === 2);
                            $isStarted = $attendanceInfo && (int) $list['feed_given'] === 1 && empty($attendanceInfo->end_time);
                            $isCompleted = $attendanceInfo && (int) $list['feed_given'] === 1 && !empty($attendanceInfo->end_time);
                            $isReady = ($attendanceInfo && (int) $list['feed_given'] !== 1) || (!$attendanceInfo && $showClassReady);
                            $statusLabel = $isCompleted ? 'Completed' : ($isStarted ? 'In Progress' : ($isReady ? 'Ready' : 'Upcoming'));
                            $statusColor = $isCompleted ? '#2f5fa1' : (($isStarted || $isReady) ? '#0d7c73' : '#a1802f');
                            $statusBg = $isCompleted ? '#e6ecf5' : (($isStarted || $isReady) ? '#e4f1ee' : '#f6efdc');
                            $statusBorder = $isCompleted ? '#cdd9ee' : (($isStarted || $isReady) ? '#c4e2da' : '#e9dcbc');
                            $cardTone = ($isStarted || $isReady) ? '' : 'is-gold';
                            $location = trim(($list['venue'] ?? '') . (!empty($list['room']) ? ' - ' . $list['room'] : ''));
                        @endphp
                        <div class="td-card td-class-card {{ $cardTone }}">
                            <div class="td-class-time">
                                <div class="td-time-main">{{ $timeMain }}</div>
                                <div class="td-time-ampm">{{ $timeAmPm }}</div>
                                <div class="td-time-rule"></div>
                                <div class="td-time-status" style="color: {{ $statusColor }}">{{ $statusLabel }}</div>
                            </div>
                            <div class="td-class-divider"></div>
                            <div class="td-class-body">
                                <div class="td-class-meta">
                                    <span class="td-code">{{ $list['group'] }}</span>
                                    <span class="td-status-pill" style="color: {{ $statusColor }}; background: {{ $statusBg }}; border: 1px solid {{ $statusBorder }};">
                                        <span class="td-status-dot" style="background: {{ $statusColor }}"></span>{{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="td-class-title">{{ $list['module'] }}</div>
                                <div class="td-class-sub">
                                    <span><i data-lucide="graduation-cap" class="w-4 h-4"></i>{{ $list['course'] }}</span>
                                    @if(!empty($location))
                                        <span><i data-lucide="map-pin" class="w-4 h-4"></i>{{ $location }}</span>
                                    @endif
                                </div>

                                @if($attendanceInfo != null)
                                    <div class="td-action-row">
                                        @if($list['feed_given'] != 1)
                                            <a data-attendanceinfo="{{ $attendanceInfo->id }}" data-id="{{ $list['id'] }}" href="{{ route('tutor-dashboard.attendance', [$list['tutor_id'], $list['id']]) }}" class="start-punch td-action">
                                                <i data-lucide="check-square" class="w-4 h-4"></i>Feed Attendance
                                            </a>
                                        @else
                                            <a href="{{ route('tutor-dashboard.attendance', [$list['tutor_id'], $list['id']]) }}" data-attendanceinfo="{{ $attendanceInfo->id }}" data-id="{{ $list['id'] }}" class="start-punch td-action">
                                                <i data-lucide="eye" class="w-4 h-4"></i>View Feed
                                            </a>
                                            @if($list['feed_given'] == 1 && $attendanceInfo->end_time == null)
                                                <a data-attendanceinfo="{{ $attendanceInfo->id }}" data-id="{{ $list['id'] }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch td-action is-danger">
                                                    <i data-lucide="x-circle" class="w-4 h-4"></i>End Class
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                @else
                                    @if($showClassReady)
                                        <div class="td-action-row">
                                            <a data-tw-toggle="modal" data-id="{{ $list['id'] }}" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch td-action">
                                                <i data-lucide="check-square" class="w-4 h-4"></i>Start Class
                                            </a>
                                        </div>
                                    @elseif($showClassPending || date('H:i:s') < $classStart)
                                        <div class="td-alert" role="alert">
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                            <span>Class Start button appears <strong>15 minutes</strong> before the scheduled time.</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="td-card td-empty">No class found for the selected day.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="td-section-head">
                    <div class="td-section-title-wrap">
                        <h2 class="td-section-title">My Modules</h2>
                        <span class="td-pill is-gold" id="tdModuleCount">{{ $moduleCount }}</span>
                    </div>
                    <div id="term-dropdown" class="dropdown relative">
                        <button id="selected-term" class="dropdown-toggle td-term-button" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="calendar-days" class="w-4 h-4"></i>
                            <svg class="td-term-spinner w-4 h-4" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".35" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                            <span class="td-term-label">{{ $currentTermLabel }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </button>
                        <div class="dropdown-menu">
                            <ul class="dropdown-content td-term-menu">
                                @foreach($termList as $term)
                                    <li>
                                        <a id="term-{{ $term->id }}" data-tutor_id="{{ $employee->user_id }}" data-instance_term_id="{{ $term->id }}" data-instance_term="{{ $term->name }}" data-instance_term_label="{{ $termLabelFor($term->name) }}" href="javascript:;" class="dropdown-item term-select {{ ($currentTermName == $term->name) ? 'dropdown-active' : '' }}">
                                            <span>{{ $termLabelFor($term->name) }}</span>
                                            @if($currentTermName == $term->name)
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div id="TermBox" class="td-module-panel">
                    @forelse($currentModules as $termData)
                        @php
                            $tone = $toneFor(($termData->group ?? '') . ($termData->module ?? ''));
                        @endphp
                        <a href="{{ route('tutor-dashboard.plan.module.show', $termData->id) }}" target="_blank" class="td-module-card">
                            <span class="td-module-badge" style="background: {{ $tone[0] }}; color: {{ $tone[1] }}; border: 1px solid {{ $tone[2] }};">{{ $badgeFor($termData->group) }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="td-module-code">{{ $termData->group }}</span>
                                <span class="td-module-title">{{ $termData->module }}</span>
                                <span class="td-module-course">{{ $termData->course }}</span>
                            </span>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-[#c9bd9a] flex-none"></i>
                        </a>
                    @empty
                        <div class="td-empty">Modules not found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        @include('pages.tutor.dashboard.modals')
    </div>
@endsection

@section('script')
    @vite('resources/js/tutor-dashboard-new.js')
@endsection
