@extends('../layout/' . $layout)

@section('body_class', 'pt-dashboard-body')

@section('subhead')
    <title>{{ $title }}</title>
    <style>
        body.pt-dashboard-body {
            --pt-ink: #12312e;
            --pt-deep: #0b2320;
            --pt-green: #0d7c73;
            --pt-green-dark: #0a5d57;
            --pt-gold: #c6a44e;
            --pt-muted: #8497a4;
            --pt-line: #e6e1d3;
            --pt-soft: #faf9f4;
            --pt-cream: #f6f3ea;
            --pt-danger: #b3261e;
            --pt-warning: #9a5411;
            background: #eef1f0;
        }

        body.pt-dashboard-body .content--top-nav {
            background: #eef1f0;
            padding: 0;
        }

        #personalTutorDashboard {
            --pt-ink: #12312e;
            --pt-deep: #0b2320;
            --pt-green: #0d7c73;
            --pt-green-dark: #0a5d57;
            --pt-gold: #c6a44e;
            --pt-muted: #8497a4;
            --pt-line: #e6e1d3;
            --pt-soft: #faf9f4;
            --pt-cream: #f6f3ea;
            --pt-danger: #b3261e;
            --pt-warning: #9a5411;
            color: var(--pt-ink);
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            margin: 0 auto;
            max-width: 1452px;
            padding: 26px 26px 46px;
        }

        #personalTutorDashboard *,
        #personalTutorDashboard *::before,
        #personalTutorDashboard *::after {
            box-sizing: border-box;
        }

        #personalTutorDashboard a {
            color: inherit;
            text-decoration: none;
        }

        #personalTutorDashboard .pt-card {
            background: #fff;
            border: 1px solid var(--pt-line);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
        }

        #personalTutorDashboard .pt-welcome {
            align-items: center;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin-bottom: 18px;
            padding: 18px 22px;
        }

        #personalTutorDashboard .pt-eyebrow {
            color: #a1926b;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        #personalTutorDashboard .pt-title {
            color: #0f2d2a;
            font-family: "Newsreader", Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.05;
            margin: 0;
        }

        #personalTutorDashboard .pt-term-selector {
            min-width: 0;
        }

        #personalTutorDashboard .pt-term-selector .dropdown {
            display: flex;
            justify-content: flex-end;
        }

        #personalTutorDashboard .pt-term-button {
            align-items: center;
            background: var(--pt-green);
            border: 0;
            border-radius: 10px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 12.5px;
            font-weight: 700;
            gap: 9px;
            justify-content: space-between;
            min-height: 40px;
            min-width: 132px;
            padding: 0 14px;
            width: auto;
        }

        #personalTutorDashboard .pt-term-button svg {
            flex: 0 0 auto;
        }

        #personalTutorDashboard .pt-term-label {
            white-space: nowrap;
        }

        #personalTutorDashboard .pt-term-chev {
            transition: transform .15s ease;
        }

        #personalTutorDashboard .dropdown.show .pt-term-chev,
        #personalTutorDashboard #ptTermDropdown[aria-expanded="true"] .pt-term-chev {
            transform: rotate(180deg);
        }

        #personalTutorDashboard .pt-term-button:hover {
            background: var(--pt-green-dark);
        }

        #personalTutorDashboard .pt-dropdown-panel {
            background: #fff;
            border: 1px solid var(--pt-line);
            border-radius: 12px;
            box-shadow: 0 18px 40px -14px rgba(16, 49, 46, .32);
            max-height: 264px;
            overflow: auto;
            padding: 10px 8px;
            width: 205px;
        }

        #personalTutorDashboard .pt-dropdown-panel::-webkit-scrollbar,
        #personalTutorDashboard .autoFillDropdown::-webkit-scrollbar {
            width: 9px;
        }

        #personalTutorDashboard .pt-dropdown-panel::-webkit-scrollbar-track,
        #personalTutorDashboard .autoFillDropdown::-webkit-scrollbar-track {
            background: #f4f2eb;
            border-radius: 999px;
        }

        #personalTutorDashboard .pt-dropdown-panel::-webkit-scrollbar-thumb,
        #personalTutorDashboard .autoFillDropdown::-webkit-scrollbar-thumb {
            background: #c6c6c6;
            border-radius: 999px;
        }

        #personalTutorDashboard .pt-dropdown-heading {
            color: #a1926b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            padding: 2px 8px 8px;
            text-transform: uppercase;
        }

        #personalTutorDashboard .pt-dropdown-panel .dropdown-item {
            align-items: center;
            border-radius: 8px;
            color: #2c3f3c;
            display: flex;
            font-size: 12.5px;
            font-weight: 500;
            justify-content: space-between;
            padding: 9px 10px;
        }

        #personalTutorDashboard .pt-dropdown-panel .dropdown-item svg {
            display: none;
            flex: 0 0 auto;
        }

        #personalTutorDashboard .pt-dropdown-panel .dropdown-item:hover,
        #personalTutorDashboard .pt-dropdown-panel .dropdown-item.is-active {
            background: #e4f1ee;
            color: var(--pt-green);
            font-weight: 700;
        }

        #personalTutorDashboard .pt-dropdown-panel .dropdown-item.is-active svg {
            display: block;
        }

        #personalTutorDashboard .pt-kpi-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin-bottom: 20px;
            position: relative;
        }

        #personalTutorDashboard .pt-kpi-card {
            background: #fff;
            border: 1px solid var(--pt-line);
            border-radius: 14px;
            box-shadow: 0 1px 2px rgba(16, 49, 46, .04);
            min-height: 132px;
            padding: 16px 18px;
        }

        #personalTutorDashboard .pt-kpi-label {
            align-items: center;
            color: #98a7a4;
            display: flex;
            font-size: 10.5px;
            font-weight: 700;
            gap: 9px;
            letter-spacing: .07em;
            margin-bottom: 11px;
            text-transform: uppercase;
        }

        #personalTutorDashboard .pt-kpi-icon {
            align-items: center;
            background: #e4f1ee;
            border-radius: 9px;
            color: var(--pt-green);
            display: inline-flex;
            flex: 0 0 32px;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        #personalTutorDashboard .pt-kpi-icon.is-gold {
            background: #f3ecd8;
            color: #a1802f;
        }

        #personalTutorDashboard .pt-kpi-icon.is-warning {
            background: #fbecda;
            color: var(--pt-warning);
        }

        #personalTutorDashboard .pt-kpi-icon.is-danger {
            background: #fbe6e3;
            color: var(--pt-danger);
        }

        #personalTutorDashboard .pt-kpi-value {
            color: #0f2d2a;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 26px;
            font-weight: 700;
            line-height: 1;
        }

        #personalTutorDashboard .pt-kpi-value.is-danger {
            color: var(--pt-danger);
        }

        #personalTutorDashboard .pt-kpi-value.is-warning {
            color: var(--pt-warning);
        }

        #personalTutorDashboard .pt-kpi-note {
            color: #8a9b98;
            font-size: 11px;
            line-height: 1.35;
            margin-top: 6px;
        }

        #personalTutorDashboard .pt-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 7px;
        }

        #personalTutorDashboard .pt-chip {
            background: #f3ecd8;
            border-radius: 6px;
            color: #a1802f;
            display: inline-flex;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1;
            padding: 4px 7px;
        }

        #personalTutorDashboard .pt-progress {
            background: #eef0ea;
            border-radius: 999px;
            height: 6px;
            margin-top: 8px;
            overflow: hidden;
        }

        #personalTutorDashboard .pt-progress span {
            background: #c07d24;
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        #personalTutorDashboard .pt-layout-grid {
            align-items: start;
            display: grid;
            gap: 20px;
            grid-template-columns: 330px minmax(0, 1fr);
        }

        #personalTutorDashboard .pt-left-rail,
        #personalTutorDashboard .pt-main {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        #personalTutorDashboard .pt-profile-card {
            padding: 22px;
            text-align: center;
        }

        #personalTutorDashboard .pt-avatar {
            align-items: center;
            background: radial-gradient(circle at 50% 32%, #1c4d47, #0b2320);
            border: 3px solid var(--pt-gold);
            border-radius: 999px;
            box-shadow: 0 8px 22px -12px rgba(11, 35, 32, .7);
            color: #e8d59a;
            display: inline-flex;
            font-family: "Newsreader", Georgia, serif;
            font-size: 28px;
            font-weight: 600;
            height: 88px;
            justify-content: center;
            overflow: hidden;
            width: 88px;
        }

        #personalTutorDashboard .pt-avatar img {
            display: block;
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        #personalTutorDashboard .pt-profile-name {
            color: #0f2d2a;
            font-family: "Newsreader", Georgia, serif;
            font-size: 19px;
            font-weight: 600;
            margin-top: 13px;
        }

        #personalTutorDashboard .pt-profile-email {
            color: #8a9b98;
            font-size: 12px;
            margin-top: 2px;
            overflow-wrap: anywhere;
        }

        #personalTutorDashboard .pt-role-pill {
            align-items: center;
            background: #f3ecd8;
            border: 1px solid #e4dcc7;
            border-radius: 999px;
            color: #a1802f;
            display: inline-flex;
            font-size: 10.5px;
            font-weight: 700;
            gap: 6px;
            letter-spacing: .06em;
            margin-top: 11px;
            padding: 4px 11px;
            text-transform: uppercase;
        }

        #personalTutorDashboard .pt-role-pill span {
            background: var(--pt-gold);
            border-radius: 999px;
            height: 5px;
            width: 5px;
        }

        #personalTutorDashboard .pt-search-form {
            display: flex;
            margin-top: 16px;
            position: relative;
        }

        #personalTutorDashboard .autoCompleteField {
            position: relative;
            width: 100%;
        }

        #personalTutorDashboard .pt-input,
        #personalTutorDashboard .pt-native-select {
            background: var(--pt-soft);
            border: 1px solid #e4dcc7;
            border-radius: 11px;
            color: #0f2d2a;
            font-size: 12.5px;
            height: 42px;
            outline: 0;
            padding: 0 42px 0 38px;
            width: 100%;
        }

        #personalTutorDashboard .pt-search-form .pt-input {
            background: #fff;
            border-color: var(--pt-gold);
            height: 39px;
            padding: 0 14px 0 38px;
        }

        #personalTutorDashboard .pt-native-select {
            appearance: auto;
            cursor: pointer;
            padding-left: 34px;
        }

        #personalTutorDashboard .pt-input:focus,
        #personalTutorDashboard .pt-native-select:focus {
            border-color: var(--pt-gold);
            box-shadow: none;
        }

        #personalTutorDashboard .pt-search-icon {
            color: #a1926b;
            left: 12px;
            position: absolute;
            top: 13px;
            z-index: 2;
        }

        #personalTutorDashboard .pt-search-button {
            align-items: center;
            background: var(--pt-green);
            border: 0;
            border-radius: 9px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            height: 34px;
            justify-content: center;
            position: absolute;
            right: 4px;
            top: 4px;
            width: 34px;
            z-index: 3;
        }

        #personalTutorDashboard .pt-profile-card .pt-search-button {
            display: none;
        }

        #personalTutorDashboard .pt-search-button:disabled {
            cursor: not-allowed;
            opacity: .6;
        }

        #personalTutorDashboard .autoFillDropdown {
            background: #fff;
            border: 1px solid var(--pt-line);
            border-radius: 12px;
            box-shadow: 0 20px 46px -16px rgba(16, 49, 46, .36);
            display: none;
            left: 0;
            list-style: none;
            margin: 7px 0 0;
            max-height: 286px;
            overflow: auto;
            padding: 9px 8px;
            position: absolute;
            right: 0;
            text-align: left;
            top: 100%;
            z-index: 30;
        }

        #personalTutorDashboard .pt-search-results-head {
            align-items: center;
            color: #a1926b;
            display: flex;
            font-size: 10px;
            font-weight: 800;
            justify-content: space-between;
            letter-spacing: .08em;
            padding: 0 8px 7px;
            text-transform: uppercase;
        }

        #personalTutorDashboard .pt-search-count {
            align-items: center;
            background: #dff2ec;
            border-radius: 999px;
            color: var(--pt-green);
            display: inline-flex;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 10px;
            height: 20px;
            justify-content: center;
            min-width: 22px;
            padding: 0 7px;
        }

        #personalTutorDashboard .autoFillDropdown a {
            align-items: center;
            border-radius: 9px;
            color: #14322f;
            display: flex;
            gap: 10px;
            font-size: 12px;
            font-weight: 600;
            min-height: 48px;
            padding: 7px 8px;
        }

        #personalTutorDashboard .autoFillDropdown a:hover {
            background: #f7f4ea;
        }

        #personalTutorDashboard .autoFillDropdown a.disable {
            color: #9b8f78;
            cursor: default;
        }

        #personalTutorDashboard .pt-result-avatar {
            align-items: center;
            background: #e6ecf5;
            border-radius: 999px;
            color: #2f5fa1;
            display: inline-flex;
            flex: 0 0 32px;
            font-size: 11px;
            font-weight: 800;
            height: 32px;
            justify-content: center;
            width: 32px;
        }

        #personalTutorDashboard .pt-result-body {
            min-width: 0;
        }

        #personalTutorDashboard .pt-result-name {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #personalTutorDashboard .pt-result-reg {
            color: #a1926b;
            display: block;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 10.5px;
            margin-top: 1px;
        }

        #personalTutorDashboard .pt-result-chevron {
            color: #b8a36b;
            flex: 0 0 auto;
            font-size: 22px;
            line-height: 1;
            margin-left: auto;
        }

        #personalTutorDashboard .pt-panel {
            overflow: hidden;
        }

        #personalTutorDashboard .pt-panel-header {
            align-items: center;
            border-bottom: 1px solid #eef0ea;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: space-between;
            padding: 16px 18px;
        }

        #personalTutorDashboard .pt-today-panel .pt-panel-header {
            border-bottom: 1px solid #eef0ea;
            flex-wrap: nowrap;
            gap: 10px;
            padding: 16px 14px 10px;
        }

        #personalTutorDashboard .pt-panel-title {
            color: #0f2d2a;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0;
            margin: 0;
        }

        #personalTutorDashboard .pt-panel-title.is-large {
            font-size: 16px;
        }

        #personalTutorDashboard .pt-toolbar {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
        }

        #personalTutorDashboard .pt-field-shell {
            align-items: center;
            background: #fff;
            border: 1px solid #e4dcc7;
            border-radius: 10px;
            color: #4a5f5d;
            display: inline-flex;
            gap: 7px;
            min-height: 38px;
            padding: 0 11px;
            position: relative;
        }

        #personalTutorDashboard .pt-field-shell .pt-input,
        #personalTutorDashboard .pt-field-shell .pt-native-select {
            background: transparent;
            border: 0;
            border-radius: 0;
            height: 36px;
            max-width: 148px;
            padding: 0;
        }

        #personalTutorDashboard .pt-field-shell svg {
            color: #a1926b;
            flex: 0 0 auto;
        }

        #personalTutorDashboard .pt-today-date {
            flex: 0 0 150px;
            gap: 6px;
            min-height: 32px;
            padding: 0 9px;
        }

        #personalTutorDashboard .pt-today-date .pt-input {
            font-size: 12px;
            height: 30px;
            max-width: none;
            width: 100%;
        }

        #personalTutorDashboard .pt-class-list,
        #personalTutorDashboard .pt-module-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 14px;
            position: relative;
        }

        #personalTutorDashboard .pt-today-panel .pt-class-list {
            padding: 14px 14px 16px;
        }

        #personalTutorDashboard .pt-class-card,
        #personalTutorDashboard .pt-module-item,
        #personalTutorDashboard #todaysClassListWrap .box,
        #personalTutorDashboard #personalTutormoduleList .box {
            background: var(--pt-soft);
            border: 1px solid #eceadf;
            border-radius: 13px;
            box-shadow: none;
            padding: 13px;
        }

        #personalTutorDashboard .pt-class-card.is-live {
            background: #f9ece5;
            border-color: #eccbbd;
        }

        #personalTutorDashboard .pt-class-card.is-live .pt-class-time {
            color: #b66138;
        }

        #personalTutorDashboard #todaysClassListWrap > .intro-x {
            display: block !important;
            margin-bottom: 0;
        }

        #personalTutorDashboard #todaysClassListWrap > .intro-x > div:first-child {
            display: none;
        }

        #personalTutorDashboard #todaysClassListWrap > .intro-x > .box {
            margin-left: 0;
            width: 100%;
        }

        #personalTutorDashboard .pt-class-head,
        #personalTutorDashboard .pt-module-head {
            align-items: flex-start;
            display: flex;
            gap: 8px;
            justify-content: space-between;
        }

        #personalTutorDashboard .pt-class-title,
        #personalTutorDashboard .pt-module-title {
            color: #14322f;
            font-size: 12.5px;
            font-weight: 700;
            line-height: 1.35;
        }

        #personalTutorDashboard .pt-class-time,
        #personalTutorDashboard .pt-mono {
            color: #8497a4;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        #personalTutorDashboard .pt-class-sub,
        #personalTutorDashboard .pt-module-sub {
            color: #8497a4;
            font-size: 10.5px;
            line-height: 1.35;
            margin-top: 3px;
        }

        #personalTutorDashboard .pt-action,
        #personalTutorDashboard .start-punch.btn,
        #personalTutorDashboard .pt-table-action {
            align-items: center;
            background: var(--pt-green);
            border: 1px solid var(--pt-green);
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-size: 11.5px;
            font-weight: 700;
            gap: 7px;
            justify-content: center;
            line-height: 1;
            margin-top: 11px;
            min-height: 34px;
            padding: 8px 13px;
        }

        #personalTutorDashboard .pt-action:hover,
        #personalTutorDashboard .start-punch.btn:hover,
        #personalTutorDashboard .pt-table-action:hover {
            background: var(--pt-green-dark);
            border-color: var(--pt-green-dark);
            color: #fff;
        }

        #personalTutorDashboard .pt-action.is-danger,
        #personalTutorDashboard .btn-danger.start-punch {
            background: var(--pt-danger);
            border-color: var(--pt-danger);
        }

        #personalTutorDashboard .pt-class-alert {
            align-items: flex-start;
            background: #efd1cf;
            border: 1px solid #e8c5c1;
            border-radius: 6px;
            color: #c01818;
            display: flex;
            font-size: 12.5px;
            gap: 10px;
            line-height: 1.35;
            margin-top: 13px;
            padding: 16px 18px;
        }

        #personalTutorDashboard .pt-class-alert svg {
            color: #c01818;
            flex: 0 0 auto;
            height: 13px;
            margin-top: 3px;
            width: 13px;
        }

        body.pt-dashboard-body #addNoteModal .modal-dialog,
        body.pt-dashboard-body #smsSMSModal .modal-dialog {
            height: 100vh;
            margin: 0 0 0 auto;
            max-width: min(420px, 100vw);
            padding: 0;
            position: absolute;
            right: 0;
            top: 0;
            transform: translateX(100%) !important;
            transition: transform .2s ease;
            width: min(420px, 100vw);
        }

        body.pt-dashboard-body #addNoteModal.show .modal-dialog,
        body.pt-dashboard-body #smsSMSModal.show .modal-dialog {
            transform: translateX(0) !important;
        }

        body.pt-dashboard-body #addNoteModal .modal-content,
        body.pt-dashboard-body #smsSMSModal .modal-content {
            border: 0;
            border-radius: 0;
            box-shadow: -24px 0 60px -34px rgba(11, 35, 32, .65);
            height: 100vh;
            overflow: hidden;
        }

        body.pt-dashboard-body #addNoteForm,
        body.pt-dashboard-body #smsSMSForm,
        body.pt-dashboard-body .pt-note-drawer {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }

        body.pt-dashboard-body .pt-note-header {
            background: #0c332e;
            color: #fff;
            padding: 22px;
        }

        body.pt-dashboard-body .pt-note-eyebrow {
            color: #9fb8b3;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .12em;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        body.pt-dashboard-body .pt-note-close {
            align-items: center;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 9px;
            color: #dce8e6;
            display: inline-flex;
            height: 32px;
            justify-content: center;
            position: absolute;
            right: 16px;
            top: 16px;
            width: 32px;
        }

        body.pt-dashboard-body .pt-note-student {
            align-items: center;
            display: flex;
            gap: 14px;
            padding-right: 38px;
        }

        body.pt-dashboard-body .pt-note-avatar {
            align-items: center;
            background: #f4e6ec;
            border-radius: 999px;
            color: #a13f6b;
            display: inline-flex;
            flex: 0 0 46px;
            font-size: 13px;
            font-weight: 800;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        body.pt-dashboard-body .pt-note-student-name {
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.2;
        }

        body.pt-dashboard-body .pt-note-student-meta {
            color: #a9c0bc;
            font-size: 11.5px;
            margin-top: 2px;
        }

        body.pt-dashboard-body .pt-note-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 20px 22px 26px;
        }

        body.pt-dashboard-body .pt-note-section {
            margin-bottom: 18px;
        }

        body.pt-dashboard-body .pt-note-label {
            color: #9aa7a3;
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 9px;
            text-transform: uppercase;
        }

        body.pt-dashboard-body .pt-note-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr 1fr;
        }

        body.pt-dashboard-body .pt-note-field {
            background: #fff;
            border: 1px solid #d8e0de;
            border-radius: 10px;
            color: #12312e;
            font-size: 12.5px;
            min-height: 40px;
            padding: 0 12px;
            width: 100%;
        }

        body.pt-dashboard-body .pt-note-field:focus {
            border-color: var(--pt-green);
            box-shadow: 0 0 0 3px rgba(13, 124, 115, .12);
        }

        body.pt-dashboard-body textarea.pt-note-field {
            min-height: 150px;
            padding: 12px;
            resize: vertical;
        }

        body.pt-dashboard-body .pt-note-section .ts-wrapper .ts-control {
            border: 1px solid #d8e0de;
            border-radius: 10px;
            box-shadow: none;
            font-size: 12.5px;
            min-height: 40px;
            padding: 8px 12px;
        }

        body.pt-dashboard-body .pt-note-editor .document-editor__toolbar {
            border: 1px solid #d8e0de;
            border-bottom: 0;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
        }

        body.pt-dashboard-body .pt-note-editor .document-editor__editable-container {
            border: 1px solid #d8e0de;
            border-radius: 0 0 10px 10px;
        }

        body.pt-dashboard-body .pt-note-editor .ck-editor__editable,
        body.pt-dashboard-body .pt-note-editor .document-editor__editable {
            min-height: 180px;
        }

        body.pt-dashboard-body .pt-note-upload {
            align-items: center;
            border: 1px dashed #cbd9d6;
            border-radius: 10px;
            color: #5c6e6b;
            cursor: pointer;
            display: flex;
            font-size: 12px;
            font-weight: 700;
            gap: 8px;
            justify-content: center;
            min-height: 42px;
            padding: 10px 12px;
        }

        body.pt-dashboard-body .pt-note-document-name {
            color: #8a9b98;
            display: block;
            font-size: 11.5px;
            margin-top: 7px;
            overflow-wrap: anywhere;
        }

        body.pt-dashboard-body .pt-note-check {
            align-items: center;
            color: #5c6e6b;
            display: inline-flex;
            font-size: 12px;
            gap: 9px;
        }

        body.pt-dashboard-body .pt-note-check input {
            accent-color: var(--pt-green);
            height: 18px;
            width: 18px;
        }

        body.pt-dashboard-body .pt-sms-counter {
            color: #8a9b98;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 700;
        }

        body.pt-dashboard-body .pt-drawer-alert {
            background: #fdf4f3;
            border-bottom: 1px solid #f0d8d5;
            color: var(--pt-danger);
            font-size: 12px;
            padding: 10px 22px;
        }

        body.pt-dashboard-body .pt-note-footer {
            align-items: center;
            background: #fff;
            border-top: 1px solid #eef0ea;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 16px 22px;
        }

        body.pt-dashboard-body .pt-note-save,
        body.pt-dashboard-body .pt-note-cancel {
            align-items: center;
            border-radius: 9px;
            display: inline-flex;
            font-size: 12.5px;
            font-weight: 800;
            justify-content: center;
            min-height: 40px;
            padding: 10px 16px;
        }

        body.pt-dashboard-body .pt-note-save {
            background: var(--pt-green);
            border: 1px solid var(--pt-green);
            color: #fff;
            flex: 1 1 auto;
        }

        body.pt-dashboard-body .pt-note-cancel {
            background: #fff;
            border: 1px solid #d8e0de;
            color: #5c6e6b;
            flex: 0 0 auto;
        }

        #personalTutorDashboard .pt-module-item {
            align-items: center;
            display: flex;
            gap: 11px;
            padding: 12px;
            transition: background .15s ease;
        }

        #personalTutorDashboard .pt-module-item:hover,
        #personalTutorDashboard #personalTutormoduleList .box:hover {
            background: #faf9f4;
        }

        #personalTutorDashboard .pt-module-icon {
            align-items: center;
            background: #e4f1ee;
            border-radius: 9px;
            color: var(--pt-green);
            display: inline-flex;
            flex: 0 0 34px;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        #personalTutorDashboard .pt-module-count {
            background: #f3ecd8;
            border-radius: 6px;
            color: #a1926b;
            flex: 0 0 auto;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
        }

        #personalTutorDashboard #personalTutormoduleList {
            max-height: 324px;
            overflow-y: auto;
            overscroll-behavior: contain;
            padding-right: 10px;
            scrollbar-gutter: stable;
        }

        #personalTutorDashboard #personalTutormoduleList::-webkit-scrollbar {
            width: 8px;
        }

        #personalTutorDashboard #personalTutormoduleList::-webkit-scrollbar-track {
            background: #f4f2eb;
            border-radius: 999px;
        }

        #personalTutorDashboard #personalTutormoduleList::-webkit-scrollbar-thumb {
            background: #c8c1ac;
            border-radius: 999px;
        }

        #personalTutorDashboard .pt-data-wrap {
            overflow-x: auto;
            position: relative;
        }

        #personalTutorDashboard .pt-data-table {
            border: 0;
            border-collapse: separate;
            border-spacing: 0;
            display: block;
            min-width: 980px;
            width: 100%;
        }

        #personalTutorDashboard .pt-data-table thead,
        #personalTutorDashboard .pt-data-table tbody {
            display: block;
            width: 100%;
        }

        #personalTutorDashboard .pt-data-table tr {
            align-items: center;
            display: grid;
            margin: 0;
            width: 100%;
        }

        #personalTutorDashboard .pt-attendance-table tr {
            grid-template-columns: minmax(310px, 1.9fr) 108px minmax(330px, 2.25fr) 150px;
        }

        #personalTutorDashboard .pt-elearning-table tr {
            grid-template-columns: 126px minmax(270px, 2.3fr) minmax(150px, 1.15fr) 96px minmax(150px, 1.3fr) 154px 20px;
        }

        #personalTutorDashboard .pt-data-table th,
        #personalTutorDashboard .pt-data-table td {
            border: 0;
            display: block;
            line-height: 1.35;
            padding: 15px 20px;
            vertical-align: middle;
        }

        #personalTutorDashboard .pt-data-table thead tr {
            background: var(--pt-soft);
            border-bottom: 1px solid #eef0ea;
        }

        #personalTutorDashboard .pt-data-table th {
            color: #a1926b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            padding-bottom: 11px;
            padding-top: 11px;
            text-align: left;
            text-transform: uppercase;
            white-space: normal;
        }

        #personalTutorDashboard .pt-attendance-table th:last-child,
        #personalTutorDashboard .pt-attendance-table td:last-child {
            text-align: right;
        }

        #personalTutorDashboard .pt-data-table tbody tr {
            background: #fff;
            border-bottom: 1px solid #f3f2ec;
        }

        #personalTutorDashboard .pt-data-table tbody tr:hover {
            background: var(--pt-soft);
        }

        #personalTutorDashboard .pt-data-table td[colspan] {
            grid-column: 1 / -1;
        }

        #personalTutorDashboard .pt-student-cell {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
            padding-right: 14px;
        }

        #personalTutorDashboard .pt-student-avatar {
            align-items: center;
            background: #e4f1ee;
            border-radius: 999px;
            color: #2f5fa1;
            display: inline-flex;
            flex: 0 0 42px;
            font-size: 12px;
            font-weight: 800;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        #personalTutorDashboard .pt-student-name {
            color: #14322f;
            display: inline-block;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.25;
            margin-bottom: 2px;
        }

        #personalTutorDashboard .pt-student-course {
            color: #8497a4;
            font-size: 11px;
            line-height: 1.35;
        }

        #personalTutorDashboard .pt-student-meta {
            color: #a2b2b0;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11px;
            margin-top: 2px;
        }

        #personalTutorDashboard .pt-attendance-value {
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 15px;
            font-weight: 800;
        }

        #personalTutorDashboard .pt-attendance-value.is-low {
            color: #b3261e;
        }

        #personalTutorDashboard .pt-attendance-value.is-mid {
            color: #9a5411;
        }

        #personalTutorDashboard .pt-attendance-value.is-high {
            color: #12735a;
        }

        #personalTutorDashboard .pt-attendance-meter {
            background: #eef0ea;
            border-radius: 999px;
            height: 6px;
            margin-top: 6px;
            max-width: 74px;
            overflow: hidden;
        }

        #personalTutorDashboard .pt-attendance-meter span {
            background: #c0392b;
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        #personalTutorDashboard .pt-attendance-meter span.is-mid {
            background: #c07d24;
        }

        #personalTutorDashboard .pt-attendance-meter span.is-high {
            background: #1f8a5b;
        }

        #personalTutorDashboard .pt-module-stack {
            display: flex;
            flex-direction: column;
            gap: 9px;
            padding-right: 16px;
        }

        #personalTutorDashboard .pt-missed-module-card {
            background: #fdf4f3;
            border: 1px solid #f2d9d5;
            border-left: 4px solid #d7362a;
            border-radius: 10px;
            color: #9a2b23;
            padding: 10px 12px;
        }

        #personalTutorDashboard .pt-missed-module-card.is-attended {
            background: #f1f8f4;
            border-color: #cfe8da;
            border-left-color: #1f9d6c;
            color: #155e43;
        }

        #personalTutorDashboard .pt-missed-title {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.3;
        }

        #personalTutorDashboard .pt-missed-title svg {
            flex: 0 0 auto;
        }

        #personalTutorDashboard .pt-module-code {
            background: #fff;
            border: 1px solid #e0ded4;
            border-radius: 5px;
            color: #5a6f6c;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: .03em;
            padding: 2px 7px;
        }

        #personalTutorDashboard .pt-module-time {
            align-items: center;
            color: #8a9b98;
            display: inline-flex;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 10.5px;
            gap: 5px;
            margin-top: 7px;
        }

        #personalTutorDashboard .pt-attendance-table .addNoteBtn,
        #personalTutorDashboard .pt-attendance-table .addSmsBtn {
            align-items: center;
            background: #fff;
            border: 1px solid #d7dedd;
            border-radius: 8px;
            color: #2c4643;
            display: inline-flex;
            font-size: 0;
            height: 34px;
            justify-content: center;
            margin: 0 0 0 8px;
            width: 34px;
        }

        #personalTutorDashboard .pt-attendance-table .addSmsBtn {
            border-color: #f0d8d5;
            color: var(--pt-danger);
        }

        #personalTutorDashboard .pt-attendance-table .addNoteBtn svg,
        #personalTutorDashboard .pt-attendance-table .addSmsBtn svg {
            display: block;
            height: 16px;
            margin: 0;
            stroke: currentColor;
            width: 16px;
        }

        #personalTutorDashboard .pt-elearning-table td:first-child div:first-child {
            color: #14322f;
            font-size: 12.5px;
            font-weight: 700;
        }

        #personalTutorDashboard .pt-elearning-table td:first-child div:last-child {
            color: #8497a4;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11.5px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(2) a {
            color: #14322f;
            font-size: 13px;
            font-weight: 700;
            white-space: normal;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(2) .text-slate-500 {
            color: #8497a4;
            font-size: 11px;
            white-space: normal;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(2) .btn,
        #personalTutorDashboard .pt-elearning-table td:nth-child(2) .rounded.bg-primary {
            background: #e4f1ee;
            border-radius: 6px;
            color: var(--pt-green);
            display: inline-flex;
            font-size: 9.5px;
            font-weight: 800;
            letter-spacing: .04em;
            margin: 6px 4px 0 0;
            padding: 3px 7px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(3) img {
            height: 30px;
            width: 30px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(3) .font-medium {
            color: #14322f;
            font-size: 12px;
            font-weight: 700;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(5) .btn-rounded {
            align-items: center;
            background: #e4f1ee;
            border: 0;
            border-radius: 8px;
            color: var(--pt-green);
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            height: 26px;
            justify-content: center;
            margin-right: 8px;
            width: 26px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(5) .text-success {
            color: #12735a;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 11.5px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(6) input.class-fileupload {
            height: 1px;
            opacity: 0;
            position: absolute;
            width: 1px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(6) label {
            align-items: center;
            background: #fff;
            border: 1px solid #bfe0d8;
            border-radius: 8px;
            color: #12735a;
            cursor: pointer;
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            justify-content: center;
            margin: 0 0 0 6px;
            min-height: 32px;
            padding: 7px 12px;
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(6) input.class-fileupload[value="No"] + label {
            border-color: #f0d8d5;
            color: var(--pt-danger);
        }

        #personalTutorDashboard .pt-elearning-table td:nth-child(6) input.class-fileupload:checked + label {
            background: var(--pt-green);
            border-color: var(--pt-green);
            color: #fff;
        }

        #personalTutorDashboard .pt-badge {
            align-items: center;
            background: linear-gradient(180deg, #f7edcf, #f0e2b8);
            border: 1px solid #e3d19a;
            border-radius: 8px;
            color: #7a5a12;
            display: inline-flex;
            font-size: 12px;
            font-weight: 800;
            gap: 6px;
            padding: 6px 11px;
        }

        #personalTutorDashboard #undecidedCount {
            align-items: baseline;
            background: #e4f1ee;
            border: 1px solid #c9e6df;
            border-radius: 999px;
            color: var(--pt-green);
            display: inline-flex;
            font-size: 14px;
            font-weight: 800;
            min-height: 34px;
            padding: 6px 12px;
        }

        #personalTutorDashboard .leaveTableLoader {
            align-items: center;
            background: rgba(11, 35, 32, .18);
            border-radius: inherit;
            display: none;
            inset: 0;
            justify-content: center;
            position: absolute;
            z-index: 18;
        }

        #personalTutorDashboard .leaveTableLoader.active {
            display: flex;
        }

        #personalTutorDashboard #studentAttendanceTrackingWrap.is-loading .pt-data-table {
            opacity: .42;
            transition: opacity .15s ease;
        }

        #personalTutorDashboard #studentAttendanceTrackingWrap .leaveTableLoader {
            background: rgba(246, 243, 234, .72);
            backdrop-filter: blur(1px);
            color: var(--pt-green);
        }

        #personalTutorDashboard #studentAttendanceTrackingWrap .leaveTableLoader svg {
            stroke: var(--pt-green);
        }

        #personalTutorDashboard .pt-empty {
            color: #96876a;
            font-size: 13px;
            padding: 24px;
            text-align: center;
        }

        #personalTutorDashboard .pt-term-alert {
            align-items: center;
            background: #fffdf7;
            border-color: #e2d5b9;
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            padding: 15px 18px;
        }

        #personalTutorDashboard .pt-term-alert-icon {
            align-items: center;
            background: #f3ecd8;
            border: 1px solid #e4dcc7;
            border-radius: 12px;
            color: #a1802f;
            display: inline-flex;
            flex: 0 0 42px;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        #personalTutorDashboard .pt-term-alert-title {
            color: #12312e;
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            line-height: 1.25;
        }

        #personalTutorDashboard .pt-term-alert-copy {
            color: #8d7c58;
            display: block;
            font-size: 12px;
            line-height: 1.35;
            margin-top: 2px;
        }

        @media (max-width: 1280px) {
            #personalTutorDashboard .pt-kpi-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            #personalTutorDashboard .pt-layout-grid {
                grid-template-columns: 300px minmax(0, 1fr);
            }
        }

        @media (max-width: 980px) {
            #personalTutorDashboard {
                padding: 18px 14px 36px;
            }

            #personalTutorDashboard .pt-welcome,
            #personalTutorDashboard .pt-layout-grid {
                display: block;
            }

            #personalTutorDashboard .pt-term-selector,
            #personalTutorDashboard .pt-left-rail {
                margin-top: 16px;
            }

            #personalTutorDashboard .pt-main {
                margin-top: 20px;
            }

            #personalTutorDashboard .pt-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            #personalTutorDashboard .pt-kpi-grid {
                grid-template-columns: 1fr;
            }

            #personalTutorDashboard .pt-title {
                font-size: 24px;
            }
        }
    </style>
@endsection

@section('subcontent')
    @php
        $employeeName = trim(($employee->title->name ?? '') . ' ' . ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
        $employeeName = $employeeName !== '' ? $employeeName : ($employee->full_name ?? $user->name ?? 'Personal Tutor');
        $employeeEmail = $employee->user->email ?? $user->email ?? '';
        $initialsFor = function ($name) {
            $clean = preg_replace('/^(?:(Mr|Mrs|Ms|Miss|Dr|Md)\.?\s+)+/i', '', trim((string) $name));
            $parts = preg_split('/\s+/', $clean ?: 'London Churchill');
            $first = $parts[0] ?? 'L';
            $last = count($parts) > 1 ? $parts[count($parts) - 1] : $first;

            return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
        };
        $employeeInitials = $initialsFor($employeeName);
        $otherTermsCount = is_countable($otherTerms) ? count($otherTerms) : 0;
        $moduleTotal = 0;
        $moduleSummary = [];
        $moduleLabelFor = function ($classType) {
            $label = trim((string) $classType);
            $normalised = strtolower($label);

            return [
                'theory' => 'Theory',
                'tutorial' => 'Tutorial',
                'seminar' => 'Seminer',
            ][$normalised] ?? $label;
        };
        if($myModules->count() > 0):
            foreach($myModules as $mm):
                $moduleTotal += (int) $mm->TOTAL_MODULE;
                if((int) $mm->TOTAL_MODULE > 0):
                    $moduleSummary[] = (int) $mm->TOTAL_MODULE . ' ' . $moduleLabelFor($mm->class_type);
                endif;
            endforeach;
        endif;
        $attendanceAvgValue = (float) str_replace('%', '', $attendance_avg);
        $attendanceTone = $attendanceAvgValue < 60 ? 'is-danger' : ($attendanceAvgValue < 75 ? 'is-warning' : '');
    @endphp

    <div id="personalTutorDashboard">
        <input type="hidden" id="planCourseId" value="0">

        <div class="pt-card pt-welcome">
            <div>
                <div class="pt-eyebrow">Welcome back</div>
                <h1 class="pt-title">{{ $employeeName }}</h1>
            </div>

            <div class="pt-term-selector ptTermDropdwnWrap">
                <div class="dropdown">
                    <button id="ptTermDropdown" class="dropdown-toggle pt-term-button term-dropdown-btn" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                        <span class="pt-term-label">{{ (isset($current_term->id) && $current_term->id > 0 ? $current_term->name : 'Select Term') }}</span>
                        <i data-lucide="chevron-down" class="pt-term-chev w-4 h-4"></i>
                    </button>
                    <div class="dropdown-menu">
                        <ul class="dropdown-content pt-dropdown-panel">
                            <li class="pt-dropdown-heading">Select Period</li>
                            @if($otherTermsCount > 0)
                                @foreach($otherTerms as $term)
                                    <li>
                                        <a data-term="{{ $term->name }}" data-id="{{ $term->id }}" href="javascript:void(0);" class="dropdown-item pt_term_item {{ (isset($current_term->id) && $current_term->id == $term->id ? 'is-active text-primary font-medium' : '') }}">
                                            <span>{{ $term->name }}</span>
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </a>
                                    </li>
                                @endforeach
                            @else
                                <li><span class="dropdown-item">No assigned terms</span></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt_term_content_wrap relative">
            <div class="leaveTableLoader">
                <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-10 h-10">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </div>

            <div class="pt_term_content">
                @if(isset($current_term->id) && $current_term->id > 0)
                    <div class="pt-kpi-grid">
                        <div class="pt-kpi-card">
                            <div class="pt-kpi-label">
                                <span class="pt-kpi-icon"><i data-lucide="book-open" class="w-4 h-4"></i></span>
                                Modules
                            </div>
                            <div id="totalModule" class="pt-kpi-value">{{ $moduleTotal }}</div>
                            <div class="pt-kpi-note">
                                @if(!empty($moduleSummary))
                                    @foreach($moduleSummary as $index => $summary)
                                        @if($index > 0) &middot; @endif{{ $summary }}
                                    @endforeach
                                @else
                                    0 Modules
                                @endif
                            </div>
                        </div>

                        <div class="pt-kpi-card">
                            <div class="pt-kpi-label">
                                <span class="pt-kpi-icon"><i data-lucide="users" class="w-4 h-4"></i></span>
                                Students
                            </div>
                            <div class="pt-kpi-value">{{ $no_of_assigned }}</div>
                            <div class="pt-kpi-note">Assigned to selected term</div>
                        </div>

                        <div class="pt-kpi-card">
                            <div class="pt-kpi-label">
                                <span class="pt-kpi-icon is-gold"><i data-lucide="file-text" class="w-4 h-4"></i></span>
                                Assignments
                            </div>
                            <div class="pt-kpi-value">{{ $no_of_assignment }}</div>
                            <div class="pt-kpi-note">Expected this term</div>
                        </div>

                        <div class="pt-kpi-card">
                            <div class="pt-kpi-label">
                                <span class="pt-kpi-icon is-warning"><i data-lucide="trending-up" class="w-4 h-4"></i></span>
                                Avg attendance
                            </div>
                            <div class="pt-kpi-value {{ $attendanceTone }}">{{ $attendance_avg }}</div>
                            <div class="pt-progress"><span style="width: {{ min(100, max(0, $attendanceAvgValue)) }}%;"></span></div>
                        </div>

                        <div class="pt-kpi-card">
                            <div class="pt-kpi-label">
                                <span class="pt-kpi-icon is-danger"><i data-lucide="alert-triangle" class="w-4 h-4"></i></span>
                                Below 60%
                            </div>
                            <a target="_blank" href="{{ route('attendance.percentage', [auth()->user()->id, ($current_term->id > 0 ? $current_term->id : 0)]) }}" class="pt-kpi-value is-danger">{{ $bellow_60 }}</a>
                            <div class="pt-kpi-note">Need follow-up</div>
                        </div>
                    </div>
                @else
                    <div class="pt-card pt-term-alert" role="alert">
                        <span class="pt-term-alert-icon"><i data-lucide="calendar-search" class="w-5 h-5"></i></span>
                        <span>
                            <span class="pt-term-alert-title">Current term data not found</span>
                            <span class="pt-term-alert-copy">Please select a term to load modules, attendance and student tracking.</span>
                        </span>
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-layout-grid">
            <div class="pt-left-rail">
                <div class="pt-card pt-profile-card">
                    <span class="pt-avatar">{{ $employeeInitials }}</span>
                    <div class="pt-profile-name">{{ $employeeName }}</div>
                    <div class="pt-profile-email">{{ $employeeEmail }}</div>
                    <span class="pt-role-pill"><span></span>Personal Tutor</span>

                    <form class="pt-search-form" method="post" action="#">
                        <div class="autoCompleteField" data-table="students">
                            <i data-lucide="search" class="pt-search-icon w-4 h-4"></i>
                            <input type="text" autocomplete="off" id="registration_no" name="student_id" class="pt-input registration_no" value="" placeholder="Search Student By ID">
                            <ul class="autoFillDropdown"></ul>
                            <input type="hidden" id="profileUrl" name="profile_url">
                        </div>
                        <button disabled id="viewStudentBtn" type="button" class="pt-search-button">
                            <i data-lucide="arrow-right" class="w-4 h-4 svgSearch"></i>
                            <svg style="opacity: 0;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 svgLoader absolute">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="pt-card pt-panel pt-today-panel">
                    <div class="pt-panel-header">
                        <h2 class="pt-panel-title">Today's Classes</h2>
                        <label class="pt-field-shell pt-today-date">
                            <i data-lucide="calendar-days" class="w-4 h-4"></i>
                            <input data-pt="{{ $user->id }}" id="personalTutorCalendar" value="{{ date('d / m / Y') }}" type="text" class="pt-input" placeholder="DD / MM / YYYY" data-format="DD / MM / YYYY" data-single-mode="true">
                        </label>
                    </div>
                    <div id="todays-classlist">
                        <div id="todaysClassListWrap" class="pt-class-list">
                            @if($todays_classes->count() > 0)
                                @foreach($todays_classes as $class)
                                    @php
                                        $showClass = 0;
                                        if(in_array(auth()->user()->last_login_ip, $venue_ips)):
                                            $listStart = date('Y-m-d').' '.$class->plan->start_time;
                                            $listEnd = date('Y-m-d').' '.$class->plan->end_time;
                                            $classStart = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($listStart)));
                                            $classEnd = date('Y-m-d H:i:s', strtotime($listEnd));
                                            $currentTime = date('Y-m-d H:i:s');
                                            if($currentTime >= $classStart && $currentTime <= $classEnd):
                                                $showClass = 1;
                                            elseif($currentTime < $classStart):
                                                $showClass = 2;
                                            endif;
                                        endif;
                                    @endphp
                                    <div class="pt-class-card {{ $showClass == 1 ? 'is-live' : '' }}">
                                        <div class="pt-class-head">
                                            <div class="pt-class-title">
                                                {{ $class->plan->creations->module_name }}
                                                ({{ $class->plan->group->name }})
                                                {{ (isset($class->plan->class_type) && !empty($class->plan->class_type) ? ' - '.$class->plan->class_type : '') }}
                                            </div>
                                            <div class="pt-class-time">{{ (isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '') }}</div>
                                        </div>
                                        <div class="pt-class-sub">
                                            {{ (isset($class->plan->course->name) ? $class->plan->course->name : '') }}
                                            @if(isset($class->plan->room->name) && !empty($class->plan->room->name))
                                                &middot; {{ $class->plan->room->name }}
                                            @endif
                                        </div>

                                        @if($class->plan->class_type == 'Tutorial' || ($class->plan->class_type == 'Seminar' && ($class->proxy_tutor_id == null || $class->proxy_tutor_id == 0)))
                                            @if(isset($class->attendanceInformation->id) && $class->attendanceInformation->id > 0)
                                                @if($class->feed_given == 1)
                                                    <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" href="{{ route('tutor-dashboard.attendance', [$class->plan->personal_tutor_id, $class->id, 1]) }}" class="start-punch pt-action"><i data-lucide="eye" class="w-4 h-4"></i>View Attendance</a>
                                                @else
                                                    <a href="{{ route('tutor-dashboard.attendance', [$class->plan->personal_tutor_id, $class->id, 1]) }}" data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" class="start-punch pt-action"><i data-lucide="eye" class="w-4 h-4"></i>Feed Attendance</a>
                                                @endif
                                                @if($class->feed_given == 1 && $class->attendanceInformation->end_time == null && $class->status == 'Ongoing')
                                                    <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch pt-action is-danger"><i data-lucide="x-circle" class="w-4 h-4"></i>End Class</a>
                                                @endif
                                            @else
                                                @if($showClass == 1)
                                                    <a data-tw-toggle="modal" data-id="{{ $class['id'] }}" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch pt-action">Start Class</a>
                                                @elseif($showClass == 2)
                                                    <div class="pt-class-alert" role="alert">
                                                        <i data-lucide="alert-triangle"></i>
                                                        <span>Class Start Button appears 15 minutes before the scheduled time.</span>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="pt-empty">No class found for the day.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-card pt-panel">
                    <div class="pt-panel-header">
                        <h2 class="pt-panel-title">My Modules</h2>
                    </div>
                    <div id="personalTutormoduleListWrap" class="relative">
                        <div class="leaveTableLoader">
                            <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-10 h-10">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <div id="personalTutormoduleList" class="pt-module-list">
                            @if($modules->count() > 0)
                                @php $i = 1; @endphp
                                @foreach($modules as $mod)
                                    @php $module_id = (isset($mod->parent_id) && $mod->parent_id > 0 ? $mod->parent_id : $mod->id); @endphp
                                    <a class="block" href="{{ route('tutor-dashboard.plan.module.show', $module_id) }}" target="_blank">
                                        <div id="moduleset-{{ $mod->id }}" class="module-details_{{ $mod->id }}">
                                            <div class="pt-module-item">
                                                <span class="pt-module-icon"><i data-lucide="book-open" class="w-4 h-4"></i></span>
                                                <div class="min-w-0 flex-1">
                                                    <div class="pt-module-title">{{ $mod->creations->module_name }}</div>
                                                    <div class="pt-module-sub">
                                                        {{ $mod->group->name }} &middot; {{ (!empty($mod->class_type) ? $mod->class_type : (isset($mod->creations->class_type) && !empty($mod->creations->class_type) ? $mod->creations->class_type : 'Unknown')) }}
                                                    </div>
                                                </div>
                                                <span class="pt-module-count">{{ $mod->activeAssign->count() }}</span>
                                            </div>
                                        </div>
                                    </a>
                                    @php $i += 1; @endphp
                                @endforeach
                            @else
                                <div class="pt-empty">Modules not found.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-main">
                <div class="pt-card pt-panel">
                    <div class="pt-panel-header">
                        <h2 class="pt-panel-title is-large">Students Attendance Tracking</h2>
                        <div class="pt-toolbar">
                            <label class="pt-field-shell">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <select name="trackingStatus" id="trackingStatus" class="pt-native-select">
                                    <option value="0">Outstanding</option>
                                    <option value="1">Close</option>
                                </select>
                            </label>
                            <label class="pt-field-shell">
                                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                                <input type="text" name="class_date" class="pt-input classDate" id="theAttendanceDate" value="{{ $yesterday }}">
                            </label>
                        </div>
                    </div>
                    <div class="pt-data-wrap" id="studentAttendanceTrackingWrap">
                        <div class="leaveTableLoader">
                            <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-10 h-10">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <table class="pt-data-table pt-attendance-table" id="studentTrackingListTable">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Attendance</th>
                                    <th>Missed Module</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4">
                                        <div class="pt-empty">Assigned student not found for the day.</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-card pt-panel">
                    <div class="pt-panel-header">
                        <div class="flex items-center flex-wrap gap-3">
                            <h2 class="pt-panel-title is-large">
                                E-learning Tracking
                                {{ (isset($theTerm->attenTerm->name) && !empty($theTerm->attenTerm->name) ? '['.$theTerm->attenTerm->name.']' : '') }}
                            </h2>
                            <span class="pt-badge"><i data-lucide="calendar-days" class="w-4 h-4"></i>{{ date('M Y') }}</span>
                            <button id="undecidedCount" type="button">{{ (isset($undecidedUploads) && !empty($undecidedUploads) ? $undecidedUploads : '0') }}</button>
                        </div>
                        <div class="pt-toolbar">
                            <label class="pt-field-shell">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                                <select class="pt-native-select" id="planClassStatus">
                                    <option value="Undecided">Undecided</option>
                                    <option value="Yes">Upload Completed</option>
                                    <option value="No">No Upload found</option>
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="pt-data-wrap dailyClassInfoTableWrap">
                        <div class="leaveTableLoader">
                            <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-10 h-10">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <table class="pt-data-table pt-elearning-table" id="dailyClassInfoTable">
                            <thead>
                                <tr>
                                    <th>Schedule</th>
                                    <th>Module</th>
                                    <th>Tutor</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th class="text-right">Upload Found?</th>
                                    <th>&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                {!! $classInformation !!}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @include('pages.personal-tutor.dashboard.modals')
    </div>
@endsection

@section('script')
    @vite('resources/js/tutor-personal.js')
@endsection
