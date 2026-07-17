@extends('../layout/' . $layout)

@section('body_class', 'tutor-module-body')

@section('subhead')
    <title>{{ $title }}- </title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.tutor-module-body {
            background: #eef1f0;
        }

        body.tutor-module-body .content--top-nav {
            background: #eef1f0;
        }

        #tutorModuleDetails {
            --tm-ink: #12312e;
            --tm-deep: #0f2d2a;
            --tm-muted: #5a6f6c;
            --tm-faint: #93a09d;
            --tm-line: #e6e1d3;
            --tm-soft-line: #eef0ea;
            --tm-cream: #fbfaf6;
            --tm-cream-strong: #f6f3ea;
            --tm-green: #0d7c73;
            --tm-green-dark: #0a655d;
            --tm-gold: #c6a44e;
            --tm-gold-dark: #a1802f;
            --tm-danger: #b3261e;
            color: var(--tm-ink);
            font-family: "IBM Plex Sans", system-ui, sans-serif;
            margin: 0;
            max-width: none;
            padding: 0;
            width: 100%;
        }

        #tutorModuleDetails *,
        #tutorModuleDetails *::before,
        #tutorModuleDetails *::after {
            box-sizing: border-box;
        }

        #tutorModuleDetails a {
            text-decoration: none;
        }

        #tutorModuleDetails .tm-shell {
            background: #fff;
            border: 1px solid var(--tm-line);
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05), 0 24px 50px -34px rgba(11, 35, 32, .5);
            margin-bottom: 20px;
            overflow: hidden;
        }

        #tutorModuleDetails .tm-hero {
            background: radial-gradient(135% 160% at 10% -10%, #fff 0%, #f6f5ec 60%, #eef1ea 100%);
            overflow: hidden;
            padding: 30px 32px 26px;
            position: relative;
        }

        #tutorModuleDetails .tm-hero::before {
            background: radial-gradient(circle, rgba(198, 164, 78, .16), transparent 70%);
            border-radius: 999px;
            content: "";
            height: 280px;
            position: absolute;
            right: -40px;
            top: -70px;
            width: 280px;
        }

        #tutorModuleDetails .tm-hero::after {
            background-image: linear-gradient(rgba(13, 124, 115, .05) 1px, transparent 1px), linear-gradient(90deg, rgba(13, 124, 115, .05) 1px, transparent 1px);
            background-size: 26px 26px;
            content: "";
            height: 100%;
            -webkit-mask-image: linear-gradient(90deg, transparent, #000);
            mask-image: linear-gradient(90deg, transparent, #000);
            position: absolute;
            right: 0;
            top: 0;
            width: 340px;
        }

        #tutorModuleDetails .tm-hero-inner {
            align-items: flex-start;
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }

        #tutorModuleDetails .tm-kicker {
            align-items: center;
            color: var(--tm-green);
            display: inline-flex;
            font-size: 10.5px;
            font-weight: 700;
            gap: 8px;
            letter-spacing: .14em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        #tutorModuleDetails .tm-kicker::before {
            background: var(--tm-gold);
            content: "";
            height: 1px;
            width: 22px;
        }

        #tutorModuleDetails .tm-title {
            color: #10312e;
            font-family: "IBM Plex Serif", Georgia, serif;
            font-size: 34px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.05;
            margin: 0;
        }

        #tutorModuleDetails .tm-title span {
            color: #b8912f;
        }

        #tutorModuleDetails .tm-badges {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        #tutorModuleDetails .tm-badge {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 11.5px;
            font-weight: 700;
            gap: 7px;
            letter-spacing: .04em;
            padding: 6px 13px;
        }

        #tutorModuleDetails .tm-badge.is-gold {
            background: rgba(198, 164, 78, .14);
            border: 1px solid rgba(198, 164, 78, .4);
            color: #8a6d1f;
        }

        #tutorModuleDetails .tm-badge.is-green {
            background: rgba(47, 174, 127, .12);
            border: 1px solid rgba(47, 174, 127, .34);
            color: var(--tm-green);
        }

        #tutorModuleDetails .tm-badge-dot {
            background: #2fae7f;
            border-radius: 999px;
            height: 6px;
            width: 6px;
        }

        #tutorModuleDetails .tm-team {
            flex: none;
            min-width: 280px;
        }

        #tutorModuleDetails .tm-team-label {
            color: #5a7671;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .12em;
            margin-bottom: 11px;
            text-transform: uppercase;
        }

        #tutorModuleDetails .tm-team-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        #tutorModuleDetails .tm-person {
            align-items: center;
            background: #fff;
            border: 1px solid var(--tm-line);
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(16, 49, 46, .05);
            display: flex;
            gap: 11px;
            padding: 8px 14px 8px 8px;
        }

        #tutorModuleDetails .tm-avatar {
            align-items: center;
            background: #137a70;
            border: 2px solid rgba(198, 164, 78, .5);
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 38px;
            font-size: 12px;
            font-weight: 700;
            height: 38px;
            justify-content: center;
            overflow: hidden;
            width: 38px;
        }

        #tutorModuleDetails .tm-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        #tutorModuleDetails .tm-person-name {
            color: #10312e;
            display: block;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        #tutorModuleDetails .tm-person-role {
            color: #6b807c;
            display: block;
            font-size: 10.5px;
        }

        #tutorModuleDetails .tm-stat-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: 24px;
            max-width: 640px;
            position: relative;
            z-index: 1;
        }

        #tutorModuleDetails .tm-stat {
            align-items: center;
            background: #fff;
            border: 1px solid var(--tm-line);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(16, 49, 46, .05);
            display: flex;
            gap: 12px;
            padding: 14px 16px;
        }

        #tutorModuleDetails .tm-stat-icon {
            align-items: center;
            background: rgba(198, 164, 78, .16);
            border-radius: 10px;
            color: #b8912f;
            display: inline-flex;
            flex: 0 0 38px;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        #tutorModuleDetails .tm-stat-icon.is-green {
            background: rgba(47, 174, 127, .16);
            color: #1f9e78;
        }

        #tutorModuleDetails .tm-stat-icon.is-ink {
            background: rgba(16, 49, 46, .07);
            color: #3d5651;
        }

        #tutorModuleDetails .tm-stat-label {
            color: #5a7671;
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        #tutorModuleDetails .tm-stat-value {
            color: #10312e;
            display: block;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 15px;
            font-weight: 600;
            margin-top: 2px;
        }

        #tutorModuleDetails .tm-tabs {
            align-items: center;
            background: var(--tm-cream);
            border-top: 1px solid var(--tm-soft-line);
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 14px 20px;
        }

        #tutorModuleDetails .tm-tabs .nav-item {
            margin: 0 !important;
        }

        #tutorModuleDetails .tm-tabs .nav-link {
            align-items: center;
            background: transparent;
            border: 1px solid transparent;
            border-radius: 999px;
            color: var(--tm-muted);
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            padding: 9px 16px !important;
            transition: all .15s;
            white-space: nowrap;
        }

        #tutorModuleDetails .tm-tabs .nav-link:hover,
        #tutorModuleDetails .tm-tabs .nav-link.active {
            background: #f4e8c4;
            color: #8a6d1f;
        }

        #tutorModuleDetails .tm-tabs .nav-link i,
        #tutorModuleDetails .tm-tabs .nav-link svg {
            color: #93a09d;
            height: 16px;
            width: 16px;
        }

        #tutorModuleDetails .tm-tabs .nav-link.active i,
        #tutorModuleDetails .tm-tabs .nav-link.active svg,
        #tutorModuleDetails .tm-tabs .nav-link:hover i,
        #tutorModuleDetails .tm-tabs .nav-link:hover svg {
            color: #b8912f;
        }

        #tutorModuleDetails .tm-tab-toggle .tm-tab-caret {
            transition: transform .15s;
        }

        #tutorModuleDetails .tm-tab-toggle[aria-expanded="true"] .tm-tab-caret {
            transform: rotate(180deg);
        }

        /* The dropdown plugin moves an open .dropdown-menu to <body>, so these rules cannot be
           nested under #tutorModuleDetails — the menu is no longer inside it once shown. */
        .tm-tab-menu {
            font-family: "IBM Plex Sans", system-ui, sans-serif;
            min-width: 236px;
        }

        .tm-tab-menu .dropdown-content {
            padding: 6px !important;
        }

        .tm-tab-menu .tm-subtab {
            align-items: center;
            border-radius: 9px !important;
            display: flex;
            gap: 11px;
            padding: 9px 11px !important;
        }

        .tm-tab-menu .tm-subtab:hover,
        .tm-tab-menu .tm-subtab.active {
            background: #f4e8c4 !important;
        }

        .tm-tab-menu .tm-subtab-icon {
            align-items: center;
            background: rgba(13, 124, 115, .10);
            border-radius: 9px;
            color: #0d7c73;
            display: inline-flex;
            flex: 0 0 32px;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        .tm-tab-menu .tm-subtab.active .tm-subtab-icon {
            background: rgba(184, 145, 47, .18);
            color: #8a6d1f;
        }

        .tm-tab-menu .tm-subtab-text {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }

        .tm-tab-menu .tm-subtab-text strong {
            color: #12312e;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.3;
        }

        .tm-tab-menu .tm-subtab.active .tm-subtab-text strong {
            color: #8a6d1f;
        }

        .tm-tab-menu .tm-subtab-text small {
            color: #93a09d;
            font-size: 11px;
            line-height: 1.3;
        }

        #tutorModuleDetails .tm-content {
            margin-top: 20px;
        }

        #tutorModuleDetails #class-dates {
            padding-top: 14px;
        }

        #tutorModuleDetails .tm-panel {
            animation: tmFadeIn .2s ease;
            background: #fff;
            border: 1px solid var(--tm-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
            overflow: hidden;
        }

        #tutorModuleDetails .tm-panel-pad {
            padding: 24px 26px;
        }

        #tutorModuleDetails .tm-section-head {
            align-items: center;
            border-bottom: 1px solid #f0ede3;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 20px 24px;
        }

        #tutorModuleDetails .tm-section-head.no-border {
            border-bottom: 0;
            padding: 0 0 20px;
        }

        #tutorModuleDetails .tm-section-title {
            color: var(--tm-deep);
            font-family: "IBM Plex Serif", Georgia, serif;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        #tutorModuleDetails .tm-toolbar {
            align-items: center;
            border-bottom: 1px solid #f0f2ec;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: space-between;
            padding: 14px 24px;
        }

        #tutorModuleDetails .tm-filter {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        #tutorModuleDetails .tm-filter label {
            align-items: center;
            color: #8b9995;
            display: inline-flex;
            font-size: 12.5px;
            gap: 8px;
            margin: 0;
        }

        #tutorModuleDetails .tm-control,
        #tutorModuleDetails .form-control,
        #tutorModuleDetails .form-select,
        #tutorModuleDetails .tom-select,
        #tutorModuleDetails .ts-control {
            /* background-color, not the background shorthand: the shorthand resets
               background-image, and that is where @tailwindcss/forms draws the select chevron. */
            background-color: #f9f7f1 !important;
            border: 1px solid #ded7c6 !important;
            border-radius: 9px !important;
            box-shadow: none !important;
            color: var(--tm-deep) !important;
            font-size: 13px !important;
            min-height: 38px;
        }

        #tutorModuleDetails .tm-control,
        #tutorModuleDetails .form-control,
        #tutorModuleDetails .form-select {
            padding: 8px 12px !important;
        }

        /* The chevron is painted over the right edge, so keep a gutter for it — the rule above
           would otherwise let the selected value run underneath the arrow. */
        #tutorModuleDetails .form-select {
            padding-right: 32px !important;
        }

        #tutorModuleDetails .tm-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            justify-content: flex-end;
        }

        #tutorModuleDetails .tm-btn,
        #tutorModuleDetails .btn {
            align-items: center;
            border-radius: 9px !important;
            display: inline-flex;
            font-size: 13px !important;
            font-weight: 600 !important;
            gap: 7px;
            justify-content: center;
            min-height: 38px;
            padding: 8px 14px !important;
        }

        #tutorModuleDetails .btn-primary,
        #tutorModuleDetails .tm-btn-primary {
            background: rgba(13, 124, 115, .10) !important;
            border: 1px solid rgba(13, 124, 115, .22) !important;
            color: var(--tm-green-dark) !important;
        }

        #tutorModuleDetails .btn-primary:hover,
        #tutorModuleDetails .tm-btn-primary:hover {
            background: rgba(13, 124, 115, .20) !important;
        }

        #tutorModuleDetails .btn-secondary,
        #tutorModuleDetails .btn-outline-secondary,
        #tutorModuleDetails .tm-btn-secondary {
            background: #f4f0e6 !important;
            border: 1px solid #e4dcc7 !important;
            color: var(--tm-muted) !important;
        }

        #tutorModuleDetails .btn-success,
        #tutorModuleDetails .tm-btn-success {
            background: var(--tm-green) !important;
            border-color: var(--tm-green) !important;
            color: #fff !important;
        }

        #tutorModuleDetails .btn-danger,
        #tutorModuleDetails .tm-btn-danger {
            background: rgba(179, 38, 30, .10) !important;
            border: 1px solid rgba(179, 38, 30, .28) !important;
            color: var(--tm-danger) !important;
        }

        #tutorModuleDetails .tm-icon-btn,
        #tutorModuleDetails .btn-rounded {
            border-radius: 8px !important;
            height: 34px !important;
            min-height: 34px !important;
            padding: 0 !important;
            width: 34px !important;
        }

        #tutorModuleDetails .tm-table-wrap {
            overflow-x: auto;
            padding: 0;
        }

        #tutorModuleDetails .table-report,
        #tutorModuleDetails table.table {
            border-collapse: separate;
            border-spacing: 0;
            margin: 0 !important;
            width: 100%;
        }

        #tutorModuleDetails .table-report thead th,
        #tutorModuleDetails table.table thead th,
        #tutorModuleDetails .tabulator .tabulator-header .tabulator-col {
            background: #fafaf7 !important;
            border-bottom: 2px solid #eef0ea !important;
            color: #9aa8a5 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        #tutorModuleDetails table.table td,
        #tutorModuleDetails table.table th,
        #tutorModuleDetails .table-report td,
        #tutorModuleDetails .table-report th {
            border-bottom: 1px solid #f3f4f0 !important;
            padding: 14px 16px !important;
            vertical-align: middle;
        }

        #tutorModuleDetails .tabulator {
            background: #fff;
            border: 0 !important;
            font-family: "IBM Plex Sans", system-ui, sans-serif;
        }

        #tutorModuleDetails .tabulator .tabulator-tableholder {
            background: #fff;
        }

        #tutorModuleDetails .tabulator .tabulator-row {
            border-bottom: 1px solid #f3f4f0 !important;
        }

        #tutorModuleDetails .tabulator .tabulator-row:nth-child(even) {
            background: #fbfbf9 !important;
        }

        #tutorModuleDetails .tabulator .tabulator-row .tabulator-cell {
            border-right: 0 !important;
            color: #3f524f;
            padding: 12px 14px !important;
        }

        #tutorModuleDetails .tabulator-footer {
            background: #fafaf7 !important;
            border-top: 1px solid #f0ede3 !important;
        }

        #tutorModuleDetails .tm-participant-filter {
            justify-content: flex-end;
        }

        #tutorModuleDetails .tm-participant-filter .form-select {
            font-weight: 600;
            min-width: 86px;
        }

        #tutorModuleDetails .tm-selected-bar {
            align-items: center;
            background: #f4f8fc;
            border-bottom: 1px solid #dbe6f2;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            padding: 12px 24px;
        }

        #tutorModuleDetails .tm-selection-meta {
            align-items: center;
            color: #5a6f6c;
            display: inline-flex;
            flex-wrap: wrap;
            font-size: 12.5px;
            gap: 8px;
        }

        #tutorModuleDetails .tm-selected-count {
            align-items: center;
            background: #2f6fb0;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            height: 26px;
            justify-content: center;
            min-width: 26px;
            padding: 0 8px;
        }

        #tutorModuleDetails .tm-selection-label {
            color: #3f524f;
            font-weight: 600;
        }

        #tutorModuleDetails .tm-selection-clear {
            background: transparent;
            border: 0;
            color: #2f6fb0;
            font-size: 12.5px;
            font-weight: 700;
            padding: 0;
            text-decoration: underline;
        }

        #tutorModuleDetails .tm-selected-bar .btn {
            border-radius: 9px !important;
            height: 34px;
            min-height: 34px;
            padding: 6px 12px !important;
        }

        #tutorModuleDetails .tm-selected-bar .sendBulkSmsBtn {
            background: #b07d1c !important;
            border-color: #b07d1c !important;
            color: #fff !important;
        }

        #tutorModuleDetails .tm-selected-bar .sendBulkMailBtn {
            background: #0d7c73 !important;
            border-color: #0d7c73 !important;
            color: #fff !important;
        }

        #tutorModuleDetails .tm-selected-bar #exportStudentList {
            background: #f4f0e6 !important;
            border: 1px solid #e4dcc7 !important;
            color: #5a6f6c !important;
        }

        #tutorModuleDetails .tm-table.tabulator {
            border-radius: 0;
            color: #12312e;
            overflow: hidden;
        }

        #tutorModuleDetails .tm-table .tabulator-header {
            background: #fafaf7 !important;
            border-bottom: 2px solid #eef0ea !important;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col {
            background: #fafaf7 !important;
            border-right: 0 !important;
            min-height: 46px;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col .tabulator-col-content {
            align-items: center;
            display: flex;
            padding: 12px 0 !important;
        }

        /* Tabulator mounts the sort arrow inside .tabulator-col-title-holder, NOT inside
           .tabulator-col-content — so the holder is the flex row that keeps the arrow beside
           the label. headerHozAlign writes text-align onto the title, which a flex row ignores,
           so each non-default alignment is pinned on the holder below. */
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col .tabulator-col-title-holder {
            align-items: center;
            display: flex;
            flex: 1 1 auto;
            gap: 6px;
            justify-content: flex-start;
            min-width: 0;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tm-participant-select-cell .tabulator-col-title-holder,
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tm-participant-mode-cell .tabulator-col-title-holder {
            justify-content: center;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tm-assessment-actions-cell .tabulator-col-title-holder {
            justify-content: flex-end;
        }

        #tutorModuleDetails .tm-table .tabulator-row .tabulator-cell.tm-assessment-actions-cell {
            justify-content: flex-end;
        }

        /* Tabulator gives the title width:100%, which would stretch it and shove the arrow out
           to the column edge — the exact drift being fixed here. Let it size to its text. */
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col .tabulator-col-title {
            flex: 0 1 auto;
            min-width: 0;
            width: auto;
        }

        /* The header checkbox sits in an inline title element, so it rides the text baseline and
           drifts from the row checkboxes below it. Centre it the same way the cells already are.
           Row cells must stay inline-flex — a block-level flex here breaks the row's inline flow. */
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tm-participant-select-cell .tabulator-col-title {
            align-items: center;
            display: flex;
            justify-content: center;
            overflow: visible;
        }

        #tutorModuleDetails .tm-table .tabulator-row .tabulator-cell.tm-participant-select-cell {
            justify-content: center;
        }

        /* Sort arrow rides inline after the title. */
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tabulator-sortable .tabulator-col-title {
            padding-right: 0 !important;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-sorter {
            bottom: auto;
            flex: 0 0 auto;
            position: static;
            right: auto;
            top: auto;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
            background-position: center;
            background-repeat: no-repeat;
            background-size: 11px 11px;
            border: 0 !important;
            height: 11px;
            opacity: 0;
            transition: opacity .12s ease-in-out;
            width: 11px;
        }

        /* At rest only the sorted column shows an arrow; hovering a sortable column hints at it. */
        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tabulator-sortable:hover .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
            opacity: .55;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="none"] .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%239aa8a5' stroke-width='2.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m8 9 4-4 4 4'/%3E%3Cpath d='m16 15-4 4-4-4'/%3E%3C/svg%3E");
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="asc"] .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%230d7c73' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 15 6-6 6 6'/%3E%3C/svg%3E");
            opacity: 1;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col.tabulator-sortable[aria-sort="desc"] .tabulator-col-content .tabulator-col-sorter .tabulator-arrow {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%230d7c73' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            opacity: 1;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col:first-child .tabulator-col-content,
        #tutorModuleDetails .tm-table .tabulator-row .tabulator-cell:first-child {
            padding-left: 24px !important;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col:last-child .tabulator-col-content,
        #tutorModuleDetails .tm-table .tabulator-row .tabulator-cell:last-child {
            padding-right: 24px !important;
        }

        #tutorModuleDetails .tm-table .tabulator-header .tabulator-col-title {
            color: #9aa8a5 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            letter-spacing: .05em;
            line-height: 1.2;
            text-transform: uppercase;
        }

        #tutorModuleDetails .tm-table .tabulator-tableholder,
        #tutorModuleDetails .tm-table .tabulator-tableHolder {
            background: #fff;
            max-height: 620px;
        }

        #tutorModuleDetails .tm-table .tabulator-row {
            background: #fff !important;
            border-bottom: 1px solid #f3f4f0 !important;
            min-height: 57px;
        }

        #tutorModuleDetails .tm-table .tabulator-row.tabulator-row-even {
            background: #fbfbf9 !important;
        }

        #tutorModuleDetails .tm-table .tabulator-row.tabulator-selected,
        #tutorModuleDetails .tm-table .tabulator-row.tabulator-selected:hover {
            background: #eef4fb !important;
        }

        #tutorModuleDetails .tm-table .tabulator-row .tabulator-cell {
            align-items: center;
            border-right: 0 !important;
            color: #3f524f;
            display: inline-flex;
            min-height: 57px;
            padding: 11px 0 !important;
            white-space: nowrap;
        }

        #tutorModuleDetails .tm-table .tabulator-header input[type="checkbox"],
        #tutorModuleDetails .tm-table .tm-participant-select-cell input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            background: #fff;
            border: 1px solid #cdd4d1;
            border-radius: 5px;
            cursor: pointer;
            height: 20px;
            margin: 0;
            position: relative;
            width: 20px;
        }

        #tutorModuleDetails .tm-table .tabulator-header input[type="checkbox"]:checked,
        #tutorModuleDetails .tm-table .tm-participant-select-cell input[type="checkbox"]:checked {
            background: #2f6fb0;
            border-color: #2f6fb0;
        }

        #tutorModuleDetails .tm-table .tabulator-header input[type="checkbox"]:checked::after,
        #tutorModuleDetails .tm-table .tm-participant-select-cell input[type="checkbox"]:checked::after {
            border: solid #fff;
            border-width: 0 2px 2px 0;
            content: "";
            height: 10px;
            left: 6px;
            position: absolute;
            top: 2px;
            transform: rotate(45deg);
            width: 5px;
        }

        #tutorModuleDetails .tm-table .tm-participant-sn {
            color: #93a09d;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 12.5px;
        }

        /* ---- Assessments table ---- */

        #tutorModuleDetails .tm-assessment-name {
            color: #12312e;
            font-size: 13.5px;
            font-weight: 600;
        }

        #tutorModuleDetails .tm-assessment-date {
            color: #3f524f;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 12.5px;
        }

        #tutorModuleDetails .tm-assessment-date.is-empty {
            color: #b6c0bd;
        }

        #tutorModuleDetails .tm-row-actions {
            align-items: center;
            display: inline-flex;
            gap: 6px;
            justify-content: flex-end;
        }

        #tutorModuleDetails .tm-row-action {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            flex: 0 0 30px;
            height: 30px;
            justify-content: center;
            padding: 0;
            transition: filter .12s ease-in-out;
            width: 30px;
        }

        #tutorModuleDetails .tm-row-action:hover {
            filter: brightness(.95);
        }

        #tutorModuleDetails .tm-row-action svg {
            height: 15px;
            width: 15px;
        }

        #tutorModuleDetails .tm-row-action.is-download {
            background: #f7edda;
            border-color: #ecdcbd;
            color: #a9782a;
        }

        #tutorModuleDetails .tm-row-action.is-upload {
            background: #d9ebe7;
            border-color: #bcdcd5;
            color: #0d7c73;
        }

        #tutorModuleDetails .tm-row-action.is-edit {
            background: #e4f0e8;
            border-color: #c8e0d1;
            color: #2f7a52;
        }

        #tutorModuleDetails .tm-row-action.is-delete {
            background: #fbe9e7;
            border-color: #f2d4d0;
            color: #c0392b;
        }

        #tutorModuleDetails .tm-row-action.is-restore {
            background: #e8eff7;
            border-color: #cddcec;
            color: #2f5fa1;
        }

        #tutorModuleDetails .tm-participant-reg {
            align-items: center;
            display: inline-flex;
            gap: 11px;
            min-width: 0;
        }

        #tutorModuleDetails .tm-participant-avatar {
            align-items: center;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 34px;
            font-size: 12px;
            font-weight: 700;
            height: 34px;
            justify-content: center;
            letter-spacing: 0;
            width: 34px;
        }

        #tutorModuleDetails .tm-participant-regno {
            color: #3f524f;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 12.5px;
            font-weight: 500;
        }

        #tutorModuleDetails .tm-participant-mode {
            align-items: center;
            display: inline-flex;
            gap: 7px;
            justify-content: center;
            width: 100%;
        }

        #tutorModuleDetails .tm-participant-mode svg {
            height: 19px;
            width: 19px;
        }

        #tutorModuleDetails .tm-participant-mode .is-day {
            color: #b98116;
        }

        #tutorModuleDetails .tm-participant-mode .is-evening {
            color: #0d7c73;
        }

        #tutorModuleDetails .tm-participant-mode .is-disability {
            color: #9b1313;
        }

        #tutorModuleDetails .tm-participant-name {
            color: #12312e;
            font-size: 13.5px;
            font-weight: 500;
        }

        #tutorModuleDetails .tm-participant-status {
            align-items: center;
            border: 1px solid #c4e2da;
            border-radius: 8px;
            color: #0d7c73;
            display: inline-flex;
            box-sizing: border-box;
            font-size: 11.5px;
            font-weight: 600;
            gap: 6px;
            height: 23px;
            line-height: 1;
            padding: 0 10px;
        }

        #tutorModuleDetails .tm-participant-status::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 6px;
            width: 6px;
        }

        #tutorModuleDetails .tm-participant-status.is-enrolled {
            background: #e4f1ee;
            border-color: #c4e2da;
            color: #0d7c73;
        }

        #tutorModuleDetails .tm-participant-status.is-warning {
            background: #f6efdc;
            border-color: #e9dcbc;
            color: #a1802f;
        }

        #tutorModuleDetails .tm-participant-status.is-danger {
            background: #fbeceb;
            border-color: #f2d4d0;
            color: #c0392b;
        }

        #tutorModuleDetails .tm-participant-status.is-muted {
            background: #f4f5f4;
            border-color: #e1e5e1;
            color: #8a9b98;
        }

        #tutorModuleDetails .tm-table .tabulator-footer {
            background: #fafaf7 !important;
            border-top: 1px solid #f0ede3 !important;
            color: #5a6f6c !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            padding: 14px 24px !important;
            text-align: left !important;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-paginator {
            align-items: center;
            color: #5a6f6c;
            display: flex;
            font-size: 12px;
            font-weight: 500;
            gap: 8px;
            width: 100%;
        }

        /* Tabulator already emits its own localized "Page Size" label; style that rather than
           injecting a second one. */
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-paginator > label {
            color: #8b9995;
            font-size: 12px;
            font-weight: 500;
            margin: 0;
        }

        /* No auto margin here — the page-size select above already owns the push to the right.
           Two competing auto margins would split the free space and strand this mid-footer. */
        #tutorModuleDetails .tm-table .tabulator-footer .tm-participant-counter {
            color: #8b9995;
            font-size: 12px;
            font-weight: 500;
            padding-right: 8px;
            white-space: nowrap;
        }

        /* The auto right margin is what pushes the counter and the pager to the right edge.
           It rides on the page-size select because that is the one element both tables always
           have — the counter only exists on Participants. */
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page-size {
            background: #fff !important;
            border: 1px solid #dfe4e2 !important;
            border-radius: 9px !important;
            color: #3f524f !important;
            font-size: 12px;
            font-weight: 700;
            height: 30px;
            margin: 0 auto 0 0 !important;
            padding: 4px 28px 4px 10px !important;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-paginator > .tabulator-page:first-of-type {
            margin-left: 0 !important;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-pages {
            margin: 0 !important;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page {
            align-items: center;
            background: #fff !important;
            border: 1px solid #dfe4e2 !important;
            border-radius: 8px !important;
            color: #5a6f6c !important;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            height: 30px;
            justify-content: center;
            margin: 0 0 0 4px !important;
            min-width: 30px;
            padding: 0 8px !important;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page.active {
            background: #0d7c73 !important;
            border-color: #0d7c73 !important;
            color: #fff !important;
        }

        /* First/Prev/Next/Last render as chevrons. Tabulator sets aria-label and title on each
           of these from its localization, so hiding the label text keeps them announced.
           The glyph is a mask painted with currentColor, so it tracks the button's own colour
           (including the :disabled grey) without a second copy of each icon. */
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="first"],
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="prev"],
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="next"],
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="last"] {
            font-size: 0 !important;
            padding: 0 !important;
            width: 30px;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="first"]::before,
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="prev"]::before,
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="next"]::before,
        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="last"]::before {
            background-color: currentColor;
            content: "";
            display: block;
            height: 15px;
            -webkit-mask-position: center;
            mask-position: center;
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            -webkit-mask-size: 15px 15px;
            mask-size: 15px 15px;
            width: 15px;
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="first"]::before {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m11 17-5-5 5-5'/%3E%3Cpath d='m18 17-5-5 5-5'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m11 17-5-5 5-5'/%3E%3Cpath d='m18 17-5-5 5-5'/%3E%3C/svg%3E");
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="prev"]::before {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m15 18-6-6 6-6'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m15 18-6-6 6-6'/%3E%3C/svg%3E");
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="next"]::before {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m9 18 6-6-6-6'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m9 18 6-6-6-6'/%3E%3C/svg%3E");
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page[data-page="last"]::before {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 17 5-5-5-5'/%3E%3Cpath d='m13 17 5-5-5-5'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 17 5-5-5-5'/%3E%3Cpath d='m13 17 5-5-5-5'/%3E%3C/svg%3E");
        }

        #tutorModuleDetails .tm-table .tabulator-footer .tabulator-page:disabled {
            color: #c3ccc9 !important;
            opacity: 1 !important;
        }

        #tutorModuleDetails .dropdown-menu {
            border: 1px solid var(--tm-line) !important;
            border-radius: 12px !important;
            box-shadow: 0 18px 40px -12px rgba(11, 35, 32, .28) !important;
            overflow: hidden;
        }

        #tutorModuleDetails .dropdown-content {
            padding: 6px !important;
        }

        #tutorModuleDetails .dropdown-item {
            border-radius: 8px !important;
            color: #3f524f !important;
            font-size: 13px !important;
            font-weight: 600 !important;
        }

        #tutorModuleDetails .tm-empty {
            color: #96876a;
            font-size: 13px;
            padding: 38px 24px;
            text-align: center;
        }

        #tutorModuleDetails .tm-mono {
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
        }

        body.tutor-module-body .modal .modal-content {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 30px 80px -20px rgba(11, 35, 32, .5);
            overflow: hidden;
        }

        body.tutor-module-body .modal .modal-header {
            border-bottom: 1px solid #f0ede3;
            padding: 20px 24px;
        }

        body.tutor-module-body .modal .modal-header h2,
        body.tutor-module-body .modal .modal-body .title,
        body.tutor-module-body .modal .modal-body .confModTitle,
        body.tutor-module-body .modal .modal-body .successModalTitle,
        body.tutor-module-body .modal .modal-body .warningModalTitle {
            color: var(--tm-deep, #0f2d2a);
            font-family: "IBM Plex Serif", Georgia, serif;
            font-weight: 600;
        }

        body.tutor-module-body .modal .modal-body {
            color: #3f524f;
            font-family: "IBM Plex Sans", system-ui, sans-serif;
        }

        body.tutor-module-body .modal .modal-footer {
            background: #fbfaf6;
            border-top: 1px solid #f0ede3;
            padding: 16px 24px;
        }

        body.tutor-module-body .modal .btn {
            align-items: center;
            border-radius: 9px !important;
            display: inline-flex;
            font-size: 13px !important;
            font-weight: 600 !important;
            gap: 7px;
            justify-content: center;
            min-height: 38px;
            padding: 8px 14px !important;
        }

        body.tutor-module-body .modal .btn-primary {
            background: rgba(13, 124, 115, .10) !important;
            border: 1px solid rgba(13, 124, 115, .22) !important;
            color: #0a655d !important;
        }

        body.tutor-module-body .modal .btn-outline-secondary,
        body.tutor-module-body .modal .btn-secondary {
            background: #f4f0e6 !important;
            border: 1px solid #e4dcc7 !important;
            color: #5a6f6c !important;
        }

        body.tutor-module-body .modal .btn-danger {
            background: rgba(179, 38, 30, .10) !important;
            border: 1px solid rgba(179, 38, 30, .28) !important;
            color: #b3261e !important;
        }

        body.tutor-module-body .modal .btn-success,
        body.tutor-module-body .modal .btn-outline-success {
            background: #0d7c73 !important;
            border-color: #0d7c73 !important;
            color: #fff !important;
        }

        body.tutor-module-body .modal .form-control,
        body.tutor-module-body .modal .form-select,
        body.tutor-module-body .modal .tom-select,
        body.tutor-module-body .modal .ts-control,
        body.tutor-module-body .modal textarea {
            /* See the note on #tutorModuleDetails .form-select — the shorthand would drop the
               select chevron that @tailwindcss/forms paints as a background-image. */
            background-color: #f9f7f1 !important;
            border: 1px solid #ded7c6 !important;
            border-radius: 9px !important;
            box-shadow: none !important;
            color: #0f2d2a !important;
            font-size: 13px !important;
            min-height: 38px;
        }

        body.tutor-module-body .tm-modal-icon {
            align-items: center;
            background: rgba(13, 124, 115, .12);
            border-radius: 11px;
            color: #0d7c73;
            display: inline-flex;
            height: 40px;
            justify-content: center;
            width: 40px;
        }

        body.tutor-module-body .dropzone {
            background: #fbfcfb !important;
            border: 2px dashed #cdd6cf !important;
            border-radius: 16px !important;
            color: #3a4a47;
            min-height: 170px;
        }

        body.tutor-module-body .tm-radio-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        body.tutor-module-body .tm-radio-stack label {
            align-items: center;
            border: 1px solid #e6e8e3;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            gap: 12px;
            padding: 13px 16px;
        }

        body.tutor-module-body .tm-radio-stack label:has(input:checked) {
            background: rgba(13, 124, 115, .06);
            border-color: #0d7c73;
        }

        body.tutor-module-body .tm-radio-stack span {
            align-items: center;
            color: #12312e;
            display: inline-flex;
            font-size: 13.5px;
            font-weight: 600;
            gap: 8px;
        }

        @keyframes tmFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 980px) {
            #tutorModuleDetails {
                padding: 0;
            }

            #tutorModuleDetails .tm-stat-grid {
                grid-template-columns: 1fr;
            }

            #tutorModuleDetails .tm-team {
                flex: 1 1 100%;
            }
        }
    </style>
@endsection

@php
    $initialsFor = function ($name) {
        $clean = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+/i', '', trim((string) $name));
        $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
        return strtoupper(substr($parts[0] ?? 'L', 0, 1) . substr($parts[count($parts) - 1] ?? 'C', 0, 1));
    };
    $moduleTitle = $data->module ?? ($plan->creations->module_name ?? 'Module');
    $courseTitle = $data->course ?? ($plan->course->name ?? '');
    $termTitle = $data->term_name ?? ($plan->attenTerm->name ?? '');
    $classType = isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : ($data->classType ?? '');
    $tutorName = $plan->tutor->employee->full_name ?? $data->tutor ?? null;
    $personalTutorName = null;

    if (isset($plan->class_type) && $plan->class_type == 'Tutorial' && $plan->personal_tutor_id > 0) {
        $personalTutorName = $plan->personalTutor->employee->full_name ?? $data->personalTutor ?? null;
    } elseif (isset($plan->class_type) && $plan->class_type != 'Tutorial' && isset($plan->tutorial->personal_tutor_id) && $plan->tutorial->personal_tutor_id > 0) {
        $personalTutorName = $plan->tutorial->personalTutor->employee->full_name ?? $data->personalTutor ?? null;
    }

    $showCourseContent = $plan->class_type != 'Tutorial' && $plan->class_type != 'Seminar';
    $defaultDatesActive = !$showCourseContent;
@endphp

@section('subcontent')
<div id="tutorModuleDetails">
    <div class="tm-shell">
        <div class="tm-hero">
            <div class="tm-hero-inner">
                <div class="min-w-0">
                    <div class="tm-kicker">{{ $courseTitle }}{{ !empty($termTitle) ? ' - '.$termTitle : '' }}</div>
                    <h1 class="tm-title">{{ $moduleTitle }} @if(!empty($classType))<span>({{ $classType }})</span>@endif</h1>
                    <div class="tm-badges">
                        <span class="tm-badge is-gold">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>{{ $data->group }}
                        </span>
                        <span class="tm-badge is-green">
                            <span class="tm-badge-dot"></span>{{ $classType ?: 'Class' }}
                        </span>
                    </div>
                </div>

                <div class="tm-team">
                    <div class="tm-team-label">Teaching Team</div>
                    <div class="tm-team-list">
                        @if($plan->tutor_id > 0 && !empty($tutorName))
                            <div class="tm-person">
                                <span class="tm-avatar">
                                    <img alt="{{ $tutorName }}" src="{{ (isset($plan->tutor->employee->photo_url) ? $plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                </span>
                                <span>
                                    <span class="tm-person-name">{{ $tutorName }}</span>
                                    <span class="tm-person-role">Tutor</span>
                                </span>
                            </div>
                        @endif

                        @if(!empty($personalTutorName))
                            <div class="tm-person">
                                <span class="tm-avatar" style="background:#c94f7c;">
                                    @if(isset($plan->personalTutor->employee->photo_url) || isset($plan->tutorial->personalTutor->employee->photo_url))
                                        <img alt="{{ $personalTutorName }}" src="{{ $plan->personalTutor->employee->photo_url ?? $plan->tutorial->personalTutor->employee->photo_url ?? asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    @else
                                        {{ $initialsFor($personalTutorName) }}
                                    @endif
                                </span>
                                <span>
                                    <span class="tm-person-name">{{ $personalTutorName }}</span>
                                    <span class="tm-person-role">Personal Tutor</span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tm-stat-grid">
                <div class="tm-stat">
                    <span class="tm-stat-icon"><i data-lucide="layers" class="w-5 h-5"></i></span>
                    <span>
                        <span class="tm-stat-label">Group</span>
                        <span class="tm-stat-value">{{ $data->group }}</span>
                    </span>
                </div>
                <div class="tm-stat">
                    <span class="tm-stat-icon is-green"><i data-lucide="users" class="w-5 h-5"></i></span>
                    <span>
                        <span class="tm-stat-label">Students</span>
                        <span class="tm-stat-value">{{ $studentCount }}</span>
                    </span>
                </div>
                <div class="tm-stat">
                    <span class="tm-stat-icon is-ink"><i data-lucide="calendar-days" class="w-5 h-5"></i></span>
                    <span>
                        <span class="tm-stat-label">Class Type</span>
                        <span class="tm-stat-value">{{ $classType }}</span>
                    </span>
                </div>
            </div>
        </div>

        <ul class="tm-tabs nav nav-link-tabs" role="tablist">
            @if($showCourseContent)
                <li id="availabilty-tab" class="nav-item" role="presentation">
                    <a href="javascript:void(0);" class="nav-link active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab">
                        <i data-lucide="book-open" class="w-4 h-4"></i> Course Content
                    </a>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <a href="https://teams.microsoft.com/v2/" class="nav-link">
                    <img class="h-4 w-4 object-contain" src="{{ asset('build/assets/images/mircrosoft-team-logo.png') }}" alt=""> Microsoft Teams
                </a>
            </li>
            <li id="class-dates-tab" class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link {{ $defaultDatesActive ? 'active' : '' }}" data-tw-target="#class-dates" aria-controls="class-dates" aria-selected="{{ $defaultDatesActive ? 'true' : 'false' }}" role="tab">
                    <i data-lucide="calendar" class="w-4 h-4"></i> Class Dates
                </a>
            </li>
            <li id="participants-tab" class="nav-item" role="presentation">
                <a href="javascript:void(0);" class="nav-link" data-tw-target="#participants" aria-controls="participants" aria-selected="false" role="tab">
                    <i data-lucide="users" class="w-4 h-4"></i> Participants
                </a>
            </li>
            @if($showCourseContent)
                @if(isset(auth()->user()->priv()['assessment']) && auth()->user()->priv()['assessment'] == 1)
                    <li id="assessment-tab" class="nav-item" role="presentation">
                        <a href="javascript:void(0);" class="nav-link" data-tw-target="#assessment" aria-controls="assessment" aria-selected="false" role="tab">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i> Assessment
                        </a>
                    </li>
                @endif
                @if(isset(auth()->user()->priv()['analytics']) && auth()->user()->priv()['analytics'] == 1)
                    <li id="analytics-tab" class="nav-item" role="presentation">
                        <a href="javascript:void(0);" class="nav-link" data-tw-target="#analytics" aria-controls="analytics" aria-selected="false" role="tab">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Analytics
                        </a>
                    </li>
                @endif
            @endif
            @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
                <li id="submission-tab" class="nav-item dropdown" role="presentation" data-tw-placement="bottom-start">
                    <a href="javascript:void(0);" class="nav-link tm-tab-toggle" data-tw-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                        <i data-lucide="file-check-2" class="w-4 h-4"></i> Result Submission
                        <i data-lucide="chevron-down" class="w-4 h-4 tm-tab-caret"></i>
                    </a>
                    <div class="dropdown-menu tm-tab-menu">
                        <ul class="dropdown-content">
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item tm-subtab active" data-tm-pane="submission-result">
                                    <span class="tm-subtab-icon"><i data-lucide="layers" class="w-4 h-4"></i></span>
                                    <span class="tm-subtab-text">
                                        <strong>Result Submission</strong>
                                        <small>Upload &amp; submit results</small>
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="dropdown-item tm-subtab" data-tm-pane="submission-log">
                                    <span class="tm-subtab-icon"><i data-lucide="history" class="w-4 h-4"></i></span>
                                    <span class="tm-subtab-text">
                                        <strong>Submission Log</strong>
                                        <small>History of submissions</small>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>

    <div class="tm-content tab-content">
        @if($showCourseContent)
            <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
                <div class="tm-panel tm-panel-pad">
                    @include('pages.tutor.module.includes.activity')
                </div>
            </div>
        @endif

        <div id="class-dates" class="tab-pane {{ $defaultDatesActive ? 'active' : '' }}" role="tabpanel" aria-labelledby="class-dates-tab">
            @include('pages.tutor.module.includes.dates')
        </div>

        <div id="participants" class="tab-pane" role="tabpanel" aria-labelledby="participants-tab">
            <div class="tm-panel">
                @include('pages.tutor.module.includes.studentlist')
            </div>
        </div>

        @if($showCourseContent)
            <div id="assessment" class="tab-pane" role="tabpanel" aria-labelledby="assessment-tab">
                <div class="tm-panel">
                    @include('pages.tutor.module.includes.assessments')
                </div>
            </div>
            <div id="analytics" class="tab-pane" role="tabpanel" aria-labelledby="analytics-tab">
                @include('pages.tutor.module.includes.analytics')
            </div>
        @endif

        @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
            @include('pages.tutor.module.includes.submission')
        @endif
    </div>
</div>
@include('pages.tutor.module.component.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks.js')
    @vite('resources/js/plan-tasks-analytics.js')
    @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
        @vite('resources/js/results-submission.js')
        <script>
            // The Result Submission tab is a dropdown, not a [role="tab"], so the theme's tab
            // plugin never sees it: its panes and its active state are switched here instead.
            (function () {
                const root = document.getElementById('tutorModuleDetails');
                const toggle = root && root.querySelector('.tm-tab-toggle');
                if (!toggle) return;

                const content = root.querySelector('.tm-content');

                function setToggleActive(active) {
                    toggle.classList.toggle('active', active);
                    toggle.setAttribute('aria-selected', active ? 'true' : 'false');
                }

                function activate(item) {
                    root.querySelectorAll('.tm-tabs [role="tab"]').forEach(function (tab) {
                        tab.classList.remove('active');
                        tab.setAttribute('aria-selected', 'false');
                    });
                    setToggleActive(true);

                    content.querySelectorAll(':scope > .tab-pane').forEach(function (pane) {
                        pane.classList.remove('active');
                        pane.removeAttribute('style');
                    });
                    const pane = document.getElementById(item.dataset.tmPane);
                    if (pane) pane.classList.add('active');

                    document.querySelectorAll('.tm-tab-menu .tm-subtab').forEach(function (other) {
                        other.classList.toggle('active', other === item);
                    });

                    const dropdown = window.tailwind && window.tailwind.Dropdown
                        ? window.tailwind.Dropdown.getInstance(toggle)
                        : null;
                    if (dropdown) dropdown.hide();
                }

                // Delegated from document because an open menu is relocated to <body>.
                document.addEventListener('click', function (event) {
                    const item = event.target.closest('.tm-tab-menu .tm-subtab');
                    if (item) {
                        activate(item);
                        return;
                    }
                    if (event.target.closest('.tm-tabs [role="tab"]')) {
                        setToggleActive(false);
                    }
                });
            })();
        </script>
    @endif
@endsection
