@extends('../layout/' . $layout)

@section('body_class', 'hr-holiday-management-body')

@section('subhead')
    <title>{{ $title }}</title>
    <style>
        body.hr-holiday-management-body,
        body.hr-holiday-management-body .content--top-nav {
            background: #eef1f0;
        }

        #holidayManagementPage {
            --hm-ink: #12312e;
            --hm-muted: #8b9995;
            --hm-line: #e6e1d3;
            --hm-soft-line: #f0f2ec;
            --hm-cream: #faf9f5;
            --hm-panel: #fffdf7;
            --hm-green: #0d7c73;
            --hm-green-dark: #0a655d;
            --hm-gold: #c6a44e;
            --hm-gold-dark: #a1802f;
            --hm-danger: #b3261e;
            color: var(--hm-ink);
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            margin: 0;
            max-width: none;
            padding: 0;
            width: 100%;
        }

        #holidayManagementPage *,
        #holidayManagementPage *::before,
        #holidayManagementPage *::after {
            box-sizing: border-box;
        }

        #holidayManagementPage a {
            color: inherit;
            text-decoration: none;
        }

        #holidayManagementPage .hm-card {
            background: #fff;
            border: 1px solid var(--hm-line);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
        }

        #holidayManagementPage .hm-hero {
            align-items: center;
            display: flex;
            gap: 20px;
            justify-content: space-between;
            margin-bottom: 22px;
            min-height: 112px;
            padding: 22px 24px;
        }

        #holidayManagementPage .hm-hero-copy {
            align-items: center;
            display: flex;
            gap: 17px;
            min-width: 0;
        }

        #holidayManagementPage .hm-hero-icon {
            align-items: center;
            background: var(--hm-green);
            border-radius: 14px;
            box-shadow: 0 14px 24px -14px rgba(13, 124, 115, .85);
            color: #fff;
            display: inline-flex;
            flex: 0 0 54px;
            height: 54px;
            justify-content: center;
            width: 54px;
        }

        #holidayManagementPage .hm-eyebrow {
            color: #a1926b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        #holidayManagementPage .hm-title {
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.05;
            margin: 0;
        }

        #holidayManagementPage .hm-subtitle {
            color: var(--hm-muted);
            font-size: 13px;
            margin-top: 8px;
        }

        #holidayManagementPage .hm-calendar-link {
            align-items: center;
            background: #fffdf7;
            border: 1px solid #d8cfb8;
            border-radius: 11px;
            color: #374743;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            min-height: 40px;
            padding: 0 16px;
        }

        #holidayManagementPage .hm-calendar-link svg {
            color: var(--hm-green);
        }

        #holidayManagementPage .hm-year-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        #holidayManagementPage .hm-year-card {
            background: #fff;
            border: 1px solid var(--hm-line);
            border-radius: 16px;
            overflow: hidden;
        }

        #holidayManagementPage .hm-year-header,
        #holidayManagementPage .hm-year-toggle {
            border: 0;
            margin: 0;
        }

        #holidayManagementPage .hm-year-toggle {
            align-items: center;
            background: #fff;
            color: var(--hm-ink);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            min-height: 72px;
            padding: 18px 24px;
            text-align: left;
            width: 100%;
        }

        #holidayManagementPage .hm-year-toggle::after {
            display: none;
        }

        #holidayManagementPage .hm-year-toggle:not(.collapsed) {
            background: #f4f8f6;
        }

        #holidayManagementPage .hm-year-main {
            align-items: center;
            display: inline-flex;
            gap: 14px;
            min-width: 0;
        }

        #holidayManagementPage .hm-year-icon {
            align-items: center;
            background: #e4f1ee;
            border-radius: 10px;
            color: var(--hm-green);
            display: inline-flex;
            flex: 0 0 36px;
            height: 36px;
            justify-content: center;
            width: 36px;
        }

        #holidayManagementPage .hm-year-label {
            color: #a1926b;
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            line-height: 1.1;
            text-transform: uppercase;
        }

        #holidayManagementPage .hm-year-value {
            color: var(--hm-green);
            display: block;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.1;
            margin-top: 3px;
        }

        #holidayManagementPage .hm-year-side {
            align-items: center;
            display: inline-flex;
            gap: 16px;
        }

        #holidayManagementPage .hm-year-stats {
            align-items: center;
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }

        #holidayManagementPage .hm-pill {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            min-height: 22px;
            padding: 3px 9px;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-pill--pending {
            background: #f6efdc;
            border: 1px solid #e9dcbc;
            color: var(--hm-gold-dark);
        }

        #holidayManagementPage .hm-pill--approved {
            background: #e6f4ee;
            border: 1px solid #c4e5d5;
            color: var(--hm-green);
        }

        #holidayManagementPage .hm-pill--rejected {
            background: #fbeceb;
            border: 1px solid #f2d4d0;
            color: var(--hm-danger);
        }

        #holidayManagementPage .hm-year-chevron {
            align-items: center;
            background: #fff;
            border: 1px solid #e4dcc7;
            border-radius: 9px;
            color: var(--hm-green);
            display: inline-flex;
            flex: 0 0 30px;
            height: 30px;
            justify-content: center;
            width: 30px;
        }

        #holidayManagementPage .hm-year-chevron svg {
            transition: transform .16s ease;
        }

        #holidayManagementPage .hm-year-toggle:not(.collapsed) .hm-year-chevron svg {
            transform: rotate(180deg);
        }

        #holidayManagementPage .hm-year-body {
            background: #fbfbf9;
            border-top: 1px solid #e6ede9;
            display: flex;
            flex-direction: column;
            gap: 22px;
            padding: 22px 24px 26px;
        }

        #holidayManagementPage .hm-leave-panel {
            --hm-accent: var(--hm-green);
            --hm-chip-bg: #e6f4ee;
            --hm-chip-border: #c4e5d5;
            background: #fff;
            border: 1px solid #ecebe2;
            border-radius: 15px;
            overflow: hidden;
        }

        #holidayManagementPage .hm-leave-panel--pending {
            --hm-accent: var(--hm-gold);
            --hm-chip-bg: #f6efdc;
            --hm-chip-border: #e9dcbc;
        }

        #holidayManagementPage .hm-leave-panel--approved {
            --hm-accent: var(--hm-green);
            --hm-chip-bg: #e6f4ee;
            --hm-chip-border: #c4e5d5;
        }

        #holidayManagementPage .hm-leave-panel--rejected {
            --hm-accent: var(--hm-danger);
            --hm-chip-bg: #fbeceb;
            --hm-chip-border: #f2d4d0;
        }

        #holidayManagementPage .hm-section-head {
            align-items: center;
            border-bottom: 1px solid var(--hm-soft-line);
            display: flex;
            gap: 11px;
            min-height: 62px;
            padding: 15px 20px;
        }

        #holidayManagementPage .hm-section-icon {
            align-items: center;
            background: var(--hm-chip-bg);
            border-radius: 9px;
            color: var(--hm-accent);
            display: inline-flex;
            flex: 0 0 32px;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        #holidayManagementPage .hm-section-title {
            color: var(--hm-ink);
            font-size: 15px;
            font-weight: 600;
        }

        #holidayManagementPage .hm-section-count {
            background: var(--hm-chip-bg);
            border: 1px solid var(--hm-chip-border);
            border-radius: 999px;
            color: var(--hm-accent);
            font-size: 11.5px;
            font-weight: 600;
            line-height: 1;
            margin-left: 2px;
            padding: 2px 9px;
        }

        #holidayManagementPage .hm-table-shell {
            overflow-x: auto;
        }

        #holidayManagementPage .hm-tabulator.tabulator {
            background: #fff;
            border: 0;
            color: var(--hm-ink);
            font-family: inherit;
            min-width: 920px;
        }

        #holidayManagementPage .hm-tabulator .tabulator-header {
            background: #fafaf7;
            border: 0;
            border-bottom: 1px solid var(--hm-soft-line);
            color: #9aa8a5;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        #holidayManagementPage .hm-tabulator .tabulator-col {
            background: transparent;
            border: 0;
            min-height: 40px;
        }

        #holidayManagementPage .hm-tabulator .tabulator-col-content {
            padding: 12px 18px;
        }

        #holidayManagementPage .hm-tabulator .tabulator-row {
            background: #fff;
            border: 0;
            border-bottom: 1px solid #f3f4f0;
            border-left: 3px solid var(--hm-accent);
            min-height: 72px;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-tabulator .tabulator-row:nth-child(even) {
            background: #fbfbf9;
        }

        #holidayManagementPage .hm-tabulator .tabulator-row:hover {
            background: #f4f8f6;
        }

        #holidayManagementPage .hm-tabulator .tabulator-cell {
            border: 0;
            display: inline-block;
            min-height: 72px;
            overflow: hidden;
            padding: 14px 18px;
            text-overflow: ellipsis;
            vertical-align: middle;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-person-cell {
            align-items: center;
            display: flex;
            gap: 12px;
            min-height: 44px;
            min-width: 0;
        }

        #holidayManagementPage .hm-avatar {
            align-items: center;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 38px;
            font-size: 12px;
            font-weight: 800;
            height: 38px;
            justify-content: center;
            overflow: hidden;
            width: 38px;
        }

        #holidayManagementPage .hm-avatar img {
            display: block;
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        #holidayManagementPage .hm-person-name {
            color: #12312e;
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-meta-main {
            color: #12433d;
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-person-role,
        #holidayManagementPage .hm-meta-sub {
            color: #93a09d;
            display: block;
            font-size: 11.5px;
            line-height: 1.25;
            margin-top: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-status-cell {
            align-items: center;
            display: flex;
            flex-direction: row;
            gap: 7px;
            justify-content: flex-start;
            min-height: 44px;
            min-width: 0;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-status-chip {
            align-items: center;
            background: var(--hm-chip-bg);
            border: 1px solid var(--hm-chip-border);
            border-radius: 8px;
            color: var(--hm-accent);
            display: inline-flex;
            font-size: 11.5px;
            font-weight: 600;
            gap: 6px;
            line-height: 1.1;
            max-width: 100%;
            overflow: hidden;
            padding: 5px 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-status-time {
            align-items: center;
            background: #f6efdc;
            border: 1px solid #e9dcbc;
            border-radius: 8px;
            color: var(--hm-gold-dark);
            display: inline-flex;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 600;
            justify-content: center;
            line-height: 1.1;
            min-height: 24px;
            padding: 5px 9px;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-date-cell {
            align-items: center;
            color: #3f524f;
            display: flex;
            font-size: 13px;
            font-weight: 500;
            min-height: 44px;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-title-cell {
            align-items: center;
            color: #4a5a57;
            display: flex;
            font-size: 12.5px;
            min-height: 44px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-title-cell.is-empty {
            color: #c3ccc9;
        }

        #holidayManagementPage .hm-title-cell.is-long {
            font-style: italic;
        }

        #holidayManagementPage .hm-meta-cell {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 44px;
            min-width: 0;
        }

        #holidayManagementPage .hm-supervised {
            color: var(--hm-green);
            flex: 0 0 auto;
        }

        #holidayManagementPage .hm-tabulator .tabulator-footer {
            align-items: center;
            background: #fafaf7;
            border: 0;
            border-top: 1px solid #f3f4f0;
            color: #8b9995;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            padding: 13px 20px;
            text-align: left;
            width: 100%;
        }

        #holidayManagementPage .hm-tabulator .tabulator-footer .tabulator-footer-contents {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            width: 100%;
        }

        #holidayManagementPage .hm-tabulator .tabulator-footer .tabulator-paginator {
            align-items: center;
            display: flex;
            flex: 1 1 auto;
            flex-wrap: nowrap;
            gap: 5px;
            justify-content: flex-end;
            margin-left: auto;
            width: 100%;
        }

        #holidayManagementPage .hm-tabulator .tabulator-footer .tabulator-paginator > label {
            align-items: center;
            display: inline-flex;
            gap: 9px;
            margin-right: 0;
        }

        #holidayManagementPage .hm-tabulator .tabulator-footer .tabulator-pages {
            align-items: center;
            display: inline-flex;
            gap: 5px;
            margin: 0;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page-size {
            background: #fff;
            border: 1px solid #e0e2dd;
            border-radius: 9px;
            color: #0f2d2a;
            font-size: 12.5px;
            font-weight: 600;
            margin-left: 8px;
            margin-right: auto;
            min-height: 30px;
            min-width: 58px;
            padding: 4px 28px 4px 10px;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page {
            background: #fff;
            border: 1px solid #e0e2dd;
            border-radius: 8px;
            color: #5a6f6c;
            font-size: 12.5px;
            font-weight: 600;
            min-height: 30px;
            min-width: 32px;
            padding: 5px 8px;
            text-align: center;
            white-space: nowrap;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page[data-page="first"],
        #holidayManagementPage .hm-tabulator .tabulator-page[data-page="prev"],
        #holidayManagementPage .hm-tabulator .tabulator-page[data-page="next"],
        #holidayManagementPage .hm-tabulator .tabulator-page[data-page="last"] {
            min-width: 42px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page[title*="First"],
        #holidayManagementPage .hm-tabulator .tabulator-page[title*="Prev"],
        #holidayManagementPage .hm-tabulator .tabulator-page[title*="Next"],
        #holidayManagementPage .hm-tabulator .tabulator-page[title*="Last"] {
            min-width: 42px !important;
            padding-left: 12px !important;
            padding-right: 12px !important;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page.active {
            background: var(--hm-green);
            border-color: var(--hm-green);
            color: #fff;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page:disabled {
            color: #9ca9a6;
            opacity: 1;
        }

        #holidayManagementPage .hm-tabulator .tabulator-page:not(:disabled):hover {
            background: #eef7f5;
            border-color: #c4e5d5;
            color: var(--hm-green);
        }

        #holidayManagementPage .hm-tabulator .tabulator-placeholder {
            color: #96876a;
            font-size: 13px;
            padding: 24px;
        }

        #holidayManagementPage .hm-empty {
            color: #96876a;
            font-size: 13px;
            padding: 26px;
            text-align: center;
        }

        @media (max-width: 900px) {
            #holidayManagementPage {
                padding: 0;
            }

            #holidayManagementPage .hm-hero,
            #holidayManagementPage .hm-year-toggle {
                align-items: stretch;
                flex-direction: column;
            }

            #holidayManagementPage .hm-year-side {
                justify-content: space-between;
                width: 100%;
            }
        }
    </style>
@endsection

@section('subcontent')
    @php
        $holidayStats = $holidayStats ?? [];
        $sectionMeta = [
            [
                'type' => 'pending',
                'title' => 'Pending Leaves',
                'icon' => 'clock',
                'count' => 'pending',
            ],
            [
                'type' => 'approved',
                'title' => 'Approved Leaves',
                'icon' => 'check',
                'count' => 'approved',
            ],
            [
                'type' => 'rejected',
                'title' => 'Rejected Leaves',
                'icon' => 'x',
                'count' => 'rejected',
            ],
        ];
    @endphp

    <div id="holidayManagementPage">
        <section class="hm-card hm-hero">
            <div class="hm-hero-copy">
                <span class="hm-hero-icon"><i data-lucide="calendar-check" class="w-6 h-6"></i></span>
                <span class="min-w-0">
                    <span class="hm-eyebrow">HR Portal &middot; Leave</span>
                    <h1 class="hm-title">Holiday Management</h1>
                    <span class="hm-subtitle">Leave requests by holiday year &middot; London Churchill College</span>
                </span>
            </div>
            <a href="{{ route('hr.portal.leave.calendar') }}" class="hm-calendar-link">
                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                <span>Holiday Calendar</span>
            </a>
        </section>

        <div id="employeeHolidayAccordion" class="accordion employeeHolidayAccordion hm-year-list">
            @forelse($years as $year)
                @php
                    $yearLabel = date('Y', strtotime($year->start_date)).' - '.date('Y', strtotime($year->end_date));
                    $stats = $holidayStats[$year->id] ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];
                @endphp
                <div class="accordion-item hm-year-card">
                    <div id="employeeHolidayAccordion-{{ $loop->index }}" class="accordion-header hm-year-header">
                        <button data-year="{{ $year->id }}" class="holidayCollapseBtns accordion-button hm-year-toggle {{ ($loop->index == 0 ? '' : 'collapsed') }}" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ ($loop->index == 0 ? 'true' : 'false') }}" aria-controls="employeeHolidayAccordion-collapse-{{ $loop->index }}">
                            <span class="hm-year-main">
                                <span class="hm-year-icon"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
                                <span>
                                    <span class="hm-year-label">Holiday Year</span>
                                    <span class="hm-year-value">{{ $yearLabel }}</span>
                                </span>
                            </span>
                            <span class="hm-year-side">
                                <span class="hm-year-stats">
                                    <span class="hm-pill hm-pill--pending">{{ $stats['pending'] }} pending</span>
                                    <span class="hm-pill hm-pill--approved">{{ $stats['approved'] }} approved</span>
                                    <span class="hm-pill hm-pill--rejected">{{ $stats['rejected'] }} rejected</span>
                                </span>
                                <span class="hm-year-chevron"><i data-lucide="chevron-down" class="w-4 h-4"></i></span>
                            </span>
                        </button>
                    </div>
                    <div id="employeeHolidayAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ ($loop->index == 0 ? 'show' : '') }}" aria-labelledby="employeeHolidayAccordion-{{ $loop->index }}" data-tw-parent="#employeeHolidayAccordion">
                        <div class="accordion-body hm-year-body">
                            @foreach($sectionMeta as $section)
                                <section class="hm-leave-panel hm-leave-panel--{{ $section['type'] }}">
                                    <header class="hm-section-head">
                                        <span class="hm-section-icon"><i data-lucide="{{ $section['icon'] }}" class="w-4 h-4"></i></span>
                                        <span class="hm-section-title">{{ $section['title'] }}</span>
                                        <span class="hm-section-count">{{ $stats[$section['count']] ?? 0 }}</span>
                                    </header>
                                    <div class="hm-table-shell">
                                        <div id="leaveListTable-{{ $section['type'] }}-{{ $year->id }}" data-year="{{ $year->id }}" data-type="{{ $section['type'] }}" class="manageHolidayListTables hm-tabulator hm-tabulator--{{ $section['type'] }} table-report table-report--tabulator"></div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="hm-card hm-empty">No active holiday year found.</div>
            @endforelse
        </div>
    </div>

<!-- BEGIN: Edit New Request Modal -->
<div id="empNewLeaveRequestModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="empNewLeaveRequestForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Update Leave Request</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="updateNLR" class="btn btn-primary w-auto">     
                        Save                  
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
                    <input type="hidden" name="employee_leave_id" value="0"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit New Request Modal -->


<!-- BEGIN: Success Modal Content -->
<div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 successModalTitle"></div>
                    <div class="text-slate-500 mt-2 successModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->

<!-- BEGIN: Warning Modal Content -->
<div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 warningModalTitle"></div>
                    <div class="text-slate-500 mt-2 warningModalDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Warning Modal Content -->

<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                    <div class="text-slate-500 mt-2 confModDesc"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-holiday-manager.js')
@endsection
