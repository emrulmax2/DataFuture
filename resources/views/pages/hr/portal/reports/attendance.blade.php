@extends('../layout/' . $layout)

@section('body_class', 'hr-attendance-report-body')

@section('subhead')
    <title>{{ $title }}</title>
    <style>
        body.hr-attendance-report-body,
        body.hr-attendance-report-body .content--top-nav {
            background: #eef1f0;
        }

        #attendanceReportPage {
            --ar-ink: #12312e;
            --ar-muted: #8b9995;
            --ar-line: #e6e1d3;
            --ar-soft-line: #eef0ea;
            --ar-green: #0d7c73;
            --ar-gold: #c6a44e;
            --ar-gold-dark: #a1802f;
            color: var(--ar-ink);
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            width: 100%;
        }

        #attendanceReportPage *,
        #attendanceReportPage *::before,
        #attendanceReportPage *::after {
            box-sizing: border-box;
        }

        #attendanceReportPage a {
            text-decoration: none;
        }

        #attendanceReportPage .ar-card {
            background: #fff;
            border: 1px solid var(--ar-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
        }

        #attendanceReportPage .ar-hero {
            margin-top: 26px;
            padding: 22px 24px;
        }

        #attendanceReportPage .ar-hero-top {
            align-items: center;
            display: flex;
            gap: 18px;
            justify-content: space-between;
        }

        #attendanceReportPage .ar-hero-copy {
            align-items: center;
            display: flex;
            gap: 17px;
            min-width: 0;
        }

        #attendanceReportPage .ar-hero-icon {
            align-items: center;
            background: var(--ar-green);
            border-radius: 14px;
            box-shadow: 0 14px 24px -14px rgba(13, 124, 115, .85);
            color: #fff;
            display: inline-flex;
            flex: 0 0 54px;
            height: 54px;
            justify-content: center;
            width: 54px;
        }

        #attendanceReportPage .ar-eyebrow {
            color: #a1926b;
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        #attendanceReportPage .ar-title {
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 27px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.08;
            margin: 0;
        }

        #attendanceReportPage .ar-title-mark {
            border-bottom: 2px solid var(--ar-gold);
            color: var(--ar-green);
            display: inline-block;
            padding-bottom: 3px;
        }

        #attendanceReportPage .ar-subtitle {
            color: var(--ar-muted);
            display: block;
            font-size: 13px;
            margin-top: 8px;
        }

        #attendanceReportPage .ar-download {
            align-items: center;
            background: #18864f;
            border-radius: 10px;
            box-shadow: 0 12px 26px -16px rgba(5, 90, 52, .95);
            color: #fff;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 700;
            gap: 8px;
            min-height: 42px;
            padding: 0 19px;
        }

        #attendanceReportPage .ar-download:hover {
            background: #137543;
            color: #fff;
        }

        #attendanceReportPage .ar-download.is-loading {
            opacity: .65;
            pointer-events: none;
        }

        #attendanceReportPage .ar-filter-line {
            border-top: 1px solid var(--ar-soft-line);
            margin-top: 22px;
            padding-top: 20px;
        }

        #attendanceReportPage .ar-filter-grid {
            align-items: end;
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(280px, 340px) minmax(170px, 200px) minmax(170px, 180px);
        }

        #attendanceReportPage .ar-field-label {
            color: #9aa8a5;
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        #attendanceReportPage .ar-employee-wrap,
        #attendanceReportPage .ar-menu-wrap {
            position: relative;
            width: 100%;
        }

        #attendanceReportPage .ar-employee-control,
        #attendanceReportPage .ar-menu-control {
            align-items: center;
            background: #f9f7f1 !important;
            border: 1px solid #ded7c6 !important;
            border-radius: 11px !important;
            box-shadow: none !important;
            color: #0f2d2a;
            display: flex;
            font-size: 13.5px;
            height: 44px;
            width: 100%;
        }

        #attendanceReportPage .ar-employee-wrap.is-open .ar-employee-control,
        #attendanceReportPage .ar-menu-wrap.is-open .ar-menu-control {
            border-color: var(--ar-gold) !important;
        }

        #attendanceReportPage .ar-employee-control {
            cursor: pointer;
            gap: 10px;
            padding: 11px 13px !important;
            text-align: left;
        }

        #attendanceReportPage .ar-employee-control .ar-control-icon {
            color: var(--ar-green);
            display: inline-flex;
            flex: 0 0 16px;
            height: 16px;
            width: 16px;
        }

        #attendanceReportPage .ar-employee-label {
            color: #8b9995;
            flex: 1 1 auto;
            font-size: 13.5px;
            font-weight: 500;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .ar-employee-label.is-selected {
            color: #0f2d2a;
            font-weight: 600;
        }

        #attendanceReportPage .ar-employee-chevron,
        #attendanceReportPage .ar-menu-chevron {
            color: #a1926b;
            flex: 0 0 auto;
            transition: transform .15s ease;
        }

        #attendanceReportPage .ar-employee-wrap.is-open .ar-employee-chevron,
        #attendanceReportPage .ar-menu-wrap.is-open .ar-menu-chevron {
            transform: rotate(180deg);
        }

        #attendanceReportPage .ar-employee-list {
            background: #fffdf7;
            border: 1px solid #d8cfb8;
            border-radius: 13px;
            box-shadow: 0 26px 54px -18px rgba(16, 49, 46, .45);
            display: none;
            left: 0;
            max-height: 335px;
            overflow-y: auto;
            padding: 6px;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            z-index: 40;
        }

        #attendanceReportPage .ar-employee-wrap.is-open .ar-employee-list {
            display: block;
        }

        #attendanceReportPage .ar-employee-search-wrap {
            background: #fffdf7;
            border-bottom: 1px solid #eee7d9;
            padding: 5px 5px 7px;
            position: sticky;
            top: -6px;
            z-index: 2;
        }

        #attendanceReportPage .ar-employee-search {
            background: #fff;
            border: 1px solid #ded7c6;
            border-radius: 9px;
            color: #12312e;
            font-size: 13px;
            min-height: 36px;
            outline: 0;
            padding: 8px 10px 8px 34px;
            width: 100%;
        }

        #attendanceReportPage .ar-employee-search:focus {
            border-color: var(--ar-gold);
            box-shadow: 0 0 0 3px rgba(198, 164, 78, .12);
        }

        #attendanceReportPage .ar-employee-search-icon {
            color: var(--ar-green);
            left: 15px;
            pointer-events: none;
            position: absolute;
            top: 15px;
        }

        #attendanceReportPage .ar-employee-option {
            background: transparent;
            border: 0;
            border-radius: 9px;
            cursor: pointer;
            padding: 0;
            text-align: left;
            width: 100%;
        }

        #attendanceReportPage .ar-employee-option:hover,
        #attendanceReportPage .ar-employee-option.is-active {
            background: #eef7f4;
        }

        #attendanceReportPage .ar-employee-row {
            align-items: center;
            border-bottom: 1px solid #eee7d9;
            display: flex;
            gap: 10px;
            min-height: 50px;
            padding: 8px 9px;
        }

        #attendanceReportPage .ar-employee-option-all {
            color: var(--ar-green);
            font-size: 13.5px;
            font-weight: 700;
            padding: 10px 11px;
        }

        #attendanceReportPage .ar-employee-avatar {
            align-items: center;
            background: var(--ar-avatar-bg, #0d7c73);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 32px;
            font-size: 11px;
            font-weight: 700;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        #attendanceReportPage .ar-employee-name {
            color: #12312e;
            display: block;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.15;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .ar-employee-role {
            color: #8b9995;
            display: block;
            font-size: 11px;
            line-height: 1.2;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .ar-employee-empty {
            color: #96876a;
            display: none;
            font-size: 13px;
            padding: 14px 11px;
            text-align: center;
        }

        #attendanceReportPage .ar-menu-control {
            cursor: pointer;
            gap: 10px;
            justify-content: flex-start;
            padding: 11px 13px !important;
            text-align: left;
        }

        #attendanceReportPage .ar-month-control {
            height: 44px;
            padding-bottom: 11px !important;
            padding-top: 11px !important;
        }

        #attendanceReportPage .ar-month-input {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            border: 0;
            border-color: transparent !important;
            box-shadow: none !important;
            color: #0f2d2a;
            cursor: pointer;
            flex: 1 1 auto;
            font-size: 13.5px;
            font-weight: 600;
            min-width: 0;
            outline: 0 !important;
            padding: 0 !important;
            --tw-ring-color: transparent !important;
            --tw-ring-offset-shadow: 0 0 #0000 !important;
            --tw-ring-shadow: 0 0 #0000 !important;
            width: 100%;
        }

        #attendanceReportPage .ar-month-input:focus,
        #attendanceReportPage .ar-month-input:focus-visible,
        #attendanceReportPage .ar-month-input:active {
            border: 0 !important;
            box-shadow: none !important;
            outline: 0 !important;
            --tw-ring-color: transparent !important;
            --tw-ring-offset-shadow: 0 0 #0000 !important;
            --tw-ring-shadow: 0 0 #0000 !important;
        }

        #attendanceReportPage .ar-month-input::placeholder {
            color: #8b9995;
        }

        .litepicker.attendance-month-litepicker .attendance-month-option.is-disabled,
        .litepicker.attendance-month-litepicker .attendance-month-option.is-disabled:hover {
            background: #f3f1eb;
            border-color: #e5dfd0;
            color: #b9b0a0;
            cursor: not-allowed;
            opacity: .65;
        }

        #attendanceReportPage .ar-control-icon {
            color: var(--ar-green);
            flex: 0 0 auto;
        }

        #attendanceReportPage .ar-menu-label {
            flex: 1 1 auto;
            font-size: 13.5px;
            font-weight: 500;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .ar-menu-label.is-selected,
        #attendanceReportPage .ar-menu-wrap[data-selected="1"] .ar-menu-label {
            color: #0f2d2a;
            font-weight: 600;
        }

        #attendanceReportPage .ar-menu-list {
            background: #fffdf7;
            border: 1px solid #d8cfb8;
            border-radius: 13px;
            box-shadow: 0 26px 54px -18px rgba(16, 49, 46, .45);
            display: none;
            left: 0;
            overflow: hidden;
            padding: 6px;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            z-index: 35;
        }

        #attendanceReportPage .ar-menu-wrap.is-open .ar-menu-list {
            display: block;
        }

        #attendanceReportPage .ar-menu-option {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 9px;
            color: #3f524f;
            cursor: pointer;
            display: flex;
            font-size: 13.5px;
            font-weight: 500;
            justify-content: space-between;
            min-height: 36px;
            padding: 10px 11px;
            text-align: left;
            width: 100%;
        }

        #attendanceReportPage .ar-menu-option:hover {
            background: #faf6ec;
        }

        #attendanceReportPage .ar-menu-option.is-active {
            background: #eef7f4;
            color: var(--ar-green);
            font-weight: 700;
        }

        #attendanceReportPage .ar-menu-check {
            color: var(--ar-green);
            display: none;
            flex: 0 0 auto;
        }

        #attendanceReportPage .ar-menu-option.is-active .ar-menu-check {
            display: inline-flex;
        }

        #attendanceReportPage .ar-native-select {
            display: none;
        }

        #attendanceReportPage .attendanceReportWrap {
            margin-top: 20px;
        }

        #attendanceReportPage .hr-att-report-card {
            background: #fff;
            border: 1px solid var(--ar-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
            overflow: hidden;
        }

        #attendanceReportPage .hr-att-report-scroll {
            overflow-x: auto;
        }

        #attendanceReportPage .hr-att-report-table {
            min-width: 1180px;
        }

        #attendanceReportPage .hr-att-report-row {
            align-items: center;
            display: grid;
            gap: 12px;
            grid-template-columns: 2fr 1fr 1.1fr 1.1fr 1.15fr 1.1fr .9fr 1.15fr;
            min-height: 62px;
            padding: 12px 24px;
        }

        #attendanceReportPage .hr-att-report-head {
            background: #fafaf7;
            border-bottom: 2px solid var(--ar-soft-line);
            color: #9aa8a5;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            min-height: 44px;
            padding-bottom: 14px;
            padding-top: 14px;
            position: sticky;
            text-transform: uppercase;
            top: 0;
            z-index: 2;
        }

        #attendanceReportPage .hr-att-gross-head {
            color: var(--ar-green);
        }

        #attendanceReportPage .hr-att-report-body-row {
            background: #fff;
            border-bottom: 1px solid #f3f4f0;
            color: inherit;
        }

        #attendanceReportPage .hr-att-report-body-row:nth-child(odd) {
            background: #fbfbf9;
        }

        #attendanceReportPage .hr-att-report-body-row:hover {
            background: #f8faf9;
            color: inherit;
        }

        #attendanceReportPage .hr-att-person {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }

        #attendanceReportPage .hr-att-avatar {
            align-items: center;
            background: var(--hr-att-avatar-bg, var(--ar-green));
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 36px;
            font-size: 11.5px;
            font-weight: 700;
            height: 36px;
            justify-content: center;
            width: 36px;
        }

        #attendanceReportPage .hr-att-name {
            color: var(--ar-green);
            display: block;
            font-size: 13px;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .hr-att-role {
            color: #93a09d;
            display: block;
            font-size: 11px;
            margin-top: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceReportPage .hr-att-report-row > span:not(.hr-att-person),
        #attendanceReportPage .hr-att-money,
        #attendanceReportPage .hr-att-strong,
        #attendanceReportPage .hr-att-warn,
        #attendanceReportPage .hr-att-muted,
        #attendanceReportPage .hr-att-gross {
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12.5px;
            text-align: right;
            white-space: nowrap;
        }

        #attendanceReportPage .hr-att-money {
            color: #5a6f6c;
            font-weight: 500;
        }

        #attendanceReportPage .hr-att-strong {
            color: #12312e;
            font-weight: 700;
        }

        #attendanceReportPage .hr-att-warn {
            color: var(--ar-gold-dark);
        }

        #attendanceReportPage .hr-att-muted {
            color: #c3ccc9;
        }

        #attendanceReportPage .hr-att-gross {
            color: var(--ar-green);
            font-size: 13px;
            font-weight: 700;
        }

        #attendanceReportPage .hr-att-chip {
            background: #f6efdc;
            border: 1px solid #e9dcbc;
            border-radius: 7px;
            color: var(--ar-gold-dark);
            display: inline-flex;
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
        }

        #attendanceReportPage .hr-att-report-total {
            background: #f4f8f6;
            border-top: 2px solid #dcebe5;
            color: #0f2d2a;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            min-height: 56px;
            text-transform: uppercase;
        }

        #attendanceReportPage .hr-att-empty {
            align-items: center;
            color: #96876a;
            display: flex;
            gap: 8px;
            justify-content: center;
            min-height: 90px;
            padding: 24px;
        }

        @media (max-width: 900px) {
            #attendanceReportPage .ar-hero-top,
            #attendanceReportPage .ar-filter-grid {
                align-items: stretch;
                grid-template-columns: 1fr;
            }

            #attendanceReportPage .ar-hero-top {
                flex-direction: column;
            }

            #attendanceReportPage .ar-download {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
@endsection

@section('subcontent')
    @php
        $reportCount = $reportHtml['count'] ?? $employees->count();
        $monthLabel = date('F Y', strtotime($theDate));
        $exportDate = date('Y-m-d', strtotime($theDate));
        $selectedMonthRoute = date('m-Y', strtotime($theDate));
        $avatarPalette = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
    @endphp

    <div id="attendanceReportPage">
        <section class="ar-card ar-hero">
            <div class="ar-hero-top">
                <div class="ar-hero-copy">
                    <span class="ar-hero-icon"><i data-lucide="bar-chart-3" class="w-6 h-6"></i></span>
                    <span class="min-w-0">
                        <span class="ar-eyebrow">HR Portal &middot; Reports</span>
                        <h1 class="ar-title">Attendance Report's Of <span class="ar-title-mark">{{ $monthLabel }}</span></h1>
                        <span class="ar-subtitle"><span class="hr-att-count">{{ $reportCount }}</span> staff &middot; monthly hours &amp; pay summary</span>
                    </span>
                </div>
                <a id="downloadExcel" data-base-url="{{ route('hr.portal.reports.attendance.export', $exportDate) }}" href="{{ route('hr.portal.reports.attendance.export', $exportDate) }}" class="ar-download">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    <span>Download Excel</span>
                </a>
            </div>

            <form id="attendanceReportForm" class="ar-filter-line" method="post" action="#">
                @csrf
                <div class="ar-filter-grid">
                    <div>
                        <label class="ar-field-label" for="employee_id">Filter by Employee</label>
                        <div class="ar-employee-wrap" data-ar-employee-menu data-selected="0">
                            <button type="button" class="ar-employee-control" data-ar-employee-toggle aria-expanded="false">
                                <i data-lucide="user" class="ar-control-icon w-4 h-4"></i>
                                <span class="ar-employee-label" data-ar-employee-label>All Employees</span>
                                <i data-lucide="chevron-down" class="ar-employee-chevron w-4 h-4"></i>
                            </button>
                            <div class="ar-employee-list" data-ar-employee-list>
                                <div class="ar-employee-search-wrap">
                                    <i data-lucide="search" class="ar-employee-search-icon w-4 h-4"></i>
                                    <input type="search" class="ar-employee-search" data-ar-employee-search placeholder="Search employee..." autocomplete="off">
                                </div>
                                <button type="button" class="ar-employee-option ar-employee-option-all is-active" data-ar-employee-option data-value="all" data-label="All Employees">
                                    All Employees
                                </button>
                                @foreach($employees as $emp)
                                    @php
                                        $employeeName = trim(($emp->first_name ?? '').' '.($emp->last_name ?? ''));
                                        $employeeName = $employeeName !== '' ? $employeeName : $emp->full_name;
                                        $employeeRole = $emp->employment->employeeJobTitle->name ?? 'Staff';
                                        $nameParts = preg_split('/\s+/', preg_replace('/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i', '', $employeeName), -1, PREG_SPLIT_NO_EMPTY);
                                        $firstInitial = strtoupper(substr($nameParts[0] ?? 'L', 0, 1));
                                        $lastInitial = strtoupper(substr($nameParts[count($nameParts) - 1] ?? 'C', 0, 1));
                                        $hash = 0;
                                        foreach(str_split($employeeName) as $char):
                                            $hash = (($hash * 31) + ord($char)) & 0xffffffff;
                                        endforeach;
                                        $avatarColour = $avatarPalette[$hash % count($avatarPalette)];
                                    @endphp
                                    <button type="button" class="ar-employee-option" data-ar-employee-option data-value="{{ $emp->id }}" data-label="{{ $employeeName }}" data-search="{{ strtolower($employeeName.' '.$employeeRole) }}">
                                        <span class="ar-employee-row">
                                            <span class="ar-employee-avatar" style="--ar-avatar-bg: {{ $avatarColour }};">{{ $firstInitial.$lastInitial }}</span>
                                            <span class="min-w-0">
                                                <span class="ar-employee-name">{{ $employeeName }}</span>
                                                <span class="ar-employee-role">{{ $employeeRole }}</span>
                                            </span>
                                        </span>
                                    </button>
                                @endforeach
                                <div class="ar-employee-empty" data-ar-employee-empty>No employee found</div>
                            </div>
                            <select id="employee_id" name="employee_id[]" class="ar-native-select" tabindex="-1" aria-hidden="true">
                                <option value="all" selected data-role="" data-initials="" data-avatar="">All Employees</option>
                                @foreach($employees as $emp)
                                    @php
                                        $employeeName = trim(($emp->first_name ?? '').' '.($emp->last_name ?? ''));
                                        $employeeName = $employeeName !== '' ? $employeeName : $emp->full_name;
                                    @endphp
                                    <option value="{{ $emp->id }}">{{ $employeeName }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="ar-field-label" for="employee_work_type_id">Type</label>
                        <div class="ar-menu-wrap" data-ar-select-menu data-selected="0">
                            <button type="button" class="ar-menu-control" data-ar-menu-toggle aria-expanded="false">
                                <i data-lucide="menu" class="ar-control-icon w-4 h-4"></i>
                                <span class="ar-menu-label" data-ar-menu-label>All</span>
                                <i data-lucide="chevron-down" class="ar-menu-chevron w-4 h-4 text-[#a1926b]"></i>
                            </button>
                            <div class="ar-menu-list" data-ar-menu-list>
                                <button type="button" class="ar-menu-option is-active" data-ar-menu-option data-value="" data-label="All">
                                    <span>All</span>
                                    <i data-lucide="check" class="ar-menu-check w-4 h-4"></i>
                                </button>
                                @foreach($workTypes as $type)
                                    <button type="button" class="ar-menu-option" data-ar-menu-option data-value="{{ $type->id }}" data-label="{{ $type->name }}">
                                        <span>{{ $type->name }}</span>
                                        <i data-lucide="check" class="ar-menu-check w-4 h-4"></i>
                                    </button>
                                @endforeach
                            </div>
                            <select id="employee_work_type_id" name="employee_work_type_id" class="ar-native-select" tabindex="-1" aria-hidden="true">
                                <option value="">All</option>
                                @foreach($workTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="ar-field-label">Month</label>
                        <div class="ar-menu-wrap" data-ar-month-picker data-selected="1">
                            <div class="ar-menu-control ar-month-control">
                                <i data-lucide="calendar-days" class="w-4 h-4 text-[#a1802f]"></i>
                                <input type="text" id="report_month_picker" class="ar-month-input" value="{{ $monthLabel }}" readonly data-date="{{ $exportDate }}" data-route-value="{{ $selectedMonthRoute }}">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" value="{{ $exportDate }}" id="the_date" name="the_date"/>
            </form>
        </section>

        <div class="attendanceReportWrap" style="display: {{ (isset($reportHtml['html']) && !empty($reportHtml['html']) ? 'block;' : 'none;') }}">
            {!! (isset($reportHtml['html']) && !empty($reportHtml['html']) ? $reportHtml['html'] : '') !!}
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report.js')
@endsection
